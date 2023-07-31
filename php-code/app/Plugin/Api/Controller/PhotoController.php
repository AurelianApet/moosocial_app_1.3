<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Photo Controller
 *
 */
class PhotoController extends ApiAppController {

    /**
     * Scaffold
     *
     * @var mixed
     */
    public $scaffold;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Photo.Album');
        $this->loadModel('Photo.Photo');
    }

    // Browse photo from a group
    public function photoGroup() {
        $page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;
        $uid = $this->Auth->user('id');
        $target_id = $this->request->params['group_id'];
        // check permission if group is private
        $this->loadModel('Group.Group');
        $group = $this->Group->findById($target_id);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $this->loadModel('Group.GroupUser');
        $is_member = $this->GroupUser->isMember($uid, $target_id);

        if ($group['Group']['type'] == PRIVACY_PRIVATE) {
            $cuser = $this->_getUser();

            if (!$cuser['Role']['is_admin'] && !$is_member) {
                $this->throwErrorCodeException('not_group_member');
                throw new ApiUnauthorizedException(__d("api","This is a private group"));
            }
        }
        $limit = Configure::read('Photo.photo_item_per_pages');
        $photos = $this->Photo->getPhotos('group_group', $target_id, $page, $limit, null);
        if (empty($photos)) {
            throw new ApiNotFoundException(__d('api', 'Photo not found'));
        }
        $this->set('photos', $photos);
    }

    public function view() {
        $id = $this->request->params['photo_id'];

        $photo = $this->Photo->findById($id);
        if (empty($photo)) {
            throw new ApiNotFoundException(__d('api', 'Photo not found'));
        }
        $this->_checkPermission(array('aco' => 'photo_view'));
        $this->_checkPermission(array('user_block' => $photo['Photo']['user_id']));
        $this->loadModel('Photo.PhotoTag');
        $photo['photo_tag'] = $this->PhotoTag->find('all', array('conditions' => array('photo_id' => $photo['Photo']['id'])
        ));

        $this->set('photo', $photo);
    }

    // Tag a user to photo
    public function tag() {
        $this->loadModel('User');
        $uid = $this->Auth->user('id');
        $user_id = $this->request->data['user_id'];
        $photo_id = $this->request->data['photo_id'];
        $photo = $this->Photo->findById($photo_id);
        if (empty($photo)) {
            throw new ApiNotFoundException(__d('api', 'Photo not found'));
        }
        $user = $this->User->findById($user_id);
        if (empty($user)) {
            throw new ApiNotFoundException(__d('api', 'User not found'));
        }
        $this->_checkPermission(array('aco' => 'photo_view'));
        $this->_checkPermission(array('user_block' => $photo['Photo']['user_id']));
        $this->loadModel('Photo.PhotoTag');

        // if tagging a member then check if that member is already tagged in this photo
        if (!empty($user_id))
            $tag = $this->PhotoTag->find('first', array('conditions' => array('photo_id' => $photo_id, 'PhotoTag.user_id' => $user_id)));

        if (empty($tag)) {
            $this->PhotoTag->save(array('photo_id' => $photo_id,
                'user_id' => $user_id,
                'tagger_id' => $uid,
                'value' => $this->request->data['value'],
                'style' => $this->request->data['style']
            ));

            if ($user_id) {
                // insert into activity
                $this->loadModel('Activity');
                $activity = $this->Activity->getRecentActivity('photos_tag', $user_id);

                if (!empty($activity)) {
                    $photo_ids = explode(',', $activity['Activity']['items']);
                    $photo_ids[] = $photo_id;

                    $this->Activity->id = $activity['Activity']['id'];
                    $this->Activity->save(array('items' => implode(',', $photo_ids)
                    ));
                } else {
                    $this->Activity->save(array('type' => APP_USER,
                        'action' => 'photos_tag',
                        'user_id' => $user_id,
                        'item_type' => 'Photo_Photo',
                        'items' => $photo_id,
                        'query' => 1,
                        'params' => 'no-comments',
                        'plugin' => 'Photo',
                        'privacy' => $photo['Photo']['moo_privacy']
                    ));
                }

                if ($user_id != $uid) {
                    // add notification
                    $this->loadModel("User");
                    if ($this->User->checkSettingNotification($user_id, 'tag_photo')) {
                        $this->loadModel('Notification');
                        $this->Notification->record(array('recipients' => $user_id,
                            'sender_id' => $uid,
                            'action' => 'photo_tag',
                            'url' => '/photos/view/' . $photo_id . '#content'
                        ));
                    }
                }
            }

            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->PhotoTag->id,
                '_serialize' => array('message', 'id'),
            ));
        } else {
            throw new ApiBadRequestException(__("Duplicated tag!"));
        }
    }

    // Remove tag a user to photo
    public function removeTag() {

        $this->loadModel('Photo.PhotoTag');
        $tag = $this->PhotoTag->findById($this->request->data['tag_id']);
        if (!$tag) {
            throw new ApiNotFoundException(__d('api', 'Not tagged yet.'));
        }

        // tagger, user was tagged and photo author can delete tag
        $admins = array($tag['PhotoTag']['user_id'], $tag['PhotoTag']['tagger_id'], $tag['Photo']['user_id']);

        $this->_checkPermission(array('admins' => $admins));
        $this->PhotoTag->delete($this->request->data['tag_id']);

        $this->loadModel('Activity');
        $activity = $this->Activity->getRecentActivity('photos_tag', $tag['PhotoTag']['user_id']);

        if ($activity) {
            $items = array_filter(explode(',', $activity['Activity']['items']));
            $items = array_diff($items, array($tag['PhotoTag']['photo_id']));

            if (!count($items)) {
                $this->Activity->delete($activity['Activity']['id']);
            } else {
                $this->Activity->id = $activity['Activity']['id'];
                $this->Activity->save(
                        array('items' => implode(',', $items))
                );
            }
        }
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    //Upload photo in a group
    public function groupUpload() {
        $this->loadModel('Activity');
        $this->loadModel('Group.Group');
        $this->_checkPermission(array('aco' => 'photo_upload'));
        $group = $this->Group->findById($this->request->data['target_id']);
        if (empty($group)) {
            throw new ApiNotFoundException(__d('api', 'Group not found'));
        }
        $uid = $this->Auth->user('id');

        if (isset($_FILES['qqfile'])) {
            foreach ($_FILES['qqfile']['name'] as $i => $wallphoto):
                $wallphotos[$i]['name'] = $wallphoto;
            endforeach;
            foreach ($_FILES['qqfile']['size'] as $i => $wallphoto):
                $wallphotos[$i]['size'] = $wallphoto;
            endforeach;
            foreach ($_FILES['qqfile']['type'] as $i => $wallphoto):
                $wallphotos[$i]['type'] = $wallphoto;
            endforeach;
            foreach ($_FILES['qqfile']['tmp_name'] as $i => $wallphoto):
                $wallphotos[$i]['tmp_name'] = $wallphoto;
            endforeach;
            foreach ($_FILES['qqfile']['error'] as $i => $wallphoto):
                $wallphotos[$i]['error'] = $wallphoto;
            endforeach;

            foreach ($wallphotos as $wallphoto):
                $_FILES['qqfile'] = $wallphoto;
                $upload = $this->_uploadThumbnail();
                $file[] = $upload['file'];
            endforeach;
        }
        else {
            throw new ApiBadRequestException(__d('api', "Please select photo"));
        }
        $photoList = $file;

        $this->request->data['type'] = 'Group_Group';
        $this->request->data['user_id'] = $uid;

        $photoId = array();
        foreach ($photoList as $photoItem) {
            if (!empty($photoItem)) {
                $this->request->data['thumbnail'] = $photoItem;
                $this->Photo->create();

                $this->Photo->set($this->request->data);
                $this->Photo->save();
                array_push($photoId, $this->Photo->id);
            }
        }

        $privacy = PRIVACY_EVERYONE;

        if ($group['Group']['type'] == PRIVACY_PRIVATE || $group['Group']['type'] == PRIVACY_RESTRICTED)
            $privacy = PRIVACY_ME;

        $share = 0;
        if ($privacy == PRIVACY_EVERYONE) {
            $share = 1;
        }

        $this->Activity->save(array(
            'type' => 'Group_Group',
            'target_id' => $this->request->data['target_id'],
            'action' => 'photos_add',
            'user_id' => $uid,
            'items' => join(',', $photoId),
            'item_type' => 'Photo_Photo',
            'privacy' => $privacy,
            'query' => 1,
            'plugin' => 'Photo',
            'share' => $share
        ));
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    //Set photo as profile cover photo
    public function setCover() {
        $uid = $this->Auth->user('id');

        $path = 'uploads' . DS . 'tmp' . DS;
        $url = 'uploads/tmp/';



        $photo_id = $this->request->data['photo_id'];

        $this->loadModel('Photo.Photo');
        $this->loadModel('Photo.Album');
        $aPhoto = $this->Photo->findById($photo_id);
        if (!$aPhoto) {
            throw new ApiNotFoundException(__d('api', 'Photo not found.'));
        }

        if ($aPhoto['Photo']['year_folder']) {  // hacking for MOOSOCIAL-2771
            $year = date('Y', strtotime($aPhoto['Photo']['created']));
            $month = date('m', strtotime($aPhoto['Photo']['created']));
            $day = date('d', strtotime($aPhoto['Photo']['created']));
            $photo_path = WWW_ROOT . "uploads" . DS . "photos" . DS . "thumbnail" . DS . $year . DS . $month . DS . $day . DS . $aPhoto['Photo']['id'] . DS . $aPhoto['Photo']['thumbnail'];
        } else {
            $photo_path = WWW_ROOT . 'uploads' . DS . 'photos' . DS . 'thumbnail' . DS . $aPhoto['Photo']['id'] . DS . $aPhoto['Photo']['thumbnail'];
        }

        // copy to tmp path
        $file = $photo_path;
        $newTmpAvatar = WWW_ROOT . $path . $aPhoto['Photo']['thumbnail'];
        copy($file, $newTmpAvatar);
        $newTmpAvatar1 = WWW_ROOT . $path . 'tmp_' . $aPhoto['Photo']['thumbnail'];
        copy($file, $newTmpAvatar1);

        $album = $this->Album->getUserAlbumByType($uid, 'cover');
        $title = 'Cover Pictures';

        if (empty($album)) {
            $this->Album->save(array('user_id' => $uid, 'type' => 'cover', 'title' => $title), false);
            $album_id = $this->Album->id;
            $album = $this->Album->initFields();
        } else {
            $album_id = $album['Album']['id'];
        }

        // resize image
        App::import('Vendor', 'phpThumb', array('file' => 'phpThumb/ThumbLib.inc.php'));

        $photo = PhpThumbFactory::create($path . DS . $aPhoto['Photo']['thumbnail']);

        // save to db
        $this->loadModel('Photo.Photo');
        $this->Photo->create();
        $this->Photo->set(array('user_id' => $uid,
            'target_id' => $album_id,
            'type' => 'Photo_Album',
            'thumbnail' => $path . $aPhoto['Photo']['thumbnail'],
        ));
        $this->Photo->save();

        // save album cover
        if (isset($album['Album']['cover']) && !$album['Album']['cover']) {
            $this->Album->id = $album_id;
            $this->Album->save(array('cover' => $aPhoto['Photo']['thumbnail']));
        }

        /* Create and update cover */
        $cover_path = WWW_ROOT . 'uploads' . DS . 'covers';
        $cover_loc = $cover_path . DS . $aPhoto['Photo']['thumbnail'];

        if (!file_exists($cover_path)) {
            mkdir($cover_path, 0755, true);
            file_put_contents(WWW_ROOT . $path . DS . 'index.html', '');
        }

        // resize image
        $cover = PhpThumbFactory::create($path . 'tmp_' . $aPhoto['Photo']['thumbnail'], array('jpegQuality' => PHOTO_QUALITY));
        $cover->adaptiveResize(COVER_WIDTH, COVER_HEIGHT)->save($cover_loc);

        // delete tmp thumbnail
        if (file_exists(WWW_ROOT . $path . 'tmp_' . $aPhoto['Photo']['thumbnail'])) {
            unlink(WWW_ROOT . $path . 'tmp_' . $aPhoto['Photo']['thumbnail']);
        }

        $this->loadModel('User');
        $user = $this->User->findById($uid);

        // delete old files
        $this->User->removeCoverFile($user['User']);

        // update user cover pic in db
        $this->User->id = $uid;
        $this->User->save(array('cover' => $aPhoto['Photo']['thumbnail']));

        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    //Set photo as avatar photo
    public function setAvatar() {
        $uid = $this->Auth->user('id');

        $path = 'uploads' . DS . 'tmp' . DS;

        $photo_id = $this->request->data['photo_id'];

        $this->loadModel('Photo.Photo');
        $this->loadModel('Photo.Album');
        $aPhoto = $this->Photo->findById($photo_id);
        if (!$aPhoto) {
            throw new ApiNotFoundException(__d('api', 'Photo not found.'));
        }

        if ($aPhoto['Photo']['year_folder']) {  // hacking for MOOSOCIAL-2771
            $year = date('Y', strtotime($aPhoto['Photo']['created']));
            $month = date('m', strtotime($aPhoto['Photo']['created']));
            $day = date('d', strtotime($aPhoto['Photo']['created']));
            $photo_path = WWW_ROOT . "uploads" . DS . "photos" . DS . "thumbnail" . DS . $year . DS . $month . DS . $day . DS . $aPhoto['Photo']['id'] . DS . $aPhoto['Photo']['thumbnail'];
        } else {
            $photo_path = WWW_ROOT . 'uploads' . DS . 'photos' . DS . 'thumbnail' . DS . $aPhoto['Photo']['id'] . DS . $aPhoto['Photo']['thumbnail'];
        }

        // copy to tmp path
        $file = $photo_path;
        $newTmpAvatar = WWW_ROOT . $path . $aPhoto['Photo']['thumbnail'];
        $newTmpAvatar1 = WWW_ROOT . $path . 'tmp_' . $aPhoto['Photo']['thumbnail'];
        copy($file, $newTmpAvatar);

        copy($file, $newTmpAvatar1);

        $album = $this->Album->getUserAlbumByType($uid, 'profile');
        $title = 'Profile Pictures';

        if (empty($album)) {
            $this->Album->save(array('user_id' => $uid, 'type' => 'profile', 'title' => $title), false);
            $album_id = $this->Album->id;
            $album = $this->Album->initFields();
        } else {
            $album_id = $album['Album']['id'];
        }

        // save to db
        $this->loadModel('Photo.Photo');
        $this->Photo->create();
        $this->Photo->set(array('user_id' => $uid,
            'target_id' => $album_id,
            'type' => 'Photo_Album',
            'thumbnail' => $path . $aPhoto['Photo']['thumbnail'],
        ));
        $this->Photo->save();

        if (isset($album['Album']['cover']) && !$album['Album']['cover']) {
            $this->Album->save(array('cover' => $aPhoto['Photo']['thumbnail']));
            $this->Album->id = $album_id;
        }

        $this->loadModel('User');
        $user = $this->User->findById($uid);

        $this->User->id = $uid;
        $this->User->set(array('avatar' => $path . 'tmp_' . $aPhoto['Photo']['thumbnail']));
        $this->User->save();

        // insert into activity feed
        if ($user['User']['last_login'] != $user['User']['created']) {
            $this->loadModel('Activity');
            $activity = $this->Activity->getRecentActivity('user_avatar', $uid);

            if (empty($activity)) {
                $this->Activity->save(array('type' => 'user',
                    'action' => 'user_avatar',
                    'user_id' => $uid
                ));
            }
        }

        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    //Update caption photo
    public function caption() {
        $photo_id = '';
        if ($this->request->is('put')) {
            $data = $this->request->input('json_decode');
            $caption = $data->caption ? $data->caption : $this->request->data['caption'];
            $photo_id = $data->photo_id ? $data->photo_id : $this->request->data['photo_id'];
        } else {
            $caption = $this->request->data['caption'];
            $photo_id = $this->request->data['photo_id'];
        }
        $this->loadModel('Photo.Photo');
        $this->loadModel('Photo.Album');
        $aPhoto = $this->Photo->findById($photo_id);
        $this->_checkPermission(array('admins' => array($aPhoto['Photo']['user_id'])));
        $this->_checkPermission(array('aco' => 'album_create'));
        if (!$aPhoto) {
            throw new ApiNotFoundException(__d('api', 'Photo not found.'));
        }
        $this->Photo->id = $aPhoto['Photo']['id'];
        $this->Photo->save(array('caption' => $caption));
        $this->set(array(
            'message' => __d('api', 'success'),
            'id' => $this->Photo->id,
            '_serialize' => array('message', 'id'),
        ));
    }

    // Delete a photo
    public function delete() {
        $photo = $this->Photo->findById($this->request->params['photo_id']);
        if (empty($photo)) {
            throw new ApiNotFoundException(__d('api', 'Photo not found'));
        }
        $admins = array($photo['Photo']['user_id']);

        if ($photo['Photo']['type'] == 'Group_Group') { // if it's a group photo, add group admins to the admins array
            // get group admins
            $this->loadModel('Group.GroupUser');

            $group_admins = $this->GroupUser->getUsersList($photo['Photo']['target_id'], GROUP_USER_ADMIN);
            $admins = array_merge($admins, $group_admins);
        }

        if ($photo['Photo']['album_type']) {
            switch ($photo['Photo']['album_type']) {
                case 'Group_Group':
                    $this->loadModel('Group.GroupUser');
                    $group_admins = $this->GroupUser->getUsersList($photo['Photo']['album_type_id'], GROUP_USER_ADMIN);
                    $admins = array_merge($admins, $group_admins);

                    break;
                case 'Event_Event':
                    $event = MooCore::getInstance()->getItemByType($photo['Photo']['album_type'], $photo['Photo']['album_type_id']);
                    if ($event)
                        $admins[] = $event['Event']['user_id'];
                    break;
            }
        }

        // make sure user can delete photo
        $this->_checkPermission(array('admins' => $admins));

        // permission ok, delete photo now
        $this->Photo->delete($photo['Photo']['id']);

        // delete activity comment_add_photo
        $activityModel = MooCore::getInstance()->getModel('Activity');
        $activityModel->deleteAll(array('Activity.item_type' => 'Photo_Photo', 'Activity.action' => 'comment_add_photo', 'Activity.item_id' => $photo['Photo']['id']));

        // delete activity photos_tag
        $activityModel->deleteAll(array('Activity.item_type' => 'Photo_Photo', 'Activity.action' => 'photos_tag', 'Activity.items' => $photo['Photo']['id']));

        $cakeEvent = new CakeEvent('Plugin.Controller.Group.afterDeletePhoto', $this, array('item' => $photo));
        $this->getEventManager()->dispatch($cakeEvent);

        if (!$photo['Photo']['album_type']) { 
            // update cover of album
            $nextCoverPhoto = $this->Photo->find('first', array('conditions' => array('Photo.type' => 'Photo_Album', 'Photo.target_id' => $photo['Photo']['target_id'])));
            $currentCoverPhoto = $this->Album->find('first', array('conditions' => array('Album.id' => $photo['Photo']['target_id'])));

            if (!empty($nextCoverPhoto)) {
                // cond1: delete item is cover => need to update cover
                // cond2: current album have no cover => need to update cover
                if ($photo['Photo']['thumbnail'] == $currentCoverPhoto['Album']['cover'] || empty($currentCoverPhoto['Album']['cover'])) {
                    $this->Album->id = $photo['Photo']['target_id'];
                    $this->Album->save(array(
                        'cover' => $nextCoverPhoto['Photo']['thumbnail']
                    ));
                }
            } else {
                $this->Album->id = $photo['Photo']['target_id'];
                $this->Album->save(array(
                    'cover' => ''
                ));
            }
        } 
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
        
    }

}
