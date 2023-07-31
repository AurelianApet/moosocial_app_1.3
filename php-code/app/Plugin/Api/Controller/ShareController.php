<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Share Controller
 *
 */
class ShareController extends ApiAppController {

    public function beforeShare($share) {
        $uid = $this->Auth->user('id');
        $activity = array();
        $object_id = isset($share['object_id']) ? $share['object_id'] : null;
        $param = isset($share['param']) ? $share['param'] : null;
        switch ($param) {
            case 'Blog_Blog':
                $type = "blog_item_detail_share";
                break;
            case 'Photo_Album':
                $type = "album_item_detail_share";
                break;
            case 'Photo_Photo':
                $type = "photo_item_detail_share";
                break;
            case 'Video_Video':
                $type = "video_item_detail_share";
                break;
            case 'Topic_Topic':
                $type = "topic_item_detail_share";
                break;
            case 'Group_Group':
                $type = "group_item_detail_share";
                break;
            case 'Event_Event':
                $type = "event_item_detail_share";
                break;
            default:
                if ($param != "Activity") {
                    throw new ApiBadRequestException(__d('api', 'Param not correct. Please check again'));
                }
                break;
        }

        $plugin = '';
        $object = null;
        //$social_link_share = FULL_BASE_URL . $this->request->webroot;
        if (!empty($param)) {
            list($plugin, $name) = mooPluginSplit($param);

            if (!empty($object_id)) {
                $object = MooCore::getInstance()->getItemByType($param, $object_id);
                if (empty($object)) {
                    throw new ApiNotFoundException(__('Items not found'));
                }


                $this->loadModel('Friend');
                $text = strtolower($plugin) . '_view';
                $this->_checkPermission(array('aco' => $text));
                $areFriends = $this->Friend->areFriends($uid, $object[$name]['id']);
                if (isset($object[$name]['privacy']))
                    $this->_checkPrivacy($object[$name]['privacy'], $object[$name]['id'], $areFriends);

                if ($plugin == 'Group') {
                    if ($object[$name]['moo_privacy'] == PRIVACY_PRIVATE || $object[$name]['moo_privacy'] == PRIVACY_RESTRICTED) {
                        $this->throwErrorCodeException('privacy_setting');
                        throw new ApiBadRequestException(__d('api', 'Can not share this group'));
                    }
                }
                if ($plugin == 'Event') {
                    if ($object[$name]['type'] == PRIVACY_PRIVATE) {
                        $this->throwErrorCodeException('private_item');
                        throw new ApiBadRequestException(__d('api', 'Can not share this event'));
                    }
                }
                if ($plugin == 'Photo') {
                    if (!empty($object['Album']['moo_privacy']) && $object['Album']['moo_privacy'] == PRIVACY_ME) {
                        throw new ApiBadRequestException(__d('api', 'Can not share this items'));
                    }
                }
                if ($plugin == 'Video' || $plugin == 'Topic') {
                    $this->loadModel('Group.GroupUser');
                    if ($object[$name]['group_id']) {
                        $is_member = $this->GroupUser->isMember($uid, $object[$name]['group_id']);
                        $cuser = $this->_getUser();
                        if (!$cuser['Role']['is_admin'] && !$is_member) {
                            $this->throwErrorCodeException('not_group_member');
                            throw new ApiBadRequestException(__d('api', 'This item is in a group. Only group members can share'));
                        }
                    }
                    if ($plugin == 'Topic') {
                        if ($object[$name]['locked']) {
                            $this->throwErrorCodeException('topic_is_blocked');
                            throw new ApiBadRequestException(__('This topic has been locked'));
                        }
                    }
                }
                if ($plugin == 'Photo' && $object[$name]['type'] == 'Group_Group') {
                    $this->loadModel('Group.GroupUser');
                    if ($object[$name]['target_id']) {
                        $is_member = $this->GroupUser->isMember($uid, $object[$name]['target_id']);
                        $cuser = $this->_getUser();
                        if (!$cuser['Role']['is_admin'] && !$is_member) {
                            $this->throwErrorCodeException('not_group_member');
                            throw new ApiBadRequestException(__('This a group photo. Only group members can share'));
                        }
                    }
                }
            }

//            if (!empty($plugin)) {
//                $social_link_share = FULL_BASE_URL . $object[key($object)]['moo_href'];
//            }
        }

        if ($param == "Activity") {
            $this->loadModel('Activity');
            $activity = $this->Activity->findById($object_id);
            //echo '<pre>';print_r($object);die;
            if (empty($activity)) {
                throw new ApiNotFoundException(__('Items not found'));
            }
            if ($activity['Activity']['privacy'] == PRIVACY_ME) {
                $this->throwErrorCodeException('private_item');
                throw new ApiBadRequestException(__d('api', 'This is private item. Can not share'));
            }
            if (empty($activity['Activity']['share']) && $activity['Activity']['share']) {
                throw new ApiBadRequestException(__d('api', 'Can not share this item'));
            }
            $type = $activity['Activity']['action'] . '_share';
        }

        $shareArray = array(
            'activity' => $activity,
            'param' => $param,
            'plugin' => $plugin,
            'type' => $type,
            'object' => $object,
            'object_id' => $object_id,
        );
        //$this->set(compact('activity', 'cuid', 'param', 'plugin', 'type', 'object', 'object_id', 'social_link_share'));
        return $shareArray;
    }

