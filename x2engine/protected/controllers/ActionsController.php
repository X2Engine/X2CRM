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

class ActionsController extends x2base {

	public $modelClass = 'ActionChild';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('invalid','sendReminder'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','createSplash','quickCreate','createInline','viewGroup','complete',
					'completeRedirect','update','viewAll','search','completeNew','parseType','getTerms','uncomplete','uncompleteRedirect','delete','shareAction'),
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
	
		$action = ActionChild::model()->findByPk($id);

		if($action != null) {
		
			$users = UserChild::getNames();
			$association = $this->getAssociation($action->associationType,$action->associationId);
			
			// if($association != null)
				// $associationName = $association->name;
			//else
				//$associationName = Yii::t('app','None');
			
			
			if ($action->assignedTo==Yii::app()->user->getName() || $action->visibility==1 || $action->assignedTo=='Anyone') {
				UserChild::addRecentItem('t',$id,Yii::app()->user->getId());	//add action to user's recent item list
				$this->render('view',array(
					'model'=>$this->loadModel($id),
					'associationModel'=>$association,
					'users'=>$users,
				));
			} else
				$this->redirect('index');
		} else
			$this->redirect('index');
	}
	
	public function actionShareAction($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('actions',"Reminder, the following action is due")." ".date("Y-m-d",$model->dueDate).":\n
".Yii::t('actions','Description').": $model->actionDescription
".Yii::t('actions','Type').": $model->type
".Yii::t('actions','Associations').": ".Yii::t('actions',$model->associationName)."
".Yii::t('actions','Link to the action').": ".'http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl.'/index.php/actions/'.$model->id;
		if(isset($_POST['email']) && isset($_POST['body'])){
			$email=$_POST['email'];
			$body=$_POST['body'];
			
			$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
			if($count==0){
				$this->redirect(array('shareAction','id'=>$model->id));
			}
			
			$subject=Yii::t('actions',"Reminder, the following action is due")." ".date("Y-m-d",$model->dueDate);
			

			
			mail($email,$subject,$body);
			$this->redirect(array('view','id'=>$model->id));
		}
		$this->render('shareAction',array(
			'model'=>$model,
			'body'=>$body,
		));
	}

	public function actionSendReminder() {
		
		$dataProvider=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'condition'=>'(dueDate=\''.date('Y-m-d').'\' AND complete=\'No\')',
		)));

		$emails= UserChild::getEmails();

		$actionArray=$dataProvider->getData();

		foreach($actionArray as $action) {
			if($action->reminder=='Yes') {

				if($action->associationId!=0) {
					$contact=ContactChild::model()->findByPk($action->associationId);
					$name=$contact->firstName.' '.$contact->lastName;
				} else
					$name=Yii::t('actions','No one');
				$email=$emails[$action->assignedTo];
				if(isset($action->type))
					$type=$action->type;
				else
					$type=Yii::t('actions','Not specified');

				$subject=Yii::t('actions','Action Reminder:');
				$body=Yii::t('actions',"Reminder, the following action is due today: \n Description: {description}\n Type: {type}.\n Associations: {name}.\nLink to the action: ",
					array('{description}'=>$action->actionDescription,'{type}'=>$type,'{name}'=>$name))
					.'http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl.'/index.php/actions/'.$action->id;
				$headers='From: '.Yii::app()->params['adminEmail'];

				if($action->associationType!='none')
					$body.="\n\n".Yii::t('actions','Link to the {type}',array('{type}'=>ucfirst($action->associationType))).': http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl."/index.php/".$action->associationType."/".$action->associationId;
				$body.="\n\n".Yii::t('actions','Powered by ').'<a href=http://x2engine.com>X2Engine</a>';

				mail($email,$subject,$body,$headers);
			}
		}
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	
	public function actionCreate() {

		$model = new ActionChild;
		$users = UserChild::getNames();

	// Uncomment the following line if AJAX validation is needed
	// $this->performAjaxValidation($model);

		if(isset($_POST['ActionChild'])) {
			
			$model->attributes=$_POST['ActionChild'];
                        
			if($model->associationId=='')
				$model->associationId=0;
			//if($model->

			$model->createDate = time();	// created now, full datetime
			//$model->associationId=$_POST['ActionChild']['associationId'];
			$dueDate = strtotime($model->dueDate);
			$model->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM

			//if($type=='none')
			//	$model->associationId=0;
			//$model->associationType=$type;
			
			$association = $this->getAssociation($model->associationType,$model->associationId);
			
			if($association != null) {
				$model->associationName = $association->name;
			} else {
				$model->associationName='None';
				//$model->associationId = 0;
			}
			if($model->associationName=='None' && $model->associationType!='none'){
				$model->associationName=ucfirst($model->associationType);
			}
			if($_POST['submit']=='comment') {	// if user clicked "New Comment" rather than "New Action"
				$model->createDate = time();
				$model->dueDate = time();
				$model->completeDate = time();
				$model->complete='Yes';
				$model->visibility='1';
				$model->assignedTo=Yii::app()->user->getName();
				$model->completedBy=Yii::app()->user->getName();
				$model->type='note';
			}
                        $model=$this->updateChangelog($model,'Create');
			if ($model->save()) {
				if(isset($_GET['inline']) || $model->type=='note')
					$this->redirect(array($model->associationType.'/view','id'=>$model->associationId));
				else
					$this->redirect(array('view','id'=>$model->id));
			}
		}
		if(isset($_GET['param'])) {
			$pieces=explode(';',$_GET['param']);
			$user=$pieces[0];
			$pieces2=explode(':',$pieces[1]);
			$type=$pieces2[0];
			$id=$pieces2[1];
		} else {	// defaults
			$user=Yii::app()->user->getName();
			$type='none';
			$id=null;
		}
		$names=$this->parseType($type);
		$model->associationType=$type;
		$model->assignedTo=$user;
		if(!empty($id))
			$model->associationId=$id;

		$this->render('create',array(
			'model'=>$model,
			'names'=>$names,
			'users'=>$users
		));
		
	}

	public function actionQuickCreate() {
		$users = UserChild::getNames();
		$actionModel=new ActionChild;
		$contactModel=new ContactChild;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($actionModel);

		if(isset($_POST['ActionChild']) && isset($_POST['ContactChild'])) {
			$actionModel->attributes=$_POST['ActionChild'];
			
			$contactModel->attributes=$_POST['ContactChild'];
			
			$actionModel->createDate=time();
			$contactModel->createDate=time();
			
			$actionModel->visibility = $contactModel->visibility;
			$actionModel->assignedTo = $contactModel->assignedTo;
			$actionModel->priority = $contactModel->priority;
			
			// reset to blank if it's the default value
			$attributeLabels = ContactChild::attributeLabels();
			if($contactModel->address == $attributeLabels['address'])
				$contactModel->address = '';
			if($contactModel->city == $attributeLabels['city'])
				$contactModel->city = '';
			if($contactModel->state == $attributeLabels['state'])
				$contactModel->state = '';
			if($contactModel->zipcode == $attributeLabels['zipcode'])
				$contactModel->zipcode = '';
			if($contactModel->country == $attributeLabels['country'])
				$contactModel->country = '';
			
			$dueDate = strtotime($actionModel->dueDate);
			$actionModel->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM

			if($_POST['submit']=='comment') {	// if user clicked "New Comment" rather than "New Action"
				$actionModel->createDate = time();
				$actionModel->dueDate = time();
				$actionModel->completeDate = time();
				$actionModel->complete='Yes';
				$actionModel->visibility='1';
				$actionModel->assignedTo=Yii::app()->user->getName();
				$actionModel->completedBy=Yii::app()->user->getName();
				$actionModel->type='note';
			}
			$contactModel=$this->updateChangelog($contactModel,'Create');
			if($contactModel->save()) {

				// $actionModel->dueDate=$actionModel->dueDate;
				$actionModel->associationId = $contactModel->id;
				$actionModel->associationType = 'contacts';
				$actionModel->associationName = $contactModel->firstName.' '.$contactModel->lastName;
				$actionModel=$this->updateChangelog($actionModel,'Create');
				if($actionModel->save())
					$this->redirect(array('contacts/view','id'=>$contactModel->id));
				else
					$contactModel->delete();
			}
		}

		$this->render('quickCreate',array(
			'actionModel'=>$actionModel,
			'contactModel'=>$contactModel,
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
		$names=$this->parseType($model->type);
		$users=UserChild::getNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['ActionChild'])) {
			$temp=$model->attributes;
			$model->attributes=$_POST['ActionChild'];

			$dueDate = strtotime($model->dueDate);
			$model->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM

			$association = $this->getAssociation($model->associationType,$model->associationId);
			
			if($association != null) {
				$model->associationName = $association->name;
			} else {
				$model->associationName = 'None';
				$model->associationId = 0;
			}
			$changes=$this->calculateChanges($temp,$model->attributes);
			$model=$this->updateChangelog($model,$changes);
			if($model->save()) {
				if(isset($_GET['redirect']) && $model->associationType!='none')	// if the action has an association
					$this->redirect(array($model->associationType.'/view','id'=>$model->associationId));	// go back to the association
				else	// no association
					$this->redirect(array('actions/view','id'=>$model->id));	// view the action
			}
		}

		$this->render('update',array(
			'model'=>$model,
			'names'=>$names,
			'users'=>$users,
		));
	}
	
	// Postpones due date (and sets action to incomplete)
	public function actionTomorrow($id) {
		$model = $this->loadModel($id);
		$model->complete='No';
		$model->dueDate=time()+86400;	//set to tomorrow
		if($model->save()){
			if($model->associationType!='none')
				$this->redirect(array($model->associationType.'/'.$model->associationId));
			else
				$this->redirect(array('view','id'=>$id));
		}
	}

	// Deletes a particular model
	public function actionDelete($id) {

		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest){
                        $this->cleanUpTags($model);
			$model->delete();
                }
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(isset($_GET['ajax']))
			echo 'Success';
		else
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	public function actionComplete($id) {
		//die(var_dump($_POST));
		$model=$this->loadModel($id);
		if(Yii::app()->user->getName()==$model->assignedTo || $model->assignedTo=='Anyone' || Yii::app()->user->getName()=='admin') {
			
			if(isset($_POST['note']))
				$model->actionDescription = $model->actionDescription."\n\n".$_POST['note'];
				
			$model=$this->updateChangelog($model,'Completed');
			$model->save();
			ActionChild::completeAction($id);

			$createNew = isset($_GET['createNew']) || (isset($_POST['submit']) && ($_POST['submit']=='completeNew'));
			$redirect = isset($_GET['redirect']) || $createNew;
			
			if($redirect) {
				if($model->associationType!='none') {	// if the action has an association
					$this->redirect(array($model->associationType.'/view','id'=>$model->associationId));	// go back to the association
				} else {	// no association
					if($createNew)
						$this->redirect(array('actions/create'));		// go to blank 'create action' page
					else
						$this->redirect(array('actions/view','id'=>$model->id));	// view the action
				}
			} else {
				$this->redirect(array('actions/view','id'=>$model->id)); 
			}
		} else {
			$this->redirect(array('actions/invalid'));
		}
	}
	
	// Postpones due date (and sets action to incomplete)
	public function actionUncomplete($id) {
		$model = $this->loadModel($id);
		$model->complete = 0;
		$model->completeDate = null;
		//$model->dueDate = date("Y-m-d",time()+(86400));
		if($model->save()){
		if(isset($_GET['redirect'])) {
			if($model->associationType!='none')
				$this->redirect(array($model->associationType.'/'.$model->associationId));
			else
				$this->redirect(array('view','id'=>$id));
		} else {
			$this->redirect(array('actions/view','id'=>$id));
		}
		}else{
			print_r($model->getErrors());
		}
	}
	
	// Lists all actions assigned to this user
	public function actionIndex() {
		$model=new ActionChild('search');
		$name='ActionChild';
		parent::index($model,$name);
	}

	// List all public actions
	public function actionViewAll() {
		$model=new ActionChild('search');
		$name='ActionChild';
		parent::index($model,$name);
	}
	
	public function actionViewGroup() {
		$model=new ActionChild('search');
		$name='ActionChild';
		parent::index($model,$name);
	}

	// Admin view of all actions
	public function actionAdmin() {
		$model=new ActionChild('search');
		$name='ActionChild';
		parent::admin($model,$name);
	}
	
	// display error page
	public function actionInvalid() {
		$this->render('invalid');
	}

	
	public function actionParseType() {
		if(isset($_POST['ActionChild']['associationType'])){
			$type=$_POST['ActionChild']['associationType'];
			echo $type;
		}else{
			echo 'none';
		}
	}

	public function actionGetTerms(){
		$type=$_GET['type'];
		if($type!='none'){
		if($type=='contacts'){
			$sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm ORDER BY firstName ASC';
		}else{
			$sql = 'SELECT id, name as value FROM x2_'.$type.' WHERE name LIKE :qterm ORDER BY name ASC';
		}
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
		}else{
			echo array('0'=>'None');
		}
	}

	protected function getAssociation($type,$id) {
	
		$classes = array(
			'actions'=>'ActionChild',
			'contacts'=>'ContactChild',
			'projects'=>'ProjectChild',
			'accounts'=>'AccountChild',
			'sales'=>'SaleChild',
		);
		
		if(array_key_exists($type,$classes) && $id != 0)
			return CActiveRecord::model($classes[$type])->findByPk($id);
		else
			return null;
	}
	
/* 	public function parseName($arr) {
		$type=$arr[0]; 
		$id=$arr[1];
		if(isset($id) || true) {
			if($type=='project') {
				 $data=CActiveRecord::model('ProjectChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='contact') {
				 $data=CActiveRecord::model('ContactChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='account') {
				 $data=CActiveRecord::model('AccountChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='case') {
				 $data=CActiveRecord::model('CaseChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='sale') {
				 $data=CActiveRecord::model('SaleChild')->findByPk($id);
				 $name=$data->name;
			} else {
				$data=null
				$name='None';
			}
		} else {
			 $data=null;
			 $name='None';
		}
		return array($name,$data);
	} */
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=ActionChild::model('ActionChild')->findByPk((int)$id);
		//$dueDate=$model->dueDate;
		//$model=ActionChild::changeDates($model);
		// if($model->associationId!=0) {
			// $model->associationName = $this->parseName(array($model->associationType,$model->associationId));
		// } else
			// $model->associationName = 'None';
		
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='actions-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
