<?php $this->setCurrentStyle(4) ?>
<?php
$groupHelper = MooCore::getInstance()->getHelper('Group_Group');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooGroup'), 'object' => array('$', 'mooGroup'))); ?>
mooGroup.initOnCreate();
<?php $this->Html->scriptEnd(); ?>

<div class="create_form">
    <div class="bar-content">
        <div class="content_center">
            <div class="box3">
                <form id="createForm">
                    <?php
                    if (!empty($group['Group']['id'])){
                        echo $this->Form->hidden('id', array('value' => $group['Group']['id']));
                        echo $this->Form->hidden('photo', array('value' => $group['Group']['photo']));
                    }else{
                        echo $this->Form->hidden('photo', array('value' => ''));
                    }
                    ?>
                    <div class="mo_breadcrumb">
                        <h1><?php if (empty($group['Group']['id'])) echo __( 'Add New Group');
                    else echo __( 'Edit Group'); ?></h1>
                    </div>
                    <div class="full_content">
                        <div class="form_content">
                            <ul>
                                 <li>
                                    <div class="thumb_content">
                                        <div class="thumb_item">
                                            <?php if (!empty($group['Group']['photo'])): ?>
                                            <img width="150" id="item-avatar" class="img_wrapper" style="background-image:url(<?php echo $groupHelper->getImage($group, array('prefix' => '150_square'))?>)" src="<?php echo $this->request->webroot?>theme/<?php echo $this->theme ?>/img/s.png" />
                                            <?php else: ?>
                                                <img width="150" id="item-avatar" class="img_wrapper" style="display: none;" src="<?php echo $this->Storage->getImage("theme/".$this->theme."/img/s.png");?>" />
                                            <?php endif; ?>

                                        </div>
                                        <div id="select-0" class="item_upload_thumb" style="margin: 10px 0 0 0px;"></div>
                                        <div class="thumb_text">
                                            <h4><?php echo __('Upload Group Thumb') ?></h4>
                                            <div><?php echo __('Click thumb to upload') ?></div>
                                        </div>
                                    </div>    
                                    
                                </li>
                                <li>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input name="name" class="mdl-textfield__input" type="text" value="<?php echo $group['Group']['name'] ?>" />
                                        <label class="mdl-textfield__label"><?php echo  __( 'Group Name') ?></label>
                                    </div>
                                </li>
                                <li>
                                     <?php echo $this->Form->select( 'category_id', $categories, array('empty' => false, 'value' => $group['Group']['category_id'] ) ); ?> 
                                    
                                </li>
                                <li>
                                    <div>
                                        <label><?php echo __('Description') ?></label>
                                    </div>
                                    <?php echo $this->Form->tinyMCE('description', array('value' => $group['Group']['description'], 'id' => 'editor')); ?>
                                </li>
                                <li>
                                        <?php
                                        echo $this->Form->select('type', array(PRIVACY_PUBLIC => __( 'Public'),
                                            PRIVACY_PRIVATE => __( 'Private'),
                                            PRIVACY_RESTRICTED => __( 'Restricted')
                                                ), array('value' => $group['Group']['type'], 'empty' => false)
                                        );
                                        ?>
                                        
                                    
                                </li>
                                
                                <li>
                                    
                                        <button type='button' id='saveBtn' class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'>
                                            <?php echo __( 'Save'); ?>
                                        </button>
                                        
                                        <?php if (!empty($group['Group']['id'])): ?>

                                            <a href="<?php echo  $this->request->base ?>/groups/view/<?php echo  $group['Group']['id'] ?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo  __( 'Cancel') ?></a>

                                        <?php endif; ?>
                                   
                                </li>
                            </ul>
                            <div class="error-message" style="display:none;"></div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>