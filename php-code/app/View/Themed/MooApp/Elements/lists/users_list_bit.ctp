<?php
if (count($users) > 0)
{
	foreach ($users as $user):
?>
	<li <?php if ( isset($type) && $type == 'home' ): ?>id="friend_<?php echo $user['Friend']['friend_id']?>"<?php endif; ?>
		<?php if ( isset($group) ): ?>id="member_<?php echo $user['GroupUser']['id']?>"<?php endif; ?>
        class="user-list-index">
            <div class="list-content">
                <div class="user-idx-item">

                       <?php echo $this->Moo->getItemPhoto(array('User' => $user['User']), array('prefix' => '200_square','class'=>'user_list_thumb'))?>
				<?php
                   if(isset($user_block)){
                        $this->MooPopup->tag(array(
                               'href'=>$this->Html->url(array("controller" => "user_blocks",
                                                              "action" => "ajax_remove",
                                                              "plugin" => false,
                                                              $user['User']['id']
                                                          )),
                               'title' => __('Unblock'),
                               'innerHtml'=> '<i class="icon-delete"></i> ',
                            'id' => 'unblock_'.$user['User']['id'],
                            'class' => 'add_people unblock',
                            'style' => 'float:right;',
                       ));
                   }
                ?>
		<div class="user-list-info">
                        <div class="user-name-info">
			<?php echo $this->Moo->getName($user['User'])?>
                        </div>

                    <span class="date">
                            <?php echo __n( '%s friend', '%s friends', $user['User']['friend_count'], $user['User']['friend_count'] )?> .
                            <?php echo __n( '%s photo', '%s photos', $user['User']['photo_count'], $user['User']['photo_count'] )?><br />


                    </span>
                    <?php if($user['User']['id'] != $uid): ?>
                    <?php $is_content = false;?>
                    <?php
                    	ob_start(); 
                    ?>
                    <div class="dropdown user_list_dropdown">
                        <button id="user_action_<?php echo $user['User']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                            <i class="material-icons">more_vert</i>
                        </button>
                        <ul id="user_action_list_<?php echo $user['User']['id']?>" class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="user_action_<?php echo $user['User']['id']?>">

                            <?php if ( isset($friends_request) && in_array($user['User']['id'], $friends_request) && $user['User']['id'] != $uid): ?>
                            	<?php $is_content = true;?>
                                <li class="mdl-menu__item ">
                                <a href="<?php echo $this->request->base?>/friends/ajax_cancel/<?php echo $user['User']['id']?>" id="cancelFriend_<?php echo $user['User']['id']?>" class="add_people cancel_request" title="<?php __('Cancel a friend request');?>">
                                    <?php echo __('Cancel Request')?>
                                </a>
                                </li>
                            <?php elseif ( !empty($respond) && in_array($user['User']['id'], $respond ) && $user['User']['id'] != $uid): ?>
								<?php $is_content = true;?>
                                <li class="mdl-menu__item"><a data-id="<?php echo  $request_id[$user['User']['id']]; ?>" data-status="1" class="respondRequest" href="javascript:void(0)"><?php echo  __('Accept'); ?></a></li>
                                <li class="mdl-menu__item"><a data-id="<?php echo  $request_id[$user['User']['id']]; ?>" data-status="0" class="respondRequest" href="javascript:void(0)"><?php echo  __('Delete'); ?></a></li>


                            <?php elseif (isset($friends) && in_array($user['User']['id'], $friends) && $user['User']['id'] != $uid): ?>
                            	<?php $is_content = true;?>
                                <li class="mdl-menu__item">
                                <?php
                                    $this->MooPopup->tag(array(
                                           'href'=>$this->Html->url(array("controller" => "friends",
                                                                          "action" => "ajax_remove",
                                                                          "plugin" => false,
                                                                          $user['User']['id']
                                                                      )),
                                           'title' => __('Remove'),
                                           'innerHtml'=> __('Remove'),
                                        'id' => 'removeFriend_'.$user['User']['id'],
                                        'class' => 'add_people'
                                   ));
                               ?>
                                </li>
                               <?php elseif(isset($friends) && isset($friends_request) && !in_array($user['User']['id'], $friends) && !in_array($user['User']['id'], $friends_request) && $user['User']['id'] != $uid): ?>
                               <?php $is_content = true;?>
                                <li class="mdl-menu__item">
                                   <?php
                                    $this->MooPopup->tag(array(
                                           'href'=>$this->Html->url(array("controller" => "friends",
                                                                          "action" => "ajax_add",
                                                                          "plugin" => false,
                                                                          $user['User']['id']
                                                                      )),
                                           'title' => sprintf( __('Send %s a friend request'), h($user['User']['name']) ),
                                           'innerHtml'=> __('Add'),
                                        'id' => 'addFriend_'. $user['User']['id'],
                                        'class'=> 'add_people'
                                   ));
                               ?>
                                    </li>
                                                  <?php endif; ?>




                                <?php if ( isset($type) && $type == 'home' ): ?>
                                <?php $is_content = true;?>
                                <li class="mdl-menu__item">
                                              <?php
                                    $this->MooPopup->tag(array(
                                           'href'=>$this->Html->url(array("controller" => "friends",
                                                                          "action" => "ajax_remove",
                                                                          "plugin" => false,
                                                                          $user['User']['id']
                                                                      )),
                                           'title' => '',
                                           'innerHtml'=> __('Remove Friend'),
                                        'id' => 'removeFriend_'. $user['User']['id']
                                   ));
                               ?>
                                </li>
                                <?php endif; ?>


                    <?php if ( isset($group) && isset($admins) && $user['User']['id'] != $uid && $group['User']['id'] != $user['User']['id'] &&
                                       ( !empty($cuser['Role']['is_admin']) || in_array($uid, $admins) ) ):
                    ?>
                    <?php $is_content = true;?>
                    <li class="mdl-menu__item">
                    <a href="javascript:void(0)" class="removeMember" data-id="<?php echo $user['GroupUser']['id']?>"><?php echo __('Remove Member')?></a> .
                    </li>
                    <?php endif; ?>

                    <?php if ( isset($group) && isset($admins) && !in_array($user['User']['id'], $admins) &&
                                       ( !empty($cuser['Role']['is_admin']) || $uid == $group['User']['id'] ) ):
                    ?>
                    <?php $is_content = true;?>
                    <li class="mdl-menu__item">
                    <a href="javascript:void(0)" class="changeAdmin" data-id="<?php echo $user['GroupUser']['id']?>" data-type="make"><?php echo __('Make Admin')?></a>
                    </li>
                    <?php endif; ?>

                    <?php if ( isset($group) && isset($admins) && in_array($user['User']['id'], $admins) && $user['User']['id'] != $group['User']['id'] &&
                                       ( !empty($cuser['Role']['is_admin']) || $uid == $group['User']['id'] ) ):
                    ?>
                    <?php $is_content = true;?>
                    <li class="mdl-menu__item">
                    <a href="javascript:void(0)" class="changeAdmin" data-id="<?php echo $user['GroupUser']['id']?>" data-type="remove"><?php echo __('Remove Admin')?></a>
                    </li>
                    <?php endif; ?>
            </ul>
            </div>
            <?php
               	$page = ob_get_contents();
   				ob_end_clean(); 
   				if ($is_content) echo $page;
               ?>
               <?php endif; ?>               
		</div>
		</div>
            </div>
        <?php //$this->Html->rating($user['User']['id'],'users'); ?>
	</li>
<?php
	endforeach;
}
else
	echo '<div class="clear">' . __('No more results found') . '</div>';
?>
