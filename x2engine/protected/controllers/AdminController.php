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
				'actions'=>array('getRoundRobin','updateRoundRobin','getRoutingRules','roundRobin','evenDistro','getRoutingType',
                                                'getRole','getWorkflowStages','download','cleanUp','sql','getFieldData'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('viewPage','getAttributes','getDropdown', 'getFieldType'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('index','howTo','searchContact','sendEmail','mail','search','toggleAccounts',
					'export','import','uploadLogo','toggleDefaultLogo','createModule','deleteModule','exportModule',
					'importModule','toggleSales','setTimeout','emailSetup', 'googleIntegration', 'setChatPoll','renameModules','manageModules',
					'createPage','contactUs','viewChangelog','toggleUpdater','translationManager','addCriteria',
					'deleteCriteria','setLeadRouting','roundRobinRules','deleteRouting','addField','removeField',
					'customizeFields','manageFields', 'editor','createFormLayout','deleteFormLayout','formVersion','dropDownEditor','manageDropDowns','deleteDropdown','editDropdown',
					'roleEditor','deleteRole','editRole','manageRoles','roleException','appSettings','updater','registerModules','toggleModule', 'viewLogs','delete',),
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
		$admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
		$rrId=$admin->rrId;
		return $rrId;
	}
	
	public function updateRoundRobin(){
		$admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
		$admin->rrId=$admin->rrId+1;
		$admin->save();
	}
	
	public function actionGetRoutingType(){
		$admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
		$type=$admin->leadDistribution;
		if($type==""){
			echo "";
		}elseif($type=="evenDistro"){
			echo $this->evenDistro();
		}elseif($type=="trueRoundRobin"){
			echo $this->roundRobin();
		}elseif($type=="customRoundRobin"){
			$arr=$_POST;
                        $users=$this->getRoutingRules($arr);
                        if($users!=""){
                            $rrId=$users[count($users)-1];
                            unset($users[count($users)-1]);
                            $i=$rrId%count($users);
                            echo $users[$i];
                        }
		}
	}
	
	public function roundRobin(){
		$admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
		$online = $admin->onlineOnly;
		x2base::cleanUpSessions();
		$usernames = array();
		$sessions = Session::getOnlineUsers();
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
		$admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
		$online = $admin->onlineOnly;
		x2base::cleanUpSessions();
		$usernames = array();
		$sessions = Session::getOnlineUsers();
		$users = CActiveRecord::model('UserChild')->findAll();
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
	
	public function getRoutingRules($data){
            $admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
            $online = $admin->onlineOnly;
            x2base::cleanUpSessions();
            $sessions = Session::getOnlineUsers();

            $rules=CActiveRecord::model('LeadRouting')->findAll("",array('order'=>'priority'));
            foreach($rules as $rule){
                $arr=LeadRouting::parseCriteria($rule->criteria);
                $flagArr=array();
                foreach($arr as $criteria){
                    if(isset($data[$criteria['field']])){
                        $val=$data[$criteria['field']];
                        $operator=$criteria['comparison'];
                        $target=$criteria['value'];
                        if($operator!='contains'){
                            switch($operator){
                                case '>':
                                    $flag=($val>=$target);
                                    break;
                                case '<':
                                    $flag=($val<=$target);
                                    break;
                                case '=':
                                    $flag=($val==$target);
                                    break;
                                case '!=':
                                    $flag=($val!=$target);
                                    break;
                                default:
                                    $flag=false;
                            }
                        }else{
                            $flag=preg_match("/$target/i",$val)!=0;
                            
                        }
                        
                    }
                    $flagArr[]=$flag;
                }
                if(!in_array(false,$flagArr)){
                    $users=$rule->users;
                    $users=explode(", ",$users);
                            if(is_null($rule->groupType)){
                                if($online==1)
                                    $users=array_intersect($users,$sessions);
                            }else{
                                $groups=$rule->users;
                                $groups=explode(", ",$groups);
                                $users=array();
                                foreach($groups as $group){
                                    if($rule->groupType==0){
                                        $links=GroupToUser::model()->findAllByAttributes(array('groupId'=>$group));
                                        foreach($links as $link){
                                            if(array_search(UserChild::model()->findByPk($link->userId)->username,$users)===false)
                                                $users[]=UserChild::model()->findByPk($link->userId)->username;
                                        }
                                    }else{
                                        $users[]=$group;
                                    }
                                }
                                if($online==1 && $rule->groupType==0)
                                    $users=array_intersect($users,$sessions);
                            }
                            $users[]=$rule->rrId;
                            $rule->rrId++;
                            $rule->save();
                            return $users;
                }
            }
	}
	
	public function actionRoundRobinRules(){
		$model=new LeadRouting;
		$users=UserChild::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
                $priorityArray=array();
                for($i=1;$i<=LeadRouting::model()->count()+1;$i++){
                    $priorityArray[$i]=$i;
                }
		$dataProvider=new CActiveDataProvider('LeadRouting', array(
				'criteria'=>array(
					'order'=>'priority ASC',
				)
			));
		if(isset($_POST['LeadRouting'])) {
                        $values=$_POST['Values'];
                        $criteria=array();
                        for($i=0;$i<count($values['field']);$i++){
                            $tempArr=array($values['field'][$i],$values['comparison'][$i],$values['value'][$i]);
                            $criteria[]=implode(',',$tempArr);
                        }
                        $model->criteria=json_encode($criteria);
			$model->attributes=$_POST['LeadRouting'];
                        $model->priority=$_POST['LeadRouting']['priority'];
                        if(isset($_POST['group'])){
                            $group=true;
                            $model->groupType=$_POST['groupType'];
                        }else{
                            $model->groupType=null;
                        }
			
			$model->users=Accounts::parseUsers($model->users);
                        $check=LeadRouting::model()->findByAttributes(array('priority'=>$model->priority));
                        if(isset($check)){
                            $query="UPDATE x2_lead_routing SET priority=priority+1 WHERE priority>='$model->priority'";
                            $command=Yii::app()->db->createCommand($query);
                            $command->execute();
                        }
			if($model->save()) {
                            $this->redirect('roundRobinRules');
			}
		}
		
		$this->render('customRules',array(
			'model'=>$model,
			'users'=>$users,
			'dataProvider'=>$dataProvider,
                        'priorityArray'=>$priorityArray,
		));
            
        }
        
        public function actionRoleEditor(){
            $model=new Roles;
            
            if(isset($_POST['Roles'])){
                $model->attributes=$_POST['Roles'];
                if(!(isset($_POST['viewPermissions']) && isset($_POST['editPermissions'])))
                    $this->redirect('manageRoles');
                $viewPermissions=$_POST['viewPermissions'];
                $editPermissions=$_POST['editPermissions'];
                if(isset($_POST['Roles']['users']))
                    $users=$model->users;
                else
                    $users=array();
                $model->users="";
                if($model->save()){
                    foreach($users as $user){
                        $role=new RoleToUser;
                        $role->roleId=$model->id;
                        if(!is_numeric($user)){
                            $userRecord=UserChild::model()->findByAttributes(array('username'=>$user));
                            $role->userId=$userRecord->id;
                            $role->type='user';
                        }/* x2temp */else{
                            $role->userId=$user;
                            $role->type='group';
                        }/* end x2temp */
                        $role->save();
                    }
                    $fields=Fields::model()->findAll();
                    $temp=array();
                    foreach($fields as $field){
                        $temp[]=$field->id;
                    }
                    $both=array_intersect($viewPermissions,$editPermissions);
                    $view=array_diff($viewPermissions,$editPermissions);
                    $neither=array_diff($temp,$viewPermissions);
                    foreach($both as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=2;
                        $rolePerm->save();
                    }
                    foreach($view as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=1;
                        $rolePerm->save();
                    }
                    foreach($neither as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=0;
                        $rolePerm->save();
                    }
                    
                }
                $this->redirect('manageRoles');
            }
            
            $this->render('roleEditor',array(
                'model'=>$model,
            ));
        }
        
        public function actionDeleteRole(){
            $roles=Roles::model()->findAll();
            if(isset($_POST['role'])){
                $id=$_POST['role'];
                $role=Roles::model()->findByAttributes(array('name'=>$id));
                $id=$role->id;
                $userRoles=RoleToUser::model()->findAllByAttributes(array('roleId'=>$role->id));
                foreach($userRoles as $userRole){
                    $userRole->delete();
                }
                $permissions=RoleToPermission::model()->findAllByAttributes(array('roleId'=>$role->id));
                foreach($permissions as $permission){
                    $permission->delete();
                }
                $workflowRoles=RoleToWorkflow::model()->findAllByAttributes(array('replacementId'=>$role->id));
                foreach($workflowRoles as $workflow){
                    $workflow->delete();
                }
                $role->delete();
                
                $this->redirect('manageRoles');
            }
            
            $this->render('deleteRole',array(
                'roles'=>$roles,
            ));
        }
        
        public function actionEditRole(){
            $model=new Roles;
            
            if(isset($_POST['Roles'])){
                $id=$_POST['Roles']['name'];
                $model=Roles::model()->findByAttributes(array('name'=>$id));
                $id=$model->id;
                if(!(isset($_POST['viewPermissions']) && isset($_POST['editPermissions'])))
                    $this->redirect('manageRoles');
                $viewPermissions=$_POST['viewPermissions'];
                $editPermissions=$_POST['editPermissions'];
                if(isset($_POST['users']))
                    $users=$_POST['users'];
                else
                    $users=array();
                $model->users="";
                if($model->save()){
                    $userRoles=RoleToUser::model()->findAllByAttributes(array('roleId'=>$model->id));
                    foreach($userRoles as $role){
                        $role->delete();
                    }
                    $permissions=RoleToPermission::model()->findAllByAttributes(array('roleId'=>$model->id));
                    foreach($permissions as $permission){
                        $permission->delete();
                    }
                    foreach($users as $user){
                        $userRecord=UserChild::model()->findByAttributes(array('username'=>$user));
                        $role=new RoleToUser;
                        $role->roleId=$model->id;
                        if(!is_numeric($user)){
                            $userRecord=UserChild::model()->findByAttributes(array('username'=>$user));
                            $role->userId=$userRecord->id;
                            $role->type='user';
                        }/* x2temp */else{
                            $role->userId=$user;
                            $role->type='group';
                        }/* end x2temp */
                        $role->save();
                    }
                    $fields=Fields::model()->findAll();
                    $temp=array();
                    foreach($fields as $field){
                        $temp[]=$field->id;
                    }
                    $both=array_intersect($viewPermissions,$editPermissions);
                    $view=array_diff($viewPermissions,$editPermissions);
                    $neither=array_diff($temp,$viewPermissions);
                    foreach($both as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=2;
                        $rolePerm->save();
                    }
                    foreach($view as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=1;
                        $rolePerm->save();
                    }
                    foreach($neither as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=0;
                        $rolePerm->save();
                    }
                    
                }
                $this->redirect('manageRoles');
            }
            
            $this->render('editRole',array(
                'model'=>$model,
            ));
        }
        
        public function actionRoleException(){
            $model=new Roles;
            $temp=Workflow::model()->findAll();
            $workflows=array();
            foreach($temp as $workflow){
                $workflows[$workflow->id]=$workflow->name;
            }
            if(isset($_POST['Roles'])){
                $workflow=$_POST['workflow'];
                $workflowName=Workflow::model()->findByPk($workflow)->name;
                $stage=$_POST['workflowStages'];
                $stageName=WorkflowStage::model()->findByPk($stage)->name;
                $viewPermissions=$_POST['viewPermissions'];
                $editPermissions=$_POST['editPermissions'];
                $users=$_POST['users'];
                $model->attributes=$_POST['Roles'];
                $oldRole=Roles::model()->findByAttributes(array('name'=>$model->name));
                $model->users="";
                $model->name.=" - $workflowName: $stageName";
                if($model->save()){
                    $replacement=new RoleToWorkflow;
                    $replacement->workflowId=$workflow;
                    $replacement->stageId=$stage;
                    $replacement->roleId=$oldRole->id;
                    $replacement->replacementId=$model->id;
                    $replacement->save();
                    $fields=Fields::model()->findAll();
                    $temp=array();
                    foreach($fields as $field){
                        $temp[]=$field->id;
                    }
                    $both=array_intersect($viewPermissions,$editPermissions);
                    $view=array_diff($viewPermissions,$editPermissions);
                    $neither=array_diff($temp,$viewPermissions);
                    foreach($both as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=2;
                        $rolePerm->save();
                    }
                    foreach($view as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=1;
                        $rolePerm->save();
                    }
                    foreach($neither as $field){
                        $rolePerm=new RoleToPermission;
                        $rolePerm->roleId=$model->id;
                        $rolePerm->fieldId=$field;
                        $rolePerm->permission=0;
                        $rolePerm->save();
                    }
                }
                $this->redirect('manageRoles');
            }
            $this->render('roleException',array(
                'model'=>$model,
                'workflows'=>$workflows,
            ));
        }
        
        public function actionGetWorkflowStages(){
            if(isset($_POST['workflow'])){
                $id=$_POST['workflow'];
                $stages=Workflow::getStages($id);
                foreach($stages as $key=>$value){
                    echo CHtml::tag('option', array('value'=>$key),CHtml::encode($value),true);
                }
            
            }else{
                echo CHtml::tag('option', array('value'=>''),CHtml::encode(var_dump($_POST)),true); 
            }
        }
        
        public function actionGetRole(){
            if(isset($_POST['Roles'])){
                $id=$_POST['Roles']['name'];
                if(is_null($id)){
                    echo ""; 
                    exit;
                }
                $role=Roles::model()->findByAttributes(array('name'=>$id));
                $id=$role->id;
                $roles=RoleToUser::model()->findAllByAttributes(array('roleId'=>$id));
                $users=array();
                foreach($roles as $link){
                    if($link->type=='user')
                        $users[]=UserChild::model()->findByPk($link->userId)->username;
                    /* x2temp */
                    else
                        $users[]=Groups::model()->findByPk($link->userId)->id;
                    /* end x2temp */
                }
                $allUsers=UserChild::model()->findAll();
                $selected=array();
                $unselected=array();
                foreach($users as $user){
                    $selected[]=$user;
                }
                foreach($allUsers as $user){
                    $unselected[$user->username]=$user->firstName." ".$user->lastName;
                }
                /* x2temp */
                $groups=Groups::model()->findAll();
                foreach($groups as $group){
                    $unselected[$group->id]=$group->name;
                }
                /* end x2temp */
                unset($unselected['admin']);
                echo "<div id='users'><label>Users</label>";
                echo CHtml::dropDownList('users[]',$selected,$unselected,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>8));
                echo "</div>";
                $fields=Fields::model()->findAllBySql("SELECT * FROM x2_fields ORDER BY modelName ASC");
                $viewSelected=array();
                $editSelected=array();
                $fieldUnselected=array();
                $fieldPerms=RoleToPermission::model()->findAllByAttributes(array('roleId'=>$role->id));
                foreach($fieldPerms as $perm){
                    if($perm->permission==2){
                        $viewSelected[]=$perm->fieldId;
                        $editSelected[]=$perm->fieldId;
                    }else if($perm->permission==1){
                        $viewSelected[]=$perm->fieldId;
                    }
                }
                foreach($fields as $field){
                    $fieldUnselected[$field->id]=$field->modelName." - ".$field->attributeLabel;
                }
                echo "<br /><label>View Permissions</label>";
                echo CHtml::dropDownList('viewPermissions[]',$viewSelected,$fieldUnselected,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>8));
                echo "<br /><label>Edit Permissions</label>";
                echo CHtml::dropDownList('editPermissions[]',$editSelected,$fieldUnselected,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>8));
            }
        }
        
        public function actionManageRoles(){
            $model=new Roles;
            $dataProvider=new CActiveDataProvider('Roles');
            $roles=$dataProvider->getData();
            $arr=array();
            foreach($roles as $role){
                $arr[$role->name]=$role->name;
            }
            $temp=Workflow::model()->findAll();
            $workflows=array();
            foreach($temp as $workflow){
                $workflows[$workflow->id]=$workflow->name;
            }
            
            $this->render('manageRoles',array(
                'dataProvider'=>$dataProvider,
                'model'=>$model,
                'roles'=>$arr,
                'workflows'=>$workflows,
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
                    $type=ucfirst($_POST['Criteria']['modelType']);
                if(isset($_POST['Fields']['modelName']))
                    $type=ucfirst($_POST['Fields']['modelName']);
                if($type=='Quotes')$type="Quote";
                if($type=="Products")$type="Product";
                
                $arr=CActiveRecord::model($type)->attributeLabels();
                
                foreach($arr as $key=>$value){
                    echo CHtml::tag('option', array('value'=>$key),CHtml::encode($value),true);
                }
            }else{
                echo CHtml::tag('option', array('value'=>''),CHtml::encode(var_dump($_POST)),true); 
            }
        }
	
	// public function actionToggleAccounts() {
		// $admin=Admin::model()->findByPk(1);
		// if($admin->accounts==1)
			// $admin->accounts=0;
		// else
			// $admin->accounts=1;
		
		// $admin->update();
		
		// $this->redirect('index');
	// }
		
	// public function actionToggleSales() {
		// $admin=Admin::model()->findByPk(1);
		// if($admin->sales==1)
			// $admin->sales=0;
		// else
			// $admin->sales=1;
		
		// $admin->update();
		
		// $this->redirect('index');
	// }
	
	public function actionSetTimeout() {
		
		$admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
		if(isset($_POST['Admin'])) {
			$timeout = $_POST['Admin']['timeout'];
			
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
		
		$admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
		if(isset($_POST['Admin'])) {
			$timeout = $_POST['Admin']['chatPollTime'];
			
			$admin->chatPollTime=$timeout;

			if($admin->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('setChatPoll',array(
			'admin'=>$admin,
		));
	}
	
	public function actionAppSettings() {
		
		$admin = &Yii::app()->params->admin;
		if(isset($_POST['Admin'])) {
		
			// if(!isset($_POST['Admin']['ignoreUpdates']))
				// $admin->ignoreUpdates = 1;

			$admin->attributes = $_POST['Admin'];
			$admin->timeout *= 60;	//convert from minutes to seconds
			

			if($admin->save()) {
				$this->redirect('appSettings');
			}
		}
		$admin->timeout = ceil($admin->timeout / 60);
		$this->render('appSettings',array(
			'model'=>$admin,
		));
	}

	public function actionSetLeadRouting() {
		
		$admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
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
	
	public function actionGoogleIntegration() {
		
		$admin = &Yii::app()->params->admin;
		if(isset($_POST['Admin'])) {
			foreach($admin->attributes as $fieldName=>$field) {
				if(isset($_POST['Admin'][$fieldName])) {
					$admin->$fieldName = $_POST['Admin'][$fieldName];
				}
			}

			if($admin->save()) {
				$this->redirect('googleIntegration');
			}
		}
		$this->render('googleIntegration',array(
			'model'=>$admin,
		)); 
	}

	public function actionEmailSetup() {
		
		$admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
		if(isset($_POST['Admin'])) {
			$admin->attributes = $_POST['Admin'];
			
			// $admin->chatPollTime=$timeout;

			// $admin->save();
			if($admin->save()) {
				$this->redirect('emailSetup');
			}
		}
		
		$this->render('emailSetup',array(
			'model'=>$admin,
		));
	}

	public function actionAddField(){
		$model=new Fields;
		if(isset($_POST['Fields'])){
			$model->attributes=$_POST['Fields'];
                        (isset($_POST['Fields']['required']) && $_POST['Fields']['required']==1)?$model->required=1:$model->required=0;
			$model->type=$_POST['Fields']['type'];
			// $model->visible=1;
			$model->custom=1;
			$model->modified=1;
                        $model->fieldName=strtolower($model->fieldName);
			
			$fieldType=$model->type;
                        switch($fieldType){
                            case "boolean":
                                $fieldType="BOOLEAN";
                                break;
                            case "float":
                                $fieldType="FLOAT";
                                break;
                            case "int":
                                $fieldType="BIGINT";
                                break;
                            case "text":
                                $fieldType="TEXT";
                                break;
                            default:
                                $fieldType='VARCHAR(250)';
                                break;
                        }
			
			if($model->type=='dropdown'){
                            if(isset($_POST['dropdown'])){
                                $id=$_POST['dropdown'];
                                $model->linkType=$id;
                            }
			}
                        if($model->type=="link"){
                            if(isset($_POST['dropdown'])){
                                $linkType=$_POST['dropdown'];
                                $model->linkType=$linkType;
                            }
                        }
			$type=strtolower($model->modelName);
			$field=strtolower($model->fieldName);
			if(preg_match("/\s/",$field)){
				
			}else{
                            if($model->save()){
                                    $sql="ALTER TABLE x2_$type ADD COLUMN $field $fieldType";
                                    $command = Yii::app()->db->createCommand($sql);
                                    $result = $command->query();

                            }   
			}
			$this->redirect('manageFields');
		}
		
	}

	
	public function actionRemoveField(){
		
		if(isset($_POST['field']) && $_POST['field']!=""){
			$id = $_POST['field'];
			$field = Fields::model()->findByPk($id);
			$model = strtolower($field->modelName);
			$fieldName = strtolower($field->fieldName);
			if($field->delete()){
				$sql="ALTER TABLE x2_$model DROP COLUMN $fieldName";
				$command = Yii::app()->db->createCommand($sql);
				$result = $command->query();
			}
			
		}
                $this->redirect('manageFields');
	}
	
	public function actionCustomizeFields(){
		
		$model=new Fields;
		if(isset($_POST['Fields'])){
			$type=$_POST['Fields']['modelName'];
			$field=$_POST['Fields']['fieldName'];
			
			$modelField=Fields::model()->findByAttributes(array('modelName'=>$type,'fieldName'=>$field));
                        $modelField->attributes=$_POST['Fields'];
                        $modelField->type=$_POST['Fields']['type'];
			if($modelField->type=='dropdown'){
                            if(isset($_POST['dropdown'])){
                                $id=$_POST['dropdown'];
                                $modelField->linkType=$id;
                            }
			}
                        if($modelField->type=="link"){
                            if(isset($_POST['dropdown'])){
                                $linkType=$_POST['dropdown'];
                                $modelField->linkType=$linkType;
                            }
                        }
			$modelField->modified=1;
                        (isset($_POST['Fields']['required']) && $_POST['Fields']['required']==1)?$modelField->required=1:$modelField->required=0;
			
			if($modelField->save())
				$this->redirect('manageFields');
		}
		
	}
        
        public function actionGetFieldData(){
            if(isset($_POST['Fields']['fieldName'])){
                $field=$_POST['Fields']['fieldName'];
                $modelField=Fields::model()->findByAttributes(array('fieldName'=>$field));
                $temparr=$modelField->attributes;
                if(!empty($modelField->linkType)){
                    $type=$modelField->type;
                    if($type=='link'){
                        $arr=array();
                        $modules=Modules::model()->findAll();
                        foreach($modules as $module){
                            if($module->searchable){
                                $arr[$module->name]=$module->title;
                            }
                        }
                        $temparr['dropdown']=CHtml::dropDownList('dropdown',$modelField->linkType,$arr);
                        
                    }elseif($type=='dropdown'){
                        $dropdowns=Dropdowns::model()->findAll();
                        $arr=array();
                        foreach($dropdowns as $dropdown){
                                $arr[$dropdown->id]=$dropdown->name;
                        }

                        $temparr['dropdown']=CHtml::dropDownList('dropdown','',$arr);
                    }
                }else{
                        $temparr['dropdown']="";
                }
                echo CJSON::encode($temparr);
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
			$model->createdBy='admin';
			$model->createDate=time();
			$model->lastUpdated=time();
			$model->updatedBy='admin';
			
			$module=new Modules;
                        $module->adminOnly=0;
                        $module->toggleable=1;
                        $module->custom=1;
                        $module->visible=1;
                        $module->editable=0;
                        $module->searchable=0;
                        $module->menuPosition=Modules::model()->count();
                        $module->name='document';
                        $module->title=$model->title;
                        
                        if($module->save()){
			
                            if($model->save()) {
                                    $this->redirect(array('viewPage','id'=>$model->id));
                            }
                        }
		}
		
		$this->render('createPage',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
	
	public function actionViewPage($id) {
		$model = DocChild::model()->findByPk($id);
		if(!isset($model))
			$this->redirect(array('docs/index'));
		
		$this->render('viewTemplate',array(
			'model'=>$model,
		));
	}
	
	public function actionRenameModules() {
		
                $order=Modules::model()->findAllByAttributes(array('visible'=>1));
                $menuItems=array();
                foreach($order as $module){
                    $menuItems[$module->name]=$module->title;
                }
		foreach($menuItems as $key => $value)
			$menuItems[$key] = preg_replace('/&#58;/',':',$value);	// decode any colons

		if(isset($_POST['module']) && isset($_POST['name'])) {
			$module=$_POST['module'];
			$name=$_POST['name'];

			$moduleRecord=Modules::model()->findByAttributes(array('name'=>$module,'title'=>$menuItems[$module]));
                        $moduleRecord->title=$name;
			
			if($moduleRecord->save()) {
				$this->redirect('index');
			}
		}
		
		$this->render('renameModules',array(
			'modules'=>$menuItems,
		));
	}

	public function actionManageModules() {

		// get admin model
                
                $modules=Modules::model()->findAll(array('order'=>'menuPosition ASC'));
		
		$menuItems = array();		// assoc. array with correct order, containing realName => nickName
		$selectedItems = array();
                
                foreach($modules as $module){
                    $menuItems[$module->name]=$module->title;
                    if($module->visible){
                        $selectedItems[]=$module->name;
                    }
                }


		if(isset($_POST['formSubmit'])) {
			$selectedItems = isset($_POST['menuItems'])? $_POST['menuItems'] : array();
			$newMenuItems = array();
			
			
			// build $newMenuItems array
			foreach($selectedItems as $item) {
				$newMenuItems[] = $menuItems[$item];	// copy each selected item into $newMenuItems
				unset($menuItems[$item]);					// and remove them from $menuItems
			}
			
			foreach($newMenuItems as $item){
                            $moduleRecord=Modules::model()->findByAttributes(array('name'=>$item));
                            $moduleRecord->visible=1;
                            $moduleRecord->menuPosition=array_search($item,$newMenuItems);
                            if($moduleRecord->save()){
                                
                            }
                        }
			foreach($menuItems as $item){
                            $moduleRecord=Modules::model()->findByAttributes(array('name'=>$item));
                            $moduleRecord->visible=0;
                            $moduleRecord->menuPosition=-1;
                            if($moduleRecord->save()){
                                
                            }
                        }
			
			$this->redirect('manageModules');
                        
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
				'Š'=>'S', 'š'=>'s', '�?'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', '�?'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 
				'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', '�?'=>'I', 'Î'=>'I', 
				'�?'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 
				'Û'=>'U', 'Ü'=>'U', '�?'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 
				'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 
				'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 
				'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
			);

			$moduleName = strtolower(strtr($moduleName,$trans));		// replace characters with their A-Z equivalent, if possible
			
			$moduleName = preg_replace('/\W/','',$moduleName);	// now remove all remaining non-alphanumeric or _ chars
			
			$moduleName = preg_replace('/^[0-9_]+/','',$moduleName);	// remove any numbers or _ from the beginning
			
			
			
			if($moduleName == '')								// if there is nothing left of moduleName at this point,
				$moduleName = 'module' . substr(time(),5);		// just generate a random one

			
			if(!is_null(Modules::model()->findByAttributes(array('title'=>$title))) || !is_null(Modules::model()->findByAttributes(array('name'=>$moduleName))))
				$errors[] = Yii::t('module','A module with that title already exists');
			if(empty($errors)) {
                            
                                $this->writeConfig($title,$moduleName,$recordName);
				$this->createNewTable($moduleName);
				
				$this->createSkeletonDirectories($moduleName);
				
				$moduleRecord=new Modules;
                                $moduleRecord->name=$moduleName;
                                $moduleRecord->title=$title;
                                $moduleRecord->custom=1;
                                $moduleRecord->visible=1;
                                $moduleRecord->editable=$_POST['editable'];
                                $moduleRecord->adminOnly=$_POST['adminOnly'];
                                $moduleRecord->searchable=$_POST['searchable'];
                                $moduleRecord->toggleable=1;
                                $moduleRecord->menuPosition=Modules::model()->count();
                                $moduleRecord->save();
				
				$this->redirect(array('/'.$moduleName.'/default/index'));
			}
		}
		
		$this->render('createModule',array('errors'=>$errors));
	}
        
        private function writeConfig($title,$moduleName,$recordName) {
            
                $templateFolder=Yii::app()->file->set('protected/modules/template/');
                $templateFolder->copy($moduleName);
                
                $module=Yii::app()->file->set('protected/modules/template/TemplatesModule.php');
                $module->copy('protected/modules/'.$moduleName.'/TemplatesModule.php');
               

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
	
	private function createNewTable($moduleName) {
                $moduleTitle=ucfirst($moduleName);
		$sqlList=array("CREATE TABLE x2_".$moduleName."(
                        id INT NOT NULL AUTO_INCREMENT primary key,
                        assignedTo VARCHAR(250),
                        name VARCHAR(250) NOT NULL,
                        description TEXT,
                        createDate INT,
                        lastUpdated INT,
                        updatedBy VARCHAR(250)
                        )",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom) VALUES ('$moduleTitle', 'id', 'ID', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'name', 'Name', '0', 'varchar')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'assignedTo', 'Assigned To', '0', 'assignment')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'description', 'Description', '0', 'text')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'createDate', 'Create Date', '0', 'date')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'lastUpdated', 'Last Updated', '0', 'date')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'updatedBy', 'Updated By', '0', 'assignment')");
                foreach($sqlList as $sql){
                    $command = Yii::app()->db->createCommand($sql);
                    $command->execute();
                }
	}
	
	private function createSkeletonDirectories($moduleName) {
		
		$errors = array();
		

			$fileNames = array(
				'protected/modules/'.$moduleName.'/views/default/_detailView.php',
				'protected/modules/'.$moduleName.'/views/default/_form.php',
				'protected/modules/'.$moduleName.'/views/default/_search.php',
				'protected/modules/'.$moduleName.'/views/default/_view.php',
				'protected/modules/'.$moduleName.'/views/default/admin.php',
				'protected/modules/'.$moduleName.'/views/default/create.php',
				'protected/modules/'.$moduleName.'/views/default/index.php',
				'protected/modules/'.$moduleName.'/views/default/update.php',
				'protected/modules/'.$moduleName.'/views/default/view.php',
				'protected/modules/'.$moduleName.'/models/Templates.php',
				'protected/modules/'.$moduleName.'/controllers/DefaultController.php',
                                'protected/modules/'.$moduleName.'/register.php',
                                'protected/modules/'.$moduleName.'/TemplatesModule.php',
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
					
					$newFileName = preg_replace('/templates/',$moduleName,$templateFile->filename);
                                        $newFileName = preg_replace('/template/',$moduleName,$templateFile->filename);// replace 'template' with
					$newFileName = preg_replace('/Templates/',ucfirst($moduleName),$newFileName);	// module name for new files
                                        $templateFile->setFileName($newFileName);
                                        $templateFile->setContents($contents);
				}
			}

	}
	
	public function actionDeleteModule() {
	
		
		if(isset($_POST['name'])) {
			$moduleName = $_POST['name'];
			$module=Modules::model()->findByAttributes(array('name'=>$moduleName));
                        if(isset($module)){
                            if($module->name!='document' && $module->delete()) {
                                    $config=include('protected/modules/'.$moduleName.'/register.php');
                                    $uninstall=$config['uninstall'];
                                    foreach($uninstall as $sql){
                                        $query=Yii::app()->db->createCommand($sql);
                                        $query->execute();
                                    }
                                    $fields=Fields::model()->findAllByAttributes(array('modelName'=>$moduleName));
                                    foreach($fields as $field){
                                        $field->delete();
                                    }

                                    $this->rrmdir('protected/modules/'.$moduleName);
                            }else{
                                $module->delete();
                            }
                        }
			$this->redirect(array('admin/index'));
		}
		
		$arr = array();
		$modules=Modules::model()->findAllByAttributes(array('toggleable'=>1));
                foreach($modules as $item){
                    $arr[$item->name]=$item->title;
                }
		
		$this->render('deleteModule',array(
			'modules'=>$arr,
		));
	}
	
	
	public function actionExportModule() {
		
		if(isset($_POST['name'])) {
			$moduleName=strtolower($_POST['name']);

			$fields=Fields::model()->findAllByAttributes(array('modelName'=>ucfirst($moduleName)));
			$sql="";
					
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
				if(array_search($field->fieldName,$disallow)===false){
                                    $fieldType=$field->type;
                                    switch($fieldType){
                                        case "boolean":
                                        $fieldType="BOOLEAN";
                                        break;
                                    case "float":
                                        $fieldType="FLOAT";
                                        break;
                                    case "int":
                                        $fieldType="BIGINT";
                                        break;
                                    case "text":
                                        $fieldType="TEXT";
                                        break;
                                    default:
                                        $fieldType='VARCHAR(250)'; 
                                        break;
                                    }
					$sql.="ALTER TABLE x2_$moduleName ADD COLUMN $field->fieldName $fieldType; INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', '$field->fieldName', '$field->attributeLabel', '1', '1');";
                                }
                        }
			
			$db=Yii::app()->file->set("protected/modules/$moduleName/sqlData.sql");
                        $db->create();
			$db->setContents($sql);
                        
			if(file_exists($moduleName.".zip")) {
				unlink($moduleName.".zip");
			}
                        
			$zip=Yii::app()->zip;
			$zip->makeZip('protected/modules/'.$moduleName,$moduleName.".zip");
			$finalFile=Yii::app()->file->set($moduleName.".zip");
			$finalFile->download();
                        $this->redirect('exportModule');
                        
		}
		
		$arr=array();
		
		$modules=Modules::model()->findAll();
                foreach($modules as $module){
                    if($module->custom){
                        $arr[$module->name]=$module->title;
                    }
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
			$zip->extractZip("$moduleName.zip",'protected/modules/');
			
			$admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
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
			
			
			$this->redirect(array($moduleName.'/index'));
		}
		$this->render('importModule');
	}
        
        public function actionRegisterModules(){
            
            $modules=scandir('protected/modules');
            $modules=array_combine($modules,$modules);
            $arr=array();
            foreach($modules as $module){
                if(file_exists("protected/modules/$module/register.php") && is_null(Modules::model()->findByAttributes(array('name'=>$module)))){
                    $arr[]=($module);
                }
            }
            $registeredModules=Modules::model()->findAll();
            
            $this->render('registerModules',array(
                'modules'=>$arr,
                'registeredModules'=>$registeredModules,
            ));
        }
        
        public function actionToggleModule($module){

            $config=include("protected/modules/$module/register.php");  
            $exists=Modules::model()->findByAttributes(array('name'=>$module));
            if(!isset($exists)){
                $moduleRecord=new Modules;
                $moduleRecord->editable=$config['editable']?1:0;
                $moduleRecord->searchable=$config['searchable']?1:0;
                $moduleRecord->adminOnly=$config['adminOnly']?1:0;
                $moduleRecord->custom=$config['custom']?1:0;
                $moduleRecord->toggleable=$config['toggleable']?1:0;
                $moduleRecord->name=$module;
                $moduleRecord->title=$config['name'];
                $moduleRecord->visible=1;
                $moduleRecord->menuPosition=Modules::model()->count();

                if($moduleRecord->save()){
                    $install=$config['install'];
                }
            }else{
                $exists->visible=$exists->visible?0:1;
                
                if($exists->save()){
                    if($exists->toggleable){
                        $uninstall=$config['uninstall'];
                    }else{
                        
                    }
                }
            }
            $this->redirect('registerModules');
        }
	
	public function actionEditor() {

		$layoutModel = null;
		$defaultView = false;
		$defaultForm = false;
		
		if(isset($_GET['id']) && !empty($_GET['id'])) {
		
			$id = $_GET['id'];
			$layoutModel = FormLayout::model()->findByPk($id);

			if(!isset($layoutModel))
				$this->redirect(array('editor'));

			$modelName = $layoutModel->model;

			if(isset($_POST['layout'])) {
				$layoutModel->layout = urldecode($_POST['layout']);
				$layoutModel->defaultView = isset($_POST['defaultView']) && $_POST['defaultView'] == 1;
				$layoutModel->defaultForm = isset($_POST['defaultForm']) && $_POST['defaultForm'] == 1;
				

				// if this is the default view, unset defaultView for all other forms
				if($layoutModel->defaultView) {
					$layouts = FormLayout::model()->findAllByAttributes(array('model'=>$modelName,'defaultView'=>1));
					foreach($layouts as &$layout) {
						$layout->defaultView = false;
						$layout->save();
					}
					unset($layout);
				}
				// if this is the default form, unset defaultForm for all other forms
				if($layoutModel->defaultForm) {
					$layouts = FormLayout::model()->findAllByAttributes(array('model'=>$modelName,'defaultForm'=>1));
					foreach($layouts as &$layout) {
						$layout->defaultForm = false;
						$layout->save();
					}
					unset($layout);
				}

				$layoutModel->save();
				$this->redirect(array('editor','id'=>$id));
			}
			
		} else {
			$modelName = isset($_GET['model'])? $_GET['model'] : '';
			$id = '';
		}
		
		$modules=Modules::model()->findAllByAttributes(array('editable'=>1));

		$modelList = array(''=>'---');
			foreach($modules as $module) {
                            $modelList[ucfirst($module->name)] = $module->title;
			}
				
		$versionList = array(''=>'---');
		if(!empty($modelName)) {
			$layouts = FormLayout::model()->findAllByAttributes(array('model'=>$modelName));
			
			foreach($layouts as &$layout)
				$versionList[$layout->id] = $layout->version . (($layout->defaultView || $layout->defaultForm)? ' ('.Yii::t('admin','Default').')' : '');
			unset($layout);
		}

		$this->render('editor',array(
			'modelName'=>$modelName,
			'id'=>$id,
			'layoutModel'=>$layoutModel,
			'modelList'=>$modelList,
			'versionList'=>$versionList,
			'defaultView'=>isset($layoutModel->defaultView)? $layoutModel->defaultView : false,
			'defaultForm'=>isset($layoutModel->defaultForm)? $layoutModel->defaultForm : false,
		));
	}
	
	// returns either a full form layout, or a list of layout versions, for AJAX requests
	// public function actionFormVersion() {
	
		// if(isset($_GET['id'])) {
			// $layout = FormLayout::findByAttributes(array('id'=>$_GET['id']));
			// if(isset($layout))
				// echo $layout->layout;
			// else
				// throw new CHttpException(404,'Requested form layout not found');
		// } elseif(isset($_GET['model'])) {
			// $layouts = FormLayout::findAllByAttributes(array('model'=>$model));
			// if(isset($layouts) && count($layouts) > 0) {
				// $layoutList = array();
				// foreach($layouts as &$layout)
					// $layoutList[$layout->id] = $layout->version;
				
				// unset($layout);
				
				
				
				// echo CHtml::dropDownList('layoutVersions','',$layoutList); 
				
			// } else
				// throw new CHttpException(404,'Requested model not recognized');
		// } else
			// throw new CHttpException(400,'Invalid request');
	// }
	
	public function actionCreateFormLayout(){
		if(isset($_GET['newLayout'],$_GET['model'],$_GET['layoutName'])) {
			// $currentLayouts = FormLayout::model()->findAllByAttributes(array('model'=>$_GET['model']));
		
			$newLayout = new FormLayout;
			
			if(isset($_POST['layout']))
				$newLayout->layout = urldecode($_POST['layout']);
			
			$newLayout->version = $_GET['layoutName'];
			$newLayout->model = $_GET['model'];
			$newLayout->createDate = time();
			$newLayout->lastUpdated = time();
			$newLayout->defaultView = false;
			$newLayout->defaultForm = false;
			$newLayout->save();
			$this->redirect(array('editor','id'=>$newLayout->id));
		}
	}
	
	public function actionDeleteFormLayout($id){
	
		$layout = FormLayout::model()->findByPk($id);
		if(isset($layout)) {
			$modelName = $layout->model;
			$defaultView = $layout->defaultView;
			$defaultForm = $layout->defaultForm;
			$layout->delete();
			
			// if we just deleted the default, find the next layout and make it the default
			if($defaultView) {
				$newDefaultView = FormLayout::model()->findByAttributes(array('model'=>$modelName));
				if(isset($newDefaultView)) {
					$newDefaultView->defaultView = true;
					$newDefaultView->save();
				}
			}
			if($defaultForm) {
				$newDefaultForm = FormLayout::model()->findByAttributes(array('model'=>$modelName));
				if(isset($newDefaultForm)) {
					$newDefaultForm->defaultForm = true;
					$newDefaultForm->save();
				}
			}
			$this->redirect(array('editor','model'=>$modelName));
		} else
			$this->redirect('editor');
	}
	
	public function actionManageDropDowns(){
		
		$dataProvider=new CActiveDataProvider('Dropdowns');
		$model=new Dropdowns;
		
		$dropdowns=$dataProvider->getData();
		foreach($dropdowns as $dropdown){
			$temp=json_decode($dropdown->options);
			$str="";
			foreach($temp as $item){
				$str.=$item.", ";
			}
			$str=substr($str,0,-2);
			$dropdown->options=$str;
		}
		$dataProvider->setData($dropdowns);
		
		$this->render('manageDropDowns',array(
			'dataProvider'=>$dataProvider,
			'model'=>$model,
			'dropdowns'=>$dataProvider->getData(),
		));
	}
	
	public function actionDropDownEditor(){
		$model=new Dropdowns;
		
		if(isset($_POST['Dropdowns'])){
			$model->attributes=$_POST['Dropdowns'];
			$temp=array();
			foreach($model->options as $option){
                            if($option!="")
				$temp[$option]=$option;
			}
                        if(count($temp)>0){
                            $model->options=json_encode($temp);
                            if($model->save()){
                                    $this->redirect('manageDropDowns');
                            }
                        }else{
                            $this->redirect('manageDropDowns');
                        }
		}
		
		$this->render('dropDownEditor',array(
			'model'=>$model,
		));
	}
	
	public function actionDeleteDropdown(){
		$dropdowns=Dropdowns::model()->findAll();
		
		if(isset($_POST['dropdown'])){
			$model=Dropdowns::model()->findByPk($_POST['dropdown']);
			
			$model->delete();
			$this->redirect('manageDropDowns');
		}
		
		$this->render('deleteDropdowns',array(
			'dropdowns'=>$dropdowns,
		));
	}
	
	public function actionEditDropdown(){
		$model=new Dropdowns;
		
		if(isset($_POST['Dropdowns'])){
			$model=Dropdowns::model()->findByAttributes(array('name'=>$_POST['Dropdowns']['name']));
			$model->attributes=$_POST['Dropdowns'];
			$temp=array();
			foreach($model->options as $option){
                            if($option!="")
				$temp[$option]=$option;
			}
			$model->options=json_encode($temp);
			if($model->save()){
				$this->redirect('manageDropDowns');
			}
		}
		$this->render('editDropdowns');
	}
	
	public function actionGetDropdown(){
		if(isset($_POST['Dropdowns']['name'])){
			$name=$_POST['Dropdowns']['name'];
			$model=Dropdowns::model()->findByAttributes(array('name'=>$name));
			$str="";
			
			$options=json_decode($model->options);
			foreach($options as $option){
				$str.="<li>
						<input type=\"text\" size=\"30\"  name=\"Dropdowns[options][]\" value='$option' />
						<div class=\"\">
							<a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\">[".Yii::t('workflow','Up')."]</a>
							<a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\">[".Yii::t('workflow','Down')."]</a>
							<a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[".Yii::t('workflow','Del')."]</a>
						</div>
						<br />
					</li>";
			}
			echo $str;
		}
	}
	
	public function actionGetFieldType(){
		if(isset($_POST['Fields']['type'])){
			$type=$_POST['Fields']['type'];
			if($type=="dropdown"){
				$dropdowns=Dropdowns::model()->findAll();
				$arr=array();
				foreach($dropdowns as $dropdown){
					$arr[$dropdown->id]=$dropdown->name;
				}
				
				echo CHtml::dropDownList('dropdown','',$arr);
			}elseif($type=='link'){
                            $arr=array();
                            $modules=Modules::model()->findAll();
                            foreach($modules as $module){
                                if($module->searchable){
                                    $arr[$module->name]=$module->title;
                                }
                            }
                            echo CHtml::dropDownList('dropdown','',$arr);
                        }
		}
	}
	
	
	public function actionExport() {
		$this->globalExport();
		$this->render('export',array(
		));
	}
	
	private function globalExport() {
		
		$file='data.csv';
		$fp = fopen($file, 'w+');
                
                $modules=Modules::model()->findAll();
                $pieces=array();
                foreach($modules as $module){
                    $pieces[]=$module->name;
                }
		
                $tempArr=array();
                foreach($pieces as $model){
                    if($model=="quotes")
                        $model="quote";
                    if($model=="products")
                        $model="product";
                    if($model!='dashboard' && $model!='calendar' && is_null(Docs::model()->findByAttributes(array('title'=>$model))))
                        $tempArr[ucfirst($model)]=CActiveRecord::model(ucfirst($model))->findAll();
                }
                
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
		$fp = fopen($file,'r+');
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
                        if(isset($temp[$field]))
                            $model->$field=$temp[$field];
                    }
                    $lookup = CActiveRecord::model(get_class($model))->findByPk($model->id);
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
        
        public function actionUpdater(){
            include('protected/config/emailConfig.php');

            $context = stream_context_create(array(
                'http' => array(
                    'timeout' => 15		// Timeout in seconds
                )
            ));
            if(!isset($updaterVersion)){
                $updaterVersion="";
            }else{
            }
            if($versionTest = @file_get_contents('http://x2planet.com/updates/versionCheck.php',0,$context)){
                $url='x2planet';
            }
            else if($versionTest = @file_get_contents('http://x2base.com/updates/versionCheck.php',0,$context)){
                $url='x2base';
            }
            
            $updaterCheck=file_get_contents("http://www.$url.com/updates/updateCheck.php");
            
            if($updaterCheck!=$updaterVersion){
                $file="protected/controllers/AdminController.php";
                $this->ccopy("http://$url.com/updates/x2engine/".$file , $file);
                $file="protected/views/admin/updater.php";
                $this->ccopy("http://$url.com/updates/x2engine/".$file , $file);
                $config="<?php
\$host='$host';
\$user='$user';
\$pass='$pass';
\$dbname='$dbname';
\$version='$version';
\$updaterVersion='$updaterCheck';
?>";
                file_put_contents('protected/config/emailConfig.php', $config);
                $this->redirect('updater');
            }
            
            $contents=file_get_contents("http://www.$url.com/updates/update.php?version=$version");
            $pieces=explode(";",$contents);
            $newVersion=$pieces[3];
            $sqlList=$pieces[1];
            $changelog=$pieces[4];
            $deletionList=explode(':',$pieces[2]);
            if($pieces[0]!="")
                $fileList=explode(":",$pieces[0]);
            else
                $fileList=array('');
            if($sqlList!="")
                $sqlList=explode(":&",$sqlList);
            else
                $sqlList=array('');
            $this->saveBackup($fileList);
            
            $this->render('updater',array(
                'fileList'=>$fileList,
                'sqlList'=>$sqlList,
                'newVersion'=>$newVersion,
                'changelog'=>$changelog,
                'updaterVersion'=>$updaterVersion,
                'updaterCheck'=>$updaterCheck,
                'version'=>$version,
                'versionTest'=>$versionTest,
                'deletionList'=>$deletionList,
                'url'=>$url,
            ));
        }
        
        public function actionDownload(){
            if(Yii::app()->request->isAjaxRequest){
                $url=$_GET['url'];
                $file=$_GET['file'];
                if($url=='x2planet' || $url=='x2base'){
                    $i=0;
                    while(!$this->ccopy("http://$url.com/updates/x2engine/".$file , $file) && $i<5){
                       $i++; 
                    }
                    if($i==5){ 
                        $this->_sendResponse('500','Error copying file');
                    }else{
                        $this->_sendResponse('200','File copied successfully');
                    }
                }else{
                    $this->_sendResponse('501','Update server not implemented for URL provided.');
                }
            }else{
                $this->_sendResponse('400','Update requests must be made via AJAX.');
            }
        }
        
        public function actionDelete(){
            if(isset($_POST['delete'])){
                $file=$_POST['delete'];
                if(file_exists($file)){
                    if(unlink($file)) 
                        $this->_sendResponse('200','File deleted successfully');
                    else
                        $this->_sendResponse('500','File deletion failed');
                }
            }
        }
        
        public function actionSql(){
            if(isset($_POST['sql'])){
                $sql=$_POST['sql'];
                $command = Yii::app()->db->createCommand($sql);
                $result=$command->execute();
                $this->_sendResponse('200','SQL Executed Successfully');
            }
        }
        
        public function actionCleanUp(){
            include('protected/config/emailConfig.php');
            if(isset($_POST['status'])){
                $status=$_POST['status'];
                if($status=='error'){
                    $this->restoreBackup(json_decode($_POST['fileList'],true));
                    echo "Update failed.  Please try again or contact X2Engine.";
                }else{
                    $url=$_POST['url'];
                    $updaterCheck=file_get_contents("http://www.$url.com/updates/updateCheck.php");
                    $newVersion=$_POST['version'];
                    $config="<?php
\$host='$host';
\$user='$user';
\$pass='$pass';
\$dbname='$dbname';
\$version='$newVersion';
\$updaterVersion='$updaterCheck';
?>";
file_put_contents('protected/config/emailConfig.php', $config);
                    echo "Update succeeded!";
                }
            }
            if(is_dir('backup'))
                $this->rrmdir('backup');
            $assets=scandir('assets');
            foreach($assets as $folder){
                if($folder!=".." && $folder!=".")
                    $this->rrmdir($folder);
            }
        }
        
        function ccopy($filepath, $file){
    
            $pieces=explode('/',$file);
            unset($pieces[count($pieces)]);
            for($i=0;$i<count($pieces);$i++){
                $str="";
                for($j=0;$j<$i;$j++){
                    $str.=$pieces[$j].'/';
                }

                if(!is_dir($str) && $str!=""){
                    mkdir($str);
                }
            }
            return copy($filepath, $file);
        }
        function rrmdir($dir) { 
           if (is_dir($dir)) { 
             $objects = scandir($dir); 
             foreach ($objects as $object) { 
               if ($object != "." && $object != "..") { 
                 if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object); 
               } 
             } 
             reset($objects); 
             rmdir($dir); 
           } 
        }
        function saveBackup($fileList){
            if(!is_dir('backup'))
                mkdir('backup');
            foreach($fileList as $file){
                if($file!="" && file_exists($file)){
                    $this->ccopy($file,'backup/'.$file);
                }
            }
        }
        function restoreBackup($fileList){
            foreach($fileList as $file){
                if($file!="" && file_exists($file)){
                    copy('backup/'.$file,$file);
                }
            }  
        }

        private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
        {
            // set the status
            $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
            header($status_header);
            // and the content type
            header('Content-type: ' . $content_type);

            // pages with body are easy
            if($body != '')
            {
                // send the body
                echo $body;
                exit;
            }
            // we need to create the body if none is passed
            else
            {
                // create some body messages
                $message = '';

                // this is purely optional, but makes the pages a little nicer to read
                // for your users.  Since you won't likely send a lot of different status codes,
                // this also shouldn't be too ponderous to maintain
                switch($status)
                {
                    case 401:
                        $message = 'You must be authorized to view this page.';
                        break;
                    case 404:
                        $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                        break;
                    case 500:
                        $message = 'The server encountered an error processing your request.';
                        break;
                    case 501:
                        $message = 'The requested method is not implemented.';
                        break;
                }

                // servers don't always have a signature turned on 
                // (this is an apache directive "ServerSignature On")
                $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

                // this should be templated in a real-world solution
                $body = '
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
        </head>
        <body>
            <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
            <p>' . $message . '</p>
            <hr />
            <address>' . $signature . '</address>
        </body>
        </html>';

                echo $body;
                exit;
            }
        }
        private function _getStatusCodeMessage($status)
        {
            // these could be stored in a .ini file and loaded
            // via parse_ini_file()... however, this will suffice
            // for an example
            $codes = Array(
                200 => 'OK',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
        }
		
	public function actionViewLogs() {
	
		$this->render('viewLogs');
	
	
	}
}