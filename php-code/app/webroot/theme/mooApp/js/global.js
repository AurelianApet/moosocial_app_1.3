
var sModal;
var file_uploading = 0;
var keyEffect = {};
var replaceLength = {};


function validateUser(){
    if (typeof(mooViewer) == 'undefined'){
        $.fn.SimpleModal({
            btn_ok : mooPhrase.__('btn_ok'),
            model: 'modal',
            title: mooPhrase.__('warning'),
            contents: mooPhrase.__('please_login')
        }).showModal();
		return false;
    }
    else if (mooCore['setting.require_email_validation'] && !mooViewer['is_confirmed']){
        $.fn.SimpleModal({
            btn_ok : mooPhrase.__('btn_ok'),
            model: 'modal',
            title: mooPhrase.__('warning'),
            contents: mooPhrase.__('please_confirm_your_email')
        }).showModal();
		return false;
    }
    else if (mooCore['setting.approve_users'] && !mooViewer['is_approved']){
        $.fn.SimpleModal({
            btn_ok : mooPhrase.__('btn_ok'),
            model: 'modal',
            title: mooPhrase.__('warning'),
            contents: mooPhrase.__('your_account_is_pending_approval')
        }).showModal();
		return false;
    }
    
    return true;
}

function registerOverlay()
{
    $('.overlay').unbind('click');
    $('.overlay').click(function()
    {
        overlay_title = $(this).attr('title');
        overlay_url = $(this).attr('href');
        overlay_div = $(this).attr('rel');

        if (overlay_div)
        {
            sModal = $.fn.SimpleModal({
                btn_ok : mooPhrase.__('btn_ok'),
                model: 'modal',
                title: overlay_title,
                contents: $('#' + overlay_div).html()
            });
        }
        else
        {
            sModal = $.fn.SimpleModal({
                width: 600,
                model: 'modal-ajax',
                title: overlay_title,
                offsetTop: 100,
                param: {
                    url: overlay_url,
                    onRequestComplete: function() {
                        $(".tip").tipsy({ html: true, gravity: 's' });
                    },
                    onRequestFailure: function() { }
                }
            });
        }

        sModal.showModal();

        return false;
    });
}

function registerImageOverlay()
{
    $('.attached-image').magnificPopup({
        type:'image',
        gallery: { enabled: true },
        zoom: {
            enabled: true,
            opener: function(openerElement) {
                return openerElement;
            }
        }
    });
}

function submitComment(activity_id)
{
    if (jQuery.trim(jQuery("#commentForm_"+activity_id).val()) != '' || $('#comment_image_'+activity_id).val() != '')
    {
        $('#commentButton_' + activity_id + ' a').addClass('disabled');
        $('#commentButton_' + activity_id + ' a').prepend('<i class="icon-refresh icon-spin"></i>');
        $.post(mooConfig.url.base + "/activities/ajax_comment", {activity_id: activity_id,thumbnail:$('#comment_image_'+activity_id).val(), comment: jQuery("#commentForm_"+activity_id).val()}, function(data){
            if (data != '')
                showPostedComment(activity_id, data);
        });
    }
}

function submitItemComment(item_type, item_id, activity_id)
{
    if ($.trim(jQuery("#commentForm_"+activity_id).val()) != '' || $('#comment_image_'+activity_id).val() != '')
    {
        $('#commentButton_' + activity_id + ' a').prepend('<i class="icon-refresh icon-spin"></i>');
        $('#commentButton_' + activity_id + ' a').addClass('disabled');
        $.post(mooConfig.url.base + "/comments/ajax_share", {type: item_type, target_id: item_id, thumbnail:$('#comment_image_'+activity_id).val() ,message: jQuery("#commentForm_"+activity_id).val(), activity: 1}, function(data){
            if (data != '')
                showPostedComment(activity_id, data);
        });
    }
}

function showPostedComment(activity_id, data)
{
    $('#newComment_'+activity_id).before(data);
    $('.slide').slideDown();
    $('#commentButton_' + activity_id + ' a').removeClass('disabled');
    $('#commentButton_' + activity_id + ' a i').remove();
    $("#commentForm_"+activity_id).val('');
    $("#commentButton_"+activity_id).hide();
    registerCrossIcons();
    $('.commentBox').css('height', '27px');
    $('#comment_preview_image_' + activity_id).html('');
    $('#comment_image_' + activity_id).val('');
    $('#comment_button_attach_'+activity_id).show();
    registerImageComment();
}



