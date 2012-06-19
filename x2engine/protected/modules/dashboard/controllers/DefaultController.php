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
    public $modelClass = 'Dashboard';
    public $layout = '//layouts/col2Dash';
	public function accessRules() {
		return array(
            array('allow',
                 'actions'=>array('getItems'),
                 'users'=>array('*'), 
            ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','getItems','moveWidget','setAUTH','update','changeColumns'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','testScalability'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actions() {
		return array(
		);
	}

    public function actionChangeColumns(){
        $aasd = $_POST['dropdown'];
        $model = new Dashboard('search');
        if($item = $_POST['dropdown']){
            $this->render('admin',array(
                'model'=>$model,
                'item'=>$item,
            ));
        }
    }


    public function actionGetItems(){
        $uid = Yii::app()->user->getId();
		$sql = 'SELECT * FROM x2_widgets WHERE showDASH = 1 AND userid = '.$uid;
		$command = Yii::app()->db->createCommand($sql);
		$result = $command->queryAll();
        echo CJSON::encode($result); 
        exit;
	}
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model=$this->loadModel($id);	 
		$type='dashboard';
		parent::view($model, $type);
    }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
        
        public function create($model,$oldAttributes, $api){
            if($api==0)
                parent::create($model,$oldAttributes,$api);
            else
                return parent::create($model,$oldAttributes,$api);
        }
        
	public function actionCreate() {
		$model=new Dashboard;
		$users=User::getNames();
		unset($users['admin']);
        unset($users['']);
        if(isset($_POST['Dashboard'])){
            $temp = $model->attributes;
            foreach($_POST['Dashboard'] as $name => $value){
                if ($value = $model->getAttributeLabel($name))
                    $value = '';
            }
            foreach(array_keys($model->attributes) as $field){
                if(isset($_POST['Dashboard'][$field])){
                    $model->field = $_POST['Dashboard'][$field];
                    $fieldData = Fields::model()->findByAttributes(array('modelName'=>'Dashboard','fieldName'=>$field));
                }
            }
            $this->create($model,$temp,'0');
        }
		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
    public function update($model, $oldAttributes,$api){
            // process currency into an INT
            if($api==0)
                parent::update($model,$oldAttributes,$api);
            else
                return parent::update($model,$oldAttributes,$api);
    }
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		$users=User::getNames();
        $fields=Fields::model()->findAllByAttributes(array('modelName'=>"Dashboard"));
        if(isset($_POST['Dashboard'])){
            $temp=$model->attributes;
            foreach($_POST['Dashboard'] as $name => $value){
                if ($value == $model->getAttributeLabel($name)) 
                    $value = null;
            }
           $this->update($model,$temp,'0');
        }
        foreach(array_keys($model->attributes) as $field){
            if(isset($_POST['Dashboard'][$field])){
                $model->field = $_POST['Dashboard'][$field];
                $fieldData = Fields::model()->findByAttributes(array('modelName'=>'Dashboard', 'fieldName'=>$field));
            }
        }
		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
		));
    }
	/*
	public function actionSaveChanges($id) {
		$account=$this->loadModel($id);
		if(isset($_POST['Accounts'])) {
			$temp=$account->attributes;
			foreach($account->attributes as $field=>$value){
                            if(isset($_POST['Accounts'][$field])){
                                $account->$field=$_POST['Accounts'][$field];
                            }
                        }

			// process currency into an INT
			$account->annualRevenue = $this->parseCurrency($account->annualRevenue,false);
			$changes=$this->calculateChanges($temp,$account->attributes, $account);
			$account=$this->updateChangelog($account,$changes);
			$account->update();
			$this->redirect(array('view','id'=>$account->id));
		}
	}
        */
	public function actionAddUser($id) {
		$users=User::getNames();
	unset($users['admin']);
		unset($users['']);
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }
		$contacts=Contacts::getAllNames();
		$model=$this->loadModel($id);
		$users=Accounts::editUserArray($users,$model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Accounts'])) {
                    
			$temp=$model->assignedTo; 
			$tempArr=$model->attributes;
			$model->attributes=$_POST['Accounts'];  
			$arr=$_POST['Accounts']['assignedTo'];
			$model->assignedTo=Accounts::parseUsers($arr);
			if($temp!="")
				$temp.=", ".$model->assignedTo;
			else
				$temp=$model->assignedTo;
			$model->assignedTo=$temp;
			$changes=$this->calculateChanges($tempArr,$model->attributes);
			$model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
			'action'=>'Add'
		));
	} 

	public function actionRemoveUser($id) {

		$model=$this->loadModel($id);

		$pieces=explode(', ',$model->assignedTo); 
		$pieces=Sales::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Accounts'])) {
			$temp=$model->attributes;
			$model->attributes=$_POST['Accounts'];  
			$arr=$_POST['Accounts']['assignedTo'];

			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=Accounts::parseUsersTwo($pieces);

			$model->assignedTo=$temp;
			$changes=$this->calculateChanges($temp,$model->attributes);
			$model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$pieces,
			'action'=>'Remove'
		));
	}
        
        public function delete($id){
            
            $model=$this->loadModel($id);
            $dataProvider=new CActiveDataProvider('Actions', array(
                'criteria'=>array(
                    'condition'=>'associationId='.$id.' AND associationType=\'account\'',
            )));

            $actions=$dataProvider->getData();
            foreach($actions as $action){
                    $action->delete();
            }
            $this->cleanUpTags($model);
            $model->delete();
        }

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
					'condition'=>'associationId='.$id.' AND associationType=\'account\'',
			)));

			$actions=$dataProvider->getData();
			foreach($actions as $action){
				$action->delete();
			}
                        $this->cleanUpTags($model);
			$model->delete();
		} else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		
		$model=new Dashboard('search');
		$name='Dashboard';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
    public function actionAdmin() {
        $pageParam = ucfirst('Dashboard'). '_page';
        $downParam = 'down';
        if (isset($_GET[$pageParam])) {
            $page = $_GET[$pageParam];
            Yii::app()->user->setState($this->id.'_page',(int)$page);
        } else {
            $URL = Yii::app()->request->requestUri.'?Dashboard_page=1';
            $this->redirect($URL,true,302);
        }
		$model=new Dashboard('search');
        $name='Dashboard';
        $this->render('admin',array(
            'model'=>$model,
            'item'=>0,
        ));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Dashboard::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
