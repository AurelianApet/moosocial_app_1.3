<?php
$groupHelper = MooCore::getInstance()->getHelper('Group_Group');
$topic_id = !empty( $this->request->named['topic_id'] ) ? $this->request->named['topic_id'] : 0;
$video_id = !empty( $this->request->named['video_id'] ) ? $this->request->named['video_id'] : 0;
$tab = !empty( $tab ) ? $tab : '';
?>

<?php if($this->request->is('ajax')): ?>
<script>
    require(["jquery","mooGroup", "hideshare"], function($,mooGroup) {
        mooGroup.initOnView();
        $(".sharethis").hideshare({media: '<?php echo $groupHelper->getImage($group,array('prefix' => '300_square'))?>', linkedin: false});

        $('.group_feature').click(function(e){
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: $(this).attr('href'),
                success: function (data) {
                    location.reload();
                }
            });
        });
    });
</script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false,'requires'=>array('jquery','mooGroup', 'hideshare'),'object'=>array('$','mooGroup'))); ?>
    mooGroup.initOnView();
    $(".sharethis").hideshare({media: '<?php echo $groupHelper->getImage($group,array('prefix' => '300_square'))?>', linkedin: false});

    $('.group_feature').click(function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('href'),
            success: function (data) {
                location.reload();
            }
        });
    });
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
    <div>
        <img src="<?php echo $groupHelper->getImage($group, array('prefix' => '300_square'))?>" class="page-avatar" id="av-img">
            <h1 class="info-home-name"><?php echo h($group['Group']['name'])?></h1>
            
    </div>
    
