<?php if($this->request->is('ajax')):?>
<script type="text/javascript">
    require(["jquery","mooTab"], function($,mooTab) {$(document).ready(function(){
        mooTab.initActivitySwitchTabs();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooTab'), 'object' => array('$', 'mooTab'))); ?>
mooTab.initActivitySwitchTabs();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php if(!$this->request->is('ajax')):?>
<div class="p_l_7 home_content_feed">
    <div id="home-content">
<?php endif;?>
        <?php if ( empty( $tab ) ): ?>
        <div class="p_l_7 check-home">
           

            <?php $this->MooActivity->wall($homeActivityWidgetParams)?>
        </div>
        <?php else: ?>
         <?php echo __('Loading...')?>
        <?php endif; ?>
<?php if(!$this->request->is('ajax')):?>        
    </div>
</div>
<?php endif;?>