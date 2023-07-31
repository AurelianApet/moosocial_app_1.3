<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Reports Controller
 *
 */
class ReportsController extends ApiAppController {

    // check items exist , privacy , permission before do next action .
    protected function _checkBeforeAction($type, $item_id) {
        $uid = $this->Auth->user('id');
        list($plugin, $model) = mooPluginSplit($type);

        if ($plugin)
            $this->loadModel($plugin . '.' . $model);
        else
            $this->loadModel($model);

        $item = $this->$model->findById($item_id);


        if (empty($item)) {
            throw new ApiNotFoundException(__d('api', 'items not exist.'));
        }
        if ($plugin) { //echo '<pre>';print_r($item);die;
            $this->loadModel('Friend');
            $text = strtolower($plugin) . '_view';
            $this->_checkPermission(array('aco' => $text));
            $areFriends = $this->Friend->areFriends($uid, $item[$model]['id']);
            if (isset($item[$model]['privacy']))
                $this->_checkPrivacy($item[$model]['privacy'], $item[$model]['id'], $areFriends);

            if ($plugin == 'Video' || $plugin == 'Topic') {
                $this->loadModel('Group.GroupUser');
                if ($item[$model]['group_id']) {
                    $this->throwErrorCodeException('not_group_member');
                    throw new ApiBadRequestException(__d('api', 'This item is in a group. can not report'));
                }
                if ($plugin == 'Topic') {
                    if ($item[$model]['locked']) {
                        $this->throwErrorCodeException('topic_is_blocked');
                        throw new ApiBadRequestException(__('This topic has been locked'));
                    }
                }
            }
            if ($plugin == 'Photo' && $item[$model]['type'] == 'Group_Group') {
                $this->loadModel('Group.GroupUser');
                $this->loadModel('Group');
                if ($item[$model]['target_id']) {
                    $is_member = $this->GroupUser->isMember($uid, $item[$model]['target_id']);
                    $cuser = $this->_getUser();
                    if (!$cuser['Role']['is_admin'] && !$is_member) {
                        
                        $group = $this->Group->findById($item[$model]['target_id']);
                        if ($group['Group']['moo_privacy'] == 2) {
                            $this->throwErrorCodeException('not_group_member');
                            throw new ApiBadRequestException(__('This a group photo. Only group members can view'));
                        }
                    }
                }
            }
        }
    }

    public function action() {

        if (!isset($this->request->data['reason']) || empty($this->request->data['reason'])) {
            throw new ApiBadRequestException(__d('api', 'Reason is required '));
        }
        if (!isset($this->request->data['target_id']) || empty($this->request->data['target_id'])) {
            throw new ApiBadRequestException(__d('api', $this->request->params['object'] . ' id is required'));
        }
        $type = $this->_getType($this->request->params['object']);
        $this->_checkBeforeAction($type, $this->request->data['target_id']);
        $this->request->data['type'] = $type;
        $uid = $this->Auth->user('id');

        $this->request->data['user_id'] = $uid;
        $this->Report->set($this->request->data);

        $count = $this->Report->find('count', array('conditions' => array('type' => $this->request->data['type'],
                'target_id' => $this->request->data['target_id'],
                'user_id' => $uid)
        ));
        if ($count > 0) {
            throw new ApiBadRequestException(__('Duplicated report'));
        }

        $item = MooCore::getInstance()->getItemByType($this->request->data['type'], $this->request->data['target_id']);

        if ($this->Report->save()) { // successfully saved	
            $this->loadModel('AdminNotification');
            if (!empty($uid)) {
                $this->AdminNotification->save(array('user_id' => $uid,
                    'message' => $this->request->data['reason'],
                    'text' => __('reported a %s', key($item)),
                    'url' => $item[key($item)]['moo_href'],
                ));
            }
            $this->set(array(
                'message' => __('Thank you! Your report has been submitted'),
                '_serialize' => array('message'),
            ));
        }
    }

}
