<?php
echo $this->Html->css(array('jquery-ui', 'footable.core.min'), null, array('inline' => false));
echo $this->Html->script(array('jquery-ui', 'footable'), array('inline' => false));

$this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
$this->Html->addCrumb(__('mooApp Plugins'), array('controller' => 'moo_app_plugins', 'action' => 'admin_index'));

$this->startIfEmpty('sidebar-menu');
echo $this->element('admin/adminnav', array('cmenu' => 'mooApp'));
$this->end();
?>
<?php $this->Html->scriptStart(array('inline' => false)); ?>
$( document ).ready(function() {
	$('#send').click(function(){
		$('#message_error').hide();
		if ($.trim($('#message').val()) == '')
		{
			$('#message_error').show();
			return;
		}
		$('#send').addClass("disabled");
		$.ajax({ 
		    type: 'POST', 
		    url:   '<?php echo $this->request->base; ?>/admin/moo_app/moo_app_settings/ajax_broadcast',
		    data: { 
		    		'message': $.trim($('#message').val()),
		    		'link' : $.trim($('#link').val())
		    	  }, 
		    success: function (data) { 
		    	location.reload();
		    }
		});
	});
});
<?php $this->Html->scriptEnd(); ?>
<div class="portlet-body form">
    <div class=" portlet-tabs">
        <div class="tabbable tabbable-custom boxless tabbable-reversed">
            <?php echo $this->Moo->renderMenu('MooApp', 'Message Broadcast');?>
            <div class="row" style="padding-top: 10px;">
                <div class="col-md-12">
                    <div class="tab-content">
                        <div class="tab-pane active" id="portlet_tab1">
                        	<form class="form-horizontal" method="post" enctype="multipart/form-data">
                        		<div class="form-body">
                        			<div class="form-group">
                                        <label class="col-md-3 control-label">
                                            <?php echo __("Message");?>
                                        </label>
                                        <div class="col-md-7">
                                        	<input id="message" class="form-control" type="text" name="message">
                                        </div>
                        			</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">
                                            <?php echo __("Link");?>
                                        </label>
                                        <div class="col-md-7">
                                        	<input id="link" class="form-control" type="text" name="link">
                                        </div>
                        			</div>
                        		</div>
                        		<div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="button" id="send" class="btn btn-circle btn-action" value="<?php echo __('Send');?>">
                                        </div>
                                    </div>
									<div class="row">
										<div class="col-md-offset-3 col-md-6">
											<div class="alert alert-danger error-message" id="message_error" style="display:none;margin-top: 10px;"><?php echo __('Message is required');?></div>
										</div>
									</div>
                                </div>
                        	</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>