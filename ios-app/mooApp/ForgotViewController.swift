//
//  ForgotViewController.swift
//  mooApp
//
//  Created by Thanh Lam on 2/23/17.
//  Copyright © 2017 moosocialloft. All rights reserved.
//
import Foundation
import UIKit

class ForgotViewController: AppViewController,UITextFieldDelegate,AppServiceDelegate {
    
    @IBOutlet weak var lbForgotPassword: UILabel!
    
    @IBOutlet weak var lbDescriptionForgotPassword: UILabel!
    
    @IBOutlet weak var tfEmail: UITextField!
    
    @IBOutlet weak var btSubmit: UIButton!
    
    @IBOutlet weak var forgotIndicator: UIActivityIndicatorView!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // self.view.backgroundColor = AppConfigService.sharedInstance.config.color_main_style
        lbForgotPassword.text = NSLocalizedString("login_page_forgot_password",comment:"login_page_forgot_password")
        lbForgotPassword.font = UIFont.boldSystemFont(ofSize: 20)
        lbDescriptionForgotPassword.text = NSLocalizedString("forgot_password_page_desciption",comment:"forgot_password_page_desciption")
        lbDescriptionForgotPassword.font = UIFont.systemFont(ofSize: 12)
        btSubmit.backgroundColor = AppConfigService.sharedInstance.config.color_login_button_background_style
        btSubmit.layer.cornerRadius = 5
        forgotIndicator.stopAnimating()
        forgotIndicator.isHidden = true
    }
    
    override func viewDidLayoutSubviews() {
        self.navigationController?.navigationBar.barTintColor = AppConfigService.sharedInstance.config.navigationBar_barTintColor
        self.navigationController?.navigationBar.isTranslucent = true
        self.navigationController?.navigationBar.tintColor = AppConfigService.sharedInstance.config.navigationBar_textTintColor
        
    }
    
    @IBAction func submitForgotPassword(_ sender: Any) {
        if !ValidateService.sharedInstance.isEmail(tfEmail.text!) {
            alert(ValidateService.sharedInstance.getLastMessage())
        }
        else{
            forgotIndicator.isHidden = false
            forgotIndicator.startAnimating()
            UserService.sharedInstance.registerCallback(self).forgotPassword(tfEmail.text!)
        }
    }
    
    
    func serviceCallack(_ identifier: String, data: AnyObject?) {
        
        switch identifier {
        case "UserService.forgot.Success":
            forgotIndicator.stopAnimating()
            forgotIndicator.isHidden = true
            tfEmail.isHidden = true
            btSubmit.isHidden = true
            lbDescriptionForgotPassword.text = NSLocalizedString("forgot_password_page_success",comment:"forgot_password_page_success")
            break
        case "UserService.forgot.Failure":
            forgotIndicator.stopAnimating()
            forgotIndicator.isHidden = true
            break
        default: break
        }
    }
}
