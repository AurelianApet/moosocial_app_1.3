<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Topic Controller
 *
 */
class TopicController extends ApiAppController {

    /**
     * Scaffold
     *
     * @var mixed
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Topic.Topic');
    }

    public $scaffold;

    protected function _checkTopic($topic, $allow_author = false) {
        $this->_checkExistence($topic);
        $admins = array();

        if ($allow_author)
            $admins = array($topic['User']['id']); // topic creator
            
// if it's a group topic then group admins can do it
        if (!empty($topic['Topic']['group_id'])) {
            $this->loadModel('Group.GroupUser');

            $group_admins = $this->GroupUser->getUsersList($topic['Topic']['group_id'], GROUP_USER_ADMIN);
            $admins = array_merge($admins, $group_admins);
        }

        $this->_checkPermission(array('admins' => $admins));
    }

    // browse topic by type : all,my,friend,popular,search
    public function browse() {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');
        if (!isset($this->request->params['type']))
            $type = 'search';
        else
            $type = $this->request->params['type'];
        if ($type == 'filter')
            $type = 'search';
        $param ='';
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
        }
        if ($type != 'popular') {
            $topics = $this->Topic->getTopics($type, $param, $page);
        } else {
            $topics = $this->Topic->getPopularTopics(null, Configure::read('core.popular_interval'));
        }
        if (empty($topics)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->set('topics', $topics);
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
                throw new ApiBadRequestException(__d('api', 'Only group members can view topics'));
            }
        }

        $topics = $this->Topic->getTopics('group', $param, $page);
        if (empty($topics)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        foreach ($topics as $key => $topic) {
            $admins = $this->GroupUser->getUsersList($topic['Topic']['group_id'], GROUP_USER_ADMIN);
            $topics[$key]['Topic']['admins'] = $admins;
        }

        $this->set('topics', $topics);
    }

    public function view() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['id'];

        $topic = $this->Topic->findById($id);
        if (empty($topic)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->_checkPermission(array('aco' => 'topic_view'));
        $this->_checkPermission(array('user_block' => $topic['Topic']['user_id']));

        $this->loadModel('Friend');

        if (!empty($topic['Topic']['group_id'])) {
            $this->loadModel('Group.GroupUser');

            $is_member = $this->GroupUser->isMember($uid, $topic['Topic']['group_id']);
            $this->set('is_member', $is_member);

            if ($topic['Group']['type'] == PRIVACY_PRIVATE) {
                $cuser = $this->_getUser();
                if (!$cuser['Role']['is_admin'] && !$is_member) {
                    $this->throwErrorCodeException('not_group_member');
                    throw new ApiBadRequestException(__("This is a private group topic and can only be viewed by the group's members"));
                }
            }

            $admins = $this->GroupUser->getUsersList($topic['Topic']['group_id'], GROUP_USER_ADMIN);
            $this->set('admins', $admins);

            $topic['admins'] = $admins;
        }

        $this->loadModel('Attachment');
        $attachments = $this->Attachment->getAttachments(PLUGIN_TOPIC_ID, $topic['Topic']['id']);

        $files = array();
        $pictures = array();

        foreach ($attachments as $a)
            if (in_array(strtolower($a['Attachment']['extension']), array('jpg', 'jpeg', 'png', 'gif')))
                $topic['pictures'][] = $a;
            else
                $topic['files'][] = $a;

        $this->set('topic', $topic);
    }

    public function viewByCategory() {
        $this->loadModel('Category');
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $cat_id = $this->request->params['category_id'];


        if (!empty($cat_id)) {
            $topics = $this->Topic->getTopics('category', $cat_id, $page);
        }
        if (empty($topics)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->set('topics', $topics);
    }

    public function save() {

        $this->_checkPermission(array('confirm' => true));
        $uid = $this->Auth->user('id');

        if (!empty($this->request->data['id'])) { // edit topic
            // check edit permission
            $topic = $this->Topic->findById($this->request->data['id']);
            if (empty($topic)) {
                throw new ApiNotFoundException(__d('api', 'Topic not found'));
            }
            $this->_checkTopic($topic, true);

            $this->Topic->id = $this->request->data['id'];
        } else {
            if ($this->request->params['type'] == 'edit') {
                if (empty($this->request->data['id']))
                    throw new ApiBadRequestException(__d("api", "Topic id is missing"));
            }
            // if it's a group topic, check if user has permission to create topic in this group
            if (!empty($this->request->data['group_id'])) {
                $this->loadModel('Group.GroupUser');

                if (!$this->GroupUser->isMember($uid, $this->request->data['group_id'])) {
                    $this->throwErrorCodeException('not_group_member');
                    throw new ApiBadRequestException(__d("api", "You are not member of this group."));
                }
                $this->request->data['category_id'] = 0;
            }
            $this->request->data['user_id'] = $uid;
            $this->request->data['lastposter_id'] = $uid;
            $this->request->data['last_post'] = date('Y-m-d H:i:s');
        }

        if (!isset($this->request->data['body']) || !isset($this->request->data['title']) || !isset($this->request->data['category_id'])) {
            if (!isset($this->request->data['body']))
                throw new ApiBadRequestException(__d('api', 'topic body is missing.'));
            if (!isset($this->request->data['title']))
                throw new ApiBadRequestException(__d('api', 'topic title is missing.'));
            if (!isset($this->request->data['category_id']) && !isset($this->request->data['group_id']))
                throw new ApiBadRequestException(__d('api', 'topic category is missing.'));
        }
        if (isset($this->request->data['tags'])) {
            $this->_checkTags(array($this->request->data['tags']));
        }
        $this->request->data['body'] = str_replace('../', '/', $this->request->data['body']);
        if (isset($_FILES['qqfile'])) {
            $upload = $this->_uploadThumbnail();
            $this->request->data['thumbnail'] = $upload['file'];
        }
        if (!empty($this->request->data['group_id'])) {
            $this->request->data['category_id'] = 0;
        }

        $this->Topic->set($this->request->data);
        if ($this->Topic->save()) {
            if (empty($this->request->data['id'])) { // add topic
                $type = APP_USER;
                $target_id = 0;
                $privacy = PRIVACY_EVERYONE;

                // Todo: refactor on group plugin
                if (!empty($this->request->data['group_id'])) {
                    $type = 'Group_Group';
                    $target_id = $this->request->data['group_id'];

                    $this->loadModel('Group.Group');
                    $group = $this->Group->findById($this->request->data['group_id']);

                    if ($group['Group']['type'] == PRIVACY_PRIVATE)
                        $privacy = PRIVACY_ME;

                    Cache::delete('group_detail_' . $target_id, 'group');
                }
                $this->loadModel('Activity');
                $this->Activity->save(array('type' => $type,
                    'target_id' => $target_id,
                    'action' => 'topic_create',
                    'user_id' => $uid,
                    'item_type' => 'Topic_Topic',
                    'privacy' => $privacy,
                    'item_id' => $this->Topic->id,
                    'query' => 1,
                    'params' => 'item',
                    'plugin' => 'Topic'
                ));
            }
            $event = new CakeEvent('Plugin.Controller.Topic.afterSaveTopic', $this, array(
                'uid' => $uid,
                'id' => $this->Topic->id,
            ));
            $this->getEventManager()->dispatch($event);

            // update Topic item_id for photo thumbnail
            $this->loadModel('Photo.Photo');
            $this->Photo->updateAll(array('Photo.target_id' => $this->Topic->id), array(
                'Photo.type' => 'Topic',
                'Photo.user_id' => $uid,
                'Photo.target_id' => 0
            ));
            $this->loadModel('Tag');
            $this->Tag->saveTags($this->request->data['tags'], $this->Topic->id, 'Topic_Topic');
            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->Topic->id,
                '_serialize' => array('message', 'id'),
            ));
        }
    }

    public function delete() {

        $id = $this->request->params['topic_id'];
        $topic = $this->Topic->findById($id);
        if (empty($topic)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->_checkTopic($topic, true);

        $this->Topic->deleteTopic($topic);
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterDeleteTopic', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    public function pin() {
        $uid = $this->Auth->user('id');

        $id = $this->request->params['topic_id'];
        $topic = $this->Topic->findById($id);
        if (empty($topic)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->_checkPermission(array('aco' => array($topic['User']['id'])));
        $this->_checkTopic($topic, true);
        if ($topic['Topic']['pinned'] == 1)
            throw new ApiBadRequestException(__d("api", "Topic already pinned"));
        $this->Topic->id = $id;
        $this->Topic->save(array('pinned' => 1));

        // event
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterPin', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->set(array(
            'message' => __('Topic has been pinned'),
            '_serialize' => array('message'),
        ));
    }

    public function unpin() {

        $id = $this->request->params['topic_id'];
        $topic = $this->Topic->findById($id);
        if (empty($topic)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->_checkPermission(array('aco' => array($topic['User']['id'])));
        $this->_checkTopic($topic, true);
        if ($topic['Topic']['pinned'] == 0)
            throw new ApiBadRequestException(__d("api", "Topic already unpinned"));
        $this->Topic->id = $id;
        $this->Topic->save(array('pinned' => 0));

        // event
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterUnPin', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->set(array(
            'message' => __('Topic has been unpinned'),
            '_serialize' => array('message'),
        ));
    }

    public function lock() {

        $id = $this->request->params['topic_id'];
        $topic = $this->Topic->findById($id);
        if (empty($topic)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->_checkPermission(array('aco' => array($topic['User']['id'])));
        $this->_checkTopic($topic, true);
        if ($topic['Topic']['locked'] == 1)
            throw new ApiBadRequestException(__d("api", "Topic already locked"));
        $this->Topic->id = $id;
        $this->Topic->save(array('locked' => 1));

        // event
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterLock', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->set(array(
            'message' => __('Topic has been locked'),
            '_serialize' => array('message'),
        ));
    }

    public function unlock() {

        $id = $this->request->params['topic_id'];
        $topic = $this->Topic->findById($id);
        if (empty($topic)) {
            throw new ApiNotFoundException(__d('api', 'Topic not found'));
        }
        $this->_checkPermission(array('aco' => array($topic['User']['id'])));
        $this->_checkTopic($topic, true);
        if ($topic['Topic']['locked'] == 0)
            throw new ApiBadRequestException(__d("api", "Topic already unlocked"));
        $this->Topic->id = $id;
        $this->Topic->save(array('locked' => 0));


        $this->set(array(
            'message' => __('Topic has been unlocked'),
            '_serialize' => array('message'),
        ));
    }

}
