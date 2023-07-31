<?php

App::uses('AppController', 'Controller');

class ApiAppController extends AppController {

    protected $token = null;
    protected $ownerIdResouseRequest = null;
    protected $clientIdRequest = null;
    public $check_subscription = false;
    public $check_force_login = false;
    public $errorCode = null;
    public $publicAction = array('users.getRegister', 'users.postRegister', 'users.getForgot', 'users.postForgot');

    public function beforeFilter() {
        parent::beforeFilter();
        if (!$this->isPublicAction()) {
            $this->OAuth2 = $this->Components->load('OAuth2');
            $this->OAuth2->verifyResourceRequest(array('token'));
        } else {
            $this->Auth->allow();
        }
    }

    public function throwErrorCodeException($errorCodeText) {
        $this->request->data('apiErrorCodeText', $errorCodeText);
    }

    public function isPublicAction($actions = array()) {
        $default = $this->publicAction;
        $actions = array_merge($default, $actions);
        $cAction = $this->request->params['action'];
        $cController = $this->params['controller'];

        if (in_array("$cController.$cAction", $actions)) {
            return true;
        }
        return false;
    }

    protected function _prepareDir($path) {
        $path = WWW_ROOT . $path;

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
            file_put_contents($path . DS . 'index.html', '');
        }
    }

    protected function _filterData($user = null, $filter_field = array()) {
        foreach ($filter_field as $field) {
            if (isset($user['User'][$field]))
                unset($user['User'][$field]);
        }
        unset($user['User']['moo_plugin']);
        return $user;
    }

    // check privacy
    protected function _canViewProfile($user) {
        $canView = false;
        $uid = $this->Auth->user('id');
        $cuser = $this->_getUser();

        if ($uid == $user['id'] || !empty($cuser['Role']['is_super']))
            $canView = true;
        else {
            switch ($user['privacy']) {
                case PRIVACY_EVERYONE:
                    $canView = true;
                    break;

                case PRIVACY_FRIENDS:
                    $this->loadModel('Friend');
                    $areFriends = $this->Friend->areFriends($uid, $user['id']);

                    if ($areFriends)
                        $canView = true;

                    break;

                case PRIVACY_ME:
                    if ($uid == $user['id'])
                        $canView = true;

                    break;
            }
        }

        return $canView;
    }

    public function _checkPrivacy($privacy, $owner, $areFriends = null, $redirect = true)
    {
        $uid = $this->Auth->user('id');
        if ($uid == $owner) { // owner
            return;
        }

        $viewer = MooCore::getInstance()->getViewer();
        if (!empty($viewer) && $viewer['Role']['is_admin']) {
            return true;
        }

        switch ($privacy) {
            case PRIVACY_FRIENDS:
                if (empty($areFriends)) {
                    $areFriends = false;

                    if (!empty($uid)) { //  check if user is a friend
                        $this->loadModel('Friend');
                        $areFriends = $this->Friend->areFriends($uid, $owner);
                    }
                }

                if (!$areFriends) {
                    $this->throwErrorCodeException('friends_only');
                    throw new ApiUnauthorizedException(__d('api', 'Only friends of the poster can view this item'));
                }

                break;

            case PRIVACY_ME:
                $this->throwErrorCodeException('only_me');
                throw new ApiUnauthorizedException(__d('api', 'Only the poster can view this item'));
        }
    }

    protected function _checkPermission($options = array()) {
        $viewer = MooCore::getInstance()->getViewer();
        if (!empty($viewer) && $viewer['Role']['is_admin']) {
            return true;
        }

        $cuser = $this->_getUser();
        $authorized = true;
        $hash = '';
        $return_url = '/return_url:' . base64_encode($this->request->here);

        //check normal subscription
        $this->options = $options;
        //$this->getEventManager()->dispatch(new CakeEvent('AppController.validNormalSubscription', $this));
        // check aco
        if (!empty($options['aco'])) {
            $acos = $this->_getUserRoleParams();

            if (!in_array($options['aco'], $acos)) {
                $authorized = false;
                throw new ApiUnauthorizedException(__d('api', 'Access denied'));
            }
        } else {
            // check login
            if (!$cuser) {
                $authorized = false;
                throw new ApiUnauthorizedException(__d('api', 'Please login or register'));
            } else {
                // check role
                if (!empty($options['roles']) && !in_array($cuser['role_id'], $options['roles'])) {
                    $authorized = false;
                    throw new ApiUnauthorizedException(__d('api', 'Access denied'));
                }

                // check admin
                if (!empty($options['admin']) && !$cuser['Role']['is_admin']) {
                    $authorized = false;
                    throw new ApiUnauthorizedException(__d('api', 'Access denied'));
                }

                // check super admin
                if (!empty($options['super_admin']) && !$cuser['Role']['is_super']) {
                    $authorized = false;
                    throw new ApiUnauthorizedException(__d('api', 'Access denied'));
                }


                // check approval
                if (Configure::read('core.approve_users') && !$cuser['approved']) {
                    $authorized = false;
                    throw new ApiUnauthorizedException(__d('api', 'Your account is pending approval.'));
                }

                // check confirmation
                if (Configure::read('core.email_validation') && !empty($options['confirm']) && !$cuser['confirmed']) {
                    $authorized = false;
                    throw new ApiUnauthorizedException(__d('api', 'You have not confirmed your email address! Check your email (including junk folder) and click on the validation link to validate your email address'));
                }

                // check owner
                if (!empty($options['admins']) && !in_array($cuser['id'], $options['admins']) && !$cuser['Role']['is_admin']
                ) {
                    $authorized = false;
                    throw new ApiUnauthorizedException(__d('api', 'Access denied'));
                }
            }
        }
    }

    protected function _getType($objectType) {
        switch ($objectType) {
            case 'activity' :
                $type = 'activity';
                break;
            case 'activity_comment' :
                $type = 'core_activity_comment';
                break;
            case 'comment' :
                $type = 'comment';
                break;
            case 'blog' :
                $type = 'Blog_Blog';
                break;
            case 'album' :
                $type = 'Photo_Album';
                break;
            case 'photo' :
                $type = 'Photo_Photo';
                break;
            case 'video' :
                $type = 'Video_Video';
                break;
            case 'topic' :
                $type = 'Topic_Topic';
                break;
            case 'conversation' :
                $type = APP_CONVERSATION;
                break;
            case 'user' :
                $type = 'user';
                break;
            case 'group' :
                $type = 'group_group';
                break;
            case 'event' :
                $type = 'event_event';
                break;
        }
        return $type;
    }

    protected function _checkExistence($item = null) {
        if (empty($item)) {
            throw new ApiNotFoundException(__d('api', 'items not exist.'));
        }
    }

    protected function _uploadThumbnail() {
        // save this picture to album
        $path = 'uploads' . DS . 'tmp';
        $url = 'uploads/tmp/';

        $this->_prepareDir($path);

        $allowedExtensions = MooCore::getInstance()->_getPhotoAllowedExtension();

        $maxFileSize = MooCore::getInstance()->_getMaxFileSize();

        App::import('Vendor', 'qqFileUploader');
        $uploader = new qqFileUploader($allowedExtensions, $maxFileSize);

        // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
        $result = $uploader->handleUpload($path);

        if (!empty($result['success'])) {
            App::import('Vendor', 'phpThumb', array('file' => 'phpThumb/ThumbLib.inc.php'));

            $result['thumb'] = FULL_BASE_URL . $this->request->webroot . $url . $result['filename'];
            $result['file'] = $path . DS . $result['filename'];
        }
        //echo '<pre>';print_r($result);die;
        // to pass data through iframe you will need to encode all html tags
        return $result;
    }

    protected function _checkTags($values) {
        $value = $values[key($values)];
        $value = str_replace(' ', '', $value);
        $value = str_replace(',', '', $value);
        if (!$value)
            return true;

        if (preg_match('/[#$%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/', $value)) {
            throw new ApiBadRequestException(__d('api', 'No special characters ( /,?,#,%,...) allowed in Tags'));
        }
        return true;
    }
    
    protected function _validateData($model = null)
    {
        if (!$model->validates()) {
            $errors = $model->invalidFields();
            
            $this->throwErrorCodeException('validate_failed');
            throw new ApiBadRequestException(current(current($errors)));
        }
        return true;
    }

}
