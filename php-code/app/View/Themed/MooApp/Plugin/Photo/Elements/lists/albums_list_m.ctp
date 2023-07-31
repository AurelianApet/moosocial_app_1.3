<div class="bar-content">
<div class="content_center">
    <div class="mo_breadcrumb">
        <?php echo $this->element('lists/categories_list')?>
        <?php
        $this->MooPopup->tag(array(
            'href' => $this->Html->url(array("controller" => "albums",
                "action" => "create",
                "plugin" => 'photo',
            )),
            'title' => __('Create New Album'),
            'innerHtml' => __('New Album'),
            'class' => 'topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
        ));
        ?>
    </div>
    <div class="filter_block">
                 
        <?php echo $this->element('sidebar/search'); ?>
           
    </div>
    <ul class="albums photo-albums" id="album-list-content">
        <?php echo $this->element('lists/albums_list'); ?>
    </ul>
    <div class="clear"></div>
    </div>
</div>