<?php
$photoHelper = MooCore::getInstance()->getHelper('Photo_Photo');
$friendModel = MooCore::getInstance()->getModel('Friend');
$photoModel = MooCore::getInstance()->getModel('Photo_Photo');
?>


<?php if (Configure::read('Photo.photo_enabled')): ?>
<?php if (!empty($albums)): ?>

<?php endif; ?>
<?php endif; ?>
    
<div class="p_7">
    
    <?php $this->MooActivity->wall($profileActivities)?>
</div>