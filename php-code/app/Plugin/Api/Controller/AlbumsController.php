<?php
App::uses('ApiAppController', 'Api.Controller');
/**
 * Albums Controller
 *
 * @property Album $Album
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class AlbumsController extends ApiAppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');

}