function showCommentForm(activity_id)
{
    jQuery("#comments_"+activity_id).show();
    jQuery("#newComment_"+activity_id).show();

    $('#commentForm_'+activity_id).focus();
}

function resetVideoUpload(){
    $('#video_pc_feed_preview').hide();
    $('#title').val('');
    $('#description').val('');
    $('#video_destination').val('');
}

function postWall()
{
    if (!validateUser()){
        return false;
    }

    var msg = $('#message').val();
    if ($.trim(msg) != '' || $('#wall_photo_preview :not(#addMoreImage)').html() != '')
    {
        disableButton('status_btn');
        $('#status_btn').spin('small');
        $.post(mooConfig.url.base + "/activities/ajax_share", $("#wallForm").serialize(), function(data){
            $('#wall_photo').val('');
            enableButton('status_btn');
            $('#message').val("");
            if ($("#video_destination").length > 0 && $("#video_destination").val() != ''){
                $.fn.SimpleModal({
                    btn_ok : mooPhrase.__('btn_ok'),
                    model: 'modal',
                    title: mooPhrase.__('processing_video'),
                    contents: mooPhrase.__('processing_video_msg')
                }).showModal();
                
                setTimeout(function(){
                    $('#simpleModal').hide();
                    $('.modal-backdrop').remove();
                }, 3000);
                
                resetVideoUpload();
                
                $parseJSON = $.parseJSON(data);
                
                // send request convert video
                $.post(mooConfig.url.base + "/upload_video/upload_videos/feed_convert", {activity_id : $parseJSON.activity_id}, function(data){
                    
                });
            }
            else{
                if (data != '')
                {
                    if($('.no-feed').length > 0 )
                        $('#list-content .no-feed').remove();
                    $('#list-content').prepend(data);
                    registerCrossIcons();
                    $('#message').css('height', '36px');
                    $('.slide').slideDown();

                $('#wall_photo_preview span:not(.addMoreImage)').remove();
                $('#addMoreImage').hide();
                $('.form-feed-holder').css('padding-bottom','0px');

                    //register image
                    var attachment_id = $(data).find('div[id^=comment_button_attach_]').data('id');
                    registerAttachComment(attachment_id);
                }
            }       

            $('#status_btn').spin(false);
            MooResponsive.init();
            $(".tip").tipsy({ html: true, gravity: 's' });
            $('[data-toggle="tooltip"]').tooltip();

            

        });
        $('.stt-action .userTagging-userTagging').addClass('hidden');
        $('.stt-action').css('margin-top','0');
        $('#wall_photo_preview').hide();
        $('#userTagging').tagsinput('removeAll');
         componentHandler.upgradeAllRegistered();
    }else{
        $.fn.SimpleModal({
            btn_ok : mooPhrase.__('btn_ok'),
            model: 'modal',
            title: mooPhrase.__('warning'),
            contents: mooPhrase.__('share_whats_new_can_not_empty')
        }).showModal();
    }

}

