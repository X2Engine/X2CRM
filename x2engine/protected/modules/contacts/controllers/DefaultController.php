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

	public $modelClass = 'Contacts';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('getItems'),
				'users'=>array('*'), 
			),
			array('allow',	// allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index',
					'list',
					'lists',
					'viewMy',
					'view',
					'update',
					'create',
					'quickContact',
					'import',
					'importContacts',
					'viewNotes',
					'search',
					'addNote',
					'deleteNote',
					'saveChanges',
					'createAction',
					'importExcel',
					'export',
					'getTerms',
					'getContacts',
					'delete',
					'shareContact',
					'viewSales',
					'createList',
					'createListFromSelection',
					'updateList',
					'addToList',
					'removeFromList',
					'deleteList',
					'inlineEmail',
					'quickUpdateHistory',
				),
				'users'=>array('@'),
			),
			array('allow',	// allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array(
					'admin','testScalability'
				),
				'users'=>array('admin'),
			),
			array('deny',	// deny all users
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
	
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {

		$contact = $this->loadModel($id);
                $viewPermissions=($contact->assignedTo == Yii::app()->user->getName() || $contact->visibility == 1 || Yii::app()->user->getName() == 'admin');
                /* x2temp */
                $groups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
                $temp=array();
                foreach($groups as $group){
                    $temp[]=$group->groupId;
                }
                if(array_search($contact->assignedTo,$temp)!==false){
                    $viewPermissions=true;
                }
                if($contact->visibility=='2'){
                    $user=UserChild::model()->findByAttributes(array('username'=>$contact->assignedTo));
                    $groups=GroupToUser::model()->findAllByAttributes(array('userId'=>$user->id));
                    $tempOne=array();
                    foreach($groups as $group){
                        $tempOne[]=$group->groupId;
                    }
                    $userGroups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
                    $tempTwo=array();
                    foreach($userGroups as $userGroup){
                        $tempTwo[]=$userGroup->groupId;
                    }
                    if(count(array_intersect($tempOne,$tempTwo))>0){
                        $viewPermissions=true;
                    }
                }
                if(is_numeric($contact->assignedTo)){
                    $contact->assignedTo=Groups::model()->findByPk($contact->assignedTo)->name;
                }
                /* end x2temp */
		if ($viewPermissions) {
			UserChild::addRecentItem('c',$id,Yii::app()->user->getId());	////add contact to user's recent item list
			parent::view($contact, 'contacts');
		} else
			$this->redirect('index');
	}
	
	public function actionViewSales($id){
		
		$sales=Relationships::model()->findAllByAttributes(array('firstType'=>'Contacts','firstId'=>$id,'secondType'=>'Sales'));
		$temp=array();
		foreach($sales as $sale){
			$temp[]=Sales::model()->findByPk($sale->secondId);
		}
		$sales=$temp;
		$model=$this->loadModel($id);
		
		$this->render('viewSales',array(
			'sales'=>$sales,
			'model'=>$model,
		));
	}
	
	public function actionGetTerms(){
		$sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
	
	public function actionGetContacts() {
		$sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm ORDER BY firstName ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
        
        public function actionGetItems() {
		$sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm ORDER BY firstName ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
        
	
	public function actionShareContact($id) {
		$users=UserChild::getNames();
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('contacts','Contact Record Details')." \n
".Yii::t('contacts','Name').": $model->firstName $model->lastName 
".Yii::t('contacts','E-Mail').": $model->email 
".Yii::t('contacts','Phone').": $model->phone 
".Yii::t('contacts','Account').": $model->company 
".Yii::t('contacts','Address').": $model->address 
$model->city, $model->state $model->zipcode 
".Yii::t('contacts','Background Info').": $model->backgroundInfo 
".Yii::t('app','Link').": ".'http://'.Yii::app()->request->getServerName().$this->createUrl('contacts/view/'.$model->id);

		$body = trim($body);

		$errors = array();
		$status = array();
		$email = array();
		if(isset($_POST['email'], $_POST['body'])){
		
			$subject = Yii::t('contacts','Contact Record Details');
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
		$this->render('shareContact',array(
			'model'=>$model,
			'users'=>$users,
			'body'=>$body,
			'currentWorkflow'=>$this->getCurrentWorkflow($model->id,'contacts'),
			'email'=>$email,
			'status'=>$status,
			'errors'=>$errors
		));
	}
	
	// Creates contact record
	public function create($model, $oldAttributes, $api){
		$model->createDate=time();
		$model->lastUpdated=time();
		if($api==0)
			parent::create($model,$oldAttributes,$api);
		else
			return parent::create($model,$oldAttributes,$api);
	}

	// Controller/action wrapper for create()
	public function actionCreate() {
		$model = new Contacts;
		$name='Contacts';
		$users=UserChild::getNames();
		$accounts=Accounts::getNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Contacts'])) {
			// clear values that haven't been changed from the default
			foreach($_POST['Contacts'] as $name => &$value) {
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
                                            if(count($names)>1) 
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
                        
			$temp=$model->attributes;
			foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Contacts'][$field])){
                                $model->$field=$_POST['Contacts'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Contacts','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
                        if(!isset($model->visibility))
                            $model->visibility=1;
			$this->create($model,$temp,'0'); 
			
		}
		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'accounts'=>$accounts,
		));
	}
	
	public function actionQuickContact() {
		//exit("ha");
		
		$model = new Contacts;
		$attributeLabels = $model->attributeLabels();
		
		// if it is ajax validation request
		// if(isset($_POST['ajax']) && $_POST['ajax']=='quick-contact-form') {
			// echo CActiveForm::validate($model);
			// Yii::app()->end();
		// }

		// collect user input data
		if(isset($_POST['Contacts'])) {
			// clear values that haven't been changed from the default
			foreach($_POST['Contacts'] as $name => &$value) {
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
                                            if(count($names)>1) 
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
			$temp=$model->attributes;
			foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Contacts'][$field])){
                                $model->$field=$_POST['Contacts'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Contacts','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                            }
                        }

			$model->visibility = 1;
			// validate user input and save contact
			$changes=$this->calculateChanges($temp,$model->attributes, $model);
			$model=$this->updateChangelog($model,'Create');
			$model->createDate=time();
			if($model->save()) {
				$this->renderPartial('application.components.views.quickContact',array());
			} //else print_r($model->getErrors());
		}
	}
	/*
	public function actionSaveChanges($id) {
		$contact=$this->loadModel($id);
		if(isset($_POST['Contacts'])) {
			// clear values that haven't been changed from the default
			foreach($_POST['Contacts'] as $name => $value) {
				if($value == $contact->getAttributeLabel($name)){
					$_POST['Contacts'][$name] = '';
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
			$temp=$contact->attributes;
                        foreach(array_keys($contact->attributes) as $field){
                            if(isset($_POST['Contacts'][$field])){
                                $contact->$field=$_POST['Contacts'][$field];
                            }
                        }
                        $contact->company=$_POST['companyAutoComplete'];
			if($contact->save()){
				$changes=$this->calculateChanges($temp,$contact->attributes, $contact);
                                $contact=$this->updateChangelog($contact,$changes);
			}
			$this->redirect(array('view','id'=>$contact->id));
		} else
			$this->redirect(array('view','id'=>$contact->id));
		
	}
        */
	// Updates a contact record
	public function update($model,$oldAttributes, $api){
		
		if($api==0)
			parent::update($model,$oldAttributes,$api);
		else
			return parent::update($model,$oldAttributes,$api);
	}
	

	// Controller/action wrapper for update()
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
		$users=UserChild::getNames();
		$accounts=Accounts::getNames(); 
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>"Contacts"));
		foreach($fields as $field){
			if($field->type=='link'){
				$fieldName=$field->fieldName;
				$type=ucfirst($field->linkType);
				if(is_numeric($model->$fieldName) && $model->$fieldName!=0){
					eval("\$newLookupModel=$type::model()->findByPk(".$model->$fieldName.");");
					if(isset($newLookupModel))
						$model->$fieldName=$newLookupModel->name;
				}
			}elseif($field->type=='date'){
				$fieldName=$field->fieldName;
				if(is_numeric($model->$fieldName))
					$model->$fieldName=date("Y-m-d",$model->$fieldName);
			}
		}
		
		foreach($model->fields as $field) {
			$value = $model->$field['fieldName'];
			if($field['type']=='link') {
				if(is_numeric($value) && $value!=0) {
					$linkModel = CActiveRecord::model(ucfirst($field['linkType']))->findByPk($value);
					if(isset($linkModel))
						$model->$field['fieldName'] = $linkModel->name;
				}
			} elseif($field['type']=='date') {
				if(is_numeric($model->$field['fieldName']))
					$model->$field['fieldName'] = date("Y-m-d",$value);
			}
		}
		
		// $fields = Fields::model()->findAllByAttributes(array('modelName'=>"Contacts"));
		// foreach($fields as $field) {
			// if($field->type=='link') {
				// $fieldName=$field->fieldName;
				// $type=ucfirst($field->linkType);
				// if(is_numeric($model->$fieldName) && $model->$fieldName!=0) {
					// eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
					// if(isset($lookupModel))
						// $model->$fieldName=$lookupModel->name;
				// }
			// } elseif($field->type=='date') {
				// $fieldName=$field->fieldName;
				// if(is_numeric($model->$fieldName))
					// $model->$fieldName=date("Y-m-d",$model->$fieldName);
			// }
		// }

		if(isset($_POST['Contacts'])) {
			$oldAttributes = $model->attributes;
			foreach($_POST['Contacts'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name)){
					$_POST['Contacts'][$name] = '';
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
                                            $lookupModel=$type::model()->findByAttributes(array('name'=>$arr));
                                        }else{
                                            $names=explode(" ",$arr);
                                            if(count($names)>1) 
                                                $lookupModel=Contacts::model()->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
                                        }
                                        if(isset($lookupModel)){
                                            $val=$lookupModel->id;
                                        }else{
                                            if(isset($arr))
                                                $val=$arr;
                                            else
                                                $val="";
                                        }
                                    }
                                }
                                $model->$newKey=$val;
                                unset($lookupModel);
                            }
                            
                        }
			foreach(array_keys($model->attributes) as $field){
				if(isset($_POST['Contacts'][$field])){
					$model->$field=$_POST['Contacts'][$field];
					$fieldData=Fields::model()->findByAttributes(array('modelName'=>'Contacts','fieldName'=>$field));
					if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
						$model->$field=Accounts::parseUsers($model->$field);
					}elseif($fieldData->type=='date'){
						$model->$field=strtotime($model->$field);
					}
				}
			}
			
			$this->update($model,$oldAttributes,false);
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'accounts'=>$accounts,
		));
	}

	// Default action - displays all visible Contact Lists
	public function actionLists() {
	
		// Yii:app()->params->groups
	
		$criteria = new CDbCriteria(array());
		
		if(Yii::app()->user->getName() != 'admin') {
			$criteria->addCondition('assignedTo="' . Yii::app()->user->getName() . '"');
			$criteria->addCondition('visibility = 1','OR');
			if(count(Yii::app()->params->groups))
				$criteria->addCondition('assignedTo IN ('.implode(',',Yii::app()->params->groups).')','OR');
		}
	
		$contactLists = new CActiveDataProvider('X2List', array(
			'sort'=>array(
				'defaultOrder'=>'createDate DESC',
			),
			// 'pagination'=>array(
				// 'pageSize'=>ProfileChild::getResultsPerPage(),
			// ),
			'criteria'=>$criteria
		));
	
		// $model = new Contacts('search');
		
		// $contactLists = ContactList::model()->findAll();
	
		/* $contactLists = new CActiveDataProvider('X2List', array(
			'sort'=>array(
				'defaultOrder'=>'createDate DESC',
			),
			// 'pagination'=>array(
				// 'pageSize'=>ProfileChild::getResultsPerPage(),
			// ),
			//'criteria'=>array('condition' => 'assignedTo="' . Yii::app()->user->getName() . '" OR visibility = 1'),
		)); */
		// $tempArr=array();
		// foreach($contactLists->getData() as $contact){
			// $flag=false;
			// if($contact->assignedTo || $contact->visibility=='1'){
				// $tempArr[]=$contact;
				// $flag=true;
			// }
			/* x2temp */
			// if(!$flag){
				// $groups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
				// $temp=array();
				// foreach($groups as $group){
					// $temp[]=$group->groupId;
				// }
				// if(array_search($contact->assignedTo,$temp)!==false){
					// $tempArr[]=$contact;
				// }
				// if(is_numeric($contact->assignedTo)){
					// $contact->assignedTo=Groups::model()->findByPk($contact->assignedTo)->name;
				// }
			// }
			/* end x2temp */
		// }
		// $contactLists->setData($tempArr);

		$totalContacts = CActiveRecord::model('Contacts')->count();
		$str='assignedTo="'.Yii::app()->user->getName().'"';
		/* x2temp */
		$groupLinks=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
		$temp="(";
		foreach($groupLinks as $link){
			$temp.=$link->groupId.", ";
		}
		$temp=substr($temp,0,-2).")";
		if(count($temp)>2)
			$str.=" || assignedTo IN ".$temp;
		/* end x2temp */
		$totalMyContacts = CActiveRecord::model('Contacts')->count($str);
		
		$allContacts = new X2List;
		$allContacts->attributes = array(
			'id' => 'all',
			'campaignId' => 0,
			'name' => Yii::t('contacts','All Contacts'),
			'description' => '',
			'type' => 'dynamic',
			'visibility' => 1,
			'count' => $totalContacts,
			'createDate' => 0,
			'lastUpdated' => 0,
		);
		$myContacts = new X2List;
		$myContacts->attributes = array(
			'id' => 'my',
			'campaignId' => 0,
			'name' => Yii::t('contacts','My Contacts'),
			'description' => '',
			'type' => 'dynamic',
			'visibility' => 1,
			'count' => $totalMyContacts,
			'createDate' => 0,
			'lastUpdated' => 0,
		);

			
		$contactListData = $contactLists->getData();
		// $contactListData[] = $allContacts3;
		$contactListData[] = $myContacts;
		$contactListData[] = $allContacts;
		$contactLists->setData($contactListData);
		
		
		$this->render('listIndex',array(
			'contactLists'=>$contactLists,
		));
	}

	// Lists all contacts assigned to this user
	public function actionViewMy() {
		$model = new Contacts('search');
		$name='Contacts';
		parent::index($model,$name);
	}
	
	// Lists all visible contacts
	public function actionIndex() {
		$model = new Contacts('search');
		$name = 'Contacts';
		parent::index($model,$name);
	}

	// Shows contacts in the specified list
	public function actionList() {
	
		$id = isset($_GET['id'])? $_GET['id'] : 'all';

		if(is_numeric($id))
			$list = CActiveRecord::model('X2List')->findByPk($id);
		if(isset($list)) {

			$model = new Contacts('searchList');
			$dataProvider = $model->searchList($id);
			
			$this->render('list',array(
				'listModel'=>$list,
				// 'listName'=>$list->name,
				// 'listId'=>$id,
				'dataProvider'=>$dataProvider,
				'model'=>$model,
			));
			
		} else {
			// $model=new Contacts('search');

			// $pageParam = ucfirst($this->modelClass). '_page';
			// if (isset($_GET[$pageParam])) {
				// $page = $_GET[$pageParam];
				// Yii::app()->user->setState($this->id.'-page',(int)$page);
			// } else {
				// $page=Yii::app()->user->getState($this->id.'-page',1);
				// $_GET[$pageParam] = $page;
			// }

			// if (intval(Yii::app()->request->getParam('clearFilters'))==1) {
				// EButtonColumnWithClearFilters::clearFilters($this,$model);//where $this is the controller
			// }
			// die($id);
			if($id = 'all')
				$this->redirect(array('/contacts/index'));
				// $dataProvider = CActiveRecord::model('Contacts')->searchAll();
			else
				$this->redirect(array('/contacts/viewMy'));
				// $dataProvider = CActiveRecord::model('Contacts')->search();

			// $this->render('index',array(
				// 'model'=>$model,
				// 'dataProvider'=>$dataProvider,
			// ));
		}
	}

	
	public function actionCreateListFromSelection() {
		if(isset($_POST['gvSelection'], $_POST['listName'], $_POST['modelName']) 
			&& !empty($_POST['gvSelection']) && is_array($_POST['gvSelection']) && $_POST['listName'] != '' && class_exists($_POST['modelName'])) {

			foreach($_POST['gvSelection'] as &$contactId) {
				if(!ctype_digit($contactId))
					throw new CHttpException(400,Yii::t('app','Invalid selection.'));
			}
			
			$list = new X2List;
			$list->name = $_POST['listName'];
			$list->modelName = $_POST['modelName'];
			$list->type = 'static';
			$list->assignedTo = Yii::app()->user->getName();
			$list->visibility = 1;
			$list->createDate=time();
			$list->lastUpdated=time();

			$itemModel = CActiveRecord::model($_POST['modelName']);
			
			if($list->save()) {	// if the list is valid save it so we can get the ID
				$count = 0;
				foreach($_POST['gvSelection'] as &$itemId) {
				
					if($itemModel->exists('id="'.$itemId.'"')) {	// check if contact exists
						$item = new X2ListItem;
						$item->contactId = $itemId;
						$item->listId = $list->id;
						if($item->save())	// add all the things!
							$count++;
					}
				}
				$list->count = $count;
				if($list->save())
					echo $this->createUrl('/contacts/list/'.$list->id);
			}
		}
	}
		
		
	public function actionCreateList() {
		// $list = new X2List;
		// $list->modelName = 'Contacts';
		// $list->type = 'dynamic';
		// $list->assignedTo = '';
		// $list->visibility = 1;
		
		// $contactModel = new Contacts;
		
		// $comparisonList = array(
			// '='=>'=',
			// '>'=>'>',
			// '<'=>'<',
			// 'empty'=>Yii::t('empty','empty'),
			// 'contains'=>Yii::t('contacts','contains'),
		// );
		
		// $criteriaModels = array(); 

		// if(isset($_POST['X2List'])) {
		
			// $list->attributes = $_POST['X2List'];
			// $list->modelName = 'Contacts';
			// $list->createDate=time();
			// $list->lastUpdated=time();

			// if($list->type == 'dynamic' && isset($_POST['X2List']['attribute'],$_POST['X2List']['comparison'],$_POST['X2List']['value'])) {
		
				// $attributes = &$_POST['X2List']['attribute'];
				// $comparisons = &$_POST['X2List']['comparison'];
				// $values = &$_POST['X2List']['value'];

				// if(count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

					// for($i=0; $i<count($attributes); $i++) {
					
						// if((array_key_exists($attributes[$i],$contactModel->attributeLabels()) || $attributes[$i] == 'tags')
							// && $values[$i] != '' && array_key_exists($comparisons[$i],$comparisonList)) {
							
							// $criteriaModels[$i] = new X2ListCriterion;
							// $criteriaModels[$i]->listId = $list->id;
							// $criteriaModels[$i]->type = 'attribute';
							// $criteriaModels[$i]->attribute = $attributes[$i];
							// $criteriaModels[$i]->comparison = $comparisons[$i];
							// $criteriaModels[$i]->value = $values[$i];
							// $criteriaModels[$i]->validate();
						// }
					
					// }
					// if($list->save()) {
						// foreach($criteriaModels as &$criterion)
							// $criterion->save();
						// unset($criterion);
						
						// $this->redirect(array('/contacts/list/'.$list->id));
					// }
	
				// }
			// } elseif($list->save()) {
				// $this->redirect(array('/contacts/list/'.$list->id));
			// }
		// }
		
		// if(empty($criteriaModels)) {
			// $default = new X2ListCriterion;
			// $default->value = '';
			// $default->attribute = '';
			// $default->comparison = '=';
			// $criteriaModels[] = $default;
		// }
		
		
		
		
		
		
		$list = new X2List;
		$list->modelName = 'Contacts';
		$list->type = 'dynamic';
		$list->assignedTo = '';
		$list->visibility = 1;

		$contactModel = new Contacts;
		$comparisonList = array(
			'='=>'=',
			'>'=>'>',
			'<'=>'<',
			'empty'=>Yii::t('empty','empty'),
			'contains'=>Yii::t('contacts','contains'),
		);
		
		if(isset($_POST['X2List'])) {
		
			$list->attributes = $_POST['X2List'];
			$list->modelName = 'Contacts';
			$list->createDate=time();
			$list->lastUpdated=time();
		
		if($list->type == 'dynamic')
			$criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId'=>$list->id),new CDbCriteria(array('order'=>'id ASC')));

			if(isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {
			
				$attributes = &$_POST['X2List']['attribute'];
				$comparisons = &$_POST['X2List']['comparison'];
				$values = &$_POST['X2List']['value'];

				if(count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

					$list->attributes = $_POST['X2List'];
					$list->modelName = 'Contacts';
					
					$list->lastUpdated = time();

					if($list->save()) {
					
						X2ListCriterion::model()->deleteAllByAttributes(array('listId'=>$list->id));	// delete old criteria
						
						for($i=0; $i<count($attributes); $i++) {	// create new criteria
						
							if((array_key_exists($attributes[$i],$contactModel->attributeLabels()) || $attributes[$i] == 'tags')
								&& $values[$i] != '' && array_key_exists($comparisons[$i],$comparisonList)) {
								
								$criterion = new X2ListCriterion;
								$criterion->listId = $list->id;
								$criterion->type = 'attribute';
								$criterion->attribute = $attributes[$i];
								$criterion->comparison = $comparisons[$i];
								$criterion->value = $values[$i];
								$criterion->save();
							}
						}
						$this->redirect(array('/contacts/list/'.$list->id));
					}
				}
			}
		}
		
		if(empty($criteriaModels)) {
			$default = new X2ListCriterion;
			$default->value = '';
			$default->attribute = '';
			$default->comparison = 'contains';
			$criteriaModels[] = $default;
		}
		
		$this->render('createList',array(
			'model'=>$list,
			'criteriaModels'=>$criteriaModels,
			'users'=>UserChild::getNames(),
			// 'attributeList'=>$attributeList,
			'comparisonList'=>$comparisonList,
			'listTypes'=>array(
				'dynamic'=>Yii::t('contacts','Dynamic'),
				'static'=>Yii::t('contacts','Static')
			),
		));
	}

	public function actionUpdateList($id) {
		$list = X2List::model()->findByPk($id);
		
		if(!isset($list))
			throw new CHttpException(400,Yii::t('app','This list cannot be found.'));
			
		if(!$this->editPermissions($list))
			throw new CHttpException(403,Yii::t('app','You do not have permission to modify this list.'));

		$contactModel = new Contacts;
		$comparisonList = array(
			'='=>'=',
			'>'=>'>',
			'<'=>'<',
			'empty'=>Yii::t('empty','empty'),
			'contains'=>Yii::t('contacts','contains'),
		);
		
		if($list->type == 'dynamic')
			$criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId'=>$list->id),new CDbCriteria(array('order'=>'id ASC')));

		if(isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {
		
			$attributes = &$_POST['X2List']['attribute'];
			$comparisons = &$_POST['X2List']['comparison'];
			$values = &$_POST['X2List']['value'];

			if(count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

				$list->attributes = $_POST['X2List'];
				$list->modelName = 'Contacts';
				
				$list->lastUpdated = time();

				if($list->save()) {
				
					X2ListCriterion::model()->deleteAllByAttributes(array('listId'=>$list->id));	// delete old criteria
					
					for($i=0; $i<count($attributes); $i++) {	// create new criteria
					
						if((array_key_exists($attributes[$i],$contactModel->attributeLabels()) || $attributes[$i] == 'tags')
							&& $values[$i] != '' && array_key_exists($comparisons[$i],$comparisonList)) {
							
							$criterion = new X2ListCriterion;
							$criterion->listId = $list->id;
							$criterion->type = 'attribute';
							$criterion->attribute = $attributes[$i];
							$criterion->comparison = $comparisons[$i];
							$criterion->value = $values[$i];
							$criterion->save();
						}
					}
					$this->redirect(array('/contacts/list/'.$list->id));
				}
			}
		}
		
		if(empty($criteriaModels)) {
			$default = new X2ListCriterion;
			$default->value = '';
			$default->attribute = '';
			$default->comparison = 'contains';
			$criteriaModels[] = $default;
		}
		
		$this->render('updateList',array(
			'model'=>$list,
			'criteriaModels'=>$criteriaModels,
			'users'=>UserChild::getNames(),
			// 'attributeList'=>$attributeList,
			'comparisonList'=>$comparisonList,
			'listTypes'=>array(
				'dynamic'=>Yii::t('contacts','Dynamic'),
				'static'=>Yii::t('contacts','Static')
			),
		));
	}
	public function actionAddToList() {
	
		if(isset($_POST['gvSelection'], $_POST['listId']) && !empty($_POST['gvSelection']) && is_array($_POST['gvSelection'])) {

			foreach($_POST['gvSelection'] as &$contactId)
				if(!ctype_digit($contactId)) throw new CHttpException(400,Yii::t('app','Invalid selection.'));

			$list = X2List::model()->findByPk($_POST['listId']);
			
			// check permissions
			if(isset($list) && $list->type == 'static' && $this->editPermissions($list)) {
				
				$count = 0;
				foreach($_POST['gvSelection'] as &$contactId) {
					$listItem = new X2ListItem();
					$listItem->listId = $list->id;
					$listItem->contactId = $contactId;
					if($listItem->save())
						$count++;
				}
				$list->count = X2ListItem::model()->countByAttributes(array('listId'=>$list->id));
				$list->save();
				echo 'success';
			} else
				throw new CHttpException(403,Yii::t('app','You do not have permission to modify this list.'));
		}
	}
	
	
	// Yii::app()->db->createCommand()->select('id')->from($tableName)->where(array('like','name',"%$value%"))->queryColumn();
	public function actionRemoveFromList() {
	
		if(isset($_POST['gvSelection'], $_POST['listId']) && !empty($_POST['gvSelection']) && is_array($_POST['gvSelection'])) {

			foreach($_POST['gvSelection'] as $contactId)
				if(!ctype_digit($contactId)) throw new CHttpException(400,Yii::t('app','Invalid selection.'));

			$list = X2List::model()->findByPk($_POST['listId']);
			
			// check permissions
			if(isset($list) && $list->type == 'static'	&& $this->editPermissions($list)) {
				X2ListItem::model()->deleteAllByAttributes(array('listId'=>$list->id),'contactId IN ('.implode(',',$_POST['gvSelection']).')'); // delete all the things!
			
				$list->count = X2ListItem::model()->countByAttributes(array('listId'=>$list->id));
				$list->save();
			}
			
			echo 'success';
		}
	}
	
	public function actionDeleteList() {
	
		$id = isset($_GET['id'])? $_GET['id'] : 'all';

		if(is_numeric($id))
			$list = CActiveRecord::model('X2List')->findByPk($id);
		if(isset($list)) {
		
			// check permissions
			if($this->editPermissions($list)) {
				X2ListItem::model()->deleteAllByAttributes(array('listId'=>$list->id)); // delete all the things!
				$list->delete();
			} else
				throw new CHttpException(403,Yii::t('app','You do not have permission to modify this list.'));
		}
		$this->redirect(array('/contacts/lists'));
	}
	
	public function actionImportContacts() {
		if (isset($_FILES['contacts'])) {

			$temp = CUploadedFile::getInstanceByName('contacts');
			$temp->saveAs('contacts.csv');
			$this->import('contacts.csv');
		}
		$this->render('importContacts');
	}

	public function actionImportExcel() {
		if (isset($_FILES['contacts'])) {

			$temp = CUploadedFile::getInstanceByName('contacts');
			$temp->saveAs('contacts.csv');
			$this->importExcel('contacts.csv');
		}
		$this->render('importExcel');
	}

	private function import($file) {
		$arr = file($file);

		for ($i = 1; $i < count($arr) - 1; $i++) {

			$str = $arr[$i] . $arr[$i + 1];
			$i++;
			$pieces = explode(',', $str);

			$model = new Contacts;

			$model->visibility = 1;
			$model->createDate=time();
                        $model->lastUpdated=time();
                        $model->updatedBy='admin';
			$model->backgroundInfo = $this->stripquotes($pieces[77]);
			$model->firstName = $this->stripquotes($pieces[1]);
			$model->lastName = $this->stripquotes($pieces[3]);
			$model->assignedTo = Yii::app()->user->getName();
			$model->company = $this->stripquotes($pieces[5]);
			$model->title = $this->stripquotes($pieces[7]);
			$model->email = $this->stripquotes($pieces[57]);
			$model->phone = $this->stripquotes($pieces[31]);
			$model->address = $this->stripquotes($pieces[8]) . ' ' . $this->stripquotes($pieces[9]) . ' ' . $this->stripquotes($pieces[10]);
			$model->city=$this->stripquotes($pieces[11]);
			$model->state=$this->stripquotes($pieces[12]);
			$model->zipcode=$this->stripquotes($pieces[13]);
			$model->country=$this->stripquotes($pieces[14]);

			if ($model->save()) {

			}
		}
		unlink($file);
		$this->redirect('index');
	}

	private function importExcel($file){
		$fp=fopen($file,'r+');

		$meta=fgetcsv($fp);
                while($arr=fgetcsv($fp)){
                    $model=new Contacts;
                    $attributes=array_combine($meta,$arr);
                    foreach($attributes as $attribute=>$value){
                        if(array_search($attribute,array_keys($model->attributes))!==false){
                            $model->$attribute=$value;
                        }
                    }
                    if($model->save()){
                        
                    }
                }
                
		unlink($file);
		$this->redirect('index');	
	}
	
	public function actionExport(){
		$this->exportToTemplate();
		$this->render('export',array(
		));
	}
	
	private function exportToTemplate(){
		$contacts=Contacts::model()->findAll();
		$list=array(array_keys($contacts[0]->attributes));
		foreach($contacts as $contact){
			$list[]=$contact->attributes;
		}
		$file='file.csv';
		$fp = fopen($file, 'w+');
		
		foreach ($list as $fields) {
			fputcsv($fp, $fields);
			
		}
		
		fclose($fp);
		
	}

	private function stripquotes($str) {
		if (strlen($str) > 2) {
			$str = substr($str, 1, strlen($str) - 2);
		}
		return $str;
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model = new Contacts('search');
		$name = 'Contacts';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model = CActiveRecord::model('Contacts')->findByPk((int) $id);
		if ($model === null)
			throw new CHttpException(404, Yii::t('app','The requested page does not exist.'));
		return $model;
	}

	public function actionDelete($id) {
		$model = $this->loadModel($id);
		if (Yii::app()->request->isPostRequest) {
			$dataProvider = new CActiveDataProvider('Actions', array(
						'criteria' => array('condition' => 'associationId=' . $id . ' AND associationType=\'contacts\'')));

			$actions = $dataProvider->getData();
			foreach ($actions as $action) {
                            $action->delete();
			}
                        $this->cleanUpTags($model);
			$model->delete();
		}
		else
			throw new CHttpException(400, Yii::t('app','Invalid request. Please do not repeat this request again.'));

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if (!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
}
