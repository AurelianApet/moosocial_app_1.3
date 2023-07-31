

<?php if (Configure::read('Event.event_enabled') == 1): ?>
<ul class="event_content_list">
<?php
if(!isset($events)){
    if($uid !== null){
        $events = $this->requestAction("events/upcomming/uid:".$uid);
    }else{
        $events = array();
    }
}
$eventHelper = MooCore::getInstance()->getHelper('Event_Event');
    if (count($events) > 0):
        foreach ($events as $event):
    ?>
        <li class="full_content">
                
                <div class="event_cover">
                    
                    <a class='event-list-thumb' style="background-image:url(<?php echo $eventHelper->getImage($event, array());?>)" href="<?php echo $this->request->base?>/events/view/<?php echo $event['Event']['id']?>/<?php echo seoUrl($event['Event']['title'])?>">
                        
                        <div class="event_feed_mainInfo">
                            <div class="boxGradient"></div>
                            
                        </div>
                        
                    </a>
                    <div class="event-info-list">
                        <?php if( !empty($uid) && (($event['Event']['user_id'] == $uid ) || ( !empty($cuser) && $cuser['Role']['is_admin'] ) ) ): ?>
                        <div class="list_option">
                            <div class="dropdown">
                                <button id="event_edit_<?php echo $event['Event']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                                    <i class="material-icons">more_vert</i>
                                </button>

                                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="event_edit_<?php echo $event['Event']['id']?>">
                                    <?php if ($event['User']['id'] == $uid || ( !empty($cuser) && $cuser['Role']['is_admin'] )): ?>
                                        <li  class="mdl-menu__item"><a href="<?php echo $this->request->base?>/events/create/<?php echo $event['Event']['id']?>"> <?php echo __( 'Edit Event')?></a></li>
                                    <?php endif; ?>
                                    <?php if ( ($event['Event']['user_id'] == $uid ) || ( !empty( $event['Event']['id'] ) && !empty($cuser) && $cuser['Role']['is_admin'] ) ): ?>
                                        <li class="mdl-menu__item"><a href="javascript:void(0)" data-id="<?php echo $event['Event']['id']?>" class="deleteEvent"> <?php echo __( 'Delete Event')?></a></li>
                                        
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="left">
                             <div class="event_feed_extrainfo event_time">
                                <!--span><i class='material-icons'>access_time</i></span-->
                                <?php echo $this->Time->event_format($event['Event']['from'], '%b %d')?>  <?php //echo $event['Event']['from_time']?>
                                <!-- -                
                                <?php echo $this->Time->format('F j, Y', $event['Event']['to'])?> <?php echo $event['Event']['to_time']?>-->
                            </div>
                        </div>
                        <div class="right">
                         <div class="event_info_title">

                           <?php echo h($event['Event']['title'])?>


                        </div>
                        <div class='small_detail'>
                             <?php if ($event['Event']['type'] == PRIVACY_PUBLIC): ?>
                             <?php echo __('Public')?>
                             <?php elseif ($event['Event']['type'] == PRIVACY_PRIVATE): ?>
                             <?php echo __('Private')?>
                             <?php endif; ?>
                             &middot; <?php echo __( '%s attending', $event['Event']['event_rsvp_count'])?>
                        </div>  
                        
                        <?php //$this->Html->rating($event['Event']['id'],'events','Event'); ?>
                        </div>
                    </div>

                    

                </div>

        </li>
    <?php
        endforeach;
    else:
        echo '<div class="clear text-center">' . __( 'No more results found') . '</div>';
    endif;

?>

<?php
if (!empty($more_result)):
?>

    <?php $this->Html->viewMore($more_url) ?>
<?php
endif;

?>
</ul>
<?php endif; ?>
<section class="modal fade in" id="mapmodals">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo  $this->MooGMap->loadGoogleMap('',530,300,true); ?>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</section><!-- /.modal -->

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooEvent"], function($,mooEvent) {
        mooEvent.initOnListing();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooEvent'), 'object' => array('$', 'mooEvent'))); ?>
mooEvent.initOnListing();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>