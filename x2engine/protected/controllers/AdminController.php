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
class AdminController extends Controller {
	
	public $portlets=array();

	public function actionIndex() {
		$this->render('index');
	}
	
	public function actionHowTo($guide) {
		if($guide=='gii')
			$this->render('howToGii');
		else if($guide=='model')
			$this->render('howToModel');
		else
			$this->redirect('index');
	}

	// Uncomment the following methods and override them if needed
	
	public function filters() {
		// return the filter configuration for this controller, e.g.:
		return array(
			'accessControl',
		);
	}
	/*
	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('viewPage'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('index','howTo','searchContact','sendEmail','mail','search','toggleAccounts',
					'export','import','uploadLogo','toggleDefaultLogo','createModule','deleteModule','exportModule',
					'importModule','toggleSales','setTimeout','setChatPoll','renameModules','manageModules','createPage','contactUs'),
				'users'=>array('admin'),
			),
			array('deny', 
				'users'=>array('*')
			)
		);
	}
	
	public function actionSearchContact() {
		$this->render('searchContactInfo');
	}
	
	public function actionSendEmail() {
		$criteria=$_POST['searchTerm'];
		
		$mailingList=ContactChild::getMailingList($criteria);
		
		$this->render('sendEmail', array(
			'criteria'=>$criteria,
			'mailingList'=>$mailingList,
		));
	}

	public function actionMail() {
		$subject=$_POST['subject'];
		$body=$_POST['body'];
		$criteria=$_POST['criteria'];
		
		$headers='From: '.Yii::app()->name;
		
		$mailingList=ContactChild::getMailingList($criteria);
		
		foreach($mailingList as $email) {
			mail($email,$subject,$body,$headers);
		}
		
		$this->render('mail', array(
			'mailingList'=>$mailingList,
			'criteria'=>$criteria,
		));
	}
	
	public function actionContactUs() {

		if(isset($_POST['email'])) {
			$email=$_POST['email'];
			$subject=$_POST['subject'];
			$body=$_POST['body'];
			
			mail('contact@x2engine.com',$subject,$body,"From: $email");
			$this->redirect('index');
		}
		
		$this->render('contactUs');
	}
	
	public function actionToggleAccounts() {
		$admin=Admin::model()->findByPk(1);
		if($admin->accounts==1)
			$admin->accounts=0;
		else
			$admin->accounts=1;
		
		$admin->update();
		
		$this->redirect('index');
	}
		
	public function actionToggleSales() {
		$admin=Admin::model()->findByPk(1);
		if($admin->sales==1)
			$admin->sales=0;
		else
			$admin->sales=1;
		
		$admin->update();
		
		$this->redirect('index');
	}
	
	public function actionSetTimeout() {
		
		$admin=Admin::model()->findByPk(1);
		if(isset($_POST['Admin'])) {
			$timeout=$_POST['Admin']['timeout'];
			
			$admin->timeout=$timeout;
			
			if($admin->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('setTimeout',array(
			'admin'=>$admin,
		));
	}

	public function actionSetChatPoll() {
		
		$admin = CActiveRecord::model('AdminChild')->findByPk(1);
		if(isset($_POST['AdminChild'])) {
			$timeout = $_POST['AdminChild']['chatPollTime'];
			
			$admin->chatPollTime=$timeout;

			if($admin->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('setChatPoll',array(
			'admin'=>$admin,
		));
	}

	public function actionCreatePage() {

		$model=new DocChild;
		$users=UserChild::getNames();
		if(isset($_POST['DocChild'])) {
			
			$model->attributes=$_POST['DocChild'];
			$arr=$model->editPermissions;
			if(isset($arr))
				$model->editPermissions=AccountChild::parseUsers($arr);
			$model->text=$_POST['msgpost'];
			$model->createdBy='admin';
			$model->createDate=time();
			$model->lastUpdated=time();
			$model->updatedBy='admin';
			
			$admin=Admin::model()->findByPk(1);
			if(isset($admin)) {
				if($admin->menuOrder!="") {
					$admin->menuOrder.=":".mb_ereg_replace(':','&#58;',$model->title);
					$admin->menuVisibility.=":1";
					$admin->menuNicknames.=":".mb_ereg_replace(':','&#58;',$model->title);
				}
				else{
					$admin->menuOrder=$model->title;
					$admin->menuVisibility.=":1";
					$admin->menuNicknames=$model->title;
				}
				$admin->save();
			}
			
			if($model->save()) {
				$this->redirect(array('viewPage','id'=>$model->id));
			}
		}
		
		$this->render('createPage',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
	
	public function actionViewPage($id) {
		$model=DocChild::model()->findByPk($id);
		$this->render('viewTemplate',array(
			'model'=>$model,
		));
	}
	
	public function actionRenameModules() {
		
		$admin=Admin::model()->findByPk(1);

		$menuItems = AdminChild::getMenuItems();
		
		foreach($menuItems as $key => $value)
			$menuItems[$key] = mb_ereg_replace('&#58;',':',$value);	// decode any colons

		if(isset($_POST['module']) && isset($_POST['name'])) {
			$module=$_POST['module'];
			$name=$_POST['name'];

			$menuItems[$module]=$name;
			
			//$orderStr="";
			//$nickStr="";

			foreach($menuItems as $key=>$value) {
				//$orderStr .= $key.":";
				//$nickStr .= $value.":";
				
				$menuItems[$key] = mb_ereg_replace(':','&#58;',$value);	// encode any colons in nicknames
			}
			
			//$orderStr=substr($orderStr,0,-1);
			//$nickStr=substr($nickStr,0,-1);
			
			$admin->menuOrder = implode(':',array_keys($menuItems));
			$admin->menuNicknames = implode(':',array_values($menuItems));
			
			if($admin->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('renameModules',array(
			'modules'=>$menuItems,
		));
	}

	public function actionManageModules() {

		// get admin model
		$admin=Admin::model()->findByPk(1);

		$nicknames = explode(":",$admin->menuNicknames);
		$menuOrder = explode(":",$admin->menuOrder);
		$menuVis = explode(":",$admin->menuVisibility);
		
		$menuItems = array();		// assoc. array with correct order, containing realName => nickName
		$selectedItems = array();
		
		for($i=0;$i<count($menuOrder);$i++) {				// load items from menuOrder into $menuItems keys
			$menuItems[$menuOrder[$i]] = Yii::t('app',$nicknames[$i]);	// set values to their (translated) nicknames
			
			if($menuVis[$i] == 1)
				$selectedItems[] = $menuOrder[$i];			// but only include them if they are visible
		}


		if(isset($_POST['formSubmit'])) {
		
			$selectedItems = isset($_POST['menuItems'])? $_POST['menuItems'] : array();
			$newMenuItems = array();
			
			// enable/disable accounts and sales features if they are added/removed
			if(in_array('accounts',$selectedItems))
				$admin->accounts=1;
			else
				$admin->accounts=0;
				
			if(in_array('sales',$selectedItems))
				$admin->sales=1;
			else
				$admin->sales=0;
			
			// build $newMenuItems array
			foreach($selectedItems as $item) {
				$newMenuItems[$item] = $menuItems[$item];	// copy each selected item into $newMenuItems
				unset($menuItems[$item]);					// and remove them from $menuItems
			}
			
			$newMenuVis = array();
			for($i=0;$i<count($newMenuItems);$i++) {	// set all selected items to '1'
				$newMenuVis[] = 1;
			}
			for($i=0;$i<count($menuItems);$i++) {		// set all unselected items to '0'
				$newMenuVis[] = 0;
			}
			
			$newMenuOrder = array_merge(array_keys($newMenuItems), array_keys($menuItems));
			$newNicknames = array_merge(array_values($newMenuItems), array_values($menuItems));
			
			foreach($newNicknames as &$value)
				$value = mb_ereg_replace(':','&#58;',$value);	// encode any colons
			
			$admin->menuVisibility = implode(":",$newMenuVis);
			$admin->menuOrder = implode(":",$newMenuOrder);
			$admin->menuNicknames = implode(":",$newNicknames);

			if($admin->update()) {
				$this->redirect('manageModules');
			}
		}
		$this->render('manageModules',array(
			'menuItems'=>$menuItems,
			'selectedItems'=>$selectedItems
		));
	}
	
	
	public function actionUploadLogo() {
		if(isset($_FILES['logo-upload'])) {
			$temp = CUploadedFile::getInstanceByName('logo-upload');
			$name=$temp->getName();
			$temp->saveAs('uploads/logos/'.$name);
			$admin=ProfileChild::model()->findByAttributes(array('username'=>'admin'));
			$logo=Media::model()->findByAttributes(array('associationId'=>$admin->id,'associationType'=>'logo'));
			if(isset($logo)) {
				unlink($logo->fileName);
				$logo->delete();
			}
			
			$logo=new Media;
			$logo->associationType='logo';
			
			$logo->associationId=$admin->id;
			$logo->fileName='uploads/logos/'.$name;
			
			if($logo->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('uploadLogo');
	}
	
	public function actionToggleDefaultLogo() {
		
		$adminProf=ProfileChild::model()->findByAttributes(array('username'=>'admin'));
		$logo=Media::model()->findByAttributes(array('associationId'=>$adminProf->id,'associationType'=>'logo'));
		if(!isset($logo)) {

			$logo=new Media;
			$logo->associationType='logo';
			$name='yourlogohere.png';
			$logo->associationId=$adminProf->id;
			$logo->fileName='uploads/logos/'.$name;

			if($logo->save()) {

			}
		} else {
			$logo->delete();
		}
		$this->redirect(array('index'));
	}
	
	public function actionCreateModule() {
	
		$errors = array();

		if(isset($_POST['moduleName'])) {
		
			$title = trim($_POST['title']);
			$recordName = trim($_POST['recordName']);
			
			$moduleName = trim($_POST['moduleName']);

			if(preg_match('/\W/',$moduleName) || preg_match('/^[^a-zA-Z]+/',$moduleName))			// are there any non-alphanumeric or _ chars?
				$errors[] = Yii::t('module','Invalid table name'); //$this->redirect('createModule');									// or non-alpha characters at the beginning?

			if($moduleName == '')		// we will attempt to use the title
				$moduleName = $title;	// as the backend name, if possible
				
			if($recordName == '')		// use title for record name 
				$recordName = $title;	// if none is provided

			$trans = array(
				'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 
				'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 
				'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 
				'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 
				'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 
				'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 
				'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
			);

			$moduleName = strtolower(strtr($moduleName,$trans));		// replace characters with their A-Z equivalent, if possible
			
			$moduleName = preg_replace('/\W/','',$moduleName);	// now remove all remaining non-alphanumeric or _ chars
			
			$moduleName = preg_replace('/^[0-9_]+/','',$moduleName);	// remove any numbers or _ from the beginning
			
			
			
			if($moduleName == '')								// if there is nothing left of moduleName at this point,
				$moduleName = 'module' . substr(time(),5);		// just generate a random one

			$admin = Admin::model()->findByPk(1);
			$menuOrder = explode(':',$admin->menuOrder);
			$menuNickNames = explode(':',$admin->menuNicknames);
			
			if(in_array(mb_ereg_replace(':','&#58;',$title),$menuNickNames)
				|| in_array($moduleName,$menuOrder)
				|| array_key_exists('x2_'.$moduleName,Yii::app()->db->schema->getTables()))
				$errors[] = Yii::t('module','A module with that title already exists');
			
			if(empty($errors)) {
			
				$assignedTo=$_POST['displayAssignedTo'];
				$description=$_POST['displayDescription'];
				
				$displayOne=$_POST['displayOne'];
				$fieldOne=$_POST['fieldOne'];
				if($fieldOne==Yii::t('module','Enter field name here') || $fieldOne=="") {
					$fieldOne="";
					$displayOne=0;
				}
				
				$displayTwo=$_POST['displayTwo'];
				$fieldTwo=$_POST['fieldTwo'];
				if($fieldTwo==Yii::t('module','Enter field name here') || $fieldTwo=='') {
					$fieldTwo="";
					$displayTwo=0;
				}
				
				$displayThree=$_POST['displayThree'];
				$fieldThree=$_POST['fieldThree'];
				if($fieldThree==Yii::t('module','Enter field name here') || $fieldThree=="") {
					$fieldThree="";
					$displayThree=0;
				}
				
				$displayFour=$_POST['displayFour'];
				$fieldFour=$_POST['fieldFour'];
				if($fieldFour==Yii::t('module','Enter field name here') || $fieldFour=='') {
					$fieldFour="";
					$displayFour=0;
				}
				
				$displayFive=$_POST['displayFive'];
				$fieldFive=$_POST['fieldFive'];
				if($fieldFive==Yii::t('module','Enter field name here') || $fieldFive=='') {
					$fieldFive="";
					$displayFive=0;
				}

				$this->writeConfig($title,$moduleName,$recordName,$assignedTo,$description,$displayOne,$fieldOne,$displayTwo,$fieldTwo,$displayThree,$fieldThree,$displayFour,$fieldFour,$displayFive,$fieldFive);
				
				$this->createNewTable($moduleName);
				
				$this->createSkeletonDirectories($moduleName);
				
				$model = Yii::app()->file->set('protected/models/'.ucfirst($moduleName).'.php');
				$contents = $model->getContents();
				$contents = preg_replace('/\$fieldOne/',$fieldOne,$contents);
				$contents = preg_replace('/\$fieldTwo/',$fieldTwo,$contents);
				$contents = preg_replace('/\$fieldThree/',$fieldThree,$contents);
				$contents = preg_replace('/\$fieldFour/',$fieldFour,$contents);
				$contents = preg_replace('/\$fieldFive/',$fieldFive,$contents);
				$model->setContents($contents);
				
				// add new module to the admin menuOrder fields
				if(empty($admin->menuOrder)) {
					$admin->menuOrder = $moduleName;
					$admin->menuVisibility = "1";
					$admin->menuNicknames = $title;
				} else {
					$admin->menuOrder .= ":" . $moduleName;
					$admin->menuVisibility .= ":1";
					$admin->menuNicknames .= ":" . mb_ereg_replace(':','&#58;',$title);	// encode any colons so they don't break the admin menuOrder field;
				}
				$admin->save();
				
				$this->redirect(array($moduleName.'/index'));
			}
		}
		
		$this->render('createModule',array('errors'=>$errors));
	}
	
	private function writeConfig($title,$moduleName,$recordName,$assignedTo,$description,$displayOne,$fieldOne,$displayTwo,$fieldTwo,$displayThree,$fieldThree,$displayFour,$fieldFour,$displayFive,$fieldFive) {
		
		$configFile = Yii::app()->file->set('protected/config/templatesConfig.php', true);
		$configFile->copy($moduleName.'Config.php');
		
		$configFile=Yii::app()->file->set('protected/config/'.$moduleName.'Config.php', true);
		
		$str = "<?php
\$moduleConfig = array(
	'title'=>'".addslashes($title)."',
	'moduleName'=>'".addslashes($moduleName)."',
	'recordName'=>'".addslashes($recordName)."',
	'assignedToDisplay'=>'".addslashes($assignedTo)."',
	'descriptionDisplay'=>'".addslashes($description)."',
	'displayOne'=>'".addslashes($displayOne)."',
	'displayTwo'=>'".addslashes($displayTwo)."',
	'displayThree'=>'".addslashes($displayThree)."',
	'displayFour'=>'".addslashes($displayFour)."',
	'displayFive'=>'".addslashes($displayFive)."',
);
?>";
/* 		$str="<?php
\$name=\"$name\";
\$title=\"".ucfirst($name)."\";
\$assignedToDisplay=\"$assignedTo\";
\$descriptionDisplay=\"$description\";
\$displayOne=\"$displayOne\";
\$displayTwo=\"$displayTwo\";
\$displayThree=\"$displayThree\";
\$displayFour=\"$displayFour\";
\$displayFive=\"$displayFive\";
?>"; */
		
