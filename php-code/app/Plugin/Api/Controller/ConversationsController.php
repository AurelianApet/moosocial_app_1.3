<?php
App::uses('ApiAppController', 'Api.Controller');
/**
 * Conversations Controller
 *
 * @property Conversation $Conversation
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ConversationsController extends ApiAppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');

}
