<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooPhoto"], function($, mooPhoto) {
        mooPhoto.initOnPhotoView({
            photo_id : <?php echo $photo['Photo']['id']?>,
            photo_thumb : '<?php echo $photo['Photo']['thumbnail']?>',
            tag_uid : <?php echo isset($this->request->named['uid']) ? $this->request->named['uid'] : 0?>,
            taguserid : <?php echo isset($this->request->query['uid']) ? $this->request->query['uid'] : 0?>,
            type : '<?php echo $type; ?>',
            target_id : <?php echo $target_id?>,
            album_type : '<?php echo $photo['Photo']['album_type']; ?>',
            album_type_id : <?php echo $photo['Photo']['album_type_id']; ?>
        });
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooPhoto'), 'object' => array('$', 'mooPhoto'))); ?>
mooPhoto.initOnPhotoView({
    photo_id : <?php echo $photo['Photo']['id']?>,
    photo_thumb : '<?php echo $photo['Photo']['thumbnail']?>',
    tag_uid : <?php echo isset($this->request->named['uid']) ? $this->request->named['uid'] : 0?>,
    taguserid : <?php echo isset($this->request->query['uid']) ? $this->request->query['uid'] : 0?>,
    type : '<?php echo $type?>',
    target_id : <?php echo $target_id?>,
    album_type : '<?php echo $photo['Photo']['album_type']; ?>',
    album_type_id : <?php echo $photo['Photo']['album_type_id']; ?>
});
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>
<?php
$photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
?>
<div id="photo_wrapper" >

    <div id="tag-wrapper">
        <img src="<?php if ($is_redirect) echo $this->Storage->getImage('/photo/img/noimage/privacy.png'); else echo $photoHelper->getImage($photo, array('prefix' => '1500')) . '?' . time();?>" id="photo_src">
        <div id="tag-target"></div>
        <div id="tag-input">
            <?php echo __( "Enter person's name")?>
            <input type="text" id="tag-name">
            <?php echo __( 'Or select a friend')?>
            <div id="friends_list" class="tag_friends_list"></div>
            <a href="#" id="tag-submit" class="button button-action"><?php echo __( 'Submit')?></a>
            <a href="#" id="tag-cancel" class="button"><?php echo __( 'Cancel')?></a>
        </div>
        <?php 
        foreach ( $photo_tags as $tag ): 
        ?>
        <div style="<?php echo $tag['PhotoTag']['style']?>" class="hotspot" id="hotspot-0-<?php echo $tag['PhotoTag']['id']?>"><span>
            <?php
            if ( $tag['PhotoTag']['user_id'] )
                echo $this->Moo->getName( $tag['User'], false );
            else
                echo h($tag['PhotoTag']['value']);
            ?>
        </span></div>
        <?php
        endforeach;
        ?>        
    </div>
    
    
    
    <?php if ( ( $photo['Photo']['type'] == 'Group_Group' ) ):?>
    <a href="<?php echo $this->request->base?>/groups/view/<?php echo $photo['Photo']['target_id']?>" id="photo_close_icon" class="lb_icon"><i class="icon-delete icon-2x topButton"></i></a>
    <?php elseif ( ( $photo['Photo']['type'] == 'Photo_Album' ) ): ?>
    <a href="<?php echo $this->request->base?>/albums/view/<?php echo $photo['Photo']['target_id']?>/<?php echo seoUrl($photo['Album']['moo_title'])?>" id="photo_close_icon" class="lb_icon"><i class="icon-delete icon-2x topButton"></i></a>
    <?php endif; ?>

    <?php if (!empty($neighbors['next']['Photo']['id'])): ?>
    <a href="javascript:void(0)" data-id="<?php echo $neighbors['next']['Photo']['id']?>" id="photo_left_arrow" class="showPhoto lb_icon"><i class="icon-left-open-big icon-4x"></i></a>
    <?php endif; ?>
    
    <?php if (!empty($neighbors['prev']['Photo']['id'])): ?>
    <a href="javascript:void(0)" data-id="<?php echo $neighbors['prev']['Photo']['id']?>" id="photo_right_arrow" class="showPhoto lb_icon"><i class="icon-right-open-big icon-4x"></i></a>
    <?php endif; ?>
    <?php
        $nextPhoto = '';
        if(!empty($neighbors['next']['Photo']['id']))
            $nextPhoto = $neighbors['next']['Photo']['id'];
        else if(empty($neighbors['next']['Photo']['id']) && !empty($neighbors['prev']['Photo']['id']))
            $nextPhoto = $neighbors['prev']['Photo']['id'];
    ?>
