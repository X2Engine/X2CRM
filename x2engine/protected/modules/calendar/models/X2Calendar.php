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



/**
 * @package application.modules.calendar.models
 *
 * @deprecated
 * @todo Find out if this class is still actually used for anything. Delete it
 *  and anything else associated with it (i.e. the database table) if not.
 */
Yii::import('application.components.calendarSync.*');

class X2Calendar extends CActiveRecord
{
        public $outlookCalenarName;
	public $googleCalendarName;
        public $defaultCalendar;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Contacts the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_calendars';
	}
        
        public function rules(){
            return array(
                array('name','required'),
                array('remoteSync','boolean'),
                array('name','length', 'max' => 100),
                array('syncType, remoteCalendarId, remoteCalOutlook, remoteCalendarUrl, ctag, credentials','safe')
            );
        }
        
        public function behaviors() {
            $behaviors = array();
            if (!empty($this->syncType) && (!empty($this->remoteCalendarId) || !empty($this->remoteCalOutlook))) {
                if ($this->syncType == 'google') {
                    $behaviors['syncBehavior'] = 'GoogleCalendarSync';
                }
                if ($this->syncType == 'outlook') {
                    $behaviors['syncBehavior'] = 'OutlookCalendarSync';
                }
            }
            return array_merge($behaviors, parent::behaviors());
        }
        
        public function attachSyncBehavior(){
            if (!empty($this->syncType) && (!empty($this->remoteCalendarId) || !empty($this->remoteCalOutlook))) {
                if ($this->syncType == 'google') {
                    $this->attachBehavior('syncBehavior','GoogleCalendarSync');
                }
                if ($this->syncType == 'outlook') {
                    $this->attachBehavior('syncBehavior','OutlookCalendarSync');
                }
            }
        }
	
	public function attributeLabels() {
		return array(
                    'id' => Yii::t('admin','ID'),
                    'remoteCalendarId' => Yii::t('calendar','Remote Calendar Name'),
                    'remoteCalOutlook' => Yii::t('calendar','Remote Calendar Name'),
		);
	}
        
	public static function getNames() {
		$calendars = X2Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
		$names = array();
		foreach($calendars as $calendar)
			$names["{$calendar->id}"] = $calendar->name;
		
		return $names;
	}
     
        /*
        * Get All the Calenders from the user
        */
       public static function getOutlookCalendarList($id = null) {

           $client = new OutlookAuthenticator('calendar');
           $access_token = $client->getAccessToken();

           if($client->getAccessToken()){
                $access = "Bearer " . $access_token;

                $ch = curl_init();
                //create header and body for the POST request
                curl_setopt($ch, CURLOPT_URL,"https://graph.microsoft.com/v1.0/me/calendars");
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:' .$access, 
                                                           'Content-Type: application/json'));

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                //execute url
                $server_output = curl_exec($ch);
                curl_close ($ch);

                $temp = CJSON::decode($server_output);
                $calendarlist = $temp['value'];
                $calendars = array();

                foreach( $calendarlist as $object ){
                   foreach( $object as $key => $value ){
                       if($key == 'id'){
                       $id = $value;    
                       }
                       if($key == 'name'){
                       $tempCalendar = array( (string)$id => (string)$value );
                       $calendars = array_merge($calendars, $tempCalendar);
                       }
                   } 
                }
           }else{
               $calendars = null;
           }

           return array($client, $calendars);
       }
        
        /**
         * Returns a list of calendars that can be synced to
         */
        public static function getGoogleCalendarList($id = null){
            $client = new GoogleAuthenticator('calendar');
            try {
                if ($client->getAccessToken()) {
                    $googleCalendar = $client->getCalendarService();
                    try {
                        $calList = $googleCalendar->calendarList->listCalendarList();
                        $syncedCalendarCmd = Yii::app()->db->createCommand()
                                ->selectDistinct('remoteCalendarId')
                                ->from('x2_calendars');
                        if(!is_null($id)){
                            $syncedCalendarCmd->where('id != :id AND remoteCalendarId IS NOT NULL',array(':id'=>$id));
                        }else{
                            $syncedCalendarCmd->where('remoteCalendarId IS NOT NULL');
                        }
                        $syncedCalendars = $syncedCalendarCmd->queryColumn();
                        $googleCalendarList = array();
                        foreach ($calList['items'] as $cal) {
                            if(!in_array($cal['id'], $syncedCalendars)){
                                $googleCalendarList[$cal['id']] = $cal['summary'];
                            }
                        }
                    } catch (Google_Service_Exception $e) {
                        if ($e->getCode() == '403') {
                            $errors[] = 'Google Calendar API access has not been configured.';
                            Yii::app()->user->setFlash(
                                    'error', 'Google Calendar API access has not been configured.');
                            $googleCalendarList = null;
                            //$auth->flushCredentials();
                        } elseif ($e->getCode() == '401') {
                            $errors[] = 'Invalid user credentials provided. Please try again.';
                            Yii::app()->user->setFlash(
                                    'error', 'Invalid user credentials. Please ensure your account is ' .
                                    'able to use this service or delete the access permissions ' .
                                    'and try again.');
                            $googleCalendarList = null;
                            $client->flushCredentials();
                        }
                    }
                }else{
                    $googleCalendarList = null;
                }
            } catch (Google_Auth_Exception $e) {
                $client->flushCredentials();
                $client->setErrors($e->getMessage());
                $client = null;
                $googleCalendarList = null;
            }
            return array($client, $googleCalendarList);
        }
        
	/**
     * Getter for the possible actions used by the calendar
     * @return array Array of constructed URLS
     */ 
    public static function getCalendarUrls(){
        $urls = array(
            'jsonFeed' => Yii::app()->createUrl('/calendar/jsonFeed'), // feed to get actions from users
            'jsonFeedGroup' => Yii::app()->createUrl('/calendar/jsonFeedGroup'), // feed to get actions from group Calendar
            'jsonFeedShared' => Yii::app()->createUrl('/calendar/jsonFeedShared'), // feed to get actions from shared calendars
            'currentUserFeed' => Yii::app()->createUrl('/calendar/jsonFeed', array('user' => Yii::app()->user->name)), // add current user actions to calendar
            'anyoneUserFeed' => Yii::app()->createUrl('/calendar/jsonFeed', array('user' => 'Anyone')), // add Anyone actions to calendar
            'moveAction' => Yii::app()->createUrl('/calendar/moveAction'),
            'resizeAction' => Yii::app()->createUrl('/calendar/resizeAction'),
            'viewAction' => Yii::app()->createUrl('/calendar/viewAction'),
            'saveAction' => Yii::app()->createUrl('/actions/actions/quickUpdate'),
            'editAction' => Yii::app()->createUrl('/calendar/editAction'),
            'completeAction' => Yii::app()->createUrl('/calendar/completeAction'),
            'uncompleteAction' => Yii::app()->createUrl('/calendar/uncompleteAction'),
            'deleteAction' => Yii::app()->createUrl('/calendar/deleteAction'),
            'saveCheckedCalendar' => Yii::app()->createUrl('/calendar/saveCheckedCalendar'),
            'saveCheckedCalendarFilter' => Yii::app()->createUrl('/calendar/saveCheckedCalendarFilter'),
            'index' => Yii::app()->createUrl('/calendar/index')
        );
        
        return $urls;
    
    }

    public static function translationArray($key, $encode = true){
    	if ($key == 'buttonText'){
	    	$array = array( // translate buttons
	    	    'today' => Yii::t('calendar', 'Today'),
	    	    'month' => Yii::t('calendar', 'Month'),
	    	    'agendaWeek' => Yii::t('calendar', 'Week'),
	    	    'day' => Yii::t('calendar', 'Day'),
	    	);
    	}
    	else if ($key == 'monthNames'){
	    	$array =  array( // translate month names
	    	    Yii::t('calendar', 'January'),
	    	    Yii::t('calendar', 'February'),
	    	    Yii::t('calendar', 'March'),
	    	    Yii::t('calendar', 'April'),
	    	    Yii::t('calendar', 'May'),
	    	    Yii::t('calendar', 'June'),
	    	    Yii::t('calendar', 'July'),
	    	    Yii::t('calendar', 'August'),
	    	    Yii::t('calendar', 'September'),
	    	    Yii::t('calendar', 'October'),
	    	    Yii::t('calendar', 'November'),
	    	    Yii::t('calendar', 'December'),
	    	);
	    }
    	else if ($key == 'monthNamesShort'){
	    	$array =  array( // translate short month names
	    	    Yii::t('calendar', 'Jan'),
	    	    Yii::t('calendar', 'Feb'),
	    	    Yii::t('calendar', 'Mar'),
	    	    Yii::t('calendar', 'Apr'),
	    	    Yii::t('calendar', 'May'),
	    	    Yii::t('calendar', 'Jun'),
	    	    Yii::t('calendar', 'Jul'),
	    	    Yii::t('calendar', 'Aug'),
	    	    Yii::t('calendar', 'Sep'),
	    	    Yii::t('calendar', 'Oct'),
	    	    Yii::t('calendar', 'Nov'),
	    	    Yii::t('calendar', 'Dec'),
	    	);
	    }
    	else if ($key == 'dayNames'){
	    	$array =  array( // translate day names
	    	    Yii::t('calendar', 'Sunday'),
	    	    Yii::t('calendar', 'Monday'),
	    	    Yii::t('calendar', 'Tuesday'),
	    	    Yii::t('calendar', 'Wednesday'),
	    	    Yii::t('calendar', 'Thursday'),
	    	    Yii::t('calendar', 'Friday'),
	    	    Yii::t('calendar', 'Saturday'),
	    	);
	    }
    	if ($key == 'dayNamesShort'){
	    	$array =  array( // translate short day names
	    	    Yii::t('calendar', 'Sun'),
	    	    Yii::t('calendar', 'Mon'),
	    	    Yii::t('calendar', 'Tue'),
	    	    Yii::t('calendar', 'Wed'),
	    	    Yii::t('calendar', 'Thu'),
	    	    Yii::t('calendar', 'Fri'),
	    	    Yii::t('calendar', 'Sat'),	
	    	);
	    }

	    if( $encode )
	    	return CJSON::encode($array);
	    else
	    	return $array;

    }
    
    protected function beforeDelete() {
        if($this->asa('syncBehavior')){
            $this->deleteRemoteActions();
        }
        return parent::beforeDelete();
    }
	
        public function setCalendarPermissions($view, $edit){
            $permissions = array();
            if (is_array($view)) {
                foreach ($view as $userId) {
                    $permissions[$userId] = array('view' => 1, 'edit' => 0);
                }
            }
            if (is_array($edit)) {
                foreach ($edit as $userId) {
                    $permissions[$userId] = array('view' => 1, 'edit' => 1);
                }
            }
            X2CalendarPermissions::model()->deleteAllByAttributes(array('calendarId'=>$this->id));
            foreach ($permissions as $userId => $perms) {
                $permissionRecord = new X2CalendarPermissions();
                $permissionRecord->calendarId = $this->id;
                $permissionRecord->userId = $userId;
                $permissionRecord->view = $perms['view'];
                $permissionRecord->edit = $perms['edit'];
                $permissionRecord->save();
            }
        }
        
        public function getUserIdsWithViewPermission(){
            return Yii::app()->db->createCommand()
                    ->select('userId')
                    ->from('x2_calendar_permissions')
                    ->where('view = 1 AND calendarId = :calendarId',array(':calendarId'=>$this->id))
                    ->queryColumn();
        }
        
        public function getUserIdsWithEditPermission(){
            return Yii::app()->db->createCommand()
                    ->select('userId')
                    ->from('x2_calendar_permissions')
                    ->where('edit = 1 AND calendarId = :calendarId',array(':calendarId'=>$this->id))
                    ->queryColumn();
        }

	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
		if(!Yii::app()->user->checkAccess('CalendarAdminAccess')) // if not admin
			$criteria->condition = "createdBy='". Yii::app()->user->name . "'"; // user can only edit shared calendar they have created
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}
	
	public function searchBase(
        $criteria, $pageSize=null, $showHidden='false') {

		$criteria->compare('name',$this->name,true);
		
		return new SmartActiveDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>Profile::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

    public function getDisplayName ($plural=true, $ofModule=true) {
        return Yii::t('calendar', 'Calendar');
    }
	
}
