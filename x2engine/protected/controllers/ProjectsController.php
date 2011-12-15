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


class ProjectsController extends x2base {

	public $modelClass = 'ProjectChild';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','removeUser','addUser','updateStatus','setEndDate','search','addNote','deleteNote'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model=$this->loadModel($id);
		$model->assignedTo=UserChild::getUserLinks($model->assignedTo);
		$type='project';
		
		parent::view($model,$type);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$users=UserChild::getNames();
		$contacts=Contacts::getAllNames();
		$model=new ProjectChild;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['ProjectChild'])) {
		
			$model->attributes=$_POST['ProjectChild'];
                        $model->createDate=time();
			$this->updateChangelog($model);
			$arr=$model->assignedTo;
			$model->assignedTo=ProjectChild::parseUsers($arr);
			$arr=$model->associatedContacts;
			$model->associatedContacts=ProjectChild::parseContacts($arr);
			
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$users=UserChild::getNames();
		$contacts=Contacts::getAllNames();
		$model=$this->loadModel($id);
		$model=$this->updateChangelog($model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Projects'])) {
			$model->attributes=$_POST['Projects'];
			$this->updateChangelog($model);
			
			$arr=$model->assignedTo;
			if($arr[0]!=null)
				$str=ProjectChild::parseUsers($arr);
			else
				$str='Anyone';

			$model->assignedTo=$str;
			
			$arr=$model->associatedContacts;
			if($arr[0]!=null)
				$str=ProjectChild::parseContacts($arr);
			else
				$str='None';

			$model->associatedContacts=$str;

			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts
		));
	}


	public function actionAddUser($id) {
		$users=UserChild::getNames();
		$contacts=Contacts::getAllNames();
		$model=$this->loadModel($id);
		$users=Accounts::editUserArray($users, $model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Projects'])) {
			$temp=$model->assignedTo; 
			$model->attributes=$_POST['Projects'];  
			$arr=$model->assignedTo;
			$this->updateChangelog($model);

			$model->assignedTo=ProjectChild::parseUsers($arr);
			$temp.=', '.$model->assignedTo;
			$model->assignedTo=$temp;
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts
		));
	}

	public function actionRemoveUser($id) {
		$model=$this->loadModel($id);
		$pieces=explode(', ',$model->assignedTo);
		$pieces=Accounts::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Projects'])) {
			$model->attributes=$_POST['Projects'];  
			$arr=$model->assignedTo;
			$this->updateChangelog($model);

			foreach($arr as $id=>$user) {
				unset($pieces[$user]);
			}
			
			$temp=Accounts::parseUsersTwo($pieces);

			$model->assignedTo=$temp;
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$pieces,
		));
	}

	public function actionUpdateStatus($id) {

		$model=$this->loadModel($id);
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Projects'])) {
			$model->attributes=$_POST['Projects'];
			$this->updateChangelog($model);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('updateStatus',array(
			'model'=>$model,
		));
	}

	public function actionSetEndDate($id) {
		$model=$this->loadModel($id);
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Projects'])) {
			$model->attributes=$_POST['Projects'];
			$this->updateChangelog($model);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}
		$this->render('setDate',array(
			'model'=>$model,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new ProjectChild('search'); 
		$name='ProjectChild';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new ProjectChild('search'); 
		$name='ProjectChild';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=ProjectChild::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}

	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
				'condition'=>'associationId='.$id.' AND associationType=\'project\'',
			)));
			$actions=$dataProvider->getData();
			foreach($actions as $action){
				$action->delete();
			}
			$model->delete();
		}
		else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}
}