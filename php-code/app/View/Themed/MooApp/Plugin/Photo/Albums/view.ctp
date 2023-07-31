<?php
$photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooPhoto', 'hideshare'),'object'=>array('$', 'mooPhoto'))); ?>
$(".sharethis").hideshare({media: '<?php echo $photoHelper->getAlbumCover($album['Album']['cover'], array('prefix' => '300_square'))?>', linkedin: false});
mooPhoto.initOnViewAlbum();
<?php $this->Html->scriptEnd(); ?>

<div class="bar-content full_content ">
        <div class="content_center">
            <div class=" post_body album_view_detail">
                <div class="info_header">
                    <?php if ( empty( $album['Album']['type'] ) ): ?>
                   
                        
                         
                        <div class="list_option">
                            <div class="dropdown">
                                <button id="album_edit_<?php echo $album['Album']['id']?>" type="button" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                                    <i class="material-icons">more_vert</i>
                                </button>
                                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="album_edit_<?php echo $album['Album']['id']?>">
                                    <?php if ( $uid == $album['User']['id'] || ( !empty($cuser) && $cuser['Role']['is_admin'] ) ): ?>
                                    <li class="mdl-menu__item">
                                        <?php
                                                $this->MooPopup->tag(array(
                                                       'href'=>$this->Html->url(array("controller" => "albums",
                                                                                      "action" => "create",
                                                                                      "plugin" => 'photo',
                                                                                      $album['Album']['id']

                                                                                  )),
                                                       'title' => __( 'Edit Album'),
                                                       'innerHtml'=> __( 'Edit Album'),
                                                    "data-backdrop" => "static"
                                               ));
                                           ?>
                                          </li>
                                    <li class="mdl-menu__item"><a href="javascript:void(0);" class="deleteAlbum" data-id="<?php echo $album['Album']['id']?>"><?php echo __( 'Delete Album')?></a></li>
                                    <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/albums/edit/<?php echo $album['Album']['id']?>"><?php echo __( 'Edit Photos')?></a></li>
                                     <?php endif; ?>
                                    <li class="mdl-menu__item">
                                        <?php
                                            $this->MooPopup->tag(array(
                                                   'href'=>$this->Html->url(array("controller" => "reports",
                                                                                  "action" => "ajax_create",
                                                                                  "plugin" => false,
                                                                                  'photo_album',
                                                                                  $album['Album']['id'],
                                                                              )),
                                                   'title' =>  __( 'Report Album'),
                                                   'innerHtml'=>  __( 'Report Album'),
                                           ));
                                       ?>
                                      </li>
                                      
                                      <?php if ($album['Album']['privacy'] != PRIVACY_ME): ?>
                                        <!-- not allow sharing only me item -->
                                      <li>
                                          <a href="javascript:void(0);" share-url="<?php echo $this->Html->url(array(
                                                'plugin' => false,
                                                'controller' => 'share',
                                                'action' => 'ajax_share',
                                                'Photo_Album',
                                                'id' => $album['Album']['id'],
                                                'type' => 'album_item_detail'
                                            ), true); ?>" class="shareFeedBtn"><?php echo __('Share'); ?></a>
                                      </li>
                                      <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                       
                   
                     
                <?php endif; ?>
                    <h1><?php echo h($album['Album']['moo_title'])?></h1>
                    

                    <div class="album-detail-info">
                        <?php echo __( 'Posted by %s', $this->Moo->getName($album['User']))?> <?php echo __( 'in')?> <a href="<?php echo $this->request->base?>/photos/index/<?php echo $album['Album']['category_id']?>/<?php echo seoUrl($album['Category']['name'])?>"><?php echo $album['Category']['name']?></a> <?php echo $this->Moo->getTime( $album['Album']['created'], Configure::read('core.date_format'), $utz )?>
                        &nbsp;&middot;&nbsp;<?php if ($album['Album']['privacy'] == PRIVACY_PUBLIC): ?>
                            <?php echo __('Public') ?>
                            <?php elseif ($album['Album']['privacy'] == PRIVACY_PRIVATE): ?>
                            <?php echo __('Private') ?>
                            <?php elseif ($album['Album']['privacy'] == PRIVACY_FRIENDS): ?>
                            <?php echo __('Friend') ?>
                            <?php endif; ?>
                    </div>
                    <?php if ( $uid == $album['User']['id'] ): ?>
                        
                            <?php
                                $this->MooPopup->tag(array(
                                    'href'=>$this->Html->url(array("controller" => "photos",
                                                                      "action" => "ajax_upload",
                                                                      "plugin" => 'photo',
                                                                      'Photo_Album',
                                                                      $album['Album']['id'],
                                                                  )),
                                    'title' => h($album['Album']['moo_title']),
                                    'innerHtml'=> __( 'Upload Photos'),
                                    'class' => ' mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored',
                                    'data-backdrop' => 'static'
                               ));
                           ?>
                        
                        
                        <?php endif; ?>
                    <?php //$this->Html->rating($album['Album']['id'], 'albums','Photo');  ?>
                </div>        
            <?php echo $this->element( 'lists/photos_list', array( 'type' => 'Photo_Album' ) ); ?>
            <div class="bottom_info">
                <?php echo $this->Moo->formatText( $album['Album']['description'], false, true, array('no_replace_ssl' => 1) )?>

                <?php if (!empty($tags)): ?>
                <div class="tag_middle" >
                    <?php echo $this->element( 'blocks/tags_item_block' ); ?>
                </div>
                <?php endif; ?>
                
               <?php echo $this->element( 'likes', array('shareUrl' => $this->Html->url(array(
                    'plugin' => false,
                    'controller' => 'share',
                    'action' => 'ajax_share',
                    'Photo_Album',
                    'id' => $album['Album']['id'],
                    'type' => 'album_item_detail'
                ), true), 'item' => $album['Album'], 'type' => 'Photo_Album' ) ); ?>
                
            </div>
            
            
                
              <div class="clear"></div>
        </div>
        </div>
</div>
<?php echo $this->renderComment();?>


