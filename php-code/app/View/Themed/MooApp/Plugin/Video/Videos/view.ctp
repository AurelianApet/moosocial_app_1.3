<?php
$videoHelper = MooCore::getInstance()->getHelper('Video_Video');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooVideo', 'hideshare'), 'object' => array('$', 'mooVideo'))); ?>
$(".sharethis").hideshare({media: '<?php echo $videoHelper->getImage($video, array('prefix' => '300_square'));?>', linkedin: false});
mooVideo.initOnView();
<?php $this->Html->scriptEnd(); ?> 

<?php $this->setNotEmpty('east');?>
<?php $this->start('east'); ?>
  
    <?php if(!empty($tags)): ?>
        <div class="box2 ">
            <h3><?php echo __( 'Tags')?></h3>
            <div class="box_content">
                <?php echo $this->element( 'blocks/tags_item_block' ); ?>
            </div>
        </div>
    <?php endif; ?>
	<?php if ( !empty( $similar_videos ) ): ?>
        <div class="box2 box_style2">
            <h3><?php echo __( 'Similar Videos')?></h3>
            <div class="box_content">
                <?php echo $this->element('blocks/videos_block', array('videos' => $similar_videos)); ?>
            </div>
        </div>
	<?php endif; ?>
<?php $this->end(); ?>
<div class="bar-content">
    <div >
        <div class="video-detail">
            <?php echo $this->element('Video./video_snippet', array('video' => $video)); ?>
        </div>
    <div class="content_center full_content p_m_10">
        <div class="video-detai-info">
        <div class="video-detail-action">
            <div class="list_option">
                <div class="dropdown">
                    <button id="video_edit_<?php echo $video["Video"]["id"] ?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                        <i class="material-icons">more_vert</i>
                    </button>
                    <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="video_edit_<?php echo $video["Video"]["id"] ?>">
                        <?php if ($video['User']['id'] == $uid || ( !empty($cuser) && $cuser['Role']['is_admin'] ) || ( !empty($admins) && in_array($uid, $admins) )): ?>
                        <li class="mdl-menu__item">
                            <?php
                                $this->MooPopup->tag(array(
                                       'href'=>$this->Html->url(array("controller" => "videos",
                                                                      "action" => "create",
                                                                      "plugin" => 'video',
                                                                      $video['Video']['id']

                                                                  )),
                                       'title' => __( 'Edit Video Details'),
                                       'innerHtml'=> __( 'Edit Video'),
                               ));
                           ?>
                                                      </li>
                                                  <li class="mdl-menu__item"><a href="javascript:void(0)" class="deleteVideo" data-id="<?php echo $video["Video"]["id"]; ?>"><?php echo __( 'Delete Video')?></a></li>
                                                  <?php endif; ?>
                                                  <li class="mdl-menu__item">
                                                      <?php
                                $this->MooPopup->tag(array(
                                       'href'=>$this->Html->url(array("controller" => "reports",
                                                                      "action" => "ajax_create",
                                                                      "plugin" => false,
                                                                      'video_video',
                                                                      $video['Video']['id']
                                                                  )),
                                       'title' => __( 'Report Video'),
                                       'innerHtml'=> __( 'Report Video'),
                               ));
                           ?>
                            </li>
                         <?php if ($video['Video']['privacy'] != PRIVACY_ME): ?>
                            <!-- not allow sharing only me item -->
                            <li>
                                <a href="javascript:void(0);" share-url="<?php echo $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'share',
                                    'action' => 'ajax_share',
                                    'Video_Video',
                                    'id' => $video['Video']['id'],
                                    'type' => 'video_item_detail'
                                ), true); ?>" class="shareFeedBtn"><?php echo __('Share'); ?></a>
                            </li>
                            <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>	
    	<h1 class="video-detail-title"><?php echo h($video['Video']['title'])?></h1>
    	<?php if (isset($group) && $group):?>
	    	<div><?php echo __('In group');?>: <a href="<?php echo $group['Group']['moo_href']?>"><?php echo $group['Group']['moo_title']?></a></div>
	    <?php endif;?>
        <div class="extra_info">
    	<?php echo __( 'Posted by %s', $this->Moo->getName($video['User']))?> <?php echo __( 'in')?> <a href="<?php echo $this->request->base?>/videos/index/<?php echo $video['Video']['category_id']?>/<?php echo seoUrl($video['Category']['name'])?>"><?php echo $video['Category']['name']?></a> <?php echo $this->Moo->getTime($video['Video']['created'], Configure::read('core.date_format'), $utz)?>
    	&nbsp;&middot;&nbsp;<?php if ($video['Video']['privacy'] == PRIVACY_PUBLIC): ?>
                        <?php echo __('Public') ?>
                        <?php elseif ($video['Video']['privacy'] == PRIVACY_ME): ?>
                        <?php echo __('Private') ?>
                        <?php elseif ($video['Video']['privacy'] == PRIVACY_FRIENDS): ?>
                        <?php echo __('Friend Only') ?>
                        <?php endif; ?>
       
        </div>
        
    	<div class="video-description truncate" data-more-text="<?php echo __( 'Show More')?>" data-less-text="<?php echo __( 'Show Less')?>">
    		<?php echo $this->Moo->formatText( $video['Video']['description'], false, true, array('no_replace_ssl' => 1) )?>
    	</div>
        
        <?php //$this->Html->rating($video['Video']['id'],'videos', 'Video'); ?>
        </div>
        <?php echo $this->element('likes', array('shareUrl' => $this->Html->url(array(
                    'plugin' => false,
                    'controller' => 'share',
                    'action' => 'ajax_share',
                    'Video_Video',
                    'id' => $video['Video']['id'],
                    'type' => 'video_item_detail'
                ), true), 'item' => $video['Video'], 'type' => $video['Video']['moo_type'])); ?>
    </div>
   <div class="clear"></div>
    </div>
</div>
  
<?php echo $this->renderComment();?>
  


                
