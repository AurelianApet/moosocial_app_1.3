
<ul class="blog-content-list">

<?php
$blogHelper = MooCore::getInstance()->getHelper('Blog_Blog');
if (!empty($blogs) && count($blogs) > 0)
{
	$i = 1;
	foreach ($blogs as $blog):
?>
        <li class="full_content p_m_10">
            <a href="<?php echo $this->request->base?>/blogs/view/<?php echo $blog['Blog']['id']?>/<?php echo seoUrl($blog['Blog']['title'])?>">
            <img width="100" height="75" style="background-image:url(<?php echo $blogHelper->getImage($blog, array('prefix' => '150_square'))?>)" src="<?php echo $this->request->webroot ?>theme/<?php echo $this->theme ?>/img/s.png">
            </a>
            <div class="blog-info">
          
                <a class="title" href="<?php echo $this->request->base?>/blogs/view/<?php echo $blog['Blog']['id']?>/<?php echo seoUrl($blog['Blog']['title'])?>">
                    <?php echo h($blog['Blog']['title']) ?>
                </a>


            <?php if( !empty($uid) && (($blog['Blog']['user_id'] == $uid ) || ( !empty($cuser) && $cuser['Role']['is_admin'] ) ) ): ?>
                
                <div class="list_option">
                    <div class="dropdown">
                        <button id="demo-menu-lower-right_<?php echo $blog['Blog']['id']?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                            <i class="material-icons">more_vert</i>
                        </button>
                        <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="demo-menu-lower-right_<?php echo $blog['Blog']['id']?>">
                          <?php if ($blog['User']['id'] == $uid || ( !empty($cuser) && $cuser['Role']['is_admin'] )): ?>
                                <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/blogs/create/<?php echo $blog['Blog']['id']?>"> <?php echo __( 'Edit Entry')?></a></li>
                            <?php endif; ?>
                            <?php if ( ($blog['Blog']['user_id'] == $uid ) || ( !empty( $blog['Blog']['id'] ) && !empty($cuser) && $cuser['Role']['is_admin'] ) ): ?>
                                <li class="mdl-menu__item"><a href="javascript:void(0)" data-id="<?php echo $blog['Blog']['id']?>" class="deleteBlog"> <?php echo __( 'Delete Entry')?></a></li>
                                
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="extra_info">
                    <?php echo __( 'Posted by')?> <?php echo $this->Moo->getName($blog['User'], false)?><br/>
                    <?php echo $this->Moo->getTime( $blog['Blog']['created'], Configure::read('core.date_format'), $utz )?> &nbsp;
                    <?php
                        switch($blog['Blog']['privacy']){
                            case 1:
                                $icon_class = 'fa fa-globe';
                                $tooltip = __('Shared with: Everyone');
                                break;
                            case 2:
                                $icon_class = 'fa fa-group';
                                $tooltip = __('Shared with: Friends Only');
                                break;
                            case 3:
                                $icon_class = 'fa fa-user';
                                $tooltip = __('Shared with: Only Me');
                                break;
                        }
                    ?>
                    <a style="color:#888;" class="tip" href="javascript:void(0);" original-title="<?php echo  $tooltip ?>"> <i class="<?php echo  $icon_class ?>"></i></a>
                </div>
           
			<div >
<!--                            <div>
				<?php 
                                echo $this->Text->convert_clickable_links_for_hashtags($this->Text->truncate(strip_tags(str_replace(array('<br>','&nbsp;'), array(' ',''), $blog['Blog']['body'])), 200, array('eclipse' => '')), Configure::read('Blog.blog_hashtag_enabled'));
				?>
                            </div>-->
                            
			</div>
                <div class="clear"></div>
<!--                <div class="extra_info">
                    <?php $this->Html->rating($blog['Blog']['id'],'blogs', 'Blog'); ?>
                </div>-->
        </div>
	</li>
<?php
    $i++;
	endforeach;
}
else
	echo '<div class="clear" align="center">' . __( 'No more results found') . '</div>';
?>
<?php if (isset($more_url)&& !empty($more_result)): ?>
    <?php $this->Html->viewMore($more_url) ?>
<?php endif; ?>
</ul>
<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooBlog"], function($,mooBlog) {
        mooBlog.initOnListing();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooBlog'), 'object' => array('$', 'mooBlog'))); ?>
mooBlog.initOnListing();
<?php $this->Html->scriptEnd(); ?> 
<?php endif; ?>
