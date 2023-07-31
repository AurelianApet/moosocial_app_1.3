<?php if($this->request->is('ajax')): ?>
<script>
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initRespondRequest();
    });
</script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false,'requires'=>array('jquery','mooUser'),'object'=>array('$','mooUser'))); ?>
    mooUser.initRespondRequest();
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php
if(empty($title)) $title = "Featured Members";
if(empty($num_item_show)) $num_item_show = 10;

$friends = $this->requestAction(
    "users/friends/num_item_show:$num_item_show/user_id:$uid"
);
?>

<div class="profile-header">
    
    
    <div id="cover">
        <div class="boxGradient"></div>
        <img id="cover_img_display" width="100%" src="<?php echo $this->request->webroot?>theme/<?php echo $this->theme ?>/img/s.png" style="background-image:url(<?php echo $this->storage->getUrl($user['User']["id"],'',$user['User']['cover'],"moo_covers");?>)" />
        <?php if ( !empty( $cover_album_id ) ): ?>
            <a href="<?php echo $this->request->base?>/albums/view/<?php echo $cover_album_id?>"></a>
        <?php endif; ?>

        <?php if ( $uid == $user['User']['id'] ): ?>
            <div id="cover_upload">
                <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "users",
                                            "action" => "ajax_cover",
                                            "plugin" => false,
                                           
                                        )),
             'title' => __('Edit Cover Picture'),
             'innerHtml'=> '<i class="material-icons">local_see</i>',
          'data-backdrop' => 'static',
     ));
 ?>
                
            </div>
        <?php endif; ?>
            <div id="avatar">
            <?php if ( !empty( $profile_album_id ) ): ?>
                <a href="<?php echo $this->request->base?>/albums/view/<?php echo $profile_album_id?>">
                    <?php echo $this->Moo->getItemPhoto(array('User' => $user['User']), array('prefix' => '200_square'), array("id" => "av-img"))?>
                </a>
            <?php else: ?>
                <?php echo $this->Moo->getItemPhoto(array('User' => $user['User']), array("id" => "av-img", 'prefix' => '200_square'))?>
            <?php endif; ?>

            <?php if ( $uid == $user['User']['id'] ): ?>
                <div id="avatar_upload" >
                    <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "users",
                                            "action" => "ajax_avatar",
                                            "plugin" => false,
                                        )),
             'title' => __('Edit Profile Picture'),
             'innerHtml'=> '<i class="material-icons">local_see</i>',
             'data-backdrop' => 'static'
     ));
 ?>

                </div>
            <?php endif; ?>
        <?php if ( !empty($is_online)): ?>
                <span class="online-stt">
                </span>
        <?php endif; ?>
    </div>
    </div>
    
    <div class="section-menu"><?php $this->Html->rating($uid,'profile'); ?> 
        <div class="profile-action">
	 <?php $this->getEventManager()->dispatch(new CakeEvent('View.Elements.User.headerProfile.beforeRenderSectionMenu', $this)); ?>
            <ul>
             <?php if ($user['User']['id'] == $uid): ?>
                <li >
            <a href="<?php echo $this->request->base?>/users/profile" >
            
                <i class="material-icons md-24">mode_edit</i>
                </a>
                </li>
        <?php endif; ?>
			<?php if ($user['User']['id'] != $uid && !empty($uid)): ?>
                    <li >
            
            <?php
                  $this->MooPopup->tag(array(
                         'href'=>$this->Html->url(array("controller" => "conversations",
                                                        "action" => "ajax_send",
                                                        "plugin" => false,
                                                        $user['User']['id']
                                                    )),
                         'title' => __('Send New Message'),
                         'innerHtml'=> '<i class="material-icons md-24">insert_comment</i>'
                 ));
             ?>
                    </li>

            <?php if ( !empty($request_sent) ): ?>
                    <li >
            <a id="userCancelFriend" href="<?php echo $this->request->base?>/friends/ajax_cancel/<?php echo $user['User']['id']?>" class="topButton button button-action" title="<?php __('Cancel a friend request');?>">
                <i class="icon-pending"></i>
            </a>
                    </li>
            <?php endif; ?>

            <?php if ( !empty($respond) ): ?>
           <li>
                <div class="dropdown" style="float:right" >
                    <a href="#" id="respond" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" title="<?php __('Respond to Friend Request');?>">
                        <i class="material-icons">group_add</i>
                    </a>

                    <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="respond">
                        <li class="mdl-menu__item"><a class="respondRequest" data-id="<?php echo  $request_id; ?>" data-status="1" href="javascript:void(0)"><?php echo  __('Accept'); ?></a></li>
                        <li class="mdl-menu__item"><a class="respondRequest" data-id="<?php echo  $request_id; ?>" data-status="0" href="javascript:void(0)"><?php echo  __('Delete'); ?></a></li>
                    </ul>
                </div>
			</li>
                
            <?php endif; ?>

            <?php if ( !empty($uid) && !$areFriends && empty($request_sent) && empty($respond) ): ?>
                <li>
                <?php
                    $this->MooPopup->tag(array(
                           'href'=>$this->Html->url(array("controller" => "friends",
                                                          "action" => "ajax_add",
                                                          "plugin" => false,
                                                          $user['User']['id']
                                                      )),
                           'title' => sprintf( __('Send %s a friend request'), h($user['User']['name']) ),
                           'innerHtml'=>'<i class="material-icons md-24">person_add</i>',
                        'id' => 'addFriend_'. $user['User']['id']
                   ));

               ?>
                </li>
            <?php endif; ?>

            <?php if ($uid && Configure::read("core.enable_follow") && $uid != $user['User']['id']): ?>
                <?php
                $followModel = MooCore::getInstance()->getModel("UserFollow");
                $follow = $followModel->checkFollow($uid,$user['User']['id']);
                ?>
                <li>
                    <a href="javascript:void(0);" class="user_action_follow" data-uid="<?php echo $user['User']['id']; ?>" data-follow="<?php if ($follow) echo '1'; else echo '0'; ?>"><i class="material-icons md-24"><?php if ($follow) echo 'check'; else echo 'rss_feed'; ?></i></a>
                </li>
            <?php endif; ?>
        <?php endif;?>
            <li class="dropdown">
                <a id="profile_edit_<?php echo $user['User']['id']; ?>" href="#" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"><i class="material-icons">more_vert</i></a>
                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="profile_edit_<?php echo $user['User']['id']; ?>">
        
        
        <?php if ( !empty($cuser['role_id']) && $cuser['Role']['is_admin'] && !$user['User']['featured'] ): ?>
            <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/admin/users/feature/<?php echo $user['User']['id']?>"><?php echo __('Feature User')?></a></li>
            <?php endif; ?>
            <?php if ( !empty($cuser['role_id']) && $cuser['Role']['is_admin'] && $user['User']['featured'] ): ?>
            <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/admin/users/unfeature/<?php echo $user['User']['id']?>"><?php echo __('Unfeature User')?></a></li>
            <?php endif; ?>
            <?php if ( !empty($cuser['role_id']) && $cuser['Role']['is_admin'] && !$user['Role']['is_admin'] ): ?>
            <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/admin/users/edit/<?php echo $user['User']['id']?>"><?php echo __('Edit User')?></a></li>
            <?php endif; ?>
            <li class="mdl-menu__item">
                            <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "reports",
                                            "action" => "ajax_create",
                                            "plugin" => false,
                                            'user',
                                            $user['User']['id']
                                        )),
             'title' => __('Report User'),
             'innerHtml'=> __('Report User'),
     ));
 ?>
                          </li>
            <?php if ( !empty($uid) && $areFriends ): ?>
            <li class="mdl-menu__item"><?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "friends",
                                            "action" => "ajax_remove",
                                            "plugin" => false,
                                            $user['User']['id']
                                            
                                        )),
             'title' => __('Unfriend'),
             'innerHtml'=> __('Unfriend'),
     ));
 ?></li>
            <?php endif; ?>
            <?php if ( !empty($uid) && ($uid != $user['User']['id'] ) && !$user['Role']['is_admin'] && !$user['Role']['is_super']): ?>
            <li class="mdl-menu__item"><?php
                if(!$is_viewer_block){
                    $this->MooPopup->tag(array(
                        'href'=>$this->Html->url(array("controller" => "user_blocks",
                                            "action" => "ajax_add",
                                            "plugin" => false,
                                             $user['User']['id']
                                            
                                        )),
                            'title' => __('Block'),
                            'innerHtml'=> __('Block'),
                         ));
                }else{
                    $this->MooPopup->tag(array(
                        'href'=>$this->Html->url(array("controller" => "user_blocks",
                                            "action" => "ajax_remove",
                                            "plugin" => false,
                                            $user['User']['id']
                                            
                                        )),
                            'title' => __('Unblock'),
                            'innerHtml'=> __('Unblock'),
                         ));
                }
 ?></li>
 <?php endif;?>
