<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * Administrative, app-wide configuration actions.
 * 
 * Note: when running {@link actionUpdater}, if a new version is available on the
 * remote updates server, this file will be overwritten by the new file, which
 * is downloaded upon running the action (route admin/updater).
 * 
 * @package X2CRM.controllers 
 */
class AdminController extends Controller {

    public $portlets = array();
    public $layout = '//layouts/main';
    
    /**
     * A list of actions to include.
     * 
     * This method specifies which actions are defined elsewhere but used here.
     * These actions are pro code that is included in the pro version of the software.
     * 
     * @return array An array of actions to include. 
     */
    public function actions(){
        return array(
			'getRoleAccess' => array(
				'class' => 'GetRoleAccessAction',
			),
            'editRoleAccess' => array(
                'class' => 'EditRoleAccessAction',
            ),
		);
    }
    
    /**
     * View the main admin menu
     */
    public function actionIndex() {
		if(isset($_GET['translateMode']))
			Yii::app()->session['translate'] = $_GET['translateMode']==1;
        $this->render('index');
    }
    
    /**
     * An overridden Yii method that happens before an action.
     * 
     * This method handles authorization on an attempt by a user to access an action.
     * The same method is defined in {@link X2Base::beforeAction} with a few minor differences.
     * 
     * @param string $action A paramter passed by Yii's internal action handling.
     * @return boolean True if the action is allowed to continue, otherwise throw exception.
     * @throws CHttpException Generates a 403 error if authorization fails
     */
    protected function beforeAction($action=null){
        $auth=Yii::app()->authManager;
        $action=ucfirst($this->getId()) . ucfirst($this->getAction()->getId());
        $authItem=$auth->getAuthItem($action);
        if(Yii::app()->user->checkAccess($action) || is_null($authItem) || Yii::app()->user->getName()=='admin'){
            return true;
        }elseif(Yii::app()->user->isGuest){
            $this->redirect($this->createUrl('/site/login'));
        }else{
            throw new CHttpException(403, 'You are not authorized to perform this action.');
        }
    }

    /**
     * @deprecated
     * Deprecated method for how to guides.
     * 
     * While these guides technically still exist in the software, much more useful
     * and up to date information can be found on the X2Engine website.
     * 
     * @param type $guide Which how to guide to access.
     */
    public function actionHowTo($guide) {
        if ($guide == 'gii')
            $this->render('howToGii');
        else if ($guide == 'model')
            $this->render('howToModel');
        else
            $this->redirect('index');
    }

    /**
     * Filters to be used by the controller.
     * 
     * This method defines which filters the controller will use.  Filters can be
     * built in with Yii or defined in the controller (see {@link AdminController::filterClearCache}).
     * See also Yii documentation for more information on filters.
     * 
     * @return array An array consisting of the filters to be used. 
     */
    public function filters() {
        // return the filter configuration for this controller, e.g.:
        return array(
            //'accessControl',
            'clearCache'
        );
    }

    /**
     * A list of behaviors for the controller to use.
     * 
     * {@link LeadRoutingBehavior} is used to consolidate code for lead routing rules.
     * As such, it has been moved to an external file.  This file includes LeadRoutingBehavior
     * or downloads it if the file does not currently exist.  See also Yii documentation
     * for more information on behaviors.
     * 
     * @return array An array of behaviors to implement. 
     */
	public function behaviors() {
		$file = 'protected/components/LeadRoutingBehavior.php';
		if (!file_exists($file)) {
			if ($versionTest = @file_get_contents('http://x2base.com/updates/versionCheck.php', 0, $context)) {
				$url = 'x2base';
			} else if ($versionTest = @file_get_contents('http://x2planet.com/updates/versionCheck.php', 0, $context)) {
				$url = 'x2planet';
			}
			$this->ccopy("http://$url.com/updates/x2engine/" . $file, $file);
		}
		return array(
			'LeadRoutingBehavior' => array(
				'class' => 'LeadRoutingBehavior'
			)
		);
	}

	/**
     * @deprecated
     * Deprecated access control function.
     * 
     * This function used to be used to control access roles for actions within 
     * the admin tab.  This system has been replaced with Yii's built in RBAC
     * which uses {@link AdminController::beforeAction} to determine permissions. 
     */
    public function accessRules() {
        /*return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('getRoutingType', 'getRole', 'getWorkflowStages', 'download', 'cleanUp', 'sql', 'getFieldData', 'installUpdate'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('viewPage', 'getAttributes', 'getDropdown', 'getFieldType'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'index', 'howTo', 'searchContact', 'sendEmail', 'mail', 'search', 'toggleAccounts',
                    'export', 'import', 'uploadLogo', 'toggleDefaultLogo', 'createModule', 'deleteModule', 'exportModule',
                    'importModule', 'toggleSales', 'setTimeout', 'emailSetup', 'googleIntegration', 'setChatPoll',
                    'renameModules', 'manageModules', 'createPage', 'contactUs', 'viewChangelog', 'toggleUpdater',
                    'translationManager', 'addCriteria', 'deleteCriteria', 'setLeadRouting', 'roundRobinRules',
                    'deleteRouting', 'addField', 'removeField', 'customizeFields', 'manageFields', 'editor',
                    'createFormLayout', 'deleteFormLayout', 'formVersion', 'dropDownEditor', 'manageDropDowns',
                    'deleteDropdown', 'editDropdown', 'roleEditor', 'deleteRole', 'editRole', 'manageRoles',
                    'roleException', 'appSettings', 'updater', 'registerModules', 'toggleModule', 'viewLogs', 'delete',
                    'tempImportWorkflow', 'workflowSettings', 'testVariables','testRoles'
                ),
                'users' => array('admin'),
            ),
            array('deny',
                'users' => array('*')
            )
        );*/
    }
    
    /**
     * A filter to clear the cache.
     * 
     * This method clears the cache whenever the admin controller is accessed.
     * Caching improves performance throughout the app, but will occasionally 
     * need to be cleared. Keeping this filter here allows for cleaning up the
     * cache when required.
     * 
     * @param type $filterChain The filter chain Yii is currently acting on.
     */
    public function filterClearCache($filterChain) {
        $cache = Yii::app()->cache;
        if (isset($cache))
            $cache->flush();
        $filterChain->run();
    }
    
    /**
     * @deprecated
     * Deprecated function for mass emailing contacts.
     * 
     * This method used to render a page to search for contacts to send out a 
     * mass mailing list to. The Marketing module has replaced this functionality
     * and is significantly more useful. 
     */
    public function actionSearchContact() {
        $this->render('searchContactInfo');
    }
    
    /**
     * @deprecated
     * Deprecated method to render a list of contacts meeting the search criteria of the previous method.
     *  
     * This method would be accessed when the {@link AdminController::actionSearchContact}
     * action had data posted in the form on the page.  It would 
     */
    public function actionSendEmail() {
        $criteria = $_POST['searchTerm'];

        $mailingList = Contacts::getMailingList($criteria);

        $this->render('sendEmail', array(
            'criteria' => $criteria,
            'mailingList' => $mailingList,
        ));
    }

    /**
     * @deprecated
     * Deprecated method to actually send mass emails.
     * 
     * This method links with the previous two deprecated methods to send out emails
     * after a contact list has been made and confirmed.  It has been replaced
     * by the Marketing module. 
     */
    public function actionMail() {
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $criteria = $_POST['criteria'];

        $headers = 'From: ' . Yii::app()->name;

        $mailingList = Contacts::getMailingList($criteria);

        foreach ($mailingList as $email) {
            mail($email, $subject, $body, $headers);
        }

        $this->render('mail', array(
            'mailingList' => $mailingList,
            'criteria' => $criteria,
        ));
    }

    /**
     * Find user for lead routing.
     * 
     * This method uses {@link LeadRoutingBehavior} to determine the proper user
     * for lead distribution within the app.  The user is echoed out to allow for
     * access via AJAX request. 
     */
    public function actionGetRoutingType() {
		$assignee = $this->getNextAssignee();
		//support original behavior
		if ($assignee == "Anyone") $assignee = "";
		echo $assignee;
    }

    /**
     * Render/save the Custom Lead Routing Rules
     * 
     * This method renders a grid of Custom Round Robin Rules and allows for new
     * rules to be created and saved. These rules are used in conjunction with
     * {@link AdminController::actionGetRoutingType} when the "Custom Round Robin"
     * lead distribution method is chosen. 
     */
    public function actionRoundRobinRules() {
        $model = new LeadRouting;
        $users = User::getNames();
        unset($users['Anyone']);
        unset($users['admin']);
        $priorityArray = array();
        for ($i = 1; $i <= LeadRouting::model()->count() + 1; $i++) {
            $priorityArray[$i] = $i;
        }
        $dataProvider = new CActiveDataProvider('LeadRouting', array(
                    'criteria' => array(
                        'order' => 'priority ASC',
                    )
                ));
        if (isset($_POST['LeadRouting'])) {
            $values = $_POST['Values'];
            $criteria = array();
            for ($i = 0; $i < count($values['field']); $i++) {
                $tempArr = array($values['field'][$i], $values['comparison'][$i], $values['value'][$i]);
                $criteria[] = implode(',', $tempArr);
            }
            $model->criteria = json_encode($criteria);
            $model->attributes = $_POST['LeadRouting'];
            $model->priority = $_POST['LeadRouting']['priority'];
            if (isset($_POST['group'])) {
                $group = true;
                $model->groupType = $_POST['groupType'];
            } else {
                $model->groupType = null;
            }

            $model->users = Accounts::parseUsers($model->users);
            $check = LeadRouting::model()->findByAttributes(array('priority' => $model->priority));
            if (isset($check)) {
                $query = "UPDATE x2_lead_routing SET priority=priority+1 WHERE priority>='$model->priority'";
                $command = Yii::app()->db->createCommand($query);
                $command->execute();
            }
            if ($model->save()) {
                $this->redirect('roundRobinRules');
            }
        }

        $this->render('customRules', array(
            'model' => $model,
            'users' => $users,
            'dataProvider' => $dataProvider,
            'priorityArray' => $priorityArray,
        ));
    }

