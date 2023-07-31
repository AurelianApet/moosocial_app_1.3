<?php echo  $this->Session->flash(); ?>
<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
<div class="bar-content">
    <div class="profile-info-menu">
        <?php echo $this->element('profilenav', array("cmenu" => "password"));?>
    </div>
</div>
<?php $this->end(); ?>
<div class="bar-content ">
    <div class="content_center profile-info-edit">
        <form method="post">
        <div id="center" >
           <!--  <div class="mo_breadcrumb">
            <h1><?php echo __('Change Password')?></h1>
            </div> -->
             <div class="full_content">
                <div class="content_center">
                    <div class='edit-profile-section'>

                        <ul>
                            <li>
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input name="old_password" class="mdl-textfield__input" type="password" value="" />
                                    <label class="mdl-textfield__label"><?php echo __('Current Password') ?></label>
                                </div>

                            </li>
                            <li>
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input name="password" class="mdl-textfield__input" type="password" value="" />
                                    <label class="mdl-textfield__label"><?php echo __('New Password') ?></label>
                                </div>
                            </li>
                            <li>
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input name="password2" class="mdl-textfield__input" type="password" value="" />
                                    <label class="mdl-textfield__label"><?php echo __('Verify Password') ?></label>
                                </div>

                            </li>
                        </ul>
                            <div style="margin-top:10px"><input type="submit" value="<?php echo __('Change Password')?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1"></div>
                        <div class='clear'></div>
                    </div>
                </div>
             </div>
        </div>
        </form>
    </div>
</div>