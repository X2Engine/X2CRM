<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * @package application.modules.mobile.controllers
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
                'actions' => array('chat', 'logout', 'home', 'getMessages', 'newMessage','contact','home2','more','online', 'activity', 'people', 'profile'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('index', 'login', 'forgetMe', 'captcha'),
                'users' => array('*'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

	public function behaviors() {
		return array_merge (parent::behaviors (), array(
			'CommonSiteControllerBehavior' => array('class' => 'application.components.CommonSiteControllerBehavior')));
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

        $this->dataUrl = $this->createUrl('/mobile/site/chat');
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
        Session::cleanUpSessions();
        $sessions = Session::model()->findAll();
        $usernames = array();
        $users = array();
        foreach($sessions as $session) {
            $usernames[] = $session->user;
        }
        $usernames = array_unique ($usernames);
        foreach($usernames as $username){
            $user = User::model()->findByAttributes(array('username'=>$username));
            $users[] = $user->firstName." ".$user->lastName;
        }

        sort ($users);
        $this->render('online',array(
            'users'=>$users,
        ));
    }

    public function actionActivity (){
        $this->render('activity');
    }
    

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        $user = Yii::app()->user;
        if ($user == null || $user->isGuest)
//            $this->render('index');
            $this->redirect($this->createUrl('/mobile/site/login'));
        else
            $this->redirect($this->createUrl('/mobile/site/home'));
    }
    
    public function actionMore(){
        $user = Yii::app()->user;
        if ($user == null || $user->isGuest)
//            $this->render('index');
            $this->redirect($this->createUrl('/mobile/site/login'));
        else
            $this->redirect($this->createUrl('/mobile/site/home2'));
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

    /**
     * Obtain the IP address of the current web client.
     * @return string
     */
    function getRealIp() {
        foreach(array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ) as $var) {
            if(array_key_exists($var,$_SERVER)){
                foreach(explode(',',$_SERVER[$var]) as $ip) {
                    $ip = trim($ip);
                    if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                        return $ip;
                }
            }
        }
        return false;
    }

    /**
     * Clears remember me cookies and redirects to login page. 
     */
    public function actionForgetMe () {
        $loginForm = new LoginForm;
        foreach(array('username','rememberMe') as $attr) {
            // Remove the cookie if they unchecked the box
            AuxLib::clearCookie(CHtml::resolveName($loginForm, $attr));
        }
        $this->redirect($this->createUrl('/mobile/site/login'));
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {
        
        $this->dataUrl = $this->createUrl('/mobile/site/login');
        $this->pageId = 'site-login';
        $model = new LoginForm;
        $model->useCaptcha = false;
        
        // if it is ajax validation request
        /* this would bypass captcha. commented out to prevent security vulnerability */
        /*if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }*/
        
        // collect user input data
        if(isset($_POST['LoginForm'])) {
            $this->login ($model, true);
        }

        // display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * Displays the home page
     */
    public function actionHome() {
        // display the home page
        $this->dataUrl = $this->createUrl('/mobile/site/home');
        $this->pageId = 'site-home';
        $this->render('home', array());
    }
    
    public function actionHome2() {
        // display the home page
        $this->dataUrl = $this->createUrl('/site/home2');
        $this->pageId = 'site-home2';
        $this->render('home2', array());
    }
    
    public function actionPeople() {
        // display the home page
        $this->dataUrl = $this->createUrl('/mobile/site/people');
        $this->pageId = 'site-people';
        
        $users = User::model()->findAll();
        
        $this->render('peopleList', array('users' => $users));
    }
    
    public function actionProfile($id) {
        // display the home page
        $this->dataUrl = $this->createUrl("/mobile/site/profile",array('id'=>$id));
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
        
        $this->redirect($this->createUrl('/mobile/site/login'));
    }

}
