<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooPhoto"], function($,mooPhoto) {
        mooPhoto.initOnCreateAlbum();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooPhoto'), 'object' => array('$', 'mooPhoto'))); ?>
mooPhoto.initOnCreateAlbum();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php
$tags_value = '';
if (!empty($tags)) $tags_value = implode(', ', $tags);
?>
<div class="title-modal popup_create_title">
    <?php if (isset($album['Album']['id']) && $album['Album']['id']):?>
    <?php echo __( 'Edit Album')?>
    <?php else: ?>
    <?php echo __( 'Create New Album')?>
    <?php endif; ?>
    <button type="button" class="close" data-dismiss="modal">
        <i class="material-icons md-24">close</i> 
    </button>
</div>
<div class="modal-body popup_create_body">
<div class="create_form">
<form id="createForm">
<?php echo $this->Form->hidden('id', array('value' => $album['Album']['id'])); ?>
<ul style="position:relative">
	<li>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <input name="title" class="mdl-textfield__input" type="text" value="<?php echo $album['Album']['title'] ?>" />
                <label class="mdl-textfield__label" ><?php echo __( 'Album Title')?></label>
            </div>
	</li>
	<li>
        <?php echo $this->Form->select( 'category_id', $categories, array('empty' => false, 'value' => $album['Album']['category_id'] ) ); ?>
            
            
	</li>
	<li>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <textarea name="description" class="mdl-textfield__input" type="text" ><?php echo $album['Album']['description'] ?></textarea>
                <label class="mdl-textfield__label" ><?php echo __( 'Description')?></label>
            </div>            
	</li>
	<li>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <input name="tags" class="mdl-textfield__input" type="text" value="<?php echo $tags_value ?>" />
                <label class="mdl-textfield__label" ><?php echo __( 'Tags')?></label>
            </div> 
	</li>
	<li>
           
		<?php echo $this->Form->select('privacy', array( PRIVACY_EVERYONE => __( 'Everyone'), 
                                                                 PRIVACY_FRIENDS  => __( 'Friends Only'), 
                                                                 PRIVACY_ME 	  => __( 'Only Me') 
                                                 ), 
                                                 array( 'value' => $album['Album']['privacy'], 
                                                                'empty' => false
                               ) ); 
		?>
           
	</li>
        <li style="margin:20px 0;">
            <a type='button' class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1' id="saveBtn">
                    <?php echo __('Save')?>
            </a>   
            <a href="#" class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1' data-dismiss="modal"><?php echo __('Cancel') ?></a>        
	</li>
</ul>
</form>
</div>
</div>
<div class="error-message" style="display:none;"></div>