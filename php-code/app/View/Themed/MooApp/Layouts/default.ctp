<?php
/**
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 */
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        <?php if ( Configure::read('core.site_offline') ) echo __('[OFFLINE]'); ?>

        <?php if (isset($title_for_layout) && $title_for_layout){ echo $title_for_layout; } else if(isset($mooPageTitle) && $mooPageTitle) { echo $mooPageTitle; } ?> | <?php echo Configure::read('core.site_name'); ?>
    </title>
    <meta name="description" content="<?php if (isset($description_for_layout) && $description_for_layout){ echo $description_for_layout; }else if(isset($mooPageDescription) && $mooPageDescription) {echo $mooPageDescription;}else if(Configure::read('core.site_description')){ echo Configure::read('core.site_description');}?>"/>
    <meta name="keywords" content="<?php if(isset($mooPageKeyword) && $mooPageKeyword){echo $mooPageKeyword;}else if(Configure::read('core.site_keywords')){ echo Configure::read('core.site_keywords');}?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <meta property="og:url" content="<?php echo $this->Html->url( null, true ); ?>" />
    <link rel="canonical" href="<?php echo $this->Html->url( null, true ); ?>" /> 
    <?php if(isset($og_image)): ?>
    <meta property="og:image" content="<?php echo $og_image?>" />
    <?php else: ?>
    <meta property="og:image" content="<?php echo FULL_BASE_URL . $this->request->webroot?>img/og-image.png" />
    <?php endif; ?>

    <?php echo  $this->Html->css('https://fonts.googleapis.com/css?family=Roboto:400,300,500,700'); ?>
    <?php echo  $this->Html->css('https://fonts.googleapis.com/icon?family=Material+Icons'); ?>
    <link rel="stylesheet" href="<?php echo $this->request->webroot ?>theme/mooApp/css/material.min.css">
    <script src="https://storage.googleapis.com/code.getmdl.io/1.0.4/material.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    
    

    <?php
        echo $this->Html->meta('icon');
        $this->loadLibarary('mooCore');
        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->Minify->render();
    ?>
   
   <?php
    if(!empty($site_rtl)){
        //echo $this->Html->css('rtl');
        ?>
    <link rel="stylesheet" href="<?php echo $this->request->webroot ?>theme/mooApp/css/rtl.css">
    <?php 
    }
    ?>

</head>
<body class="default-body" id="<?php echo $this->getPageId(); ?>">
<?php echo $this->element('misc/fb_include'); ?>
<?php echo $this->fetch('header'); ?>


<div class="container " id="content-wrapper" <?php $this->getNgController() ?>>
    <?php echo html_entity_decode( Configure::read('core.header_code') )?>


    <div class="row">
        <?php
        //echo $this->Session->flash();
        $flash_mess = $this->Session->flash();
        echo $flash_mess;
        if(empty($flash_mess))
            echo $this->Session->flash('confirm_remind');
        ?>
        
        <?php echo $this->fetch('content'); ?>
        
        <?php echo $this->element('footer_mobi'); ?>
    </div>
    <!-- Modal -->
    <?php $this->MooPopup->html(); ?>
    <section class="modal fade" id="langModal" role="basic" tabindex='-1' aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content"></div>
        </div>
    </section>
    <section class="modal fade modal-fullscreen force-fullscreen" tabindex='-1' id="photoModal" role="basic" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"></div>
        </div>
    </section>

    <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->
    <div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Modal title</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <!-- Config -->
                    <a type="button" class="btn btn-clean ok"><?php echo __('OK')?></a>
                    <a type="button" class="btn btn-clean" data-dismiss="modal"><?php echo __('Cancel')?></a>

                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
	<div class="modal fade" id="plan-view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">Modal title</h4>
		  </div>
		  <div class="modal-body">
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary">Save changes</button>
		  </div>
		</div>
	  </div>
	</div>
    <?php //echo $this->fetch('footer'); ?>





<div id="shareFeedModal" data-backdrop="static" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo __('Share') ?></h4>
            </div>
			<div class="modal-body">
			<script>
               
				function ResizeIframe(id){
				  var frame = document.getElementById(id);
				  frame.height = frame.contentWindow.document.body.scrollHeight + "px";
				}
           
			</script>
			  <iframe id="iframeShare" onload="ResizeIframe('iframeShare')" src="" width="99.6%" height="" frameborder="0"></iframe>
              <button type="button" class="close_share mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored" data-dismiss="modal"><?php echo __('Cancel') ?></button>
			</div>
   
		</div>
	</div>
</div>
</div>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true,'requires'=>array('jquery'), 'object' => array('$'))); ?>
$('#resend_validation_link').attr('class','mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1');
<?php $this->Html->scriptEnd(); ?>


<script src="//maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=<?php echo Configure::read('core.google_dev_key'); ?>"></script>
<?php
echo $this->fetch('config');
echo $this->fetch('mooPhrase');
echo $this->fetch('mooScript');
echo $this->fetch('script');
?>
<?php echo $this->element('sql_dump'); ?>
<?php echo html_entity_decode( Configure::read('core.analytics_code') )?>
</body>
</html>