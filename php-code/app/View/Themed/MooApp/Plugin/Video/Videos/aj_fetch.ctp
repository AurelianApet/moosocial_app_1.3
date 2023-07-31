<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooVideo"], function($, mooVideo) {
        mooVideo.initAfterFetch();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooVideo'), 'object' => array('$', 'mooVideo'))); ?>
mooVideo.initAfterFetch();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php
$tags_value = '';
if (!empty($tags)) $tags_value = implode(', ', $tags);
?>
<?php if ( !empty( $video['Video']['id'] ) ): ?>

<?php if($this->request->is('ajax')): ?>
<div class="title-modal">
    <?php echo __( 'Edit Video')?>
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?php endif; ?>

<div class="modal-body">

            <div class="create_form">
                <form id="createForm">
                    <?php endif; ?>
                    <div class="create_form">
                    <ul>
                        <?php echo $this->Form->hidden('id', array('value' => $video['Video']['id'])); ?>
                        <?php echo $this->Form->hidden('source_id', array('value' => $video['Video']['source_id'])); ?>
                        <?php echo $this->Form->hidden('thumb', array('value' => $video['Video']['thumb'])); ?>

                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="title" class="mdl-textfield__input" type="text" value="<?php echo $video['Video']['title'] ?>" />
                                <label class="mdl-textfield__label"><?php echo __( 'Video Title')?></label>
                            </div> 
                        </li>

                        <?php if(empty($isGroup)): ?>
                        <li>
                                
                                   
                                
                                    <?php echo $this->Form->select( 'category_id', $categories, array( 'value' => $video['Video']['category_id'] ) ); ?>
                                


                        </li>
                        <?php endif; ?>

                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <textarea name="description" class="mdl-textfield__input" type="text" ><?php echo $video['Video']['description'] ?></textarea>
                                <label class="mdl-textfield__label"><?php echo __( 'Description')?></label>
                            </div> 
                        </li>

                        <?php if(empty($isGroup)): ?>
                        <li>
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input name="tags" class="mdl-textfield__input" type="text" value="<?php echo $tags_value ?>" />
                                <label class="mdl-textfield__label" ><?php echo __( 'Tags')?></label>
                            </div> 
                        </li>
                        <li>
                               



                            <?php
                            echo $this->Form->select( 'privacy',
                                                      array( PRIVACY_EVERYONE => __( 'Everyone'),
                                                             PRIVACY_FRIENDS  => __( 'Friends Only'),
                                                             PRIVACY_ME 	  => __( 'Only Me')
                                                            ),
                                                      array( 'value' => $video['Video']['privacy'],
                                                             'empty' => false
                                                            )
                                                    );
                            ?>
                                  
                        </li>
                        <?php endif; ?>

                        <li style="margin:16px 0">
                               


                                <button type='button' class='mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1' id="saveBtn">
                                    <?php echo __('Save')?>
                                </button>
                            
                            <?php if ( !empty( $video['Video']['id'] ) ): ?>
                            <a href="javascript:void(0)" data-id="<?php echo $video['Video']['id'] ?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1 deleteVideo"><?php echo __( 'Delete Video')?></a>
                            <?php endif; ?>
                             <a href="#" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1" data-dismiss="modal">
                                        <?php echo __('Cancel')?>
                                    </a>
                              
                        </li>
                    </ul>
                    </div>
                    <?php if ( !empty( $video['Video']['id'] ) ): ?>
                    </form>
            </div>
        </div>

<?php endif; ?>

<div class="error-message" style="display:none;margin-top:10px;"></div>