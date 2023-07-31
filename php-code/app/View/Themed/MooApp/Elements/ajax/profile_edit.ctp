<?php

$bday_month = '';
$bday_day = '';
$bday_year = '';
if (!empty($cuser['birthday']))
{
	$birthday = explode('-', $cuser['birthday']);
	$bday_year = $birthday[0];
	$bday_month = $birthday[1];
	$bday_day = $birthday[2];
}
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooUser'), 'object' => array('$', 'mooUser'))); ?>
mooUser.initOnProfileEdit();
<?php $this->Html->scriptEnd(); ?>

<div class="edit-profile-section">
<h2><?php echo __('Required Information')?></h2>
<ul class="">
	<li>
	    <?php 
	    if ( Configure::read('core.name_change') ):
	        
        ?>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input name="name" class="mdl-textfield__input" type="text" value="<?php echo $cuser['name'] ?>" />
            <label class="mdl-textfield__label"><?php echo __('Full Name')?></label>
        </div>
        <?php 
        else:
        
           echo $this->Form->hidden('name', array('value' => $cuser['name']));
           echo $cuser['name'];
       
        endif;
	    ?>
          
	</li>
	<li>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input name="email" class="mdl-textfield__input" type="text" value="<?php echo $cuser['email'] ?>" />
            <label class="mdl-textfield__label"><?php echo __('Email Address') ?></label>
        </div>
    </li>
	<li>
        <div class="label">
           <?php echo __('Birthday')?>
        </div>
        <div>
            <div class="col-xs-4">
            <?php echo $this->Form->month('birthday', array('value' => $bday_month))?>
            </div>
            <div class="col-xs-4">
                <div class='p_l_2'>
            <?php echo $this->Form->day('birthday', array('value' => $bday_day))?>
                </div>
            </div>
            <div class="col-xs-4">
            <?php echo $this->Form->year('birthday', 1930, date('Y'), array('value' => $bday_year))?>
            </div>

        </div>
        <div class="clear"></div>
    </li>
	<li>
        <div class="label"><?php echo __('Gender') ?></div>
        <div>
        <?php echo $this->Form->select('gender', $this->Moo->getGenderList(), array('value' => $cuser['gender'])); ?>
        </div>   
            <div class="clear"></div>
     </li>
	<?php $enable_timezone_selection = Configure::read('core.enable_timezone_selection');
        if ( !empty( $enable_timezone_selection ) ): ?> 
	<li>
        <div class="label"><?php echo __('Timezone') ?></div>
        <div>
            <?php echo $this->Form->select('timezone', $this->Moo->getTimeZones(), array('value' => $cuser['timezone'])); ?>
            </div>
    </li>    
	<?php endif; ?>	
</ul>
</div>
<div class="edit-profile-section">
<h2><?php echo __('Optional Information')?></h2>
<ul >
    <?php if ( in_array('user_username', $uacos) && ( Configure::read('core.username_change') || empty($cuser['username']) ) ): ?>
	<li> 
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input name="username" class="mdl-textfield__input" type="text" value="<?php echo $cuser['username'] ?>" />
            <label class="mdl-textfield__label" ><?php echo __('Username') ?></label>
        </div>
		<a href="javascript:void(0)" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored " style="margin-top: 5px;" id="checkButton"><i class="icon-ok"></i> <?php echo __('Check Availability')?></a>
		<div style="display:none;margin:5px 0 0" id="message"></div>
        
      
	</li>
	<?php endif; ?>
	<li>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <textarea class="mdl-textfield__input" name="about"><?php echo $cuser['about'] ?></textarea>
            <label class="mdl-textfield__label" for="sample3"><?php echo __('About')?></label>
        </div>
           
    </li>
</ul>
</div>

<?php if ( !empty( $custom_fields ) || (count($profile_type) > 1 && ($is_edit || !$cuser['ProfileType']['id'])) ): ?>
<div class="edit-profile-section">
<h2><?php echo __('Additional Information')?></h2>
<?php
echo $this->element( 'custom_fields', array( 'show_require' => true, 'show_heading' => true ) );
?>
</div>
<?php endif; ?>

<div class="edit-profile-section">
<h2><?php echo __('User Settings')?></h2>
<ul class="">
	<li>
            <div class="label">
            <?php echo __('Profile Privacy')?>
            </div>
            <div >
		<?php echo $this->Form->select('privacy', array( PRIVACY_EVERYONE => __('Everyone'), 
														 PRIVACY_FRIENDS => __('Friends Only'), 
														 PRIVACY_ME => __('Only Me')), 
												  array('value' => $cuser['privacy'], 'empty' => false)); ?>
            </div>
            <div class="clear"></div>
        </li>
	<li>
            <?php echo $this->Form->checkbox('hide_online', array('checked' => $cuser['hide_online'])); ?>
            <?php echo __('Do not show my online status')?>
        </li>
    <?php
        $send_message_to_non_friend = Configure::read('core.send_message_to_non_friend');
    ?>
    <?php if($send_message_to_non_friend): ?>
    <li>
        <?php echo $this->Form->checkbox('receive_message_from_non_friend', array('checked' => $cuser['receive_message_from_non_friend'])); ?>
        <?php echo __('Receive message from non-friend')?>
    </li>
    <?php endif; ?>
    <?php  ?>
</ul>
</div>
<div class="edit-profile-section" style="border:none">
<div class='col-sm-4 hidden-xs hidden-sm'>&nbsp;</div>
<div class='col-sm-8'>
    <div style="margin-top:10px"><input id="save_profile" type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored1" value="<?php echo __('Save Changes')?>"></div>
</div>
<div class='clear'></div>
    </div>

<div class="error-message" id="errorMessage" style="display:none"></div>