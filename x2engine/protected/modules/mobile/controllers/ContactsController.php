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
 * @package X2CRM.modules.mobile.controllers
 */
class ContactsController extends MobileController{
	
	 public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index','new','search','view','viewAll'),
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }
	
	public function actionView($id){
		
		$model=$this->loadModel($id);
		
		$this->render('view',array(
			'model'=>$model,
		));
	}
	
	
	public function actionIndex(){
		
		$user=User::model()->findByPk(Yii::app()->user->getId());
		$topList=$user->topContacts;
		$pieces=explode(',',$topList);
		$contacts=array();
		foreach($pieces as $piece){
			$contact=X2Model::model('Contacts')->findByPk($piece); 
			if(isset($contact))
				$contacts[]=$contact;
		}
		$dataProvider=new CActiveDataProvider('Contacts');
		$dataProvider->setData($contacts);
		
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}
	
	public function actionNew(){
		
		$model=new Contacts;
		$attributeLabels = $model->attributeLabels();
		
		if(isset($_POST['ajax']) && $_POST['ajax']=='quick-contact-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['Contacts'])) {
			// $this->redirect('http://www.google.com/');
			$model->attributes = $_POST['Contacts'];
			//
				// $model->firstName = 'bob';
				// $model->lastName = 'dole';
				// $model->phone = '';
				// $model->email = '';
			$model->visibility = 1;
			
			// reset to blank if it's the default value
			if($model->firstName == $attributeLabels['firstName'])
				$model->firstName = '';
			if($model->lastName == $attributeLabels['lastName'])
				$model->lastName = '';
			if($model->phone == $attributeLabels['phone'])
				$model->phone = '';
			if($model->email == $attributeLabels['email'])
				$model->email = '';
			
			// validate user input and save contact
			
			$model->createDate=time();
			if($model->save()) {
				echo "1";
				$this->redirect($this->createUrl('contacts/view/?id='.$model->id));
			} //else print_r($model->getErrors());
		}
		$this->render('quickContact');
	}
	
	public function actionSearch(){
		$model=new Contacts;
		$attributeLabels = $model->attributeLabels();
		if(isset($_POST['Contacts'])){
			$model->attributes=$_POST['Contacts'];
			$firstName=true;
			$lastName=true;
			if($model->firstName == $attributeLabels['firstName'])
				$firstName=false;
			if($model->lastName == $attributeLabels['lastName'])
				$lastName=false;

			if($firstName && $lastName){
				$dataProvider=new CActiveDataProvider('Contacts', array(
					'criteria'=>array(
						'order'=>'lastName ASC',
						'condition'=>"firstName='$model->firstName' AND lastName='$model->lastName'"
				)));
			}else if($firstName && !$lastName){
				$dataProvider=new CActiveDataProvider('Contacts', array(
					'criteria'=>array(
						'order'=>'firstName ASC',
						'condition'=>"firstName='$model->firstName'"
				)));
			}else if(!$firstName && $lastName){
				$dataProvider=new CActiveDataProvider('Contacts', array(
					'criteria'=>array(
						'order'=>'lastName ASC',
						'condition'=>"lastName='$model->lastName'"
				)));
			}else{
				$this->redirect($this->createUrl('site/home'));
			}
			
			$this->render('viewAll',array(
				'dataProvider'=>$dataProvider,
			));
		}else{
			$this->render('search',array(
				'model'=>$model,
			));
		}
	}
	
	public function actionViewAll(){
		$model=new Contacts;
		
		
	}
	
	
	public function loadModel($id) {
		$model = X2Model::model('Contacts')->findByPk((int) $id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');
		return $model;
	}
}

?>
