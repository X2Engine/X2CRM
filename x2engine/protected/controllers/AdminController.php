<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Administrative, app-wide configuration actions.
 *
 * Note: when running {@link actionUpdater}, if a new version is available on the
 * remote updates server, this file will be overwritten by the new file, which
 * is downloaded upon running the action (route admin/updater).
 *
 * @package X2CRM.controllers
 * @property boolean $noRemoteAccess (Read-only) true indicates there's no way to automatically retrieve files.
 */
class AdminController extends Controller {

    public $portlets = array();
    public $layout = '//layouts/column1';

	/**
	 * Behavior classes used by AdminController
	 * @var array
	 */
	public static $behaviorClasses = array('LeadRoutingBehavior', 'UpdaterBehavior','CommonControllerBehavior');

	/**
	 * Extraneous properties for individual behaviors
	 * @var array
	 */
	public static $behaviorProperties = array('UpdaterBehavior'=>array('isConsole'=>false));

	/**
	 * Miscellaneous component classes that the controller (or its behaviors)
	 * depend on, but that aren't directly used by it as behaviors.
	 * @var type
	 */
	public static $dependencies = array('FileUtil','ResponseBehavior');

	/**
	 * Stores value of {@link $noRemoteAccess}
	 * @var boolean
	 */
	private $_noRemoteAccess;

    /**
     * A list of actions to include.
     *
     * This method specifies which actions are defined elsewhere but used here.
     * These actions are pro code that are included in the pro version of the software.
     *
     * @return array An array of actions to include.
     */
    public function actions() {
		return array(
			'getRoleAccess' => array(
				'class' => 'GetRoleAccessAction',
			),
			'editRoleAccess' => array(
				'class' => 'EditRoleAccessAction',
			),
			'emailDropboxSettings' => array(
				'class' => 'EmailDropboxSettingsAction'
			),
		);
	}

    public function actionCalculateTranslationRedundancy(){
        $files=scandir('protected/messages/template');
        $languageList=array();
        foreach($files as $file){
            if($file!='.' && $file!='..')
                $languageList[$file]=array_keys(include("protected/messages/template/$file"));
        }
        $keys=array_keys($languageList);
        for($i=0;$i<count($languageList);$i++){
            for($j=$i+1;$j<count($languageList);$j++){
                $intersect=array_intersect($languageList[$keys[$i]],$languageList[$keys[$j]]);
                $unique=(count($languageList[$keys[$i]])-count($intersect)) + (count($languageList[$keys[$j]])-count($intersect));
                echo "For ".$keys[$i]." to ".$keys[$j]." ".round(count($intersect)/$unique*100,2)."% items identical.<br />";
            }
        }
    }

    public function actionFindMissingPermissions(){
        $controllers=array(
            'AdminController'=>'application.controllers.AdminController',
            'StudioController'=>'application.controllers.StudioController',
            'AccountsController'=>'application.modules.accounts.controllers.AccountsController',
            'ActionsController'=>'application.modules.actions.controllers.ActionsController',
            'CalendarController'=>'application.modules.calendar.controllers.CalendarController',
            'ChartsController'=>'application.modules.charts.controllers.ChartsController',
            'ContactsController'=>'application.modules.contacts.controllers.ContactsController',
            'DocsController'=>'application.modules.docs.controllers.DocsController',
            'GroupsController'=>'application.modules.groups.controllers.GroupsController',
            'MarketingController'=>'application.modules.marketing.controllers.MarketingController',
            'WeblistController'=>'application.modules.marketing.controllers.WeblistController',
            'MediaController'=>'application.modules.media.controllers.MediaController',
            'OpportunitiesController'=>'application.modules.opportunities.controllers.OpportunitiesController',
            'ProductsController'=>'application.modules.products.controllers.ProductsController',
            'QuotesController'=>'application.modules.quotes.controllers.QuotesController',
            'ReportsController'=>'application.modules.reports.controllers.ReportsController',
            'ServicesController'=>'application.modules.services.controllers.ServicesController',
            'UsersController'=>'application.modules.users.controllers.UsersController',
            'WorkflowController'=>'application.modules.workflow.controllers.WorkflowController',
            'BugReportsController'=>'application.modules.bugReports.controllers.BugReportsController',
        );
        $missingPermissions=array();
        $auth=Yii::app()->authManager;
        foreach($controllers as $class=>$controller){
            Yii::import($controller);
            $methods=get_class_methods($class);
            $arr=explode('Controller',$class);
            $name=$arr[0];
            if(is_array($methods)){
                foreach($methods as $method){
                    if(strpos($method,'action')===0 && $method!='actions'){
                        $method=$name.substr($method,6);
                        $authItem = $auth->getAuthItem($method);
                        if(is_null($authItem))
                            $missingPermissions[]=$method;
                    }
                }
            }
        }
        printR($missingPermissions);
    }

