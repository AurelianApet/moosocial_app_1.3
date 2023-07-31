//
//  UserModel.swift
//  mooApp
//
//  Copyright (c) SocialLOFT LLC
//  mooSocial - The Web 2.0 Social Network Software
//  @website: http://www.moosocial.com
//  @author: mooSocial
//  @license: https://moosocial.com/license/
//

import Foundation


struct UserModel{
    let id:Int
    let name:String?
    let email:String?
    let avatar:AnyObject?
    //let photo:String?
    let last_login:String?
    let photo_count:Int?
    let friend_count:Int?
    let notification_count:Int?
    let friend_request_count:Int?
    let blog_count:Int?
    let topic_count:Int?
    let conversation_user_count:Int?
    let gender:String?
    let birthday:String?
    let timezone:String?
    let about:String?
    let lang:String?
    let menus:AnyObject?
    let cover:String?
    let profile_url:String?
}

extension UserModel{
    init?(json:[String:Any]){
        guard let id = Int((json["id"] as? String)!),
            let name = json["name"] as? String,
            let email = json["email"] as? String,
        let profile_url = json["profile_url"] as? String
            else{
                return nil
        }
        let avatar = json["avatar"] as AnyObject
        let menus = json["menus"] as AnyObject
        let gender = json["gender"] as? String
        let birthday = json["birthday"] as? String
        let last_login = json["last_login"] as? String
        let photo_count = Int((json["photo_count"] as? String)!)
        let friend_count = Int((json["friend_count"] as? String)!)
        let notification_count = Int((json["notification_count"] as? String)!)
        let friend_request_count = Int((json["friend_request_count"] as? String)!)
        let blog_count = Int((json["blog_count"] as? String)!)
        let topic_count = Int((json["topic_count"] as? String)!)
        let conversation_user_count = Int((json["conversation_user_count"] as? String)!)
        let timezone = json["timezone"] as? String
        let about = json["about"] as? String
        let lang = json["lang"] as? String
        let cover = json["cover"] as? String
        
        self.id = id
        self.name = name
        self.email = email
        self.avatar = avatar
        //self.photo = photo
        self.last_login = last_login
        self.photo_count = photo_count
        self.friend_count = friend_count
        self.notification_count = notification_count
        self.friend_request_count = friend_request_count
        self.blog_count = blog_count
        self.topic_count = topic_count
        self.conversation_user_count = conversation_user_count
        self.gender = gender
        self.birthday = birthday
        self.timezone = timezone
        self.about = about
        self.lang = lang
        self.menus = menus
        self.cover = cover
        self.profile_url = profile_url
    }
}
