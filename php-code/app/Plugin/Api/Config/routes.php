<?php

// Auth
Router::connect('/api/auth/token', array(
    'plugin' => 'Api',
    'controller' => 'auths_ext', // Fixing the conflict routing with social_integrate plugin
    '[method]' => 'POST',
    'action' => 'token',
    'ext' => 'json',
));
// End Auth

//----- Activity Feeds

// -- /activity/home
Router::connect('/api/activity/home', array(
        'plugin' => 'Api',
        'controller' => 'Activity',
        '[method]' => 'GET',
        'action' => 'home',
        'ext' => 'json',
    )
);

// --- POST /activity/post
Router::connect('/api/activity/post', array(
    'plugin' => 'Api',
    'controller' => 'Activity',
    '[method]' => 'POST',
    'action' => 'post',
    'ext' => 'json',
));

// -- /activity/{activity_id}
Router::connect('/api/activity/:activity_id', array(
        'plugin' => 'Api',
        'controller' => 'Activity',
        '[method]' => 'GET',
        'action' => 'get',
        'ext' => 'json',
),
    array(
        'pass' => array('activity_id'),
        'id' => '[0-9]+'
    )
);
// -- /activity/{activity_id}
Router::connect('/api/activity/:activity_id', array(
        'plugin' => 'Api',
        'controller' => 'Activity',
        '[method]' => 'DELETE',
        'action' => 'delete',
        'ext' => 'json',
),
    array(
        'pass' => array('activity_id'),
        'id' => '[0-9]+'
    )
);
// -- /activity/delete/{activity_id}
Router::connect('/api/activity/:activity_id', array(
        'plugin' => 'Api',
        'controller' => 'Activity',
        '[method]' => 'GET',
        'action' => 'delete',
        'ext' => 'json',
),
    array(
        'pass' => array('activity_id'),
        'id' => '[0-9]+'
    )
);
//------ End Activity Feeds

//---- Friends

// --- /friend/list
Router::connect('/api/friend/list', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'GET',
    'action' => 'getlist',
    'ext' => 'json',
));

// --- /friend/list
Router::connect('/api/friend/:user_id/list', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'GET',
    'action' => 'getuserlist',
    'ext' => 'json',
),
    array(
        'pass' => array('user_id'),
        'id' => '[0-9]+'
    )
);

// --- /friend/add
Router::connect('/api/friend/add', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'POST',
    'action' => 'add',
    'ext' => 'json',
));

// --- /friend/accept
Router::connect('/api/friend/accept', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'POST',
    'action' => 'accept',
    'ext' => 'json',
));

// --- /friend/reject
Router::connect('/api/friend/reject', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'POST',
    'action' => 'reject',
    'ext' => 'json',
));

// --- /friend/cancel
Router::connect('/api/friend/cancel', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'POST',
    'action' => 'cancel',
    'ext' => 'json',
));

// --- /friend/delete
Router::connect('/api/friend/delete', array(
    'plugin' => 'Api',
    'controller' => 'friends',
    '[method]' => 'POST',
    'action' => 'delete',
    'ext' => 'json',
));

//---- END Friends

// --- LIKES 

// --- /:object/like
Router::connect('/api/:object/like', array(
    'plugin' => 'Api',
    'controller' => 'likes',
    '[method]' => 'POST',
    'action' => 'add',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
    )
);

// --- /:object/like
Router::connect('/api/:object/like', array(
    'plugin' => 'Api',
    'controller' => 'likes',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
    )
);

// --- /:object/like/delete
Router::connect('/api/:object/like/delete', array(
    'plugin' => 'Api',
    'controller' => 'likes',
    '[method]' => 'POST',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
    )
);

// --- /:object/like/view/:item_id
Router::connect('/api/:object/like/view/:item_id', array(
    'plugin' => 'Api',
    'controller' => 'likes',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
        'item_id' => '[0-9]+',
    )
);
// --- END LIKES 


// --- DISLIKES 

// --- /:object/dislike
Router::connect('/api/:object/dislike', array(
    'plugin' => 'Api',
    'controller' => 'dislikes',
    '[method]' => 'POST',
    'action' => 'add',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
    )
);

// --- /:object/dislike
Router::connect('/api/:object/dislike', array(
    'plugin' => 'Api',
    'controller' => 'dislikes',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
    )
);

// --- /:object/dislike/delete
Router::connect('/api/:object/dislike/delete', array(
    'plugin' => 'Api',
    'controller' => 'dislikes',
    '[method]' => 'POST',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
    )
);

