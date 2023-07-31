<?php $this->setCurrentStyle(4) ?>
<?php
$tags_value = '';
$blogHelper = MooCore::getInstance()->getHelper('Blog_Blog');
if (!empty($tags)) $tags_value = implode(', ', $tags);
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooBlog'), 'object' => array('$', 'mooBlog'))); ?>
mooBlog.initOnCreate();
<?php $this->Html->scriptEnd(); ?>

<div class="create_form">
<div class="bar-content">
<div class="content_center">
<div class="box3">
	<form id='createForm' action="<?php echo  $this->request->base; ?>/blogs/save" method="post">
	<?php
	if (!empty($blog['Blog']['id']))
		echo $this->Form->hidden('id', array('value' => $blog['Blog']['id']));
        echo $this->Form->hidden('thumbnail', array('value' => $blog['Blog']['thumbnail']));
        echo $this->Form->hidden('blog_photo_ids');
	?>
	<div class="mo_breadcrumb">
            <h1><?php if (empty($blog['Blog']['id'])) echo __( 'Write New Entry'); else echo __( 'Edit Entry');?></h1>
        </div>
        <div class="full_content">
            <div class="form_content">
                <ul >
                    <li>
                            <div class="thumb_content">
                                <div id="blog_thumnail_preview" class="thumb_item">
                                    <?php if (!empty($blog['Blog']['thumbnail'])): ?>
                                    <img width="150" id="item-avatar" class="img_wrapper" style="background-image:url(<?php echo  $blogHelper->getImage($blog, array('prefix' => '150_square')) ?>)" src="<?php echo $this->request->webroot?>theme/<?php echo $this->theme ?>/img/s.png" />
                                    <?php else: ?>
                                        <img width="150" id="item-avatar" class="img_wrapper" style="display: none;" src="<?php echo $this->Storage->getImage("theme/".$this->theme."/img/s.png");?>" />
                                    <?php endif; ?>

                                </div>
                                <div id="blog_thumnail" class="item_upload_thumb"></div>
                                <div class="thumb_text">
                                    <h4><?php echo __('Upload Blog Thumb') ?></h4>
                                    <div><?php echo __('Click thumb to upload') ?></div>
                                </div>
                            </div>    
                            
                        </li>
                        <li>

                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="title" class="mdl-textfield__input" type="text" value="<?php echo $blog['Blog']['title'] ?>" />
                                <label class="mdl-textfield__label" ><?php echo __('Title') ?></label>
                            </div>
                            
                        </li>
                        <li>
                           <?php echo $this->Form->select( 'category_id', $cats, array('empty' => false, 'value' => $blog['Blog']['category_id'] ) ); ?> 

                           
                        </li>
                        <li>
                             <div>
                                <label><?php echo __('Body') ?></label>
                            </div>
                            <?php echo $this->Form->tinyMCE('body', array('value' => $blog['Blog']['body'], 'id' => 'editor')); ?>
                        </li>
                        
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="tags" class="mdl-textfield__input" type="text"  value="<?php echo $tags_value ?>" />
                                <label class="mdl-textfield__label"><?php echo __('Tags') ?></label>
                            </div>
                            
                            
                            <div class="clear"></div>
                        </li>
                        
                        <li>
                            
                           
                                <?php echo $this->Form->select( 'privacy', 
                                                                                                array( PRIVACY_EVERYONE => __( 'Everyone'), 
                                                                                                           PRIVACY_FRIENDS  => __( 'Friends Only'), 
                                                                                                           PRIVACY_ME 		=> __( 'Only Me') ), 
                                                                                                array( 'value' => $blog['Blog']['privacy'],
                                                                                                           'empty' => false
                                                                                         ) ); 
                                ?>
                            
                        </li>
                        
                </ul>

                
    
                
                    <div id="images-uploader" style="display:none;margin:10px 0;">
                        <div id="photos_upload"></div>
                        <a href="#" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored" id="triggerUpload"><?php echo __( 'Upload Queued Files')?></a>
                    </div>
                    <?php if(empty($isMobile)): ?>
                        <a id="toggleUploader" href="javascript:void(0)"><?php echo __( 'Toggle Images Uploader')?></a>
                    <?php endif; ?>
                        <div style="margin:20px 0">
                            <button type='button' id='saveBtn' class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'>
                                <?php echo __('Save')?>
                            </button>
                            
                                <?php if ( !empty( $blog['Blog']['id'] ) ): ?>
                                <a href="<?php echo $this->request->base?>/blogs/view/<?php echo $blog['Blog']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo __( 'Cancel')?></a>
                                <?php endif; ?>
                                <?php if ( ($blog['Blog']['user_id'] == $uid ) || ( !empty( $blog['Blog']['id'] ) && $cuser['Role']['is_admin'] ) ): ?>
                                <a href="javascript:void(0)" data-id="<?php echo $blog['Blog']['id'] ?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1 deleteBlog"><?php echo __( 'Delete')?></a>
                                <?php endif; ?>
                        </div>
                        <div class="error-message" id="errorMessage" style="display: none;"></div>
                
        </form>
                <div class="clear"></div>

        </div>
    </div>
</div>
</div>
</div>
</div>