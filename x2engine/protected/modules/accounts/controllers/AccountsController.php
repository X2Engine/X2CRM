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
 * @package X2CRM.modules.accounts.controllers 
 */
class AccountsController extends x2base { 

	public $modelClass = 'Accounts';

	public function accessRules() {
		return array(
                        array('allow',
                            'actions'=>array('getItems'),
                            'users'=>array('*'), 
                        ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','addUser','addContact','removeUser','removeContact',
					'addNote','deleteNote','saveChanges','delete','shareAccount','inlineEmail'),
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
		$sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
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
		$model=$this->loadModel($id);	 
		
		$type='accounts';
		parent::view($model, $type);
	}
	
	public function actionShareAccount($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('accounts','Account Record Details')." <br />
<br />".Yii::t('accounts','Name').": $model->name
<br />".Yii::t('accounts','Description').": $model->description
<br />".Yii::t('accounts','Revenue').": $model->annualRevenue
<br />".Yii::t('accounts','Phone').": $model->phone
<br />".Yii::t('accounts','Website').": $model->website
<br />".Yii::t('accounts','Type').": $model->type
<br />".Yii::t('app','Link').": ".CHtml::link($model->name,'http://'.Yii::app()->request->getServerName().$this->createUrl('/accounts/'.$model->id));
		$body = trim($body);

		$errors = array();
		$status = array();
		$email = array();
		if(isset($_POST['email'], $_POST['body'])){
		
			$subject = Yii::t('accounts',"Account Record").": $model->name";
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
		$this->render('shareAccount',array(
			'model'=>$model,
			'body'=>$body,
			'email'=>$email,
			'status'=>$status,
			'errors'=>$errors
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
        
        public function create($model,$oldAttributes, $api){
            
            $model->annualRevenue = $this->parseCurrency($model->annualRevenue,false);
            $model->createDate=time();
            if($api==0)
                parent::create($model,$oldAttributes,$api);
            else
                return parent::create($model,$oldAttributes,$api);
        }
        
	public function actionCreate() {
		$model=new Accounts;
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }

		if(isset($_POST['Accounts'])) {
			$temp=$model->attributes;
			foreach($_POST['Accounts'] as $name => &$value) {
			if($value == $model->getAttributeLabel($name))
				$value = '';
			}
			$model->setX2Fields($_POST['Accounts']);
			if(isset($_POST['x2ajax'])) {
				if($this->create($model,$temp, '1')) { // success creating account?
					$primaryAccountLink = '';
					$newPhone = '';
					$newWebsite = '';
					if(isset($_POST['ModelName']) && isset($_POST['ModelId'])) {
						$rel=new Relationships;
						$rel->firstType=$_POST['ModelName'];
						$rel->firstId=$_POST['ModelId'];
						$rel->secondType='Accounts';
						$rel->secondId=$model->id;
						$rel->save();
						if($_POST['ModelName'] == 'Contacts') {
							$contact = Contacts::model()->findByPk($_POST['ModelId']);
							if($contact) {
								$changed = false;
								if($contact->company == '') { // if no primary account on this contact
									$contact->company = $model->id; // make this primary account
									$changed = true;
									$primaryAccountLink = $model->createLink();
								}
								if(isset($model->website) && (!isset($contact->website) || $contact->website == "")) {
									$contact->website = $model->website;
									$newWebsite = $contact->website;
									$changed = true;
								}
								if(isset($model->phone) && (!isset($contact->phone) || $contact->phone == "")) {
									$contact->phone = $model->phone;
									$newPhone = $contact->phone;
									$changed = true;
								}
								
								if($changed)
									$contact->update();
							}
						} else if($_POST['ModelName'] == 'Opportunity') {
							$opportunity = Opportunity::model()->findByPk($_POST['ModelId']);
							if($opportunity) {
								if(!isset($opportunity->accountName) || $opportunity->accountName == '') {
									$opportunity->accountName = $model->id;
									$opportunity->update();
									$primaryAccountLink = $model->createLink();
								}
							}
						}
					}

					echo json_encode(
						array(
							'status'=>'success',
							'name'=>$model->name,
							'id'=>$model->id,
							'primaryAccountLink'=>$primaryAccountLink,
							'newWebsite'=>$newWebsite,
							'newPhone'=>$newPhone,
						)
					);
					Yii::app()->end();
				} else {
					$x2ajaxCreateError = true;
				}
			} else {
				$this->create($model,$temp, '0');
	//			var_dump($model->errors);
			}
		}
		
		if(isset($_POST['x2ajax'])) {
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			if(isset($x2ajaxCreateError) && $x2ajaxCreateError == true) {
				$page = $this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'accounts'), true, true);
				echo json_encode(
					array(
						'status'=>'userError',
						'page'=>$page,
					)
				);
			} else {
				$this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'accounts'), false, true);
			}
		} else {
			$this->render('create',array(
				'model'=>$model,
				'users'=>$users,
			));
		}

	}
        
        public function update($model, $oldAttributes,$api){
            // process currency into an INT
            $model->annualRevenue = $this->parseCurrency($model->annualRevenue,false);

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
		unset($users['admin']);
		unset($users['']);
                foreach(Groups::model()->findAll() as $group){
                    $users[$group->id]=$group->name;
                }
		
		$curUsers=$model->assignedTo;
		$userPieces=explode(', ',$curUsers);
		$arr=array();
		foreach($userPieces as $piece){
			$arr[]=$piece;
		}

		$model->assignedTo=$arr;

		if(isset($_POST['Accounts'])) {
			$temp=$model->attributes;
			foreach($_POST['Accounts'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name))
					$value = null;
				}
			$model->setX2Fields($_POST['Accounts']);
			$this->update($model,$temp,'0');
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
		$pieces=Opportunity::editUsersInverse($pieces);

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
		
		$model=new Accounts('search');
		$this->render('index', array('model'=>$model));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Accounts::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