</div>
<?php if ($is_show_full_photo):?>
<div class="photo_comments">
    <div class="photo_right col-md-sl2 pull-right full_content">
        <div class="bar-content">
            <div class="content_center">
                
                <div id="lb_description">
                    <div class="dropdown">
                        <button id="photo_edit_<?php echo $photo['Photo']['target_id'] ?>" class="topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                            <i class="material-icons">more_vert</i>
                        </button>
                        <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="photo_edit_<?php echo $photo['Photo']['target_id'] ?>">      
                            <?php if ($uid == $photo['Photo']['user_id']): ?>
                            <li class="mdl-menu__item"><a href="javascript:void(0);"  class="set_cover"><?php echo __( 'Set as cover')?></a><span id="set_cover"></span></li>
                            <li class="mdl-menu__item"><a href="javascript:void(0);"  class="set_avatar"><?php echo __( 'Set as profile picture')?></a><span id="set_avatar"></span></li>
                            <?php endif; ?>

                            <?php if ( ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $admins ) && in_array( $uid, $admins ) ) ): ?>

                                <li class="mdl-menu__item"><a href="javascript:void(0)" data-next-photo="<?php echo  $nextPhoto; ?>" id="delete_photo"><?php echo __( 'Delete Photo')?></a></li>
                            <?php endif; ?>
                            <li class="mdl-menu__item">
                                 <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "reports",
                                            "action" => "ajax_create",
                                            "plugin" => false,
                                            'photo_photo',
                                            $photo['Photo']['id'],
                                        )),
             'title' => __( 'Report Photo'),
             'innerHtml'=> __( 'Report Photo'),
     ));
 ?>
                             </li>
                            <?php if ($photo['Album']['privacy'] != PRIVACY_ME && $photo['Group']['moo_privacy'] != PRIVACY_RESTRICTED && $photo['Group']['moo_privacy'] != PRIVACY_PRIVATE): ?>
                         <!-- not allow sharing only me item -->
                        <li class="mdl-menu__item">
                            <a href="javascript:void(0);" share-url="<?php echo $this->Html->url(array(
                                  'plugin' => false,
                                  'controller' => 'share',
                                  'action' => 'ajax_share',
                                  'Photo_Photo',
                                  'id' => $photo['Photo']['id'],
                                  'type' => 'photo_item_detail'
                              ), true); ?>" class="shareFeedBtn"><?php echo __('Share'); ?></a>
                        </li>
                        <?php endif; ?>
                            <?php if ( $can_tag ): ?>
            <li id="tagPhoto" class="hidden-xs hidden-sm"><a href="javascript:void(0)" onclick="tagPhoto()"><i class="icon-tag"></i> <?php echo __( 'Tag Photo')?></a></li>
            <?php endif; ?>
            <?php if ( !empty( $photo['Photo']['original'] ) ): ?>
            <li><a href="<?php echo $this->request->webroot?><?php echo $photo['Photo']['original']?>" target="_blank"><i class="icon-download-alt"></i> <?php echo __( 'Download Hi-res')?></a></li>
            <?php endif; ?>
			    
                        </ul>   
                    </div>
                    <h1>
                    <?php if ( $photo['Photo']['type'] == 'Group_Group' ): ?>

                    <a href="<?php echo $this->request->base?>/groups/view/<?php echo $photo['Photo']['target_id']?>/<?php echo seoUrl($photo['Group']['name'])?>"><?php echo __( 'Photos of %s', h($photo['Group']['name']))?></a>
                    <?php else: ?>
                    <a href="<?php echo $this->request->base?>/albums/view/<?php echo $photo['Photo']['target_id']?>/<?php echo seoUrl($photo['Album']['moo_title'])?>"><?php echo h($photo['Album']['moo_title'])?></a>
                    <?php endif; ?> 
                    </h1>
      
    </div>
                <?php if ( !empty($photo['Photo']['like_count']) ): ?>
                <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "likes",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'Photo_Photo',
                                            $photo['Photo']['id']
                                        )),
             'title' => __( 'People Who Like This'),
             'innerHtml'=> __n('%s person likes this', '%s people like this', $photo['Photo']['like_count'], $photo['Photo']['like_count'] ),
          'data-dismiss' => 'modal'
     ));
 ?>
              
                <?php endif; ?>
