<?php
    //echo "<div>$west$center$east</div>" . $south;
?>


    <?php if (!empty($is_profile_page)): ?>
       <?php echo $this->element('user/header_profile'); ?>
     <?php endif; ?>
       


       
        <div id="center">
        <?php echo $center; ?>
        </div>
   

<div class="clear"></div>
<?php if( !$this->isEmpty('south') ): ?>
<?php echo $south; ?>
 <?php endif; ?>

