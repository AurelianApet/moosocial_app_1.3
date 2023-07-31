<?php
$link = unserialize($activity['Activity']['params']);
$url = (isset($link['url']) ? $link['url'] : $activity['Activity']['content']);
?>
<div class="feed_text comment_message">
	<?php echo $this->viewMore(h($activity['Activity']['content']),null, null, null, true, array('no_replace_ssl' => 1));?>
	<?php if(!empty($activity['UserTagging']['users_taggings'])) $this->MooPeople->with($activity['UserTagging']['id'], $activity['UserTagging']['users_taggings']); ?>
</div>
<?php  if ( !empty( $link['title'] ) ): ?>
<div class="activity_item link_feed">
    
    <?php if ( !empty( $link['image'] ) ): 
         if ( strpos( $link['image'], 'http' ) === false ):
                                $link_image = $this->request->webroot . 'uploads/links/' .  $link['image'] ;
                            else:
                                $link_image = $link['image'];
                            endif;
        ?>
    <div class="activity_left">
    <img src="<?php echo $this->request->webroot ?>theme/<?php echo $this->theme ?>/img/s.png" style="background-image:url(<?php echo $link_image ?>)" />
    </div>
    <?php endif; ?>
    <div class="<?php if ( !empty( $link['image'] ) ): ?>activity_right <?php endif; ?>">
        <a class="feed_title" href="<?php echo $url;?>" target="_blank" rel="nofollow">
            <strong><?php echo h($link['title'])?></strong>            
        </a>
        
         <?php
        if ( !empty( $link['description'] ) )
            echo '<div class=" feed_detail_text">' . ($this->Text->truncate($link['description'], 150, array('exact' => false))) . '</div>';
        ?>
    </div>
</div>
<?php endif;?>