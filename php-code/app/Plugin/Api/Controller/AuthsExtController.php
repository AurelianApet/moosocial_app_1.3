<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Auths Controller
 *
 */
class AuthsExtController extends ApiAppController {

    public function beforeFilter() {
        $this->loadModel('User');
        //debug($this->verifyResourceRequest(array('token')));
        parent::beforeFilter();
    }
    public function token(){

        if ($this->request->is('api')) {
            $this->OAuth2->token();
        }
    }
    public function token1() {
        
        if (!$this->isRefeshTokenRequest()) {

            if ($this->validateResourceOwnerPasswordCredentials()) {
                /*
                 * http://tools.ietf.org/html/rfc6749#section-5.1
                 * Successful Response
                 * 
                 * We are using Bearer token type to make a protected resource request 
                 * http://tools.ietf.org/html/rfc6750#page-10
                 */

                $this->sendReponse($this->createToken());
               
            } else {
                /*
                 * http://tools.ietf.org/html/rfc6749#section-5.2
                 * Error Response
                 */
                throw new ApiBadRequestException(__('Parameter error : username or password is invalid'));
            }
        } else {
            if ($this->validateRefreshingToken()) {
                $token = $this->getRefreshTokenData();
                if (is_null($token)) {
                    throw new ApiBadRequestException(__('The refresh token provided is invalid'));
                }
                
                $this->sendReponse($this->createToken());
            }
        }
        
    }

    /**
     * http://tools.ietf.org/html/rfc6749#section-3.2
     * The client MUST use the HTTP "POST" method when making access token requests.
     *
     *
     * http://tools.ietf.org/html/rfc6749#section-4.3.2
     */
    private function validateTokentRequest() {
        if (!$this->request->is('post')) {
            throw new ApiBadRequestException(__('The client MUST use the HTTP "POST" method when making access token requests.'));
        }

        $data = $this->request->data;
        if (empty($data['grant_type'])) {
            throw new ApiBadRequestException(__('grant_type is REQUIRED'));
        }

        if (($data['grant_type'] != "password") && ($data['grant_type'] != "refresh_token")) {
            throw new ApiBadRequestException(__('grant_type\'s value MUST be set to "password" or "refresh_token" '));
        }


        return true;
    }

    private function validateResourceOwnerPasswordCredentials() {
        $data = $this->request->data;
        if (empty($data['username'])) {
            throw new ApiBadRequestException('Missing parameter : username is REQUIRED');
        }

        if (empty($data['password'])) {
            throw new ApiBadRequestException('Missing parameter : password is REQUIRED');
        }
        // Todo: Verify username and password 
        $user = $this->User->findByEmail(trim($data['username']));
        
        if (empty($user)) {
            return false;
        }
        // Automaticaly detecting OwnerIdRewsoudRequest
        $this->setOwnerIdRewsoudRequest($user['User']['id']);
        $passwordHasher = new MooPasswordHasher();
        return $user['User']['password'] == $passwordHasher->hash($data['password'],$user['User']['salt']);
    }

    private function validateRefreshingToken() {
         $data = $this->request->data;
        if (empty($data['refresh_token'])) {
            throw new ApiBadRequestException('Missing parameter : refresh_token is REQUIRED');
        }
        return true;
    }

    private function generateToken($type = null) {
        if ($type == "refresh") {
            
        }
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(20, MCRYPT_DEV_URANDOM);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        // Last resort which you probably should just get rid of:
        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        return substr(hash('sha512', $randomData), 0, 40);
    }

    private function isRefeshTokenRequest() {
        if ($this->validateTokentRequest()) {

            if (isset($this->request->data['grant_type']) && $this->request->data['grant_type'] == "refresh_token") {
                return true;
            }
        }
        return false;
    }

    private function createToken() {
        $config = array(
            'token_type' => 'bearer',
            'access_lifetime' => 5184000,
            'refresh_token_lifetime' => 31104000,
        );

        $token = array(
            'access_token' => $this->generateToken(),
            'token_type' => $config['token_type'],
            'expires_in' => $config['access_lifetime'],
            'refresh_token' => $this->generateToken("refresh"),
            'scope' => null,
        );

        $expires = date('Y-m-d H:i:s', time() + $config['access_lifetime']);
        $accessTokenSaved = $this->OauthAccessToken->save(array('OauthAccessToken' => array(
                'client_id' => null,
                'expires' => $expires,
                'user_id' => $this->ownerIdResouseRequest,
                'scope' => null,
                'access_token' => $token["access_token"],
        )));
        $expires = date('Y-m-d H:i:s', time() + $config['refresh_token_lifetime']);
        $RefressTokenSaved = $this->OauthRefreshToken->save(array('OauthRefreshToken' => array(
                'client_id' => null,
                'expires' => $expires,
                'user_id' => $this->ownerIdResouseRequest,
                'scope' => null,
                'refresh_token' => $token["refresh_token"],
        )));
        return ($accessTokenSaved && $RefressTokenSaved) ? $token : false;
    }
    private function sendReponse($token) {
        $this->set(array(
                    'access_token' => $token['access_token'],
                    'token_type' => $token['token_type'],
                    'expires_in' => $token['expires_in'],
                    'refresh_token' => $token['refresh_token'],
                    'scope' => $token['scope'],
                    '_serialize' => array('access_token','token_type','expires_in','refresh_token','scope')
                ));
    }

}
