<?php
App::uses('CakeEventListener', 'Event');

class MooAppListener implements CakeEventListener
{

    public function implementedEvents()
    {
        return array(
            'AppController.doSetTheme' => 'doSetTheme',
            'Model.afterSave' => 'doAfterSave',
            'MooView.BeforeRenderContent' => 'doBeforeRenderContent',
            'MooView.AfterRenderContent' => 'doAfterRenderContent',
            'NotificationsController.beforeRedirectView' => 'beforeRedirectView',
        	'Plugin.Controller.UsersApi.me' => 'UsersApiMe',
        	"Controller.beforeRender" => "ControllerbeforeRender",
        );
    }
    
    public function ControllerbeforeRender($event)
    {
    	$controller = $event->subject();
    	$controller->set('app_suggest',$controller->Cookie->read('app_suggest'));
    }
    
    public function UsersApiMe($event)
    {    	
    	$api = Configure::read('MooApp');
    	$menus = array();
    	foreach ($api as $key=>$value)
    	{
    		if (strpos($key,'menu_') !== FALSE)
    		{
    			$menu = array();
    			$menu['name'] = str_replace("menu_","",$key);
    			$menu['value'] = $value;
    			$menus[] = $menu;
    		}
    	}
    	$event->result['menus'] = $menus;
		$event->result['admod_banner_id'] = $api['mooapp_google_admod_banner_id'];
		$event->result['admod_interstitial_id'] = $api['mooapp_google_admod_interstitial_id'];
		$event->result['ads_show_full'] = $api['mooapp_ads_show_full'];
		$event->result['ads_show_bottom'] = $api['mooapp_ads_show_bottom'];
    }
    
    public function beforeRedirectView($event)
    {
        $controller = $event->subject();
        $notification = $event->data['notification'];
        if ((!empty($controller->request->query['gcm_id']) || !empty($controller->request->data['gcm_id']))) {
            $gcm = !empty($controller->request->query['gcm_id'])?$controller->request->query['gcm_id']:$controller->request->data['gcm_id'];
            $this->_gcmModel()->query( "UPDATE " . $this->_gcmModel()->tablePrefix . "api_gcms SET sound=1 WHERE id = '".$gcm."'" );
        }
        if ($notification['Notification']['action'] == "friend_add") {
            $controller->redirect('/friends/ajax_requests');
        }

    }

    public function doSetTheme($event)
    {
        $e = $event->subject();


        // For android app
        if ($e->request->is('androidApp') || $e->request->is('iosApp')) {
            $e->theme = "mooApp";
        }
    }

    public function doAfterSave($event)
    {
        $api_key = Configure::read('MooApp.gcm_server_api_key');
        $mooAppNotificationModel = MooCore::getInstance()->getModel('MooApp.MooAppNotification');

        if ($api_key && Configure::read('MooApp.google_cloud_message_enable')) {
            $model = $event->subject();
            $type = ($model->plugin) ? $model->plugin . '_' : '' . get_class($model);
            $data = $event->data;
            if ($type == 'Notification') {
                if (!$data[0])
                    return true;
                
                $cron_action = array();
                $controller = new Controller();
                $controller->getEventManager()->dispatch(new CakeEvent('MooApp.Lib.MooAppListener.getListActionNotifyCronjob', $this,array(
                	'cron_action'=>&$cron_action
                )));				
                $id = $model->id;
                $notification = $model->findById($id);
                if (in_array($notification['Notification']['action'], $cron_action))
                {
                	$mooAppNotificationModel->save(array(
			        	'notification_id' => $id
			      	));
                	return;
                }
                $this->sendNotificationsToAndroid($notification);
                $this->sendNotificationsToIOS($notification);
            }
        }
    }

    public function sendNotificationsToAndroid($notification)
    {

        //$registrationIds = $this->getRegistrationIds($notification['Notification']['user_id'], 'android');
        $gcms = $this->getGCMTokens($notification['Notification']['user_id'], 'android');
        if (count($gcms)) {
            $user_sender = $this->_userModel()->findById($notification['Notification']['sender_id']);
            $params = array(                
                'notification_url' => FULL_BASE_URL . $this->_view()->request->base . "/notifications/ajax_view/" . $notification['Notification']['id'],
                'notification_id' => $notification['Notification']['id'],
                'photo_url' => $this->_view()->Moo->getImageUrl($user_sender, array('prefix' => '200_square'))
            );

            // Sound effect
            foreach ($gcms as $gcm ){
				$default = Configure::read('Config.language');
				Configure::write('Config.language', $gcm['ApiGcm']['language']);
				$params['message'] = $user_sender['User']['name'] . " " . $this->_view()->element('misc/notification_texts', array('noti' => $notification));
				Configure::write('Config.language', $default);
				
                $tmp = $params;				
                if ($gcm['ApiGcm']['sound'] == 1) {
                    $tmp['sound'] = 1;
                    $this->_gcmModel()->query( "UPDATE " . $this->_gcmModel()->tablePrefix . "api_gcms SET sound=0 WHERE id = '".$gcm['ApiGcm']['id']."'" );
                }else{
                    $tmp['sound'] = 0;
                }
				$tmp['notification_url'].='?gcm_id='.$gcm['ApiGcm']['id'];
                $this->sendNotifications(array($gcm['ApiGcm']['token']),array
                (
                    'registration_ids' => array($gcm['ApiGcm']['token']),
                    'data' => $tmp
                ));

            }

        }
    }

