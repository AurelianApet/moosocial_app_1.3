//
//  LoginService.swift
//  mooApp
//
//  Copyright (c) SocialLOFT LLC
//  mooSocial - The Web 2.0 Social Network Software
//  @website: http://www.moosocial.com
//  @author: mooSocial
//  @license: https://moosocial.com/license/
//

import UIKit
import Alamofire



open class AuthenticationService : AppService {
    // Mark : Propeties
    var presentViewController : AppViewController?
    
    // Mark : setter and getter
    func setViewController(_ viewController:AppViewController)->AuthenticationService{
        presentViewController = viewController
        return self
    }
    
    
    // Mark: init
    override init() {
        super.init()
    }
    // Mark: Singleton
    class var sharedInstance : AuthenticationService {
        struct Singleton {
            static let instance = AuthenticationService()
        }
        
        
        // Return singleton instance
        return Singleton.instance
    }
    
    // Mark: process
    func doLogout()->(){
        UIApplication.shared.applicationIconBadgeNumber = 0
        AppConfigService.sharedInstance.beforeLogout()
        SharedPreferencesService.sharedInstance.clearToken()
    }
    func doLogin()->(){}
    func doAfterLogin(){
        AppConfigService.sharedInstance.bootAfterLogin()
    }
    func doAfterRefeshingToken(){
        doAfterLogin()
    }
    func identifyUser( _ username : String? = String() , passwd : String? = String() ,  forceLogin : Bool = true ,  forceRefeshToken : Bool = false)->(){
        let api = AppConfigService.sharedInstance.apiSetting
        
        if forceLogin && !forceRefeshToken {
            // First login for getting token
            
            let parameters:Parameters = [
                "username":username!,
                "password":passwd!
            ]
            AlamofireService.sharedInstance.privateSession!.request( api!["URL_AUTH_TOKEN"],method:.post,parameters: parameters,encoding:JSONEncoding.default)
                .validate()
                .responseJSON { response in
                    switch response.result {
                    case .success(let JSON):
                        
                        let token = TokenModel(json:JSON as! [String : Any])
                        
                        SharedPreferencesService.sharedInstance.saveToken(token)
                     
                        self.doAfterLogin()
                        
                        self.dispatch("AuthenticationService.identifyUser.forceLogin.Success")
                    case .failure(let error):
                        self.dispatch("AuthenticationService.identifyUser.forceLogin.Failure")
                        if let data = response.data {
                            if let JSONDictionary = JsonService.sharedInstance.convertToNSDictionary(data){
                                AlertService.sharedInstance.process(JSONDictionary.value(forKey: "message") as! String)
                                print("\(error)")
                            }
                            print("Response data: \(NSString(data: data, encoding: String.Encoding.utf8.rawValue)!)")
                            
                        }
                        
                    }
            }
          
        } else if forceRefeshToken {
            // refesh token
            
            let parameters:Parameters = [
                "grant_type":"refresh_token",
                "refresh_token":SharedPreferencesService.sharedInstance.token!.refresh_token! as String
            ]
            AlamofireService.sharedInstance.privateSession!.request(api!["URL_AUTH_TOKEN"],method:.post,parameters: parameters,encoding:JSONEncoding.default)
                .validate()
                .responseJSON { response in
                    switch response.result {
                    case .success(let JSON):
                        
                        
                        let token = TokenModel(json:JSON as! [String : Any])
                        //token.load(nil,dictionary:JSON as? NSDictionary)
                        SharedPreferencesService.sharedInstance.saveToken(token)
                        self.doAfterRefeshingToken()
                        self.dispatch("AuthenticationService.identifyUser.forceRefeshToken.Success")
                    case .failure(let error):
                        
                        if let data = response.data {
                            self.dispatch("AuthenticationService.identifyUser.forceRefeshToken.Failure")
                            if let JSONDictionary = JsonService.sharedInstance.convertToNSDictionary(data){
                                AlertService.sharedInstance.process(JSONDictionary.value(forKey: "message") as! String)
                                print("\(error)")
                            }
                            print("Response data: \(NSString(data: data, encoding: String.Encoding.utf8.rawValue)!)")
                            
                        }
                        
                    }
            }
        }else{
            //
        }
    }
    
}

