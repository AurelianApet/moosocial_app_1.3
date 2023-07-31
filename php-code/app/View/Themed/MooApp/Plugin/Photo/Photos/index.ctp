

 <div class="bar-content">   
     <div class="content_center">
         
    
         <div class="mo_breadcrumb">

            <?php echo $this->element('lists/categories_list')?>
            <?php if (!empty($uid)): ?>
            <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "albums",
                                            "action" => "create",
                                            "plugin" => 'photo',
                                            
                                        )),
             'title' => __( 'New Album'),
             'innerHtml'=> __( 'New Album'),
          'data-backdrop' => 'static',
          'class' => 'topButton mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
     ));
 ?>
            
            <?php endif; ?>
         </div>
	

	 <div class="filter_block">
                 
        <?php echo $this->element('sidebar/search'); ?>
           
    </div>
	<ul class="albums photo-albums" id="album-list-content">
            <?php 
            if ( !empty( $this->request->named['category_id'] ) || !empty($cat_id) ){
                if (empty($cat_id)){
                    $cat_id = $this->request->named['category_id'];
                }
                
                echo $this->element( 'lists/albums_list', array( 'album_more_url' => '/albums/browse/category/' . $cat_id . '/page:2' ) );
            }
            else {
                echo $this->element( 'lists/albums_list', array( 'album_more_url' => '/albums/browse/all/page:2' ) );
            }
            ?>	
	</ul>
        <div class="clear"></div>
     </div>
 </div>