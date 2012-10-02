<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

/**
 * @package X2CRM.modules.mobile.controllers
 */
class SiteController extends MobileController {

//    public function init() {
//        parent::init();
//        $this->layout = 'mobile1';
//    }


	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions' => array('chat', 'logout', 'home', 'getMessages', 'newMessage','contact','home2','more','online', 'people', 'profile'),
				'users' => array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions' => array('index', 'login', 'captcha'),
				'users' => array('*'),
			),
			array('deny', // deny all users
				'users' => array('*'),
			),
		);
	}
	
	public function actions() {
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
				'testLimit'=>1,
			),
		);
	}

	public function actionChat() {

		$this->dataUrl = $this->createUrl('site/chat/');
		$this->pageId = 'site-chat';
		$this->render('chat');
	}

	public function actionNewMessage() {
		$time=time();
		if (isset($_POST['message']) && $_POST['message'] != '') {
			$user = Yii::app()->user->getName();
			$chat = new Social;
			$chat->data = $_POST['message'];
			$chat->timestamp = $time;
			$chat->user = $user;
			$chat->type = 'chat';

			if ($chat->save()) {
				echo '1';
			}
		}
	}

	public function actionGetMessages() {
		$time=time();
		$sinceMidnight=(3600*date("H"))+(60*date("i"))+date("s");
		$latest = '';
		if (isSet($_GET['latest']))
			$latest = $_GET['latest'];
		$retrys = 20;
		$content = array();
		$records = array();
		while (true) {
			$str = '';
			$chatLog = new CActiveDataProvider('Social', array(
						'criteria' => array(
							'order' => 'timestamp DESC',
							'condition' => 'type="chat" AND timestamp > '. (($latest != '') ? (''.$latest) : ''.($time-$sinceMidnight))
						),
						'pagination' => array(),
					));
			$records = $chatLog->getData();
			if (sizeof($records) > 0) {
				foreach ($records as $chat) {
					if ($latest != '' && $chat->timestamp < $latest)
						continue;
					$user = User::model()->findByAttributes(array('username' => $chat->user));
					if ($user != null)
						$content[] = array('username' => $chat->user,
							'userid' => $user->id,
							'message' => $chat->data,
							'timestamp' => $chat->timestamp,
							'when' => date('g:i:s A',$chat->timestamp));
				}
				if (sizeof($content) > 0) {
					$str = json_encode($content);
					echo $str;
					break;
				}
			}
			if (--$retrys > 0) {
				sleep(1);
			} else {
				echo $str;
				break;
			}
		}
	}
	
	public function actionOnline(){
		x2base::cleanUpSessions();
		$sessions = Session::model()->findAll();
		$usernames = array();
		$users = array();
		foreach($sessions as $session) {
			$usernames[] = $session->user;
		}
		foreach($usernames as $username){
			$user = User::model()->findByAttributes(array('username'=>$username));
			$users[] = $user->firstName." ".$user->lastName;
		}
		
		$this->render('online',array(
			'users'=>$users,
		));
	}
	

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        $user = Yii::app()->user;
        if ($user == null || $user->isGuest)
