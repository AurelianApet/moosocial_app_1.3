<?php
App::uses('SearchController','Controller');


/**
 * Searchs Controller
 *
 */
class SearchsController extends SearchController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->OAuth2 = $this->Components->load('OAuth2');
        $this->OAuth2->verifyResourceRequest(array('token'));
        $this->loadModel('User');

    }
    public function index($keyword = null, $plugin = null){
        if($this->request->is('post')){
            if(!empty($this->request->data['keyword'])){
                $keyword = $this->request->data['keyword'];
            }

        }
        parent::index($keyword,$plugin);
    }
}
