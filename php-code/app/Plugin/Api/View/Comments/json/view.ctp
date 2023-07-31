<?php
    $commentArray = array();

    if($type == 'activity') :
        
        if(isset($data['ActivityComment'])) :
            $commentcount = count($data['ActivityComment']);
        endif;
        if (isset($data['PhotoComment'])) : 
            $commentcount = count($data['PhotoComment']);
        endif;
        if (isset($data['ItemComment'])) : 
            $commentcount = count($data['ItemComment']);
        endif;
        
        if(!empty($data['ActivityComment'])) : 
            foreach ($data['ActivityComment'] as  $comment) :
                            $commentArray[] = array (
                                'id' => $comment['id'],
                                'userId' => $comment['user_id'],
                                'userName' => $comment['User']['name'],
                                'edited' => $comment['edited'],
                                'message' => $comment['comment'],
                                'likeCount' => $comment['like_count'],
                                'dislikeCount' => $comment['dislike_count'],
                                'created' => $comment['created'],
                                'thumnail' => $comment['thumbnail'] ? $this->Moo->getItemPhoto(array('ActivityComment'=>$comment), array('prefix' => '200'),array(),true) : '' ,
                            );
            endforeach;
        endif;
        if(!empty($data['PhotoComment'])) : 
                            foreach ($data['PhotoComment'] as  $comment) : 
                                $commentArray[] = array (
                                    'id' => $comment['Comment']['id'],
                                    'userId' => $comment['Comment']['user_id'],
                                    'userName' => $comment['User']['name'],
                                    'edited' => $comment['Comment']['edited'],
                                    'message' => $comment['Comment']['message'],
                                    'likeCount' => $comment['Comment']['like_count'],
                                    'dislikeCount' => $comment['Comment']['dislike_count'],
                                    'created' => $comment['Comment']['created'],
                                    'thumnail' => $comment['Comment']['thumbnail'] ? $this->Moo->getItemPhoto($comment, array('prefix' => '200'),array(),true) : '' ,
                                );
                           endforeach;
        endif;
        if(!empty($data['ItemComment'] )) : 
                            foreach ($data['ItemComment'] as  $comment) : 
                                $commentArray[] = array (
                                    'id' => $comment['Comment']['id'],
                                    'userId' => $comment['Comment']['user_id'],
                                    'userName' => $comment['User']['name'],
                                    'edited' => $comment['Comment']['edited'],
                                    'message' => $comment['Comment']['message'],
                                    'likeCount' => $comment['Comment']['like_count'],
                                    'dislikeCount' => $comment['Comment']['dislike_count'],
                                    'created' => $comment['Comment']['created'],
                                    'thumnail' => $comment['Comment']['thumbnail'] ? $this->Moo->getItemPhoto($comment, array('prefix' => '200'),array() , true) : '' ,
                                );
                           endforeach;
        endif;
        
    else:
        $commentcount = $data['comment_count'];
        foreach ($data['comments'] as $comment):
            $commentArray[] = array (
                                    'id' => $comment['Comment']['id'],
                                    'userId' => $comment['Comment']['user_id'],
                                    'userName' => $comment['User']['name'],
                                    'edited' => $comment['Comment']['edited'],
                                    'message' => $comment['Comment']['message'],
                                    'likeCount' => $comment['Comment']['like_count'],
                                    'dislikeCount' => $comment['Comment']['dislike_count'],
                                    'created' => $comment['Comment']['created'],
                                    'thumnail' => $comment['Comment']['thumbnail'] ? $this->Moo->getItemPhoto($comment, array('prefix' => '200'),array() , true) : '' ,
                                );
        endforeach;
        
    endif;
        
        $feed[]= array(
            'commentCount' => $commentcount,
            'commentObject' => $commentArray,
        );
echo json_encode($feed);