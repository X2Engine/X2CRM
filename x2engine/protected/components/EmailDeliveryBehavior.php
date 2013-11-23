<?php

/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */

/**
 * Email delivery methods.
 *
 * @package X2CRM.components
 * @property Credentials $credentials (read-only) The SMTP account to use for
 *  delivery, if applicable.
 * @property array $from The sender of the email.
 * @property PHPMailer $mailer PHPMailer instance
 * @property Profile $userProfile Profile, i.e. for email sender and signature
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailDeliveryBehavior extends CBehavior {

    /**
     * Stores the email credentials, if an account has been defined and is used.
     * @var mixed
     */
    private $_credentials;

    /**
     * @var array Sender address
     */
    private $_from;

    /**
     * Stores an instance of PHPMailer
     * @var PHPMailer
     */
    private $_mailer;

    /**
     * Stores value of {@link userProfile}
     * @var Profile
     */
    private $_userProfile;
    
    /**
     * ID of the credentials record to use for SMTP authentication
     * @var integer
     */
    public $credId = null;
    
    /**
     * @var array Status codes
     */
    public $status = array();
    /**
     * Adds email addresses to a PHPMail object
     * @param type $phpMail
     * @param type $addresses
     */
    public function addEmailAddresses(&$phpMail, $addresses){

        if(isset($addresses['to'])){
            foreach($addresses['to'] as $target){
                if(count($target) == 2)
                    $phpMail->AddAddress($target[1], $target[0]);
            }
        } else{
            if(count($addresses) == 2 && !is_array($addresses[0])){ // this is just an array of [name, address],
                $phpMail->AddAddress($addresses[1], $addresses[0]); // not an array of arrays
            }else{
                foreach($addresses as $target){  //this is an array of [name, address] subarrays
                    if(count($target) == 2)
                        $phpMail->AddAddress($target[1], $target[0]);
                }
            }
        }
        if(isset($addresses['cc'])){
            foreach($addresses['cc'] as $target){
                if(count($target) == 2)
                    $phpMail->AddCC($target[1], $target[0]);
            }
        }
        if(isset($addresses['bcc'])){
            foreach($addresses['bcc'] as $target){
                if(count($target) == 2)
                    $phpMail->AddBCC($target[1], $target[0]);
            }
        }
    }

    /**
     * Perform the email delivery with PHPMailer.
     *
     * Any special authentication and security should take place in here.
     *
     * @throws Exception
     * @return array
     */
    public function deliverEmail($addresses, $subject, $message, $attachments = array()){
        $phpMail = $this->mailer;

        try{

            $this->addEmailAddresses($phpMail, $addresses);

            $phpMail->Subject = $subject;
            // $phpMail->AltBody = $message;
            $phpMail->MsgHTML($message);
            // $phpMail->Body = $message;
            // add attachments, if any
            if($attachments){
                foreach($attachments as $attachment){
                    if($attachment['temp']){ // stored as a temp file?
                        $file = 'uploads/media/temp/'.$attachment['folder'].'/'.$attachment['filename'];
                        if(file_exists($file)) // check file exists
                            if(filesize($file) <= (10 * 1024 * 1024)) // 10mb file size limit
                                $phpMail->AddAttachment($file);
                            else
                                throw new Exception("Attachment '{$attachment['filename']}' exceeds size limit of 10mb.");
                    } else{ // stored in media library
                        $file = 'uploads/media/'.$attachment['folder'].'/'.$attachment['filename'];
                        if(file_exists($file)) // check file exists
                            if(filesize($file) <= (10 * 1024 * 1024)) // 10mb file size limit
                                $phpMail->AddAttachment($file);
                            else
                                throw new Exception("Attachment '{$attachment['filename']}' exceeds size limit of 10mb.");
                    }
                }
            }

            $phpMail->Send();

            // delete temp attachment files, if they exist
            if($attachments){
                foreach($attachments as $attachment){
                    if($attachment['temp']){
                        $file = 'uploads/media/temp/'.$attachment['folder'].'/'.$attachment['filename'];
                        $folder = 'uploads/media/temp/'.$attachment['folder'];
                        if(file_exists($file))
                            unlink($file); // delete temp file
                        if(file_exists($folder))
                            rmdir($folder); // delete temp folder
                        TempFile::model()->deleteByPk($attachment['id']);
                    }
                }
            }

            $this->status['code'] = '200';
            $this->status['message'] = Yii::t('app', 'Email Sent!');
        }catch(phpmailerException $e){
            $this->status['code'] = '500';
            $this->status['message'] = $e->getMessage()." ".$e->getFile()." L".$e->getLine(); //Pretty error messages from PHPMailer
        }catch(Exception $e){
            $this->status['code'] = '500';
            $this->status['message'] = $e->getMessage()." ".$e->getFile()." L".$e->getLine(); //Boring error messages from anything else!
        }
        return $this->status;
    }

    /**
     * Getter for {@link credentials}
     * returns Credentials
     */
    public function getCredentials(){
        if(!isset($this->_credentials)){
            if($this->credId == Credentials::LEGACY_ID)
                $this->_credentials = false;
            else{
                $cred = Credentials::model()->findByPk($this->credId);
                $this->_credentials = empty($cred) ? false : $cred;
            }
        }
        return $this->_credentials;
    }

    public function getFrom(){
        if(!isset($this->_from)) {
			if($this->credentials)
				$this->_from = array(
					'name' => $this->credentials->auth->senderName,
					'address' => $this->credentials->auth->email
				);
			else
				$this->_from = array(
					'name' => $this->userProfile->fullName,
					'address' => $this->userProfile->emailAddress
				);
		}
        return $this->_from;
    }

    /**
     * Magic getter for {@link phpMailer}
     * @return \PHPMailer
     */
    public function getMailer(){
        if(!isset($this->_mailer)){
            require_once(realpath(Yii::app()->basePath.'/components/phpMailer/class.phpmailer.php'));

            $phpMail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
            $phpMail->CharSet = 'utf-8';

            $cred = $this->credentials;
            if($cred){ // Use an individual user email account if specified and valid
                $phpMail->IsSMTP();
                $phpMail->Host = $cred->auth->server;
                $phpMail->Port = $cred->auth->port;
                $phpMail->SMTPSecure = $cred->auth->security;
                if(!empty($cred->auth->password)){
                    $phpMail->SMTPAuth = true;
                    $cred->auth->emailUser('user');
                    $phpMail->Username = $cred->auth->user;
                    $phpMail->Password = $cred->auth->password;
                }
                // Use the specified credentials (which should have the sender name):
                $phpMail->AddReplyTo($cred->auth->email, $cred->auth->senderName);
                $phpMail->SetFrom($cred->auth->email, $cred->auth->senderName);
                $this->from = array('address' => $cred->auth->email, 'name' => $cred->auth->senderName);
            }else{ // Use the system default (legacy method)
                switch(Yii::app()->params->admin->emailType){
                    case 'sendmail':
                        $phpMail->IsSendmail();
                        break;
                    case 'qmail':
                        $phpMail->IsQmail();
                        break;
                    case 'smtp':
                        $phpMail->IsSMTP();

                        $phpMail->Host = Yii::app()->params->admin->emailHost;
                        $phpMail->Port = Yii::app()->params->admin->emailPort;
                        $phpMail->SMTPSecure = Yii::app()->params->admin->emailSecurity;
                        if(Yii::app()->params->admin->emailUseAuth == 'admin'){
                            $phpMail->SMTPAuth = true;
                            $phpMail->Username = Yii::app()->params->admin->emailUser;
                            $phpMail->Password = Yii::app()->params->admin->emailPass;
                        }


                        break;
                    case 'mail':
                    default:
                        $phpMail->IsMail();
                }
                // Use sender specified in attributes/system (legacy method):
                $from = $this->from;
                if($from == null){ // if no from address (or not formatted properly)
                    if(empty($this->userProfile->emailAddress))
                        throw new Exception('Your profile doesn\'t have a valid email address.');

                    $phpMail->AddReplyTo($this->userProfile->emailAddress, $this->userProfile->fullName);
                    $phpMail->SetFrom($this->userProfile->emailAddress, $this->userProfile->fullName);
                } else{
                    $phpMail->AddReplyTo($from['address'], $from['name']);
                    $phpMail->SetFrom($from['address'], $from['name']);
                }
            }

            $this->_mailer = $phpMail;
        }
        return $this->_mailer;
    }

    /**
     * Magic getter for {@link userProfile}
     * @return Profile
     */
    public function getUserProfile(){
        if(!isset($this->_userProfile)){
            if(empty($this->_userProfile)){
                if(Yii::app()->params->noSession){
                    // As a last resort: use admin
                    $this->_userProfile = Profile::model()->findByPk(1);
                }else{
                    // By default: if no profile was defined, and it's in a web
                    // session, use the current user's profile.
                    $this->_userProfile = Yii::app()->params->profile;
                }
            }
        }
        return $this->_userProfile;
    }

    public function setFrom($from){
        $this->_from = $from;
    }

    /**
     * Magic setter for {@link userProfile}
     * @param Profile $profile
     */
    public function setUserProfile(Profile $profile){
        $this->_userProfile = $profile;
    }

}

?>