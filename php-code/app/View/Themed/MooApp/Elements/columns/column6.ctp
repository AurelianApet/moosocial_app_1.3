<?php
   // echo $north . "<div>$west$center</div>";
?>
<?php if( !$this->isEmpty('north') ): ?>
    <?php echo $north ;?>
<?php endif; ?>

    <?php if (!empty($is_profile_page)): ?>
        <?php echo $this->element('user/header_profile'); ?>
    <?php endif; ?>
       
        <div id="center">
        <?php echo $center; ?>
        </div>
    





