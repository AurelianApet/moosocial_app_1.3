<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires' => array('jquery', 'mooAlert'), 'object' => array('$', 'mooAlert'))); ?>
$('#form_contact').submit(function( event ) {
    if ( jQuery('#contact_name').val() == '' || jQuery('#contact_email').val() == '' || jQuery('#subject').val() == '' || jQuery('#message').val() == '' )
    {
        mooAlert.alert('<?php echo addslashes(__('All fields are required'))?>');
        event.preventDefault();
        return;
    }

    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    if( reg.test( jQuery('#contact_email').val() ) == false ) {
        mooAlert.alert('<?php echo addslashes(__('Invalid email address'))?>');
        event.preventDefault();
        return;
    }

    mooAjax.post({
        url : mooConfig.url.base + "/home/contact",
        data: $("#form_contact").serialize()
    }, function(data){
        var json = $.parseJSON(data);
        if ( json.status){
            window.location = mooConfig.url.base + "/home/contact";
        }
        else
        {
            mooAlert.alert(json.message);
        }
    });
    event.preventDefault();
    return;

});
<?php $this->Html->scriptEnd(); ?>

<?php
if ( !$uid )
{
	$cuser['name']  = '';
	$cuser['email'] = '';
}
?>
<div class="bar-content ">
    <div class="content_center profile-info-edit">
        <form id="form_contact" method="post">
            <div id="center" >
                <!--  <div class="mo_breadcrumb">
            <h1><?php echo __('Contact Us')?></h1>
            </div> -->
                <div class="full_content">
                    <div class="content_center">
                        <div class='edit-profile-section'>

                            <ul>
                                <li>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input name="contact_name" id="contact_name" class="mdl-textfield__input" type="text" value="<?php echo $cuser['name']; ?>" />
                                        <label class="mdl-textfield__label"><?php echo __('Your Name') ?></label>
                                    </div>

                                </li>
                                <li>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input name="sender_email" id="contact_email" class="mdl-textfield__input" type="text" value="<?php echo $cuser['email']; ?>" />
                                        <label class="mdl-textfield__label"><?php echo __('Email Address') ?></label>
                                    </div>
                                </li>
                                <li>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input name="subject" id="subject" class="mdl-textfield__input" type="text" value="" />
                                        <label class="mdl-textfield__label"><?php echo __('Subject') ?></label>
                                    </div>
                                </li>
                                <li>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <textarea class="mdl-textfield__input" name="message"></textarea>
                                        <label class="mdl-textfield__label"><?php echo __('Message') ?></label>
                                    </div>
                                </li>
                                <li>
                                    <?php $recaptcha_publickey = Configure::read('core.recaptcha_publickey');
                                    if ( $this->Moo->isRecaptchaEnabled()): ?>
                                        <div>
                                            <script src='<?php echo $this->Moo->getRecaptchaJavascript();?>'></script>
                                            <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_publickey?>"></div>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            </ul>
                            <div style="margin-top:10px"><input type="submit" value="<?php echo __('Send')?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"></div>
                            <div class='clear'></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>