//            $this->render('index');
            $this->redirect($this->createUrl('site/login/'));
        else
            $this->redirect($this->createUrl('site/home/'));
    }
    
    public function actionMore(){
        $user = Yii::app()->user;
        if ($user == null || $user->isGuest)
//            $this->render('index');
            $this->redirect($this->createUrl('site/login/'));
        else
            $this->redirect($this->createUrl('site/home2/'));
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the contact page
     */
    public function actionContact() {

        $model = new ContactForm;
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                $headers = "From: {$model->email}\r\nReply-To: {$model->email}";
                mail(Yii::app()->params['adminEmail'], $model->subject, $model->body, $headers);
                Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }
    
    function getRealIp() {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			return $_SERVER['REMOTE_ADDR'];
	}

    /**
     * Displays the login page
     */
    public function actionLogin() {

        $this->dataUrl = $this->createUrl('site/login/');
        $this->pageId = 'site-login';
        $model = new LoginForm;
        $model->useCaptcha = false;

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

          // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
 			$activeCheck=User::model()->findByAttributes(array('username'=>$model->username));
                        if(isset($activeCheck)){
                            $model->username=$activeCheck->username;
                        }
			if(isset($activeCheck) && $activeCheck->status=='1')
				$activeCheck=true;
			else
				$activeCheck=false;

			$ip = $this->getRealIp();

			$session = CActiveRecord::model('Session')->findByAttributes(array('user'=>$model->username,'IP'=>$ip));
			x2base::cleanUpSessions();
			if(isset($session)) {
				var_dump($session->status);
				$session->lastUpdated = time();

				if($session->status < 1) {
					if($session->status > -3)
						$session->status -= 1;
				} else {
					$session->status = -1;
				}
				if($session->status < -1)
					$model->useCaptcha = true;
				if($session->status < -2)
					$model->setScenario('loginWithCaptcha');
			} else { 
				$session = new Session;
				$session->user = $model->username;
				$session->lastUpdated = time();
				$session->status = 1;
				$session->IP = $ip;
			}

            // validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()) {
				$user = User::model()->findByPk(Yii::app()->user->getId());
				$user->login = time();
				$user->save();
				if($user->username=='admin'){
					if(ini_get('allow_url_fopen') == 1) {
						$context = stream_context_create(array(
							'http' => array('timeout' => 2)		// set request timeout in seconds
						));
						$updateSources = array(
							'http://x2planet.com/updates/versionCheck.php',
							'http://x2base.com/updates/versionCheck.php'
						);
						$newVersion = '';
						
						foreach($updateSources as $url) {
							$sourceVersion = @file_get_contents($url,0,$context);
							if($sourceVersion !== false) {
								$newVersion = $sourceVersion;
								break;
							}
						}
						if(empty($newVersion))
							$newVersion = Yii::app()->params->version;
						/* 
						// check X2Planet for updates
						$x2planetVersion = @file_get_contents('http://x2planet.com/updates/versionCheck.php',0,$context);
						if($x2planetVersion !== false)
							$newVersion = $x2planetVersion;
						else {
							// try X2Base if that didn't work
							$x2baseVersion = @file_get_contents('http://x2base.com/updates/versionCheck.php',0,$context);
							if($x2baseVersion !== false)
								$newVersion=$x2baseVersion;
							else
								$newVersion=Yii::app()->params->version;
						} */
						$unique_id = Yii::app()->db->createCommand("SELECT `unique_id` FROM `x2_admin`")->queryScalar();
						if(version_compare($newVersion,Yii::app()->params->version) > 0 && $unique_id !='none' && $unique_id != Null) {	// if the latest version is newer than our version
							Yii::app()->session['versionCheck']=false;
							Yii::app()->session['newVersion']=$newVersion;
						}
						else
							Yii::app()->session['versionCheck']=true;
					}
					else
						Yii::app()->session['versionCheck']=true;
				} else
					Yii::app()->session['versionCheck']=true;
					
				Yii::app()->session['loginTime']=time();
                                $session->status=1;
				$session->save();

				$cookie = new CHttpCookie('x2mobilebrowser', 'true'); // create cookie
				$cookie->expire = time() + 31104000; // expires in 1 year
				Yii::app()->request->cookies['x2mobilebrowser'] = $cookie; // save cookie
				$this->redirect($this->createUrl('site/home/'));
			} else if($activeCheck) {
				$session->save();
				$model->verifyCode = '';
			}
        }
        // display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * Displays the home page
     */
    public function actionHome() {
        // display the home page
        $this->dataUrl = $this->createUrl('site/home/');
        $this->pageId = 'site-home';
        $this->render('home', array());
    }
    
    public function actionHome2() {
        // display the home page
        $this->dataUrl = $this->createUrl('site/home2/');
        $this->pageId = 'site-home2';
        $this->render('home2', array());
    }
    
    public function actionPeople() {
        // display the home page
        $this->dataUrl = $this->createUrl('site/people/');
        $this->pageId = 'site-people';
        
        $users = User::model()->findAll();
        
        $this->render('peopleList', array('users' => $users));
    }
    
    public function actionProfile($id) {
        // display the home page
        $this->dataUrl = $this->createUrl("site/profile/$id");
        $this->pageId = 'site-profile';
        
        $user = User::model()->findByPk($id);
        
        $this->render('profile', array('user' => $user));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        $user = Yii::app()->user;
        Yii::app()->user->logout();
        $this->redirect($this->createUrl('site/login/'));
    }

}