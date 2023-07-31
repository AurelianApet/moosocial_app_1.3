<?php
App::uses('ApiAppController', 'Api.Controller');
/**
 * Videos Controller
 *
 */
class ApiGcmsController extends ApiAppController {
	public function beforeFilter(){
		parent::beforeFilter();
		if($this->OAuth2->getOwnerResourceRequest()){
			$this->user_id = $this->OAuth2->getOwnerResourceRequest();
		}
	}
	public function post(){


		if(isset($this->request->data['token']) && !empty($this->request->data['token'])){
			// Make sure unique GCM token for each user
			$this->delete();

			$client_type = 'android';
			if ($this->request->is('iosApp')) {
				$client_type = 'ios';
			}
			if ($this->request->is('androidApp')) {
				$client_type = 'android';
			}
			
			$language = (isset($this->request->data['language']) && $this->request->data['language']) ? $this->request->data['language'] : 'eng';
			$this->ApiGcm->save(
				array('ApiGcm'=>array(
					'user_id' =>$this->user_id,
					'token'=>$this->request->data['token'],
					'client_type'=>$client_type,
					'language' => $language
				))
			);
		}else{
			throw new ApiBadRequestException(__("GCM Token is emtpy"));
		}
		$this->set(array(
			'message' => 'GCM Token is added',
			'_serialize' => array('message')
		));
	}
	public function delete(){
		if(isset($this->request->data['token']) && !empty($this->request->data['token'])){
			$this->ApiGcm->deleteAll(array('ApiGcm.token'=>$this->request->data['token']),false);
		}else{
			throw new ApiBadRequestException(__("GCM Token is emtpy"));
		}

		$this->set(array(
			'message' => 'GCM Token is deleted',
			'_serialize' => array('message')
		));
	}
}
