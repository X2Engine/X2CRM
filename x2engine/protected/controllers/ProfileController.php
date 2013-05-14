<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * User profiles controller
 *
 * @package X2CRM.controllers
 */
class ProfileController extends x2base {

    /**
     * @var string The class of the model most often handled by this controller.
     */
    public $modelClass = 'Profile';

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules(){
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array(
                    'index', 'view', 'update', 'search', 'addPost', 'deletePost', 'uploadPhoto', 'profiles',
                    'settings', 'addComment', 'setSound', 'deleteSound', 'setBackground', 'deleteBackground',
                    'changePassword', 'setResultsPerPage', 'hideTag', 'unhideTag', 'resetWidgets', 'updatePost'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function filters(){
        return array(
            'accessControl',
            'setPortlets',
        );
    }

    public function actionHideTag($tag){
        $tag = "#".$tag;
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $hiddenTags = json_decode($profile->hiddenTags, true);
            if(!is_array($hiddenTags))
                $hiddenTags = array();
            if(!in_array($tag, $hiddenTags)){
                array_push($hiddenTags, $tag);
                $profile->hiddenTags = json_encode($hiddenTags);
                $profile->save();
            }
        }
    }

    public function actionUnhideTag($tag){
        $tag = "#".$tag;
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $hiddenTags = json_decode($profile->hiddenTags, true);
            if(!is_array($hiddenTags))
                $hiddenTags = array();
            if(in_array($tag, $hiddenTags)){
                unset($hiddenTags[array_search($tag, $hiddenTags)]);
                $profile->hiddenTags = json_encode($hiddenTags);
                $profile->save();
            }
        }
    }

    public function actionUpdatePost($id){
        $post = Events::model()->findByPk($id);
        if(isset($_POST['Events'])){
            $post->text = $_POST['Events']['text'];
            $post->save();
            $this->redirect(array('site/whatsNew'));
        }
        $commentDataProvider = new CActiveDataProvider('Events', array(
                    'criteria' => array(
                        'order' => 'timestamp ASC',
                        'condition' => "type='comment' AND associationType='Events' AND associationId=$id",
                        )));
        $this->render('updatePost', array(
            'model' => $post,
            'commentDataProvider' => $commentDataProvider
        ));
    }

