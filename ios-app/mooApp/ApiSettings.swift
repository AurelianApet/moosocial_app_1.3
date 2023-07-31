//
//  File.swift
//  mooApp
//
//  Copyright (c) SocialLOFT LLC
//  mooSocial - The Web 2.0 Social Network Software
//  @website: http://www.moosocial.com
//  @author: mooSocial
//  @license: https://moosocial.com/license/
//
import Foundation
public struct ApiSettings {
    var  BASE_URL:String? = "http://localhost/moolab/2.2.2"
    /*  Api url for getting token and refeshing token     */
     var URL_AUTH_TOKEN:String?
    /* Api url for getting user detail */
     var URL_USER_ME:String?
     var URL_SEARCH:String?
     var URL_GET_NOTIFICATION_COUNT:String?
     var URL_LIST_NOTIFICATION:String?
     var URL_GCM:String?
     var URL_SIGNUP:String?
    var URL_FORGOT_PASSWORD:String?
    var URL_POST_MARK_READ_UNREAD_MESSAGE:String?
    var  API_URL = Dictionary<String,String>()
    init(url:String?=String()){
        if url != String(){
            BASE_URL = url
        }
        BASE_URL = BASE_URL!+"/api/"
        //URL_AUTH_TOKEN = BASE_URL! + "auth/token"
        //URL_USER_ME = BASE_URL! + "user/me"
        //URL_SEARCH = BASE_URL! + "search"
        //URL_LIST_NOTIFICATION = BASE_URL! + "notification/me/show"
        //URL_GET_NOTIFICATION_COUNT = BASE_URL! + "notification/me"
        //URL_GCM = BASE_URL! + "user/me/gcm/token"
        //URL_SIGNUP = BASE_URL! + "user/register"
        API_URL = [
            "BASE_URL":BASE_URL!,
            "URL_AUTH_TOKEN":BASE_URL! + "auth/token",
            "URL_USER_ME":BASE_URL! + "user/me",
            "URL_USER_ME_AVATAR":BASE_URL! + "user/me/avatar",
            "URL_SEARCH":BASE_URL! + "search",
            "URL_LIST_NOTIFICATION":BASE_URL! + "notification/me/show",
            "URL_GET_NOTIFICATION_COUNT":BASE_URL! + "notification/me",
            "URL_GET_NOTIFICATION_CLEAR":BASE_URL! + "notification/me/clear",
            "URL_POST_DELETE_NOTIFICATION":BASE_URL! + "notification/me/delete",
            "URL_POST_MARK_READ_UNREAD_NOTIFICATION":BASE_URL! + "notification/:id",
            "URL_GCM":BASE_URL! + "user/me/gcm/token",
            "URL_GCM_DELETE":BASE_URL! + "user/me/gcm/token/delete",
            "URL_SIGNUP":BASE_URL! + "user/register",
            "URL_LIST_MESSEAGE":BASE_URL! + "message/me/show",
            "URL_POST_MARK_READ_UNREAD_MESSAGE":BASE_URL! + "message/:id",
            "URL_FORGOT_PASSWORD":BASE_URL! + "user/forgot",
        ]

    }
    subscript(key:String)->String{
        return API_URL[key]!
    
    }
    subscript(key:String,action:String)->String{
        if action == "hasToken"{
            return API_URL[key]! + "?access_token=" + (SharedPreferencesService.sharedInstance.token?.access_token)! as String
        }
        return API_URL[key]! as String
    }
}
