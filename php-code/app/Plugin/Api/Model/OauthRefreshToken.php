<?php
App::uses('ApiAppModel', 'Api.Model');
/**
 * OauthRefreshToken Model
 *
 * @property Client $Client
 * @property User $User
 */
class OauthRefreshToken extends ApiAppModel {

/**
 * Primary key field
 *
 * @var string
 */
	public $primaryKey = 'refresh_token';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'refresh_token';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'refresh_token' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
            /*
		'client_id' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
             */
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
