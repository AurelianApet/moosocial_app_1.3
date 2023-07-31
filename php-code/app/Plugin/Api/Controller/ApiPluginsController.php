<?php 
class ApiPluginsController extends AppController{
    public $components = array('QuickSettings');
    public function beforeFilter(){
        parent::beforeFilter();
        $this->loadModel('Setting');
        $this->loadModel('SettingGroup');
    }
    public function admin_index()
    {
        $this->set('title_for_layout', __('Api Plugin'));

        $this->QuickSettings->run($this, array("Api"), null);

    }
    public function admin_delete()
    {

    }
    public function admin_settings(){}
}