    /**
     * Deletes a post in the public feed for the current user.
     * @param integer $id
     */
    public function actionDeletePost($id){
        $post = Events::model()->findByPk($id);
        if(isset($post)){
            if($post->type == 'comment'){
                $postParent = X2Model::model('Events')->findByPk($post->associationId);
                $user = ProfileChild::model()->findByPk($postParent->associationId);
            }else{
                $user = ProfileChild::model()->findByPk($post->associationId);
            }
            if(isset($postParent) && $post->user != Yii::app()->user->getName()){
                if($postParent->associationId == Yii::app()->user->getId())
                    $post->delete();
            }
            if($post->user == Yii::app()->user->getName() || $post->associationId == Yii::app()->user->getId() || Yii::app()->user->checkAccess('AdminIndex')){
                if($post->delete()){

                }
            }
        }
        $this->redirect(array('site/whatsNew'));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){

        $dataProvider = new CActiveDataProvider('Events', array(
                    'criteria' => array(
                        'order' => 'timestamp DESC',
                        'condition' => "type='feed' AND associationId=$id AND (visibility=1 OR associationId=".Yii::app()->user->getId()." OR user='".Yii::app()->user->getName()."')",
                        )));

        $this->render('view', array(
            'model' => $this->loadModel($id),
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Display/set user profile settings.
     */
    public function actionSettings(){
        $model = $this->loadModel(Yii::app()->user->getId());


        $modules = Modules::model()->findAllByAttributes(array('visible' => 1));
        $menuItems = array();
        foreach($modules as $module){
            if($module->name == 'document'){
                $menuItems[$module->title] = $module->title;
            }else{
                $menuItems[$module->name] = $module->title;
            }
        }
        $menuItems = array('' => Yii::t('app', "What's New")) + $menuItems;

        if(isset($_POST['ProfileChild'])){
            $model->attributes = $_POST['ProfileChild'];

            if($model->save()){
                //$this->redirect(array('view','id'=>$model->id));
            }
            $this->refresh();
        }
        $languageDirs = scandir('./protected/messages'); // scan for installed language folders

        $languages = array('en' => 'English');

        foreach($languageDirs as $code){  // look for langauges name
            $name = $this->getLanguageName($code, $languageDirs);  // in each item in $languageDirs
            if($name !== false)
                $languages[$code] = $name; // add to $languages if name is found
        }
        $times = $this->getTimeZones();

        $myBackgroundProvider = new CActiveDataProvider('Media', array(
                    'criteria' => array(
                        'condition' => "(associationType = 'bg-private' AND associationId = '".Yii::app()->user->getId()."') OR associationType = 'bg'",
                        'order' => 'createDate DESC'
                    ),
                ));
        $myLoginSoundProvider = new CActiveDataProvider('Media', array(
                    'criteria' => array(
                        'condition' => "(associationType='loginSound' AND (private=0 OR private IS NULL OR uploadedBy='".Yii::app()->user->getName()."'))",
                        'order' => 'createDate DESC'
                    ),
                ));
        $myNotificationSoundProvider = new CActiveDataProvider('Media', array(
                    'criteria' => array(
                        'condition' => "(associationType='notificationSound' AND (private=0 OR private IS NULL OR uploadedBy='".Yii::app()->user->getName()."'))",
                        'order' => 'createDate DESC'
                    ),
                ));
        $hiddenTags = json_decode(Yii::app()->params->profile->hiddenTags, true);
        if(empty($hiddenTags))
            $hiddenTags = array();
        $allTags = Yii::app()->db->createCommand()
                ->select('COUNT(*) AS count, tag')
                ->from('x2_tags')
                ->group('tag')
                ->where('tag IS NOT NULL AND tag IN (\''.implode("','", $hiddenTags).'\')')
                ->order('tag ASC')
                ->limit(20)
                ->queryAll();

        $this->render('settings', array(
            'model' => $model,
            'languages' => $languages,
            'times' => $times,
            'myBackgrounds' => $myBackgroundProvider,
            'myLoginSounds' => $myLoginSoundProvider,
            'myNotificationSounds' => $myNotificationSoundProvider,
            'menuItems' => $menuItems,
            'allTags' => $allTags
        ));
    }

    /**
     * Updates a particular model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id){
        if($id == Yii::app()->user->getId() || Yii::app()->user->checkAccess('AdminIndex')){
            $model = $this->loadModel($id);
            $users = User::getNames();
            $accounts = Accounts::getNames();

            if(isset($_POST['ProfileChild'])){

                $temp = $model->attributes;
                foreach($_POST['ProfileChild'] as $name => $value){
                    if($value == $model->getAttributeLabel($name)){
                        $_POST['ProfileChild'][$name] = '';
                    }
                    $model->$name = $value;
                }
                if($model->save()){
                    $this->redirect(array('view', 'id' => $model->id));
                }
            }

            $this->render('update', array(
                'model' => $model,
                'users' => $users,
                'accounts' => $accounts,
            ));
        }else{
            $this->redirect(array('/profile/'.$id));
        }
    }

    /**
     * Changes the password for the user given by its record ID number.
     * @param integer $id ID of the user to be updated.
     */
    public function actionChangePassword($id){
        if($id == Yii::app()->user->getId()){
            $user = UserChild::model()->findByPk($id);
            if(isset($_POST['oldPassword']) && isset($_POST['newPassword']) && isset($_POST['newPassword2'])){

                $oldPass = $_POST['oldPassword'];
                $newPass = $_POST['newPassword'];
                $newPass2 = $_POST['newPassword2'];
                if((crypt($oldPass, '$5$rounds=32678$'.$user->password) == '$5$rounds=32678$'.$user->password) || md5($oldPass) == $user->password){
                    if($newPass == $newPass2){
                        $user->password = md5($newPass);
                        $user->save();

                        $this->redirect($this->createUrl('profile/'.$id));
                    }
                }else{
                    Yii::app()->clientScript->registerScript('alertPassWrong', "alert('Old password is incorrect.');");
                }
            }

            $this->render('changePassword', array(
                'model' => $user,
            ));
        }
    }

    /**
     * Upload a profile photo.
     * @param integer $id ID of the user in question.
     */
    public function actionUploadPhoto($id){
        if($id == Yii::app()->user->getId()){
            $prof = ProfileChild::model()->findByPk($id);
            if(isset($_FILES['photo'])){
                if($_FILES["photo"]["size"] < 2000000){
                    if($prof->avatar != '' && isset($prof->avatar) && file_exists($prof->avatar)){
                        unlink($prof->avatar);
                    }
                    $temp = CUploadedFile::getInstanceByName('photo');
                    $name = $this->generatePictureName();
                    $ext = $temp->getExtensionName();
                    $temp->saveAs('uploads/'.$name.'.'.$ext);

                    $prof->avatar = 'uploads/'.$name.'.'.$ext;
                    if($prof->save()){

                    }
                }else{
                    echo "File is too large!";
                }
            }
        }
        $this->redirect(array('view', 'id' => $id));
    }

    /**
     * Set the background image.
     */
    public function actionSetBackground(){
        if(isset($_POST['name'])){

            $profile = X2Model::model('ProfileChild')->findByPk(Yii::app()->user->getId());

            $profile->backgroundImg = $_POST['name'];

            if($profile->save()){
                echo "success";
            }
            //$this->redirect(array('profile/settings','id'=>Yii::app()->user->getId()));
        }
    }

    /**
     * Delete a background image.
     *
     * @param type $id
     */
    public function actionDeleteBackground($id){

        $image = X2Model::model('Media')->findByPk($id);
        if($image->associationId == Yii::app()->user->getId() && ($image->associationType == 'bg' || $image->associationType == 'bg-private')){

            $profile = X2Model::model('ProfileChild')->findByPk(Yii::app()->user->getId());

            if($profile->backgroundImg == $image->fileName){ // if this BG is currently in use, clear user's background image setting
                $profile->backgroundImg = '';
                $profile->save();
            }

            if($image->delete()){
                unlink('uploads/'.$image->fileName); // delete file
                echo 'success';
            }
        }
    }

    /**
     * Sets login/notification sound.
     * @param String $sound Which sound should be set.
     */
    public function actionSetSound($sound){
        if(isset($_POST['name'])){
            $profile = X2Model::model('ProfileChild')->findByPk(Yii::app()->user->getId());
            $profile->$sound = $_POST['name'];
            if($profile->update(array($sound))){
                echo "success";
            }
        }
    }

    public function actionDeleteSound($id, $sound){
        $sound = X2Model::model('Media')->findByPk($id);
        $profile=Yii::app()->params->profile;
        $type=$sound->associationType;
        if($profile->$type == $sound->fileName){ // if this sound is currently in use, clear user's sound setting
            $profile->$type = '';
            $profile->update(array($sound->associationType));
        }
        if($sound->delete()){
            unlink('uploads/media/'.$sound->uploadedBy.'/'.$sound->fileName); // delete file
            echo 'success';
        }
        return true;
    }

    /**
     * Generate a random filename for a picture.
     *
     * @return string
     */
    private function generatePictureName(){

        $time = time();
        $rand = chr(rand(65, 90));
        $salt = $time.$rand;
        $name = md5($salt.md5($salt).$salt);
        return $name;
    }

    /**
     * Add a new post to the social feed.
     *
     * @param integer $id ID of the user.
     */
    public function actionAddPost($id, $redirect){
        $post = new Events;
        // $user = $this->loadModel($id);
        if(isset($_POST['Events']) && $_POST['Events']['text'] != Yii::t('app', 'Enter text here...')){
            $post->text = $_POST['Events']['text'];
            $post->visibility = $_POST['Events']['visibility'];
            if(isset($_POST['Events']['associationId']))
                $post->associationId = $_POST['Events']['associationId'];
            //$soc->attributes = $_POST['Social'];
            //die(var_dump($_POST['Social']));
            $post->user = Yii::app()->user->getName();
            $post->type = 'feed';
            $post->subtype = $_POST['Events']['subtype'];
            $post->lastUpdated = time();
            $post->timestamp = time();
            if(!isset($post->associationId) || $post->associationId == 0)
                $post->associationId = $id;
            if($post->save()){
                if($post->associationId != Yii::app()->user->getId()){

                    $notif = new Notification;

                    $notif->type = 'social_post';
                    $notif->createdBy = $post->user;
                    $notif->modelType = 'Profile';
                    $notif->modelId = $post->associationId;

                    $notif->user = Yii::app()->db->createCommand()
                            ->select('username')
                            ->from('x2_users')
                            ->where('id=:id', array(':id' => $post->associationId))
                            ->queryScalar();

                    // $prof = X2Model::model('ProfileChild')->findByAttributes(array('username'=>$post->user));
                    // $notif->text = "$prof->fullName posted on your profile.";
                    // $notif->record = "profile:$prof->id";
                    // $notif->viewed = 0;
                    $notif->createDate = time();
                    // $subject=X2Model::model('ProfileChild')->findByPk($id);
                    // $notif->user = $subject->username;
                    $notif->save();
                }
            }
        }
        if($redirect == "view")
            $this->redirect(array('view', 'id' => $id));
        else
            $this->redirect(array('site/whatsNew'));
    }

    /**
     * Posts a comment on some post.
     *
     * @param $comment string the text you're posting
     * @param $id integer the id of the post you're commenting on
     */
    public function actionAddComment($comment, $id){

        // if(isset($_GET['comment'],$_GET['id'])) {

        $postModel = Events::model()->findByPk($id);

        if($postModel === null)
            throw new CHttpException(404, Yii::t('app', 'The requested post does not exist.'));

        $commentModel = new Events;
        $commentModel->text = $comment;
        $commentModel->user = Yii::app()->user->name;
        $commentModel->type = 'comment';
        $commentModel->associationId = $postModel->id;
        $commentModel->associationType = 'Events';
        $commentModel->timestamp = time();

        if($commentModel->save()){

            $postModel->lastUpdated = time();
            $postModel->save();

            $profileUser = Yii::app()->db->createCommand()
                    ->select('username')
                    ->from('x2_users')
                    ->where('id=:id', array(':id' => $postModel->associationId))
                    ->queryScalar();


            // notify the owner of the feed containing the post you commented on (unless that person is you)
            if($postModel->associationId != Yii::app()->user->getId()){
                $postNotif = new Notification;
                $postNotif->type = 'social_comment';
                $postNotif->createdBy = $commentModel->user;
                $postNotif->modelType = 'Profile';
                $postNotif->modelId = $postModel->associationId;

                // look up the username of the owner of the feed
                $postNotif->user = $profileUser;

                $postNotif->createDate = time();
                $postNotif->save();
            }
            // now notify the person whose post you commented on (unless they're the same person as the first notification)
            if($profileUser != $postModel->user && $postModel->user != Yii::app()->user->name){
                $commentNotif = new Notification;
                $commentNotif->type = 'social_comment';
                $commentNotif->createdBy = $commentModel->user;
                $commentNotif->modelType = 'Profile';
                $commentNotif->modelId = $postModel->associationId;

                $commentNotif->user = $postModel->user;

                $commentNotif->createDate = time();
                $commentNotif->save();
            }

            // $notif=new Notifications;
            // $prof=X2Model::model('ProfileChild')->findByAttributes(array('username'=>$comment->user));
            // $notif->text="$prof->fullName added a comment to a post.";
            // $notif->record="profile:$model->associationId";
            // $notif->viewed=0;
            // $notif->createDate=time();
            // $subject=X2Model::model('ProfileChild')->findByAttributes(array('username'=>$post->user));
            // $notif->user=$subject->username;
            // if($notif->user!=Yii::app()->user->getName())
            // $notif->save();
            // $notif=new Notifications;
            // $prof=X2Model::model('ProfileChild')->findByAttributes(array('username'=>$comment->user));
            // $subject=X2Model::model('ProfileChild')->findByPk($post->associationId);
            // $notif->text="$prof->fullName added a comment to a post.";
            // $notif->record="profile:$model->associationId";
            // $notif->viewed=0;
            // $notif->createDate=time();
            // $notif->user=$subject->username;
            // if($notif->user!=Yii::app()->user->getName())
            // $notif->save();
        }
        // }
        if(isset($_GET['redirect'])){
            if($_GET['redirect'] == "view")
                $this->redirect(array('view', 'id' => $postModel->associationId));
            if($_GET['redirect'] == "index")
                $this->redirect(array('index'));
        } else
            $this->redirect(array('index'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex(){
        $this->redirect(array('/site/whatsNew'));
    }

    /**
     * Lists users profiles.
     */
    public function actionProfiles(){
        $model = new Profile('search');
        $this->render('profiles', array('model' => $model));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     *
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     */
    public function loadModel($id){
        $model = ProfileChild::model('ProfileChild')->findByPk((int) $id);
        if($model === null)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }

    /**
     * Obtain the name of the language given its 2-5 letter code.
     *
     * If a language pack was found for the language code, return its full
     * name. Otherwise, return false.
     *
     * @param string $code
     * @param array $languageDirs
     * @return mixed
     */
    private function getLanguageName($code, $languageDirs){ // lookup language name for the language code provided
        if(in_array($code, $languageDirs)){ // is the language pack here?
            $appMessageFile = "protected/messages/$code/app.php";
            if(file_exists($appMessageFile)){ // attempt to load 'app' messages in
                $appMessages = include($appMessageFile);     // the chosen language
                if(is_array($appMessages) and isset($appMessages['languageName']) && $appMessages['languageName'] != 'Template')
                    return $appMessages['languageName'];       // return language name
            }
        }
        return false; // false if languge pack wasn't there
    }

    /**
     * Return a mapping of Olson TZ code names to timezone names.
     * @return array
     */
    private function getTimeZones(){
        return array(
            'Pacific/Midway' => "(GMT-11:00) Midway Island",
            'US/Samoa' => "(GMT-11:00) Samoa",
            'US/Hawaii' => "(GMT-10:00) Hawaii",
            'US/Alaska' => "(GMT-09:00) Alaska",
            'US/Pacific' => "(GMT-08:00) Pacific Time (US & Canada)",
            'America/Tijuana' => "(GMT-08:00) Tijuana",
            'US/Arizona' => "(GMT-07:00) Arizona",
            'US/Mountain' => "(GMT-07:00) Mountain Time (US & Canada)",
            'America/Chihuahua' => "(GMT-07:00) Chihuahua",
            'America/Mazatlan' => "(GMT-07:00) Mazatlan",
            'America/Mexico_City' => "(GMT-06:00) Mexico City",
            'America/Monterrey' => "(GMT-06:00) Monterrey",
            'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
            'US/Central' => "(GMT-06:00) Central Time (US & Canada)",
            'US/Eastern' => "(GMT-05:00) Eastern Time (US & Canada)",
            'US/East-Indiana' => "(GMT-05:00) Indiana (East)",
            'America/Bogota' => "(GMT-05:00) Bogota",
            'America/Lima' => "(GMT-05:00) Lima",
            'America/Caracas' => "(GMT-04:30) Caracas",
            'Canada/Atlantic' => "(GMT-04:00) Atlantic Time (Canada)",
            'America/La_Paz' => "(GMT-04:00) La Paz",
            'America/Santiago' => "(GMT-04:00) Santiago",
            'Canada/Newfoundland' => "(GMT-03:30) Newfoundland",
            'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
            'Greenland' => "(GMT-03:00) Greenland",
            'Atlantic/Stanley' => "(GMT-02:00) Stanley",
            'Atlantic/Azores' => "(GMT-01:00) Azores",
            'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
            'Africa/Casablanca' => "(GMT) Casablanca",
            'Europe/Dublin' => "(GMT) Dublin",
            'Europe/Lisbon' => "(GMT) Lisbon",
            'Europe/London' => "(GMT) London",
            'Africa/Monrovia' => "(GMT) Monrovia",
            'UTC' => "(UTC)",
            'Europe/Amsterdam' => "(GMT+01:00) Amsterdam",
            'Europe/Belgrade' => "(GMT+01:00) Belgrade",
            'Europe/Berlin' => "(GMT+01:00) Berlin",
            'Europe/Bratislava' => "(GMT+01:00) Bratislava",
            'Europe/Brussels' => "(GMT+01:00) Brussels",
            'Europe/Budapest' => "(GMT+01:00) Budapest",
            'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
            'Europe/Ljubljana' => "(GMT+01:00) Ljubljana",
            'Europe/Madrid' => "(GMT+01:00) Madrid",
            'Europe/Paris' => "(GMT+01:00) Paris",
            'Europe/Prague' => "(GMT+01:00) Prague",
            'Europe/Rome' => "(GMT+01:00) Rome",
            'Europe/Sarajevo' => "(GMT+01:00) Sarajevo",
            'Europe/Skopje' => "(GMT+01:00) Skopje",
            'Europe/Stockholm' => "(GMT+01:00) Stockholm",
            'Europe/Vienna' => "(GMT+01:00) Vienna",
            'Europe/Warsaw' => "(GMT+01:00) Warsaw",
            'Europe/Zagreb' => "(GMT+01:00) Zagreb",
            'Europe/Athens' => "(GMT+02:00) Athens",
            'Europe/Bucharest' => "(GMT+02:00) Bucharest",
            'Africa/Cairo' => "(GMT+02:00) Cairo",
            'Africa/Harare' => "(GMT+02:00) Harare",
            'Europe/Helsinki' => "(GMT+02:00) Helsinki",
            'Europe/Istanbul' => "(GMT+02:00) Istanbul",
            'Asia/Jerusalem' => "(GMT+02:00) Jerusalem",
            'Europe/Kiev' => "(GMT+02:00) Kyiv",
            'Europe/Minsk' => "(GMT+02:00) Minsk",
            'Europe/Riga' => "(GMT+02:00) Riga",
            'Europe/Sofia' => "(GMT+02:00) Sofia",
            'Europe/Tallinn' => "(GMT+02:00) Tallinn",
            'Europe/Vilnius' => "(GMT+02:00) Vilnius",
            'Asia/Baghdad' => "(GMT+03:00) Baghdad",
            'Asia/Kuwait' => "(GMT+03:00) Kuwait",
            'Europe/Moscow' => "(GMT+03:00) Moscow",
            'Africa/Nairobi' => "(GMT+03:00) Nairobi",
            'Asia/Riyadh' => "(GMT+03:00) Riyadh",
            'Europe/Volgograd' => "(GMT+03:00) Volgograd",
            'Asia/Tehran' => "(GMT+03:30) Tehran",
            'Asia/Baku' => "(GMT+04:00) Baku",
            'Asia/Muscat' => "(GMT+04:00) Muscat",
            'Asia/Tbilisi' => "(GMT+04:00) Tbilisi",
            'Asia/Yerevan' => "(GMT+04:00) Yerevan",
            'Asia/Kabul' => "(GMT+04:30) Kabul",
            'Asia/Yekaterinburg' => "(GMT+05:00) Ekaterinburg",
            'Asia/Karachi' => "(GMT+05:00) Karachi",
            'Asia/Tashkent' => "(GMT+05:00) Tashkent",
            'Asia/Kolkata' => "(GMT+05:30) Kolkata",
            'Asia/Kathmandu' => "(GMT+05:45) Kathmandu",
            'Asia/Almaty' => "(GMT+06:00) Almaty",
            'Asia/Dhaka' => "(GMT+06:00) Dhaka",
            'Asia/Novosibirsk' => "(GMT+06:00) Novosibirsk",
            'Asia/Bangkok' => "(GMT+07:00) Bangkok",
            'Asia/Jakarta' => "(GMT+07:00) Jakarta",
            'Asia/Krasnoyarsk' => "(GMT+07:00) Krasnoyarsk",
            'Asia/Chongqing' => "(GMT+08:00) Chongqing",
            'Asia/Hong_Kong' => "(GMT+08:00) Hong Kong",
            'Asia/Irkutsk' => "(GMT+08:00) Irkutsk",
            'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
            'Australia/Perth' => "(GMT+08:00) Perth",
            'Asia/Singapore' => "(GMT+08:00) Singapore",
            'Asia/Taipei' => "(GMT+08:00) Taipei",
            'Asia/Ulaanbaatar' => "(GMT+08:00) Ulaan Bataar",
            'Asia/Urumqi' => "(GMT+08:00) Urumqi",
            'Asia/Seoul' => "(GMT+09:00) Seoul",
            'Asia/Tokyo' => "(GMT+09:00) Tokyo",
            'Asia/Yakutsk' => "(GMT+09:00) Yakutsk",
            'Australia/Adelaide' => "(GMT+09:30) Adelaide",
            'Australia/Darwin' => "(GMT+09:30) Darwin",
            'Australia/Brisbane' => "(GMT+10:00) Brisbane",
            'Australia/Canberra' => "(GMT+10:00) Canberra",
            'Pacific/Guam' => "(GMT+10:00) Guam",
            'Australia/Hobart' => "(GMT+10:00) Hobart",
            'Australia/Melbourne' => "(GMT+10:00) Melbourne",
            'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
            'Australia/Sydney' => "(GMT+10:00) Sydney",
            'Asia/Vladivostok' => "(GMT+10:00) Vladivostok",
            'Asia/Magadan' => "(GMT+11:00) Magadan",
            'Pacific/Auckland' => "(GMT+12:00) Auckland",
            'Pacific/Fiji' => "(GMT+12:00) Fiji",
            'Asia/Kamchatka' => "(GMT+12:00) Kamchatka",
        );
    }

    /**
     * Sets the the option for the number of results per page.
     * @param integer $results
     */
    public function actionSetResultsPerPage($results){
        Yii::app()->params->profile->resultsPerPage = $results;
        Yii::app()->params->profile->save();
    }

    public function actionResetWidgets($id){
        $model = $this->loadModel($id);

        $model->layout = json_encode($model->initLayout());
        $model->update();

        $this->redirect(array('view', 'id' => $id));
    }

}