    public function sendNotificationsToIOS($notification)
    {
        //$registrationIds = $this->getRegistrationIds($notification['Notification']['user_id'], 'ios');
        $gcms = $this->getGCMTokens($notification['Notification']['user_id'], 'ios');
        if (count($gcms)) {
            $user_sender = $this->_userModel()->findById($notification['Notification']['sender_id']);
            $params = array(               
                'notification_url' => FULL_BASE_URL . $this->_view()->request->base . "/notifications/ajax_view/" . $notification['Notification']['id'],
                'notification_id' => $notification['Notification']['id'],
                'photo_url' => $this->_view()->Moo->getImageUrl($user_sender, array('prefix' => '200_square'))
            );

            $options = array();
            if ($notification['Notification']['plugin'])
            {
                $options = array('plugin' => $notification['Notification']['plugin']);
            }


            // Sound effect
            foreach ($gcms as $gcm ){
				$default = Configure::read('Config.language');
				Configure::write('Config.language', $gcm['ApiGcm']['language']);
				$params['message'] = $user_sender['User']['name'] . " " . $this->_view()->element('misc/notification_texts', array('noti' => $notification),$options);
				Configure::write('Config.language', $default);

                $iNotification = array(
                    'text' => $params['message'],
                    'badge' => $this->countCurrentNotification($notification['Notification']['user_id']) + 1,
                );
                if ($gcm['ApiGcm']['sound'] == 1) {
                    $notification['sound'] = 'default';
                    $this->_gcmModel()->query( "UPDATE " . $this->_gcmModel()->tablePrefix . "api_gcms SET sound=0 WHERE id = '".$gcm['ApiGcm']['id']."'" );
                }
                $tmp = $params;
                $tmp['notification_url'].='?gcm_id='.$gcm['ApiGcm']['id'];
                //CakeLog::write('GoogleClouldMessage', var_export($gcm,true));
                $this->sendNotificationsFirebase(array($gcm['ApiGcm']['token']),array
                (
                    'registration_ids' => array($gcm['ApiGcm']['token']),
                    'data' => $tmp,
                    'content_available' => true,
                    'priority' => 'high',
                    'notification' => $iNotification
                ));
            }

        }

    }
    private function countCurrentNotification($userId){
        $cuser = $this->_userModel()->findById($userId);

        $count_notification = isset($cuser['User']['notification_count']) ? $cuser['User']['notification_count'] : 0;
        $count_conversation = isset($cuser['User']['conversation_user_count']) ? $cuser['User']['conversation_user_count'] : 0;
        return  $count_notification;
    }
    private function getGCMTokens($userId,$type = 'android'){
        return $this->_gcmModel()->find('all', array(
            'conditions' => array('ApiGcm.user_id' => $userId,
                'ApiGcm.client_type' => $type
            )
        ));

    }
    private function getRegistrationIds($userId, $type = 'android')
    {
        /*
        $gcms = $this->_gcmModel()->find('all', array(
            'conditions' => array('ApiGcm.user_id' => $userId,
                'ApiGcm.client_type' => $type
            )
        ));
        */
        $gcms = $this->getGCMTokens($userId,$type);
        $registrationIds = array();
        if (count($gcms)) {

            foreach ($gcms as $gcm) {
                $registrationIds[] = $gcm['ApiGcm']['token'];
            }

        }
        return $registrationIds;
    }

    private function sendNotifications($registrationIds,$fields)
    {
        $api_key = Configure::read('MooApp.gcm_server_api_key');
        if ($api_key && Configure::read('MooApp.google_cloud_message_enable')) {
            $headers = array
            (
                'Authorization: key=' . $api_key,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);

            //CakeLog::write('GoogleClouldMessage', var_export($result,true));
            if (isset($result['results'])) {
                $gcmModel = MooCore::getInstance()->getModel('Api.ApiGcm');
                foreach ($result['results'] as $key => $item) {
                    if (isset($item['error']) && $item['error'] == "NotRegistered") {
                        $this->_gcmModel()->deleteAll(array('ApiGcm.token' => $registrationIds[$key]), false);
                    }
                }
            }
        }

    }
    private function sendNotificationsFirebase($registrationIds,$fields)
    {
        $api_key = Configure::read('MooApp.gcm_server_api_key');
        if ($api_key && Configure::read('MooApp.google_cloud_message_enable')) {
            $headers = array
            (
                'Authorization: key=' . $api_key,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);

            //CakeLog::write('GoogleClouldMessage', var_export($result,true));
            if (isset($result['results'])) {
                $gcmModel = MooCore::getInstance()->getModel('Api.ApiGcm');
                foreach ($result['results'] as $key => $item) {
                    if (isset($item['error']) && $item['error'] == "NotRegistered") {
                        $this->_gcmModel()->deleteAll(array('ApiGcm.token' => $registrationIds[$key]), false);
                    }
                }
            }
        }

    }
    private function _view()
    {
        return MooCore::getInstance()->getMooView();
    }

