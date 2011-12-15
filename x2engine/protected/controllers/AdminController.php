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
// ********************************************************************************/  
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
				'actions'=>array('getRoundRobin','updateRoundRobin','getRoutingRules','roundRobin','evenDistro','getRoutingType'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('viewPage','getAttributes'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('index','howTo','searchContact','sendEmail','mail','search','toggleAccounts',
					'export','import','uploadLogo','toggleDefaultLogo','createModule','deleteModule','exportModule',
					'importModule','toggleSales','setTimeout','setChatPoll','renameModules','manageModules',
					'createPage','contactUs','viewChangelog','toggleUpdater','translationManager','addCriteria',
                                        'deleteCriteria','setLeadRouting','roundRobinRules','deleteRouting','addField','removeField',
                                        'customizeFields','manageFields', 'editor'),
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
		
		$mailingList=Contacts::getMailingList($criteria);
		
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
		
		$mailingList=Contacts::getMailingList($criteria);
		
		foreach($mailingList as $email) {
			mail($email,$subject,$body,$headers);
		}
		
		$this->render('mail', array(
			'mailingList'=>$mailingList,
			'criteria'=>$criteria,
		));
	}
        
        public function getRoundRobin(){
		$admin=CActiveRecord::model('Admin')->findByPk(1);
		$rrId=$admin->rrId;
		return $rrId;
	}
	
	public function updateRoundRobin(){
		$admin=CActiveRecord::model('Admin')->findByPk(1);
		$admin->rrId=$admin->rrId+1;
		$admin->save();
	}
        
        public function actionGetRoutingType(){
            $admin=CActiveRecord::model('Admin')->findByPk(1);
            $type=$admin->leadDistribution;
            if($type==""){
                echo "";
            }elseif($type=="evenDistro"){
                echo $this->evenDistro();
            }elseif($type=="trueRoundRobin"){
                echo $this->roundRobin();
            }elseif($type=="customRoundRobin"){
                $arr=$_POST;
                foreach($arr as $key=>$value){
                    $users=$this->getRoutingRules($key,$value);
                    if($users!=""){
                        $rrId=$users[count($users)-1];
                        unset($users[count($users)-1]);
                        $i=$rrId%count($users);
                        echo $users[$i];
                        break;
                    }
                }
            }
        }
        
        public function roundRobin(){
            $admin=AdminChild::model()->findByPk(1);
            $online=$admin->onlineOnly;
            x2base::cleanUpSessions();
            $usernames=array();
            $sessions=SessionChild::getOnlineUsers();
            $users=CActiveRecord::model('UserChild')->findAll();
            unset($users['admin']);
            foreach($users as $user){
                $usernames[]=$user->username;
            }
            if($online==1){
                $users=array_intersect($usernames,$sessions);
            }else{
                $users=$usernames;
            }
            $rrId=$this->getRoundRobin();
            $i=$rrId%count($users);
            $this->updateRoundRobin();
            return $users[$i];
        }
        
        public function evenDistro(){
            $admin=AdminChild::model()->findByPk(1);
            $online=$admin->onlineOnly;
            x2base::cleanUpSessions();
            $usernames=array();
            $sessions=SessionChild::getOnlineUsers();
            $users=CActiveRecord::model('UserChild')->findAll();
            foreach($users as $user){
                $usernames[]=$user->username;
            }
            
            if($online==1){
                $users=array_intersect($usernames,$sessions);
            }else{
                $users=$usernames;
            }
            
            $numbers=array();
            foreach($users as $user){
                if($user!='admin'){
                    $actions=CActiveRecord::model('Actions')->findAllByAttributes(array('assignedTo'=>$user,'complete'=>'No'));
                    if(isset($actions))
                        $numbers[$user]=count($actions);
                    else
                       $numbers[$user]=0; 
                }
            }
            asort($numbers);
            reset($numbers);
            return key($numbers);
        }
        
        public function getRoutingRules($field, $value){
            $admin=AdminChild::model()->findByPk(1);
            $online=$admin->onlineOnly;
            x2base::cleanUpSessions();
            $sessions=SessionChild::getOnlineUsers();
            
            $rule=CActiveRecord::model('LeadRouting')->findByAttributes(array('field'=>$field,'value'=>$value));
            if(isset($rule)){
                $users=$rule->users;
                $users=explode(", ",$users);
                $users[]=$rule->rrId;
                $rule->rrId++;
                $rule->save();
                if($online==1)
                    $users=array_intersect($users,$sessions);
                return $users;
            }else
                return "";
        }
        
        public function actionRoundRobinRules(){
                $model=new LeadRouting;
                $users=UserChild::getNames();
                unset($users['']);
                unset($users['admin']);
                $dataProvider=new CActiveDataProvider('LeadRouting');
		if(isset($_POST['LeadRouting'])) {
			$model->attributes=$_POST['LeadRouting'];
                        
                        $model->users=Accounts::parseUsers($model->users);
			
			if($model->save()) {
				$this->redirect('roundRobinRules');
			}
		}
		
		$this->render('customRules',array(
			'model'=>$model,
                        'users'=>$users,
                        'dataProvider'=>$dataProvider,
		));
            
        }
        
        public function actionToggleUpdater(){
            
            $admin=AdminChild::model()->findByPk(1);
            $admin->ignoreUpdates?$admin->ignoreUpdates=0:$admin->ignoreUpdates=1;
            $admin->save();
            $this->redirect('index');
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
        
        public function actionViewChangelog(){
            
            $model=new Changelog('search');
            $model->timestamp=null;
            
            $pageParam = ucfirst('Changelog'). '_page';
		if (isset($_GET[$pageParam])) {
			$page = $_GET[$pageParam];
			Yii::app()->user->setState($this->id.'-page',(int)$page);
		} else {
			$page=Yii::app()->user->getState($this->id.'-page',1);
			$_GET[$pageParam] = $page;
		}
		if (intval(Yii::app()->request->getParam('clearFilters'))==1) {
			EButtonColumnWithClearFilters::clearFilters($this,$model);//where $this is the controller
		}
            
            $this->render('viewChangelog',array(
                'model'=>$model,
            ));
        }
        
        public function actionAddCriteria(){
            $criteria=new Criteria;
            $users=UserChild::getNames();
            $dataProvider=new CActiveDataProvider('Criteria');
            unset($users['']);
            if(isset($_POST['Criteria'])){
               $criteria->attributes=$_POST['Criteria'];
               $str="";
               $arr=$criteria->users;
               if($criteria->type=='assignment' && count($arr)>1){
                   $this->redirect('addCriteria');
               }
               if(isset($arr)){
                   foreach($arr as $user){
                       $str.=$user.", ";
                   }
                   $str=substr($str,0,-2);
               }
               $criteria->users=$str;
               if($criteria->modelType!=null && $criteria->comparisonOperator!=null){
                   if($criteria->save()){

                   }
                   $this->redirect('index');
               }
               
            }
            $this->render('addCriteria',array(
                'users'=>$users,
                'model'=>$criteria,
                'dataProvider'=>$dataProvider,
            ));
        }
        
        public function actionDeleteCriteria($id){
            
            $model=Criteria::model()->findByPk($id);
            $model->delete();
            $this->redirect(array('addCriteria'));
        }
        
        public function actionDeleteRouting($id){
            
            $model=LeadRouting::model()->findByPk($id);
            $model->delete();
            $this->redirect(array('roundRobinRules'));
        }
        
        public function actionGetAttributes(){
            if(isset($_POST['Criteria']['modelType']) || isset($_POST['Fields']['modelName'])){
                if(isset($_POST['Criteria']['modelType']))
                    $type=$_POST['Criteria']['modelType'];
                if(isset($_POST['Fields']['modelName']))
                    $type=$_POST['Fields']['modelName'];
                
                $arr=CActiveRecord::model($type)->attributeLabels();
                
                foreach($arr as $key=>$value){
                    echo CHtml::tag('option', array('value'=>$key),CHtml::encode($value),true);
                }
            }else{
                echo CHtml::tag('option', array('value'=>''),CHtml::encode(var_dump($_POST)),true); 
            }
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
        
        public function actionSetLeadRouting() {
		
		$admin=Admin::model()->findByPk(1);
		if(isset($_POST['Admin'])) {
			$routing=$_POST['Admin']['leadDistribution'];
                        $online=$_POST['Admin']['onlineOnly'];
			
			$admin->leadDistribution=$routing;
                        $admin->onlineOnly=$online;
			
			if($admin->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('leadRouting',array(
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
        
        public function actionAddField(){
            $model=new Fields;
            if(isset($_POST['Fields'])){
                $model->attributes=$_POST['Fields'];
                $model->visible=1;
                $model->custom=1;
                $model->modified=1;
                $type=lcfirst($model->modelName);
                $field=lcfirst($model->fieldName);
                if(preg_match("/\s/",$field)){
                    
                }else{
                    if($model->save()){
                        $sql="ALTER TABLE x2_$type ADD COLUMN $field VARCHAR(250)";
                        $command = Yii::app()->db->createCommand($sql);
                        $result = $command->query();
                        
                    }   
                }
                $this->redirect('manageFields');
            }
            
        }
        
        public function actionRemoveField(){
            
            if(isset($_POST['field'])){
                $id=$_POST['field'];
                $field=Fields::model()->findByPk($id);
                $model=lcfirst($field->modelName);
                $fieldName=lcfirst($field->fieldName);
                if($field->delete()){
                    $sql="ALTER TABLE x2_$model DROP COLUMN $fieldName";
                    $command = Yii::app()->db->createCommand($sql);
                    $result = $command->query();
                }
                $this->redirect('manageFields');
            }
        }
        
        public function actionCustomizeFields(){
            
            $model=new Fields;
            if(isset($_POST['Fields'])){
                $type=$_POST['Fields']['modelName'];
                $field=$_POST['Fields']['fieldName'];
                
                $modelField=Fields::model()->findByAttributes(array('modelName'=>$type,'fieldName'=>$field));
                if($_POST['Fields']['attributeLabel']!="")
                    $modelField->attributeLabel=$_POST['Fields']['attributeLabel'];
                $modelField->visible=$_POST['Fields']['visible'];
                $modelField->modified=1;
                
                if($modelField->save())
                    $this->redirect('manageFields');
            }
            
        }
        
        public function actionManageFields(){
            $model=new Fields;
            $dataProvider=new CActiveDataProvider('Fields',array(
                'criteria'=>array(
                    'condition'=>'modified=1'
                )
            ));
            $fields=Fields::model()->findAllByAttributes(array('custom'=>'1'));
            $arr=array();
            foreach($fields as $field){
                $arr[$field->id]=$field->attributeLabel;
            }
            
            $this->render('manageFields',array(
                'dataProvider'=>$dataProvider,
                'model'=>$model,
                'fields'=>$arr,
            ));
        }

	public function actionCreatePage() {

		$model=new DocChild;
		$users=UserChild::getNames();
		if(isset($_POST['DocChild'])) {
			
			$model->attributes=$_POST['DocChild'];
			$arr=$model->editPermissions;
			if(isset($arr))
				$model->editPermissions=Accounts::parseUsers($arr);
			$model->text=$_POST['msgpost'];
			$model->createdBy='admin';
			$model->createDate=time();
			$model->lastUpdated=time();
			$model->updatedBy='admin';
			
			$admin=Admin::model()->findByPk(1);
			if(isset($admin)) {
				if($admin->menuOrder!="") {
					$admin->menuOrder.=":".preg_replace('/:/u','&#58;',$model->title);
					$admin->menuVisibility.=":1";
					$admin->menuNicknames.=":".preg_replace('/:/u','&#58;',$model->title);
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
			$menuItems[$key] = preg_replace('/&#58;/',':',$value);	// decode any colons

		if(isset($_POST['module']) && isset($_POST['name'])) {
			$module=$_POST['module'];
			$name=$_POST['name'];

			$menuItems[$module]=$name;
			
			//$orderStr="";
			//$nickStr="";

			foreach($menuItems as $key=>$value) {
				//$orderStr .= $key.":";
				//$nickStr .= $value.":";
				
				$menuItems[$key] = preg_replace('/:/u','&#58;',$value);	// encode any colons in nicknames
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
				$value = preg_replace('/:/u','&#58;',$value);	// encode any colons
			
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
	
	public function actionTranslationManager() {
		$this->layout = null;
		$messagePath = 'protected/messages';
		include('protected/extensions/TranslationManager.php');
		// die('hello:'.var_dump($_POST));
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
			
			if(in_array(preg_replace('/:/u','&#58;',$title),$menuNickNames)
				|| in_array($moduleName,$menuOrder)
				|| array_key_exists('x2_'.$moduleName,Yii::app()->db->schema->getTables()))
				$errors[] = Yii::t('module','A module with that title already exists');
			if(empty($errors)) {
			
				$assignedTo=$_POST['displayAssignedTo'];
				$description=$_POST['displayDescription'];
				
				$customFields=$_POST['CustomFields'];
                                $visibility=$customFields['visible'];
                                $names=$customFields['fieldName'];
                                $labels=$customFields['attributeLabel'];
                                
                                foreach($names as $field){
                                    if($field=="Enter field name here")
                                        $field="";
                                }
                                $this->writeConfig($title,$moduleName,$recordName);
				$this->createNewTable($moduleName, $visibility, $names, $labels);
				
				$this->createSkeletonDirectories($moduleName);
				
				
				// add new module to the admin menuOrder fields
				if(empty($admin->menuOrder)) {
					$admin->menuOrder = $moduleName;
					$admin->menuVisibility = "1";
					$admin->menuNicknames = $title;
				} else {
					$admin->menuOrder .= ":" . $moduleName;
					$admin->menuVisibility .= ":1";
					$admin->menuNicknames .= ":" . preg_replace('/:/u','&#58;',$title);	// encode any colons so they don't break the admin menuOrder field;
				}
				$admin->save();
				
				$this->redirect(array($moduleName.'/index'));
			}
		}
		
		$this->render('createModule',array('errors'=>$errors));
	}
        
        private function writeConfig($title,$moduleName,$recordName) {

		$configFile = Yii::app()->file->set('protected/config/templatesConfig.php', true);
		$configFile->copy($moduleName.'Config.php');

		$configFile=Yii::app()->file->set('protected/config/'.$moduleName.'Config.php', true);

		$str = "<?php
\$moduleConfig = array(
	'title'=>'".addslashes($title)."',
	'moduleName'=>'".addslashes($moduleName)."',
	'recordName'=>'".addslashes($recordName)."',
);
?>";
                
                $configFile->setContents($str);
}
	
	private function createNewTable($moduleName, $visibility, $names, $labels) {
		$sqlList=array("CREATE TABLE x2_".$moduleName."(
                        id INT NOT NULL AUTO_INCREMENT primary key,
                        assignedTo VARCHAR(40),
                        name VARCHAR(100) NOT NULL,
                        description TEXT,
                        createDate INT,
                        lastUpdated INT,
                        updatedBy VARCHAR(40)
                        )",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'id', 'ID', '1', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'name', 'Name', '1', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'assignedTo', 'Assigned To', '1', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'description', 'Description', '1', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'createDate', 'Create Date', '1', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'lastUpdated', 'Last Updated', '1', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'updatedBy', 'Updated By', '1', '0')");
                foreach($sqlList as $sql){
                    $command = Yii::app()->db->createCommand($sql);
                    $command->execute();
                }
                for($i=0;$i<count($names);$i++){
                    $sql="ALTER TABLE x2_".$moduleName." ADD COLUMN $names[$i] VARCHAR(250)";
                    $command = Yii::app()->db->createCommand($sql);
                    $command->execute();
                    $field=new Fields;
                    $field->modelName=$moduleName;
                    $field->fieldName=$names[$i];
                    $field->visible=$visibility[$i];
                    $field->custom=1;
                    $field->modified=1;
                    $field->attributeLabel=$labels[$i];
                    $field->save();
                }
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
		$standard = array('contacts','actions','docs','accounts','sales','workflow');

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
			$fields=Fields::model()->findAllByAttributes(array('modelName'=>ucfirst($moduleName)));
                        foreach($fields as $field){
                            $field->delete();
                        }
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
                        
                        $fields=Fields::model()->findAllByAttributes(array('modelName'=>ucfirst($moduleName)));
                        $sql="CREATE TABLE x2_".$moduleName."(
                        id INT NOT NULL AUTO_INCREMENT primary key,
                        assignedTo VARCHAR(40),
                        name VARCHAR(100) NOT NULL,
                        description TEXT,
                        createDate INT,
                        lastUpdated INT,
                        updatedBy VARCHAR(40)
                        );INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'id', 'ID', '1', '0');
                        INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'name', 'Name', '1', '0');
                        INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'assignedTo', 'Assigned To', '1', '0');
                        INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'description', 'Description', '1', '0');
                        INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'createDate', 'Create Date', '1', '0');
                        INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'lastUpdated', 'Last Updated', '1', '0');
                        INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', 'updatedBy', 'Updated By', '1', '0');";
                                
                        $disallow=array(
                            "id",
                            "assignedTo",
                            "name",
                            "description",
                            "createDate",
                            "lastUpdated",
                            "updatedBy",
                        );
                        foreach($fields as $field){
                            if(array_search($field->fieldName,$disallow)===false)
                                $sql.="ALTER TABLE x2_$moduleName ADD COLUMN $field->fieldName VARCHAR(250); INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', '$field->fieldName', '$field->attributeLabel', '1', '1');";
                        }
                        
                        $db=Yii::app()->file->set("sqlData.sql");
                        $db->create();
                        $db->setContents($sql);
                        $db->copy($moduleName.'/sqlData.sql');
                        
			$config=Yii::app()->file->set('protected/config/'.$moduleName.'Config.php');
			$config->copy("$moduleName/".$moduleName.'Config.php');
                        
			$model=Yii::app()->file->set('protected/models/'.ucfirst($moduleName).'.php');
			$model->copy("$moduleName/".ucfirst($moduleName).".php");
			
			$controller=Yii::app()->file->set('protected/controllers/'.ucfirst($moduleName).'Controller.php');
			$controller->copy("$moduleName/".ucfirst($moduleName)."Controller.php");
			
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
			
			$zip=Yii::app()->zip;
			$zip->extractZip("$moduleName.zip",'temp');
                        
                        $sql=$model=Yii::app()->file->set('temp/'.$moduleName.'/sqlData.sql');
                        $sqlContents=$sql->getContents();
                        $pieces=explode(";",$sqlContents);
                        foreach($pieces as $query){
                            if($query!=""){
                                $command = Yii::app()->db->createCommand($query);
                                $command->execute();
                            }
                        }
			
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
        
        public function actionEditor(){
            if(isset($_GET['model'])){
                $modelName=$_GET['model'];
                eval("\$model=new ".ucfirst($modelName).";");
                $formUrl="//$modelName/_form";
            }else{
                $formUrl="";
                $model=null;
            }
            
            
            
            $this->render('formEditor',array(
                'formUrl'=>$formUrl,
                'model'=>$model,
            ));
        }
		
	
	public function actionExport() {
		$this->globalExport();
		$this->render('export',array(
		));
	}
	
	private function globalExport() {
		
		$file='data.csv';
		$fp = fopen($file, 'w+');
                
                $admin=AdminChild::model()->findByPk(1);
                $order=$admin->menuOrder; 
		
                $pieces=explode(":",$order);
                $tempArr=array();
                foreach($pieces as $model){
                    $tempArr[ucfirst($model)]=CActiveRecord::model(ucfirst($model))->findAll();
                }
                
		$tempArr['Users']=UserChild::model()->findAll();
                $tempArr['Profile']=ProfileChild::model()->findAll();
                $labels=array();
                foreach($tempArr as $model=>$data){
                    $temp=CActiveRecord::model($model);
                    $tempKeys=array_keys($temp->attributes);
                    $tempKeys[]=$model;
                    $labels[$model]=$tempKeys;
                }
		
		fputcsv($fp,array(Yii::app()->params->version));

                $keys=array_keys($tempArr);
                
                for($i=0;$i<count($tempArr);$i++){
                    $meta=$labels[$keys[$i]];
                    fputcsv($fp,$meta);
                    foreach($tempArr[$keys[$i]] as $data){
                        $tempAtr=$data->attributes;
                        $tempAtr[]=$keys[$i];
                        fputcsv($fp,$tempAtr);
                    }
                    
                }

		fclose($fp);

	}
	
	public function actionImport() {
            if (isset($_FILES['data'])) {
                $overwrite=$_POST['overwrite'];
                $temp = CUploadedFile::getInstanceByName('data');
                $temp->saveAs('data.csv');
                $this->globalImport('data.csv',$overwrite);
            }
            $this->render('import');
        }
		
	private function globalImport($file, $overwrite) {
		$fp=fopen($file,'r+');
		$version=fgetcsv($fp);
		$version=$version[0];
		$type="";
                $meta=array();
		while($pieces=fgetcsv($fp)) {
                    if($pieces[count($pieces)-1]!=$type){
                        $type=$pieces[count($pieces)-1];
                        $meta=$pieces;
                        continue;
                    }
                    eval('$model=new '.$pieces[count($pieces)-1].";");
                    unset($pieces[count($pieces)-1]);
                    $tempMeta=$meta;
                    unset($tempMeta[count($tempMeta)-1]);
                    $temp=array();
                    for($i=0;$i<count($tempMeta);$i++){
                        $temp[$tempMeta[$i]]=$pieces[$i];
                    }
                    foreach($model->attributes as $field=>$value){
                        $model->$field=$temp[$field];
                    }
                    $lookup=CActiveRecord::model(get_class($model))->findByPk($model->id);
                    if(!isset($lookup)){
                        $model->save();
                    }else if($overwrite){
                        $lookup->delete();
                        $model->save();
                    }
                    
			
		}
		unlink($file);
		$this->redirect('index');
	}
		
}