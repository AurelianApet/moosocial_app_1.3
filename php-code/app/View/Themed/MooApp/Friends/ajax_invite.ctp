<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooGlobal"], function($,mooGlobal) {
        mooGlobal.initInviteFriendBtn();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true,'requires'=>array('jquery', 'mooGlobal'), 'object' => array('$', 'mooGlobal'))); ?>
mooGlobal.initInviteFriendBtn();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<div class="bar-content">
    <div class="content_center">
        <div class="mo_breadcrumb">
        <h1><?php echo __('Invite Your Friends')?></h1>
        </div>
        <div class="full_content p_m_10">
            <div class="create_form">
                <form id="inviteForm">
                <ul class="">
                    <li>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <textarea class="mdl-textfield__input" name="to" ></textarea>
                            <label class="mdl-textfield__label" ><?php echo __('To')?></label>
                        </div>
                    </li>
                    <li>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <textarea class="mdl-textfield__input" name="message" ></textarea>
                            <label class="mdl-textfield__label" ><?php echo __('Message')?></label>
                        </div>
                    </li>                   
                    <li class="form_info">
                        <?php echo __("Enter your friends' emails below (separated by commas). Limit 10 email addresses per request")?>
                    </li>
                    <?php 
            		if ($this->Moo->isRecaptchaEnabled()): ?>
            		<li>            			
                       <div>
                           <script src='<?php echo $this->Moo->getRecaptchaJavascript();?>'></script>
				           <div class="g-recaptcha" data-sitekey="<?php echo $this->Moo->getRecaptchaPublickey()?>"></div>
                        </div>
				    </li>
				    <?php endif; ?>
                    <li>
                            <a href="#" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored1" id="inviteButton"><?php echo __('Send Invitation')?></a>
                    </li>
                </ul>
                </form>
            </div>
            <div class="error-message" style="display:none;"></div>
        </div>
    </div>
</div>
