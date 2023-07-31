<?php
$items = array();
if (!empty($notifications)){
    foreach($notifications as $noti){
        if(isset($noti['Sender']['email'])) unset($noti['Sender']['email']);
        if(isset($noti['Sender']['password'])) unset($noti['Sender']['password']);
        if(isset($noti['Sender']['avatar']))
            $noti['Sender']['avatar'] = $this->Moo->getItemPhoto(array('User' => $noti['Sender']),array( 'prefix' => '100_square'),array(),true);
        if(isset($noti['Sender']['code'])) unset($noti['Sender']['code']);
        if(isset($noti['Sender']['moo_href']))
            $noti['Sender']['profile_url'] = FULL_BASE_URL.$noti['Sender']['moo_href'];
        $tmp=array(
            'id'=>$noti['Notification']['id'],
            'from'=>$noti['Sender'],
            'to'=>null,
            'created_time'=>$this->Moo->getTime($noti['Notification']['created'], Configure::read('core.date_format'), $utz),
            'updated_time'=>$noti['Notification']['created'],
            'title'=>$this->element('misc/notification_texts', array('noti' => $noti)),
            'link'=>FULL_BASE_URL.$this->request->base."/notifications/ajax_view/".$noti['Notification']['id'],
            'unread'=>!$noti['Notification']['read'],
            'object'=>array('photo'=>$this->Moo->getItemPhoto(array('User' => $noti['Sender']), array('alt'=>h($noti['Sender']['name']),'class'=> "img_wrapper2", 'width'=>"45", 'prefix' => '50_square')))
            //'object'=>array('photo'=> $noti['Sender'])=> '50_square')))
        );
        // Hacking for ios and androi app
        if (isset($noti['Sender']['moo_thumb'])) {
            $tmp['object'] = array('photo'=>$noti['Sender'][$noti['Sender']['moo_thumb']]);
        }else{
            $tmp['object'] = array('photo'=>$this->Moo->getItemPhoto(array('User' => $noti['Sender']), array('alt'=>h($noti['Sender']['name']),'class'=> "img_wrapper2", 'width'=>"45", 'prefix' => '50_square')));
        }

        $tmp['title'] = html_entity_decode($tmp['title'], ENT_QUOTES);

        $items[] = $tmp;
    }
}
if(empty($items) ) {
    $items = array('Error' => 'No record found');
}
echo json_encode($items);