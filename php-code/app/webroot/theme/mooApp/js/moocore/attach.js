/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'mooFileUploader', 'mooGlobal'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooAttach = factory();
    }
}(this, function ($, mooFileUploader, mooGlobal) {
    
    // app/View/Activities/ajax_share.ctp
    // app/View/Elements/activities.ctp
    // app/View/Elements/comment_form.ctp
    var registerAttachComment = function(id, type){
            if(typeof type == "undefined")
            {
                type = '';
            }
            else
            {
                type = '#' + type + ' ';
            }
            var uploader = new mooFileUploader.fineUploader({
                element: $(type + '#comment_button_attach_'+id)[0],
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
                        $(type + '#comment_preview_image_'+id).append(element);
                        $(type + '#comment_button_attach_'+id).hide();
                    },
                    onComplete: function(id_img, fileName, response, xhr) {
                        $(this.getItemByFileId(id_img)).remove();
                        img = $('<img src="'+ mooConfig.url.base + '/' +response.photo+'">');
                        img.load(function() {
                            var element = $('#attach_'+id+'_'+id_img);
                            element.attr('style','background-image:url(' + mooConfig.url.base + '/' + response.photo + ')');
                            var deleteItem = $('<a href="javascript:void(0);"><i class="icon-delete"></i></a>');
                            element.append(deleteItem);
                            
                            element.find('.icon-delete').unbind('click');
                            element.find('.icon-delete').click(function(){
                                element.remove();
                                $(type + '#comment_button_attach_'+id).show();
                                $(type + '#comment_image_'+id).val('');
                                });
                        });


                        $(type + '#comment_image_'+id).val(response.photo);
                    }
                }
            });
    };
    
    var registerAttachCommentEdit = function(type,id){
            var uploader = new mooFileUploader.fineUploader({
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
                        
                        element.find('.icon-delete').unbind('click');
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
    };
    
    return {
        registerAttachComment : registerAttachComment,
        registerAttachCommentEdit : registerAttachCommentEdit
    }   
}));

