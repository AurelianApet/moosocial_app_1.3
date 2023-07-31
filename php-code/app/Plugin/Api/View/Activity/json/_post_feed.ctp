<?php
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
   
    switch ($data['Activity']['item_type']) {
        case '' :
            $activityText = $title = '';
            $feedLink = $imageArray = array ();
                        $activityText = $data['Activity']['content'];
                        if (!empty($data['UserTagging']['users_taggings'])) : 
                            $activityText = array ();
                            $activityText['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                            $activityText['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                        endif;
 
               if($data['Activity']['action'] == 'wall_post_link') :
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
               

            $items = array(
                'feedTtile' => $title,
                'feedLink' => $feedLink ? $feedLink : '',
                'feedContent' => $activityText ? $activityText : '',
                'likeCount' => $data['Activity']['like_count'],
                'dislikeCount' => $data['Activity']['dislike_count'],
                'imageArray' => $imageArray ? $imageArray : '',

            );
            break;
            

        case 'Photo_Photo': 
        case 'Photo_Album': 
            $photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
            $friendModel = MooCore::getInstance()->getModel('Friend');
            $photoModel = MooCore::getInstance()->getModel('Photo_Photo');
            $photoArray = array ();
            $feedTitle = $activityText = '';
            $photos_total = 0;
            if($data['Activity']['type'] == 'User' || $data['Activity']['type'] == 'user' ) : 
                $activityText = $data['Activity']['content'];
                if (!empty($data['UserTagging']['users_taggings'])) : 
                    $activityText = array ();
                    $activityText['content'] = $data['Activity']['content'] . $this->MooPeople->getWithNotUrl($data['UserTagging']['id'], $data['UserTagging']['users_taggings'],false);
                    $activityText['tagUser'] = $this->MooPeople->getUserTagged($data['UserTagging']['users_taggings']);
                endif;
                
                    $photoArray = array (
                        'id' => $object ["Album"]['id'],
                        'url' => FULL_BASE_URL.$this->request->base."/albums/view/".$object['Album']['id']."/".seoUrl($object['Album']['title']),
                        'title_1' => h($this->Text->truncate($object ["Album"]['title'], 40)),
                        'created' => $object ["Album"]['created'],
                        'type' => "Album",
                    );
 
            endif;
            if($data['Activity']['type'] == 'Group_Group' || $data['Activity']['type'] == 'Event_Event') :
    
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
                        $photoArray  = array(
                            'url' => $subject[$name]['moo_href'] ,
                            'title' => $data['User']['name'] . ' > '  . h($subject[$name]['moo_title']) ,
                            );
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

                             if($c <= count($photos)) {
                                $imageArray [$c] = $photoHelper->getImage($photo, array('prefix' => '150_square'));
                                $c++;
                             }
                         endforeach;
                    
                    endif;
                    

                    $items = array(
                        'feedTitle' => $feedTitle ? $feedTitle : '' ,
                        'feedContent' => $activityText ? $activityText : '' ,
                        'photoContent' => $photoArray ? $photoArray : '' ,
                        'likeCount' => $data['Activity']['like_count'],
                        'dislikeCount' => $data['Activity']['dislike_count'],
                        'photoCount' => count($photos_total),
                        'photoArray' => $imageArray,
                    );


            break;
    }

    $feed['items'] = array(
        'dataType' => $data['Activity']['item_type'],
        'dataPlugin' => $data['Activity']['plugin'],
        'published' => $this->Moo->getTime( $data['Activity']['created'], Configure::read('core.date_format'), $utz ),
        'privacy' => $data['Activity']['privacy'],
        'actor' => array (
                'url' => $data['User']['moo_href'],
                'objectType' => $data['User']['moo_type'],
                'id' => $data['User']['id'],
                'image' => array(
                            '100' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '100_square'),array(),true),
                            '200' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '200'),array(),true),
                            '600' => $this->Moo->getItemPhoto(array('User' => $data['User']),array( 'prefix' => '600'),array(),true),
                    ),
                'displayName' => $data['User']['name'],
            ),
        'verb' => $data['Activity']['action'],
        'objects' => $items,
    
);

if(empty($feed) ) {
    $feed = array('Warning' => 'There are no new feeds to view at this time.');
}
echo json_encode($feed);