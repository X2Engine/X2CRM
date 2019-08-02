<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




Yii::import('application.components.Ical');

/**
 *  Calendar lets you create calendar events, view actions from other modules, and sync to google calendar.
 *
 * @property User $currentUser The currently logged-in user who is accessing the calendar
 * @package application.modules.calendar.controllers
 */
class CalendarController extends x2base {

    private $_currentUser;

    public $modelClass = 'X2Calendar';
    public $calendarUsers = null; // list of users for choosing whose calendar to view
    public $groupCalendars = null;
    public $sharedCalendars = null; // list of shared calendars to view/hide
    public $googleCalendars = null;
    public $calendarFilter = null;

    public function accessRules(){
        return array(
            array(
                'allow',
                'actions' => array('getItems'),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array(
                    'index',
                    'jsonFeed',
                    'myCalendarPermissions',
                    'create',
                    'update',
                    'list',
                    'delete',
                    'createEvent',
                    'view',
                    'viewAction',
                    'editAction',
                    'moveAction',
                    'resizeAction',
                    'saveAction',
                    'completeAction',
                    'uncompleteAction',
                    'deleteAction',
                    'saveCheckedCalendar',
                    'toggleUserCalendarsVisible',
                    'togglePortletVisible',
                    //'uploadToGoogle'
                ),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'userCalendarPermissions'),
                'users' => array('admin'),
            ),
            array(
                'deny', // deny all users
                'users' => array('*'),
            ),
        );
    }
    
    public function actionSyncActionsToGoogleCalendar(){
        $auth = new GoogleAuthenticator('calendar');
        $token = $auth->getAccessToken();
        $this->redirect('index');
    }

    /**
     * Show Calendar
     */
    public function actionIndex(){
        // Yii::app()->params->profile->syncAllEvents();
        // $events = $this->feedAll(); // Submits prerendered list of events to the calendar.        
        $this->initCheckedCalendars(); // ensure user has a list to save checked calendars
        $this->render('calendar', array());
    }

    /**
     * Show Calendar
     */
    public function actionAdmin(){
        $this->initCheckedCalendars(); // ensure user has a list to save checked calendars
        $this->render('calendar');
    }

    public function actionView($id){
        if($id == 0)
            $this->redirect(array('index'));
        else{
            $model = X2Calendar::model()->findByPk($id);
            parent::view($model, 'calendar');
        }
    }

    

    /**
     * Formats calendars in iCal format, for third-party calendar software.
     * @param type $user
     * @param type $key
     */
    public function actionIcal($user,$key,$calendars=null,$daysAhead=30,$daysBehind=30) {
        $user = User::model()->findByAlias($user);
        if(!($user instanceof User) || $user->calendarKey != $key) {
            header('Status: 401 Forbidden');
            Yii::app()->end();
        }
        $this->_currentUser = $user;
        Yii::app()->setSuModel($user);
        // It may be necessary to instead use 'text/calendar' as the
        // content type in some instances, e.g., Google.
        header('Content-Type: text/plain');

        $calendars = isset($_GET['calendars'])? explode(',',$_GET['calendars']) : array($user->username);
        $start = (isset($_GET['start']))? $_GET['start'] : time() - 86400 * $daysBehind;
        $end = (isset($_GET['end']))? $_GET['end'] : time() + 86400 * $daysAhead;
        $calendarActions = array();

        // Retrieve relevent actions
        foreach ($calendars as $cal) {
            $userCal = self::calendarActions($cal, $start, $end);
            if ($userCal != null && is_array($userCal))
                $calendarActions = array_merge($calendarActions, $userCal);
        }

        $ical = new Ical;
        $ical->setActions($calendarActions);
        $ical->render();
    }
   
    // overridden to disable parent method
    public function actionQuickView ($id) {
        echo Yii::t('app', 'Quick view not supported');
    }

    /**
     * Create shared calendar
     */
    public function actionCreate(){

        $model = new X2Calendar;

        $calendar = filter_input(INPUT_POST, 'X2Calendar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if(is_array($calendar)){
            $model->attributes = $calendar;
            //Added fix was that the remoteCalendarId can not be empty
            //This is because it can not attachSyncBehavior without it.
            if($model->remoteSync && (!empty($model->remoteCalendarId) || !empty($model->remoteCalOutlook))){
                $model->attachSyncBehavior();
                if(isset($_SESSION['token'])){
                    $credentials = $_SESSION['token'];
                    $model->credentials = $credentials;
                }if($model->syncType == 'google'){
                $model->remoteCalendarUrl = str_replace('{calendarId}', $model->remoteCalendarId, $model->syncBehavior->calendarUrl);
                }if($model->syncType == 'outlook'){
                $model->remoteCalendarUrl = str_replace('{calendarId}', $model->remoteCalOutlook, $model->syncBehavior->calendarUrl);    
                }
            }

            $model->createdBy = Yii::app()->user->name;
            $model->updatedBy = Yii::app()->user->name;
            $model->createDate = time();
            $model->lastUpdated = time();

            if ($model->save()) {
                $viewPermissions = filter_input(INPUT_POST, 'view-permission',
                        FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $editPermissions = filter_input(INPUT_POST, 'edit-permission',
                        FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $model->setCalendarPermissions($viewPermissions, $editPermissions);
                $this->redirect(array('index'));
            }
        }

        $admin = Yii::app()->settings;
        $googleIntegration = $admin->googleIntegration;
        $outlookIntegration = $admin->outlookIntegration;
        $hubCreds = Credentials::model()->findByPk($admin->hubCredentialsId);
        $hubCalendaring = false;
        if ($hubCreds && $hubCreds->auth)
            $hubCalendaring = $hubCreds->auth->enableGoogleCalendar;

        // if google integration is activated let user choose if they want to link this calendar to a google calendar
        if ($googleIntegration || $hubCalendaring) {
            list ($client, $googleCalendarList) = X2Calendar::getGoogleCalendarList();
        }else{
            $client = null;
            $googleCalendarList = null;
        }
        
        if ($outlookIntegration){
           list($clientOutlook, $outlookCalendarList) = X2Calendar::getOutlookCalendarList();
        }else{
           $clientOutlook = null;
           $outlookCalendarList = null;
        }
        $this->render('create', array(
            'model' => $model,
            'client' => $client,
            'clientOutlook' => $clientOutlook,
            'googleIntegration' => $googleIntegration,
            'outlookIntegration' => $outlookIntegration,
            'googleCalendarList' => $googleCalendarList,
            'outlookCalendarList' => $outlookCalendarList,
            'hubCalendaring' => $hubCalendaring,
        ));
    }

    /**
     * update calendar with id $id
     */
    public function actionUpdate($id){
        $model = $this->loadModel($id);
        if(Yii::app()->user->name === $model->createdBy 
                || Yii::app()->params->isAdmin 
                || in_array(Yii::app()->user->id, $model->getUserIdsWithEditPermission())){
            $calendar = filter_input(INPUT_POST, 'X2Calendar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if(is_array($calendar)){
                $oldAttributes = $model->attributes;
                $model->attributes = $calendar;
                if($model->remoteSync){
                    if(!$model->asa('syncBehavior')){
                        $model->attachSyncBehavior();
                    }
                    if(isset($_SESSION['token'])){
                        $credentials = $_SESSION['token'];
                        $model->credentials = $credentials;
                    }if($model->syncType == 'google'){
                        if($oldAttributes['remoteCalendarId'] !== $model->remoteCalendarId){
                            $model->deleteRemoteActions();
                            $model->remoteCalendarUrl = str_replace('{calendarId}', $model->remoteCalendarId, $model->syncBehavior->calendarUrl);
                            $model->ctag = null;
                            $model->syncToken = null;
                        }
                    }if($model->syncType == 'outlook'){    
                        if($oldAttributes['remoteCalOutlook'] !== $model->remoteCalOutlook){
                            $model->deleteRemoteActions();
                            $model->remoteCalendarUrl = str_replace('{calendarId}', $model->remoteCalOutlook, $model->syncBehavior->calendarUrl);
                            $model->ctag = null;
                            $model->syncToken = null;
                        }
                    }    
                }

                $model->updatedBy = Yii::app()->user->name;
                $model->lastUpdated = time();

                if ($model->save()) {
                    $viewPermissions = filter_input(INPUT_POST, 'view-permission',
                            FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    $editPermissions = filter_input(INPUT_POST, 'edit-permission',
                            FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    $model->setCalendarPermissions($viewPermissions, $editPermissions);
                    $this->redirect(array('index'));
                }
            }


            $admin = Yii::app()->settings;
            $googleIntegration = $admin->googleIntegration;
            $outlookIntegration = $admin->outlookIntegration;
            $hubCreds = Credentials::model()->findByPk($admin->hubCredentialsId);
            $hubCalendaring = false;
            if ($hubCreds && $hubCreds->auth)
                $hubCalendaring = $hubCreds->auth->enableGoogleCalendar;

            if ($googleIntegration || $hubCalendaring) {
                list ($client, $googleCalendarList) = X2Calendar::getGoogleCalendarList($id);
            }else{
                $client = null;
                $googleCalendarList = null;
            }
             if ($outlookIntegration){
                list($clientOutlook, $outlookCalendarList) = X2Calendar::getOutlookCalendarList();
            }else{
               $clientOutlook = null;
               $outlookCalendarList = null;
            }
            
            $this->render('update', array(
                'model' => $model,
                'client' => $client,
                'clientOutlook' => $clientOutlook,
                'googleIntegration' => $googleIntegration,
                'outlookIntegration' => $outlookIntegration,
                'googleCalendarList' => $googleCalendarList,
                'outlookCalendarList' => $outlookCalendarList,
                'hubCalendaring' => $hubCalendaring,
            ));
        }else{
            $this->denied();
        }
    }

    public function actionList(){
        $model = new X2Calendar('search');
        $this->render('index', array('model' => $model));
    }

    /**
     * Delete shared Calendar
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            $model = $this->loadModel($id);
            $model->delete();
            $this->redirect(array('index'));
        } else {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * @param string $calendarId username to fetch events for
     * @param int    $start unix time to for start of window
     * @param int    $end unix ending time
     */
    public function actionJsonFeed($calendarId, $start, $end){
        echo CJSON::encode($this->getFeed($calendarId, $start, $end));
    }

    public function formatActionToEvent($action, $id){
        if( !($action->visibility >= 1 || // don't show private actions,
                $action->assignedTo == Yii::app()->user->name ||  // unless they belong to current user
                Yii::app()->params->isAdmin) ) // admin sees all
            return false;


        $linked = !empty($action->associationType) && 
            strtolower($action->associationType) != 'none' && 
            class_exists(X2Model::getModelName($action->associationType));
        if ($linked) {
            $associatedModel = X2Model::getMOdelOfTypeWithId (
                X2Model::getModelName($action->associationType), $action->associationId);
            if ($associatedModel) {
                $associationUrl = $associatedModel->getUrl ();
            } else {
                $associationUrl = '';
            }
        }
        
        $title = $action->shortActionText;

        //Email formatting
        $title = preg_replace('/<b>/', '', $title);
        $title = preg_replace('/<\/b>/', '', $title);
        $title = preg_replace('/\n\n/', "\n", $title);
        $title = preg_replace('/<!--EndSig-->/', '', $title);
        $title = preg_replace('/<!--BeginOpenedEmail-->/', '', $title);
        $title = preg_replace('/<!--BeginSignature-->/', '', $title);

        if(in_array($action->type, array(
                'email', 'emailFrom', 'email_quote', 'email_invoice', 'emailOpened',
                'emailOpened_quote', 'emailOpened_invoice'))){
            $title = 'Email: '.$title;
        }

        $event = array(
            'title' => $title,
            'description' => $title,
            'start' => date('Y-m-d H:i', $action->dueDate),
            'id' => $action->id,
            'complete' => $action->complete,
            'calendarAssignment' => $id,
            'allDay' => false,
        );

        if($action->allDay)
            $event['allDay'] = $action->allDay;

        if($action->color) {
            $event['color'] = $action->color;
        } else {
            $event['color'] = '#6389de';
            // old default color
            //$event['color'] = '#3a87ad';
        }

        static $brightnesses = array ();
        if (!isset ($brightnesses[$event['color']])) {
            $brightnesses[$event['color']] = X2Color::getColorBrightness ($event['color']);
        }
        if ($brightnesses[$event['color']] < 115) {
            $event['textColor'] = 'white';
        }

        if($action->type == 'event'){
            if($action->completeDate)
                $event['end'] = date('Y-m-d H:i', $action->completeDate);

            $event['type'] = 'event';
            $event['associationType'] = $action->associationType;
        }

        $event['linked'] = $linked;
        if($linked){
            $event['associationType'] = $action->associationType;
            $event['associationUrl'] = $associationUrl;
            $event['associationName'] = $action->associationName;
        }
        
        $editable = X2CalendarPermissions::getEditableUserCalendarNames();
        // If it is a group id, we don't need to check this
        $userEditable = !is_int($id) && isset($editable[$id]);

        $event['editable'] = $userEditable &&
            Yii::app()->user->checkAccess('ActionsUpdate',array('X2Model'=>$action));
        
        return $event;

    }

    /**
     * Fetches events assigned to a user between two timestamps
     * @param string $calendarId username to fetch events for 
     * @param int $start UNIX timestamp for the beginning time 
     * @param int $end UNIX timestamp for the end time
     * @return array an array of fetched events
     */
    public function getFeed($calendarId, $start, $end){
        $calendar = X2Calendar::model()->findByPk($calendarId);
        if($calendar && $calendar->asa('syncBehavior')){
            $calendar->sync();
        }
        $actions = $this->calendarActions($calendarId,$start,$end);

        $events = array();
        foreach($actions as $action){
            $event = $this->formatActionToEvent($action, $calendarId);

            if($event)
                $events[] = $event;
        }

        return $events;
    }

    /**
     *    Ajax requests call this function, which returns a form filled with the event data.
     *  The form is then appended to a dialog in the users browser.
     */
    public function actionEditAction(){
        if(isset($_POST['ActionId'])){ // ensure we are getting sane post data
            $id = $_POST['ActionId'];
            $model = Actions::model()->with('invites')->findByPk($id);
            $isEvent = json_decode($_POST['IsEvent']);

            Yii::app()->clientScript->scriptMap['*.js'] = false;
            Yii::app()->clientScript->scriptMap['*.css'] = false;
            $this->renderPartial('editAction', array('model' => $model, 'isEvent' => $isEvent), false, true);
        }
    }

    /**
     *    Ajax requests call this function, which returns read only action data.
     *  The data is then appended to a dialog in the users browser.
     */
    public function actionViewAction(){
        if(isset($_POST['ActionId'])){ // ensure we are getting sane post data
            $id = $_POST['ActionId'];
            $model = Actions::model()->with('invites')->findByPk($id);
            $isEvent = json_decode($_POST['IsEvent']);

            Yii::app()->clientScript->scriptMap['*.js'] = false;
            Yii::app()->clientScript->scriptMap['*.css'] = false;
            $this->renderPartial(
                'viewAction',
                array(
                    'model' => $model,
                    'isEvent' => $isEvent
                ), false, true);
        }
    }

    // move the start time of an action
    // if the action has a complete date (or end date) it is also moved
    public function actionMoveAction(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];
            $dayDelta = $_POST['dayChange']; // +/-
            $minuteDelta = $_POST['minuteChange']; // +/-
            $allDay = $_POST['isAllDay'];

            $action = Actions::model()->findByPk($id);
            $action->allDay = (($allDay == 'true' || $allDay == 1) ? 1 : 0);
            $action->dueDate += ($dayDelta * 86400) + ($minuteDelta * 60);
            if($action->completeDate)
                $action->completeDate += ($dayDelta * 86400) + ($minuteDelta * 60);

            if($action->save()){
                $event = X2Model::model('Events')->findByAttributes(array('associationType' => 'Actions', 'associationId' => $action->id));
                if(isset($event)){
                    $event->timestamp = $action->dueDate;
                    $event->save();
                }
            }
        }
    }

    // move the end (or complete) time of an action
    // if the action doesn't have a
    public function actionResizeAction(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];
            $dayDelta = $_POST['dayChange']; // +/-
            $minuteDelta = $_POST['minuteChange']; // +/-

            $action = Actions::model()->findByPk($id);
            if($action->completeDate) // actions without complete date aren't updated
                $action->completeDate += ($dayDelta * 86400) + ($minuteDelta * 60);
            else if($action->type == 'event') // event without end date? give it one
                $action->completeDate = $action->dueDate + ($dayDelta * 86400) + ($minuteDelta * 60);

            $action->save();
        }
    }

    // make an action complete
    public function actionCompleteAction(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];

            $action = Actions::model()->findByPk($id);
            $action->complete = "Yes";
            $action->completedBy = Yii::app()->user->getName();
            $action->completeDate = time();
            $action->save();
        }
    }

    // make an action uncomplete
    public function actionUncompleteAction(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];

            $action = Actions::model()->findByPk($id);
            $action->complete = "No";
            $action->completedBy = null;
            $action->completeDate = null;
            $action->save();
        }
    }

    // delete an action from the database
    public function actionDeleteAction(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];
            $action = Actions::model()->findByPk($id);

            X2Model::model('Events')->deleteAllByAttributes(array('associationType' => 'Actions', 'type' => 'calendar_event', 'associationId' => $action->id));
            $action->delete();
        }
    }

    // check if user profile has a list to remember which calendars the user has checked
    // if not, create the list
    public function initCheckedCalendars(){
        $user = User::model()->findByPk(Yii::app()->user->getId());
        // calendar list not initialized?
        if($user->showCalendars == null)
            $user->initCheckedCalendars();
    }

    // if a user checked/unchecked a calendar, remember for the next to the user visits the page
    public function actionSaveCheckedCalendar(){
        if(isset($_POST['Calendar'])){
            $calendar = $_POST['Calendar'];
            $checked = $_POST['Checked'];
            $type = $_POST['Type'];
            $calendarType = $type.'Calendars';

            // get user list of checked calendars
            $user = User::model()->findByPk(Yii::app()->user->getId());
            $showCalendars = json_decode($user->showCalendars, true);

            if($checked)  // remember to show calendar
                if(!in_array($calendar, $showCalendars[$calendarType]))
                    $showCalendars[$calendarType][] = $calendar;
                else // stop remembering to show calendar
                if(($key = array_search($calendar, $showCalendars[$calendarType])) !== false) // find calendar in list of shown calendars
                    unset($showCalendars[$calendarType][$key]);

            /**/print_r($showCalendars);
            $user->showCalendars = CJSON::encode($showCalendars);
            $user->save();
        }
    }

    public function actionToggleUserCalendarsVisible(){
        echo Yii::app()->params->profile->userCalendarsVisible;
    }

    public function actionTogglePortletVisible($portlet){
        $parameterName = $portlet."Visible";
        if(isset(Yii::app()->params->profile->$parameterName)){
            $visible = Yii::app()->params->profile->$parameterName;
            $visible = !$visible;
            Yii::app()->params->profile->$parameterName = $visible;
            Yii::app()->params->profile->save();
            echo $visible;
        }else{
            echo 1; // if portlet not found, just make it visible
        }
    }
    
    public function actionEventRsvp($email, $inviteKey) {
        $this->layout = '//layouts/column1';
        $invite = X2Model::model('CalendarInvites')->findByAttributes(array(
            'email' => $email,
            'inviteKey' => $inviteKey,
        ));
        if (!$invite) {
            $this->denied();
        }
        $action = X2Model::model('Actions')->findByPk($invite->actionId);
        $status = filter_input(INPUT_POST, 'status');
        if (!is_null($status)) {
            $contact = X2Model::model('Contacts')->findByEmail($email);
            if ($contact && $contact->asa('MappableBehavior')) {
                $contact->logLocation('eventRSVP', 'POST');
            }
            $user = X2Model::model('User')->findByAttributes(array('emailAddress' => $email));
            if ($user && $user->asa('MappableBehavior')) {
                $user->logLocation('eventRSVP', 'POST');
            }
            $invite->status = $status;
            $invite->save();
            Yii::app()->end();
        }
        $this->render('eventRsvp', array('invite' => $invite, 'action' => $action));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id){
        $model = X2Calendar::model()->findByPk((int) $id);
        if($model === null)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }

    /**
     * Retrieve calendar events for a given user happening between two specified
     * dates.
     * @param string|integer $calendarUser Username or group ID whose calendar
     *  events are to be loaded and returned
     * @param type $start Beginning time range
     * @param type $end End time range
     * @param mixed $includePublic Set to 1 or boolean true to include all
     *  calendar events 
     * @return array An array of action records
     */
    public function calendarActions($calendarUser, $start, $end){
        $staticAction = Actions::model();
        // View permissions for the viewing user
        $criteria = $staticAction->getAccessCriteria();
        // Assignment condition: all events for the user whose calendar is being viewed:
        $criteria->addCondition('`calendarId` = :calendarId');
        $criteria->addCondition("`type` IS NULL OR `type`='' OR `type`='event'");
        $criteria->addCondition('(`dueDate` >= :start1 AND `dueDate` <= :end1) '
                .'OR (`completeDate` >= :start2 AND `completeDate` <= :end2)');
        $criteria->params = array_merge($criteria->params, array(
            ':start1' => $start,
            ':start2' => $start,
            ':end1' => $end,
            ':end2' => $end,
            ':calendarId' => $calendarUser
        ));
        return Actions::model()->findAllWithoutActionText($criteria);
    }

    /**
     * Getter function for {@link $currentUser}
     * @return type
     */
    public function getCurrentUser() {
        if(!isset($this->_currentUser)) {
            $this->_currentUser = User::model()->findByPk(Yii::app()->user->id);
        }
        return $this->_currentUser;
    }

    /**
     * Create a menu for the Calendar
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Calendar = Modules::displayName();
        $Actions = Modules::displayName(true, "Actions");
        $User = Modules::displayName(false, "Users");
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'myPermissions', 'userPermissions', 'sync',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('calendar', '{calendar}', array('{calendar}'=>$Calendar)),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('calendar', 'Create {calendar}', array(
                    '{calendar}'=>$Calendar,
                )),
                'url'=>array('create')
            ),
            array(
                'name'=>'update',
                'label'=>Yii::t('calendar', 'Update {calendar}', array(
                    '{calendar}'=>$Calendar,
                )),
                'url'=>array('update', 'id'=>$modelId),
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('calendar', 'Delete {calendar}', array(
                    '{calendar}'=>$Calendar,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?'
                )
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
