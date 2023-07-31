<div class="bar-content">
    <div class="content_center full_content p_m_10">
        
        
        <ul class="users_list" id="list-content">
            <?php
            if (!empty($values) || !empty($online_filter))
                echo __('Loading...');
            else
                echo $this->element('lists/users_list', array('more_url' => '/users/ajax_browse/all/page:2'));
            ?>
        </ul>
        <div class="clear"></div>
    </div>
</div>