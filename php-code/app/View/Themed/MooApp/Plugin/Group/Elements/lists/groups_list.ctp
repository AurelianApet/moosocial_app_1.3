

<?php
if(Configure::read('Group.group_enabled') == 1):
$groupHelper = MooCore::getInstance()->getHelper('Group_Group');
?>
<ul class="group-content-list">
<?php
if (!empty($groups) && count($groups) > 0):
    $i = 1;
    foreach ($groups as $group):
        
        ?>
        <li class="full_content" <?php if ($i == count($groups)) echo 'style="border-bottom:0"'; ?>>
            <a href="<?php echo  $this->request->base ?>/groups/view/<?php echo  $group['Group']['id'] ?>/<?php echo  seoUrl($group['Group']['name']) ?>">
                <img src="<?php echo $groupHelper->getImage($group, array('prefix' => '150_square'))?>" class="group-thumb" />
            </a>
            <div class="group-info">
                <a class="title" href="<?php echo  $this->request->base ?>/groups/view/<?php echo  $group['Group']['id'] ?>/<?php echo  seoUrl($group['Group']['name']) ?>"><b><?php echo  h($group['Group']['name']) ?></b></a>

                <div class="extra_info">
                    <?php
                    switch ($group['Group']['type']) {
                        case PRIVACY_PUBLIC:
                            echo __( 'Public');
                            break;

                        case PRIVACY_RESTRICTED:
                            echo __( 'Restricted');
                            break;

                        case PRIVACY_PRIVATE:
                            echo __( 'Private');
                            break;
                    }
                    ?> . 
                    <?php echo  __n('%s member', '%s members', $group['Group']['group_user_count'], $group['Group']['group_user_count']) ?>
                </div>
               
                <?php //$this->Html->rating($group['Group']['id'],'groups', 'Group'); ?>

            </div>

            <?php if (!empty($uid) && ( ( !empty($group['Group']['my_status']) && $group['Group']['my_status']['GroupUser']['status'] == GROUP_USER_ADMIN ) || !empty($cuser['Role']['is_admin'] )) ): ?>
                <div class="list_option">
                    <?php if ( ( !empty($group['Group']['my_status']) && $group['Group']['my_status']['GroupUser']['status'] == GROUP_USER_ADMIN ) || $group['Group']['type'] != PRIVACY_PRIVATE  || !empty($cuser['Role']['is_admin'] )): ?>
                    <div class="dropdown">
                        <button id="group_edit_<?php echo $group['Group']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                            <i class="material-icons">more_vert</i>
                        </button>
                        <?php //debug( ( !empty($group['Group']['my_status']) && $group['Group']['my_status']['GroupUser']['status'] == GROUP_USER_ADMIN ) || !empty($cuser['Role']['is_admin'] )); ?>
                        <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="group_edit_<?php echo $group['Group']['id']?>">
                            <?php if ( ( !empty($group['Group']['my_status']) && $group['Group']['my_status']['GroupUser']['status'] == GROUP_USER_ADMIN && $group['Group']['user_id'] == $uid) || !empty($cuser['Role']['is_admin'] ) ): ?>
                                <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/groups/create/<?php echo $group['Group']['id']?>"><?php echo __( 'Edit Group')?></a></li>
                                <li class="mdl-menu__item"><a href="javascript:void(0)" data-id="<?php echo  $group['Group']['id'] ?>" class="deleteGroup"><?php echo __( 'Delete Group')?></a></li>
                            <?php endif; ?>
                            <?php if ( !empty($my_status) && ( $my_status['GroupUser']['status'] == GROUP_USER_MEMBER || $my_status['GroupUser']['status'] == GROUP_USER_ADMIN ) && ( $uid != $group['Group']['user_id'] ) ): ?>
                                <li class="mdl-menu__item"><a href="javascript:void(0)" onclick="mooConfirm('<?php echo addslashes(__('Are you sure you want to leave this group?'))?>', '<?php echo $this->request->base?>/groups/do_leave/<?php echo $group['Group']['id']?>')"><?php echo __('Leave Group')?></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </li>
        <?php
        $i++;
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
endif;
?>
</ul>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooGroup"], function($,mooGroup) {
        mooGroup.initOnListing();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooGroup'), 'object' => array('$', 'mooGroup'))); ?>
mooGroup.initOnListing();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>