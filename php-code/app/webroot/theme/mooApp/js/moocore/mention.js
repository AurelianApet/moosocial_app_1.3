/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'overlay', 'textcomplete', 'bloodhound'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooMention = factory();
    }
}(this, function ($) {
    var init = function(id,type){

    };
    
    var resetMention = function(obj){
    };

    var reConfigOverlay = function(obj,reRender){

    };
    //    exposed public methods
    return {
        init:init,
        resetMention: resetMention,
        reConfigOverlay: reConfigOverlay
    }
}));