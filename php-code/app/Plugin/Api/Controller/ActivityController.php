<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Activity Controller
 *
 */
class ActivityController extends ApiAppController {

    public function beforeFilter() {
        parent::beforeFilter();

        $this->loadModel('User');
        $uid = $this->Auth->user('id');
    }

    public function home() {
        $type = $this->request->query('filter') ? $this->request->query('filter') : 'everyone';
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');

        if (in_array($type, array('everyone', 'friends'))) {
            $param = $this->Auth->user('id');
            if ($type == 'friends' && !$param) {
                $this->_checkPermission();
            }
        }
        if (empty($param)) {
            throw new ApiBadRequestException("Filter is invalid");
        }
        $data = array();

        $check_post_status = true;
        $subject = MooCore::getInstance()->getSubject();
        list($plugin, $name) = mooPluginSplit($type);
        if (!empty($plugin)) {
            $helper = MooCore::getInstance()->getHelper($plugin . '_' . $plugin);
            $check_post_status = $helper->checkPostStatus($subject, $uid);
        }
        $this->set('check_post_status', $check_post_status);

        $activity_feed = $type;
        $this->loadModel('Activity');

        $activities = $this->Activity->getActivities($activity_feed, $param, $uid, $page);
        $activities_count = $this->Activity->getActivitiesCount($activity_feed, $param, $uid);
        

        $this->set('activities', $activities);
        $data['activities'] = $activities;

        // get activity likes
        if (!empty($uid)) {
            $this->loadModel('Like');

            $activity_likes = $this->Like->getActivityLikes($activities, $uid);
            $this->set('activity_likes', $activity_likes);
            $data['activity_likes'] = $activity_likes;
        }

        if (!empty($data['activities'])) :
            $this->set('datas', $data);
        else:
            throw new ApiNotFoundException(__d('api', 'There are no new feeds to view at this time.'));
        endif;
    }

