<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

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
                'actions' => array('chat', 'logout', 'home', 'getMessages', 'newMessage','contact'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('index', 'login'),
                'users' => array('*'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
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
                    $user = UserChild::model()->findByAttributes(array('username' => $chat->user));
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
     * Displays the login page
     */
    public function actionLogin() {

        $this->dataUrl = $this->createUrl('site/login/');
        $this->pageId = 'site-login';
        $model = new LoginForm;

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login())
                $this->redirect($this->createUrl('site/home/'));
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

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        $user = Yii::app()->user;
        Yii::app()->user->logout();
        $this->redirect($this->createUrl('site/login/'));
    }

}