<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Video Controller
 *
 */
class VideoController extends ApiAppController {

    /**
     * Scaffold
     *
     * @var mixed
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Video.Video');
    }

    public $scaffold;

    public function fetchVideo($source = null) {
        if (!isset($source['type']) || !isset($source['url']) || empty($source['type']) || empty($source['url'])) {
            throw new ApiBadRequestException(__d("api", "Please check type or url again."));
        }
        $video = $this->Video->fetchVideo($source['type'], $source['url']);

        if (!empty($video)) {
            if (!empty($this->request->data['group_id'])) { // public video
                $source['group_id'] = $this->request->data['group_id'];
            }
            return $video;
        } else {
            throw new ApiBadRequestException(__d("api", "Invalid URL. Please try again"));
        }
    }

    public function browse() {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');
        $role_id = $this->_getUserRoleId();

        if (!empty($this->request->named['category_id'])) {
            $type = 'category';
            $param = $this->request->named['category_id'];
        }
        if (!isset($this->request->params['type']))
            $type = 'search';
        else
            $type = $this->request->params['type'];
        if ($type == 'filter')
            $type = 'search';
        $param = '';
        $sFriendsList = '';
        switch ($type) {
            case 'all':
                $param = $uid;
                break;
            case 'my':
            case 'friends':
                $this->_checkPermission();
                $param = $uid;
                break;

            case 'search':
                if (isset($this->request->data['keyword'])) {
                    $param = urldecode($this->request->data['keyword']);
                }

                if (!Configure::read('core.guest_search') && empty($uid))
                    $this->_checkPermission();

                break;

            default:
                $this->loadModel('Friend');
                $friends_list = $this->Friend->getFriendsList($uid);
                $aFriendListId = array_keys($friends_list);
                $sFriendsList = implode(',', $aFriendListId);
                if ($type != 'category') {
                    $param = $uid;
                }
        }

        if ($type != 'popular') {
            $videos = $this->Video->getVideos($type, $param, $page, RESULTS_LIMIT, $sFriendsList, $role_id);
        } else {
            $videos = $this->Video->getPopularVideos(null, Configure::read('core.popular_interval'));
        }
        if (empty($videos)) {
            throw new ApiNotFoundException(__d('api', 'Video not found'));
        }
        $this->set('videos', $videos);
    }

    public function browseByGroup() {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');
        $param = $this->request->params['group_id'];
        // check permission if group is private
        $this->loadModel('Group.Group');
        $group = $this->Group->findById($param);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->loadModel('Group.GroupUser');
        $is_member = $this->GroupUser->isMember($uid, $param);

        if ($group['Group']['type'] == PRIVACY_PRIVATE) {
            $cuser = $this->_getUser();

            if (!$cuser['Role']['is_admin'] && !$is_member) {
                $this->throwErrorCodeException('not_group_member');
                throw new ApiBadRequestException(__d('api', 'Only group members can view videos'));
            }
        }

        $videos = $this->Video->getVideos('group', $param, $page);
        if (empty($videos)) {
            throw new ApiNotFoundException(__d('api', 'Video not found'));
        }
        foreach ($videos as $key => $video) {
            $admins = $this->GroupUser->getUsersList($video['Video']['group_id'], GROUP_USER_ADMIN);
            $videos[$key]['Video']['admins'] = $admins;
        }

        $this->set('videos', $videos);
    }

    public function viewByCategory() {
        $this->loadModel('Category');
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $cat_id = $this->request->params['category_id'];


        if (!empty($cat_id)) {
            $videos = $this->Video->getVideos('category', $cat_id, $page);
        }
        if (empty($videos)) {
            throw new ApiNotFoundException(__d('api', 'Video not found'));
        }
        $this->set('videos', $videos);
    }

    public function view() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['id'];

        $video = $this->Video->findById($id);
        if (empty($video)) {
            throw new ApiNotFoundException(__d('api', 'Video not found'));
        }
        $this->_checkPermission(array('aco' => 'video_view'));
        $this->_checkPermission(array('user_block' => $video['Video']['user_id']));

        $this->loadModel('Friend');

        if (!empty($video['Video']['group_id'])) {
            $this->loadModel('Group.GroupUser');

            $is_member = $this->GroupUser->isMember($uid, $video['Video']['group_id']);
            $this->set('is_member', $is_member);

            if ($video['Group']['type'] == PRIVACY_PRIVATE) {
                $cuser = $this->_getUser();
                if (!$cuser['Role']['is_admin'] && !$is_member) {
                    $this->throwErrorCodeException('not_group_member');
                    throw new ApiBadRequestException(__("This is a private group video and can only be viewed by the group's members"));
                }
            }

            $admins = $this->GroupUser->getUsersList($video['Video']['group_id'], GROUP_USER_ADMIN);
            $this->set('admins', $admins);

            $video['admins'] = $admins;
        }

        $this->_checkPrivacy($video['Video']['privacy'], $video['User']['id']);

        $video['Video']['description'] = CakeText::truncate(strip_tags($video['Video']['description']), 80, array('ellipsis' => '', 'html' => false, 'exact' => false));
        $this->set('video', $video);
    }

    public function save() {

        $uid = $this->Auth->user('id');

        if (isset($this->request->data['tags'])) {
            $this->_checkTags(array($this->request->data['tags']));
        }
        $this->request->data['privacy'] = isset($this->request->data['privacy']) ? $this->request->data['privacy'] : 1;
        if (isset($this->request->data['group_id'])) {
            $this->request->data['category_id'] = 0;
        } else {
            if (!isset($this->request->data['category_id'])) {
                throw new ApiBadRequestException(__d('api', 'video category is missing.'));
            }
        }

        if (!empty($this->request->data['id'])) { // edit video
            // check edit permission			
            $video = $this->Video->findById($this->request->data['id']);
            if (empty($video)) {
                throw new ApiNotFoundException(__d('api', 'Video not found'));
            }
            if (!isset($this->request->data['title']) || empty($this->request->data['title']))
                $this->request->data['title'] = $video['Video']['title'];
            if (!isset($this->request->data['description']) || empty($this->request->data['description']))
                $this->request->data['description'] = $video['Video']['description'];

            $admins = array($video['User']['id']); // video creator

            $this->loadModel('Group.GroupUser');
            $group_admins = $this->GroupUser->getUsersList($video['Video']['group_id'], GROUP_USER_ADMIN);
            $admins = array_unique(array_merge($admins, $group_admins));

            // if it's a group video, add group admins to the admins array for permission checking
            $cakeEvent = new CakeEvent('Plugin.Controller.Video.edit', $this, array('video' => $video, 'admins' => $admins));
            $this->getEventManager()->dispatch($cakeEvent);
            if (!empty($cakeEvent->result['admins']))
                $admins = $cakeEvent->result['admins'];

            $this->_checkPermission(array('admins' => $admins));
            $this->Video->id = $this->request->data['id'];
        }
        else {
            $videoarray = $this->fetchVideo($this->request->data);

            $this->request->data['source'] = $videoarray['Video']['source'];
            $this->request->data['source_id'] = $videoarray['Video']['source_id'];
            $this->request->data['thumb'] = $videoarray['Video']['thumb'];
            if (!isset($this->request->data['title']) || empty($this->request->data['title']))
                $this->request->data['title'] = $videoarray['Video']['title'];
            if (!isset($this->request->data['description']) || empty($this->request->data['description']))
                $this->request->data['description'] = $videoarray['Video']['description'];
            // if it's a group video, check if user has permission to create video in this group
            if (!empty($this->request->data['group_id'])) {
                $this->loadModel('Group.GroupUser');
                if (!$this->GroupUser->isMember($uid, $this->request->data['group_id'])) {
                    $this->throwErrorCodeException('not_group_member');
                    throw new ApiBadRequestException(__d("api", "You are not member of this group."));
                }
                $this->request->data['category_id'] = 0;
            }

            $this->request->data['user_id'] = $uid;
        }

        $this->Video->set($this->request->data);

        if ($this->Video->save()) { // successfully saved	
            if (empty($this->request->data['id'])) { // add video
                $cakeEvent = new CakeEvent('Plugin.Controller.Video.afterAdd', $this, array('privacy' => $this->request->data['privacy'], 'uid' => $uid, 'video_id' => $this->Video->id));
                $this->getEventManager()->dispatch($cakeEvent);
            }
            $cakeEvent = new CakeEvent('Plugin.Controller.Video.afterSave', $this, array(
                'id' => $this->Video->id,
                'uid' => $uid,
                'privacy' => isset($this->request->data['privacy']) ? $this->request->data['privacy'] : PRIVACY_PUBLIC
            ));
            $this->getEventManager()->dispatch($cakeEvent);

            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->Video->id,
                '_serialize' => array('message', 'id'),
            ));
        } else {
            throw new ApiBadRequestException(__d('api', 'please check again.'));
        }
    }

    public function delete() {

        $id = $this->request->params['video_id'];
        $video = $this->Video->findById($id);
        if (empty($video)) {
            throw new ApiNotFoundException(__d('api', 'Video not found'));
        }

        $cakeEvent = new CakeEvent('Plugin.Controller.Video.beforeDelete', $this, array('video' => $video));
        $this->getEventManager()->dispatch($cakeEvent);
        if (!empty($cakeEvent->result['admins'])) {
            $admins = $cakeEvent->result['admins'];
        }
        $this->_checkPermission(array('admins' => $admins));
        $this->Video->deleteVideo($video);

        $cakeEvent = new CakeEvent('Plugin.Controller.Video.afterDeleteVideo', $this, array('item' => $video));
        $this->getEventManager()->dispatch($cakeEvent);
        
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));




       
    }

}
