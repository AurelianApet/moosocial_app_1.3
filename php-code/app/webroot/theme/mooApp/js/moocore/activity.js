/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery','mooPhrase', 'mooBehavior', 'mooMention', 'mooEmoji', 'mooFileUploader', 'mooUser', 'mooButton', 'mooGlobal',
            'mooResponsive', 'mooAttach', 'mooComment', 'mooLike',
            'autogrow', 'spinner'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooActivities = factory();
    }
}(this, function ($, mooPhrase, mooBehavior, mooMention, mooEmoji, mooFileUploader, mooUser, 
    mooButton, mooGlobal, mooResponsive, mooAttach, mooComment, mooLike) {
    
    var config = {};
    
    var initRemoveTags = function(){
        $('.removeTags').unbind('click');
        $('.removeTags').click(function(){
            var data = $(this).data();
            removeTags(data.activityId, data.activityItemType);
        });
    }
    
    var removeTags = function(item_id, item_type){
        $.fn.SimpleModal({
            btn_ok: mooPhrase.__('confirm'),
            btn_cancel: mooPhrase.__('cancel'),
            callback: function(){
                $.post(mooConfig.url.base+'/activities/ajax_remove_tags', {item_id: item_id, item_type : item_type}, function() {
                    window.location.reload();
                });
            },
            title: mooPhrase.__('remove_tags'),
            contents: mooPhrase.__('remove_tags_contents'),
            model: 'confirm', 
            hideFooter: false, 
            closeButton: false
        }).showModal();
    };
    
    var initShowAllComments = function(){
        $('.showAllComments').unbind('click');
        $('.showAllComments').on('click', function(){
            var data = $(this).data();
            showAllComments(data.id);
        });
    }
    
    
    var showAllComments = function( activity_id ){
        $('#comments_' + activity_id + ' .hidden').fadeIn();
        $('#comments_' + activity_id + ' .hidden').attr('class','');
        $('#all_comments_' + activity_id).hide();
    }
    
    var removeActivity = function(id)
    {
        $.fn.SimpleModal({
            btn_ok: mooPhrase.__('ok'),
            btn_cancel: mooPhrase.__('cancel'),
            callback: function(){
                $.post(mooConfig.url.base+'/activities/ajax_remove', {id: id}, function() {
                    $('#activity_'+id).fadeOut('normal', function() {
                        $('#activity_'+id).remove();
                    });
                });
            },
            title: mooPhrase.__('please_confirm'),
            contents: mooPhrase.__('please_confirm_remove_this_activity'),
            model: 'confirm', 
            hideFooter: false, 
            closeButton: false
        }).showModal();
    };
    
    
    var initEditActivity = function(){
        $('.editActivity').unbind("click");
        $('.editActivity').click(function(){
            var data = $(this).data();
            editActivity(data.activityId);
        });
    }
    
    var initRemoveActivity = function(){
        $('.removeActivity').unbind("click");
        $('.removeActivity').click(function(){
            var data = $(this).data();
            removeActivity(data.activityId);
        });
    }
    
    // app/View/Activities/ajax_load_activity_edit.ctp
    var initOnAjaxLoadActivityEdit = function(){
        
        // init cancel edit activity event
        $('.cancelEditActivity').unbind('click');
        $('.cancelEditActivity').click(function(){
            var data = $(this).data();
            cancelEditActivity(data.activityId);
        });
        
        // init comfirm edit activity event
        $('.confirmEditActivity').unbind('click');
        $('.confirmEditActivity').click(function(){
            var data = $(this).data();
            confirmEditActivity(data.activityId);
        });
    }

    var activity_edit_array = [];
    var editActivity = function(activity_id)
    {
    	if ($('#activity_edit_'+activity_id).length == 0)
        {
            $.post(mooConfig.url.base + '/activities/ajax_loadActivityEdit/'+ activity_id, function(data){
                $('#activity_feed_content_text_'+activity_id + ' .comment_message').hide();
                $(data).insertAfter($('#activity_feed_content_text_'+activity_id + ' .comment_message'));
                activity_edit_array.push(activity_id);
                init();

                
                //user mention
                mooMention.init($(data).find('textarea').attr('id'),'edit_activity');
                
                //user emoji
                mooEmoji.init($(data).find('textarea').attr('id'));
            });
        }
    };

    var cancelEditActivity = function(activity_id)
    {
        //destroy overlay instance;
        if($('#message_edit_'+activity_id).siblings('.textoverlay')){
            $('#message_edit_'+activity_id).destroyOverlayInstance($('#message_edit_'+activity_id));
        }

        $('#activity_feed_content_text_'+activity_id + ' .comment_message').show();
        $('#activity_edit_'+activity_id).remove();

        var index = $.inArray(activity_id, activity_edit_array);
        activity_edit_array.splice(index, 1);
    };

    var confirmEditActivity = function(activity_id)
    {
        if ($.trim($('#message_edit_'+activity_id).val()) != '')
        {
            var messageVal;
            if($("#message_edit_"+activity_id+"_hidden").length != 0){
                messageVal = $("#message_edit_"+activity_id+'_hidden').val();
            }else{
                messageVal = $("#message_edit_"+activity_id).val()
            }
            $.post(mooConfig.url.base + '/activities/ajax_editActivity/'+ activity_id,{message: messageVal}, function(data){
                //destroy overlay instance;
                if($('#message_edit_'+activity_id).siblings('.textoverlay')){
                    $('#message_edit_'+activity_id).destroyOverlayInstance($('#message_edit_'+activity_id));
                }

                $('#activity_feed_content_text_'+activity_id + ' .comment_message').html($(data).html());
                $('#history_activity_'+activity_id).show();
                cancelEditActivity(activity_id);
            });
        }
   
 };
     var removeActivityPhotoComment = function(id)
    {
        $.fn.SimpleModal({
            btn_ok: mooPhrase.__('ok'),
            btn_cancel: mooPhrase.__('cancel'),
            callback: function(){
                $.post(mooConfig.url.base+'/comments/ajax_remove', {id: id}, function() {
                    $('#photo_comment_'+id).fadeOut('normal', function() {
                        $('#photo_comment_'+id).remove();
                    });
                });
            },
            title:  mooPhrase.__('please_confirm'),
            contents: mooPhrase.__('please_confirm_remove_this_activity'),
            model: 'confirm', hideFooter: false, closeButton: false
        }).showModal();
    };
    
    var submitComment = function(activity_id)
    {
        if ($.trim($("#commentForm_"+activity_id).val()) != '' || $('#comment_image_'+activity_id).val() != '')
        {
            $('#commentButton_' + activity_id + ' a').addClass('disabled');
            $('#commentButton_' + activity_id + ' a').prepend('<i class="icon-refresh icon-spin"></i>');
            var comment = ($("#commentForm_"+activity_id).siblings('input.messageHidden').length > 0) ? $("#commentForm_"+activity_id).siblings('input.messageHidden').val() : $("#commentForm_"+activity_id).val();
            $.post(mooConfig.url.base + "/activities/ajax_comment", {activity_id: activity_id,thumbnail:$('#comment_image_'+activity_id).val(), comment: comment}, function(data){
                if (data != ''){
                    showPostedComment(activity_id, data);

                    
                    //reset mention
                    var textArea = $("#commentForm_"+activity_id);
                    mooMention.resetMention(textArea);
                     componentHandler.upgradeAllRegistered();
                }

                
            });

        }
    };
    
    var submitItemComment = function(item_type, item_id, activity_id)
    {
        if ($.trim($("#commentForm_"+activity_id).val()) != '' || $('#comment_image_'+activity_id).val() != '')
        {
            $('#commentButton_' + activity_id + ' a').prepend('<i class="icon-refresh icon-spin"></i>');
            $('#commentButton_' + activity_id + ' a').addClass('disabled');
            var message = '';
            if($("#commentForm_"+activity_id).siblings('.messageHidden').length > 0){
                message = $("#commentForm_"+activity_id).siblings('.messageHidden').val();
            }else{
                message = $("#commentForm_"+activity_id).val();
            }
            $.post(mooConfig.url.base + "/comments/ajax_share", {type: item_type, target_id: item_id, thumbnail:$('#comment_image_'+activity_id).val() ,message: message, activity: 1}, function(data){
                if (data != ''){
                    showPostedComment(activity_id, data);

                    
                    //reset mention
                    var textArea = $("#commentForm_"+activity_id);
                    mooMention.resetMention(textArea);
                    componentHandler.upgradeAllRegistered();
                }
            });

        }
    };

    var showPostedComment = function(activity_id, data)
    {
        if (mooConfig.comment_sort_style === '1'){
            $('#newComment_'+activity_id).before(data);
        }else{
            $('#newComment_'+activity_id).after(data);
        }
        
        $('.slide').slideDown();
        $('#commentButton_' + activity_id + ' a').removeClass('disabled');
        $('#commentButton_' + activity_id + ' a i').remove();
        $("#commentForm_"+activity_id).val('');
        $("#commentButton_"+activity_id).hide();
        
        $('.commentBox').css('height', '27px');
        $('#comment_preview_image_' + activity_id).html('');
        $('#comment_image_' + activity_id).val('');
        $('#comment_button_attach_'+activity_id).show();
        mooBehavior.registerImageComment();
        init();
    };

    var changeActivityPrivacy = function(obj, activity_id, privacy)
    {
        $.post(mooConfig.url.base + '/activities/ajax_changeActivityPrivacy/',{activityId: activity_id, privacy: privacy}, function(data){
            if(data != ''){
                data = JSON.parse(data);
                var icon;
                switch(data.icon) {
                    case "icon-globe-1":
                        icon="public";
                        break;
                    case "icon-users-1":
                        icon="people";
                        break;
                    case "icon-lock-1":
                        icon="lock";
                        break;
                }
                var parent = obj.parents('.dropdown');
                parent.find('a#permission_'+activity_id).attr('original-title',data.text);

                parent.find('a#permission_'+activity_id+' i').html(icon);
                parent.find('.dropdown-menu li a').removeClass('n52');
                obj.addClass('n52');
            }
        });
    };
    
    // app/View/Elements/activity_form.ctp
    // app/View/Elements/activities.ctp
    // app/View/Comments/ajax_share.ctp
    // app/View/Activities/ajax_share.ctp
    var init = function(configParam){
        $('textarea:not(.no-grow)').autogrow();
        if( typeof config !== undefined) config = configParam;

        // init remove tags
        initRemoveTags();
        
        // bind edit activity event
        initEditActivity();
        
        // bind remove activity event
        initRemoveActivity();
        
        // bind edit activity comment event
        mooComment.initEditActivityComment();
             
        // bind remove activity comment event
        mooComment.initRemoveActivityComment();
        
        // bind remove item comment event
        mooComment.initRemoveItemComment();
        
        // remove  activity photo comment event
        $('body').off('click.activity','a.admin-or-owner-confirm-delete-photo-comment').on('click.activity','a.admin-or-owner-confirm-delete-photo-comment',function(){
            var data = $(this).data();

            if( typeof data.commentId !== undefined){
                removeActivityPhotoComment(data.commentId);
            }
        });
        // submitComment event
        $('body').off('click.activity','a.viewer-submit-comment').on('click.activity','a.viewer-submit-comment',function(){
            var data = $(this).data();

            if( typeof data.activityId !== undefined){
                submitComment(data.activityId);
            }
        });
        // submitComment event
        $('body').off('click.activity','a.viewer-submit-item-comment').on('click.activity','a.viewer-submit-item-comment',function(){
            var data = $(this).data();

            if( typeof data.itemType !== undefined && typeof data.activityItemId !== undefined && typeof data.activityId !== undefined){
                submitItemComment(data.itemType,data.activityItemId,data.activityId);
            }
        });
        //change activity's privacy
        $('body').off('click.activity','a.change-activity-privacy').on('click.activity','a.change-activity-privacy',function(){
            var data = $(this).data();
            if(typeof data.activityId !== undefined && typeof data.privacy !== undefined){
                changeActivityPrivacy($(this),data.activityId, data.privacy);
            }
        });
        
        // init showCommentForm
        mooComment.initShowCommentForm();
        
        // init LikeActivit
        mooLike.initLikeActivity();
        
        // init remove item comment
        mooComment.initRemoveItemComment();
        
        // init edit item comment
        mooComment.initEditItemComment();
        
        // init show comment btn on focus textarea 
        mooComment.initShowCommentBtn();
        
        // init View all %s comments
        initShowAllComments();
        
        // init load more
        mooBehavior.initMoreResults();

        componentHandler.upgradeAllRegistered();
        
    }
    
    // app/View/Elements/activity_form.ctp
    var initActivityForm = function(){
        
        $('[data-toggle="tooltip"]').tooltip();
        
        var uploader = new mooFileUploader.fineUploader({
            element: $('#select-2')[0],
            text: {
                uploadButton: '<div class="upload-section"><span class=" mdl-button mdl-js-button mdl-button--icon"><i class="material-icons">local_see</i></span></div>'
            },
            validation: {
                allowedExtensions: mooConfig.photoExt,
                sizeLimit : mooConfig.sizeLimit
            },
            multiple: true,
            request: {
                endpoint: mooConfig.url.base + "/upload/wall"
            },

            callbacks: {
                onError: mooGlobal.errorHandler,
                onSubmit: function(id, fileName){
                    var element = $('<span id="feed_'+id+'" style="background-image:url(' + mooConfig.url.base + '/img/indicator.gif);background-size:inherit;background-repeat:no-repeat"></span>');
                    element.insertBefore('.addMoreImage');
                    $('#wall_photo_preview').show();
                    $('#addMoreImage').show();
                    resetVideoUpload();
                },
                onComplete: function(id, fileName, response, xhr) {
                    if (response.success){
                        $(this.getItemByFileId(id)).remove();
                        var img = $('<img src="'+response.file_path+'">');
                        img.load(function() {
                            var element = $('#feed_'+id);
                            element.attr('style','background-image:url(' + response.file_path + ')');
                            var deleteItem = $('<a href="#"><i class="icon-delete"></i></a>');
                            element.append(deleteItem);

                            element.find('.icon-delete').unbind('click');
                            element.find('.icon-delete').click(function(e){
                                 e.preventDefault();
                                 $(this).parents('span').remove();
                                 $('#wall_photo').val($('#wall_photo').val().replace(response.photo + ',',''));
                            });
                        });

                        var wall_photo = $('#wall_photo').val();
                        $('#wall_photo').val(wall_photo+ response.photo + ',');              

                    }         
                }
            }
        });


        // FIXED_JS todo for upload video plugin 
        if ($('#videoPcFeed').length > 0){
            var uploader2 = new mooFileUploader.fineUploader({
                element: $('#videoPcFeed')[0],
                multiple: false,
                text: {
                    uploadButton: '<div class="upload-section"><i class="material-icons">videocam</i></div>'
                },
                validation: {
                    allowedExtensions: mooConfig.videoExt,
                    sizeLimit: mooConfig.sizeLimit       
                },
                request: {
                    endpoint: mooConfig.url.base + "/upload_video/upload_videos/process_upload"
                },
                callbacks: {
                    onError: mooGlobal.errorHandler,
                    onSubmit: function(id, fileName){
                        $('#wall_photo_preview').hide();
                        $('#video_pc_feed_preview').show();
                        $('.video_thumb').spin('medium');
                    },
                    onComplete: function (id, fileName, response) {
                        if (response.success){
                            $('#video_destination').val(response.filename);
                            $('.video_thumb').spin(false);
                        }
                    }
                }
            });

            $('#triggerUpload').click(function () {
                if (uploader2._storedFileIds.length) {

                    uploader2.uploadStoredFiles();
                    $(".error-message").hide();
                }
                else {
                    $(".error-message").html(mooPhrase.__('uv_4'));
                    $(".error-message").show();
                }
            });
        }

        $('#addMoreImage').unbind('click');
        $('#addMoreImage').click(function(){
            $('#select-2 input[name=file]').click();
        });
        
        // init onfocus share what's news
        $('#message').on('focus', function(){
            mooComment.showCommentButton(0);
        });
        
        // bind share button
        $('#status_btn').unbind('click');
        $('#status_btn').click(function(){
            postWall(); 
        });
        
        
        // show activity form
        $('#status_box').slideDown("slow");
    }
    
    var resetVideoUpload = function(){
        $('#video_pc_feed_preview').hide();
        $('#title').val('');
        $('#description').val('');
        $('#video_destination').val('');
    }
    
    var postWall = function()
    {
        if (!mooUser.validateUser()){
            return false;
        }

        var msg = $('#message').val();
        if ($.trim(msg) != '' || ($('#wall_photo_preview :not(#addMoreImage)').html() != '' && $('#wall_photo_preview :not(#addMoreImage)').html() != 'add'))
        {
            console.log('111');
            mooButton.disableButton('status_btn');
            $('#status_btn').spin('small');
            $.post(mooConfig.url.base + "/activities/ajax_share", $("#wallForm").serialize(), function(data){
                $('#wall_photo').val('');
                mooButton.enableButton('status_btn');
                $('#message').val("");
                if ($("#video_destination").length > 0 && $("#video_destination").val() != ''){
                    $.fn.SimpleModal({
                        btn_ok : mooPhrase.__('btn_ok'),
                        btn_cancel: mooPhrase.__('cancel'),
                        model: 'modal',
                        title: mooPhrase.__('processing_video'),
                        contents: mooPhrase.__('processing_video_msg')
                    }).showModal();

                    setTimeout(function(){
                        $('#simpleModal').hide();
                        $('.modal-backdrop').remove();
                    }, 3000);

                    resetVideoUpload();

                    var parseJSON = $.parseJSON(data);

                    // send request convert video
                    $.post(mooConfig.url.base + "/upload_video/upload_videos/feed_convert", {activity_id : parseJSON.activity_id}, function(data){

                    });
                }
                else{
                    if (data != '')
                    {
                        if($('.no-feed').length > 0 ){
                            $('#list-content .no-feed').remove();
                        }
                        
                        $('#list-content').prepend(data);
                        
                        $('#message').css('height', '36px');
                        $('.slide').slideDown();

                        $('#wall_photo_preview span:not(.addMoreImage)').remove();
                        $('#addMoreImage').hide();
                        $('.form-feed-holder').css('padding-bottom','0px');

                        //register image
                        var attachment_id = $(data).find('div[id^=comment_button_attach_]').data('id');
                        mooAttach.registerAttachComment(attachment_id);
                    }
                }       

                $('#status_btn').spin(false);
                mooResponsive.init();
                $(".tip").tipsy({ html: true, gravity: 's' });
                $('[data-toggle="tooltip"]').tooltip();

                //reset mention
                var textArea = $("#wallForm").find('#message');
                
                mooMention.resetMention(textArea);
                

            });
            $('.stt-action .userTagging-userTagging').addClass('hidden');
            $('.stt-action').css('margin-top','0');
            $('#wall_photo_preview').hide();
            $('#userTagging').tagsinput('removeAll');
        }else{
            $.fn.SimpleModal({
                btn_ok : mooPhrase.__('btn_ok'),
                btn_cancel: mooPhrase.__('cancel'),
                model: 'modal',
                title: mooPhrase.__('warning'),
                contents: mooPhrase.__('share_whats_new_can_not_empty')
            }).showModal();
        }
    }
    
    //    exposed public method
    return {
        init:init,
        initActivityForm : initActivityForm,
        initOnAjaxLoadActivityEdit : initOnAjaxLoadActivityEdit
    };
}));