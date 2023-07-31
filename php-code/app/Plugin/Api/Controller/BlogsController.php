<?php

App::uses('ApiAppController', 'Api.Controller');

/**
 * Blogs Controller
 *
 */
class BlogsController extends ApiAppController {

    /**
     * Scaffold
     *
     * @var mixed
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Blog.Blog');
    }

    public $scaffold;

    // browse blog by type : all,my,friend,popular,search
    public function browse() {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        if (!isset($this->request->params['type']))
            $type = 'search';
        else
            $type = $this->request->params['type'];
        $uid = $this->Auth->user('id');
        $param = $sFriendsList = '';
        if ($type == 'filter')
            $type = 'search';
        switch ($type) {
            case 'all':
                $param = $uid;
                break;
            case 'my':
                $this->set('user_blog', true);
                $this->_checkPermission();
                $param = $uid;
                break;
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
        $role_id = $this->_getUserRoleId();
        if ($type != 'popular') {
            $blogs = $this->Blog->getBlogs($type, $param, $page, null, $sFriendsList, $role_id);
        } else {
            $blogs = $this->Blog->getPopularBlogs(null, Configure::read('core.popular_interval'));
        }
        if (empty($blogs)) {
            throw new ApiNotFoundException(__d('api', 'Blog not found'));
        }
        $this->set('blogs', $blogs);
    }

    public function view() {
        $uid = $this->Auth->user('id');
        $id = $this->request->params['id'];
        $blog = $this->Blog->findById($id);
        if (empty($blog)) {
            throw new ApiNotFoundException(__d('api', 'Blog not found'));
        }
        $this->loadModel('Friend');
        $areFriends = $this->Friend->areFriends($uid, $blog['Blog']['id']);
        if (isset($blog['Blog']['privacy']))
            $this->_checkPrivacy($blog['Blog']['privacy'], $blog['Blog']['id'], $areFriends);
        $this->_checkPermission(array('aco' => 'blog_view'));
        $this->set('blog', $blog);
    }

    public function save() {
        $uid = $this->Auth->user('id');

        $this->_checkPermission(array('confirm' => true));

        if (!empty($this->request->data['id'])) { // edit blog
            // check edit permission			
            $blog = $this->Blog->findById($this->request->data['id']);
            if (empty($blog)) {
                throw new ApiNotFoundException(__d('api', 'Blog not found'));
            }
            $this->_checkPermission(array('admins' => array($blog['User']['id'])));
            $this->Blog->id = $this->request->data['id'];
        } else {
            if ($this->request->params['type'] == 'edit') {
                if (empty($this->request->data['id']))
                    throw new ApiBadRequestException(__d("api", "Blog id is missing"));
            }
            $this->request->data['user_id'] = $uid;
        }
        if (!isset($this->request->data['body']) || !isset($this->request->data['title']) || !isset($this->request->data['category_id']) ) {
            if (!isset($this->request->data['body']))
                throw new ApiBadRequestException(__d('api', 'blog body is missing.'));
            if (!isset($this->request->data['title']))
                throw new ApiBadRequestException(__d('api', 'blog title is missing.'));
            if (!isset($this->request->data['category_id']))
                throw new ApiBadRequestException(__d('api', 'blog category is missing.'));
        }
        if (isset($this->request->data['tags'])) {
            $this->_checkTags(array($this->request->data['tags']));
        }
        if (!isset($this->request->data['privacy'])) {
            $this->request->data['privacy'] = 1;
        }

        $this->request->data['body'] = str_replace('../', '/', $this->request->data['body']);
        if (isset($_FILES['qqfile'])) {
            $upload = $this->_uploadThumbnail();
            $this->request->data['thumbnail'] = $upload['file'];
        }

        if ($this->Blog->save($this->request->data)) {
            // update Blog item_id for photo thumbnail
            $this->loadModel('Photo.Photo');
            $this->Photo->updateAll(array('Photo.target_id' => $this->Blog->id), array(
                'Photo.type' => 'Blog',
                'Photo.user_id' => $uid,
                'Photo.target_id' => 0
            ));

            $event = new CakeEvent('Plugin.Controller.Blog.afterSaveBlog', $this, array(
                'tags' => $this->request->data['tags'],
                'id' => $this->Blog->id,
                'privacy' => $this->request->data['privacy']
            ));

            $this->getEventManager()->dispatch($event);

            $this->set(array(
                'message' => __d('api', 'success'),
                'id' => $this->Blog->id,
                '_serialize' => array('message', 'id'),
            ));
        }
    }

    public function delete() {
        $id = $this->request->params['blog_id'];
        $blog = $this->Blog->findById($id);
        if (empty($blog)) {
                throw new ApiNotFoundException(__d('api', 'Blog not found'));
            }
        $this->_checkPermission(array('admins' => array($blog['User']['id'])));

        $this->Blog->deleteBlog($blog);
        $this->set(array(
                'message' => __d('api', 'success'),
                '_serialize' => array('message'),
            ));
    }
    public function viewByCategory() {
        $this->loadModel('Category');
        $uid = $this->Auth->user('id');
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        $cat_id = $this->request->params['category_id'];


        $this->loadModel('Friend');
        $friends_list = $this->Friend->getFriendsList($uid);
        $aFriendListId = array_keys($friends_list);
        $sFriendsList = implode(',', $aFriendListId);

        $role_id = $this->_getUserRoleId();

        if (!empty($cat_id)) {
            $blogs = $this->Blog->getBlogs('category', $cat_id, $page, null, $sFriendsList, $role_id);
        }
        if (empty($blogs)) {
            throw new ApiNotFoundException(__d('api', 'Blog not found'));
        }
        $this->set('blogs', $blogs);
    }

}
