<?php 
App::uses('MooPlugin','Lib');
class MooAppPlugin implements MooPlugin{
    public function install(){}
    public function uninstall(){}
    public function settingGuide(){}
    public function menu()
    {
        return array(
            'Settings' => array('plugin' => 'moo_app', 'controller' => 'moo_app_settings', 'action' => 'admin_index'),
        	'Logo' => array('plugin' => 'moo_app', 'controller' => 'moo_app_settings', 'action' => 'admin_logo'),
			'Message Broadcast' => array('plugin' => 'moo_app', 'controller' => 'moo_app_settings', 'action' => 'admin_broadcast'),
        );
    }
    /*
    Example for version 1.0: This function will be executed when plugin is upgraded (Optional)
    public function callback_1_0(){}
    */
}