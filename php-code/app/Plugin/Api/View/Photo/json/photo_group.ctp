<?php

$photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
$friendModel = MooCore::getInstance()->getModel('Friend');
$photoModel = MooCore::getInstance()->getModel('Photo_Photo');
foreach ($photos as $photo): //echo '<pre>';print_r($photo);die; 
    $photoArray[] = array(
        'id' => $photo['Photo']['id'],
        'create' => h($photo['Photo']['created']),
        'caption' => $photo['Photo']['caption'],
        'thumbnail' => array(
                '150_square' => $photoHelper->getImage($photo, array('prefix' => '150_square')),
                '450' => $photoHelper->getImage($photo, array('prefix' => '450')),
            ), 
        'privacy' => $photo['Photo']['privacy'],
        'type' => $photo['Photo']['type'],
        'url' => FULL_BASE_URL . $photo['Photo']['moo_href'],
        'comment_count' => $photo['Photo']['comment_count'],
        'like_count' => $photo['Photo']['like_count'],
        'dislike_count' => $photo['Photo']['dislike_count'],
        'user_id' => $photo['Photo']['user_id'],
        'user_name' => $photo['User']['name'],
    );
endforeach;
echo json_encode($photoArray);