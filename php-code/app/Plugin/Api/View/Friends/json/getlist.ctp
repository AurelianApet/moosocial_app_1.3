<?php
$userList['totalFriendCount'] = count($users);
foreach ($users as $i =>  $user ) :
    $userList[] = array(
        'url' => FULL_BASE_URL . $user['User']['moo_href'],
        'type' => $user['User']['moo_type'],
        'id' => $user['User']['id'],
        'name' => $user['User']['name'],
        'photoCount' => $user['User']['photo_count'],
        'friendCount' => $user['User']['friend_count'],
        );
endforeach; 
echo json_encode($userList);