		$configFile->setContents($str);
	}
	
	private function createNewTable($moduleName) {
		
		$sql="CREATE TABLE x2_".$moduleName."(
id INT NOT NULL AUTO_INCREMENT primary key,
assignedTo VARCHAR(40),
name VARCHAR(100) NOT NULL,
description TEXT,
fieldOne VARCHAR(255),
fieldTwo VARCHAR(255),
fieldThree VARCHAR(255),
fieldFour VARCHAR(255),
fieldFive VARCHAR(255),
createDate INT,
lastUpdated INT,
updatedBy VARCHAR(40)
)";
		$command = Yii::app()->db->createCommand($sql);
		$command->execute();
	}
	
	private function createSkeletonDirectories($moduleName) {
		
		$errors = array();
		
		if(mkdir('protected/views/'.$moduleName)) {
		
			$fileNames = array(
				'protected/views/templates/_detailView.php',
				'protected/views/templates/_form.php',
				'protected/views/templates/_search.php',
				'protected/views/templates/_view.php',
				'protected/views/templates/admin.php',
				'protected/views/templates/create.php',
				'protected/views/templates/index.php',
				'protected/views/templates/update.php',
				'protected/views/templates/view.php',
				'protected/models/Templates.php',
				'protected/controllers/TemplatesController.php',
			);
			foreach($fileNames as $fileName) {
				$templateFile = Yii::app()->file->set($fileName);
				if(!$templateFile->exists) {
					$errors[] = Yii::t('module','Template file {filename} could not be found');
					break;
				} else {
					$contents = $templateFile->getContents();
					$contents = preg_replace('/templates/',$moduleName,$contents);			// write moduleName into view files
					$contents = preg_replace('/Templates/',ucfirst($moduleName),$contents);
					
					$newFileName = preg_replace('/templates/',$moduleName,$fileName);				// replace 'template' with
					$newFileName = preg_replace('/Templates/',ucfirst($moduleName),$newFileName);	// module name for new files
					$newFile = Yii::app()->file->set($newFileName);
					$newFile->create();
					$newFile->setContents($contents);
				}
			}
/* 			$_form=Yii::app()->file->set('protected/views/templates/_form.php');
			$_search=Yii::app()->file->set('protected/views/templates/_search.php');
			$_view=Yii::app()->file->set('protected/views/templates/_view.php');
			$admin=Yii::app()->file->set('protected/views/templates/admin.php');
			$create=Yii::app()->file->set('protected/views/templates/create.php');
			$index=Yii::app()->file->set('protected/views/templates/index.php');
			$update=Yii::app()->file->set('protected/views/templates/update.php');
			$view=Yii::app()->file->set('protected/views/templates/view.php');
			$_detailView=Yii::app()->file->set('protected/views/templates/_detailView.php');
			
			$_form->copy('protected/views/'.$moduleName.'/_form.php');
			$_form=Yii::app()->file->set('protected/views/'.$moduleName.'/_form.php');
			$contents=$_form->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$_form->setContents($contents);
			
			$_search->copy('protected/views/'.$moduleName.'/_search.php');
			$_search=Yii::app()->file->set('protected/views/'.$moduleName.'/_search.php');
			$contents=$_search->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$_search->setContents($contents);
			
			$_view->copy('protected/views/'.$moduleName.'/_view.php');
			$_view=Yii::app()->file->set('protected/views/'.$moduleName.'/_view.php');
			$contents=$_view->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$_view->setContents($contents);
			
			$admin->copy('protected/views/'.$moduleName.'/admin.php');
			$admin=Yii::app()->file->set('protected/views/'.$moduleName.'/admin.php');
			$contents=$admin->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$admin->setContents($contents);
			
			$create->copy('protected/views/'.$moduleName.'/create.php');
			$create=Yii::app()->file->set('protected/views/'.$moduleName.'/create.php');
			$contents=$create->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$create->setContents($contents);
			
			$index->copy('protected/views/'.$moduleName.'/index.php');
			$index=Yii::app()->file->set('protected/views/'.$moduleName.'/index.php');
			$contents=$index->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$index->setContents($contents);
			
			$update->copy('protected/views/'.$moduleName.'/update.php');
			$update=Yii::app()->file->set('protected/views/'.$moduleName.'/update.php');
			$contents=$update->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$update->setContents($contents);
			
			$view->copy('protected/views/'.$moduleName.'/view.php');
			$view=Yii::app()->file->set('protected/views/'.$moduleName.'/view.php');
			$contents=$view->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$view->setContents($contents);
			
			$_detailView->copy('protected/views/'.$moduleName.'/_detailView.php');
			$_detailView=Yii::app()->file->set('protected/views/'.$moduleName.'/_detailView.php');
			$contents=$_detailView->getContents();
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$_detailView->setContents($contents);
			
			$controller=Yii::app()->file->set('protected/controllers/TemplatesController.php');
			$controller->copy('protected/controllers/'.ucfirst($moduleName)."Controller.php");
			$controller=Yii::app()->file->set('protected/controllers/'.ucfirst($moduleName)."Controller.php");
			$contents=$controller->getContents();
			$contents=preg_replace('/TemplatesController/',ucfirst($moduleName)."Controller",$contents);
			$contents=preg_replace('/Templates/',ucfirst($moduleName),$contents);
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$controller->setContents($contents);
			
			$model=Yii::app()->file->set('protected/models/Templates.php');
			$model->copy('protected/models/'.ucfirst($moduleName).'.php');
			$model=Yii::app()->file->set('protected/models/'.ucfirst($moduleName).'.php');
			$contents=$model->getContents();
			$contents=preg_replace('/class Templates extends CActiveRecord/','class '.ucfirst($moduleName).' extends CActiveRecord',$contents);
			$contents=preg_replace('/templates/',$moduleName,$contents);
			$model->setContents($contents); */
		}
	}
	
	public function actionDeleteModule() {
	
		$admin = Admin::model()->findByPk(1);
		
		if(isset($_POST['name'])) {
			$moduleName = $_POST['name'];

			$menuOrder = explode(":",$admin->menuOrder);
			$menuVis = explode(":",$admin->menuVisibility);
			$menuNickNames = explode(":",$admin->menuNicknames);

			$moduleIndex = array_search($moduleName,$menuOrder);
			if($moduleIndex!==false) {				// if the module is in menuOrder
				unset($menuOrder[$moduleIndex]);		// then remove it from menuOrder,
				unset($menuVis[$moduleIndex]);			// menuVisibility
				unset($menuNickNames[$moduleIndex]);	// and menuNicknames
			}
			$admin->menuOrder = implode(':',$menuOrder);
			$admin->menuVisibility = implode(':',$menuVis);
			$admin->menuNicknames = implode(':',$menuNickNames);
			
			if($admin->save()) {
				//$moduleName=lcfirst($moduleName);
				$file = Yii::app()->file->set('protected/controllers/'.ucfirst($moduleName).'Controller.php');
				$this->deleteTable($moduleName);
				
				if($file->exists) {
					$this->deleteFiles($moduleName);
				} else {
					$file = Yii::app()->file->set('protected/views/admin/view'.ucfirst($moduleName).'.php');
					$file->delete();
				}
			} else {
				print_r($admin->getErrors());
			}

			$this->redirect(array('admin/index'));
		}
		
		$arr = array();
		$standard = array('contacts','actions','docs','accounts','sales');

		$pieces = explode(":",$admin->menuOrder);
		foreach($pieces as $piece) {
			if(array_search($piece,$standard)===false)
				$arr[]=$piece;
		}
		
		$this->render('deleteModule',array(
			'modules'=>$arr,
		));
	}
	
	private function deleteTable($moduleName) {
		
		if(Yii::app()->db->schema->getTable("x2_$moduleName")) {
			$command = Yii::app()->db->createCommand()->dropTable("x2_$moduleName");
			//$command->execute();
		}
		//$sql="DROP TABLE x2_$name IF EXISTS";
		//$command=Yii::app()->db->createCommand($sql);
		//$command->execute();
	}
	
	private function deleteFiles($moduleName) {
	
		$fileNames = array(
			'protected/views/'.$moduleName.'/',
			'protected/controllers/'.ucfirst($moduleName).'Controller.php',
			'protected/models/'.ucfirst($moduleName).'.php',
			'protected/config/'.$moduleName.'Config.php',
		);
		
		foreach($fileNames as $fileName) {
			$file=Yii::app()->file->set($fileName);
			if($file->exists)
				$file->delete();
		}
		// $dir->delete();
		
		// $controller=Yii::app()->file->set();
		// $controller->delete();
		
		// $model=Yii::app()->file->set();
		// $model->delete();
	}
	
	public function actionExportModule() {
		
		if(isset($_POST['name'])) {
			$moduleName=lcfirst($_POST['name']);
			
			mkdir($moduleName);
			mkdir("$moduleName/$moduleName");
			
			$model=Yii::app()->file->set('protected/models/'.ucfirst($moduleName).'.php');
			$model->copy("$moduleName/".ucfirst($moduleName).".php");
			
			$controller=Yii::app()->file->set('protected/controllers/'.ucfirst($moduleName).'Controller.php');
			$controller->copy("$moduleName/".ucfirst($moduleName)."Controller.php");
			
			$config=Yii::app()->file->set('protected/config/'.$moduleName.'Config.php');
			$config->copy("$moduleName/".$moduleName.'Config.php');
			
			$views=Yii::app()->file->set('protected/views/'.$moduleName);
			$contents=$views->contents;
			
			foreach($contents as $file) {
				$pieces=explode('/',$file);
				$fileName=$pieces[count($pieces)-1];
				$temp=Yii::app()->file->set('protected/views/'.$moduleName.'/'.$fileName);
				$temp->copy("$moduleName/$moduleName/".$fileName);
			}
			if(file_exists($moduleName.".zip")) {
				unlink($moduleName.".zip");
			}
			$zip=Yii::app()->zip;
			$zip->makeZip($moduleName,$moduleName.".zip");
			$dir=Yii::app()->file->set($moduleName);
			$dir->delete();
			$finalFile=Yii::app()->file->set($moduleName.".zip");
			$finalFile->download();
		}
		
		$admin=Admin::model()->findByPk(1);
		$arr=array();
		$standard=array('contacts','actions','docs','accounts','sales');
		$list=$admin->menuOrder;
		$pieces=explode(":",$list);
		foreach($pieces as $piece) {
			if(array_search($piece,$standard)===false)
				$arr[]=$piece;
		}
		
		$this->render('exportModules',array(
			'modules'=>$arr,
		));
	}
	
	public function actionImportModule() {
		
		if (isset($_FILES['data'])) {

			$module=Yii::app()->file->set('data');
			$moduleName=$module->filename;
			
			$this->createNewTable($moduleName);
			
			$zip=Yii::app()->zip;
			$zip->extractZip("$moduleName.zip",'temp');
			
			$config=Yii::app()->file->set('temp/'.$moduleName.'/'.$moduleName.'Config.php');
			$config->copy('protected/config/'.$moduleName.'Config.php');
			
			$model=Yii::app()->file->set('temp/'.$moduleName.'/'.ucfirst($moduleName).'.php');
			$model->copy('protected/models/'.ucfirst($moduleName).'.php');
			
			$controller=Yii::app()->file->set('temp/'.$moduleName.'/'.ucfirst($moduleName).'Controller.php');
			$controller->copy('protected/controllers/'.ucfirst($moduleName).'Controller.php');
			
			$views=Yii::app()->file->set('temp/'.$moduleName.'/'.$moduleName);
			$contents=$views->contents;
			mkdir('protected/views/'.$moduleName);
			foreach($contents as $file) {
				$pieces=explode('/',$file);
				$fileName=$pieces[count($pieces)-1];
				$temp=Yii::app()->file->set("temp/$moduleName/$moduleName/".$fileName);
				$res=$temp->copy('protected/views/'.$moduleName.'/'.$fileName);
			}
			
			$admin=Admin::model()->findByPk(1);
			if(isset($admin)) {
				if($admin->menuOrder!="") {
					$admin->menuOrder.=":".ucfirst($moduleName);
					$admin->menuVisibility.=":1";
					$admin->menuNicknames.=":".ucfirst($moduleName);
				}
				else{
					$admin->menuOrder=ucfirst($moduleName);
					$admin->menuVisibility="1";
					$admin->menuNicknames=ucfirst($moduleName);
				}
				$admin->save();
			}
			
			$dir=Yii::app()->file->set('temp');
			$dir->delete();
			
			$this->redirect(array($moduleName.'/index'));
		}
		$this->render('importModule');
	}
		
	
	public function actionExport() {
		$this->globalExport();
		$this->render('export',array(
		));
	}
	
	private function globalExport() {
		
		$file='file.csv';
		$fp = fopen($file, 'w+');
		
		$users=UserChild::model()->findAll();
		$contacts=ContactChild::model()->findAll();
		$actions=ActionChild::model()->findAll();
		$sales=SaleChild::model()->findAll();
		$accounts=AccountChild::model()->findAll();
		$docs=Docs::model()->findAll();
		$profiles=Profile::model()->findAll();
		
		fputcsv($fp,array("1.0"));
		
		$userList=array();
		foreach($users as $user) {
			$userList[]=$user->attributes;
		}
		foreach ($userList as $fields) {
			unset($fields['id']);
			unset($fields['updatePassword']);
			$fields[]='user';
			fputcsv($fp, $fields);
			
		}
		
	
		$contactList=array();
		foreach($contacts as $contact) {
			$contactList[]=$contact->attributes;
		}
		foreach ($contactList as $fields) {
			unset($fields['id']);
			$fields[]='contact';
			fputcsv($fp, $fields);
			
		}
		
		$actionList=array();
		foreach($actions as $action) {
			$actionList[]=$action->attributes;
		}
		foreach ($actionList as $fields) {
			unset($fields['id']);
			$fields[]='action';
			fputcsv($fp, $fields);
			
		}
		
		$saleList=array();
		foreach($sales as $sale) {
			$saleList[]=$sale->attributes;
		}
		foreach ($saleList as $fields) {
			unset($fields['id']);
			$fields[]='sale';
			fputcsv($fp, $fields);
			
		}
		
		$accountList=array();
		foreach($accounts as $account) {
			$accountList[]=$account->attributes;
		}
		foreach ($accountList as $fields) {
			unset($fields['id']);
			$fields[]='account';
			fputcsv($fp, $fields);
			
		}
		
		$docList=array();
		foreach($docs as $doc) {
			$docList[]=$doc->attributes;
		}
		foreach ($docList as $fields) {
			unset($fields['id']);
			$fields[]='doc';
			fputcsv($fp, $fields);
			
		}
		
		$profileList=array();
		foreach($profiles as $profile) {
			if($profile->username!='admin')
				$profileList[]=$profile->attributes;
		}
		foreach ($profileList as $fields) {
			unset($fields['id']);
			unset($fields['avatar']);
			$fields[]='profile';
			fputcsv($fp, $fields);
			
		}


		fclose($fp);

	}
	
	public function actionImport() {
		if (isset($_FILES['data'])) {

				$temp = CUploadedFile::getInstanceByName('data');
				$temp->saveAs('data.csv');
				$this->globalImport('data.csv');
			}
			$this->render('import');
		}
		
	private function globalImport($file) {
		$fp=fopen($file,'r+');
		$version=fgetcsv($fp);
		$version=$version[0];
		
		while($pieces=fgetcsv($fp)) {
			$end=count($pieces)-1;
			while($pieces[$end]=="" && $end>0) {
				$end--;
			}
			if($pieces[$end]=='contact') {
				unset($pieces[$end]);
				$this->importContact($pieces,$version);
			}else if($pieces[$end]=='user') {
				unset($pieces[$end]);
				$this->importUser($pieces,$version);
			}else if ($pieces[$end]=='action') {
				unset($pieces[$end]);
				$this->importAction($pieces,$version);
			}else if($pieces[$end]=='sale') {
				unset($pieces[$end]);
				$this->importSale($pieces,$version);
			}else if($pieces[$end]=='account') {
				unset($pieces[$end]);
				$this->importAccount($pieces,$version);
			}else if($pieces[$end]=='doc') {
				unset($pieces[$end]);
				$this->importDoc($pieces,$version);
			}else if($pieces[$end]=='profile') {
				unset($pieces[$end]);
				$this->importProfile($pieces,$version);
			} else {
				
			}
			
		}
		unlink($file);
		$this->redirect('index');
	}
		
		
	private function importUser($pieces,$version) {
		$model=new UserChild;
		
		$model->firstName=$pieces[0];
		$model->lastName=$pieces[1];
		$model->username=$pieces[2];
		$model->password=$pieces[3];
		$model->title=$pieces[4];
		$model->department=$pieces[5];
		$model->officePhone=$pieces[6];
		$model->cellPhone=$pieces[7];
		$model->homePhone=$pieces[8];
		$model->address=$pieces[9];
		$model->backgroundInfo=$pieces[10];
		$model->emailAddress=$pieces[11];
		$model->status=$pieces[12];
		$model->lastUpdated=$pieces[13];
		$model->updatedBy=$pieces[14];
		$model->recentItems=$pieces[15];
		$model->topContacts=$pieces[16];
		
		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {
		}
	}
	
	private function importContact($pieces,$version) {
		$model = new ContactChild;
		
		$model->visibility=1;
		$model->assignedTo=Yii::app()->user->getName();
		$model->firstName=$pieces[0];
		$model->lastName=$pieces[1];
		$model->title=$pieces[2];
		$model->company=$pieces[3];
		$model->accountId=$pieces[4];
		$model->phone=$pieces[5];
		$model->email=$pieces[6];
		$model->website=$pieces[7];
		$model->address=$pieces[8];
		$model->city=$pieces[9];
		$model->state=$pieces[10];
		$model->zipcode=$pieces[11];
		$model->country=$pieces[12];
		$model->visibility=$pieces[13];
		$model->assignedTo=$pieces[14];
		$model->backgroundInfo=$pieces[15];
		$model->twitter=$pieces[16];;
		$model->linkedin=$pieces[17];;
		$model->skype=$pieces[18];;
		$model->googleplus=$pieces[19];
		$model->lastUpdated=$pieces[20];
		$model->updatedBy=$pieces[21];
		$model->priority=$pieces[22];
		$model->leadSource=$pieces[23];
		$model->rating=$pieces[24];
		$model->createDate=$pieces[25];

		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {

		} else {
			
		}
	}
		
		
	private function importAction($pieces,$version) {
		$model=new ActionChild;
		
		$model->assignedTo=$pieces[0];
		$model->actionDescription=$pieces[1];
		$model->visibility=$pieces[2];
		$model->associationId=$pieces[3];
		$model->associationType=$pieces[4];
		$model->associationName=$pieces[5];
		$model->dueDate=$pieces[6];
		$model->showTime=$pieces[7];
		$model->priority=$pieces[8];
		$model->type=$pieces[9];
		$model->createDate=$pieces[10];
		$model->complete=$pieces[11];
		$model->reminder=$pieces[12];
		$model->completedBy=$pieces[13];
		$model->completeDate=$pieces[14];
		$model->lastUpdated=$pieces[15];
		$model->updatedBy=$pieces[16];
		
		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {
			
		} else {
			
		}
	}
		
	private function importSale($pieces,$version) {
		$model=new SaleChild;
		
		$model->assignedTo=$pieces[0];
		$model->name=$pieces[1];
		$model->quoteAmount=$pieces[2];
		$model->salesStage=$pieces[3];
		$model->expectedCloseDate=$pieces[4];
		$model->probability=$pieces[5];
		$model->leadSource=$pieces[6];
		$model->description=$pieces[7];
		$model->createDate=$pieces[8];
		$model->lastUpdated=$pieces[9];
		$model->updatedBy=$pieces[10];
		
		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {
			
		}
	}
	
	private function importAccount($pieces,$version) {
		$model=new AccountChild;					
		$model->name=$pieces[0];
		$model->website=$pieces[1];
		$model->type=$pieces[2];
		$model->annualRevenue=$pieces[3];
		$model->phone=$pieces[4];
		$model->tickerSymbol=$pieces[5];
		$model->employees=$pieces[6];
		$model->assignedTo=$pieces[7];
		$model->createDate=$pieces[8];
		$model->associatedContacts=$pieces[9];
		$model->description=$pieces[10];
		$model->lastUpdated=$pieces[11];
		$model->updatedBy=$pieces[12];
		
		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {
			
		}
	}

	
	private function importDoc($pieces,$version) {
		$model=new Docs;
		
		$model->title=$pieces[0];
		$model->text=$pieces[1];
		$model->createdBy=$pieces[2];
		$model->createDate=$pieces[3];
		$model->updatedBy=$pieces[4];
		$model->lastUpdated=$pieces[5];
		
		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {
			
		}
	}

	
	
	private function importProfile($pieces,$version) {
		$model=new ProfileChild;
		
		$model->fullName=$pieces[0];
		$model->username=$pieces[1];
		$model->officePhone=$pieces[2];
		$model->cellPhone=$pieces[3];
		$model->emailAddress=$pieces[4];
		$model->notes=$pieces[5];
		$model->status=$pieces[6];
		$model->tagLine=$pieces[7];
		$model->lastUpdated=$pieces[8];
		$model->updatedBy=$pieces[9];
		$model->allowPost=$pieces[10];
		$model->language=$pieces[11];
		if($model->language=="") {
			$model->language="en";
		}
		$model->timeZone=$pieces[12];
		$model->resultsPerPage=$pieces[13];
		$model->widgets=$pieces[14];
		$model->widgetOrder=$pieces[15];
		$model->backgroundColor=$pieces[16];
		$model->menuBgColor=$pieces[17];
		$model->menuTextColor=$pieces[18];
		$model->backgroundImg=$pieces[19];
		$model->pageOpacity=$pieces[20];
		
		$model=Rules::applyRules($model,$version);
		
		if($model->save()) {
		
		} else {
			
		}
	}
}