// --- /:object/dislike/view/:item_id
Router::connect('/api/:object/dislike/view/:item_id', array(
    'plugin' => 'Api',
    'controller' => 'dislikes',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'object' => '(activity|activity_comment|comment|blog|album|photo|video|topic)',
        'item_id' => '[0-9]+',
    )
);
// --- END DISLIKES 

// --- COMMENTS

// --- /:object/comment
Router::connect('/api/:object/comment', array(
    'plugin' => 'Api',
    'controller' => 'comments',
    '[method]' => 'POST',
    'action' => 'add',
    'ext' => 'json',
),
    array(
        'object' => '(activity|conversation|blog|album|photo|video|topic)',
    )
);

// --- /:object/comment/edit
Router::connect('/api/:object/comment/edit', array(
    'plugin' => 'Api',
    'controller' => 'comments',
    '[method]' => 'POST',
    'action' => 'edit',
    'ext' => 'json',
),
    array(
        'object' => '(activity|blog|album|photo|video|topic)',
    )
);
// --- /:object/comment
Router::connect('/api/:object/comment', array(
    'plugin' => 'Api',
    'controller' => 'comments',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'object' => '(activity|blog|album|photo|video|topic)',
    )
);
// --- /:object/comment/delete
Router::connect('/api/:object/comment/delete', array(
    'plugin' => 'Api',
    'controller' => 'comments',
    '[method]' => 'POST',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'object' => '(activity|blog|album|photo|video|topic)',
    )
);
// --- /:object/comment/view/:item_id
Router::connect('/api/:object/comment/view/:item_id', array(
    'plugin' => 'Api',
    'controller' => 'comments',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'object' => '(activity|conversation|blog|album|photo|video|topic)',
        'item_id' => '[0-9]+',
    )
);
// --- /:object/comment/:item_id/edited/:comment_id
Router::connect('/api/:object/comment/:item_id/edited/:comment_id', array(
    'plugin' => 'Api',
    'controller' => 'comments',
    '[method]' => 'GET',
    'action' => 'listEdited',
    'ext' => 'json',
),
    array(
        'object' => '(activity|conversation|blog|album|photo|video|topic)',
        'comment_id' => '[0-9]+',
        'item_id' => '[0-9]+',
    )
);
// --- END COMMENTS

// Blog
// --- /blog/:type
Router::connect('/api/blog/:type', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'type' => '(all|my|friends|popular)',
    )
);
// --- /blog/filter/:keyword
Router::connect('/api/blog/filter', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'POST',
    'action' => 'browse',
    'ext' => 'json',
));
// --- /blog/filter/:keyword
Router::connect('/api/blog/view/:id', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'id' => '[0-9]+',
    )
);
// --- /blog/create
Router::connect('/api/blog/:type', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
),
    array(
        'type' => '(create|edit)',
    )
);
// --- /blog/create
//Router::connect('/api/blog/edit', array(
//    'plugin' => 'Api',
//    'controller' => 'blogs',
//    '[method]' => 'PUT',
//    'action' => 'create',
//    'ext' => 'json',
//));

// --- /blog/delete
Router::connect('/api/blog/delete/:blog_id', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'blog_id' => '[0-9]+',
    )
);
// --- /blog/delete
Router::connect('/api/blog/delete/:blog_id', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'blog_id' => '[0-9]+',
    )
);
// --- /blog/category/:category_id
Router::connect('/api/blog/category/:category_id', array(
    'plugin' => 'Api',
    'controller' => 'blogs',
    '[method]' => 'GET',
    'action' => 'viewByCategory',
    'ext' => 'json',
),
    array(
        'category_id' => '[0-9]+',
    )
);
//  END Blog

// Topic
// --- /topic/:type
Router::connect('/api/topic/:type', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'type' => '(all|my|friends|popular)',
    )
);
// --- /topic/group/:group_id
Router::connect('/api/topic/group/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'browseByGroup',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /topic/filter/:keyword
Router::connect('/api/topic/filter', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'POST',
    'action' => 'browse',
    'ext' => 'json',
));
// --- /topic/view/:id
Router::connect('/api/topic/view/:id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'id' => '[0-9]+',
    )
);
// --- /topic/create
Router::connect('/api/topic/:type', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
),
    array(
        'type' => '(create|edit)',
    )
);
//// --- /topic/create
//Router::connect('/api/topic/edit', array(
//    'plugin' => 'Api',
//    'controller' => 'topic',
//    '[method]' => 'POST',
//    'action' => 'save',
//    'ext' => 'json',
//));

