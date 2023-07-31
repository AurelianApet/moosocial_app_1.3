<?php 
$event = $object; 
$eventHelper = MooCore::getInstance()->getHelper('Event_Event');
?>

     <div class="activity_feed_content">
        
        <div class="activity_text">
            <?php echo $this->Moo->getName($event['User'], true, true) ?>
            <?php echo __('created a new event'); ?>
        </div>

        <div class="parent_feed_time">
            <span class="date"><?php echo $this->Moo->getTime($event['Event']['created'], Configure::read('core.date_format'), $utz) ?></span>
        </div>
        
    </div>
    <div class="clear"></div>

    <div class="activity_item event_create_feed">
    <div class="activity_left">
	<a class="event_feed_image <?php if($event['Event']['photo'] == ''): ?> event_no_image<?php endif; ?>" href="<?php echo $event['Event']['moo_href']?>" >
            
       
            <img style="background-image:url(<?php echo $eventHelper->getImage($event, array());?>)" src="<?php echo $this->request->webroot ?>theme/<?php echo $this->theme ?>/img/s.png"/>
       

            <div class="event_feed_mainInfo">
                <div class='boxGradient'></div>
                
            </div>
        </a>
    </div>
    <div class="activity_right ">
	<div class="event_feed_extrainfo event_time">
        <?php echo $this->Time->event_format($event['Event']['from'], '%b %d')?>
    </div>
    <div class="event_feed_info">
           <div class="event_info_title">
                <?php echo h($event['Event']['moo_title'])?>
            </div>
            <div class='small_detail'>
                 <?php if ($event['Event']['type'] == PRIVACY_PUBLIC): ?>
                 <?php echo __('Public')?>
                 <?php elseif ($event['Event']['type'] == PRIVACY_PRIVATE): ?>
                 <?php echo __('Private')?>
                 <?php endif; ?>
                 &middot; <?php echo __( '%s attending', $event['Event']['event_rsvp_count'])?>
            </div>     
    </div>
    </div>
    </div>
