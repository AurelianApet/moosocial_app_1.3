<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooEmoji"], function($,mooEmoji) {
        mooEmoji.init('message');
    });
</script>
<?php endif; ?>

<div class="bar-content full_content ">
    <div class="content_center">
        <?php if ((empty($uid) && !empty($invited_user)) ||
            (!empty($uid) && (($group['Group']['type'] != PRIVACY_PRIVATE && empty($my_status['GroupUser']['status'])) || ($group['Group']['type'] == PRIVACY_PRIVATE && !empty($my_status) && $my_status['GroupUser']['status'] == 0 ) ) ) ): ?>

        <a class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored1 join-btn" href="<?php echo  $this->request->base ?>/groups/do_request/<?php echo  $group['Group']['id'] ?>"><?php echo  __('Join') ?></a>

        <?php endif; ?>
        <?php if (!empty($request_count)): ?>

                <?php
                $this->MooPopup->tag(array(
                       'href'=>$this->Html->url(array("controller" => "groups",
                                                      "action" => "ajax_requests",
                                                      "plugin" => 'group',
                                                      $group['Group']['id'],

                                                  )),
                       'title' => __('Join Requests'),
                    'class' => 'join-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored1 join-btn',
                    'id' => 'join-request',
                    'data-request' => $request_count,
                       'innerHtml'=> $request_count . " " .  __n('join request', 'join requests', $request_count),
               ));
           ?>


        <?php endif; ?>



        <div >
        <ul class="group-info info">
            <li><label><?php echo  __('Category') ?>:</label>
                <div>
                <a href="<?php echo  $this->request->base ?>/groups/index/<?php echo  $group['Group']['category_id'] ?>/<?php echo  seoUrl($group['Category']['name']) ?>">
                    <?php echo  $group['Category']['name'] ?></a>
                </div>
            </li>
            <li><label><?php echo  __('Type') ?>:</label>
                <div>
                <?php
                switch ($group['Group']['type']) {
                    case PRIVACY_PUBLIC:
                        echo __('Public (anyone can view and join)');
                        break;

                    case PRIVACY_PRIVATE:
                        echo __('Private (only group members can view details)');
                        break;

                    case PRIVACY_RESTRICTED:
                        echo __('Restricted (anyone can join upon approval)');
                        break;
                }
                ?>
                </div>
            </li>
            <?php
            if ($group['Group']['type'] != PRIVACY_PRIVATE || (!empty($cuser) && $cuser['Role']['is_admin'] ) ||
                    (!empty($my_status) && ( $my_status['GroupUser']['status'] == GROUP_USER_MEMBER || $my_status['GroupUser']['status'] == GROUP_USER_ADMIN ) )
            ):
                ?>
                <li><label><?php echo  __('Description') ?>:</label>
                    <div>
                        <div class="video-description truncate" data-more-text="<?php echo __( 'Show More')?>" data-less-text="<?php echo __( 'Show Less')?>">
                            <?php echo $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $group['Group']['description'] , Configure::read('Group.group_hashtag_enabled')))?>
                        </div>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
            <?php $this->Html->rating($group['Group']['id'],'groups', 'Group'); ?>
        </div>

    </div>
</div>
<?php
if ($group['Group']['type'] != PRIVACY_PRIVATE || (!empty($cuser) && $cuser['Role']['is_admin'] ) ||
        (!empty($my_status) && ($my_status['GroupUser']['status'] == GROUP_USER_MEMBER || $my_status['GroupUser']['status'] == GROUP_USER_ADMIN ) )
):
    ?>

    <div class="p_7">

        <?php $this->MooActivity->wall($groupActivities)?>
    </div>
<?php endif; ?>

