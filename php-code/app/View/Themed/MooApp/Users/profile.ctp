<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initOnUserProfile();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooUser'), 'object' => array('$', 'mooUser'))); ?>
mooUser.initOnUserProfile();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
<div class="bar-content">
    
</div>
<?php $this->end(); ?>
<div class="bar-content ">
    <div class="content_center profile-info-edit">
        <form id="form_edit_user" method="post">
        <div id="center">
             <a href="<?php echo $this->request->base?>/users/view/<?php echo $uid?>" class="pull-right mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored2"><?php echo __('View Profile')?></a>
            <div class="full_content">
                <div class="content_center">
                <?php echo $this->element('ajax/profile_edit');?>
                <div class="edit-profile-section" style="border:none">
                    <?php if ( !$cuser['Role']['is_super'] ): ?>
                        <ul class="list6 list6sm" style="margin:10px 0">
                            <li><a href="javascript:void(0)" class="deactiveMyAccount" ><?php echo __('Deactivate my account')?></a></li>
                            <li><a href="javascript:void(0)" class="deleteMyAccount"><?php echo __('Request to permanently delete my account')?></a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                </div>
            </div>
        </div>
        </form>
    </div>
</div>