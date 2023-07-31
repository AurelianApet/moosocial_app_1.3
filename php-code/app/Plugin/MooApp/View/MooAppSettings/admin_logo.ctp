<?php
echo $this->Html->css(array('jquery-ui', 'footable.core.min'), null, array('inline' => false));
echo $this->Html->script(array('jquery-ui', 'footable'), array('inline' => false));

$this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
$this->Html->addCrumb(__('mooApp Plugins'), array('controller' => 'moo_app_plugins', 'action' => 'admin_index'));

$this->startIfEmpty('sidebar-menu');
echo $this->element('admin/adminnav', array('cmenu' => 'mooApp'));
$this->end();
?>
<div class="portlet-body form">
    <div class=" portlet-tabs">
        <div class="tabbable tabbable-custom boxless tabbable-reversed">
            <?php echo $this->Moo->renderMenu('MooApp', 'Logo');?>
            <div class="row" style="padding-top: 10px;">
                <div class="col-md-12">
                    <div class="tab-content">
                        <div class="tab-pane active" id="portlet_tab1">
                        	<form class="form-horizontal" method="post" enctype="multipart/form-data">
                        		<div class="form-body">
                        			<div class="form-group">
                                        <label class="col-md-3 control-label">
                                            <?php echo __("Upload New Image Popup");?>
                                        </label>
                                        <div class="col-md-7">
                                        	<input type="file" name="Filedata">
                                        </div>
                        			</div>
                        			<?php if (Configure::read("MooApp.mooapp_logo_popup")):?>
	                        			<div class="form-group">
	                                          <label class="col-md-3 control-label"></label>
	                                          <div class="col-md-7">
	                                               <img src="<?php echo $this->request->webroot . Configure::read("MooApp.mooapp_logo_popup")?>">
	                                          </div>
	                                    </div>
                                    <?php endif;?>
                        		</div>
                        		<div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="submit" class="btn btn-circle btn-action" value="<?php echo __('Save Settings');?>">
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