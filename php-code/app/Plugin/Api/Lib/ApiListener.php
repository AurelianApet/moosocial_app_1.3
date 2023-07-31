<?php
App::uses('CakeEventListener', 'Event');

class ApiListener implements CakeEventListener
{

    public function implementedEvents()
    {
        return array(
            'Model.beforeDelete' => 'doAfterDelete',
        );
    }

    public function doAfterDelete($event)
    {
        $model = $event->subject();
        $type = ($model->plugin) ? $model->plugin . '_' : '' . get_class($model);
        if ($type == 'User') {
            $gcmModel = MooCore::getInstance()->getModel('Api.ApiGcm');
            $gcmModel->deleteAll(array('ApiGcm.user_id' => $model->id), false);

            $accessTokenModel = MooCore::getInstance()->getModel('OauthAccessToken');
            $accessTokenModel->deleteAll(array('OauthAccessToken.user_id' => $model->id), false);

            $refreshTokenModel = MooCore::getInstance()->getModel('OauthRefreshToken');
            $refreshTokenModel->deleteAll(array('OauthRefreshToken.user_id' => $model->id), false);
        }
    }
}