function ajax_postComment(id)
{
    if ($.trim($('#postComment').val()) != '' || $.trim($('#theaterPhotoComment').val()) != '' || $('#comment_image_' + id).val() != '')
    {
        $('#shareButton').addClass('disabled');
        $('#shareButton').prepend('<i class="icon-refresh icon-spin"></i>');
        var commentFormSerialize = '';
        if ($('#commentForm').length){
            commentFormSerialize = $("#commentForm").serialize();
        }

        if ($('#theaterPhotoCommentForm').length){
            commentFormSerialize = $("#theaterPhotoCommentForm").serialize();
        }
        $.post(mooConfig.url.base + "/comments/ajax_share", commentFormSerialize, function(data){

            $('#shareButton').removeClass('disabled');
            $('#shareButton i').remove();
            $('.commentForm').css('height', '35px');
            
            if ($('#postComment').length){
                $('#postComment').val("");
            }
            
            if ($('#theaterPhotoComment').length){
                $('#theaterPhotoComment').val("");
            }

            if (data != '')
            {
                if ($('#theaterComments').length){
                    $('#theaterComments').prepend(data);
                }
                else {
                    $('#comments').prepend(data);
                }
                
                $('.slide').slideDown();
                if (!$('#theaterComments').length){                	
                	$("#comment_count").html( parseInt($("#comment_count").html()) + 1 );
                }

                
                $('#comment_preview_image_' + id).html('');
                $('#comment_image_' + id).val('');
                $('#comment_button_attach_'+id).show();
                registerImageComment();

                
                componentHandler.upgradeAllRegistered();
            }
        });
//        if($('#theaterComments').size() > 0){
//            if($('#theaterComments').height() > 500){
//                $('#theaterComments').slimScroll({ height: '500px',start: 'bottom' });
//            }
//            
//        }
        
    }else{
        // mooAlert('Comment can not empty');
        $.fn.SimpleModal({
            btn_ok : mooPhrase.__('btn_ok'),
            model: 'modal',
            title: mooPhrase.__('warning'),
            contents: mooPhrase.__('comment_empty')
        }).showModal();
    }
}
var activity_edit_array = [];
function editActivity(activity_id)
{
	if (activity_edit_array.indexOf(activity_id) === -1)
	{
		$.post(mooConfig.url.base + '/activities/ajax_loadActivityEdit/'+ activity_id, function(data){
			$('#activity_feed_content_text_'+activity_id + ' .comment_message').hide();
			$(data).insertAfter($('#activity_feed_content_text_'+activity_id + ' .comment_message'));
			activity_edit_array.push(activity_id);
	    });
	}
}

function cancelEditActivity(activity_id)
{
	$('#activity_feed_content_text_'+activity_id + ' .comment_message').show();
	$('#activity_edit_'+activity_id).remove();
	
	var index = $.inArray(activity_id, activity_edit_array);
	activity_edit_array.splice(index, 1);
}

function confirmEditActivity(activity_id)
{
	if ($.trim($('#message_edit_'+activity_id).val()) != '')
	{
		$.post(mooConfig.url.base + '/activities/ajax_editActivity/'+ activity_id,{message: jQuery("#message_edit_"+activity_id).val()}, function(data){
			$('#activity_feed_content_text_'+activity_id + ' .comment_message').html($(data).html());
			$('#history_activity_'+activity_id).show();
			cancelEditActivity(activity_id);			
	    });
	}
}


function cancelEditActivityComment(comment_id)
{
    //destroy overlay instance;
    if($("#message_activity_comment_edit_"+comment_id).siblings('.textoverlay')){
        $("#message_activity_comment_edit_"+comment_id).destroyOverlayInstance($("#message_activity_comment_edit_"+comment_id));
    }

	$('#activity_feed_comment_text_'+comment_id).show();
	$('#activity_comment_edit_'+comment_id).remove();
	
	var index = $.inArray(comment_id, activity_comment_edit_array);
	activity_comment_edit_array.splice(index, 1);
}

function confirmEditActivityComment(comment_id)
{
	if ($.trim($('#message_activity_comment_edit_'+comment_id).val()) != '' || $('#activity_comment_attach_id_'+comment_id).val() != '')
	{
        var messageVal;
        if(jQuery("#message_activity_comment_edit_"+comment_id+"_hidden").length != 0){
            messageVal = jQuery("#message_activity_comment_edit_"+comment_id+"_hidden").val();
        }else{
            messageVal = jQuery("#message_activity_comment_edit_"+comment_id).val()
        }
		$.post(mooConfig.url.base + '/activities/ajax_editActivityComment/'+ comment_id,{'comment_attach': $('#activity_comment_attach_id_'+comment_id).val() ,message: messageVal}, function(data){
            //destroy overlay instance;
            if($("#message_activity_comment_edit_"+comment_id).siblings('.textoverlay')){
                $("#message_activity_comment_edit_"+comment_id).destroyOverlayInstance($("#message_activity_comment_edit_"+comment_id));
            }

            $('#activity_feed_comment_text_'+comment_id).html($(data).html());
			$('#history_activity_comment_'+comment_id).show();
			registerImageComment();
			cancelEditActivityComment(comment_id);
	    });
	}
}



