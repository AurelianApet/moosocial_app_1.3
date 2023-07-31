<?php
$groupHelper = MooCore::getInstance()->getHelper('Group_Group');
//switch ($group['Group']['type']) {
//    case PRIVACY_PUBLIC:
//            $privacy =  __('Public (anyone can view and join)');
//        break;
//    case PRIVACY_PRIVATE:
//            $privacy = __('Private (only group members can view details)');
//        break;
//    case PRIVACY_RESTRICTED:
//            $privacy = __('Restricted (anyone can join upon approval)');
//        break;
//                }
if ($group['Group']['type'] != PRIVACY_PRIVATE || (!empty($cuser) && $cuser['Role']['is_admin'] ) ||
                (!empty($my_status) && ( $my_status['GroupUser']['status'] == GROUP_USER_MEMBER || $my_status['GroupUser']['status'] == GROUP_USER_ADMIN ) )
        ) {
            $groupArray = array(
                'id' => $group['Group']['id'],
                'name' => h($group['Group']['name']),
                'create' => h($group['Group']['created']),
                'description' => $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $group['Group']['description'] , Configure::read('Group.group_hashtag_enabled'))),
                'thumbnail' => $groupHelper->getImage($group, array('prefix' => '150_square')),
                'featured' => $group['Group']['featured'],
                'group_user_count' => $group['Group']['group_user_count'],
                'category_id' => $group['Group']['category_id'],
                'category_name' => $group['Category']['name'],
                'privacy' => $group['Group']['type'],
                'join_request' => isset($request_count) ? $request_count : '' ,
                'photo_count' => $group['Group']['photo_count'],
                'topic_count' => $group['Group']['topic_count'],
                'video_count' => $group['Group']['video_count'],
                'user_create_id' => $group['Group']['user_id'],
                'user_create_name' => $group['User']['name'],
            );
        }
        else {
            $groupArray = array(
                'id' => $group['Group']['id'],
                'name' => h($group['Group']['name']),
                'category_id' => $group['Group']['category_id'],
                'category_name' => $group['Category']['name'],
                'privacy' => $privacy,
            );            
        }
echo json_encode($groupArray);