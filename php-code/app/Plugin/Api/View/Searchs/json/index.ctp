<?php
$items = array();
if (count($users) > 0){
    foreach ($users as $item){
        $items[] = array(
            'id' => $item["User"]['id'],
            'url' => FULL_BASE_URL.$this->request->base."/".((!empty( $item["User"]['username'] )) ? '-' . $item["User"]['username'] : 'users/view/'.$item["User"]['id']),
            'avatar' =>  $this->Moo->getItemPhoto(array('User' => $item["User"]),array( 'prefix' => '200_square'),array(),true),
            'owner_id' => $item ["User"]['id'],
            'title_1' => h($this->Text->truncate($item ["User"]['name'], 40)),
            'title_2' => __n( '%s friend', '%s friends', $item['User']['friend_count'], $item['User']['friend_count'] )." ".__n( '%s photo', '%s photos', $item['User']['photo_count'], $item['User']['photo_count'] ),
            'created' => $item ["User"]['created'],
            'type' => "User"
        );
    }
}
$utz = ( !is_numeric(Configure::read('core.timezone')) ) ? Configure::read('core.timezone') : 'UTC';
$cuser = MooCore::getInstance()->getViewer();
// user timezone
if ( !empty( $cuser['User']['timezone'] ) ){
    $utz = $cuser['User']['timezone'];
}
foreach ($searches as $k => $search) {
    switch ($k) {
        case 'Photo':
            $photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
            $friendModel = MooCore::getInstance()->getModel('Friend');
            $photoModel = MooCore::getInstance()->getModel('Photo_Photo');
            //var_dump($albums);die();
            if (count($albums) > 0) {
                foreach ($albums as $item) {
                    $covert = '';
                    if ($item['Album']['type'] == 'newsfeed' && $role_id != ROLE_ADMIN && $uid != $item['Album']['user_id'] && (!$uid || $friendModel->areFriends($uid,
                                $item['Album']['user_id']))
                    ) {
                        $photo = $photoModel->getPhotoCoverOfFeedAlbum($item['Album']['id']);
                        if ($photo) {
                            $covert = $photoHelper->getImage($photo, array('prefix' => '150_square'));
                        } else {
                            $covert = $photoHelper->getAlbumCover('', array('prefix' => '150_square'));
                        }
                    } else {
                        $covert = $photoHelper->getAlbumCover($item['Album']['cover'], array('prefix' => '150_square'));
                    }

                    $items[] = array(
                        'id' => $item ["Album"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/albums/view/".$item['Album']['id']."/".seoUrl($item['Album']['title']),
                        'avatar' => $covert,
                        'owner_id' => $item ["Album"]['user_id'],
                        'title_1' => h($this->Text->truncate($item ["Album"]['title'], 40)),
                        'title_2' => __n('%s photo', '%s photos', $item['Album']['photo_count'], $item['Album']['photo_count']),
                        'created' => $item ["Album"]['created'],
                        'type' => "Album"
                    );
                }
            }

            break;
        case 'Video':
            $videoHelper = MooCore::getInstance()->getHelper('Video_Video');

            if (!empty($videos) && count($videos) > 0) {
                foreach ($videos as $item) {
                    $privacy = 'Public';
                    switch ($item['Video']['privacy']) {
                        case PRIVACY_PUBLIC:
                            $privacy =  __( 'Public');
                            break;

                        case PRIVACY_FRIENDS:
                            $privacy =  __( 'Friend');
                            break;

                        case PRIVACY_PRIVATE:
                            $privacy =  __( 'Private');
                            break;
                    }
                    $items[] = array(
                        'id' => $item ["Video"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base . "/videos/view/" . $item['Video']['id'] . "/" . seoUrl($item['Video']['title']),
                        'avatar' => $videoHelper->getImage($item, array('prefix' => '450')),
                        'owner_id' => $item ["Video"]['user_id'],
                        'title_1' => $item ["Video"]['title'],
                        'title_2' => __n('%s like', '%s likes', $item['Video']['like_count'], $item['Video']['like_count']) .
                            ' ' . $this->Moo->getTime($item['Video']['created'], Configure::read('core.date_format'), $utz) .
                            ' ' . $privacy,
                        'created' => $item ["Video"]['created'],
                        'type' => "Video"
                    );
                }

            }
            break;
        case 'Event':
            $eventHelper = MooCore::getInstance()->getHelper('Event_Event');
            if (count($events) > 0) {
                foreach ($events as $item) {

                    $items[] = array(
                        'id' => $item ["Event"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/events/view/".$item['Event']['id']."/".seoUrl($item['Event']['title']),
                        'avatar' => $eventHelper->getImage($item, array('prefix' => '250')),
                        'owner_id' => $item ["Event"]['user_id'],
                        'title_1' => h($this->Text->truncate($item ["Event"]['title'], 40)),
                        'title_2' => h($item['Event']['location']) . ' ' . __('%s attending', $item['Event']['event_rsvp_count']),
                        'created' => $item ["Event"]['created'],
                        'type' => "Event"
                    );
                }
            }
            break;
        case 'Topic':
            $topicHelper = MooCore::getInstance()->getHelper('Topic_Topic');
            if (!empty($topics) && count($topics) > 0)
            {
                foreach ($topics as $item){

                    $items[] = array(
                        'id' => $item["Topic"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/topics/view/".$item['Topic']['id']."/".seoUrl($item['Topic']['title']),
                        'avatar' =>  $topicHelper->getImage($item, array('prefix' => '150_square')),
                        'owner_id' => $item ["Topic"]['user_id'],
                        'title_1' => h($this->Text->truncate($item ["Topic"]['title'], 40)),
                        'title_2' => __( 'Last posted by %s', $this->Moo->getNameWithoutUrl($item['LastPoster'], false)) .
                            ' ' . $this->Moo->getTime( $item['Topic']['last_post'], Configure::read('core.date_format'), $utz ),
                        'created' => $item ["Topic"]['created'],
                        'type' => "Topic"
                    );
                }
            }
            break;
        case 'Blog':
            $blogHelper = MooCore::getInstance()->getHelper('Blog_Blog');
            if (!empty($blogs) && count($blogs) > 0){

                foreach ($blogs as $item){
                    $items[] = array(
                        'id' => $item["Blog"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/blogs/view/".$item['Blog']['id']."/".seoUrl($item['Blog']['title']),
                        'avatar' =>  $blogHelper->getImage($item, array('prefix' => '150_square')),
                        'owner_id' => $item ["Blog"]['user_id'],
                        'title_1' => $item ["Blog"]['title'],
                        'title_2' => __( 'Posted by') . ' ' . $this->Moo->getNameWithoutUrl($item['User'], false) . ' ' .$this->Moo->getTime( $item['Blog']['created'], Configure::read('core.date_format'), $utz ),
                        'created' => $item ["Blog"]['created'],
                        'type' => "Blog"
                    );
                }
            }
            break;
        case 'Group':
            $groupHelper = MooCore::getInstance()->getHelper('Group_Group');
            if (!empty($groups) && count($groups) > 0){
                foreach ($groups as $item){
                    $privacy = 'Public';
                    switch ($item['Group']['type']) {
                        case PRIVACY_PUBLIC:
                            $privacy =  __( 'Public');
                            break;

                        case PRIVACY_RESTRICTED:
                            $privacy =  __( 'Restricted');
                            break;

                        case PRIVACY_PRIVATE:
                            $privacy =  __( 'Private');
                            break;
                    }
                    $items[] = array(
                        'id' => $item["Group"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/groups/view/".$item['Group']['id']."/".seoUrl($item['Group']['name']),
                        'avatar' =>  $groupHelper->getImage($item, array('prefix' => '150_square')),
                        'owner_id' => $item ["Group"]['user_id'],
                        'title_1' => h($this->Text->truncate($item["Group"]['name'], 40)),
                        'title_2' => __n('%s member', '%s members', $item['Group']['group_user_count'], $item['Group']['group_user_count']) .
                            ' ' . $privacy,
                        'created' => $item ["Group"]['created'],
                        'type' => "Group"
                    );
                }
            }
            break;


        default:
            $this->getEventManager()->dispatch(new CakeEvent('Plugin.View.Api.Search', $this,array(		               
				'items'=>&$items,
				'type' => $k
			)));
    }
}
if(empty($items) ) {
	throw new ApiNotFoundException(__d('api', 'Item not found'));
}
echo json_encode($items);