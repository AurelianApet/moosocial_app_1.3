<?php
App::uses('ExceptionRenderer', 'Error');

class AppExceptionRenderer extends ExceptionRenderer {
    public function apiBadRequest($error) {
        $url = $this->controller->request->here();
        $code = 400;
        $errorcode = null;
        if(!empty($this->controller->request->data['apiErrorCodeText'])) $errorcode = $this->controller->request->data['apiErrorCodeText'];
        $message = $error->getMessage();
        $this->controller->response->statusCode($code);
        $this->controller->set(array(
            'errorCode' => $errorcode ? $errorcode : Inflector::slug( strtolower($message), '_' ),
            'name' => h($message),
            'message' => h($message),
            'url' => FULL_BASE_URL . $url,
            'error' => $error,
            '_serialize' => array('errorCode', 'name', 'message', 'url')
        ));
        $this->_outputMessage($this->template);
    }
    public function apiNotFound($error) {
        $url = $this->controller->request->here();
        $code = 404;
        $errorcode = null;
        if(!empty($this->controller->request->data['apiErrorCodeText'])) $errorcode = $this->controller->request->data['apiErrorCodeText'];
        $message = $error->getMessage();
        $this->controller->response->statusCode($code);
        $this->controller->set(array(
            'errorCode' => $errorcode ? $errorcode : Inflector::slug( strtolower($message), '_' ),
            'name' => h($message),
            'message' => h($message),
            'url' => FULL_BASE_URL . $url,
            'error' => $error,
            '_serialize' => array('errorCode', 'name', 'message', 'url')
        ));
        $this->_outputMessage($this->template);
    }
    public function apiUnauthorized($error) {
        $url = $this->controller->request->here();
        $code = 401;
        $errorcode = null;
        if(!empty($this->controller->request->data['apiErrorCodeText'])) $errorcode = $this->controller->request->data['apiErrorCodeText'];
        $message = $error->getMessage();
        $this->controller->response->statusCode($code);
        $this->controller->set(array(
            'errorCode' => $errorcode ? $errorcode : Inflector::slug( strtolower($message), '_' ),
            'name' => h($message),
            'message' => h($message),
            'url' => FULL_BASE_URL . $url,
            'error' => $error,
            '_serialize' => array('errorCode', 'name', 'message', 'url')
        ));
        $this->_outputMessage($this->template);
    }
}