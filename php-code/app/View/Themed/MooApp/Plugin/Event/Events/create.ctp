<?php $this->setCurrentStyle(4);?>
<?php
$eventHelper = MooCore::getInstance()->getHelper('Event_Event');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true,'requires'=>array('jquery','mooEvent'), 'object' => array('$', 'mooEvent'))); ?>
mooEvent.initOnCreate();
<?php $this->Html->scriptEnd(); ?>

<div class="create_form">
<div class="bar-content">
    <div class="content_center">
        <form id="createForm">
        <?php
        if (!empty($event['Event']['id'])){
            echo $this->Form->hidden('id', array('value' => $event['Event']['id']));
            echo $this->Form->hidden('photo', array('value' => $event['Event']['photo']));
        }else{
            echo $this->Form->hidden('photo', array('value' => ''));
        }
        ?>	

        <div class="box3">	
            <div class="mo_breadcrumb">
                <h1><?php if (empty($event['Event']['id'])) echo __( 'Add New Event'); else echo __( 'Edit Event');?></h1>
            </div>

            <div class="full_content">
                <div class="form_content">
                <ul>
                        <li>
                            <div class="thumb_content">
                                <div class="thumb_item">
                                    <?php if (!empty($event['Event']['photo'])): ?>
                                    <img width="150" id="item-avatar" class="img_wrapper" style="background-image:url(<?php echo  $eventHelper->getImage($event, array('prefix' => '150_square')) ?>)" src="<?php echo $this->request->webroot?>theme/<?php echo $this->theme ?>/img/s.png" />
                                    <?php else: ?>
                                        <img width="150" id="item-avatar" class="img_wrapper" style="display: none;" src="<?php echo $this->Storage->getImage("theme/".$this->theme."/img/s.png");?>" />
                                    <?php endif; ?>

                                </div>
                                <div id="select-0" class="item_upload_thumb" style="margin: 10px 0 0 0px;"></div>
                                <div class="thumb_text">
                                    <h4><?php echo __('Upload Event Thumb') ?></h4>
                                    <div><?php echo __('Click thumb to upload') ?></div>
                                </div>
                            </div>    
                            
                        </li>
                        <li>
                            <?php 
                           echo $this->Form->select('type', array( PRIVACY_PUBLIC  => __( 'Public'), 
                                                                                                           PRIVACY_PRIVATE => __( 'Private')
                                                                                                   ), 
                                                                                            array( 'value' => $event['Event']['type'], 'empty' => false ) 
                                                                           ); 
                           ?>
                          


                        </li>
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="title" class="mdl-textfield__input" type="text" value="<?php echo $event['Event']['title'] ?>" />
                                <label class="mdl-textfield__label"><?php echo __( 'Event Title')?></label>
                            </div>
                        </li>
                        <li>
                           <?php echo $this->Form->select( 'category_id', $categories, array('empty' => false, 'value' => $event['Event']['category_id'] ) ); ?> 
                                
                            
                                
                            
                        </li>
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="location" class="mdl-textfield__input" type="text" value="<?php echo $event['Event']['location'] ?>" />
                                <label class="mdl-textfield__label"><?php echo __( 'Location')?></label>
                            </div>
                            
                        </li>
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="address" class="mdl-textfield__input" type="text" value="<?php echo $event['Event']['address'] ?>" />
                                <label class="mdl-textfield__label"><?php echo __( 'Address')?></label>
                            </div>
                            
                        </li>
                        <li class="picker_field">
                            
                           
                                <div class='col-xs-6'>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input name="from" class="mdl-textfield__input datepicker" type="text" value="<?php echo $event['Event']['from'] ?>" />
                                        <label class="mdl-textfield__label"><?php echo __( 'From')?></label>
                                    </div>
                                </div>
                                <div class='col-xs-6'>
                                    <div class="m_l_2">
                                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                            <input name="from_time" class="mdl-textfield__input timepicker" type="text" value="<?php echo $event['Event']['from_time'] ?>" />
                                            
                                        </div>
                                       
                                    </div>
                                </div>
                            
                            <div class="clear"></div>
                        </li>
                        <li class="picker_field">
                            
                            <div class='col-xs-6'>
                                <div class="m_l_2">
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input name="to" class="mdl-textfield__input datepicker" type="text" value="<?php echo $event['Event']['to'] ?>" />
                                        <label class="mdl-textfield__label"><?php echo __( 'To')?></label>
                                    </div>

                                </div>
                            </div>
                            <div class='col-xs-6'>
                                <div class="m_l_2">
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input name="to_time" class="mdl-textfield__input timepicker" type="text" value="<?php echo $event['Event']['to_time'] ?>" />
                                    
                                </div>
                               
                                    </div>
                            </div>
                            
                            <div class="clear"></div>
                        </li>
                        <li>
                            
                                <?php $currentTimezone = !empty($event['Event']['timezone']) ? $event['Event']['timezone'] : $cuser['timezone']; ?>
                                <?php echo $this->Form->select('timezone', $this->Moo->getTimeZones(), array('empty' => false, 'value' => $currentTimezone)); ?>
                            
                        </li>
                        <li>
                            <div>
                                <label><?php echo __('Information') ?></label>
                            </div>
                            <?php echo $this->Form->tinyMCE('description', array('value' => $event['Event']['description'], 'id' => 'editor')); ?>
                        </li>
                        <li>
                            
                                <button type='button' class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored' id="saveBtn"><?php echo __( 'Save')?></button>
                                
                                <?php if ( !empty( $event['Event']['id'] ) ): ?>
                                    <a href="<?php echo $this->request->base?>/events/view/<?php echo $event['Event']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored"><?php echo __( 'Cancel')?></a>
                                <?php endif; ?>
                                <?php if ( ($event['Event']['user_id'] == $uid ) || ( !empty( $event['Event']['id'] ) && !empty($cuser['Role']['is_admin']) ) ): ?>
                                    <a href="javascript:void(0)" data-id="<?php echo $event['Event']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored deleteEvent"><?php echo __( 'Delete')?></a>
                                <?php endif; ?>
                            
                        </li>
                </ul>
           
                <div class="error-message" style="display:none;"></div>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
</div>