// --- /topic/delete
Router::connect('/api/topic/delete/:topic_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// --- /topic/delete
Router::connect('/api/topic/delete/:topic_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// --- /topic/category/:category_id
Router::connect('/api/topic/category/:category_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'viewByCategory',
    'ext' => 'json',
),
    array(
        'category_id' => '[0-9]+',
    )
);
// --- /topic/pin/:topic_id
Router::connect('/api/topic/pin/:topic_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'pin',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// --- /topic/unpin/:topic_id
Router::connect('/api/topic/unpin/:topic_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'unpin',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// --- /topic/lock/:topic_id
Router::connect('/api/topic/lock/:topic_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'lock',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// --- /topic/unlock/:topic_id
Router::connect('/api/topic/unlock/:topic_id', array(
    'plugin' => 'Api',
    'controller' => 'topic',
    '[method]' => 'GET',
    'action' => 'unlock',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
//  END Topic


// Video
// --- /video/:type
Router::connect('/api/video/:type', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'type' => '(all|my|friends|popular)',
    )
);
// --- /video/group/:group_id
Router::connect('/api/video/group/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'GET',
    'action' => 'browseByGroup',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /video/filter/:keyword
Router::connect('/api/video/filter', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'POST',
    'action' => 'browse',
    'ext' => 'json',
));
// --- /video/view/:id
Router::connect('/api/video/view/:id', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'id' => '[0-9]+',
    )
);
// --- /video/category/:category_id
Router::connect('/api/video/category/:category_id', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'GET',
    'action' => 'viewByCategory',
    'ext' => 'json',
),
    array(
        'category_id' => '[0-9]+',
    )
);
// --- /video/:type
Router::connect('/api/video/:type', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
),
    array(
        'type' => '(create|edit)',
    )
);

// --- /video/delete/:video_id
Router::connect('/api/video/delete/:video_id', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// --- /video/delete/:video_id
Router::connect('/api/video/delete/:video_id', array(
    'plugin' => 'Api',
    'controller' => 'video',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'topic_id' => '[0-9]+',
    )
);
// END VIDEO


// ALBUM
// --- /album/:type
Router::connect('/api/album/:type', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'type' => '(all|my|friends|popular)',
    )
);
// --- /album/filter/:keyword
Router::connect('/api/album/filter', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'POST',
    'action' => 'browse',
    'ext' => 'json',
));
// --- /album/category/:category_id
Router::connect('/api/album/category/:category_id', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'category_id' => '[0-9]+',
    )
);
// --- /album/view/:album_id
Router::connect('/api/album/view/:album_id', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'album_id' => '[0-9]+',
    )
);
// --- /album/:type
Router::connect('/api/album/:type', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
),
    array(
        'type' => '(create|edit)',
    )
);
// --- /album/:type
Router::connect('/api/album/upload', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'POST',
    'action' => 'uploadPhoto',
    'ext' => 'json',
));
// --- /album/setcover
Router::connect('/api/album/setcover', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'POST',
    'action' => 'setAlbumCover',
    'ext' => 'json',
));
// --- /album/delete/:album_id
Router::connect('/api/album/delete/:album_id', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'album_id' => '[0-9]+',
    )
);
// --- /album/delete/:album_id
Router::connect('/api/album/delete/:album_id', array(
    'plugin' => 'Api',
    'controller' => 'album',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'album_id' => '[0-9]+',
    )
);
// END ALBUM

// PHOTO

