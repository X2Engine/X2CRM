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

class SalesController extends x2base {

	public $modelClass = 'SaleChild';
		
	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','addUser','addContact','removeUser','removeContact',
                                    'saveChanges','delete','shareSale'),
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
		$type = 'sales';
		$model = $this->loadModel($id);
		$model->assignedTo = UserChild::getUserLinks($model->assignedTo);
		$model->associatedContacts = ContactChild::getContactLinks($model->associatedContacts);
		
		parent::actionView($model, $type);
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
		if(isset($_POST['email']) && isset($_POST['body'])){
			$email=$_POST['email'];
			
			$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
			if($count==0){
				$this->redirect(array('shareSale','id'=>$model->id));
			}
			
			$subject=Yii::t('sales','Sale Record Details');
			

			
			mail($email,$subject,$body);
			$this->redirect(array('view','id'=>$model->id));
		}
		$this->render('shareSale',array(
			'model'=>$model,
			'body'=>$body,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model = new SaleChild;
		$users = UserChild::getNames();
		$contacts=ContactChild::getAllNames();
		unset($users['admin']);
		unset($users['Anyone']);
		unset($contacts['0']);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SaleChild'])) {
			$model->attributes=$_POST['SaleChild'];
			
			if(isset($_POST['companyAutoComplete']) && $model->accountName==""){
				$model->accountName=$_POST['companyAutoComplete'];
				$model->accountId="";
			}
			// process currency into an INT
			$model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);
			
			$model=$this->updateChangelog($model,'Create'); 
			if(isset($model->assignedTo))
				$model->assignedTo = SaleChild::parseUsers($model->assignedTo);
			if(isset($model->associatedContacts))
				$model->associatedContacts = SaleChild::parseContacts($model->associatedContacts);
			$model->createDate=time();
			if($model->expectedCloseDate!=""){
				$model->expectedCloseDate=strtotime($model->expectedCloseDate);
			}
		
			
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
		));
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

		if(isset($_POST['Sales'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Sales'];
			
			// process currency into an INT
			$model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);
			
			$arr=$model->assignedTo;
			if(isset($model->assignedTo))
				$model->assignedTo=SaleChild::parseUsers($arr);
			$arr=$model->associatedContacts;
			if(isset($model->associatedContacts))
				$model->associatedContacts=SaleChild::parseContacts($arr);
			$model->createDate=time();
			if($model->expectedCloseDate!=""){
				$model->expectedCloseDate=strtotime($model->expectedCloseDate);
			}
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
		$sale=$this->loadModel($id);
		if(isset($_POST['Sales'])) {
                        $temp=$sale->attributes;
			$sale->attributes=$_POST['Sales'];
			
			// process currency into an INT
			$sale->quoteAmount = $this->parseCurrency($sale->quoteAmount,false);
			
			
			if($sale->expectedCloseDate!=""){
				$sale->expectedCloseDate=strtotime($sale->expectedCloseDate);
			}
                        $changes=$this->calculateChanges($temp,$sale->attributes);
                        $sale=$this->updateChangelog($sale,$changes);
			$sale->save();
			$this->redirect(array('view','id'=>$sale->id));
		}
	}

	public function actionAddUser($id) {
		$users=UserChild::getNames();
		$contacts=ContactChild::getAllNames();
		$model=$this->loadModel($id);
		$users=SaleChild::editUserArray($users,$model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
			$temp=$model->assignedTo; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->assignedTo;
			

			$model->assignedTo=SaleChild::parseUsers($arr);
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
		$contacts=ContactChild::getAllNames();
		$model=$this->loadModel($id);

		$contacts=SaleChild::editContactArray($contacts, $model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
			$temp=$model->associatedContacts; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->associatedContacts;
			

			$model->associatedContacts=SaleChild::parseContacts($arr);
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
		$pieces=SaleChild::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->assignedTo;
			
			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=SaleChild::parseUsersTwo($pieces);

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
		$pieces=SaleChild::editContactsInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sales']))
		{
                        $temp=$model->attribtes;
			$model->attributes=$_POST['Sales'];  
			$arr=$model->associatedContacts;
			
			
			foreach($arr as $id=>$contact){
				unset($pieces[$contact]);
			}
			
			$temp=SaleChild::parseContactsTwo($pieces);

			$model->associatedContacts=$temp;
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
		$model=new SaleChild('search');
		$name='SaleChild';
		parent::actionIndex($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new SaleChild('search');
		$name='SaleChild';
		parent::actionAdmin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=SaleChild::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
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