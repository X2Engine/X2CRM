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

class ContactsController extends x2base {

	public $modelClass = 'ContactChild';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',	// allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index',
					'list',
					'viewAll',
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
					'updateList',
					'deleteList',
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

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {

		$contact = $this->loadModel($id);

		if ($contact->assignedTo == Yii::app()->user->getName() || $contact->visibility == 1 || Yii::app()->user->getName() == 'admin') {
			UserChild::addRecentItem('c',$id,Yii::app()->user->getId());	////add contact to user's recent item list
			parent::view($contact, 'contacts');
		} else
			$this->redirect('index');
	}
	
	public function actionViewSales($id){
		
		$sales=Relationships::model()->findAllByAttributes(array('firstType'=>'Contacts','firstId'=>$id,'secondType'=>'Sales'));
		$temp=array();
		foreach($sales as $sale){
			$temp[]=SaleChild::model()->findByPk($sale->secondId);
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
	
	public function actionGetContacts(){
		$sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm ORDER BY firstName ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
	
	public function actionShareContact($id){
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
		if(isset($_POST['email']) && isset($_POST['body'])){
			$email=$_POST['email'];
			$body=$_POST['body'];
			$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
			if($count==0){
				$this->redirect(array('shareContact','id'=>$model->id));
			}
			
			$subject=Yii::t('contacts','Contact Record Details');
			

			
			mail($email,$subject,$body);
			$this->redirect(array('view','id'=>$model->id));
		}
		$this->render('shareContact',array(
			'model'=>$model,
			'users'=>$users,
			'body'=>$body,
		));
	}
	
	// Creates contact record
	public function create($model, $oldAttributes, $api){
		$account = Accounts::model()->findByAttributes(array('name'=>$model->company));
		if(isset($account))
			$contact->accountId = $account->id;
		else
			$model->accountId = 0;
		$model->createDate=time();
		$model->lastUpdated=time();
		if($api==0)
			parent::create($model,$oldAttributes,$api);
		else
			return parent::create($model,$oldAttributes,$api);
	}

	// Controller/action wrapper for create()
	public function actionCreate() {
		$model = new ContactChild;
		$name='ContactChild';
		$users=UserChild::getNames();
		$accounts=AccountChild::getNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['ContactChild'])) {
			// clear values that haven't been changed from the default
			foreach($_POST['ContactChild'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name))
					$value = '';
			}
			$temp=$model->attributes;
			$model->attributes = $_POST['ContactChild'];

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
		
		$model = new ContactChild;
		$attributeLabels = ContactChild::attributeLabels();
		
		// if it is ajax validation request
		// if(isset($_POST['ajax']) && $_POST['ajax']=='quick-contact-form') {
			// echo CActiveForm::validate($model);
			// Yii::app()->end();
		// }

		// collect user input data
		if(isset($_POST['ContactChild'])) {
			// clear values that haven't been changed from the default
			foreach($_POST['ContactChild'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name))
					$value = '';
			}
			$temp=$model->attributes;
			$model->attributes = $_POST['ContactChild'];

			$model->visibility = 1;
			
			$account = Accounts::model()->findByAttributes(array('name'=>$contact->company));
			if(isset($account))
				$contact->accountId = $account->id;
			else
				$contact->accountId = 0; 
			// validate user input and save contact
			$changes=$this->calculateChanges($temp,$model->attributes);
			$model=$this->updateChangelog($model,'Create');
			$model->createDate=time();
			if($model->save()) {
				$this->renderPartial('application.components.views.quickContact',array());
			} //else print_r($model->getErrors());
		}
	}
	
	public function actionSaveChanges($id) {
		$contact=$this->loadModel($id);
		if(isset($_POST['ContactChild'])) {
			// clear values that haven't been changed from the default
			foreach($_POST['ContactChild'] as $name => $value) {
				if($value == $contact->getAttributeLabel($name)){
					$_POST['ContactChild'][$name] = '';
				}
			}
			$temp=$contact->attributes;
			$contact->attributes=$_POST['ContactChild'];
                        $contact->company=$_POST['companyAutoComplete'];
			$account = Accounts::model()->findByAttributes(array('name'=>$contact->company));
			if(isset($account))
				$contact->accountId = $account->id;
			else
				$contact->accountId = 0; 

			if($contact->save()){
				$changes=$this->calculateChanges($temp,$contact->attributes);
                                $contact=$this->updateChangelog($contact,$changes);
			}else{
                            print_r($contact->getErrors());exit;
                        }
			$this->redirect(array('view','id'=>$contact->id));
		} else
			$this->redirect(array('view','id'=>$contact->id));
		
	}

	// Updates a contact record
	public function update($model,$oldAttributes, $api){
		
		$account = Accounts::model()->findByAttributes(array('name'=>$model->company));
		if(isset($account))
				$model->accountId = $account->id;
		else
				$model->accountId = 0;
		if($api==0)
			parent::create($model,$oldAttributes,$api);
		else
			return parent::create($model,$oldAttributes,$api);
	}
	

	// Controller/action wrapper for update()
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
		$users=UserChild::getNames();
		$accounts=AccountChild::getNames();  
		
		 

