<div class="bar-content">
<div class="content_center">
    <div class="mo_breadcrumb">
        <?php echo $this->element('lists/categories_list')?>
        <?php if (!empty($uid)): ?> 
        <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "videos",
                                            "action" => "create",
                                            "plugin" => 'video',
                                            
                                        )),
             'title' => __( 'Share New Video'),
             'innerHtml'=> __( 'New Video'),
            'data-backdrop' => 'static',
          'class' => 'topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
     ));
            ?>
        <?php endif; ?>
       
    </div>
    <div class="filter_block">
            <?php echo $this->element('sidebar/search'); ?>
    </div>
    <ul class="albums" id="list-content">
        <?php echo $this->element('lists/videos_list'); ?>
    </ul>
    </div>
</div>