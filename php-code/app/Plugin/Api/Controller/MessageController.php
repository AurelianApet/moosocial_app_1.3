<?php
App::uses('ConversationsController','Controller');
class MessageController extends ConversationsController {
    public function beforeFilter() {
        parent::beforeFilter();
        $this->OAuth2 = $this->Components->load('OAuth2');
        $this->OAuth2->verifyResourceRequest(array('token'));
        $this->loadModel('User');
        $this->loadModel('Conversation');
    }
    public function show() {
        $this->_checkPermission();
        $uid = $this->Auth->user('id');

        //$page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;
        $page = (!empty($this->request->query['page'])) ? $this->request->query['page'] : 1;
        $this->loadModel('ConversationUser');

        $this->Conversation->unbindModel(
            array('belongsTo' => array('User'))
        );

        $this->Conversation->unbindModel(
            array('hasMany' => array('Comment'))
        );

        $this->ConversationUser->recursive = 3;
        $filter = "first";
        if ((!empty($this->request->query['filter']) || !empty($this->request->data['filter']))) {
            $filter = !empty($this->request->query['filter'])?$this->request->query['filter']:$this->request->data['filter'];
        }

        $conversations = $this->ConversationUser->find('all', array('conditions' => array('ConversationUser.user_id' => $uid),
            'limit' => RESULTS_LIMIT,
            'page' => $page,
            'order' => 'modified desc'
        ));
        
        if (!count($conversations))
        {
        	throw new ApiNotFoundException(__d('api', 'Message not found'));
        }
        
        switch ($filter) {
            case "first":
                $this->set('notifications', array_slice($conversations, 0, 10));
                break;
            case "more":
                $this->set('notifications', array_slice($conversations, 9, count($conversations) - 1));
                break;
            case "all":
                $this->set('conversations', $conversations);
                break;
            default:

        }

    }

    public function update($id){


        $status = isset($this->request->data['unread']) ? $this->request->data['unread'] : 0;
        $uid = $this->Auth->user('id');

        $this->loadModel('ConversationUser');
        $conversations = $this->ConversationUser->find('first', array('conditions' => array('ConversationUser.user_id' => $uid, 'ConversationUser.conversation_id' => $id)));
        if(!empty($conversations)) {
            $this->ConversationUser->id = $conversations['ConversationUser']['id'];
            $this->ConversationUser->save( array( 'unread' => $status ) );
        }

        $this->set(array(
            'message' => "Done",
            '_serialize' => array('message')
        ));

    }
}