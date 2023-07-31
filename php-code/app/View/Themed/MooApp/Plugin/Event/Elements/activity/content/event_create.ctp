<?php 
$event = $object; 
$eventHelper = MooCore::getInstance()->getHelper('Event_Event');
?>

<?php if (!empty($activity['Activity']['content'])): ?>
<div class="comment_message">
<?php echo $this->viewMore(h($activity['Activity']['content']),null, null, null, true, array('no_replace_ssl' => 1)); ?>
</div>
<?php endif; ?>

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
        <!--span><i class='material-icons'>access_time</i></span-->
        <?php echo $this->Time->event_format($event['Event']['from'], '%b %d')?>
        <!-- -                
        <?php echo $this->Time->format('F j, Y', $event['Event']['to'])?> <?php echo $event['Event']['to_time']?>-->
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

            <!--
            <div class="event_feed_extrainfo event_location">
                <span><i class='material-icons'>location_city</i></span> <?php echo h($event['Event']['location'])?>
            </div>
            <div class="event_feed_extrainfo event_location">
                <span><i class='material-icons'>place</i></span> <?php echo h($event['Event']['address'])?>
            </div>-->
	</div>
    </div>
</div>