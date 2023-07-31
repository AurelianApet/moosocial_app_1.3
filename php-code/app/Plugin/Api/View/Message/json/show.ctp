<?php
$items = array();
if (!empty($conversations)) {
    foreach ($conversations as $conver) {
        if (isset($conver['Conversation']['LastPoster']['email'])) unset($conver['Conversation']['LastPoster']['email']);
        if (isset($conver['Conversation']['LastPoster']['password'])) unset($conver['Conversation']['LastPoster']['password']);
        if (isset($conver['Conversation']['LastPoster']['Role'])) unset($conver['Conversation']['LastPoster']['Role']);
        //if (isset($conver['Sender']['avatar']))
        //    $conver['Sender']['avatar'] = $this->Moo->getItemPhoto(array('User' => $conver['Sender']), array('prefix' => '100_square'), array(), true);
        //if (isset($conver['Sender']['code'])) unset($conver['Sender']['code']);
        //if (isset($conver['Sender']['moo_href']))
        //    $conver['Sender']['profile_url'] = FULL_BASE_URL . $conver['Sender']['moo_href'];
        $tmp = array(
            'id' => $conver['Conversation']['id'],
            'from' => $conver['Conversation']['LastPoster'],
            'to' => null,
            'created_time' => $this->Moo->getTime($conver['Conversation']['created'], Configure::read('core.date_format'), $utz),
            'updated_time' => $conver['Conversation']['created'],
            'subject' => h($conver['Conversation']['subject']),
            'message' => h($this->Text->truncate($conver['Conversation']['message'], 85, array('exact' => false))),
            'link' => FULL_BASE_URL . $this->request->base .'/conversations/view/'.$conver['Conversation']['id'],
            'unread' => !($conver['ConversationUser']['unread'] == 0),
            'object' => array(
                'photo' => $this->Moo->getItemPhoto(array('User' => $conver['Conversation']['LastPoster']), array('prefix' => '100_square'), array(), true)


            )
        );
// Hacking for ios and androi app
        $more_info = __n("%s message", "%s messages", $conver['Conversation']['message_count'],$conver['Conversation']['message_count']). " ";
        $more_info .= __('Participants') . ':';
        $i = 1;
        $count = count($conver['Conversation']['ConversationUser']);
        foreach ($conver['Conversation']['ConversationUser'] as $user){
            $more_info .= strip_tags($this->Moo->getNameWithoutUrl($user['User'], true));
            $remaining = $count - $i;

            if ($i == $count)
                break;
            elseif ($i >= 3 && ( $remaining > 0 )) {
                $more_info .= ' and ' .$remaining .' others';
                break;
            } else
                $more_info .= ', ';
            $i++;
        }
        $tmp["object"]["more_info"] = $more_info;
        $items[] = $tmp;
    }
}

if(empty($items) ) {
    $items = array('Error' => 'No record found');
}
echo json_encode($items);