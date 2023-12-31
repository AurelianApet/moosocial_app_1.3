<script type="text/javascript">
    require(["jquery","mooGroup"], function($, mooGroup) {
        mooGroup.initOnAjaxViewTopic();
    });
</script>


<div class="bar-content full_content p_m_10">
    <div class="content_center">
        <div class="post_body topic_view_body">
            
            <?php if ($uid == $topic['Topic']['user_id'] || !empty($cuser['Role']['is_admin']) || in_array($uid, $admins) ): ?>
                <div class="dropdown">
                <button id="topic_edit_<?php echo $topic['Topic']['id']?>" class="topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                        <i class="material-icons md-24">more_vert</i>
                </button>
                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="topic_edit_<?php echo $topic['Topic']['id']?>">
                    <?php if ($uid == $topic['Topic']['user_id'] || ( !empty($cuser) && $cuser['Role']['is_admin'] ) || in_array($uid, $admins) ): ?>
                    <li class="mdl-menu__item"><a href='javascript:void(0)' class="ajaxLoadTopicEdit" data-url="<?php echo $this->request->base?>/topics/group_create/<?php echo $topic['Topic']['id']?>"><?php echo __( 'Edit Topic')?></a></li>
                    <li class="mdl-menu__item"><a href="javascript:void(0);" class="deleteTopic" data-id="<?php echo $topic['Topic']['id']?>" data-group="<?php echo $this->request->data['group_id']?>"><?php echo  __( 'Delete') ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($cuser['Role']['is_admin']) || in_array($uid, $admins) ): ?>
                        <?php if ( !$topic['Topic']['pinned'] ): ?>
                        <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/topics/do_pin/<?php echo $topic['Topic']['id']?>"><?php echo __( 'Pin Topic')?></a></li>
                        <?php else: ?>
                        <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/topics/do_unpin/<?php echo $topic['Topic']['id']?>"><?php echo __( 'Unpin Topic')?></a></li>
                        <?php endif; ?>

                        <?php if ( !$topic['Topic']['locked'] ): ?>
                        <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/topics/do_lock/<?php echo $topic['Topic']['id']?>"><?php echo __( 'Lock Topic')?></a></li>
                        <?php else: ?>
                        <li class="mdl-menu__item"><a href="<?php echo $this->request->base?>/topics/do_unlock/<?php echo $topic['Topic']['id']?>"><?php echo __( 'Unlock Topic')?></a></li>
                        <?php endif; ?>     
                    <?php endif; ?>
                    <li class="mdl-menu__item">
                        <?php
                            $this->MooPopup->tag(array(
                                   'href'=>$this->Html->url(array("controller" => "reports",
                                                                  "action" => "ajax_create",
                                                                  "plugin" => false,
                                                                  'topic_topic',
                                                                  $topic['Topic']['id']
                                                              )),
                                   'title' => __( 'Report Topic'),
                                   'innerHtml'=> __( 'Report Topic'),
                           ));
                       ?>
                          </li>
                           <?php if ($topic['Group']['moo_privacy'] == PRIVACY_PUBLIC): ?>
                          <li class="mdl-menu__item">
                               <a href="javascript:void(0);" share-url="<?php echo $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'share',
                                    'action' => 'ajax_share',
                                    'Topic_Topic',
                                    'id' => $topic['Topic']['id'],
                                    'type' => 'topic_item_detail'
                                ), true); ?>" class="shareFeedBtn"><?php echo __('Share'); ?></a>
                           </li>
                           <?php endif; ?>
                </ul>   
            </div>   
            <?php endif; ?>  

        
        <div class="title_center">
             <h2><?php echo h($topic['Topic']['title']); ?></h2>
        </div>
        <div class="date"><?php echo __( 'Posted by %s', $this->Moo->getName($topic['User']))?> <?php echo $this->Moo->getTime($topic['Topic']['created'], Configure::read('core.date_format'), $utz)?></div>
         
    <div class="clear"></div>
    <div class="comment_message" style="margin:5px 0">
        <?php echo $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $topic['Topic']['body'] , Configure::read('Topic.topic_hashtag_enabled')))?>

        <?php if ( !empty( $pictures ) ): ?>
            <div class='topic_attached_file'>
                <div class="date"><?php echo __( 'Attached Images')?></div>
                <ul class="list4 p_photos ">
                    <?php foreach ($pictures as $p): ?>
                        <li class='col-xs-6 col-ms-4 col-md-3' >
                            <div class="p_2">
                                <a style="background-image:url(<?php echo $this->request->webroot?>uploads/attachments/t_<?php echo $p['Attachment']['filename']?>)" href="<?php echo $this->request->webroot?>uploads/attachments/<?php echo $p['Attachment']['filename']?>" class="attached-image layer_square"></a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class='clear'></div>
            </div>
        <?php endif; ?>

        <?php if ( !empty( $files ) ): ?>
        <div style="margin:10px 0">
            <div class="date"><?php echo __( 'Attached Files')?></div>
            <ul class="list6 list6sm">
            <?php foreach ($files as $attachment): ?>     
                <li><i class="icon-attach"></i><a href="<?php echo $this->request->base?>/attachments/download/<?php echo $attachment['Attachment']['id']?>"><?php echo $attachment['Attachment']['original_filename']?></a> <span class="date">(<?php echo __n('%s download', '%s downloads', $attachment['Attachment']['downloads'], $attachment['Attachment']['downloads'] )?>)</span></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($topic['Group']['moo_privacy'] == PRIVACY_PUBLIC): ?>
            <?php echo $this->element('likes', array('shareUrl' => $this->Html->url(array(
                                        'plugin' => false,
                                        'controller' => 'share',
                                        'action' => 'ajax_share',
                                        'Topic_Topic',
                                        'id' => $topic['Topic']['id'],
                                        'type' => 'topic_item_detail'
                                    ), true), 'item' => $topic['Topic'], 'type' => 'Topic_Topic', 'hide_container' => false)); ?>
        <?php else: ?>
            <?php echo $this->element('likes', array('doNotShare' => true, 'shareUrl' => $this->Html->url(array(
                                        'plugin' => false,
                                        'controller' => 'share',
                                        'action' => 'ajax_share',
                                        'Topic_Topic',
                                        'id' => $topic['Topic']['id'],
                                        'type' => 'topic_item_detail'
                                    ), true), 'item' => $topic['Topic'], 'type' => 'Topic_Topic', 'hide_container' => false)); ?>
        <?php endif; ?>
            </div>
    <div class="topic-comment">
