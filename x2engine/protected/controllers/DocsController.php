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

class DocsController extends x2base {

	public $modelClass="DocChild";
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','createEmail','update','exportToHtml','changePermissions', 'delete'),
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

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->redirect(array('admin/viewPage/'.$id));
	}

	/**
	 * Creates a new doc.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
			$users=UserChild::getNames();
			unset($users['Anyone']);
			unset($users['admin']);
			unset($users[Yii::app()->user->getName()]);
			$model=new DocChild;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['DocChild'])) {
			$temp=$model->attributes;
			$model->attributes=$_POST['DocChild'];

			$arr=$model->editPermissions;
			if(isset($arr))
				$model->editPermissions=Accounts::parseUsers($arr);

			$model->createdBy=Yii::app()->user->getName();
			$model->createDate=time();
                        $changes=$this->calculateChanges($temp,$model->attributes);
			$model=$this->updateChangeLog($model,'Create');
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
			$users = UserChild::getNames();
			unset($users['Anyone']);
			unset($users['admin']);
			unset($users[Yii::app()->user->getName()]);
			$model = new DocChild;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['DocChild'])) {
			$temp=$model->attributes;
			$model->attributes=$_POST['DocChild'];
			$model->type = 'email';

			$model->editPermissions = '';
			// $arr=$model->editPermissions;
			// if(isset($arr))
				// $model->editPermissions=Accounts::parseUsers($arr);

			$model->createdBy=Yii::app()->user->getName();
			$model->createDate=time();
                        $changes=$this->calculateChanges($temp,$model->attributes);
			$model=$this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>null,
		));
	}
	
	public function actionChangePermissions($id){
		$model=$this->loadModel($id);
		if(Yii::app()->user->getName()=='admin' || Yii::app()->user->getName()==$model->createdBy){
			$users=UserChild::getNames();
			unset($users['admin']);
			unset($users['Anyone']);
			$str=$model->editPermissions;
			$pieces=explode(", ",$str);
			$model->editPermissions=$pieces;
			
			if(isset($_POST['DocChild'])){
				$model->attributes=$_POST['DocChild'];
				$arr=$model->editPermissions;
				
				$model->editPermissions=Accounts::parseUsers($arr);
				if($model->save()){
					$this->redirect(array('view','id'=>$id));
				}
			}
			
			$this->render('editPermissions',array(
				'model'=>$model,
				'users'=>$users,
			));
		}else{
			$this->redirect(array('view','id'=>$id));
		}
	}
        
    public function actionExportToHtml($id){
            
		$model=$this->loadModel($id);
		$file='doc.html';
		$fp=fopen($file,'w+');
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
		$link=CHtml::link(Yii::t('app','Download').'!',Yii::app()->request->baseUrl."/doc.html");
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
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
		$perm=$model->editPermissions;
		$pieces=explode(", ",$perm);
		if(Yii::app()->user->getName()=='admin' || Yii::app()->user->getName()==$model->createdBy || array_search(Yii::app()->user->getName(),$pieces)!==false || Yii::app()->user->getName()==$perm){  
			if(isset($_POST['DocChild']))
			{
				$model->attributes=$_POST['DocChild'];
                                
                                $model=$this->updateChangeLog($model,'Edited');
				if($model->save())
					$this->redirect(array('update','id'=>$model->id,'saved'=>true, 'time'=>time()));
			}

			$this->render('update',array(
				'model'=>$model,
			));
		
		}else{
			$this->redirect(array('view','id'=>$id));
		}
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$model=$this->loadModel($id);
                        $this->cleanUpTags($model);
                        $model->delete();

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
	public function actionIndex()
	{
		$model=new DocChild('search');
		$name="Docs";
                
                $attachments=new CActiveDataProvider('Media',array(
                    'criteria'=>array(
				'order'=>'createDate DESC',
				'condition'=>'associationType="docs"'
		)));
                
		$pageParam = ucfirst($this->modelClass). '_page';
		if (isset($_GET[$pageParam])) {
			$page = $_GET[$pageParam];
			Yii::app()->user->setState($this->id.'-page',(int)$page);
		} else {
			$page=Yii::app()->user->getState($this->id.'-page',1);
			$_GET[$pageParam] = $page;
		}

		if (intval(Yii::app()->request->getParam('clearFilters'))==1) {
			EButtonColumnWithClearFilters::clearFilters($this,$model);//where $this is the controller
		}
			$this->render('index',array(
			'model'=>$model,
                        'attachments'=>$attachments,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new DocChild('search');
		$name="Docs";
                
                $attachments=new CActiveDataProvider('Media',array(
                    'criteria'=>array(
				'order'=>'createDate DESC',
				'condition'=>'associationType="docs"'
		)));
                
		$pageParam = ucfirst($this->modelClass). '_page';
		if (isset($_GET[$pageParam])) {
			$page = $_GET[$pageParam];
			Yii::app()->user->setState($this->id.'-page',(int)$page);
		} else {
			$page=Yii::app()->user->getState($this->id.'-page',1);
			$_GET[$pageParam] = $page;
		}

		if (intval(Yii::app()->request->getParam('clearFilters'))==1) {
			EButtonColumnWithClearFilters::clearFilters($this,$model);//where $this is the controller
		}
			$this->render('admin',array(
			'model'=>$model,
                        'attachments'=>$attachments,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model = CActiveRecord::model('DocChild')->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='docs-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
