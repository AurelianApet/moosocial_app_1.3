<?php

$photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
$friendModel = MooCore::getInstance()->getModel('Friend');
$photoModel = MooCore::getInstance()->getModel('Photo_Photo'); //echo '<pre>';print_r($album['Photo']);die;
    $covert = '';
    if ($album['Album']['type'] == 'newsfeed' &&  $role_id != ROLE_ADMIN && $uid != $album['Album']['user_id'] && (!$uid || $friendModel->areFriends($uid,$album['Album']['user_id'])))  
    {    		
	$photo = $photoModel->getPhotoCoverOfFeedAlbum($album['Album']['id']);
            if ($photo)
	    {
                $covert = $photoHelper->getImage($photo, array('prefix' => '150_square'));
	    }
	    else
	    {
	    	$covert = $photoHelper->getAlbumCover('', array('prefix' => '150_square'));
	    }
    }
    else
    {
    	$covert = $photoHelper->getAlbumCover($album['Album']['cover'], array('prefix' => '150_square'));
    }
    $photoArray = array();
    foreach ($photoAlbums as $photoAlbum) : //echo '<pre>';print_r($photoAlbum);die;
        $photoArray[] = array (
            'id' => $photoAlbum['Photo']['id'],
            'create' => $photoAlbum['Photo']['created'],
            'target_id' => $photoAlbum['Photo']['target_id'],
            'type' => $photoAlbum['Photo']['type'],
            'thumbnail' => array(
                '150_square' => $photoHelper->getImage($photoAlbum, array('prefix' => '150_square')),
                '450' => $photoHelper->getImage($photoAlbum, array('prefix' => '450')),
            ), 
            'caption' => $photoAlbum['Photo']['caption'],
            'privacy' => $photoAlbum['Photo']['privacy'],
            'comment_count' => $photoAlbum['Photo']['comment_count'],
            'like_count' => $photoAlbum['Photo']['like_count'],
            'dislike_count' => $photoAlbum['Photo']['dislike_count'],
        );
    endforeach;
    
    $albumArray = array(
        'id' => $album['Album']['id'],
        'create' => h($album['Album']['created']),
        'title' => h($album['Album']['title']),
        'description' => $album['Album']['description'],
        'thumbnail' => $covert,
        'privacy' => $album['Album']['privacy'],
        'type' => $album['Album']['type'] ? $album['Album']['type'] : $album['Album']['moo_type'],
        'photo' => $photoArray ,
        'category_id' => $album['Album']['category_id'],
        'comment_count' => $album['Album']['comment_count'],
        'like_count' => $album['Album']['like_count'],
        'dislike_count' => $album['Album']['dislike_count'],
        'user_id' => $album['Album']['user_id'],
        'user_name' => $album['User']['name'],
    );
echo json_encode($albumArray);