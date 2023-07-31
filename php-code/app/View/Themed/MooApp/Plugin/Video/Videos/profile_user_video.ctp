<?php $upload_video = Configure::read('UploadVideo.uploadvideo_enabled'); ?>
<?php if ($upload_video): ?>
    <?php
    echo $this->Html->script(array('jquery.fileuploader'), array('inline' => false));
    echo $this->Html->css(array('fineuploader'));
    ?>
<?php endif; ?>

<div class="bar-content">
    <div class="content_center">
        <div class="title_center p_m_12">
            <?php if ($user_id == $uid): ?>

                <?php
                $this->MooPopup->tag(array(
                    'href'=>$this->Html->url(array("controller" => "videos",
                        "action" => "create",
                        "plugin" => 'video',

                    )),
                    'title' => __('Share New Video'),
                    'innerHtml'=> __('New Video'),
                    'class' => 'topButton btnVideo mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
                ));
                ?>

                <?php if ($upload_video): ?>
                    <!-- check enabled upload video from pc -->
                    <?php
                    $this->MooPopup->tag(array(
                        'href' => $this->Html->url(array("controller" => "upload_videos",
                            "action" => "ajax_upload",
                            "plugin" => 'upload_video',
                        )),
                        'title' => __('Upload Video'),
                        'innerHtml' => __('Upload Video'),
                        'data-backdrop' => 'static',
                        'data-keyboard' => 'false',
                        'class' => 'topButton btnVideo mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1'
                    ));
                    ?>
                <?php endif; ?>


            <?php endif; ?>
            <h2 class="header_h2"><?php echo  __( 'Videos') ?></h2>
        </div>
        <ul class="albums" id="list-content">
            <?php echo $this->element('lists/videos_list'); ?>
        </ul>
    </div>
</div>