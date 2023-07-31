<script type="text/javascript">
    require(["jquery","mooBehavior"], function($, mooBehavior) {
        mooBehavior.initMoreResults();
    });
</script>

<?php if ($page == 1):?>
<div class="title-modal">
    <?php echo __('Edit History') ?>
     <button type="button" class="hide_popup"  data-dismiss="modal"><span aria-hidden="true"><i class="material-icons md-24">close</i></span></button>
</div>
<?php endif;?>
<div class="modal-body">
<?php if ($page == 1):?>
<ul id="list-content-history " class="edit-history">
<?php endif;?>
<?php
	foreach ($histories as $history){
		?>
		<li>
			<?php echo $this->Moo->getItemPhoto(array('User' => $history['User']), array( 'prefix' => '50_square'))?>
                    <div>
                        <div><?php echo $this->Moo->getName($history['User'])?></div>
			<?php echo $this->Moo->getTime( $history['CommentHistory']['created'], Configure::read('core.date_format'), $utz )?>
			<p><?php echo $this->viewMore(h($history['CommentHistory']['content']));?></p>
			<?php if ($history['CommentHistory']['photo']):?>
				<p class="comment-edited">
				<?php
					switch ($history['CommentHistory']['photo']) {
						case 1: echo __('Added photo attachment.');
						break;
						case 2: echo __('Replaced photo attachment.');
						break;
						case 3: echo __('Deleted photo attachment.');
						break;						
					}
				?>
				</p>	
			<?php endif;?>
                    </div>
		</li>
		<?php 	
	} 
	if ($historiesCount > $page * RESULTS_LIMIT)
	{
		?>
		<li>
			<?php $this->Html->viewMore($more_url, 'list-content-history'); ?>
		</li>
		<?php 
	}
?>
<?php if ($page == 1):?>
</ul>
<?php endif;?>
</div>