<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Event Controller
 *
 */
class EventController extends ApiAppController {

    /**
     * Scaffold
     *
     * @var mixed
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Event.Event');
        $this->loadModel('Event.EventRsvp');
    }

    public function browse() {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');
        $role_id = $this->_getUserRoleId();
        $param = '';
        if (!isset($this->request->params['type']))
            $type = 'search';
        else
            $type = $this->request->params['type'];

        if ($type == 'myupcomming')
            $type = 'my';
        if ($type == 'friendattend')
            $type = 'friends';

        if ($type == 'search') {
            if (isset($this->request->data['keyword'])) {
                $param = urldecode($this->request->data['keyword']);
            }
        }
        if (!empty($this->request->params['category_id'])) {
            $type = 'category';
            $param = $this->request->params['category_id'];
        }
        switch ($type) {
            case 'my':
            case 'mypast':
            case 'friends':
                $this->_checkPermission();
                $events = $this->EventRsvp->getEvents($type, $uid, $page, $role_id);
                break;
            default: // all, past, category

                $eventId = $this->EventRsvp->findAllByUserId($this->Auth->user('id'), array('event_id'));
                if (!empty($eventId)) {
                    $eventId = implode(',', Hash::extract($eventId, '{n}.EventRsvp.event_id'));
                } else
                    $eventId = '';

                $events = $this->Event->getEvents($type, $param, $page, $role_id, $eventId);
                $events = Hash::sort($events, '{n}.Event.from', ' asc');
        }

        if (empty($events)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $this->set('events', $events);
    }

    // GET event activity
    public function eventActivity() {
        $id = $this->request->params['event_id'];
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();
        $event = $this->Event->findById($id);
        $eventFeeds = '';
        if (empty($event)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $role_id = $this->_getUserRoleId();
        $this->_checkPermission(array('aco' => 'event_view'));
        $this->_checkPermission(array('user_block' => $event['Event']['user_id']));

        $my_rsvp = Cache::read('eventrsvp.myrsvp.' . $uid . '.event.' . $id, 'event');
        if (empty($my_rsvp)) {
            $my_rsvp = $this->EventRsvp->getMyRsvp($uid, $id);
            Cache::write('eventrsvp.myrsvp.' . $uid . '.event.' . $id, $my_rsvp, 'event');
        }

        if (empty($my_rsvp) && $event['Event']['type'] == PRIVACY_PRIVATE && $role_id != ROLE_ADMIN && ( $cuser['id'] != $event['User']['id'] )) {
            $this->throwErrorCodeException('private_event');
            throw new ApiBadRequestException(__d('api', 'This is private event . You do not have permission to view'));
        } else {
            MooCore::getInstance()->setSubject($event);
            $eventFeeds = $this->Feeds->get();
        }

        if (empty($eventFeeds)) {
            throw new ApiNotFoundException(__('There are no new feeds to view at this time.'));
        }
        $this->set('datas', $eventFeeds);
    }

    // GET event rsvp
    public function rsvp() {
        $cuser = $this->_getUser();
        $uid = $this->Auth->user('id');
        $id = $this->request->params['event_id'];
        $type = $this->request->params['type'];
        if ($type == 'wait')
            $rsvp_type = 0;
        if ($type == 'attend')
            $rsvp_type = 1;
        if ($type == 'maybe')
            $rsvp_type = 3;
        if ($type == 'no')
            $rsvp_type = 2;
        $event = $this->Event->findById($id);
        if (empty($event)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $role_id = $this->_getUserRoleId();
        $this->_checkPermission(array('aco' => 'event_view'));
        $this->_checkPermission(array('user_block' => $event['Event']['user_id']));

        $my_rsvp = Cache::read('eventrsvp.myrsvp.' . $uid . '.event.' . $id, 'event');
        if (empty($my_rsvp)) {
            $my_rsvp = $this->EventRsvp->getMyRsvp($uid, $id);
            Cache::write('eventrsvp.myrsvp.' . $uid . '.event.' . $id, $my_rsvp, 'event');
        }

        if (empty($my_rsvp) && $event['Event']['type'] == PRIVACY_PRIVATE && $role_id != ROLE_ADMIN && ( $cuser['id'] != $event['User']['id'] )) {
            $this->throwErrorCodeException('private_event');
            throw new ApiBadRequestException(__d('api', 'This is private event . You do not have permission to view'));
        }

        $users = $this->EventRsvp->getRsvp($id, $rsvp_type, null);
        if (empty($users)) {
            throw new ApiNotFoundException(__d('api', 'User not found'));
        }
        $this->set('users', $users);
    }

    // GET event detail by id
    public function view() {
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();
        $id = $this->request->params['event_id'];
        $event = $this->Event->findById($id);
        if (empty($event)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $role_id = $this->_getUserRoleId();
        $this->_checkPermission(array('aco' => 'event_view'));
        $this->_checkPermission(array('user_block' => $event['Event']['user_id']));
        $my_rsvp = Cache::read('eventrsvp.myrsvp.' . $uid . '.event.' . $id, 'event');
        if (empty($my_rsvp)) {
            $my_rsvp = $this->EventRsvp->getMyRsvp($uid, $id);
            Cache::write('eventrsvp.myrsvp.' . $uid . '.event.' . $id, $my_rsvp, 'event');
        }

        if (empty($my_rsvp) && $event['Event']['type'] == PRIVACY_PRIVATE && $role_id != ROLE_ADMIN && ( $cuser['id'] != $event['User']['id'] )) {
            $this->throwErrorCodeException('private_event');
            throw new ApiBadRequestException(__d('api', 'This is private event . You do not have permission to view'));
        }
        $this->set('event', $event);
    }

    // SEnd invite to attend event .
    public function sendInvite() {
        $cuser = $this->_getUser();
        $uid = $this->Auth->user('id');
        $id = $this->request->data['event_id'];
        $event = $this->Event->findById($id);
        if (empty($event)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $role_id = $this->_getUserRoleId();
        $this->_checkPermission(array('aco' => 'event_view'));
        $this->_checkPermission(array('user_block' => $event['Event']['user_id']));
        $my_rsvp = Cache::read('eventrsvp.myrsvp.' . $uid . '.event.' . $id, 'event');
        if (empty($my_rsvp)) {
            $my_rsvp = $this->EventRsvp->getMyRsvp($uid, $id);
            Cache::write('eventrsvp.myrsvp.' . $uid . '.event.' . $id, $my_rsvp, 'event');
        }

        if (empty($my_rsvp) && $event['Event']['type'] == PRIVACY_PRIVATE && $role_id != ROLE_ADMIN && ( $cuser['id'] != $event['User']['id'] )) {
            $this->throwErrorCodeException('private_event');
            throw new ApiBadRequestException(__d('api', 'This is private event . You do not have permission to view'));
        }


        if (!empty($this->request->data['friends']) || !empty($this->request->data['emails'])) {

            if (!empty($this->request->data['friends'])) {
                $this->loadModel('Event.EventRsvp');
                $data = array();
                $friends = explode(',', $this->request->data['friends']);
                $rsvp_list = $this->EventRsvp->getRsvpList($id);

                foreach ($friends as $friend_id)
                    if (!in_array($friend_id, $rsvp_list))
                        $data[] = array('event_id' => $this->request->data['event_id'], 'user_id' => $friend_id);

                if (!empty($data)) {
                    $this->EventRsvp->saveAll($data);

                    $cakeEvent = new CakeEvent('Plugin.Controller.Event.sentInvite', $this, array('friends' => $friends, 'cuser' => $cuser, 'event_id' => $this->request->data['event_id'], 'event' => $event));
                    $this->getEventManager()->dispatch($cakeEvent);
                }
            }

            if (!empty($this->request->data['emails'])) {
                $emails = explode(',', $this->request->data['emails']);

                $i = 1;
                foreach ($emails as $email) {
                    if ($i <= 10) {
                        if (Validation::email(trim($email))) {
                            $ssl_mode = Configure::read('core.ssl_mode');
                            $http = (!empty($ssl_mode)) ? 'https' : 'http';
                            $this->MooMail->send(trim($email), 'event_invite_none_member', array(
                                'event_title' => $event['Event']['moo_title'],
                                'event_link' => $http . '://' . $_SERVER['SERVER_NAME'] . $event['Event']['moo_href'],
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

    // SEnd rsvp
    public function sendRSVP() {

        $uid = $this->Auth->user('id');
        $this->request->data['user_id'] = $uid;
        $id = $this->request->data['event_id'];
        $event = $this->Event->findById($id);
        if (empty($event)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $this->_checkPermission(array('user_block' => $event['Event']['user_id']));
        if (empty($this->request->data['rsvp']) && !isset($this->request->data['rsvp'])) {
            throw new ApiBadRequestException(__d('api', 'Please add your RSVP'));
        }
        // find existing and update if necessary
        $my_rsvp = $this->EventRsvp->getMyRsvp($uid, $this->request->data['event_id']);

        // check if user was invited
        if (empty($my_rsvp) && $event['Event']['type'] == PRIVACY_PRIVATE) {
            $this->throwErrorCodeException('private_event');
            throw new ApiBadRequestException(__d('api', 'This is private event . You do not have permission to view'));
        }

        if (!empty($my_rsvp)) {
            $this->EventRsvp->id = $my_rsvp['EventRsvp']['id'];

            // user changed rsvp from attending to something else
            if ($my_rsvp['Event']['type'] != PRIVACY_PRIVATE && $my_rsvp['EventRsvp']['rsvp'] == RSVP_ATTENDING && isset($this->request->data['rsvp']) && $this->request->data['rsvp'] != RSVP_ATTENDING) {
                $cakeEvent = new CakeEvent('Plugin.Controller.Event.changeRsvpFromAttending', $this, array('uid' => $uid, 'event_id' => $this->request->data['event_id']));
                $this->getEventManager()->dispatch($cakeEvent);
            }
        } else {
            // first time rsvp
            if ($event['Event']['type'] == PRIVACY_PUBLIC && isset($this->request->data['rsvp']) && $this->request->data['rsvp'] == RSVP_ATTENDING) { // attending
                $cakeEvent = new CakeEvent('Plugin.Controller.Event.firstTimeRsvp', $this, array('uid' => $uid, 'event' => $event));
                $this->getEventManager()->dispatch($cakeEvent);
            }
        }
        if ($this->EventRsvp->save($this->request->data)) {
            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->EventRsvp->id,
                '_serialize' => array('message', 'id'),
            ));
        }
    }

    // create and edit event
    public function save() {

        $uid = $this->Auth->user('id');
        $this->request->data['type'] = isset($this->request->data['privacy']) ? $this->request->data['privacy'] : 1;
        if (isset($_FILES['qqfile'])) {
            $upload = $this->_uploadThumbnail();
            $this->request->data['photo'] = $upload['file'];
        }
        if (!isset($this->request->data['title']))
            throw new ApiBadRequestException(__d('api', 'Event title is missing.'));
        if (!isset($this->request->data['description']))
            throw new ApiBadRequestException(__d('api', 'Event description is missing.'));
        if (!isset($this->request->data['category_id']))
            throw new ApiBadRequestException(__d('api', 'Event category is missing.'));
        if (!isset($this->request->data['from']))
            throw new ApiBadRequestException(__d('api', 'Event date start is missing.'));
        if (!isset($this->request->data['to']))
            throw new ApiBadRequestException(__d('api', 'Event date end is missing.'));

        if (!empty($this->request->data['id'])) {
            // check edit permission
            $event = $this->Event->findById($this->request->data['id']);
            if (empty($event)) {
                throw new ApiNotFoundException(__d('api', 'Event not found'));
            }
            $this->_checkPermission(array('admins' => array($event['User']['id'])));
            $this->Event->id = $this->request->data['id'];
        } else
            $this->request->data['user_id'] = $uid;

        $this->Event->set($this->request->data);

        $this->_validateData($this->Event);

        //echo '<pre>';print_r($this->request->data); die;

        if ($this->Event->save()) { // successfully saved	
            //update field 'type' again because conflict with upload behavior
            $this->Event->id;
            $this->Event->save(array('type' => $this->request->data['type'], 'id' => $this->Event->id));

            if (empty($this->request->data['id'])) { // add event
                // rsvp the creator
                $this->loadModel('Event.EventRsvp');
                $this->EventRsvp->save(array('user_id' => $uid, 'event_id' => $this->Event->id, 'rsvp' => RSVP_ATTENDING));

                $event = new CakeEvent('Plugin.Controller.Event.afterSaveEvent', $this, array(
                    'uid' => $uid,
                    'id' => $this->Event->id,
                    'type' => $this->request->data['type']));

                $this->getEventManager()->dispatch($event);
            }

            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->Event->id,
                '_serialize' => array('message', 'id'),
            ));
        }
    }

    // Delete event
    public function delete() {
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();
        $role_id = $this->_getUserRoleId();
        $id = $this->request->params['event_id'];
        $event = $this->Event->findById($id);
        if (empty($event)) {
            throw new ApiNotFoundException(__d('api', 'Event not found'));
        }
        $my_rsvp = Cache::read('eventrsvp.myrsvp.' . $uid . '.event.' . $id, 'event');
        if (empty($my_rsvp) && $event['Event']['type'] == PRIVACY_PRIVATE && $role_id != ROLE_ADMIN && ( $cuser['id'] != $event['User']['id'] )) {
            $this->throwErrorCodeException('private_event');
            throw new ApiBadRequestException(__d('api', 'This is private event . You do not have permission to view'));
        }
        $this->_checkPermission(array('admins' => array($event['User']['id'])));

        $this->Event->deleteEvent($event);

        $cakeEvent = new CakeEvent('Plugin.Controller.Event.afterDeleteEvent', $this, array('item' => $event));
        $this->getEventManager()->dispatch($cakeEvent);
        $this->set(array(
            'message' => __('Event has been deleted'),
            '_serialize' => array('message'),
        ));
    }

}