    /**
     * View the main admin menu
     */
    public function actionIndex() {
        if (isset($_GET['translateMode']))
            Yii::app()->session['translate'] = $_GET['translateMode'] == 1;
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
    protected function beforeAction($action = null) {
        $auth = Yii::app()->authManager;
        $action = ucfirst($this->getId()) . ucfirst($this->getAction()->getId());
        $authItem = $auth->getAuthItem($action);
        if (Yii::app()->user->checkAccess($action) || is_null($authItem) || Yii::app()->user->checkAccess('AdminIndex')) {
            return true;
        } elseif (Yii::app()->user->isGuest) {
			Yii::app()->user->returnUrl = Yii::app()->request->requestUri;
            $this->redirect($this->createUrl('/site/login'));
        } else {
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

    public function actionHowTo($guide) {
        if ($guide == 'gii')
            $this->render('howToGii');
        else if ($guide == 'model')
            $this->render('howToModel');
        else
            $this->redirect('index');
    }*/

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
            'clearCache',
			//'clearAuthCache'
        );
    }

    /**
     * A list of behaviors for the controller to use.
	 *
	 * It will download missing files (including classes that aren't behaviors)
	 * if any that are defined in {@link $behaviorClasses} are missing from the
	 * local filesystem.
     *
     * {@link LeadRoutingBehavior} is used to consolidate code for lead routing rules.
     * As such, it has been moved to an external file.  This file includes LeadRoutingBehavior
     * or downloads it if the file does not currently exist.  See also Yii documentation
     * for more information on behaviors.
	 * {@link UpdaterBehavior} is a centralized, re-usable behavior class for code
	 * pertaining to the updater that is agnostic to whether the update is being
	 * performed inside of a web request.
	 * {@link CommonControllerBehavior} is for methods shared between x2base and Admin controller
     *
     * @return array An array of behaviors to implement.
     */
    public function behaviors() {
		$missingClasses = array();
		$behaviors = array();
		$maxTries = 3;
		$GithubUrl = 'https://raw.github.com/X2Engine/X2Engine/master/x2engine/protected';
		$x2planUrl = 'http://x2planet.com/updates/x2engine/protected';
		$files = array_merge( array_fill_keys( self::$behaviorClasses, 'behavior'), array_fill_keys(self::$dependencies,'dependency'));
		$tryCurl = in_array(ini_get('allow_url_fopen'),array(0,'Off','off'));
		foreach ($files as $class=>$type) {
			// First try to download from the X2Engine update server...
			$path = "components/$class.php";
			$absPath = Yii::app()->basePath . "/$path";
			if (!file_exists($absPath)) {
				if(!is_dir(dirname($absPath)))
					throw new CHttpException(500,'The components folder is missing on the webserver! This is very bad. How is this even possible?');
				$i = 0;
				while (!$this->copyRemote("$x2planUrl/$path", $absPath, $tryCurl) && $i < $maxTries) {
					$i++;
				}
				// Try to download the file from Github...
				if ($i >= $maxTries) {
					$i = 0;
					while (!$this->copyRemote("$GithubUrl/$path", $path, $tryCurl) && $i < $maxTries) {
						$i++;
					}
				}
				// Mark the file as a failed download.
				if ($i >= $maxTries) {
					$missingClasses[] = "protected/$path";
				}
			}
			if ($type == 'behavior') {
				$behaviors[$class] = array(
					'class' => $class
				);
			}
		}

		// Display error.
		// Test:
		// $missingClasses[] = 'FOO';
		if(count($missingClasses))
			$this->missingClassesException($missingClasses);

		// Add extraneous behavior properties:
		foreach (self::$behaviorProperties as $class => $properties) {
			foreach ($properties as $name => $value) {
				$behaviors[$class][$name] = $value;
			}
		}
        return $behaviors;
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
        /* return array(
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
          ); */
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
		if(isset($cache))
			$cache->flush();
		$filterChain->run();
	}

	/**
	 * A filter to clear the authItem cache.
	 * @param type $filterChain The filter chain Yii is currently acting on.
	 */
	public function filterClearAuthCache($filterChain) {
		// Check for existence of authCache object (for backwards compatibility)
        if(!is_null(Yii::app()->db->getSchema()->getTable('x2_auth_cache'))){
            if(Yii::app()->hasComponent('authCache')) {
                $authCache = Yii::app()->authCache;
                if (isset($authCache))
                    $authCache->clear();
            }
        }
		$filterChain->run();

	}

    /**
     * @deprecated
     * Deprecated function for mass emailing contacts.
     *
     * This method used to render a page to search for contacts to send out a
     * mass mailing list to. The Marketing module has replaced this functionality
     * and is significantly more useful.

    public function actionSearchContact() {
        $this->render('searchContactInfo');
    }*/

    /**
     * @deprecated
     * Deprecated method to render a list of contacts meeting the search criteria of the previous method.
     *
     * This method would be accessed when the {@link AdminController::actionSearchContact}
     * action had data posted in the form on the page.  It would

    public function actionSendEmail() {
        $criteria = $_POST['searchTerm'];

        $mailingList = Contacts::getMailingList($criteria);

        $this->render('sendEmail', array(
            'criteria' => $criteria,
            'mailingList' => $mailingList,
        ));
    }*/

    /**
     * @deprecated
     * Deprecated method to actually send mass emails.
     *
     * This method links with the previous two deprecated methods to send out emails
     * after a contact list has been made and confirmed.  It has been replaced
     * by the Marketing module.

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
    }*/

    public function actionManageTags(){
        $dataProvider=new CActiveDataProvider('Tags',array(
            'criteria'=>array(
                'group'=>'tag'
            ),
            'pagination'=>array(
                'pageSize'=>isset($pageSize)? $pageSize : ProfileChild::getResultsPerPage(),
            ),
        ));

        $this->render('manageTags',array(
            'dataProvider'=>$dataProvider,
        ));
    }

    public function actionDeleteTag($tag){
        if(!empty($tag)){
            if($tag!='all'){
                $tag="#".$tag;
                X2Model::model('Tags')->deleteAllByAttributes(array('tag'=>$tag));
            }else{
                X2Model::model('Tags')->deleteAll();
            }
        }
        $this->redirect('manageTags');
    }

    public function actionManageSessions() {
        $dataProvider = new CActiveDataProvider('Session');

        $this->render('manageSessions', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionToggleSession($id) {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $session = Session::model()->findByPk($id);
            if (isset($session)) {
                $session->status = !$session->status;
                $ret=$session->status;
                if($session->save()){
                    echo $ret;
                }
            }
        }
    }

    public function actionEndSession($id) {
        echo Session::model()->deleteByPk($id);
    }

    public function actionViewSessionLog(){
        $sessionLog=new CActiveDataProvider('SessionLog',array(
           'sort' => array(
                        'defaultOrder' => 'timestamp DESC',
                    ),
            'pagination'=>array(
                'pageSize'=>Profile::getResultsPerPage()
            )
        ));
        $this->render('viewSessionLog',array(
            'dataProvider'=>$sessionLog,
        ));
    }

    public function actionViewSessionHistory($id){
        $sessions=X2Model::model('SessionLog')->findAllByAttributes(array('sessionId'=>$id));
        $firstTimestamp=0;
        $lastTimestamp=0;
        $str="<table class='items'><thead><tr><th>User</th><th>Status</th><th>Timestamp</th></tr></thead>";
        foreach($sessions as $session){
            $str.="<tr>";
            $str.="<td>".User::getUserLinks($session->user)."</td>";
            $str.="<td>".SessionLog::parseStatus($session->status)."</td>";
            $str.="<td>".Formatter::formatCompleteDate($session->timestamp)."</td>";
            $str.="</tr>";
        }
        $str.="</table>";
        echo $str;
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
        if ($assignee == "Anyone")
            $assignee = "";
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
            if(!isset($_POST['viewPermissions']))
                $viewPermissions=array();
            else
                $viewPermissions = $_POST['viewPermissions'];
            if(!isset($_POST['editPermissions']))
                $editPermissions=array();
            else
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
        $auth = Yii::app()->authManager;
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
            if(!isset($_POST['viewPermissions']))
                $viewPermissions=array();
            else
                $viewPermissions = $_POST['viewPermissions'];
            if(!isset($_POST['editPermissions']))
                $editPermissions=array();
            else
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
            if (isset($workflow) && !empty($workflow))
                $workflowName = Workflow::model()->findByPk($workflow)->name;
            else
                $this->redirect('manageRoles');
            $stage = $_POST['workflowStages'];
            if (isset($stage) && !empty($stage))
                $stageName = X2Model::model('WorkflowStage')->findByAttributes(array('workflowId'=>$workflow,'stageNumber'=>$stage))->name;
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

    public function actionToggleUpdater() {
        $this->redirect('updaterSettings');
    }*/

    /**
     * @deprecated
     * A deprecated method for contacting X2Engine Inc.
     *
     * This method has been replaced with a form on our website, and is no longer
     * linked to anywhere on the application.  If you wish to get in contact with us,
     * please visit www.x2engine.com

    public function actionContactUs() {

        if (isset($_POST['email'])) {
            $email = $_POST['email'];
            $subject = $_POST['subject'];
            $body = $_POST['body'];

            mail('contact@x2engine.com', $subject, $body, "From: $email");
            $this->redirect('index');
        }

        $this->render('contactUs');
    }*/

    /**
     * Render the changelog.
     *
     * This action renders the user changelog page, which contains a list of all
     * changed made by users within the app.
     */
    public function actionViewChangelog() {

        $model = new Changelog('search');
        if(isset($_GET['Changelog'])){
            foreach($_GET['Changelog'] as $field=>$value){
                if($model->hasAttribute($field)){
                    $model->$field=$value;
                }
            }
        }
        $this->render('viewChangelog', array(
            'model' => $model,
        ));
    }

    public function actionClearChangelog() {
        Changelog::model()->deleteAll();
        $this->redirect('viewChangelog');
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
        unset($users['Anyone']);
        $criteria->users=Yii::app()->user->getName();
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

        if (isset($type)) {
            foreach (X2Model::model('Fields')->findAllByAttributes(array('modelName' => $type)) as $field) {

                if (isset($_POST['Criteria']))
                    $data[$field->fieldName] = $field->attributeLabel;
                else
                    $data[$field->id] = $field->attributeLabel;
            }
        }
        $htmlOptions = array();
        echo CHtml::listOptions('', $data, $htmlOptions);
    }

    /**
     * @deprecated
     * Deprecated function to set user timeout.
     *
     * This method formerly controlled the user session timeout settings for the
     * software.  This setting is now controlled by the "General Settings" page.

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
    }*/

    /**
     * @deprecated
     * Deprecated method to set chat polling
     *
     * This method formerly controlled the configuration of chat polling requests.
     * This timeout is now set by the "General Settings" page.

    public function actionSetChatPoll() {

        $admin = &Yii::app()->params->admin; //X2Model::model('Admin')->findByPk(1);
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
    }*/

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
            $oldFormat=$admin->contactNameFormat;
            $admin->attributes = $_POST['Admin'];
            foreach($_POST['Admin'] as $attribute=>$value){
                if($admin->hasAttribute($attribute)){
                    $admin->$attribute=$value;
                }
            }
			if(isset($_POST['currency'])) {
				if($_POST['currency'] == 'other') {
					$admin->currency = $_POST['currency2'];
					if(empty($admin->currency))
						$admin->addError('currency',Yii::t('admin','Please enter a valid currency type.'));
				} else
					$admin->currency = $_POST['currency'];
			}
            if($oldFormat!=$admin->contactNameFormat){
                if($admin->contactNameFormat=='lastName, firstName'){
                    $command=Yii::app()->db->createCommand()->setText('UPDATE x2_contacts SET name=CONCAT(lastName,", ",firstName)')->execute();
                }elseif($admin->contactNameFormat=='firstName lastName'){
                    $command=Yii::app()->db->createCommand()->setText('UPDATE x2_contacts SET name=CONCAT(firstName," ",lastName)')->execute();
                }
            }
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

    public function actionActivitySettings() {

        $admin = &Yii::app()->params->admin;
        $admin->eventDeletionTypes=json_decode($admin->eventDeletionTypes,true);
        if (isset($_POST['Admin'])) {

            $admin->eventDeletionTime = $_POST['Admin']['eventDeletionTime'];
            $admin->eventDeletionTypes=json_encode($_POST['Admin']['eventDeletionTypes']);
            if ($admin->save()) {
                $this->redirect('activitySettings');
            }
        }
        $this->render('activitySettings', array(
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
            if ($routing == 'singleUser') {
                $user = $_POST['Admin']['rrId'];
                $admin->rrId = $user;
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
     * Sets the service routing type.
     *
     * This method allows for the admin to configure which option to use for service case
     * distribution.  This is what determines the actions of {@link ServiceRoutingBehavior}.
     */
    public function actionSetServiceRouting() {

        $admin = &Yii::app()->params->admin; //Admin::model()->findByPk(1);
        if (isset($_POST['Admin'])) {
            $routing = $_POST['Admin']['serviceDistribution'];
            $online = $_POST['Admin']['serviceOnlineOnly'];
            if ($routing == 'singleUser') {
                $user = $_POST['Admin']['srrId'];
                $admin->srrId = $user;
            } else if($routing == 'singleGroup') {
                $group = $_POST['Admin']['sgrrId'];
                $admin->sgrrId = $group;
            }

            $admin->serviceDistribution = $routing;
            $admin->serviceOnlineOnly = $online;

            if ($admin->save()) {
                $this->redirect('index');
            }
        }

        $this->render('serviceRouting', array(
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

        $admin = &Yii::app()->params->admin; //X2Model::model('Admin')->findByPk(1);
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
            $model->modelName=X2Model::getModelName($model->modelName);
			if(strpos('c_',$model->fieldName) !== 0)
				// This is a safeguard against fields that end up having
				// identical names to fields added later in updates.
				$model->fieldName = "c_{$model->fieldName}";

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
                case "dateTime":
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
            $tableName = X2Model::model($model->modelName)->tableName();
            $field=$model->fieldName;
            if (preg_match("/\s/", $field)) {

            } else {
                if ($model->save()) {
                    $sql = "ALTER TABLE $tableName ADD COLUMN $field $fieldType";
                    $command = Yii::app()->db->createCommand($sql);
                    try{
                        $result = $command->query();
                    }catch(CDbException $e){
                        $model->delete();
                    }

                }
            }
            $this->redirect('manageFields');
        }
    }

    public function actionValidateField($fieldName, $modelName){
        function in_arrayi($needle, $haystack) {
            return in_array(strtolower($needle), array_map('strtolower', $haystack));
        }
        $reservedWords = array_merge(require('protected/data/mysqlReservedWords.php'),require('protected/data/modelReservedWords.php'));

        if(in_arrayi($fieldName,$reservedWords)){
            echo Yii::t('admin','This field is a MySQL reserved word.  Choose a different field name.');
        }elseif(preg_match('/\W/', $fieldName) || preg_match('/^[^a-zA-Z]+/', $fieldName)){
            echo Yii::t('admin','Field names can only contain alphanumeric characters.');
        }else{
            $field=X2Model::model('Fields')->findByAttributes(array('modelName'=>$modelName,'fieldName'=>$fieldName));
            if(isset($field)){
                echo Yii::t('admin',"That model & field name combination is already in use.");
            }else{
                echo "0";
            }
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
            $tableName = X2Model::model($field->modelName)->tableName();
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
            $fieldModel = X2Model::model('Fields')->findByPk($_POST['Fields']['id']);
            $oldType = $fieldModel->type;
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
            if ($fieldType != $oldType) {
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
                    case "dateTime":
                        $fieldType = "BIGINT";
                        break;
                    case "currency":
                        $fieldType = "FLOAT";
                        break;
                    default:
                        $fieldType = 'VARCHAR(250)';
                        break;
                }
            }
            $tableName = X2Model::model($fieldModel->modelName)->tableName();
            $fieldModel->modified = 1;
            $fieldName=$fieldModel->fieldName;
            (isset($_POST['Fields']['required']) && $_POST['Fields']['required'] == 1) ? $fieldModel->required = 1 : $fieldModel->required = 0;
            (isset($_POST['Fields']['searchable']) && $_POST['Fields']['searchable'] == 1) ? $fieldModel->searchable = 1 : $fieldModel->searchable = 0;
            if ($fieldModel->save()) {
                if ($fieldType != $oldType) {
                    $sql = "ALTER TABLE $tableName MODIFY COLUMN $fieldName $fieldType";
                    $command = Yii::app()->db->createCommand($sql);
                    $result = $command->query();
                }
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
            $fieldModel = X2Model::model('Fields')->findByPk($_POST['Fields']['id']);
            $temparr = $fieldModel->attributes;
            if (!empty($fieldModel->linkType)) {
                $type = $fieldModel->type;
                if ($type == 'link') {
                    $query = Yii::app()->db->createCommand()
                            ->select('modelName')
                            ->from('x2_fields')
                            ->group('modelName')
                            ->queryAll();
                    $arr = array();
                    foreach ($query as $array) {
                        if ($array['modelName'] != 'Calendar')
                            $arr[$array['modelName']] = $array['modelName'];
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

        $model = new Docs;
        $users = User::getNames();
        if (isset($_POST['Docs'])) {

            $model->attributes = $_POST['Docs'];
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
            $module->title = $model->name;

            if ($module->save()) {

                if ($model->save()) {
                    $this->redirect(array('/docs/' . $model->id . '?static=true'));
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
        $model = CActiveRecord::model('Docs')->findByPk($id);
        if(!isset($model))
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
            if ($module->name != 'users') {
                if ($module->name != 'document')
                    $menuItems[$module->name] = $module->title;
                else
                    $menuItems[$module->title] = $module->title;
                if ($module->visible) {
                    $selectedItems[] = ($module->name != 'document') ? $module->name : $module->title;
                }
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
                } else {
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
                } else {
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
            $admin = ProfileChild::model()->findByPk(1);
            $logo = Media::model()->findByAttributes(array('associationId' => $admin->id, 'associationType' => 'logo'));
            if (isset($logo)) {
                if(file_exists($logo->fileName))
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

        $adminProf = ProfileChild::model()->findByPk(1);
        $logo = Media::model()->findByAttributes(array('associationId' => $adminProf->id, 'associationType' => 'logo'));
        if (!isset($logo)) {

            $logo = new Media;
            $logo->associationType = 'logo';
            $name = 'yourlogohere.png';
            $logo->associationId = $adminProf->id;
            $logo->fileName = 'uploads/logos/' . $name;

            if ($logo->save()) {

            }
        } else if($logo->fileName!='uploads/logos/yourlogohere.png') {
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
        include('protected/components/TranslationManager.php');
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

		if(isset($_POST['moduleName'])) {

			$title = trim($_POST['title']);
			$recordName = trim($_POST['recordName']);

			$moduleName = trim($_POST['moduleName']);

			if(preg_match('/\W/', $moduleName) || preg_match('/^[^a-zA-Z]+/', $moduleName))   // are there any non-alphanumeric or _ chars?
				$errors[] = Yii::t('module', 'Invalid table name'); //$this->redirect('createModule');									// or non-alpha characters at the beginning?

			if($moduleName == '')  // we will attempt to use the title
				$moduleName = $title; // as the backend name, if possible

			if($recordName == '')  // use title for record name
				$recordName = $title; // if none is provided

			$trans = include('protected/data/transliteration.php');

			$moduleName = strtolower(strtr($moduleName,$trans));  // replace characters with their A-Z equivalent, if possible

			$moduleName = preg_replace('/\W/', '', $moduleName); // now remove all remaining non-alphanumeric or _ chars

			$moduleName = preg_replace('/^[0-9_]+/', '', $moduleName); // remove any numbers or _ from the beginning


			if($moduleName == '')        // if there is nothing left of moduleName at this point,
				$moduleName = 'module' . substr(time(), 5);  // just generate a random one


			if(!is_null(Modules::model()->findByAttributes(array('title' => $title))) || !is_null(Modules::model()->findByAttributes(array('name' => $moduleName))))
				$errors[] = Yii::t('module', 'A module with that name already exists');
			if(empty($errors)) {
				try {
					$this->createSkeletonDirectories($moduleName);
					$this->writeConfig($title,$moduleName,$recordName);
					$this->createNewTable($moduleName);
				} catch(Exception $e) {
					die($e->getMessage());
				}

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
				$auth = Yii::app()->authManager;
				$auth->createOperation(ucfirst($moduleName) . 'Index');
				$auth->addItemChild('DefaultRole', ucfirst($moduleName) . 'Index');
				$auth->createOperation(ucfirst($moduleName) . 'Admin');
				$auth->addItemChild('administrator', ucfirst($moduleName) . 'Admin');
				$this->redirect(array('/' . $moduleName . '/index'));
			}
		}

		$this->render('createModule', array('errors' => $errors));
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
			) COLLATE = utf8_general_ci",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom) VALUES ('$moduleTitle', 'id', 'ID', '0')",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'name', 'Name', '0', 'varchar')",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'assignedTo', 'Assigned To', '0', 'assignment')",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'description', 'Description', '0', 'text')",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'createDate', 'Create Date', '0', 'date')",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'lastUpdated', 'Last Updated', '0', 'date')",
			"INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'updatedBy', 'Updated By', '0', 'assignment')");
		foreach($sqlList as $sql) {
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

		$templateFolderPath = 'protected/modules/template/';
		$moduleFolderPath = 'protected/modules/'.$moduleName.'/';

		$moduleFolder = Yii::app()->file->set($moduleFolderPath);
		if(!$moduleFolder->exists && $moduleFolder->createDir() === false)
			throw new Exception('Error creating module folder "'.$moduleFolderPath.'".');

		if(Yii::app()->file->set($templateFolderPath)->copy($moduleName) === false)
			throw new Exception('Error copying Template folder "'.$templateFolderPath.'".');

		// list of files to process
		$fileNames = array(
			'register.php',
			'templatesConfig.php',
			'TemplatesModule.php',
			'controllers/DefaultController.php',
			'data/install.sql',
			'data/uninstall.sql',
			'models/Templates.php',
			'views/default/_search.php',
			'views/default/_view.php',
			'views/default/admin.php',
			'views/default/create.php',
			'views/default/index.php',
			'views/default/update.php',
			'views/default/view.php',
		);

		foreach($fileNames as $fileName) {
			// calculate proper file name
			$fileName = $moduleFolderPath . $fileName;

			$file = Yii::app()->file->set($fileName);
			if(!$file->exists)
				throw new Exception('Unable to find template file "'.$fileName.'".');

			// rename files
			$newFileName = str_replace(array('templates','Templates'),array($moduleName,ucfirst($moduleName)),$file->filename);
			if($file->setFileName($newFileName) === false)
				throw new Exception('Error renaming template file "'.$fileName.'" to "'.$newFileName.'".');

			// chmod($file->filename, 0755);
			// $file->setPermissions(0755);

			// replace "template", "Templates", etc within the file
			$contents = $file->getContents();
			$contents = str_replace(array('templates','Templates'),array($moduleName,ucfirst($moduleName)),$contents);

			if($file->setContents($contents) === false)
				throw new Exception('Error modifying template file "'.$newFileName.'".');
		}
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
	private function writeConfig($title,$moduleName,$recordName) {

		$configFilePath = 'protected/modules/'.$moduleName.'/'.$moduleName.'Config.php';
		$configFile = Yii::app()->file->set($configFilePath,true);

		$contents = str_replace(
			array(
				'{title}',
				'{moduleName}',
				'{recordName}',
			),
			array(
				addslashes($title),
				addslashes($moduleName),
				addslashes($recordName),
			),
			$configFile->getContents()
		);

		if($configFile->setContents($contents) === false)
			throw new Exception('Error writing to config file "'.$configFilePath.'".');
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
			$moduleName = $module->name;
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
								$query = Yii::app()->db->createCommand($sqlLine);
                                try{
                                    $query->execute();
                                }catch(CDbException $e){

                                }
							}
						}
					} else {
						// The old way, for backwards compatibility:
						foreach ($uninstall as $sql) {
							$query = Yii::app()->db->createCommand($sql);
							$query->execute();
						}
					}
					X2Model::model('Fields')->deleteAllByAttributes(array('modelName' => $moduleName));
					X2Model::model('FormLayout')->deleteAllByAttributes(array('model' => $moduleName));
					$auth = Yii::app()->authManager;
					$auth->removeAuthItem(ucfirst($moduleName) . 'Index');
					$auth->removeAuthItem(ucfirst($moduleName) . 'Admin');

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
        $dlFlag=false;
        if (isset($_POST['name'])) {
            $moduleName = ($_POST['name']);

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
                    $sql.="/*&*/ALTER TABLE x2_$moduleName ADD COLUMN $field->fieldName $fieldType;/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES ('$moduleName', '$field->fieldName', '$field->attributeLabel', '1', '1');";
                }
            }
            $formLayouts=X2Model::model('FormLayout')->findAllByAttributes(array('model'=>$moduleName));
            foreach($formLayouts as $layout){
                $attributes=$layout->attributes;
                unset($attributes['id']);
                $attributeKeys=array_keys($attributes);
                $attributeValues=array_values($attributes);
                $keys=implode(", ",$attributeKeys);
                $values="'".implode("', '",$attributeValues)."'";
                $sql.="/*&*/INSERT INTO x2_form_layouts ($keys) VALUES ($values);";
            }
			$db = Yii::app()->file->set("protected/modules/$moduleName/sqlData.sql");
			$db->create();
			$db->setContents($sql);

			if (file_exists($moduleName . ".zip")) {
				unlink($moduleName . ".zip");
			}

            $zip = Yii::app()->zip;
            $zip->makeZip('protected/modules/' . $moduleName, $moduleName . ".zip");
            $dlFlag=true;
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
            'dlFlag'=>$dlFlag?:false,
            'file'=>$dlFlag?($_POST['name']):''
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
            $module->copy($moduleName.".zip");
            $zip = Yii::app()->zip;
            $zip->extractZip("$moduleName.zip", 'protected/modules/');

            $regPath = "protected/modules/$moduleName/register.php";
            $regFile = realpath($regPath);
            if ($regFile) {
                $install = require_once($regFile);
                foreach ($install['install'] as $sql) {
                    $sqlComm = $sql;
                    if (is_string($sql)) {
                        if (file_exists($sql)) {
                            $sqlComm = explode('/*&*/', file_get_contents($sql));
                        }
                    }
                    foreach ($sqlComm as $sqlLine) {
                        if(!empty($sqlLine)){
                            $command=Yii::app()->db->createCommand($sqlLine);
                            $command->execute();
                        }
                    }
                }
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
            elseif ($module->name == 'products')
                $modelList['Product'] = 'Product';
            elseif ($module->name == 'quotes')
                $modelList['Quote'] = 'Quote';
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
            $temp = json_decode($dropdown->options,true);
            if(is_array($temp)){
                $str=implode(", ",$temp);
            }else{
                $str=$dropdown->options;
            }
            $dropdown->options = $str;
        }
        $dataProvider->setData($dropdowns);

        $this->render('manageDropDowns', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
            'dropdowns' => Dropdowns::model()->findAll(),
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
                $arr = array();
                foreach ($query as $array) {
                    if ($array['modelName'] != 'Calendar')
                        $arr[$array['modelName']] = $array['modelName'];
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
        $modelList = array(
            'Admin' => array('name' => 'Admin Settings', 'count' => 1),
        );
        $modules = Modules::model()->findAll();
        foreach ($modules as $module) {
            $name = ucfirst($module->name);
            if($name!='Document'){
                $controllerName = $name . 'Controller';
                if(file_exists('protected/modules/'.$module->name.'/controllers/'.$controllerName.'.php')){
                    Yii::import("application.modules.$module->name.controllers.$controllerName");
                    $controller = new $controllerName($controllerName);
                    $model = $controller->modelClass;
                    if (class_exists($model)) {
                        $recordCount = X2Model::model($model)->count();
                        if ($recordCount > 0) {
                            $modelList[$model] = array('name' => $module->title, 'count' => $recordCount);
                        }
                    }
                }
            }
        }
        $this->render('export', array(
            'modelList' => $modelList,
        ));
    }

    public function actionPrepareExport() {
        $file = 'data.csv';
        $fp = fopen($file, 'w+');
        fputcsv($fp, array(Yii::app()->params->version));
        fclose($fp);
    }

    /**
     * Private method to handle the export
     *
     * This method actually prepares all the data and the CSV for export before
     * rending the page with the download.
     */
    public function actionGlobalExport($model, $page) {
        if (class_exists($model)) {
            ini_set('memory_limit', -1);
            $file = 'data.csv';
            $fp = fopen($file, 'a+');
            $tempModel = X2Model::model($model);
            $meta = array_keys($tempModel->attributes);
            $meta[] = $model;
            if ($page == 0)
                fputcsv($fp, $meta);
            $dp = new CActiveDataProvider($model, array(
                        'pagination' => array(
                            'pageSize' => 100,
                        ),
                    ));
            $pg = $dp->getPagination();
            $pg->setCurrentPage($page);
            $dp->setPagination($pg);
            $records = $dp->getData();
            $pageCount = $dp->getPagination()->getPageCount();

            foreach ($records as $record) {
                $tempAttributes = $tempModel->attributes;
                $tempAttributes = array_merge($tempAttributes, $record->attributes);
                $tempAttributes[] = $model;
                fputcsv($fp, $tempAttributes);
            }

            unset($tempModel, $dp);

            fclose($fp);
            if ($page + 1 < $pageCount) {
                echo $page + 1;
            }
        }
    }

    public function actionDownloadData($file) {
        if(!preg_match('/\.\./',$file)){
            $file = Yii::app()->file->set($file);
            $file->send();
        }
    }

    public function actionRollbackStage($model,$stage,$importId){
        $stages=array(
            "tags"=>"DELETE a FROM x2_tags a
                INNER JOIN
                x2_imports b ON b.modelId=a.itemId AND b.modelType=a.type
                WHERE b.modelType='$model' AND b.importId='$importId'",
            "relationships"=>"DELETE a FROM x2_relationships a
                INNER JOIN
                x2_imports b ON b.modelId=a.firstId AND b.modelType=a.firstType
                WHERE b.modelType='$model' AND b.importId='$importId'",
            "actions"=>"DELETE a FROM x2_actions a
                INNER JOIN
                x2_imports b ON b.modelId=a.associationId AND b.modelType=a.associationType
                WHERE b.modelType='$model' AND b.importId='$importId'",
            "records"=>"DELETE a FROM ".X2Model::model($model)->tableName()." a
                INNER JOIN
                x2_imports b ON b.modelId=a.id
                WHERE b.modelType='$model' AND b.importId='$importId'",
            "import"=>"DELETE FROM x2_imports WHERE modelType='$model' AND importId='$importId'",
        );
        $sqlQuery=$stages[$stage];
        $command=Yii::app()->db->createCommand($sqlQuery);
        $result=$command->execute();
        echo $result;
    }

    public function actionRollbackImport(){
        if(isset($_GET['importId'])){
            $importId=$_GET['importId'];
            $types=Yii::app()->db->createCommand()
                ->select('modelType')
                ->from('x2_imports')
                ->group('modelType')
                ->where('importId=:importId',array(':importId'=>$importId))
                ->queryAll();
            $count=Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from('x2_imports')
                    ->group('importId')
                    ->where('importId=:importId',array(':importId'=>$importId))
                    ->queryRow();
            $count=$count['COUNT(*)'];
            $typeArray=array();
            foreach($types as $tempArr){
                $typeArray[]=$tempArr['modelType'];
            }
            $this->render('rollbackImport',array(
                'typeArray'=>$typeArray,
                'dataProvider'=>null,
                'count'=>$count,
            ));

        }else{
            $data=array();
            $imports=Yii::app()->db->createCommand()
                ->select('importId')
                ->from('x2_imports')
                ->group('importId')
                ->queryAll();
            foreach($imports as $key=>$array){
                $data[$key]['id']=$key;
                $data[$key]['importId']=$array['importId'];
                $count=Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from('x2_imports')
                    ->group('importId')
                    ->where('importId=:importId',array(':importId'=>$array['importId']))
                    ->queryRow();
                $data[$key]['records']=$count['COUNT(*)'];
                $timestamp=Yii::app()->db->createCommand()
                    ->select('timestamp')
                    ->from('x2_imports')
                    ->group('importId')
                    ->order('timestamp ASC')
                    ->where('importId=:importId',array(':importId'=>$array['importId']))
                    ->queryRow();
                $data[$key]['timestamp']=$timestamp['timestamp'];
                $data[$key]['link']="";
            }
            $dataProvider=new CArrayDataProvider($data);
            $this->render('rollbackImport',array(
                'typeArray'=>array(),
                'dataProvider'=>$dataProvider,
            ));
        }

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
            $_SESSION['overwrite'] = $overwrite;
            $_SESSION['counts'] = array();
            $_SESSION['overwriten'] = array();
            $_SESSION['overwriteFailure']=array();
            $_SESSION['model'] = "";
            $_SESSION['failed'] = 0;
            $temp = CUploadedFile::getInstanceByName('data');
            $temp->saveAs('data.csv');
            $this->render('processImport', array(
                'overwrite' => $overwrite,
            ));
            //$this->globalImport('data.csv', $overwrite);
        } else {
            $this->render('import');
        }
    }

    public function actionPrepareImport() {
        $fp = fopen('data.csv', 'r+');
        $version = fgetcsv($fp);
        $version = $version[0];
        $tempMeta = fgetcsv($fp);
        $model = $tempMeta[count($tempMeta) - 1];
        unset($tempMeta[count($tempMeta) - 1]);
        $_SESSION['metaData'] = $tempMeta;
        $_SESSION['model'] = $model;
        $_SESSION['lastFailed'] = "";
        $_SESSION['offset'] = ftell($fp);
        fclose($fp);
        $criteria=new CDbCriteria;
        $criteria->order="importId DESC";
        $criteria->limit=1;
        $import=Imports::model()->find($criteria);
        if(isset($import)){
            $_SESSION['importId']=$import->importId+1;
        }else{
            $_SESSION['importId']=1;
        }
        $failedImport = fopen('failedImport.csv', 'w+');
        fputcsv($failedImport, array(Yii::app()->params->version));
        fclose($failedImport);
        echo json_encode(array($version));
    }

    public function actionGlobalImport() {
        if (isset($_POST['count']) && file_exists('data.csv')) {
            $metaData = $_SESSION['metaData'];
            $modelType = $_SESSION['model'];
            $count = $_POST['count'];
            $fp = fopen('data.csv', 'r+');
            fseek($fp, $_SESSION['offset']);
            for ($i = 0; $i < $count; $i++) {
                $arr = fgetcsv($fp);
                if ($arr !== false && !is_null($arr)) {
                    while ("" === end($arr)) {
                        array_pop($arr);
                    }
                    $newType = array_pop($arr);
                    if ($newType != $modelType) {
                        $_SESSION['model'] = $newType;
                        $_SESSION['metaData'] = $arr;
                        $modelType = $_SESSION['model'];
                        $metaData = $_SESSION['metaData'];
                    } else {
                        $attributes = array_combine($metaData, $arr);
                        if($modelType=="Actions" && (isset($attributes['type']) && $attributes['type']=='workflow')){
                            $model = new Actions('workflow');
                        }else{
                            $model = new $modelType;
                        }

                        foreach ($attributes as $key => $value) {
                            if ($model->hasAttribute($key) && isset($value)) {
                                if($value=="")
                                    $value=null;
                                $model->$key = $value;
                            }
                        }
                        $lookup = X2Model::model($modelType)->findByPk($model->id);
                        $lookupFlag = isset($lookup);
                        if($model->validate() || $modelType=="User"){
                            $saveFlag=true;
                            if ($lookupFlag) {
                                if ($_SESSION['overwrite'] == 1) {
                                    $lookup->delete();
                                }else{
                                    $saveFlag=false;
                                    isset($_SESSION['overwriteFailure'][$modelType])?$_SESSION['overwriteFailure'][$modelType]++:$_SESSION['overwriteFailure'][$modelType]=1;
                                }
                                if(!$model->validate()){
                                    $saveFlag=false;
                                    $failedImport = fopen('failedImport.csv', 'a+');
                                    $lastFailed = $_SESSION['lastFailed'];
                                    if ($lastFailed != $modelType) {
                                        $tempMeta = $metaData;
                                        $tempMeta[] = $modelType;
                                        fputcsv($failedImport, $tempMeta);
                                    }
                                    $attr = $model->attributes;
                                    $tempAttributes = X2Model::model($modelType)->attributes;
                                    $attr = array_merge($tempAttributes, $attr);
                                    $attr[] = $modelType;
                                    fputcsv($failedImport, $attr);
                                    $_SESSION['lastFailed'] = $modelType;
                                    isset($_SESSION['failed']) ? $_SESSION['failed']++ : $_SESSION['failed'] = 1;
                                }
                            }
                            if ($saveFlag && $model->save()) {
                                if($modelType!="Admin" && !(($modelType=="User" || $modelType=="Profile") && ($model->username=='admin' || $model->username=='api'))){
                                    $importLink=new Imports;
                                    $importLink->modelType=$modelType;
                                    $importLink->modelId=$model->id;
                                    $importLink->importId=$_SESSION['importId'];
                                    $importLink->timestamp=time();
                                    $importLink->save();
                                }
                                isset($_SESSION['counts'][$modelType]) ? $_SESSION['counts'][$modelType]++ : $_SESSION['counts'][$modelType] = 1;
                                if ($lookupFlag) {
                                    isset($_SESSION['overwriten'][$modelType]) ? $_SESSION['overwriten'][$modelType]++ : $_SESSION['overwriten'][$modelType] = 1;
                                } else {
                                    isset($_SESSION['overwriten'][$modelType])? : $_SESSION['overwriten'][$modelType] = 0;
                                }
                            }
                        } else {
                            $failedImport = fopen('failedImport.csv', 'a+');
                            $lastFailed = $_SESSION['lastFailed'];
                            if ($lastFailed != $modelType) {
                                $tempMeta = $metaData;
                                $tempMeta[] = $modelType;
                                fputcsv($failedImport, $tempMeta);
                            }
                            $attr = $model->attributes;
                            $tempAttributes = X2Model::model($modelType)->attributes;
                            $attr = array_merge($tempAttributes, $attr);
                            $attr[] = $modelType;
                            fputcsv($failedImport, $attr);
                            $_SESSION['lastFailed'] = $modelType;
                            isset($_SESSION['failed']) ? $_SESSION['failed']++ : $_SESSION['failed'] = 1;
                        }
                    }
                } else {
                    echo json_encode(array(
                        0,
                        json_encode($_SESSION['counts']),
                        json_encode($_SESSION['overwriten']),
                        json_encode($_SESSION['failed']),
                        json_encode($_SESSION['overwriteFailure']),
                    ));
                    return;
                }
            }
            $_SESSION['offset'] = ftell($fp);
            echo json_encode(array(
                1,
                json_encode($_SESSION['counts']),
                json_encode($_SESSION['overwriten']),
                json_encode($_SESSION['failed']),
                json_encode($_SESSION['overwriteFailure']),
            ));
        }
    }

    public function actionCleanUpImport() {
        unlink('data.csv');
        unset($_SESSION['counts']);
        unset($_SESSION['overwriten']);
        unset($_SESSION['model']);
        unset($_SESSION['overwrite']);
        unset($_SESSION['metaData']);
        unset($_SESSION['failed']);
        unset($_SESSION['lastFailed']);
        unset($_SESSION['overwriteFailure']);
    }

    /**
	 * Runs the updater. It is in this action where the entire file is copied
	 * from the remote update server.
	 */
	public function actionUpdater() {
		if (!file_exists('protected/config/X2Config.php'))
			$this->regenerateConfig();
		include('protected/config/X2Config.php');

		$context = stream_context_create(array(
			'http' => array(
				'timeout' => 15  // Timeout in seconds
				)));

		// Check to see if there's an update available. By this point in time
		// (if this AdminController is running inside of a much older installation)
		// FileUtil should have been downloaded and available.
		$versionTest = FileUtil::getContents('http://x2planet.com/installs/updates/versionCheck', 0, $context);
		$updaterCheck = FileUtil::getContents('http://x2planet.com/installs/updates/updateCheck', 0, $context);

		if ($updaterCheck && $versionTest) {
			if ($updaterCheck != $updaterVersion) {
				$this->runUpdateUpdater($updaterCheck,'updater');
			}

			if (version_compare($version,$versionTest)<0) {
				// Fetch update data from the server:
				$admin = Yii::app()->params->admin;
				$unique_id = 'none';
				if(isset($admin->unique_id))
					if(!in_array($admin->unique_id,array(Null,'none')))
							$unique_id = $admin->unique_id;
				$route = '{version}/{unique_id}';
				$params = array('{version}' => $version, '{unique_id}' => $unique_id);
				if (isset($admin->edition)) {
					if ($admin->edition != 'opensource') {
						$route .= '_{edition}_{n_users}';
						$params['{edition}'] = $admin->edition;
						$params['{n_users}'] = Yii::app()->db->createCommand()->select('COUNT(*)')->from('x2_users')->queryScalar();
					}
				}
				$updateData = FileUtil::getContents('http://x2planet.com/installs/updates/' . strtr($route, $params));


				if ($updateData) {
					// Render the updater with the data
					$updateData = CJSON::decode($updateData);
					$updateData['newVersion'] = $versionTest;
					$updateData['updaterCheck'] = $updaterCheck;


					if (!isset($updateData['errors'])) {
						$updateData['scenario'] = 'update';
						foreach(array('updaterCheck','updaterVersion','version','unique_id') as $var)
							$updateData[$var] = ${$var};
						$updateData['edition'] = 'opensource';
						if(isset(Yii::app()->params->admin->edition))
							$updateData['edition'] = Yii::app()->params->admin->edition;
						$updateData['url'] = 'x2planet';
						// Ready to run the updater.
						$newFiles = $updateData['fileList'];
						if(!empty($updateData['nonFreeFileList']))
							$newFiles = array_merge($newFiles,$updateData['nonFreeFileList']);
						$this->render('updater', $updateData);
					} else { // Scenario $updateData['errors'] is set; server denied/dropped request
						// Redirect, with the appropriate error message
						$this->render('updater', array(
							'scenario' => 'error',
							'message' => Yii::t('admin', "Could not retrieve update data."),
							'longMessage' => Yii::t('admin', $updateData['errors'])
						));
					}
				} else { // Scenario $updateData === False; request failed
					// Redirect, with the appropriate error message
					$this->render('updater', array(
						'scenario' => 'error',
						'message' => Yii::t('admin', "Could not retrieve update data."),
						'longMessage' => Yii::t('admin', 'Error connecting to the updates server.')
					));
				}
			} else { // scenario $version == $versionTest; already up-to-date.
				Yii::app()->session['versionCheck']=true;
				$this->render('updater',array('scenario'=>'message','version'=>$version,'message'=>Yii::t('admin','X2CRM is at the latest version!')));
			}

		} else { // $updaterCheck === False || $versionTest === False; couldn't connect to server
			// Redirect to updater with the appropriate error message
			$this->render('updater',array('scenario'=>'error','message'=>Yii::t('admin','Error connecting to the updates server.')));
		}
	}

	/**
	 * The upgrader action.
	 */
	public function actionUpgrader() {
		// Remove database backup; if it exists, the user most likely came here
		// immediately after updating to the latest version, in which case the
		// backup is outdated (applies to the old version)
		$this->removeDatabaseBackup();
		$thisVersion = Yii::app()->params->version;
		$currentVersion = FileUtil::getContents('http://x2planet.com/installs/updates/versionCheck');
		if (version_compare($thisVersion, $currentVersion) < 0) {
			$this->render('updater', array(
				'scenario' => 'error',
				'message' => 'Update required',
				'longMessage' => "Before upgrading, you must update to the latest version ($currentVersion). " . CHtml::link(Yii::t('app', 'Update'), 'updater', array('class' => 'x2-button'))
			));
		} else {
			include('protected/config/X2Config.php');

			$context = stream_context_create(array(
				'http' => array(
					'timeout' => 15  // Timeout in seconds
					)));

			// Check to see if the updater has changed:
			$updaterCheck = FileUtil::getContents('http://x2planet.com/installs/updates/updateCheck', 0, $context);

			if ($updaterCheck != $updaterVersion) {
				$this->runUpdateUpdater($updaterCheck, 'upgrader');
			}
			$this->render('updater', array(
				'scenario' => 'upgrade',
				'version' => $thisVersion,
				'unique_id' => '',
				'url' => 'x2planet',
				'newVersion' => $currentVersion,
				'updaterCheck' => $updaterCheck,
				'updaterVersion' => $updaterVersion,
				'edition' => Yii::app()->params->admin->edition
			));
		}
	}

    /**
     * Control settings for the updater
     *
     * This method controls the update interval setting for the application.
     */
    public function actionUpdaterSettings() {
		// $this->layout = '//layouts/column1';
        $admin = &Yii::app()->params->admin;
        if (isset($_POST['Admin'])) {
            $admin->setAttributes($_POST['Admin']);
            foreach (array('unique_id', 'edition') as $var)
                if (isset($_POST['unique_id']))
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
	public function actionDownload($url, $route, $file) {
		set_exception_handler('ResponseBehavior::respondWithException');
		if (Yii::app()->request->isAjaxRequest) {
			if ($url == 'x2planet') {
				if ($this->downloadSourceFile($route, $file))
					UpdaterBehavior::respond(Yii::t('admin','File {file} downloaded successfully.',array('{file}'=>$file)));
			} else {
				UpdaterBehavior::respond('Update server not implemented for URL provided.', true, true);
			}
		} else {
			$this->_sendResponse('400', 'Update requests must be made via AJAX.');
		}
	}

	/**
	 * Back up the database and existing files to be deleted or replaced in an
	 * update/upgrade.
	 */
	public function actionBackup() {
		set_error_handler('ResponseBehavior::respondWithError');
		set_exception_handler('ResponseBehavior::respondWithException');
		if($this->makeDatabaseBackup())
			UpdaterBehavior::respond(Yii::t('admin','Backup saved to').' protected/data/'.UpdaterBehavior::BAKFILE);
	}

	/**
	 * Looks for an existing database backup.
	 */
	public function actionCheckDatabaseBackup() {
		try {
			if($this->checkDatabaseBackup())
				UpdaterBehavior::respond(Yii::t('admin','Backup file exists and is not more than 24 hours old.'));
		} catch (Exception $e) {
			UpdaterBehavior::respond(Yii::t('admin','Backup does not exist or is too old.'),true,true);
		}
	}

	/**
	 * Yields the backup file.
	 */
	public function actionDownloadDatabaseBackup() {
		$backup = realpath($this->dbBackupPath);
		if((bool) $backup) {
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=".UpdaterBehavior::BAKFILE);
			header("Content-type: application/octet");
			header("Content-Transfer-Encoding: binary");
			readfile($backup);
		} else {
			if(!empty($_SERVER['HTTP_REFERER']))
				header("Location: {$_SERVER['HTTP_REFERER']}");
			else
				$this->redirect('index');
		}

	}

    /**
     * Finalizes an update/upgrade by applying file and database changes.
     *
     * This method replaces the SQL method as well as finishing copying files over.
     * Both of these happen at once to prevent issues from files depending on SQL
     * changes or vice versa.
     */
    public function actionEnactChanges($scenario=null,$autoRestore = false) {
		set_error_handler('ResponseBehavior::respondWithError');
		set_exception_handler('ResponseBehavior::respondWithException');
		$autoRestore = (bool) $autoRestore;
		if($this->enactChanges($scenario,$_POST,$autoRestore))
			UpdaterBehavior::respond(Yii::t('admin',ucfirst($scenario).' complete.'));
	}

	/**
	 * Retrieve the number of users
	 */
	public function actionGetNUsers() {
		echo Yii::app()->db->createCommand()->select('COUNT(*)')->from('x2_users')->queryScalar();
		Yii::app()->end();
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
			408 => 'Request Timeout',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     * View the changelogs.

    public function actionViewLogs() {
        $this->render('viewLogs');
    }*/

    /**
     * Improved version of array_search that allows for regex searching
     *
     * @param string $find Regex to search on
     * @param array $in_array An array to search in
     * @param array $keys_found An array of keys which meet the regex
     * @return type Returns the an array of keys if $in_array is valid, or false if not.
     */
    function Array_Search_Preg($find, $in_array, $keys_found = Array()) {
        if (is_array($in_array)) {
            foreach ($in_array as $key => $val) {
                if (is_array($val))
                    $this->Array_Search_Preg($find, $val, $keys_found);
                else {
                    if (preg_match('/' . $find . '/', $val))
                        $keys_found[] = $key;
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
    function deCamelCase($str) {
        $str = preg_replace("/(([a-z])([A-Z])|([A-Z])([A-Z][a-z]))/", "\\2\\4 \\3\\5", $str);
        return ucfirst($str);
    }

	/**
	 * Prints an error message explaing what has gone wrong when the classes are missing.
	 * @param array $classes The missing dependencies
	 */
	public function missingClassesException($classes) {
		$message = trim("
One or more dependencies of AdminController are missing and could not be automatically retrieved:
".implode(', ',$classes)."

To diagnose this error, please upload and run the requirements check script on your server:
http://x2planet.com/installs/requirements.php

The error is most likely due to one of the following things:
(1) PHP processes run by the web server do not have permission to create or modify files
(2) x2planet.com and/or raw.github.com are currently unavailable
(3) This web server has no outbound internet connection, because it is: (a) behind a firewall that does not permit outbound connections; (b) connected to a private subnet with broken DNS resolution or (c) connected to a private subnet with no outbound route

To stop this error from occurring, if the problem persists: restore the file protected/controllers/AdminController.php to the copy from your version of X2CRM:
https://raw.github.com/X2Engine/X2Engine/".Yii::app()->params->version."/x2engine/protected/controllers/AdminController.php");
		$this->error500($message);
	}


	public function actionAuthGraph() {

		if(!Yii::app()->user->checkAccess('AdminIndex'))
			return;

		$allTasks = array();

		$authGraph = array();

		$taskNames = Yii::app()->db->createCommand()
			->select('name')
			->from('x2_auth_item')
			->where('type=1')
			->queryColumn();

		foreach($taskNames as $task) {
			$children = Yii::app()->db->createCommand()
				->select('child')
				->from('x2_auth_item_child')
				->where('parent=:parent',array(':parent'=>$task))
				->queryColumn();

			foreach($children as $child)
				$allTasks[$task][$child] = array();
		}

		$bizruleTasks = Yii::app()->db->createCommand()
			->select('name')
			->from('x2_auth_item')
			->where('bizrule IS NOT NULL')
			->queryColumn();

		function buildGraph($task,&$allTasks,&$authGraph) {

			if(!isset($allTasks[$task]) || empty($allTasks[$task])) {
				return array();
			} else {
				$children = array();

				foreach(array_keys($allTasks[$task]) as $child) {

					if(isset($authGraph[$child]) && $authGraph[$child] === false)
						continue;

					$childGraph = buildGraph($child,$allTasks,$authGraph);

					$children[$child] = $childGraph;
					$authGraph[$child] = false;	// this is a child task, remove it from the top level
				}
				return $children;
			}
		}

		foreach(array_keys($allTasks) as $task)
			$authGraph[$task] = buildGraph($task,$allTasks,$authGraph);

		foreach(array_keys($authGraph) as $key) {
			if(empty($authGraph[$key]))
				unset($authGraph[$key]);
		}

		$this->render('authGraph',array('authGraph'=>$authGraph,'bizruleTasks'=>$bizruleTasks));

	}


	/**
	 * Wrapper for {@link UpdaterBehavior::updateUpdater} that displays errors
	 * in a user-friendly way and reloads the page.
	 */
	public function runUpdateUpdater($updaterCheck,$redirect) {
		try {
			if(count($classes = $this->updateUpdater($updaterCheck)))
				$this->missingClassesException($classes);
			$this->redirect($redirect);
		} catch(Exception $e) {
			$this->error500($e->getMessage());
		}
	}

	/**
	 * Wrapper for FileUtil
	 * @param type $path
	 */
	public function rrmdir($path) {
		FileUtil::rrmdir($path);
	}

	/**
	 * Last-resort, built-in, fail-resistant copy method
	 *
	 * Copy method used in the case that FileUtil is not yet available (i.e. if
	 * AdminController was downloaded in an auto-update by a much older version
	 * but nothing else). Returns true on success and false on failure.
	 *
	 * @param string $remoteFile URL of file to fetch
	 * @param string $localFile Path to local destination
	 * @param boolean $curl Whether to use CURL
	 * @return boolean
	 */
	public function copyRemote($remoteFile,$localFile,$curl) {
		$this->checkRemoteMethods();
		if(!$curl) {
			$context = stream_context_create(array(
			'http' => array(
				'timeout' => 15  // Timeout in seconds
				)));
			return copy($remoteFile, $localFile, $context) !== false;
		} else {
			// Try using CURL
			$ch = curl_init($remoteFile);
			$curlopt = array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_BINARYTRANSFER => 1,
				CURLOPT_POST => 0,
				CURLOPT_TIMEOUT => 15
			);
			curl_setopt_array($ch, $curlopt);
			$contents = curl_exec($ch);
			if ((bool) $contents) {
				return file_put_contents($localFile,$contents) !== false;
			} else
				return false;
		}
	}

	/**
	 * Magic getter for "noRemoteAccess" property.
	 *
	 * If true, signifies that there is no possible way to retrieve remote files.
	 * @return boolean
	 */
	public function getNoRemoteAccess() {
		if (!isset($this->_noRemoteAccess))
			$this->_noRemoteAccess =
					!extension_loaded('curl')
					&& (
					in_array(ini_get('allow_url_fopen'), array(0, 'Off', 'off'))
					|| !(function_exists('file_get_contents') && function_exists('copy'))
					);
		return $this->_noRemoteAccess;
	}

	/**
	 * Check whether it is possible to retrieve remote files.
	 *
	 * Based on the availability of CURL and allow_url_fopen (from {@
	 */
	public function checkRemoteMethods() {
		if ($this->noRemoteAccess)
			$this->error500(Yii::t('admin', 'X2CRM needs to retrieve one or more remote files, but no remote access methods are available on this web server, because allow_url_fopen is disabled and the CURL extension is missing.'));
	}

	/**
	 * Explicit, attention-grabbing error message w/o bug reporter.
	 *
	 * This is intended for errors that are NOT bugs, but that arise from server
	 * malconfiguration and/or missing requirements for running X2CRM.
	 * @param type $message
	 */
	public function error500($message) {
		$app = Yii::app();
		$email = Yii::app()->params->adminEmail;
		$inAction = @is_subclass_of($this->action,'CAction');
		if ($app->params->hasProperty('admin')) {
			if ($app->params->admin->hasProperty('emailFromAddr'))
				$email = $app->params->admin->emailFromAddr;
		}
		$inAction = @is_subclass_of($this->action,'CAction');
		if ($inAction) {
			$data = array(
				'scenario' => 'error',
				'message' => Yii::t('admin', "Cannot run {action}.", array('{action}' => $this->action->id)),
				'longMessage' => str_replace("\n","<br />",$message),
			);
			$this->render('updater', $data);
			Yii::app()->end();
		} else {
			$data = array(
				'time' => time(),
				'admin' => $email,
				'version' => Yii::getVersion(),
				'message' => $message
			);
			header("HTTP/1.1 500 Internal Server Error");
			$this->renderPartial('system.views.error500', array('data' => $data));
		}
		Yii::app()->end();
	}

}
