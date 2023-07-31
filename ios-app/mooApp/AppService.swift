//
//  AppService.swift
//  mooApp
//
//  Copyright (c) SocialLOFT LLC
//  mooSocial - The Web 2.0 Social Network Software
//  @website: http://www.moosocial.com
//  @author: mooSocial
//  @license: https://moosocial.com/license/
//

import Foundation
import Alamofire

protocol AppServiceDelegate {
    func serviceCallack(_ identifier:String,data:AnyObject?)
}

open class AppService : NSObject {
    var serviceDelegate : AppServiceDelegate?
    let api = AppConfigService.sharedInstance.apiSetting
    func dispatch(_ identifier:String,data:AnyObject? = nil){
        
        if serviceDelegate != nil{
            
            serviceDelegate?.serviceCallack(identifier, data: data)
        }
    }
    func registerCallback(_ delegate:AppServiceDelegate)-> Self {
        self.serviceDelegate = delegate
      
        return self
    }
    func unRegisterCallack(){
        self.serviceDelegate = nil
        
    }
}
