<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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
 * Primary/default controller for the web application.
 *
 * @package X2CRM.controllers
 */
class SiteController extends x2base {

    // Declares class-based actions.
    //public $layout = '//layouts/main';

    public $modelClass = 'Admin';
    public $portlets = array();

    public function filters(){
        return array(
            'setPortlets',
            'accessControl',
        );
    }

    protected function beforeAction($action = null){
        if(is_int(Yii::app()->locked) && 
           !Yii::app()->user->checkAccess('GeneralAdminSettingsTask') && 
           !(in_array($this->action->id,array('login','logout')) || 
             Yii::app()->user->isGuest)) {

            $this->appLockout();
        }
        return true;
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
                    'Updater' => array(
                        'class' => 'application.components.UpdaterBehavior',
                        'isConsole' => false,
                    ),
                ));
    }

    public function accessRules(){
        return array(
            array('allow',
                'actions' => array('login', 'forgetMe', 'index', 'logout', 'warning', 'captcha', 'googleLogin', 'error', 'storeToken', 'sendErrorReport'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('groupChat', 'newMessage', 'getMessages', 'checkNotifications', 'updateNotes', 'addPersonalNote',
                    'getNotes', 'getURLs', 'addSite', 'deleteMessage', 'fullscreen', 'widgetState', 'widgetOrder', 'saveGridviewSettings', 'saveFormSettings',
                    'saveWidgetHeight', 'inlineEmail', 'tmpUpload', 'upload', 'uploadProfilePicture', 'index', 'contact',
                    'viewNotifications', 'inlineEmail', 'toggleShowTags', 'appendTag', 'removeTag', 'addRelationship', 'printRecord', 'createRecords',
                    'toggleVisibility', 'page', 'showWidget', 'hideWidget', 'reorderWidgets', 'minimizeWidget', 'publishPost', 'getEvents', 'loadComments',
                    'loadPosts', 'addComment', 'flagPost', 'broadcastEvent', 'minimizePosts',
                    'bugReport', 'deleteRelationship', 'minMaxLeftWidget', 'toggleFeedControls', 'toggleFeedFilters',
                    'getTip', 'share', 'activityFeedOrder', 'activityFeedWidgetBgColor', 'likePost', 'loadLikeHistory',
                    'dynamicDropdown', 'stickyPost', 'getEventsBetween', 'mediaWidgetToggle', 'createChartSetting',
                    'deleteChartSetting', 'GetActionsBetweenAction', 'DeleteURL'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('motd'),
                'users' => array('admin'),
            ),
            array('deny',
                'users' => array('*')
            )
        );
    }

    public function actions(){
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
                'testLimit' => 1,
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
        );
    }

//    /**
//     * Obtain the widget list for the current web user.
//     *
//     * @param CFilterChain $filterChain
//     */
//    public function filterSetPortletsq($filterChain){
//        if(!Yii::app()->user->isGuest){
//            $this->portlets=array();
//            $this->portlets = ProfileChild::getWidgets();
//            // $this->portlets=array();
//            // $arr=ProfileChild::getWidgets(Yii::app()->user->getId());
//
//            // foreach($arr as $key=>$value){
//                // $config=ProfileChild::parseWidget($value,$key);
//                // $this->portlets[$key]=$config;
//            // }
//        }
//        $filterChain->run();
//    }

    public function actionSendErrorReport(){
        if(isset($_POST['report'])){
            $errorReport = $_POST['report'];
            if(isset($_POST['email'])){
                $errorReport = unserialize(base64_decode($errorReport));
                $errorReport['email'] = $_POST['email'];
                $errorReport = base64_encode(serialize($errorReport));
            }
            if(isset($_POST['bugDescription'])){
                $errorReport = unserialize(base64_decode($errorReport));
                $errorReport['bugDescription'] = $_POST['bugDescription'];
                $errorReport = base64_encode(serialize($errorReport));
            }
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


    public function actionActivityFeedOrder(){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $profile->activityFeedOrder = !$profile->activityFeedOrder;
            $profile->update(array('activityFeedOrder'));
        }
    }

    public function actionActivityFeedWidgetBgColor($color){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $theme = $profile->theme;
            $theme['activityFeedWidgetBgColor'] = $color;
            $profile->theme = $theme;
            $profile->update(array('theme'));
        }
    }

    public function actionMediaWidgetToggle(){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $profile->mediaWidgetDrive = !$profile->mediaWidgetDrive;
            $profile->update(array('mediaWidgetDrive'));
        }
    }

    // Outputs white or black depending on input color
    // @param $colorString a string representing a hex number
    // @param $testType standardText or linkText
    function convertTextColor($colorString, $textType){
        // Split the string to red, green and blue components
        // Convert hex strings into ints
        $red = intval(substr($colorString, 0, 2), 16);
        $green = intval(substr($colorString, 2, 2), 16);
        $blue = intval(substr($colorString, 4, 2), 16);
        if($textType == 'standardText'){
            return (((($red * 299) + ($green * 587) + ($blue * 114)) / 1000) >= 128) ? 'black' : 'white';
        }else if($textType == 'linkText'){
            if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
                return '#fff000';  // Yellow links
            }
            else
                return '#0645AD'; // Blue link color
        }
        else if($textType == 'visitedLinkText'){
            if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
                return '#ede100';  // Yellow links
            }
            else
                return '#0B0080'; // Blue link color
        }
        else if($textType == 'activeLinkText'){
            if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
                return '#fff000';  // Yellow links
            }
            else
                return '#0645AD'; // Blue link color
        }
        else if($textType == 'hoverLinkText'){
            if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
                return '#fff761';  // Yellow links
            }
            else
                return '#3366BB'; // Blue link color
        }
    }

    public function actionGetTip(){
        //opensource or pro
        $edition = yii::app()->params->admin->edition;
        //True or False
        $admin = Yii::app()->params->isAdmin;
        //Check user type and editon to deliever an appropriate tip
        if($edition == 'pro'){
            if($admin){
                $where = 'edition = "pro" OR edition = "opensource"';
            }else{
                $where = 'admin = 0';
            }
        }else if($admin){
            $where = 'edition = "opensource"';
        }else{
            $where = 'admin = 0 AND edition = "opensource"';
        }
        $tip = Yii::app()->db->createCommand()
                ->select('*')
                ->from('x2_tips')
                ->where($where)
                ->order('rand()')
                ->queryRow();
        echo json_encode($tip);
    }

    public function actionDynamicDropdown($val, $dropdownId, $field = false, $module = null){
        $dropdown = X2Model::model('Dropdowns')->findByAttributes(array('parent' => $dropdownId, 'parentVal' => $val));
        if(isset($dropdown)){
            if(!$field){
                echo CHtml::tag('option', array('value' => ''), CHtml::encode('-'), true);
                $data = json_decode($dropdown->options, true);
                foreach($data as $value => $name){
                    echo CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                }
            }else{
                $fieldRecord = X2Model::model('Fields')->findByAttributes(array('modelName' => $module, 'type' => 'dependentDropdown', 'linkType' => $dropdownId));
                if(isset($fieldRecord)){ // Look up dependentDropdown field with a link to the master dropdown.
                    $htmlStr = CHtml::tag('option', array('value' => ''), CHtml::encode('Select an option'), true);
                    $data = json_decode($dropdown->options, true);
                    foreach($data as $value => $name){ // Build an HTML string of the dropdown response.
                        $htmlStr .= CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                    }
                    echo CJSON::encode(array($fieldRecord->fieldName, $htmlStr)); // Echo back the field name to update + the dropdown HTMl.
                }
            }
        }else{
            if(!$field){
                echo CHtml::tag('option', array('value' => ''), '-', true);
            }else{
                $fieldRecord = X2Model::model('Fields')->findByAttributes(array('modelName' => $module, 'type' => 'dependentDropdown', 'linkType' => $dropdownId));
                if(isset($fieldRecord))
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
    public function actionMinMaxLeftWidget ($action, $widgetName) {
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $layout = $profile->getLayout ();
            $minimize;
            if ($action === 'expand') {
                $minimize = false;
            } else if ($action === 'collapse') {
                $minimize = true;
            } else {
                echo 'failure';
                return;
            }
            if (in_array ($widgetName, array_keys ($layout['left']))) {
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
    public function actionMotd(){
        if(isset($_POST['message'])){
            $motd = $_POST['message'];
            $temp = Social::model()->findByAttributes(array('type' => 'motd'));
            $temp->data = $motd;
            if($temp->update())
                echo $motd;
            else
                echo "An error has occured.";
        }else{
            echo "An error has occured.";
        }
    }

    /**
     * Renders the group chat.
     */
    public function actionGroupChat(){
        $this->portlets = array();
        $this->layout = '//layouts/column1';
        //$portlets = $this->portlets;
        // display full screen group chat
        $this->render('groupChat');
    }

    /**
     * Creates a new chat message from the current web user.
     */
    public function actionNewMessage(){
        if(isset($_POST['chat-message']) && $_POST['chat-message'] != ''
                && $_POST['chat-message'] != Yii::t('app', 'Enter text here...')){

            $user = Yii::app()->user->getName();
            $chat = new Social;
            $chat->data = $_POST['chat-message'];
            ;
            $chat->user = $user;
            $chat->visibility = 1;
            $chat->timestamp = time();
            $chat->type = 'chat';

            if($chat->save()){
                echo CJSON::encode(array(
                    array(
                        $chat->id,
                        date('g:i:s A', $chat->timestamp),
                        '<span class="my-username">'.$chat->user.'</span>',
                        $this->convertUrls($chat->data)
                    )
                ));
            }
        }
    }

    /**
     * Add a personal note to the list of notes for the current web user.
     */
    public function actionAddPersonalNote(){
        if(isset($_POST['note-message']) && $_POST['note-message'] != ''){
            $user = Yii::app()->user->getName();
            $note = new Social;
            $note->associationId = Yii::app()->user->getId();
            $note->data = $_POST['note-message'];
            ;
            $note->user = $user;
            $note->visibility = 1;
            $note->timestamp = time();
            $note->type = 'note';

            if($note->save()){
                echo "1";
            }
        }
    }

    /**
     * Adds a new URL
     */
    public function actionAddSite(){
        if((isset($_POST['url-title']) && isset($_POST['url-url'])) && ($_POST['url-title'] != '' && $_POST['url-url'] != '')){
            $site = new URL;
            $site->title = $_POST['url-title'];
            $site->url = $_POST['url-url'];
            $site->userid = Yii::app()->user->getId();
            $site->timestamp = time();
            if($site->save()){
                echo CJSON::encode (array (
                    CHtml::link(
                        Yii::t('app', $site->title), $site->url, array('target'=>'_blank')),
                    CHtml::link(
                        '[x]',
                        array('/site/DeleteURL', 'id' => $site->id),
                        array (
                            'title' => Yii::t('app', 'Delete Link'),
                            'class' => 'delete-top-site-link',
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
    public function actionGetNotes($url){
        $content = Social::model()->findAllByAttributes(array('type' => 'note', 'associationId' => Yii::app()->user->getId()), array(
            'order' => 'timestamp DESC',
                ));
        $res = "";
        foreach($content as $item){
            $res .= $this->convertUrls($item->data)." ".CHtml::link('[x]', array('/site/deleteMessage', 'id' => $item->id, 'url' => $url)).'<br /><br />';
        }
        if($res == ""){
            $res = Yii::t('app', "Feel free to enter some notes!");
        }
        echo $res;
    }

    public function actionDeleteURL($id){
        if(isset($id)){
            Yii::app()->db->createCommand()->delete(
                'x2_urls', 'id=:id', array(':id' => $id));
        }
    }

    public function actionEditURL($id, $url)
    {
        //$entry->title = 'ggg';
        //$this->list = array('item1','item2');
        $this->redirect($url);
    }
    /**
     * Gets URLs for "top sites"
     * @param string $url
     */
    /*public function actionGetURLs($url){
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
    }*/

    /**
     * Delete a message from the social feed.
     * @param integer $id
     * @param string $url
     */
    public function actionDeleteMessage($id, $url){
        $note = Social::model()->findByPk($id);
        if(isset($note))
            $note->delete();
        $this->redirect($url);
    }

    /**
     * Sets "Fullscreen" mode for the current web user / session
     */
    public function actionFullscreen(){
        Yii::app()->session['fullscreen'] = (isset($_GET['fs']) && $_GET['fs'] == 1);
        $profile = Yii::app()->params->profile;
        $profile->fullscreen = (isset($_GET['fs']) && $_GET['fs'] == 1);
        $profile->update(array('fullscreen'));
        // echo var_dump(Yii::app()->session['fullscreen']);
        echo 'Success';
    }

    public function actionDeleteRelationship($firstId, $firstType, $secondId, $secondType){
        $rel = X2Model::model('Relationships')->findByAttributes(array('firstId' => $firstId, 'firstType' => $firstType, 'secondId' => $secondId, 'secondType' => $secondType));
        if(isset($rel)){
            $rel->delete();
        }else{
            $rel = X2Model::model('Relationships')->findByAttributes(array('firstId' => $secondId, 'firstType' => $secondType, 'secondId' => $firstId, 'secondType' => $firstType));
            if(isset($rel)){
                $rel->delete();
            }
        }
        if(isset($_GET['redirect'])){
            $this->redirect($this->createUrl($_GET['redirect']));
        }
    }

    /**
     * Checks for the widget's state.
     */
    public function actionWidgetState(){

        if(isset($_GET['widget']) && isset($_GET['state'])){
            $widgetName = $_GET['widget'];
            $widgetState = ($_GET['state'] == 0) ? '0' : '1';

            // $profile = Yii::app()->params->profile;

            $order = explode(":", Yii::app()->params->profile->widgetOrder);
            $visibility = explode(":", Yii::app()->params->profile->widgets);

            // var_dump($order);
            // var_dump($visibility);
            if(array_key_exists($widgetName, Yii::app()->params->registeredWidgets)){

                $pos = array_search($widgetName, $order);
                $visibility[$pos] = $widgetState;
                // die(var_dump($visibility));

                Yii::app()->params->profile->widgets = implode(':', $visibility);

                if(Yii::app()->params->profile->update(array('widgets'))){
                    echo 'success';
                }
            }
        }
    }

    /**
     * Responds with the order of widgets for the current user.
     */
    public function actionWidgetOrder(){
        if(isset($_POST['widget'])){

            $widgetList = $_POST['widget'];

            // $profile = Yii::app()->params->profile;
            $order = Yii::app()->params->profile->widgetOrder;
            $visibility = Yii::app()->params->profile->widgets;

            $order = explode(":", $order);
            $visibility = explode(":", $visibility);

            $newOrder = array();

            foreach($widgetList as $item){
                if(array_key_exists($item, Yii::app()->params->registeredWidgets))
                    $newOrder[] = $item;
            }
            $str = "";
            $visStr = "";
            foreach($newOrder as $item){
                $pos = array_search($item, $order);
                $vis = $visibility[$pos];
                $str.=$item.":";
                $visStr.=$vis.":";
            }
            $str = substr($str, 0, -1);
            $visStr = substr($visStr, 0, -1);

            Yii::app()->params->profile->widgetOrder = $str;
            Yii::app()->params->profile->widgets = $visStr;

            if(Yii::app()->params->profile->save()){
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
    public function actionSaveGridviewSettings(){
        $result = false;

        // gv settings parameter is prefixed by a unique id
        $gvSettings;
        foreach ($_GET as $key => $val) {
            if (preg_match ("/gvSettings$/", $key )) {
                $gvSettings = json_decode($val, true);
            }
        }

        if(isset ($gvSettings) && isset($_GET['viewName'])){
            if(isset($gvSettings))
                $result = ProfileChild::setGridviewSettings($gvSettings, $_GET['viewName']);
        }
        if($result)
            echo '200 Success';
        else
            echo '400 Failure';
    }

    /**
     * Save settings for a custom form layout.
     *
     * @throws CHttpException
     */
    public function actionSaveFormSettings(){
        $result = false;
        if(isset($_GET['formSettings']) && isset($_GET['formName'])){
            $formSettings = json_decode($_GET['formSettings'], true);

            if(isset($formSettings))
                $result = ProfileChild::setFormSettings($formSettings, $_GET['formName']);
        }
        if($result)
            echo 'success';
        else
            throw new CHttpException(400, 'Invalid request. Probabaly something wrong with the JSON string.');
    }

    /**
     * Saves the height of a widget.
     */
    public function actionSaveWidgetHeight(){
        if(isset($_POST['Widget']) && isset($_POST['Height'])){
            $heights = $_POST['Height'];
            $widget = $_POST['Widget'];
            $widgetSettings = ProfileChild::getWidgetSettings();

            foreach($heights as $key => $height){
                $widgetSettings->$widget->$key = intval($height);
            }

            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->update();
        }
    }

    /**
     * Uploads a file to a temporary folder.
     *
     * Upload a file to a temp folder, which will presumably be deleted shortly thereafter
     * Temp files are stored in a temp folder with a randomly generated name. They are stored
     * in 'uploads/media/temp'
     */
    public function actionTmpUpload(){
        if(isset($_FILES['upload'])){
            $upload = CUploadedFile::getInstanceByName('upload');

            if($upload){

                $name = $upload->getName();
                $name = str_replace(' ', '_', $name);

                $temp = TempFile::createTempFile($name);

                if($temp && $upload->saveAs($temp->fullpath())) // temp file saved
                    echo json_encode(array('status' => 'success', 'id' => $temp->id, 'name' => $name));
                else
                    echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file.')));
            } else{
                echo json_encode(array('status' => 'notsent', 'message' => Yii::t('media', 'File was not sent to server.')));
            }
        }else{
            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file.')));
        }
    }

    private function handleDefaultUpload ($model, $name) {
        $note = new Actions;
        $note->createDate = time();
        $note->dueDate = time();
        $note->completeDate = time();
        $note->complete = 'Yes';
        $note->visibility = '1';
        $note->completedBy = Yii::app()->user->getName();
        if($model->private){
            $note->assignedTo = Yii::app()->user->getName();
            $note->visibility = '0';
        }else{
            $note->assignedTo = 'Anyone';
        }
        $note->type = 'attachment';
        $note->associationId = $_POST['associationId'];
        $note->associationType = $_POST['associationType'];

        $association = $this->getAssociation($note->associationType, $note->associationId);
        if($association != null)
            $note->associationName = $association->name;

        $note->actionDescription = $model->fileName.':'.$model->id;
        if($note->save()){

        }else{
            unlink('uploads/'.$name);
        }
        if($model->associationType == 'product')
            $this->redirect(array('/products/products/view','id'=>$model->associationId));
        $this->redirect(array($model->associationType.'/'.$model->associationType.'/view','id'=>$model->associationId));

    }
 
    /**
     * @param object $model
     * @param string $name
     */
    private function handleFeedTypeUpload ($model, $name) {
        $event = new Events;
        $event->user = Yii::app()->user->getName();
        if(isset($_POST['attachmentText']) && !empty($_POST['attachmentText'])){
            $event->text = $_POST['attachmentText'];
        }else{
            $event->text = Yii::t('app', 'Attached file: ');
        }
        $event->type = 'media';
        $event->timestamp = time();
        $event->lastUpdated = time();
        $event->associationId = $model->id;
        $event->associationType = 'Media';
        if($event->save()){
            //$this->redirect('profile');
        }else{
            unlink('uploads/'.$name);
        }

        if (AuxLib::isMobile ()) {

            $this->redirect (array('/mobile/site/activity'));
        } else {
            if (isset ($_POST['profileId'])) {
                $this->redirect (array('/profile/view', 'id' => $_POST['profileId']));
            } else {
                $this->redirect (array('/profile/view', 'id' => Yii::app()->user->getId()));
            }
        }
    }

    /**
     * Remove a temp file and the temp folder that is in.
     */
    public function actionRemoveTmpUpload(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];
            if(is_numeric($id)){
                $tempFile = TempFile::model()->findByPk($id);
                $folder = $tempFile->folder;
                $name = $tempFile->name;
                if(file_exists('uploads/media/temp/'.$folder.'/'.$name))
                    unlink('uploads/media/temp/'.$folder.'/'.$name); // delete file
                if(file_exists('uploads/media/temp/'.$folder))
                    rmdir('uploads/media/temp/'.$folder); // delete folder
                $tempFile->delete(); // delete database entry tracking temp file
            }
        }
    }

    /**
     * Upload a file.
     */
    public function actionUpload(){
        if(isset($_FILES['upload'])){
            if(isset($_POST['drive']) && $_POST['drive']){ // google drive
                $auth = new GoogleAuthenticator();
                if($auth->getAccessToken()){
                    $service = $auth->getDriveService();
                }
                $createdFile = null;
                if(isset($service, $_SESSION['access_token'], $_FILES['upload'])){
                    try{
                        $file = new Google_DriveFile();
                        $file->setTitle($_FILES['upload']['name']);
                        $file->setDescription('Uploaded by X2CRM');
                        $file->setMimeType($_FILES['upload']['type']);

                        $data = file_get_contents($_FILES['upload']['tmp_name']);
                        $createdFile = $service->files->insert($file, array(
                            'data' => $data,
                            'mimeType' => $_FILES['upload']['type'],
                                ));
                        if(is_array($createdFile)){
                            $model = new Media;
                            $model->fileName = $createdFile['id'];
                            $model->title = $createdFile['title'];
                            if(isset($_POST['associationId']))
                                $model->associationId = $_POST['associationId'];
                            if(isset($_POST['associationType']))
                                $model->associationType = $_POST['associationType'];
                            if(isset($_POST['private']))
                                $model->private = $_POST['private'];
                            $model->uploadedBy = Yii::app()->user->getName();
                            $model->mimetype = $createdFile['mimeType'];
                            $model->filesize = $createdFile['fileSize'];
                            $model->drive = 1;
                            $model->save();
                            if($model->associationType == 'feed'){
                                $event = new Events;
                                $event->user = Yii::app()->user->getName();
                                if(isset($_POST['attachmentText']) && !empty($_POST['attachmentText'])){
                                    $event->text = $_POST['attachmentText'];
                                }else{
                                    $event->text = Yii::t('app', 'Attached file: ');
                                }
                                $event->type = 'media';
                                $event->timestamp = time();
                                $event->lastUpdated = time();
                                $event->associationId = $model->id;
                                $event->associationType = 'Media';
                                $event->save();
                                $this->redirect (array('/profile/view', 'id' => Yii::app()->user->getId()));
                            }elseif($model->associationType == 'docs'){
                                $this->redirect(array('/docs/docs/index'));
                            }elseif(!empty($model->associationType) && !empty($model->associationId)){
                                $note = new Actions;
                                $note->createDate = time();
                                $note->dueDate = time();
                                $note->completeDate = time();
                                $note->complete = 'Yes';
                                $note->visibility = '1';
                                $note->completedBy = Yii::app()->user->getName();
                                if($model->private){
                                    $note->assignedTo = Yii::app()->user->getName();
                                    $note->visibility = '0';
                                }else{
                                    $note->assignedTo = 'Anyone';
                                }
                                $note->type = 'attachment';
                                $note->associationId = $_POST['associationId'];
                                $note->associationType = $_POST['associationType'];

                                $association = $this->getAssociation($note->associationType, $note->associationId);
                                if($association != null)
                                    $note->associationName = $association->name;

                                $note->actionDescription = $model->fileName.':'.$model->id;
                                if($note->save()){
                                    $this->redirect(array($model->associationType.'/'.$model->associationId));
                                }
                            }else{
                                $this->redirect('/media/media/view',array('id'=>$model->id));
                            }
                        }else{
                            throw new CHttpException('400', 'Invalid request.');
                        }
                    }catch(Google_AuthException $e){
                        $auth->flushCredentials();
                        $auth->setErrors($e->getMessage());
                        $service = null;
                        $createdFile = null;
                    }
                }else{
                    if(isset($_SERVER['HTTP_REFERER'])){
                        $this->redirect($_SERVER['HTTP_REFERER']);
                    }else{
                        throw new CHttpException('400', 'Invalid request');
                    }
                }
            }else{ // non-google drive upload
                $model = new Media;
                $temp = CUploadedFile::getInstanceByName('upload'); // file uploaded through form
                if(isset($temp)){
                    $name = $temp->getName();
                    $name = str_replace(' ', '_', $name);
                    $check = Media::model()->findAllByAttributes(array('fileName' => $name));

                    // rename file if there name conflicts by suffixing "(n)"
                    if(count($check) != 0){
                        $count = 1;
                        $newName = $name;
                        $arr = explode('.', $name);
                        $name = $arr[0];
                        while(count($check) != 0){
                            $newName = $name.'('.$count.').'.$temp->getExtensionName();
                            $check = Media::model()->findAllByAttributes(array('fileName' => $newName));
                            $count++;
                        }
                        $name = $newName;
                    }

                    $username = Yii::app()->user->name;

                    // copy file to user's media uploads directory
                    if(FileUtil::ccopy($temp->getTempName(), "uploads/media/$username/$name")){
                        if(isset($_POST['associationId']))
                            $model->associationId = $_POST['associationId'];
                        if(isset($_POST['associationType']))
                            $model->associationType = $_POST['associationType'];
                        if(isset($_POST['private']))
                            $model->private = $_POST['private'];
                        $model->uploadedBy = Yii::app()->user->getName();
                        $model->createDate = time();
                        $model->lastUpdated = time();
                        $model->fileName = $name;
                        if($model->save()){
                        }

                        // handle different upload types
                        switch ($model->associationType) {
                            case 'feed':
                                $this->handleFeedTypeUpload ($model, $name);
                                break;
                            case 'docs':
                                $this->redirect(array('/docs/docs/index'));
                                break;
                            case 'loginSound':
                            case 'notificationSound':
                                $this->redirect(
                                    array('/profile/settings', 'id' => Yii::app()->user->getId()));
                                break;
                            case 'bg':
                            case 'bg-private':
                                $this->redirect(
                                    array('/profile/settings', 'id' => Yii::app()->user->getId()));
                                break;
                            default:
                                $this->handleDefaultUpload ($model, $name);
                                break;
                        }
                    }
                }else{
                    if(isset($_SERVER['HTTP_REFERER'])){
                        $this->redirect($_SERVER['HTTP_REFERER']);
                    }else{
                        throw new CHttpException('400', 'Invalid request');
                    }
                }
            }
        }else{
            throw new CHttpException('400', 'Invalid request.');
        }
    }

    /**
     * Upload contact profile picture from Facebook.
     */
    public function actionUploadProfilePicture(){
        if(isset($_POST['photourl'])){
            $photourl = $_POST['photourl'];
            $name = 'profile_picture_'.$_POST['associationId'].'.jpg';
            $model = new Media;
            $check = Media::model()->findAllByAttributes(array('fileName' => $name));
            if(count($check) != 0){
                $count = 1;
                $newName = $name;
                $arr = explode('.', $name);
                $name = $arr[0];
                while(count($check) != 0){
                    $newName = $name.'('.$count.').jpg';
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
            $img = FileUtil::ccopy($photourl, "uploads/$name");
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
            if($association != null){
                $note->associationName = $association->name;
            }
            $note->actionDescription = $model->fileName.':'.$model->id;
            if($note->save()){

            }else{
                unlink('uploads/'.$name);
            }
            $this->redirect(array($model->associationType.'/'.$model->associationId));
        }
    }

    /**
     * Index action.
     *
     * This is the default 'index' action that is invoked when an action
     * is not explicitly requested by users.
     */
    //
    public function actionIndex(){
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        // check if we are on a mobile browser
        if(isset($_GET['mobile']) && $_GET['mobile'] == 'false'){
            $cookie = new CHttpCookie('x2mobilebrowser', 'false'); // create cookie
            $cookie->expire = time() + 31104000; // expires in 1 year
            Yii::app()->request->cookies['x2mobilebrowser'] = $cookie; // save cookie
        }else{
            $mobileBrowser = Yii::app()->request->cookies->contains('x2mobilebrowser') ? Yii::app()->request->cookies['x2mobilebrowser']->value : '';
            if($mobileBrowser == 'true')
                $this->redirect(array('/mobile/site/index'));
        }

        if (Yii::app()->user->isGuest) {
            $this->redirect(array('/site/login'));
        } else {
            $profile = Yii::app()->params->profile;
            if(Yii::app()->params->isAdmin){
                $admin = &Yii::app()->params->admin;
                if(Yii::app()->session['versionCheck'] == false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time()))
                    Yii::app()->session['alertUpdate'] = true;
                else
                    Yii::app()->session['alertUpdate'] = false;
            }else{
                Yii::app()->session['alertUpdate'] = false;
            }

            if(empty($profile->startPage)){
                $this->redirect (array('/profile/view', 'id' => Yii::app()->user->getId()));
            }else{
                $controller = Yii::app()->file->set('protected/controllers/'.ucfirst($profile->startPage).'Controller.php');
                $module = Yii::app()->file->set('protected/modules/'.$profile->startPage.'/controllers/'.ucfirst($profile->startPage).'Controller.php');
                if($controller->exists || $module->exists){
                    if($controller->exists)
                        $this->redirect(array($profile->startPage.'/index'));
                    if($module->exists)
                        $this->redirect(array($profile->startPage.'/'.$profile->startPage.'/index'));
                } else{
                    $page = CActiveRecord::model('Docs')->findByAttributes(array('name' => ucfirst($profile->startPage)));
                    if(isset($page)){
                        $id = $page->id;
                        $this->redirect(array('/docs/docs/view','id'=>$id,'static'=>'true'));
                    }else{
                        $this->redirect(array('/site/profile'));
                    }
                }
            }
        }
    }

    function phpinfo_array($return = false){
        ob_start();
        phpinfo(-1);

        $pi = preg_replace(
                array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
            '#<h1>Configuration</h1>#', "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
            "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
            .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
            '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
            "# +#", '#<tr>#', '#</tr>#'), array('$1', '', '', '', '</$1>'."\n", '<', ' ', ' ', ' ', '', ' ',
            '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
            "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
            '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
            '<tr><td>Zend Engine</td><td>$2</td></tr>'."\n".
            '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'), ob_get_clean());

        $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
        unset($sections[0]);

        $pi = array();
        foreach($sections as $section){
            $n = substr($section, 0, strpos($section, '</h2>'));
            preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $askapache, PREG_SET_ORDER);
            foreach($askapache as $m)
                if(is_array($m) && count($m) == 4){
                    if(empty($p[$n]))
                        $p[$n] = array();
                    $pi[$n][$m[1]] = (!isset($m[3]) || $m[2] == $m[3]) ? $m[2] : array_slice($m, 2);
                }
        }

        return ($return === false) ? print_r($pi) : $pi;
    }

    /**
     * Error printing.
     *
     * This is the action to handle external exceptions.
     */
    public function actionError(){

        function var_dump_to_string($var){
            $output = "<pre>";
            _var_dump_to_string($var, $output);
            $output .= "</pre>";
            return $output;
        }

        function _var_dump_to_string($var, &$output, $prefix = ""){
            foreach($var as $key => $value){
                if(is_array($value)){
                    $output.= $prefix.$key.": \n";
                    _var_dump_to_string($value, $output, "  ".$prefix);
                }else{
                    $output.= $prefix.$key.": ".$value."\n";
                }
            }
        }

        function is_disabled($function){
            $disabled_functions = explode(',', str_replace(" ", "", ini_get('disable_functions')));
            return in_array($function, $disabled_functions);
        }

        if($error = Yii::app()->errorHandler->error){
            if(Yii::app()->request->isAjaxRequest){
                echo $error['message'];
            }else{
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                if($error['code'] == '404'){
                    $request = Yii::app()->request->requestUri;
                    if(preg_match('/opportunity/', $request)){
                        $request = preg_replace('/opportunity/', 'opportunities', $request);
                        $this->redirect($request);
                    }
                    if(preg_match('/id/', $request)){
                        $request = preg_replace('/id\//', '', $request);
                        $this->redirect($request);
                    }
                    if(empty($referer)){
                        $this->render('errorDisplay', $error);
                        Yii::app()->end();
                    }
                }
                if(in_array($error['code'],array('403','400','503'))){
                    $this->render('errorDisplay', $error);
                    Yii::app()->end();
                }
                $request = Yii::app()->request->requestUri;
                if(!is_disabled('phpinfo')){
                    $info = $this->phpinfo_array(true);
                }else{
                    $info = '';
                }
                if(!empty(Yii::app()->params->admin->emailFromAddr))
                    $email = Yii::app()->params->admin->emailFromAddr;
                else
                    $email = "";
                $get = var_dump_to_string($_GET);
                $post = var_dump_to_string($_POST);
                $phpversion = phpversion();
                $x2version = Yii::app()->params->version;
                unset($error['traces']);
                $error['trace'] = CHtml::encode($error['trace']);
                $phpInfoErrorReport = base64_encode(serialize(array_merge($error, array(
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

                $errorReport = base64_encode(serialize(array_merge($error, array(
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

    public function actionBugReport(){

        $info = $this->phpinfo_array(true);
        if(!empty(Yii::app()->params->admin->emailFromAddr))
            $email = Yii::app()->params->admin->emailFromAddr;
        else
            $email = "";
        $phpversion = phpversion();
        $x2version = Yii::app()->params->version;
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $phpInfoErrorReport = base64_encode(serialize(array(
                    'phpinfo' => $info,
                    'phpversion' => $phpversion,
                    'x2version' => $x2version,
                    'adminEmail' => $email,
                    'user' => Yii::app()->user->getName(),
                    'isAdmin' => Yii::app()->params->isAdmin,
                    'userAgent' => $userAgent,
                )));

        $errorReport = base64_encode(serialize(array(
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
    public function actionContact(){
        $model = new ContactForm;
        if(isset($_POST['ContactForm'])){
            $model->attributes = $_POST['ContactForm'];
            if($model->validate()){
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
    protected function getAssociation($type, $id){

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

        if(array_key_exists($type, $classes) && $id != 0)
            return X2Model::model($classes[$type])->findByPk($id);
        else
            return null;
    }

    /**
     * View all notifications for the current web user.
     */
    public function actionViewNotifications(){
        $dataProvider = new CActiveDataProvider('Notification', array(
                    'criteria' => array(
                        // 'order'=>'viewed ASC',
                        'condition' => 'user="'.Yii::app()->user->name.'"'
                    ),
                    'sort' => array(
                        'defaultOrder' => 'createDate DESC'
                    ),
                ));
        $this->render('viewNotifications', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionWarning(){
        header("Content-type: image/gif");
        $img = 'R0lGODlhZABQAPcAANgAAP///w';
        for($i = 0; $i < 203; $i++)
            $img .= 'AAAAA';
        $img .= 'CwAAAAAZABQAAAI/wABCBxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjwkDiBS5cKRAkiBTKkQJIMBKlwNZqpxJUOZJkzdNjiS5s+VOnDph9qT5EqbPmzGNHk3acilSny6jClVK1CDQqUyzIpUKFaXMn1SrWuXq9CtVnmS9KrUptqBRtE+dLsUKd+7at23dDu2aFCdUpmrB+t2bt7Dhw4gTK57pV+viqmwfG7ZJ+GfOqYOHEn4cmaVnu1vTipYct+bbs5aPovWcWmPYiJ/jUg5bl29kjLcfxi6LNzZctWU3NpYItmvg04GNZ85NfDjptrWfiwUuHfrp6pDvTryKNaRzvWNdo//e3l33a9Mp2TL3HtqsWdPHMd+N/5KvXN0n0T9lfZC6ftCqnXdcafg1pZV/7vl3YHfR6bfXev01xh9vWSkoG4NkgXeZY+ZpCCCFAF63H12vsfYebMhxJ198mF024F/DJfgfdhlJGON5NDan12w54jabejj22FBnJQYpZFERIgShcS5eJ1VqKq5oZHhJVskQXgYaSFd+TWHpZUwQfXffjCGB2WVNSqLJ5ZdTUqnjlWbeeJaaWsa5JHxKijnmWJ8JSGeW8oHZJpnw4QihUGammSigay7qkJ6beahonWsiuuiXjXJ55aD1eZciUHLZ2GKTbeoZJqfY3bnpkZ4K5uqrrbHwWiistAom6624klchizC+aFlQtiKGKaVaWoqlpnNpiupHUfWXn3bEuuXgs5KV2BeazWYpraDFLpueVdiGa+xuZyLqLbPazilttseme6a2iQ1rKaVeXapmvcjmxd+v/Ga43GoBRprrwAQXbPDBCOMqZ6HXtvrkX/f1VNyfPDm60rbEzmukdl/RqTHG5XGqbrTKloRenxZnS/GkJnucL6MXc9tbyurtiHHLiY6r8sbicltps7SxPKixcRb98s1eamY0uM5arOjHMLfbdMlRL73WtE7zaSKeDecJrHIQR3m1oCMnjDPLZsdcZtpst+22QQEBADs=';
        echo base64_decode($img);
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
        $this->redirect (array ('login'));
    }

    /**
     * Displays the login page
     */
    public function actionLogin(){
        $this->layout = '//layouts/login';
        if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest){
            $this->redirect(Yii::app()->homeUrl);
            return;
        }

        $model = new LoginForm;
        $model->useCaptcha = false;

        if(isset($_POST['LoginForm'])){
            $model->attributes = $_POST['LoginForm']; // get user input data
            if($model->rememberMe){
                foreach(array('username','rememberMe') as $attr) {
                    // Expires in 30 days
                    AuxLib::setCookie (CHtml::resolveName ($model, $attr), $model->$attr,
                        2592000);
                }
            }else{
                foreach(array('username','rememberMe') as $attr) {
                    // Remove the cookie if they unchecked the box
                    AuxLib::clearCookie(CHtml::resolveName($model, $attr));
                }
            }

            Session::cleanUpSessions();

            $ip = $this->getRealIp();

            // increment count on every session with this user/IP, to prevent brute force attacks using session_id spoofing or whatever
            Yii::app()->db->createCommand('UPDATE x2_sessions SET status=status-1, lastUpdated=:time WHERE user=:name AND IP=:ip AND status BETWEEN -2 AND 0')
                    ->bindValues(array(':time' => time(), ':name' => $model->username, ':ip' => $ip))
                    ->execute();

            $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                    ->select('username')
                    ->from('x2_users')
                    ->where('username=:name AND status=1', array(':name' => $model->username))
                    ->limit(1)
                    ->queryScalar(); // get the correctly capitalized username

            if($activeUser === false){
                $model->verifyCode = ''; // clear captcha code
                $model->addError('username', Yii::t('app', 'Incorrect username or password.'));
                $model->addError('password', Yii::t('app', 'Incorrect username or password.'));
            }else{
                $model->username = $activeUser;

                if(isset($_SESSION['sessionId']))
                    $sessionId = $_SESSION['sessionId'];
                else
                    $sessionId = null;

                $session = X2Model::model('Session')->findByPk($sessionId);

                // if this client has already tried to log in, increment their attempt count
                if($session === null){
                    $sessionId = $_SESSION['sessionId'] = session_id();
                    $session = new Session;
                    $session->id = $sessionId;
                    $session->user = $model->username;
                    $session->lastUpdated = time();
                    $session->status = 0;
                    $session->IP = $ip;
                }else{
                    $session->lastUpdated = time();
                    if($session->status < -1)
                        $model->useCaptcha = true;
                    if($session->status < -2)
                        $model->setScenario('loginWithCaptcha');
                }

                if($model->validate() && $model->login()){  // user successfully logged in
                    $adminUser = X2Model::model('User')->findByPk(1);
                    if(isset($adminUser) && $adminUser->username == Yii::app()->user->getName())
                        $this->checkUpdates();   // check for updates if admin
                    else
                        Yii::app()->session['versionCheck'] = true; // ...or don't

                    $session->status = 1;
                    $session->save();
                    SessionLog::logSession($model->username, $sessionId, 'login');
                    $_SESSION['playLoginSound'] = true;
                    if(Yii::app()->user->returnUrl == '/site/index')
                        $this->redirect(array('/site/index'));
                    else
                        $this->redirect(Yii::app()->user->returnUrl); // after login, redirect to wherever
                } else{ // login failed
                    $model->verifyCode = ''; // clear captcha code
                    if($model->hasErrors()){
                        $model->addError('username', Yii::t('app', 'Incorrect username or password.'));
                        $model->addError('password', Yii::t('app', 'Incorrect username or password.'));
                    }
                    $session->save();
                }
            }
        }

        header('REQUIRES_AUTH: 1'); // tell windows making AJAX requests to redirect

        $this->render('login', array('model' => $model)); // display the login form
    }

    /**
     * Log in using a Google account.
     */
    public function actionGoogleLogin(){
        $this->layout = '//layouts/login';
        $model = new LoginForm;
        $model->useCaptcha = false;

        // echo var_dump(Session::getOnlineUsers());
        if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest){
            $this->redirect(Yii::app()->homeUrl);
            return;
        }
        require_once 'protected/components/GoogleAuthenticator.php';
        $auth = new GoogleAuthenticator();
        if(Yii::app()->params->admin->googleIntegration && $token = $auth->getAccessToken()){
            try{
                $user = $auth->getUserInfo($token);
                $email = filter_var($user->email, FILTER_SANITIZE_EMAIL);
                $profileRecord = X2Model::model('Profile')->findByAttributes(array('googleId' => $email));
                if(!isset($profileRecord)){
                    $userRecord = X2Model::model('User')->findByAttributes(array('emailAddress' => $email));
                    $profileRecord = X2Model::model('Profile')->findByAttributes(array(), "emailAddress=:email OR googleId=:email", array(':email' => $email));
                }
                if(isset($userRecord) || isset($profileRecord)){
                    if(!isset($profileRecord)){
                        $profileRecord = X2Model::model('Profile')->findByPk($userRecord->id);
                    }
                    $auth->storeCredentials($profileRecord->id, $_SESSION['access_token']);
                }
                if(isset($userRecord) || isset($profileRecord)){
                    if(!isset($userRecord)){
                        $userRecord = User::model()->findByPk($profileRecord->id);
                    }
                    $username = $userRecord->username;
                    $password = $userRecord->password;
                    $model->username = $username;
                    $model->password = $password;
                    if($model->login(true)){
                        $ip = $this->getRealIp();

                        Session::cleanUpSessions();
                        if(isset($_SESSION['sessionId']))
                            $sessionId = $_SESSION['sessionId'];
                        else
                            $sessionId = $_SESSION['sessionId'] = session_id();

                        $session = X2Model::model('Session')->findByPk($sessionId);

                        // if this client has already tried to log in, increment their attempt count
                        if($session === null){
                            $session = new Session;
                            $session->id = $sessionId;
                            $session->user = $model->username;
                            $session->lastUpdated = time();
                            $session->status = 1;
                            $session->IP = $ip;
                        }else{
                            $session->lastUpdated = time();
                        }
                        // x2base::cleanUpSessions();
                        // $session = X2Model::model('Session')->findByAttributes(array('user'=>$userRecord->username,'IP'=>$ip));
                        // if(isset($session)) {
                        // $session->lastUpdated = time();
                        // } else {
                        // $session = new Session;
                        // $session->user = $model->username;
                        // $session->lastUpdated = time();
                        // $session->status = 1;
                        // $session->IP = $ip;
                        // }
                        $session->save();
                        SessionLog::logSession($userRecord->username, $sessionId, 'googleLogin');
                        $userRecord->login = time();
                        $userRecord->save();
                        Yii::app()->session['versionCheck'] = true;

                        Yii::app()->session['loginTime'] = time();
                        $session->status = 1;

                        if(Yii::app()->user->returnUrl == 'site/index')
                            $this->redirect(array('/site/index'));
                        else
                            $this->redirect(Yii::app()->user->returnUrl);
                    } else{
                        print_r($model->getErrors());
                    }
                }else{
                    $this->render('googleLogin', array(
                        'failure' => 'email',
                        'email' => $email,
                    ));
                }
            }catch(Google_AuthException $e){
                $auth->flushCredentials();
                $auth->setErrors($e->getMessage());
                $this->render('googleLogin', array(
                    'failure' => $auth->getErrors(),
                ));
            }catch(NoUserIdException $e){
                $auth->flushCredentials();
                $auth->setErrors($e->getMessage());
                $this->render('googleLogin', array(
                    'failure' => $auth->getErrors(),
                ));
            }
        }else{
            $this->render('googleLogin');
        }
    }

    public function actionStoreToken(){
        $code = file_get_contents('php://input');
        require_once 'protected/extensions/google-api-php-client/src/Google_Client.php';
        $client = new Google_Client();
        $client->setClientId(Yii::app()->params->admin->googleClientId);
        $client->setClientSecret(Yii::app()->params->admin->googleClientSecret);
        $client->setRedirectUri('postmessage');
        $client->setAccessType('offline');
        $client->authenticate($code);
        $token = json_decode($client->getAccessToken());
        // Verify the token
        $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token='.
                $token->access_token;
        $req = new Google_HttpRequest($reqUrl);

        $tokenInfo = json_decode(
                $client::getIo()->authenticatedRequest($req)->getResponseBody());
        // If there was an error in the token info, abort.
        if(isset($tokenInfo->error) && $tokenInfo->error){
            return new Response($tokenInfo->error, 500);
        }
        // Make sure the token we got is for our app.
        if($tokenInfo->audience != Yii::app()->params->admin->googleClientId){
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
        if(isset($profileRecord)){
            $auth->storeCredentials($profileRecord->id, $_SESSION['access_token']);
        }
        $response = 'Successfully connected with token: '.print_r($token, true);
        echo $response;
    }

    /**
     * Toggle display of tags.
     */
    public function actionToggleShowTags($tags){
        if($tags == 'allUsers'){
            Yii::app()->params->profile->tagsShowAllUsers = true;
            Yii::app()->params->profile->update(array('tagsShowAllUsers'));
        }else if($tags == 'justMe'){
            Yii::app()->params->profile->tagsShowAllUsers = false;
            Yii::app()->params->profile->update(array('tagsShowAllUsers'));
        }
    }

    /**
     * Adds a tag to a model.
     *
     * Echoes "true" if tag was created (and was not a duplicate), "false" otherwise.
     */
    public function actionAppendTag(){
        if(isset($_POST['Type'], $_POST['Id'], $_POST['Tag']) && ctype_alpha($_POST['Type'])){
            $model = X2Model::model($_POST['Type'])->findByPk($_POST['Id']);
            echo $model->addTags($_POST['Tag']);
            exit;
            if($model !== null && $model->addTags($_POST['Tag'])){
                echo 'true';
                return;
            }
        }
        echo 'false';
    }

    /**
     * Removes a tag from a model.
     *
     * Echoes "true" if tag was removed, "false" otherwise.
     */
    public function actionRemoveTag(){
        if(isset($_POST['Type'], $_POST['Id'], $_POST['Tag']) && ctype_alpha($_POST['Type'])){
            $model = X2Model::model($_POST['Type'])->findByPk($_POST['Id']);

            if($model !== null && $model->removeTags($_POST['Tag'])){
                echo 'true';
                return;
            }
        }
        echo 'false';
    }

    /**
     * Add a record to record relationship
     *
     * A record can be a contact, opportunity, or account. This function is
     * called via ajax from the Relationships Widget.
     *
     */
    public function actionAddRelationship(){
        //check if relationship already exits
        if(isset($_POST['ModelName']) && isset($_POST['ModelId']) && isset($_POST['RelationshipModelName']) && isset($_POST['RelationshipModelId'])){

            $modelName = $_POST['ModelName'];
            $modelId = $_POST['ModelId'];
            $relationshipModelName = $_POST['RelationshipModelName'];
            $relationshipModelId = $_POST['RelationshipModelId'];

            $relationship = Relationships::model()->findByAttributes(array(
                'firstType' => $_POST['ModelName'],
                'firstId' => $_POST['ModelId'],
                'secondType' => $_POST['RelationshipModelName'],
                'secondId' => $_POST['RelationshipModelId'],
                    ));
            if($relationship){
                echo "duplicate";
                Yii::app()->end();
            }
            $relationship = Relationships::model()->findByAttributes(array(
                'firstType' => $_POST['RelationshipModelName'],
                'firstId' => $_POST['RelationshipModelId'],
                'secondType' => $_POST['ModelName'],
                'secondId' => $_POST['ModelId'],
                    ));
            if($relationship){
                echo "duplicate";
                Yii::app()->end();
            }

            $relationship = new Relationships;
            $relationship->firstType = $_POST['ModelName'];
            $relationship->firstId = $_POST['ModelId'];
            $relationship->secondType = $_POST['RelationshipModelName'];
            $relationship->secondId = $_POST['RelationshipModelId'];
            $relationship->save();
//            if($relationshipModelName == "Contacts"){
//                $results = Yii::app()->db->createCommand("SELECT * from x2_relationships WHERE (firstType='Contacts' AND firstId=$relationshipModelId AND secondType='Accounts') OR (secondType='Contacts' AND secondId=$relationshipModelId AND firstType='Accounts')")->queryAll();
//                if(sizeof($results) == 1){
//                    $model = Contacts::model()->findByPk($relationshipModelId);
//                    if($model){
//                        $model->company = $modelId;
//                        $model->update();
//                    }
//                }
//            }
            echo "success";
            Yii::app()->end();
        }
    }

    /*
    Display a print-friendly version of the x2layout view associated with the specified
    model class and id.
    */
    public function actionPrintRecord ($modelClass, $id, $pageTitle='') {
        if (isset ($id) && isset ($modelClass)) {
            //$model = $this->getModel ($id, true, $modelClass);
            $model = CActiveRecord::model($modelClass)->findByPk((int) $id);
            echo $this->renderPartial ('printableRecord', array(
                'model' => $model,
                'modelClass' => $modelClass,
                'id' => $id,
                'pageTitle' => $pageTitle
            ), true);
            return;
        }
    }

    public function actionCreateRecords(){
        $contact = new Contacts;
        $account = new Accounts;
        $opportunity = new Opportunity;
        $users = User::getNames();

        if(isset($_POST['Contacts']) && isset($_POST['Accounts']) && isset($_POST['Opportunity'])){
            //        var_dump($_POST);
            //        exit();
            $contact->setX2Fields($_POST['Contacts']);
            $account->setX2Fields($_POST['Accounts']);
            $opportunity->setX2Fields($_POST['Opportunity']);

            $allValid = true;

            if($contact->validate() == false)
                $allValid = false;

            if($account->validate() == false)
                $allValid = false;

            if($opportunity->validate() == false)
                $allValid = false;

            if($allValid){
                $c = $this->createContact($contact, $contact->attributes, '1');
                $a = $this->createAccount($account, $account->attributes, '1');
                $o = $this->createOpportunity($opportunity, $opportunity->attributes, '1');



                if($c && $a && $o){ // all records created?
                    $contact->company = $account->id;
                    $contact->update();
                    $opportunity->accountName = $account->id;
                    $opportunity->update();

                    Relationships::create('Contacts', $contact->id, 'Accounts', $account->id);
                    Relationships::create('Opportunity', $opportunity->id, 'Contacts', $contact->id);
                    Relationships::create('Opportunity', $opportunity->id, 'Accounts', $account->id);

                    if(isset($_GET['ret'])){
                        if($_GET['ret'] == 'contacts')
                            $this->redirect(array("/contacts/contacts/view",'id'=>$contact->id));
                        else if($_GET['ret'] == 'accounts')
                            $this->redirect(array("/accounts/accounts/view",'id'=>$account->id));
                        else if($_GET['ret'] == 'opportunities')
                            $this->redirect(array("/opportunities/opportunities/view",'id'=>$opportunity->id));
                    } else{
                        $this->redirect(array("/contacts/contacts/view",$contact->id));
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
    public function createContact($model, $oldAttributes, $api){
        $model->createDate = time();
        $model->lastUpdated = time();
        if(empty($model->visibility) && $model->visibility != 0)
            $model->visibility = 1;
        if($api == 0){
            parent::create($model, $oldAttributes, $api);
        }else{
            $lookupFields = Fields::model()->findAllByAttributes(array('modelName' => 'Contacts', 'type' => 'link'));
            foreach($lookupFields as $field){
                $fieldName = $field->fieldName;
                if(isset($model->$fieldName)){
                    $lookup = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $model->$fieldName));
                    if(isset($lookup))
                        $model->$fieldName = $lookup->id;
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
    public function createAccount($model, $oldAttributes, $api){

        $model->annualRevenue = Formatter::parseCurrency($model->annualRevenue, false);
        $model->createDate = time();
        if($api == 0)
            parent::create($model, $oldAttributes, $api);
        else
            return parent::create($model, $oldAttributes, $api);
    }

    /**
     * Creates opportunity record
     *
     * Call this function from createRecords
     */
    public function createOpportunity($model, $oldAttributes, $api = 0){

        // process currency into an INT
//        $model->quoteAmount = Formatter::parseCurrency($model->quoteAmount,false);

        if(isset($model->associatedContacts))
            $model->associatedContacts = Opportunity::parseContacts($model->associatedContacts);
        $model->createDate = time();
        $model->lastUpdated = time();
        // $model->expectedCloseDate = Formatter::parseDate($model->expectedCloseDate);
        if($api == 1){
            return parent::create($model, $oldAttributes, $api);
        }else{
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
    public function actionLogout(){
        $user = User::model()->findByPk(Yii::app()->user->getId());
        if(isset($user)){
            $user->lastLogin = time();
            $user->save();

            if(isset($_SESSION['sessionId'])){
                SessionLog::logSession($user->username, $_SESSION['sessionId'], 'logout');
                X2Model::model('Session')->deleteByPk($_SESSION['sessionId']);
            }else{
                X2Model::model('Session')->deleteAllByAttributes(array('IP' => $this->getRealIp()));
            }
        }
        if(isset($_SESSION['access_token']))
            unset($_SESSION['access_token']);

        /*$login = new LoginForm;
        foreach(array('username', 'rememberMe') as $attr){
            // Remove the cookie if they unchecked the box
            AuxLib::clearCookie(CHtml::resolveName($login, $attr));
        }*/

        Yii::app()->user->logout();

        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionToggleVisibility(){
        $id = $_SESSION['sessionId'];
        $session = Session::model()->findByAttributes(array('id' => $id));
        if(isset($session)){
            if($session->status < 0)
                $session->status = 0;
            $session->status = !$session->status;
            $session->save();
            SessionLog::logSession(Yii::app()->user->getName(), $id, $session->status ? "visible" : "invisible");
        }
        $this->redirect($_GET['redirect']);
    }

    /**
     *  Remove a widget from the page and put it in the widgets menu
     *
     */
    function actionHideWidget(){
        if(isset($_POST['name'])){
            $name = $_POST['name'];

            $layout = Yii::app()->params->profile->getLayout();

            // the widget could be in any of the blocks in the page, so check all of them
            foreach($layout as $b => &$block){
                if(isset($block[$name])){
                    if($b == 'right'){
                        $layout['hiddenRight'][$name] = $block[$name];
                    }else{
                        $layout['hidden'][$name] = $block[$name];
                    }
                    unset($block[$name]);
                    Yii::app()->params->profile->saveLayout($layout);
                    break;
                }
            }

            // make a list of hidden widgets, using <li>, to send back to the browser
            $list = "";
            foreach($layout['hidden'] as $name => $widget){
                $list .= "<li><span class=\"x2-widget-menu-item\" id=\"$name\">{$widget['title']}</span></li>";
            }
            foreach($layout['hiddenRight'] as $name => $widget){
                $list .= "<li><span class=\"x2-widget-menu-item right\" id=\"$name\">{$widget['title']}</span></li>";
            }

            echo Yii::app()->params->profile->getWidgetMenu();
        }
    }

    function actionShowWidget(){
        if(isset($_POST['name']) && isset($_POST['block'])){ // ensure we have the params we need
            $name = $_POST['name'];
            $block = $_POST['block'];

            if (isset ($_POST['moduleName'])) {
                $moduleName = $_POST['moduleName'];
            } else {
                $moduleName = '';
            }

            if(isset($_POST['modelType']) && isset($_POST['modelId'])){
                $modelType = $_POST['modelType'];
                $modelId = $_POST['modelId'];
            }

            $layout = Yii::app()->params->profile->getLayout();

            if($block == 'right'){ // x2temp: remove when $layout['hiddenRight'] is merged into $layout['hidden']
                foreach($layout['hiddenRight'] as $key => $widget){
                    if($key == $name){
                        $widget['minimize'] = false; // un-minimize widgets when we show them
                        $layout[$block][$key] = $widget;
                        unset($layout['hiddenRight'][$key]);
                        Yii::app()->params->profile->saveLayout($layout);
                        Yii::app()->session['fullscreen'] = false; // we just added a widget to the right sidebar, so turn off fullscreen mode
                        //    Yii::app()->clientScript->scriptMap['*.js'] = false;
                        //    $this->renderPartial('application.components.views.centerWidget', array('widget'=>$widget, 'name'=>$name, 'modelType'=>$modelType, 'modelId'=>$modelId), false, true);

                        break;
                    }
                }
            }else{

                foreach($layout['hidden'] as $key => $widget){
                    if($key == $name){
                        $widget['minimize'] = false; // un-minimize widgets when we show them
                        $layout[$block][$key] = $widget;
                        unset($layout['hidden'][$key]);
                        Yii::app()->params->profile->saveLayout($layout);
                        Yii::app()->clientScript->scriptMap['*.js'] = false;
                        $this->renderPartial('application.components.views.centerWidget',
                            array(
                                'widget' => $widget,
                                'name' => $name,
                                'modelType' => $modelType,
                                'moduleName' => $moduleName,
                                'modelId' => $modelId), false, true);
                        break;
                    }
                }
            }
        }
    }

    function actionReorderWidgets(){
        if(isset($_POST['x2widget']) && isset($_POST['x2widget'])){
            $widgets = $_POST['x2widget']; // list of widgets
            $block = $_POST['block']; // left, right, or center

            $layout = Yii::app()->params->profile->getLayout();

            $newOrder = array();

            foreach($widgets as $name){
                foreach($layout[$block] as $key => $widget){
                    if($key == $name){
                        $newOrder[$key] = $widget;
                    }
                }
            }

            $layout[$block] = $newOrder;
            Yii::app()->params->profile->saveLayout($layout);
        }
    }

    function actionMinimizeWidget(){
        if(isset($_POST['name']) && isset($_POST['minimize'])){
            $name = $_POST['name'];
            $minimize = json_decode($_POST['minimize']);
            $layout = Yii::app()->params->profile->getLayout();

            // the widget could be in any of the blocks in the page, so check all of them
            foreach($layout as &$block){
                foreach($block as $key => &$widget){
                    if($key == $name){
                        $widget['minimize'] = $minimize;
                        Yii::app()->params->profile->saveLayout($layout);
                        break 2;
                    }
                }
            }
        }
    }

    /**
     * Connects to the X2 update servers and sets Yii::app()->session['versionCheck']
     * to true (up to date) or false (not up to date). Also sets Yii::app()->session['newVersion']
     * to the latest version if not up to date.
     *//*
      protected function checkUpdates(){
      if(!file_exists($secImage = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','images',base64_decode(Yii::app()->params->updaterSecurityImage)))))
      return;
      $i = Yii::app()->params->admin->unique_id;
      $v = Yii::app()->params->version;
      $e = Yii::app()->params->admin->edition;
      $context = stream_context_create(array(
      'http' => array('timeout' => 4)  // set request timeout in seconds
      ));

      $updateCheckUrl = 'https://x2planet.com/installs/updates/check?'.http_build_query(compact('i','v'));
      $securityKey = FileUtil::getContents($updateCheckUrl, 0, $context);
      if($securityKey === false)
      return;
      $h = hash('sha512',base64_encode(file_get_contents($secImage)).$securityKey);
      $n = null;
      if(!($e == 'opensource' || empty($e)))
      $n = Yii::app()->db->createCommand()->select('COUNT(*)')->from('x2_users')->queryScalar();

      $newVersion = FileUtil::getContents('https://x2planet.com/installs/updates/check?'.http_build_query(compact('i','v','h','n')),0,$context);
      if(empty($newVersion))
      return;

      if(version_compare($newVersion, $v) > 0 && !in_array($i, array('none', Null))){ // if the latest version is newer than our version and updates are enabled
      Yii::app()->session['versionCheck'] = false;
      Yii::app()->session['newVersion'] = $newVersion;
      } else
      Yii::app()->session['versionCheck'] = true;
      } */
}

