<?php if($this->request->is('ajax')): ?>
<script>
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initAjaxRequest();
    });
</script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false,'requires'=>array('jquery','mooUser'),'object'=>array('$','mooUser'))); ?>
    mooUser.initAjaxRequest();
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php $this->setCurrentStyle(4);?>

<div class="bar-content">
    <div class="content_center">
      
	<div class="full_content ">

        <?php if (empty($requests)): echo '<div align="center">' . __('You have no friend requests') . '</div>';
        else: ?>
        <ul class="request_list comment_wrapper" style="margin-top:0">
        <?php foreach ($requests as $request): ?>
                <li id="request_<?php echo $request['FriendRequest']['id']?>">
                        
                        <?php echo $this->Moo->getItemPhoto(array('User' => $request['Sender']), array( 'prefix' => '100_square'), array('class' => 'img_wrapper2 user_avatar_large'))?>
                        <div class="friend-request-info">
                                <?php echo $this->Moo->getName($request['Sender'])?><br /><?php echo nl2br(h($request['FriendRequest']['message']))?><br />
                                <span class="date"><?php echo $this->Moo->getTime( $request['FriendRequest']['created'], Configure::read('core.date_format'), $utz )?></span>

                                <div class="request-action">
                                    <a href="javascript:void(0)" data-id="<?php echo $request['FriendRequest']['id']?>" data-status="1" class="respondRequest mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"><?php echo __('Accept')?></a>
                                    <a href="javascript:void(0)" data-id="<?php echo $request['FriendRequest']['id']?>" data-status="0" class="respondRequest mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1 "><?php echo __('Delete')?></a>
                                </div>
                        </div>
                </li>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        </div>
    </div>
</div>