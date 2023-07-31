<?php
App::uses('ApiAppController', 'Api.Controller');

class DocumentApiController extends  AppController{
    public $components = array('QuickSettings');
    public function index()
    {
        $this->set('title_for_layout', __('Api Document'));

        $this->QuickSettings->run($this, array("Api"), null);
    }
}