function cancelEditItemComment(comment_id, isPhotoComment)
{
    //destroy overlay instance;
    if($('#message_item_comment_edit_'+comment_id).siblings('.textoverlay')){
        $('#message_item_comment_edit_'+comment_id).destroyOverlayInstance($('#message_item_comment_edit_'+comment_id));
    }
    var item_feed_id = '#item_feed_comment_text_';
    if(typeof isPhotoComment !== 'undefined' && isPhotoComment)
        item_feed_id = '#photo_feed_comment_text_';
	$(item_feed_id+comment_id).show();
	$('#item_comment_edit_'+comment_id).remove();
	
	var index = $.inArray(comment_id, item_comment_edit_array);
	item_comment_edit_array.splice(index, 1);
}

function removeItemComment(id,isTheaterMode)
{
    $.fn.SimpleModal({
        btn_ok: mooPhrase.__('btn_ok'),
        btn_cancel: mooPhrase.__('btn_cancel'),
        callback: function(){
            $.post(mooConfig.url.base + '/comments/ajax_remove', {id: id}, function() {
                $('#itemcomment_'+id).fadeOut('normal', function() {
                    $('#itemcomment_'+id).remove();
                    if(!isTheaterMode){
                        $('#comment_count').html( parseInt($('#comment_count').html()) - 1 );
                    }
                    else
                    {
                        $('#photo_comment_'+id).remove();
                    }
                });
            });
        },
        title: mooPhrase.__('please_confirm'),
        contents: mooPhrase.__('confirm_delete_comment'),
        model: 'confirm', hideFooter: false, closeButton: false
    }).showModal();
}

function confirmEditItemComment(comment_id, isPhotoComment)
{
	if ($.trim($('#message_item_comment_edit_'+comment_id).val()) != '' || $('#item_comment_attach_id_'+comment_id).val() != '')
	{
        var messageVal;
        if(jQuery("#message_item_comment_edit_"+comment_id+"_hidden").length != 0){
            messageVal = jQuery("#message_item_comment_edit_"+comment_id+"_hidden").val();
        }else{
            messageVal = jQuery("#message_item_comment_edit_"+comment_id).val()
        }
		$.post(mooConfig.url.base + '/comments/ajax_editComment/'+ comment_id,{'comment_attach': $('#item_comment_attach_id_'+comment_id).val() ,message: messageVal}, function(data){
            //destroy overlay instance;
            if($('#message_item_comment_edit_'+comment_id).siblings('.textoverlay')){
                $('#message_item_comment_edit_'+comment_id).destroyOverlayInstance($('#message_item_comment_edit_'+comment_id));
            }

            $('#item_feed_comment_text_'+comment_id).html($(data).html());
            $('#photo_feed_comment_text_'+comment_id).html($(data).html());
			$('#history_item_comment_' + comment_id).show();
                        $('#history_activity_comment_' + comment_id).show();
			registerImageComment();
			cancelEditItemComment(comment_id, isPhotoComment);
	    });
	}
}



function removePhotoComment(type,id)
{
	$('#'+type+'_comment_attach_id_'+id).val('');
	$('#'+type+'_comment_preview_attach_'+id).html('');
	$('#'+type+'_comment_attach_'+id).show();
}

function createItem( type, jsonView )
{
    disableButton('saveBtn');
    var action  = 'ajax_save';
    if(jsonView)
        action = 'save';
    mooAjax.post({
        url : mooConfig.url.base + "/" + type + "/"+action,
        data: jQuery("#createForm").serialize()
    }, function(data){
        var json = $.parseJSON(data);

        if ( json.result == 1 )
            window.location = mooConfig.url.base + '/' + type + '/view/' + json.id;
        else
        {
            enableButton('saveBtn');
            $(".error-message").show();
            $(".error-message").html(json.message);
            if ($('.spinner').length > 0){
                $('.spinner').remove();
            }
        }
    });
}