    /**
     * Create a new Role.
     * 
     * This method is accessed by a form on the {@link AdminController::actionManageRoles}
     * page to create a new role. View and Edit permissions are set and saved in the proper
     * tables in this method, and then the user is redirected back to the "Manage Roles"
     * page.
     */
    public function actionRoleEditor() {
        $model = new Roles;
        if (isset($_POST['Roles'])) {
            $model->attributes = $_POST['Roles'];
            if (!(isset($_POST['viewPermissions']) && isset($_POST['editPermissions'])))
                $this->redirect('manageRoles');
            $viewPermissions = $_POST['viewPermissions'];
            $editPermissions = $_POST['editPermissions'];
            if (isset($_POST['Roles']['users']))
                $users = $model->users;
            else
                $users = array();
            $model->users = "";
            if ($model->save()) {
                
                foreach ($users as $user) {
                    $role = new RoleToUser;
                    $role->roleId = $model->id;
                    if (!is_numeric($user)) {
                        $userRecord = User::model()->findByAttributes(array('username' => $user));
                        $role->userId = $userRecord->id;
                        $role->type = 'user';
                    }/* x2temp */ else {
                        $role->userId = $user;
                        $role->type = 'group';
                    }/* end x2temp */
                    $role->save();
                }
                $fields = Fields::model()->findAll();
                $temp = array();
                foreach ($fields as $field) {
                    $temp[] = $field->id;
                }
                $both = array_intersect($viewPermissions, $editPermissions);
                $view = array_diff($viewPermissions, $editPermissions);
                $neither = array_diff($temp, $viewPermissions);
                foreach ($both as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 2;
                    $rolePerm->save();
                }
                foreach ($view as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 1;
                    $rolePerm->save();
                }
                foreach ($neither as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 0;
                    $rolePerm->save();
                }
            }
            $this->redirect('manageRoles');
        }
    }

    /**
     * Delete an existing role.
     * 
     * This method is accessed by a form on the {@link AdminController::manageRoles}
     * page to allow for the deletion of admin created roles.  Default system roles
     * (authenticated, guest, and admin) cannot be deleted this way.
     */
    public function actionDeleteRole() {
        $auth=Yii::app()->authManager;
        if (isset($_POST['role'])) {
            $id = $_POST['role'];
            $role = Roles::model()->findByAttributes(array('name' => $id));
            $id = $role->id;
            $userRoles = RoleToUser::model()->findAllByAttributes(array('roleId' => $role->id));
            foreach ($userRoles as $userRole) {
                $userRole->delete();
            }
            $permissions = RoleToPermission::model()->findAllByAttributes(array('roleId' => $role->id));
            foreach ($permissions as $permission) {
                $permission->delete();
            }
            $workflowRoles = RoleToWorkflow::model()->findAllByAttributes(array('replacementId' => $role->id));
            foreach ($workflowRoles as $workflow) {
                $workflow->delete();
            }
            $auth->removeAuthItem($role->name);
            $role->delete();

            $this->redirect('manageRoles');
        }
    }
    
    /**
     * Modify the permissions on an existing role.
     * 
     * This action is called by a form on the {@link AdminController::actionManageRoles}
     * page to allow for the modification of an existing role. 
     */
    public function actionEditRole() {
        $model = new Roles;
        
        if (isset($_POST['Roles'])) {
            $id = $_POST['Roles']['name'];
            $model = Roles::model()->findByAttributes(array('name' => $id));
            $id = $model->id;
            if (!(isset($_POST['viewPermissions']) && isset($_POST['editPermissions'])))
                $this->redirect('manageRoles');
            $viewPermissions = $_POST['viewPermissions'];
            $editPermissions = $_POST['editPermissions'];
            if (isset($_POST['users']))
                $users = $_POST['users'];
            else
                $users = array();
            $model->users = "";
            if ($model->save()) {
                
                $userRoles = RoleToUser::model()->findAllByAttributes(array('roleId' => $model->id));
                foreach ($userRoles as $role) {
                    $role->delete();
                }
                $permissions = RoleToPermission::model()->findAllByAttributes(array('roleId' => $model->id));
                foreach ($permissions as $permission) {
                    $permission->delete();
                }
                foreach ($users as $user) {
                    $userRecord = User::model()->findByAttributes(array('username' => $user));
                    $role = new RoleToUser;
                    $role->roleId = $model->id;
                    if (!is_numeric($user)) {
                        $userRecord = User::model()->findByAttributes(array('username' => $user));
                        $role->userId = $userRecord->id;
                        $role->type = 'user';
                    }/* x2temp */ else {
                        $role->userId = $user;
                        $role->type = 'group';
                    }/* end x2temp */
                    $role->save();
                }
                $fields = Fields::model()->findAll();
                $temp = array();
                foreach ($fields as $field) {
                    $temp[] = $field->id;
                }
                $both = array_intersect($viewPermissions, $editPermissions);
                $view = array_diff($viewPermissions, $editPermissions);
                $neither = array_diff($temp, $viewPermissions);
                foreach ($both as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 2;
                    $rolePerm->save();
                }
                foreach ($view as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 1;
                    $rolePerm->save();
                }
                foreach ($neither as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 0;
                    $rolePerm->save();
                }
            }
            $this->redirect('manageRoles');
        }

        $this->render('editRole', array(
            'model' => $model,
        ));
    }

    /**
     * Create a workflow based exception for a role.
     * 
     * This method is called by a form on the {@link AdminController::manageRoles} 
     * page to allow for the creation of workflow based exceptions for a role.
     * Workflow exceptions modify which fields are visible or editable based on
     * what stage of a workflow a contact is in.
     */
    public function actionRoleException() {
        $model = new Roles;
        $temp = Workflow::model()->findAll();
        $workflows = array();
        foreach ($temp as $workflow) {
            $workflows[$workflow->id] = $workflow->name;
        }
        if (isset($_POST['Roles'])) {
            $workflow = $_POST['workflow'];
            if(isset($workflow) && !empty($workflow))
                $workflowName = Workflow::model()->findByPk($workflow)->name;
            else
                $this->redirect('manageRoles');
            $stage = $_POST['workflowStages'];
            if(isset($stage) && !empty($stage))
                $stageName = WorkflowStage::model()->findByPk($stage)->name;
            else
                $this->redirect('manageRoles');
            $viewPermissions = $_POST['viewPermissions'];
            $editPermissions = $_POST['editPermissions'];
            $model->attributes = $_POST['Roles'];
            $oldRole = Roles::model()->findByAttributes(array('name' => $model->name));
            $model->users = "";
            $model->name.=" - $workflowName: $stageName";
            if ($model->save()) {
                $replacement = new RoleToWorkflow;
                $replacement->workflowId = $workflow;
                $replacement->stageId = $stage;
                $replacement->roleId = $oldRole->id;
                $replacement->replacementId = $model->id;
                $replacement->save();
                $fields = Fields::model()->findAll();
                $temp = array();
                foreach ($fields as $field) {
                    $temp[] = $field->id;
                }
                $both = array_intersect($viewPermissions, $editPermissions);
                $view = array_diff($viewPermissions, $editPermissions);
                $neither = array_diff($temp, $viewPermissions);
                foreach ($both as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 2;
                    $rolePerm->save();
                }
                foreach ($view as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 1;
                    $rolePerm->save();
                }
                foreach ($neither as $field) {
                    $rolePerm = new RoleToPermission;
                    $rolePerm->roleId = $model->id;
                    $rolePerm->fieldId = $field;
                    $rolePerm->permission = 0;
                    $rolePerm->save();
                }
            }
            $this->redirect('manageRoles');
        }
    }
    
    /**
     * Modify workflow configuration settings.
     * 
     * This method allows for the configuration of workflow backdating functions.
     * These settings control whether or not users are allowed to set workflow
     * completion dates to be in the past, and to what extent they can modify a
     * workflow action once it is marked as complete. 
     */
    public function actionWorkflowSettings() {
        $admin = &Yii::app()->params->admin;
        if (isset($_POST['Admin'])) {

            $admin->attributes = $_POST['Admin'];
            // $admin->timeout *= 60;	//convert from minutes to seconds


            if ($admin->save()) {
                // $this->redirect('workflowSettings');
            }
        }
        // $admin->timeout = ceil($admin->timeout / 60);
        $this->render('workflowSettings', array(
            'model' => $admin,
        ));
    }

    /**
     * A method to echo a dropdown of workflow stages.
     * 
     * This method is called via AJAX request and echoes back a dropdown with 
     * options consisting of all stages for a particular workflow. 
     */
    public function actionGetWorkflowStages() {
        if (isset($_POST['workflow'])) {
            $id = $_POST['workflow'];
            $stages = Workflow::getStages($id);
            foreach ($stages as $key => $value) {
                echo CHtml::tag('option', array('value' => $key), CHtml::encode($value), true);
            }
        } else {
            echo CHtml::tag('option', array('value' => ''), CHtml::encode(var_dump($_POST)), true);
        }
    }

