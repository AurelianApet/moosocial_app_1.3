<?php
App::uses('NotificationsController', 'Controller');

/**
 * Notifications Controller
 *
 */
class NotificationController extends NotificationsController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->OAuth2 = $this->Components->load('OAuth2');
        $this->OAuth2->verifyResourceRequest(array('token'));
        $this->loadModel('User');
    }

    public function refresh()
    {

        $cuser = $this->_getUser();
        $this->set(array(
            'count_notification' => isset($cuser['notification_count']) ? $cuser['notification_count'] : "0",
            'count_conversation' => isset($cuser['conversation_user_count']) ? $cuser['conversation_user_count'] : "0",
            '_serialize' => array('count_notification', 'count_conversation')
        ));

    }

    public function show()
    {

        $this->_checkPermission();
        $uid = $this->Auth->user('id');

        $this->Notification->bindModel(
            array('belongsTo' => array(
                'Sender' => array(
                    'className' => 'User',
                    'foreignKey' => 'sender_id'
                )
            )
            )
        );
        
    	$page = 1;
        if ((!empty($this->request->query['page']) || !empty($this->request->data['page']))) {
            $page = !empty($this->request->query['page'])?$this->request->query['page']:$this->request->data['page'];
        }
        
        $notifications = $this->Notification->find('all',array(
    		'conditions' => array('Notification.user_id'=>$uid),
    		'limit' => RESULTS_LIMIT,
    		'page' => $page,
    	));

    	if (!count($notifications))
        {
        	throw new ApiNotFoundException(__d('api', 'Notification not found'));
        }

		$this->set('notifications', $notifications);
    }
    public function clear()
    {
        $this->ajax_clear();
        $this->autoRender = true;
        $this->set(array(
            'message' => "Done",
            '_serialize' => array('message')
        ));

    }
    public function remove(){
        $data = $this->request->data;
        if (empty($data['id'])) {
            throw new ApiBadRequestException('Missing parameter : id is REQUIRED');
        }
        $this->ajax_remove($data['id']);
        $this->autoRender = true;
        $this->set(array(
            'message' => "Done",
            '_serialize' => array('message')
        ));
    }
    public function update($id){


        $status = isset($this->request->data['unread']) ? $this->request->data['unread'] : 0;
        $viewer_id = MooCore::getInstance()->getViewer(true);
        $this->loadModel('Notification');

        $notifications = $this->Notification->find('all', array('conditions' => array('Notification.id' => $id, 'Notification.user_id' => $viewer_id)));
        foreach ($notifications as $item){
            $this->Notification->clear();
            $this->Notification->id = $item['Notification']['id'];
            $this->Notification->save(array('read' => $status));
        }
        $this->set(array(
            'message' => "Done",
            '_serialize' => array('message')
        ));

    }
}
