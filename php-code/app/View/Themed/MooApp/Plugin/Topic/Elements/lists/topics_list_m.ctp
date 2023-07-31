<div class="bar-content">
<div class="content_center">
    <div class="mo_breadcrumb">
    
    	<?php echo $this->element('lists/categories_list')?>
        <a href="<?php echo $this->request->base ?>/topics/create" class="topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo __('New Topic') ?></a>

    </div>
    <ul class="list6 comment_wrapper" id="list-content">
        <?php echo $this->element('lists/topics_list'); ?>
    </ul>
</div>
</div>