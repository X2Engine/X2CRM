<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

class AccountsController extends x2base {

	public $modelClass = 'AccountChild';

	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','addUser','addContact','removeUser','removeContact',
					'addNote','deleteNote','saveChanges','delete','shareAccount'),
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

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model=$this->loadModel($id);
		
		$model->assignedTo=UserChild::getUserLinks($model->assignedTo);
		
		$str = '';
		$contacts=ContactChild::model()->findAllByAttributes(array('company'=>$model->name));
		foreach($contacts as $contact){
			$str.=$contact->id.' ';
		}
		$model->associatedContacts=$str;
		
		$model->associatedContacts=ContactChild::getContactLinks($model->associatedContacts);
		
		$type='accounts';
		parent::actionView($model, $type);
	}
	
	public function actionShareAccount($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('accounts','Account Record Details')." \n
".Yii::t('accounts','Name').": $model->name
".Yii::t('accounts','Description').": $model->description
".Yii::t('accounts','Revenue').": $model->annualRevenue
".Yii::t('accounts','Phone').": $model->phone
".Yii::t('accounts','Website').": $model->website
".Yii::t('accounts','Type').": $model->type
".Yii::t('app','Link').": ".'http://'.Yii::app()->request->getServerName().$this->createUrl('accounts/view/'.$model->id);
		if(isset($_POST['email']) && isset($_POST['body'])){
			$email=$_POST['email'];
			$body=$_POST['body'];
			
			$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
			if($count==0){
				$this->redirect(array('shareAccount','id'=>$model->id));
			}
			
			$subject=Yii::t('accounts',"Account Record").": $model->name";
			

			
			mail($email,$subject,$body);
			$this->redirect(array('view','id'=>$model->id));
		}
		$this->render('shareAccount',array(
			'model'=>$model,
			'body'=>$body,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new AccountChild;
		$users=UserChild::getNames();
		unset($users['admin']);
		unset($users['Anyone']);
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['AccountChild'])) {
			$model->attributes=$_POST['AccountChild'];
			
			// process currency into an INT
			$model->annualRevenue = $this->parseCurrency($model->annualRevenue,false);
			
			$model=$this->updateChangelog($model,"Created");
			if(isset($model->assignedTo))
				$model->assignedTo = AccountChild::parseUsers($model->assignedTo);
			if(isset($model->associatedContacts))
				$model->associatedContacts = AccountChild::parseContacts($model->associatedContacts);
			$model->createDate=time();
		
			
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
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
		$model=$this->loadModel($id);
		$users=UserChild::getNames();
		unset($users['admin']);
		unset($users['Anyone']);
		$contacts=ContactChild::getAllNames();
		unset($contacts['0']);
		
		$curUsers=$model->assignedTo;
		$userPieces=explode(', ',$curUsers);
		$arr=array();
		foreach($userPieces as $piece){
			$arr[]=$piece;
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

		if(isset($_POST['Accounts'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Accounts'];
                        
                        
			
			// process currency into an INT
			$model->annualRevenue = $this->parseCurrency($model->annualRevenue,false);
			
			$arr=$model->assignedTo;
			if(isset($model->assignedTo))
				$model->assignedTo=AccountChild::parseUsers($arr);
			$arr=$model->associatedContacts;
			if(isset($model->associatedContacts))
				$model->associatedContacts=AccountChild::parseContacts($arr);
			
                        $changes=$this->calculateChanges($temp,$model->attributes);
			$model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
		));
	}
	
	public function actionSaveChanges($id) {
		$account=$this->loadModel($id);
		if(isset($_POST['Accounts'])) {
                        $temp=$account->attributes;
			$account->attributes=$_POST['Accounts'];
                        
			// process currency into an INT
			$account->annualRevenue = $this->parseCurrency($account->annualRevenue,false);
			$changes=$this->calculateChanges($temp,$account->attributes);
			$account=$this->updateChangelog($account,$changes);
			$account->update();
			$this->redirect(array('view','id'=>$account->id));
		}
	}

	public function actionAddUser($id) {
		$users=UserChild::getNames();
		$contacts=ContactChild::getAllNames();
		$model=$this->loadModel($id);
		$users=AccountChild::editUserArray($users,$model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Accounts'])) {
			$temp=$model->assignedTo; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Accounts'];  
			$arr=$model->assignedTo;
                        
                        

			$model->assignedTo=AccountChild::parseUsers($arr);
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
		$pieces=AccountChild::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Accounts'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Accounts'];  
			$arr=$model->assignedTo;
                        
                        
			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=AccountChild::parseUsersTwo($pieces);

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
		
		$model=new AccountChild('search');
		$name='AccountChild';
		parent::actionIndex($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new AccountChild('search');
		$name='AccountChild';
		parent::actionAdmin($model,$name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=AccountChild::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}