</ul>			
               
         </li>
        </ul>    
        </div>
    </div>

    <div class="user_main_info">
    <div class="profile-info-section">
        <h1><?php echo h($this->Text->truncate($user['User']['name'], 30, array('exact' => false)))?></h1>
    </div>

    
    <div class="profile_info">
        <?php //echo $this->Html->rating($user['User']['id'],'users'); ?>
    </div>
    </div>
</div>
<div class="profile_plg_menu">
    <?php if ( $canView ): ?>
        <ul class="list3 profile_info">
                <?php if ( !empty( $user['User']['gender'] ) ): ?>
                    <li style="background:none;padding:0"><span class="date"><?php echo __('Gender')?>:</span> <?php $this->Moo->getGenderTxt($user['User']['gender']); ?></li>
                <?php endif; ?>
                <?php if ( !empty( $user['User']['birthday'] ) && $user['User']['birthday'] != '0000-00-00'): ?>
                    <li><span class="date"><?php echo __('Born on')?>:</span> <?php echo $this->Time->event_format($user['User']['birthday'], '%B %d')?></li>
                <?php endif; ?>
                <?php 
                //add profile type
	            ?>
                <?php if ($user['ProfileType']):?>
                	<?php if (Configure::read('core.enable_show_profile_type') ):?>
                	<li>
                		<span class="date"><?php echo __('Profile type');?>: </span>
                		<a href="<?php echo $this->request->base;?>/users/index/profile_type:<?php echo $user['ProfileType']['id'];?>"><?php echo $user['ProfileType']['name'];?></a>
                	</li>	
                	<?php endif;?>
                <?php $helper = MooCore::getInstance()->getHelper("Core_Moo");?>
                 <?php foreach ($fields as $field): 
                    if (!in_array($field['ProfileField']['type'],$helper->profile_fields_default))
                     {
                         $options = array();
                         if ($field['ProfileField']['plugin'])
                         {
                             $options = array('plugin' => $field['ProfileField']['plugin']);
                         }

                         echo $this->element('profile_field/' . $field['ProfileField']['type'].'_profile', array('field' => $field,'user'=>$user),$options);
                         continue;
                     }
                       if ( !empty( $field['ProfileFieldValue']['value'] ) && $field['ProfileField']['type'] != 'heading' ) :
                    ?>
                            <li><span class="date"><?php echo $field['ProfileField']['name']?>: </span>
                                    <?php echo $this->element( 'misc/custom_field_value', array( 'field' => $field ) ); ?>
                            </li>
                    <?php endif; 
                endforeach; 
                    ?>
                 <?php endif;?>
            </ul>
    <?php endif; ?>
    <?php if ( $canView ): ?>
	<div id="browse" class="menu">
		<ul class="list2 menu_top_list">
                    <li class="current">
                        <a class="no-ajax" href="<?php echo $this->Moo->getProfileUrl( $user['User'] )?>">
                            <?php echo __('Profile')?>
                        </a>
                    </li>
                    <li>
                        <a data-url="<?php echo $this->request->base?>/users/ajax_info/<?php echo $user['User']['id']?>" rel="profile-content" href="#">
                            <?php echo __('Info')?>
                        </a>
                    </li>
                    <li>
                        <a data-url="<?php echo $this->request->base?>/users/profile_user_friends/<?php echo $user['User']['id']?>" rel="profile-content" href="#">
                            <?php echo __('Friends')?>
                        </a>
                    </li>
                    <li class="dropdown">
                        <span id="profile_menu" class="mdl-button mdl-js-button mdl-js-ripple-effect"><?php echo __('More') ?></span>
                        <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="profile_menu">
                        	<?php if ($uid && $uid == $user['User']['id']):?>
	                        	<li class="mdl-menu__item">
									<a data-url="<?php echo $this->request->base?>/users/profile_user_blocks/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><?php echo __('Blocked Members')?></a>
								</li>
							<?php endif;?>
                            <?php if (Configure::read("core.enable_follow") && $uid && $uid == $user['User']['id']): ?>
                                <li class="mdl-menu__item">
                                    <a data-url="<?php echo $this->request->base?>/follows/user_follows/<?php echo $user['User']['id']?>" rel="profile-content" id="profile_follow" href="#"><?php echo __('Following')?></a>
                                </li>
                            <?php endif; ?>
                            <?php if (Configure::read('Photo.photo_enabled')): ?>
                            <li class="mdl-menu__item">
                                    <a data-url="<?php echo $this->request->base?>/photos/profile_user_photo/<?php echo $user['User']['id']?>" rel="profile-content" id="user_photos" href="#">
                                        <?php echo __('Albums')?>
                                    </a>
                            </li>		
                            <?php endif; ?>
                            <?php if (Configure::read('Blog.blog_enabled')): ?>
                            <li class="mdl-menu__item">
                                <a data-url="<?php echo $this->request->base?>/blogs/profile_user_blog/<?php echo $user['User']['id']?>" rel="profile-content" href="#">
                                    <?php echo __('Blogs')?>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (Configure::read('Topic.topic_enabled')): ?>
                            <li class="mdl-menu__item">
                                <a data-url="<?php echo $this->request->base?>/topics/profile_user_topic/<?php echo $user['User']['id']?>" rel="profile-content" href="#">
                                    <?php echo __('Topics')?>
                                 </a>
                            </li>		
                            <?php endif; ?>
                            <?php if (Configure::read('Video.video_enabled')): ?>
                            <li class="mdl-menu__item"><a data-url="<?php echo $this->request->base?>/videos/profile_user_video/<?php echo $user['User']['id']?>" rel="profile-content" href="#">
                                    <?php echo __('Videos')?>
                                    </a>
                            </li>	
                            <?php endif; ?>
                            <?php
                                    $this->getEventManager()->dispatch(new CakeEvent('profile.afterRenderMenu', $this)); 
                            ?>
                            <?php
                            if ( $this->elementExists('menu/user') )
                                echo $this->element('menu/user');
                            ?>
                        </ul>
                    </li>
                    
		</ul>
	</div>
    <?php endif; ?>
</div>