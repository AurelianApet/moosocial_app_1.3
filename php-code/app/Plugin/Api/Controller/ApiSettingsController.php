<?php 
class ApiSettingsController extends AppController{
    public $components = array('QuickSettings');
    public function admin_index()
    {
        $this->set('title_for_layout', __('Api Settings'));

        $this->QuickSettings->run($this, array("Api"), null);
    }
}