    public function wall() {

        $share = $this->beforeShare($this->request->data);

        $messageText = isset($this->request->data['message']) ? $this->request->data['message'] : '';
        $action = $share['type'];
        $param = $share['param'];
        $object_id = $share['object_id'];
        $userTagging = isset($this->request->data['userTagging']) ? $this->request->data['userTagging'] : '';


        $uid = $this->Auth->user('id');

        $sender = MooCore::getInstance()->getViewer();
        $userModel = MooCore::getInstance()->getModel('User');

        // $share_type : me, friend, group, msg, email

        $this->loadModel('Activity');

        // check activity_id, make sure it is number
        if (!empty($object_id) && is_numeric($object_id)) {

            $data = array();
            $data['content'] = $messageText;
            $data['message'] = $messageText;
            $data['messageText'] = $messageText;
            $data['user_id'] = $uid;
            $data['share'] = true;
            $data['action'] = $action;

            $owner_id = null;

            if ($param == "Activity") { // share activity feed
                // find activity by activity_id
                $activity = $this->Activity->findById($object_id);
                $data['parent_id'] = $activity['Activity']['id'];
                $data['item_type'] = $activity['Activity']['item_type'];
                $data['plugin'] = $activity['Activity']['plugin'];
                $data['items'] = $activity['Activity']['items'];
                $data['privacy'] = $activity['Activity']['privacy'];
                $data['params'] = $activity['Activity']['params'];
                $data['type'] = 'User';
                $owner_id = $activity['Activity']['user_id'];
                $shared_link = Router::url(array(
                            'plugin' => false,
                            'controller' => 'users',
                            'action' => 'view',
                            $activity['Activity']['user_id'],
                            'activity_id' => $activity['Activity']['id']
                                ), true);
            } else { // share item detail
                list($plugin, $name) = mooPluginSplit($param);
                $object = MooCore::getInstance()->getItemByType($param, $object_id);

                $data['parent_id'] = $object[key($object)]['id'];
                if (isset($object[key($object)]['moo_privacy'])) {
                    $data['privacy'] = $object[key($object)]['moo_privacy'];
                }
                $data['type'] = 'User';
                $data['item_type'] = $param;
                $data['plugin'] = $plugin;
                $shared_link = FULL_BASE_URL . $object[key($object)]['moo_href'];
                $owner_id = $object[key($object)]['user_id'];
            }

            $share_activity = null;

            if (!empty($data)) {
                // do share
                $data['created'] = null;
                $data['modified'] = null;
                unset($data['id']);
                $this->Activity->clear();
                $this->Activity->create();
                $this->Activity->set($data);
                $this->Activity->save();

                $share_activity = $this->Activity->read();

                $activity_id = isset($share_activity['Activity']['id']) ? $share_activity['Activity']['id'] : 0;
                //notification for user mention
                $url = '/users/view/' . $uid . '/activity_id:' . $activity_id;
                $this->_sendNotificationToMentionUser($messageText, $url, 'mention_user');

                // tagging
                if (!empty($userTagging)) {
                    $this->loadModel('UserTagging');
                    $this->UserTagging->save(array('item_id' => $activity_id,
                        'item_table' => 'activities',
                        'users_taggings' => $userTagging,
                        'created' => date("Y-m-d H:i:s"),
                    ));
                }

                // notification
                $this->loadModel('Notification');
                if ($owner_id != $uid && !empty($share_activity)) { // not notify owner item if owner shared
                    $sharedLink = Router::url(array(
                                'plugin' => false,
                                'controller' => 'users',
                                'action' => 'view',
                                $share_activity['Activity']['user_id'],
                                'activity_id' => $share_activity['Activity']['id']
                                    ), true);

                    if ($userModel->checkSettingNotification($owner_id, 'share_item')) {
                        $this->Notification->record(array('recipients' => $owner_id,
                            'sender_id' => $uid,
                            'action' => 'shared_your_post',
                            'url' => $sharedLink
                        ));
                    }
                }


                // event
                $cakeEvent = new CakeEvent('Controller.Share.afterShare', $this, array('data' => $data));
                $this->getEventManager()->dispatch($cakeEvent);

                $this->set(array(
                    'message' => __('Shared Successfully'),
                    '_serialize' => array('message'),
                ));
            }
        }
    }