		if(isset($_POST['ContactChild'])) {
			$temp=$model->attributes;
			foreach($_POST['ContactChild'] as $name => $value) {
				if($value == $model->getAttributeLabel($name)){
					$_POST['ContactChild'][$name] = '';
				}
			}
			$model->attributes=$_POST['ContactChild'];
			
			$this->update($model,$temp,'0');
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'accounts'=>$accounts,
		));
	}

	// Default action - displays all visible Contact Lists
	public function actionIndex() {
		$model = new ContactChild('search');
		
		// $contactLists = ContactList::model()->findAll();
	
		$contactLists = new CActiveDataProvider('ContactList', array(
			'sort'=>array(
				'defaultOrder'=>'createDate DESC',
			),
			// 'pagination'=>array(
				// 'pageSize'=>ProfileChild::getResultsPerPage(),
			// ),
			'criteria'=>array('condition' => 'assignedTo="' . Yii::app()->user->getName() . '" OR visibility = 1'),
		));

		$totalContacts = CActiveRecord::model('Contacts')->count();
		$totalMyContacts = CActiveRecord::model('Contacts')->count('assignedTo="'.Yii::app()->user->getName().'"');
		
		$allContacts = new ContactList;
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
		$myContacts = new ContactList;
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
		$model=new ContactChild('search');
		$name='ContactChild';
		parent::index($model,$name);
	}
	
	// Lists all visible contacts
	public function actionViewAll() {
		$model=new ContactChild('search');
		$name='ContactChild';
		parent::index($model,$name);
	}

	// Shows contacts in the specified list
	public function actionList() {
	
		$id = isset($_GET['id'])? $_GET['id'] : 'all';

		if(is_numeric($id))
			$list = CActiveRecord::model('ContactList')->findByPk($id);
		if(isset($list)) {

			$dataProvider = CActiveRecord::model('ContactChild')->searchList($id);
			
			$this->render('list',array(
				'listName'=>$list->name,
				'listId'=>$id,
				'dataProvider'=>$dataProvider,
			));
			
		} else {
			// $model=new ContactChild('search');

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
				$this->redirect(array('contacts/viewAll'));
				// $dataProvider = CActiveRecord::model('ContactChild')->searchAll();
			else
				$this->redirect(array('contacts/viewMy'));
				// $dataProvider = CActiveRecord::model('ContactChild')->search();

			// $this->render('index',array(
				// 'model'=>$model,
				// 'dataProvider'=>$dataProvider,
			// ));
		}
	}

	public function actionCreateList() {
		return;
	
		$model = new ContactList;
		$test = new ContactListCriterion;
		$test->value = 'sausages';
		$test->attribute = 'leadSource';
		$test->comparison = '=';
		$criteriaModels = array($test);
		
		// $name='ContactList';
		// $users=UserChild::getNames();
		// $accounts=AccountChild::getNames();

		if(isset($_POST['ContactList'])) {
			// clear values that haven't been changed from the default
			// foreach($_POST['ContactChild'] as $name => &$value) {
				// if($value == $model->getAttributeLabel($name))
					// $value = '';
			// }
			$model->attributes = $_POST['ContactList'];

			$model->createDate=time();
			$model->lastUpdated=time();
			
			
			
			$model->save();
			
		}
		$attributeList = array_flip(ContactChild::attributeLabels());
		$users = UserChild::getNames();
		
		
		$this->render('createList',array(
			'model'=>$model,
			'criteriaModels'=>$criteriaModels,
			'users'=>$users,
			'attributeList'=>$attributeList,
			'comparisonList'=>array(
				'='=>'=',
				'>'=>'<',
				'<'=>'<',
				'empty'=>Yii::t('empty','empty'),
				'contains'=>Yii::t('contacts','contains'),
			),
			'listTypes'=>array(
				'dynamic'=>Yii::t('contacts','Dynamic'),
				'static'=>Yii::t('contacts','Static')
			),
		));
	
	}

	
	
	
	public function actionUpdateList() {
	

	}
	
	
	
	
	
	public function actionDeleteList() {
	
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

			$model = new ContactChild;

			$model->visibility = 1;
			$model->createDate=time();
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
		$count=0;
		while($arr=fgetcsv($fp)){
			if($count>0){
				$pieces=$arr;                
				$model = new ContactChild;

				$model->visibility=1;
				$model->assignedTo=Yii::app()->user->getName();
				$model->firstName=$pieces[0];
				$model->lastName=$pieces[1];
				$model->title=$pieces[2];
				$model->company=$pieces[3];
				$model->phone=$pieces[4];
				$model->email=$pieces[5];
				$model->website=$pieces[6];
				$model->address=$pieces[7];
				$model->city=$pieces[8];
				$model->state=$pieces[9];
				$model->zipcode=$pieces[10];
				$model->country=$pieces[11];
				$model->backgroundInfo=$pieces[12];
				$model->lastUpdated=$pieces[13];
				$model->twitter=$pieces[14];
				$model->linkedin=$pieces[15];
				$model->skype=$pieces[16];
				$model->googleplus=$piecs[17];
				$model->priority=$pieces[18];
				$model->leadSource=$pieces[19];
				$model->rating=$pieces[20];
				$model->createDate=$pieces[21];
				$model->facebook=$pieces[22];
				$model->otherUrl=$pieces[23];

				if($model->save()){

				}

			}
			$count++;

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
		$contacts=ContactChild::model()->findAll();
		$list=array(array('First Name','Last Name', 'Title','Company', 'Phone', 'Email', 'Website', 'Address', 'City', 'State', 'Zip Code', 'Country', 'Background Info', 'Last Updated', 'Priority', 'Lead Source', 'Create Date'));
		foreach($contacts as $contact){
			$list[]=$contact->attributes;
		}
		$file='file.csv';
		$fp = fopen($file, 'w+');
		
		foreach ($list as $fields) {
			unset($fields['id']);
			unset($fields['accountId']);
			unset($fields['visibility']);
			unset($fields['assignedTo']);
			unset($fields['updatedBy']);
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
		$model = new ContactChild('search');
		$name = 'ContactChild';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model = CActiveRecord::model('ContactChild')->findByPk((int) $id);
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
