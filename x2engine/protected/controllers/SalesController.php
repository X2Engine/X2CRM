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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

class SalesController extends x2base {

	public $modelClass = 'Sales';
		
	public function accessRules() {
		return array(
                        array('allow',
                            'actions'=>array('getItems'),
                            'users'=>array('*'), 
                        ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','addUser','addContact','removeUser','removeContact',
                                    'saveChanges','delete','shareSale','inlineEmail'),
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
			'inlineEmail'=>array(
				'class'=>'InlineEmailAction',
			),
		);
	}
        
        public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_sales WHERE name LIKE :qterm ORDER BY name ASC';
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
		$type = 'sales';
		$model = $this->loadModel($id);
		$model->assignedTo = UserChild::getUserLinks($model->assignedTo);
		$model->associatedContacts = Contacts::getContactLinks($model->associatedContacts);
		
		parent::view($model, $type);
	}
	
	public function actionShareSale($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('sales','Sale Record Details')." \n
".Yii::t('sales','Name').": $model->name
".Yii::t('sales','Description').": $model->description
".Yii::t('sales','Quote Amount').": $model->quoteAmount
".Yii::t('sales','Sales Stage').": $model->salesStage
".Yii::t('sales','Lead Source').": $model->leadSource
".Yii::t('sales','Probability').": $model->probability
".Yii::t('app','Link').": ".'http://'.Yii::app()->request->getServerName().$this->createUrl('sales/view/'.$model->id);
		
		$body = trim($body);

		$errors = array();
		$status = array();
		$email = array();
		if(isset($_POST['email'], $_POST['body'])){
		
			$subject = Yii::t('sales','Sale Record Details');
			$email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
			$body = $_POST['body'];
			// if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
			if($email['to'] === false)
				$errors[] = 'email';
			if(empty($body))
				$errors[] = 'body';
			
			if(empty($errors))
				$status = $this->sendUserEmail($email,$subject,$body);

			if(array_search('200',$status)) {
				$this->redirect(array('view','id'=>$model->id));
				return;
			}
			if($email['to'] === false)
				$email = $_POST['email'];
			else
				$email = $this->mailingListToString($email['to']);
		}
		$this->render('shareSale',array(
			'model'=>$model,
			'body'=>$body,
			'currentWorkflow'=>$this->getCurrentWorkflow($model->id,'sales'),
			'email'=>$email,
			'status'=>$status,
			'errors'=>$errors
		));
	}
	
	public function create($model,$oldAttributes,$api=0){
		
		if(isset($_POST['companyAutoComplete']) && $model->accountName==""){
			$model->accountName=$_POST['companyAutoComplete'];
			$model->accountId="";
		}
		// process currency into an INT
		$model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);
		
		if(isset($model->associatedContacts))
				$model->associatedContacts = Sales::parseContacts($model->associatedContacts);
		$model->createDate=time();
                $model->lastUpdated=time();
		if($model->expectedCloseDate!=""){
				$model->expectedCloseDate=strtotime($model->expectedCloseDate);
		}
		parent::create($model,$oldAttributes,'0');
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model = new Sales;
		$users = UserChild::getNames();
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }
		$contacts = Contacts::getAllNames();
		unset($users['admin']);
		unset($users['']);
		unset($contacts['0']);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
                    $temp=$model->attributes;
                    foreach($_POST['Sales'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name))
                                    $value = '';
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
                                            $lookupModel=Contacts::model()->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
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
                    foreach(array_keys($model->attributes) as $field){
                        if(isset($_POST['Sales'][$field])){
                            $model->$field=$_POST['Sales'][$field];
                            if(is_array($model->$field))
                                    $model->$field=Accounts::parseUsers($model->$field);
                        }
                    }
                    
                    $this->create($model,$temp);
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
		));
	}
        
        public function update($model,$oldAttributes,$api=0){
            
            // process currency into an INT
            $model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);

            $arr=$model->associatedContacts;
            if(isset($model->associatedContacts)){
                foreach($model->associatedContacts as $contact){
                    $rel=new Relationships;
                    $rel->firstType='Contacts';
                    $rel->firstId=$contact;
                    $rel->secondType='Sales';
                    $rel->secondId=$model->id;
                    $rel->save();
                }
                    $model->associatedContacts=Sales::parseContacts($arr);
            }
            $model->createDate=time();
            if($model->expectedCloseDate!=""){
                    $model->expectedCloseDate=strtotime($model->expectedCloseDate);
            }
            
            parent::update($model,$oldAttributes,'0');
        }
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		if($model->expectedCloseDate!=""){
			$model->expectedCloseDate=date("Y-m-d H:i",$model->expectedCloseDate);
		}
		$users=UserChild::getNames();
		unset($users['admin']);
		unset($users['']);
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }
		$contacts=Contacts::getAllNames();
		unset($contacts['0']);
		
		$curUsers=$model->assignedTo;
		$userPieces=explode(', ',$curUsers);
		$arr=array();
		foreach($userPieces as $piece){
			$arr[]=$piece;
		}
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>"Sales"));
                foreach($fields as $field){
                    if($field->type=='link'){
                        $fieldName=$field->fieldName;
                        $type=$field->linkType;
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
		$model->assignedTo=$arr;
		
		$curContacts=$model->associatedContacts;
		$contactPieces=explode(" ",$curContacts);
		$arr=array();
		foreach($contactPieces as $piece){
			$arr[]=$piece;
		}
		
		$model->associatedContacts=$arr;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
                    $temp=$model->attributes;
                    foreach($_POST['Sales'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name))
                                    $value = '';
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
                                            $lookupModel=Contacts::model()->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
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
                    foreach(array_keys($model->attributes) as $field){
                        if(isset($_POST['Sales'][$field])){
                            $model->$field=$_POST['Sales'][$field];
                            if(is_array($model->$field))
                                    $model->$field=Accounts::parseUsers($model->$field);
                        }
                    }

                    $this->update($model,$temp);
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
		));
	}
	/*
	public function actionSaveChanges($id) {
		$sale=$this->loadModel($id);
		if(isset($_POST['Sales'])) {
			$temp=$sale->attributes;
			foreach($sale->attributes as $field=>$value){
                            if(isset($_POST['Sales'][$field])){
                                $sale->$field=$_POST['Sales'][$field];
                            }
                        }
			
			// process currency into an INT
			$sale->quoteAmount = $this->parseCurrency($sale->quoteAmount,false);
			
			
			if($sale->expectedCloseDate!=""){
				$sale->expectedCloseDate=strtotime($sale->expectedCloseDate);
			}
			$changes=$this->calculateChanges($temp,$sale->attributes, $sale);
			$sale=$this->updateChangelog($sale,$changes);
			$sale->save();
			$this->redirect(array('view','id'=>$sale->id));
		}
	}
        */
	public function actionAddUser($id) {
		$users=UserChild::getNames();
		unset($users['admin']);
		unset($users['']);
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }
		$contacts=Contacts::getAllNames();
                unset($contacts['0']);
		$model=$this->loadModel($id);
		$users=Sales::editUserArray($users,$model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
			$temp=$model->assignedTo; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->assignedTo;
			

			$model->assignedTo=Sales::parseUsers($arr);
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

	public function actionAddContact($id) {
		$users=UserChild::getNames();
		unset($users['admin']);
		unset($users['']);
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }
		$contacts=Contacts::getAllNames();
                unset($contacts['0']);
		$model=$this->loadModel($id);

		$contacts=Sales::editContactArray($contacts, $model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
			$temp=$model->associatedContacts; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->associatedContacts;
                        foreach($arr as $contactId){
                            $rel=new Relationships;
                            $rel->firstType='Contacts';
                            $rel->firstId=$contactId;
                            $rel->secondType='Sales';
                            $rel->secondId=$model->id;
                            $rel->save();
                        }
			

			$model->associatedContacts=Sales::parseContacts($arr);
			$temp.=" ".$model->associatedContacts;
			$model->associatedContacts=$temp;
                        $changes=$this->calculateChanges($tempArr,$model->attributes);
                        $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addContact',array( 
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

		if(isset($_POST['Sales'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->assignedTo;
			
			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=Sales::parseUsersTwo($pieces);

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

	public function actionRemoveContact($id) {

		$model=$this->loadModel($id);
		$pieces=explode(" ",$model->associatedContacts);
		$pieces=Sales::editContactsInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales']))
		{
                        $temp=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->associatedContacts;
			
			
			foreach($arr as $id=>$contact){
                                $rel=CActiveRecord::model('Relationships')->findByAttributes(array('firstType'=>'Contacts','firstId'=>$contact,'secondType'=>'Sales','secondId'=>$model->id));
                                if(isset($rel))
                                    $rel->delete();
				unset($pieces[$contact]);
			}
			
			$temp2=Sales::parseContactsTwo($pieces);

			$model->associatedContacts=$temp2;
                        $changes=$this->calculateChanges($temp,$model->attributes);
                        $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addContact',array(
			'model'=>$model,
			'contacts'=>$pieces,
			'action'=>'Remove'
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Sales('search');
		$name='Sales';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new Sales('search');
		$name='Sales';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Sales::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}
        
        public function delete($id){
            $model=$this->loadModel($id);
            $dataProvider=new CActiveDataProvider('Actions', array(
                    'criteria'=>array(
                    'condition'=>'associationId='.$id.' AND associationType=\'sale\'',
            )));
            $actions=$dataProvider->getData();
            foreach($actions as $action){
                    $action->delete();
            }
            $this->cleanUpTags($model);
            $model->delete();
        }

	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
				'condition'=>'associationId='.$id.' AND associationType=\'sale\'',
			)));
			$actions=$dataProvider->getData();
			foreach($actions as $action){
				$action->delete();
			}
                        $this->cleanUpTags($model);
			$model->delete();
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	public function actionGetTerms(){
		$sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
}