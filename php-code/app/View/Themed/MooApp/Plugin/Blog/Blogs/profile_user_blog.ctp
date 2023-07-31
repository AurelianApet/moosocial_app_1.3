
<div class="bar-content">
    <div class="content_center">
        <div class="title_center p_m_10">
        <?php if ($user_id == $uid): ?>
            <div >
                <a href="<?php echo  $this->request->base ?>/blogs/create" class="topButton  mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1">
                   
                            <?php echo  __('New Blog') ?>
                    
                    
                </a>
            </div>
        <?php endif; ?>
            <h2 class="header_h2"><?php echo  __( 'Blogs') ?></h2>
        </div>
        <ul class="list6 comment_wrapper list-mobile" id="list-content">
            <?php echo $this->element('lists/blogs_list', array('user_blog' => true)); ?>
        </ul>
    </div>
</div>