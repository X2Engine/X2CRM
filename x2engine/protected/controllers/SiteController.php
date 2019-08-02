<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * Primary/default controller for the web application.
 *
 * @package application.controllers
 */
class SiteController extends x2base {

    // Declares class-based actions.
    //public $layout = '//layouts/main';

    public $modelClass = 'Admin';
    public $portlets = array();

    public function filters() {
        return array_merge(parent::filters(), array(
            'setPortlets',
            'accessControl',
        ));
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'MobileControllerBehavior' => array(
                'class' =>
                'application.modules.mobile.components.behaviors.' .
                'MobileSiteControllerBehavior'
            ),
            'CommonSiteControllerBehavior' => array(
                'class' => 'application.components.behaviors.CommonSiteControllerBehavior'),
        ));
    }

    protected function beforeAction($action = null) {
        $this->validateMobileRequest($action);
        if (is_int(Yii::app()->locked) &&
                !Yii::app()->user->checkAccess('GeneralAdminSettingsTask') &&
                !(in_array($this->action->id, array('login', 'logout')) ||
                Yii::app()->user->isGuest)) {

            $this->appLockout();
        }
        return $this->runBehaviorBeforeActionHandlers($action);
    }

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array(
                    'unsubscribe', 'login', 'forgetMe', 'index', 'logout', 'warning', 'captcha', 'googleLogin',
                    'error', 'storeToken', 'sendErrorReport', 'resetPassword', 'anonHelp',
                    'mobileResetPassword', 'webleadCaptcha', 'needsTwoFactor', 'unsubscribe'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array(
                    'groupChat', 'newMessage', 'getMessages', 'checkNotifications', 'updateNotes',
                    'addPersonalNote', 'getNotes', 'getURLs', 'addSite', 'deleteMessage',
                    'fullscreen', 'widgetState', 'widgetOrder', 'saveGridviewSettings',
                    'saveFormSettings', 'saveWidgetHeight', 'inlineEmail', 'tmpUpload', 'upload',
                    'uploadProfilePicture', 'index', 'contact', 'viewNotifications', 'inlineEmail',
                    'toggleShowTags', 'appendTag', 'removeTag', 'printRecord',
                    'createRecords', 'toggleVisibility', 'page', 'showWidget', 'hideWidget',
                    'reorderWidgets', 'minimizeWidget', 'publishPost', 'getEvents', 'loadComments',
                    'loadPosts', 'addComment', 'flagPost', 'broadcastEvent', 'minimizePosts',
                    'bugReport', 'deleteRelationship', 'minMaxLeftWidget', 'toggleFeedControls',
                    'toggleFeedFilters', 'share', 'activityFeedOrder',
                    'activityFeedWidgetBgColor', 'likePost', 'loadLikeHistory', 'dynamicDropdown',
                    'stickyPost', 'getEventsBetween', 'mediaWidgetToggle', 'createChartSetting',
                    'deleteChartSetting', 'GetActionsBetweenAction', 'DeleteURL', 'widgetSetting',
                    'removeTmpUpload', 'duplicateCheck', 'resolveDuplicates', 'getSkypeLink',
                    'mergeRecords', 'ajaxSave', 'layoutPreview', 'tourSeen', 'viewEmbedded'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('motd'),
                'expression' => 'Yii::app()->params->isAdmin',
            ),
            array('deny',
                'users' => array('*')
            )
        );
    }

    public function actions() {
        return array_merge($this->getBehaviorActions(), array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
                'testLimit' => 1,
            ),
            'webleadCaptcha' => array(
                'class' => 'WebFormCaptchaAction',
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
            'GetActionsBetweenAction' => array(
                'class' => 'GetActionsBetweenAction'
            ),
        ));
    }


    public function actionUnsubscribe() {
          if (isset( $_GET["unsubscribeALL"])){
                Contacts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'email=:email', array(':email' => $_GET["email"]));
                Accounts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'email=:email', array(':email' => $_GET["email"]));
                X2Leads::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'email=:email', array(':email' => $_GET["email"]));
                Opportunity::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'email=:email', array(':email' => $_GET["email"]));
               X2ListItem::model()
                        ->updateAll(
                                array('unsubscribed' => time()), 'emailAddress=:email AND unsubscribed=0', array('email' => $_GET["email"]));
                                            
                Contacts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'businessEmail=:email', array(':email' => $_GET["email"]));
                Accounts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'businessEmail=:email', array(':email' => $_GET["email"]));
                X2Leads::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'businessEmail=:email', array(':email' => $_GET["email"]));
                Opportunity::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'businessEmail=:email', array(':email' => $_GET["email"]));
                         
                           
                Contacts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'personalEmail=:email', array(':email' => $_GET["email"]));
                Accounts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'personalEmail=:email', array(':email' => $_GET["email"]));
                X2Leads::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'personalEmail=:email', array(':email' => $_GET["email"]));
                Opportunity::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'personalEmail=:email', array(':email' => $_GET["email"]));
                           
                             
                Contacts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'alternativeEmail=:email', array(':email' => $_GET["email"]));
                Accounts::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'alternativeEmail=:email', array(':email' => $_GET["email"]));
                X2Leads::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'alternativeEmail=:email', array(':email' => $_GET["email"]));
                Opportunity::model()
                        ->updateAll(
                                array('doNotEmail' => true), 'alternativeEmail=:email', array(':email' => $_GET["email"]));
          }
          $categories = Yii::app()->db->createCommand()
                    ->select('options')
                        ->from('x2_dropdowns')
                        ->where('id=155')
                        ->queryRow();
         $maillist = json_decode($categories["options"]);
         foreach($maillist as $key => $value) {
             $FullName = 'Unsubscribe_' . $value . '_X2_internal_list';
             $list = CActiveRecord::model('X2List')->findByAttributes(array('name' => $FullName));
             $item = CActiveRecord::model('X2ListItem')->findByAttributes(array('emailAddress' => $_GET["email"], 'listId' => $list->id));
            
             
             if(!isset($item) && isset($_GET[str_replace(' ', '_', $value)])){
                 $unSub = new X2ListItem;
                $unSub->emailAddress =  $_GET["email"];
                $unSub->listId = $list->id;
                $unSub->save();
                
             }
         }
          echo Yii::app()->settings->EmailUnSubPage;
      }

    public function actionSendErrorReport() {
        if (isset($_POST['report'])) {
            $errorReport = $_POST['report'];
            $errorReport = CJSON::decode(base64_decode($errorReport));
            if (isset($_POST['email'])) {
                $errorReport['email'] = $_POST['email'];
            }
            if (isset($_POST['bugDescription'])) {
                $errorReport['bugDescription'] = $_POST['bugDescription'];
            }
            $errorReport['edition'] = Yii::app()->edition;
            $errorReport = base64_encode(CJSON::encode($errorReport));
            $ccUrl = "http://www.x2software.com/receiveErrorReport.php";
            $ccSession = curl_init($ccUrl);
            curl_setopt($ccSession, CURLOPT_POST, 1);
            curl_setopt($ccSession, CURLOPT_HTTPHEADER, array('Accept-Charset: UTF-8;'));
            curl_setopt($ccSession, CURLOPT_POSTFIELDS, array('errorReport' => $errorReport));
            curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
            $ccResult = curl_exec($ccSession);
            curl_close($ccSession);
            echo $ccResult;
        }
    }

    public function actionActivityFeedOrder() {
        $profile = Yii::app()->params->profile;
        if (isset($profile)) {
            $profile->activityFeedOrder = !$profile->activityFeedOrder;
            $profile->update(array('activityFeedOrder'));
        }
    }

    public function actionActivityFeedWidgetBgColor($color) {
        $profile = Yii::app()->params->profile;
        if (isset($profile)) {
            $theme = $profile->theme;
            $theme['activityFeedWidgetBgColor'] = $color;
            $profile->theme = $theme;
            $profile->update(array('theme'));
        }
    }

    public function actionMediaWidgetToggle() {
        $profile = Yii::app()->params->profile;
        if (isset($profile)) {
            $profile->mediaWidgetDrive = !$profile->mediaWidgetDrive;
            $profile->update(array('mediaWidgetDrive'));
        }
    }

    /**
     * Action to set a widget setting
     * @param string $widget the widget name 
     * @param string $setting the setting name
     * @param string $value the value to save in the setting
     * */
    public function actionWidgetSetting($widget, $setting) {
        $value = $_GET['value'];
        Profile::changeWidgetSetting($widget, $setting, $value);
    }

    // Outputs white or black depending on input color
    // @param $colorString a string representing a hex number
    // @param $testType standardText or linkText
    function convertTextColor($colorString, $textType) {
        // Split the string to red, green and blue components
        // Convert hex strings into ints
        $red = intval(substr($colorString, 0, 2), 16);
        $green = intval(substr($colorString, 2, 2), 16);
        $blue = intval(substr($colorString, 4, 2), 16);
        if ($textType == 'standardText') {
            return (((($red * 299) + ($green * 587) + ($blue * 114)) / 1000) >= 128) ? 'black' : 'white';
        } else if ($textType == 'linkText') {
            if (((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
                return '#fff000';  // Yellow links
            } else
                return '#0645AD'; // Blue link color
        }
        else if ($textType == 'visitedLinkText') {
            if (((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
                return '#ede100';  // Yellow links
            } else
                return '#0B0080'; // Blue link color
        }
        else if ($textType == 'activeLinkText') {
            if (((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
                return '#fff000';  // Yellow links
            } else
                return '#0645AD'; // Blue link color
        }
        else if ($textType == 'hoverLinkText') {
            if (((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
                return '#fff761';  // Yellow links
            } else
                return '#3366BB'; // Blue link color
        }
    }

    public function actionDynamicDropdown($val, $dropdownId, $field = false, $module = null) {
        $dropdown = X2Model::model('Dropdowns')->findByAttributes(array('parent' => $dropdownId, 'parentVal' => $val));
        if (isset($dropdown)) {
            if (!$field) {
                echo CHtml::tag('option', array('value' => ''), CHtml::encode('-'), true);
                $data = json_decode($dropdown->options, true);
                foreach ($data as $value => $name) {
                    echo CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                }
            } else {
                $fieldRecord = X2Model::model('Fields')->findByAttributes(array('modelName' => $module, 'type' => 'dependentDropdown', 'linkType' => $dropdownId));
                if (isset($fieldRecord)) { // Look up dependentDropdown field with a link to the master dropdown.
                    $htmlStr = CHtml::tag('option', array('value' => ''), CHtml::encode('Select an option'), true);
                    $data = json_decode($dropdown->options, true);
                    foreach ($data as $value => $name) { // Build an HTML string of the dropdown response.
                        $htmlStr .= CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                    }
                    echo CJSON::encode(array($fieldRecord->fieldName, $htmlStr)); // Echo back the field name to update + the dropdown HTMl.
                }
            }
        } else {
            if (!$field) {
                echo CHtml::tag('option', array('value' => ''), '-', true);
            } else {
                $fieldRecord = X2Model::model('Fields')->findByAttributes(array('modelName' => $module, 'type' => 'dependentDropdown', 'linkType' => $dropdownId));
                if (isset($fieldRecord))
                    echo CJSON::encode(array($fieldRecord->fieldName, CHtml::tag('option', array('value' => ''), '-', true)));
            }
        }
    }

    /**
     * Saves left widget minimize setting to user's profile.
     * @param string ('collapse' | 'expand')
     * @param string The name of the widget. This should match the widget name defined
     *  in the layout stored in the user's profile.
     * @return string 'failure' if the setting could not be saved, 'success' otherwise
     */
    public function actionMinMaxLeftWidget($action, $widgetName) {
        $profile = Yii::app()->params->profile;
        if (isset($profile)) {
            $layout = $profile->getLayout();
            $minimize;
            if ($action === 'expand') {
                $minimize = false;
            } else if ($action === 'collapse') {
                $minimize = true;
            } else {
                echo 'failure';
                return;
            }
            if (in_array($widgetName, array_keys($layout['left']))) {
                $layout['left'][$widgetName]['minimize'] = $minimize;
                Yii::app()->params->profile->layout = json_encode($layout);
                Yii::app()->params->profile->update(array('layout'));
            }
            echo 'success';
            return;
        }
        echo 'failure';
    }

    /**
     * Displays message of the day.
     */
    public function actionMotd() {
        if (isset($_POST['message'])) {
            $motd = $_POST['message'];
            $temp = Social::model()->findByAttributes(array('type' => 'motd'));
            $temp->data = $motd;
            if ($temp->save())
                echo $motd;
            else
                echo "An error has occured.";
        }else {
            echo "An error has occured.";
        }
    }

    /**
     * Renders the group chat.
     */
    public function actionGroupChat() {
        $this->portlets = array();
        $this->layout = '//layouts/column1';
        //$portlets = $this->portlets;
        // display full screen group chat
        $this->render('groupChat');
    }

    /**
     * Creates a new chat message from the current web user.
     */
    public function actionNewMessage() {
        if (isset($_POST['chat-message']) && $_POST['chat-message'] != '' && $_POST['chat-message'] != Yii::t('app', 'Enter text here...')) {

            $user = Yii::app()->user->getName();
            $chat = new Social;
            $chat->data = $_POST['chat-message'];
            ;
            $chat->user = $user;
            $chat->visibility = 1;
            $chat->timestamp = time();
            $chat->type = 'chat';

            if ($chat->save()) {
                echo CJSON::encode(array(
                    array(
                        $chat->id,
                        date('g:i:s A', $chat->timestamp),
                        '<span class="my-username">' . $chat->user . '</span>',
                        $this->convertUrls($chat->data)
                    )
                ));
            }
        }
    }

    /**
     * Add a personal note to the list of notes for the current web user.
     */
    public function actionAddPersonalNote() {
        if (isset($_POST['note-message']) && $_POST['note-message'] != '') {
            $user = Yii::app()->user->getName();
            $note = new Social;
            $note->associationId = Yii::app()->user->getId();
            $note->data = $_POST['note-message'];
            ;
            $note->user = $user;
            $note->visibility = 1;
            $note->timestamp = time();
            $note->type = 'note';

            if ($note->save()) {
                echo "1";
            }
        }
    }

    /**
     * Adds a new URL
     */
    public function actionAddSite() {
        if ((isset($_POST['url-title']) && isset($_POST['url-url'])) && ($_POST['url-title'] != '' && $_POST['url-url'] != '')) {

            $site = new URL;
            $site->title = $_POST['url-title'];
            $site->url = $_POST['url-url'];
            $site->userid = Yii::app()->user->getId();
            $site->timestamp = time();
            if ($site->save()) {
                echo CJSON::encode(array(
                    CHtml::link(
                            $site->title, URL::prependProto($site->url), array('target' => '_blank')),
                    CHtml::link(
                            '', array('/site/DeleteURL', 'id' => $site->id), array(
                        'title' => Yii::t('app', 'Delete Link'),
                        'class' => 'delete-top-site-link fa fa-close',
                        'target' => '_blank'
                            )
                    )
                ));
            }
        }
    }

    /**
     * Obtains notes for displaying within the notes widget.
     * @param string $url The deletion URL for notes
     */
    public function actionGetNotes($url) {
        $content = Social::model()->findAllByAttributes(array('type' => 'note', 'associationId' => Yii::app()->user->getId()), array(
            'order' => 'timestamp DESC',
        ));
        $res = "";
        foreach ($content as $item) {
            $res .= $this->convertUrls(CHtml::encode($item->data)) . " " . CHtml::link('[x]', array('/site/deleteMessage', 'id' => $item->id, 'url' => $url)) . '<br /><br />';
        }
        if ($res == "") {
            $res = Yii::t('app', "Feel free to enter some notes!");
        }
        echo $res;
    }

    public function actionDeleteURL($id) {
        if (isset($id)) {
            Yii::app()->db->createCommand()->delete(
                    'x2_urls', 'id=:id', array(':id' => $id));
        }
    }

    public function actionEditURL($id, $url) {
        //$entry->title = 'ggg';
        //$this->list = array('item1','item2');
        $this->redirect($url);
    }

    /**
     * Gets URLs for "top sites"
     * @param string $url
     */
    /* public function actionGetURLs($url){
      $content = URL::model()->findAllByAttributes(
      array('userid' => Yii::app()->user->getId()), array('order' => 'timestamp DESC'));
      $res = '<table><tr><th>'.Yii::t('app', 'Link').'</th><th>Delete</th></tr>';
      if($content){
      foreach($content as $entry){
      if(strpos($entry->url, 'http://') === false){
      $entry->url = "http://".$entry->url;
      }
      $res .=
      '<tr>'.
      '<td>' .
      CHtml::link(
      Yii::t('app', $entry->title), $entry->url,
      array('target'=>'_blank')) .
      "</td>".
      "<td>" .
      CHtml::link(
      'Delete',
      array('/site/DeleteURL', 'id' => $entry->id, 'url' => $url)).
      "</td>".
      "</tr>";
      }
      }else{
      $res .=
      "<tr><td>".
      CHtml::link(
      Yii::t('app', 'Example'), 'http://www.x2engine.com',
      array('target'=>'_blank')).
      "</td><td>".
      "<a href='.'>".Yii::t('app', 'Delete')."</a>".
      "</td></tr>";
      }
      echo $res;
      } */

    /**
     * Delete a message from the social feed.
     * @param integer $id
     * @param string $url
     */
    public function actionDeleteMessage($id, $url) {
        $note = Social::model()->findByPk($id);
        if (isset($note))
            $note->delete();
        $this->redirect($url);
    }

    /**
     * Sets "Fullscreen" mode for the current web user / session
     */
    public function actionFullscreen() {
        Yii::app()->session['fullscreen'] = (isset($_GET['fs']) && $_GET['fs'] == 1);
        $profile = Yii::app()->params->profile;
        $profile->fullscreen = (isset($_GET['fs']) && $_GET['fs'] == 1);
        $profile->update(array('fullscreen'));
        // echo var_dump(Yii::app()->session['fullscreen']);
        echo 'Success';
    }

    public function actionDeleteRelationship($firstId, $firstType, $secondId, $secondType) {
        $rel = X2Model::model('Relationships')->findByAttributes(array('firstId' => $firstId, 'firstType' => $firstType, 'secondId' => $secondId, 'secondType' => $secondType));
        if (isset($rel)) {
            $rel->delete();
        } else {
            $rel = X2Model::model('Relationships')->findByAttributes(array('firstId' => $secondId, 'firstType' => $secondType, 'secondId' => $firstId, 'secondType' => $firstType));
            if (isset($rel)) {
                $rel->delete();
            }
        }
        if (isset($_GET['redirect'])) {
            $this->redirect($this->createUrl($_GET['redirect']));
        }
    }

    /**
     * Checks for the widget's state.
     */
    public function actionWidgetState() {

        if (isset($_GET['widget']) && isset($_GET['state'])) {
            $widgetName = $_GET['widget'];
            $widgetState = ($_GET['state'] == 0) ? '0' : '1';

            // $profile = Yii::app()->params->profile;

            $order = explode(":", Yii::app()->params->profile->widgetOrder);
            $visibility = explode(":", Yii::app()->params->profile->widgets);

            // var_dump($order);
            // var_dump($visibility);
            if (array_key_exists($widgetName, Yii::app()->params->registeredWidgets)) {

                $pos = array_search($widgetName, $order);
                $visibility[$pos] = $widgetState;
                // die(var_dump($visibility));

                Yii::app()->params->profile->widgets = implode(':', $visibility);

                if (Yii::app()->params->profile->update(array('widgets'))) {
                    echo 'success';
                }
            }
        }
    }

    /**
     * Responds with the order of widgets for the current user.
     */
    public function actionWidgetOrder() {
        if (isset($_POST['widget'])) {

            $widgetList = $_POST['widget'];

            // $profile = Yii::app()->params->profile;
            $order = Yii::app()->params->profile->widgetOrder;
            $visibility = Yii::app()->params->profile->widgets;

            $order = explode(":", $order);
            $visibility = explode(":", $visibility);

            $newOrder = array();

            foreach ($widgetList as $item) {
                if (array_key_exists($item, Yii::app()->params->registeredWidgets))
                    $newOrder[] = $item;
            }
            $str = "";
            $visStr = "";
            foreach ($newOrder as $item) {
                $pos = array_search($item, $order);
                if (array_key_exists($pos, $visibility)) {
                    $vis = $visibility[$pos];
                } else {
                    $vis = 1;
                }
                $str .= $item . ":";
                $visStr .= $vis . ":";
            }
            $str = substr($str, 0, -1);
            $visStr = substr($visStr, 0, -1);

            Yii::app()->params->profile->widgetOrder = $str;
            Yii::app()->params->profile->widgets = $visStr;

            if (Yii::app()->params->profile->save()) {
                echo 'success';
            }
        }
    }

    /**
     * Save custom gridview settings.
     *
     * Saves the settings (i.e. columns, column position and column width)
     * for the X2GridView model.
     */
    public function actionSaveGridviewSettings() {
        $result = false;

        // gv settings parameter is prefixed by a unique id
        $gvSettings;
        foreach ($_POST as $key => $val) {
            if (preg_match("/gvSettings$/", $key)) {
                $gvSettings = json_decode($val, true);
            }
        }

        if (isset($gvSettings) && isset($_POST['viewName'])) {
            if (isset($gvSettings))
                $result = Profile::setGridviewSettings($gvSettings, $_POST['viewName']);
        }
        if ($result) {
            echo '200 Success';
        } else {
            echo '400 Failure';
        }
    }

    /**
     * Save settings for a custom form layout.
     *
     * @throws CHttpException
     */
    public function actionSaveFormSettings() {
        $result = false;
        if (isset($_GET['formSettings']) && isset($_GET['formName'])) {
            $formSettings = json_decode($_GET['formSettings'], true);

            if (isset($formSettings)) {
                $result = Profile::setFormSettings($formSettings, $_GET['formName']);
            }
        }
        if ($result) {
            echo 'success';
        } else {
            throw new CHttpException(400, 'Invalid request. Probabaly something wrong with the JSON string.');
        }
    }

    /**
     * Saves the height of a widget.
     */
    public function actionSaveWidgetHeight() {
        if (isset($_POST['Widget']) && isset($_POST['Height'])) {
            $heights = $_POST['Height'];
            $widget = $_POST['Widget'];
            $widgetSettings = Profile::getWidgetSettings();

            foreach ($heights as $key => $height) {
                $widgetSettings->$widget->$key = intval($height);
            }

            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->save();
        }
    }

    /**
     * Uploads a file to a temporary folder.
     *
     * Upload a file to a temp folder, which will presumably be deleted shortly thereafter
     * Temp files are stored in a temp folder with a randomly generated name. They are stored
     * in 'uploads/media/temp'
     */
    public function actionTmpUpload() {
        if (isset($_FILES['upload'])) {
            $upload = CUploadedFile::getInstanceByName('upload');

            if ($upload) {
                $name = str_replace(' ', '_', $upload->getName());
                $temp = TempFile::createTempFile($name);
                if (!$temp) {
                    echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. Temp file not created')));
                } else {
                    if ($upload->saveAs($temp->fullpath())) { // temp file saved
                        echo json_encode(array('status' => 'success', 'id' => $temp->id, 'name' => $name));
                    } else {
                        $error = $upload->getError();
                        if ($error == UPLOAD_ERR_NO_FILE) {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. No file found')));
                        } elseif ($error == UPLOAD_ERR_INI_SIZE) {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. Temp file is larger than the max upload ini size')));
                        } elseif ($error == UPLOAD_ERR_FORM_SIZE) {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. Temp file is larger than the max upload form size')));
                        } elseif ($error == UPLOAD_ERR_PARTIAL) {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. The File was only partially uploaded')));
                        } elseif ($error == UPLOAD_ERR_NO_TMP_DIR) {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. Missing the temporary folder to store the uploaded file')));
                        } elseif ($error == UPLOAD_ERR_CANT_WRITE) {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. Failed to write the uploaded file to disk')));
                        } elseif (defined('UPLOAD_ERR_EXTENSION') && $error == UPLOAD_ERR_EXTENSION) {
                            // available for PHP 5.2.0 or above
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. A PHP extension stopped the file upload.')));
                        } else {
                            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. Temp file not saved')));
                        }
                    }
                }
            } else {
                echo json_encode(array('status' => 'notsent', 'message' => Yii::t('media', 'File was not sent to server.')));
            }
        } else {
            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file. HTTP File Upload variables not set')));
        }
    }

    private function handleDefaultUpload($model, $name) {
        $note = new Actions;
        $note->createDate = time();
        $note->dueDate = time();
        $note->completeDate = time();
        $note->complete = 'Yes';
        $note->visibility = '1';
        $note->completedBy = Yii::app()->user->getName();
        if ($model->private) {
            $note->assignedTo = Yii::app()->user->getName();
            $note->visibility = '0';
        } else {
            $note->assignedTo = 'Anyone';
        }
        $note->type = 'attachment';
        $note->associationId = $_POST['associationId'];
        $note->associationType = $_POST['associationType'];

        $association = $this->getAssociation($note->associationType, $note->associationId);
        if ($association != null) {
            $note->associationName = $association->name;
        }

        $note->actionDescription = $model->fileName . ':' . $model->id;
        if ($note->save()) {
            
        } else {
            unlink('uploads/protected/' . $name);
        }
        if ($model->associationType == 'product') {
            $this->redirect(array('/products/products/view', 'id' => $model->associationId));
        }
        $this->redirect(array($model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId));
    }

    /**
     * @param object $model
     * @param string $name
     */
    private function handleFeedTypeUpload($model, $name) {
        $event = new Events;
        $event->user = Yii::app()->user->getName();
        if (isset($_POST['attachmentText']) && !empty($_POST['attachmentText'])) {
            $event->text = $_POST['attachmentText'];
        } /* else {
          $event->text = Yii::t('app', 'Attached file: ');
          } */
        $location = Yii::app()->params->profile->user->logLocation('activityPost', 'POST');
        $geoCoords = isset($_POST['geoCoords']) ? CJSON::decode($_POST['geoCoords'], true) : null;
        $isCheckIn = ($geoCoords && (isset($geoCoords['lat']) || isset($geoCoords['locationEnabled'])));
        if ($location && $isCheckIn) {
            // Only associate location when a checkin is requested
            $event->locationId = $location->id;
            $staticMap = $location->generateStaticMap();
            $event->text .= '$|&|$' . $geoCoords['comment'] . '$|&|$'; //temporary dividers to be parsed later
            $geocodedAddress = isset($geoCoords['address']) ? $geoCoords['address'] : $location->geocode();
        }
        $event->type = 'media';
        $event->subtype = 'Social Post';
        $event->timestamp = time();
        $event->lastUpdated = time();
        $event->associationId = $model->associationId;
        $event->associationType = 'User';
        if (isset($_POST['recordLinks']) && ($decodedLinks = CJSON::decode($_POST['recordLinks'], true))) {
            $event->recordLinks = $decodedLinks;
        }
        $newEventIdTimestamp = $event->timestamp ? $event->timestamp : 0;
        if ($model->private) {
            $event->visibility = 0;
        }
        $location = Yii::app()->params->profile->user->logLocation('activityPost', 'POST');
        if ($location) {
            $event->locationId = $location->id;
        }
        if ($event->save()) {
            //$this->redirect('profile');
            if (!empty($staticMap)) {
                if (!empty($geocodedAddress)) {
                    $event->text .= Yii::t('app', 'Checking in at ') . $geocodedAddress . ' | ' .
                            Formatter::formatDateTime(time());
                }
                $event->saveRaw(Yii::app()->params->profile, $staticMap);
            }
        } else {
            unlink('uploads/protected/' . $name);
        }

        $event = X2Model::model('Events')->findByAttributes(array('timestamp' => $newEventIdTimestamp));
        // relate file to event
        $join = new RelationshipsJoin('insert', 'x2_events_to_media');
        $join->eventsId = $event->id;
        $join->mediaId = $model->id;
        if (!$join->save()) {
            throw new CException(implode(';', $join->getAllErrorMessages()));
        }

        if (isset($_POST['profileId'])) {
            $this->redirect(array('/profile/view', 'id' => $_POST['profileId']));
        } else {
            $this->redirect(array('/profile/view', 'id' => Yii::app()->user->getId()));
        }
    }

    private function handleTopicReplyUpload($model, $name) {
        $topicReply = new TopicReplies;

        if (isset($_POST['TopicReplies'])) {
            $topicReply->setAttributes($_POST['TopicReplies']);
            if ($topicReply->save()) {
                $model->associationId = $topicReply->id;
                $model->update(array('associationId'));
                echo $topicReply->id;
            } else {
                file_exists('uploads/protected/' . $name) && unlink('uploads/protected/' . $name);
                echo CJSON::encode(array(
                    'message' => $topicReply->getAllErrorMessages(),
                ));
            }
        } else {
            file_exists('uploads/protected/' . $name) && unlink('uploads/protected/' . $name);
            echo CJSON::encode(array(
                'message' => Yii::t('app', 'Reply '),
            ));
        }
    }

    /**
     * Remove a temp file and the temp folder that is in.
     */
    public function actionRemoveTmpUpload() {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            if (is_numeric($id)) {
                $tempFile = TempFile::model()->findByPk($id);
                if ($tempFile) {
                    $folder = $tempFile->folder;
                    $name = $tempFile->name;
                    if (file_exists('uploads/protected/media/temp/' . $folder . '/' . $name))
                        unlink('uploads/protected/media/temp/' . $folder . '/' . $name); // delete file
                    if (file_exists('uploads/protected/media/temp/' . $folder))
                        rmdir('uploads/protected/media/temp/' . $folder); // delete folder
                    $tempFile->delete(); // delete database entry tracking temp file
                }
            }
        }
    }

    /**
     * Upload a file.
     */
    public function actionUpload() {
        if (!isset($_FILES['upload'])) {
            throw new CHttpException('400', 'Invalid request.');
        }

        if (isset($_POST['drive']) && $_POST['drive']) { // google drive
            $auth = new GoogleAuthenticator();
            if ($auth->getAccessToken()) {
                $service = $auth->getDriveService();
            }
            $createdFile = null;
            if (isset($service, $_SESSION['access_token'], $_FILES['upload'])) {
                try {
                    $file = new Google_Service_Drive_DriveFile();
                    $file->setTitle($_FILES['upload']['name']);
                    $file->setDescription('Uploaded by X2Engine');
                    $file->setMimeType($_FILES['upload']['type']);

                    if (empty($_FILES['upload']['tmp_name'])) {
                        $err = false;
                        switch ($_FILES['newfile']['error']) {
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                $err .= 'File size exceeds limit of ' . get_max_upload() . ' bytes.';
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $err .= 'File upload was not completed.';
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                $err .= 'Zero-length file uploaded.';
                                break;
                            default:
                                $err .= 'Internal error ' . $_FILES['newfile']['error'];
                                break;
                        }
                        if ((bool) $message) {
                            throw new CException($message);
                        }
                    }
                    $data = file_get_contents($_FILES['upload']['tmp_name']);
                    $createdFile = $service->files->insert($file, array(
                        'data' => $data,
                        'mimeType' => $_FILES['upload']['type'],
                        'uploadType' => 'multipart',
                    ));
                    if ($createdFile instanceof Google_Service_Drive_DriveFile) {
                        $model = new Media;
                        $model->fileName = $createdFile['id'];
                        $model->name = $createdFile['title'];
                        if (isset($_POST['associationId'])) {
                            $model->associationId = $_POST['associationId'];
                        }
                        if (isset($_POST['associationType'])) {
                            $model->associationType = $_POST['associationType'];
                        }
                        if (isset($_POST['private'])) {
                            $model->private = $_POST['private'];
                        }
                        $model->uploadedBy = Yii::app()->user->getName();
                        $model->mimetype = $createdFile['mimeType'];
                        $model->filesize = $createdFile['fileSize'];
                        $model->drive = 1;
                        $model->save();
                        if ($model->associationType == 'feed') {
                            $event = new Events;
                            $event->user = Yii::app()->user->getName();
                            if (isset($_POST['attachmentText']) && !empty($_POST['attachmentText'])) {
                                $event->text = $_POST['attachmentText'];
                            } /* else {
                              $event->text = Yii::t('app', 'Attached file: ');
                              } */
                            $event->type = 'media';
                            $event->timestamp = time();
                            $event->lastUpdated = time();
                            $event->associationId = $model->id;
                            $event->associationType = 'Media';
                            if (isset($_POST['recordLinks']) && ($decodedLinks = CJSON::decode($_POST['recordLinks'], true))) {
                                $event->recordLinks = $decodedLinks;
                            }
                            $event->save();
                            if (Auxlib::isAjax()) {
                                return print("success");
                            }
                            $this->redirect(array('/profile/view', 'id' => Yii::app()->user->getId()));
                        } elseif ($model->associationType == 'docs') {
                            if (Auxlib::isAjax()) {
                                return print("success");
                            }
                            $this->redirect(array('/docs/docs/index'));
                        } elseif (!empty($model->associationType) && !empty($model->associationId)) {
                            $note = new Actions;
                            $note->createDate = time();
                            $note->dueDate = time();
                            $note->completeDate = time();
                            $note->complete = 'Yes';
                            $note->visibility = '1';
                            $note->completedBy = Yii::app()->user->getName();
                            if ($model->private) {
                                $note->assignedTo = Yii::app()->user->getName();
                                $note->visibility = '0';
                            } else {
                                $note->assignedTo = 'Anyone';
                            }
                            $note->type = 'attachment';
                            $note->associationId = $_POST['associationId'];
                            $note->associationType = $_POST['associationType'];

                            $association = $this->getAssociation($note->associationType, $note->associationId);
                            if ($association != null) {
                                $note->associationName = $association->name;
                            }

                            $note->actionDescription = $model->fileName . ':' . $model->id;
                            if ($note->save()) {
                                if (Auxlib::isAjax()) {
                                    return print("success");
                                }
                                $this->redirect(array($model->associationType . '/' . $model->associationId));
                            }
                        } else {
                            if (Auxlib::isAjax()) {
                                return print("success");
                            }
                            $this->redirect('/media/media/view', array('id' => $model->id));
                        }
                    } else {
                        throw new CHttpException('400', 'Invalid request.');
                    }
                } catch (Google_Auth_Exception $e) {
                    $auth->flushCredentials();
                    $auth->setErrors($e->getMessage());
                    $service = null;
                    $createdFile = null;
                }
            } else {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    if (Auxlib::isAjax()) {
                        return print("success");
                    }
                    $this->redirect($_SERVER['HTTP_REFERER']);
                } else {
                    throw new CHttpException('400', 'Invalid request');
                }
            }
        } else { // non-google drive upload
            $model = new Media;
            $temp = CUploadedFile::getInstanceByName('upload'); // file uploaded through form
            if ($temp && ($tempName = $temp->getTempName()) && !empty($tempName)) {
                $name = str_replace(' ', '_', $temp->getName());
                $model->fileName = $name;
                $model->resolveNameConflicts();

                $username = Yii::app()->user->name;
                // copy file to user's media uploads directory
                if (FileUtil::ccopy($tempName, "uploads/protected/media/$username/$model->fileName")) {

                    if (isset($_POST['associationId'])) {
                        $model->associationId = $_POST['associationId'];
                    }
                    if (isset($_POST['associationType'])) {
                        $model->associationType = $_POST['associationType'];
                    }
                    if (isset($_POST['private']) && !strcmp($_POST['private'], 'true')) {
                        $model->private = 1;
                    } else {
                        $model->private = 0;
                    }
                    $model->uploadedBy = Yii::app()->user->getName();
                    $model->createDate = time();
                    $model->lastUpdated = time();
                    $model->mimetype = $temp->type;

                    if (!$model->save()) {
                        $errors = $model->getErrors();
                        $error = ArrayUtil::pop(ArrayUtil::pop($errors));
                        Yii::app()->user->setFlash(
                                'top-error', Yii::t('app', 'Attachment failed. ' . $error));
                        if (Auxlib::isAjax()) {
                            return print("success");
                        }
                        $this->redirect(
                                array(
                                    $model->associationType . '/' . $model->associationType .
                                    '/view',
                                    'id' => $model->associationId
                        ));
                        Yii::app()->end();
                    } else {
                        $relatedModel = X2Model::getModelOfTypeWithId($model->associationType, $model->associationId, true);
                        $model->createRelationship($relatedModel);
                    }

                    // handle different upload types
                    switch ($model->associationType) {
                        case 'feed':
                            $this->handleFeedTypeUpload($model, $name);
                            break;
                        case 'docs':
                            if (Auxlib::isAjax()) {
                                return print("success");
                            }
                            $this->redirect(array('/docs/docs/index'));
                            break;
                        case 'loginSound':
                        case 'notificationSound':
                            if (Auxlib::isAjax()) {
                                return print("success");
                            }
                            $this->redirect(
                                    array('/profile/settings', 'id' => Yii::app()->user->getId()));
                            break;
                        case 'bg':
                        case 'bg-private':
                            $this->redirect(
                                    array(
                                        '/profile/settings',
                                        'bgId' => $model->id
                                    )
                            );
                            break;
                        case 'none':
                            if (Auxlib::isAjax()) {
                                return print("success");
                            }
                            break;
                        case 'topicReply':
                            $this->handleTopicReplyUpload($model, $name);
                            break;
                        default:
                            $this->handleDefaultUpload($model, $name);
                            break;
                    }
                }
            } else {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    if (Auxlib::isAjax()) {
                        return print("success");
                    }
                    $this->redirect($_SERVER['HTTP_REFERER']);
                } else {
                    throw new CHttpException('400', 'Invalid request');
                }
            }
            if (isset($_GET['redirect'])) {
                $this->redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function actionUploadFeedPostPicture() {
        
    }

    /**
     * Upload contact profile picture from Facebook.
     */
    public function actionUploadProfilePicture() {
        if (isset($_POST['photourl'])) {
            $photourl = $_POST['photourl'];
            $name = 'profile_picture_' . $_POST['associationId'] . '.jpg';
            $model = new Media;
            $check = Media::model()->findAllByAttributes(array('fileName' => $name));
            if (count($check) != 0) {
                $count = 1;
                $newName = $name;
                $arr = explode('.', $name);
                $name = $arr[0];
                while (count($check) != 0) {
                    $newName = $name . '(' . $count . ').jpg';
                    $check = Media::model()->findAllByAttributes(array('fileName' => $newName));
                    $count++;
                }
                $name = $newName;
            }
            $model->associationId = $_POST['associationId'];
            $model->associationType = $_POST['type'];
            $model->createDate = time();
            $model->fileName = $name;

            // download and save picture
            $img = FileUtil::ccopy($photourl, "uploads/protected/$name");
            $model->save();

            // put picture into new action
            $note = new Actions;
            $note->createDate = time();
            $note->dueDate = time();
            $note->completeDate = time();
            $note->complete = 'Yes';
            $note->visibility = '1';
            $note->completedBy = "Web Lead";
            $note->assignedTo = 'Anyone';
            $note->type = 'attachment';
            $note->associationId = $_POST['associationId'];
            $note->associationType = $_POST['type'];

            $association = $this->getAssociation($note->associationType, $note->associationId);
            if ($association != null) {
                $note->associationName = $association->name;
            }
            $note->actionDescription = $model->fileName . ':' . $model->id;
            if ($note->save()) {
                
            } else {
                unlink('uploads/protected/' . $name);
            }
            $this->redirect(array($model->associationType . '/' . $model->associationId));
        }
    }

    /**
     * Index action.
     *
     * This is the default 'index' action that is invoked when an action
     * is not explicitly requested by users.
     */
    //
    public function actionIndex() {
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        if (Yii::app()->user->isGuest) {
            $this->redirectToLogin();
        } else {
            $profile = Yii::app()->params->profile;
            if (Yii::app()->params->isAdmin) {
                $admin = &Yii::app()->settings;
                if (Yii::app()->session['versionCheck'] == false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time())) {
                    Yii::app()->session['alertUpdate'] = true;
                } else {
                    Yii::app()->session['alertUpdate'] = false;
                }
            } else {
                Yii::app()->session['alertUpdate'] = false;
            }
            if (isset($_GET['code']) && isset($_GET['state'])) {
                if ($_GET['redirectedFrom'] === 'dropbox') {
                    Yii::app()->session['dropbox_code'] = $_GET['code'];
                    Yii::app()->session['dropbox_status'] = $_GET['state'];
                } else if ($_GET['redirectedFrom'] === 'linkedIn') {
                    Yii::app()->session['linkedIn_code'] = $_GET['code'];
                    Yii::app()->session['linkedIn_status'] = $_GET['state'];
                }
            }
            if (empty($profile->startPage)) {
                $this->redirect(array('/profile/view', 'id' => Yii::app()->user->getId()));
            } else {
                $controller = Yii::app()->file->set('protected/controllers/' . ucfirst($profile->startPage) . 'Controller.php');
                $module = Yii::app()->file->set('protected/modules/' . $profile->startPage . '/controllers/' . ucfirst($profile->startPage) . 'Controller.php');
                if ($controller->exists || $module->exists) {
                    if ($controller->exists) {
                        $this->redirect(array($profile->startPage . '/index'));
                    }
                    if ($module->exists) {
                        $this->redirect(array($profile->startPage . '/' . $profile->startPage . '/index'));
                    }
                } else {
                    $page = CActiveRecord::model('Docs')->findByAttributes(array('name' => ucfirst($profile->startPage)));
                    if (isset($page)) {
                        $id = $page->id;
                        $this->redirect(array('/docs/docs/view', 'id' => $id, 'static' => 'true'));
                    } else {
                        $this->redirect(array('/site/profile'));
                    }
                }
            }
        }
    }

    function phpinfo_array($return = false) {
        ob_start();
        phpinfo(-1);

        $pi = preg_replace(
                array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
            '#<h1>Configuration</h1>#', "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
            "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
            . '<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
            '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
            "# +#", '#<tr>#', '#</tr>#'), array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
            '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' .
            "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>',
            '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
            '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
            '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'), ob_get_clean());

        $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
        unset($sections[0]);

        $pi = array();
        foreach ($sections as $section) {
            $n = substr($section, 0, strpos($section, '</h2>'));
            preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $askapache, PREG_SET_ORDER);
            foreach ($askapache as $m) {
                if (is_array($m) && count($m) == 4) {
                    if (empty($p[$n])) {
                        $p[$n] = array();
                    }
                    $pi[$n][$m[1]] = (!isset($m[3]) || $m[2] == $m[3]) ? $m[2] : array_slice($m, 2);
                }
            }
        }

        return ($return === false) ? print_r($pi) : $pi;
    }

    /**
     * Error printing.
     *
     * This is the action to handle external exceptions.
     */
    public function actionError() {

        function var_dump_to_string($var) {
            $output = "<pre>";
            _var_dump_to_string($var, $output);
            $output .= "</pre>";
            return $output;
        }

        function _var_dump_to_string($var, &$output, $prefix = "") {
            foreach ($var as $key => $value) {
                if (is_array($value)) {
                    $output .= $prefix . $key . ": \n";
                    _var_dump_to_string($value, $output, "  " . $prefix);
                } else {
                    $output .= $prefix . $key . ": " . $value . "\n";
                }
            }
        }

        function is_disabled($function) {
            $disabled_functions = explode(',', str_replace(" ", "", ini_get('disable_functions')));
            return in_array($function, $disabled_functions);
        }

        if ($error = Yii::app()->errorHandler->error) {
            if ($this->isAjaxRequest()) {
                echo $error['message'];
            } else {
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                if ($error['code'] == '404') {
                    $request = Yii::app()->request->requestUri;
                    if (preg_match('/opportunity/', $request)) {
                        $request = preg_replace('/opportunity/', 'opportunities', $request);
                        $this->redirect($request);
                    }
                    if (empty($referer)) {
                        $this->render('errorDisplay', $error);
                        Yii::app()->end();
                    }
                }
                if (in_array($error['code'], array('403', '400', '503'))) {
                    $this->render('errorDisplay', $error);
                    Yii::app()->end();
                }
                $request = Yii::app()->request->requestUri;
                if (!is_disabled('phpinfo')) {
                    $info = $this->phpinfo_array(true);
                } else {
                    $info = '';
                }
                $email = !empty(Yii::app()->settings->emailFromAddr) ? Yii::app()->settings->emailFromAddr : "";
                $get = var_dump_to_string($_GET);
                $post = var_dump_to_string($_POST);
                $phpversion = phpversion();
                $x2version = Yii::app()->params->version;
                unset($error['traces']);
                $error['trace'] = "{$error['type']} in {$error['file']}({$error['line']})\n{$error['trace']}";
                $error['trace'] = CHtml::encode($error['trace']);
                $phpInfoErrorReport = base64_encode(CJSON::encode(array_merge($error, array(
                            'request' => $request,
                            'phpinfo' => $info,
                            'referer' => $referer,
                            'get' => $get,
                            'post' => $post,
                            'phpversion' => $phpversion,
                            'x2version' => $x2version,
                            'adminEmail' => $email,
                            'user' => Yii::app()->user->getName(),
                            'isAdmin' => Yii::app()->params->isAdmin,
                            'userAgent' => $userAgent,
                ))));

                $errorReport = base64_encode(CJSON::encode(array_merge($error, array(
                            'request' => $request,
                            'referer' => $referer,
                            'get' => $get,
                            'post' => $post,
                            'phpversion' => $phpversion,
                            'x2version' => $x2version,
                            'adminEmail' => $email,
                            'user' => Yii::app()->user->getName(),
                            'isAdmin' => Yii::app()->params->isAdmin,
                            'userAgent' => $userAgent,
                ))));

                $this->render('error', array_merge($error, array(
                    'request' => $request,
                    'info' => $info,
                    'referer' => $referer,
                    'get' => $get,
                    'post' => $post,
                    'phpversion' => $phpversion,
                    'x2version' => $x2version,
                    'errorReport' => $errorReport,
                    'phpInfoErrorReport' => $phpInfoErrorReport,
                )));
            }
        }
    }

    public function actionBugReport() {
        $info = $this->phpinfo_array(true);
        $email = !empty(Yii::app()->settings->emailFromAddr) ? Yii::app()->settings->emailFromAddr : "";
        $phpversion = phpversion();
        $x2version = Yii::app()->params->version;
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $phpInfoErrorReport = base64_encode(CJSON::encode(array(
                    'phpinfo' => $info,
                    'phpversion' => $phpversion,
                    'x2version' => $x2version,
                    'adminEmail' => $email,
                    'user' => Yii::app()->user->getName(),
                    'isAdmin' => Yii::app()->params->isAdmin,
                    'userAgent' => $userAgent,
        )));

        $errorReport = base64_encode(CJSON::encode(array(
                    'phpversion' => $phpversion,
                    'x2version' => $x2version,
                    'adminEmail' => $email,
                    'user' => Yii::app()->user->getName(),
                    'isAdmin' => Yii::app()->params->isAdmin,
                    'userAgent' => $userAgent,
        )));

        $this->render('bugReport', array(
            'phpInfoErrorReport' => $phpInfoErrorReport,
            'errorReport' => $errorReport,
            'x2version' => $x2version,
            'phpversion' => $phpversion,
        ));
    }

    /**
     *  Displays the About page
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
     * Obtains the record association type for an object, i.e. contacts.
     *
     * @param string $type
     * @param integer $id
     * @return mixed
     */
    protected function getAssociation($type, $id) {
        $classes = array(
            'actions' => 'Actions',
            'contacts' => 'Contacts',
            'accounts' => 'Accounts',
            'product' => 'Product',
            'products' => 'Product',
            'Campaign' => 'Campaign',
            'quote' => 'Quote',
            'quotes' => 'Quote',
            'opportunities' => 'Opportunity',
        );

        return (array_key_exists($type, $classes) && $id != 0) ? X2Model::model($classes[$type])->findByPk($id) : null;
    }

    /**
     * View a linked page as an iframe to provide X2CRM context
     */
    public function actionViewEmbedded($id) {
        $model = Modules::model()->findByPk($id);
        if (!$model || !$model->linkOpenInFrame) {
            throw new CHttpException('400', 'Invalid request.');
        }
        $this->render('viewEmbedded', array(
            'title' => $model->title,
            'url' => $model->linkHref,
        ));
    }

    /**
     * View all notifications for the current web user.
     */
    public function actionViewNotifications() {
        $pageSize = Profile::getResultsPerPage();

        $dataProvider = new CActiveDataProvider('Notification', array(
            'criteria' => array(
                // 'order'=>'viewed ASC',
                'condition' => 'user="' . Yii::app()->user->name . '"'
            ),
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            'sort' => array(
                'defaultOrder' => 'createDate DESC'
            ),
        ));
        $this->render('viewNotifications', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionWarning() {
        header("Content-type: image/gif");
        $img = 'R0lGODlhZABQAPcAANgAAP///w';
        for ($i = 0; $i < 203; $i++) {
            $img .= 'AAAAA';
        }
        $img .= 'CwAAAAAZABQAAAI/wABCBxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjwkDiBS5cKRAkiBTKkQJIMBKlwNZqpxJUOZJkzdNjiS5s+VOnDph9qT5EqbPmzGNHk3acilSny6jClVK1CDQqUyzIpUKFaXMn1SrWuXq9CtVnmS9KrUptqBRtE+dLsUKd+7at23dDu2aFCdUpmrB+t2bt7Dhw4gTK57pV+viqmwfG7ZJ+GfOqYOHEn4cmaVnu1vTipYct+bbs5aPovWcWmPYiJ/jUg5bl29kjLcfxi6LNzZctWU3NpYItmvg04GNZ85NfDjptrWfiwUuHfrp6pDvTryKNaRzvWNdo//e3l33a9Mp2TL3HtqsWdPHMd+N/5KvXN0n0T9lfZC6ftCqnXdcafg1pZV/7vl3YHfR6bfXev01xh9vWSkoG4NkgXeZY+ZpCCCFAF63H12vsfYebMhxJ198mF024F/DJfgfdhlJGON5NDan12w54jabejj22FBnJQYpZFERIgShcS5eJ1VqKq5oZHhJVskQXgYaSFd+TWHpZUwQfXffjCGB2WVNSqLJ5ZdTUqnjlWbeeJaaWsa5JHxKijnmWJ8JSGeW8oHZJpnw4QihUGammSigay7qkJ6beahonWsiuuiXjXJ55aD1eZciUHLZ2GKTbeoZJqfY3bnpkZ4K5uqrrbHwWiistAom6624klchizC+aFlQtiKGKaVaWoqlpnNpiupHUfWXn3bEuuXgs5KV2BeazWYpraDFLpueVdiGa+xuZyLqLbPazilttseme6a2iQ1rKaVeXapmvcjmxd+v/Ga43GoBRprrwAQXbPDBCOMqZ6HXtvrkX/f1VNyfPDm60rbEzmukdl/RqTHG5XGqbrTKloRenxZnS/GkJnucL6MXc9tbyurtiHHLiY6r8sbicltps7SxPKixcRb98s1eamY0uM5arOjHMLfbdMlRL73WtE7zaSKeDecJrHIQR3m1oCMnjDPLZsdcZtpst+22QQEBADs=';
        echo base64_decode($img);
    }

    /**
     * Clears remember me cookies and redirects to login page. 
     */
    public function actionForgetMe() {
        $loginForm = new LoginForm;
        foreach (array('username', 'rememberMe', 'sessionToken') as $attr) {
            // Remove the cookie if they unchecked the box
            $cookieName = CHtml::resolveName($loginForm, $attr);
            $cookie = new CHttpCookie($cookieName, '');
            $cookie->expire = time() - 3600; // expire cookie
            Yii::app()->request->cookies[$cookieName] = $cookie;
        }
        $this->redirectToLogin();
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {
        // Checks if app is requested from mobile
        if (Yii::app()->isMobileApp()) {
            $this->redirect('/mobile/login');
        }

        // Verifies ip
        $this->verifyIpAccess($this->getRealIp());

        // Create new login form
        $model = new LoginForm;
        $model->useCaptcha = false;
        if ($this->loginRequiresCaptcha()) {
            $model->useCaptcha = true;
            $model->setScenario('loginWithCaptcha');
        }

        // Fills login form if cookie is still present
        $profile = null;
        if (isset($_COOKIE['LoginForm'])) {
            $model->setAttributes($_COOKIE['LoginForm']);
            if (is_array($_COOKIE['LoginForm']) &&
                    in_array('username', array_keys($_COOKIE['LoginForm']))) {

                $username = $_COOKIE['LoginForm']['username'];
                $profile = Profile::model()->findByAttributes(array(
                    'username' => $username
                ));
                if ($profile) {
                    Yii::app()->params->profile = $profile;
                }
            }
        }

        /*
          $facebook = FacebookBehavior::createFacebookInstance();
          if (!$facebook->checkIfLoggedIn()) {
          printR($facebook->requestAccess(), true);
          }
         *
         */

        // Redirects to home dashboard
        $this->layout = '//layouts/login';
        if (Yii::app()->user->isInitialized && !Yii::app()->user->isGuest) {
            $this->redirect(Yii::app()->homeUrl);
            return;
        }

        // If login form provided, login using form
        if (isset($_POST['LoginForm'])) {
            $this->login($model);
        }

        // Tell windows making ajax requests to redirect
        header('REQUIRES_AUTH: 1');

        // Render login form
        $this->render('login', array(
            'model' => $model,
            'profile' => $profile,
        ));
    }

    /**
     * Test is a user needs two factor auth, and send a verification code if so
     */
    public function actionNeedsTwoFactor() {
        if (!Yii::app()->request->isPostRequest) {
            $this->denied();
        }
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $model = Profile::model()->findByAttributes(array(
            'username' => $username,
        ));
        if ($model && $model->enableTwoFactor) {
            if (!$model->requestTwoFA(true)) {
                throw new CHttpException(500, Yii::t('profile', 'Failed to request two factor authentication code!'));
            } else {
                echo 'yes';
            }
        }
    }

    /**
     * Log in using a Google account.
     */
    public function actionGoogleLogin() {
        $this->layout = '//layouts/login';
        $model = new LoginForm;
        $model->useCaptcha = false;

        // echo var_dump(Session::getOnlineUsers());
        if (Yii::app()->user->isInitialized && !Yii::app()->user->isGuest) {
            $this->redirect(Yii::app()->homeUrl);
            return;
        }
        require_once 'protected/components/GoogleAuthenticator.php';
        $auth = new GoogleAuthenticator();

        $credentials = Yii::app()->settings->getGoogleIntegrationCredentials();
        if ($credentials && Yii::app()->settings->googleIntegration &&
                $token = $auth->getAccessToken()) {

            try {
                $user = $auth->getUserInfo($token);
                $email = filter_var($user->email, FILTER_SANITIZE_EMAIL);
                $profileRecord = X2Model::model('Profile')->findByAttributes(array('googleId' => $email));
                if (!isset($profileRecord)) {
                    $userRecord = X2Model::model('User')->findByAttributes(array('emailAddress' => $email));
                    $profileRecord = X2Model::model('Profile')->findByAttributes(array(), "emailAddress=:email OR googleId=:email", array(':email' => $email));
                }
                if (isset($userRecord) || isset($profileRecord)) {
                    if (!isset($profileRecord)) {
                        $profileRecord = X2Model::model('Profile')->findByPk($userRecord->id);
                    }
                    $auth->storeCredentials($profileRecord->id, $_SESSION['access_token']);
                }
                if (isset($userRecord) || isset($profileRecord)) {
                    if (!isset($userRecord)) {
                        $userRecord = User::model()->findByPk($profileRecord->id);
                    }
                    $username = $userRecord->username;
                    $password = $userRecord->password;
                    $model->username = $username;
                    $model->password = $password;
                    if ($model->login(true)) {
                        $ip = $this->getRealIp();

                        Session::cleanUpSessions();
                        SessionToken::cleanUpSessions();
                        if (isset($_SESSION['sessionId'])) {
                            $sessionId = $_SESSION['sessionId'];
                        } else {
                            $sessionId = $_SESSION['sessionId'] = session_id();
                        }

                        $session = X2Model::model('Session')->findByPk($sessionId);

                        // if this client has already tried to log in, increment their attempt count
                        if ($session === null) {
                            $session = new Session;
                            $session->id = $sessionId;
                            $session->user = $model->getSessionUsername();
                            $session->lastUpdated = time();
                            $session->status = 1;
                            $session->IP = $ip;
                        } else {
                            $session->lastUpdated = time();
                        }

                        $session->save();
                        SessionLog::logSession($userRecord->username, $sessionId, 'googleLogin');
                        $userRecord->login = time();
                        $userRecord->save();
                        Yii::app()->session['versionCheck'] = true;

                        Yii::app()->session['loginTime'] = time();
                        $session->status = 1;

                        if (Yii::app()->user->returnUrl == 'site/index') {
                            $this->redirect(array('/site/index'));
                        } else {
                            $this->redirect(Yii::app()->user->returnUrl);
                        }
                    }
                } else {
                    $this->render('googleLogin', array(
                        'failure' => 'email',
                        'email' => $email,
                        'credentials' => $credentials,
                    ));
                }
            } catch (Google_Auth_Exception $e) {
                $auth->flushCredentials();
                $auth->setErrors($e->getMessage());
                $this->render('googleLogin', array(
                    'failure' => $auth->getErrors(),
                    'credentials' => $credentials,
                ));
            } catch (NoUserIdException $e) {
                $auth->flushCredentials();
                $auth->setErrors($e->getMessage());
                $this->render('googleLogin', array(
                    'failure' => $auth->getErrors(),
                    'credentials' => $credentials,
                ));
            }
        } else {
            $this->render('googleLogin');
        }
    }

    public function actionStoreToken() {
        if (isset($_POST['code'])) {
            $code = $_POST['code'];

            require_once 'protected/integration/Google/google-api-php-client/src/Google/autoload.php';

            $client = new Google_Client();
            $credentials = Yii::app()->settings->getGoogleIntegrationCredentials();
            $client->setClientId($credentials['clientId']);
            $client->setClientSecret($credentials['clientSecret']);
            $client->setRedirectUri('postmessage');
            $client->setAccessType('offline');
            $client->authenticate($code);
            $token = json_decode($client->getAccessToken());
            // Verify the token
            $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' .
                    $token->access_token;
            $req = new Google_Http_Request($reqUrl);

            $tokenInfo = json_decode($client->getAuth()->authenticatedRequest($req)->getResponseBody());
            // If there was an error in the token info, abort.
            if (isset($tokenInfo->error) && $tokenInfo->error) {
                return new Response($tokenInfo->error, 500);
            }
            // Make sure the token we got is for our app.
            if ($tokenInfo->audience != $credentials['clientId']) {
                return new Response(
                        "Token's client ID does not match app's.", 401);
            }

            // Store the token in the session for later use.
            $_SESSION['token'] = json_encode($token);
            $_SESSION['access_token'] = json_encode($token);
            $auth = new GoogleAuthenticator();
            $user = $auth->getUserInfo($client->getAccessToken());
            $email = filter_var($user->email, FILTER_SANITIZE_EMAIL);
            $profileRecord = Profile::model()->findByAttributes(array(), "emailAddress=:email OR googleId=:email", array(':email' => $email));
            if (isset($profileRecord)) {
                $auth->storeCredentials($profileRecord->id, $_SESSION['access_token']);
            }
            $response = 'Successfully connected with token: ' . print_r($token, true);
        } else {
            $response = 'Invalid request.';
        }
        echo $response;
    }

    /**
     * Toggle display of tags.
     */
    public function actionToggleShowTags($tags) {
        if ($tags == 'allUsers') {
            Yii::app()->params->profile->tagsShowAllUsers = true;
            Yii::app()->params->profile->update(array('tagsShowAllUsers'));
        } else if ($tags == 'justMe') {
            Yii::app()->params->profile->tagsShowAllUsers = false;
            Yii::app()->params->profile->update(array('tagsShowAllUsers'));
        }
    }

    /**
     * Adds a tag to a model.
     *
     * Echoes "true" if tag was created (and was not a duplicate), "false" otherwise.
     */
    public function actionAppendTag() {
        if (isset($_POST['Type'], $_POST['Id'], $_POST['Tag']) &&
                preg_match('/^[\w\d_-]+$/', $_POST['Type'])) {

            if (!class_exists($_POST['Type'])) {
                echo 'false';
                return;
            }
            $model = X2Model::model($_POST['Type'])->findByPk($_POST['Id']);

            if ($model === null || !Yii::app()->controller->checkPermissions($model, 'view')) {
                $this->denied();
            }
            echo $model->addTags($_POST['Tag']);
            Yii::app()->end();
        }
        echo 'false';
    }

    /**
     * Removes a tag from a model.
     *
     * Echoes "true" if tag was removed, "false" otherwise.
     */
    public function actionRemoveTag() {
        if (isset($_POST['Type'], $_POST['Id'], $_POST['Tag']) &&
                preg_match('/^[\w\d_-]+$/', $_POST['Type'])) {

            if (!class_exists($_POST['Type'])) {
                echo 'false';
                return;
            }
            $model = X2Model::model($_POST['Type'])->findByPk($_POST['Id']);
            if ($model !== null && $model->removeTags($_POST['Tag'])) {
                echo 'true';
                return;
            }
        }
        echo 'false';
    }

    /**
     * Display a print-friendly version of the x2layout view associated with the specified
     * model class and id.
     */
    public function actionPrintRecord($modelClass, $id, $pageTitle = '') {
        $this->layout = '//layouts/print';

        $modelTitle = '';
        if (preg_match('/: /', $pageTitle)) {
            $modelTitle = preg_replace('/(^.*):\ .*/', '\1', $pageTitle);
            $pageTitle = preg_replace('/^.*:\ /', '', $pageTitle);
        }
        if (isset($id) && isset($modelClass)) {
            //$model = $this->getModel ($id, true, $modelClass);
            $model = CActiveRecord::model($modelClass)->findByPk((int) $id);
            echo $this->render('printableRecord', array(
                'model' => $model,
                'modelClass' => $modelClass,
                'id' => $id,
                'pageTitle' => $pageTitle,
                'modelTitle' => $modelTitle
                    ), true);
            return;
        }
    }

    public function actionCreateRecords() {
        $contact = new Contacts;
        $account = new Accounts;
        $opportunity = new Opportunity;
        $users = User::getNames();

        if (isset($_POST['Contacts']) && isset($_POST['Accounts']) && isset($_POST['Opportunity'])) {
            $contact->setX2Fields($_POST['Contacts']);
            $account->setX2Fields($_POST['Accounts']);
            $opportunity->setX2Fields($_POST['Opportunity']);

            $validAccount = true;

            if ($account->validate() == false) {
                $validAccount = false;
                // validate other models so that the user gets feedback
                $contact->validate();
                $opportunity->validate();
            }

            if ($validAccount) {
                $allValid = true;
                $a = $this->createAccount($account, $account->attributes, '1');

                // Contact and Opportunity require Account id for lookup field
                $contact->company = Fields::nameId($account->name, $account->id);
                if ($contact->validate() == false) {
                    $allValid = false;
                }
                $c = $this->createContact($contact, $contact->attributes, '1');

                $opportunity->accountName = Fields::nameId($account->name, $account->id);
                $opportunity->contactName = Fields::nameId($contact->name, $contact->id);
                if ($opportunity->validate() == false) {
                    $allValid = false;
                }
                $o = $this->createOpportunity($opportunity, $opportunity->attributes, '1');

                if ($allValid && $c && $a && $o) { // all records created?
                    $contact->createRelationship($account);
                    $opportunity->createRelationship($contact);
                    $opportunity->createRelationship($account);

                    if (isset($_GET['ret'])) {
                        if ($_GET['ret'] == 'contacts') {
                            $this->redirect(array(
                                "/contacts/contacts/view",
                                'id' => $contact->id
                            ));
                        } else if ($_GET['ret'] == 'accounts') {
                            $this->redirect(array(
                                "/accounts/accounts/view",
                                'id' => $account->id
                            ));
                        } else if ($_GET['ret'] == 'opportunities') {
                            $this->redirect(array(
                                "/opportunities/opportunities/view",
                                'id' => $opportunity->id
                            ));
                        }
                    } else {
                        $this->redirect(array(
                            "/contacts/contacts/view",
                            $contact->id
                        ));
                    }
                } else {
                    // otherwise clean up
                    $types = array(
                        'account' => 'Accounts',
                        'contact' => 'Contacts',
                        'opportunity' => 'Opportunity',
                    );
                    foreach ($types as $model => $type) {
                        if (${$model} && isset(${$model}->id)) {
                            $modelId = ${$model}->id;
                            ${$model}->delete();

                            // delete all new actions and events from creating/deleting records
                            foreach (array('Actions', 'Events') as $meta) {
                                X2Model::model($meta)->deleteAllByAttributes(array(
                                    'associationId' => $modelId,
                                    'associationType' => $type,
                                ));
                            }
                        }
                    }
                }
            }
        }

        $this->render('createRecords', array(
            'contact' => $contact,
            'account' => $account,
            'opportunity' => $opportunity,
            'users' => $users,
        ));
    }

    /**
     * Creates contact record
     *
     * Call this function from createRecords
     */
    public function createContact($model, $oldAttributes, $api) {
        $model->createDate = time();
        $model->lastUpdated = time();
        if (empty($model->visibility) && $model->visibility != 0) {
            $model->visibility = 1;
        }
        if ($api == 0) {
            parent::create($model, $oldAttributes, $api);
        } else {
            $lookupFields = Fields::model()->findAllByAttributes(array('modelName' => 'Contacts', 'type' => 'link'));
            foreach ($lookupFields as $field) {
                $fieldName = $field->fieldName;
                if (isset($model->$fieldName)) {
                    $lookup = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $model->$fieldName));
                    if (isset($lookup)) {
                        $model->$fieldName = $lookup->id;
                    }
                }
            }
            return parent::create($model, $oldAttributes, $api);
        }
    }

    /**
     * Creates account record
     *
     * Call this function from createRecords
     */
    public function createAccount($model, $oldAttributes, $api) {

        $model->annualRevenue = Formatter::parseCurrency($model->annualRevenue, false);
        $model->createDate = time();
        if ($api == 0) {
            parent::create($model, $oldAttributes, $api);
        } else {
            return parent::create($model, $oldAttributes, $api);
        }
    }

    /**
     * Creates opportunity record
     *
     * Call this function from createRecords
     */
    public function createOpportunity($model, $oldAttributes, $api = 0) {

        // process currency into an INT
        // $model->quoteAmount = Formatter::parseCurrency($model->quoteAmount,false);

        if (isset($model->associatedContacts)) {
            $model->associatedContacts = Opportunity::parseContacts($model->associatedContacts);
        }
        $model->createDate = time();
        $model->lastUpdated = time();
        // $model->expectedCloseDate = Formatter::parseDate($model->expectedCloseDate);
        if ($api == 1) {
            return parent::create($model, $oldAttributes, $api);
        } else {
            parent::create($model, $oldAttributes, '0');
        }
    }

    /**
     * Checks for any tasks that need to be executed at a specific time
     *
     * Needs to be called by a cronjob.
     */
    // public function actionCron() {
    // $emails = X2Model::model('Actions')->findByAttributes(array(
    // 'type'=>'email_staged',
    // 'dueDate'=>'<'.time(),
    // 'complete'=>'No'
    // ));
    // }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        $user = User::model()->findByPk(Yii::app()->user->getId());
        if (isset($user)) {
            $user->lastLogin = time();
            $user->save();

            if (isset($_SESSION['sessionId'])) {
                SessionLog::logSession($user->username, $_SESSION['sessionId'], 'logout');
                X2Model::model('Session')->deleteByPk($_SESSION['sessionId']);
            } else {
                X2Model::model('Session')->deleteAllByAttributes(array('IP' => $this->getRealIp()));
            }
        }
        if (isset($_SESSION['access_token'])) {
            unset($_SESSION['access_token']);
        }

        unset(Yii::app()->request->cookies['sessionToken']);
        /* $login = new LoginForm;
          foreach(array('username', 'rememberMe') as $attr){
          // Remove the cookie if they unchecked the box
          AuxLib::clearCookie(CHtml::resolveName($login, $attr));
          } */

        Yii::app()->user->logout();

        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionToggleVisibility() {
        $id = $_SESSION['sessionId'];
        $session = Session::model()->findByAttributes(array('id' => $id));
        if (isset($session)) {
            if ($session->status < 0) {
                $session->status = 0;
            }
            $session->status = !$session->status;
            $session->save();
            SessionLog::logSession(Yii::app()->user->getName(), $id, $session->status ? "visible" : "invisible");
        }
        $this->redirect($_GET['redirect']);
    }

    /**
     *  Remove a right widget from the page and put it in the hidden widgets menu
     */
    public function actionHideWidget() {
        if (isset($_POST['name']) && isset($_POST['position'])) {
            $name = $_POST['name'];
            $position = $_POST['position'];

            $layout = Yii::app()->params->profile->getLayout();
            if (isset($layout[$position][$name])) {

                $layout['hiddenRight'][$name] = $layout[$position][$name];
                unset($layout[$position][$name]);
                Yii::app()->params->profile->saveLayout($layout);
            }

            echo Yii::app()->params->profile->getWidgetMenu();
        }
    }

    public function actionShowWidget() {
        if (isset($_POST['name']) && isset($_POST['block'])) { // ensure we have the params we need
            $name = $_POST['name'];
            $block = $_POST['block'];

            if (isset($_POST['moduleName'])) {
                $moduleName = $_POST['moduleName'];
            } else {
                $moduleName = '';
            }

            if (isset($_POST['modelType']) && isset($_POST['modelId'])) {
                $modelType = $_POST['modelType'];
                $modelId = $_POST['modelId'];
            }

            $layout = Yii::app()->params->profile->getLayout();

            if ($block == 'right') { // x2temp: remove when $layout['hiddenRight'] is merged into $layout['hidden']
                foreach ($layout['hiddenRight'] as $key => $widget) {
                    if ($key == $name) {
                        $widget['minimize'] = false; // un-minimize widgets when we show them
                        $layout[$block][$key] = $widget;
                        unset($layout['hiddenRight'][$key]);
                        Yii::app()->params->profile->saveLayout($layout);
                        Yii::app()->session['fullscreen'] = false; // we just added a widget to the right sidebar, so turn off fullscreen mode
                        // Yii::app()->clientScript->scriptMap['*.js'] = false;
                        // $this->renderPartial('application.components.views.centerWidget', array('widget'=>$widget, 'name'=>$name, 'modelType'=>$modelType, 'modelId'=>$modelId), false, true);

                        break;
                    }
                }
            }
        }
    }

    public function actionReorderWidgets() {
        if (isset($_POST['x2widget']) && isset($_POST['x2widget'])) {
            $widgets = $_POST['x2widget']; // list of widgets
            $block = $_POST['block']; // left, right, or center
            $layout = Yii::app()->params->profile->getLayout();

            if ($block === 'left') {

                $newOrder = array();

                // remove ordered left widgets from the layout and prepend them to the list
                // of left widgets in the new order
                foreach ($widgets as $name) {
                    foreach ($layout[$block] as $key => $widget) {
                        if ($key == $name) {
                            $newOrder[$key] = $widget;
                            unset($layout[$block][$key]);
                        }
                    }
                }
                $layout[$block] = $newOrder + $layout[$block];
            } else {
                $newOrder = array();

                foreach ($widgets as $name) {
                    foreach ($layout[$block] as $key => $widget) {
                        if ($key == $name) {
                            $newOrder[$key] = $widget;
                        }
                    }
                }

                $layout[$block] = $newOrder;
                Yii::app()->params->profile->saveLayout($layout);
            }
            Yii::app()->params->profile->saveLayout($layout);
        }
    }

    function actionMinimizeWidget() {
        if (isset($_POST['name']) && isset($_POST['minimize'])) {
            $name = $_POST['name'];
            $minimize = json_decode($_POST['minimize']);
            $layout = Yii::app()->params->profile->getLayout();

            // the widget could be in any of the blocks in the page, so check all of them
            foreach ($layout as &$block) {
                foreach ($block as $key => &$widget) {
                    if ($key == $name) {
                        $widget['minimize'] = $minimize;
                        Yii::app()->params->profile->saveLayout($layout);
                        break 2;
                    }
                }
            }
        }
    }

    public function resetPasswordHelper($id, $title) {
        $scenario = 'new';
        $message = Yii::t('app', 'Enter the email address associated with your user account to request a new password and username reminder.');
        $request = new PasswordReset;
        $resetForm = null;

        if (isset($_POST['PasswordReset'])) {
            // Submitting a password reset request
            $request->setAttributes($_POST['PasswordReset']);
            if ($request->save()) {
                $request->setScenario('afterSave');
                if (!$request->validate(array('email'))) {
                    // Create a new model. It is done this way (adding the
                    // validation error to a new model) so that there is a trail
                    // of reset request attempts that can be counted to determine
                    // if the user has made too many.
                    $oldRequest = $request;
                    $request = new $request;
                    $request->setAttributes($oldRequest->getAttributes(array('email')), false);
                    $request->addErrors($oldRequest->getErrors());
                } else {
                    // A user with the corresponding email was found. Attempt to
                    // send the email and whatever happens, don't display the
                    // form again.
                    $scenario = 'message';
                    $mail = new EmailDeliveryBehavior();
                    $mail->credId = Credentials::model()->getDefaultUserAccount(
                            Credentials::$sysUseId['systemNotificationEmail'], 'email');

                    // Compose the message & headers
                    $message = Yii::t('users', "You have requested to reset the password for user {user} in {appName}.", array(
                                '{user}' => $request->user->alias,
                                '{appName}' => Yii::app()->settings->appName
                    ));
                    $message .= ' ' .
                            Yii::t('users', "To finish resetting your password, please open the following link: ");
                    $message .= "<br /><br />" .
                            $this->createAbsoluteUrl('/site/resetPassword') . '?' .
                            http_build_query(array('id' => $request->id));
                    $message .= "<br /><br />" .
                            Yii::t('users', "If you did not make this request, please disregard this email.");

                    $recipients = array(
                        'to' => array(
                            array('', $request->email)
                        )
                    );

                    // Send the email
                    $status = $mail->deliverEmail(
                            $recipients, Yii::app()->settings->appName . " password reset", $message);

                    // Set the response message accordingly.
                    if ($status['code'] == 200) {
                        $title = Yii::t('users', 'Almost Done!');
                        $message = Yii::t('users', 'Check your email at {email} for '
                                        . 'further instructions to finish resetting your password.', array('{email}' => $request->email));
                    } else {
                        $title = Yii::t('users', 'Could not send email.');
                        $message = Yii::t(
                                        'users', 'Sending of the password reset verification email failed with ' .
                                        'message: {message}', array(
                                    '{message}' => $status['message']
                        ));
                    }
                }
            } else if ($request->limitReached) {
                $scenario = 'message';
                $message = Yii::t('app', 'You have made too many requests to reset passwords. ' .
                                'Please wait one hour before trying again.');
            }
        } else if ($id !== null) {
            // User might have arrived here through the link in a reset email.
            $scenario = 'apply';
            $request = PasswordReset::model()->findByPk($id);
            if ($request instanceof PasswordReset && !$request->isExpired) {
                // Reset request record exists.
                $user = $request->user;
                if ($user instanceof User) {
                    // ...and is valid (points to an existing user)
                    //
                    // Default message: the password entry form (initial request)
                    $message = Yii::t(
                                    'users', 'Enter a new password for user "{user}" ({name}):', array(
                                '{user}' => $user->alias,
                                '{name}' => CHtml::encode($user->firstName . ' ' . $user->lastName)
                    ));
                    $resetForm = new PasswordResetForm($user);
                    if (isset($_POST['PasswordResetForm'])) {
                        // Handle the form submission:
                        $resetForm->setAttributes($_POST['PasswordResetForm']);
                        if ($resetForm->save()) {
                            // Done, success.
                            $scenario = 'message';
                            $title = Yii::t('users', 'Password Has Been Reset');
                            $message = Yii::t(
                                            'users', 'You should now have access as "{user}" with the new ' .
                                            'password specified.', array('{user}' => $user->alias));
                        }
                    }
                } else {
                    // Invalid request record; it does not correspond to an
                    // existing user, i.e. it's an "attempt" (entering an email
                    // address to see if that sticks).
                    $scenario = 'message';
                    $title = Yii::t('users', 'Access Denied');
                    $message = Yii::t('users', 'Invalid reset key.');
                }
            } else {
                $scenario = 'message';
                $title = Yii::t('users', 'Access Denied');
                if ($request->isExpired) {
                    $message = Yii::t('users', 'The password reset link has expired.');
                } else {
                    $message = Yii::t('users', 'Invalid reset link.');
                }
            }
        }
        return compact('scenario', 'title', 'message', 'request', 'resetForm');
    }

    /**
     * Reset a user's password via a really basic email verification process
     *
     * @param type $id ID/key of the password recovery record
     */
    public function actionResetPassword($id = null) {
        if (!Yii::app()->user->isGuest) {
            $this->redirect(array('/profile/changePassword', 'id' => Yii::app()->user->id));
        }
        $this->layout = '//layouts/login';
        $title = Yii::t('app', 'Reset Password');
        $this->pageTitle = $title;
        $loginRoute = '/site/login';
        extract($this->resetPasswordHelper($id, $title));
        $this->render(
                'resetPassword', compact(
                        'scenario', 'title', 'message', 'request', 'resetForm', 'loginRoute'));
    }

    /**
     * Prints a very brief help page
     */
    public function actionAnonHelp() {
        $this->layout = '//layouts/login';
        $this->render('anonHelp');
    }

    public function actionDuplicateCheck(
    $moduleName, $modelName, $id = null, $ref = 'view', $showAll = false) {

        if (empty($id)) {
            $model = X2Model::model($modelName);
            $attributes = Yii::app()->user->getState('json_attributes');
            $model->attributes = json_decode($attributes, true);
        } else {
            $model = X2Model::model($modelName)->findByPk($id);
        }
        if ($model->asa('DuplicateBehavior')) {
            $this->render('duplicateCheck', array(
                'count' => $model->countDuplicates(),
                'newRecord' => $model,
                'duplicates' => $model->getDuplicates($showAll),
                'ref' => $ref,
                'modelName' => $modelName,
                'moduleName' => $moduleName,
            ));
        } else {
            
        }
    }

    public function actionResolveDuplicates() {
        if (isset($_POST['action'], $_POST['ref'], $_POST['modelName'])) {
            $action = $_POST['action'];
            $ref = $_POST['ref'];
            $modelName = $_POST['modelName'];
            $id = null;
            // Keep this tells us to do nothing, but we still need to mark the
            // original model as having been checked for duplicates
            if ($action === 'keepThis' && isset($_POST['data'])) {
                $attributes = json_decode($_POST['data'], true);
                if ($ref !== 'create') {
                    $model = $modelName::model()->findByPk($attributes['id']);
                    $model->duplicateChecked();
                    $id = $model->id;
                } else {
                    $model = new $modelName;
                    // If we want to keep a newly created record, we have to finish creation
                    foreach ($attributes as $key => $value) {
                        if ($key !== 'id') {
                            $model->$key = $value;
                        }
                    }
                    $model->save();
                    $model->duplicateChecked();
                    $id = $model->id;
                }
            } elseif ($action === 'ignoreNew' && isset($_POST['data']) && $ref !== 'create') {
                $attributes = json_decode($_POST['data'], true);
                $model = X2Model::model($modelName)->findByPk($attributes['id']);
                $model->markAsDuplicate();
            } elseif (($action === 'deleteNew' || ($action === 'ignoreNew' && $ref === 'create')) && isset($_POST['data'])) {
                if ($ref === 'create') {
                    return;
                } elseif (isset($_POST['data'])) {
                    $attributes = json_decode($_POST['data'], true);
                    $model = X2Model::model($modelName)->findByPk($attributes['id']);
                    $model->markAsDuplicate('delete');
                }
            } elseif ($action === 'mergeRecords' && isset($_POST['data'])) {
                $attributes = json_decode($_POST['data'], true);
                if ($ref == 'create') {
                    $model = new $modelName;
                    foreach ($attributes as $key => $value) {
                        if ($key !== 'id') {
                            $model->$key = $value;
                        }
                    }
                    $model->save();
                    $id = $model->id;
                } else {
                    $id = $attributes['id'];
                    $model = X2Model::model($modelName)->findByPk($id);
                }
                $duplicates = $model->getDuplicates(true);
                $idArray = array($id);
                foreach ($duplicates as $dupe) {
                    $idArray[] = $dupe->id;
                }
                echo http_build_query(array('idArray' => $idArray));
                return;
            } elseif (empty($action) && isset($_POST['data'])) {
                $attributes = json_decode($_POST['data'], true);
                $model = X2Model::model($modelName)->findByPk($attributes['id']);
            }
            // Modifier determines additional steps to take like hiding or deleting other records
            if (isset($_POST['modifier'], $model)) {
                switch ($_POST['modifier']) {
                    case 'hideAll':
                        $model->hideDuplicates();
                        break;
                    case 'deleteAll':
                        $model->deleteDuplicates();
                        break;
                    case 'hideThis':
                        $model->markAsDuplicate();
                        break;
                    case 'deleteThis':
                        $model->markAsDuplicate('delete');
                        break;
                }
            }
            echo $id;
        }
    }

    public function actionGetSkypeLink(array $usernames) {
        echo X2Html::renderSkypeLink($usernames);
    }

    public function actionMergeRecords($modelName) {
        $idArray = filter_input(INPUT_GET, 'idArray', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $data = filter_input(INPUT_POST, $modelName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($data)) {
            $fields = X2Model::model($modelName)->getFields(true);
            $models = array();
            $model = new $modelName;
            foreach ($idArray as $id) {
                $models[$id] = X2Model::model($modelName)->findByPk($id);
            }
            $model->setMergedCreateDate($models);
            foreach ($data as $fieldName => $value) {
                $field = $fields[$fieldName];
                if (!empty($value)) {
                    if ($field->type === 'text') {
                        $model->$fieldName = $value;
                    } else {
                        $model->$fieldName = $models[$value]->$fieldName;
                        if ($field->uniqueConstraint) {
                            $models[$value]->$fieldName = null;
                            $models[$value]->update(array($fieldName));
                        }
                    }
                }
            }
            $missingFields = array_diff(array_keys($model->attributes), array_keys($data));
            foreach ($missingFields as $attr) {
                if (in_array($attr, array_keys($fields)) && !in_array($attr, $model->MergeableBehavior->restrictedFields)) {
                    $model->MergeableBehavior->setMergedField($fields[$attr], $models);
                }
            }

            if ($model->hasAttribute('visibility') && is_null($model->visibility)) {
                $model->visibility = 1;
            }
            if ($model->hasAttribute('dupeCheck')) {
                $model->dupeCheck = 1;
            }
            if ($model->save()) {
                $model->massMergeRelatedRecords($models, true);
                $this->redirect(array(
                    strtolower(X2Model::getModuleName($modelName)) . '/view', 'id' => $model->id));
            } else {
                /**/printR($model->getErrors(), true);
            }
        } else {
            if (!empty($idArray)) {
                if (!Yii::app()->user->checkAccess(
                                X2Model::getModuleName($modelName) . 'Update')) {
                    $this->denied();
                }
                $model = X2Model::model($modelName)->findByPk($idArray[0]);
                if (isset($model)) {
                    $this->render('mergeRecords', array(
                        'model' => $model,
                        'modelName' => $modelName,
                        'idArray' => $idArray,
                    ));
                }
            }
        }
    }

    /**
     * Save model attributes POSTed by inline edit form.
     */
    public function actionAjaxSave() {
        if (isset($_POST['modelId'], $_POST['attributes'])) {
            $attributes = array();
            $modelName = null;
            foreach ($_POST['attributes'] as $attr => $val) {
                $pieces = explode('[', $attr);
                if (is_null($modelName)) {
                    $modelName = $pieces[0];
                }
                $attribute = str_replace(']', '', $pieces[1]);
                $attributes[$attribute] = $val;
            }

            $model = X2Model::model($modelName)->findByPk($_POST['modelId']);

            if (isset($model) && Yii::app()->controller->checkPermissions($model, 'edit')) {
                $model->setX2Fields($attributes);
                if ($model->save()) {
                    $retArr = array();
                    foreach (array_intersect_key(
                            $model->attributes, $attributes) as $attr => $key) {

                        $retArr[$modelName . '_' . $attr] = $model->renderAttribute(
                                $attr, true, false);
                    }
                    echo CJSON::encode(array(
                        'updatedFields' => $retArr
                    ));
                } else {
                    $errorMessages = $model->getAllErrorMessages();
                    $errorMessages['header'] = Yii::t(
                                    'app', '{modelName} could not be updated:', array(
                                '{modelName}' => $model->getDisplayName(false),
                    ));
                    echo CJSON::encode(array(
                        'errors' => $errorMessages,
                    ));
                }
            }
        }
    }

    /**
     * Action called to mark a tour (tip) as seen
     * @param  int $id ID of the tip
     */
    public function actionTourSeen($id) {
        Tours::model()->updateByPk($id, array('seen' => true));

        echo 'success';
    }

    /**
     * Gets a preview of a layout form for the form editor
     * @param  $_POST[modelName] Name of the model to preview
     * @param  $_POST[layout]  JSON Encoded layout  to preview
     * @return  Echoes out HTML
     */
    public function actionLayoutPreview() {
        $modelName = $_POST['modelName'];
        $layout = CJSON::decode($_POST['layout']);

        $model = new $modelName;
        $config = array(
            'model' => $model,
            'layoutData' => $layout,
            'scenario' => 'Inline',
            'formSettings' => array(),
        );
        echo '<div id="preview-form">';
        $this->widget('FormView', $config);
        echo '</div>';

        echo '<div id="preview-view">';
        $this->widget('DetailView', $config);
        echo '</div>';
    }

}
