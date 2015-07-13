<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

Yii::import('application.components.util.*');

/**
 * Administrative, app-wide configuration actions.
 *
 * @package application.controllers
 * @property boolean $noRemoteAccess (Read-only) true indicates there's no way to automatically retrieve files.
 * @property string $exportFile (read-only) path to the data export file
 * @author Jake Houser <jake@x2engine.com>
 */
class AdminController extends Controller {

    public $modelClass = 'Admin';
    public $portlets = array();
    public $layout = '//layouts/column1';

    /**
     * @var bool $noBackdrop If true, then the content will not have a backdrop
     */
    public $noBackdrop = false;

    /**
     * Behavior classes used by AdminController
     * @var array
     */
    public static $behaviorClasses = array(
        'LeadRoutingBehavior', 'UpdaterBehavior', 'CommonControllerBehavior',
        'ImportExportBehavior');

    /**
     * Extraneous properties for individual behaviors
     * @var array
     */
    public static $behaviorProperties = array('UpdaterBehavior' => array('isConsole' => false));

    /**
     * Miscellaneous component classes that the controller (or its behaviors)
     * depend on, but that aren't directly used by it as behaviors.
     * @var type
     */
    public static $dependencies = array(
        'util/FileUtil', 'util/EncryptUtil', 'util/ResponseUtil', 'ResponseBehavior',
        'views/requirements');

    /**
     * Stores value of {@link $noRemoteAccess}
     * @var boolean
     */
    private $_noRemoteAccess;
    private $_behaviors;
    private $_exportFile;

    /**
     * A list of actions to include.
     *
     * This method specifies which actions are defined elsewhere but used here.
     * These actions are pro code that are included in the pro version of the software.
     *
     * @return array An array of actions to include.
     */
    public function actions() {
        return array_merge($this->webUpdaterActions, array(
            
            'automateTranslation' => array(
                'class' => 'X2TranslationAction',
            ),
            'viewLog' => array(
                'class' => 'LogViewerAction',
            ),
        ));
    }

    /**
     * @deprecated
     * This is mostly a developer function used for viewing information about
     * translation files.
     *
     * This method will find a list of messages which have an entry in the translation
     * files but do not have corresponding translations. Since the advent of the
     * translation automation feature, this method should be largely unnecessary.
     */
    public function actionCalculateMissingTranslations() {
        $untranslated = array();
        $languages = scandir('protected/messages');
        foreach ($languages as $lang) {
            if (!in_array($lang, array('template', '.', '..'))) {
                $untranslated[$lang] = 0;
                $files = scandir('protected/messages/' . $lang);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $translations = array_values(include('protected/messages/' . $lang . '/' . $file));
                        foreach ($translations as $message) {
                            if (empty($message)) {
                                $untranslated[$lang] ++;
                            }
                        }
                    }
                }
            }
        }
        /**/printR($untranslated);
    }

    /**
     * @deprecated
     * Another function for analyzing the translation files.
     *
     * This method will display the "redundancy" of translation files. That is to say,
     * if a word is contained in two separate files, that word is considered
     * redundant and could be refactored into the "common.php" message file.
     * As of the advent of the translation automation function, redundancy
     * cleanup is a part of that process and this method should be unnecessary.
     */
    public function actionCalculateTranslationRedundancy() {
        $max = array('file1' => 'null', 'file2' => 'null', 'redundancy' => 0);
        $files = scandir('protected/messages/template');
        $duplicates = array();
        $languageList = array();
        $totalWords = array();
        $untranslated = 0;
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $languageList[$file] = array_keys(include("protected/messages/template/$file"));
            }
        }
        $keys = array_keys($languageList);
        for ($i = 0; $i < count($languageList); $i++) {
            $totalWords = array_merge($totalWords, $languageList[$keys[$i]]);
            for ($j = $i + 1; $j < count($languageList); $j++) {
                $intersect = array_intersect($languageList[$keys[$i]], $languageList[$keys[$j]]);
                if (!empty($intersect)) {
                    $duplicates = array_unique(array_merge($duplicates, $intersect));
                    /**/printR($intersect);
                    $unique = count($languageList[$keys[$i]]) + count($languageList[$keys[$j]]) - count($intersect);
                    $redundancy = round(count($intersect) / $unique * 100, 2);
                    echo "Between " . $keys[$i] . " and " . $keys[$j] . ": " . $redundancy . "% items identical.<br />";
                    if ($redundancy > $max['redundancy']) {
                        $max['file1'] = $keys[$i];
                        $max['file2'] = $keys[$j];
                        $max['redundancy'] = $redundancy;
                    }
                }
            }
        }
        echo "<br>The most redundant files are " . $max['file1'] . " and " . $max['file2'] . " with a redundancy of " . $max['redundancy'] . "%<br><br>";
        echo "There are " . count($duplicates) . " entries which occur more than once.<br><br>";
        echo "There are " . count($totalWords) . " entries in the translation files.";
    }

    private static function findMissingPermissions () {
        $controllers = array(
            'AdminController' => 'application.controllers.AdminController',
            'StudioController' => 'application.controllers.StudioController',
            'AccountsController' => 'application.modules.accounts.controllers.AccountsController',
            'ActionsController' => 'application.modules.actions.controllers.ActionsController',
            'CalendarController' => 'application.modules.calendar.controllers.CalendarController',
            'ContactsController' => 'application.modules.contacts.controllers.ContactsController',
            'DocsController' => 'application.modules.docs.controllers.DocsController',
            'GroupsController' => 'application.modules.groups.controllers.GroupsController',
            'MarketingController' => 
                'application.modules.marketing.controllers.MarketingController',
            'MediaController' => 'application.modules.media.controllers.MediaController',
            'OpportunitiesController' => 
                'application.modules.opportunities.controllers.OpportunitiesController',
            'ProductsController' => 'application.modules.products.controllers.ProductsController',
            'QuotesController' => 'application.modules.quotes.controllers.QuotesController',
            'ServicesController' => 'application.modules.services.controllers.ServicesController',
            'UsersController' => 'application.modules.users.controllers.UsersController',
            'WorkflowController' => 'application.modules.workflow.controllers.WorkflowController',
            'X2LeadsController' => 'application.modules.x2Leads.controllers.X2LeadsController',
            'BugReportsController' => 
                'application.modules.bugReports.controllers.BugReportsController',
             
        );
        $missingPermissions = array();
        $auth = Yii::app()->authManager;
        foreach ($controllers as $class => $controller) {
            Yii::import($controller);
            $methods = get_class_methods($class); // Grab all functions from the controller
            $arr = explode('Controller', $class);
            $name = $arr[0];
            if (is_array($methods)) {
                foreach ($methods as $method) {
                    // Only look for methods that start with "action"
                    if (strpos($method, 'action') === 0 && $method != 'actions') { 
                        $method = $name . substr($method, 6);
                        $authItem = $auth->getAuthItem($method);
                        // We can't find a permission, add it to the list of missing ones
                        if (is_null($authItem)) 
                            $missingPermissions[] = $method;
                    }
                }
            }
            if (preg_match ('/modules/', $controller)) {
                $moduleName = preg_replace (
                    '/application.modules.([^.]+)\..*/', '$1', $controller);
                $moduleClassName = ucfirst ($moduleName).'Module';
                Yii::import (preg_replace ('/controller.*/', '*', $controller));
                $controller = new $class ('', new $moduleClassName ($moduleName, null));
                $actions = $controller->actions ();
                foreach ($actions as $actionName => $params) {
                    $method = $name . ucfirst ($actionName);
                    $authItem = $auth->getAuthItem($method);
                    if (is_null($authItem)) 
                        $missingPermissions[] = $method;
                }
            }
        }
        return $missingPermissions;

    }

    /**
     * A function to print a list of actions which are present in controller files
     * but no corresponding permission exists in the database.
     *
     * This function should ideally be run before each release as a developer tool
     * to view what permissions are missing from the software. Any controller action
     * with no permission associated with it is assumed to be allowed so this is
     * a good way to look for potential security holes. Please note that all
     * relevant controllers must be specified by name in the array at the top
     * of the function.
     */
    public function actionFindMissingPermissions() {
        /**/printR (self::findMissingPermissions ());
    }

    

