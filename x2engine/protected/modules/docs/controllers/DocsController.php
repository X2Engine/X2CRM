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
 * @package X2CRM.modules.docs.controllers 
 */
class DocsController extends x2base {

	public $modelClass = 'Docs';
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	// public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',
				'users'=>array('*'), 
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','createEmail','update','exportToHtml','changePermissions', 'delete', 'getItems', 'getItem'),
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

	public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_docs WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = '%'.$_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}

	public function actionGetItem($id) {
        $model = $this->loadModel($id);
        if((($model->visibility==1 || ($model->visibility==0 && $model->createdBy==Yii::app()->user->getName())) || Yii::app()->user->checkAccess('AdminIndex'))){ 
            echo $model->text;
        }
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model = CActiveRecord::model('Docs')->findByPk($id);
		if(isset($model)){
			$permissions=explode(", ",$model->editPermissions);
			if(in_array(Yii::app()->user->getName(),$permissions))
				$editFlag=true;
			else
				$editFlag=false;
		}
		//echo $model->visibility;exit;
		if (!isset($model) || 
			   !(($model->visibility==1 || 
				($model->visibility==0 && $model->createdBy==Yii::app()->user->getName())) || 
				Yii::app()->user->checkAccess('AdminIndex')|| $editFlag))
			$this->redirect(array('docs/index'));

		$this->render('view', array(
			'model' => $model,
		));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionFullView($id) {
	
		$model = $this->loadModel($id);
	
		echo $model->text;
	}

	/**
	 * Creates a new doc.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($duplicate = false) {
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->getName()]);
		$model = new Docs;

		if($duplicate) {
			$copiedModel = Docs::model()->findByPk($duplicate);
			if(!empty($copiedModel)) {
				foreach($copiedModel->attributes as $name=>$value)
					if($name != 'id')
						$model->$name = $value;
			}
			$model->name .= ' ('.Yii::t('docs','copy').')';
		}

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Docs'])) {
			$temp = $model->attributes;
			$model->attributes=$_POST['Docs'];
            $model->visibility=$_POST['Docs']['visibility'];

			$arr = $model->editPermissions;
			if(isset($arr))
				if(is_array($arr))
					$model->editPermissions = Accounts::parseUsers($arr);

			$model->createdBy = Yii::app()->user->getName();
			$model->createDate = time();
			// $changes=$this->calculateChanges($temp,$model->attributes);
			// $model=$this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
	
	/**
	 * Creates an email template.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreateEmail() {
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->getName()]);
		$model = new Docs;
		$model->type = 'email';
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Docs'])) {
			$temp = $model->attributes;
			$model->attributes = $_POST['Docs'];
            $model->visibility = $_POST['Docs']['visibility'];
			$model->editPermissions = '';
			// $arr=$model->editPermissions;
			// if(isset($arr))
				// $model->editPermissions=Accounts::parseUsers($arr);

			$model->createdBy = Yii::app()->user->getName();
			$model->createDate = time();
			// $changes = $this->calculateChanges($temp,$model->attributes);
			// $model = $this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>null,
		));
	}
	
	public function actionCreateQuote() {
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->getName()]);
		$model = new Docs;
		$model->type = 'quote';
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Docs'])) {
			$temp = $model->attributes;
			$model->attributes = $_POST['Docs'];
            $model->visibility = $_POST['Docs']['visibility'];
			$model->editPermissions = '';
			// $arr=$model->editPermissions;
			// if(isset($arr))
				// $model->editPermissions=Accounts::parseUsers($arr);

			$model->createdBy = Yii::app()->user->getName();
			$model->createDate = time();
			// $changes = $this->calculateChanges($temp,$model->attributes);
			// $model = $this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>null,
		));
	}
	
	public function actionChangePermissions($id){
		$model = $this->loadModel($id);
		if(Yii::app()->user->checkAccess('AdminIndex') || Yii::app()->user->getName()==$model->createdBy) {
			$users = User::getNames();
			unset($users['admin']);
			unset($users['Anyone']);
			$str = $model->editPermissions;
			$pieces = explode(", ",$str);
			$model->editPermissions=$pieces;
			
			if(isset($_POST['Docs'])) {
				$model->attributes = $_POST['Docs'];
				$arr=$model->editPermissions;
				
				$model->editPermissions = Accounts::parseUsers($arr);
				if($model->save()) {
					$this->redirect(array('view','id'=>$id));
				}
			}
			
			$this->render('editPermissions',array(
				'model'=>$model,
				'users'=>$users,
			));
		} else {
			$this->redirect(array('view','id'=>$id));
		}
	}
		
	public function actionExportToHtml($id){
		$model = $this->loadModel($id);
		$file = 'doc.html';
		$fp = fopen($file,'w+');
		$data="<style>
				#wrap{
					width:6.5in;
					height:9in;
					margin-top:auto;
					margin-left:auto;
					margin-bottom:auto;
					margin-right:auto;
				}
				</style>
				<div id='wrap'>
			".$model->text."</div>";
		fwrite($fp, $data);
		fclose($fp);
		$link = CHtml::link(Yii::t('app','Download').'!',Yii::app()->request->baseUrl."/doc.html");
		$this->render('export',array(
			'model'=>$model,
			'link'=>$link,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
		$perm = $model->editPermissions;
		$pieces = explode(', ',$perm);
		if(Yii::app()->user->checkAccess('DocsAdmin') || Yii::app()->user->getName()==$model->createdBy || array_search(Yii::app()->user->getName(),$pieces)!==false || Yii::app()->user->getName()==$perm) {  
			if(isset($_POST['Docs'])) {
				$model->attributes = $_POST['Docs'];
                $model->visibility = $_POST['Docs']['visibility'];
				// $model=$this->updateChangeLog($model,'Edited');
				if($model->save()) {
					$event = new Events;
					$event->associationType='Docs';
					$event->associationId=$model->id;
					$event->type='doc_update';
					$event->user=Yii::app()->user->getName();
					$event->visibility=$model->visibility;
					$event->save();
					$this->redirect(array('update','id'=>$model->id,'saved'=>true, 'time'=>time()));
                }
			}

			$this->render('update',array(
				'model'=>$model,
			));
		} else {
			$this->redirect(array('view','id'=>$id));
		}
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$model = $this->loadModel($id);
			$this->cleanUpTags($model);
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		} else throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model = new Docs('search');
		
		$attachments=new CActiveDataProvider('Media',array(
			'criteria'=>array(
			'order'=>'createDate DESC',
			'condition'=>'associationType="docs"'
		)));
				
		$this->render('index',array(
			'model'=>$model,
			'attachments'=>$attachments,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model = CActiveRecord::model('Docs')->findByPk($id);
		if($model === null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='docs-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public function actionAutosave($id) {
		$model = $this->loadModel($id);
		if(isset($_POST['Docs'])) {
			$model->attributes = $_POST['Docs'];
			// $model = $this->updateChangeLog($model,'Edited');
			if($model->save()) {
				echo Yii::t('Docs', 'Saved at') . ' ' . Yii::app()->dateFormatter->format(Yii::app()->locale->getTimeFormat('medium'), time());
			};
		}
	}
}
