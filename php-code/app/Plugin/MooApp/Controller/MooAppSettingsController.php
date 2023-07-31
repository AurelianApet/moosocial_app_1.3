<?php 
class MooAppSettingsController extends MooAppAppController{
	public $components = array('QuickSettings');
    public function beforeFilter(){
        parent::beforeFilter();
        $this->loadModel('Setting');
        $this->loadModel('SettingGroup');
    }
    public function admin_index()
    {
        $this->set('title_for_layout', __('mooApp Plugin'));

        $this->QuickSettings->run($this, array("MooApp"), null);

    }
    
    
   	public function admin_logo()
   	{  		
   		if ( isset($_FILES['Filedata']) && is_uploaded_file($_FILES['Filedata']['tmp_name']) )
   		{
   			App::import('Vendor', 'secureFileUpload');
   			$secureUpload = new SecureImageUpload(
   					array(
   							'fileKeyName' =>  'Filedata',
   							'path'=>WWW_ROOT.'uploads' . DS,
   							'whitelist'=>array('extensions'=>array('jpg','jpeg','gif','png'),'type'=>array('image/png', 'image/jpeg', 'image/gif'),),
   							'maxSize' => 2*1024*1024, // 2Mb
   							'width'=> 75,
   							'height'=> 75,
   							'scaleUp'=>true,
   					)
   					);
   			if($secureUpload->execute()){
   				$this->Setting->updateAll( array( 'Setting.value_actual' => "'". 'uploads/'. $secureUpload->getFileName() ."'" ), array( 'Setting.name' => 'mooapp_logo_popup' ) );
   				$this->Session->setFlash(__('Successfully saved.'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in' ));
   				$this->redirect( $this->referer() );
   			}else{
   				$this->Session->setFlash(__($secureUpload->getMessage()), 'default', array('class' => 'Metronic-alerts alert alert-danger fade in' ));
   				$this->redirect( $this->referer() );
   			}
   		}
   	}
	
	public function admin_broadcast()
	{
	}
	
	public function admin_ajax_broadcast()
	{
		$message = $this->request->data['message'];
		$link = $this->request->data['link'];
		
		$api_key = Configure::read('MooApp.gcm_server_api_key');
		$fields = array(
			"to" => "/topics/global",
			'priority' => 'high',
			"notification" => array(
					"text"=> $message,

			),
			"data" => array(
				"message"=>$message,
				"url"=> $link,
				"notification_url"=>$link,
				"type"=>"global"
			)
		);
		
        if ($api_key) {
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
        }
		$this->Session->setFlash(__('Message has been successfully sent'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
		die();
	}
    
    public function admin_delete()
    {

    }
    public function admin_settings(){}
}