    // --- POST /activity/post 
    public function post() {
        
        $this->loadModel('Activity');
        $this->_checkPermission(array('confirm' => true));
        $uid = $this->Auth->user('id');
        $wallphotoArray = array();
        $this->request->data['action'] = 'wall_post';
        if (!isset($this->request->data['type'])) {
            $this->request->data['type'] = 'User';
        }
        if (!isset($this->request->data['target_id'])) {
            $this->request->data['target_id'] = 0;
        }
        if (!isset($_FILES['wallphoto']) && empty($this->request->data['message'])) {
            throw new ApiBadRequestException(__d('api', 'Share feed can not be empty'));
        }
        if (isset($_FILES['wallphoto']) || isset($this->request->data['wallphoto']) ) :

            $allowedExtensions = MooCore::getInstance()->_getPhotoAllowedExtension();
            foreach ($_FILES['wallphoto']['name'] as $i => $wallphoto):
                $wallphotos[$i]['name'] = $wallphoto;
            endforeach;
            foreach ($_FILES['wallphoto']['size'] as $i => $wallphoto):
                $wallphotos[$i]['size'] = $wallphoto;
            endforeach;
            foreach ($_FILES['wallphoto']['type'] as $i => $wallphoto):
                $wallphotos[$i]['type'] = $wallphoto;
            endforeach;
            foreach ($_FILES['wallphoto']['tmp_name'] as $i => $wallphoto):
                $wallphotos[$i]['tmp_name'] = $wallphoto;
            endforeach;
            foreach ($_FILES['wallphoto']['error'] as $i => $wallphoto):
                $wallphotos[$i]['error'] = $wallphoto;
            endforeach;

            App::import('Vendor', 'qqFileUploader');
            $path = 'uploads' . DS . 'tmp';
            $url = 'uploads/tmp/';
            $this->_prepareDir($path);
            $path = WWW_ROOT . $path;
            $uploader = new qqFileUploader($allowedExtensions);
            foreach ($wallphotos as $wallphoto):
                $_FILES['qqfile'] = $wallphoto;
                $result = $uploader->handleUpload($path);

                if (isset($result['error'])):
                    throw new ApiBadRequestException(__($result['error']));
                endif;
                if (!empty($result['success'])) :
                    // resize image
                    App::import('Vendor', 'phpThumb', array('file' => 'phpThumb/ThumbLib.inc.php'));

                    $photo = PhpThumbFactory::create($path . DS . $result['filename']);
                    $medium_photo = $result['filename'];
                    $result['photo'] = $url . $medium_photo;

                    $result['file_path'] = FULL_BASE_URL . $this->request->webroot . $url . $medium_photo;
                endif;
                $wallphotoArray[] = $result['photo'];
            endforeach;

            $this->loadModel('Photo.Album');
            $this->loadModel('Photo.Photo');
            $photoList = $wallphotoArray;

            $album = $this->Album->getUserAlbumByType($uid, 'newsfeed');
            $title = 'Newsfeed Photos';
            $album_type = '';
            $album_type_id = 0;

            if (empty($album)) {
                $this->Album->save(array('user_id' => $uid, 'type' => 'newsfeed', 'title' => $title), false);
                $album_id = $this->Album->id;
            } else
                $album_id = $album['Album']['id'];

            // MOOSOCIAL-2815
            if ($this->request->data['type'] == 'Event_Event' || $this->request->data['type'] == 'Group_Group') {
                $album_type = $this->request->data['type'];
                $album_type_id = $this->request->data['target_id'];
                $album_id = 0;
            }

            $album = $this->Album->read();

            $data = array();
            $data['type'] = 'Photo_Album';
            $data['target_id'] = $album_id;
            $data['user_id'] = $uid;
            $data['privacy'] = $this->request->data['privacy'];
            $data['album_type'] = $album_type;
            $data['album_type_id'] = $album_type_id;
            $photoId = array();
            $first = true;
            foreach ($photoList as $photoItem) :
                if ($photoItem) {
                    $data['thumbnail'] = $photoItem;
                    $this->Photo->create();
                    $this->Photo->set($data);
                    $this->Photo->save();
                    array_push($photoId, $this->Photo->id);
                    if ($first) {
                        $first = false;
                        if (!$album['Album']['cover'] && !empty($album_id)) {
                            $photo = $this->Photo->read();
                            $this->Album->clear();
                            $this->Album->id = $album_id;
                            $this->Album->save(array('cover' => $photo['Photo']['thumbnail']));
                        }
                    }
                }
            endforeach;
            $this->request->data['items'] = implode(',', $photoId);
            $this->request->data['item_type'] = 'Photo_Album';
            $this->request->data['item_id'] = $album_id;
        else:
            $this->request->data['content'] = $this->request->data['message'];
            $this->Activity->parseLink($this->request->data);
        endif;

        $this->request->data['user_id'] = $uid;
        $this->request->data['content'] = $this->request->data['message'];
        $this->request->data['privacy'] = (!empty($this->request->data['privacy']) ) ? $this->request->data['privacy'] : PRIVACY_ME;


        if ($this->request->data['type'] == 'User' && $this->request->data['target_id']) {
            $user_id = $this->request->data['target_id'];
            $user = $this->User->findById($user_id);
            $this->request->data['privacy'] = $user['User']['privacy'];
        }


        // enable shared feature for status
        // do not add share link for feed of Event and Group
        if ($this->request->data['type'] != 'Group_Group' && $this->request->data['type'] != 'Event_Event') {
            $this->request->data['share'] = true;
        }

        // enable for public Group
        if ($this->request->data['type'] == 'Group_Group') {
            $groupModel = MooCore::getInstance()->getModel('Group.Group');
            $group = $groupModel->findById($this->request->data['target_id']);
            if (!empty($group) && $group['Group']['type'] == PRIVACY_PUBLIC) {
                $this->request->data['share'] = true;
            }
        }

        // enable for public Event
        if ($this->request->data['type'] == 'Event_Event') {
            $eventModel = MooCore::getInstance()->getModel('Event.Event');
            $event = $eventModel->findById($this->request->data['target_id']);
            if (!empty($event) && $event['Event']['type'] == PRIVACY_PUBLIC) {
                $this->request->data['share'] = true;
            }
        }
        
        if ($this->Activity->save($this->request->data)) {
            $activity = $this->Activity->read();
            //notification for user mention
            $url = '/users/view/' . $uid . '/activity_id:' . $activity['Activity']['id'];
            $this->_sendNotificationToMentionUser($activity['Activity']['content'], $url, 'mention_user');

            if (!empty($this->request->data['wall_photo_id'])) {
                $this->loadModel('Photo.Photo');
                $activity['Content'] = $this->Photo->findById($this->request->data['wall_photo_id']);
            } else {
                $event = new CakeEvent('ActivitesController.afterShare', $this, array('activity' => $activity));
                $this->getEventManager()->dispatch($event);
                $activity = $this->Activity->read();
            }

            switch (strtolower($this->request->data['type'])) {
                case 'user':
                    if (!empty($this->request->data['target_id']) && $this->request->data['target_id'] != $uid) { // post on other user's profile
                        $this->loadModel('Notification');
                        $this->Notification->record(array('recipients' => $this->request->data['target_id'],
                            'sender_id' => $uid,
                            'action' => 'profile_comment',
                            'url' => '/users/view/' . $this->request->data['target_id'] . '/activity_id:' . $activity['Activity']['id']
                        ));
                    }
                    break;
            }
        }
        //$this->set('data', $activity);
        $this->set(array(
            'message' => __d('api', 'success'),
            'id' => $activity['Activity']['id'],
            '_serialize' => array('message', 'id'),
        ));
    }