    public function friend() {
        $uid = $this->Auth->user('id');
        $share = $this->beforeShare($this->request->data);

        $messageText = isset($this->request->data['message']) ? $this->request->data['message'] : '';
        $action = $share['type'];
        $param = $share['param'];
        $object_id = $share['object_id'];
        $userTagging = isset($this->request->data['userTagging']) ? $this->request->data['userTagging'] : '';
        $friendSuggestion = isset($this->request->data['friendSuggestion']) ? $this->request->data['friendSuggestion'] : '';

        $tagsUid = array();
        if (!empty($friendSuggestion)) {
            $tagsUid = explode(',', $friendSuggestion);
        }
        $this->loadModel('Friend');
        $friends_list = $this->Friend->getFriendsList($uid);
        $aFriendListId = array_keys($friends_list);
        foreach ($tagsUid as $user_id) {
            if (!in_array($user_id, $aFriendListId)) {
                $this->throwErrorCodeException('not_friend_yet');
                throw new ApiBadRequestException(__d('api', 'You are not friend with user id : ' . $user_id . ' . Can not share'));
            }
        }


        $sender = MooCore::getInstance()->getViewer();
        $userModel = MooCore::getInstance()->getModel('User');

        // $share_type : me, friend, group, msg, email

        $this->loadModel('Activity');

        // check activity_id, make sure it is number
        if (!empty($object_id) && is_numeric($object_id)) {

            $data = array();
            $data['content'] = $messageText;
            $data['message'] = $messageText;
            $data['messageText'] = $messageText;
            $data['user_id'] = $uid;
            $data['share'] = true;
            $data['action'] = $action;

            $owner_id = null;

            if ($param == "Activity") { // share activity feed
                // find activity by activity_id
                $activity = $this->Activity->findById($object_id);
                $data['parent_id'] = $activity['Activity']['id'];
                $data['item_type'] = $activity['Activity']['item_type'];
                $data['plugin'] = $activity['Activity']['plugin'];
                $data['items'] = $activity['Activity']['items'];
                $data['privacy'] = $activity['Activity']['privacy'];
                $data['params'] = $activity['Activity']['params'];
                $data['type'] = 'User';
                $owner_id = $activity['Activity']['user_id'];
                $shared_link = Router::url(array(
                            'plugin' => false,
                            'controller' => 'users',
                            'action' => 'view',
                            $activity['Activity']['user_id'],
                            'activity_id' => $activity['Activity']['id']
                                ), true);
            } else { // share item detail
                list($plugin, $name) = mooPluginSplit($param);
                $object = MooCore::getInstance()->getItemByType($param, $object_id);

                $data['parent_id'] = $object[key($object)]['id'];
                if (isset($object[key($object)]['moo_privacy'])) {
                    $data['privacy'] = $object[key($object)]['moo_privacy'];
                }
                $data['type'] = 'User';
                $data['item_type'] = $param;
                $data['plugin'] = $plugin;
                $shared_link = FULL_BASE_URL . $object[key($object)]['moo_href'];
                $owner_id = $object[key($object)]['user_id'];
            }

            $share_activity = null;

            if (!empty($data)) {
                // do share
                $activity_id = null;
                foreach ($tagsUid as $user_id) {
                    $data['target_id'] = $user_id;

                    $this->loadModel('Activity');
                    $data['created'] = null;
                    $data['modified'] = null;
                    unset($data['id']);
                    $this->Activity->clear();
                    $this->Activity->create();
                    $this->Activity->set($data);
                    $this->Activity->save();

                    // notification
                    $this->loadModel('Notification');
                    $this->Notification->record(array('recipients' => $data['target_id'],
                        'sender_id' => $data['user_id'],
                        'action' => 'shared_to_friend_wall',
                        'url' => '/users/view/' . $data['user_id'] . '/activity_id:' . $this->Activity->id
                    ));

                    $share_activity = $this->Activity->read();

                    $activity_id = isset($share_activity['Activity']['id']) ? $share_activity['Activity']['id'] : 0;
                    // tagging
                    if (!empty($userTagging)) {
                        $this->loadModel('UserTagging');
                        $this->UserTagging->save(array('item_id' => $activity_id,
                            'item_table' => 'activities',
                            'users_taggings' => $userTagging,
                            'created' => date("Y-m-d H:i:s"),
                        ));
                    }
                }

                //notification for user mention
                $url = '/users/view/' . $uid . '/activity_id:' . $activity_id;
                $this->_sendNotificationToMentionUser($messageText, $url, 'mention_user');

                // notification
                $this->loadModel('Notification');
                if ($owner_id != $uid && !empty($share_activity)) { // not notify owner item if owner shared
                    $sharedLink = Router::url(array(
                                'plugin' => false,
                                'controller' => 'users',
                                'action' => 'view',
                                $share_activity['Activity']['user_id'],
                                'activity_id' => $share_activity['Activity']['id']
                                    ), true);

                    if ($userModel->checkSettingNotification($owner_id, 'share_item')) {
                        $this->Notification->record(array('recipients' => $owner_id,
                            'sender_id' => $uid,
                            'action' => 'shared_your_post',
                            'url' => $sharedLink
                        ));
                    }
                }


                // event
                $cakeEvent = new CakeEvent('Controller.Share.afterShare', $this, array('data' => $data));
                $this->getEventManager()->dispatch($cakeEvent);

                $this->set(array(
                    'message' => __('Shared Successfully'),
                    '_serialize' => array('message'),
                ));
            }
        }
    }

