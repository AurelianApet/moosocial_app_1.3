/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooAjax = factory(root.jQuery);
    }
}(this, function ($) {
    
    var post = function (options, callback) {
        $.ajax({
            type: 'post',
            url: options.url,
            data: options.data,
            success: function(result){
                callback(result);
                componentHandler.upgradeAllRegistered();
            }
        });
    };

    var get = function (options, callback) {
        $.ajax({
            type: 'get',
            url: options.url,
            data: options.data,
            success: function(result){
                callback(result);
                componentHandler.upgradeAllRegistered();
            }
        });
    };
    
    return {
        post : post,
        get : get
    }
}));
