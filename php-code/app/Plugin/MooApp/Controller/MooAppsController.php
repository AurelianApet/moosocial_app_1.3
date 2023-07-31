<?php 
class MooAppsController extends MooAppAppController{
	public $check_subscription = false;
	public $check_force_login = false;
	public $autoRender = false;
    public function admin_index()
    {
    }
    
    public function index()
    {
    }
    
    public function remove()
    {
    	$this->Session->write("app_suggest",1);
    	die();
    }
    public function nothank()
    {
    	$this->Cookie->write('app_suggest', 1, true, 60 * 60 * 24 * 30);
    }
}