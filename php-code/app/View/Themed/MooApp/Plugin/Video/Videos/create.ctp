<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooVideo"], function($,mooVideo) {
        mooVideo.initOnCreate();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooVideo'), 'object' => array('$', 'mooVideo'))); ?>
mooVideo.initOnCreate();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php if($this->request->is('ajax')) $this->setCurrentStyle(4); ?>

<div class="title-modal popup_create_title">
    <?php echo __( 'Share New Video')?>
    <button type="button" class="close" data-dismiss="modal">
        <i class="material-icons md-24">close</i> 
    </button>
</div>
<div class="modal-body popup_create_body">
<div class="bar-content full_content">
        <form id="createForm">

            <div id="fetchForm">
                   

                    <?php 
                    if ( !empty( $this->request->data['group_id'] ) )
                            echo $this->Form->hidden('group_id', array('value' => $this->request->data['group_id']));

                    echo $this->Form->hidden('tags');
                    ?>
                    <ul>
                            <li>
                               
                               
                               
                                    <?php echo $this->Form->select( 'source', 
                                                                                                    array( VIDEO_TYPE_YOUTUBE => 'YouTube', VIDEO_TYPE_VIMEO   => 'Vimeo' ),
                                                                                                    array( 'empty' => false )
                                                                                              );
                                    ?>
                            </li>
                            <li>
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input name="url" class="mdl-textfield__input" type="text" />
                                    <label class="mdl-textfield__label"><?php echo __('URL') ?></label>
                                </div>
                                
                            </li>
                            <li>
                               
                                    <a href="#" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1" id="fetchButton">
                                        <?php echo __('Fetch')?>
                                    </a>
                                     <a href="#" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1" data-dismiss="modal">
                                        <?php echo __('Cancel')?>
                                    </a>
                              
                            </li>
                    </ul>
                    <div class="error-message" style="display:none;margin-top:10px;"></div>
            </div>
            <div id="videoForm"></div>
            <div class="clear"></div>
        </form>
        </div>
    </div>

