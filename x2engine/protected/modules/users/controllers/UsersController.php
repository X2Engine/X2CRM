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
 * @package X2CRM.modules.users.controllers
 */
class UsersController extends x2base {

	public $modelClass = 'User';
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
				'actions'=>array('view','index','create','update','admin','delete','search','inviteUsers'),
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
		$dataProvider=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'order'=>'complete DESC',
				'condition'=>'assignedTo=\''.$user->username.'\'',
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
		$model=new User;
                $groups=array();
                foreach(Groups::model()->findAll() as $group){
                    $groups[$group->id]=$group->name;
                }
                $roles=array();
                foreach(Roles::model()->findAll() as $role){
                    $roles[$role->id]=$role->name;
                }

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['User'])) {
			$model->attributes=$_POST['User'];
			//$this->updateChangelog($model);
			$model->password = md5($model->password);
            $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
			$profile=new ProfileChild;
			$profile->fullName=$model->firstName." ".$model->lastName;
			$profile->username=$model->username;
            $profile->allowPost=1;
			$profile->emailAddress=$model->emailAddress;
			$profile->status=$model->status;

			if($model->save()){
								$profile->id=$model->id;
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

		$this->render('create',array(
			'model'=>$model,
                        'groups'=>$groups,
                        'roles'=>$roles,
                        'selectedGroups'=>array(),
                        'selectedRoles'=>array(),
		));
	}
	
	public function actionCreateAccount(){
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
                        $model->password = md5($model->password);
                        $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
                        $profile=new ProfileChild;
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
                    $groups[$group->id]=$group->name;
                }
                $selectedGroups=array();
                foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $selectedGroups[]=$link->groupId;
                }
                $roles=array();
                foreach(Roles::model()->findAll() as $role){
                    $roles[$role->id]=$role->name;
                }
                $selectedRoles=array();
                foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $selectedRoles[]=$link->roleId;
                }

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['User'])) {
                    $old=$model->attributes;
                    $temp=$model->password;
                    $model->attributes=$_POST['User'];

                    if($model->password!="")
                        $model->password = md5($model->password);
                    else
                        $model->password=$temp;
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
                            
                            $profile=ProfileChild::model()->findByAttributes(array('username'=>$old['username']));
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

You are receiving this email because your X2CRM admin has invited you to create an account.
Please click on the link below to create an account at X2CRM!

";
			
			$subject="Create Your X2CRM User Account";
			$list=trim($list);
			$emails=explode(',',$list);
			foreach($emails as &$email){
                $key=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',16)),0,16);
                $user=new User('invite');
                $email=trim($email);
                $user->inviteKey=$key;
                $user->temporary=1;
                $user->emailAddress=$email;
                $user->status=0;
                $userList=User::model()->findAllByAttributes(array('emailAddress'=>$email,'temporary'=>1));
                foreach($userList as $userRecord){
                    if(isset($userRecord)){
                        $userRecord->delete();
                    }
                }
                $user->save();
                $link=(@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/users/createAccount?key='.$key);
				mail($email,$subject,$body.$link);
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

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=User::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}

	public function actionDelete($id) {
        if($id!=1){
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'condition'=>"assignedTo='$model->username'",
			)));
			$actions=$dataProvider->getData();
			foreach($actions as $action){
                                if($action->updatedBy==$model->username)
                                    $action->updatedBy='admin';
                                if($action->completedBy==$model->username)
                                    $action->completedBy='admin';
				$action->assignedTo="Anyone";
                                $action->save();
			}
            $social=Social::model()->findAllByAttributes(array('user'=>$model->username));
            foreach($social as $socialItem){
                $socialItem->delete();
            }
            $social=Social::model()->findAllByAttributes(array('associationId'=>$model->id));
            foreach($social as $socialItem){
                $socialItem->delete();
            }
                        
            $dataProvider=new CActiveDataProvider('Contacts', array(
			'criteria'=>array(
				'condition'=>"assignedTo='$model->username'",
			)));
			$contacts=$dataProvider->getData();
                        foreach($contacts as $contact){
                                if($contact->updatedBy==$model->username)
                                    $contact->updatedBy='admin';
                                // if($contact->completedBy==$model->username)
                                    // $contact->completedBy='admin';
				$contact->assignedTo="Anyone";
                                $contact->save();
			}
                        
            $prof=ProfileChild::model()->findByAttributes(array('username'=>$model->username));
            $prof->delete();
            Yii::app()->db->createCommand("DELETE FROM x2_events where user='".$model->username."' OR (type='feed' AND associationId=".$model->id.")")->execute();
			$model->delete();
			
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }else{
            throw new CHttpException(400,Yii::t('app','Cannot delete admin user.  Please do not repeat this request.'));
        }
	}

	public function actionAddTopContact() {
		if(isset($_GET['contactId']) && is_numeric($_GET['contactId'])) {
		
			//$viewId = (isset($_GET['viewId']) && is_numeric($_GET['viewId'])) ? $_GET['viewId'] : null;
			
			$id = Yii::app()->user->getId();
			$model=$this->loadModel($id);

			$topContacts = empty($model->topContacts)? array() : explode(',',$model->topContacts);

			if(!in_array($_GET['contactId'],$topContacts)) {		// only add to list if it isn't already in there
				array_unshift($topContacts,$_GET['contactId']);
				$model->topContacts = implode(',',$topContacts);
			}
			if ($model->save())
				$this->renderTopContacts();
			// else
				// echo print_r($model->getErrors());

		}
	}

	public function actionRemoveTopContact() {
		if(isset($_GET['contactId']) && is_numeric($_GET['contactId'])) {
		
			//$viewId = (isset($_GET['viewId']) && is_numeric($_GET['viewId'])) ? $_GET['viewId'] : null;
			
			$id = Yii::app()->user->getId();
			$model=$this->loadModel($id);

			$topContacts = empty($model->topContacts)? array() : explode(',',$model->topContacts);
			$index = array_search($_GET['contactId'],$topContacts);

			if($index!==false)
				unset($topContacts[$index]);

			$model->topContacts = implode(',',$topContacts);
			
			if ($model->save())
				$this->renderTopContacts();
		}
	}
	
	private function renderTopContacts() {
		$this->renderPartial('application.components.views.topContacts',array(
			'topContacts'=>User::getTopContacts(),
			//'viewId'=>$viewId
		));
	}
}
