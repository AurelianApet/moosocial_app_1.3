<?php

$eventHelper = MooCore::getInstance()->getHelper('Event_Event');
foreach ($events as $event):
    $eventArray[] = array(
        'id' => $event['Event']['id'],
        'title' => h($event['Event']['title']),
        'thumbnail' => $eventHelper->getImage($event, array('prefix' => '150_square')),
        'event_date' => $event['Event']['from'],
        'privacy' => $event['Event']['type'],
        'total_attending' => $event['Event']['event_rsvp_count'],
        'from_time' => $event['Event']['from'],
        'to_time' => $event['Event']['to'],
        'location' => h($event['Event']['location']),
        'address' => h($event['Event']['address']),
    );
endforeach;
echo json_encode($eventArray);