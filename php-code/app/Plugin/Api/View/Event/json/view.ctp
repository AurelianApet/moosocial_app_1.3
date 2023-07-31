<?php
//echo '<pre>';print_r($event);die;
$eventHelper = MooCore::getInstance()->getHelper('Event_Event');
    $eventArray = array(
        'id' => $event['Event']['id'],
        'title' => h($event['Event']['title']),
        'thumbnail' => $eventHelper->getImage($event, array('prefix' => '150_square')),
        'description' => $event['Event']['description'],
        'event_date' => $event['Event']['from'],
        'privacy' => $event['Event']['type'],
        'total_attending' => $event['Event']['event_rsvp_count'],
        'from_date' => $event['Event']['from'],
        'from_time' => $event['Event']['from_time'],
        'to_date' => $event['Event']['to'],
        'to_time' => $event['Event']['to_time'],
        'location' => h($event['Event']['location']),
        'address' => h($event['Event']['address']),
        'category_id' => $event['Category']['id'],
        'category_name' => $event['Category']['name'],
        'create_name' => $event['User']['name'],
        'create_id' => $event['User']['id'],
    );
echo json_encode($eventArray);