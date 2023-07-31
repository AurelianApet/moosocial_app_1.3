<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooGroup"], function($, mooGroup) {
        mooGroup.initTabPhoto1();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooGroup'), 'object' => array('$', 'mooGroup'))); ?>
mooGroup.initTabPhoto1();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php
	$group = MooCore::getInstance()->getItemByType('Group_Group',$target_id);
	$is_member = $this->Group->checkPostStatus($group,$uid);

?>
<div class="bar-content">
	<div class="content_center">
		<div class="mo_breadcrumb">
			
            <?php if ( !empty( $is_member ) ){?> 
            	<a href="javascript:void(0)" data-group-id="<?php echo $target_id; ?>" class="groupUploadPhoto topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1" ><?php echo __('New Photos');?></a>
            <?php }?>
            <div class="clear"></div>
            <div class="full_content p_m_10">
            <div class="<?php if ( !empty( $is_member ) ): ?> p_top_15<?php endif; ?>">
            	<?php  echo $this->element( 'lists/photos_list', array('plugin'=>'Photo' ) );?>
            </div>
		</div>
	</div>
</div>