function moreResults(url, div, obj)
{
    $(obj).spin('small');
    $(obj).parent().css('display', 'none');
    var postData = {};
    if(typeof(window.searchParams) === 'undefined')
        window.searchParams = '';

    $.post(mooConfig.url.base + url,window.searchParams ,function(data){
        $(obj).spin(false);
        $('#' + div).find('.view-more:first').remove();
        $('#' + div).children('.clear:first').remove();
        $('#' + div).find('.loading:first').remove();
        flagScroll = true;
        if ( div == 'comments' || div == 'theaterComments' ){
            $("#" + div).append(data);
            // move load more to end of comment list
            $('#'+div+' .view-more').insertAfter('#'+div+' li[id^="itemcomment_"]:last');
        }
        else
            $("#" + div).append(data);

        registerOverlay();
        registerImageComment();

        $(".tip").tipsy({ html: true, gravity: 's' });
        
        window.initShareBtn();
        

        MooResponsive.initFeedImage();
        componentHandler.upgradeAllRegistered();
    });
}

function mooAlert(msg)
{
    $.fn.SimpleModal({btn_ok: mooPhrase.__('btn_ok'), title: mooPhrase.__('message'), hideFooter: false, closeButton: false, model: 'alert', contents: msg}).showModal();
}

function mooConfirm( msg, url )
{
    // Set title
    $($('#portlet-config  .modal-header .modal-title')[0]).html(mooPhrase.__('please_confirm'));
    // Set content
    $($('#portlet-config  .modal-body')[0]).html(msg);
    // OK callback
    $('#portlet-config  .modal-footer .ok').click(function(){
        window.location = url;
    });
    $('#portlet-config').modal('show');
}

function mooAjax(sUrl, sType, aData, callback){
    if(sType.toLowerCase() == 'get'){
        $.ajax({
            url: sUrl,
            data: aData,
            success: callback
        });
    }else if(sType.toLowerCase() == 'post'){
        $.ajax({
            type: "POST",
            url: sUrl,
            data: aData,
            success: callback
        });
    }
}

function toggleCheckboxes(obj)
{
    if ( obj.checked )
        jQuery('.check').attr('checked', 'checked');
    else
        jQuery('.check').attr('checked', false);
}

function confirmSubmitForm(msg, form_id)
{
    $.fn.SimpleModal({
        btn_ok: 'OK',
        model: 'confirm',
        callback: function(){
            document.getElementById(form_id).submit();
        },
        title: 'Please Confirm',
        contents: msg,
        hideFooter: false,
        closeButton: false
    }).showModal();
}

function registerCrossIcons()
{

}

function likeIt( type, item_id, thumb_up )
{
    jQuery.post(mooConfig.url.base + '/likes/ajax_add/' + type + '/' + item_id + '/' + thumb_up, { noCache: 1 }, function(data){
        try
        {
            var res = jQuery.parseJSON(data);

            jQuery('#like_count').html( parseInt(res.like_count) );
            jQuery('#dislike_count').html( parseInt(res.dislike_count) );
            jQuery('#like_count2').html( parseInt(res.like_count) );
            jQuery('#dislike_count2').html( parseInt(res.dislike_count) );

            if ( thumb_up )
            {
                jQuery('#like_count').parent().prev().toggleClass('active');
                jQuery('#dislike_count').parent().prev().removeClass('active');
            }
            else
            {
                jQuery('#dislike_count').parent().prev().toggleClass('active');
                jQuery('#like_count').parent().prev().removeClass('active');
            }
        }
        catch (err)
        {
            validateUser();
        }
    });
}

function likePhoto( item_id, thumb_up )
{
    jQuery.post(mooConfig.url.base + '/likes/ajax_add/Photo_Photo/' + item_id + '/' + thumb_up, { noCache: 1 }, function(data){
        try
        {
            var res = jQuery.parseJSON(data);

            jQuery('#photo_like_count2').html( parseInt(res.like_count) );
            jQuery('#photo_dislike_count2').html( parseInt(res.dislike_count) );

            if ( thumb_up )
            {
                jQuery('#photo_like_count').toggleClass('active');
                jQuery('#photo_dislike_count').removeClass('active');
            }
            else
            {
                jQuery('#photo_dislike_count').toggleClass('active');
                jQuery('#photo_like_count').removeClass('active');
            }
        }
        catch (err)
        {
            validateUser();
        }
    });
}

