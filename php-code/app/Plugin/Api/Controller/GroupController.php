<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Group Controller
 *
 */
class GroupController extends ApiAppController {

    /**
     * Scaffold
     *
     * @var mixed
     */
    public $scaffold;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Group.Group');
        $this->loadModel('Group.GroupUser');
    }

    public function browse() {

        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');
        $role_id = $this->_getUserRoleId();
        $param = $sFriendsList = '';
        if (!isset($this->request->params['type']))
            $type = 'search';
        else
            $type = $this->request->params['type'];
        if ($type == 'filter')
            $type = 'search';
        if (!empty($this->request->params['category_id'])) {
            $type = 'category';
            $param = $this->request->params['category_id'];
        }
        switch ($type) {
            case 'my':
            case 'friends':
                $this->_checkPermission();
                $groups = $this->GroupUser->getGroups($type, $uid, $page, $role_id);
                break;

            case 'featured':
                $this->_checkPermission();
                $groups = $this->Group->find('all', array('conditions' => array('Group.featured' => 1)));
                break;

            case 'join':
                $this->_checkPermission();
                $groups = $this->GroupUser->getJoinedGroups($uid, null);
                break;

            case 'popular':
                $this->_checkPermission();
                $groups = $this->Group->getPopularGroups(null, Configure::read('core.popular_interval'));
                break;

            case 'search':
                if (isset($this->request->data['keyword'])) {
                    $param = urldecode($this->request->data['keyword']);
                }

                if (!Configure::read('core.guest_search') && empty($uid))
                    $this->_checkPermission();
                else {
                    $groups = $this->Group->getGroups('search', $param, $page, null, $role_id, null);
                }
                break;

            default: // all, category
                if ($type != 'category') {
                    $param = $uid;
                }
                //get users of this group

                $groupId = $this->GroupUser->findAllByUserId($uid, array('group_id'));
                if (!empty($groupId)) {
                    $groupId = implode(',', Hash::extract($groupId, '{n}.GroupUser.group_id'));
                } else
                    $groupId = '';


                $groups = $this->Group->getGroups($type, $param, $page, null, $role_id, $groupId);
                $groups = Hash::sort($groups, '{n}.Group.id', ' desc');
        }

        if (empty($groups)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }

        //echo '<pre>';print_r($groups);die;
        $this->set('groups', $groups);
    }

    public function view() {
        $id = $this->request->params['group_id'];
        $uid = $this->Auth->user('id');
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('aco' => 'group_view'));
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));
        if ($uid) {
            $my_status = $this->GroupUser->getMyStatus($uid, $group['Group']['id']);

            $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);

            $this->set('my_status', $my_status);
            $this->set('is_member', $is_member);

            if (!empty($my_status) && $my_status['GroupUser']['status'] == GROUP_USER_ADMIN) {
                $request_count = Cache::read('request_count', 'group');
                if (!$request_count) {
                    $request_count = $this->GroupUser->find('count', array('conditions' => array('group_id' => $group['Group']['id'],
                            'status' => GROUP_USER_REQUESTED)
                    ));
                    Cache::write('request_count', $request_count, 'group');
                }

                $this->set('request_count', $request_count);
            }
        }

        $this->set('group', $group);
    }

    // GET group member of group
    public function groupMember() {
        $id = $this->request->params['group_id'];
        $uid = $this->Auth->user('id');
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('aco' => 'group_view'));
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));


        $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);

        if (!(empty($is_member) && !empty($group) && $group['Group']['type'] == PRIVACY_PRIVATE)) {
            $group_members = $this->GroupUser->getUsers($id, GROUP_USER_MEMBER, null, null);
        } else {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__d('api', 'This is private group. Only group members can view'));
        }
        if (empty($group_members)) {
            throw new ApiNotFoundException(__d('api', 'Group member not found'));
        }
        //echo '<pre>';print_r($group_members);die;
        $this->set('group_members', $group_members);
    }

    // GET group admin of a group
    public function groupAdmin() {
        $id = $this->request->params['group_id'];
        $uid = $this->Auth->user('id');
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('aco' => 'group_view'));
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));


        $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);

        if (!(empty($is_member) && !empty($group) && $group['Group']['type'] == PRIVACY_PRIVATE)) {
            $group_members = $this->GroupUser->getUsers($id, GROUP_USER_ADMIN, null, null);
        } else {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__d('api', 'This is private group. Only group members can view'));
        }
        if (empty($group_members)) {
            throw new ApiNotFoundException(__d('api', 'Group admin not found'));
        }
        $this->set('group_members', $group_members);
    }

    // GET group activity
    public function groupActivity() {
        $id = $this->request->params['group_id'];
        $uid = $this->Auth->user('id');
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('aco' => 'group_view'));
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));


        $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);

        if (!(empty($is_member) && !empty($group) && $group['Group']['type'] == PRIVACY_PRIVATE)) {
            MooCore::getInstance()->setSubject($group);
            $groupFeeds = $this->Feeds->get();
        } else {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__d('api', 'This is private group. Only group members can view'));
        }
        if (empty($groupFeeds)) {
            throw new ApiNotFoundException(__d('api', 'Group admin not found'));
        }
        if (empty($groupFeeds)) {
            throw new ApiNotFoundException(__('There are no new feeds to view at this time.'));
        }
        $this->set('datas', $groupFeeds);
    }

    // Leave a group
    public function leaveGroup() {
        $id = $this->request->params['group_id'];
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('aco' => 'group_view'));
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));
        $this->_checkPermission();
        $uid = $this->Auth->user('id');

        $my_status = $this->GroupUser->getMyStatus($uid, $id);

        if (!empty($my_status) && ( $uid != $my_status['Group']['user_id'] )) {
            $this->GroupUser->delete($my_status['GroupUser']['id']);

            // remove associated activity
            if ($my_status['Group']['type'] != PRIVACY_PRIVATE) {
                $this->loadModel('Activity');
                $activity = $this->Activity->getRecentActivity('group_join', $uid);

                if ($activity) {
                    $items = array_filter(explode(',', $activity['Activity']['items']));
                    $items = array_diff($items, array($id));

                    if (!count($items)) {
                        $this->Activity->delete($activity['Activity']['id']);
                    } else {
                        $this->Activity->id = $activity['Activity']['id'];
                        $this->Activity->save(
                                array('items' => implode(',', $items))
                        );
                    }
                }
            }
            // clear cache
            Cache::clearGroup('group', 'group');
            $this->set(array(
                'message' => __('You have successfully left this group'),
                '_serialize' => array('message', 'id'),
            ));
        } else {
            if (empty($my_status)) {
                throw new ApiNotFoundException(__d('api', 'You not in this group.'));
            }
            if ($uid == $my_status['Group']['user_id']) {
                throw new ApiBadRequestException(__d('api', 'You are group admin so can not leave this group.'));
            }
        }
    }

    // Feature a group
    public function featureGroup() {
        $id = $this->request->params['group_id'];
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }

        $this->_checkPermission(array('is_admin' => true));
        $cuser = $this->_getUser();
        if (!$cuser['Role']['is_admin']) {
            throw new ApiBadRequestException(__('Only admin can feature group.'));
        }
        if ($group['Group']['type'] == PRIVACY_PRIVATE) {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__('You are not allowed feature private group.'));
        }
        if ($group['Group']['featured'] == 1) {
            throw new ApiBadRequestException(__('Group featured already.'));
        }

        $this->Group->id = $id;
        $this->Group->save(array('featured' => 1));
        $this->set(array(
            'message' => __('Group has been featured'),
            'id' => $this->Group->id,
            '_serialize' => array('message', 'id'),
        ));
    }

    // Unfeature a group
    public function unfeatureGroup() {
        $id = $this->request->params['group_id'];
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }

        $this->_checkPermission(array('is_admin' => true));
        $cuser = $this->_getUser();
        if (!$cuser['Role']['is_admin']) {
            throw new ApiBadRequestException(__('Only admin can unfeature group.'));
        }
        if ($group['Group']['type'] == PRIVACY_PRIVATE) {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__('You are not allowed unfeature private group.'));
        }
        if ($group['Group']['featured'] == 0) {
            throw new ApiBadRequestException(__('Group unfeatured already.'));
        }

        $this->Group->id = $id;
        $this->Group->save(array('featured' => 0));

        $this->set(array(
            'message' => __('Group has been unfeatured'),
            'id' => $this->Group->id,
            '_serialize' => array('message', 'id'),
        ));
    }

    // Sent join request to a group
    public function joinRequest() {
        $id = $this->request->params['group_id'];
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));

        $uid = $this->Auth->user('id');

        $data['user_id'] = $uid;
        $data['group_id'] = $id;

        // check if user has a group_user record
        $my_status = $this->GroupUser->getMyStatus($uid, $id);
        if (!empty($my_status)) { // user has a record in group_user table
            if ($my_status['GroupUser']['status'] == GROUP_USER_INVITED) { // user was invited
                $data['status'] = GROUP_USER_MEMBER;
                $this->GroupUser->id = $my_status['GroupUser']['id'];
            } else
                throw new ApiBadRequestException(__d('api', 'Already joined.'));
        }
        else {

            switch ($group['Group']['type']) {
                case PRIVACY_RESTRICTED:
                    $data['status'] = GROUP_USER_REQUESTED;
                    break;

                case PRIVACY_PUBLIC:
                    $data['status'] = GROUP_USER_MEMBER;
                    break;

                case PRIVACY_PRIVATE:
                    $this->throwErrorCodeException('private_group');
                    throw new ApiBadRequestException(__('This is a private group. You must be invited by a group admin in order to join'));
            }
        }

        $this->GroupUser->save($data);

        if (isset($data['status']) && $data['status'] == GROUP_USER_REQUESTED) { // requested
            $this->loadModel('Notification');
            $this->Notification->record(array('recipients' => $group['Group']['user_id'],
                'sender_id' => $uid,
                'action' => 'group_request',
                'url' => '/groups/view/' . $id,
                'params' => h($group['Group']['name'])
            ));
            Cache::clearGroup('group', 'group');
            $this->set(array(
                'message' => __('Your join request has been sent'),
                'id' => $id,
                '_serialize' => array('message', 'id'),
            ));
        } else { // joined		
            $this->loadModel('Activity');
            $activity = $this->Activity->getRecentActivity('group_join', $uid);

            if (!empty($activity)) {
                // aggregate activities
                $group_ids = explode(',', $activity['Activity']['items']);

                if (!in_array($group['Group']['id'], $group_ids))
                    $group_ids[] = $group['Group']['id'];

                $this->Activity->id = $activity['Activity']['id'];
                $this->Activity->save(array('items' => implode(',', $group_ids)
                ));
            }
            else {
                $data = array('type' => 'Group_Group',
                    'action' => 'group_join',
                    'user_id' => $uid,
                    'item_type' => 'Group_Group',
                    'items' => $group['Group']['id'],
                    'target_id' => $group['Group']['id'],
                    'plugin' => 'Group',
                    'privacy' => $group['Group']['type']
                );

                $this->Activity->save($data);
            }
            $this->set(array(
                'message' => __('success'),
                'id' => $id,
                '_serialize' => array('message', 'id'),
            ));
        }
    }

    // View all join request from a group
    public function viewJoinRequest() {
        $id = $this->request->params['group_id'];
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('user_block' => $group['Group']['user_id']));

        $admins_list = $this->GroupUser->getUsersList($id, GROUP_USER_ADMIN);
        $this->_checkPermission(array('admins' => $admins_list));

        $requests = $this->GroupUser->getUsers($id, GROUP_USER_REQUESTED);
        if (empty($requests)) {
            throw new ApiNotFoundException(__d('api', 'Group request not found'));
        }

        $this->set('requests', $requests);
    }

    // Accept join request from user
    public function acceptRequest() {
        $id = $this->request->params['request_id'];
        $group = $this->GroupUser->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group request not found'));
        }
        $this->GroupUser->id = $id;
        $group_user = $this->GroupUser->read();

        $admins_list = $this->GroupUser->getUsersList($group_user['GroupUser']['group_id'], GROUP_USER_ADMIN);

        $this->_checkPermission(array('admins' => $admins_list));
        if ($group_user['GroupUser']['status'] == GROUP_USER_MEMBER) {
            throw new ApiBadRequestException(__('Joined already.'));
        }
        $this->GroupUser->save(array('status' => GROUP_USER_MEMBER));

        $this->_updateActivity($group_user['Group'], $group_user['GroupUser']['user_id']);

        Cache::clearGroup('group');
        $this->set(array(
            'message' => __d('api', $group_user['User']['name'] . ' is now a member of this group'),
            'id' => $id,
            '_serialize' => array('message', 'id'),
        ));
    }

    // Delete join request
    public function deleteRequest() {
        $id = $this->request->params['request_id'];
        $group = $this->GroupUser->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group request not found'));
        }
        $this->GroupUser->id = $id;
        $group_user = $this->GroupUser->read();
        $admins_list = $this->GroupUser->getUsersList($group_user['GroupUser']['group_id'], GROUP_USER_ADMIN);

        $this->_checkPermission(array('admins' => $admins_list));


        $this->GroupUser->delete($id);
        Cache::clearGroup('group');
        $this->set(array(
            'message' => __d('api', 'You have deleted the request. The sender will not be notified'),
            '_serialize' => array('message'),
        ));
    }

    // SEnd invite to join a group .
    public function sendInvite() {
        $uid = $this->Auth->user('id');
        $id = $this->request->data['group_id'];
        $group = $this->GroupUser->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);

        if ((empty($is_member) && empty($group) && $group['Group']['type'] == PRIVACY_PRIVATE)) {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__d('api', 'This is private group. Only group members can view'));
        }
        $this->_checkPermission(array('confirm' => true));
        $cuser = $this->_getUser();

        if (!empty($this->request->data['friends']) || !empty($this->request->data['emails'])) {
            $group = $this->Group->findById($this->request->data['group_id']);
            $admins_list = $this->GroupUser->getUsersList($id, GROUP_USER_ADMIN);

            // check if user can invite
            if ($group['Group']['type'] == PRIVACY_PRIVATE && (!in_array($cuser['id'], $admins_list) )) {
                throw new ApiBadRequestException(__('Only owner of this group can invite.'));
            }
            $my_status = $this->GroupUser->getMyStatus($uid, $id);
            if (empty($my_status)) {
                throw new ApiNotFoundException(__d('api', 'You not in this group.'));
            }
            if (!empty($this->request->data['friends'])) {
                $data = array();
                $friends = explode(',', $this->request->data['friends']);

                $group_users = $this->GroupUser->getUsersList($id);

                foreach ($friends as $friend_id)
                    if (!in_array($friend_id, $group_users))
                        $data[] = array('group_id' => $id, 'user_id' => $friend_id, 'status' => GROUP_USER_INVITED);
                    else {
                        $this->throwErrorCodeException('member_already');
                        throw new ApiBadRequestException(__('Can not invite user has id : '. $friend_id .' cause already member of this group.'));
                    }

                if (!empty($data)) {
                    $this->GroupUser->saveAll($data);

                    $this->loadModel('Notification');
                    $this->Notification->record(array('recipients' => $friends,
                        'sender_id' => $cuser['id'],
                        'action' => 'group_invite',
                        'url' => '/groups/view/' . $id,
                        'params' => h($group['Group']['name'])
                    ));
                }
            }

            if (!empty($this->request->data['emails'])) {
                $emails = explode(',', $this->request->data['emails']);

                $i = 1;

                $userModel = MooCore::getInstance()->getModel('user');
                $friends = $userModel->findAllByEmail($emails);
                $friends = Hash::extract($friends, '{n}.User.id');
                $group_users = $this->GroupUser->getUsersList($id);

                foreach ($friends as $friend_id)
                    if (!in_array($friend_id, $group_users))
                        $data[] = array('group_id' => $id, 'user_id' => $friend_id, 'status' => GROUP_USER_INVITED);

                if (!empty($data))
                    $this->GroupUser->saveAll($data);

                foreach ($emails as $email) {
                    if ($i <= 10) {
                        if (Validation::email(trim($email))) {

                            //find this user base on email
                            $user = $userModel->findByEmail($email);
                            //this user does not exist
                            $invite_checksum = '';
                            if (empty($user)) {
                                $invite_checksum = uniqid();
                                $groupUserInvitedModel = MooCore::getInstance()->getModel('Group.GroupUserInvite');
                                $groupUserInvitedModel->create();
                                $groupUserInvitedModel->set(array('group_id' => $group['Group']['id'], 'email' => $email, 'invite_checksum' => $invite_checksum));
                                $groupUserInvitedModel->save();
                            }
                            $ssl_mode = Configure::read('core.ssl_mode');
                            $http = (!empty($ssl_mode)) ? 'https' : 'http';
                            $this->MooMail->send(trim($email), 'group_invite_none_member', array(
                                'group_title' => $group['Group']['moo_title'],
                                'group_link' => $http . '://' . $_SERVER['SERVER_NAME'] . $group['Group']['moo_href'] . '/' . $invite_checksum,
                                'email' => trim($email),
                                'sender_title' => $cuser['name'],
                                'sender_link' => $http . '://' . $_SERVER['SERVER_NAME'] . $cuser['moo_href'],
                                    )
                            );
                        }
                    }
                    $i++;
                }
            }
            $this->set(array(
                'message' => __('Your invitations have been sent.'),
                '_serialize' => array('message'),
            ));
        } else {
            throw new ApiBadRequestException(__d('api', 'Please insert friend id  or email to invite .'));
        }
    }

    public function notifyOn() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['group_id'];
        $group = $this->GroupUser->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);

        if ((empty($is_member) && empty($group) && $group['Group']['type'] == PRIVACY_PRIVATE)) {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__d('api', 'This is private group. Only group members can view'));
        }
        $this->loadModel('Group.GroupNotificationSetting');
        $check = $this->GroupNotificationSetting->getStatus($id, $uid);
        if ($check == 1) {
            throw new ApiBadRequestException(__d('api', 'Group notification on already'));
        }
        $this->GroupNotificationSetting->changeStatus($id, $uid);
        $this->set(array(
            'message' => __('Turn on notification successfull'),
            '_serialize' => array('message'),
        ));
    }

    public function notifyOff() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['group_id'];
        $group = $this->GroupUser->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }

        $is_member = $this->GroupUser->isMember($uid, $group['Group']['id']);
        if ((empty($is_member) && empty($group) && $group['Group']['type'] == PRIVACY_PRIVATE)) {
            $this->throwErrorCodeException('private_group');
            throw new ApiBadRequestException(__d('api', 'This is private group. Only group members can view'));
        }

        $this->loadModel('Group.GroupNotificationSetting');
        $check = $this->GroupNotificationSetting->getStatus($id, $uid);
        if ($check == 0) {
            throw new ApiBadRequestException(__d('api', 'Group notification off already'));
        }
        $this->GroupNotificationSetting->changeStatus($id, $uid);
        $this->set(array(
            'message' => __('Turn off notification successfull'),
            '_serialize' => array('message'),
        ));
    }

    public function save() {
        $this->_checkPermission(array('confirm' => true));
        $this->loadModel('Group.GroupUser');
        $uid = $this->Auth->user('id');

        if (!isset($this->request->data['name']) || !isset($this->request->data['description']) || !isset($this->request->data['category_id'])) {
            if (!isset($this->request->data['name']))
                throw new ApiBadRequestException(__d('api', 'Group name is missing.'));
            if (!isset($this->request->data['description']))
                throw new ApiBadRequestException(__d('api', 'Group description is missing.'));
            if (!isset($this->request->data['category_id']))
                throw new ApiBadRequestException(__d('api', 'Group category is missing.'));
        }

        if (!empty($this->request->data['id'])) { // edit group
            // check edit permission			
            $group = $this->Group->findById($this->request->data['id']);
            if (empty($group)) {
                throw new ApiNotFoundException(__d('api', 'Group not found'));
            }
            $this->_checkPermission(array('admins' => array($group['Group']['user_id'])));
            $this->Group->id = $this->request->data['id'];
        } else
            $this->request->data['user_id'] = $uid;

        $this->request->data['type'] = isset($this->request->data['privacy']) ? $this->request->data['privacy'] : 1;
        if (isset($_FILES['qqfile'])) {
            $upload = $this->_uploadThumbnail();
            $this->request->data['photo'] = $upload['file'];
        }
        $this->Group->set($this->request->data);
        $this->_validateData($this->Group);

        if ($this->Group->save()) {

            $event = new CakeEvent('Plugin.Controller.Group.afterSaveGroup', $this, array(
                'uid' => $uid,
                'id' => $this->Group->id,
                'type' => $this->request->data['type']
            ));

            $this->getEventManager()->dispatch($event);

            if (empty($this->request->data['id'])) { // add group
                // make the group creator admin
                $this->GroupUser->save(array('group_id' => $this->Group->id,
                    'user_id' => $uid,
                    'status' => GROUP_USER_ADMIN
                ));
            }

            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->Group->id,
                '_serialize' => array('message', 'id'),
            ));
        }
    }

    public function delete() {
        $id = $this->request->params['group_id'];
        $group = $this->Group->findById($id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->_checkPermission(array('admins' => array($group['Group']['user_id'])));

        $cakeEvent = new CakeEvent('Plugin.Controller.Group.beforeDelete', $this, array('aGroup' => $group));
        $this->getEventManager()->dispatch($cakeEvent);

        // delete activity
        $activityModel = MooCore::getInstance()->getModel('Activity');
        $parentActivity = $activityModel->find('list', array('fields' => array('Activity.id'), 'conditions' =>
            array('Activity.item_type' => 'Group_Group', 'Activity.item_id' => $group['Group']['id'])));

        $activityModel->deleteAll(array('Activity.item_type' => 'Group_Group', 'Activity.item_id' => $group['Group']['id']), true, true);

        // delete child activity
        $activityModel->deleteAll(array('Activity.item_type' => 'Group_Group', 'Activity.parent_id' => $parentActivity));

        $this->Group->delete($id);

        $cakeEvent = new CakeEvent('Plugin.Controller.Group.afterDeleteGroup', $this, array('item' => $group));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->set(array(
            'message' => __('Group has been deleted'),
            '_serialize' => array('message'),
        ));
    }

    protected function _updateActivity($group, $uid) {

        $this->loadModel('Activity');
        $activity = $this->Activity->getRecentActivity('group_join', $uid);

        if (!empty($activity)) {
            // aggregate activities
            $group_ids = explode(',', $activity['Activity']['items']);

            if (!in_array($group['id'], $group_ids))
                $group_ids[] = $group['id'];

            $this->Activity->id = $activity['Activity']['id'];
            $this->Activity->save(array('items' => implode(',', $group_ids)
            ));
        }
        else {
            $data = array('type' => 'Group_Group',
                'action' => 'group_join',
                'user_id' => $uid,
                'item_type' => 'Group_Group',
                'items' => $group['id'],
                'target_id' => $group['id'],
                'plugin' => 'Group',
                'privacy' => $group['type']
            );

            $this->Activity->save($data);
        }
    }

}
