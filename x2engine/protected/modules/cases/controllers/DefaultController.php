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

class DefaultController extends x2base {
	public $modelClass = 'Cases';
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
        
        
        public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_cases WHERE name LIKE :qterm ORDER BY name ASC';
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
		$type='cases';
		$model=$this->loadModel($id);
		parent::view($model, $type);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new Cases;
                $users=User::getNames();
                if(isset($_POST['Cases'])) {
			$temp=$model->attributes;
			foreach($_POST['Cases'] as $name => &$value) {;
				if($value == $model->getAttributeLabel($name)){
					$value = '';
                                }
			}
                        
			 foreach($_POST as $key=>$arr){
                            $pieces=explode("_",$key);
                            if(isset($pieces[0]) && $pieces[0]=='autoselect'){
                                $newKey=$pieces[1];
                                if(isset($_POST[$newKey."_id"]) && $_POST[$newKey."_id"]!=""){
                                    $val=$_POST[$newKey."_id"];
                                }else{
                                    $field=Fields::model()->findByAttributes(array('fieldName'=>$newKey));
                                    if(isset($field)){
                                        $type=ucfirst($field->linkType);
                                        if($type!="Contacts"){
                                            eval("\$lookupModel=$type::model()->findByAttributes(array('name'=>'$arr'));");
                                        }else{
                                            $names=explode(" ",$arr);
                                            if(count($names)>1) 
                                                $lookupModel=CActiveRecord::model('Contacts')->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
                                        }
                                        if(isset($lookupModel))
                                            $val=$lookupModel->id;
                                        else
                                            $val=$arr;
                                    }
                                }
                                $model->$newKey=$val;
                            }
                        }
                        
			$temp=$model->attributes;
			foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Cases'][$field])){
                                $model->$field=$_POST['Cases'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Cases','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
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
                $fields=Fields::model()->findAllByAttributes(array('modelName'=>"Cases"));
                foreach($fields as $field){
                    if($field->type=='link'){
                        $fieldName=$field->fieldName;
                        $type=ucfirst($field->linkType);
                        if(is_numeric($model->$fieldName) && $model->$fieldName!=0){
                            eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
                            if(isset($lookupModel))
                                $model->$fieldName=$lookupModel->name;
                        }
                    }elseif($field->type=='date'){
                        $fieldName=$field->fieldName;
                        $model->$fieldName=date("Y-m-d",$model->$fieldName);
                    }
                }

		if(isset($_POST['Cases'])) {
			$temp=$model->attributes;
			foreach($_POST['Cases'] as $name => $value) {
				if($value == $model->getAttributeLabel($name)){
					$_POST['Cases'][$name] = '';
				}
			}
			foreach($_POST as $key=>$arr){
                            $pieces=explode("_",$key);
                            if(isset($pieces[0]) && $pieces[0]=='autoselect'){
                                $newKey=$pieces[1];
                                if(isset($_POST[$newKey."_id"]) && $_POST[$newKey."_id"]!=""){
                                    $val=$_POST[$newKey."_id"];
                                }else{
                                    $field=Fields::model()->findByAttributes(array('fieldName'=>$newKey));
                                    if(isset($field)){
                                        $type=ucfirst($field->linkType);
                                        if($type!="Contacts"){
                                            eval("\$lookupModel=$type::model()->findByAttributes(array('name'=>'$arr'));");
                                        }else{
                                            $names=explode(" ",$arr);
                                            if(count($names)>1) 
                                                $lookupModel=CActiveRecord::model('Contacts')->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
                                        }
                                        if(isset($lookupModel))
                                            $val=$lookupModel->id;
                                        else
                                            $val=$arr;
                                    }
                                }
                                $model->$newKey=$val;
                            }
                        }
                        
			$temp=$model->attributes;
			foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Cases'][$field])){
                                $model->$field=$_POST['Cases'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Cases','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
			
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
	public function actionIndex() {
		$model=new Cases('search');
		$this->render('index', array('model'=>$model));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new Cases('search');
		$this->render('admin', array('model'=>$model));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Cases::model()->findByPk((int)$id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='cases-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