function likeActivity(item_type, id, thumb_up)
{
    var type;
    if(item_type == 'photo_comment')
        type = 'comment';
    else
        type = item_type;
    $.post(mooConfig.url.base + '/likes/ajax_add/' + type + '/' + id + '/' + thumb_up, { noCache: 1 }, function(data){
        try
        {
            var res = $.parseJSON(data);
            $('#' + item_type + '_like_' + id).html( parseInt(res.like_count) );
            $('#' + item_type + '_dislike_' + id).html( parseInt(res.dislike_count) );
            if(item_type == 'comment'){
                $('#photo_comment' + '_like_' + id).html( parseInt(res.like_count) );
                $('#photo_comment' + '_dislike_' + id).html( parseInt(res.dislike_count) );
            }

            if ( thumb_up )
            {
                $('#' + item_type + '_l_' + id).toggleClass('active');
                $('#' + item_type + '_d_' + id).removeClass('active');
                if(item_type == 'comment') {
                    $('#photo_comment' +  '_l_' + id).toggleClass('active');
                    $('#photo_comment' +  '_d_' + id).removeClass('active');
                }
            }
            else
            {
                $('#' + item_type + '_d_' + id).toggleClass('active');
                $('#' + item_type + '_l_' + id).removeClass('active');
                if(item_type == 'comment') {
                    $('#photo_comment' + '_d_' + id).toggleClass('active');
                    $('#photo_comment' + '_l_' + id).removeClass('active');
                }
            }
        }
        catch (err)
        {
            validateUser();
        }
    });
}

function showFeedVideo( source, source_id, activity_id )
{
    $('#video_teaser_' + activity_id + ' .vid_thumb').spin('small');
    $('#video_teaser_' + activity_id).load(mooConfig.url.base + '/videos/embed', { source: source, source_id: source_id }, function(){
        $('#video_teaser_' + activity_id + ' > .vid_thumb').spin(false);
    });
}



function toggleMenu(menu)
{
    if ( menu == 'leftnav' )
    {
        if ( jQuery('#leftnav').css('left') == '-200px' )
        {
            jQuery('#leftnav').animate({left:0}, 300);
            jQuery('#right').animate({right:-204}, 300);
            jQuery('#center').animate({left:200}, 300);
        }
        else
        {
            jQuery('#leftnav').animate({left:-200}, 300);
            jQuery('#center').animate({left:0}, 300);
        }
    }
    else
    {
        if ( jQuery('#right').css('right') == '-204px' )
        {
            jQuery('#right').show();
            jQuery('#right').animate({right:0}, 300);
            jQuery('#leftnav').animate({left:-200}, 300);
            jQuery('#center').animate({left:0}, 300);
        }
        else
        {
            jQuery('#right').animate({right:-204}, 300, function(){
                jQuery('#right').hide();
            });
            //jQuery('#center').animate({left:0}, 300);
        }
    }
}



function showMooDropdown(obj)
{
    jQuery(obj).next().toggle();
}

function doModeration( action, type )
{
    switch ( action )
    {
        case 'delete':
            $('#deleteForm').attr('action', mooConfig.url.base + '/admin/' + type + '/delete');
            confirmSubmitForm('Are you sure you want to delete these ' + type + '?', 'deleteForm');
            break;

        case 'move':
            $('#deleteForm').attr('action', mooConfig.url.base + '/admin/' + type + '/move');
            $('#category_id').show();
            break;

        default:
            $('#category_id').hide();
    }
}

var tmp_class;
function disableButton(button)
{
    tmp_class = $("#" + button + " i").attr("class");
    $("#" + button + " i").attr("class", "icon-refresh icon-spin");
    $("#" + button).addClass('disabled');
}

function enableButton(button)
{
    $("#" + button + " i").attr("class", tmp_class);
    $("#" + button).removeClass('disabled');
}

function initTabs(tab)
{
    jQuery('#' + tab + ' .tabs > li').click(function(){
        jQuery('#' + tab + ' li').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('#' + tab + ' .tab').hide();
        jQuery('#'+jQuery(this).attr('id')+'_content').show();
    });
}

function showMore(obj)
{
    $(obj).prev().css('max-height', 'none');
    $(obj).replaceWith('<a href="javascript:void(0)" onclick="showLess(this)" class="show-more">' + $(obj).prev().data('less-text') + '</a>');
}

