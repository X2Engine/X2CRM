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




Yii::setPathOfAlias(
        'Sabre', Yii::getPathOfAlias('application.integration.SabreDAV'));

/**
 * Abstract class for all CalDav based sync protocols. Authenticate must be
 * implemented in sublcasses to properly synchronize. Supported methods are
 * oAuthToken and username/password. See GoogleCalendarSync for an implementation.
 */
abstract class CalDavSync extends CalendarSync {

    /**
     * A list of properties which can be retrieved from a CalDav object and their
     * corresponding XML identifiers in the response objects.
     * @var array
     */
    public static $xmlProperties = array(
        'ctag' => '{http://calendarserver.org/ns/}getctag',
        'etag' => '{DAV:}getetag',
        'calendarColor' => '{http://apple.com/ns/ical/}calendar-color',
        'displayName' => '{DAV:}displayname',
        'calendarData' => '{urn:ietf:params:xml:ns:caldav}calendar-data',
        'syncToken' => '{DAV:}sync-token',
    );
    
    /**
     * An instance of X2CalDavClient to handle CalDav protocol requests
     * @var X2CalDavClient 
     */
    protected $_client;

    /**
     * Must return an array with 3 key/value pairs:
     * oAuthToken, username, password. Either oAuthToken or
     * username and password must not be null.
     */
    protected abstract function authenticate();

    /**
     * Initializes and returns a CalDav client configured with the correct authentication
     * parameters.
     * @return X2CalDavClient
     * @throws CException
     */
    protected function initializeClient() {
        $credentials = $this->authenticate();
        if (empty($credentials['oAuthToken']) && (empty($credentials['username']) || empty($credentials['password']))) {
            throw new CException('An OAuthToken or username/password combination must be provided.', 400);
        }
        $this->_client = new X2CalDavClient(array(
            'baseUri' => Yii::app()->settings->externalBaseUrl . Yii::app()->settings->externalBaseUri,
            'oAuthToken' => $credentials['oAuthToken'],
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ));
        return $this->_client;
    }

    /**
     * Returns or initializes and returns a new client
     * @return X2CalDavClient
     */
    public function getClient() {
        if (isset($this->_client)) {
            return $this->_client;
        }
        return $this->initializeClient();
    }

    /**
     * Handles synchronization of X2 and CalDav calendars. If an X2Calendar does
     * not yet have a ctag (it has never before been synced) an outbount sync is
     * performed to transfer X2 calendar events into the CalDav server. If a
     * sync token has been provided, it will use that instead of attempting a
     * full sync.
     */
    public function sync() {
        if(is_null($this->owner->ctag)){
            $this->outboundSync();
        }
        $calData = $this->getSyncInfo();
        if ($calData['ctag'] != $this->owner->ctag) {
            if (isset($this->owner->syncToken)) {
                $this->syncWithToken();
            } else {
                $this->syncWithoutToken();
            }
            $calData = $this->getSyncInfo();
            $this->owner->syncToken = $calData['syncToken'];
            $this->owner->ctag = $calData['ctag'];
            $this->owner->save();
        }
    }
    
    /**
     * Deletes all remote calendar events which were created from a sync from X2
     */
    public function deleteRemoteActions(){
        // Returns a list of IDs of Actions which were not created from an inbound
        // sync but are associated with this calendar
        $actionIds = Yii::app()->db->createCommand()
                ->select('a.id')
                ->from('x2_actions a')
                ->leftJoin('x2_action_meta_data b', 'a.id = b.actionId')
                ->where('(b.remoteSource = 0 OR b.remoteSource IS NULL) AND a.calendarId = :calendarId', array(':calendarId'=>$this->owner->id))
                ->queryColumn();
        $actions = X2Model::model('Actions')->findAllByPk($actionIds);
        foreach($actions as $action){
            $this->deleteAction($action);
        }
    }

