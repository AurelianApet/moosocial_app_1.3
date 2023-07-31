<?php
$ids = explode(',',$activity['Activity']['items']);
$groupModel = MooCore::getInstance()->getModel('Group_Group');
$groups_count = $groupModel->find( 'count', array( 'conditions' => array( 'Group.id' => $ids )));
$groups = $groupModel->find( 'all', array( 'conditions' => array( 'Group.id' => $ids ), 'limit' => 3));
$groupModel->cacheQueries = false;
$groupHelper = MooCore::getInstance()->getHelper('Group_Group');
?>
<ul class="activity_content group_feed_join">
<?php foreach ( $groups as $group ): ?>
    <li>
        <div class="activity_item">
            <div class="activity_left">
                <a href="<?php echo $group['Group']['moo_href']?>">
                    <img src="<?php echo $groupHelper->getImage($group, array('prefix' => '150_square'))?>" class="img_wrapper2" />
                </a>
            </div>
            <div class="activity_right ">
                <a class="feed_title" href="<?php echo $group['Group']['moo_href']?>"><?php echo h($group['Group']['moo_title'])?></a>
               
            </div>
        </div>
    </li>
<?php endforeach; ?>
    <?php if ($groups_count > 3): ?>
    <div><?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "groups",
                                            "action" => "ajax_group_joined",
                                            "plugin" => 'group',
                                            'activity_id:' . $activity['Activity']['id'],

                                        )),
             'title' => __('View more groups'),
             'innerHtml'=> __('View more groups'),
     ));
 ?> </div>
    <?php endif; ?>
</ul>