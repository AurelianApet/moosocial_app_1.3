<?php if ($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooActivities", "mooShare"], function($, mooActivities, mooShare) {
        mooActivities.init();
        mooShare.init();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', "mooActivities", 'mooShare'), 'object' => array('$', "mooActivities", 'mooShare'))); ?>
mooActivities.init();
mooShare.init();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php $this->setCurrentStyle(4);?>
<?php if (!empty($activity)): ?>
<li class="slide" id="activity_<?php echo $activity['Activity']['id']?>">
    <div class="feed_main_info">
    	<div class="dropdown edit-post-icon">
            <a id="activity_menu_edit_<?php echo $activity['Activity']['id']?>" href="javascript:void(0)" class="cross-icon " data-toggle="dropdown">
               <i class="material-icons md-24">more_vert</i>
            </a>
            <ul class="dropdown-menu" for="activity_menu_edit_<?php echo $activity['Activity']['id']?>">
                <li >
                    <?php
                        $this->MooPopup->tag(array(
                            'href'=>$this->Html->url(array("controller" => "notifications",
                                                          "action" => "stop",
                                                          "plugin" => false,
                                                        'activity',
                                                          $activity['Activity']['id']
                                                      )),
                            'title' => __('Stop Notifications'),
                            'innerHtml'=> __('Stop Notifications'),
                            'id' => 'stop_notification_' . $activity['Activity']['id']
                       ));
                   ?> 
                </li>
                
                <?php if(!empty($activity['UserTagging']['users_taggings']) && $activity['Activity']['user_id'] == $uid): ?>
                <li >
                    <?php
                            $this->MooPopup->tag(array(
                                   'href'=>$this->Html->url(array("controller" => "friends",
                                                                  "action" => "tagged",
                                                                  "plugin" => false,
                                                                  $activity['Activity']['id']
                                                              )),
                                   'title' => __('Tag Friends'),
                                   'innerHtml'=> __('Tag Friends'),
                           ));
                       ?> 
                </li>
                <?php endif; ?>
                
                <?php if (isset($activity['UserTagging']['users_taggings']) && in_array($uid, explode(',', $activity['UserTagging']['users_taggings']))): ?>
                <li >
                    <a href=""><?php echo __('Remove Tags'); ?></a>
                </li>
                <?php endif; ?>
                
                <?php if ($activity['Activity']['user_id'] == $uid && $activity['Activity']['action'] == 'wall_post'):?>
                <li >
                    <a class="editActivity" data-activity-id="<?php echo $activity['Activity']['id']?>" href="javascript:void(0)" >
                        <?php echo __('Edit Post'); ?>
                    </a>
                </li>
                <?php endif;?>
                <li >
                    <a class="removeActivity" data-activity-id="<?php echo $activity['Activity']['id']?>" href="javascript:void(0)">
                        <?php echo __('Delete Post'); ?>
                    </a>
                </li>
            </ul>
          </div>
        <div class="activity_feed_image">
            <?php echo $this->Moo->getItemPhoto(array('User' => $activity['User']),array( 'prefix' => '50_square'), array('class' => 'img_wrapper2 user_avatar_large'))?>
        </div>
        <div class="activity_feed_content">
            <div class="activity_text">
            <?php echo $this->Moo->getName($activity['User'])?><?php $this->getEventManager()->dispatch(new CakeEvent('element.activities.afterRenderUserNameFeed', $this,array('user'=>$activity['User']))); ?>
                <div class="feed_time">
                   <a href="<?php echo $this->request->base?>/users/view/<?php echo $activity['Activity']['user_id']?>/activity_id:<?php echo $activity['Activity']['id']?>" class="date"><?php echo __('Just now')?></a>
                    <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "histories",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'activity',
                                            $activity['Activity']['id']
                                        )),
             'title' => __('Show edit history'),
             'innerHtml'=> __('Edited'),
          'style' => empty($activity['Activity']['edited']) ? 'display:none' : '',
          'id' => 'history_activity_'. $activity['Activity']['id'],
          'class' => 'edit-btn',
		  'data-dismiss'=>'modal'
     ));
 ?>
                   
                   <?php if (!$activity['Activity']['target_id']):?>
                 	<?php
                 	 switch ($activity['Activity']['privacy']) {
                 	 	case '1':
                 	 		$text = __('Shared with: Everyone');
                 	 		$icon = '<i class="material-icons md-18">public</i>';                 	 	
                 	 	break;                 	 		
                 	 	case '2':
                 	 		$text = __('Shared with: Friend');
                 	 		$icon = '<i class="material-icons md-18">people</i>';      
                 	 	break;
                 	 	case '3':
                 	 		$text = __('Shared with: Only Me');
                 	 		$icon = '<i class="material-icons md-18">lock</i>';      
                 	 	break;
                 	 } 
                 	?>
	<?php if(($activity['Activity']['user_id'] == $uid || $cuser['Role']['is_admin']) && $activity['Activity']['action'] == 'wall_post'): ?>

                        <span class="dropdown">
                            <a id="permission_<?php echo $activity['Activity']['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" class="tip" href="javascript:void(0);" original-title="<?php echo $text;?>"> <?php echo $icon;?>&nbsp</i>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="permission_<?php echo $activity['Activity']['id'] ?>">
                                <li><a data-privacy="1" data-activity-id="<?php echo $activity['Activity']['id']; ?>" class="change-activity-privacy<?php if($activity['Activity']['privacy'] == 1) echo ' n52'; ?>" href="javascript:void(0)"><?php echo __('Everyone'); ?></a></li>
                                <li><a data-privacy="2" data-activity-id="<?php echo $activity['Activity']['id']; ?>" class="change-activity-privacy<?php if($activity['Activity']['privacy'] == 2) echo ' n52'; ?>" href="javascript:void(0)"><?php echo __('Friends Only'); ?></a></li>
                            </ul>
                        </span>
                    <?php else: ?>
                 	<a class="tip feed-privacy" href="javascript:void(0);" original-title="<?php echo $text;?>"> <?php echo $icon;?> </a>