    public function group() {
        $uid = $this->Auth->user('id');
        $share = $this->beforeShare($this->request->data);
        $messageText = isset($this->request->data['message']) ? $this->request->data['message'] : '';
        $action = $share['type'];
        $param = $share['param'];
        $object_id = $share['object_id'];
        $userTagging = isset($this->request->data['userTagging']) ? $this->request->data['userTagging'] : '';
        $groupSuggestion = isset($this->request->data['groupSuggestion']) ? $this->request->data['groupSuggestion'] : '';

        $groupIds = array();
        if (!empty($groupSuggestion)) {
            $groupIds = explode(',', $groupSuggestion);
        }
        $this->loadModel('Group.GroupUser');
        $myjoingroup = $this->GroupUser->getJoinedGroups($uid, null);
        foreach ($groupIds as $group_id) {
            if (!in_array($group_id, $myjoingroup)) {
                $this->throwErrorCodeException('not_join_group');
                throw new ApiBadRequestException(__d('api', 'You are not in group id : ' . $group_id . ' . Can not share'));
            }
        }

        $sender = MooCore::getInstance()->getViewer();
        $userModel = MooCore::getInstance()->getModel('User');

        // $share_type : me, friend, group, msg, email

        $this->loadModel('Activity');

        // check activity_id, make sure it is number
        if (!empty($object_id) && is_numeric($object_id)) {

            $data = array();
            $data['content'] = $messageText;
            $data['message'] = $messageText;
            $data['messageText'] = $messageText;
            $data['user_id'] = $uid;
            $data['share'] = true;
            $data['action'] = $action;

            $owner_id = null;

            if ($param == "Activity") { // share activity feed
                // find activity by activity_id
                $activity = $this->Activity->findById($object_id);
                $data['parent_id'] = $activity['Activity']['id'];
                $data['item_type'] = $activity['Activity']['item_type'];
                $data['plugin'] = $activity['Activity']['plugin'];
                $data['items'] = $activity['Activity']['items'];
                $data['privacy'] = $activity['Activity']['privacy'];
                $data['params'] = $activity['Activity']['params'];
                $data['type'] = 'User';
                $owner_id = $activity['Activity']['user_id'];
                $shared_link = Router::url(array(
                            'plugin' => false,
                            'controller' => 'users',
                            'action' => 'view',
                            $activity['Activity']['user_id'],
                            'activity_id' => $activity['Activity']['id']
                                ), true);
            } else { // share item detail
                list($plugin, $name) = mooPluginSplit($param);
                $object = MooCore::getInstance()->getItemByType($param, $object_id);

                $data['parent_id'] = $object[key($object)]['id'];
                if (isset($object[key($object)]['moo_privacy'])) {
                    $data['privacy'] = $object[key($object)]['moo_privacy'];
                }
                $data['type'] = 'User';
                $data['item_type'] = $param;
                $data['plugin'] = $plugin;
                $shared_link = FULL_BASE_URL . $object[key($object)]['moo_href'];
                $owner_id = $object[key($object)]['user_id'];
            }

            $share_activity = null;

            if (!empty($data)) {
                // do share
                $activity_id = null;

                foreach ($groupIds as $group_id) {
                    $data['target_id'] = $group_id;
                    $data['type'] = 'Group_Group';

                    // disable re-share for item shared in restrict and private group
                    $this->loadModel('Group.Group');
                    $group = $this->Group->findById($group_id);
                    if (!empty($group)) {
                        if ($group['Group']['type'] == PRIVACY_RESTRICTED || $group['Group']['type'] == PRIVACY_PRIVATE) {
                            $data['share'] = false;
                        }
                    }

                    $this->loadModel('Activity');
                    $data['created'] = null;
                    $data['modified'] = null;
                    unset($data['id']);
                    $this->Activity->clear();
                    $this->Activity->create();
                    $this->Activity->set($data);
                    $this->Activity->save();

                    $share_activity = $this->Activity->read();
                    $activity_id = isset($share_activity['Activity']['id']) ? $share_activity['Activity']['id'] : 0;

                    // tagging
                    if (!empty($userTagging)) {
                        $this->loadModel('UserTagging');
                        $this->UserTagging->save(array('item_id' => $activity_id,
                            'item_table' => 'activities',
                            'users_taggings' => $userTagging,
                            'created' => date("Y-m-d H:i:s"),
                        ));
                    }
                }
                //notification for user mention
                $url = '/users/view/' . $uid . '/activity_id:' . $activity_id;
                $this->_sendNotificationToMentionUser($messageText, $url, 'mention_user');

                // tagging
                if (!empty($userTagging)) {
                    $this->loadModel('UserTagging');
                    $this->UserTagging->save(array('item_id' => $activity_id,
                        'item_table' => 'activities',
                        'users_taggings' => $userTagging,
                        'created' => date("Y-m-d H:i:s"),
                    ));
                }

                // notification
                $this->loadModel('Notification');
                if ($owner_id != $uid && !empty($share_activity)) { // not notify owner item if owner shared
                    $sharedLink = Router::url(array(
                                'plugin' => false,
                                'controller' => 'users',
                                'action' => 'view',
                                $share_activity['Activity']['user_id'],
                                'activity_id' => $share_activity['Activity']['id']
                                    ), true);

                    if ($userModel->checkSettingNotification($owner_id, 'share_item')) {
                        $this->Notification->record(array('recipients' => $owner_id,
                            'sender_id' => $uid,
                            'action' => 'shared_your_post',
                            'url' => $sharedLink
                        ));
                    }
                }


                // event
                $cakeEvent = new CakeEvent('Controller.Share.afterShare', $this, array('data' => $data));
                $this->getEventManager()->dispatch($cakeEvent);

                $this->set(array(
                    'message' => __('Shared Successfully'),
                    '_serialize' => array('message'),
                ));
            }
        }
    }

