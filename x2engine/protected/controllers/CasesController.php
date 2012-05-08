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

class CasesController extends x2base {

	public $modelClass = 'CaseChild';

	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','closeCase','addNote','deleteNote','delete','inlineEmail'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actions() {
		return array(
			'inlineEmail'=>array(
				'class'=>'InlineEmailAction',
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
		$model->associatedContacts=Contacts::getContactLinks($model->associatedContacts);
		
		$type='case';
		
		parent::view($model,$type);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new CaseChild;
		$users=User::getNames();
		$contacts=Contacts::getAllNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CaseChild'])) {
			$model->attributes=$_POST['CaseChild'];
			$model->status="Open";
			$model=$this->updateChangelog($model);
                        $model->createDate=time();
			
			$model=CaseChild::parseContacts($model);
			
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		$users=User::getNames();
		$contacts=Contacts::getAllNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Cases'])) {
			$model->attributes=$_POST['Cases'];
			$model=$this->updateChangelog($model);

			$arr=$model->associatedContacts;
			$str='';
			foreach($arr as $contact) {
				$str.=' '.$contact;
			}
			$str=substr($str,1);
			$model->associatedContacts=$str;

			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new CaseChild('search');
		$name='CaseChild';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new CaseChild('search');
		$name='CaseChild';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=CaseChild::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}

	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
				'condition'=>'associationId='.$id.' AND associationType=\'case\'',
			)));

			$actions=$dataProvider->getData();
			foreach($actions as $action){
				$action->delete();
			}

			$model->delete();
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));

	}
	public function actionCloseCase($id) {
		
		$model=$this->loadModel($id);
		
		if($model->assignedTo==Yii::app()->user->getName() || Yii::app()->user->getName()=='admin'){
		
			$model->status='Closed';

			if($model->save()){
				$this->redirect(array('index'));
			}
		}else{
			$this->redirect(array('view','id'=>$id));
		}
	}
}