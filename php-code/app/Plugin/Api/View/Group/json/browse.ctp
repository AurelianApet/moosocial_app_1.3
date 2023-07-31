<?php
$groupHelper = MooCore::getInstance()->getHelper('Group_Group');
foreach ($groups as $group):
    $groupArray[] = array(
        'id' => $group['Group']['id'],
        'name' => h($group['Group']['name']),
        'description' => $this->Text->convert_clickable_links_for_hashtags($this->Text->truncate(strip_tags(str_replace(array('<br>','&nbsp;'), array(' ',''), $group['Group']['description'])), 200, array('exact' => false)), Configure::read('Group.group_hashtag_enabled')) ,
        'thumbnail' => $groupHelper->getImage($group, array('prefix' => '150_square')),
    );
endforeach;
echo json_encode($groupArray);