// Used to manually test updater file copy in Windows
//    public static function caseSensitiveCopyTest () {
//        $ds = DIRECTORY_SEPARATOR;
//        $ube = new CComponent();
//        $properties = array ();
//        $ubconfig = array_merge(array(
//            'class' => 'UpdaterBehavior',
//            'isConsole' => true,
//            'noHalt' => true,
//        ),$properties);
//        $ube->attachBehavior('UpdaterBehavior', $ubconfig);
//        $source = $ube->webroot.'/protected/tests/data/UpdaterBehaviorTest/source/a.js';
//        $target = $ube->webroot.'/protected/tests/data/UpdaterBehaviorTest/app/a.js';
//
//        $sourcePath = FileUtil::relpath($source, '.');
//        $targetPath = FileUtil::relpath($target, '.');
//        FileUtil::ccopy ($source, $target);
//        $ube->removeFiles (array (
//            '/protected/tests/data/UpdaterBehaviorTest/app/A.js'
//        ));
//    }

    /**
     * View the main admin menu
     */
    public function actionIndex() {
        //if(isset($_GET['translateMode'])) // Old feature Matthew implemented to better visualize missing translations, no longer used.
        //Yii::app()->session['translate'] = $_GET['translateMode'] == 1;
        $this->render('index');
    }

    /**
     * An overridden Yii method that happens before an action.
     *
     * This method handles authorization on an attempt by a user to access an action.
     * A slightly modified version of method is included in X2Base as a behavior.
     *
     * @param string $action A paramter passed by Yii's internal action handling.
     * @return boolean True if the action is allowed to continue, otherwise throw exception.
     * @throws CHttpException Generates a 403 error if authorization fails
     */
    protected function beforeAction($action = null) {
        $auth = Yii::app()->authManager;
        $action = ucfirst($this->getId()) . ucfirst($this->getAction()->getId());
        $authItem = $auth->getAuthItem($action);
        // Backwards-compatible way (to make updates safe) of determining if the user has admin rights.
        $imAdmin = false;
        if (Yii::app()->params->hasProperty('isAdmin')) {
            $imAdmin = Yii::app()->params->isAdmin || Yii::app()->user->checkAccess($action) || is_null($authItem);
        } else if (version_compare(Yii::app()->params->version, '2.0') >= 0) {
            $imAdmin = Yii::app()->user->checkAccess('AdminIndex') || is_null($authItem);
        } else {
            $imAdmin = Yii::app()->user->name == 'admin';
        }
        if ($imAdmin) {
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
      } */

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
     * The reason for all this is that in older versions, the updater utility,
     * when updating itself, will download the latest version of
     * AdminController. This necessitates downloading all of its dependencies,
     * so that AdminController can still run properly, in order to be backwards-
     * compatible.
     *
     * It uses the same form as a typical magic getter method (private storage
     * property, check if it's set first and return) because the method is also
     * called in the override {@link createAction()}
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
        if (!isset($this->behaviors)) {
            $missingClasses = array();
            $behaviors = array();
            $maxTries = 3;
            $GithubUrl = 'https://raw.github.com/X2Engine/X2Engine/master/x2engine/protected';
            $x2planUrl = 'https://x2planet.com/updates/x2engine/protected'; // NOT using UpdaterBehavior.updateServer because that behavior may not yet exist
            $files = array_merge(array_fill_keys(self::$behaviorClasses, 'behavior'), array_fill_keys(self::$dependencies, 'dependency'));
            $tryCurl = in_array(ini_get('allow_url_fopen'), array(0, 'Off', 'off'));
            foreach ($files as $class => $type) {
                // First try to download from the X2Engine update server...
                $path = "components/$class.php";
                $absPath = Yii::app()->basePath . "/$path";
                if (!file_exists($absPath)) {
                    if (!is_dir(dirname($absPath))) {
                        mkdir(dirname($absPath));
                    }
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
            // Uncomment this next line to test:
            // $missingClasses[] = 'FOO';
            if (count($missingClasses))
                $this->missingClassesException($missingClasses);

            // Add extraneous behavior properties:
            foreach (self::$behaviorProperties as $class => $properties) {
                foreach ($properties as $name => $value) {
                    $behaviors[$class][$name] = $value;
                }
            }
            $this->_behaviors = $behaviors;
        }
        return $this->_behaviors;
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
        if (isset($cache))
            $cache->flush();
        $filterChain->run();
    }

    /**
     * A filter to clear the authItem cache.
     * @param type $filterChain The filter chain Yii is currently acting on.
     */
    public function filterClearAuthCache($filterChain) {
        // Check for existence of authCache object (for backwards compatibility)
        if (!is_null(Yii::app()->db->getSchema()->getTable('x2_auth_cache'))) {
            if (Yii::app()->hasComponent('authCache')) {
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
      } */
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
      } */
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

      $headers = 'From: ' . Yii::app()->settings->appName;

      $mailingList = Contacts::getMailingList($criteria);

      foreach ($mailingList as $email) {
      mail($email, $subject, $body, $headers);
      }

      $this->render('mail', array(
      'mailingList' => $mailingList,
      'criteria' => $criteria,
      ));
      } */

    /**
     * The tag manager page of the administrative section.
     *
     * This page allows for the admin user to view a list of tags and how many
     * records have that tag. From here, the admin can mass delete individual tags
     * or remove all tags.
     */
    public function actionManageTags() {
        $dataProvider = new CActiveDataProvider('Tags', array(
            'criteria' => array(
                'group' => 'tag'
            ),
            'pagination' => array(
                'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
            ),
        ));

        $this->render('manageTags', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * This function is called via AJAX by Manage Tags to remove a tag.
     * @param string $tag The name of the tag to be deleted.
     */
    public function actionDeleteTag($tag) {
        if (!empty($tag)) {
            if ($tag != 'all') {
                $tag = "#" . $tag;
                X2Model::model('Tags')->deleteAllByAttributes(array('tag' => $tag));
            } else {
                X2Model::model('Tags')->deleteAll();
            }
        }
        $this->redirect('manageTags');
    }

    /**
     * An administrative page to see a list of all current sessions. From here,
     * the admin can toggle visible/invisible or end any user session.
     */
    public function actionManageSessions() {
        $dataProvider = new CActiveDataProvider('Session');

        $this->render('manageSessions', array(
            'dataProvider' => $dataProvider,
        ));
    }

    

    /**
     * An AJAX called function to set a particular session to visible or invisible
     * @param $id The ID of the session to be toggled.
     */
    public function actionToggleSession($id) {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $session = Session::model()->findByPk($id);
            if (isset($session)) {
                $session->status = !$session->status;
                $ret = $session->status;
                if ($session->save()) {
                    echo $ret;
                }
            }
        }
    }

    /**
     * An AJAX called function to allow the admin to forcibly end a session,
     * logging the user out.
     * @param $id The ID of the session.
     */
    public function actionEndSession($id) {
        echo Session::model()->deleteByPk($id);
    }

    /**
     * An administrative function to view a historical list of sessions and events
     * associated with them.
     *
     * If the admin has turned on the "Session Logging" feature in the General
     * Settings page, all sessions are logged in the session log here. Specific
     * timestamps for login/logout as well as going visible or invisible are provided
     * here. The admin can also click into a session to load the full history
     * of session related activity for that session.
     */
    public function actionViewSessionLog() {
        $sessionLog = new CActiveDataProvider('SessionLog', array(
            'sort' => array(
                'defaultOrder' => 'timestamp DESC',
            ),
            'pagination' => array(
                'pageSize' => Profile::getResultsPerPage()
            )
        ));
        $this->render('viewSessionLog', array(
            'dataProvider' => $sessionLog,
        ));
    }

    /**
     * An AJAX called function which will return HTML containing a full history
     * of a particular session, from login to logout.
     * @param $id The ID of the session
     */
    public function actionViewSessionHistory($id) {
        $sessions = X2Model::model('SessionLog')->findAllByAttributes(array('sessionId' => $id));
        $firstTimestamp = 0;
        $lastTimestamp = 0;
        $str = "<table class='items'><thead><tr><th>User</th><th>Status</th><th>Timestamp</th></tr></thead>";
        foreach ($sessions as $session) {
            $str.="<tr>";
            $str.="<td>" . User::getUserLinks($session->user) . "</td>";
            $str.="<td>" . SessionLog::parseStatus($session->status) . "</td>";
            $str.="<td>" . Formatter::formatCompleteDate($session->timestamp) . "</td>";
            $str.="</tr>";
        }
        $str.="</table>";
        echo $str;
    }

    /**
     * An administrative function to display a grid of user view data--that is a
     * log of when a user viewed a particular record.
     */
    public function actionUserViewLog() {
        $dataProvider = new CActiveDataProvider('ViewLog', array(
            'sort' => array(
                'defaultOrder' => 'timestamp DESC',
            ),
            'pagination' => array(
                'pageSize' => Profile::getResultsPerPage()
            )
        ));
        $this->render('userViewLog', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Delete all ViewLog entries from the database.
     */
    public function actionClearViewHistory() {
        X2model::model('ViewLog')->deleteAll();
        $this->redirect('userViewLog');
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
                $tempArr = array(
                    $values['field'][$i], $values['comparison'][$i], $values['value'][$i]);
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

            $model->users = Fields::parseUsers($model->users);
            $check = LeadRouting::model()->findByAttributes(array('priority' => $model->priority));
            if (isset($check)) {
                $query = "UPDATE x2_lead_routing " .
                        "SET priority=priority+1 " .
                        "WHERE priority>='$model->priority'";
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
     * @deprecated
     * The functionality for creating a new Role is handled in actionManageRoles, and the
     * roleEditor view's form is currently set to the action manageRoles.
     *
     * This method is accessed by a form on the {@link AdminController::actionManageRoles}
     * page to create a new role. View and Edit permissions are set and saved in the proper
     * tables in this method, and then the user is redirected back to the "Manage Roles"
     * page.
     */
    /*    public function actionRoleEditor(){
      $model = new Roles;
      if(isset($_POST['Roles'])){
      $model->attributes = $_POST['Roles'];
      if(!isset($_POST['viewPermissions']))
      $viewPermissions = array();
      else
      $viewPermissions = $_POST['viewPermissions'];
      if(!isset($_POST['editPermissions']))
      $editPermissions = array();
      else
      $editPermissions = $_POST['editPermissions'];
      if(isset($_POST['Roles']['users']))
      $users = $model->users;
      else
      $users = array();
      $model->users = "";
      if($model->save()){

      foreach($users as $user){
      $role = new RoleToUser;
      $role->roleId = $model->id;
      if(!is_numeric($user)){
      $userRecord = User::model()->findByAttributes(array('username' => $user));
      $role->userId = $userRecord->id;
      $role->type = 'user';
      }/* x2temp */ /* else{
      $role->userId = $user;
      $role->type = 'group';
      }/* end x2temp */ /*
      $role->save();
      }
      $fields = Fields::model()->findAll();
      $temp = array();
      foreach($fields as $field){
      $temp[] = $field->id;
      }
      $both = array_intersect($viewPermissions, $editPermissions);
      $view = array_diff($viewPermissions, $editPermissions);
      $neither = array_diff($temp, $viewPermissions);
      foreach($both as $field){
      $rolePerm = new RoleToPermission;
      $rolePerm->roleId = $model->id;
      $rolePerm->fieldId = $field;
      $rolePerm->permission = 2;
      $rolePerm->save();
      }
      foreach($view as $field){
      $rolePerm = new RoleToPermission;
      $rolePerm->roleId = $model->id;
      $rolePerm->fieldId = $field;
      $rolePerm->permission = 1;
      $rolePerm->save();
      }
      foreach($neither as $field){
      $rolePerm = new RoleToPermission;
      $rolePerm->roleId = $model->id;
      $rolePerm->fieldId = $field;
      $rolePerm->permission = 0;
      $rolePerm->save();
      }
      }
      $this->redirect('manageRoles');
      }
      } */

    /**
     * Delete an existing role.
     *
     * This method is accessed by a form on the {@link AdminController::manageRoles}
     * page to allow for the deletion of admin created roles.  Default system roles
     * (authenticated, guest, and admin) cannot be deleted this way.
     */
    public function actionDeleteRole() {
        $auth = Yii::app()->authManager;
        if (isset($_POST['role']) && !in_array($_POST['role'], array('authenticated', 'guest', 'admin'))) {
            $id = $_POST['role'];
            $role = Roles::model()->findByAttributes(array('name' => $id));
            if (!isset($role)) {
                // Nonexistent role
                Yii::app()->user->setFlash('error', Yii::t('admin', 'Role does not exist'));
                $this->redirect('manageRoles');
            }
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
            $timeout = isset($_POST['Roles']['timeout']) ? $_POST['Roles']['timeout'] : null;
            $role = Roles::model()->findByAttributes(array('name' => $id));
            if ($role) {
                $model = $role;
                $id = $model->id;
                if (!isset($_POST['viewPermissions']))
                    $viewPermissions = array();
                else
                    $viewPermissions = $_POST['viewPermissions'];
                if (!isset($_POST['editPermissions']))
                    $editPermissions = array();
                else
                    $editPermissions = $_POST['editPermissions'];
                if (isset($_POST['users']))
                    $users = $_POST['users'];
                else
                    $users = array();
                $model->users = "";
                if ($timeout !== null) {
                    $model->timeout = $timeout * 60; // Timeout is specified in minutes
                } else {
                    $model->timeout = null;
                }
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
                $stageName = X2Model::model('WorkflowStage')->findByAttributes(array('workflowId' => $workflow, 'stageNumber' => $stage))->name;
            else
                $this->redirect('manageRoles');
            if (!isset($_POST['viewPermissions']))
                $viewPermissions = array();
            else
                $viewPermissions = $_POST['viewPermissions'];
            if (!isset($_POST['editPermissions']))
                $editPermissions = array();
            else
                $editPermissions = $_POST['editPermissions'];
            $model->attributes = $_POST['Roles'];
            $model->timeout *= 60;
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
        $admin = &Yii::app()->settings;
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
                echo CHtml::tag('option', array('value' => $key + 1), CHtml::encode($value), true);
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
            $role = Roles::model()->findByAttributes(array('name' => $id));
            if (!$role) {
                echo "";
                exit;
            }
            $id = $role->id;
            $roles = RoleToUser::model()->findAllByAttributes(array('roleId' => $id));
            $users = array();
            foreach ($roles as $link) {
                if ($link->type == 'user') {
                    $user = User::model()->findByPk($link->userId);
                    if (isset($user))
                        $users[] = $user->username;
                }
                /* x2temp */
                else {
                    $group = Groups::model()->findByPk($link->userId);
                    if (isset($group))
                        $users[] = $group->id;
                }
                /* end x2temp */
            }
            $allUsers = User::model()->findAll('status="1"');
            $selected = array();
            $unselected = array();
            foreach ($users as $user) {
                $selected[] = $user;
            }
            foreach ($allUsers as $user) {
                $unselected[CHtml::encode($user->username)] = CHtml::encode($user->firstName . " " . $user->lastName);
            }
            /* x2temp */
            $groups = Groups::model()->findAll();
            foreach ($groups as $group) {
                $unselected[$group->id] = CHtml::encode($group->name);
            }
            /* end x2temp */
            unset($unselected['admin']);

            $sliderId = 'editTimeoutSlider';
            $textfieldId = 'editTimeout';
            if (isset($_GET['mode']) && in_array($_GET['mode'], array('edit', 'exception'))) {
                // Handle whether this was called from editRole or roleException, they
                // need different IDs to work on the same page.
                $sliderId .= "-" . $_GET['mode'];
                $textfieldId .= "-" . $_GET['mode'];
            }

            $timeoutSet = $role->timeout !== null;
            echo "
                <div class='row' id='set-session-timeout-row'>
                <input id='set-session-timeout' type='checkbox' class='left' " .
            ($timeoutSet ? 'checked="checked"' : '') . ">
                <label>" . Yii::t('admin', 'Enable Session Timeout') . "</label>
                </div>
            ";
            echo "<div id='timeout-row' class='row' " .
            ($timeoutSet ? '' : "style='display: none;'") . ">";
            echo Yii::t('admin', 'Set role session expiration time (in minutes).');
            echo "<br />";
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => $role->timeout / 60,
                // additional javascript options for the slider plugin
                'options' => array(
                    'min' => 5,
                    'max' => 1440,
                    'step' => 5,
                    'change' => "js:function(event,ui) {
                                    $('#" . $textfieldId . "').val(ui.value);
                                    $('#save-button').addClass('highlight');
                                }",
                    'slide' => "js:function(event,ui) {
                                    $('#" . $textfieldId . "').val(ui.value);
                                }",
                ),
                'htmlOptions' => array(
                    'style' => 'width:340px;margin:10px 9px;',
                    'id' => $sliderId
                ),
            ));
            echo CHtml::activeTextField(
                    $role, 'timeout', array(
                'id' => $textfieldId,
                'disabled' => ($role->timeout !== null ? '' : 'disabled'),
            ));
            echo "</div>";
            Yii::app()->clientScript->registerScript('timeoutScript', "
                $('#set-session-timeout').change (function () {
                    if ($(this).is (':checked')) {
                        $('#timeout-row').slideDown ();
                        $('#" . $textfieldId . "').removeAttr ('disabled');
                    } else {
                        $('#timeout-row').slideUp ();
                        $('#" . $textfieldId . "').attr ('disabled', 'disabled');
                    }
                });
                $('#" . $textfieldId . "').val( $('#" . $sliderId . "').slider('value') );
            ", CClientScript::POS_READY);
            echo "<script>";
            Yii::app()->clientScript->echoScripts();
            echo "</script>";

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
                $fieldUnselected[$field->id] = X2Model::getModelTitle($field->modelName) . " - " . $field->attributeLabel;
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

        $model = new Roles;
        $model->timeout = 60;
        if (isset($_POST['Roles'])) {
            $model->attributes = $_POST['Roles'];
            if (!isset($_POST['viewPermissions']))
                $viewPermissions = array();
            else
                $viewPermissions = $_POST['viewPermissions'];
            if (!isset($_POST['editPermissions']))
                $editPermissions = array();
            else
                $editPermissions = $_POST['editPermissions'];
            if (isset($_POST['Roles']['users']))
                $users = $model->users;
            else
                $users = array();
            $model->users = "";
            $model->timeout *= 60;

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
            } else {
                foreach ($model->getErrors() as $err)
                    $errors = $err;
                $errors = implode(',', $errors);
                Yii::app()->user->setFlash('error', Yii::t('admin', "Unable to save role: {errors}", array('{errors}' => $errors)));
            }
            $this->redirect('manageRoles');
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
     * new updates to X2Engine.  This has been replaced with an option in the "Updater
     * Settings" page of the Admin tab.

      public function actionToggleUpdater() {
      $this->redirect('updaterSettings');
      } */
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
      } */

    /**
     * Render the changelog.
     *
     * This action renders the user changelog page, which contains a list of all
     * changes made by users within the app.
     */
    public function actionViewChangelog() {

        $model = new Changelog('search');
        if (isset($_GET['Changelog'])) {
            foreach ($_GET['Changelog'] as $field => $value) {
                if ($model->hasAttribute($field)) {
                    $model->$field = $value;
                }
            }
        }
        $this->render('viewChangelog', array(
            'model' => $model,
        ));
    }

    /**
     * Export all changelog entries to CSV
     */
    public function actionExportChangelog() {
        ini_set('memory_limit', -1);
        $csv = $this->safePath ('changelog.csv');
        $fp = fopen ($csv, 'w+');
        $meta = array_keys (Changelog::model()->attributes);
        fputcsv ($fp, $meta);
        $records = Changelog::model()->findAll();
        foreach ($records as $record) {
            $line = $record->attributes;
            fputcsv ($fp, $line);
        }
        fclose ($fp);
    }

    /**
     * Delete all changelog entries from the database.
     */
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
        $criteria->users = Yii::app()->user->getName();
        if (isset($_POST['Criteria'])) {
            $criteria->attributes = $_POST['Criteria'];
            $str = "";
            $arr = $criteria->users;
            if ($criteria->type == 'assignment' && count($arr) > 1) {
                $this->redirect('addCriteria');
            }
            if (isset($arr)) {
                $str = implode(', ', $arr);
            }
            $criteria->users = $str;
            if ($criteria->modelType != null && $criteria->comparisonOperator != null) {
                if ($criteria->save()) {
                    
                }
                $this->refresh();
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

        Criteria::model()->deleteByPk($id);
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

        LeadRouting::model()->deleteByPk($id);
        $this->redirect(array('roundRobinRules'));
    }

    /**
     * @deprecated
     * Deprecated function to set user timeout.
     *
     * This method formerly controlled the user session timeout settings for the
     * software.  This setting is now controlled by the "General Settings" page.

      public function actionSetTimeout() {

      $admin = &Yii::app()->settings; //Admin::model()->findByPk(1);
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
      } */
    /**
     * @deprecated
     * Deprecated method to set chat polling
     *
     * This method formerly controlled the configuration of chat polling requests.
     * This timeout is now set by the "General Settings" page.

      public function actionSetChatPoll() {

      $admin = &Yii::app()->settings; //X2Model::model('Admin')->findByPk(1);
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
      } */

    /**
     * Control general settings for the software.
     *
     * This method renders a page with settings for a variety of admin options.
     * This includes things like Contact name formatting, session timeout and
     * notification poll times, and basic privacy the for action history.
     * These settings are application wide and not per user.
     */
    public function actionAppSettings() {

        $admin = &Yii::app()->settings;
        if (isset($_POST['Admin'])) {

            // if(!isset($_POST['Admin']['ignoreUpdates']))
            // $admin->ignoreUpdates = 1;
            $oldFormat = $admin->contactNameFormat;
            $admin->attributes = $_POST['Admin'];
            foreach ($_POST['Admin'] as $attribute => $value) {
                if ($admin->hasAttribute($attribute)) {
                    $admin->$attribute = $value;
                }
            }
            if (isset($_POST['currency'])) {
                if ($_POST['currency'] == 'other') {
                    $admin->currency = $_POST['currency2'];
                    if (empty($admin->currency))
                        $admin->addError('currency', Yii::t('admin', 'Please enter a valid currency type.'));
                } else
                    $admin->currency = $_POST['currency'];
            }
            if ($oldFormat != $admin->contactNameFormat) {
                if ($admin->contactNameFormat == 'lastName, firstName') {
                    $command = Yii::app()->db->createCommand()->setText('UPDATE x2_contacts SET name=CONCAT(lastName,", ",firstName)')->execute();
                } elseif ($admin->contactNameFormat == 'firstName lastName') {
                    $command = Yii::app()->db->createCommand()->setText('UPDATE x2_contacts SET name=CONCAT(firstName," ",lastName)')->execute();
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

    /**
     * Render a page with options for activity feed settings.
     *
     * The administrator is allowed to configure what sort of information should
     * be displayed in the activity feed and for how long. This page sets options
     * for automated deletion of any chosen types after a set time period to help
     * keep the database cleaner.
     */
    public function actionActivitySettings() {

        $admin = &Yii::app()->settings;
        $admin->eventDeletionTypes = json_decode($admin->eventDeletionTypes, true);
        if (isset($_POST['Admin'])) {

            $admin->eventDeletionTime = $_POST['Admin']['eventDeletionTime'];
            $admin->eventDeletionTypes = json_encode($_POST['Admin']['eventDeletionTypes']);
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

        $admin = &Yii::app()->settings; //Admin::model()->findByPk(1);
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

        $admin = &Yii::app()->settings; //Admin::model()->findByPk(1);
        if (isset($_POST['Admin'])) {
            $routing = $_POST['Admin']['serviceDistribution'];
            $online = $_POST['Admin']['serviceOnlineOnly'];
            if ($routing == 'singleUser') {
                $user = $_POST['Admin']['srrId'];
                $admin->srrId = $user;
            } else if ($routing == 'singleGroup') {
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
     * allow for users to log in with their Google account and sync X2Engine's calendars
     * with their Google Calendar.
     */
    public function actionGoogleIntegration() {

        $admin = &Yii::app()->settings;
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

    public function actionTwitterIntegration () {
        $credId = Yii::app()->settings->twitterCredentialsId;

        if ($credId && ($cred = Credentials::model ()->findByPk ($credId))) {
            $params =  array ('id' => $credId);
        } else {
            $params = array('class' => 'TwitterApp');
        }
            $url = Yii::app()->createUrl('/profile/createUpdateCredentials', $params);
            $this->redirect ($url);
    }

    /**
     * Configure email settings.
     *
     * This allows for configuration of how emails are handled by X2Engine.  The admin
     * can select to use the server that the software is hosted on or a separate mail server.
     */
    public function actionEmailSetup() {

        $admin = &Yii::app()->settings;
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/manageCredentials.js');
        if (isset($_POST['Admin'])) {
            $admin->attributes = $_POST['Admin'];

            if ($admin->save()) {
                $this->redirect('emailSetup');
            }
        } else {
            // set defaults
            if (!isset($admin->doNotEmailLinkText))
                $admin->doNotEmailLinkText = Admin::getDoNotEmailLinkDefaultText();
            if (!isset($admin->doNotEmailPage))
                $admin->doNotEmailPage = Admin::getDoNotEmailDefaultPage();
        }

        $this->render('emailSetup', array(
            'model' => $admin,
        ));
    }

    /**
     * Form/submit action for adding or customizing a field.
     *
     * This method allows for the creation of custom fields linked to any customizable
     * module in X2Engine.  This is used by "Manage Fields." It is used to reload the
     * form via AJAX.
     * 
     * @param bool $search If set to 1/true, perform a lookup for an existing field
     * @param bool $save If set to 1/true, attempt to save the model; otherwise just echo the form.
     */
    public function actionCreateUpdateField($search = 0, $save = 0, $override = 0) {
        $changedType = false;
        if ($search) {
            // A field is being looked up, to populate form fields for customizing
            // an existing field
            $new = false;
            if (isset($_POST['Fields'])) {
                $model = Fields::model()->findByAttributes(array_intersect_key($_POST['Fields'], array_fill_keys(array('modelName', 'fieldName'), null)));
            }
        } else {
            // Requesting the form
            $new = true;
        }
        if (!isset($model) || !(bool) $model) {
            // If the field model wasn't found, create the object
            $model = new Fields;
        }

        if (isset($_POST['Fields']) && ($model->isNewRecord || $override)) {
            $oldType = $model->type;
            $model->attributes = $_POST['Fields'];
            // field name exists if model refers to actual db record
            if ($model->fieldName && $model->type !== $oldType) $changedType = true;
        }

        $message = '';
        $error = false;

        if (isset($_POST['Fields']) && $save) {
            $model->attributes = $_POST['Fields'];
            if (!isset ($_POST['Fields']['linkType'])) {
                // This can be removed if we ever make the linkType attribute more structured
                // (i.e. field type-specific link type validation rules)
                $model->linkType = null;
            }

            // Set the default value
            if (isset($_POST['AmorphousModel'])) {
                $aModel = $_POST['AmorphousModel'];
                $model->defaultValue = $model->parseValue($aModel['customized_field']);
            }

            $new = $model->isNewRecord;
            $model->modified = 1; // The field has been modified
            if ($new) // The field should be marked as custom since the user is adding it
                $model->custom = 1;

            if ($model->save()) {
                $message = $new ? Yii::t('admin', 'Field added.') : Yii::t('admin', 'Field modified successfully.');
                if ($new) {
                    $model = new Fields;
                }
            } else {
                $error = true;
                $message = Yii::t('admin', 'Please correct the following errors.');
            }
        }
        $dummyModel = new AmorphousModel;
        $dummyModel->addField($model, 'customized_field');
        $dummyModel->setAttribute('customized_field', $model->defaultValue);

        $this->renderPartial('createUpdateField', array(
            'model' => $model,
            'new' => $new,
            'dummyModel' => $dummyModel,
            'message' => $message,
            'error' => $error,
            'changedType' => $changedType,
        ));
    }

    /**
     * Delete a field.
     *
     * This method allows for the deletion of custom fields.  Default fields cannot
     * be deleted in this way.
     */
    public function actionRemoveField($getCount = false) {

        if (isset($_POST['field']) && $_POST['field'] != "") {
            $id = $_POST['field'];
            $field = Fields::model()->findByPk($id);
            if ($getCount) {
                $nonNull = $field->countNonNull();
                if ($nonNull) {
                    echo Yii::t('admin', 'This field contains data; it is non-empty in {n} records.', array(
                        '{n}' => '<span style="color:red;font-weight:bold">' . $nonNull . '</span>'
                            )
                    );
                } else {
                    echo Yii::t('admin', 'The field appears to be empty. Deleting it will not result in any data loss.');
                }
                Yii::app()->end();
            }
            $field->delete();
        }
        $this->redirect('manageFields');
    }

    /**
     * General field management.
     *
     * This action serves as the landing page for all of the custom field related
     * actions within the software.
     */
    public function actionManageFields() {
        // New model for the form:
        $model = new Fields;

        // Set up grid view:
        $searchModel = new Fields('search');
        $criteria = new CDbCriteria;
        $criteria->addCondition('modified=1');
        $searchModel->setAttributes(
                isset($_GET['Fields']) ? $_GET['Fields'] : array(), false);
        foreach ($searchModel->attributes as $name => $value) {
            $criteria->compare($name, $value);
        }
        $pageSize = Profile::getResultsPerPage();
        $dataProvider = new SmartActiveDataProvider('Fields', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => Profile::getResultsPerPage(),
            ),
        ));

        // Set up fields list
        $fields = Fields::model()->findAllByAttributes(array('custom' => '1'));
        $arr = array();
        foreach ($fields as $field) {
            $arr[$field->id] = $field->attributeLabel;
        }

        $this->render('manageFields', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
            'searchModel' => $searchModel,
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
        $existingDocs = X2Model::model('Docs')->findAll();
        $existingDocs = CHtml::listData($existingDocs, 'id', 'name');
        $model = new Docs;
        $users = User::getNames();
        if (isset($_POST['Docs'])) {

            $model->attributes = $_POST['Docs'];
            $arr = $model->editPermissions;
            if (isset($arr))
                $model->editPermissions = Fields::parseUsers($arr);
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
                    $this->redirect(array('/docs/docs/view', 'id' => $model->id, 'static' => 'true'));
                }
            }
        } else if (isset($_POST['existingDoc'])) {
            $existingDoc = urldecode($_POST['existingDoc']);
            $docRecord = X2Model::model('Docs')->findByAttributes(array('name' => $existingDoc));
            if (!is_null($docRecord)) {
                $module = new Modules;
                $module->adminOnly = 0;
                $module->toggleable = 1;
                $module->custom = 1;
                $module->visible = 1;
                $module->editable = 0;
                $module->searchable = 0;
                $module->menuPosition = Modules::model()->count();
                $module->name = 'document';
                $module->title = $docRecord->name;
                if ($module->save()) {
                    echo $docRecord->id;
                    Yii::app()->end();
                } else {
                    $this->refresh();
                }
            }
        }

        $this->render('createPage', array(
            'model' => $model,
            'users' => $users,
            'existingDocs' => $existingDocs,
        ));
    }

    /**
     * @deprecated
     * View a page that has been created.
     *
     * This method is what is called when a user clicks the top bar link to a static
     * page that has been previously created.  Nearly identical to a document view
     * but without the widgets in the layout. This function is no longer used in
     * favor of the document view action.
     *
     * @param int $id The ID of the page being viewed.
     */
    public function actionViewPage($id) {
        $model = CActiveRecord::model('Docs')->findByPk($id);
        if (!isset($model))
            $this->redirect(array('/docs/docs/index'));

        $this->render('viewTemplate', array(
            'model' => $model,
        ));
    }

    /**
     * Change the title of a module.
     *
     * This allows for the configuration of the display name of a module. Before
     * version 5.0, this would not affect text other than the top bar menu.
     */
    public function actionRenameModules() {

        $order = Modules::model()->findAllByAttributes(
                array(
                    'visible' => 1,
                )
        );
        $menuItems = array();
        $itemNames = array();
        foreach($order as $module){
            $menuItems[$module->name] = Yii::t('app', $module->title);
            if ($module->custom || $module->name === 'bugReports')
                $itemNames[$module->name] = Modules::itemDisplayName($module->name);
        }
        foreach ($menuItems as $key => $value) {
            $menuItems[$key] = preg_replace('/&#58;/', ':', $value); // decode any colons
        }

        if (isset($_POST['module']) && isset($_POST['name'])) {
            $module = $_POST['module'];
            $name = $_POST['name'];

            $moduleRecord = Modules::model()->findByAttributes(
                    array(
                        'name' => $module,
                        'title' => $menuItems[$module],
                    )
            );
            if(isset($moduleRecord)){
                $match = Modules::model()->findByAttributes(array('title'=>$name));
                $itemName = isset($_POST['itemName'])? $_POST['itemName'] : "";

                if (empty($name)) {
                    Yii::app()->user->setFlash('error', Yii::t('admin', "You must specify a title."));
                } else if (isset($match) && ($match->name !== $moduleRecord->name)) {
                    Yii::app()->user->setFlash('error', Yii::t('admin', "A module with this title already exists."));
                } else {
                    $moduleRecord->title = $name;
                    if (!empty($itemName) && ($moduleRecord->custom || $moduleRecord->name === 'bugReports'))
                        $moduleRecord->itemName = $itemName;

                    if ($moduleRecord->retitle ($name)) {
                        $this->redirect('index');
                    }
                }
            }
        }

        $this->render('renameModules', array(
            'modules' => $menuItems,
            'itemNames' => $itemNames,
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
                    $menuItems[$module->name] = Yii::t('app', $module->title);
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
     * the X2Engine logo in the top left corner of the software.
     */
    public function actionUploadLogo() {
        if (isset($_FILES['logo-upload'])) {
            $temp = CUploadedFile::getInstanceByName('logo-upload');
            if ($temp === null) {
                Yii::app()->user->setFlash('error', Yii::t('admin', 'There was an error uploading your logo.'));
                $this->redirect('uploadLogo');
            }
            $name = $temp->getName();
            $allowedExtensions = array('gif', 'jpg', 'jpeg', 'tif', 'tiff', 'bmp', 'png');
            if (!in_array($temp->getExtensionName(), $allowedExtensions)) {
                Yii::app()->user->setFlash('error', Yii::t('admin', 'Invalid file extension.'));
                $this->redirect('uploadLogo');
            }
            $temp->saveAs('uploads/logos/' . $name);
            $admin = Profile::model()->findByPk(1);
            $logo = Media::model()->findByAttributes(array('associationId' => $admin->id, 'associationType' => 'logo'));
            if (isset($logo)) {
                if (file_exists($logo->fileName))
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
     * Reverts the logo back to X2Engine.
     */
    public function actionToggleDefaultLogo() {

        $adminProf = Yii::app()->params->adminProfile;
        $logo = Media::model()->findByAttributes(array('associationId' => $adminProf->id, 'associationType' => 'logo'));
        if (!isset($logo)) {

            $logo = new Media;
            $logo->associationType = 'logo';
            $name = 'yourlogohere.png';
            $logo->associationId = $adminProf->id;
            $logo->fileName = 'uploads/logos/' . $name;

            if ($logo->save()) {
                
            }
        } else if ($logo->fileName != 'uploads/logos/yourlogohere.png') {
            $logo->delete();
        }
        $this->redirect(array('index'));
    }

    /**
     * Create or edit translations.
     *
     * This method allows the admin to access the X2Engine built in translation manager.
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
     * Function to convert custom modules to be in line with the current codebase.
     *
     * This function takes any pre-3.5.1 custom module and performs all necessary
     * operations to make the module compatible with the latest version. Additionally
     * an optional "updateFlag" parameter can be passed, in which case the custom
     * module will have its file contents re-generated to be at the latest version
     * of the template files.
     * TODO: clean up backupFlag code. backupFlag checks no longer necessary since conversion now
     * aborts when backup fails.
     */
    public function actionConvertCustomModules() {
        $status = array();
        if (!empty($_POST)) {
            $updateFlag = false;
            if (isset($_POST['updateFlag']) && $_POST['updateFlag'] == "Yes") {
                $updateFlag = true; // We need to update file contents as well.
            }
            $modules = X2Model::model('Modules')->findAllByAttributes(array('custom' => 1));
            if (count ($modules)==0){ // There are no custom modules...
                $status['admin']['error']=Yii::t('admin','Fatal error - No custom modules found.');
                $status['admin']['title']=Yii::t('admin','Module Conversion');
            }
            foreach ($modules as $module){
                $moduleName = $module->name;
                $modulePath = 'protected/modules/'.$moduleName;
                $ucName = ucfirst($moduleName);
                if (is_dir ($modulePath)){
                    $failed = false;
                    // Log everything in the "status" array
                    $status[$moduleName] = array(
                        'title'=>$module->title,
                        'messages'=>array(),
                        'error'=>null
                    );
                    $status[$moduleName]['messages'][] = Yii::t('admin',"Module exists").
                        ": $moduleName";
                    // Attempt to make a backup
                    if (FileUtil::ccopy($modulePath, 'backup/modules/'.$moduleName)) {
                        $backupFlag = true;
                        $status[$moduleName]['messages'][] = Yii::t('admin','Module successfully '.
                            'backed up in backup/modules/{moduleName}',array(
                                '{moduleName}'=>$moduleName
                        ));
                    } else {
                        $backupFlag = false;
                        $status[$moduleName]['error'] = Yii::t('admin',
                            'Backup failed. Unable to write to backup directory. '.
                            'Aborting module conversion.');
                        $this->render('convertCustomModules', array(
                            'status' => $status,
                        ));
                        Yii::app()->end ();
                    }
                    if (file_exists ($modulePath.'/controllers/DefaultController.php')) {
                        // Controller needs to be updated to the new format
                        $renamed = rename ($modulePath.'/controllers/DefaultController.php',
                            $modulePath.'/controllers/'.$ucName.'Controller.php'
                        );
                        if ($renamed) {
                            $status[$moduleName]['messages'][] = Yii::t('admin',
                                '{default} still existed and was successfully renamed to '.
                                '{controller}.',array(
                                    '{default}'=>'DefaultController',
                                    '{controller}'=>$ucName.'Controller',
                            ));
                            $file = Yii::app()->file->set($modulePath.'/controllers/'.
                                $ucName.'Controller.php');
                            $contents = $file->getContents();
                            $contents = str_replace(array('DefaultController'),
                                array($ucName.'Controller'), $contents);
                            $success = $file->setContents($contents);

                            if($success !== false){
                                $status[$moduleName]['messages'][]=Yii::t('admin',
                                    'Class declaration successfully altered.');
                            }else{
                                $status[$moduleName]['error']=Yii::t('admin',
                                    'Fatal error - Unable to change class declaration. '.
                                    'Aborting module conversion.');
                                $failed = true;
                                if($backupFlag){
                                    FileUtil::rrmdir($modulePath);
                                    if (FileUtil::ccopy('backup/modules/'.$moduleName, $modulePath)) {
                                        $status[$moduleName]['error'] .= " ".Yii::t('admin',
                                            'Module backup was successfully restored.');
                                    }
                                }
                            }
                        } else { // Fail for this module, restore from backup if we were able to.
                            $status[$moduleName]['error'] = Yii::t('admin',
                                'Fatal error - Unable to rename controller class. '.
                                'Aborting module conversion.');
                            $failed = true;
                            if($backupFlag){
                                FileUtil::rrmdir($modulePath);
                                if(FileUtil::ccopy('backup/modules/'.$moduleName, $modulePath)) {
                                    $status[$moduleName]['error'].=" ".Yii::t('admin',
                                        'Module backup was successfully restored.');
                                }
                            }
                        }
                    }
                    if (is_dir ($modulePath.'/views/default' && !$failed)) {
                        // The view files need to be updated to the new format
                        if (is_dir($modulePath.'/views/'.$moduleName)) {
                            FileUtil::ccopy($modulePath.'/views/default/',
                                $modulePath.'/views/'.$moduleName);
                            $status[$moduleName]['messages'][] = Yii::t('admin',
                                'Module view folder already exists. View files successfully copied.');
                        }else {
                            $renamed = rename ($modulePath.'/views/default',
                                $modulePath.'/views/'.$moduleName);
                            if ($renamed){
                                $status[$moduleName]['messages'][] = Yii::t('admin',
                                    'Module view folder successfully renamed.');
                            }else{
                                $status[$moduleName]['error'] = Yii::t('admin',
                                    'Fatal error - Unable to rename module view folder. '.
                                    'Aborting module conversion.');
                               $failed = true;
                                if($backupFlag){
                                    FileUtil::rrmdir($modulePath);
                                    if(FileUtil::ccopy('backup/modules/'.$moduleName, $modulePath)){
                                        $status[$moduleName]['error'] .= " ".Yii::t('admin',
                                            'Module backup was successfully restored.');
                                    }
                                }
                            }
                        }
                    }
                    $viewDir = $modulePath.'/views/'.$moduleName;
                    if (is_dir($viewDir) && !$failed) {
                        // Update view files to use item name from database
                        $viewFiles = scandir ($viewDir);
                        $success = true;
                        foreach ($viewFiles as $filename) {
                            if (!preg_match('/^\w+\.php$/', $filename))
                                continue;
                            $file = Yii::app()->file->set($viewDir."/".$filename);
                            $contents = $file->getContents();
                            $contents = str_replace('$moduleConfig[\'recordName\']',
                                'Modules::itemDisplayName()', $contents);
                            if ($filename === 'index.php') {
                                // Replace the gridview title
                                $searchPattern = '\'title\'=>$moduleConfig[\'title\']';
                                $replacement = '\'title\'=>Modules::displayName(true, '.
                                    '$moduleConfig[\'moduleName\'])';
                                $contents = str_replace($searchPattern, $replacement, $contents);
                            }
                            $success = $success && $file->setContents($contents);
                        }
                        if (!$success) {
                            $status[$moduleName]['error']=Yii::t('admin',
                                'Fatal error - Unable to update view files. '.
                                'Aborting module conversion.');
                            $failed = true;
                            if ($backupFlag) {
                                FileUtil::rrmdir($modulePath);
                                if (FileUtil::ccopy('backup/modules/'.$moduleName, $modulePath)) {
                                    $status[$moduleName]['error'] .= " ".Yii::t('admin',
                                        'Module backup was successfully restored.');
                                }
                            }
                        }
                    }

                    $auth = Yii::app()->authManager;
                    // Check for a common access item's existence
                    $testItem = $auth->getAuthItem($ucName.'ReadOnlyAccess');
                    // It doesn't exist, we need to create permissions for this module.
                    if(is_null($testItem)){
                        $this->createDefaultModulePermissions ($ucName);
                        $status[$moduleName]['messages'][] = Yii::t('admin',
                            'Permissions configuration complete.');
                    }
                    if($updateFlag){
                        // If they specified we need to update, re-generate the custom module
                        // from the template files.
                        include('protected/modules/'.$moduleName.'/'.$moduleName.'Config.php');
                        $this->createSkeletonDirectories($moduleName);
                        $this->writeConfig($moduleConfig['title'], $moduleConfig['moduleName'],
                            $moduleConfig['recordName']);
                        $status[$moduleName]['messages'][]=Yii::t('admin',
                            'Module files updated to the latest version.');
                    }
                }
            }
            $authCache = Yii::app()->authCache;
            if (isset($authCache)) // Auth cache needs to be cleared to reset cached permissions
                $authCache->clear();
        }
        $this->render('convertCustomModules', array(
            'status' => $status,
        ));
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

            // are there any non-alphanumeric or _ chars? or non-alpha characters at the beginning?
            if (preg_match('/\W/', $moduleName) || preg_match('/^[^a-zA-Z]+/', $moduleName)) {
                $errors[] = Yii::t('module', 'Invalid table name'); //$this->redirect('createModule');
            }

            if ($moduleName == '') // we will attempt to use the title as the backend name, if possible
                $moduleName = $title;

            if ($recordName == '') // use title for record name if none is provided
                $recordName = $title;

            $trans = include('protected/data/transliteration.php');

            // replace characters with their A-Z equivalent, if possible
            $moduleName = strtolower(strtr($moduleName, $trans));

            // now remove all remaining non-alphanumeric or _ chars
            $moduleName = preg_replace('/\W/', '', $moduleName);

            // remove any numbers or _ from the beginning
            $moduleName = preg_replace('/^[0-9_]+/', '', $moduleName);


            if ($moduleName == '') { // if there is nothing left of moduleName at this point,
                $moduleName = 'module' . substr(time(), 5);  // just generate a random one
            }

            if (!is_null(Modules::model()->findByAttributes(array('title' => $title))) ||
                    !is_null(Modules::model()->findByAttributes(array('name' => $moduleName)))) {
                $errors[] = Yii::t('module', 'A module with that name already exists');
            }
            if (empty($errors)) {
                $dirFlag = false;
                $configFlag = false;
                $tableFlag = false;
                try {
                    $this->createSkeletonDirectories($moduleName);
                    $dirFlag = true; // Try to create the fileset
                    $this->writeConfig($title, $moduleName, $recordName);
                    $configFlag = true; // Write the configuration
                    $this->createNewTable($moduleName);
                    $tableFlag = true; // Create the DB table
                } catch (Exception $e) {
                    /*
                     * If any of the operations in the try block fail, we need
                     * to roll back whatever successfully happened before that.
                     * The flag variables below indicate which rollback operations
                     * to take.
                     */
                    if ($dirFlag) {
                        FileUtil::rrmdir('protected/modules/' . $moduleName);
                    } else {
                        $errors[] = Yii::t('module', 'Unable to create custom module directory.');
                    }
                    if ($configFlag) {
                        // Nothing, already taken care of by the file delete above
                    } elseif ($dirFlag) {
                        $errors[] = Yii::t('module', 'Unable to create config file for custom module.');
                    }
                    if ($tableFlag) {
                        $this->deleteTable($moduleName);
                    } elseif ($dirFlag && $configFlag) {
                        $errors[] = Yii::t('module', 'Unable to create table for custom module.');
                    }
                }
                if (empty($errors)) {
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

                    Yii::import('application.modules.' . $moduleName . '.models.*');
                    $layoutModel = new FormLayout;
                    $layoutModel->model = ucfirst($moduleName);
                    $layoutModel->version = "Default";
                    $layoutModel->layout = X2Model::getDefaultFormLayout($moduleName);
                    $layoutModel->createDate = time();
                    $layoutModel->lastUpdated = time();
                    $layoutModel->defaultView = true;
                    $layoutModel->defaultForm = true;
                    $layoutModel->save();

                    $this->redirect(array('/' . $moduleName . '/index'));
                }
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
			nameId VARCHAR(250) DEFAULT NULL,
			description TEXT,
			createDate INT,
			lastUpdated INT,
			updatedBy VARCHAR(250),
            UNIQUE(nameId)
			) COLLATE = utf8_general_ci",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, readOnly, keyType) VALUES ('$moduleTitle', 'id', 'ID', '0', '1','PRI')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, readOnly, keyType) VALUES ('$moduleTitle', 'nameId', 'nameId', '0', '1','FIX')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type, required) VALUES ('$moduleTitle', 'name', 'Name', '0', 'varchar', '1')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'assignedTo', 'Assigned To', '0', 'assignment')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('$moduleTitle', 'description', 'Description', '0', 'text')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type, readOnly) VALUES ('$moduleTitle', 'createDate', 'Create Date', '0', 'date', '1')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type, readOnly) VALUES ('$moduleTitle', 'lastUpdated', 'Last Updated', '0', 'date', '1')",
            "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type, readOnly) VALUES ('$moduleTitle', 'updatedBy', 'Updated By', '0', 'assignment', '1')");
        foreach ($sqlList as $sql) {
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
        $this->createDefaultModulePermissions($moduleTitle);
    }

    /**
     * Private helper method to create initial permissions structure when
     * creating a new custom module or importing one
     * @param string $moduleName Name of the module
     */
    private function createDefaultModulePermissions($moduleName) {
        $auth = Yii::app()->authManager;
        $authRule = 'return $this->checkAssignment($params);';
        $guestSite = $auth->getAuthItem('GuestSiteFunctionsTask');
        $auth->createOperation($moduleName . 'GetItems');  // Guest Access
        $auth->createOperation($moduleName . 'View');  // Read Only
        $auth->createOperation($moduleName . 'Create');  // Basic Access
        $auth->createOperation($moduleName . 'Update');  // Update Access
        $auth->createOperation($moduleName . 'Index');  // Minimum Requirements
        $auth->createOperation($moduleName . 'Admin');  // Admin Access
        $auth->createOperation($moduleName . 'Delete');  // Full Access
        $auth->createOperation($moduleName . 'GetTerms');  // Minimum Requirements
        $auth->createOperation($moduleName . 'DeleteNote');  // Full Access
        $auth->createOperation($moduleName . 'Search');  // Minimum Requirements
        // Access Group Definitions
        $roleAdminAccess = $auth->createTask($moduleName . 'AdminAccess');
        $roleFullAccess = $auth->createTask($moduleName . 'FullAccess');
        $rolePrivateFullAccess = $auth->createTask($moduleName . 'PrivateFullAccess');
        $roleUpdateAccess = $auth->createTask($moduleName . 'UpdateAccess');
        $rolePrivateUpdateAccess = $auth->createTask($moduleName . 'PrivateUpdateAccess');
        $roleBasicAccess = $auth->createTask($moduleName . 'BasicAccess');
        $roleReadOnlyAccess = $auth->createTask($moduleName . 'ReadOnlyAccess');
        $rolePrivateReadOnlyAccess = $auth->createTask($moduleName . 'PrivateReadOnlyAccess');
        $roleMinimumRequirements = $auth->createTask($moduleName . 'MinimumRequirements');

        // Private Task Definitions
        $rolePrivateDelete = $auth->createTask($moduleName . 'DeletePrivate', 'Delete their own records', $authRule);
        $rolePrivateDelete->addChild($moduleName . 'Delete');
        $rolePrivateDelete->addChild($moduleName . 'DeleteNote');
        $rolePrivateUpdate = $auth->createTask($moduleName . 'UpdatePrivate', 'Update their own records', $authRule);
        $rolePrivateUpdate->addChild($moduleName . 'Update');
        $rolePrivateView = $auth->createTask($moduleName . 'ViewPrivate', 'View their own record', $authRule);
        $rolePrivateView->addChild($moduleName . 'View');

        // Guest Requirements
        $guestSite->addChild($moduleName . 'GetItems');

        // Minimum Requirements
        $roleMinimumRequirements->addChild($moduleName . 'Index');
        $roleMinimumRequirements->addChild($moduleName . 'GetTerms');
        $roleMinimumRequirements->addChild($moduleName . 'Search');

        // Read Only
        $roleReadOnlyAccess->addChild($moduleName . 'MinimumRequirements');
        $roleReadOnlyAccess->addChild($moduleName . 'View');

        // Private Read Only
        $rolePrivateReadOnlyAccess->addChild($moduleName . 'MinimumRequirements');
        $rolePrivateReadOnlyAccess->addChild($moduleName . 'ViewPrivate');

        // Basic Access
        $roleBasicAccess->addChild($moduleName . 'MinimumRequirements');
        $roleBasicAccess->addChild($moduleName . 'Create');

        // Update Access
        $roleUpdateAccess->addChild($moduleName . 'MinimumRequirements');
        $roleUpdateAccess->addChild($moduleName . 'Update');

        // Private Update Access
        $rolePrivateUpdateAccess->addChild($moduleName . 'MinimumRequirements');
        $rolePrivateUpdateAccess->addChild($moduleName . 'UpdatePrivate');

        // Full Access
        $roleFullAccess->addChild($moduleName . 'MinimumRequirements');
        $roleFullAccess->addChild($moduleName . 'Delete');
        $roleFullAccess->addChild($moduleName . 'DeleteNote');

        // Private Full Access
        $rolePrivateFullAccess->addChild($moduleName . 'MinimumRequirements');
        $rolePrivateFullAccess->addChild($moduleName . 'DeletePrivate');

        // Admin Access
        $roleAdminAccess->addChild($moduleName . 'MinimumRequirements');
        $roleAdminAccess->addChild($moduleName . 'Admin');

        // Assign the permissions to roles
        $defaultRole = $auth->getAuthItem('DefaultRole');
        $defaultRole->removeChild($moduleName . 'Index');
        $defaultRole->addChild($moduleName . 'UpdateAccess');
        $defaultRole->addChild($moduleName . 'BasicAccess');
        $defaultRole->addChild($moduleName . 'ReadOnlyAccess');
        $defaultRole->addChild($moduleName . 'MinimumRequirements');

        $adminRole = $auth->getAuthItem('administrator');
        $adminRole->removeChild($moduleName . 'Admin');
        $adminRole->addChild($moduleName . 'AdminAccess');
        $adminRole->addChild($moduleName . 'FullAccess');
        $adminRole->addChild($moduleName . 'UpdateAccess');
        $adminRole->addChild($moduleName . 'PrivateUpdateAccess');
        $adminRole->addChild($moduleName . 'BasicAccess');
        $adminRole->addChild($moduleName . 'ReadOnlyAccess');
        $adminRole->addChild($moduleName . 'MinimumRequirements');
    }

    /**
     * Cleanup operation for custom modules. This is run on deletion to remove
     * the database table.
     * @param string $moduleName The name of the module being deleted
     */
    private function deleteTable($moduleName) {
        $moduleTitle = ucfirst($moduleName);
        $ucName = $moduleTitle;
        $sqlList = array(
            'DROP TABLE IF EXISTS `x2_' . $moduleName . '`',
            'DELETE FOM x2_fields WHERE modelName="' . $moduleTitle . '"',
        );
        foreach ($sqlList as $sql) {
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
        $auth = Yii::app()->authManager;
        $auth->removeAuthItem($ucName . 'GetItems');
        $auth->removeAuthItem($ucName . 'View');
        $auth->removeAuthItem($ucName . 'Create');
        $auth->removeAuthItem($ucName . 'Update');
        $auth->removeAuthItem($ucName . 'Index');
        $auth->removeAuthItem($ucName . 'Admin');
        $auth->removeAuthItem($ucName . 'Delete');
        $auth->removeAuthItem($ucName . 'GetTerms');
        $auth->removeAuthItem($ucName . 'DeleteNote');
        $auth->removeAuthItem($ucName . 'Search');
        $auth->removeAuthItem($ucName . 'AdminAccess');
        $auth->removeAuthItem($ucName . 'FullAccess');
        $auth->removeAuthItem($ucName . 'PrivateFullAccess');
        $auth->removeAuthItem($ucName . 'UpdateAccess');
        $auth->removeAuthItem($ucName . 'PrivateUpdateAccess');
        $auth->removeAuthItem($ucName . 'BasicAccess');
        $auth->removeAuthItem($ucName . 'ReadOnlyAccess');
        $auth->removeAuthItem($ucName . 'PrivateReadOnlyAccess');
        $auth->removeAuthItem($ucName . 'MinimumRequirements');
    }

    /**
     * Create file system for a custom module
     *
     * This method is called by {@link AdminController::actionCreateModule} as a
     * part of creating a new module.  This method copies all the proper files to
     * their new directories, renames them, and replaces the contents to fit the
     * new module name.
     *
     * @param string $moduleName The name of the module being created
     */
    private function createSkeletonDirectories($moduleName) {

        $errors = array();

        $templateFolderPath = 'protected/modules/template/';
        $moduleFolderPath = 'protected/modules/' . $moduleName . '/';

        $moduleFolder = Yii::app()->file->set($moduleFolderPath);
        if (!$moduleFolder->exists && $moduleFolder->createDir() === false)
            throw new Exception('Error creating module folder "' . $moduleFolderPath . '".');

        if (Yii::app()->file->set($templateFolderPath)->copy($moduleName) === false)
            throw new Exception('Error copying Template folder "' . $templateFolderPath . '".');

        // list of files to process
        $fileNames = array(
            'register.php',
            'templatesConfig.php',
            'TemplatesModule.php',
            'controllers/TemplatesController.php',
            'data/install.sql',
            'data/uninstall.sql',
            'models/Templates.php',
            'views/templates/_search.php',
            'views/templates/_view.php',
            'views/templates/admin.php',
            'views/templates/create.php',
            'views/templates/index.php',
            'views/templates/update.php',
            'views/templates/view.php',
        );

        foreach ($fileNames as $fileName) {
            // calculate proper file name
            $fileName = $moduleFolderPath . $fileName;

            $file = Yii::app()->file->set($fileName);
            if (!$file->exists)
                throw new Exception('Unable to find template file "' . $fileName . '".');

            // rename files
            $newFileName = str_replace(array('templates', 'Templates'), array($moduleName, ucfirst($moduleName)), $file->filename);
            if ($file->setFileName($newFileName) === false)
                throw new Exception('Error renaming template file "' . $fileName . '" to "' . $newFileName . '".');

            // chmod($file->filename, 0755);
            // $file->setPermissions(0755);
            // replace "template", "Templates", etc within the file
            $contents = $file->getContents();
            $contents = str_replace(array('templates', 'Templates'), array($moduleName, ucfirst($moduleName)), $contents);

            if ($file->setContents($contents) === false)
                throw new Exception('Error modifying template file "' . $newFileName . '".');
        }
        if (!is_dir('protected/modules/' . $moduleName . '/views/' . $moduleName)) {
            rename('protected/modules/' . $moduleName . '/views/templates', 'protected/modules/' . $moduleName . '/views/' . $moduleName);
        } else {
            FileUtil::ccopy('protected/modules/' . $moduleName . '/views/templates', 'protected/modules/' . $moduleName . '/views/' . $moduleName);
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
    private function writeConfig($title, $moduleName, $recordName) {

        $configFilePath = 'protected/modules/' . $moduleName . '/' . $moduleName . 'Config.php';
        $configFile = Yii::app()->file->set($configFilePath, true);

        $contents = str_replace(
                array(
            '{title}',
            '{moduleName}',
            '{recordName}',
                ), array(
            addslashes($title),
            addslashes($moduleName),
            addslashes($recordName),
                ), $configFile->getContents()
        );

        if ($configFile->setContents($contents) === false)
            throw new Exception('Error writing to config file "' . $configFilePath . '".');
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
                if ($module->name != 'document' && $module->delete())
                    $this->deleteModuleData ($moduleName);
                else
                    $module->delete();
            }
            $this->redirect(array('/admin/index'));
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
     * Helper function to remove the files and SQL data associated with a module
     * @param string $moduleName Name of the module to delete
     */
    private function deleteModuleData($moduleName) {
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
                    try {
                        $query->execute();
                    } catch (CDbException $e) {
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
        X2Model::model('Fields')->updateAll(array('linkType' => null, 'type' => 'varchar'), "linkType='$moduleName'");
        X2Model::model('FormLayout')->deleteAllByAttributes(array('model' => $moduleName));
        X2Model::model('Relationships')->deleteAll('firstType = :model OR secondType = :model', array(':model' => $moduleName));
        $auth = Yii::app()->authManager;
        $ucName = ucfirst($moduleName);
        $auth->removeAuthItem($ucName . 'GetItems');
        $auth->removeAuthItem($ucName . 'View');
        $auth->removeAuthItem($ucName . 'Create');
        $auth->removeAuthItem($ucName . 'Update');
        $auth->removeAuthItem($ucName . 'Index');
        $auth->removeAuthItem($ucName . 'Admin');
        $auth->removeAuthItem($ucName . 'Delete');
        $auth->removeAuthItem($ucName . 'GetTerms');
        $auth->removeAuthItem($ucName . 'DeleteNote');
        $auth->removeAuthItem($ucName . 'Search');
        $auth->removeAuthItem($ucName . 'AdminAccess');
        $auth->removeAuthItem($ucName . 'FullAccess');
        $auth->removeAuthItem($ucName . 'PrivateFullAccess');
        $auth->removeAuthItem($ucName . 'UpdateAccess');
        $auth->removeAuthItem($ucName . 'PrivateUpdateAccess');
        $auth->removeAuthItem($ucName . 'BasicAccess');
        $auth->removeAuthItem($ucName . 'ReadOnlyAccess');
        $auth->removeAuthItem($ucName . 'PrivateReadOnlyAccess');
        $auth->removeAuthItem($ucName . 'MinimumRequirements');
        $auth->removeAuthItem($ucName . 'ViewPrivate');
        $auth->removeAuthItem($ucName . 'UpdatePrivate');
        $auth->removeAuthItem($ucName . 'DeletePrivate');

        FileUtil::rrmdir('protected/modules/' . $moduleName);
    }

    /**
     * Export the mapping from the model importer
     */
    public function actionExportMapping() {
        $model = $_POST['model'];
        $name = (isset($_POST['name']) && !empty($_POST['name'])) ? $_POST['name'] : "Unknown";
        $name .= " to X2Engine " . Yii::app()->params->version;
        $keys = $_POST['keys'];
        $attributes = $_POST['attributes'];
        $this->verifyImportMap($model, $keys, $attributes, true);
        if (!empty($_SESSION['importMap'])) {
            $map = array('name' => $name, 'mapping' => $_SESSION['importMap']);
            $mapFile = fopen($this->safePath('importMapping.json'), 'w');
            fwrite($mapFile, json_encode($map));
            fclose($mapFile);
        }
    }

    /**
     * Export records from a model
     */
    public function actionExportModels($listId = null) {
        unset($_SESSION['modelExportFile'], $_SESSION['exportModelCriteria'], $_SESSION['modelExportMeta']);
        $modelList = Modules::getExportableModules();
        // Determine the model selected by the user
        if (isset($_GET['model']) || isset($_POST['model'])) {
            $model = (isset($_GET['model'])) ? $_GET['model'] : $_POST['model'];
            $modelName = str_replace(' ', '', $model);
        }
        if (isset($model) && in_array($modelName, array_keys($modelList))) {
            $staticModel = X2Model::model($modelName);
            $modulePath = '/' . $staticModel->module;
            $modulePath .= $modulePath;
            if (is_null($listId) || $model != 'Contacts') {
                $file = "records_export.csv";
                $listName = CHtml::link(
                    Yii::t('admin', 'All {model}', array(
                        '{model}' => $model)),
                    array($modulePath . '/index'),
                    array('style' => 'text-decoration:none;')
                );

                // Forcefully disable eager loading so it doesn't go super-slow)
                $_SESSION['exportModelCriteria'] = new CDbCriteria();
                $_SESSION['exportModelCriteria']->with = array();
            } else {
                $list = X2List::load($listId);
                $_SESSION['exportModelCriteria'] = $list->queryCriteria();
                $file = "list" . $listId . ".csv";
                $listName = CHtml::link(Yii::t('admin', 'List') . " $listId: " . $list->name, array($modulePath . '/list', 'id' => $listId), array('style' => 'text-decoration:none;'));
            }
            $filePath = $this->safePath($file);
            $_SESSION['modelExportFile'] = $file;
            $attributes = X2Model::model($modelName)->attributes;
            if ($modelName === 'Actions') {
                // Make sure the ActionText is exported too
                $attributes = array_merge($attributes, array('actionDescription' => null));
            }
            $meta = array_keys($attributes);
            if (isset($list)) {
                // Figure out gridview settings to export those columns
                $gridviewSettings = json_decode(Yii::app()->params->profile->gridviewSettings, true);
                if (isset($gridviewSettings['contacts_list' . $listId])) {
                    $tempMeta = array_keys($gridviewSettings['contacts_list' . $listId]);
                    $meta = array_intersect($tempMeta, $meta);
                }
            }
            // Set up metadata
            $_SESSION['modelExportMeta'] = $meta;
            $fp = fopen($filePath, 'w+');
            fputcsv($fp, $meta);
            fclose($fp);
        } else {
            // If an invalid model was chosen, unset it so that the model list
            // will be displayed instead.
            if (isset($model))
                unset($model);
        }

        $viewParam = array(
            'modelList' => $modelList,
            'listId' => $listId,
            'model' => ''
        );
        if (isset($model)) {
            $viewParam['model'] = $model;
            if ($model == 'Contacts') {
                $viewParam['listName'] = $listName;
            }
        }

        $this->render('exportModels', $viewParam);
    }

    /**
     * An AJAX called function which exports data to a CSV via pagination.
     * This is a generalized version of the Contacts export.
     * @param int $page The page of the data provider to export
     */
    public function actionExportModelRecords($page, $model) {
        X2Model::$autoPopulateFields = false;
        $file = $this->safePath($_SESSION['modelExportFile']);
        $staticModel = X2Model::model(str_replace(' ', '', $model));
        $fields = $staticModel->getFields();
        $fp = fopen($file, 'a+');

        // Load data provider based on export criteria
        $excludeHidden = !isset($_GET['includeHidden']) || $_GET['includeHidden'] === 'false';
        if ($page == 0 && $excludeHidden && isset($_SESSION['exportModelCriteria']) &&
            ($_SESSION['exportModelCriteria'] instanceof CDbCriteria)) {

            // Save hidden condition in criteria
            $hiddenConditions = $staticModel->getHiddenCondition();
            $_SESSION['exportModelCriteria']->addCondition ($hiddenConditions);
        }
        $dp = new CActiveDataProvider($model, array(
            'criteria' => isset($_SESSION['exportModelCriteria']) ? $_SESSION['exportModelCriteria'] : array(),
            'pagination' => array(
                'pageSize' => 100,
            ),
        ));
        // Flip through to the right page.
        $pg = $dp->getPagination();
        $pg->setCurrentPage($page);
        $dp->setPagination($pg);
        $records = $dp->getData();
        $pageCount = $dp->getPagination()->getPageCount();

        // We need to set our data to be human friendly, so loop through all the
        // records and format any date / link / visibility fields.
        foreach ($records as $record) {
            foreach ($fields as $field) {
                $fieldName = $field->fieldName;
                if ($field->type == 'date' || $field->type == 'dateTime') {
                    if (is_numeric($record->$fieldName))
                        $record->$fieldName = Formatter::formatLongDateTime($record->$fieldName);
                }elseif ($field->type == 'link') {
                    $name = $record->$fieldName;
                    if (!empty($field->linkType)) {
                        list($name, $id) = Fields::nameAndId($name);
                    }
                    if (!empty($name))
                        $record->$fieldName = $name;
                }elseif ($fieldName == 'visibility') {
                    switch ($record->$fieldName) {
                        case 0:
                            $record->$fieldName = 'Private';
                            break;
                        case 1:
                            $record->$fieldName = 'Public';
                            break;
                        case 2:
                            $record->$fieldName = 'User\'s Groups';
                            break;
                        default:
                            $record->$fieldName = 'Private';
                    }
                }
            }
            // Enforce metadata to ensure accuracy of column order, then export.
            $combinedMeta = array_combine($_SESSION['modelExportMeta'], $_SESSION['modelExportMeta']);
            $recordAttributes = $record->attributes;
            if ($model === 'Actions') {
                // Export descriptions with Actions
                $actionText = $record->actionText;
                if ($actionText) {
                    $actionDescription = $actionText->text;
                    $recordAttributes = array_merge($recordAttributes, array(
                        'actionDescription' => $actionDescription
                    ));
                }
            }
            $tempAttributes = array_intersect_key($recordAttributes, $combinedMeta);
            $tempAttributes = array_merge($combinedMeta, $tempAttributes);
            fputcsv($fp, $tempAttributes);
        }

        unset($dp);

        fclose($fp);
        if ($page + 1 < $pageCount) {
            echo $page + 1;
        }
    }

    protected function generateModuleSqlData ($moduleName) {
        $sql = "";
        $disallow = array(
            "id",
            "assignedTo",
            "name",
            "nameId",
            "description",
            "createDate",
            "lastUpdated",
            "updatedBy",
        );

        $fields = Fields::model()->findAllByAttributes(array(
            'modelName' => ucfirst($moduleName)
        ));
        foreach ($fields as $field) {
            if (array_search($field->fieldName, $disallow) === false) {
                $fieldType = $field->type;
                $columnDefinitions = Fields::getFieldTypes('columnDefinition');
                if (isset($columnDefinitions[$fieldType])) {
                    $fieldType = $columnDefinitions[$fieldType];
                } else {
                    $fieldType = 'VARCHAR(250)';
                }

                $linkType = $field->linkType;
                if ($field->type === 'dropdown') {
                    // Export associated dropdown values
                    $dropdown = Dropdowns::model()->findByPk ($field->linkType);
                    if ($dropdown) {
                        $sql .= "/*&*/INSERT INTO x2_dropdowns ".
                                "(name, options, multi, parent, parentVal) ".
                            "VALUES ".
                                "('$dropdown->name', '$dropdown->options', '$dropdown->multi', ".
                                "'$dropdown->parent', '$dropdown->parentVal');";
                        // Temporarily set the linkType to the dropdowns name: this is to avoid
                        // messy ID conflicts when importing a module to existing installations
                        $linkType = $dropdown->name;
                    }
                }

                $sql .= "/*&*/ALTER TABLE x2_$moduleName ADD COLUMN $field->fieldName $fieldType;";
                $sql .= "/*&*/INSERT INTO x2_fields ".
                        "(modelName, fieldName, attributeLabel, modified, custom, type, linkType) ".
                    "VALUES ".
                        "('$moduleName', '$field->fieldName', '$field->attributeLabel', '1', '1', ".
                        "'$field->type', '$linkType');";
            }
        }
        $formLayouts = X2Model::model('FormLayout')->findAllByAttributes(array(
            'model' => $moduleName
        ));
        foreach ($formLayouts as $layout) {
            $attributes = $layout->attributes;
            unset($attributes['id']);
            $attributeKeys = array_keys($attributes);
            $attributeValues = array_values($attributes);
            $keys = implode(", ", $attributeKeys);
            $values = "'" . implode("', '", $attributeValues) . "'";
            $sql.="/*&*/INSERT INTO x2_form_layouts ($keys) VALUES ($values);";
        }
        return $sql;
    }

    /**
     * Export a custom module.
     *
     * This method creates a zip file from a custom module with all the proper
     * files and SQL for installation required to set up the module again.  These
     * zip files can be imported into other X2 installations.
     */
    public function actionExportModule() {
        $dlFlag = false;
        if (isset($_POST['name'])) {
            $moduleName = ($_POST['name']);

            $sql = $this->generateModuleSqlData($moduleName);
            $db = Yii::app()->file->set("protected/modules/$moduleName/sqlData.sql");
            $db->create();
            $db->setContents($sql);

            if (file_exists($moduleName . ".zip")) {
                unlink($moduleName . ".zip");
            }

            $zip = Yii::app()->zip;
            $zip->makeZip('protected/modules/' . $moduleName, $moduleName . ".zip");
            $dlFlag = true;
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
            'dlFlag' => $dlFlag? : false,
            'file' => $dlFlag ? ($_POST['name']) : ''
        ));
    }

    /************************************************************************
     *          Record Importing Methods
     ***********************************************************************/

    /**
     * The general model import page
     */
    public function actionImportModels(){
        // Determine the model selected by the user
        if (isset($_GET['model']) || isset($_POST['model'])) {
            $_SESSION['model'] = (isset($_GET['model'])) ? $_GET['model'] : $_POST['model'];
        }
        if (isset($_FILES['data'])) {
            $temp = CUploadedFile::getInstanceByName('data');
            $temp->saveAs($filePath = $this->safePath('data.csv'));
            ini_set('auto_detect_line_endings', 1); // Account for Mac based CSVs if possible
            $_SESSION['csvLength'] = 0;
            if (file_exists($filePath)) {
                $fp = fopen($filePath, 'r+');
                $_SESSION['csvLength'] = $this->calculateCsvLength($filePath);
                $this->fixCsvLineEndings($filePath);
            } else {
                throw new Exception('There was an error saving the models file.');
            }

            list($meta, $x2attributes) = $this->initializeModelImporter($fp);
            $preselectedMap = false;

            if (isset($_POST['x2maps'])) {
                // Use an existing import map from the app
                $importMap = $this->loadImportMap($_POST['x2maps']);
                if (empty($importMap)) {
                    $_SESSION['errors'] = Yii::t('admin', 'Unable to load import map');
                    $this->redirect('importModels');
                }
                $_SESSION['importMap'] = $importMap['mapping'];
                $_SESSION['mapName'] = $importMap['name'];
                // Make sure $importMap is consistent with and without an uploaded import map
                $importMap = $importMap['mapping'];
                $preselectedMap = true;
            } else if (CUploadedFile::getInstanceByName('mapping') instanceof CUploadedFile
                    && CUploadedFile::getInstanceByName('mapping')->size > 0) {
                $this->loadUploadedImportMap();
                $preselectedMap = true;
                $importMap = $_SESSION['importMap'];
            } else {
                // Set up import map via the internal function
                $this->createImportMap($x2attributes, $meta);

                $importMap = $_SESSION['importMap'];
                // We need the flipped version to display to users more easily which
                // of their fields maps to what X2 field
                $importMap = array_flip($importMap);
            }
            $sampleRecords = $this->prepareImportSampleRecords($meta, $fp);
            fclose($fp);

            // Remove the import failures column; the user doesn't need to know about it
            $meta = array_filter($meta, function($x) {
                return $x !== 'X2_Import_Failures';
            });

            $this->render('processModels', array(
                'attributes' => $x2attributes,
                'meta' => $meta,
                'fields' => $_SESSION['fields'],
                'model' => str_replace(' ', '', $_SESSION['model']),
                'sampleRecords' => $sampleRecords,
                'importMap' => $importMap,
                'preselectedMap' => $preselectedMap,
            ));
        } else {
            $modelList = Modules::getExportableModules();
            $errors = (isset($_SESSION['errors']) ? $_SESSION['errors'] : "");
            $this->render('importModels', array(
                'model' => isset($_SESSION['model']) ? $_SESSION['model'] : '',
                'modelList' => $modelList,
                'errors' => $errors,
            ));
        }
    }

    /**
     * Calculates the number of lines in a CSV file for import
     * Warning: This must load and traverse the length of the file
     */
    protected function calculateCsvLength($csvfile) {
        $lines = file($csvfile, FILE_SKIP_EMPTY_LINES);
        $lineCount = count($lines) - 1;
        return $lineCount;
    }

    /**
     * Remove any lone \r characters
     * @param string $csvfile Path to CSV file
     */
    protected function fixCsvLineEndings($csvFile) {
        $text = file_get_contents ($csvFile);
        $replacement = preg_replace ('/\r([^\n])/m', "\r\n\\1", $text);
        file_put_contents ($csvFile, $replacement);
    }

    /**
     * Read metadata from the CSV and initialize session variables
     * @param resource $fp File pointer to CSV
     * @return array CSV Metadata and X2Attributes
     */
    protected function initializeModelImporter($fp) {
        $meta = fgetcsv($fp);
        if($meta === false)
            throw new Exception('There was an error parsing the models of the CSV.');
        while("" === end($meta)){
            array_pop($meta); // Remove empty data from the end of the metadata
        }
        if(count($meta) == 1){ // This was from a global export CSV, the first row is the version
            $version = $meta[0]; // Remove it and repeat the above process
            $meta = fgetcsv($fp);
            if($meta === false)
                throw new Exception('There was an error parsing the contents of the CSV.');
            while("" === end($meta)){
                array_pop($meta);
            }
        }
        if(empty($meta)){
            $_SESSION['errors'] = Yii::t('admin', "Empty CSV or no metadata specified");
            $this->redirect('importModels');
        }

        // Set our file offset for importing Contacts
        $_SESSION['offset'] = ftell($fp);
        $_SESSION['metaData'] = $meta;
        $failedContacts = fopen($this->safePath('failedRecords.csv'), 'w+');
        // Add the import failures column to the failed records meta
        $failedHeader = $meta;
        if (end($meta) != 'X2_Import_Failures')
            $failedHeader[] = 'X2_Import_Failures';
        fputcsv($failedContacts, $failedHeader);
        fclose($failedContacts);

        // Ensure the selected model hasn't been lost
        if (array_key_exists('model', $_SESSION))
            $modelName = str_replace(' ', '', $_SESSION['model']);
        else
            $this->errorMessage (Yii::t('admin',
                "Session information has been lost. Please retry your import."
            ));
        $x2attributes = array_keys(X2Model::model($modelName)->attributes);
        while("" === end($x2attributes)){
            array_pop($x2attributes);
        }
        if ($modelName === 'Actions') {
            // add Action.description to attributes so that it is automatically mapped
            $x2attributes[] = 'actionDescription';
        }
        // Initialize session data
        $_SESSION['importMap'] = array();
        $_SESSION['imported'] = 0;
        $_SESSION['failed'] = 0;
        $_SESSION['created'] = 0;
        $_SESSION['fields'] = X2Model::model($modelName)->getFields(true);
        $_SESSION['x2attributes'] = $x2attributes;
        $_SESSION['mapName'] = "";
        $_SESSION['importId'] = $this->getNextImportId();

        return array($meta, $x2attributes);
    }

    /**
     * The goal of this function is to attempt to map meta into a series of
     * Contact attributes, which it will do via string comparison on the Contact
     * attribute names, the Contact attribute labels and a pattern match.
     * @param array $attributes Contact model's attributes
     * @param array $meta Provided metadata in the CSV
     */
    protected function createImportMap($attributes, $meta) {
        // We need to do data processing on attributes, first copy & preserve
        $originalAttributes = $attributes;
        // Easier to just do both strtolower than worry about case insensitive comparison
        $attributes = array_map('strtolower', $attributes);
        $processedMeta = array_map('strtolower', $meta);
        // Remove any non word characters or underscores
        $processedMeta = preg_replace('/[\W|_]/', '', $processedMeta);
        // Now do the same with Contact attribute labels
        $labels = X2Model::model(str_replace(' ', '', $_SESSION['model']))->attributeLabels();
        $labels = array_map('strtolower', $labels);
        $labels = preg_replace('/[\W|_]/', '', $labels);
        /*
         * At the end of this loop, any fields we are able to suggest a mapping
         * for are automatically populated into an array in $_SESSION with
         * the format:
         *
         * $_SESSION['importMap'][<x2_attribute>] = <metadata_attribute>
         */
        foreach($meta as $metaVal){
            // Ignore the import failures column
            if ($metaVal == 'X2_Import_Failures')
                continue;
            // Same reason as $originalAttributes
            $originalMetaVal = $metaVal;
            $metaVal = strtolower(preg_replace('/[\W|_]/', '', $metaVal));
            /*
             * First check if we're lucky and maybe the processed metadata value
             * matches a contact attribute directly. Things like first_name
             * would be converted to firstname and so match perfectly. If we
             * find a match here, assume it is the most correct possibility
             * and add it to our session import map
             */
            if(in_array($metaVal, $attributes)){
                $attrKey = array_search($metaVal, $attributes);
                $_SESSION['importMap'][$originalAttributes[$attrKey]] = $originalMetaVal;
            /*
             * The next possibility is that the metadata value matches an attribute
             * label perfectly. This is more common for a field like company
             * where the label is "Account" but it's our second best bet for
             * figuring out the metadata.
             */
            }elseif(in_array($metaVal, $labels)){
                $attrKey = array_search($metaVal, $labels);
                $_SESSION['importMap'][$attrKey] = $originalMetaVal;
            /*
             * The third best option is that there is a partial word match
             * on the metadata value. However, we don't want to do a simple
             * preg search as that may give weird results, we want to limit
             * with a word boundary to see if the first part matches. This isn't
             * ideal but it fixes some edge cases.
             */
            }elseif(count(preg_grep("/\b$metaVal/i", $attributes)) > 0){
                $keys = array_keys(preg_grep("/\b$metaVal/i", $attributes));
                $attrKey = $keys[0];
                if(!isset($_SESSION['importMap'][$originalMetaVal]))
                    $_SESSION['importMap'][$originalAttributes[$attrKey]] = $originalMetaVal;
            /*
             * Finally, check if there is a partial word match on the attribute
             * label as opposed to the field name
             */
            }elseif(count(preg_grep("/\b$metaVal/i", $labels)) > 0){
                $keys = array_keys(preg_grep("/\b$metaVal/i", $labels));
                $attrKey = $keys[0];
                if(!isset($_SESSION['importMap'][$originalMetaVal]))
                    $_SESSION['importMap'][$attrKey] = $originalMetaVal;
            }
        }
        /*
         * Finally, we want to do a quick reverse operation in case there
         * were any fields that weren't mapped correctly based on the directionality
         * of the word boundary. For example, if we were checking "zipcode"
         * against "zip" this would not be a match because the pattern "zipcode"
         * is longer and will fail. However, "zip" will match into "zipcode"
         * and should be accounted for. This loop goes through the x2 attributes
         * instead of the metadata to ensure bidirectionality.
         */
        foreach($originalAttributes as $attribute){
            if(in_array($attribute, $processedMeta)){
                $metaKey = array_search($attribute, $processedMeta);
                $_SESSION['importMap'][$attribute] = $meta[$metaKey];
            }elseif(count(preg_grep("/\b$attribute/i", $processedMeta)) > 0){
                $matches = preg_grep("/\b$attribute/i", $processedMeta);
                $metaKeys = array_keys($matches);
                $metaValue = $meta[$metaKeys[0]];
                if(!isset($_SESSION['importMap'][$attribute]))
                    $_SESSION['importMap'][$attribute] = $metaValue;
            }
        }
    }

    /**
     * This grabs 5 sample records from the CSV to get an example of what
     * the data looks like.
     * @return array Sample records
     */
    protected function prepareImportSampleRecords($meta, $fp) {
        $sampleRecords = array();
        for($i = 0; $i < 5; $i++){
            if($sampleRecord = fgetcsv($fp)){
                if(count($sampleRecord) > count($meta)){
                    $sampleRecord = array_slice($sampleRecord, 0, count($meta));
                }
                if(count($sampleRecord) < count($meta)){
                    $sampleRecord = array_pad($sampleRecord, count($meta), null);
                }
                if(!empty($meta)){
                    $sampleRecord = array_combine($meta, $sampleRecord);
                    $sampleRecords[] = $sampleRecord;
                }
            }
        }
        return $sampleRecords;
    }

    /**
     * Save and attempt to load the uploaded import mapping
     */
    protected function loadUploadedImportMap() {
        $temp = CUploadedFile::getInstanceByName('mapping');
        $temp->saveAs($mapPath = $this->safePath('mapping.json'));
        $mappingFile = fopen($mapPath, 'r');
        $importMap = fread($mappingFile, filesize($mapPath));
        $importMap = json_decode($importMap, true);
        if ($importMap === null) {
            $_SESSION['errors'] = Yii::t('admin', 'Invalid JSON string specified');
            $this->redirect('importModels');
        }
        $_SESSION['importMap'] = $importMap;

        if (array_key_exists('mapping', $importMap)) {
            $_SESSION['importMap'] = $importMap['mapping'];
            if (isset($importMap['name']))
                $_SESSION['mapName'] = $importMap['name'];
            else
                $_SESSION['mapName'] = Yii::t('admin', "Untitled Mapping");
            // Make sure $importMap is consistent with legacy import map format
            $importMap = $importMap['mapping'];
        } else {
            $_SESSION['importMap'] = $importMap;
            $_SESSION['mapName'] = Yii::t('admin', "Untitled Mapping");
        }

        fclose($mappingFile);
        if (file_exists($mapPath))
            unlink($mapPath);
    }

    /**
     * Bulk import of model records
     *
     * The actual meat of the import process happens here, this is called recursively via
     * AJAX to import sets of records. This is a refactored and generalized version of the
     * old Contacts importRecords.
     */
    public function actionImportModelRecords() {
        if(isset($_POST['count']) && file_exists($path = $this->safePath('data.csv')) &&
                isset($_POST['model'])){

            ini_set ('auto_detect_line_endings', 1); // Account for Mac based CSVs if possible
            $importedIds = array();
            $modelName = ucfirst($_POST['model']);
            $count = $_POST['count']; // Number of records to import
            $metaData = $_SESSION['metaData'];
            $importMap = $_SESSION['importMap'];
            $fp = fopen($path, 'r+');
            fseek($fp, $_SESSION['offset']); // Seek to the right file offset
            // Store valid model attributes to insert in bulk later
            $modelContainer = array(
                $modelName => array(),
            );
            // Add a container to store the text if we're importing Actions
            if ($modelName === 'Actions')
                $modelContainer['ActionText'] = array();
            // Store an array of relationships for each record
            $relationships = array();
            // Keep track of the linked models that were created
            $createdLinkedModels = array();
            // Whether the user has mapped the ID field
            $mappedId = false;

            for($i = 0; $i < $count; $i++){
                $arr = fgetcsv($fp); // Loop through and start importing
                if($arr !== false && !is_null($arr)){
                    if ($arr === array(null)) {
                        // Skip empty lines
                        continue;
                    }
                    if(count($arr) > count($metaData))
                        $arr = array_slice($arr, 0, count($metaData));
                    else if (count($arr) < count($metaData))
                        $arr = array_pad($arr, count($metaData), null);
                    unset($_POST);

                    // Add a container for this model's relationships
                    $relationships[] = array();
                    $linkedRecordPreexist = array();
                    // Nix all invalid multibyte sequences to avoid errors
                    $arr = array_map('Formatter::mbSanitize',$arr);
                    if (!empty($metaData) && !empty($arr))
                        $importAttributes = array_combine($metaData, $arr);
                    else
                        continue;

                    $model = new $modelName;
                    foreach($metaData as $attribute){
                        if ($importMap[$attribute] === 'id')
                            $mappedId = true;
                        $isActionText = ($modelName === 'Actions' &&
                            $importMap[$attribute] === 'actionDescription');
                        if (isset($importMap[$attribute]) &&
                                ($model->hasAttribute ($importMap[$attribute]) || $isActionText)) {
                            $this->importRecordAttribute ($modelName, $model, $importMap[$attribute],
                                $importAttributes[$attribute], $relationships, $modelContainer,
                                $createdLinkedModels);
                            $_POST[$importMap[$attribute]] = $model->$importMap[$attribute];
                        }
                    }
                    $this->fixupImportedAttributes ($modelName, $model);

                    if (! $model->hasErrors() && $model->validate())
                        $this->saveImportedModel ($model, $modelName, $modelContainer, $importedIds);
                    else
                        $this->markFailedRecord ($model, $arr, $metaData);
                }else{
                    $this->finishImportBatch ($modelName, $modelContainer, $relationships,
                        $createdLinkedModels, $mappedId, true);
                    return;
                }
            }
            // Change the offset to wherever we got to and continue.
            $_SESSION['offset'] = ftell($fp);
            $this->finishImportBatch ($modelName, $modelContainer, $relationships,
                $createdLinkedModels, $mappedId);
        }
    }

    /**
     * The import assumes we have human readable data in the CSV and will thus need to convert. This
     * method converts link, date, and dateTime fields to the appropriate machine friendly data.
     * @param string $modelName The model class being imported
     * @param X2Model $model The currently importing model record
     * @param string $fieldName Field to set
     * @param string $importAttribute Value to set field
     * @param array $relationships Attributes of Relationships to be created
     * @param array $modelContainer Attributes of Models to be created
     * @param array $createdLinkedModels Linked models that will be created
     */
    protected function importRecordAttribute($modelName, X2Model &$model, $fieldName, $importAttribute,
            &$relationships, &$modelContainer, &$createdLinkedModels) {

        $fieldRecord = Fields::model()->findByAttributes(array(
            'modelName' => $modelName,
            'fieldName' => $fieldName,
        ));
        /**
         * Skip setting the attribute if it has already been set or if the entry from
         * the CSV is empty.
         */
        if (empty($importAttribute) && ($importAttribute !== 0 && $importAttribute !== '0')) {
            return;
        }
        if ($fieldName === 'actionDescription' && $modelName === 'Actions') {
            $text = new ActionText;
            $text->text = $importAttribute;
            if (isset($model->id))
                $text->actionId = $model->id;
            $modelContainer['ActionText'][] = $text->attributes;
            return;
        }


        // ensure the provided id is valid
        if ((strtolower($fieldName) === 'id')
                && (!preg_match('/^\d+$/', $importAttribute) || $importAttribute >= 4294967295)) {
            return;
        }
        switch($fieldRecord->type){
            case "link":
                $this->importRecordLinkAttribute ($modelName, $model, $fieldRecord, $importAttribute,
                    $relationships, $modelContainer, $createdLinkedModels);
                break;
            case "dateTime":
            case "date":
                if(strtotime($importAttribute) !== false){
                    $model->$fieldName = strtotime($importAttribute);
                }elseif(is_numeric($importAttribute)){
                    $model->$fieldName = $importAttribute;
                }else{

                }
                break;
            case "visibility":
                switch ($importAttribute) {
                    case 'Private':
                        $model->$fieldName = 0;
                        break;
                    case 'Public':
                        $model->$fieldName = 1;
                        break;
                    case 'User\'s Groups':
                        $model->$fieldName = 2;
                        break;
                    default:
                        $model->$fieldName = $importAttribute;
                }
                break;
            default:
                $model->$fieldName = $importAttribute;
        }
    }

    /**
     * Handle setting link type fields and create linked records if specified
     * @param string $modelName The model class being imported
     * @param X2Model $model The currently importing model record
     * @param Fields $fieldRecord Field to set
     * @param string $importAttribute Value to set field
     * @param array $relationships Attributes of Relationships to be created
     * @param array $modelContainer Attributes of Models to be created
     * @param array $createdLinkedModels Linked models that will be created
     */
    protected function importRecordLinkAttribute($modelName, X2Model &$model, Fields $fieldRecord,
            $importAttribute, &$relationships, &$modelContainer, &$createdLinkedModels) {
        $fieldName = $fieldRecord->fieldName;
        $className = ucfirst($fieldRecord->linkType);

        if(ctype_digit($importAttribute)){
            $lookup = X2Model::model($className)->findByPk($importAttribute);
            $model->$fieldName = $importAttribute;
            if(!empty($lookup)){
                // Create a link to the existing record
                $model->$fieldName = $lookup->nameId;
                $relationship = new Relationships;
                $relationship->firstType = $modelName;
                $relationship->secondType = $className;
                $relationship->secondId = $importAttribute;
                $relationships[count($relationships)-1][] = $relationship->attributes;
            }
        } else {
            $lookup = X2Model::model($className)->findByAttributes(array('name' => $importAttribute));
            if (isset($lookup)) {
                $model->$fieldName = $lookup->nameId;
                $relationship = new Relationships;
                $relationship->firstType = $modelName;
                $relationship->secondType = $className;
                $relationship->secondId = $lookup->id;
                $relationships[count($relationships)-1][] = $relationship->attributes;
            } elseif (isset($_SESSION['createRecords']) && $_SESSION['createRecords'] == 1 &&
                    !($modelName === 'BugReports' && $fieldRecord->linkType === 'BugReports')) {
                // Skip creating related bug reports; the created report wouldn't hold any useful info.
                $className = ucfirst($fieldRecord->linkType);
                if (class_exists($className)){
                    $lookup = new $className;
                    if ($_SESSION['skipActivityFeed'] === 1)
                        $lookup->createEvent = false;
                    $lookup->name = $importAttribute;
                    if ($className === 'Contacts' || $modelName === 'X2Leads') {
                        $this->fixupImportedContactName ($lookup);
                    }
                    if ($lookup->hasAttribute('visibility'))
                        $lookup->visibility = 1;
                    if ($lookup->hasAttribute('description'))
                        $lookup->description = "Generated by ".$modelName." import.";
                    if ($lookup->hasAttribute('createDate'))
                        $lookup->createDate = time();

                    if (!array_key_exists($className, $modelContainer))
                        $modelContainer = array_merge($modelContainer, array($className => array()));
                    // Ensure this linked record has not already been accounted for
                    $createNewLinkedRecord = true;
                    if ($model->hasAttribute('name')) {
                        $model->$fieldName = $lookup->name;
                    } else {
                        $model->$fieldName = $importAttribute;
                    }

                    foreach ($modelContainer[$className] as $record) {
                        if ($record['name'] === $lookup->name) {
                            $createNewLinkedRecord = false;
                            break;
                        }
                    }

                    if ($createNewLinkedRecord) {
                        $modelContainer[$className][] = $lookup->attributes;
                        if(isset($_SESSION['created'][$className])){
                            $_SESSION['created'][$className]++;
                        }else{
                            $_SESSION['created'][$className] = 1;
                        }
                    }
                    $relationship = new Relationships;
                    $relationship->firstType = $modelName;
                    $relationship->secondType = $className;
                    $relationships[count($relationships)-1][] = $relationship->attributes;
                    $createdLinkedModels[] = $model->$fieldName;
                }
            } else {
                $model->$fieldName = $importAttribute;
            }
        }
    }

    /**
     * Helper method to help out the user in the special case where a Contact's full name
     * is set, but first and last name aren't, or vice versa.
     * @param $model
     */
    protected function fixupImportedContactName (&$model) {
        if (!empty($model->name) && (empty($model->firstName) && empty($model->lastName))) {
            $names = preg_split('/ /', $model->name);
            if (count($names) === 2) {
                $model->firstName = $names[0];
                $model->lastName = $names[1];
            }
        } else if (empty($model->name) && (!empty($model->firstName) && !empty($model->lastName)))
            $model->name = $model->firstName ." ". $model->lastName;
    }

    /**
     * This method is used after importing a records attributes to perform extra tasks, such as
     * assigning lead routing, setting visibility, and reconstructing Action associations.
     * @param string $modelName Name of the model being imported
     * @param X2Model $model Current moel to import
     */
    protected function fixupImportedAttributes($modelName, X2Model &$model) {
        if ($modelName === 'Contacts' || $modelName === 'X2Leads')
            $this->fixupImportedContactName ($model);

        if ($modelName === 'Actions' && isset($model->associationType))
            $this->reconstructImportedActionAssoc($model);

        // Set visibility to Public by default if unset by import
        if ($model->hasAttribute('visibility') && is_null($model->visibility))
            $model->visibility = 1;
        // If date fields were provided, do not create new values for them
        if (!empty($model->createDate) || !empty($model->lastUpdated) ||
                !empty($model->lastActivity)) {
            $now = time();
            if (empty($model->createDate))
                $model->createDate = $now;
            if (empty($model->lastUpdated))
                $model->lastUpdated = $now;
            if ($model->hasAttribute('lastActivity') && empty($model->lastActivity))
                $model->lastActivity = $now;
        }
        if($_SESSION['leadRouting'] == 1){
            $assignee = $this->getNextAssignee();
            if($assignee == "Anyone")
                $assignee = "";
            $model->assignedTo = $assignee;
        }
        // Loop through our override and set the manual data
        foreach($_SESSION['override'] as $attr => $val){
            $model->$attr = $val;
        }
    }

    /**
     * Handle reconstructing and validating Action associations
     * @param Action $model Action to reconstruct association
     */
    protected function reconstructImportedActionAssoc(Actions &$model) {
        $exportableModules = array_merge(
            array_keys(Modules::getExportableModules()),
            array('None')
        );
        $exportableModules = array_map('lcfirst', $exportableModules);
        $model->associationType = lcfirst($model->associationType);
        if (!in_array($model->associationType, $exportableModules)) {
            // Invalid association type
            $model->addError ('associationType', Yii::t('admin', 'Unknown associationType.'));
        } else if (isset($model->associationId) && $model->associationId !== '0') {
            $associatedModel = X2Model::model($model->associationType)
                ->findByPk($model->associationId);
            if ($associatedModel)
                $model->associationName = $associatedModel->nameId;
        } else if (!isset($model->associationId) && isset($model->associationName)) {
            // Retrieve associationId
            $staticAssociationModel = X2Model::model($model->associationType);
            if ($staticAssociationModel->hasAttribute('name') &&
                    !ctype_digit($model->associationName)) {
                $associationModelParams = array('name'=>$model->associationName);
            } else {
                $associationModelParams = array('id' => $model->associationName);
            }
            $lookup = $staticAssociationModel->findByAttributes($associationModelParams);
            if (isset($lookup)) {
                $model->associationId = $lookup->id;
                $model->associationName = $lookup->hasAttribute('nameId') ?
                    $lookup->nameId : $lookup->name;
            }
        }
    }

    /**
     * Finalize this batch of records by performing a mass insert, handling accounting,
     * updating nameIds, and rendering the JSON response
     * @param string $modelName Name of the model class being imported
     * @param array $modelContainer Array of model attributes to be created
     * @param array $relationships Array of relationship attributes to be created
     * @param array $createdLinkedModels Array of name|nameId of the newly created linked records
     * @param boolean $mappedId Whether the primary model's ID has been mapped: this alters
     *    the result of lastInsertId
     * @param boolean $finished Whether this batch has reached the end of the CSV
     */
    protected function finishImportBatch($modelName, $modelContainer, $relationships,
            $createdLinkedModels, $mappedId, $finished = false) {
        // Keep track of the lastInsertId for each type
        $lastInsertedIds = array();

        // First insert the records being imported
        $lastInsertedIds[$modelName] = $this->insertMultipleRecords(
            $modelName,
            $modelContainer[$modelName]
        );
        $primaryModelCount = count($modelContainer[$modelName]);
        // If id was mapped, then lastInsertId would actually be the last in the sequence.
        // Otherwise, lastInsertId would be the first
        if ($mappedId) {
            $primaryIdRange = range (
                $lastInsertedIds[$modelName] - $primaryModelCount + 1,
                $lastInsertedIds[$modelName]
            );
        } else {
            $primaryIdRange = range (
                $lastInsertedIds[$modelName],
                $lastInsertedIds[$modelName] + $primaryModelCount - 1
            );
        }
        $this->handleImportAccounting ($modelContainer[$modelName], $modelName, $lastInsertedIds,
            $relationships, $createdLinkedModels, $mappedId);
        $this->massUpdateImportedNameIds ($primaryIdRange, $modelName);

        // Now create remaining auxiliary records
        foreach ($modelContainer as $type => $models) {
            if ($type === $modelName) // these were already processed
                continue;

            if ($modelName === 'Actions' && $type === 'ActionText') {
                // set the actionIds and insert ActionText records
                $firstInsertedId = $primaryIdRange[0];
                $actionTexts = array();
                foreach ($models as $i => $model) {
                    if (empty($model) or isset($model['actionId']))
                        continue;
                    $model['actionId'] = $firstInsertedId + $i;
                    $actionTexts[] = $model;
                }
                $this->insertMultipleRecords ('ActionText', $actionTexts);
            } else {
                // otherwise handle the records normally
                $lastInsertedIds[$type] = $this->insertMultipleRecords ($type, $models);
                $this->handleImportAccounting ($models, $type, $lastInsertedIds,
                    $relationships, $createdLinkedModels);
                $this->fixupLinkFields ($modelName, $type, $primaryIdRange);
                // related records won't have ID set; therefore, lastInsertId would have
                // returned the first record in a sequence
                $idRange = range(
                    $lastInsertedIds[$type],
                    $lastInsertedIds[$type] + count($models) - 1
                );
                $this->massUpdateImportedNameIds ($idRange, $type);
            }
        }
        $this->establishImportRelationships ($relationships, $primaryIdRange[0],
            $createdLinkedModels, $mappedId);

        echo json_encode(array(
            ($finished ? '1' : '0'),
            $_SESSION['imported'],
            $_SESSION['failed'],
            json_encode($_SESSION['created']),
        ));
    }

    /**
     * Populate the nameId field since auto-populating fields is
     * disabled and it is far more efficient to do it in a single query
     */
    protected function massUpdateImportedNameIds($importedIds, $type) {
        $hasNameId = Fields::model()->findByAttributes(array(
            'fieldName' => 'nameId',
            'modelName' => $type,
        ));
        if ($hasNameId)
            X2Model::massUpdateNameId($type,$importedIds);
    }

    /**
     * Create additional records related to the import, including the requested Tags, comment
     * Actions, Import records, Events, and Relationships
     * @param array $models Array of arrays of model attributes
     * @param string $modelName Name of the model being imported
     * @param array $lastInsertedIds The last MySQL IDs that were created, indexed by model type
     * @param array $relationships Array of relationship attributes to be created
     * @param array $createdLinkedModels Array of the name|nameId of newly created linked records
     * @param boolean $mappedId Whether ID was mapped: this affects lastInsertId's behavior
     */
    protected function handleImportAccounting($models, $modelName, $lastInsertedIds, $relationships,
            $createdLinkedModels, $mappedId = false) {
        $now = time();
        $editingUsername = Yii::app()->user->name;
        $modelContainer = array(
            'Imports' => array(),
            'Actions' => array(),
            'Events' => array(),
            'Notification' => array(),
            'Tags' => array(),
        );
        if ($mappedId)
            $firstNewId = $lastInsertedIds[$modelName] - count($models) + 1;
        else
            $firstNewId = $lastInsertedIds[$modelName];
        for($i = 0; $i < count($models); $i++) {
            $record = $models[$i];
            if ($mappedId) {
                $modelId = $models[$i]['id'];
            } else {
                $modelId = $i + $firstNewId;
            }
            // Create a event for the imported record, and create a notification for the assigned
            // user if one exists, since X2ChangelogBehavior will not be triggered with
            // createMultipleInsertCommand()
            if ($_SESSION['skipActivityFeed'] !== 1) {
                $event = new Events;
                $event->visibility = array_key_exists('visibility', $record) ?
                    $record['visibility'] : 1;
                $event->associationType = $modelName;
                $event->associationId = $modelId;
                $event->timestamp = $now;
                $event->user = $editingUsername;
                $event->type = 'record_create';
                $modelContainer['Events'][] = $event->attributes;
            }
            if (array_key_exists('assignedTo', $record) && !empty($record['assignedTo']) &&
                    $record['assignedTo'] != $editingUsername && $record['assignedTo'] != 'Anyone') {
                $notif = new Notification;
                $notif->user = $record['assignedTo'];
                $notif->createdBy = $editingUsername;
                $notif->createDate = $now;
                $notif->type = 'create';
                $notif->modelType = $modelName;
                $notif->modelId = $modelId;
                $modelContainer['Notification'][] = $notif->attributes;
            }

            // Add all listed tags
            foreach($_SESSION['tags'] as $tag){
                $tagModel = new Tags;
                $tagModel->taggedBy = 'Import';
                $tagModel->timestamp = $now;
                $tagModel->type = $modelName;
                $tagModel->itemId = $modelId;
                $tagModel->tag = $tag;
                $tagModel->itemName = $modelName;
                $modelContainer['Tags'][] = $tagModel->attributes;
            }
            // Log a comment if one was requested
            if(!empty($_SESSION['comment'])){
                $action = new Actions;
                $action->associationType = lcfirst(str_replace(' ', '', $modelName));
                $action->associationId = $modelId;
                $action->createDate = $now;
                $action->updatedBy = Yii::app()->user->getName();
                $action->lastUpdated = $now;
                $action->complete = "Yes";
                $action->completeDate = $now;
                $action->completedBy = Yii::app()->user->getName();
                $action->type = "note";
                $action->visibility = 1;
                $action->reminder = "No";
                $action->priority = 1; // Set priority to Low
                $modelContainer['Actions'][] = $action->attributes;
            }

            $importLink = new Imports;
            $importLink->modelType = $modelName;
            $importLink->modelId = $modelId;
            $importLink->importId = $_SESSION['importId'];
            $importLink->timestamp = $now;
            $modelContainer['Imports'][] = $importLink->attributes;
        }

        foreach ($modelContainer as $type => $records) {
            if (empty($records))
                continue;
            $lastInsertId = $this->insertMultipleRecords ($type, $records);
            if ($type === 'Actions') {
                // Create ActionText and Import records for the comment Actions that were created
                if (empty($records))
                    continue;
                $actionImportRecords = array();
                $actionTextRecords = array();
                $actionIdRange = range ($lastInsertId, $lastInsertId + count($records) - 1);
                foreach ($actionIdRange as $i) {
                    $importLink = new Imports;
                    $importLink->modelType = "Actions";
                    $importLink->modelId = $i;
                    $importLink->importId = $_SESSION['importId'];
                    $importLink->timestamp = $now;
                    $actionImportRecords[] = $importLink->attributes;

                    $actionText = new ActionText;
                    $actionText->actionId = $i;
                    $actionText->text = $_SESSION['comment'];
                    $actionTextRecords[] = $actionText->attributes;
                }
                $this->insertMultipleRecords ('Imports', $actionImportRecords);
                $this->insertMultipleRecords ('ActionText', $actionTextRecords);
            }
        }
    }

    /**
     * Process the link-type fields to set nameId
     * @param int $count The number of primary models being imported
     * @param string $modelName The primary model being imported
     * @param string $type The model of the linked record
     * @param array $lastInsertedIds Array of last inserted IDs, indexed by model name
     */
    protected function fixupLinkFields ($modelName, $type, $primaryIdRange) {
        $linkTypeFields = Yii::app()->db->createCommand()
            ->select('fieldName')
            ->from('x2_fields')
            ->where('type = "link" AND modelName = :modelName AND linkType = :linkType', array(
                ':modelName' => $modelName,
                ':linkType' => $type,
            ))->queryColumn();
        $primaryTable = X2Model::model($modelName)->tableName();
        foreach ($linkTypeFields as $field) {
            // update each link type field
            $staticModel = X2Model::model($type);
            if (!$staticModel->hasAttribute('name'))
                continue;
            $secondaryTable = $staticModel->tableName();

            $sql = 'UPDATE `'.$primaryTable.'` a JOIN `'.$secondaryTable.'` b '.
                       'ON a.'.$field.' = b.name '.
                       'SET a.`'.$field.'` = CONCAT(b.name, \''.Fields::NAMEID_DELIM.'\', b.id) '.
                       'WHERE a.id in ('.implode(',', $primaryIdRange).')';
            Yii::app()->db->createCommand ($sql)->execute();
        }
    }

    /**
     * Create relationships records for the linked models
     * @param array $relationships Relationship attributes
     * @param int $firstNewId The first inserted id
     * @param array $createdLinkedModels
     * @param boolean $mappedId Whether or not ID was a mapped field
     */
    protected function establishImportRelationships($relationships, $firstNewId,
            $createdLinkedModels, $mappedId = false) {
        $validRelationships = array();

        foreach ($relationships as $i => $modelsRelationships) {
            $modelId = $i + $firstNewId;
            if (empty($modelsRelationships)) // skip placeholders
                continue;
            foreach ($modelsRelationships as $relationship) {
                $relationship['firstId'] = $modelId;
                if (empty($relationship['secondId'])) {
                    $model = X2Model::model($relationship['firstType'])
                        ->findByPk ($modelId);
                    $linkedStaticModel = X2Model::model($relationship['secondType']);
                    if (!$model)
                        continue;
                    $fields = Yii::app()->db->createCommand()
                        ->select('fieldName')
                        ->from('x2_fields')
                        ->where('type = \'link\' AND modelName = :firstType AND '.
                            'linkType = :secondType', array(
                                ':firstType' => $relationship['firstType'],
                                ':secondType' => $relationship['secondType'],
                        ))->queryColumn();
                    foreach ($fields as $field) {
                        // Check for relationships to new linked models for each link type field
                        if (empty($model->$field)) // skip fields that weren't set
                            continue;
                        $attr = $linkedStaticModel->hasAttribute('nameId') ? 'nameId' : 'name';
                        $linkedId = Yii::app()->db->createCommand()
                            ->select('id')
                            ->from($linkedStaticModel->tableName())
                            ->where($attr.' = :reference', array(
                                ':reference' => $model->$field,
                            ))->queryScalar();
                        if (!$linkedId)
                            continue;
                        $relationship['secondId'] = $linkedId;
                    }
                }
                if (!empty($relationship['secondId']))
                    $validRelationships[] = $relationship;
            }
        }
        $this->insertMultipleRecords ('Relationships', $validRelationships);
    }

    /**
     * Execute a multiple insert command
     * @param string $modelType Child of X2Model
     * @param array $models Array of model attributes to create
     * @return int Last inserted id
     */
    protected function insertMultipleRecords($modelType, $models) {
        if (empty($models))
            return null;
        $tableName = X2Model::model($modelType)->tableName();
        Yii::app()->db->schema->commandBuilder
            ->createMultipleInsertCommand($tableName, $models)
            ->execute();
        $lastInsertId = Yii::app()->db->schema->commandBuilder
            ->getLastInsertId($tableName);
        return $lastInsertId;
    }

    /**
     * Remove a record with the same ID, save the model attributes in the container, and increment
     * the count of imported records
     * @param X2Model $model
     * @param array $modelContainer Array of validated models attributes
     * @param array $importedIds Array of ids from imported models
     */
    protected function saveImportedModel(X2Model $model, $modelName, &$modelContainer, &$importedIds) {
        if(!empty($model->id)){
            $lookup = X2Model::model(str_replace(' ', '', $modelName))->findByPk($model->id);
            if(isset($lookup)){
                Relationships::model()->deleteAllByAttributes(array(
                    'firstType' => $modelName,
                    'firstId' => $lookup->id)
                );
                Relationships::model()->deleteAllByAttributes(array(
                    'secondType' => $modelName,
                    'secondId' => $lookup->id)
                );
                $lookup->delete();
                unset($lookup);
            }
        }
        // Save our model & create the import records and 
        // relationships. Passing $validate=false to CActiveRecord.save
        // because validation has already happened at this point
        $modelContainer[$modelName][] = $model->attributes;
        $_SESSION['imported']++;
        $importedIds[] = $model->id;
    }

    /**
     * Save the failed record into a CSV with validation errors
     * @param X2Model $model
     * @param array $arr
     * @param array $metadata
     */
    protected function markFailedRecord (X2Model $model, &$arr, $metaData) {
        // If the import failed, then put the data into the failedRecords CSV for easy recovery.
        $failedRecords = fopen($this->safePath('failedRecords.csv'), 'a+');
        $errorMsg = array();
        foreach ($model->errors as $error)
            $errorMsg[] = strtr(implode(' ', array_values($error)), '"', "'");
        $errorMsg = implode(' ', $errorMsg);

        // Add the error to the last column of the csv record
        if (end($metaData) === 'X2_Import_Failures')
            $arr[count($arr)-1] = $errorMsg;
        else
            $arr[] = $errorMsg;
        fputcsv($failedRecords, $arr);
        fclose($failedRecords);
        $_SESSION['failed']++;
    }

    /************************************************************************
     *          End Record Importing Methods
     ***********************************************************************/

    /**
     * Import a zip of a module.
     *
     * This method will allow the admin to import a zip file of an exported X2
     * module.
     */
    public function actionImportModule() {

        if (isset($_FILES['data'])) {

            $module = Yii::app()->file->set('data');
            if (!$module->exists) {
                Yii::app()->user->setFlash('error', Yii::t('admin',
                    'There was an error uploading the module.'));
                $this->redirect('importModule');
            }

            $moduleName = $module->filename;
            if (X2Model::model('Modules')->findByAttributes(array('name' => $moduleName))) {
                Yii::app()->user->setFlash('error', Yii::t('admin',
                    'Unable to upload module. A module with this name already exists.'));
                $this->redirect('importModule');
            }
            if ($module->extension !== 'zip') {
                Yii::app()->user->setFlash('error', Yii::t('admin',
                    'There was an error uploading the module. Please select a valid zip archive.'));
                $this->redirect('importModule');
            }

            $filename = $this->asa('ImportExportBehavior')->safePath($moduleName . ".zip");
            if ($module->copy($filename) === false || !file_exists($filename)) {
                Yii::app()->user->setFlash('error', Yii::t('admin',
                    "There was an error saving the module."));
                $this->redirect('importModule');
            }
            $zip = Yii::app()->zip;
            if ($zip->extractZip($filename, 'protected/modules/') === false) {
                Yii::app()->user->setFlash('error', Yii::t('admin',
                    "There was an error unzipping the module. Please ensure the zip archive is not corrupt."));
                $this->redirect('importModule');
            }

            $this->loadModuleData ($moduleName);
            unlink($filename);

            $this->createDefaultModulePermissions (ucfirst($moduleName));
            $this->fixupImportedModuleDropdowns (array($moduleName));

            $this->redirect(array($moduleName . '/index'));
        }
        $this->render('importModule');
    }

    /**
     * Private helper function to load module SQL
     * @param string $moduleName Name of the module to install SQL from
     */
    private function loadModuleData($moduleName) {
        $regPath = implode(DIRECTORY_SEPARATOR, array(
            'protected', 'modules', $moduleName, 'register.php'
        ));
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
                    if (!empty($sqlLine)) {
                        $command = Yii::app()->db->createCommand($sqlLine);
                        $command->execute();
                    }
                }
            }
        }
    }

    /**
     * @deprecated
     * DO NOT USE
     *
     * Testing method used for a prototype system of managing modules in a more
     * modular fashion.  This is NOT ready for use and should not be accessed.
     * This is intended to actually allow turning on/off of modules and installation.
     * This has been mostly superceded by the import/export feature but a use may
     * yet be found for it.
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
     * @deprecated
     * DO NOT USE
     *
     * Like {@link actionRegisterModules} this method is not yet ready for use.
     * Please refrain from attempting to use this module or it will likely create
     * issues in your installation.
     *
     * @param string $module The name of the module being toggled.
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
                $layoutModel->scenario = isset($_POST['scenario']) ? $_POST['scenario'] : 'Default';

                // if this is the default view, unset defaultView for all other forms
                if ($layoutModel->defaultView)
                    FormLayout::clearDefaultFormLayouts ('view', $modelName, $layoutModel->scenario);
                // if this is the default form, unset defaultForm for all other forms
                if ($layoutModel->defaultForm)
                    FormLayout::clearDefaultFormLayouts ('form', $modelName, $layoutModel->scenario);

                $layoutModel->save();
                $this->redirect(array('editor', 'id' => $id));
            }
        }else {
            $modelName = isset($_GET['model']) ? $_GET['model'] : '';
            if (!empty($modelName)) {
                try {
                    $model = X2Model::model($modelName);
                } catch (Exception $e) {
                    throw new CHttpException(400, 'The model you have requested does not exist. Please do not repeat this request.');
                }
            }
            $id = '';
        }

        $modules = Modules::model()->findAllByAttributes(array('editable' => 1));

        $modelList = array('' => '---');
        foreach ($modules as $module) {
            if ($module->name == 'marketing')
                $modelList['Campaign'] = Yii::t('marketing', 'Campaign');
            elseif ($module->name == 'opportunities')
                $modelList['Opportunity'] = Yii::t('opportunities', 'Opportunity');
            elseif ($module->name == 'products')
                $modelList['Product'] = Yii::t('products', 'Product');
            elseif ($module->name == 'quotes')
                $modelList['Quote'] = Yii::t('quotes', 'Quote');
            else
                $modelList[ucfirst($module->name)] = Yii::t('app', $module->title);
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
            $temp = json_decode($dropdown->options, true);
            if (is_array($temp)) {
                $str = implode(", ", $temp);
            } else {
                $str = $dropdown->options;
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
            if (isset($model->options)) {
                foreach ($model->options as $option) {
                    if ($option != "") {
                        $temp[$option] = $option;
                    }
                }
            }
            if (count($temp) > 0) {
                $model->options = json_encode($temp);
                if ($model->save()) {
                    
                }
            }
            $this->redirect(
                    'manageDropDowns'
            );
        }
    }

    /**
     * Delete a custom dropdown
     */
    public function actionDeleteDropdown() {
        $dropdowns = Dropdowns::model()->findAll('id>=1000');

        if (isset($_POST['dropdown'])) {
            if ($_POST['dropdown'] != Actions::COLORS_DROPDOWN_ID) {
                $model = Dropdowns::model()->findByPk($_POST['dropdown']);
                $model->delete();
                $this->redirect('manageDropDowns');
            }
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

        // TODO: validate dropdown select client-side
        if (isset($_POST['Dropdowns']['id']) && ctype_digit ($_POST['Dropdowns']['id'])) {
            $model = Dropdowns::model()->findByPk(
                    $_POST['Dropdowns']['id']);
            if (!isset ($model)) {
                throw new CHttpException (404, Yii::t('app', 'Dropdown could not be found'));
            }
            if ($model->id == Actions::COLORS_DROPDOWN_ID) {
                if (AuxLib::issetIsArray($_POST['Dropdowns']['values']) &&
                        AuxLib::issetIsArray($_POST['Dropdowns']['labels']) &&
                        count($_POST['Dropdowns']['values']) ===
                        count($_POST['Dropdowns']['labels'])) {

                    if (AuxLib::issetIsArray($_POST['Admin']) &&
                            isset($_POST['Admin']['enableColorDropdownLegend'])) {

                        Yii::app()->settings->enableColorDropdownLegend = 
                            $_POST['Admin']['enableColorDropdownLegend'];
                        Yii::app()->settings->save();
                    }

                    $options = array_combine(
                            $_POST['Dropdowns']['values'], $_POST['Dropdowns']['labels']);
                    $temp = array();
                    foreach ($options as $value => $label) {
                        if ($value != "")
                            $temp[$value] = $label;
                    }
                    $model->options = json_encode($temp);
                    $model->save();
                }
            } else {
                $model->attributes = $_POST['Dropdowns'];
                $temp = array();
                if (is_array($model->options) && count($model->options) > 0) {
                    foreach ($model->options as $option) {
                        if ($option != "")
                            $temp[$option] = $option;
                    }
                    $model->options = json_encode($temp);
                    if ($model->save()) {
                        
                    }
                }
            }
        }
        $this->redirect(
                'manageDropDowns'
        );
    }

    /**
     * Print out a dropdown's data
     *
     * This method is called via AJAX by {@link actionEditDropdown} to get the
     * options of the dropdown for the edit dropdown page.
     */
    public function actionGetDropdown() {
        if (isset($_POST['Dropdowns']['id'])) {
            $id = $_POST['Dropdowns']['id'];
            $model = Dropdowns::model()->findByPk($id);
            if ($model === null) {
                return;
            }

            $options = json_decode($model->options);
            if ($id == Actions::COLORS_DROPDOWN_ID) {
                $this->renderPartial(
                        'application.modules.actions.views.actions._colorDropdownForm', array(
                    'model' => $model,
                    'options' => $options,
                ), false, true);
            } else {
                $this->renderPartial(
                        'application.components.views._dropdownForm', array(
                    'model' => $model,
                    'options' => $options,
                ), false, true);
            }
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
            $field = new Fields;
            $field->attributes = $_POST['Fields'];
            $type = $_POST['Fields']['type'];
            $model = new AmorphousModel();
            $model->addField($field, 'customized_field');

            $this->renderPartial('fieldDefault', array(
                'field' => $field,
                'dummyModel' => $model,
                'type' => $type,
                'echoScripts' => true
            ));
        }
    }

    /**
     * Export all data
     *
     * This method is used to export all of the data from the software as a CSV
     */
    public function actionExport() {
        $modelList = array(
            'Admin' => array('name' => Yii::t('admin', 'Admin Settings'), 'count' => 1),
        );
        $modules = Modules::model()->findAll();
        foreach ($modules as $module) {
            $name = ucfirst($module->name);
            if ($name != 'Document') {
                $controllerName = $name . 'Controller';
                if (file_exists('protected/modules/' . $module->name . '/controllers/' . $controllerName . '.php')) {
                    Yii::import("application.modules.$module->name.controllers.$controllerName");
                    $controller = new $controllerName($controllerName);
                    $model = $controller->modelClass;
                    if (class_exists($model)) {
                        $recordCount = X2Model::model($model)->count();
                        if ($recordCount > 0) { // Only display modules we actually have data for...
                            $modelList[$model] = array('name' => Yii::t('app', $module->title), 'count' => $recordCount);
                        }
                    }
                }
            }
        }
        $extraModels = array('Fields', 'Dropdowns', 'FormLayout');
        foreach ($extraModels as $model) {
            if (class_exists($model)) {
                $fieldCount = X2Model::model($model)->count();
                if ($fieldCount > 0)
                    $modelList[$model] = array('name' => Yii::t('app', $model), 'count' => $fieldCount);
            }
        }

        $this->render('export', array(
            'modelList' => $modelList,
        ));
    }

    ///////////////////
    // GLOBAL EXPORT //
    ///////////////////

    /**
     * Helper function to generate the necessary CSV via ajax and insert version data.
     */
    public function actionPrepareExport() {
        $fp = fopen($this->safePath(), 'w+');
        fputcsv($fp, array('v' . Yii::app()->params->version));
        fclose($fp);
    }

    /**
     * An AJAX called method to export module data.
     *
     * This method actually prepares all the data via recursive AJAX requests
     * until all data has been exported. This exports each module into the CSV
     * by class, using pagination to cut down on request time.
     *
     * @param string $model The name of the current model being exported
     * @param int $page The page of data which the data provider's paginator is on
     */
    public function actionGlobalExport($model, $page) {
        if (class_exists($model)) {
            ini_set('memory_limit', -1);
            $file = $this->safePath();
            $fp = fopen($file, 'a+');
            $tempModel = X2Model::model($model);
            $meta = array_keys($tempModel->attributes);
            $meta[] = $model;
            if ($page == 0)
                fputcsv($fp, $meta); // If we're on the first page for this model, need to add metadata.
            $dp = new CActiveDataProvider($model, array(
                'pagination' => array(
                    'pageSize' => 100,
                ),
            ));
            $pg = $dp->getPagination();
            $pg->setCurrentPage($page); // These two lines will set the data provider
            $dp->setPagination($pg); // paginator to the requested page of data
            $records = $dp->getData();
            $pageCount = $dp->getPagination()->getPageCount(); // Total number of pages

            foreach ($records as $record) {
                // Re-pack all unpacked attributes for writing to a file, so that
                // they can be interpolated as strings:
                foreach ($record->behaviors() as $name => $config) {
                    $behavior = $record->asa($name);
                    if ($behavior instanceof TransformedFieldStorageBehavior) {
                        $behavior->packAll();
                        $record->disableBehavior($name);
                    }
                }
                $tempAttributes = $tempModel->attributes;
                $tempAttributes = array_merge($tempAttributes, $record->attributes);
                if ($model == 'Profile') {
                    $tempAttributes['theme'] = json_encode($record->theme);
                }
                $tempAttributes[] = $model;
                fputcsv($fp, $tempAttributes); // Export the data to CSV
            }

            unset($tempModel, $dp);

            fclose($fp);
            if ($page + 1 < $pageCount) {
                echo $page + 1; // If there are still more pages to go, echo the next page number
            }
        }
    }

//  $file = Yii::app()->file->set($this->safePath($file));
    /**
     * Helper function called in a lot of places to download a file
     * @param string $file Filepath of the requested file
     */
    public function actionDownloadData($file) {
        $this->sendFile($file);
    }

    /**
     * An AJAX called function used to rollback a data import.
     *
     * This function is called several times with different parameters as a part
     * of the rollback process and runs a variety of SQL queries to remove data
     * created as part of the import process.
     * @param string $model The name of the model Class
     * @param string $stage The stage to be run for this step
     * @param int $importId The ID of the import being rolled back
     */
    public function actionRollbackStage($model, $stage, $importId) {
        $stages = array(
            // Delete all tag data
            "tags" => "DELETE a FROM x2_tags a
                INNER JOIN
                x2_imports b ON b.modelId=a.itemId AND b.modelType=a.type
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete all relationship data
            "relationships" => "DELETE a FROM x2_relationships a
                INNER JOIN
                x2_imports b ON b.modelId=a.firstId AND b.modelType=a.firstType
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete any associated actions
            "actions" => "DELETE a FROM x2_actions a
                INNER JOIN
                x2_imports b ON b.modelId=a.associationId AND b.modelType=a.associationType
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete the records themselves
            "records" => "DELETE a FROM " . X2Model::model($model)->tableName() . " a
                INNER JOIN
                x2_imports b ON b.modelId=a.id
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete the log of the records being imported
            "import" => "DELETE FROM x2_imports WHERE modelType='$model' AND importId='$importId'",
        );
        $sqlQuery = $stages[$stage];
        $command = Yii::app()->db->createCommand($sqlQuery);
        $result = $command->execute();
        echo $result;
    }

    /**
     * An administrative view to rollback any data imports which have been conducted.
     */
    public function actionRollbackImport() {
        // If an import ID is passed, load specific information about this import
        if (isset($_GET['importId']) && ctype_digit ($_GET['importId'])) {
            $importId = $_GET['importId'];
            $types = Yii::app()->db->createCommand()
                    ->select('modelType')
                    ->from('x2_imports')
                    ->group('modelType')
                    ->where('importId=:importId', array(':importId' => $importId))
                    ->queryAll();
            $count = Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from('x2_imports')
                    ->group('importId')
                    ->where('importId=:importId', array(':importId' => $importId))
                    ->queryRow();
            $count = $count['COUNT(*)'];
            $typeArray = array();
            foreach ($types as $tempArr) {
                $typeArray[] = $tempArr['modelType'];
            }
            $this->render('rollbackImport', array(
                'typeArray' => $typeArray,
                'dataProvider' => null,
                'count' => $count,
                'importId' => $importId,
            ));
        } else {
            // Otherwise, load a list of imports to choose from
            $data = array();
            $imports = Yii::app()->db->createCommand()
                    ->select('importId')
                    ->from('x2_imports')
                    ->group('importId')
                    ->queryAll();
            foreach ($imports as $key => $array) {
                $data[$key]['id'] = $key;
                $data[$key]['importId'] = $array['importId'];
                $count = Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_imports')
                        ->group('importId')
                        ->where('importId=:importId', array(':importId' => $array['importId']))
                        ->queryRow();
                $data[$key]['type'] = Yii::app()->db->createCommand()
                        ->select('modelType')
                        ->from('x2_imports')
                        ->where('importId=:importId', array(':importId' => $array['importId']))
                        ->queryScalar();
                $data[$key]['records'] = $count['COUNT(*)'];
                $timestamp = Yii::app()->db->createCommand()
                        ->select('timestamp')
                        ->from('x2_imports')
                        ->group('importId')
                        ->order('timestamp ASC')
                        ->where('importId=:importId', array(':importId' => $array['importId']))
                        ->queryRow();
                $data[$key]['timestamp'] = $timestamp['timestamp'];
                $data[$key]['link'] = "";
            }
            $dataProvider = new CArrayDataProvider($data);
            $this->render('rollbackImport', array(
                'typeArray' => array(),
                'dataProvider' => $dataProvider,
            ));
        }
    }

    /**
     * Import data from a CSV
     *
     * This method allows for the import of data by the admin into the software.
     * This import expects machine readable data (i.e. data which would be directly
     * inserted into the database like unix timestamps) and the final column of
     * each row should be the type of record being imported (e.g. Contacts, Actions, etc.)
     * This particular function merely renders the upload page.
     */
    public function actionImport() {
        $formModel = new GlobalImportFormModel;

        if (isset ($_POST['GlobalImportFormModel']) && isset ($_FILES['GlobalImportFormModel'])) {
            $formModel->setAttributes ($_POST['GlobalImportFormModel']);
            $formModel->data = CUploadedFile::getInstance ($formModel, 'data');

            if ($formModel->validate ()) {
                $_SESSION['overwrite'] = $formModel->overwrite;
                $_SESSION['counts'] = array();
                $_SESSION['overwriten'] = array();
                $_SESSION['overwriteFailure'] = array();
                $_SESSION['model'] = "";
                $_SESSION['failed'] = 0;

                if ($formModel->data->saveAs($this->safePath())) {
                    // If we have post data, render the import processing page
                    $this->render('processImport', array(
                        'overwrite' => $formModel->overwrite,
                    ));
                    Yii::app()->end ();
                } else {
                    $formModel->addError ('data', Yii::t('admin', 'File could not be uploaded'));
                }
            }
        }

        $this->render('import', array (
            'formModel' => $formModel
        ));
    }

    /**
     * Helper function to prepare a lot of the necessary information for a data
     * import. A large amount of this data is stored in the session so as to be
     * preserved between the AJAX requests which will occur as a part of the import
     * process.
     */
    public function actionPrepareImport() {
        $fp = fopen($this->safePath(), 'r+');
        $version = fgetcsv($fp); // The first row should be just the version number of the data
        $version = $version[0];
        $tempMeta = fgetcsv($fp);
        while ("" === end($tempMeta)) { // Clear all blank rows from the metadata
            array_pop($tempMeta);
        }
        $model = array_pop($tempMeta); // The last column should be the model class
        $_SESSION['metaData'] = $tempMeta; // Store the current metadata
        $_SESSION['model'] = $model; // Store the current class
        $_SESSION['lastFailed'] = "";
        /*
         * THIS IS ESSENTIAL. The ftell function reads the current position in the
         * file so we know where to start from next time. All AJAX based imports
         * will neeed to use this function.
         */
        $_SESSION['offset'] = ftell($fp);
        fclose($fp);
        $criteria = new CDbCriteria;
        $criteria->order = "importId DESC";
        $criteria->limit = 1;
        $import = Imports::model()->find($criteria);
        if (isset($import)) { // Set the ID of the current import to be 1 higher than the last one
            $_SESSION['importId'] = $import->importId + 1;
        } else {
            $_SESSION['importId'] = 1;
        }
        $failedImport = fopen($this->safePath('failedImport.csv'), 'w+'); // Prepare a CSV for any failed records
        fputcsv($failedImport, array(Yii::app()->params->version));
        fclose($failedImport);
        echo json_encode(array($version));
    }

    public function actionPrepareModelImport() {
        // Keys & attributes are our finalized import map
        if (isset($_POST['attributes']) && isset($_POST['keys']) && isset($_POST['model'])) {
            $model = $_POST['model'];
            $keys = $_POST['keys'];
            $attributes = $_POST['attributes'];
            $preselectedMap = (isset($_POST['preselectedMap']) && $_POST['preselectedMap'] === 'true') ? true : false;
            $_SESSION['tags'] = array();
            // Grab any tags that need to be added to each record
            if (isset($_POST['tags']) && !empty($_POST['tags'])) {
                $tags = explode(',', $_POST['tags']);
                foreach ($tags as $tag) {
                    if (substr($tag, 0, 1) != "#")
                        $tag = "#" . $tag;
                    $_SESSION['tags'][] = $tag;
                }
            }
            // The override allows the user to specify fixed values for certain fields
            $_SESSION['override'] = array();
            if (isset($_POST['forcedAttributes']) && isset($_POST['forcedValues'])) {
                $override = array_combine($_POST['forcedAttributes'], $_POST['forcedValues']);
                $_SESSION['override'] = $override;
            }
            // Comments will log a comment on the record
            $_SESSION['comment'] = "";
            if (isset($_POST['comment']) && !empty($_POST['comment'])) {
                $_SESSION['comment'] = $_POST['comment'];
            }
            // Whether to use lead routing
            $_SESSION['leadRouting'] = 0;
            if (isset($_POST['routing']) && $_POST['routing'] == 1) {
                $_SESSION['leadRouting'] = 1;
            }
            // Whether to post the new records to the activity feed
            $_SESSION['skipActivityFeed'] = 0;
            if (isset($_POST['skipActivityFeed']) && $_POST['skipActivityFeed'] == 1) {
                $_SESSION['skipActivityFeed'] = 1;
            }
            $_SESSION['createRecords'] = $_POST['createRecords'] == "checked" ? "1" : "0";
            $_SESSION['imported'] = 0;
            $_SESSION['failed'] = 0;
            $_SESSION['created'] = array();
            if ($preselectedMap) {
                $keys = array_keys($_SESSION['importMap']);
                $attributes = array_values($_SESSION['importMap']);
            }
            $this->verifyImportMap($model, $keys, $attributes);
            $cache = Yii::app()->cache;
            if (isset($cache)) {
                $cache->flush();
            }
        }
    }

    /**
     * Helper function to return the next importId
     * @return int Next import ID
     */
    private function getNextImportId() {
        $criteria = new CDbCriteria;
        $criteria->order = "importId DESC";
        $criteria->limit = 1;
        $import = Imports::model()->find($criteria);

        // Figure out which import this is so we can set up the Imports models
        // for this import.
        if (isset($import)) {
            $importId = $import->importId + 1;
        } else {
            $importId = 1;
        }
        return $importId;
    }

    /**
     * Parse the given keys and attributes to ensure required fields are
     * mapped and new fields are to be created. The verified map will be
     * stored in the 'importMap' key for the $_SESSION super global.
     * @param string $model name of the model
     * @param array $keys
     * @param array $attributes
     * @param boolean $createFields whether or not to create new fields
     */
    protected function verifyImportMap($model, $keys, $attributes, $createFields = false) {
        if (!empty($keys) && !empty($attributes)) {
            // New import map is the provided data
            $importMap = array_combine($keys, $attributes);
            $conflictingFields = array();
            $failedFields = array();

            // To keep track of fields that were mapped multiple times
            $mappedValues = array();
            $multiMappings = array();

            foreach ($importMap as $key => &$value) {
                if (in_array($value, $mappedValues) && !empty($value) && !in_array($value, $multiMappings)) {
                    // This attribute is mapped to two different fields in X2
                    $multiMappings[] = $value;
                } else if ($value !== 'createNew') {
                    $mappedValues[] = $value;
                }
                // Loop through and figure out if we need to create new fields
                $origKey = $key;
                $key = Formatter::deCamelCase($key);
                $key = preg_replace('/\[W|_]/', ' ', $key);
                $key = mb_convert_case($key, MB_CASE_TITLE, "UTF-8");
                $key = preg_replace('/\W/', '', $key);
                if ($value == 'createNew' && !$createFields) {
                    $importMap[$origKey] = 'c_' . strtolower($key);
                    $fieldLookup = Fields::model()->findByAttributes(array('modelName' => $model, 'fieldName' => $key));
                    if (isset($fieldLookup)) {
                        $conflictingFields[] = $key;
                        continue;
                    } else {
                        $customFieldLookup = Fields::model()->findByAttributes(array('modelName' => $model, 'fieldName' => $importMap[$origKey]));
                        if (!$customFieldLookup instanceof Fields) {
                            // Create a custom field if one doesn't exist already
                            $columnName = strtolower($key);
                            $field = new Fields;
                            $field->modelName = $model;
                            $field->type = "varchar";
                            $field->fieldName = $columnName;
                            $field->required = 0;
                            $field->searchable = 1;
                            $field->relevance = "Medium";
                            $field->custom = 1;
                            $field->modified = 1;
                            $field->attributeLabel = $field->generateAttributeLabel($key);
                            if (!$field->save()) {
                                $failedFields[] = $key;
                            }
                        }
                    }
                }
            }

            // Check for required attributes that are missing
            $requiredAttrs = Yii::app()->db->createCommand()
                    ->select('fieldName, attributeLabel')
                    ->from('x2_fields')
                    ->where('modelName = :model AND required = 1', array(':model' => str_replace(' ', '', $model)))
                    ->query();
            $missingAttrs = array();
            foreach ($requiredAttrs as $attr) {
                // Skip visibility, it can be set for them
                if (strtolower($attr['fieldName']) == 'visibility')
                    continue;
                // Ignore missing first/last name, this can be inferred from full name
                if ($model === 'Contacts' && ($attr['fieldName'] === 'firstName' || $attr['fieldName'] === 'lastName') && in_array('name', array_values($importMap)))
                    continue;
                // Otherwise, a required field is missing and should be reported to the user
                if (!in_array($attr['fieldName'], array_values($importMap)))
                    $missingAttrs[] = $attr['attributeLabel'];
            }
            if (!empty($conflictingFields)) {
                echo CJSON::encode(array("2", implode(', ', $conflictingFields)));
            } else if (!empty($missingAttrs)) {
                echo CJSON::encode(array("3", implode(', ', $missingAttrs)));
            } else if (!empty($failedFields)) {
                echo CJSON::encode(array("1", implode(', ', $failedFields)));
            } else if (!empty($multiMappings)) {
                echo CJSON::encode(array("4", implode(', ', $multiMappings)));
            } else {
                echo CJSON::encode(array("0"));
            }

            $_SESSION['importMap'] = $importMap;
        } else {
            echo CJSON::encode(array("0"));
            $_SESSION['importMap'] = array();
        }
    }

    /**
     * List available import maps from a directory,
     * optionally of type $model
     * @param string $model Name of the model to load import maps
     * @return array Available import maps, with the filenames as
     * keys, and import mapping names (product/version) as values
     */
    protected function availableImportMaps($model = null) {
        $maps = array();
        if ($model === "X2Leads")
            $model = "Leads";
        $modelName = (isset($model)) ? lcfirst($model) : '.*';
        $importMapDir = "importMaps";
        $files = scandir($this->safePath($importMapDir));
        foreach ($files as $file) {
            $filename = basename($file);
            // Filenames are in the form "app-model.json"
            if (!preg_match('/^.*-' . $modelName . '\.json$/', $filename))
                continue;
            $mapping = file_get_contents($this->safePath($importMapDir . DIRECTORY_SEPARATOR . $file));
            $mapping = json_decode($mapping, true);
            $maps[$file] = $mapping['name'];
        }
        return $maps;
    }

    /**
     * Load an import map from the map directory
     * @param string $filename of the import map
     * @return array Import map
     */
    protected function loadImportMap($filename) {
        if (empty($filename))
            return null;
        $importMapDir = "importMaps";
        $map = file_get_contents($this->safePath($importMapDir . DIRECTORY_SEPARATOR . $filename));
        $map = json_decode($map, true);
        return $map;
    }

    /**
     * Allows for control of setting the externally visible URL for the CRM.
     * This function is in the wrong place (in the middle of all the import functions)
     * and should be cleaned up (or possibly refactored, see my notes on the Admin
     * Controller refactor) but I'm only writing comments right now and trying
     * not to make code modifications.
     */
    public function actionPublicInfo() {
        $admin = &Yii::app()->settings;
        if (isset($_POST['Admin'])) {
            $admin->attributes = $_POST['Admin'];
            if ($admin->save()) {
                $this->redirect('publicInfo');
            }
        }
        if ($admin->externalBaseUrl == '' && !$admin->hasErrors('externalBaseUrl'))
            $admin->externalBaseUrl = Yii::app()->request->getHostInfo();
        if ($admin->externalBaseUri == '' && !$admin->hasErrors('externalBaseUri'))
            $admin->externalBaseUri = Yii::app()->baseUrl;
        $this->render('publicInfo', array(
            'model' => $admin,
        ));
    }

    /**
     * Import a set of CSV data into the software.
     *
     * This function is called via AJAX and is the meat of the global import process.
     * It takes the variable "count" as POST data to determine how many records
     * it should import in this step, which is usually 50, but is arbitrary
     * except for server load considerations. It reads data out of the "data.csv"
     * file and imports it. See inline comments for details of what's going on.
     *
     * @return null A return statement to cease further execution, could probably be cleaned up & removed
     */
    public function actionGlobalImport() {
        if (isset($_POST['count']) && file_exists($this->safePath())) {
            $metaData = $_SESSION['metaData']; // Grab the most recent metadata
            $modelType = $_SESSION['model']; // And model
            $count = $_POST['count'];
            $fp = fopen($this->safePath(), 'r+');
            /*
             * THIS IS ESSENTIAL. As with the above block noted as essential,
             * this was KEY to figuring out how to do an AJAX based CSV read.
             * The fseek function will move the file pointer to the specified offset,
             * which we always store in the $_SESSION['offset'] variable.
             */
            fseek($fp, $_SESSION['offset']);
            for ($i = 0; $i < $count; $i++) { // Loop up to the speficied count.
                $arr = fgetcsv($fp); // Grab the next row
                if ($arr !== false && !is_null($arr)) {
                    while ("" === end($arr)) { // Remove blank space from the end
                        array_pop($arr);
                    }
                    $newType = array_pop($arr); // Pull the last column to check the model type
                    if ($newType != $modelType) {
                        /*
                         * If this is the first row of a new model type, the data
                         * in the last column will be a different class name. In that
                         * case, we assume this new row consists of the metadata
                         * for this new model class and that the next set of records
                         * will be of this model type. This information is stored
                         * in the session in case a set of 50 records breaks
                         * unevenly across model types (e.g. the system needs to import
                         * more than 50 of a given record).
                         */
                        $_SESSION['model'] = $newType;
                        $_SESSION['metaData'] = $arr;
                        $modelType = $_SESSION['model'];
                        $metaData = $_SESSION['metaData'];
                    } else {
                        $attributes = array_combine($metaData, $arr);
                        if ($modelType == "Actions" && (isset($attributes['type']) && 
                            $attributes['type'] == 'workflow')) {
                            // In the event that we're importing workflow, we need a special 
                            // scenario.
                            $model = new Actions('workflow');
                        } else {
                            $model = new $modelType;
                        }
                        /*
                         * This loops through and sets the attributes manually.
                         * Realistically, this could be refactored to use the
                         * SetX2Fields function, but you'd need to be sure the
                         * data wasn't double formatted (e.g. it's already a unix
                         * timestamp, not a date string, and doesn't need to be
                         * converted again) due to the fact that a user could supply
                         * either human readable or machine readable data.
                         */
                        foreach ($attributes as $key => $value) {
                            if ($model->hasAttribute($key) && isset($value)) {
                                if ($value == "")
                                    $value = null;
                                $model->$key = $value;
                            }
                        }
                        // Don't make a changelog record.
                        $model->disableBehavior('changelog');
                        // Don't manually set the timestamp fields
                        $model->disableBehavior('X2TimestampBehavior');
                        if ($model instanceof User || $model instanceof Profile) {
                            if ($model->id == '1') {
                                /*
                                 * If a model of type User with the ID of one is
                                 * being imported skip so that we DO NOT
                                 * OVERWRITE THE CURRENT ADMIN USER.
                                 */
                                continue;
                            }
                            // Users & Profile normally require special validation, set a scenario for import
                            $model->setScenario('import');
                        }
                        if ($_SESSION['overwrite'] == 1 && 
                            property_exists ($model, 'subScenario')) {

                            $model->subScenario = 'importOverwrite';
                        }

                        // If an ID was provided, check if there's already a model with that ID
                        $lookup = X2Model::model($modelType)->findByPk($model->id);
                        $lookupFlag = isset($lookup);
                        /*
                         * I'm not sure if "validate" will succeed anymore given the
                         * change made to ID being a "unique" field in X2Model's rules
                         * This should be investigated at some point.
                         */
                        if ($model->validate() || $modelType == "User" || $modelType == 'Profile') {
                            $saveFlag = true;
                            if ($lookupFlag) {
                                if ($_SESSION['overwrite'] == 1) { // If the user specified to overwrite, delete the old lookup
                                    if ($modelType === "Fields") {
                                        /**
                                         * Leave fields intact; the record information would be deleted when
                                         * the column is removed by Fields' afterDelete hook.
                                         */
                                        continue;
                                    }
                                    $lookup->disableBehavior('changelog');
                                    $lookup->delete();
                                } else {
                                    $saveFlag = false; // Otherwise, note a failure in the logging section that we were unable to overwrite a record.
                                    isset($_SESSION['overwriteFailure'][$modelType]) ? $_SESSION['overwriteFailure'][$modelType] ++ : $_SESSION['overwriteFailure'][$modelType] = 1;
                                }
                                if (!$model->validate()) {
                                    $saveFlag = false;
                                    $failedImport = fopen($this->safePath('failedImport.csv'), 'a+');
                                    $lastFailed = $_SESSION['lastFailed'];
                                    if ($lastFailed != $modelType) {
                                        $tempMeta = $metaData; // Keep track of the metadata of failed records
                                        $tempMeta[] = $modelType;
                                        fputcsv($failedImport, $tempMeta);
                                    }
                                    $attr = $model->attributes;
                                    $tempAttributes = X2Model::model($modelType)->attributes;
                                    $attr = array_merge($tempAttributes, $attr);
                                    $attr[] = $modelType;
                                    fputcsv($failedImport, $attr);
                                    $_SESSION['lastFailed'] = $modelType; // Specify the most recent model type failure in case metadata needs to be changed
                                    isset($_SESSION['failed']) ? $_SESSION['failed'] ++ : $_SESSION['failed'] = 1;
                                }
                            }
                            if ($saveFlag && $model->save()) {
                                if ($modelType != "Admin" && !(($modelType == "User" || $modelType == "Profile") && ($model->id == '1' || $model->username == 'api'))) {
                                    // Generate a new "Imports" model in case of rollback
                                    $importLink = new Imports;
                                    $importLink->modelType = $modelType;
                                    $importLink->modelId = $model->id;
                                    $importLink->importId = $_SESSION['importId'];
                                    $importLink->timestamp = time();
                                    $importLink->save();
                                }
                                if ($modelType === "Fields") {
                                    // If we're creating a field, we must also recreate the 
                                    // respective table index
                                    if (isset($model->keyType))
                                        $model->createIndex($model->keyType === "UNI");
                                } else if ($modelType === "FormLayout") {
                                    /*
                                    Ensure default form settings are maintained. If overwrite 
                                    is set, the most recently imported layout will be set to 
                                    default, otherwise the default flags for the newly imported 
                                    layout will be cleared.
                                    */
                                    if ($_SESSION['overwrite']) {
                                        if ($model->defaultView)
                                            FormLayout::clearDefaultFormLayouts (
                                                'view', $model->model);
                                        if ($model->defaultForm)
                                            FormLayout::clearDefaultFormLayouts (
                                                'form', $model->model);
                                        $model->save ();
                                    } else {
                                        $model->defaultView = false;
                                        $model->defaultForm = false;
                                        $model->save();
                                    }
                                }
                                // Relic of when action description wasn't a field, not sure if necessary.
                                if ($modelType == 'Actions' && isset($attributes['actionDescription'])) {
                                    $model->actionDescription = $attributes['actionDescription'];
                                }
                                // Update counts in the session logging variables.
                                isset($_SESSION['counts'][$modelType]) ? $_SESSION['counts'][$modelType] ++ : $_SESSION['counts'][$modelType] = 1;
                                if ($lookupFlag) {
                                    isset($_SESSION['overwriten'][$modelType]) ? $_SESSION['overwriten'][$modelType] ++ : $_SESSION['overwriten'][$modelType] = 1;
                                } else {
                                    isset($_SESSION['overwriten'][$modelType])? : $_SESSION['overwriten'][$modelType] = 0;
                                }
                            }
                        } else {
                            // Put the failed lead into the failed import CSV
                            //AuxLib::debugLogR ('failed to import '.get_class ($model));
                            //AuxLib::debugLogR ($model->getErrors ());
                            $failedImport = fopen($this->safePath('failedImport.csv'), 'a+');
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
                            isset($_SESSION['failed']) ? $_SESSION['failed'] ++ : $_SESSION['failed'] = 1;
                        }
                    }
                } else {
                    // "0" at the beginning means we reached the end of the file
                    // and don't need to do another set.
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
            // Update the file offset pointer in the session.
            $_SESSION['offset'] = ftell($fp);
            echo json_encode(array(
                1, // The "1" indicated we need to keep going.
                json_encode($_SESSION['counts']),
                json_encode($_SESSION['overwriten']),
                json_encode($_SESSION['failed']),
                json_encode($_SESSION['overwriteFailure']),
            ));
        }
    }

    /**
     * Post-processing function for the import tool, unset session variables
     * and delete the uploaded data file.
     */
    public function actionCleanUpImport() {
        unlink($this->safePath());
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
     * Post-processing for the import process to clean out the SESSION vars.
     */
    public function actionCleanUpModelImport() {
        unset($_SESSION['tags']);
        unset($_SESSION['override']);
        unset($_SESSION['comment']);
        unset($_SESSION['leadRouting']);
        unset($_SESSION['createRecords']);
        unset($_SESSION['imported']);
        unset($_SESSION['failed']);
        unset($_SESSION['created']);
        unset($_SESSION['importMap']);
        unset($_SESSION['offset']);
        unset($_SESSION['metaData']);
        unset($_SESSION['fields']);
        unset($_SESSION['x2attributes']);
        unset($_SESSION['model']);
        if (file_exists($path = $this->safePath('data.csv'))) {
            unlink($path);
        }
        if (file_exists($path = $this->safePath('importMapping.json'))) {
            unlink($path);
        }
    }

    /**
     * Control settings for the updater
     *
     * This method controls the update interval setting for the application.
     */
    public function actionUpdaterSettings() {
        $admin = &Yii::app()->settings;
        // Save new updater cron settings in crontab
        $cf = new CronForm;
        $cf->jobs = array(
            'app_update' => array(
                'cmd' => Yii::app()->basePath . DIRECTORY_SEPARATOR . 'yiic update app --lock=1',
                'desc' => Yii::t('admin', 'Automatic software updates cron job'),
            ),
        );
        if (isset($_POST['Admin'])) {
            $admin->setAttributes($_POST['Admin']);
            foreach (array('unique_id', 'edition') as $var)
                if (isset($_POST['unique_id']))
                    $admin->$var = $_POST[$var];
            if ($admin->save()) {
                if (isset($_POST['cron'])) {
                    // Save new updater cron settings in crontab
                    $cf->save($_POST);
                } else {
                    // Delete remaining jobs
                    $cf->save(array());
                }
                $this->redirect('updaterSettings');
            }
        }
        foreach ($cf->jobs as $tag => $attributes) {
            $commands[$tag] = $attributes['cmd'];
        }
        if (isset($_POST['cron'])) {
            // Save new updater cron settings in crontab
            $cf->save($_POST);
        }
        $this->render('updaterSettings', array(
            'model' => $admin,
            'displayCmds' => $commands
        ));
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
      } */

    /**
     * Prints an error message explaing what has gone wrong when the classes are missing.
     * @param array $classes The missing dependencies
     */
    public function missingClassesException($classes) {
        $message = Yii::t('admin', 'One or more dependencies of AdminController are missing and could not be automatically retrieved. They are {classes}', array('{classes}' => implode(', ', $classes)));
        $message .= "\n\n" . Yii::t('admin', 'To diagnose this error, please upload and run the requirements check script on your server.');
        $message .= "\nhttps://x2planet.com/installs/requirements.php";
        $message .= "\n\n" . Yii::t('admin', 'The error is most likely due to one of the following things:');
        $message .= "\n(1) " . Yii::t('admin', 'PHP processes run by the web server do not have permission to create or modify files');
        $message .= "\n(2) " . Yii::t('admin', 'x2planet.com and raw.github.com are currently unavailable');
        $message .= "\n(3) " . Yii::t('admin', 'This web server has no outbound internet connection. This could be because it is behind a firewall that does not permit outbound connections, operating within a private network with broken domain name resolution, or with no outbound route.');
        $message .= "\n\n" . Yii::t('admin', 'To stop this error from occurring, if the problem persists, restore the file {adminController} to the copy from your version of X2Engine:', array('{adminController}' => 'protected/controllers/AdminController.php'));
        $message .= "\n" . "https://raw.github.com/X2Engine/X2Engine/" . Yii::app()->params->version . "/x2engine/protected/controllers/AdminController.php";
        $this->error500($message);
    }

    /**
     * Function written by Matthew to display a tree-like hierarchy of the roles
     * Legend:
     *  blue: roles (type 0)
     *  white: tasks (type 1)
     *  red: actions (type 2)
     */
    public function actionAuthGraph() {

        if (!Yii::app()->params->isAdmin)
            return;

        $allTasks = array();

        $authGraph = array();

        $taskNames = Yii::app()->db->createCommand()
                ->select('name')
                ->from('x2_auth_item')
                ->where('type=1')
                ->queryColumn();

        foreach ($taskNames as $task) {
            $children = Yii::app()->db->createCommand()
                    ->select('child')
                    ->from('x2_auth_item_child')
                    ->where('parent=:parent', array(':parent' => $task))
                    ->queryColumn();

            foreach ($children as $child)
                $allTasks[$task][$child] = array();
        }

        $bizruleTasks = Yii::app()->db->createCommand()
                ->select('name')
                ->from('x2_auth_item')
                ->where('bizrule IS NOT NULL')
                ->queryColumn();

        function buildGraph($task, &$allTasks, &$authGraph) {

            if (!isset($allTasks[$task]) || empty($allTasks[$task])) {
                return array();
            } else {
                $children = array();

                foreach (array_keys($allTasks[$task]) as $child) {

                    if (isset($authGraph[$child]) && $authGraph[$child] === false)
                        continue;

                    $childGraph = buildGraph($child, $allTasks, $authGraph);

                    $children[$child] = $childGraph;
                    $authGraph[$child] = false; // this is a child task, remove it from the top level
                }
                return $children;
            }
        }

        foreach (array_keys($allTasks) as $task)
            $authGraph[$task] = buildGraph($task, $allTasks, $authGraph);

        foreach (array_keys($authGraph) as $key) {
            if (empty($authGraph[$key]))
                unset($authGraph[$key]);
        }

        $this->render('authGraph', array('authGraph' => $authGraph, 'bizruleTasks' => $bizruleTasks));
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
    public function copyRemote($remoteFile, $localFile, $curl) {
        $this->checkRemoteMethods();
        if (!$curl) {
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
                return file_put_contents($localFile, $contents) !== false;
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
            $this->_noRemoteAccess = !extension_loaded('curl') && (
                    in_array(ini_get('allow_url_fopen'), array(0, 'Off', 'off')) || !(function_exists('file_get_contents') && function_exists('copy'))
                    );
        return $this->_noRemoteAccess;
    }

    /**
     * Check whether it is possible to retrieve remote files.
     */
    public function checkRemoteMethods() {
        if ($this->noRemoteAccess)
            $this->error500(Yii::t('admin', 'X2Engine needs to retrieve one or more remote files, but no remote access methods are available on this web server, because allow_url_fopen is disabled and the CURL extension is missing.'));
    }

    /**
     * Explicit, attention-grabbing error message w/o bug reporter.
     *
     * This is intended for errors that are NOT bugs, but that arise from server
     * malconfiguration and/or missing requirements for running X2Engine, as a
     * last-ditch effort to fail gracefully.
     * @param type $message
     */
    public function error500($message) {
        $app = Yii::app();
        $email = Yii::app()->params->adminEmail;
        $inAction = $this->action instanceof CAction;
        if ($app->params->hasProperty('admin')) {
            if ($app->params->admin->hasProperty('emailFromAddr'))
                $email = $app->params->admin->emailFromAddr;
        } else if ($app->hasProperty('settings')) {
            if ($app->settings->hasProperty('emailFromAddr')) {
                $email = $app->settings->emailFromAddr;
            }
        }
        $inAction = @is_subclass_of($this->action, 'CAction');
        if ($inAction) {
            $data = array(
                'scenario' => 'error',
                'message' => Yii::t('admin', "Cannot run {action}.", array('{action}' => $this->action->id)),
                'longMessage' => str_replace("\n", "<br />", $message),
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

    /**
     * Change the application name
     */
    public function actionChangeApplicationName() {
        $model = Admin::model()->findByPk(1);
        if (isset($_POST['Admin'])) {
            $model->setAttributes($_POST['Admin']);
            if ($model->save()) {
                $this->redirect('index');
            }
        }
        $this->render('changeApplicationName', array(
            'model' => $model
        ));
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
            if ($type == 'Marketing')
                $type = 'Campaign';
            elseif ($type == 'Quotes')
                $type = 'Quote';
            elseif ($type == 'Products')
                $type = 'Product';
            elseif ($type == 'Opportunities')
                $type = 'Opportunity';

            foreach (X2Model::model('Fields')->findAllByAttributes(array('modelName' => $type)) as $field) {
                if ($field->fieldName != 'id') {
                    if (isset($_POST['Criteria']))
                        $data[$field->fieldName] = $field->attributeLabel;
                    else
                        $data[$field->id] = $field->attributeLabel;
                }
            }
        }
        asort($data);
        $data = array('' => '-') + $data;
        $htmlOptions = array();
        echo CHtml::listOptions('', $data, $htmlOptions);
    }

    
    
//    public function actionTestURL(){
//        $baseUrl = Yii::app()->request->getIsSecureConnection()?"https://":"http://"
//                .Yii::app()->request->serverName;
//        $baseUri = Yii::app()->baseUrl;
//        if(empty(Yii::app()->settings->externalBaseUrl)){
//            Yii::app()->settings->externalBaseUrl = $baseUrl;
//        }
//        if(empty(Yii::app()->settings->externalBaseUri)){
//            Yii::app()->settings->externalBaseUri = $baseUri;
//        }
//        Yii::app()->settings->save();
//    }
}