    /**
     * Retrieves a set of calendar event paths from a given sync token and synchronizes
     * them with an X2 Calendar
     */
    protected function syncWithToken() {
        
        for($I = 0; $I < 13; $I++){
            $time = $I;
            if($time == 12){
                $time = NULL;
        }

            $syncResult = $this->client->sync($this->owner->remoteCalendarUrl, $this->owner->syncToken, $time);
            $paths = array();
            foreach ($syncResult as $syncPath => $eventData) {
                $keys = array_keys($eventData);
                $statusCode = $keys[0];
                if ($statusCode == '200') {
                    $paths[] = $syncPath;
                } elseif ($statusCode == '404') {
                    $pieces = explode('/',$syncPath);
                    $actionUrl = str_replace('.ics', '', urldecode($pieces[count($pieces)-1]));
                    $actionMetaData = X2Model::model('ActionMetaData')->findByAttributes(array(
                        'remoteCalendarUrl' => $actionUrl,
                    ));
                    if (isset($actionMetaData)) {
                        $action = X2Model::model('Actions')->findByPk($actionMetaData->actionId);
                        if ($action)
                            $action->delete();
                    }
                }
            }
            // Gets all calendar events which were created / updated since the last sync
            $multigetResult = $this->client->multiget($this->owner->remoteCalendarUrl, $paths, array(
                self::$xmlProperties['etag'],
                self::$xmlProperties['calendarData'],
            ));
            $this->createUpdateActions($multigetResult);
        }
    }

    /**
     * Performs a full synchronization in the event that there is no token to
     * calculate a diff from. This function is called after outboundSync to ensure
     * no Actions which were already in X2 are deleted
     */
    protected function syncWithoutToken() {
        $calendarEvents = $this->client->report($this->owner->remoteCalendarUrl, array(
            self::$xmlProperties['etag'],
            self::$xmlProperties['calendarData'],
        ));
        
        $paths = $this->createUpdateActions($calendarEvents, true);
        $pathList = AuxLib::bindArray($paths);
        $bindParams = array(':calId' => $this->owner->id);
        $deletedActionCmd = Yii::app()->db->createCommand()
                ->select('a.id')
                ->from('x2_actions a')
                ->join('x2_action_meta_data b', 'a.id = b.actionId');
        if(!empty($pathList)){
            $bindParams = array_merge(
                $bindParams, $pathList);
            $deletedActionCmd->where('a.calendarId = :calId AND b.remoteCalendarUrl NOT IN ' . AuxLib::arrToStrList(array_keys($pathList)), $bindParams);
        }else{
            $deletedActionCmd->where('a.calendarId = :calId', $bindParams);
        }
        $deletedActions = $deletedActionCmd->queryColumn();
        if(!empty($deletedActions)){
            $actionIdParams = AuxLib::bindArray($deletedActions);
            $reminderIds = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_events')
                    ->where('associationType = "Actions" AND associationId IN '. AuxLib::arrToStrList($actionIdParams). ' AND type = "action_reminder"')
                    ->queryColumn();
            X2Model::model('Events')->deleteByPk($reminderIds);
            X2Model::model('Actions')->deleteByPk($deletedActions);
        }
    }

    /**
     * Retrieves up to date information about the status of a remote calendar,
     * including the ctag and syncToken which can be used to determine if any
     * changes have been made and generate a diff of those changes
     * @return array
     */
    protected function getSyncInfo() {
        $calendarInfo = $this->client->propFind($this->owner->remoteCalendarUrl, array(
            self::$xmlProperties['displayName'],
            self::$xmlProperties['ctag'],
            self::$xmlProperties['syncToken'],
        ));
        return array(
            'ctag' => $calendarInfo[self::$xmlProperties['ctag']],
            'syncToken' => $calendarInfo[self::$xmlProperties['syncToken']]
        );
    }
    
