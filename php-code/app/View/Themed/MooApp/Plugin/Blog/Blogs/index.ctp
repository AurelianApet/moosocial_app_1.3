<?php
echo $this->Html->css(array('jquery.mp'), null, array('inline' => false));
echo $this->Html->script(array('jquery.mp.min'), array('inline' => false)); 
?>


<div class="bar-content">
    <div class="content_center">
	
        <div class="mo_breadcrumb">
            
            <?php if (!empty($uid)): ?>
            <a href="<?php echo $this->request->base?>/blogs/create" class="topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1">
               <?php echo __('New Blog') ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="filter_block">
            <div id="filters" style="margin-top:5px">
                        <?php if(!Configure::read('core.guest_search') && empty($uid)): ?>
                        <?php else: ?>
                <?php echo $this->Form->text( 'keyword', array( 'placeholder' => __('Search Blogs'), 'rel' => 'blogs', 'class' => 'json-view') );?>
                        <?php endif; ?>
            </div>
        </div>
        <ul id="list-content">
	        <?php echo $this->element( 'lists/blogs_list', array( 'more_url' => '/blogs/browse/all/page:2' ) ); ?>
        </ul>
    </div>
</div>
