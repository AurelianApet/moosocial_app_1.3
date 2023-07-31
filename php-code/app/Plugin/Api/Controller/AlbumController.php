<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Albums Controller
 *
 * @property Album $Album
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class AlbumController extends ApiAppController {

    /**
     * Components
     *
     * @var array
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Photo.Album');
        $this->loadModel('Photo.Photo');
    }

    public $components = array('Paginator', 'Session');

    public function browse() {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $uid = $this->Auth->user('id');
        $role_id = $this->_getUserRoleId();
        $sFriendsList = '';
        if (!isset($this->request->params['type']))
            $type = 'search';
        else
            $type = $this->request->params['type'];
        if ($type == 'filter')
            $type = 'search';
        if (!empty($this->request->params['category_id'])) {
            $type = 'category';
            $param = $this->request->params['category_id'];
        }
        $param = '';
        switch ($type) {
            case 'my':
            case 'friends':
                $this->_checkPermission();
                $param = $uid;
                break;

            case 'search':
                if (isset($this->request->data['keyword'])) {
                    $param = urldecode($this->request->data['keyword']);
                }
                if (!Configure::read('core.guest_search') && empty($uid))
                    $this->_checkPermission();

                break;
            default:
                $this->loadModel('Friend');
                $friends_list = $this->Friend->getFriendsList($uid);
                $aFriendListId = array_keys($friends_list);
                $sFriendsList = implode(',', $aFriendListId);
                $param = $uid;
        }
        if ($type != 'popular') {
            $albums = $this->Album->getAlbums($type, $param, $page, RESULTS_LIMIT, $sFriendsList, $role_id);
        } else {
            $albums = $this->Album->getPopularAlbums(null, Configure::read('core.popular_interval'));
        }
        if (empty($albums)) {
            throw new ApiNotFoundException(__d('api', 'Album not found'));
        }
        //echo '<pre>';print_r($albums);die;
        $this->set('albums', $albums);
    }

    public function view() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['album_id'];

        $album = $this->Album->findById($id);
        if (empty($album)) {
            throw new ApiNotFoundException(__d('api', 'Album not found'));
        }
        $this->_checkPermission(array('aco' => 'album_view'));
        $this->_checkPermission(array('user_block' => $album['Album']['user_id']));
        MooCore::getInstance()->setSubject($album);

        $this->_checkPrivacy($album['Album']['privacy'], $album['User']['id']);

        $this->loadModel('Photo.Photo');
        $limit = Configure::read('Photo.photo_item_per_pages');
        $params = array();
        if ($album['Album']['type'] == 'newsfeed') {
            $this->loadModel('Friend');
            $params['newsfeed'] = true;
            if ($uid == $album['User']['id'] || $this->_getUserRoleId() == ROLE_ADMIN || ($uid && $this->Friend->areFriends($uid, $album['User']['id']))) {
                $params['is_friend'] = true;
            }
        }

        $photoAlbums = $this->Photo->getPhotos('Photo_Album', $id, 1, $limit, $params);

        $album['Photo']['description'] = CakeText::truncate($album['Album']['description'], 80, array('ellipsis' => '', 'html' => false, 'exact' => false));

        $this->set('album', $album);
        $this->set('photoAlbums', $photoAlbums);
    }

    public function save() {
        $uid = $this->Auth->user('id');

        if (!isset($this->request->data['title']) || !isset($this->request->data['category_id'])) {
            if (!isset($this->request->data['title']))
                throw new ApiBadRequestException(__d('api', 'album title is missing.'));
            if (!isset($this->request->data['category_id']) && !isset($this->request->data['group_id']))
                throw new ApiBadRequestException(__d('api', 'album category is missing.'));
        }
        if (!empty($this->request->data['id'])) {
            // check edit permission			
            $album = $this->Album->findById($this->request->data['id']);
            if (empty($album)) {
                throw new ApiNotFoundException(__d('api', 'Album not found'));
            }
            $this->_checkPermission(array('admins' => array($album['User']['id'])));
            $this->Album->id = $this->request->data['id'];
        } else
            $this->request->data['user_id'] = $uid;
        $this->Album->set($this->request->data);
        $this->_validateData($this->Album);

        $this->Album->set($this->request->data);
        if ($this->Album->save()) { // successfully saved	
            // save tags
            $this->loadModel('Tag');
            $this->Tag->saveTags($this->request->data['tags'], $this->Album->id, 'Photo_Album');


            $event = new CakeEvent('Plugin.Controller.Album.afterSaveAlbum', $this, array(
                'uid' => $uid,
                'id' => $this->Album->id,
                'privacy' => $this->request->data['privacy']
            ));

            $this->getEventManager()->dispatch($event);

            $album = $this->Album->read();
            $this->loadModel('Activity');
            $this->Activity->updateAll(array('privacy' => $album['Album']['privacy']), array('action' => 'photos_add', 'item_type' => 'Photo_Album', 'item_id' => $album['Album']['id']));

            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->Album->id,
                '_serialize' => array('message', 'id'),
            ));
        } else {
            throw new ApiBadRequestException(__d('api', 'please check again.'));
        }
    }

    public function uploadPhoto() {
        $uid = $this->Auth->user('id');
        $this->loadModel('Activity');
        $this->_checkPermission(array('aco' => 'photo_upload'));
        $album = $this->Album->findById($this->request->data['target_id']);
        if (empty($album)) {
            throw new ApiNotFoundException(__d('api', 'Album not found'));
        }
        $this->_checkPermission(array('aco' => 'album_view'));
        $this->_checkPermission(array('user_block' => $album['Album']['user_id']));
        if ( $uid != $album['User']['id'] ){
            throw new ApiUnauthorizedException(__d('api', 'Only the poster can upload photo.'));
        }
        $photoList = array();

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
            $photoList = $file;
        }
        else {
            throw new ApiBadRequestException(__d('api', 'Please select photo'));
        }
        

        $this->request->data['type'] = 'Photo_Album';
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

        $activity = $this->Activity->getItemActivity('Photo_Album', $this->request->data['target_id']);

        if (!empty($activity)) { // update the existing one
            $this->Activity->id = $activity['Activity']['id'];
            $this->Activity->save(array('items' => join(',', $photoId), 'privacy' => $album['Album']['privacy']));
        } else // insert new
            $this->Activity->save(array('type' => APP_USER,
                'action' => 'photos_add',
                'user_id' => $uid,
                'items' => join(',', $photoId),
                'item_type' => 'Photo_Album',
                'item_id' => $this->request->data['target_id'],
                'privacy' => $album['Album']['privacy'],
                'query' => 1,
                'params' => 'item',
                'plugin' => 'Photo'
            ));

        // update privacy photo album
        $this->Photo->updateAll(array('Photo.privacy' => $album['Album']['privacy']), array('Photo.id' => $photoId));

        $event = new CakeEvent('Plugin.Controller.Album.afterSaveAlbum', $this, array(
            'uid' => $uid,
            'id' => $album['Album']['id'],
            'privacy' => $album['Album']['privacy']
        ));

        $this->getEventManager()->dispatch($event);
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    public function setAlbumCover() {


        $album = $this->Album->findById($this->request->data['id']);
        if (empty($album)) {
            throw new ApiNotFoundException(__d('api', 'Album not found'));
        }
        $this->_checkPermission(array('admins' => array($album['User']['id'])));
        $photo = $this->Photo->findById($this->request->data['photo_id']);
        if (empty($photo)) {
            throw new ApiNotFoundException(__d('api', 'Photo not found'));
        }
        if ($photo['Photo']['target_id'] != $this->request->data['id']) {
            throw new ApiBadRequestException(__d('api', 'Photo not belong to this album.'));
        }

        $nextCoverPhoto = $this->Photo->find('first', array('conditions' => array('Photo.type' => 'Photo_Album', 'Photo.id' => $photo['Photo']['id'])));
        $currentCoverPhoto = $this->Album->find('first', array('conditions' => array('Album.id' => $photo['Photo']['target_id'])));
        if (!empty($nextCoverPhoto)) {
            // cond1: delete item is cover => need to update cover
            // cond2: current album have no cover => need to update cover
            if ($photo['Photo']['thumbnail'] != $currentCoverPhoto['Album']['cover'] || empty($currentCoverPhoto['Album']['cover'])) {
                $this->Album->id = $photo['Photo']['target_id']; //echo $nextCoverPhoto['Photo']['thumbnail'];die;
                $this->Album->save(array(
                    'cover' => $nextCoverPhoto['Photo']['thumbnail']
                ));
            }
        }
        $this->set(array(
            'message' => __d('api', 'success'),
            '_serialize' => array('message'),
        ));
    }

    public function delete() {
        $album = $this->Album->findById($this->request->params['album_id']);
        if (empty($album)) {
            throw new ApiNotFoundException(__d('api', 'Album not found'));
        }
        $this->_checkPermission(array('admins' => array($album['User']['id'])));
        if ($album['Album']['type'] != 'cover') {
            $this->Album->deleteAlbum($album);

            $cakeEvent = new CakeEvent('Plugin.Controller.Album.afterDeleteAlbum', $this, array('item' => $album));
            $this->getEventManager()->dispatch($cakeEvent);

            $this->set(array(
                'message' => __('Album has been deleted'),
                '_serialize' => array('message'),
            ));
        } else {
            throw new ApiBadRequestException(__('You can\'t delete cover album'));
        }
    }

}
