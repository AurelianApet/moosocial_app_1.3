<div id="filters" style="margin-top:5px">
    <?php if (!Configure::read('core.guest_search') && empty($uid)): ?>
    <?php else: ?>
        <?php echo $this->Form->text('keyword', array('placeholder' => __('Search Topics'), 'rel' => 'topics', 'class' => 'json-view')); ?>
    <?php endif; ?>
</div>