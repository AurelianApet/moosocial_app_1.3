<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true,'requires'=>array('jquery'), 'object' => array('$'))); ?>
    $('#mark_all_as_read').click(function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('href'),
            success: function (data) {
                location.reload();
            }
        });
    });
<?php $this->Html->scriptEnd(); ?>
<div class="bar-content">
<div class="content_center">
    <div class="mo_breadcrumb button-header-list">

          <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "conversations",
                                            "action" => "ajax_send",
                                            "plugin" => false,

                                        )),
             'title' => __('Send New Message'),
             'innerHtml'=> __('New Message'),
          'class' => 'pull-right mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored1'
     ));
 ?>
        <?php
        echo $this->Html->link(__('Mark All As Read'),
            array("controller" => "conversations",
                "action" => "mark_all_read",
                "plugin" => false,
            ),

	    array('id'=>'mark_all_as_read','class' => 'pull-right mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored2')
        );
 ?>



    </div>
    <ul class="list6 comment_wrapper conversation_list" id="list-content">
    <?php echo $this->element( 'lists/messages_list', array( 'more_url' => '/conversations/ajax_browse/page:2' ) ); ?>
    </ul>
</div>
</div>