function showLess(obj)
{
    $(obj).prev().css('max-height', '');
    $(obj).replaceWith('<a href="javascript:void(0)" onclick="showMore(this)" class="show-more">' + $(obj).prev().data('more-text') + '</a>');
}

function removeNotification(id)
{
    mooAjax(mooConfig.url.base + '/notifications/ajax_remove/'+id, 'get', '', function(data) {
        $("#noti_"+id).slideUp();

        if ( $('#noti_' + id).hasClass('unread') && jQuery("#notification_count").html() != '0' )
        {
            var noti_count = parseInt($(".notification_count").html()) - 1;

            if(noti_count == 0)
            {
                $(".notification_count").remove();
            }
            else
            {
                $(".notification_count").html( noti_count );
            }
            $("#notification_count").html( noti_count );

            Tinycon.setBubble( noti_count );
        }
    });
}

function registerAttachComment(id)
{

	var uploader = new qq.FineUploader({
	    element: $('#comment_button_attach_'+id)[0],
	    text: {
	        uploadButton: '<div class="upload-section"><i class="material-icons">local_see</i></div>'
	    },
	    validation: {
	        allowedExtensions: mooConfig.photoExt,
	        sizeLimit: mooConfig.sizeLimit
	    },
	    multiple: false,
	    request: {
	        endpoint: mooConfig.url.base+"/upload/wall"
	    },
	    callbacks: {
	        onError: mooGlobal.errorHandler,
            onSubmit: function(id_img, fileName){
                var element = $('<span id="attach_'+id+'_'+id_img+'" style="background-image:url('+mooConfig.url.base+'/img/indicator.gif);background-size:inherit;background-repeat:no-repeat"></span>');
                $('#comment_preview_image_'+id).append(element);
                $('#comment_button_attach_'+id).hide(); 
            },
	        onComplete: function(id_img, fileName, response, xhr) {
                    $(this.getItemByFileId(id_img)).remove();
                    
                    img = $('<img src="'+ mooConfig.url.base + '/' +response.photo+'">');
                    img.load(function() {
                    	var element = $('#attach_'+id+'_'+id_img);
                        element.attr('style','background-image:url(' + mooConfig.url.base + '/' + response.photo + ')');
                        var deleteItem = $('<a href="javascript:void(0);"><i class="icon-delete"></i></a>');
                        element.append(deleteItem);
                        element.find('.icon-delete').click(function(){
    	                    element.remove();
    	                    $('#comment_button_attach_'+id).show();
    	                    $('#comment_image_'+id).val('');
    		            });
                    });
                    

                    $('#comment_image_'+id).val(response.photo);	             
	        }
	    }
	});
}

function registerAttachCommentEdit(type,id)
{
	var uploader = new qq.FineUploader({
	    element: $('#'+type+'_comment_attach_'+id)[0],
	    text: {
	        uploadButton: '<div class="upload-section"><i class="material-icons">local_see</i></div>'
	    },
	    validation: {
	        allowedExtensions: mooConfig.photoExt,
	        sizeLimit: mooConfig.sizeLimit
	    },
	    multiple: false,
	    request: {
	        endpoint: mooConfig.url.base+"/upload/wall"
	    },
	    callbacks: {
	        onError: mooGlobal.errorHandler,
	        onSubmit: function(id_img, fileName){
	            var element = $('<span id="attach_'+'_'+id+'_'+id_img+'" style="background-image:url('+mooConfig.url.base+'/img/indicator.gif);background-size:inherit;background-repeat:no-repeat"></span>');
	            $('#'+type+'_comment_preview_attach_'+id).append(element);
	            $('#'+type+'_comment_attach_'+id).hide(); 
	        },
	        onComplete: function(id_img, fileName, response, xhr) {
	        	$(this.getItemByFileId(id_img)).remove()
	        	
	        	img = $('<img src="'+ mooConfig.url.base + '/' +response.photo+'">');
                img.load(function() {
                	var element = $('#attach_'+'_'+id+'_'+id_img);
                	element.attr('style','background-image:url(' + mooConfig.url.base + '/' + response.photo + ')');
                    var deleteItem = $('<a href="javascript:void(0);"><i class="icon-delete"></i></a>');
                    element.append(deleteItem);
                    element.find('.icon-delete').click(function(){
    	            	element.remove();
    	            	$('#'+type+'_comment_attach_'+id).show();
    	            	$('#'+type+'_comment_attach_id_'+id).val('');
    	            });
                })
                
	            $('#'+type+'_comment_attach_id_'+id).val(response.photo);
	            $('#'+type+'_comment_attach_'+id).hide();    
	        }
	    }
	});
}

