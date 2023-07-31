
<div class="bar-content">
    <div class="content_center">
        <div class="title_center p_m_10">
        <?php if ($user_id == $uid): ?>
            
                <a href="<?php echo  $this->request->base ?>/topics/create" class="topButton btnTopic mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1">
                    
                            <?php echo  __('New Topic') ?>
                    
                </a>    
            
        <?php endif; ?>
            <h2 class="header_h2"><?php echo  __( 'Topics') ?></h2>
        </div>
        <ul class="list6 comment_wrapper" id="list-content">
            <?php echo $this->element('lists/topics_list'); ?>
        </ul>
    </div>
</div>