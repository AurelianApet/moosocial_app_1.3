<?php
foreach ($group_members as $mem) :
    $member[] = array(
        'id' => $mem['User']['id'],
        'name' => $mem['User']['name'],
        'avatar' => array(
                    '100' => $this->Moo->getItemPhoto(array('User' => $mem['User']),array( 'prefix' => '100_square'),array(),true),
                    '200' => $this->Moo->getItemPhoto(array('User' => $mem['User']),array( 'prefix' => '200'),array(),true),
                    '600' => $this->Moo->getItemPhoto(array('User' => $mem['User']),array( 'prefix' => '600'),array(),true),
            ),
        'profile_url' => FULL_BASE_URL.$mem['User']['moo_href'],
);
endforeach;

echo json_encode($member);