function registerImageComment()
{
	if ($('.comment_thumb a').length)
	{
	    $('.comment_thumb a').magnificPopup({
	        type:'image',
	        gallery: { enabled: false },
	        image: {
	        	cursor: ''
	        }
	    });
	}
}
var flagScroll = true;



var isMobile = {
    Android: function() {

        return navigator.userAgent.match(/Android/i);

    },
    BlackBerry: function() {

        return navigator.userAgent.match(/BlackBerry/i);

    },
    iOS: function() {

        return navigator.userAgent.match(/iPhone|iPad|iPod/i);

    },
    Opera: function() {

        return navigator.userAgent.match(/Opera Mini/i);

    },
    Windows: function() {

        return navigator.userAgent.match(/IEMobile/i);

    },
    any: function() {
        return(isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());

    }

};
function toggleUploadPhoto(){
    var myMarginTopPhoto = parseInt( $(".stt-action").css("marginTop") );
    var bottomPhotoReview = 0;
    if(myMarginTopPhoto <=30){
        myMarginTopPhoto += 75;
        $('.stt-action').css('margin-top',myMarginTopPhoto);
    }
   
    if(!$('.stt-action > #userTagging-id-userTagging > .userTagging-userTagging').hasClass('hidden')){
        bottomPhotoReview = 70;        
        
    }else{
        bottomPhotoReview = 39;
    }
    
     $('#wall_photo_preview').css('bottom',bottomPhotoReview);
}


window.closeModal = function(){
    $('#shareFeedModal').modal('hide');
};

window.statusModal = function($title, $msg){
    $.fn.SimpleModal({
        btn_ok: mooPhrase.__('btn_ok'),
        model: 'modal',
        title: $title,
        contents: $msg
    }).showModal();
}

window.initShareBtn = function (){
    $('body').off('click','.shareFeedBtn').on('click','.shareFeedBtn',function(){
        var iframeSrc = $(this).attr('share-url');
        //check login user first
        $.post(mooConfig.url.base + '/share/index',function(data){
            try {
                data = JSON.parse(data);
                if(data['nonLogin']){
                    validateUser();
                }
            }catch(e) {
                $('#shareFeedModal iframe').attr("src",iframeSrc);
                $('#shareFeedModal').modal({show:true});
            }

        });


    });
}


$(document).ready(function () {
    $(window).on('beforeunload', function(){
        if(file_uploading == 1)
        {
            return confirmNavigation();
        }
    });
    
    window.initShareBtn();
})

// MOOSOCIAL-2141
$(document).keyup(function(e) {
    if (e.keyCode == 27){

        if(file_uploading == 1)
        {
            return confirmNavigation();
        }
    }
});

function confirmNavigation()
{
    if (file_uploading == 1)
    {
        var msg = mooPhrase.__('are_you_sure_leave_this_page');
        if (confirm(msg))
        {
            file_uploading = 0;
            $(window).unbind('beforeunload');
            $('#themeModal').modal('hide');
            return true;
        }else{
            return;
        }
    }else {
        file_uploading = 0;
        $('#themeModal').modal('hide')
        return;
    }

}
$(document).ready(function () {
    $('.accept-cookie').on('click',function(){
        var answer = $(this).data('answer');
        var $this = $(this);
        $.post(mooConfig.url.base+'/users/accept_cookie',{answer:answer},function(data){
            data = JSON.parse(data);
            if (data.result) {
                $('#cookies-warning').remove();
            }
            else {
                location.href = data.url;
            }
        })
    });
    $('.delete-warning-cookies').on('click',function(){
        $('#cookies-warning').remove();
        $('body').removeClass('page_has_cookies');
    });
});
$(window).load(function(){
    $('#status_box').slideDown("slow");
})