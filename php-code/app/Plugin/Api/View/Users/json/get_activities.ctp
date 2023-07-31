<?php
foreach ($datas['activities'] as $i =>  $data ) : 
    
    $check_privacy_type = true;
	$admins_current = (isset($admins) ? array_merge($admins,array($data['Activity']['user_id'])) : array($data['Activity']['user_id']));
	$item_type = $data['Activity']['item_type'];
	if ($data['Activity']['plugin'])
	{
		$options = array('plugin'=>$data['Activity']['plugin']);
	}
	else
	{
		$options = array();
	}
	
	if ($item_type)
	{
		list($plugin, $name) = mooPluginSplit($item_type);
		$object = MooCore::getInstance()->getItemByType($item_type,$data['Activity']['item_id']);
		
	}
	else
	{ 
		$plugin = '';
		$name ='';
		$object = null;
	}
        //echo '<pre>'; print_r($data);die;
    $items = array();
    $activityId = $data['Activity']['id']; 
    $tagUser = $title = $target = $type = $objecttType = '';
    switch ($data['Activity']['item_type']) {
        case '' :
            $activityText = $title = '';
            $shareContent = $feedLink = $imageArray = array ();
  
            if($data['Activity']['action'] == 'user_create') :
                $type = 'join';$objecttType='Activty';
                $title =  $data['User']['name'] . ' ' . __('joined %s', Configure::read('core.site_name'));
            endif;
            
            if($data['Activity']['action'] == 'user_avatar') :
                 if ($data['User']['gender'] == 'Female'): 
                    $title = __('changed her profile picture');
                 elseif($data['User']['gender'] == 'Male'):
                    $title = __('changed his profile picture');
                 else:
                    $title = __('changed their profile picture');
                 endif;
                 $type = 'update';$objecttType='Activty';
            endif;
            
            if($data['Activity']['action'] == 'wall_post' || $data['Activity']['action'] == 'wall_post_link') : 
                    $type = 'post';$objecttType='Activty';
                        $activityText = $data['Activity']['content'];
                        if (!empty($data['UserTagging']['users_taggings'])) : 
                            $activityText = array ();
                            $activityText = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                            $tagUser = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                        endif;
                    if ($data['Activity']['target_id']):
                        
                        $type = 'post';$objecttType='Activty_Post_User';
//                        $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
//
//                        list($plugin, $name) = mooPluginSplit($data['Activity']['type']);
//                        $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);
//
//                        if ($show_subject):
//                        $title = array(
//                            'url' => $subject[$name]['moo_href'] ,
//                            'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
//                            );
//                         endif;  
                     endif;
                     
                    if($data['Activity']['action'] == 'wall_post_link') :
                        $type = 'post';$objecttType='Activty_Link';
                        $link = unserialize($data['Activity']['params']);
                        $url = (isset($link['url']) ? $link['url'] : $data['Activity']['content']);
                        
                        if ( !empty( $link['image'] ) ): 
                            if ( strpos( $link['image'], 'http' ) === false ):
                                $feedLink['image'] = $this->request->webroot . 'uploads/links/' .  $link['image'] ;
                            else:
                                $feedLink['image'] = $link['image'];
                            endif;
                         endif;
                        $feedLink['title'] =  h($link['title']);
                        $feedLink['url'] =  $url;
                        if ( !empty( $link['description'] ) ) :
                            $feedLink['description'] =  ($this->Text->truncate($link['description'], 150, array('exact' => false))) ;
                        endif;
                    endif; 
               endif;
            
            if($data['Activity']['action'] == 'wall_post_share' || $data['Activity']['action'] == 'wall_post_link_share'  ) :
                $type = 'share';$objecttType='Activty';
                $activityId = $data['Activity']['parent_id'];
                if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                    list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);
                    $activityModel = MooCore::getInstance()->getModel('Activity');
                    $parentFeed = $activityModel->findById($data['Activity']['parent_id']);
                    
                    $title = __("shared %s's post",  $parentFeed['User']['name'] ) ;
                    $target = array(
                        'url' => $parentFeed['User']['moo_href'],
                        'id' =>  $parentFeed['User']['id'],
                        'type' =>  'User',
                        );
                 endif;
                 
                $activityText = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $activityText = array ();
                    $activityText = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $tagUser = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']); 
                endif;
                

            endif;
            

            $items = array(
                'type' => $objecttType,
                'id' => $activityId ,
                'url' => FULL_BASE_URL . $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'users',
                                    'action' => 'view',
                                    $data['User']['id'],
                                    'activity_id' => $activityId
                                )),
                'content' => $activityText ? $activityText : '',
                'link' => $feedLink ? $feedLink : '',
                'imageArray' => $imageArray ? $imageArray : '',
                'tagUser' => $tagUser ? $tagUser : '',
            );
            break;
            
        case 'user' :
            $title = '';
            if($data['Activity']['action'] == 'friend_add') :
                $type = 'add';$objecttType='User';
                
                $ids = explode(',', $data['Activity']['items']);
                $userModel = MooCore::getInstance()->getModel('User');
                $userModel->cacheQueries = false;
                $users = $userModel->find('all', array('conditions' => array('User.id' => $ids)));
                
                //get status of feed 
                $friend_add1 = '%s';
                $friend_add2 = __('%s and %s');
                $friend_add3 = __('%s and %s');
                $friend_add = '';

                switch (count($users)) {
                    case 1:
                        $friend_add = sprintf($friend_add1,  h($users[0]['User']['name']));
                        break;
                    case 2:
                        $friend_add = sprintf($friend_add2,  h($users[0]['User']['name'])  ,  h($users[1]['User']['name']) );
                        break;
                    case 3:
                    default :
                        $friend_add = sprintf($friend_add3, h($users[0]['User']['name']), abs(count($users) - 1) . ' ' .  __('others') );
                        break;
                }

                $title = $data['User']['name'] .' '. __('is now friends with') . ' ' . $friend_add;
                
                //get Friend List display at feed
                $userArrays = array();
                 foreach ( $users as $user ):
                    if (!empty($user)): 
                        
                        if (!empty($uid)):
                                $userStatus = '';
                                if ( $this->MooPeople->sentFriendRequest($user['User']['id'])): 
                                   $userStatus =  __('Cancel Request');
                                elseif ($this->MooPeople->respondFriendRequest($user['User']['id'])): 
                                    $userStatus =  __('Respond to Friend Request');
                                elseif ($this->MooPeople->isFriend($user['User']['id'])): 
                                        $userStatus =  __('Remove');     
                                elseif ($user['User']['id'] != $uid): 
                                        $userStatus = __('Add');
                                endif; 
                         endif; 
                        $userArrays[] = array (
                            'url' => $user['User']['moo_href'],
                            'type' => $user['User']['moo_type'],
                            'id' => $user['User']['id'],
                            'image' => array(
                                        '50_square' => $this->Moo->getItemPhoto(array('User' => $user['User']),array( 'prefix' => '50_square'),array(),true),
                                        '100_square' => $this->Moo->getItemPhoto(array('User' => $user['User']),array( 'prefix' => '100_square'),array(),true),
                                        '200_square' => $this->Moo->getItemPhoto(array('User' => $user['User']),array( 'prefix' => '200_square'),array(),true),
                                        '600' => $this->Moo->getItemPhoto(array('User' => $user['User']),array( 'prefix' => '600'),array(),true),
                                        "type" =>  "Link",
                                        "mediaType" => "image/jpeg",
                                ),
                            'name' => $user['User']['name'],
                            'friendCount' => __n('%s friend', '%s friends', $user['User']['friend_count'], $user['User']['friend_count']),
                            'photoCount' => __n('%s photo', '%s photos', $user['User']['photo_count'], $user['User']['photo_count']) ,
                            'status' => $userStatus,
                            );

                    endif; 
                 endforeach; 
            
                
            endif;
            
            
            //respond data 
            $items = array(
                'userList' => $userArrays,
            );
            break;
        case 'Photo_Photo':  
            $photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
            $friendModel = MooCore::getInstance()->getModel('Friend');
            $photoModel = MooCore::getInstance()->getModel('Photo_Photo');
            $shareContent = $photoArray = array ();
            $feedTitle = $activityText = '';
            if($data['Activity']['type'] == 'User' || $data['Activity']['type'] == 'user' ) : 
                $activityText = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $activityText = array ();
                    $activityText = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $tagUser = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                endif;
                
                if($data['Activity']['action'] == 'photos_tag') :
                    $type = 'post';$objecttType='Photo_Tag';
                    $title =  $data['User']['name'] . ' ' . __('was tagged in a photo');
                endif;
                if($data['Activity']['action'] == 'comment_add_photo') :
                    $type = 'post';$objecttType='Photo_Comment';
                    $title =  $data['User']['name'] . ' ' . __('commented on %s photo', possession( $data['User'],  $object['User'] ) );
                endif;
                if($data['Activity']['action'] == 'photo_item_detail_share'):
                    $type = 'share';$objecttType='Photo';
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                            $photo = $photoModel->findById($data['Activity']['parent_id']);
                            
                            $title = $data['User']['name'] . ' ' .__("shared %s's photo",  $photo['User']['name']);
                            
                            $target = array(
                            'url' => $photo['User']['moo_href'],
                            'id' =>  $photo['User']['id'],
                            'name' =>  $photo['User']['name'],
                            'type' =>  'User',
                            );
                        endif;
                    if ($data['Activity']['target_id']): 
                            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);

                            list($plugin, $name) = mooPluginSplit($data['Activity']['type']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);

                             if ($show_subject):
                            $photoArray  = array(
                                'url' => $subject[$name]['moo_href'] ,
                                'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                                );
                            endif;
                    endif;
                endif;
            endif;
                    if(!empty($data['Activity']['items'])) :
                        $ids = explode(',', $data['Activity']['items']);
                        $photos_total = $photoModel->find('all', array('conditions' => array('Photo.id' => $ids)));
                        $photos = $photoModel->find('all', array('conditions' => array('Photo.id' => $ids),
                            'limit' => 4
                                ));
                        $c = 1 ;
                        $imageArray = array();
                         foreach ( $photos as $photo ):

                             if($c <= count($photos)) :
                                $imageArray [$c] = $photoHelper->getImage($photo, array('prefix' => '150_square'));
                                $c++;
                             endif;
                         endforeach;
                    elseif($data['Activity']['action'] == 'photo_item_detail_share')  :
                        $photos_total = 1;
                        $photo = $photoModel->findById($data['Activity']['parent_id']);
                        $imageArray = $photoHelper->getImage($photo, array('prefix' => '850'));
                    endif;
                    

                    $items = array(
                        'type' => $objecttType,
                        'id' => $activityId ,
                        'url' => FULL_BASE_URL . $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'users',
                                    'action' => 'view',
                                    $data['User']['id'],
                                    'activity_id' => $activityId
                                )),
                        'content' => $activityText ? $activityText : '' ,
                        'imageCount' => count($photos_total),
                        'imageArray' => $imageArray,
                        'tagUser' => $tagUser ? $tagUser : '',
                    );


            break;
        case 'Photo_Album':  
            $photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
            $friendModel = MooCore::getInstance()->getModel('Friend');
            $photoModel = MooCore::getInstance()->getModel('Photo_Photo');
            $imageArray = $shareContent = $photoArray = array ();
            $url = $feedTitle = $activityText = '' ;
            
            if($data['Activity']['type'] == 'User' || $data['Activity']['type'] == 'user' ) : 
                $activityText = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $activityText = array ();
                    $activityText = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $tagUser = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                endif;

                if($data['Activity']['action'] == 'wall_post' || $data['Activity']['action'] == 'photos_add') :
                    
                    if($data['Activity']['action'] == 'wall_post') :
                        $type = 'post';$objecttType='Album';
                        if ($data['Activity']['target_id']):
                            $type = 'post';$objecttType='Activty_Post_User';
                        endif;
                    endif;

                    if($data['Activity']['action'] == 'photos_add') :
                        $type = 'add';$objecttType='Album';
                        if ($data['Activity']['target_id']):
                            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);
                            $name = key($subject);
                            if ($show_subject):
                                $title = array(
                                    'url' => $subject[$name]['moo_href'] ,
                                    'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                                );
                            else:

                                $number = count(explode(',', $data['$data']['items'])); 
                                if ($number > 1) :
                                   $title =  __('added %s new photos', $number);
                                else :
                                    $title = __('added %s new photo', $number);
                                endif;
                            endif;
                        else: 
                                list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);
                               if ($object): 	
                                   $number = count(explode(',', $data['Activity']['items']));
                                   if ($number > 1) :
                                       $title =  __('added %s new photos to album', $number);
                                   else :
                                       $title =  __('added %s new photo to album', $number);
                                   endif;
                                   $title .= ' ' . h($object[$name]['moo_title']) ;
                                   //$feedTitle['url'] = $object[$name]['moo_href'];
                                endif; 
                        endif; 
                    endif;
              
                    
                    $target = array (
                            'id' => $object ["Album"]['id'],
                            'url' => FULL_BASE_URL . $this->request->base."/albums/view/".$object['Album']['id']."/".seoUrl($object['Album']['title']),
                            'type' => "Album",
                        );
                endif;
                    

                if($data['Activity']['action'] == 'wall_post_share' || $data['Activity']['action'] == 'photos_add_share' ) : 
                        $type = 'share';$objecttType='Activty';
                        $activityId = $data['Activity']['parent_id'];
                            if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                                list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);
                                $activityModel = MooCore::getInstance()->getModel('Activity');
                                $parentFeed = $activityModel->findById($data['Activity']['parent_id']);

                                $title = __("shared %s's post",  $parentFeed['User']['name'] ) ;
                                $target = array(
                                'url' => $parentFeed['User']['moo_href'],
                                'id' =>  $parentFeed['User']['id'],
                                'type' =>  'User',
                                );
                             endif;
                endif;
                    
                $url = FULL_BASE_URL . $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'users',
                                    'action' => 'view',
                                    $data['User']['id'],
                                    'activity_id' => $activityId
                                ));
                    
                if($data['Activity']['action'] == 'album_item_detail_share'):
                        $type = 'share';$objecttType='Album';
                        if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                            $albumModel = MooCore::getInstance()->getModel('Photo_Album');
                            $album = $albumModel->findById($data['Activity']['parent_id']);
                            
                            $title = $data['User']['name'] . ' ' .__("shared %s's album",  $album['User']['name']);
                            
                            $target = array(
                            'url' => $album['User']['moo_href'],
                            'id' =>  $album['User']['id'],
                            'name' =>  $album['User']['name'],
                            'type' =>  'User',
                            );
                            $activityId = $album['Album']['id'];
                            $url = FULL_BASE_URL . $album['Album']['moo_href'];
                            $photos_total = $photoModel->find('all', array('conditions' => array('Photo.type' => 'Photo_Album', 'Photo.target_id' => $album['Album']['id'])));
                        endif;
                        if ($data['Activity']['target_id']): 
                            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);

                            list($plugin, $name) = mooPluginSplit($data['Activity']['type']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);

                             if ($show_subject):
                            $photoArray  = array(
                                'url' => $subject[$name]['moo_href'] ,
                                'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                                );

                            endif;
                        endif; 
                endif;    
            endif;
            
            if($data['Activity']['type'] == 'Group_Group' || $data['Activity']['type'] == 'Event_Event') :
                        $type = 'post';$objecttType='Album';
                            $activityText = $data['Activity']['content'];
                                if (!empty($data['UserTagging']['users_taggings'])) : 
                                    $activityText = array ();
                                    $activityText['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                                    $activityText['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                                endif;
                        if ($data['Activity']['target_id']): 
                                $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);

                                list($plugin, $name) = mooPluginSplit($data['Activity']['type']);
                                $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);

                                if ($show_subject):
                                    $title  =  $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ;
                                endif; 
                                 $target = array (
                                    "id" => $subject[$name]['id'],
                                    "url" => $subject[$name]['moo_href'],
                                    "name" => $subject[$name]['moo_title'],
                                    "type" => $subject[$name]['moo_plugin'],
                                  );
                                $url = FULL_BASE_URL . $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'users',
                                    'action' => 'view',
                                    $data['User']['id'],
                                    'activity_id' => $activityId
                                ));
                        endif;

                    endif;

            if(!empty($data['Activity']['items'])) :
                        $ids = explode(',', $data['Activity']['items']);
                        $photos_total = $photoModel->find('all', array('conditions' => array('Photo.id' => $ids)));
                        $photos = $photoModel->find('all', array('conditions' => array('Photo.id' => $ids),
                            'limit' => 4
                                ));
                        $c = 1 ;
                        $imageArray = array();
                         foreach ( $photos as $photo ):

                             if($c <= count($photos)) :
                                $imageArray [$c] = $photoHelper->getImage($photo, array('prefix' => '150_square'));
                                $c++;
                             endif;
                         endforeach;
                    elseif($data['Activity']['action'] == 'photo_item_detail_share')  :
                        $photos_total = 1;
                        $photo = $photoModel->findById($data['Activity']['parent_id']);
                        $imageArray = $photoHelper->getImage($photo, array('prefix' => '850'));
                    endif;
                    
                    
            $items = array(
                        'type' => $objecttType,
                        'id' => $activityId ,
                        'url' => $url,
                        'content' => $activityText ? $activityText : '' ,
                        'imageCount' => count($photos_total),
                        'imageArray' => $imageArray,
                        'tagUser' => $tagUser ? $tagUser : '',
            );


        break;
            
        case 'Event_Event': 
                $eventHelper = MooCore::getInstance()->getHelper('Event_Event');
                $eventModel = MooCore::getInstance()->getModel('Event_Event');
                $eventArray = array ();
                $shareContent = array ();
                
                if($data['Activity']['action'] == 'event_create') :
                    $type = 'create';$objecttType='Event';
                    $title =  __('created a new event');
                    $eventArray = array (
                        'type' => $objecttType,
                         'id' => $object ["Event"]['id'],
                        'url' => FULL_BASE_URL . $this->Html->url(array(
                                        'plugin' => false,
                                        'controller' => 'events',
                                        'action' => 'view',
                                        $object['Event']['id'],
                                        seoUrl($object['Event']['title']),
                                    )),
                    );

                endif;
                
                if($data['Activity']['action'] == 'event_attend') :
                    $type = 'attend';$objecttType='Event';
                    $ids = explode(',', $data['Activity']['items']);
                    $events = $eventModel->find('all', array('conditions' => array('Event.id' => $ids)
                        ));

                    $attending1 = '%s';
                    $attending2 = '%s and %s';
                    $attending3 = '%s and %s';
                    $attending = '';
                    switch (count($events)):
                    case 1:
                        $attending = sprintf($attending1, h($events[0]['Event']['title']) );
                        break;
                    case 2:
                        $attending = sprintf($attending2, h($events[0]['Event']['title']) , h($events[1]['Event']['title'])) ;
                        break;
                    case 3:
                    default :
                        $attending = sprintf($attending2, h($events[0]['Event']['title']) , abs(count($events) - 1) . ' ' . __('others'));
                        break;
                    endswitch;

                    $title = $data['User']['name'] . ' '. __('is attending') . ' ' .  $attending;
                    $eventArray['type'] =$objecttType;
                    foreach ( $events as $event ):
                        $eventArray['event'][] = array(
                            'url' => $event['Event']['moo_href'] ,            
                            'name' => h($event['Event']['moo_title']),
                            'id' => $event['Event']['id'],

                        );
                    endforeach;
                endif;
                
                
                if($data['Activity']['action'] == 'event_create_share' || $data['Activity']['action'] == 'event_item_detail_share' ) : 
                    
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']):
                        
                        
                        if($data['Activity']['action'] == 'event_item_detail_share') :
                            $type = 'share';$objecttType='Event';
                            $event = $eventModel->findById($data['Activity']['parent_id']);
                            
                             $title = array (
                                'title' => __("shared %s's event",  $event['User']['name'] ),
                                'linkUser' => $event['User']['moo_href'],
                                'linkPost' => $event['Event']['moo_href'] ,
                                );
                             $target = array(
                                'url' => $event['User']['moo_href'],
                                'id' =>  $event['User']['id'],
                                'name' =>  $event['User']['name'],
                                'type' =>  'User',
                                );
                            $eventArray = array (
                                    'type' => $objecttType,
                                     'id' => $event["Event"]['id'],
                                    'url' => FULL_BASE_URL . $this->Html->url(array(
                                                    'plugin' => false,
                                                    'controller' => 'events',
                                                    'action' => 'view',
                                                    $event['Event']['id'],
                                                    seoUrl($event['Event']['title']),
                                                )),
                                );
                        else:
                            $type = 'share';$objecttType='Activity';
                            $activityModel = MooCore::getInstance()->getModel('Activity');
                            $parentFeed = $activityModel->findById($data['Activity']['parent_id']);
                            list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);
                            $event = MooCore::getInstance()->getItemByType($parentFeed['Activity']['item_type'], $parentFeed['Activity']['item_id']);
                            
                            $title =  __("shared %s's post",  $parentFeed['User']['name'] );  
                            $target = array(
                                'url' => $parentFeed['User']['moo_href'],
                                'id' =>  $parentFeed['User']['id'],
                                'name' =>  $parentFeed['User']['name'],
                                'type' =>  'User',
                                );
                            $eventArray = array (
                                'type' => $objecttType,
                                 'id' => $parentFeed["Activity"]['id'],
                                'url' => FULL_BASE_URL . $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'users',
                                    'action' => 'view',
                                    $data['User']['id'],
                                    'activity_id' => $parentFeed["Activity"]['id'],
                                )),
                            );
                        endif;
                        
                        
                    endif;
                    $eventArray['content'] = $data['Activity']['content'];
                    if (!empty($data['UserTagging']['users_taggings'])) : 
                        $eventArray['content'] = array ();
                        $eventArray['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                        $eventArray['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']); 
                    endif;
                endif;
                
                $items = $eventArray ? $eventArray : '';
                
            break;
            
            case 'Video_Video':
                $videoHelper = MooCore::getInstance()->getHelper('Video_Video');
                $videoArray = array ();

                    
                    if($data['Activity']['action'] == 'video_create') :
                        $type = 'create';$objecttType='Video';
                        $videoArray = array (
                            'type' => $objecttType,
                             'id' => $object["Video"]['id'],
                        );
                        if ( !empty( $object['Video']['group_id'] ) ) :
                            $videoArray['url'] = FULL_BASE_URL . $this->request->base .'/groups/view/'. $object['Video']['group_id'] .'/video_id:'.$object['Video']['id'] ;
                        else:
                            $videoArray['url'] = FULL_BASE_URL . $this->request->base .'/videos/view/'. $object['Video']['id'] . '/'. seoUrl($object['Video']['title']) ;
                        endif;
                    
                        $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
                        $name = key($subject);
                        if ($data['Activity']['target_id']): 
                            list($plugin, $name) = mooPluginSplit($data['Activity']['type']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject); 
                            if($data['Activity']['type'] == 'Group_Group') :
                                if ($show_subject): 
                                   $title  =  $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ;
                                else:
                                   $title =  __('shared a new video'); 
                               endif;
                               $target = array(
                                    'url' => FULL_BASE_URL . $subject[$name]['moo_href'],
                                    'id' =>  $subject[$name]['id'],
                                    'name' =>  $subject[$name]['moo_title'],
                                    'type' =>  'Group',
                                );
                            endif;
 
                        else:
                            $title = __('shared a new video'); 
                        endif;

                    endif;
                    
                    if($data['Activity']['action'] == 'video_create_share') : 
                        $type = 'share';$objecttType='Activity';
                        $activityModel = MooCore::getInstance()->getModel('Activity');
                        $parentFeed = $activityModel->findById($data['Activity']['parent_id']);
                        if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                            list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);

                            $title =  __("shared %s's post",  $parentFeed['User']['name'] )  ;
                            $videoArray = array (
                                'type' => $objecttType,
                                 'id' => $data['Activity']['parent_id'],
                                 'url' => FULL_BASE_URL . $this->Html->url(array(
                                            'plugin' => false,
                                            'controller' => 'users',
                                            'action' => 'view',
                                            $parentFeed['User']['id'],
                                            'activity_id' => $data['Activity']['parent_id']
                                        )),
                            );
                            $target = array(
                                'url' => $parentFeed['User']['moo_href'],
                                'id' =>  $parentFeed['User']['id'],
                                'name' =>  $parentFeed['User']['name'],
                                'type' =>  'User',
                                );
                         endif;
                    endif;
                    
                    if($data['Activity']['action'] == 'video_item_detail_share' ) : 
                        $type = 'share';$objecttType='Video';
                        if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                            $videoModel = MooCore::getInstance()->getModel('Video_Video');
                            $video = $videoModel->findById($data['Activity']['parent_id']);

                            $title = $data['User']['name'] . ' '.  __("shared %s's video",  $video['User']['name'] );
                            $target = array(
                                'url' => $video['User']['moo_href'],
                                'id' =>  $video['User']['id'],
                                'name' =>  $video['User']['name'],
                                'type' =>  'User',
                                );
                            $videoArray = array (
                                    'type' => $objecttType,
                                     'id' => $video["Video"]['id'],
                                    'url' => FULL_BASE_URL . $video['Video']['moo_href'] ,
                                );
                         endif;

                    endif;
                    $videoArray['content'] = $data['Activity']['content'];
                        if (!empty($data['UserTagging']['users_taggings'])) : 
                            $videoArray['content'] = array ();
                            $videoArray['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                            $videoArray['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                    endif;

                    $items =  $videoArray ? $videoArray : '' ;
              
            break;
            
            
        case 'Topic_Topic': 
            
            $topicHelper = MooCore::getInstance()->getHelper('Topic_Topic');
            $topicArray = array ();
            $shareContent = array ();
            $title = '';
            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
            $name = key($subject);
            
            if($data['Activity']['action'] == 'topic_create') :
                 $type = 'create';$objecttType='Topic';
                        

                if ($data['Activity']['target_id']): 

                     $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject); 

                     if ($show_subject): 
                        $title = array(
                                'url' => $subject[$name]['moo_href'],
                                'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                            );
                     else:
                        $title =  __('created a new topic'); 
                    endif;
                    $target = array(
                                    'url' => FULL_BASE_URL . $subject[$name]['moo_href'],
                                    'id' =>  $subject[$name]['id'],
                                    'name' =>  $subject[$name]['moo_title'],
                                    'type' =>  'Group',
                                );
                else:
                    $title = __('created a new topic'); 
                endif;
                $topicArray = array (
                        'id' => $object["Topic"]['id'],
                        'type' => $objecttType,
                        'url' => FULL_BASE_URL.$this->request->base."/topics/view/".$object['Topic']['id']."/".seoUrl($object['Topic']['title']),

                );
            endif; 
                             
            if($data['Activity']['action'] == 'topic_create_share') :
                    $type = 'share';$objecttType='Activity';
                    
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                        list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);
                        $activityModel = MooCore::getInstance()->getModel('Activity');
                        $parentFeed = $activityModel->findById($data['Activity']['parent_id']);
                        
                        $title =  __("shared %s's post",  $parentFeed['User']['name'] )  ;
                        $topicArray = array (
                                'type' => $objecttType,
                                 'id' => $data['Activity']['parent_id'],
                                 'url' => FULL_BASE_URL . $this->Html->url(array(
                                            'plugin' => false,
                                            'controller' => 'users',
                                            'action' => 'view',
                                            $parentFeed['User']['id'],
                                            'activity_id' => $data['Activity']['parent_id']
                                        )),
                        );
                        $target = array(
                            'url' => $parentFeed['User']['moo_href'],
                            'id' =>  $parentFeed['User']['id'],
                            'name' =>  $parentFeed['User']['name'],
                            'type' =>  'User',
                        );
                     endif;
            endif;
                
            if($data['Activity']['action'] == 'topic_item_detail_share' ) : 
                    $type = 'share';$objecttType='Topic';
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                        $topicModel = MooCore::getInstance()->getModel('Topic_Topic');
                        $topic = $topicModel->findById($data['Activity']['parent_id']);
                        
                        $title =  $data['User']['name'] . ' '.  __("shared %s's topic",  $topic['User']['name']) ;
                        $target = array(
                                'url' => $topic['User']['moo_href'],
                                'id' =>  $topic['User']['id'],
                                'name' =>  $topic['User']['name'],
                                'type' =>  'User',
                                );
                        $topicArray = array (
                                    'type' => $objecttType,
                                     'id' => $topic["Topic"]['id'],
                                    'url' => FULL_BASE_URL . $topic['Topic']['moo_href'],
                                );
                     endif;

                endif;
                $topicArray['content'] = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $topicArray['content'] = array ();
                    $topicArray['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $topicArray['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                endif;
                
                $items =  $topicArray ? $topicArray : '' ;
         
            break;
        case 'Blog_Blog':
            $blogHelper = MooCore::getInstance()->getHelper('Blog_Blog');
            $blogModel = MooCore::getInstance()->getModel('Blog_Blog');
            $blogArray = array();
            if($data['Activity']['action'] == 'blog_create') :
                    $type = 'create';$objecttType='Blog';
                    $title =  __('created a new blog entry');
                    $blogArray = array (
                        'id' => $object["Blog"]['id'],
                        'type' => $objecttType,
                        'url' => FULL_BASE_URL.$this->request->base."/blogs/view/".$object['Blog']['id']."/".seoUrl($object['Blog']['title']),
                    );
            endif;
                
                if($data['Activity']['action'] == 'blog_create_share') :
                    $type = 'share';$objecttType='Activity';
                    
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                        list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);
                        $activityModel = MooCore::getInstance()->getModel('Activity');
                        $parentFeed = $activityModel->findById($data['Activity']['parent_id']);
                        
                        $title =  __("shared %s's post",  $parentFeed['User']['name'] )  ;
                        $blogArray = array (
                                'type' => $objecttType,
                                 'id' => $data['Activity']['parent_id'],
                                 'url' => FULL_BASE_URL . $this->Html->url(array(
                                            'plugin' => false,
                                            'controller' => 'users',
                                            'action' => 'view',
                                            $parentFeed['User']['id'],
                                            'activity_id' => $data['Activity']['parent_id']
                                        )),
                        );
                        $target = array(
                            'url' => $parentFeed['User']['moo_href'],
                            'id' =>  $parentFeed['User']['id'],
                            'name' =>  $parentFeed['User']['name'],
                            'type' =>  'User',
                        );
                     endif;
                endif;
                
                if($data['Activity']['action'] == 'blog_item_detail_share' ) :  
                    $type = 'share';$objecttType='Blog';
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                        $blog = $blogModel->findById($data['Activity']['parent_id']);
                        
                        $title =  $data['User']['name'] . ' '.  __("shared %s's blog",  $blog['User']['name']) ;
                        $target = array(
                                'url' => $blog['User']['moo_href'],
                                'id' =>  $blog['User']['id'],
                                'name' =>  $blog['User']['name'],
                                'type' =>  'User',
                                );
                        $blogArray = array (
                                    'type' => $objecttType,
                                     'id' => $blog["Blog"]['id'],
                                    'url' => FULL_BASE_URL . $blog['Blog']['moo_href'],
                                );
                     endif;

                endif;
                    
                $blogArray['content'] = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $blogArray['content'] = array ();
                    $blogArray['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $blogArray['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                endif;
                
                $items =  $blogArray ? $blogArray : '' ;
       
        break;
        case 'Group_Group':
            $groupHelper = MooCore::getInstance()->getHelper('Group_Group');
            $groupModel = MooCore::getInstance()->getModel('Group_Group');
            $groupArray = array ();
            $shareContent = array ();
            $joinContent = array ();
                    
                if($data['Activity']['action'] == 'group_create') :
                    $type = 'create';$objecttType='Group';
                    $title =  __('created a new group');
                    $groupArray = array (
                        'id' => $object["Group"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/groups/view/".$object['Group']['id']."/".seoUrl($object['Group']['name']),
                        'type' => $objecttType,
                    );
                endif;


                
                if($data['Activity']['action'] == 'group_join') :
                    $type = 'join';$objecttType='Group';
                    $ids = explode(',', $data['Activity']['items']);
                    $groupModel->cacheQueries = true;
                    $groups = $groupModel->find('all', array('conditions' => array('Group.id' => $ids),
                            ));

                    $joined1 = '%s';
                    $joined2 = '%s and %s';
                    $joined3 = '%s and %s';
                    $joined = '';
                    switch (count($groups)){
                    case 1:
                        $joined = sprintf($joined1, h($groups[0]['Group']['name']) );
                        break;
                    case 2:
                        $joined = sprintf($joined2, h($groups[0]['Group']['name']) , h($groups[1]['Group']['name']) );
                        break;
                    case 3:
                    default :
                        $joined = sprintf($joined3, h($groups[0]['Group']['name']) , abs(count($groups) - 1) . ' ' . __('others'));
                        break;
                    }

                    $title = $data['User']['name'] . ' '. __('joined group') . ' ' .  $joined;
                    $groupArray['type'] = $objecttType;
                    foreach ( $groups as $group ):
                        $groupArray['group'][] = array(
                            'id' => $group['Group']['id'],
                            'url' => $group['Group']['moo_href'],
                            'name' => h($group['Group']['moo_title']),
                            'type' => h($group['Group']['moo_plugin']),
                        );
                    endforeach;
                endif;
                
                if($data['Activity']['action'] == 'group_create_share' ) :
                    $type = 'share';$objecttType='Activity';
                    $activityModel = MooCore::getInstance()->getModel('Activity');
                    $parentFeed = $activityModel->findById($data['Activity']['parent_id']);
                    if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id']): 
                        list($plugin, $name) = mooPluginSplit($data['Activity']['item_type']);

                        $title = array (
                            'title' => __("shared %s's group",  $parentFeed['User']['name']   

                            ),
                            'linkUser' => $parentFeed['User']['moo_href'],
                            'linkPost' => FULL_BASE_URL . $this->Html->url(array(
                                        'plugin' => false,
                                        'controller' => 'users',
                                        'action' => 'view',
                                        $parentFeed['User']['id'],
                                        'activity_id' => $data['Activity']['parent_id']
                                    )),
                            );
                        $groupArray = array (
                                'type' => $objecttType,
                                 'id' => $data['Activity']['parent_id'],
                                 'url' => FULL_BASE_URL . $this->Html->url(array(
                                            'plugin' => false,
                                            'controller' => 'users',
                                            'action' => 'view',
                                            $parentFeed['User']['id'],
                                            'activity_id' => $data['Activity']['parent_id']
                                        )),
                        );
                        $target = array(
                            'url' => $parentFeed['User']['moo_href'],
                            'id' =>  $parentFeed['User']['id'],
                            'name' =>  $parentFeed['User']['name'],
                            'type' =>  'User',
                        );
                     endif;
                     
                endif;
                if($data['Activity']['action'] == 'group_item_detail_share' ) :
                    $type = 'share';$objecttType='Group';
                    $group = $groupModel->findById($data['Activity']['parent_id']);
                    $title =  $data['User']['name'] . ' '.  __("shared %s's group",  $group['User']['name']) ;
                    $target = array(
                                'url' => $group['User']['moo_href'],
                                'id' =>  $group['User']['id'],
                                'name' =>  $group['User']['name'],
                                'type' =>  'User',
                    );
                    $groupArray = array (
                                    'type' => $objecttType,
                                     'id' => $group["Group"]['id'],
                                    'url' => FULL_BASE_URL . $group['Group']['moo_href'],
                    );
                endif;
                
                $groupArray['content'] = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $groupArray['content'] = array ();
                    $groupArray['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $groupArray['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                endif;

                    $items =  $groupArray ? $groupArray : '' ;
       
            break;
    }
    if(isset($data['ActivityComment'])) :
        $commentCount = count($data['ActivityComment']);
    endif;
    if (isset($data['PhotoComment'])) : 
        $commentCount = count($data['PhotoComment']);
    endif;
    if (isset($data['ItemComment'])) : 
        $commentCount = count($data['ItemComment']);
    endif;
    $feed[$i]['items'] = array(
        'typea' => $data['Activity']['item_type'],
        'action' => $data['Activity']['action'],
        'id' => $data['Activity']['id'],
        'published' => $data['Activity']['created'],
        'privacy' => $data['Activity']['privacy'],
        'likeCount' => $data['Activity']['like_count'],
        'dislikeCount' => $data['Activity']['dislike_count'],
        'commentCount' => $commentCount ,
        'actor' => array (
                'url' => $data['User']['moo_href'],
                'type' => $data['User']['moo_type'],
                'id' => $data['User']['id'],
                'image' => array(
                            '50_square' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '50_square'),array(),true),
                            '100_square' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '100_square'),array(),true),
                            '200_square' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '200_square'),array(),true),
                            '600' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '600'),array(),true),
                            "type" =>  "Link",
                            "mediaType" => "image/jpeg",
                    ),
                'name' => $data['User']['name'],
            ),
        'title' => $title,
        'type' => $type,
        'objects' => $items,
        'target' => $target,
    
     
);
endforeach;

echo json_encode($feed);