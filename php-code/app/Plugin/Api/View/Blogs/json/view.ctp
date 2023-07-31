<?php
$blogHelper = MooCore::getInstance()->getHelper('Blog_Blog');
    $blogArray = array(
        'id' => $blog['Blog']['id'],
        'title' => h($blog['Blog']['title']),
        'body' => $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $blog['Blog']['body']  , Configure::read('Blog.blog_hashtag_enabled') )),
        'thumbnail' => $blogHelper->getImage($blog, array('prefix' => '150_square')),
        'privacy' => $blog['Blog']['privacy'],
        'category_id' => $blog['Blog']['category_id'],
        'comment_count' => $blog['Blog']['comment_count'],
        'share_count' => $blog['Blog']['share_count'],
        'like_count' => $blog['Blog']['like_count'],
        'dislike_count' => $blog['Blog']['dislike_count'],
        'userId' => $blog['Blog']['user_id'],
        'userName' => $blog['User']['name'],
    );
echo json_encode($blogArray);