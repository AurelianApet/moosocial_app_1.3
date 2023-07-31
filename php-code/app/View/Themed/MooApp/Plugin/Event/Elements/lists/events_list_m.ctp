<div class="content_center_home">
    <div class="mo_breadcrumb">
    	<?php echo $this->element('lists/categories_list')?>
        <a href="<?php echo $this->request->base ?>/events/create" class="topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo __('New Event') ?></a>

    </div>
    <ul class="event_content_list" id="list-content">
        <?php echo $this->element('lists/events_list'); ?>
    </ul>
</div>