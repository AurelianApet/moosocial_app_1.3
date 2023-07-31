<?php

foreach ($histories as $history):
    $status ='';
    switch ($history['CommentHistory']['photo']) {
        case 1: $status =  __('Added photo attachment.');
	break;
	case 2: $status = __('Replaced photo attachment.');
	break;
        case 3: $status =  __('Deleted photo attachment.');
	break;						
    }
    $editObject[] = array (
        'id' => $history['CommentHistory']['id'],
        'userId' => $history['User']['id'],
        'userName' => $history['User']['name'],
        'content' => $history['CommentHistory']['content'],
        'created' => $history['CommentHistory']['created'],
        'status' => $status,
       );
endforeach;
        $feed = array(
            'editCount' => $historiesCount,
            'editObject' => $editObject,
        );
echo json_encode($feed);