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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
		$model->assignedTo=User::getUserLinks($model->assignedTo);
		$type='project';
		
		parent::view($model,$type);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$users=User::getNames();
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
		$users=User::getNames();
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
		$users=User::getNames();
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