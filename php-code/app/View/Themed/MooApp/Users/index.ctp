<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initOnUserIndex();
        <?php if ( !empty( $values ) || !empty($online_filter) ): ?>
        $('#searchPeople').trigger('click');
        <?php endif; ?>
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooUser'), 'object' => array('$', 'mooUser'))); ?>
mooUser.initOnUserIndex();
<?php if ( !empty( $values ) || !empty($online_filter) ): ?>
$('#searchPeople').trigger('click');
<?php endif; ?>
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
	<div class="box2 filter_block">
            <h3 class="visible-xs visible-sm"><?php echo __('Browse')?></h3>
            <div class="box_content">
		<ul class="list2 menu-list" id="browse">
			<li class="current" id="everyone"><a data-url="<?php echo $this->request->base?>/users/ajax_browse/all" href="<?php echo $this->request->base?>/users"><?php echo __('Everyone')?></a></li>
			<?php if (!empty($cuser)): ?>
                        <li><a data-url="<?php echo $this->request->base?>/users/ajax_browse/friends" href="<?php echo $this->request->base?>/users"><?php echo __('My Friends')?></a></li>
                        <?php endif; ?>
		</ul>
            </div>
	</div>

<?php $this->end(); ?>

    <div class="bar-content">
        <div class="content_center full_content p_m_10">
        <div class="dropdown user_search_form">
          <button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php echo __('Search People') ?>
          </button>
          <ul class="dropdown-menu" aria-labelledby="dLabel">
            <!-- search form -->
            <?php echo $this->element('user/search_form'); ?>
            <!-- end search form -->
          </ul>
        </div>
        
            
            <ul class="users_list" id="list-content">
                    <?php 
                    if ( !empty( $values ) || !empty($online_filter) )
                            echo __('Loading...');
                    else
                            echo $this->element( 'lists/users_list', array( 'more_url' => '/users/ajax_browse/all/page:2' ) );
                    ?>
            </ul>
            <div class="clear"></div>
        </div>
    </div>

