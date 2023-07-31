<div id="mobile_suggest" class="mobile_suggest">
	<a class="close_suggest moo_app_remove" href="javascript:void(0);"><i class="fa fa-times"></i></a>
	<div>
	
	<div class="footer_top">
			<img src="<?php if (Configure::read("MooApp.mooapp_logo_popup")) echo  $this->request->webroot . Configure::read("MooApp.mooapp_logo_popup");?>" width="75" height="75" />
			<div>
				<div><b><?php echo Configure::read("MooApp.mooapp_suggestion_title");?></b></div>
				<div><?php echo Configure::read("MooApp.mooapp_suggestion_description");?></div>
			</div>
		</div>
		<div class="footer_bottom">
			<a href="javascript:void(0);" class="moo_app_nothank btn btn-clean"><?php echo __("no thanks")?></a>
			<a href="<?php echo $link_app;?>" class="btn btn-action"><?php echo __("get the app")?></a>
		</div>
	</div>
</div>