    /**
     * Echo out a series of inputs for a role editor page.
     *  
     * This method is called via AJAX from the "Edit Role" portion of the "Manage Roles"
     * page.  Upon selection of a role in the dropdown on that page, this method
     * finds all relevant information about the role and echoes it back as a form
     * to allow for editing of the role.
     */
    public function actionGetRole() {
        if (isset($_POST['Roles'])) {
            $id = $_POST['Roles']['name'];
            if (is_null($id)) {
                echo "";
                exit;
            }
            $role = Roles::model()->findByAttributes(array('name' => $id));
            $id = $role->id;
            $roles = RoleToUser::model()->findAllByAttributes(array('roleId' => $id));
            $users = array();
            foreach ($roles as $link) {
                if ($link->type == 'user')
                    $users[] = User::model()->findByPk($link->userId)->username;
                /* x2temp */
                else
                    $users[] = Groups::model()->findByPk($link->userId)->id;
                /* end x2temp */
            }
            $allUsers = User::model()->findAll('status="1"');
            $selected = array();
            $unselected = array();
            foreach ($users as $user) {
                $selected[] = $user;
            }
            foreach ($allUsers as $user) {
                $unselected[$user->username] = $user->firstName . " " . $user->lastName;
            }
            /* x2temp */
            $groups = Groups::model()->findAll();
            foreach ($groups as $group) {
                $unselected[$group->id] = $group->name;
            }
            /* end x2temp */
            unset($unselected['admin']);
            echo "<div id='users'><label>Users</label>";
            echo CHtml::dropDownList('users[]', $selected, $unselected, array('class' => 'multiselect', 'multiple' => 'multiple', 'size' => 8));
            echo "</div>";
            $fields = Fields::model()->findAllBySql("SELECT * FROM x2_fields ORDER BY modelName ASC");
            $viewSelected = array();
            $editSelected = array();
            $fieldUnselected = array();
            $fieldPerms = RoleToPermission::model()->findAllByAttributes(array('roleId' => $role->id));
            foreach ($fieldPerms as $perm) {
                if ($perm->permission == 2) {
                    $viewSelected[] = $perm->fieldId;
                    $editSelected[] = $perm->fieldId;
                } else if ($perm->permission == 1) {
                    $viewSelected[] = $perm->fieldId;
                }
            }
            foreach ($fields as $field) {
                $fieldUnselected[$field->id] = $field->modelName . " - " . $field->attributeLabel;
            }
            echo "<br /><label>View Permissions</label>";
            echo CHtml::dropDownList('viewPermissions[]', $viewSelected, $fieldUnselected, array('class' => 'multiselect', 'multiple' => 'multiple', 'size' => 8));
            echo "<br /><label>Edit Permissions</label>";
            echo CHtml::dropDownList('editPermissions[]', $editSelected, $fieldUnselected, array('class' => 'multiselect', 'multiple' => 'multiple', 'size' => 8));
        }
    }

    
    /**
     * A catch all page for roles.
     * 
     * This action renders a page with forms for the creation, editing, and deletion
     * of roles.  It also displays a grid with all user created roles (default 
     * roles are not included and cannot be edited this way). 
     */
    public function actionManageRoles() {
        $model = new Roles;
        
        $dataProvider = new CActiveDataProvider('Roles');
        $roles = $dataProvider->getData();
        $arr = array();
        foreach ($roles as $role) {
            $arr[$role->name] = $role->name;
        }
        $temp = Workflow::model()->findAll();
        $workflows = array();
        foreach ($temp as $workflow) {
            $workflows[$workflow->id] = $workflow->name;
        }

        $this->render('manageRoles', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
            'roles' => $arr,
            'workflows' => $workflows,
        ));
    }

    /**
     * @deprecated
     * A deprecated function controlling the updater.
     * 
     * This function formerly toggled whether or not to notify the admin of any
     * new updates to X2CRM.  This has been replaced with an option in the "Updater
     * Settings" page of the Admin tab. 
     */
    public function actionToggleUpdater() {

        $admin = AdminChild::model()->findByPk(1);
        $admin->ignoreUpdates ? $admin->ignoreUpdates = 0 : $admin->ignoreUpdates = 1;
        $admin->save();
        $this->redirect('index');
    }

    /**
     * @deprecated
     * A deprecated method for contacting X2Engine Inc.
     * 
     * This method has been replaced with a form on our website, and is no longer
     * linked to anywhere on the application.  If you wish to get in contact with us,
     * please visit www.x2engine.com 
     */
    public function actionContactUs() {

        if (isset($_POST['email'])) {
            $email = $_POST['email'];
            $subject = $_POST['subject'];
            $body = $_POST['body'];

            mail('contact@x2engine.com', $subject, $body, "From: $email");
            $this->redirect('index');
        }

        $this->render('contactUs');
    }

    /**
     * Render the changelog.
     * 
     * This action renders the user changelog page, which contains a list of all
     * changed made by users within the app. 
     */
    public function actionViewChangelog() {

        $model = new Changelog('search');
        $model->timestamp = null;

        $this->render('viewChangelog', array(
            'model' => $model,
        ));
    }

    /**
     * Add notification criteria.
     * 
     * This method is called by a form on the "Manage Notification Criteria" page
     * and is used to create a new criteria for generation notifications. 
     */
    public function actionAddCriteria() {
        $criteria = new Criteria;
        $users = User::getNames();
        $dataProvider = new CActiveDataProvider('Criteria');
        unset($users['']);
        if (isset($_POST['Criteria'])) {
            $criteria->attributes = $_POST['Criteria'];
            $str = "";
            $arr = $criteria->users;
            if ($criteria->type == 'assignment' && count($arr) > 1) {
                $this->redirect('addCriteria');
            }
            if (isset($arr)) {
                foreach ($arr as $user) {
                    $str.=$user . ", ";
                }
                $str = substr($str, 0, -2);
            }
            $criteria->users = $str;
            if ($criteria->modelType != null && $criteria->comparisonOperator != null) {
                if ($criteria->save()) {
                    
                }
                $this->redirect('index');
            }
        }
        $this->render('addCriteria', array(
            'users' => $users,
            'model' => $criteria,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Delete a notification criteria.
     * 
     * This function is called to delete a user created notification critera.
     * Some criteria are built in to the app and cannot be deleted this way.
     * 
     * @param int $id The ID of the criteria to be deleted. 
     */
    public function actionDeleteCriteria($id) {

        $model = Criteria::model()->findByPk($id);
        $model->delete();
        $this->redirect(array('addCriteria'));
    }
    
    /**
     * Delete a routing rule.
     * 
     * This method will delete a custom routing rule that has been configured
     * for the lead distribution process.
     * @param int $id The ID of the rule to be deleted.
     */
    public function actionDeleteRouting($id) {

        $model = LeadRouting::model()->findByPk($id);
        $model->delete();
        $this->redirect(array('roundRobinRules'));
    }

    /**
     * Echo a list of model attributes as a dropdown.
     * 
     * This method is called via AJAX as a part of creating notification criteria.
     * It takes the model or module name as POST data and returns a list of dropdown
     * options consisting of the fields available to that model. 
     */
	public function actionGetAttributes() {
		$data = array();
		$type = null;

		if (isset($_POST['Criteria']['modelType']))
			$type = ucfirst($_POST['Criteria']['modelType']);
		if (isset($_POST['Fields']['modelName']))
			$type = $_POST['Fields']['modelName'];
			
		if(isset($type)) {
			foreach(CActiveRecord::model('Fields')->findAllByAttributes(array('modelName'=>$type)) as $field) {
			
				if(isset($_POST['Criteria']))
					$data[$field->fieldName] = $field->attributeLabel;
				else
					$data[$field->id] = $field->attributeLabel;
				
			}
		}
		$htmlOptions = array();
		echo CHtml::listOptions('',$data,$htmlOptions);
	}

    /**
     * @deprecated
     * Deprecated function to set user timeout.
     * 
     * This method formerly controlled the user session timeout settings for the 
     * software.  This setting is now controlled by the "General Settings" page.
     */
    public function actionSetTimeout() {

        $admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
        if (isset($_POST['Admin'])) {
            $timeout = $_POST['Admin']['timeout'];

            $admin->timeout = $timeout;

            if ($admin->save()) {
                $this->redirect('index');
            }
        }

        $this->render('setTimeout', array(
            'admin' => $admin,
        ));
    }

    /**
     * @deprecated
     * Deprecated method to set chat polling
     * 
     * This method formerly controlled the configuration of chat polling requests.
     * This timeout is now set by the "General Settings" page.
     */
    public function actionSetChatPoll() {

        $admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
        if (isset($_POST['Admin'])) {
            $timeout = $_POST['Admin']['chatPollTime'];

            $admin->chatPollTime = $timeout;

            if ($admin->save()) {
                $this->redirect('index');
            }
        }

        $this->render('setChatPoll', array(
            'admin' => $admin,
        ));
    }

    /**
     * Control chat polling and session timeout.
     * 
     * This method renders a page with settings for user session timeout and chat
     * request polling.  These settings are application wide and not per user. 
     */
    public function actionAppSettings() {

        $admin = &Yii::app()->params->admin;
        if (isset($_POST['Admin'])) {

            // if(!isset($_POST['Admin']['ignoreUpdates']))
            // $admin->ignoreUpdates = 1;

            $admin->attributes = $_POST['Admin'];
            $admin->timeout *= 60; //convert from minutes to seconds


            if ($admin->save()) {
                $this->redirect('appSettings');
            }
        }
        $admin->timeout = ceil($admin->timeout / 60);
        $this->render('appSettings', array(
            'model' => $admin,
        ));
    }

    /**
     * Sets the lead routing type.
     * 
     * This method allows for the admin to configure which option to use for lead
     * distribution.  This is what determines the actions of {@link LeadRoutingBehavior}. 
     */
    public function actionSetLeadRouting() {

        $admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
        if (isset($_POST['Admin'])) {
            $routing = $_POST['Admin']['leadDistribution'];
            $online = $_POST['Admin']['onlineOnly'];
            if($routing=='singleUser'){
                $user=$_POST['Admin']['rrId'];
                $admin->rrId=$user;
            }

            $admin->leadDistribution = $routing;
            $admin->onlineOnly = $online;

            if ($admin->save()) {
                $this->redirect('index');
            }
        }

        $this->render('leadRouting', array(
            'admin' => $admin,
        ));
    }

    /**
     * Configure google integration.
     * 
     * This method provides a form for the entry of Google Apps data.  This will
     * allow for users to log in with their google account and sync X2CRM's calendars
     * with their Google Calendar. 
     */
    public function actionGoogleIntegration() {

        $admin = &Yii::app()->params->admin;
        if (isset($_POST['Admin'])) {
            foreach ($admin->attributes as $fieldName => $field) {
                if (isset($_POST['Admin'][$fieldName])) {
                    $admin->$fieldName = $_POST['Admin'][$fieldName];
                }
            }

            if ($admin->save()) {
                $this->redirect('googleIntegration');
            }
        }
        $this->render('googleIntegration', array(
            'model' => $admin,
        ));
    }

    /**
     * Configure email settings.
     * 
     * This allows for configuration of how emails are handled by X2CRM.  The admin
     * can select to use the server that the software is hosted on or a separate mail server. 
     */
    public function actionEmailSetup() {

        $admin = &Yii::app()->params->admin; //CActiveRecord::model('Admin')->findByPk(1);
        if (isset($_POST['Admin'])) {
            $admin->attributes = $_POST['Admin'];

            // $admin->chatPollTime=$timeout;
            // $admin->save();
            if ($admin->save()) {
                $this->redirect('emailSetup');
            }
        }

        $this->render('emailSetup', array(
            'model' => $admin,
        ));
    }

    /**
     * Add a custom field.
     * 
     * This method allows for the creation of custom fields linked to any customizable
     * module in X2CRM.  This is used by "Manage Fields." 
     */
    public function actionAddField() {
        $model = new Fields;
        if (isset($_POST['Fields'])) {
            $model->attributes = $_POST['Fields'];
            (isset($_POST['Fields']['required']) && $_POST['Fields']['required'] == 1) ? $model->required = 1 : $model->required = 0;
            (isset($_POST['Fields']['searchable']) && $_POST['Fields']['searchable'] == 1) ? $model->searchable = 1 : $model->searchable = 0;
            $model->type = $_POST['Fields']['type'];
            // $model->visible=1;
            $model->custom = 1;
            $model->modified = 1;
            $model->fieldName = strtolower($model->fieldName);

            $fieldType = $model->type;
            switch ($fieldType) {
                case "boolean":
                    $fieldType = "BOOLEAN";
                    break;
                case "float":
                    $fieldType = "FLOAT";
                    break;
                case "int":
                    $fieldType = "BIGINT";
                    break;
                case "text":
                    $fieldType = "TEXT";
                    break;
                case "date":
                    $fieldType = "BIGINT";
                    break;
                case "currency":
                    $fieldType = "FLOAT";
                    break;
                default:
                    $fieldType = 'VARCHAR(250)';
                    break;
            }

            if ($model->type == 'dropdown') {
                if (isset($_POST['dropdown'])) {
                    $id = $_POST['dropdown'];
                    $model->linkType = $id;
                }
            }
            if ($model->type == "link") {
                if (isset($_POST['dropdown'])) {
                    $linkType = $_POST['dropdown'];
                    $model->linkType = ucfirst($linkType);
                }
            }
            $tableName = CActiveRecord::model($model->modelName)->tableName();


            $field = strtolower($model->fieldName);
            if (preg_match("/\s/", $field)) {
                
            } else {
                if ($model->save()) {
                    $sql = "ALTER TABLE $tableName ADD COLUMN $field $fieldType";
                    $command = Yii::app()->db->createCommand($sql);
                    $result = $command->query();
                }
            }
            $this->redirect('manageFields');
        }
    }

    /**
     * Delete a field.
     * 
     * This method allows for the deletion of custom fields.  Default fields cannot
     * be deleted in this way. 
     */
    public function actionRemoveField() {

        if (isset($_POST['field']) && $_POST['field'] != "") {
            $id = $_POST['field'];
            $field = Fields::model()->findByPk($id);
            $fieldName = strtolower($field->fieldName);
            $tableName = CActiveRecord::model($field->modelName)->tableName();
            if ($field->delete()) {
                $sql = "ALTER TABLE $tableName DROP COLUMN $fieldName";
                $command = Yii::app()->db->createCommand($sql);
                $result = $command->query();
            }
        }
        $this->redirect('manageFields');
    }

    /**
     * Edit a pre-existing field.
     * 
     * This method allows for the editing of both user created and default fields.
     * This also changes the database schema to fit the field type and as such must
     * be used very carefully. 
     */
	public function actionCustomizeFields() {

		if (isset($_POST['Fields'])) {
			$fieldModel = CActiveRecord::model('Fields')->findByPk($_POST['Fields']['id']);
			$fieldModel->attributes = $_POST['Fields'];
			$fieldModel->type = $_POST['Fields']['type'];
			if ($fieldModel->type == 'dropdown') {
				if (isset($_POST['dropdown'])) {
					$id = $_POST['dropdown'];
					$fieldModel->linkType = $id;
				}
			}
			if ($fieldModel->type == "link") {
				if (isset($_POST['dropdown'])) {
					$linkType = $_POST['dropdown'];
					$fieldModel->linkType = ucfirst($linkType);
				}
			}
            $fieldType = $fieldModel->type;
            switch ($fieldType) {
                case "boolean":
                    $fieldType = "BOOLEAN";
                    break;
                case "float":
                    $fieldType = "FLOAT";
                    break;
                case "int":
                    $fieldType = "BIGINT";
                    break;
                case "text":
                    $fieldType = "TEXT";
                    break;
                case "date":
                    $fieldType = "BIGINT";
                    break;
                case "currency":
                    $fieldType = "FLOAT";
                    break;
                default:
                    $fieldType = 'VARCHAR(250)';
                    break;
            }
            $fieldName = strtolower($fieldModel->fieldName);
            $tableName = CActiveRecord::model($fieldModel->modelName)->tableName();
			$fieldModel->modified = 1;
			(isset($_POST['Fields']['required']) && $_POST['Fields']['required'] == 1) ? $fieldModel->required = 1 : $fieldModel->required = 0;
			(isset($_POST['Fields']['searchable']) && $_POST['Fields']['searchable'] == 1) ? $fieldModel->searchable = 1 : $fieldModel->searchable = 0;
			if ($fieldModel->save()){
                $sql = "ALTER TABLE $tableName MODIFY COLUMN $fieldName $fieldType";
                $command = Yii::app()->db->createCommand($sql);
                $result = $command->query();
				$this->redirect('manageFields');
            }
		}
	}

    /**
     * Echo a dropdown of field data.
     * 
     * This method is called via AJAX as part of editing fields.  It echoes back 
     * a list of all the relevant attributes for a field when a dropdown option
     * is selected. 
     */
	public function actionGetFieldData() {
		
		if (isset($_POST['Fields']['id'])) {
			$fieldModel = CActiveRecord::model('Fields')->findByPk($_POST['Fields']['id']);
			$temparr = $fieldModel->attributes;
			if (!empty($fieldModel->linkType)) {
				$type = $fieldModel->type;
				if ($type == 'link') {
					$query = Yii::app()->db->createCommand()
						->select('modelName')
						->from('x2_fields')
						->group('modelName')
						->queryAll();
					$arr=array();
					foreach($query as $array){
						if($array['modelName']!='Calendar')
							$arr[$array['modelName']]=$array['modelName'];
					}
					$temparr['dropdown'] = CHtml::dropDownList('dropdown', $fieldModel->linkType, $arr);
				} elseif ($type == 'dropdown') {
					$dropdowns = Dropdowns::model()->findAll();
					$arr = array();
					foreach ($dropdowns as $dropdown) {
						$arr[$dropdown->id] = $dropdown->name;
					}

					$temparr['dropdown'] = CHtml::dropDownList('dropdown', '', $arr);
				}
			} else {
				$temparr['dropdown'] = "";
			}
			echo CJSON::encode($temparr);
		}
	}

    /**
     * General field management.
     * 
     * This action serves as the landing page for all of the custom field related
     * actions within the software. 
     */
	public function actionManageFields() {
		$model = new Fields;
		$dataProvider = new CActiveDataProvider('Fields', array(
			'criteria' => array(
				'condition' => 'modified=1'
			)
		));
		$fields = Fields::model()->findAllByAttributes(array('custom' => '1'));
		$arr = array();
		foreach ($fields as $field) {
			$arr[$field->id] = $field->attributeLabel;
		}

		$this->render('manageFields', array(
			'dataProvider' => $dataProvider,
			'model' => $model,
			'fields' => $arr,
		));
	}

    /**
     * Create a static page.
     * 
     * This method allows the admin to create a static page to go on the top bar
     * menu.  The page is a basic doc editor which is then saved as a Module record
     * of type "Document." 
     */
    public function actionCreatePage() {

        $model = new DocChild;
        $users = User::getNames();
        if (isset($_POST['DocChild'])) {

            $model->attributes = $_POST['DocChild'];
            $arr = $model->editPermissions;
            if (isset($arr))
                $model->editPermissions = Accounts::parseUsers($arr);
            $model->createdBy = 'admin';
            $model->createDate = time();
            $model->lastUpdated = time();
            $model->updatedBy = 'admin';

            $module = new Modules;
            $module->adminOnly = 0;
            $module->toggleable = 1;
            $module->custom = 1;
            $module->visible = 1;
            $module->editable = 0;
            $module->searchable = 0;
            $module->menuPosition = Modules::model()->count();
            $module->name = 'document';
            $module->title = $model->title;

            if ($module->save()) {

                if ($model->save()) {
                    $this->redirect(array('viewPage', 'id' => $model->id));
                }
            }
        }

        $this->render('createPage', array(
            'model' => $model,
            'users' => $users,
        ));
    }

    /**
     * View a page that has been created.
     * 
     * This method is what is called when a user clicks the top bar link to a static
     * page that has been previously created.  Nearly identical to a document view
     * but without the widgets in the layout.
     * 
     * @param int $id The ID of the page being viewed.
     */
    public function actionViewPage($id) {
        $model = DocChild::model()->findByPk($id);
        if (!isset($model))
            $this->redirect(array('docs/index'));

        $this->render('viewTemplate', array(
            'model' => $model,
        ));
    }

    /**
     * Change the title of a module.
     * 
     * This allows for the configuration of the display name of a module.  As of 
     * version 2.0, this will not affect text other than the top bar menu. 
     */
    public function actionRenameModules() {

        $order = Modules::model()->findAllByAttributes(array('visible' => 1));
        $menuItems = array();
        foreach ($order as $module) {
            $menuItems[$module->name] = $module->title;
        }
        foreach ($menuItems as $key => $value)
            $menuItems[$key] = preg_replace('/&#58;/', ':', $value); // decode any colons

        if (isset($_POST['module']) && isset($_POST['name'])) {
            $module = $_POST['module'];
            $name = $_POST['name'];

            $moduleRecord = Modules::model()->findByAttributes(array('name' => $module, 'title' => $menuItems[$module]));
            $moduleRecord->title = $name;

            if ($moduleRecord->save()) {
                $this->redirect('index');
            }
        }

        $this->render('renameModules', array(
            'modules' => $menuItems,
        ));
    }
    
    /**
     * Re-arrange the top bar menu.
     * 
     * This form allows for the admin to change the order and visibility of top bar
     * menu items for all users.
     */
    public function actionManageModules() {

        $modules = Modules::model()->findAll(array('order' => 'menuPosition ASC'));

        $menuItems = array();  // assoc. array with correct order, containing realName => nickName
        $selectedItems = array();

        foreach ($modules as $module) {
            if($module->name!='document')
                $menuItems[$module->name]=$module->title;
            else
                $menuItems[$module->title]=$module->title;
            if ($module->visible) {
                $selectedItems[] = ($module->name!='document')?$module->name:$module->title;
            }
        }


        if (isset($_POST['formSubmit'])) {
            $selectedItems = isset($_POST['menuItems']) ? $_POST['menuItems'] : array();
            $newMenuItems = array();


            // build $newMenuItems array
            foreach ($selectedItems as $item) {
                $newMenuItems[$item] = $menuItems[$item]; // copy each selected item into $newMenuItems
                unset($menuItems[$item]);     // and remove them from $menuItems
            }
            foreach ($newMenuItems as $key => $item) {
                $moduleRecord = Modules::model()->findByAttributes(array('name' => $key));
                if (isset($moduleRecord)) {
                    $moduleRecord->visible = 1;
                    $moduleRecord->menuPosition = array_search($key, array_keys($newMenuItems));
                    if ($moduleRecord->save()) {
                        
                    }
                }else{
                    $moduleRecord = Modules::model()->findByAttributes(array('title' => $key));
                    if (isset($moduleRecord)) {
                        $moduleRecord->visible = 1;
                        $moduleRecord->menuPosition = array_search($key, array_keys($newMenuItems));
                        if ($moduleRecord->save()) {

                        }
                    }
                }
            }
            foreach ($menuItems as $key => $item) {
                $moduleRecord = Modules::model()->findByAttributes(array('name' => $key));
                if (isset($moduleRecord)) {
                    $moduleRecord->visible = 0;
                    $moduleRecord->menuPosition = -1;
                    if ($moduleRecord->save()) {
                        
                    }
                }else{
                    $moduleRecord = Modules::model()->findByAttributes(array('title' => $key));
                    if (isset($moduleRecord)) {
                        $moduleRecord->visible = 0;
                        $moduleRecord->menuPosition = -1;
                        if ($moduleRecord->save()) {

                        }
                    }
                }
            }

            $this->redirect('manageModules');
        }
        $this->render('manageModules', array(
            'menuItems' => $menuItems,
            'selectedItems' => $selectedItems
        ));
    }

    /**
     * Upload a custom logo
     * 
     * This method allows for the admin to upload their own logo to go in place of
     * the X2CRM logo in the top left corner of the software. 
     */
    public function actionUploadLogo() {
        if (isset($_FILES['logo-upload'])) {
            $temp = CUploadedFile::getInstanceByName('logo-upload');
            $name = $temp->getName();
            $temp->saveAs('uploads/logos/' . $name);
            $admin = ProfileChild::model()->findByAttributes(array('username' => 'admin'));
            $logo = Media::model()->findByAttributes(array('associationId' => $admin->id, 'associationType' => 'logo'));
            if (isset($logo)) {
                unlink($logo->fileName);
                $logo->delete();
            }

            $logo = new Media;
            $logo->associationType = 'logo';

            $logo->associationId = $admin->id;
            $logo->fileName = 'uploads/logos/' . $name;

            if ($logo->save()) {
                $this->redirect('index');
            }
        }

        $this->render('uploadLogo');
    }

    /**
     * Reverts the logo back to X2CRM. 
     */
    public function actionToggleDefaultLogo() {

        $adminProf = ProfileChild::model()->findByAttributes(array('username' => 'admin'));
        $logo = Media::model()->findByAttributes(array('associationId' => $adminProf->id, 'associationType' => 'logo'));
        if (!isset($logo)) {

            $logo = new Media;
            $logo->associationType = 'logo';
            $name = 'yourlogohere.png';
            $logo->associationId = $adminProf->id;
            $logo->fileName = 'uploads/logos/' . $name;

            if ($logo->save()) {
                
            }
        } else {
            $logo->delete();
        }
        $this->redirect(array('index'));
    }

    /**
     * Create or edit translations.
     * 
     * This method allows the admin to access the X2CRM built in translation manager.
     * Any translation for any language can be edited and saved from here, and new
     * ones can be added. 
     */
    public function actionTranslationManager() {
        $this->layout = null;
        $messagePath = 'protected/messages';
        include('protected/extensions/TranslationManager.php');
        // die('hello:'.var_dump($_POST));
    }

    /**
     * Creates a new custom module.
     * 
     * This method allows for the creation of admin defined modules to use in the
     * software. These modules are more basic in functionality than most other X2
     * modules, but are fully customizable from the studio. 
     */
    public function actionCreateModule() {

        $errors = array();

        if (isset($_POST['moduleName'])) {

            $title = trim($_POST['title']);
            $recordName = trim($_POST['recordName']);

            $moduleName = trim($_POST['moduleName']);

            if (preg_match('/\W/', $moduleName) || preg_match('/^[^a-zA-Z]+/', $moduleName))   // are there any non-alphanumeric or _ chars?
                $errors[] = Yii::t('module', 'Invalid table name'); //$this->redirect('createModule');									// or non-alpha characters at the beginning?

            if ($moduleName == '')  // we will attempt to use the title
                $moduleName = $title; // as the backend name, if possible

            if ($recordName == '')  // use title for record name 
                $recordName = $title; // if none is provided

            $trans = array(
                'Š' => 'S', 'š' => 's', '�?' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', '�?' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
                'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', '�?' => 'I', 'Î' => 'I',
                '�?' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
                'Û' => 'U', 'Ü' => 'U', '�?' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
                'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
                'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
                'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f'
            );

            $moduleName = strtolower(strtr($moduleName, $trans));  // replace characters with their A-Z equivalent, if possible

            $moduleName = preg_replace('/\W/', '', $moduleName); // now remove all remaining non-alphanumeric or _ chars

            $moduleName = preg_replace('/^[0-9_]+/', '', $moduleName); // remove any numbers or _ from the beginning



            if ($moduleName == '')        // if there is nothing left of moduleName at this point,
                $moduleName = 'module' . substr(time(), 5);  // just generate a random one


            if (!is_null(Modules::model()->findByAttributes(array('title' => $title))) || !is_null(Modules::model()->findByAttributes(array('name' => $moduleName))))
                $errors[] = Yii::t('module', 'A module with that name already exists');
            if (empty($errors)) {

                $this->writeConfig($title, $moduleName, $recordName);
                $this->createNewTable($moduleName);

                $this->createSkeletonDirectories($moduleName);

                $moduleRecord = new Modules;
                $moduleRecord->name = $moduleName;
                $moduleRecord->title = $title;
                $moduleRecord->custom = 1;
                $moduleRecord->visible = 1;
                $moduleRecord->editable = $_POST['editable'];
                $moduleRecord->adminOnly = $_POST['adminOnly'];
                $moduleRecord->searchable = $_POST['searchable'];
                $moduleRecord->toggleable = 1;
                $moduleRecord->menuPosition = Modules::model()->count();
                $moduleRecord->save();
                $auth=Yii::app()->authManager;
                $auth->createOperation(ucfirst($moduleName).'Index');
                $auth->addItemChild('authenticated',ucfirst($moduleName).'Index');
                $auth->createOperation(ucfirst($moduleName).'Admin');
                $auth->addItemChild('admin',ucfirst($moduleName).'Admin');
                $this->redirect(array('/' . $moduleName . '/index'));
            }
        }

        $this->render('createModule', array('errors' => $errors));
    }

    /**
     * Create module config file
     * 
     * This is called by {@link AdminController::actionCreateModule} in the process
     * of creating a new module.  This writes a config file for the module to use.
     * 
     * @param string $title The display title of the module
     * @param string $moduleName The actual name of the module
     * @param string $recordName What to call the records of this module
     */
    private function writeConfig($title, $moduleName, $recordName) {

        $templateFolder = Yii::app()->file->set('protected/modules/template/');
        $templateFolder->copy($moduleName);

        $module = Yii::app()->file->set('protected/modules/template/TemplatesModule.php');
        $module->copy('protected/modules/' . $moduleName . '/TemplatesModule.php');


        $configFile = Yii::app()->file->set('protected/modules/template/templatesConfig.php', true);
        $configFile->copy('protected/modules/' . $moduleName . '/' . $moduleName . 'Config.php');

        $configFile = Yii::app()->file->set('protected/modules/' . $moduleName . '/' . $moduleName . 'Config.php', true);

        $str = "<?php
\$moduleConfig = array(
	'title'=>'" . addslashes($title) . "',
	'moduleName'=>'" . addslashes($moduleName) . "',
	'recordName'=>'" . addslashes($recordName) . "',
);
?>";

        $configFile->setContents($str);
    }

    /**
     * Creates a table for a new module
     * 
     * This method is called by {@link AdminController::actionCreateModule} as part
     * of creating a new module.  This creates the table for the new module as well
     * as creating records in the x2_fields table for use in the studio.
     * 
     * @param string $moduleName The name of the module being created
     */
    private function createNewTable($moduleName) {
        $moduleTitle = ucfirst($moduleName);
        $sqlList = array("CREATE TABLE x2_" . $moduleName . "(
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
        foreach ($sqlList as $sql) {
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
    }

    /**
     * Create file system for a custom module
     * 
     * This method is called by {@link AdminController::actionCreateModule} as a
     * part of creating a new module.  This method copies all the proper files to
     * their new directories, renames them, and replaces the contents to fit the
     * new module name.
     * 
     * @param string $moduleName The name of the moduel being created
     */
    private function createSkeletonDirectories($moduleName) {

        $errors = array();


        $fileNames = array(
            'protected/modules/' . $moduleName . '/views/default/_search.php',
            'protected/modules/' . $moduleName . '/views/default/_view.php',
            'protected/modules/' . $moduleName . '/views/default/admin.php',
            'protected/modules/' . $moduleName . '/views/default/create.php',
            'protected/modules/' . $moduleName . '/views/default/index.php',
            'protected/modules/' . $moduleName . '/views/default/update.php',
            'protected/modules/' . $moduleName . '/views/default/view.php',
            'protected/modules/' . $moduleName . '/models/Templates.php',
            'protected/modules/' . $moduleName . '/controllers/DefaultController.php',
            'protected/modules/' . $moduleName . '/register.php',
            'protected/modules/' . $moduleName . '/TemplatesModule.php',
        );
        foreach ($fileNames as $fileName) {
            chmod($fileName,0755);
            $templateFile = Yii::app()->file->set($fileName);
            if (!$templateFile->exists) {
                $errors[] = Yii::t('module', 'Template file {filename} could not be found');
                break;
            } else {
                
                $contents = $templateFile->getContents();
                $contents = preg_replace('/templates/', $moduleName, $contents);   // write moduleName into view files
                $contents = preg_replace('/Templates/', ucfirst($moduleName), $contents);

                $newFileName = preg_replace('/templates/', $moduleName, $templateFile->filename);
                $newFileName = preg_replace('/template/', $moduleName, $templateFile->filename); // replace 'template' with
                $newFileName = preg_replace('/Templates/', ucfirst($moduleName), $newFileName); // module name for new files
                $templateFile->setFileName($newFileName);
                $templateFile->setContents($contents);
            }
        }
        //rename('protected/modules/' . $moduleName . '/views/templates',"protected/modules/$moduleName/views/$moduleName");
    }

    /**
     * Deletes a custom module.
     * 
     * This method deletes an admin created module from the system.  All files are 
     * deleted as well as the table associated with it. 
     */
    public function actionDeleteModule() {

        if (isset($_POST['name'])) {
            $moduleName = $_POST['name'];
            $module = Modules::model()->findByPk($moduleName);
            $moduleName=$module->name;
            if (isset($module)) {
                if ($module->name != 'document' && $module->delete()) {
                    $config = include('protected/modules/' . $moduleName . '/register.php');
                    $uninstall = $config['uninstall'];
					if (isset($config['version'])) {
						foreach ($uninstall as $sql) {
							// New convention:
							// If element is a string, treat as a path to an SQL script file.
							// Otherwise, if array, treat as a list of SQL commands to run.
							$sqlComm = $sql;
							if (is_string($sql)) {
								if (file_exists($sql)) {
									$sqlComm = explode('/*&*/', file_get_contents($sql));
								}
							}
							foreach ($sqlComm as $sqlLine) {
								$query = Yii::app()->db->createCommand($sql);
								$query->execute();
							}
						}
					} else {
						// The old way, for backwards compatibility:
						foreach ($uninstall as $sql) {
							$query = Yii::app()->db->createCommand($sql);
							$query->execute();
						}
					}
                    $fields = Fields::model()->findAllByAttributes(array('modelName' => $moduleName));
                    foreach ($fields as $field) {
                        $field->delete();
                    }
                    $auth=Yii::app()->authManager;
                    $auth->removeAuthItem(ucfirst($moduleName).'Index');
                    $auth->removeAuthItem(ucfirst($moduleName).'Admin');

                    $this->rrmdir('protected/modules/' . $moduleName);
                } else {
                    $module->delete();
                }
            }
            $this->redirect(array('admin/index'));
        }

        $arr = array();
        $modules = Modules::model()->findAllByAttributes(array('toggleable' => 1));
        foreach ($modules as $item) {
            $arr[$item->id] = $item->title;
        }

        $this->render('deleteModule', array(
            'modules' => $arr,
        ));
    }

    /**
     * Export a custom module.
     * 
     * This method creates a zip file from a custom module with all the proper 
     * files and SQL for installation required to set up the module again.  These 
     * zip files can be imported into other X2 installations. 
     */
    public function actionExportModule() {

        if (isset($_POST['name'])) {
            $moduleName = strtolower($_POST['name']);

            $fields = Fields::model()->findAllByAttributes(array('modelName' => ucfirst($moduleName)));
            $sql = "";

            $disallow = array(
                "id",
                "assignedTo",
                "name",
                "description",
                "createDate",
                "lastUpdated",
                "updatedBy",
            );
            foreach ($fields as $field) {
                if (array_search($field->fieldName, $disallow) === false) {
                    $fieldType = $field->type;
                    switch ($fieldType) {
                        case "boolean":
                            $fieldType = "BOOLEAN";
                            break;
                        case "float":
                            $fieldType = "FLOAT";
                            break;
                        case "int":
                            $fieldType = "BIGINT";
                            break;
                        case "text":
                            $fieldType = "TEXT";
                            break;
                        default:
                            $fieldType = 'VARCHAR(250)';
                            break;
                    }
                    $sql.="ALTER TABLE x2_$moduleName ADD COLUMN $field->fieldName $fieldType; INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', '$field->fieldName', '$field->attributeLabel', '1', '1');";
                }
            }

            $db = Yii::app()->file->set("protected/modules/$moduleName/sqlData.sql");
            $db->create();
            $db->setContents($sql);

            if (file_exists($moduleName . ".zip")) {
                unlink($moduleName . ".zip");
            }

            $zip = Yii::app()->zip;
            $zip->makeZip('protected/modules/' . $moduleName, $moduleName . ".zip");
            $finalFile = Yii::app()->file->set($moduleName . ".zip");
            $finalFile->download();
            $this->redirect('exportModule');
        }

        $arr = array();

        $modules = Modules::model()->findAll();
        foreach ($modules as $module) {
            if ($module->custom) {
                $arr[$module->name] = $module->title;
            }
        }

        $this->render('exportModules', array(
            'modules' => $arr,
        ));
    }

    /**
     * Import a zip of a module.
     * 
     * This method will allow the admin to import a zip file of an exported X2 
     * module. 
     */
    public function actionImportModule() {

        if (isset($_FILES['data'])) {

            $module = Yii::app()->file->set('data');
            $moduleName = $module->filename;

            $zip = Yii::app()->zip;
            $zip->extractZip("$moduleName.zip", 'protected/modules/');

            $admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
            if (isset($admin)) {
                if ($admin->menuOrder != "") {
                    $admin->menuOrder.=":" . ucfirst($moduleName);
                    $admin->menuVisibility.=":1";
                    $admin->menuNicknames.=":" . ucfirst($moduleName);
                } else {
                    $admin->menuOrder = ucfirst($moduleName);
                    $admin->menuVisibility = "1";
                    $admin->menuNicknames = ucfirst($moduleName);
                }
                $admin->save();
            }


            $this->redirect(array($moduleName . '/index'));
        }
        $this->render('importModule');
    }

    /**
     * DO NOT USE
     * 
     * Testing method used for a prototype system of managing modules in a more
     * modular fashion.  This is NOT ready for use and should not be accessed. 
     */
    public function actionRegisterModules() {

        $modules = scandir('protected/modules');
        $modules = array_combine($modules, $modules);
        $arr = array();
        foreach ($modules as $module) {
            if (file_exists("protected/modules/$module/register.php") && is_null(Modules::model()->findByAttributes(array('name' => $module)))) {
                $arr[] = ($module);
            }
        }
        $registeredModules = Modules::model()->findAll();

        $this->render('registerModules', array(
            'modules' => $arr,
            'registeredModules' => $registeredModules,
        ));
    }

    /**
     * DO NOT USE
     * 
     * Like {@link actionRegisterModules} this method is not yet ready for use.
     * Please refrain from attempting to use this module or it will likely create
     * issues in your installation.
     * 
     * @param string $module The name of the moduel being toggled.
     */
    public function actionToggleModule($module) {

        $config = include("protected/modules/$module/register.php");
        $exists = Modules::model()->findByAttributes(array('name' => $module));
        if (!isset($exists)) {
            $moduleRecord = new Modules;
            $moduleRecord->editable = $config['editable'] ? 1 : 0;
            $moduleRecord->searchable = $config['searchable'] ? 1 : 0;
            $moduleRecord->adminOnly = $config['adminOnly'] ? 1 : 0;
            $moduleRecord->custom = $config['custom'] ? 1 : 0;
            $moduleRecord->toggleable = $config['toggleable'] ? 1 : 0;
            $moduleRecord->name = $module;
            $moduleRecord->title = $config['name'];
            $moduleRecord->visible = 1;
            $moduleRecord->menuPosition = Modules::model()->count();

            if ($moduleRecord->save()) {
                $install = $config['install'];
            }
        } else {
            $exists->visible = $exists->visible ? 0 : 1;

            if ($exists->save()) {
                if ($exists->toggleable) {
                    $uninstall = $config['uninstall'];
                } else {
                    
                }
            }
        }
        $this->redirect('registerModules');
    }

    /**
     * X2Studio Form Editor
     * 
     * This method allows the admin to create and edit the form layouts for 
     * all editable modules within the software. 
     */
    public function actionEditor() {

        $layoutModel = null;
        $defaultView = false;
        $defaultForm = false;

        if (isset($_GET['id']) && !empty($_GET['id'])) {

            $id = $_GET['id'];
            $layoutModel = FormLayout::model()->findByPk($id);

            if (!isset($layoutModel))
                $this->redirect(array('editor'));

            $modelName = $layoutModel->model;

            if (isset($_POST['layout'])) {
                $layoutModel->layout = urldecode($_POST['layout']);
                $layoutModel->defaultView = isset($_POST['defaultView']) && $_POST['defaultView'] == 1;
                $layoutModel->defaultForm = isset($_POST['defaultForm']) && $_POST['defaultForm'] == 1;


                // if this is the default view, unset defaultView for all other forms
                if ($layoutModel->defaultView) {
                    $layouts = FormLayout::model()->findAllByAttributes(array('model' => $modelName, 'defaultView' => 1));
                    foreach ($layouts as &$layout) {
                        $layout->defaultView = false;
                        $layout->save();
                    }
                    unset($layout);
                }
                // if this is the default form, unset defaultForm for all other forms
                if ($layoutModel->defaultForm) {
                    $layouts = FormLayout::model()->findAllByAttributes(array('model' => $modelName, 'defaultForm' => 1));
                    foreach ($layouts as &$layout) {
                        $layout->defaultForm = false;
                        $layout->save();
                    }
                    unset($layout);
                }

                $layoutModel->save();
                $this->redirect(array('editor', 'id' => $id));
            }
        } else {
            $modelName = isset($_GET['model']) ? $_GET['model'] : '';
            $id = '';
        }

        $modules = Modules::model()->findAllByAttributes(array('editable' => 1));

        $modelList = array('' => '---');
        foreach ($modules as $module) {
            if ($module->name == 'marketing')
                $modelList['Campaign'] = 'Campaign';
            elseif ($module->name == 'opportunities')
                $modelList['Opportunity'] = 'Opportunity';
            else
                $modelList[ucfirst($module->name)] = $module->title;
        }

        $versionList = array('' => '---');
        if (!empty($modelName)) {
            $layouts = FormLayout::model()->findAllByAttributes(array('model' => $modelName));

            foreach ($layouts as &$layout)
                $versionList[$layout->id] = $layout->version . (($layout->defaultView || $layout->defaultForm) ? ' (' . Yii::t('admin', 'Default') . ')' : '');
            unset($layout);
        }

        $this->render('editor', array(
            'modelName' => $modelName,
            'id' => $id,
            'layoutModel' => $layoutModel,
            'modelList' => $modelList,
            'versionList' => $versionList,
            'defaultView' => isset($layoutModel->defaultView) ? $layoutModel->defaultView : false,
            'defaultForm' => isset($layoutModel->defaultForm) ? $layoutModel->defaultForm : false,
        ));
    }


    /**
     * Create form Layout
     * 
     * This method is called via AJAX from within {@link actionEditor} to create
     * new form layouts for use with the modules. 
     */
    public function actionCreateFormLayout() {
        if (isset($_GET['newLayout'], $_GET['model'], $_GET['layoutName'])) {
            // $currentLayouts = FormLayout::model()->findAllByAttributes(array('model'=>$_GET['model']));

            $newLayout = new FormLayout;

            if (isset($_POST['layout']))
                $newLayout->layout = urldecode($_POST['layout']);

            $newLayout->version = $_GET['layoutName'];
            $newLayout->model = $_GET['model'];
            $newLayout->createDate = time();
            $newLayout->lastUpdated = time();
            $newLayout->defaultView = false;
            $newLayout->defaultForm = false;
            $newLayout->save();
            $this->redirect(array('editor', 'id' => $newLayout->id));
        }
    }
    
    /**
     * Delete a form layout.
     * 
     * @param int $id The ID of the layout to be deleted.
     */
    public function actionDeleteFormLayout($id) {

        $layout = FormLayout::model()->findByPk($id);
        if (isset($layout)) {
            $modelName = $layout->model;
            $defaultView = $layout->defaultView;
            $defaultForm = $layout->defaultForm;
            $layout->delete();

            // if we just deleted the default, find the next layout and make it the default
            if ($defaultView) {
                $newDefaultView = FormLayout::model()->findByAttributes(array('model' => $modelName));
                if (isset($newDefaultView)) {
                    $newDefaultView->defaultView = true;
                    $newDefaultView->save();
                }
            }
            if ($defaultForm) {
                $newDefaultForm = FormLayout::model()->findByAttributes(array('model' => $modelName));
                if (isset($newDefaultForm)) {
                    $newDefaultForm->defaultForm = true;
                    $newDefaultForm->save();
                }
            }
            $this->redirect(array('editor', 'model' => $modelName));
        } else
            $this->redirect('editor');
    }

    /**
     * Landing page for admin created dropdowns
     * 
     * This method allows the admin to access the functions related to creating
     * and editing admin created dropdowns in the app.
     */
    public function actionManageDropDowns() {

        $dataProvider = new CActiveDataProvider('Dropdowns');
        $model = new Dropdowns;

        $dropdowns = $dataProvider->getData();
        foreach ($dropdowns as $dropdown) {
            $temp = json_decode($dropdown->options);
            $str = "";
            foreach ($temp as $item) {
                $str.=$item . ", ";
            }
            $str = substr($str, 0, -2);
            $dropdown->options = $str;
        }
        $dataProvider->setData($dropdowns);

        $this->render('manageDropDowns', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
            'dropdowns' => $dataProvider->getData(),
        ));
    }

    /**
     * Create a custom dropdown
     * 
     * This method allows the admin to create a custom dropdown to be used with
     * a module in conjunction with the form editor. 
     */
    public function actionDropDownEditor() {
        $model = new Dropdowns;

        if (isset($_POST['Dropdowns'])) {
            $model->attributes = $_POST['Dropdowns'];
            $temp = array();
            foreach ($model->options as $option) {
                if ($option != "")
                    $temp[$option] = $option;
            }
            if (count($temp) > 0) {
                $model->options = json_encode($temp);
                if ($model->save()) {
                    $this->redirect('manageDropDowns');
                }
            } else {
                $this->redirect('manageDropDowns');
            }
        }

        $this->render('dropDownEditor', array(
            'model' => $model,
        ));
    }

    /**
     * Delete a custom dropdown 
     */
    public function actionDeleteDropdown() {
        $dropdowns = Dropdowns::model()->findAll();

        if (isset($_POST['dropdown'])) {
            $model = Dropdowns::model()->findByPk($_POST['dropdown']);

            $model->delete();
            $this->redirect('manageDropDowns');
        }

        $this->render('deleteDropdowns', array(
            'dropdowns' => $dropdowns,
        ));
    }

    /**
     * Edit a previously created dropdown
     */
    public function actionEditDropdown() {
        $model = new Dropdowns;

        if (isset($_POST['Dropdowns'])) {
            $model = Dropdowns::model()->findByAttributes(array('name' => $_POST['Dropdowns']['name']));
            $model->attributes = $_POST['Dropdowns'];
            $temp = array();
            foreach ($model->options as $option) {
                if ($option != "")
                    $temp[$option] = $option;
            }
            $model->options = json_encode($temp);
            if ($model->save()) {
                $this->redirect('manageDropDowns');
            }
        }
        $this->render('editDropdowns');
    }

    /**
     * Print out a dropdown's data
     * 
     * This method is called via AJAX by {@link actionEditDropdown} to get the 
     * options of the dropdown for the edit dropdown page. 
     */
    public function actionGetDropdown() {
        if (isset($_POST['Dropdowns']['name'])) {
            $name = $_POST['Dropdowns']['name'];
            $model = Dropdowns::model()->findByAttributes(array('name' => $name));
            $str = "";

            $options = json_decode($model->options);
            foreach ($options as $option) {
                $str.="<li>
						<input type=\"text\" size=\"30\"  name=\"Dropdowns[options][]\" value='$option' />
						<div class=\"\">
							<a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\">[" . Yii::t('workflow', 'Up') . "]</a>
							<a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\">[" . Yii::t('workflow', 'Down') . "]</a>
							<a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[" . Yii::t('workflow', 'Del') . "]</a>
						</div>
						<br />
					</li>";
            }
            echo $str;
        }
    }

    /**
     * Echos a list of custom dropdowns
     * 
     * This method is called via AJAX on the field editor to get a list of dropdowns
     * or modules to be used for modifying the type of field.
     */
    public function actionGetFieldType() {
        if (isset($_POST['Fields']['type'])) {
            $type = $_POST['Fields']['type'];
            if ($type == "dropdown") {
                $dropdowns = Dropdowns::model()->findAll();
                $arr = array();
                foreach ($dropdowns as $dropdown) {
                    $arr[$dropdown->id] = $dropdown->name;
                }

                echo CHtml::dropDownList('dropdown', '', $arr);
            } elseif ($type == 'link') {
                $query = Yii::app()->db->createCommand()
                        ->select('modelName')
                        ->from('x2_fields')
                        ->group('modelName')
                        ->queryAll();
                    $arr=array();
                    foreach($query as $array){
                        if($array['modelName']!='Calendar')
                            $arr[$array['modelName']]=$array['modelName'];
                    }
                echo CHtml::dropDownList('dropdown', '', $arr);
            }
        }
    }

    /**
     * Export all data
     * 
     * This method is used to export all of the data from the software as a CSV
     */
    public function actionExport() {
        $this->globalExport();
        $this->render('export', array(
        ));
    }

    /**
     * Private method to handle the export
     * 
     * This method actually prepares all the data and the CSV for export before
     * rending the page with the download.
     */
    private function globalExport() {
        ini_set('memory_limit', -1);
        $file = 'data.csv';
        $fp = fopen($file, 'w+');

        $modules = Modules::model()->findAll();
        $pieces = array();
        foreach ($modules as $module) {
            $pieces[] = $module->name;
        }

        $tempArr = array();
        foreach ($pieces as $model) {
            if ($model == "quotes")
                $model = "quote";
            if ($model == "products")
                $model = "product";
            if ($model == 'marketing')
                $model = "campaign";
            if ($model == 'users')
                $model = 'user';
            if ($model!='reports' && $model != 'dashboard' && $model != 'charts' && $model != 'calendar' && is_null(Docs::model()->findByAttributes(array('title' => $model))))
                $tempArr[ucfirst($model)] = CActiveRecord::model(ucfirst($model))->findAll();
        }

        $tempArr['Profile'] = ProfileChild::model()->findAll();
        $labels = array();
        foreach ($tempArr as $model => $data) {
            $temp = CActiveRecord::model($model);
            $tempKeys = array_keys($temp->attributes);
            sort($tempKeys);
            $tempKeys[] = $model;
            $labels[$model] = $tempKeys;
        }

        fputcsv($fp, array(Yii::app()->params->version));

        $keys = array_keys($tempArr);
        for ($i = 0; $i < count($tempArr); $i++) {
            $meta = $labels[$keys[$i]];
            fputcsv($fp, $meta);
            foreach ($tempArr[$keys[$i]] as $data) {
                $tempAtr = $data->attributes;
                ksort($tempAtr);
                $tempAtr[] = $keys[$i];
                fputcsv($fp, $tempAtr);
            }
        }

        fclose($fp);
    }

    /** 
     * Import data from an export
     * 
     * This method allows for the import of data by the admin into the software.
     * The import is compatible with the format from an X2 export only at the moment,
     * so that CSV should be used as a template for how to format the data. 
     */
    public function actionImport() {
        if (isset($_FILES['data'])) {
            $overwrite = $_POST['overwrite'];
            $temp = CUploadedFile::getInstanceByName('data');
            $temp->saveAs('data.csv');
            $this->globalImport('data.csv', $overwrite);
        }
        $this->render('import');
    }

    /** 
     * Private method that actually performs the import
     * 
     * @param File $file The file of data to be imported
     * @param boolean $overwrite Whether or not to overwrite old records if there are duplicates, defaults to false
     */
    private function globalImport($file, $overwrite) {
        $fp = fopen($file, 'r+');
        $version = fgetcsv($fp);
        $version = $version[0];
        $type = "";
        $meta = array();
        while ($pieces = fgetcsv($fp)) {
            if ($pieces[count($pieces) - 1] != $type) {
                $type = $pieces[count($pieces) - 1];
                $meta = $pieces;
                continue;
            }
            if(class_exists($pieces[count($pieces) - 1]))
                $model=new $pieces[count($pieces) - 1];
            else
                continue;
            unset($pieces[count($pieces) - 1]);
            $tempMeta = $meta;
            unset($tempMeta[count($tempMeta) - 1]);
            $temp = array();
            for ($i = 0; $i < count($tempMeta); $i++) {
                $temp[$tempMeta[$i]] = $pieces[$i];
            }
            
            foreach ($temp as $field => $value) {
                if (isset($temp[$field]) && $model->hasAttribute($field)){
                    $model->$field = $temp[$field];
                }
            }
            $lookup = CActiveRecord::model(get_class($model))->findByPk($model->id);
            
            if (!isset($lookup)) {
                
                if($model->save()){
                     
                }else{
                    printR($model->getErrors(),true);
                }
                
            } else if ($overwrite) {
                $lookup->delete();
                $model->save();
            }
            unset($model);
        }
        unlink($file);
        $this->redirect('index');
    }

    /**
     * Runs the updater. It is in this action where the entire file is copied
     * from the remote update server.
     */
    public function actionUpdater() {
		if(!file_exists('protected/config/X2Config.php')) {
			// App is using old config files.
			include('protected/config/emailConfig.php');
		} else {
			include('protected/config/X2Config.php');
		}
		
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 15  // Timeout in seconds
            )
                ));
        if (!isset($updaterVersion)) {
            $updaterVersion = "";
        } else {
            
        }
		$url = 'x2planet';
		if (in_array(Yii::app()->params->admin['edition'],array('opensource',Null))) {
			if ($versionTest = @file_get_contents('http://x2base.com/updates/versionCheck.php', 0, $context)) {
				$url = 'x2base';
			} else if ($versionTest = @file_get_contents('http://x2planet.com/updates/versionCheck.php', 0, $context)) {
				$url = 'x2planet';
			}
		} else {
			$versionTest = @file_get_contents('http://x2planet.com/installs/updates/versionCheck');
		}

        $updaterCheck = file_get_contents("http://www.$url.com/updates/updateCheck.php");

        if ($updaterCheck != $updaterVersion) {
            $file = "protected/controllers/AdminController.php";
            $this->ccopy("http://$url.com/updates/x2engine/" . $file, $file);
            $file = "protected/views/admin/updater.php";
            $this->ccopy("http://$url.com/updates/x2engine/" . $file, $file);
            
			$config = "<?php\n";
			if (!isset($buildDate))
                $buildDate = time();
			$updaterVersion=$updaterCheck;
			$appName = Yii::app()->name;
			$email = Yii::app()->params->admin->emailFromAddr;
			$language = Yii::app()->language;
			foreach(array('appName','email','language','host','user','pass','dbname','version','updaterVersion') as $var)
				$config .= "\$$var='".${$var}."';\n";
			$config .= "\$buildDate = $buildDate;\n?>";
            file_put_contents('protected/config/X2Config.php', $config);
            $this->redirect('updater');
        }

	$admin = Yii::app()->params->admin;
	$unique_id = isset($admin->unique_id) ? $admin->unique_id : 'none';
	$contents = file_get_contents("http://www.$url.com/updates/update.php?version=$version&unique_id=$unique_id");
        $pieces = explode(";;", $contents);
        $newVersion = $pieces[3];
        $sqlList = $pieces[1];
        $changelog = $pieces[4];
        $deletionList = explode(':', $pieces[2]);
        if ($pieces[0] != "")
            $fileList = explode(":", $pieces[0]);
        else
            $fileList = array('');
        if ($sqlList != "")
            $sqlList = explode(":&", $sqlList);
        else
            $sqlList = array('');
        $this->saveBackup($fileList);
        $this->render('updater', array(
            'fileList' => $fileList,
            'sqlList' => $sqlList,
            'newVersion' => $newVersion,
            'changelog' => $changelog,
            'updaterVersion' => $updaterVersion,
            'updaterCheck' => $updaterCheck,
            'version' => $version,
            'versionTest' => $versionTest,
            'deletionList' => $deletionList,
            'url' => $url,
        ));
    }
	
    /**
     * Control settings for the updater
     * 
     * This method controls the update interval setting for the application.
     */
	public function actionUpdaterSettings() {
		
        $admin = &Yii::app()->params->admin;
        if (isset($_POST['Admin'])) {
			$admin->setAttributes($_POST['Admin']);
			foreach(array('unique_id','edition') as $var)
				if(isset($_POST['unique_id']))
					$admin->$var = $_POST[$var];
			if ($admin->save()) {
                $this->redirect('updaterSettings');
            }
		}
		$this->render('updaterSettings', array(
            'model' => $admin,
        ));
	}

    /**
     * Downloads files as a part of the updater. 
     */
    public function actionDownload() {
        if (isset($_GET['url']) && isset($_GET['file'])) {
            if (Yii::app()->request->isAjaxRequest) {
                $url = $_GET['url'];
                $file = $_GET['file'];
                if ($url == 'x2planet' || $url == 'x2base') {
                    $i = 0;
                    if ($file != "") {
                        while (!$this->ccopy("http://$url.com/updates/x2engine/" . $file, "temp/" . $file) && $i < 5) {
                            $i++;
                        }
                    }
                    if ($i == 5) {
                        $this->_sendResponse('500', 'Error copying file');
                    } else {
                        $this->_sendResponse('200', 'File copied successfully');
                    }
                } else {
                    $this->_sendResponse('501', 'Update server not implemented for URL provided.');
                }
            } else {
                $this->_sendResponse('400', 'Update requests must be made via AJAX.');
            }
        }
    }

    /**
     * Deletes files required for the update.
     */
    public function actionDelete() {
        if (isset($_POST['delete'])) {
            $file = $_POST['delete'];
            if (file_exists($file)) {
                if (unlink($file))
                    $this->_sendResponse('200', 'File deleted successfully');
                else
                    $this->_sendResponse('500', 'File deletion failed');
            }
        }
    }

    /**
     * @deprecated
     * Deprecated method to run SQL as part of the update. 
     */
    public function actionSql() {
        if (isset($_POST['sql'])) {
            $sql = $_POST['sql'];
            $command = Yii::app()->db->createCommand($sql);
            $result = $command->execute();
            $this->_sendResponse('200', 'SQL Executed Successfully');
        }
    }

    /**
     * Finalizes the update
     * 
     * This method replaces the SQL method as well as finishing copying files over.
     * Both of these happen at once to prevent issues from files depending on SQL
     * changes or vice versa. 
     */
    public function actionInstallUpdate() {

        $this->copyFile("temp");
        if (isset($_POST['sqlList'])) {
            $sql = $_POST['sqlList'];
            foreach ($sql as $query) {
                if ($query != "") {
                    $command = Yii::app()->db->createCommand($query);
                    $result = $command->execute();
                }
            }
        } else {
            $this->_sendResponse('500', 'Failure.');
        }
        $this->rrmdir("temp");
        $this->_sendResponse('200', 'SQL Executed Successfully');
    }

    /**
     * Wrapper for {@link ccopy}
     * 
     * Recursively copyies a directory if the specified.
     * @param string $file The starting point, whether file or directory.
     */
    protected function copyFile($file) {
        if (file_exists($file)) {
            if (is_dir($file)) {
                $objects = scandir($file);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        $this->copyFile($file . "/" . $object);
                    }
                }
            } else {
                $this->ccopy("$file", substr($file, 5));
            }
        }
    }

    /**
     * Perform all post-update tasks. 
     * 
     * Writes new data to protected/config/X2Config.php, removes the backup
     * files, and returns a message that the update succeeded.
     */
    public function actionCleanUp() {
        include('protected/config/X2Config.php');
        if (isset($_POST['status'])) {
            $status = $_POST['status'];
            if ($status == 'error') {
                $this->restoreBackup(json_decode($_POST['fileList'], true));
                echo "Update failed.  Please try again or contact X2Engine.";
            } else {
                $url = $_POST['url'];
                $updaterCheck = file_get_contents("http://www.$url.com/updates/updateCheck.php");
                $version = $_POST['version'];
                
			$config = "<?php\n";
			if (!isset($buildDate))
                $buildDate = time();
			$updaterVersion='$updaterCheck';
			$appName = Yii::app()->name;
			$email = Yii::app()->params->admin->emailFromAddr;
			$language = Yii::app()->language;
			foreach(array('appName','email','language','host','user','pass','dbname','version','updaterVersion','buildDate') as $var)
				$config .= "\$$var='".${$var}."';\n";
			$config .= "?>";
            file_put_contents('protected/config/X2Config.php', $config);
            $this->redirect('updater');
                file_put_contents('protected/config/X2Config.php', $config);
                echo "Update succeeded!";
            }
        }
        if (is_dir('backup'))
            $this->rrmdir('backup');
        $assets = scandir('assets');
        foreach ($assets as $folder) {
            if ($folder != ".." && $folder != ".")
                $this->rrmdir($folder);
        }
    }

    /**
     * Copies a file. 
     * 
     * If the local filesystem directory to where the file will be copied does 
     * not exist yet, it will be created automatically.
     * 
     * @param string $filepath The source file
     * @param strint $file The destination path.
     * @return boolean 
     */
    function ccopy($filepath, $file) {

        $pieces = explode('/', $file);
        unset($pieces[count($pieces)]);
        for ($i = 0; $i < count($pieces); $i++) {
            $str = "";
            for ($j = 0; $j < $i; $j++) {
                $str.=$pieces[$j] . '/';
            }

            if (!is_dir($str) && $str != "") {
                mkdir($str);
            }
        }
        return copy($filepath, $file);
    }

    /**
     * Recursively removes a directory.
     * @param string $dir 
     */
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->rrmdir($dir . "/" . $object); else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Saves a backup copy of a list of files.
     * @param array $fileList 
     */
    function saveBackup($fileList) {
        if (!is_dir('backup'))
            mkdir('backup');
        foreach ($fileList as $file) {
            if ($file != "" && file_exists($file)) {
                $this->ccopy($file, 'backup/' . $file);
            }
        }
    }

    /**
     * Restores a backup copy of a list of files.
     * @param array $fileList 
     */
    function restoreBackup($fileList) {
        foreach ($fileList as $file) {
            if ($file != "" && file_exists($file)) {
                copy('backup/' . $file, $file);
            }
        }
    }

    /**
     * Respond to a request with a specified status code and body.
     * 
     * @param integer $status The HTTP status code.
     * @param string $body The body of the response message
     * @param string $content_type The response mimetype.
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
            exit;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
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

    /**
     * Obtain an appropriate message for a given HTTP response code.
     * 
     * @param integer $status
     * @return string 
     */
    private function _getStatusCodeMessage($status) {
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

    /**
     * View the changelogs.
     */
    public function actionViewLogs() {
        $this->render('viewLogs');
    }
    
    /**
     * Improved version of array_search that allows for regex searching
     * 
     * @param string $find Regex to search on
     * @param array $in_array An array to search in
     * @param array $keys_found An array of keys which meet the regex
     * @return type Returns the an array of keys if $in_array is valid, or false if not. 
     */
    function Array_Search_Preg( $find, $in_array, $keys_found=Array() ) 
    { 
        if( is_array( $in_array ) ) 
        { 
            foreach( $in_array as $key=> $val ) 
            { 
                if( is_array( $val ) ) $this->Array_Search_Preg( $find, $val, $keys_found ); 
                else 
                { 
                    if( preg_match( '/'. $find .'/', $val ) ) $keys_found[] = $key; 
                } 
            } 
            return $keys_found; 
        } 
        return false; 
    } 
    
    /**
     * Takes a Camel Cased string and echoes it as separate words.
     * @param string $str The string to convert
     * @return string A de-camelcased version of the string
     */
    function deCamelCase($str){
        $str=preg_replace("/(([a-z])([A-Z])|([A-Z])([A-Z][a-z]))/","\\2\\4 \\3\\5",$str);
        return ucfirst($str);
    }
}