    /**
     * Get a list of Actions associated with this calendar but with no remote
     * source and create them in the remote calendar
     */
    protected function outboundSync(){
        $actionIds = Yii::app()->db->createCommand()
                ->select('a.id')
                ->from('x2_actions a')
                ->leftJoin('x2_action_meta_data b', 'a.id = b.actionId')
                ->where('(b.remoteSource = 0 OR b.remoteSource IS NULL) AND a.calendarId = :calendarId', array(':calendarId'=>$this->owner->id))
                ->queryColumn();
        $actions = X2Model::model('Actions')->findAllByPk($actionIds);
        foreach($actions as $action){
            if(!is_null($action->remoteCalendarUrl)){
                $action->remoteCalendarUrl = null;
            }
            $this->syncActionToCalendar($action);
        }
    }

    /**
     * Creates or updates Actions in X2 from remote calendar event data
     * @param array $calendarData XML data of remote calendar events
     * @param boolean $return Whether or not to return the paths
     * @return array A list of paths of created/updated events
     */
    protected function createUpdateActions($calendarData, $return = false) {
        if ($return) {
            $paths = array();
        }
        foreach ($calendarData as $event) {
            
            $eventEtag = $event[self::$xmlProperties['etag']];
            $eventVObj = Sabre\VObject\Reader::read($event[self::$xmlProperties['calendarData']]);
            $actionMetaData = X2Model::model('ActionMetaData')->findByAttributes(array('remoteCalendarUrl' => $eventVObj->vevent->uid->value));
            if ($return) {
                $paths[] = $eventVObj->vevent->uid->value;
            }
            if (isset($actionMetaData)) {
                $action = X2Model::model('Actions')->findByPk($actionMetaData->actionId);
                if ($action && $action->etag !== $eventEtag) {
                    $this->updateAction($action, $eventVObj, array(
                        'etag' => $eventEtag,
                    ));
                }
            } else {
                $this->createAction($eventVObj, array(
                    'etag' => $eventEtag,
                    'remoteCalendarUrl' => $eventVObj->vevent->uid->value,
                ));
            }
        }
        if ($return) {
            return $paths;
        }
    }

    /**
     * Creates an Action from a SabreDav VEvent
     */
    protected function createAction($calObject, $params = array()) {
        $action = new Actions();
        $action->etag = $params['etag'];
        $action->remoteCalendarUrl = $params['remoteCalendarUrl'];
        $this->setActionAttributes($action, $calObject);
        $action->save();
    }

    /**
     * Updates an Action from a SabreDav VEvent
     */
    protected function updateAction($action, $calObject, $params = array()) {
        $action->etag = $params['etag'];
        $this->setActionAttributes($action, $calObject);
        $action->save();
    }

    /**
     * Converts a SabreDav VEvent object's attributes into X2 friendly attributes
     * and sets the provided Action's attributes to the processed data.
     * 
     * TODO: Handle recurring events
     */
    protected function setActionAttributes(&$action, $calObject) {
        $action->actionDescription = $calObject->vevent->summary->value;
        if (!empty($calObject->vevent->description->value)) {
            if (!empty($calObject->vevent->summary->value)) {
                $action->actionDescription .= "\n" . $calObject->vevent->description->value;
            } else {
                $action->actionDescription = $calObject->vevent->description->value;
            }
        }
        $action->visibility = 1;
        $action->assignedTo = 'Anyone';
        $action->calendarId = $this->owner->id;
        $action->associationType = 'calendar';
        $action->associationName = 'Calendar';
        $action->type = 'event';
        $action->remoteSource = 1;
        if ($calObject->vevent->dtstart->getDateType() === 4) { // All day event
            $action->dueDate = strtotime($calObject->vevent->dtstart->value);
            // Subtract 1 second to fix all day display issue in Calendar
            $action->completeDate = strtotime($calObject->vevent->dtend->value) - 1;
            $action->allDay = 1;
        } else {
            $timezone = new \DateTimeZone('UTC');
            if(isset($calObject->vtimezone)){
                $timezone = new \DateTimeZone($calObject->vtimezone->tzid->value);
            }
            $startTime = new \DateTime($calObject->vevent->dtstart->value, $timezone);
            if(is_null($calObject->vevent->dtend)){
                $endTime = $startTime;
            } else {
                $endTime = new \DateTime($calObject->vevent->dtend->value, $timezone);
            }
            $action->dueDate = $startTime->getTimestamp();
            $action->completeDate = $endTime->getTimestamp();
        }
    }

