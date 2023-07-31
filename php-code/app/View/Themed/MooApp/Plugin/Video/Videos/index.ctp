<?php $upload_video = Configure::read('UploadVideo.uploadvideo_enabled');?>

<div class="bar-content">
    <div class="content_center">
 
    <div class="mo_breadcrumb">
        <?php echo $this->element('lists/categories_list')?>
        <?php if (!empty($uid)): ?>	
        <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "videos",
                                            "action" => "create",
                                            "plugin" => 'video',
                                            
                                        )),
             'title' => __( 'Share New Video'),
             'innerHtml'=> __( 'New Video'),
          	'data-backdrop' => 'static',
          'class' => 'topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
     ));
 ?>
        <?php if($upload_video): ?>
        
        <?php
            $this->MooPopup->tag(array(
                   'href'=>$this->Html->url(array("controller" => "upload_videos",
                                                  "action" => "ajax_upload",
                                                  "plugin" => 'upload_video',

                                              )),
                   'title' => __( 'Upload Video'),
                   'innerHtml'=> __( 'Upload Video'),
                	'data-backdrop' => 'static',
			'data-keyboard' => 'false',
                'class' => 'topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
           ));
       ?>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
     <div class="filter_block">
            <?php echo $this->element('sidebar/search'); ?>
    </div>
    <ul class="video-content-list" id="list-content">
        <?php 
        if ( !empty( $this->request->named['category_id'] )  || !empty($cat_id) ){
            
            if (empty($cat_id)){
                $cat_id = $this->request->named['category_id'];
            }
            
            echo $this->element( 'lists/videos_list', array( 'more_url' => '/videos/browse/category/' . $cat_id . '/page:2' ) );
        }
        else{
            echo $this->element( 'lists/videos_list', array( 'more_url' => '/videos/browse/all/page:2' ) );
        }
        ?>		
    </ul>
    </div>
</div>