    private function _userModel()
    {
        return MooCore::getInstance()->getModel('User');
    }

    private function _gcmModel()
    {
        return MooCore::getInstance()->getModel('Api.ApiGcm');
    }
    
    public function isAndroid()
    {
    	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    	if(stripos($ua,'android') !== false) { // && stripos($ua,'mobile') !== false) {
    		return true;
    	}
    	return false;
    }
    
    public function isIos()
    {
    	if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
    		return true;
    	} else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') || strstr($_SERVER['HTTP_USER_AGENT'],'iphone') ) {
    		return true;
    	}
    	return false;
    }

    public function doBeforeRenderContent($event)
    {
        $v = $event->subject();
        if (($v->request->is('androidApp') || $v->request->is('iosApp')) && CakeSession::check('Message.flash')) {
            $user = MooCore::getInstance()->getViewer();
            // update info user
            $name = ($v->request->params['plugin'] ? $v->request->params['plugin'] . '_' : '') . $v->request->params['controller'] . '_' . $v->request->params['action'];
            if ($name == "users_profile" && CakeSession::check('Message.flash') == __('Your changes have been saved')) {
                $v->addInitJs('$(function() { Android.setNameMenu("' . $user['User']['name'] . '"); });');
            }
        }
        if ($v->request->is('androidApp') || $v->request->is('iosApp')) {
            $config = $v->get('mooPage');
            if (!empty($config['Page']) && $config['Page']['alias'] != 'home_index' && $config['Page']['alias'] != 'landing_index' && $config['Page']['alias'] != 'users_view') {
                $v->setIsAllowed(false);
            }
        }

        if (Configure::read('debug') == 0){
            $min="min.";
        }else{
            $min="";
        }

        $v->Helpers->MooRequirejs->addPath(array(
            "mooApp"=>$v->Helpers->MooRequirejs->assetUrlJS("MooApp.js/main.{$min}js"),
        ));
        
        if ($v->request->is('androidApp')) {
            $v->Helpers->Html->scriptBlock(
                "require(['jquery','mooApp'], function($, mooApp) {\$(document).ready(function(){ mooApp.initApp(); }); });", array(
                    'inline' => false,
                )
            );
        }
        
        $link_app = '';
        if ($this->isAndroid())
        {
        	$link_app = Configure::read("MooApp.mooapp_android_app");
        }
        elseif ($this->isIos())
        {
        	$link_app = Configure::read("MooApp.mooapp_ios_app");
        }
        
        if (Configure::read('MooApp.mooapp_enable_app_suggestion') && $link_app!= '' && !$v->viewVars['app_suggest'] && !$v->Session->read("app_suggest") && !$v->request->is('androidApp') && !$v->request->is('iosApp'))
        {
        	$html = $this->_view()->element('MooApp.popup',array('link_app'=>$link_app));
        	$output = str_replace(array("\r\n", "\r"), "\n", $html);
        	$lines = explode("\n", $output);
        	$new_lines = array();
        	
        	foreach ($lines as $i => $line) {
        		if(!empty($line))
        			$new_lines[] = trim($line);
        	}
        	$html = implode($new_lines);
            $v->Helpers->Html->scriptBlock(
                "require(['jquery','mooApp'], function($,mooApp) {\$(document).ready(function(){ $('body' ).append($('".addslashes($html)."')); mooApp.initWeb();  });});", array(
                    'inline' => false,
                )
            );
        	$v->Helpers->Html->css( array(
        			'MooApp.main'
        		),
        		array('block' => 'css')
        	);
        }
    }

    public function doAfterRenderContent($event)
    {
        $v = $event->subject();
        if ($v->request->is('androidApp') || $v->request->is('iosApp')) {
            $config = $v->get('mooPage');
            if (count($config) && $config['Page']['alias'] != 'home_index' && $config['Page']['alias'] != 'landing_index') {
                $style = $v->currentStyle();
                $content = $v->element("columns/column$style", array(
                    'north' => null,
                    'south' => null,
                    'center' => $v->fetch('center'),
                    'west' => null,
                    'east' => null,
                ));
                $v->Blocks->set('content', $content);
            }
        }
    }
}