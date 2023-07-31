<?php if($this->request->is('ajax')): ?>
    <script type="text/javascript">
        require(["jquery","mooUser"], function($, mooUser) {
            mooUser.initOnUserList();

            $('.cancel_request').unbind('click');
            $('.cancel_request').click(function(e){
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('href'),
                    success: function (data) {
                        location.reload();
                    }
                });
            });
        });
    </script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooUser'), 'object' => array('$', 'mooUser'))); ?>
    mooUser.initOnUserList();

    $('.cancel_request').unbind('click');
    $('.cancel_request').click(function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('href'),
            success: function (data) {
                location.reload();
            }
        });
    });
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php
echo $this->element('lists/users_list_bit');
?>

<?php if (!empty($more_result)):?>

    <?php if ( !empty($type) && $type == 'search' ): ?>
        <script> var searchParams = <?php echo (isset($params))? json_encode($params) : 'false'; ?></script>
    <?php endif; ?>
    <?php $this->Html->viewMore($more_url); ?>
<?php endif; ?>