    public function msg() {
        $uid = $this->Auth->user('id');
        $share = $this->beforeShare($this->request->data);

        $messageText = isset($this->request->data['message']) ? $this->request->data['message'] : '';
        $action = $share['type'];
        $param = $share['param'];
        $object_id = $share['object_id'];
        $friendSuggestion = isset($this->request->data['friendSuggestion']) ? $this->request->data['friendSuggestion'] : '';

        $tagsUid = array();
        if (!empty($friendSuggestion)) {
            $tagsUid = explode(',', $friendSuggestion);
        }
        $this->loadModel('Friend');
        $friends_list = $this->Friend->getFriendsList($uid);
        $aFriendListId = array_keys($friends_list);
        foreach ($tagsUid as $user_id) {
            if (!in_array($user_id, $aFriendListId)) {
                $this->throwErrorCodeException('not_friend_yet');
                throw new ApiBadRequestException(__d('api', 'You are not friend with user id : ' . $user_id . ' . Can not share'));
            }
        }


        $sender = MooCore::getInstance()->getViewer();
        $userModel = MooCore::getInstance()->getModel('User');


        $this->loadModel('Activity');

        // check activity_id, make sure it is number
        if (!empty($object_id) && is_numeric($object_id)) {

            $data = array();
            $data['content'] = $messageText;
            $data['message'] = $messageText;
            $data['messageText'] = $messageText;
            $data['user_id'] = $uid;
            $data['share'] = true;
            $data['action'] = $action;

            $owner_id = null;

            if ($param == "Activity") { // share activity feed
                // find activity by activity_id
                $activity = $this->Activity->findById($object_id);
                $data['parent_id'] = $activity['Activity']['id'];
                $data['item_type'] = $activity['Activity']['item_type'];
                $data['plugin'] = $activity['Activity']['plugin'];
                $data['items'] = $activity['Activity']['items'];
                $data['privacy'] = $activity['Activity']['privacy'];
                $data['params'] = $activity['Activity']['params'];
                $data['type'] = 'User';
                $owner_id = $activity['Activity']['user_id'];
                $shared_link = Router::url(array(
                            'plugin' => false,
                            'controller' => 'users',
                            'action' => 'view',
                            $activity['Activity']['user_id'],
                            'activity_id' => $activity['Activity']['id']
                                ), true);
            } else { // share item detail
                list($plugin, $name) = mooPluginSplit($param);
                $object = MooCore::getInstance()->getItemByType($param, $object_id);

                $data['parent_id'] = $object[key($object)]['id'];
                if (isset($object[key($object)]['moo_privacy'])) {
                    $data['privacy'] = $object[key($object)]['moo_privacy'];
                }
                $data['type'] = 'User';
                $data['item_type'] = $param;
                $data['plugin'] = $plugin;
                $shared_link = FULL_BASE_URL . $object[key($object)]['moo_href'];
                $owner_id = $object[key($object)]['user_id'];
            }

            $share_activity = null;

            if (!empty($data)) {
                // do share
                $message = "Hi , \r\n " . $sender['User']['name'] . " " . __("shared you a link") . " " . $shared_link . " \r\n " . $messageText;
                $subject = __("%s shared you a link", $sender['User']['name']);


                $data['user_id'] = $uid;
                $data['lastposter_id'] = $uid;
                $data['subject'] = $subject;
                $data['message'] = $message;

                $this->loadModel('Conversation');
                $this->Conversation->set($data);
                $this->_validateData($this->Conversation);

                if (!empty($tagsUid)) {

                    if ($this->Conversation->save()) { // successfully saved	
                        $participants = array();

                        foreach ($tagsUid as $participant) {
                            $participants[] = array('conversation_id' => $this->Conversation->id, 'user_id' => $participant);
                        }

                        // add sender to convo users array
                        $participants[] = array('conversation_id' => $this->Conversation->id, 'user_id' => $uid, 'unread' => 0);

                        $this->loadModel('ConversationUser');
                        $this->ConversationUser->saveAll($participants);
                    }
                }


                // notification
                $this->loadModel('Notification');
                if ($owner_id != $uid && !empty($share_activity)) { // not notify owner item if owner shared
                    $sharedLink = Router::url(array(
                                'plugin' => false,
                                'controller' => 'users',
                                'action' => 'view',
                                $share_activity['Activity']['user_id'],
                                'activity_id' => $share_activity['Activity']['id']
                                    ), true);

                    if ($userModel->checkSettingNotification($owner_id, 'share_item')) {
                        $this->Notification->record(array('recipients' => $owner_id,
                            'sender_id' => $uid,
                            'action' => 'shared_your_post',
                            'url' => $sharedLink
                        ));
                    }
                }


                // event
                $cakeEvent = new CakeEvent('Controller.Share.afterShare', $this, array('data' => $data));
                $this->getEventManager()->dispatch($cakeEvent);

                $this->set(array(
                    'message' => __('Shared Successfully'),
                    '_serialize' => array('message'),
                ));
            }
        }
    }

