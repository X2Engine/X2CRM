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
 * @package X2CRM.modules.products.controllers 
 */
class ProductsController extends x2base {
	public $modelClass = 'Product';

	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index', 'view', 'search','getItems'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','testScalability', 'create', 'update', 'delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
        
        public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_products WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {

        // add product to user's recent item list
        User::addRecentItem('r', $id, Yii::app()->user->getId()); 

		$model = $this->loadModel($id);
		parent::view($model);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new Product;
		$users=User::getNames();
		if(isset($_POST['Product'])) {
			$temp=$model->attributes;
			$model->setX2Fields($_POST['Product']);
			// $model->price = Formatter::parseCurrency($model->price,false);
			$model->createDate=time();
			
  
			parent::create($model, $temp, 0);
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
		$model = $this->loadModel($id);
		$users=User::getNames(); 
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Product'));
		foreach($fields as $field){
			if($field->type=='link'){
				$fieldName=$field->fieldName;
				$type=ucfirst($field->linkType);
				if(is_numeric($model->$fieldName) && $model->$fieldName!=0){
					eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
					if(isset($lookupModel))
						$model->$fieldName=$lookupModel->name;
				}
			}
		}
		if(isset($_POST['Product'])) {
			$temp=$model->attributes;
			$model->setX2Fields($_POST['Product']);
			
			// generate history
			$action = new Actions;
			$action->associationType = 'product';
			$action->associationId = $model->id;
			$action->associationName = $model->name;
			$action->assignedTo = Yii::app()->user->getName();
			$action->completedBy=Yii::app()->user->getName();
			$action->dueDate = time();
			$action->completeDate = time();
			$action->visibility = 1;
			$action->complete='Yes';
		
			$action->actionDescription = "Update: <b>{$model->name}</b>
			Type: <b>{$model->type}</b>
			Price: <b>{$model->price}</b>
			Currency: <b>{$model->currency}</b>
			Inventory: <b>{$model->inventory}</b>";
			$action->save();		 
			parent::update($model,$temp,'0');
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {	// we only allow deletion via POST request
			$model = $this->loadModel($id);
			
			$model->clearTags();
			$model->delete();
			
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		} else {
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Product('search');
		$this->render('index', array('model'=>$model));
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='product-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