// --- /photo/group/:group_id
Router::connect('/api/photo/group/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'GET',
    'action' => 'photoGroup',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /photo/view/:photo_id
Router::connect('/api/photo/view/:photo_id', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'photo_id' => '[0-9]+',
    )
);
// --- /photo/group/:group_id
Router::connect('/api/photo/group/upload', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'POST',
    'action' => 'groupUpload',
    'ext' => 'json',
));
// --- /photo/tag
Router::connect('/api/photo/tag', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'POST',
    'action' => 'tag',
    'ext' => 'json',
));
// --- /photo/removetag
Router::connect('/api/photo/removetag', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'POST',
    'action' => 'removeTag',
    'ext' => 'json',
));
// --- /photo/setcover
Router::connect('/api/photo/setcover', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'POST',
    'action' => 'setcover',
    'ext' => 'json',
));
// --- /photo/setavartar
Router::connect('/api/photo/setprofilepicture', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'POST',
    'action' => 'setAvatar',
    'ext' => 'json',
));
// --- /photo/updatecaption
Router::connect('/api/photo/updatecaption', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'PUT',
    'action' => 'caption',
    'ext' => 'json',
));
// --- /photo/updatecaption
Router::connect('/api/photo/updatecaption', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'POST',
    'action' => 'caption',
    'ext' => 'json',
));
// --- /photo/delete/:album_id
Router::connect('/api/photo/delete/:photo_id', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'photo_id' => '[0-9]+',
    )
);
// --- /photo/delete/:album_id
Router::connect('/api/photo/delete/:photo_id', array(
    'plugin' => 'Api',
    'controller' => 'photo',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'photo_id' => '[0-9]+',
    )
);
// END PHOTO

// GROUP
// --- /group/:type
Router::connect('/api/group/:type', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'type' => '(all|my|friends|popular|featured|join)',
    )
);
// --- /group/filter/
Router::connect('/api/group/filter', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'POST',
    'action' => 'browse',
    'ext' => 'json',
));
// --- /group/category/:category_id
Router::connect('/api/group/category/:category_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'category_id' => '[0-9]+',
    )
);

// --- /group/view/:group_id
Router::connect('/api/group/view/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/member/:group_id
Router::connect('/api/group/member/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'groupMember',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/admin/:group_id
Router::connect('/api/group/admin/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'groupAdmin',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/activity/:group_id
Router::connect('/api/group/activity/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'groupActivity',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/leave/:group_id
Router::connect('/api/group/leave/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'leaveGroup',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/feature/:group_id
Router::connect('/api/group/feature/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'featureGroup',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/unfeature/:group_id
Router::connect('/api/group/unfeature/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'unfeatureGroup',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);

// --- /group/join/request/:group_id
Router::connect('/api/group/join/request/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'joinRequest',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /group/join/view/:group_id
Router::connect('/api/group/join/view/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'viewJoinRequest',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /group/join/accept/:request_id
Router::connect('/api/group/join/accept/:request_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'acceptRequest',
    'ext' => 'json',
),
    array(
        'request_id' => '[0-9]+',
    )
);
// --- /group/join/delete/:request_id
Router::connect('/api/group/join/delete/:request_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'deleteRequest',
    'ext' => 'json',
),
    array(
        'request_id' => '[0-9]+',
    )
);
// --- /group/notification/on/group_id
Router::connect('/api/group/notification/on/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'notifyOn',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /group/notification/off/group_id
Router::connect('/api/group/notification/off/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'notifyOff',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /group/join/delete/:request_id
Router::connect('/api/group/join/delete/:request_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'DELETE',
    'action' => 'deleteRequest',
    'ext' => 'json',
),
    array(
        'request_id' => '[0-9]+',
    )
);
// --- /group/join/invite
Router::connect('/api/group/invite', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'POST',
    'action' => 'sendInvite',
    'ext' => 'json',
));
// --- /group/create
Router::connect('/api/group/create', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
));
// --- /group/edit
Router::connect('/api/group/edit', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
));

// --- /group/delete/:group_id
Router::connect('/api/group/delete/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// --- /group/delete/:group_id
Router::connect('/api/group/delete/:group_id', array(
    'plugin' => 'Api',
    'controller' => 'group',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'group_id' => '[0-9]+',
    )
);
// END GROUP

// EVENT
// --- /event/:type
Router::connect('/api/event/:type', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'type' => '(upcoming|myupcomming|mypast|friendattend|past)',
    )
);
// --- /event/filter/
Router::connect('/api/event/filter', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'POST',
    'action' => 'browse',
    'ext' => 'json',
));
// --- /event/category/:category_id
Router::connect('/api/event/category/:category_id', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'GET',
    'action' => 'browse',
    'ext' => 'json',
),
    array(
        'category_id' => '[0-9]+',
    )
);
// --- /event/activity/:event_id
Router::connect('/api/event/activity/:event_id', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'GET',
    'action' => 'eventActivity',
    'ext' => 'json',
),
    array(
        'event_id' => '[0-9]+',
    )
);
// --- /event/:type
Router::connect('/api/event/rsvp/:type/:event_id', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'GET',
    'action' => 'rsvp',
    'ext' => 'json',
),
    array(
        'type' => '(attend|maybe|no|wait)',
        'event_id' => '[0-9]+',
    )
);

// --- /event/view/:event_id
Router::connect('/api/event/view/:event_id', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'GET',
    'action' => 'view',
    'ext' => 'json',
),
    array(
        'event_id' => '[0-9]+',
    )
);
// --- /event/invite
Router::connect('/api/event/invite', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'POST',
    'action' => 'sendInvite',
    'ext' => 'json',
));

