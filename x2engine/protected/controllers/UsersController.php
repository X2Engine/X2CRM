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

class UsersController extends x2base {

	public $modelClass = 'UserChild';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('view','addTopContact','removeTopContact'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index','create','update','admin','delete','search'),
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
		$user=UserChild::model()->findByPk($id);
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
		$model=new UserChild;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['UserChild'])) {
			$model->attributes=$_POST['UserChild'];
			//$this->updateChangelog($model);
			$model->password = md5($model->password);

			$profile=new ProfileChild;
			$profile->fullName=$model->firstName." ".$model->lastName;
			$profile->username=$model->username;
                        $profile->allowPost=1;
			$profile->emailAddress=$model->emailAddress;
			$profile->status=$model->status;

			if($model->save() && $profile->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Users'])) {
                    $temp=$model->password;
                    $model->attributes=$_POST['Users'];
                    if($model->password!="")
                        $model->password = md5($model->password);
                    else
                        $model->password=$temp;
                    if($model->save())
                        $this->redirect(array('view','id'=>$model->id));
		}
		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Lists all models.
	 */

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new UserChild('search');
		$name='UserChild';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=UserChild::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}

	public function actionDelete($id) {
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
                        
                        $dataProvider=new CActiveDataProvider('Contacts', array(
			'criteria'=>array(
				'condition'=>"assignedTo='$model->username'",
			)));
			$contacts=$dataProvider->getData();
                        foreach($contacts as $contact){
                                if($contact->updatedBy==$model->username)
                                    $contact->updatedBy='admin';
                                if($contact->completedBy==$model->username)
                                    $contact->completedBy='admin';
				$contact->assignedTo="Anyone";
                                $contact->save();
			}
                        
                        $prof=ProfileChild::model()->findByAttributes(array('username'=>$model->username));
                        $prof->delete();
                        $model->delete();
			
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
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
			'topContacts'=>UserChild::getTopContacts(),
			//'viewId'=>$viewId
		));
	}
}
