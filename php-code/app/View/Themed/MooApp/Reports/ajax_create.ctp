<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>

<script type="text/javascript">
    require(["jquery","mooBehavior"], function($,mooBehavior) {
        mooBehavior.initOnReportItem();
    });
</script>

<div class="title-modal">
    <?php echo __('Report')?>
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<div class="modal-body">
<div class="error-message" style="display:none;"></div>
<div class='create_form'>
<form id="reportForm">
<?php echo $this->Form->hidden('type', array( 'value' => $type ) ); ?>
<?php echo $this->Form->hidden('target_id', array( 'value' => $target_id ) ); ?>
<ul style="position:relative">
	<li>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                    <textarea class="mdl-textfield__input" name="reason" ></textarea>
                    <label class="mdl-textfield__label" ><?php echo __('Reason')?></label>
                </div>
            <div class='clear'></div>
	</li>
	<li>
                <a href="#" class="btn btn-clean" id="reportButton"><?php echo __('Report')?></a>
                <a href="#" class="btn btn-clean" data-dismiss="modal"><?php echo __('Cancel')?></a>

            <div class='clear'></div>
	</li>
</ul>
</form>
</div>
</div>