    public function email() {

        $share = $this->beforeShare($this->request->data);

        $messageText = isset($this->request->data['message']) ? $this->request->data['message'] : '';
        $action = $share['type'];
        $param = $share['param'];
        $object_id = $share['object_id'];

        $email = isset($this->request->data['email']) ? $this->request->data['email'] : '';

        $emailList = array();
        if (!empty($email)) {
            $emailList = explode(',', $email);
        }
        $uid = $this->Auth->user('id');

        $sender = MooCore::getInstance()->getViewer();
        $userModel = MooCore::getInstance()->getModel('User');

        // $share_type : me, friend, group, msg, email

        $this->loadModel('Activity');

        // check activity_id, make sure it is number
        if (!empty($object_id) && is_numeric($object_id)) {

            $data = array();
            $data['content'] = $messageText;
            $data['message'] = $messageText;
            $data['messageText'] = $messageText;
            $data['user_id'] = $uid;
            $data['share'] = true;
            $data['action'] = $action;

            $owner_id = null;

            if ($param == "Activity") { // share activity feed
                // find activity by activity_id
                $activity = $this->Activity->findById($object_id);
                $data['parent_id'] = $activity['Activity']['id'];
                $data['item_type'] = $activity['Activity']['item_type'];
                $data['plugin'] = $activity['Activity']['plugin'];
                $data['items'] = $activity['Activity']['items'];
                $data['privacy'] = $activity['Activity']['privacy'];
                $data['params'] = $activity['Activity']['params'];
                $data['type'] = 'User';
                $owner_id = $activity['Activity']['user_id'];
                $shared_link = Router::url(array(
                            'plugin' => false,
                            'controller' => 'users',
                            'action' => 'view',
                            $activity['Activity']['user_id'],
                            'activity_id' => $activity['Activity']['id']
                                ), true);
            } else { // share item detail
                list($plugin, $name) = mooPluginSplit($param);
                $object = MooCore::getInstance()->getItemByType($param, $object_id);

                $data['parent_id'] = $object[key($object)]['id'];
                if (isset($object[key($object)]['moo_privacy'])) {
                    $data['privacy'] = $object[key($object)]['moo_privacy'];
                }
                $data['type'] = 'User';
                $data['item_type'] = $param;
                $data['plugin'] = $plugin;
                $shared_link = FULL_BASE_URL . $object[key($object)]['moo_href'];
                $owner_id = $object[key($object)]['user_id'];
            }

            $share_activity = null;

            if (!empty($data)) {
                // do share
                foreach ($emailList as $email) {
                    if (Validation::email(trim($email))) {

                        $ssl_mode = Configure::read('core.ssl_mode');
                        $http = (!empty($ssl_mode)) ? 'https' : 'http';

                        $this->MooMail->send(trim($email), 'shared_item', array(
                            'email' => trim($email),
                            'shared_user' => $email,
                            'user_shared' => $email,
                            'shared_content' => $messageText,
                            'shared_link' => $shared_link
                                )
                        );
                    }
                }

                // notification
                $this->loadModel('Notification');
                if ($owner_id != $uid && !empty($share_activity)) { // not notify owner item if owner shared
                    $sharedLink = Router::url(array(
                                'plugin' => false,
                                'controller' => 'users',
                                'action' => 'view',
                                $share_activity['Activity']['user_id'],
                                'activity_id' => $share_activity['Activity']['id']
                                    ), true);

                    if ($userModel->checkSettingNotification($owner_id, 'share_item')) {
                        $this->Notification->record(array('recipients' => $owner_id,
                            'sender_id' => $uid,
                            'action' => 'shared_your_post',
                            'url' => $sharedLink
                        ));
                    }
                }


                // event
                $cakeEvent = new CakeEvent('Controller.Share.afterShare', $this, array('data' => $data));
                $this->getEventManager()->dispatch($cakeEvent);

                $this->set(array(
                    'message' => __('Shared Successfully'),
                    '_serialize' => array('message'),
                ));
            }
        }
    }

}
