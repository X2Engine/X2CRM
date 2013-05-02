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
			// $model->price = $this->parseCurrency($model->price,false);
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
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model = CActiveRecord::model('Product')->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
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