<?php endif; ?>
                 <?php elseif (strtolower($activity['Activity']['type']) == 'user'):?>
                   <?php 
                   	$target = MooCore::getInstance()->getItemByType($activity['Activity']['type'],$activity['Activity']['target_id']);
                   ?>
                   <?php if ($activity['Activity']['privacy'] == PRIVACY_FRIENDS) :?>
                 	<a class="tip" href="javascript:void(0);" original-title="<?php echo __('Shared with: %s\'Friends instead of %s\'Friends of friends',$target['User']['moo_title'],$target['User']['moo_title']);?>"> <i class="material-icons md-18">people</i></a>
                 	<?php else:?>
                 	<a class="tip" href="javascript:void(0);" original-title="<?php echo __('Shared with: Everyone');?>"> <i class="material-icons md-18">public</i></a>
                 	<?php endif;?>
                 	                
                 <?php endif;?>
                </div>
            </div>
        </div>
         <div class="clear"></div>
         <div class="activity_feed_content_text" id="activity_feed_content_text_<?php echo $activity['Activity']['id'];?>">
         	<?php 
         		$item_type = $activity['Activity']['item_type'];
				if ($activity['Activity']['plugin'])
				{
					$options = array('plugin'=>$activity['Activity']['plugin']);
				}
				else
				{
					$options = array();
				}
				
				if ($item_type)
				{
					list($plugin, $name) = mooPluginSplit($item_type);
					$object = MooCore::getInstance()->getItemByType($item_type,$activity['Activity']['item_id']);
					
				}
				else
				{
					$plugin = '';
					$name ='';
					$object = null;
				}
         	?>
             <?php echo $this->element('activity/content/' . $activity['Activity']['action'], array('activity' => $activity,'object'=>$object),$options);?>
         </div>
    </div>
    <div class="feed_comment_info">

	<div class="date">

            <?php if ( $activity['Activity']['params'] == 'mobile' ) echo __('via mobile'); ?>
            
            
	    <a href="javascript:void(0)" data-id="<?php echo $activity['Activity']['id']?>" data-type="activity" data-status="1"  id="activity_l_<?php echo $activity['Activity']['id']?>" class="comment-thumb likeActivity"><i class="material-icons md-24">thumb_up</i></a><span id="activity_like_<?php echo $activity['Activity']['id']?>">0</span>
        <?php if(empty($hide_dislike)): ?>
            <a href="javascript:void(0)" data-id="<?php echo $activity['Activity']['id']?>" data-type="activity" data-status="0"  id="activity_d_<?php echo $activity['Activity']['id']?>" class="comment-thumb likeActivity"><i class="material-icons md-24">thumb_down</i></a> <span id="activity_dislike_<?php echo $activity['Activity']['id']?>">0</span>
        <?php endif; ?>
            <!-- Share activity -->
            <?php echo $this->element('share', array('activity' => $activity)); ?>
            <!-- End Share activity -->
        </span>
        </div>
	<ul class="activity_comments" id="comments_<?php echo $activity['Activity']['id']?>">
		<li id="newComment_<?php echo $activity['Activity']['id']?>">
			<div class="comment_box_add">
                            <textarea class="commentBox showCommentBtn" data-id="<?php echo $activity['Activity']['id']?>" placeholder="<?php echo __('Write a comment...')?>" id="commentForm_<?php echo $activity['Activity']['id']?>"></textarea>
                            <div class="clear"></div>
				<div style="display:block" class="commentButton" id="commentButton_<?php echo $activity['Activity']['id']?>">
					<input type="hidden" id="comment_image_<?php echo $activity['Activity']['id'];?>" />
					<div data-id="<?php echo $activity['Activity']['id'];?>"  id="comment_button_attach_<?php echo $activity['Activity']['id'];?>"></div>
				    <a class=" mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored viewer-submit-comment" data-activity-id="<?php echo $activity['Activity']['id']?>" href="javascript:void(0)" > <?php echo __('Post')?></a>
				</div>
				<div id="comment_preview_image_<?php echo $activity['Activity']['id'];?>"></div>
			</div>
		</li>
	</ul>
    </div>
    <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooAttach'), 'object' => array('$', 'mooAttach'))); ?>
    mooAttach.registerAttachComment(<?php echo $activity['Activity']['id'];?>);
    <?php $this->Html->scriptEnd(); ?>
</li>
<?php endif;?>