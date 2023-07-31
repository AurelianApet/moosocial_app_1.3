<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooActivities"], function($, mooActivities) {
        mooActivities.initOnAjaxLoadActivityEdit();
    });
</script>
<?php else: ?>
<?php endif; ?>

<div id="activity_edit_<?php echo $activity['Activity']['id']?>">
	<?php echo $this->Form->textarea("message_edit_".$activity['Activity']['id']."",array('name' => "message", 'value' => $activity['Activity']['content'], 'style' => 'width:100%;margin-top:0px;'),true ); ?>
    <div class="edit-post-action">
            <a class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored admin-or-owner-cancel-edit-activity cancelEditActivity" data-activity-id="<?php echo $activity['Activity']['id'];?>" href="javascript:void(0);" ><?php echo __('Cancel');?></a>
            <a class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored admin-or-owner-confirm-edit-activity confirmEditActivity" data-activity-id="<?php echo $activity['Activity']['id'];?>" href="javascript:void(0);" ><?php echo __('Done Editing');?></a>
	</div>
</div>