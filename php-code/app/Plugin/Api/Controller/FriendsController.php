<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Friends Controller
 *
 */
class FriendsController extends ApiAppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadmodel('Friend');
        $this->loadModel('FriendRequest');
        $this->loadModel('User');
    }

    // GET friend list
    public function getlist() {
        $uid = $this->Auth->user('id');
        $users = array();
        $this->_checkPermission();
        $users = $this->Friend->getUserFriends($uid, null, null);
        if (empty($users)):
            throw new ApiNotFoundException(__d('api', 'No friend found.'));
        endif;
        $this->set('users', $users);
    }

    //GET user friend list
    public function getuserlist($id) {
        $data = $this->User->findById($id);
        if (empty($data)) {
            throw new ApiNotFoundException(__d('api', 'User does not exist.'));
        }
        $canView = $this->_canViewProfile($data["User"]);
        if (!$canView) {
            throw new ApiUnauthorizedException(__d('api', 'User does not have access to this resource.'));
        }
        $users = array();
        $users = $this->Friend->getUserFriends($id, null, null);
        if (empty($users)):
            throw new ApiNotFoundException(__d('api', 'No friend found.'));
        endif;
        $this->set('users', $users);
    }

    //POST send friend request to a user.
    public function add() {
        
        $this->_checkPermission(array('confirm' => true));
        $cuser = $this->_getUser();
        $uid = $this->Auth->user('id');
        $requestdata = $this->request->data;
        if (is_numeric($requestdata['user_id'])) {
            $user = $this->User->findById($requestdata['user_id']);
            if (empty($user)) {
                $this->throwErrorCodeException('user_not_exist');
                throw new ApiNotFoundException(__d('api', 'User does not exist.'));
            }
        } else {
            $this->throwErrorCodeException('');
            throw new ApiBadRequestException(__d('api', 'User id not correct.'));
        }

        if ($uid == $requestdata['user_id']) {
            throw new ApiBadRequestException(__('You cannot send friend request to yourself'));
        }

        // check if users are already friends
        if ($this->Friend->areFriends($uid, $requestdata['user_id'])) {
            throw new ApiBadRequestException(__('You are already a friend of this user'));
        }


        if ($this->FriendRequest->existRequest($uid, $requestdata['user_id'])) {
            throw new MethodNotAllowedException(__('You have already sent a friend request to this user'));
        }

        $requestdata['sender_id'] = $uid;

        if ($this->FriendRequest->save($requestdata)) {

            // add notification
            $this->loadModel('Notification');
            $this->Notification->record(array('recipients' => $requestdata['user_id'],
                'sender_id' => $uid,
                'action' => 'friend_add',
                'url' => '/home/index/tab:friend-requests'
            ));



            if ($user['User']['notification_email']) {
                $ssl_mode = Configure::read('core.ssl_mode');
                $http = (!empty($ssl_mode)) ? 'https' : 'http';

                $this->MooMail->send($user, 'friend_request', array(
                    'recipient_title' => $user['User']['moo_title'],
                    'recipient_link' => $http . '://' . $_SERVER['SERVER_NAME'] . $user['User']['moo_href'],
                    'sender_title' => $cuser['moo_title'],
                    'sender_link' => $http . '://' . $_SERVER['SERVER_NAME'] . $cuser['moo_href'],
                    'message' => h($requestdata['message']),
                    'request_link' => $http . '://' . $_SERVER['SERVER_NAME'] . $this->request->base . '/home/index/tab:friend-requests',
                        )
                );
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //POST accept friend request from user.
    public function accept() {

        $requestdata = $this->request->data;
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();

        $request = $this->FriendRequest->getRequest($requestdata['id']);
        if (!empty($request)) {
            $this->FriendRequest->id = $requestdata['id'];
            // insert to friends table
            $this->Friend->create();
            $this->Friend->save(array('user_id' => $uid, 'friend_id' => $request['Sender']['id']));
            $this->Friend->create();
            $this->Friend->save(array('user_id' => $request['Sender']['id'], 'friend_id' => $uid));

            // insert into activity feed
            $this->loadModel('Activity');
            $activity = $this->Activity->getRecentActivity('friend_add', $uid);

            if (!empty($activity)) {
                // aggregate activities
                $user_ids = explode(',', $activity['Activity']['items']);

                if (!in_array($request['Sender']['id'], $user_ids))
                    $user_ids[] = $request['Sender']['id'];

                $this->Activity->id = $activity['Activity']['id'];
                $this->Activity->save(array('items' => implode(',', $user_ids),
                    'params' => '',
                    'privacy' => 1,
                    'query' => 1
                ));
            }
            else {
                $this->Activity->save(array('type' => 'user',
                    'action' => 'friend_add',
                    'user_id' => $uid,
                    'item_type' => APP_USER,
                    'items' => $request['Sender']['id']
                ));
            }

            // send a notification to the sender				
            $this->loadModel('Notification');
            $this->Notification->record(array('recipients' => $request['Sender']['id'],
                'sender_id' => $uid,
                'action' => 'friend_accept',
                'url' => '/users/view/' . $uid
            ));

            //mark notification as read
            $notifyId = $this->Notification->find('first', array(
                'conditions' => array(
                    'Notification.user_id' => $uid,
                    'Notification.sender_id' => $request['Sender']['id'],
                    'Notification.action' => 'friend_add',
                    'Notification.read' => 0)
                    )
            );
            if (!empty($notifyId['Notification']['id'])) {
                $this->Notification->id = $notifyId['Notification']['id'];
                $this->Notification->save(array('read' => 1));
            }
            // delete notification
            $this->Notification->deleteAll(array('Notification.user_id' => $uid, 'Notification.sender_id' => $request['Sender']['id'], 'Notification.action' => 'friend_add'), false);

            // add private activity to sender's wall
            $this->Activity->create();
            $this->Activity->save(array('type' => 'user',
                'action' => 'friend_add',
                'user_id' => $request['Sender']['id'],
                'item_type' => APP_USER,
                'items' => $uid,
                'privacy' => 3
            ));
            $this->FriendRequest->delete($requestdata['id']);
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            throw new ApiNotFoundException(__d('api', 'Friend request not found'));
        }
    }

    //POST reject friend request from user.
    public function reject() {

        $requestdata = $this->request->data;
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();

        $request = $this->FriendRequest->getRequest($requestdata['id']);

        if (!empty($request)) {
            $this->FriendRequest->id = $requestdata['id'];
            $this->FriendRequest->delete($requestdata['id']);
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            throw new ApiNotFoundException(__d('api', 'Friend request not found'));
        }
    }

    //POST cancel friend request sent to user.
    public function cancel() {
        $id = intval($this->request->data['user_id']);
        $uid = $this->Auth->user('id');

        if (is_numeric($id)) {
            $user = $this->User->findById($id);
            if (empty($user)) {
                throw new ApiNotFoundException(__d('api', 'User does not exist.'));
            }
        } else {
            throw new ApiBadRequestException(__d('api', 'User id not correct.'));
        }

        if ($this->FriendRequest->existRequest($uid, $id)) {
            $this->FriendRequest->deleteAll(array('FriendRequest.sender_id' => $uid, 'FriendRequest.user_id' => $id));
            // Issue: counterCache not working when using deleteAll, have to using updateCounterCache
            $this->FriendRequest->updateCounterCache(array('user_id' => $id));
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            throw new ApiNotFoundException(__d('api', 'Friend request not found'));
        }
    }

    //POST delete friendship to a user
    public function delete() {
        $requestdata = $this->request->data;
        $this->_checkPermission();
        $uid = $this->Auth->user('id');
        $friend_id = $requestdata['user_id'];

        if (is_numeric($friend_id)) {
            $user = $this->User->findById($friend_id);
            if (empty($user)) {
                throw new ApiNotFoundException(__d('api', 'User does not exist.'));
            }
        } else {
            throw new ApiBadRequestException(__d('api', 'User id not correct.'));
        }
        if ($this->Friend->areFriends($uid, $friend_id)) {
            
            $this->Friend->deleteAll(array('Friend.user_id' => $uid, 'Friend.friend_id' => $friend_id), true, true);
            $this->Friend->deleteAll(array('Friend.user_id' => $friend_id, 'Friend.friend_id' => $uid), true, true);
            // remove feed
            $this->loadModel('Activity');
            $activities = $this->Activity->find('all', array('conditions' => array(
                    'OR' => array(
                        array(
                            'Activity.action' => 'friend_add',
                            'Activity.user_id' => $uid,
                        ),
                        array(
                            'Activity.action' => 'friend_add',
                            'Activity.user_id' => $friend_id,
                        )
                    ),
            )));
            foreach ($activities as $item) {
                $friendsid = explode(',', $item['Activity']['items']);

                if ($item['Activity']['user_id'] == $uid) {
                    if (($key = array_search($friend_id, $friendsid)) !== false) {
                        unset($friendsid[$key]);
                    }
                } else {
                    if (($key = array_search($uid, $friendsid)) !== false) {
                        unset($friendsid[$key]);
                    }
                }

                if (empty($friendsid)) { // delete
                    $this->Activity->delete($item['Activity']['id']);
                } else { // update
                    $this->Activity->id = $item['Activity']['id'];
                    $this->Activity->set(array(
                        'items' => implode(',', $friendsid),
                        'modified' => false
                    ));
                    $this->Activity->save();
                }
            }
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }
        else {
            throw new ApiBadRequestException(__('You are not a friend of this user'));
        }
    }

}