<?php $this->end(); ?>
<?php
        $display = true;
        if ($group['Group']['type'] == PRIVACY_PRIVATE) {
            if (empty($is_member)) {
                $display = false;
                if(!empty($cuser) && $cuser['Role']['is_admin'])
                    $display = true;
            }
        }
    ?>
    <?php if($display): ?>
        <div class="group_header">
                <div class="group_cover">
                    <img src="<?php echo $this->request->webroot ?>theme/<?php echo $this->theme ?>/img/s.png" style="background-image:url(<?php echo $groupHelper->getImage($group, array())?>)">
                    <div class="boxGradient"></div>
                    <h1><?php echo h($group['Group']['name'])?></h1>
                    <div class="">
                        
                        <?php if ($uid): ?>

                        <div class="list_option">
                            <div class="dropdown">
                                <button id="group_edit_<?php echo $group['Group']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                                    <i class="material-icons">more_vert</i>
                                </button>

                                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="group_edit_<?php echo $group['Group']['id']?>">
                                    <?php if ( ( !empty($my_status) && $my_status['GroupUser']['status'] == GROUP_USER_MEMBER  && $group['Group']['type'] != PRIVACY_PRIVATE) ||
                                !empty($cuser['Role']['is_admin'] ) ||
                                ( !empty($my_status) && $my_status['GroupUser']['status'] == GROUP_USER_ADMIN)
                                ): ?>
                                    <li class="mdl-menu__item">
                                        <?php
                                                $this->MooPopup->tag(array(
                                                       'href'=>$this->Html->url(array("controller" => "groups",
                                                                                      "action" => "ajax_invite",
                                                                                      "plugin" => 'group',
                                                                                      $group['Group']['id'],

                                                                                  )),
                                                       'title' => __( 'Invite Friends'),
                                                       'innerHtml'=> __( 'Invite Friends'),
                                               ));
                                           ?>
                                         </li>
									<?php endif;?>
                                    <?php if ( ( !empty($my_status) && $my_status['GroupUser']['status'] == GROUP_USER_ADMIN &&  $group['Group']['user_id'] == $uid) || !empty($cuser['Role']['is_admin'] ) ): ?>
                                    <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/groups/create/<?php echo $group['Group']['id']?>"><?php echo __( 'Edit Group')?></a></li>
                                    <li class="mdl-menu__item"><a href="javascript:void(0)" href="javascript:void(0)" data-id="<?php echo  $group['Group']['id'] ?>" class="deleteGroup"><?php echo __( 'Delete Group')?></a></li>
                                    <?php endif; ?>

                                    <li class="mdl-menu__item">
                                        <?php
                                            $this->MooPopup->tag(array(
                                                   'href'=>$this->Html->url(array("controller" => "reports",
                                                                                  "action" => "ajax_create",
                                                                                  "plugin" => false,
                                                                                  'group_group',
                                                                                  $group['Group']['id'],
                                                                              )),
                                                   'title' => __( 'Report Group'),
                                                'data-dismiss' => 'modal',
                                                   'innerHtml'=> __( 'Report Group'),
                                           ));
                                       ?>
                                       </li>
                                    <li class="seperate"></li>
                                    <?php if ( !empty($my_status) && ( $my_status['GroupUser']['status'] == GROUP_USER_MEMBER || $my_status['GroupUser']['status'] == GROUP_USER_ADMIN ) && ( $uid != $group['Group']['user_id'] ) ): ?>
                                    <li class="mdl-menu__item"><a href="javascript:void(0)" class="leaveGroup" data-id="<?php echo $group['Group']['id']?>"><?php echo __('Leave Group')?></a></li>
                                    <?php endif; ?>
                                    <?php if (isset($my_status['GroupUser']['status'])):?>
                                        <?php
                                        $settingModel = MooCore::getInstance()->getModel("Group.GroupNotificationSetting");
                                        $checkStatus = $settingModel->getStatus($group['Group']['id'],$uid);
                                        ?>
                                        <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/groups/stop_notification/<?php echo $group['Group']['id']?>"><?php if ($checkStatus) echo __( 'Turn Off Notification'); else echo __('Turn On Notification');?></a></li>
                                    <?php endif;?>
                                    <?php if ( ( !empty($cuser) && $cuser['Role']['is_admin'] ) ): ?>
                                    <?php if ( !$group['Group']['featured'] ): ?>
                                    <li class="mdl-menu__item"><a class="group_feature" href="<?php echo $this->request->base?>/groups/do_feature/<?php echo $group['Group']['id']?>"><?php echo __( 'Feature Group')?></a></li>
                                    <?php else: ?>
                                    <li class="mdl-menu__item"><a class="group_feature" href="<?php echo $this->request->base?>/groups/do_unfeature/<?php echo $group['Group']['id']?>"><?php echo __( 'Unfeature Group')?></a></li>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($group['Group']['type'] != PRIVACY_PRIVATE && $group['Group']['type'] != PRIVACY_RESTRICTED): ?>
                            <!-- not allow sharing only me item -->
                        <li class="mdl-menu__item">
                            <a href="javascript:void(0);" share-url="<?php echo $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'share',
                                    'action' => 'ajax_share',
                                    'Group_Group',
                                    'id' => $group['Group']['id'],
                                    'type' => 'group_item_detail'
                                ), true); ?>" class="shareFeedBtn"><?php echo __('Share'); ?></a>
                        </li>
                        <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="group_detail_menu">
                   
                    <ul class="list2" id="browse">
                        <li class="current">
                                <a class="no-ajax" href="<?php echo $this->request->base?>/groups/view/<?php echo $group['Group']['id']?>">
                                   <?php echo __( 'Details')?></a>
                        </li>		
                        <li><a data-url="<?php echo $this->request->base?>/groups/members/<?php echo $group['Group']['id']?>" rel="profile-content" id="teams" href="<?php echo $this->request->base?>/groups/view/<?php echo $group['Group']['id']?>/tab:teams">

                                <?php echo __( 'Members')?> </a>
                        </li>
                        <li><a data-url="<?php echo $this->request->base?>/photos/ajax_browse/group_group/<?php echo $group['Group']['id']?>" rel="profile-content" id="photos" href="<?php echo $this->request->base?>/groups/view/<?php echo $group['Group']['id']?>/tab:photos">

                            <?php echo __('Photos')?></a>
                        </li>
                        <li class="dropdown">
                            <span id="group_menu_more"  class="mdl-button mdl-js-button"><?php echo __('More') ?></span>
                            <ul for="group_menu_more" class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect">
                                <?php foreach ($group_menu as $item): ?>
                                <li class="mdl-menu__item"><a data-url="<?php echo $item['dataUrl']?>" rel="profile-content" id="<?php echo $item['id']?>" href="<?php echo $item['href']?>">

                                    <?php echo $item['name']?></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>                        
                    </ul>
                </div>
            </div>
	     <?php endif; ?>
<div class="group-detail">
     
	<div id="profile-content" >
            <div class="groupId" data-id="<?php echo $group['Group']['id']; ?>"></div>
            <div class="topicId" data-id="<?php echo $topic_id; ?>"></div>
            <div class="videoId" data-id="<?php echo $video_id; ?>"></div>
            <div class="tab" data-id="<?php echo $tab; ?>"></div>
        <?php if ( empty( $tab ) ): ?>
		<?php 
		if ( !empty( $this->request->named['topic_id'] ) || !empty( $this->request->named['video_id'] ) )
			echo __( 'Loading...');
		else
			echo $this->element('ajax/group_detail');
		?>
	    <?php else: ?>
            <?php echo __( 'Loading...')?>
        <?php endif; ?>
    </div>
</div>