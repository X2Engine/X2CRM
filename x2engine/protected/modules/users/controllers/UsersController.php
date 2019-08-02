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
 * @package application.modules.users.controllers
 */
class UsersController extends x2base {

    public $modelClass = 'User';

//    public function behaviors() {
//        return array_merge(parent::behaviors(), array(
//            'MobileControllerBehavior' => array(
//                'class' => 
//                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
//            ),
//        ));
//    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'actions'=>array('createAccount'),
                'users'=>array('*')
            ),
            array('allow',
                'actions'=>array('addTopContact','removeTopContact'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('view','index','create','update','admin','delete','search','inviteUsers', 'deactivateTwoFactor'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex(){
        $this->redirect('admin');
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $user=User::model()->findByPk($id);

        // Only load the Google Maps widget if we're on a User with an address
        if(isset($this->portlets['GoogleMaps']) && Yii::app()->settings->enableMaps) {
            $this->portlets['GoogleMaps']['params']['location'] = $user->address;
            $this->portlets['GoogleMaps']['params']['activityLocations'] = $user->getMapLocations();
            $this->portlets['GoogleMaps']['params']['defaultFilter'] = Locations::getDefaultUserTypes();
            $this->portlets['GoogleMaps']['params']['modelParam'] = 'userId';
        }
        $dataProvider=new CActiveDataProvider('Actions', array(
            'criteria'=>array(
                'order'=>'createDate DESC',
                'condition'=>'assignedTo=\''.$user->username.'\' OR completedBy = \''.$user->username.'\'',
        )));
        $actionHistory=$dataProvider->getData();
        $this->render('view',array(
            'model'=>$this->loadModel($id),
            'actionHistory'=>$actionHistory,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $admin = &Yii::app()->settings;
        $userCount = Yii::app()->db->createCommand(
                "SELECT COUNT(*) FROM x2_users;"
        )->queryAll();
        $userCountParsed = $userCount[0]["COUNT(*)"];
        if ($userCountParsed >= 200) {
            $this->render('userLimit',array());
        }
        
        $model=new User;
        $groups=array();
        foreach(Groups::model()->findAll() as $group){
            $groups[$group->id]=CHtml::encode($group->name);
        }
        $roles=array();
        foreach(Roles::model()->findAll() as $role){
            $roles[$role->id]=CHtml::encode($role->name);
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        $unhashedPassword = '';
        if(isset($_POST['User'])) {
            $model->attributes=$_POST['User'];
            //Temporarily maintain unhashed in case of validation error
            $unhashedPassword = $model->password;
            
            if ($model->validate (array('password')))
            
                $model->password = PasswordUtil::createHash($model->password);
            $model->userKey=substr(str_shuffle(str_repeat(
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
            $profile=new Profile;
            $profile->fullName=$model->firstName." ".$model->lastName;
            $profile->username=$model->username;
            $profile->allowPost=1;
            $profile->emailAddress=$model->emailAddress;
            $profile->status=$model->status;

             
            // set a default theme if there is one
            $admin = Yii::app()->settings;
            if ($admin->defaultTheme) {
                $profile->theme = $profile->getDefaultTheme ();
            }
             

            if($model->save()){
                $calendar = new X2Calendar();
                $calendar->createdBy = $model->username;
                $calendar->updatedBy = $model->username;
                $calendar->createDate = time();
                $calendar->lastUpdated = time();
                $calendar->name = $profile->fullName."'s Calendar";
                $calendar->save();
                $profile->id=$model->id;
                $profile->defaultCalendar = $calendar->id;
                $profile->save();
                if(isset($_POST['roles'])){
                    $roles=$_POST['roles'];
                    foreach($roles as $role){
                        $link=new RoleToUser;
                        $link->roleId=$role;
                        $link->userId=$model->id;
                        $link->type="user";
                        $link->save();
                    }
                }
                if(isset($_POST['groups'])){
                    $groups=$_POST['groups'];
                    foreach($groups as $group){
                        $link=new GroupToUser;
                        $link->groupId=$group;
                        $link->userId=$model->id;
                        $link->username=$model->username;
                        $link->save();
                    }
                }
                $this->redirect(array('view','id'=>$model->id));
            }
        }
        $model->password = $unhashedPassword;

        $this->render('create',array(
            'model'=>$model,
            'groups'=>$groups,
            'roles'=>$roles,
            'selectedGroups'=>array(),
            'selectedRoles'=>array(),
        ));
    }

    public function actionCreateAccount(){
        Yii::import('application.components.ThemeGenerator.LoginThemeHelper');
        $this->layout='//layouts/login';
        if(isset($_GET['key'])){
            $key=$_GET['key'];
            $user=User::model()->findByAttributes(array('inviteKey'=>$key));
            if(isset($user)){
                $user->setScenario('insert');
                if($key==$user->inviteKey){
                    if(isset($_POST['User'])) {
                        $model=$user;
                        $model->attributes=$_POST['User'];
                        $model->status=1;
                        //$this->updateChangelog($model);
                        
                        if ($model->validate (array('password')))
                        
                            $model->password = PasswordUtil::createHash($model->password);
                        $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
                        $profile=new Profile;
                        $profile->fullName=$model->firstName." ".$model->lastName;
                        $profile->username=$model->username;
                        $profile->allowPost=1;
                        $profile->emailAddress=$model->emailAddress;
                        $profile->status=$model->status;

                        if($model->save()){
                            $model->inviteKey=null;
                            $model->temporary=0;
                            $model->save();
                            $profile->id=$model->id;
                            $profile->save();
                            $this->redirect(array('/site/login'));
                        }
                    }
                    $this->render('createAccount',array(
                        'user'=>$user,
                    ));
                }else{
                    $this->redirect($this->createUrl('/site/login'));
                }
            }else{
                $this->redirect($this->createUrl('/site/login'));
            }
        }else{
            $this->redirect($this->createUrl('/site/login'));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model=$this->loadModel($id);
        $groups=array();
        foreach(Groups::model()->findAll() as $group){
            $groups[$group->id]=CHtml::encode($group->name);
        }
        $selectedGroups=array();
        foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
            $selectedGroups[]=$link->groupId;
        }
        $roles=array();
        foreach(Roles::model()->findAll() as $role){
            $roles[$role->id]=CHtml::encode($role->name);
        }
        $selectedRoles=array();
        foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
            $selectedRoles[]=$link->roleId;
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (!isset($model->userAlias))
            $model->userAlias = $model->username;

        if(isset($_POST['User'])) {
            $old=$model->attributes;
            $temp=$model->password;
            $model->attributes=$_POST['User'];

            if($model->password!="") {
                
                if ($model->validate (array('password')))
                
                    $model->password = PasswordUtil::createHash($model->password);
            } else {
                $model->password=$temp;
            }
            if(empty($model->userKey)){
                $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
            }
            if($model->save()){
                $profile = $model->profile;
                if(!empty($profile)) {
                    $profile->emailAddress = $model->emailAddress;
                    $profile->fullName = $model->firstName.' '.$model->lastName;
                    $profile->save();
                }
                if($old['username']!=$model->username){
                    $fieldRecords=Fields::model()->findAllByAttributes(array('fieldName'=>'assignedTo'));
                    $modelList=array();
                    foreach($fieldRecords as $record){
                        $modelList[$record->modelName]=$record->linkType;
                    }
                    foreach($modelList as $modelName=>$type){
                        if($modelName=='Quotes')
                            $modelName="Quote";
                        if($modelName=='Products')
                            $modelName='Product';
                        if(empty($type)){
                            $list=X2Model::model($modelName)->findAllByAttributes(array('assignedTo'=>$old['username']));
                            foreach($list as $item){
                                $item->assignedTo=$model->username;
                                $item->save();
                            }
                        }else{
                            $list=X2Model::model($modelName)->findAllBySql(
                                    "SELECT * FROM ".X2Model::model($modelName)->tableName()
                                    ." WHERE assignedTo LIKE '%".$old['username']."%'");
                            foreach($list as $item){
                                $assignedTo=explode(", ",$item->assignedTo);
                                $key=array_search($old['username'],$assignedTo);
                                if($key>=0){
                                    $assignedTo[$key]=$model->username;
                                }
                                $item->assignedTo=implode(", ",$assignedTo);
                                $item->save();
                            }
                        }
                    }

                    $profile=Profile::model()->findByAttributes(array('username'=>$old['username']));
                    if(isset($profile)){
                        $profile->username=$model->username;
                        $profile->save();
                    }

                }
                foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $link->delete();
                }
                foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $link->delete();
                }
                if(isset($_POST['roles'])){
                    $roles=$_POST['roles'];
                    foreach($roles as $role){
                        $link=new RoleToUser;
                        $link->roleId=$role;
                        $link->type="user";
                        $link->userId=$model->id;
                        $link->save();
                    }
                }
                if(isset($_POST['groups'])){
                    $groups=$_POST['groups'];
                    foreach($groups as $group){
                        $link=new GroupToUser;
                        $link->groupId=$group;
                        $link->userId=$model->id;
                        $link->username=$model->username;
                        $link->save();
                    }
                }
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $this->render('update',array(
            'model'=>$model,
            'groups'=>$groups,
            'roles'=>$roles,
            'selectedGroups'=>$selectedGroups,
            'selectedRoles'=>$selectedRoles,
        ));
    }

    public function actionInviteUsers(){

        if(isset($_POST['emails'])){
            $list=$_POST['emails'];

            $body="Hello,

You are receiving this email because your X2Engine administrator has invited you to create an account.
Please click on the link below to create an account at X2Engine!

";

            $subject="Create Your X2Engine User Account";
            $list=trim($list);
            $emails=explode(',',$list);
            foreach($emails as &$email){
                $key=substr(str_shuffle(str_repeat(
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',16)),0, 16);
                $user=new User('invite');
                $email=trim($email);
                $user->inviteKey=$key;
                $user->temporary=1;
                $user->emailAddress=$email;
                $user->status=0;
                $userList=User::model()->findAllByAttributes(
                    array('emailAddress'=>$email,'temporary'=>1));
                foreach($userList as $userRecord){
                    if(isset($userRecord)){
                        $userRecord->delete();
                    }
                }
                $user->save();
                $link=CHtml::link(
                    'Create Account',
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . 
                    $this->createUrl('/users/users/createAccount',array('key'=>$key)));
                $mail=new InlineEmail;
                $mail->to=$email;
                // Get email password
                $cred = Credentials::model()->getDefaultUserAccount(
                    Credentials::$sysUseId['systemResponseEmail'],'email');
                if($cred==Credentials::LEGACY_ID)
                    $cred = Credentials::model()->getDefaultUserAccount(
                        Yii::app()->user->id,'email');
                if($cred != Credentials::LEGACY_ID)
                    $mail->credId = $cred;
                $mail->subject=$subject;
                $mail->message=$body."<br><br>".$link;
                $mail->contactFlag=false;
                if($mail->prepareBody()){
                    $mail->deliver();
                }else{
                }
            }
            $this->redirect('admin');
        }

        $this->render('inviteUsers');
    }

    public function actionDeleteTemporary(){
        $deleted=User::model()->deleteAllByAttributes(array('temporary'=>1));
        $this->redirect('admin');
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new User('search');
        $this->render('admin',array('model'=>$model,'count'=>User::model()->countByAttributes(array('temporary'=>1))));
    }

    public function actionDelete($id) {
        if($id != 1){
            $model=$this->loadModel($id);
            if(Yii::app()->request->isPostRequest) {
                $model->delete();
            } else {
                throw new CHttpException(
                    400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
            }
            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
            the browser */
            if(!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
        }else{
            throw new CHttpException(
                400,Yii::t('app','Cannot delete admin user.  Please do not repeat this request.'));
        }
    }

    public function actionAddTopContact($recordId, $modelClass) {
        Yii::import('application.components.leftWidget.TopContacts');
        $model = $this->getModelFromTypeAndId ($modelClass, $recordId, false);
        if (TopContacts::addBookmark ($model))
            $this->renderTopContacts();
    }

    public function actionRemoveTopContact($recordId, $modelClass) {
        Yii::import('application.components.leftWidget.TopContacts');
        $model = $this->getModelFromTypeAndId ($modelClass, $recordId, false);
        if (TopContacts::removeBookmark ($model))
            $this->renderTopContacts();
    }
    
    public function actionUserMap(){
        if (!Yii::app()->settings->googleIntegration) {
            throw new CHttpException(403, 'Please enable Google Integration to use this page.');
        }
        $users = User::getUserIds();
        unset($users['']);
        $selectedUsers = array_keys($users);
        $filterParams = filter_input(INPUT_POST,'params',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
        $params = array();
        if(isset($filterParams['users'])){
            $selectedUsers = $filterParams['users'];
            $userParams = AuxLib::bindArray($selectedUsers);
            $userList = AuxLib::arrToStrList($userParams);
        }
        $time = isset($filterParams['timestamp'])?$filterParams['timestamp']:Formatter::formatDateTime(time());
        $locations = Yii::app()->db->createCommand(
                "SELECT lat, lon AS lng, recordId, type, comment AS info, createDate AS time"
                . " FROM ("
                ."SELECT * FROM x2_locations"
                ." WHERE recordType = 'User'"
                .(isset($filterParams['users'])?" AND recordId IN ".$userList:'')
                ." AND createDate < :time"
                ." ORDER BY createDate DESC"
                .") AS tmp GROUP BY recordId"
        )->queryAll(true, array(':time'=>strtotime($time)));
        if(!empty($locations)){
            $center = $locations[0];
        } else {
            $center = array('lat' => 0, 'lng' => 0);;
        }
        $types = Locations::getLocationTypes();
        foreach($locations as &$location){
            $location['time'] = Formatter::formatLongDateTime($location['time']);
            if(array_key_exists($location['type'],$types)){
                $location['type'] = $types[$location['type']];
            }
        }
        $this->render('userMap',array(
            'users' => $users,
            'selectedUsers'=>$selectedUsers,
            'timestamp'=>$time,
            'center'=>json_encode($center),
            'locations'=>$locations,
        ));
    }

    public function actionDeactivateTwoFactor($id){
        if (!Yii::app()->request->isPostRequest) $this->denied();
        $model = Profile::model()->findByPk($id);
        if ($model) {
            $model->enableTwoFactor = 0;
            $model->update(array('enableTwoFactor'));
        }
    }

    private function renderTopContacts() {
        $this->renderPartial('application.components.leftWidget.views.topContacts',array(
            'bookmarkRecords'=>User::getTopContacts(),
            //'viewId'=>$viewId
        ));
    }

    /**
     * Create a menu for Users
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Users = Modules::displayName();
        $User = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'feed', 'admin', 'create', 'invite', 'view', 'profile', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'feed',
                'label'=>Yii::t('profile','Social Feed'),
                'url'=>array('/profile/index')
            ),
            array(
                'name'=>'admin',
                'label' => Yii::t('users', 'Manage {users}', array(
                    '{users}' => $Users,
                )),
                'url'=>array('admin')
            ),
            array(
                'name'=>'map',
                'label' => Yii::t('users', 'View {users} Map', array(
                    '{users}' => $Users,
                )),
                'url'=>array('userMap'),
                'visible' => (bool) Yii::app()->settings->enableMaps,
            ),
            array(
                'name'=>'create',
                'label' => Yii::t('users', 'Create {user}', array(
                    '{user}' => $User,
                )),
                'url' => array('create')
            ),
            array(
                'name'=>'invite',
                'label' => Yii::t('users', 'Invite {users}', array(
                    '{users}' => $Users,
                )),
                'url' => array('inviteUsers')
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('users','View {user}', array(
                    '{user}' => $User,
                )),
                'url'=>array('view', 'id'=>$modelId)
            ),
            array(
                'name'=>'profile',
                'label'=>Yii::t('profile','View Profile'),
                'url'=>array('/profile/view','id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('users','Update {user}', array(
                    '{user}' => $User,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('users','Delete {user}', array(
                    '{user}' => $User,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }


}
