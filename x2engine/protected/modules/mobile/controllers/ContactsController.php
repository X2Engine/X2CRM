<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * @package X2CRM.modules.mobile.controllers
 */
class ContactsController extends MobileController{

	public $modelClass = 'Contacts';

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
		
		/*if(isset($_POST['ajax']) && $_POST['ajax']=='quick-contact-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}*/

		// collect user input data
		if(isset($_POST['Contacts'])) {
			// $this->redirect('http://www.google.com/');
			//$model->attributes = $_POST['Contacts'];
			$model->setX2Fields($_POST['Contacts']);
			//
				// $model->firstName = 'bob';
				// $model->lastName = 'dole';
				// $model->phone = '';
				// $model->email = '';
			$model->visibility = 1;
			
			// reset to blank if it's the default value
			/*if($model->firstName == $attributeLabels['firstName'])
				$model->firstName = '';
			if($model->lastName == $attributeLabels['lastName'])
				$model->lastName = '';
			if($model->phone == $attributeLabels['phone'])
				$model->phone = '';
			if($model->email == $attributeLabels['email'])
				$model->email = '';*/
			
			$model->createDate=time();

			// validate user input and save contact
			if($model->save()) {
				echo "1";
				$this->redirect(array('/mobile/contacts/view','id'=>$model->id));
			} //else print_r($model->getErrors());
		}
		$this->render('quickContact', array (
            'model' => $model
        ));
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
				$this->redirect($this->createUrl('/mobile/site/home'));
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
}

?>