<h2><?php echo __( 'Replies (%s)', $topic['Topic']['comment_count'])?></h2>
        
        <?php if (Configure::read('core.comment_sort_style') == COMMENT_RECENT): ?>
        
        <?php 
        if ( !isset( $is_member ) || $is_member  )
            if ( $topic['Topic']['locked'] )
                echo '<i class="icon icon-lock icon-small"></i> ' . __( 'This topic has been locked');
            else
                   echo $this->element( 'comment_form', array( 'target_id' => $topic['Topic']['id'], 'type' => 'Topic_Topic' ) ); 
        else
                echo __( 'This a group topic. Only group members can leave comment');		
        ?>
        <ul class="list6 comment_wrapper" id="comments">
        <?php echo $this->element('comments');?>
        </ul>

       <?php elseif(Configure::read('core.comment_sort_style') == COMMENT_CHRONOLOGICAL): ?>
        
        <ul class="list6 comment_wrapper" id="comments">
        <?php echo $this->element('comments_chrono');?>
        </ul>
        <?php 
        if ( !isset( $is_member ) || $is_member  )
            if ( $topic['Topic']['locked'] )
                echo '<i class="icon icon-lock icon-small"></i> ' . __( 'This topic has been locked');
            else
                   echo $this->element( 'comment_form', array( 'target_id' => $topic['Topic']['id'], 'type' => 'Topic_Topic' ) ); 
        else
                echo __( 'This a group topic. Only group members can leave comment');		
        ?>
        
        <?php endif; ?>
    </div>   
</div>
</div>
