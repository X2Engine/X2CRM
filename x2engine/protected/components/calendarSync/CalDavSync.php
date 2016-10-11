<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

Yii::setPathOfAlias(
        'Sabre', Yii::getPathOfAlias('application.integration.SabreDAV'));

abstract class CalDavSync extends CalendarSync {

    public static $xmlProperties = array(
        'ctag' => '{http://calendarserver.org/ns/}getctag',
        'etag' => '{DAV:}getetag',
        'calendarColor' => '{http://apple.com/ns/ical/}calendar-color',
        'displayName' => '{DAV:}displayname',
        'calendarData' => '{urn:ietf:params:xml:ns:caldav}calendar-data',
        'syncToken' => '{DAV:}sync-token',
    );
    protected $_client;

    /**
     * Must return an array with 3 key/value pairs:
     * oAuthToken, username, password. Either oAuthToken or
     * username and password must not be null.
     */
    protected abstract function authenticate();

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

    public function getClient() {
        if (isset($this->_client)) {
            return $this->_client;
        }
        return $this->initializeClient();
    }

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
    
    public function deleteRemoteActions(){
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

    protected function syncWithToken() {
        $syncResult = $this->client->sync($this->owner->remoteCalendarUrl, $this->owner->syncToken);
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
                    $action->delete();
                }
            }
        }
        $multigetResult = $this->client->multiget($this->owner->remoteCalendarUrl, $paths, array(
            self::$xmlProperties['etag'],
            self::$xmlProperties['calendarData'],
        ));
        $this->createUpdateActions($multigetResult);
    }

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
                if ($action->etag !== $eventEtag) {
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

    protected function createAction($calObject, $params = array()) {
        $action = new Actions();
        $action->etag = $params['etag'];
        $action->remoteCalendarUrl = $params['remoteCalendarUrl'];
        $this->setActionAttributes($action, $calObject);
        $action->save();
    }

    protected function updateAction($action, $calObject, $params = array()) {
        $action->etag = $params['etag'];
        $this->setActionAttributes($action, $calObject);
        $action->save();
    }

    protected function setActionAttributes(&$action, $calObject) {
        $action->actionDescription = $calObject->vevent->summary->value;
        if (!empty($calObject->vevent->description->value)) {
            if (!empty($calObject->vevent->summary->value)) {
                $action->actionDescription .= "\n" . $calObject->vevent->description->value;
            } else {
                $action->actionDescription = $calObject->vevent->description->value;
            }
        }
        // NEED TO HANDLE RECURRING ACTIONS STILL
        $action->visibility = 1;
        $action->assignedTo = 'Anyone';
        $action->calendarId = $this->owner->id;
        $action->associationType = 'calendar';
        $action->associationName = 'Calendar';
        $action->type = 'event';
        $action->remoteSource = 1;
        if ($calObject->vevent->dtstart->getDateType() === 4) {
            $action->dueDate = strtotime($calObject->vevent->dtstart->value);
            // Subtract 1s to fix all day display issue in Calendar
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

    public function syncActionToCalendar($action) {
        if (empty($action->remoteCalendarUrl)) {
            $calObject = $this->createCalObject($action);
        } else {
            $calObject = $this->updateCalObject($action);
        }
    }

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
