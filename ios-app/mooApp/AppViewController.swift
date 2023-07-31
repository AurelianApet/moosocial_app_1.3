//
//  AppViewController.swift
//  mooApp
//
//  Copyright (c) SocialLOFT LLC
//  mooSocial - The Web 2.0 Social Network Software
//  @website: http://www.moosocial.com
//  @author: mooSocial
//  @license: https://moosocial.com/license/
//

import UIKit

class AppViewController: UIViewController{
    
    override func viewDidLoad() {
        super.viewDidLoad()
         _ = AlertService.sharedInstance.setViewController(self)
    }
    func alert(_ message:String){
        AlertService.sharedInstance.process(message)
    }
    // Hacking for homtabar tintColor
    
    override var preferredStatusBarStyle : UIStatusBarStyle {
        return UIStatusBarStyle.lightContent
    }

}