// --- /event/rsvp
Router::connect('/api/event/rsvp', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'POST',
    'action' => 'sendRSVP',
    'ext' => 'json',
));
// --- /event/create
Router::connect('/api/event/create', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
));
// --- /event/edit
Router::connect('/api/event/edit', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'POST',
    'action' => 'save',
    'ext' => 'json',
));

// --- /event/delete/:event_id
Router::connect('/api/event/delete/:event_id', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'DELETE',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'event_id' => '[0-9]+',
    )
);
// --- /event/delete/:event_id
Router::connect('/api/event/delete/:event_id', array(
    'plugin' => 'Api',
    'controller' => 'event',
    '[method]' => 'GET',
    'action' => 'delete',
    'ext' => 'json',
),
    array(
        'event_id' => '[0-9]+',
    )
);
// END EVENT

// User

// --- /user/me
Router::connect('/api/user/me', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'me',
    'ext' => 'json',
));

// --- /user/{user-id}
Router::connect('/api/user/:id', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'user',
    'ext' => 'json',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);

// --- GET /user/{user-id}/activities
Router::connect('/api/user/:id/activities', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getActivities',
    'ext' => 'json',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/friends
Router::connect('/api/user/:id/friends', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getFriends',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/albums
Router::connect('/api/user/:id/albums', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getAlbums',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- POST /user/{user-id}/albums
Router::connect('/api/user/:id/albums', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'POST',
    'action' => 'postAlbums',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/events
Router::connect('/api/user/:id/events', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getEvents',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/groups
Router::connect('/api/user/:id/groups', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getGroups',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/likes
Router::connect('/api/user/:id/likes', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getLikes',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/notifications
Router::connect('/api/user/:id/notifications', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getNotifications',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/photos
Router::connect('/api/user/:id/photos', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getPhotos',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/{user-id}/photos/uploaded
Router::connect('/api/user/:id/photos/uploaded', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getPhotosUploaded',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
Router::connect('/api/user/:id/photos/uploaded', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getPhotosUploaded',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
// --- GET /user/register
Router::connect('/api/user/register', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getRegister',
    'ext' => 'json',
)
);
// --- POST /user/register
Router::connect('/api/user/register', array(
        'plugin' => 'Api',
        'controller' => 'users',
        '[method]' => 'POST',
        'action' => 'postRegister',
        'ext' => 'json',
    )
);
// --- POST /user/register

// --- GET /user/forgot
Router::connect('/api/user/forgot', array(
    'plugin' => 'Api',
    'controller' => 'users',
    '[method]' => 'GET',
    'action' => 'getForgot',
    'ext' => 'json',
)
);
// --- POST /user/forgot
Router::connect('/api/user/forgot', array(
        'plugin' => 'Api',
        'controller' => 'users',
        '[method]' => 'POST',
        'action' => 'postForgot',
        'ext' => 'json',
    )
);
// --- POST /user/forgot

Router::connect('/api/user/me/avatar', array(
        'plugin' => 'Api',
        'controller' => 'uploads',
        '[method]' => 'POST',
        'action' => 'avatar',
        'ext' => 'json',
    )
);


// End User

// REPORT
// --- POST /{object}/report
Router::connect('/api/:object/report', array(
        'plugin' => 'Api',
        'controller' => 'reports',
        '[method]' => 'POST',
        'action' => 'action',
        'ext' => 'json',
),
    array(
        'object' => '(blog|album|photo|video|topic|group|event|user)',
    )
);

// END REPORT
/*
Router::connect('/api/search/:keyword', array(
    'plugin' => 'Api',
    'controller' => 'searchs',
    '[method]' => 'GET',
    'action' => 'index',
    'ext' => 'json',
),
    array(
        'pass' => array('keyword'),
        //'keyword'=>'[0-9]+'
    )
);
*/
Router::connect('/api/search', array(
    'plugin' => 'Api',
    'controller' => 'searchs',
    '[method]' => 'POST',
    'action' => 'index',
    'ext' => 'json',
)
);
//--- End search

//----- Notifications
// --- /search/{keyword}
// Chat integration
$turnOnNotificaitonRouter = true;
$chatNotificationTurnOn = Configure::read('Chat.chat_turn_on_notification');
if(isset($chatNotificationTurnOn) && $chatNotificationTurnOn == 1){
    $turnOnNotificaitonRouter = false;
}
if($turnOnNotificaitonRouter){
    Router::connect('/api/notification/me', array(
            'plugin' => 'Api',
            'controller' => 'Notification',
            '[method]' => 'GET',
            'action' => 'refresh',
            'ext' => 'json',
        )
    );

}
Router::connect('/api/notification/me/show', array(
        'plugin' => 'Api',
        'controller' => 'Notification',
        '[method]' => 'GET',
        'action' => 'show',
        'ext' => 'json',
    )
);
Router::connect('/api/notification/me/clear', array(
        'plugin' => 'Api',
        'controller' => 'Notification',
        '[method]' => 'GET',
        'action' => 'clear',
        'ext' => 'json',
    )
);
Router::connect('/api/notification/me/delete', array(
        'plugin' => 'Api',
        'controller' => 'Notification',
        '[method]' => 'POST',
        'action' => 'remove',
        'ext' => 'json',
    )
);
Router::connect('/api/notification/:id', array(
        'plugin' => 'Api',
        'controller' => 'Notification',
        '[method]' => 'POST',
        'action' => 'update',
        'ext' => 'json',
    ),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
//---------------

//---------- Google Cloud Messaging
Router::connect('/api/user/me/gcm/token', array(
    'plugin' => 'Api',
    'controller' => 'ApiGcms',
    '[method]' => 'POST',
    'action' => 'post',
    'ext' => 'json',
),
    array(
        'pass' => array('user_id'),
        'user_id' => '[0-9]+'
    )
);
Router::connect('/api/user/me/gcm/token', array(
        'plugin' => 'Api',
        'controller' => 'ApiGcms',
        '[method]' => 'DELETE',
        'action' => 'delete',
        'ext' => 'json',
    )

);
Router::connect('/api/user/me/gcm/token/delete', array(
        'plugin' => 'Api',
        'controller' => 'ApiGcms',
        '[method]' => 'POST',
        'action' => 'delete',
        'ext' => 'json',
    )
);
//---------- End Google Cloud Messaging

//----- Messages
// Chat integration
$turnOnMessagesRouter = true;
$chatNotificationTurnOn = Configure::read('Chat.chat_turn_on_notification');
if(isset($chatNotificationTurnOn) && $chatNotificationTurnOn == 1){
    $turnOnMessagesRouter = false;
}
if($turnOnMessagesRouter) {
    Router::connect('/api/message/me/show', array(
            'plugin' => 'Api',
            'controller' => 'Message',
            '[method]' => 'GET',
            'action' => 'show',
            'ext' => 'json',
        )
    );
}
//------ End Messages

//----- Document
Router::connect('/api/document', array(
        'plugin' => 'Api',
        'controller' => 'DocumentApi',
        '[method]' => 'GET',
        'action' => 'index',
    )
);
//------ End Document

//----- SHARE
Router::connect('/api/share/wall', array(
    'plugin' => 'Api',
    'controller' => 'Share',
    '[method]' => 'POST',
    'action' => 'wall',
    'ext' => 'json',
    )
);
Router::connect('/api/share/friend', array(
    'plugin' => 'Api',
    'controller' => 'Share',
    '[method]' => 'POST',
    'action' => 'friend',
    'ext' => 'json',
    )
);
Router::connect('/api/share/group', array(
    'plugin' => 'Api',
    'controller' => 'Share',
    '[method]' => 'POST',
    'action' => 'group',
    'ext' => 'json',
    )
);
Router::connect('/api/share/msg', array(
    'plugin' => 'Api',
    'controller' => 'Share',
    '[method]' => 'POST',
    'action' => 'msg',
    'ext' => 'json',
    )
);
Router::connect('/api/share/email', array(
    'plugin' => 'Api',
    'controller' => 'Share',
    '[method]' => 'POST',
    'action' => 'email',
    'ext' => 'json',
    )
);
//------ END SHARE

// read message
Router::connect('/api/message/:id', array(
    'plugin' => 'Api',
    'controller' => 'Message',
    '[method]' => 'POST',
    'action' => 'update',
    'ext' => 'json',
),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);