    public function get($activity_id, $return = false) {
        $uid = $this->Auth->user('id');
        if (is_numeric($activity_id)) { 
            $this->loadModel('Activity');
            $activity = $this->Activity->findById($activity_id);
            if (empty($activity)):
                throw new ApiNotFoundException(__d('api', 'This activity feed not exist.'));
            endif;
            $activities = $this->Activity->getActivities('detail', $activity_id);
            $activity = $activities[0];

            // check group permission
            if (isset($activity['Activity']['type']) && $activity['Activity']['type'] == 'Group_Group') {
                $this->loadModel('Group.Group');
                $target_id = $activity['Activity']['target_id'];
                $group = $this->Group->find('first', array(
                    'conditions' => array(
                        'Group.id' => $target_id
                    )
                ));
                $this->loadModel('Group.GroupUser');
                $is_member = $this->GroupUser->isMember($uid, $target_id);
                $group['Group']['is_member'] = $is_member;
            }

            // check event permission
            if (isset($activity['Activity']['type']) && $activity['Activity']['type'] == 'Event_Event') {
                $this->loadModel('Event.Event');
                $target_id = $activity['Activity']['target_id'];
                $event = $this->Event->findById($activity['Activity']['target_id']);
                if ($event['Event']['type'] == PRIVACY_EVERYONE)
                    $is_invited = 1;
                else
                    $is_invited = $this->Event->EventRsvp->getMyRsvp($uid, $target_id);
            }

            if (isset($group['Group']['type'])):
                if ($group['Group']['type'] == PRIVACY_RESTRICTED && !$group['Group']['is_member']):
                    $this->throwErrorCodeException('access_denied');
                    throw new ApiBadRequestException(__d('api', 'This activity feed have content is private . You can not view this activity'));
                elseif ($group['Group']['type'] == PRIVACY_PRIVATE && !$group['Group']['is_member']):
                    $this->throwErrorCodeException('access_denied');
                    throw new ApiBadRequestException(__d('api', 'This activity feed is in a private group. You must be invited by a group admin in order to join and view this activity'));
                endif;
            elseif (isset($is_invited) && empty($is_invited)):
                $this->throwErrorCodeException('access_denied');
                throw new ApiBadRequestException(__d('api', 'This activity feed is in a private event. You can not view this activity .'));
            endif;
            if($return) {
                return $activity;
            }
            else {
                $this->set('data', $activity);
            }
        }
        else {
            throw new ApiNotFoundException(__d('api', 'This activity feed not exist.'));
        }
    }

    public function delete($activity_id) {
        if (is_numeric($activity_id)) {
            $this->autoRender = false;
            $this->_checkPermission(array('confirm' => true));

            $this->loadModel('Activity');
            $activity = $this->Activity->findById($activity_id);
            if (empty($activity)):
                throw new ApiNotFoundException(__d('api', 'This activity feed not exist.'));
            endif;

            $admins = array($activity['Activity']['user_id']); // activity poster

            switch (strtolower($activity['Activity']['type'])) {
                case 'user':
                    $admins[] = $activity['Activity']['target_id']; // user can delete status posted by other users on their profile
                    break;

                default:
                    $type = $activity['Activity']['type'];
                    $model = MooCore::getInstance()->getModel($type);
                    list($plugin, $name) = mooPluginSplit($type);
                    $helper = MooCore::getInstance()->getHelper($plugin . '_' . $plugin);
                    $subject = $model->findById($activity['Activity']['target_id']);
                    if (method_exists($helper, 'getAdminList'))
                        $admins = $helper->getAdminList($subject);
                    $admins[] = $activity['Activity']['user_id']; // user can delete status posted by other users on their profile

                    break;
            }

            $this->_checkPermission(array('admins' => $admins));
            $this->Activity->delete($activity_id);

            // event
            $cakeEvent = new CakeEvent('Controller.Activity.afterDeleteActivity', $this, array('activity' => $activity));
            $this->getEventManager()->dispatch($cakeEvent);
            $response =array('success'=>'true');
            echo json_encode($response);
        }
        else {
            throw new ApiNotFoundException(__d('api', 'This activity feed not exist.'));
        }
    }
}
