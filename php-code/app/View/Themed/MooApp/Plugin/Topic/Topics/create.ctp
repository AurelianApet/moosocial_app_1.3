<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooTopic'), 'object' => array('$', 'mooTopic'))); ?>
mooTopic.initOnCreate();
<?php $this->Html->scriptEnd(); ?>

<?php $this->setCurrentStyle(4) ?>
<?php
$topicHelper = MooCore::getInstance()->getHelper('Topic_Topic');
$tags_value = '';
if (!empty($tags)){
    $tags_value = implode(', ', $tags);
}

?>

<style>
.attach_remove {display:none;}
#attachments_list li:hover .attach_remove {display:inline-block;}
</style>




<div class="create_form">
<div class="bar-content">
<div class="content_center">
<div class="box3">
    <form id="createForm">
	<?php
	echo $this->Form->hidden( 'attachments', array( 'value' => $attachments_list ) );
        echo $this->Form->hidden('thumbnail', array('value' => $topic['Topic']['thumbnail']));
        echo $this->Form->hidden('plugin_topic_id', array('value' => PLUGIN_TOPIC_ID));
        echo $this->Form->hidden('topic_photo_ids');
	if (!empty($topic['Topic']['id']))
		echo $this->Form->hidden('id', array('value' => $topic['Topic']['id']));
	?>
        <div class="mo_breadcrumb">
            <h1><?php if (empty($topic['Topic']['id'])) echo __( 'Create New Topic'); else echo __( 'Edit Topic');?></h1>	
        </div>
        <div class="full_content p_m_10">
                <div class="form_content">
                    <ul>
                        <li>
                            <div class="thumb_content">
                                <div class="thumb_item">
                                <div id="topic_thumnail_preview">
                                    <?php if (!empty($topic['Topic']['thumbnail'])): ?>
                                    <img width="150" id="item-avatar" class="img_wrapper" style="background-image:url(<?php echo $topicHelper->getImage($topic, array('prefix' => '150_square'))?>)" src="<?php echo $this->request->webroot?>theme/<?php echo $this->theme ?>/img/s.png" />
                                    <?php else: ?>
                                        <img width="150" id="item-avatar" class="img_wrapper" style="display: none;" src="<?php echo $this->Storage->getImage("theme/".$this->theme."/img/s.png");?>" />
                                    <?php endif; ?>
                                    </div>
                                </div>
                                <div class="thumb_qq" id="topic_thumnail"></div>
                                <div class="thumb_text">
                                    <h4><?php echo __('Upload Topic Thumb') ?></h4>
                                    <div><?php echo __('Click thumb to upload') ?></div>
                                </div>
                            </div>    
                            
                        </li>
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="title" class="mdl-textfield__input" type="text" value="<?php echo $topic['Topic']['title'] ?>" />
                                <label class="mdl-textfield__label"><?php echo __( 'Topic Title')?></label>
                            </div>                            
                        </li>
                   
                        <li>
                           <?php echo $this->Form->select( 'category_id', $cats, array('empty' => false, 'value' => $topic['Topic']['category_id'] ) ); ?> 

                           
                        </li>
                        <li>
			 <div >
                            <label><?php echo __( 'Topic')?></label>
                            </div>
                            <?php echo $this->Form->tinyMCE( 'body', array( 'value' => $topic['Topic']['body'], 'id' => 'editor' ) ); ?>
                        </li>
                       
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="tags" class="mdl-textfield__input" type="text" value="<?php echo $tags_value ?>" />
                                <label class="mdl-textfield__label" ><?php echo __('Tags') ?></label>
                            </div>
                       </li>
                            
                        <?php if (!empty($attachments)): ?>
                        <li>
                            
                            <label><?php echo __( 'Attachments')?></label>
                            
                            
                            <ul class="list6 list6sm" id="attachments_list" style="overflow: hidden;">
                               <?php foreach ($attachments as $attachment): ?>
                                <li><i class="icon-attach"></i><a href="<?php echo $this->request->base?>/attachments/download/<?php echo $attachment['Attachment']['id']?>" target="_blank"><?php echo $attachment['Attachment']['original_filename']?></a>
                                        &nbsp;<a href="#" data-id="<?php echo $attachment['Attachment']['id']?>" class="attach_remove tip" title="<?php echo __( 'Delete')?>"><i class="icon-trash icon-small"></i></a>	            
                                </li>
                                <?php endforeach; ?>
                            </ul>
                           
                           
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    
                        <div id="images-uploader" style="display:none;margin:10px 0;">
                            <div id="attachments_upload"></div>
                            <a href="javascript:void(0)" class="button button-primary" id="triggerUpload"><?php echo __( 'Upload Queued Files')?></a>
                        </div>
                        <?php if(empty($isMobile)): ?>
                            <a href="javascript:void(0)" id="toggleUploader"><?php echo __( 'Toggle Attachments Uploader')?></a>
                        <?php endif; ?>
                        <div style="margin:20px 0">           
                            <button type='button' class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1' id="saveBtn"><?php echo __( 'Save')?></button>
                           
                            <?php if ( !empty( $topic['Topic']['id'] ) ): ?>
                            <a href="<?php echo $this->request->base?>/topics/view/<?php echo $topic['Topic']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo __( 'Cancel')?></a>
                            <?php endif; ?>
                            <?php if ( ($topic['Topic']['user_id'] == $uid ) || ( !empty( $topic['Topic']['id'] ) && $cuser['Role']['is_admin'] ) ): ?>
                            <a href="javascript:void(0)" data-id="<?php echo $topic['Topic']['id']?>" class="deleteTopic mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo __( 'Delete')?></a>
                            <?php endif; ?> 
                        </div>
                        <div class="error-message" id="errorMessage" style="display:none"></div>
                    
                <div class="clear"></div>
            </div>
        </div>
            
    </form>
    
</div>
    
</div>
</div>
</div>