    /**
     * Attempt to delete a remote calendar event associated with a given Action
     */
    public function deleteAction($action) {
        try{
            if(isset($action->remoteCalendarUrl, $action->etag)){
                $this->client->delete(
                        $this->owner->remoteCalendarUrl, '/' . $action->remoteCalendarUrl . '.ics', $action->etag
                );
            }
        } catch (Exception $e){
            
        }
    }

    /**
     * Either create or update a remote calendar event associated with an Action
     */
    public function syncActionToCalendar($action) {
        if (empty($action->remoteCalendarUrl)) {
            $calObject = $this->createCalObject($action);
        } else {
            $calObject = $this->updateCalObject($action);
        }
    }

    /**
     * Creates a VEvent object from an Action and sends it to a remote CalDav server
     */
    protected function createCalObject($action) {
        $calObj = new Sabre\VObject\Component\VCalendar();
        $vevent = new Sabre\VObject\Component\VEvent('VEVENT');
        $this->setEventAttributes($vevent, $action);
        $uniqueId = UUID::v4();
        $vevent->add('UID', $uniqueId);
        $calObj->add($vevent);

        if ($this->client->put($this->owner->remoteCalendarUrl, '/' . $uniqueId . '.ics', $calObj->serialize())) {
            $newEventData = $this->client->get($this->owner->remoteCalendarUrl, '/' . $uniqueId . '.ics');
            $metaData = ActionMetaData::model()->findByAttributes(array('actionId' => $action->id));
            if (!isset($metaData)) {
                $metaData = new ActionMetaData();
                $metaData->actionId = $action->id;
            }
            $metaData->etag = $newEventData['etag'];
            $metaData->remoteCalendarUrl = $uniqueId;
            $metaData->save();
        }
    }

    /**
     * Updates a VEvent object associated with an Action and sends it to a remote CalDav server
     */
    protected function updateCalObject($action) {
        $eventData = $this->client->get($this->owner->remoteCalendarUrl, '/' . $action->remoteCalendarUrl . '.ics');
        $calObj = Sabre\VObject\Reader::read($eventData['body']);
        $this->setEventAttributes($calObj->vevent, $action);
        if ($this->client->put($this->owner->remoteCalendarUrl, '/' . $action->remoteCalendarUrl . '.ics', $calObj->serialize(), $action->etag)) {
            $newEventData = $this->client->get($this->owner->remoteCalendarUrl, '/' . $action->remoteCalendarUrl . '.ics');
            $metaData = ActionMetaData::model()->findByAttributes(array('actionId' => $action->id));
            $metaData->etag = $newEventData['etag'];
            $metaData->save();
        }
    }

    /**
     * Converts an Action's attributes to CalDav friendly attributes and modifies
     * the provided VEvent with them
     */
    protected function setEventAttributes(&$vevent, $action) {

        $startTime = new Sabre\VObject\Property\DateTime('DTSTART');
        $startDateTime = new \DateTime('@' . $action->dueDate);
        $startTime->setDateTime($startDateTime);
        $endTime = new Sabre\VObject\Property\DateTime('DTEND');
        if(empty($action->completeDate)){
            $action->completeDate = $action->dueDate;
        }
        $endDateTime = new \DateTime('@' . $action->completeDate);
        $endTime->setDateTime($endDateTime);
        $vevent->dtstart = $startTime;
        $vevent->dtend = $endTime;
        $vevent->summary = $action->actionDescription;

        return $vevent;
    }

}
