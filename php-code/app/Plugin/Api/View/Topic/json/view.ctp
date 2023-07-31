<?php
$topicHelper = MooCore::getInstance()->getHelper('Topic_Topic');
    $topicArray = array(
        'id' => $topic['Topic']['id'],
        'title' => h($topic['Topic']['title']),
        'body' => $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $topic['Topic']['body']  , Configure::read('Topic.topic_hashtag_enabled') )),
        'thumbnail' => $topicHelper->getImage($topic, array('prefix' => '150_square')),
        'category_id' => $topic['Topic']['category_id'],
        'comment_count' => $topic['Topic']['comment_count'],
        'share_count' => $topic['Topic']['share_count'],
        'like_count' => $topic['Topic']['like_count'],
        'dislike_count' => $topic['Topic']['dislike_count'],
        'userId' => $topic['Topic']['user_id'],
        'userName' => $topic['User']['name'],
    );
echo json_encode($topicArray);