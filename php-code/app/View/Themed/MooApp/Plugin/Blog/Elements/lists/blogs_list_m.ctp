<div class="bar_content">
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
    <ul class="list6 comment_wrapper list-mobile" id="list-content">
        <?php echo $this->element('lists/blogs_list', array('user_blog' => true)); ?>
    </ul> 
    </div>
</div>