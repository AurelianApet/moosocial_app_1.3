<?php

App::uses('ApiAppController', 'Api.Controller');
App::uses('ActivityController', 'Api.Controller');

/**
 * Comments Controller
 *
 * @property Comment $Comment
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class CommentsController extends ApiAppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator', 'Session');

    protected function _sendNotifications($data) {
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();

        $this->loadModel('Notification');

        list($plugin, $model) = mooPluginSplit($data['type']);
        if ($plugin)
            $this->loadModel($plugin . '.' . $model);
        else
            $this->loadModel($model);

        $obj = $this->$model->findById($data['target_id']);

        // group topic / video
        if (!empty($obj[$model]['group_id']))
            $url = '/groups/view/' . $obj[$model]['group_id'] . '/' . strtolower($model) . '_id:' . $data['target_id'];
        else {
            $url = $obj[key($obj)]['moo_url'];
        }

        // send notifications to anyone who commented on this item within a day
        $users = $this->Comment->find('list', array('conditions' => array('Comment.target_id' => $data['target_id'],
                'Comment.type' => $data['type'],
                'Comment.user_id <> ' . $uid . ' AND Comment.user_id <> ' . $obj['User']['id'],
                'DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= Comment.created'
            ),
            'fields' => array('Comment.user_id'),
            'group' => 'Comment.user_id'
        ));

        if ($data['type'] == 'Photo_Photo') {
            $action = 'photo_comment';
            $params = serialize(array('actor' => $cuser, 'owner' => $obj['User']));
            $url .= '#content';
        } else {
            $action = 'item_comment';
            $params = h($obj[$model]['moo_title']);
        }

        if (!empty($users)) {
            $this->Notification->record(array('recipients' => $users,
                'sender_id' => $uid,
                'action' => $action,
                'url' => $url,
                'params' => $params
            ));
        }


        $content = strip_tags($data['message']);

        // insert into activity feed
        $this->loadModel('Activity');

        if ($data['type'] == 'Photo_Photo') { // update item comment activity
            // check privacy of album and group of this photo, if it's not for everyone then do not show it at all
            $update_activity = false;
            switch ($obj['Photo']['type']) {
                case 'Group_Group':
                    $this->loadModel('Group.Group');
                    $group = $this->Group->findById($obj['Photo']['target_id']);

                    if ($group['Group']['type'] != PRIVACY_PRIVATE)
                        $update_activity = true;

                    break;

                case 'Photo_Album':
                    $this->loadModel('Photo.Album');
                    $album = $this->Album->findById($obj['Photo']['target_id']);

                    if (isset($album['Album']['privacy']) && $album['Album']['privacy'] == PRIVACY_EVERYONE)
                        $update_activity = true;

                    break;
            }


            if ($update_activity) {
                $activity = $this->Activity->find('first', array(
                    'conditions' => array(
                        'Activity.item_type' => $data['type'],
                        'Activity.item_id' => $data['target_id'],
                        'Activity.params' => 'no-comments',
                        'Activity.type' => 'user'
                )));
                $comment = $data['comment'];
                if (!empty($activity)) { // update the latest one
                    $this->Activity->id = $activity['Activity']['id'];
                    $this->Activity->save(array('user_id' => $uid,
                        'content' => $content,
                        'items' => $comment['Comment']['id'],
                    ));
                } else // insert new      
                    $this->Activity->save(array('type' => 'user',
                        'action' => 'comment_add_' . strtolower($model),
                        'user_id' => $uid,
                        'content' => $content,
                        'item_type' => $data['type'],
                        'item_id' => $data['target_id'],
                        'query' => 1,
                        'params' => 'no-comments',
                        'plugin' => $plugin,
                        'items' => $comment['Comment']['id'],
                    ));
            }
        }
        else { // update item activity
            $activity = $this->Activity->getItemActivity($data['type'], $data['target_id']);

            if (!empty($activity)) {
                $this->Activity->id = $activity['Activity']['id'];
                $this->Activity->save(array('modified' => date("Y-m-d H:i:s")));
            }
        }


        $notificationStopModel = MooCore::getInstance()->getModel('NotificationStop');

        if (!$notificationStopModel->isNotificationStop($data['target_id'], $data['type'], $obj['User']['id'])) {
            // send notification to author
            if ($uid != $obj['User']['id']) {
                if ($data['type'] == APP_PHOTO)
                    $action = 'own_photo_comment';

                $this->Notification->record(array('recipients' => $obj['User']['id'],
                    'sender_id' => $uid,
                    'action' => $action,
                    'url' => $url,
                    'params' => $params
                ));
            }
        }

        //notification for user mention
        $this->_sendNotificationToMentionUser($data['message'], $url, 'mention_user_comment');
    }

    // get item from item_id
    protected function _getItem($type, $item_id) {
        list($plugin, $model) = mooPluginSplit($type);

            if ($plugin)
                $this->loadModel($plugin . '.' . $model);
            else
                $this->loadModel($model);

            $item = $this->$model->findById($item_id);
            return $item;
    }
    
    // check items exist , privacy , permission before do next action .
    protected function _checkBeforeAction($type, $item_id) {
        $uid = $this->Auth->user('id');
        //check item exist

        if ($type == 'activity') {
            $this->loadModel('Activity');
            $activity = $this->Activity->findById($item_id);
        } else {
            $item = $this->_getItem($type, $item_id);
        }

        if (empty($item) && empty($activity)) {
            throw new ApiNotFoundException(__d('api', 'items not exist.'));
        }

        // check permission for conversation .
        if ($type == APP_CONVERSATION) {
            $this->loadModel('ConversationUser');
            $convo_users = $this->ConversationUser->findAllByConversationId($item_id);
            if (empty($convo_users)) {
                throw new ApiNotFoundException(__d('api', 'conversation not exist.'));
            }
            foreach ($convo_users as $user) {
                $users_array[] = $user['ConversationUser']['user_id'];

                if ($uid == $user['ConversationUser']['user_id'])
                    $convo_user = $user['ConversationUser'];
            }
            $this->_checkPermission(array('admins' => $users_array));
        }
        // check activity feed allow comment or not .
        elseif ($type == 'activity') {
            if ($activity['Activity']['params'] == 'no-comments') {
                throw new ApiBadRequestException(__d('api', 'This activity feed not allow to comment'));
            }
        }
        // check privacy 
        else {
            list($plugin, $model) = mooPluginSplit($type);
            if ($plugin) { //echo '<pre>';print_r($item);die;
                $this->loadModel('Friend');
                $text = strtolower($plugin) . '_view';
                $this->_checkPermission(array('aco' => $text));
                $areFriends = $this->Friend->areFriends($uid, $item[$model]['id']);
                if (isset($item[$model]['privacy']))
                    $this->_checkPrivacy($item[$model]['privacy'], $item[$model]['id'], $areFriends);

                if ($plugin == 'Video' || $plugin == 'Topic') {
                    $this->loadModel('Group.GroupUser');
                    if ($item[$model]['group_id']) {
                        $is_member = $this->GroupUser->isMember($uid, $item[$model]['group_id']);
                        $cuser = $this->_getUser();
                        if (!$cuser['Role']['is_admin'] && !$is_member) {
                            $this->throwErrorCodeException('not_group_member');
                            throw new ApiBadRequestException(__d('api', 'This item is in a group. Only group members can leave or edit comment'));
                        }
                    }
                    if ($plugin == 'Topic') {
                        if ($item[$model]['locked']) {
                            $this->throwErrorCodeException('topic_is_blocked');
                            throw new ApiBadRequestException(__('This topic has been locked'));
                        }
                    }
                }
                if ($plugin == 'Photo' && $item[$model]['type'] == 'Group_Group') {
                    $this->loadModel('Group.GroupUser');
                    if ($item[$model]['target_id']) {
                        $is_member = $this->GroupUser->isMember($uid, $item[$model]['target_id']);
                        $cuser = $this->_getUser();
                        if (!$cuser['Role']['is_admin'] && !$is_member) {
                            $this->throwErrorCodeException('not_group_member');
                            throw new ApiBadRequestException(__('This a group photo. Only group members can leave comment'));
                        }
                    }
                }
            }
        }
    }

    public function add() {

        $this->loadModel('Activity');
        $this->loadModel('ConversationUser');

        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();
        $medium_photo = '';
        $commentdata = $this->request->data;
        $commentdata['activity_id'] = $this->request->data['item_id'];

        $type = $this->_getType($this->request->params['object']);
        $this->_checkBeforeAction($type, $commentdata['item_id']);

        // check comment is empty 
        if (empty($_FILES['commentphoto']) && empty($this->request->data['commentphoto']) && empty($this->request->data['comment'])) {
            throw new ApiBadRequestException(__d('api', 'Comment can not be empty'));
        }


        // upload image when comment by image
        if (isset($_FILES['commentphoto']) || isset($this->request->data['commentphoto'])) {

            $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

            App::import('Vendor', 'qqFileUploader');
            $uploader = new qqFileUploader($allowedExtensions);

            // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
            $path = 'uploads' . DS . 'tmp';
            $url = 'uploads/tmp/';
            $this->_prepareDir($path);
            $path = WWW_ROOT . $path;
            $_FILES['qqfile'] = $_FILES['commentphoto'];
            $result = $uploader->handleUpload($path);

            if (isset($result['error'])):
                $this->throwErrorCodeException('file extension invalid');
                throw new ApiBadRequestException(__($result['error']));
            endif;
            if (!empty($result['success'])) {
                // resize image
                App::import('Vendor', 'phpThumb', array('file' => 'phpThumb/ThumbLib.inc.php'));

                $photo = PhpThumbFactory::create($path . DS . $result['filename']);

                $medium_photo = $result['filename'];

                $result['photo'] = $url . $medium_photo;

                $result['file_path'] = FULL_BASE_URL . $this->request->webroot . $url . $medium_photo;
            }
            $commentdata['thumbnail'] = $result['photo'];
        }

        // save comment and send notify
        if ($type == 'activity') {

            $this->loadModel('ActivityComment');
            $this->loadModel('Notification');

            $commentdata['user_id'] = $uid;

            $isCommentOnePhoto = false;

            // insert this comment to the item page
            $activity = $this->Activity->findById($this->request->data['item_id']);
            $profile_id = $activity['Activity']['user_id'];
            $url = '/users/view/' . $profile_id . '/activity_id:' . $activity['Activity']['id'];

            if (!empty($activity) && !empty($activity['Activity']['item_type'])) {
                $item_type = $activity['Activity']['item_type'];
                $this->loadModel('Comment');
                // if this comment is on 1 photo
                if (
                        ($item_type == 'Photo_Album' && $activity['Activity']['action'] == 'wall_post') || ($item_type == 'Photo_Photo' && $activity['Activity']['action'] == 'photos_add')
                ) {
                    $items = explode(',', $activity['Activity']['items']);
                    if (count($items) == 1) {
                        $isCommentOnePhoto = true;
                    }
                }
            }
            if ($isCommentOnePhoto) {
                $this->Comment->clear();
                $this->request->data = array('user_id' => $uid,
                    'type' => 'Photo_Photo',
                    'target_id' => $items[0],
                    'thumbnail' => $commentdata['thumbnail'],
                    'message' => $commentdata['comment'],
                );
                $this->Comment->save($this->request->data);
                $photoComment = $this->Comment->read();
                $returnId = $photoComment['Comment']['id'];
                $this->set('comment', '1');
                $this->set('commentId', $this->Comment->id);
                $this->set('commentInPhoto', 1);
                $this->set('photoComment', $photoComment);

                $this->Activity->id = $commentdata['activity_id'];
                $this->Activity->save(array('modified' => date('Y-m-d H:i:s')));

                //notification for user mention
                $this->_sendNotificationToMentionUser($photoComment['Comment']['message'], $url, 'mention_user_comment');
                // event
                $cakeEvent = new CakeEvent('Controller.Comment.afterComment', $this, array('data' => $this->request->data));
                $this->getEventManager()->dispatch($cakeEvent);
            } else {
                if ($this->ActivityComment->save($commentdata)) {
                    $comment = $this->ActivityComment->read();
                    $returnId = $comment['ActivityComment']['id'];
                    // send notifications to commenters
                    $activity = $this->Activity->findById($commentdata['activity_id']);

                    $this->Activity->id = $commentdata['activity_id'];
                    $this->Activity->save(array('modified' => date('Y-m-d H:i:s')));

                    $params = array('actor' => $cuser, 'owner' => $activity['User']);



                    // insert this comment to the item page
                    if (!empty($activity['Activity']['item_type']) && !empty($activity['Activity']['item_id'])) {
                        $item_type = ( $activity['Activity']['item_type'] == 'Photo_Photo' ) ? 'Photo_Album' : $activity['Activity']['item_type'];

                        $this->loadModel('Comment');
                    }

                    //notification for user mention
                    $this->_sendNotificationToMentionUser($comment['ActivityComment']['comment'], $url, 'mention_user_comment');

                    // event
                    $cakeEvent = new CakeEvent('Controller.Activity.afterComment', $this, array('item' => $comment));
                    $this->getEventManager()->dispatch($cakeEvent);
                }
            }

            // send notification and email to wall author
            $notificationStopModel = MooCore::getInstance()->getModel('NotificationStop');

            if ($uid != $activity['User']['id']) {
                $check_send = false;
                if ($isCommentOnePhoto) {
                    $check_send = !$notificationStopModel->isNotificationStop($items[0], 'Photo_Photo', $activity['User']['id']);
                } else {
                    $check_send = !$notificationStopModel->isNotificationStop($activity['Activity']['id'], 'activity', $activity['User']['id']);
                }

                if ($check_send) {
                    $this->Notification->record(array('recipients' => $activity['User']['id'],
                        'sender_id' => $uid,
                        'action' => 'own_status_comment',
                        'url' => $url
                    ));
                }
            }
        } else {
            $this->loadModel('Comment');
            $this->request->data['user_id'] = $uid;
            $this->request->data['target_id'] = $this->request->data['item_id'];
            $this->request->data['message'] = isset($this->request->data['comment']) ? $this->request->data['comment'] : '';
            $this->request->data['type'] = $type;
            $this->request->data['thumbnail'] = isset($result['photo']) ? $result['photo'] : '';

            if ($this->Comment->save($this->request->data)) {
                $comment = $this->Comment->read();
                if (empty($comment)) {
                    throw new ApiNotFoundException(__d('api', 'items not exist.'));
                }
                $returnId = $comment['Comment']['id'];


                switch ($type) {
                    case APP_CONVERSATION:
                        $this->loadModel('Conversation');


                        // update unread var for participants, update modified field, message count field for convo, add noti and send email
                        $this->Conversation->id = $this->request->data['target_id'];
                        $conversation = $this->Conversation->read();
                        if (empty($conversation)) {
                            throw new ApiNotFoundException(__d('api', 'conversation not exist.'));
                        }
                        $this->Conversation->save(array('lastposter_id' => $uid,
                            //'modified' => date("Y-m-d H:i:s"), 
                            'message_count' => $conversation['Conversation']['message_count'] + 1
                        ));

                        $participants = $this->ConversationUser->find('list', array('conditions' => array('conversation_id' => $this->request->data['target_id'],
                                'ConversationUser.user_id <> ' . $uid
                            ),
                            'fields' => array('ConversationUser.user_id'),
                            'group' => 'ConversationUser.user_id'
                        ));

                        foreach ($participants as $key => $value) {
                            $this->ConversationUser->id = $key;
                            $this->ConversationUser->save(array('unread' => 1));
                        }

                        break;

                    default:


                        $cakeEvent = new CakeEvent('Controller.Comment.afterComment', $this, array('data' => $this->request->data));
                        $this->getEventManager()->dispatch($cakeEvent);

                        // MOOSOCIAL-2893
                        $this->loadModel('Activity');
                        $activity = $this->Activity->find('first', array('conditions' => array(
                                'Activity.item_type' => $type,
                                'Activity.item_id' => $this->request->data['target_id']
                        )));

                        if (!empty($activity)) {
                            $this->Activity->id = $activity['Activity']['id'];
                            $this->Activity->save(array('modified' => date('Y-m-d H:i:s')));
                        }

                        $data = $this->request->data;
                        $data['comment'] = $comment;

                        $this->_sendNotifications($data);
                }
            }
        }

        $this->set(array(
            'message' => __d('api', 'success'),
            //'id' => $returnId,
            '_serialize' => array('message'),
        ));
    }

    // edit a comment
    public function edit() {
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();
        $commentdata = $this->request->data;
        $commentdata['thumbnail'] = '';
        $type = $this->_getType($this->request->params['object']);
        $this->_checkBeforeAction($type, $commentdata['item_id']);

        // check comment is empty 
        if (empty($_FILES['commentphoto']) && empty($this->request->data['commentphoto']) && empty($this->request->data['comment'])) {
            throw new ApiBadRequestException(__d('api', 'Comment can not be empty'));
        }


        // upload image when comment by image
        if (isset($_FILES['commentphoto']) || isset($this->request->data['commentphoto'])) {

            $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

            App::import('Vendor', 'qqFileUploader');
            $uploader = new qqFileUploader($allowedExtensions);

            // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
            $path = 'uploads' . DS . 'tmp';
            $url = 'uploads/tmp/';
            $this->_prepareDir($path);
            $path = WWW_ROOT . $path;
            $_FILES['qqfile'] = $_FILES['commentphoto'];
            $result = $uploader->handleUpload($path);

            if (isset($result['error'])):
                $this->throwErrorCodeException('file extension invalid');
                throw new ApiBadRequestException(__($result['error']));
            endif;
            if (!empty($result['success'])) {
                // resize image
                App::import('Vendor', 'phpThumb', array('file' => 'phpThumb/ThumbLib.inc.php'));

                $photo = PhpThumbFactory::create($path . DS . $result['filename']);

                $medium_photo = $result['filename'];

                $result['photo'] = $url . $medium_photo;

                $result['file_path'] = FULL_BASE_URL . $this->request->webroot . $url . $medium_photo;
            }
            $commentdata['thumbnail'] = $result['photo'];
        }


        if ($type == 'activity') {
            $this->loadModel('ActivityComment');
            $activity_comment = $this->ActivityComment->findById($commentdata['comment_id']);
            if (empty($activity_comment)) {
                throw new ApiNotFoundException(__d('api', 'comment not exist.'));
            }
            $admins = array($activity_comment['ActivityComment']['user_id']); // activity poster

            switch (strtolower($activity_comment['Activity']['type'])) {
                case 'user':
                    $admins[] = $activity_comment['Activity']['target_id']; // user can delete comment posted by other users on their profile
                    break;
                default:
                    $type = $activity_comment['Activity']['type'];
                    $model = MooCore::getInstance()->getModel($type);
                    list($plugin, $name) = mooPluginSplit($type);
                    $helper = MooCore::getInstance()->getHelper($plugin . '_' . $plugin);
                    $subject = $model->findById($activity_comment['Activity']['target_id']);

                    $admins = $helper->getAdminList($subject);
                    $admins[] = $activity_comment['ActivityComment']['user_id'];
                    break;
            }

            $admins[] = $activity_comment['Activity']['user_id'];

            $this->_checkPermission(array('admins' => $admins));

            $previous_users = $this->_getUserIdInMention($activity_comment['ActivityComment']['comment']);
            $previous_users = is_array($previous_users) ? $previous_users : array();
            $new_users = $this->_getUserIdInMention($commentdata['comment']);
            $new_users = is_array($new_users) ? $new_users : array();
            $new_add_users = array_diff($new_users, $previous_users);

            $this->loadModel('CommentHistory');

            $this->ActivityComment->id = $activity_comment['ActivityComment']['id'];

            $uid = $this->Auth->user('id');

            $photo = 0;
            if (trim($commentdata['thumbnail']) == '') {
                if (trim($activity_comment['ActivityComment']['thumbnail']) != '') {
                    $photo = 3; //Remove
                }
            } else {
                if (trim($activity_comment['ActivityComment']['thumbnail']) == '') {
                    $photo = 1; // Add new
                } elseif (trim($activity_comment['ActivityComment']['thumbnail']) != $commentdata['thumbnail']) {
                    $photo = 2; //Replace
                }
            }

            if ($photo) {
                $this->ActivityComment->save(array('edited' => true, 'modified' => false, 'thumbnail' => $commentdata['thumbnail'], 'comment' => $commentdata['comment']));
            } else {
                $this->ActivityComment->save(array('edited' => true, 'modified' => false, 'comment' => $commentdata['comment']));
            }

            // event
            $cakeEvent = new CakeEvent('Controller.Activity.afterEditComment', $this, array('item' => $activity_comment));
            $this->getEventManager()->dispatch($cakeEvent);

            if (!empty($new_add_users)) {
                $url = '/users/view/' . $activity_comment['ActivityComment']['user_id'] . '/activity_id:' . $activity_comment['ActivityComment']['activity_id'];
                $this->_sendNotificationToMentionUser($commentdata['comment'], $url, 'mention_user_comment', $new_add_users);
            }

            if (!$activity_comment['ActivityComment']['edited']) {
                $this->CommentHistory->save(array(
                    'user_id' => $activity_comment['ActivityComment']['user_id'],
                    'type' => 'Core_Activity_Comment',
                    'content' => $activity_comment['ActivityComment']['comment'],
                    'target_id' => $activity_comment['ActivityComment']['id'],
                    'created' => $activity_comment['ActivityComment']['created'],
                    'photo' => $activity_comment['ActivityComment']['thumbnail'] != '' ? 1 : 0,
                ));
            }

            $this->CommentHistory->clear();
            $this->CommentHistory->save(array(
                'user_id' => $uid,
                'type' => 'Core_Activity_Comment',
                'target_id' => $activity_comment['ActivityComment']['id'],
                'content' => $commentdata['comment'],
                'photo' => $photo
            ));

            $activity_comment = $this->ActivityComment->read();
            if ($uid != $activity_comment['ActivityComment']['user_id']) {
                $this->set('other_user', $this->Auth->user());
            }
        } else {
            $this->loadModel('Comment');
            $comment = $this->Comment->findById($commentdata['comment_id']);
            if (empty($comment)) {
                throw new ApiNotFoundException(__d('api', 'comment not exist.'));
            }
            $item = MooCore::getInstance()->getItemByType($comment['Comment']['type'], $comment['Comment']['target_id']);
            $this->_checkExistence($item);
            $model = key($item);
            $this->$model = MooCore::getInstance()->getModel($comment['Comment']['type']);

            $admins = array($comment['Comment']['user_id']);

            if (isset($item[$model]['user_id']))
                $admins[] = $item[$model]['user_id'];

            $this->_checkPermission(array('admins' => $admins));

            $previous_users = $this->_getUserIdInMention($comment['Comment']['message']);
            $previous_users = is_array($previous_users) ? $previous_users : array();
            $new_users = $this->_getUserIdInMention($commentdata['comment']);
            $new_users = is_array($new_users) ? $new_users : array();
            $new_add_users = array_diff($new_users, $previous_users);

            $this->loadModel('CommentHistory');

            $this->Comment->id = $comment['Comment']['id'];

            $uid = $this->Auth->user('id');

            $photo = 0;
            if (trim($commentdata['thumbnail']) == '') {
                if (trim($comment['Comment']['thumbnail']) != '') {
                    $photo = 3; //Remove
                }
            } else {
                if (trim($comment['Comment']['thumbnail']) == '') {
                    $photo = 1; // Add new
                } elseif (trim($comment['Comment']['thumbnail']) != $commentdata['thumbnail']) {
                    $photo = 2; //Replace
                }
            }

            if ($photo) {
                $this->Comment->save(array('edited' => true, 'modified' => false, 'thumbnail' => $commentdata['thumbnail'], 'message' => $commentdata['comment']));
            } else {
                $this->Comment->save(array('edited' => true, 'modified' => false, 'message' => $commentdata['comment']));
            }

            if (!empty($new_add_users)) {
                list($plugin, $model) = mooPluginSplit($comment['Comment']['type']);
                if ($plugin)
                    $this->loadModel($plugin . '.' . $model);
                else
                    $this->loadModel($model);

                $obj = $this->$model->findById($comment['Comment']['target_id']);

                // group topic / video
                if (!empty($obj[$model]['group_id']))
                    $url = '/groups/view/' . $obj[$model]['group_id'] . '/' . strtolower($model) . '_id:' . $comment['Comment']['target_id'];
                else {
                    $url = $obj[key($obj)]['moo_url'];
                }
                $this->_sendNotificationToMentionUser($commentdata['comment'], $url, 'mention_user_comment', $new_add_users);
            }

            if (!$comment['Comment']['edited']) {
                $this->CommentHistory->save(array(
                    'user_id' => $comment['Comment']['user_id'],
                    'type' => 'Comment',
                    'content' => $comment['Comment']['message'],
                    'target_id' => $comment['Comment']['id'],
                    'created' => $comment['Comment']['created'],
                    'photo' => $comment['Comment']['thumbnail'] != '' ? 1 : 0,
                ));
            }

            $this->CommentHistory->clear();
            $this->CommentHistory->save(array(
                'user_id' => $uid,
                'type' => 'Comment',
                'target_id' => $comment['Comment']['id'],
                'content' => $commentdata['comment'],
                'photo' => $photo
            ));

            $comment = $this->Comment->read();
            if ($uid != $comment['Comment']['user_id']) {
                $this->set('other_user', $this->Auth->user());
            }

            $this->loadModel('Activity');
            $activity = $this->Activity->find('first', array(
                'conditions' => array(
                    'Activity.action' => 'comment_add_photo',
                    'Activity.items' => $commentdata['comment_id'],
                    'Activity.params' => 'no-comments',
                    'Activity.type' => 'user'
            )));

            if (!empty($activity)) { // update the latest one
                $this->Activity->id = $activity['Activity']['id'];
                $this->Activity->save(array(
                    'content' => $commentdata['comment']
                ));
            }
        }

        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    // delete a comment
    public function delete() {
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();
        $commentdata = $this->request->data;
        $type = $this->_getType($this->request->params['object']);
        $this->_checkBeforeAction($type, $commentdata['item_id']);


        if ($type == 'activity') {

            $this->loadModel('ActivityComment');
            $comment = $this->ActivityComment->findById($commentdata['comment_id']);
            $this->_checkExistence($comment);

            $admins[] = $comment['ActivityComment']['user_id']; // comment poster

            switch (strtolower($comment['Activity']['type'])) {
                case 'user':
                    $admins[] = $comment['Activity']['user_id']; // user can delete comment posted by other users on their profile
                    break;



                default:
                    $type = $comment['Activity']['type'];
                    $model = MooCore::getInstance()->getModel($type);
                    list($plugin, $name) = mooPluginSplit($type);
                    $helper = MooCore::getInstance()->getHelper($plugin . '_' . $plugin);
                    $subject = $model->findById($comment['Activity']['target_id']);

                    $admins = $helper->getAdminList($subject);
                    $admins[] = $comment['ActivityComment']['user_id'];
                    $admins[] = $comment['Activity']['user_id'];
                    break;
            }

            $this->_checkPermission(array('admins' => $admins));
            if ($commentdata['item_id'] != $comment['ActivityComment']['activity_id']) {
                $this->throwErrorCodeException('data_not_match');
                throw new ApiBadRequestException(__d('api', 'Something wrong with your data , please check again.'));
            }
            $this->ActivityComment->delete($commentdata['comment_id']);

            $comment_last = $this->ActivityComment->find('first', array(
                'conditions' => array('ActivityComment.activity_id' => $comment['Activity']['id']),
                'order' => array('ActivityComment.id DESC'),
            ));
            $this->loadModel('Activity');

            $this->Activity->id = $comment['Activity']['id'];
            if (count($comment_last)) {
                $this->Activity->save(array('modified' => $comment_last['ActivityComment']['created']));
            } else {
                $this->Activity->save(array('modified' => $comment['Activity']['created']));
            }

            // event
            $cakeEvent = new CakeEvent('Controller.Activity.afterDeleteComment', $this, array('comment' => $comment));
            $this->getEventManager()->dispatch($cakeEvent);
        } else {

            $comment = $this->Comment->findById($commentdata['comment_id']);
            $this->_checkExistence($comment);
            $item = MooCore::getInstance()->getItemByType($comment['Comment']['type'], $comment['Comment']['target_id']);
            $this->_checkExistence($item);
            $model = key($item);
            $this->$model = MooCore::getInstance()->getModel($comment['Comment']['type']);

            $admins = array($comment['Comment']['user_id']);

            $admins[] = $item[$model]['user_id'];

            // if it belongs to a group then the group admins can delete
            if (!empty($item[$model]['group_id'])) {
                $this->loadModel('Group.GroupUser');

                $group_admins = $this->GroupUser->getUsersList($item[$model]['group_id'], GROUP_USER_ADMIN);
                $admins = array_merge($admins, $group_admins);
            }

            $this->_checkPermission(array('admins' => $admins));
            //echo '<pre>';print_r($comment);die;
            if ($commentdata['item_id'] != $comment['Comment']['target_id']) {
                $this->throwErrorCodeException('data_not_match');
                throw new ApiBadRequestException(__d('api', 'Something wrong with your data , please check again.'));
            }
            $this->Comment->delete($commentdata['comment_id']);

            // descrease comment count
            if (method_exists($this->$model, 'decreaseCounter'))
                $this->$model->updateCounter($comment['Comment']['target_id']);

            //after delete comment
            $this->getEventManager()->dispatch(new CakeEvent('Controller.Comment.afterDelete', $this));

            // delete activity
            $this->loadModel('Activity');
            $this->Activity->deleteAll(array('action' => 'comment_add', 'Activity.item_type' => $comment['Comment']['type'], 'Activity.item_id' => $comment['Comment']['target_id']), true, true);

            $activity = $this->Activity->find('first', array(
                'conditions' => array('Activity.item_type' => $comment['Comment']['type'], 'Activity.item_id' => $comment['Comment']['target_id'])
            ));

            if ($activity && count($activity)) {
                $comment_last = $this->Comment->find('first', array(
                    'conditions' => array('Comment.type' => $comment['Comment']['type'], 'Comment.target_id' => $comment['Comment']['target_id']),
                    'order' => array('Comment.id DESC'),
                ));

                $this->Activity->id = $activity['Activity']['id'];
                if (count($comment_last)) {
                    $this->Activity->save(array('modified' => $comment_last['Comment']['created']));
                } else {
                    $this->Activity->save(array('modified' => $activity['Activity']['created']));
                }
            }
        }

        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    // get comment from item object
    public function view() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['item_id'];
        $type = $this->_getType($this->request->params['object']);
        $this->_checkBeforeAction($type, $id);


        if ($type == 'activity') { 
            $activites = new ActivityController();
            $data = $activites->get($id , true);
        }
        else { 
            $subject = $this->_getItem($type, $id);
            $likeModel = MooCore::getInstance()->getModel('Like');

            $key = key($subject);
            if($type == APP_CONVERSATION){
                $subject[$key]['moo_type'] = 'conversation';
            }
            $commentModel = MooCore::getInstance()->getModel('Comment');
            $comments = $commentModel->getComments($subject[$key]['id'], $subject[$key]['moo_type']);

            $data = $subject;
            $data['comments'] = $comments;
            $data['comment_count'] = isset($subject[$key]['comment_count']) ? $subject[$key]['comment_count'] :  count($comments);
        }
        $this->set(array(
            'data' => $data,
            'type' => $type,
           ));
    }
  
    // get list of edited comment
    public function listEdited() {
        $item_id = $this->request->params['item_id'];
        $comment_id = $this->request->params['comment_id'];
        $type = $this->_getType($this->request->params['object']);
        $this->_checkBeforeAction($type, $item_id);
        
        if ($type == 'activity') { 
            $activites = new ActivityController();
            $data = $activites->get($item_id , true);
            if(isset($data['ActivityComment']) && !empty($data['ActivityComment'])  ){
                $commentType = 'Core_Activity_Comment';
            }
            else {
                $commentType = 'Comment';
            }
        }
        else {
            $commentType = 'Comment';

        }
        $this->loadModel('CommentHistory');

        $histories = $this->CommentHistory->getHistory($commentType, $comment_id, null);
        $this->set(array(
            'histories' => $histories,
            'historiesCount' => $this->CommentHistory->getHistoryCount($commentType, $comment_id),
           ));
    }

}
