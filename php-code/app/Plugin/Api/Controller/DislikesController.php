<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Likes Controller
 *
 */
class DislikesController extends ApiAppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Activity');
        $this->loadModel('Like');
    }


    public function updatePhotoLike($activity = null, $thumb = 1, $deleteLike = false) {
        $uid = $this->Auth->user('id');
        if (!empty($activity)) {
            $item_type = $activity['Activity']['item_type'];

            if (
                    ($item_type == 'Photo_Album' && $activity['Activity']['action'] == 'wall_post') || ($item_type == 'Photo_Photo' && $activity['Activity']['action'] == 'photos_add')
            ) {
                $photo_id = explode(',', $activity['Activity']['items']);
                if (count($photo_id) == 1) {
                    $this->loadModel('Photo');
                    $data_like = array('type' => 'Photo_Photo', 'target_id' => $photo_id[0], 'user_id' => $uid, 'thumb_up' => $thumb);

                    $like_id = false;
                    $like = $this->Like->findByTargetIdAndType($photo_id[0], 'Photo_Photo');
                    if (!empty($like))
                        $like_id = $like['Like']['id'];

                    if ($deleteLike && $like_id) {
                        $this->Like->delete($like_id);
                    } else {
                        $this->Like->create();
                        if ($like_id)
                            $this->Like->id = $like_id;
                        $this->Like->save($data_like);
                    }

                    $this->Photo->updateCounter($photo_id[0], 'like_count', array('Like.type' => 'Photo_Photo', 'Like.target_id' => $photo_id[0], 'Like.thumb_up' => 1), 'Like');
                    $this->Photo->updateCounter($photo_id[0], 'dislike_count', array('Like.type' => 'Photo_Photo', 'Like.target_id' => $photo_id[0], 'Like.thumb_up' => 0), 'Like');
                }
            }
        }
    }

    // POST unlike an item
    public function delete() {
        $objectType = $this->request->params['object'];
        $id = $this->request->data['item_id'];

        $type = $this->_getType($objectType);
        $thumb_up = 0;

        $id = intval($id);
        $this->autoRender = false;
        $this->_checkPermission(array('confirm' => true));

        $uid = $this->Auth->user('id');

        if ($type == 'activity') {
            $activity = $this->Activity->findById($id);
        }

        list($plugin, $model) = mooPluginSplit($type);

        if ($plugin)
            $this->loadModel($plugin . '.' . $model);
        else
            $this->loadModel($model);

        $item = $this->$model->findById($id);
        if (empty($item)) {
            throw new ApiNotFoundException(__d('api', 'items not exist.'));
        }

        // clear cache item
        switch ($type) {
            case APP_PHOTO:
                Cache::delete('photo.photo_view_' . $id, 'photo');
                break;
            default:
                break;
        }

        // check to see if user already liked this item
        $like = $this->Like->getUserLike($id, $uid, $type);
        $this->$model->id = $id;
        if (!empty($like) && $like['Like']['thumb_up'] == 0) { // user already liked this item
            $this->Like->delete($like['Like']['id']);
            if (!empty($activity)) {
                $this->updatePhotoLike($activity, $thumb_up, true);
            }
            $this->$model->updateCounter($id, 'dislike_count', array('Like.type' => $type, 'Like.target_id' => $id, 'Like.thumb_up' => 0), 'Like');
        } else {
            throw new ApiBadRequestException(__d('api', 'This item is not disliked yet '));
        }

        $item = $this->$model->findById($id);
        $re = array('like_count' => $item[$model]['like_count'], 'dislike_count' => $item[$model]['dislike_count']);

        $like_current = $this->Like->getUserLike($id, $uid, $type);
        if ($type == 'Photo_Photo') {
            $this->loadModel('Activity');
            $activity = $this->Activity->find('first', array(
                'conditions' => array(
                    'OR' => array(
                        array('item_type' => 'Photo_Album', 'action' => 'wall_post', 'items' => $id),
                        array('item_type' => 'Photo_Photo', 'action' => 'photos_add', 'items' => $id),
                    )
                )
            ));
            if ($activity) {
                $this->Like->deleteAll(array('Like.type' => 'activity', 'Like.user_id' => $uid, 'Like.target_id' => $activity['Activity']['id']), false);

                if ($like_current) {
                    $likeModel = MooCore::getInstance()->getModel('Like');
                    $likeModel->Behaviors->detach('Notification');
                    $likeModel->save(array(
                        'type' => 'activity',
                        'user_id' => $uid,
                        'target_id' => $activity['Activity']['id'],
                        'thumb_up' => $like_current['Like']['thumb_up']
                    ));
                }

                $this->Activity->updateCounter($activity['Activity']['id'], 'like_count', array('Like.type' => 'activity', 'Like.target_id' => $activity['Activity']['id'], 'Like.thumb_up' => 1), 'Like');
                $this->Activity->updateCounter($activity['Activity']['id'], 'dislike_count', array('Like.type' => 'activity', 'Like.target_id' => $activity['Activity']['id'], 'Like.thumb_up' => 0), 'Like');
            }
        }

        $cakeEvent = new CakeEvent('Controller.Like.afterLike', $this, array('aLike' => $this->Like->read()));
        $this->getEventManager()->dispatch($cakeEvent);

        $response = array('success' => 'true');
        echo json_encode($response);
    }

    // POST like an item
    public function add() {
        $objectType = $this->request->params['object'];
        $id = $this->request->data['item_id'];

        $type = $this->_getType($objectType);
        $thumb_up = 0;

        $id = intval($id);
        $this->autoRender = false;
        $this->_checkPermission(array('confirm' => true));

        $uid = $this->Auth->user('id');

        if ($type == 'activity') {
            $activity = $this->Activity->findById($id);
        }

        list($plugin, $model) = mooPluginSplit($type);

        if ($plugin)
            $this->loadModel($plugin . '.' . $model);
        else
            $this->loadModel($model);

        $item = $this->$model->findById($id);
        if (empty($item)) {
            throw new ApiNotFoundException(__d('api', 'items not exist.'));
        }

        // clear cache item
        switch ($type) {
            case APP_PHOTO:
                Cache::delete('photo.photo_view_' . $id, 'photo');
                break;
            default:
                break;
        }

        // check to see if user already liked this item
        $like = $this->Like->getUserLike($id, $uid, $type);
        $this->$model->id = $id;
        if (!empty($like)) { // user already liked this item
            if ($like['Like']['thumb_up'] != 0) {
                $this->Like->id = $like['Like']['id'];
                $this->Like->save(array('thumb_up' => $thumb_up));

                if ($thumb_up) { // user thumbed down before
                    $this->$model->updateCounter($id, 'like_count', array('Like.type' => $type, 'Like.target_id' => $id, 'Like.thumb_up' => 1), 'Like');
                    $this->$model->updateCounter($id, 'dislike_count', array('Like.type' => $type, 'Like.target_id' => $id, 'Like.thumb_up' => 0), 'Like');

                    if (!empty($activity))
                        $this->updatePhotoLike($activity, $thumb_up);
                }
                else {
                    $this->$model->updateCounter($id, 'like_count', array('Like.type' => $type, 'Like.target_id' => $id, 'Like.thumb_up' => 1), 'Like');
                    $this->$model->updateCounter($id, 'dislike_count', array('Like.type' => $type, 'Like.target_id' => $id, 'Like.thumb_up' => 0), 'Like');
                    if (!empty($activity))
                        $this->updatePhotoLike($activity, $thumb_up);
                }
            } else
                throw new ApiBadRequestException(__d('api', 'Item already disliked'));
        } else {
            $data = array('type' => $type, 'target_id' => $id, 'user_id' => $uid, 'thumb_up' => $thumb_up);
            $this->Like->save($data);

            $this->$model->updateCounter($id, 'dislike_count', array('Like.type' => $type, 'Like.target_id' => $id, 'Like.thumb_up' => 0), 'Like');

            //user like activity photo with 1 photo
            if (!empty($activity))
                $this->updatePhotoLike($activity, $thumb_up);

            // do not send notification when user like comment
            if (!in_array($type, array('core_activity_comment', 'comment'))) {
                // send notification to author
                if ($uid != $item['User']['id']) {
                    switch ($type) {
                        case 'Photo_Photo':
                            $action = 'photo_like';
                            $params = '';
                            break;

                        case 'activity':
                            $action = 'activity_like';
                            $params = '';
                            break;

                        case 'core_activity_comment':
                            $action = 'item_like';
                            $params = '';
                            break;

                        default:
                            $action = 'item_like';
                            $params = isset($item[$model]['title']) ? h($item[$model]['title']) : '';

                            if (empty($params)) {
                                $params = isset($item[$model]['moo_title']) ? h($item[$model]['moo_title']) : '';
                            }
                    }

                    if (!empty($item[$model]['group_id'])) { // group topic / video
                        $url = '/groups/view/' . $item[$model]['group_id'] . '/' . $type . '_id:' . $id;
                    } elseif ($type == 'activity') { // activity
                        $url = '/users/view/' . $item['User']['id'] . '/activity_id:' . $id;
                    } else {
                        $url = isset($item[key($item)]['moo_url']) ? $item[key($item)]['moo_url'] : '';

                        if ($type == 'Photo_Photo') {
                            $url .= '#content';
                        }
                    }

                    $notificationStopModel = MooCore::getInstance()->getModel('NotificationStop');
                    if (!$notificationStopModel->isNotificationStop($id, $type, $item['User']['id'])) {
                        $this->loadModel('Notification');
                        $this->Notification->record(array('recipients' => $item['User']['id'],
                            'sender_id' => $uid,
                            'action' => $action,
                            'url' => $url,
                            'params' => $params
                        ));
                    }
                }
            }
        }

        $item = $this->$model->findById($id);
        $re = array('like_count' => $item[$model]['like_count'], 'dislike_count' => $item[$model]['dislike_count']);

        $like_current = $this->Like->getUserLike($id, $uid, $type);
        if ($type == 'Photo_Photo') {
            $this->loadModel('Activity');
            $activity = $this->Activity->find('first', array(
                'conditions' => array(
                    'OR' => array(
                        array('item_type' => 'Photo_Album', 'action' => 'wall_post', 'items' => $id),
                        array('item_type' => 'Photo_Photo', 'action' => 'photos_add', 'items' => $id),
                    )
                )
            ));
            if ($activity) {
                $this->Like->deleteAll(array('Like.type' => 'activity', 'Like.user_id' => $uid, 'Like.target_id' => $activity['Activity']['id']), false);

                if ($like_current) {
                    $likeModel = MooCore::getInstance()->getModel('Like');
                    $likeModel->Behaviors->detach('Notification');
                    $likeModel->save(array(
                        'type' => 'activity',
                        'user_id' => $uid,
                        'target_id' => $activity['Activity']['id'],
                        'thumb_up' => $like_current['Like']['thumb_up']
                    ));
                }

                $this->Activity->updateCounter($activity['Activity']['id'], 'like_count', array('Like.type' => 'activity', 'Like.target_id' => $activity['Activity']['id'], 'Like.thumb_up' => 1), 'Like');
                $this->Activity->updateCounter($activity['Activity']['id'], 'dislike_count', array('Like.type' => 'activity', 'Like.target_id' => $activity['Activity']['id'], 'Like.thumb_up' => 0), 'Like');
            }
        }

        $cakeEvent = new CakeEvent('Controller.Like.afterLike', $this, array('aLike' => $this->Like->read()));
        $this->getEventManager()->dispatch($cakeEvent);

        $response = array('success' => 'true');
        echo json_encode($response);
    }

    // GET display people who liked that item 
    public function view() {
        $objectType = $this->request->params['object'];
        $id = $this->request->params['item_id'];
        $type = $this->_getType($objectType);
        if ($type == 'activity') {
            $activity = $this->Activity->findById($id);
        }

        list($plugin, $model) = mooPluginSplit($type);

        if ($plugin)
            $this->loadModel($plugin . '.' . $model);
        else
            $this->loadModel($model);

        $item = $this->$model->findById($id);
        if (empty($item)) {
            throw new ApiNotFoundException(__d('api', 'items not exist.'));
        }
        
        $users = $this->Like->getDisLikes( $id, $type, null, null);
        if (!empty($users)) {
            $this->set('users', $users);
        }
        else {
            throw new ApiNotFoundException(__d('api', 'Nobody disliked this item'));
        }
    }

}
