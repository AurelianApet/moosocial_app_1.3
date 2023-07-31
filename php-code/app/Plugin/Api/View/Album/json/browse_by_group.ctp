<?php
$videoHelper = MooCore::getInstance()->getHelper('Video_Video');
foreach ($videos as $video):
    $videoArray[] = array(
        'id' => $video['Video']['id'],
        'create' => h($video['Video']['created']),
        'title' => h($video['Video']['title']),
        'description' => $video['Video']['description'],
        'thumbnail' => $videoHelper->getImage($video, array('prefix' => '150_square')),
        'video_type' => $video['Video']['source'],
        'privacy' => $video['Video']['privacy'],
        'category_id' => $video['Video']['category_id'],
        'comment_count' => $video['Video']['comment_count'],
        'like_count' => $video['Video']['like_count'],
        'dislike_count' => $video['Video']['dislike_count'],
        'user_id' => $video['Video']['user_id'],
        'user_name' => $video['User']['name'],
    );
endforeach;
echo json_encode($videoArray);