<div class="photo_small_info">
                <div class="comment_message" style="margin:4px 0">
                    <?php echo $this->Moo->formatText( $photo['Photo']['caption'], false, true, array('no_replace_ssl' => 1) )?>
                </div>
                <div id="tags" style="margin:5px 0;">
                    <span class="photo_view_info"><?php echo __( 'In this photo')?>: </span>
                    <?php 
                    $count = 0;
                    foreach ( $photo_tags as $tag ): 
                    ?>
                    <span class="photoDetailTags" data-tag-id="<?php echo $tag['PhotoTag']['id']?>" id="hotspot-item-0-<?php echo $tag['PhotoTag']['id']?>">
                        <?php
                        if ( $tag['PhotoTag']['user_id'] )
                            echo $this->Moo->getName( $tag['User'], false );
                        else
                            echo h($tag['PhotoTag']['value']);

                        if (( $uid && $cuser['Role']['is_admin'] ) || $uid == $tag['PhotoTag']['tagger_id'] || $uid == $tag['PhotoTag']['user_id'] ):
                        ?><a class="photoDetailRemoveTags" data-id="<?php echo $tag['PhotoTag']['id']?>" href="javascript:void(0)"><i class="icon-delete cross-icon-sm"></i></a>
                        <?php
                        endif;
                        ?>
                    </span>
                    <?php
                        $count++; 
                    endforeach; 
                    ?>
                </div>
                <span class="photo_view_info"><?php echo __( 'Posted by %s', $this->Moo->getName($photo['User'], false))?> <?php echo $this->Moo->getTime( $photo['Photo']['created'], Configure::read('core.date_format'), $utz )?></span>

                
                <div class="section-like-photo">
                    <?php if ( !empty($uid) ): ?>
            
                <a href="javascript:void(0)" id="photo_like_count" data-thumb-up="1" data-id="<?php echo $photo['Photo']['id']?>" class="likePhoto <?php if ( !empty( $uid ) && !empty( $like['Like']['thumb_up'] ) ): ?>active<?php endif; ?>">
                    <i class="material-icons md-24">thumb_up</i>
                </a>
                <?php
                    $this->MooPopup->tag(array(
                           'href'=>$this->Html->url(array("controller" => "likes",
                                                          "action" => "ajax_show",
                                                          "plugin" => false,
                                                          'Photo_Photo',
                                                          $photo['Photo']['id'],
                                                      )),
                           'title' => __( 'People Who Like This'),
                           'innerHtml'=> '<span id="photo_like_count2">' . $photo['Photo']['like_count'] . '</span>',
                        'data-dismiss' => 'modal'
                   ));
               ?>

               <?php if(empty($hide_dislike)): ?>
                <a href="javascript:void(0)" id="photo_dislike_count" data-thumb-up="0" data-id="<?php echo $photo['Photo']['id']?>" class="likePhoto <?php if ( !empty( $uid ) && isset( $like['Like']['thumb_up'] ) && $like['Like']['thumb_up'] == 0 ): ?>active<?php endif; ?>">
                    <i class="material-icons md-24">thumb_down</i></a>
                <?php
                    $this->MooPopup->tag(array(
                           'href'=>$this->Html->url(array("controller" => "likes",
                                                          "action" => "ajax_show",
                                                          "plugin" => false,
                                                          'Photo_Photo',
                                                          $photo['Photo']['id'],
                                                          1
                                                      )),
                           'title' => __( 'People Who Dislike This'),
                           'innerHtml'=> '<span id="photo_dislike_count2">' . $photo['Photo']['dislike_count'] . '</span>',
                   ));
                ?>
                <?php endif; ?>
            
            <?php endif; ?>
                </div>
                
                </div>
                <div class="photo-detail-comment">                    
                    <?php echo $this->renderComment();?>
                </div>
            </div>
        </div>
    </div>
    
            
       
   
    
    
</div>
<?php else:?>
<div class="photo_comments">
    <div class="photo_right col-md-sl2 pull-right full_content">
        <div class="bar-content">
            <div class="content_center">
                <div id="lb_description">
                    <h1>
						<a href="#"><?php echo __("You can't view or interact with this image because of view privacy.");?></a>
                    </h1>      
				</div>
            </div>
        </div>
    </div>
</div>
<?php endif;?>