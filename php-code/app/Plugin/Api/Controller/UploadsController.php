<?php
App::uses('UploadController','Controller');


class UploadsController extends UploadController {
    public function beforeFilter() {
        parent::beforeFilter();
        $this->autoRender = true;
        $this->OAuth2 = $this->Components->load('OAuth2');
        $this->OAuth2->verifyResourceRequest(array('token'));


    }
    public  function avatar(){
        $result = parent::avatar(0,false);
        $this->set("result",$result);





    }
}