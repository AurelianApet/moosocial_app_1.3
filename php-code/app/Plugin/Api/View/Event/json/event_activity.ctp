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
                        $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);

                        list($plugin, $name) = mooPluginSplit($data['Activity']['type']);
                        $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);

                        if ($show_subject):
                        $title = array(
                            'url' => $subject[$name]['moo_href'] ,
                            'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                            );
                         endif;  
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
            
            if($data['Activity']['action'] == 'photos_add') :
                    
                    if($data['Activity']['action'] == 'wall_post') :
                        $type = 'post';$objecttType='Album';
                        if ($data['Activity']['target_id']): 
                            $type = 'post';$objecttType='Activty_Post_User';
                            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);
                            $name = key($subject);
                            if ($show_subject): 
                                $title =  $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']);
                            endif;
                        endif; 
                    endif;

                    if($data['Activity']['action'] == 'photos_add') :
                        $type = 'add';$objecttType='Album';
                        if ($data['Activity']['target_id']):
                            $type = 'post';$objecttType='Activty_Post_User';
                            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);
                            $name = key($subject);
                            if ($show_subject): 
                                $title = array(
                                    'url' => $subject[$name]['moo_href'] ,
                                    'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                                );
                            else:
                                $number = count(explode(',', $data['Activity']['items'])); 
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
              
                    if($objecttType == 'Activty_Post_User'):
                        $target = array (
                            'id' => $subject[$name]['id'],
                            'name' => $subject[$name]['name'],
                            'url' => $subject[$name]['moo_href'],
                            'type' => $subject[$name]['moo_type'],
                        );
                    else:
                        $target = array (
                            'id' => $object ["Album"]['id'],
                            'url' => FULL_BASE_URL . $this->request->base."/albums/view/".$object['Album']['id']."/".seoUrl($object['Album']['title']),
                            'type' => "Album",
                        );
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
                            $subject = MooCore::getInstance()->getItemByType($data['Activity']['type'], $data['Activity']['target_id']);
                            $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject);
                            $name = key($subject);
                            if ($show_subject): 
                                $title =  $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']);
                            endif;
                        endif; 
                    endif;

                    if($data['Activity']['action'] == 'photos_add') :
                        $type = 'add';$objecttType='Album';
                        if ($data['Activity']['target_id']): 
                            $type = 'post';$objecttType='Activty_Post_User';
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
              
                    if($objecttType == 'Activty_Post_User'):
                        $target = array (
                            'id' => $subject[$name]['id'],
                            'name' => $subject[$name]['name'],
                            'url' => $subject[$name]['moo_href'],
                            'type' => $subject[$name]['moo_type'],
                        );
                    else:
                        $target = array (
                            'id' => $object ["Album"]['id'],
                            'url' => FULL_BASE_URL . $this->request->base."/albums/view/".$object['Album']['id']."/".seoUrl($object['Album']['title']),
                            'type' => "Album",
                        );
                    endif;
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
        //'typea' => $data['Activity']['item_type'],
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