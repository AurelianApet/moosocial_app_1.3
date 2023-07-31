//
//  ViewController.swift
//  mooApp
//
//  Copyright (c) SocialLOFT LLC
//  mooSocial - The Web 2.0 Social Network Software
//  @website: http://www.moosocial.com
//  @author: mooSocial
//  @license: https://moosocial.com/license/
//  code complete

import UIKit

class LoginViewController: AppViewController,UITextFieldDelegate,AppServiceDelegate {
    // Mark : Properties
    @IBOutlet weak var emailTextField: UITextField!
    @IBOutlet weak var passwordTextField: UITextField!
    @IBOutlet weak var loginButton: UIButton!
    
    @IBOutlet weak var emailLabel: UILabel!
    
    @IBOutlet weak var passwordLabel: UILabel!
    
    @IBOutlet weak var loginIndicator: UIActivityIndicatorView!
    
    @IBOutlet weak var forgotButton: UIButton!
    @IBOutlet weak var singupButton: UIButton!
    override func viewDidLoad() {
        super.viewDidLoad()
        // Layout improvement
        self.view.backgroundColor = AppConfigService.sharedInstance.config.color_main_style
        emailLabel.textColor = AppConfigService.sharedInstance.config.color_fields_login_style
        passwordLabel.textColor = AppConfigService.sharedInstance.config.color_fields_login_style
        //loginButton.backgroundColor = AppConfigService.sharedInstance.config.color_login_button_background_style
        // Multi-Language Support
        emailLabel.text = NSLocalizedString("login_page_email_lablel",comment:"login_page_email_lablel")
        emailTextField.placeholder = NSLocalizedString("login_page_email_text_field",comment:"login_page_email_text_field")
        passwordLabel.text = NSLocalizedString("login_page_password_label",comment:"login_page_password_label")
        passwordTextField.placeholder = NSLocalizedString("login_page_password_text_field",comment:"login_page_password_text_field")
        //loginButton.setTitle(NSLocalizedString("login_page_login_button",comment:"login_page_login_button"), for: UIControlState())
        singupButton.setTitle(NSLocalizedString("login_page_singup_button",comment:"login_page_singup_button"), for: UIControlState())
        forgotButton.setTitle(NSLocalizedString("login_page_forgot_button",comment:"login_page_singup_button"), for: UIControlState())
        
        // End Multi-Language Support
        // End Layout improvement
        // Do any additional setup after loading the view, typically from a nib.
        emailTextField.delegate = self
        loginIndicator.stopAnimating()
        //dumpingDataForTesting("local")
    }
    func dumpingDataForTesting(_ type:String="local"){
        if type == "local"{
            emailTextField.text = "root@local.com"
            passwordTextField.text = "1"
        }else if type == "demo"{
            emailTextField.text = "demo1@moosocial.com"
            passwordTextField.text = "123456"
        }
    }


    // MARK: UITextFieldDelegate
    func textFieldShouldReturn(_ textField: UITextField) -> Bool {
        textField.resignFirstResponder();
        return true;
    }
    func textFieldDidEndEditing(_ textField: UITextField) {
        emailTextField.text = textField.text
    }
    //Mark: Actions
    @IBAction func onTouchLoginButton(_ sender: AnyObject) {
        
        if !ValidateService.sharedInstance.isEmail(emailTextField.text!) {
           alert(ValidateService.sharedInstance.getLastMessage())
        }else if !ValidateService.sharedInstance.isPasswd(passwordTextField.text!) {
            alert(ValidateService.sharedInstance.getLastMessage())
        }
        else{
            loginIndicator.startAnimating()
            AuthenticationService.sharedInstance.registerCallback(self).identifyUser(emailTextField.text,passwd:passwordTextField.text)
        }
        
    }
    //Mark: Validation 
    func checkValidemailTextField() {
        // Disable the Save button if the text field is empty.
        //let text = emailTextField.text ?? ""
   
    }
    // Mark : AppServiceDeleage
    func serviceCallack(_ identifier: String, data: AnyObject?) {

        switch identifier {
        case "AuthenticationService.identifyUser.forceLogin.Success":
            onIdentifyUserSuccess()
            loginIndicator.stopAnimating()
            break
        case "AuthenticationService.identifyUser.forceLogin.Failure":
            loginIndicator.stopAnimating()
            break
        case "AuthenticationService.identifyUser.forceRefeshToken.Failure":
            onIdentifyUserFailure()
            loginIndicator.stopAnimating()
            break
        default: break
        }
    }
    func onIdentifyUserSuccess(){
        self.performSegue(withIdentifier: "sequeShowContainer",sender: self)
    }
    func onIdentifyUserFailure(){
        
    }
    override func viewWillAppear(_ animated: Bool) {
        self.navigationController?.setNavigationBarHidden(true, animated: animated)
        super.viewWillAppear(animated)
    }
    override func viewWillDisappear(_ animated: Bool) {
        self.navigationController?.setNavigationBarHidden(false, animated: animated)
        super.viewWillDisappear(animated)
    }
}

class LoginUITextField : UITextField {
    override func textRect(forBounds bounds: CGRect) -> CGRect {
        return bounds.insetBy(dx: 0, dy: 5);
    }
    override func editingRect(forBounds bounds: CGRect) -> CGRect {
        return bounds.insetBy(dx: 0, dy: 5);
    }

    override func didAddSubview(_ subview: UIView) {
        let border = CALayer()
        let width = CGFloat(2.0)
        self.textColor = AppConfigService.sharedInstance.config.color_fields_login_style
        border.borderColor = AppConfigService.sharedInstance.config.color_fields_login_style.cgColor
        border.frame = CGRect(x: 0, y: self.frame.size.height - width, width:  self.frame.size.width, height: self.frame.size.height)
        
        border.borderWidth = width
        self.layer.addSublayer(border)
        self.layer.masksToBounds = true
        tintColor = AppConfigService.sharedInstance.config.color_fields_login_style
        
        self.attributedPlaceholder = NSAttributedString(string:self.placeholder!, attributes: [NSForegroundColorAttributeName: AppConfigService.sharedInstance.config.color_main_style])
    }
}
