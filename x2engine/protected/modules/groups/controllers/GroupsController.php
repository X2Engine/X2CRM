<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * @package X2CRM.modules.groups.controllers 
 */
class GroupsController extends x2base {
    public $modelClass="Groups";

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$userLinks=GroupToUser::model()->findAllByAttributes(array('groupId'=>$id));
		$str="";
		foreach($userLinks as $userLink){
			$str.=User::model()->findByPk($userLink->userId)->username.", ";
		}
		$str=substr($str,0,-2);
		$users=User::getUserLinks($str);
		$this->render('view',array(
			'model'=>$this->loadModel($id),
			'users'=>$users,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new Groups;
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);

		if(isset($_POST['Groups'])){

			$model->attributes=$_POST['Groups'];
			if(isset($_POST['users']))
				$users=$_POST['users'];
			else
				$users=array();
			if($model->save()){
				foreach($users as $user){
					$link=new GroupToUser;
					$link->groupId=$model->id;
					$userRecord=User::model()->findByAttributes(array('username'=>$user));
					if(isset($userRecord)) {
						$link->userId=$userRecord->id;
						$link->username=$userRecord->username;
						$link->save();
					}
				}
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('create',array(
				'model'=>$model,
				'users'=>$users,
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
		$selected=array();
		$links=GroupToUser::model()->findAllByAttributes(array('groupId'=>$id));
		foreach($links as $link){
			$user=User::model()->findByPk($link->userId);
			if(isset($user)){
				$selected[]=$user->username;
			}
		}
		unset($users['admin']);
		unset($users['']);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Groups']))
		{
			$userLinks=GroupToUser::model()->findAllByAttributes(array('groupId'=>$model->id));
			foreach($userLinks as $userLink){
				$userLink->delete();
			}
			$model->attributes=$_POST['Groups'];
			if(isset($_POST['users']))
				$users=$_POST['users'];
			else
				$users=array();
			if($model->save()){
				foreach($users as $user){
					$link=new GroupToUser;
					$link->groupId=$model->id;
					$userRecord=User::model()->findByAttributes(array('username'=>$user));
					$link->userId=$userRecord->id;
					$link->username=$userRecord->username;
					$test=GroupToUser::model()->findByAttributes(array('groupId'=>$model->id,'userId'=>$userRecord->id));
					if(!isset($test))
						$link->save();
				}
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('update',array(
				'model'=>$model,
				'users'=>$users,
				'selected'=>$selected,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$links=GroupToUser::model()->findAllByAttributes(array('groupId'=>$id));
			foreach($links as $link) {
				$link->delete();
			}
			$contacts=CActiveRecord::model('Contacts')->findAllByAttributes(array('assignedTo'=>$id));
			foreach($contacts as $contact) {
				$contact->assignedTo='Anyone';
				$contact->save();
			}
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
					$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$dataProvider=new CActiveDataProvider('Groups');
		$this->render('index',array(
				'dataProvider'=>$dataProvider,
		));
	}
        
	public function actionGetGroups() {	
		if(isset($_POST['checked'])) { // coming from a group checkbox?
			$checked = json_decode($_POST['checked']);
		} else if(isset($_POST['group'])){
			$checked = true;
		}else{
			$checked = false;
		}
		if(isset($_POST['field'])){
            $field=$_POST['field'];
        }
		if($checked) { // group checkbox checked, return list of groups
			$groups=Groups::model()->findAll();
			foreach($groups as $group){
				echo CHtml::tag('option', array('value'=>$group->id),CHtml::encode($group->name),true);
			}
		} else { // group checkbox unchecked, return list of user names
			$users=User::getNames();
            if(!in_array($field,array_keys($users))){
                $field=Yii::app()->user->getName();
            }
			foreach($users as $key=>$value){
				echo CHtml::tag('option', array('value'=>$key, 'selected'=>$key==$field?"true":null),CHtml::encode($value),true);
			}
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Groups::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='groups-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
