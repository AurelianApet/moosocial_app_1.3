<?php
$blogHelper = MooCore::getInstance()->getHelper('Blog_Blog');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooBlog', 'hideshare'),'object'=>array('$', 'mooBlog'))); ?>
mooBlog.initOnView();
$(".sharethis").hideshare({media: '<?php echo $blogHelper->getImage($blog, array('prefix' => '300_square'));?>', linkedin: false});
<?php $this->Html->scriptEnd(); ?>


<div class="bar-content full_content">
    <div class="blog_view">
         <div class="blog_thumb">
                <img height="180" style="background-image:url(<?php echo $blogHelper->getImage($blog, array())?>)" src="<?php echo $this->request->webroot ?>theme/<?php echo $this->theme ?>/img/s.png" />
            </div>
	<div class="post_body">
           
        <div class="">
            <?php if(!empty($uid)): ?>
            <div class="list_option">
                <div class="dropdown">
                    <button id="blog_edit_<?php echo $blog['Blog']['id'] ?>" class="topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                        <i class="material-icons">more_vert</i>
                    </button>

                    <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="blog_edit_<?php echo $blog['Blog']['id'] ?>">
                        
                        <?php if ($blog['User']['id'] == $uid || ( !empty($cuser) && $cuser['Role']['is_admin'] )): ?>
                            <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/blogs/create/<?php echo $blog['Blog']['id']?>"> <?php echo __( 'Edit Entry')?></a></li>
                        <?php endif; ?>
                        <?php if ( ($blog['Blog']['user_id'] == $uid ) || ( !empty( $blog['Blog']['id'] ) && !empty($cuser) && $cuser['Role']['is_admin'] ) ): ?>
                            <li class="mdl-menu__item"><a href="javascript:void(0)" data-id="<?php echo $blog['Blog']['id']?>" class="deleteBlog"> <?php echo __( 'Delete Entry')?></a></li>
                            <li class="seperate"></li>
                        <?php endif; ?>
                        <li class="mdl-menu__item">
                            <?php
                            $this->MooPopup->tag(array(

                                'href'=>$this->Html->url(array(
                                    "controller" => "reports",
                                    "action" => "ajax_create",
                                    "plugin" => false,
                                    'Blog_Blog',
                                    $blog['Blog']['id'],
                                )),
                                'title' => __( 'Report Blog'),
                                'innerHtml'=>__( 'Report Blog'),
                            ));
                            ?>
                        </li>
                        <?php if ($blog['Blog']['privacy'] != PRIVACY_ME): ?>
                        <!-- not allow sharing only me item -->
                        <li class="mdl-menu__item">
                            <a href="javascript:void(0);" share-url="<?php echo $this->Html->url(array(
                                'plugin' => false,
                                'controller' => 'share',
                                'action' => 'ajax_share',
                                'Blog_Blog',
                                'id' => $blog['Blog']['id'],
                                'type' => 'blog_item_detail'
                            ), true); ?>" class="shareFeedBtn"><?php echo __('Share'); ?></a>        
                        </li>
                        <?php endif; ?>
                        
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            <h1 style="font-size:20px"><?php echo h($blog['Blog']['title'])?></h1>
            <?php if (isset($group) && $group):?>
		    	<div><?php echo __('In group');?>: <a href="<?php echo $group['Group']['moo_href']?>"><?php echo $group['Group']['moo_title']?></a></div>
		    <?php endif;?>
            <div class="extra_info">
                    <?php echo __( 'Posted by')?> <?php echo $this->Moo->getName($blog['User'], false)?>
                    <?php echo $this->Moo->getTime( $blog['Blog']['created'], Configure::read('core.date_format'), $utz )?> &nbsp;
                    <?php
                        switch($blog['Blog']['privacy']){
                            case 1:
                                $icon_class = '<i class="material-icons md-18">public</i>';
                                $tooltip = __('Shared with: Everyone');
                                break;
                            case 2:
                                $icon_class = '<i class="material-icons md-18">people</i>';
                                $tooltip = __('Shared with: Friends Only');
                                break;
                            case 3:
                                $icon_class = '<i class="material-icons md-18">lock</i>';
                                $tooltip = __('Shared with: Only Me');
                                break;
                        }
                    ?>
                    <a style="color:#888;" class="tip" href="javascript:void(0);" original-title="<?php echo  $tooltip ?>"> <?php echo  $icon_class ?></a>
                </div>
            
        </div>
            
            <div class="post_content">
	    <?php echo $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $blog['Blog']['body']  , Configure::read('Blog.blog_hashtag_enabled') ))?>
            </div>
	    
        <?php //$this->Html->rating($blog['Blog']['id'],'blogs','Blog'); ?>
            
          <?php echo $this->element('likes', array('shareUrl' => $this->Html->url(array(
                                'plugin' => false,
                                'controller' => 'share',
                                'action' => 'ajax_share',
                                'Blog_Blog',
                                'id' => $blog['Blog']['id'],
                                'type' => 'blog_item_detail'
                            ), true), 'item' => $blog['Blog'], 'type' => $blog['Blog']['moo_type'])); ?>
            
          <div class="clear"></div>
        </div>
        
    </div>
</div>


    <div class="blog-comment">
        <?php echo $this->renderComment();?>
    </div> 


