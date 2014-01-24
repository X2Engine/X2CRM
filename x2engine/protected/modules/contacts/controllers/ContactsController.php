<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * @package X2CRM.modules.contacts.controllers
 */
class ContactsController extends x2base {

    public $modelClass = 'Contacts';

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * No longer actually called since the permissions system changes
     * @return array access control rules
     * @deprecated
     */
    public function accessRules(){

        return array(
            array('allow',
                'actions' => array('getItems', 'getLists', 'ignoreDuplicates', 'discardNew',
                    'weblead', 'weblist'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array(
                    'index',
                    'list',
                    'lists',
                    'view',
                    'myContacts',
                    'newContacts',
                    'update',
                    'create',
                    'quickContact',
                    'import',
                    'importContacts',
                    'viewNotes',
                    'search',
                    'addNote',
                    'deleteNote',
                    'saveChanges',
                    'createAction',
                    'importExcel',
                    'export',
                    'getTerms',
                    'getContacts',
                    'delete',
                    'shareContact',
                    'viewRelationships',
                    'createList',
                    'createListFromSelection',
                    'updateList',
                    'addToList',
                    'removeFromList',
                    'deleteList',
                    'exportList',
                    'inlineEmail',
                    'quickUpdateHistory',
                    'subscribe',
                    'qtip',
                    'cleanFailedLeads',
                ),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array(
                    'admin', 'testScalability'
                ),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Return a list of external actions which need to be included.
     * @return array A merge of the parent class's imported actions in addition to the ones that are specific to the Contacts controller
     */
    public function actions(){
        return array_merge(parent::actions(), array(
            'weblead' => array(
                'class' => 'WebFormAction',
            ),
            'LeadRoutingBehavior' => array(
                'class' => 'LeadRoutingBehavior'
            ),
        ));
    }

    /**
     * Return a list of external behaviors which are necessary.
     * @return array A merge of the parent class's behaviors with the ContactsController specific ones
     */
    public function behaviors(){
        return array_merge(parent::behaviors(), array(
                    'LeadRoutingBehavior' => array(
                        'class' => 'LeadRoutingBehavior'
                    )
                ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){
        $contact = $this->loadModel($id);
        // Modify the time zone widget to display Contact time
        if(isset($this->portlets['TimeZone'])){
            $this->portlets['TimeZone']['params']['localTime'] = false;
            $this->portlets['TimeZone']['params']['model'] = &$contact;
        }
        // Only load the Google Maps widget if we're on a Contact with an address
        if(isset($this->portlets['GoogleMaps']))
            $this->portlets['GoogleMaps']['params']['location'] = $contact->cityAddress;

        if($this->checkPermissions($contact, 'view')){
            // Update the VCR list information to preserve what list we came from
            if(isset($_COOKIE['vcr-list'])){
                Yii::app()->user->setState('vcr-list', $_COOKIE['vcr-list']);
            }
            /*
             * This block is the duplicate check code. It checks if two contacts
             * have the same first/last name or if they have the same email address
             * If that is the case, then it will render the duplicateCheck view
             * and prompt the user to take action. As a safety measure, only
             * the first five duplicates are shown unless the user explicitly
             * requests them, this is in case of a situation in which a large number
             * of duplicates are detected and rendering them all would slow down
             * the system. If a duplicate is not found, render the view file instead.
             */
            if($contact->dupeCheck != '1' && !empty($contact->firstName) && !empty($contact->lastName)){
                $criteria = new CDbCriteria();
                $criteria->compare(
                    'CONCAT(firstName," ",lastName)', $contact->firstName." ".$contact->lastName, false, "OR");

                if(!empty($contact->email))
                    $criteria->compare('email', $contact->email, false, "OR");

                $criteria->compare('id', "<>".$contact->id, false, "AND");

                if(!Yii::app()->user->checkAccess('ContactsAdminAccess')){
                    $condition = 'visibility="1" OR (assignedTo="Anyone" AND visibility!="0") '.
                        'OR assignedTo="'.Yii::app()->user->getName().'"';
                    /* x2temp */
                    $groupLinks = Yii::app()->db->createCommand()
                        ->select('groupId')
                        ->from('x2_group_to_user')
                        ->where('userId='.Yii::app()->user->getId())
                        ->queryColumn();

                    if(!empty($groupLinks))
                        $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';

                    $condition .= 'OR ('.
                        'visibility=2 AND assignedTo IN ('.
                            'SELECT username '.
                            'FROM x2_group_to_user '.
                            'WHERE groupId IN ('.
                                'SELECT groupId '.
                                'FROM x2_group_to_user '.
                                'WHERE userId='.Yii::app()->user->getId().')))';

                    $criteria->addCondition($condition);
                }

                $count = X2Model::model('Contacts')->count($criteria);
                if(!isset($_GET['showAll']) || $_GET['showAll'] != 'true')
                    $criteria->limit = 5;
                $duplicates = Contacts::model()->findAll($criteria);
                if(count($duplicates) > 0){
                    $this->render('duplicateCheck', array(
                        'count' => $count,
                        'newRecord' => $contact,
                        'duplicates' => $duplicates,
                        'ref' => 'view'
                    ));
                }else{
                    $contact->dupeCheck = 1;
                    $contact->scenario = 'noChangelog';
                    $contact->update(array('dupeCheck'));
                    User::addRecentItem('c', $id, Yii::app()->user->getId()); ////add contact to user's recent item list
                    parent::view($contact, 'contacts');
                }
            }else{
                User::addRecentItem('c', $id, Yii::app()->user->getId()); ////add contact to user's recent item list
                parent::view($contact, 'contacts');
            }
        } else
            $this->redirect('index');
    }

    /**
     * This is a prototype function designed to re-build a record from the changelog.
     *
     * This method is largely a work in progress though it is functional right
     * now as is, it could just use some refactoring and improvements. On the
     * "View Changelog" page in the Admin tab there's a link on each Contact
     * changelog entry to view the record at that point in the history. Clicking
     * that link brings you here.
     * @param int $id The ID of the Contact to be viewed
     * @param int $timestamp The timestamp to view the Contact at... this should probably be refactored to changelog ID
     */
    public function actionRevisions($id, $timestamp){
        $contact = $this->loadModel($id);
        // Find all the changelog entries associated with this Contact after the given
        // timestamp. Realistically, this would be more accurate if Changelog ID
        // was used instead of the timestamp.
        $changes = X2Model::model('Changelog')->findAll('type="Contacts" AND itemId="'.$contact->id.'" AND timestamp > '.$timestamp.' ORDER BY timestamp DESC');
        // Loop through the changes and apply each one retroactively to the Contact record.
        foreach($changes as $change){
            $fieldName = $change->fieldName;
            if($contact->hasAttribute($fieldName) && $fieldName != 'id')
                $contact->$fieldName = $change->oldValue;
        }
        // Set our widget info
        if(isset($this->portlets['TimeZone']))
            $this->portlets['TimeZone']['params']['model'] = &$contact;
        if(isset($this->portlets['GoogleMaps']))
            $this->portlets['GoogleMaps']['params']['location'] = $contact->cityAddress;

        if($this->checkPermissions($contact, 'view')){

            if(isset($_COOKIE['vcr-list']))
                Yii::app()->user->setState('vcr-list', $_COOKIE['vcr-list']);

            User::addRecentItem('c', $id, Yii::app()->user->getId()); ////add contact to user's recent item list
            // View the Contact with the data modified to this point
            parent::view($contact, 'contacts');
        } else
            $this->redirect('index');
    }

    /**
     * Displays the a model's relationships with other models.
     * This has been largely replaced with the relationships widget.
     * @param type $id The id of the model to display relationships of
     * @deprecated
     */
    public function actionViewRelationships($id){
        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Relationships', array(
                    'criteria' => array(
                        'condition' => '(firstType="Contacts" AND firstId="'.$id.'") OR (secondType="Contacts" AND secondId="'.$id.'")',
                    )
                ));
        $this->render('viewOpportunities', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    }

    /**
     * Used for accounts auto-complete method.  May be obsolete.
     */
    public function actionGetTerms(){
        $sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     * Used for auto-complete methods.  This method is likely obsolete.
     */
    public function actionGetContacts(){
        $sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm OR CONCAT(firstName," ",lastName) LIKE :qterm ORDER BY firstName ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     *  Used for auto-complete methods.  This method is likely obsolete.
     */
    public function actionGetItems(){
        $model = new Contacts('search');
        $visCriteria = $model->getAccessCriteria();
        $sql = 'SELECT id, city, state, country, email, IF(assignedTo > 0, (SELECT name FROM x2_groups WHERE id=assignedTo), (SELECT fullname from x2_profile WHERE username=assignedTo) ) as assignedTo, CONCAT(firstName," ",lastName) as value FROM x2_contacts t WHERE (firstName LIKE :qterm OR lastName LIKE :qterm OR CONCAT(firstName," ",lastName) LIKE :qterm) AND ('.$visCriteria->condition.') ORDER BY firstName ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     * Return a JSON encoded list of Contact lists
     */
    public function actionGetLists(){
        if(!Yii::app()->user->checkAccess('ContactsAdminAccess')){
            $condition = ' AND (visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';

            $condition .= ' OR (visibility=2 AND assignedTo IN
				(SELECT username FROM x2_group_to_user WHERE groupId IN
				(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().'))))';
        } else{
            $condition = '';
        }
        // Optional search parameter for autocomplete
        $qterm = isset($_GET['term']) ? $_GET['term'].'%' : '';
        $result = Yii::app()->db->createCommand()
                ->select('id,name as value')
                ->from('x2_lists')
                ->where('modelName="Contacts" AND type!="campaign" AND name LIKE :qterm'.$condition, array(':qterm' => $qterm))
                ->order('name ASC')
                ->queryAll();
        echo CJSON::encode($result);
    }

    /**
     * Synchronize a Contact record with its related Account.
     * This function will load the linked Account record from the company field
     * and overwrite any shared fields with the Account's version of that field.
     * @param int $id The ID of the Contact
     */
    public function actionSyncAccount($id){
        $contact = $this->loadModel($id);
        if($contact->hasAttribute('company') && is_numeric($contact->company)){
            $account = X2Model::model('Accounts')->findByPk($contact->company);
            if(isset($account)){
                foreach($account->attributes as $key => $value){
                    // Don't change ID or any of the date fields.
                    if($contact->hasAttribute($key) && $key != 'id' && $key != 'createDate' && $key != 'lastUpdated' && $key != 'lastActivity'){
                        $contact->$key = $value;
                    }
                }
            }
        }
        $contact->save();
        $this->redirect(array('view', 'id' => $id));
    }

    /**
     * Loads a Google Maps interface with Contact location data plotted on it
     * This will generate a Google Map frame on a page with several possible
     * additional features. By default it provides a heat map of contact location
     * data. However, if a Contact ID is also provided, it will center the map
     * on that Contact's location and place a marker there. Filtering based on
     * tags or assignment is also possible with the $params array
     * @param int $contactId The ID of a Contact to center the map on
     * @param array $params Additional filter parameters to limit the visible dataset
     * @param int $loadMap The ID of a saved map to re-load previously saved settings
     */
    public function actionGoogleMaps($contactId = null, $params = array(), $loadMap = null){
        if(isset($_POST['contactId']))
            $contactId = $_POST['contactId'];
        if(isset($_POST['params'])){
            $params = $_POST['params'];
        }
        if(!empty($loadMap)){ // If we have a map ID, duplicate whatever information was saved there
            $map = Maps::model()->findByPk($loadMap);
            if(isset($map)){
                $contactId = $map->contactId;
                $params = json_decode($map->params, true);
            }
        }
        $conditions = "TRUE";
        $parameters = array();
        $tagCount = 0;
        $tagFlag = false;
        // Loop through params and add conditions to limit the contact data set
        foreach($params as $field => $value){
            if($field != 'tags' && $value != ''){
                $conditions.=" AND x2_contacts.$field=:$field";
                $parameters[":$field"] = $value;
            }elseif($value != ''){
                $tagFlag = true;
                if(!is_array($value)){
                    $value = explode(",", $value);
                }
                $tagCount = count($value);
                $tagStr = "(";
                for($i = 0; $i < count($value); $i++){
                    $tagStr.=':tag'.$i.', ';
                    $parameters[":tag$i"] = $value[$i];
                }
                $tagStr = substr($tagStr, 0, strlen($tagStr) - 2).")";
                $conditions.=" AND x2_tags.type='Contacts' AND x2_tags.tag IN $tagStr";
            }
        }
        /*
         * These two CDbCommands generate the query to grab all the location lat
         * and lon data to be used on the map. If tags are being filtered on,
         * we need a double join to grab all the requisite data, otherwise we
         * only need to join x2_contacts to x2_locations
         */
        if($tagFlag){
            $locations = Yii::app()->db->createCommand()
                    ->select('x2_locations.*')
                    ->from('x2_locations')
                    ->join('x2_contacts', 'x2_contacts.id=x2_locations.contactId')
                    ->join('x2_tags', 'x2_tags.itemId=x2_locations.contactId')
                    ->where($conditions, $parameters)
                    ->group('x2_tags.itemId')
                    ->having('COUNT(x2_tags.itemId)>='.$tagCount)
                    ->queryAll();
        }else{
            $locations = Yii::app()->db->createCommand()
                    ->select('x2_locations.*')
                    ->from('x2_locations')
                    ->join('x2_contacts', 'x2_contacts.id=x2_locations.contactId')
                    ->where($conditions, $parameters)
                    ->queryAll();
        }
        $locationCodes = array();
        // Loop through the SQL result and convert the data to an array that Google can read
        foreach($locations as $location){
            if(isset($location['lat']) && isset($location['lon'])){
                $tempArr['lat'] = $location['lat'];
                $tempArr['lng'] = $location['lon'];
                $locationCodes[] = $tempArr;
            }
        }
        /*
         * $locationCodes[0] is the first location on the map and where the map
         * will be centered. If we have a Contact ID, center it on that contact's
         * location. Otherwise center it on the first location in the set
         */
        if(isset($contactId)){
            $location = X2Model::model('Locations')->findByAttributes(array('contactId' => $contactId));
            if(isset($location)){
                $loc = array("lat" => $location->lat, "lng" => $location->lon);
                $markerLoc = array("lat" => $location->lat, "lng" => $location->lon);
                $markerFlag = true;
            }elseif(count($locationCodes) > 0){
                $loc = $locationCodes[0];
                $markerFlag = "false";
            }else{
                $loc = array('lat' => 0, 'lng' => 0);
                $markerFlag = "false";
            }
        }else{
            if(isset($locationCodes[0])){
                $loc = $locationCodes[0];
            }else{
                $loc = array('lat' => 0, 'lng' => 0);
            }
            $markerFlag = "false";
        }
        // If we already have a map, use the previous center & zoom settings
        if(isset($map)){
            $loc['lat'] = $map->centerLat;
            $loc['lng'] = $map->centerLng;
            $zoom = $map->zoom;
        }
        /*
         * This view file is actually really complicated as it uses a lot of
         * Google's JS files to render the map.
         */
        $this->render('googleEarth', array(
            'locations' => json_encode($locationCodes),
            'center' => json_encode($loc),
            'markerLoc' => isset($markerLoc) ? json_encode($markerLoc) : json_encode($loc),
            'markerFlag' => $markerFlag,
            'contactId' => isset($contactId) ? $contactId : 0,
            'assignment' => isset($_POST['params']['assignedTo']) || isset($params['assignedTo']) ? (isset($_POST['params']['assignedTo']) ? $_POST['params']['assignedTo'] : $params['assignedTo']) : '',
            'leadSource' => isset($_POST['params']['leadSource']) ? $_POST['params']['leadSource'] : '',
            'tags' => ((isset($_POST['params']['tags']) && !empty($_POST['params']['tags'])) ? $_POST['params']['tags'] : array()),
            'zoom' => isset($zoom) ? $zoom : null,
            'mapFlag' => isset($map) ? 'true' : 'false',
            'noHeatMap' => isset($_GET['noHeatMap']) && $_GET['noHeatMap'] ? true : false,
        ));
    }

    /**
     * An AJAX called function to save map settings.
     */
    public function actionSaveMap(){
        if(isset($_POST['centerLat']) && isset($_POST['centerLng']) && isset($_POST['mapName'])){
            $zoom = $_POST['zoom'];
            $centerLat = $_POST['centerLat'];
            $centerLng = $_POST['centerLng'];
            $contactId = isset($_POST['contactId']) ? $_POST['contactId'] : '';
            $params = isset($_POST['parameters']) ? $_POST['parameters'] : array();
            $mapName = $_POST['mapName'];

            $map = new Maps;
            $map->name = $mapName;
            $map->owner = Yii::app()->user->getName();
            $map->contactId = $contactId;
            $map->zoom = $zoom;
            $map->centerLat = $centerLat;
            $map->centerLng = $centerLng;
            $map->params = json_encode($params);
            if($map->save()){

            }else{

            }
        }
    }

    /* public function actionSavedSearches (){
      $this->render('savedSearches',array(
      'dataProvider'=>$dataProvider,
      ));
      } */

    /**
     * Display an index of saved maps.
     */
    public function actionSavedMaps(){
        if(Yii::app()->user->checkAccess('ContactsAdmin')){
            $dataProvider = new CActiveDataProvider('Maps');
        }else{
            $dataProvider = new CActiveDataProvider('Maps', array(
                        'criteria' => array(
                            'condition' => 'owner="'.Yii::app()->user->getName().'"',
                        )
                    ));
        }
        $this->render('savedMaps', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Delete a saved map
     * @param int $id ID of the map to delete
     */
    public function actionDeleteMap($id){
        $map = Maps::model()->findByPk($id);
        if(isset($map) && ($map->owner == Yii::app()->user->getName() || Yii::app()->user->checkAccess('ContactsAdmin')) && Yii::app()->request->isPostRequest){
            $map->delete();
        }
        $this->redirect('savedMaps');
    }

    /**
     * An AJAX called function to update the location of a Contact record
     * @param int $contactId The ID of the contact
     * @param float $lat The lattitutde of the location
     * @param float $lon The longitude of the location
     */
    public function actionUpdateLocation($contactId, $lat, $lon){
        $location = Locations::model()->findByAttributes(array('contactId' => $contactId));
        if(!isset($location)){
            $location = new Locations;
            $location->contactId = $contactId;
            $location->lat = $lat;
            $location->lon = $lon;
            $location->save();
        }else{
            if($location->lat != $lat || $location->lon != $lon){
                $location->lat = $lat;
                $location->lon = $lon;
                $location->save();
            }
        }
    }

    /**
     * Generates an email template to share Contact data
     * @param int $id The ID of the Contact
     */
    public function actionShareContact($id){
        $users = User::getNames();
        $model = $this->loadModel($id);
        $body = "\n\n\n\n".Yii::t('contacts', 'Contact Record Details')." <br />
<br />".Yii::t('contacts', 'Name').": $model->firstName $model->lastName
<br />".Yii::t('contacts', 'E-Mail').": $model->email
<br />".Yii::t('contacts', 'Phone').": $model->phone
<br />".Yii::t('contacts', 'Account').": $model->company
<br />".Yii::t('contacts', 'Address').": $model->address
<br />$model->city, $model->state $model->zipcode
<br />".Yii::t('contacts', 'Background Info').": $model->backgroundInfo
<br />".Yii::t('app', 'Link').": ".CHtml::link($model->name, $this->createAbsoluteUrl('/contacts/contacts/view',array('id'=>$model->id)));

        $body = trim($body);

        $errors = array();
        $status = array();
        $email = array();
        if(isset($_POST['email'], $_POST['body'])){

            $subject = Yii::t('contacts', 'Contact Record Details');
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if($email['to'] === false)
                $errors[] = 'email';
            if(empty($body))
                $errors[] = 'body';

            $emailFrom = Credentials::model()->getDefaultUserAccount(Credentials::$sysUseId['systemNotificationEmail'], 'email');
            if($emailFrom == Credentials::LEGACY_ID)
                $emailFrom = array(
                    'name' => Yii::app()->params->profile->fullName,
                    'address' => Yii::app()->params->profile->emailAddress
                );

            if(empty($errors))
                $status = $this->sendUserEmail($email, $subject, $body, null, $emailFrom);

            if(array_search('200', $status)){
                $this->redirect(array('view', 'id' => $model->id));
                return;
            }
            if($email['to'] === false)
                $email = $_POST['email'];
            else
                $email = $this->mailingListToString($email['to']);
        }
        $this->render('shareContact', array(
            'model' => $model,
            'users' => $users,
            'body' => $body,
            'currentWorkflow' => $this->getCurrentWorkflow($model->id, 'contacts'),
            'email' => $email,
            'status' => $status,
            'errors' => $errors
        ));
    }

    /* 	// Creates contact record
      public function create($model, $oldAttributes, $api) {
      $model->createDate = time();
      $model->lastUpdated = time();
      if(empty($model->visibility) && $model->visibility!=0)
      $model->visibility=1;
      if($api == 0) {
      parent::create($model, $oldAttributes, $api);
      } else {
      $lookupFields = Fields::model()->findAllByAttributes(array('modelName' => 'Contacts', 'type' => 'link'));
      foreach($lookupFields as $field) {
      $fieldName = $field->fieldName;
      if(isset($model->$fieldName)) {
      $lookup = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $model->$fieldName));
      if(isset($lookup))
      $model->$fieldName = $lookup->id;
      }
      }
      return parent::create($model, $oldAttributes, $api);
      }
      } */

    /**
     * Called by the duplicate checker to keep the current record
     */
    public function actionIgnoreDuplicates(){
        if(isset($_POST['data'])){

            $arr = json_decode($_POST['data'], true);
            if($_POST['ref'] != 'view'){
                if($_POST['ref'] == 'create')
                    $model = new Contacts;
                else{
                    $id = $arr['id'];
                    $model = Contacts::model()->findByPk($id);
                }
                $temp = $model->attributes;
                foreach($arr as $key => $value){
                    $model->$key = $value;
                }
            }else{
                $id = $arr['id'];
                $model = X2Model::model('Contacts')->findByPk($id);
            }
            $model->dupeCheck = 1;
            $model->disableBehavior('X2TimestampBehavior');
            if($model->save()){

            }
            // Optional parameter to determine what other steps to take, default null
            $action = $_POST['action'];
            if(!is_null($action)){
                $criteria = new CDbCriteria();
                if(!empty($model->firstName) && !empty($model->lastName))
                    $criteria->compare('CONCAT(firstName," ",lastName)', $model->firstName." ".$model->lastName, false, "OR");
                if(!empty($model->email))
                    $criteria->compare('email', $model->email, false, "OR");
                $criteria->compare('id', "<>".$model->id, false, "AND");
                if(!Yii::app()->user->checkAccess('ContactsAdminAccess')){
                    $condition = 'visibility="1" OR (assignedTo="Anyone" AND visibility!="0")  OR assignedTo="'.Yii::app()->user->getName().'"';
                    /* x2temp */
                    $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
                    if(!empty($groupLinks))
                        $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';

                    $condition .= 'OR (visibility=2 AND assignedTo IN
						(SELECT username FROM x2_group_to_user WHERE groupId IN
							(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
                    $criteria->addCondition($condition);
                }
                // If the action was hide all, hide all the other records.
                if($action == 'hideAll'){
                    $duplicates = Contacts::model()->findAll($criteria);
                    foreach($duplicates as $duplicate){
                        $duplicate->dupeCheck = 1;
                        $duplicate->assignedTo = 'Anyone';
                        $duplicate->visibility = 0;
                        $duplicate->doNotCall = 1;
                        $duplicate->doNotEmail = 1;
                        $duplicate->save();
                        $notif = new Notification;
                        $notif->user = 'admin';
                        $notif->createdBy = Yii::app()->user->getName();
                        $notif->createDate = time();
                        $notif->type = 'dup_discard';
                        $notif->modelType = 'Contacts';
                        $notif->modelId = $duplicate->id;
                        $notif->save();
                    }
                // If it was delete all...
                }elseif($action == 'deleteAll'){
                    Contacts::model()->deleteAll($criteria);
                }
            }
            echo $model->id;
        }
    }

    /**
     * Called by the duplicate checker when discarding the new record.
     */
    public function actionDiscardNew(){

        if(isset($_POST['id'])){
            $ref = $_POST['ref']; // Referring action
            $action = $_POST['action'];
            $oldId = $_POST['id'];
            if($ref == 'create' && is_null($action) || $action == 'null'){
                echo $oldId;
                return;
            }elseif($ref == 'create'){
                $oldRecord = X2Model::model('Contacts')->findByPk($oldId);
                if(isset($oldRecord)){
                    $oldRecord->disableBehavior('X2TimestampBehavior');
                    Relationships::model()->deleteAllByAttributes(array('firstType' => 'Contacts', 'firstId' => $oldRecord->id));
                    Relationships::model()->deleteAllByAttributes(array('secondType' => 'Contacts', 'secondId' => $oldRecord->id));
                    if($action == 'hideThis'){
                        $oldRecord->dupeCheck = 1;
                        $oldRecord->assignedTo = 'Anyone';
                        $oldRecord->visibility = 0;
                        $oldRecord->doNotCall = 1;
                        $oldRecord->doNotEmail = 1;
                        $oldRecord->save();
                        $notif = new Notification;
                        $notif->user = 'admin';
                        $notif->createdBy = Yii::app()->user->getName();
                        $notif->createDate = time();
                        $notif->type = 'dup_discard';
                        $notif->modelType = 'Contacts';
                        $notif->modelId = $oldId;
                        $notif->save();
                        echo $_POST['id'];
                        return;
                    }elseif($action == 'deleteThis'){
                        $oldRecord->delete();
                        echo $_POST['id'];
                        return;
                    }
                }
            }elseif(isset($_POST['newId'])){
                $newId = $_POST['newId'];
                $oldRecord = X2Model::model('Contacts')->findByPk($oldId);
                $oldRecord->disableBehavior('X2TimestampBehavior');
                $newRecord = Contacts::model()->findByPk($newId);
                $newRecord->disableBehavior('X2TimestampBehavior');
                $newRecord->dupeCheck = 1;
                $newRecord->save();
                if($action === ''){
                    $newRecord->delete();
                    echo $oldId;
                    return;
                }else{
                    if(isset($oldRecord)){

                        if($action == 'hideThis'){
                            $oldRecord->dupeCheck = 1;
                            $oldRecord->assignedTo = 'Anyone';
                            $oldRecord->visibility = 0;
                            $oldRecord->doNotCall = 1;
                            $oldRecord->doNotEmail = 1;
                            $oldRecord->save();
                            $notif = new Notification;
                            $notif->user = 'admin';
                            $notif->createdBy = Yii::app()->user->getName();
                            $notif->createDate = time();
                            $notif->type = 'dup_discard';
                            $notif->modelType = 'Contacts';
                            $notif->modelId = $oldId;
                            $notif->save();
                        }elseif($action == 'deleteThis'){
                            Relationships::model()->deleteAllByAttributes(array('firstType' => 'Contacts', 'firstId' => $oldRecord->id));
                            Relationships::model()->deleteAllByAttributes(array('secondType' => 'Contacts', 'secondId' => $oldRecord->id));
                            Tags::model()->deleteAllByAttributes(array('type' => 'Contacts', 'itemId' => $oldRecord->id));
                            Actions::model()->deleteAllByAttributes(array('associationType' => 'Contacts', 'associationId' => $oldRecord->id));
                            $oldRecord->delete();
                        }
                    }

                    echo $newId;
                }
            }
        }
    }

    /**
     * Creates a new Contact record
     */
    public function actionCreate(){
        $model = new Contacts;
        $name = 'Contacts';
        $renderFlag = true;
        $users = User::getNames();

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Contacts'])){
            $oldAttributes = $model->attributes;
            $model->setX2Fields($_POST['Contacts']);

            $criteria = new CDbCriteria();
            if(!empty($model->firstName) && !empty($model->lastName)){
                $criteria->compare('CONCAT(firstName," ",lastName)', $model->firstName." ".$model->lastName, false, "OR");
            }
            if(!empty($model->email)){
                $criteria->compare('email', $model->email, false, "OR");
            }
            if(isset($_POST['x2ajax'])){
                // if($this->create($model,$oldAttributes, '1')) { // success creating account?
                if($model->save()){ // success creating Contact?
                    $primaryAccountLink = '';
                    $newPhone = '';
                    $newWebsite = '';
                    if(isset($_POST['ModelName']) && !empty($_POST['ModelId'])){
                        Relationships::create($_POST['ModelName'], $_POST['ModelId'], 'Contacts', $model->id);

                        if($_POST['ModelName'] == 'Accounts'){
                            $account = Accounts::model()->findByPk($_POST['ModelId']);
                            if($account){
                                $changed = false;
                                if(isset($model->website) && (!isset($account->website) || $account->website == "")){
                                    $account->website = $model->website;
                                    $newWebsite = $account->website;
                                    $changed = true;
                                }
                                if(isset($model->phone) && (!isset($account->phone) || $account->phone == "")){
                                    $account->phone = $model->phone;
                                    $newPhone = $account->phone;
                                    $changed = true;
                                }

                                if($changed)
                                    $account->update();
                            }
                        } else if($_POST['ModelName'] == 'Opportunity'){
                            $opportunity = Opportunity::model()->findByPk($_POST['ModelId']);
                            if($opportunity){
                                if(isset($model->company) && $model->company != '' && (!isset($opportunity->accountName) || $opportunity->accountName == '')){
                                    $opportunity->accountName = $model->company;
                                    $opportunity->update();
                                    $primaryAccountLink = $model->createLink();
                                }
                            }
                        }
                    }

                    echo json_encode(
                            array(
                                'status' => 'success',
                                'name' => $model->name,
                                'id' => $model->id,
                                'primaryAccountLink' => $primaryAccountLink,
                                'newWebsite' => $newWebsite,
                                'newPhone' => $newPhone,
                            )
                    );
                    Yii::app()->end();
                }else{
                    $x2ajaxCreateError = true;
                }
            }else{
                if(!empty($criteria->condition)){
                    if(!Yii::app()->user->checkAccess('ContactsAdminAccess')){
                        $condition = 'visibility="1" OR (assignedTo="Anyone" AND visibility!="0")  OR assignedTo="'.Yii::app()->user->getName().'"';
                        /* x2temp */
                        $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
                        if(!empty($groupLinks))
                            $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';

                        $condition .= 'OR (visibility=2 AND assignedTo IN
							(SELECT username FROM x2_group_to_user WHERE groupId IN
								(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
                        $criteria->addCondition($condition);
                    }
                    $count = X2Model::model('Contacts')->count($criteria);
                    if(!isset($_GET['viewAll']) || $_GET['viewAll'] != 'true')
                        $criteria->limit = 5;
                    $duplicates = X2Model::model('Contacts')->findAll($criteria);
                    if(count($duplicates) > 0){
                        $this->render('duplicateCheck', array(
                            'count' => $count,
                            'newRecord' => $model,
                            'duplicates' => $duplicates,
                            'ref' => 'create'
                        ));
                        $renderFlag = false;
                    }else{
                        // $this->create($model, $oldAttributes, '0');
                        if($model->save())
                            $this->redirect(array('view', 'id' => $model->id));
                    }
                } else{
                    // $this->create($model, $oldAttributes, '0');
                    if($model->save())
                        $this->redirect(array('view', 'id' => $model->id));
                }
            }
        }

        if($renderFlag){

            if(isset($_POST['x2ajax'])){
                if(isset($x2ajaxCreateError) && $x2ajaxCreateError == true){
                    $page = $this->renderPartial('application.components.views._form', array('model' => $model, 'users' => $users, 'modelName' => 'contacts'), true, true);
                    echo json_encode(
                            array(
                                'status' => 'userError',
                                'page' => $page,
                            )
                    );
                }else{
                    $this->renderPartial('application.components.views._form', array('model' => $model, 'users' => $users, 'modelName' => 'contacts'), false, true);
                }
            }else{
                $this->render('create', array(
                    'model' => $model,
                    'users' => $users,
                ));
            }
        }
    }

    /**
     * Method of creating a Contact called by the Quick Create widget
     */
    public function actionQuickContact(){

        $model = new Contacts;
        // collect user input data
        if(isset($_POST['Contacts'])){
            // clear values that haven't been changed from the default
            //$temp=$model->attributes;
            $model->setX2Fields($_POST['Contacts']);

            $model->visibility = 1;
            // validate user input and save contact
            // $changes = $this->calculateChanges($temp, $model->attributes, $model);
            // $model = $this->updateChangelog($model, $changes);
            $model->createDate = time();
            //if($model->validate()) {
            if($model->save()){

            }else{
                //echo CHtml::errorSummary ($model);
                echo CJSON::encode($model->getErrors());
            }
            return;
            //}
            //echo '';
            //echo CJSON::encode($model->getErrors());
        }
        $this->renderPartial('application.components.views.quickContact', array(
            'model' => $model
        ));
    }
    /*
    public function actionTest(){
        $this->render('test');
    }
     *
     */

    /*
    public function actionTrigger(){
        die();
        $item = new X2FlowItem;
        $item->type = 'workflow_complete';

        var_dump($item->getParamRules());
        die();
        // $t0 = microtime(true);
        // for($i=0;$i<10000;$i++)
        // time();
        // echo (microtime(true) - $t0);
        // die();



        set_time_limit(1);

        $str = '{a+b} a c,  {a+5}%10 c/ab2 * {8.333/ 0.3}-{apples.sauce}';
        // $str = '{}a{b} {apple}{sauce} + {a} and {apple.sauce}! ';


        $str2 = '{a+b} a c e, {a+5}%10 c/ab2 * {8.333/ 0.3}-{apples.sauce}';
        var_dump($str);
        var_dump($str2);
        $diff = FineDiff::getDiffOpcodes($str, $str2, FineDiff::$wordGranularity);
        var_dump($diff);
        // $diff = new FineDiff($str,$str2,FineDiff::$wordGranularity);
        // var_dump($diff->edits);

        var_dump(FineDiff::renderToTextFromOpcodes($str, $diff));

        // die();


        var_dump($str);

        // $tokens = self::tokenize($str);
        $tokens = X2FlowParam::parseExpressionTree($str);


        var_dump($tokens);

        die();



        // $data = array('{account.createDate} +5 days');
        $attr = array('lastUpdated' => time(), 'account' => 23);

        // $test = json_encode($data);
        // echo $test;
        // var_dump( json_decode($test));

        $value = '{lastUpdated} 2 days ago';
        $matches = array();
        preg_match('/{(\w+|\w+.\w+)}/', $value, $matches); // format can be either "{field}" or "{linkField.field}"
        // if($type === 'timestamp') {
        if(count($matches) > 1){
            $value = preg_replace('/{.+}/', '', $value); // remove variables (for date/time fields)


            if(isset($attr[$matches[1]])){

                // $timestamp = getAttribute($matches[1]); //$attr[$matches[1]];
                $timestamp = $attr[$matches[1]];

                if(trim($value) === '')
                    $date = $timestamp;
                else
                    $date = strtotime($value, $timestamp);
            } else
                $date = false;
        } else
            $date = strtotime($value);
        var_dump($value);
        var_dump($date);
        var_dump(date('Y-m-d h:i:s', $date));
        // }
    }*/

    // Controller/action wrapper for update()
    public function actionUpdate($id){
        $model = $this->loadModel($id);
        $users = User::getNames();
        $renderFlag = true;

        if(isset($_POST['Contacts'])){
            $oldAttributes = $model->attributes;

            $model->setX2Fields($_POST['Contacts']);
            if($model->dupeCheck != '1'){
                $model->dupeCheck = 1;
                $criteria = new CDbCriteria();
                $criteriaFlag = false;
                if(!empty($model->firstName) && !empty($model->lastName)){
                    $criteria->compare('CONCAT(firstName," ",lastName)', $model->firstName." ".$model->lastName, false, "OR");
                    $criteriaFlag = true;
                }
                if(!empty($model->email)){
                    $criteria->compare('email', $model->email, false, "OR");
                    $criteriaFlag = true;
                }
                $criteria->compare('id', "<>".$model->id, false, "AND");
                if(!Yii::app()->user->checkAccess('ContactsAdminAccess')){
                    $condition = 'visibility="1" OR (assignedTo="Anyone" AND visibility!="0")  OR assignedTo="'.Yii::app()->user->getName().'"';
                    /* x2temp */
                    $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
                    if(!empty($groupLinks))
                        $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';

                    $condition .= 'OR (visibility=2 AND assignedTo IN
						(SELECT username FROM x2_group_to_user WHERE groupId IN
							(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
                    $criteria->addCondition($condition);
                }
                $count = X2Model::model('Contacts')->count($criteria);
                if(!empty($criteria) && $criteriaFlag){
                    $duplicates = X2Model::model('Contacts')->findAll($criteria);
                    if(count($duplicates) > 0){
                        $this->render('duplicateCheck', array(
                            'newRecord' => $model,
                            'duplicates' => $duplicates,
                            'ref' => 'update',
                            'count' => $count,
                        ));
                        $renderFlag = false;
                    }else{
                        // $this->update($model, $oldAttributes, 0);
                        if($model->save())
                            $this->redirect(array('view', 'id' => $model->id));
                    }
                } else{
                    // $this->update($model, $oldAttributes, 0);
                    if($model->save())
                        $this->redirect(array('view', 'id' => $model->id));
                }
            } else{
                // $this->update($model, $oldAttributes, 0);
                if($model->save())
                    $this->redirect(array('view', 'id' => $model->id));
            }
        }
        if($renderFlag){

            if(isset($_POST['x2ajax'])){
                Yii::app()->clientScript->scriptMap['*.js'] = false;
                Yii::app()->clientScript->scriptMap['*.css'] = false;
                if(isset($x2ajaxCreateError) && $x2ajaxCreateError == true){
                    $page = $this->renderPartial('application.components.views._form', array('model' => $model, 'users' => $users, 'modelName' => 'contacts'), true, true);
                    echo json_encode(
                            array(
                                'status' => 'userError',
                                'page' => $page,
                            )
                    );
                }else{
                    $this->renderPartial('application.components.views._form', array('model' => $model, 'users' => $users, 'modelName' => 'contacts'), false, true);
                }
            }else{
                $this->render('update', array(
                    'model' => $model,
                    'users' => $users,
                ));
            }
        }
    }

    // Displays all visible Contact Lists
    public function actionLists(){
        $criteria = new CDbCriteria();
        $criteria->addCondition('type="static" OR type="dynamic"');
        if(!Yii::app()->params->isAdmin){
            $condition = 'visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';

            $condition .= 'OR (visibility=2 AND assignedTo IN
				(SELECT username FROM x2_group_to_user WHERE groupId IN
					(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
            $criteria->addCondition($condition);
        }

        $perPage = ProfileChild::getResultsPerPage();

        //$criteria->offset = isset($_GET['page']) ? $_GET['page'] * $perPage - 3 : -3;
        //$criteria->limit = $perPage;
        $criteria->order = 'createDate DESC';

        $contactLists = X2Model::model('X2List')->findAll($criteria);

        $totalContacts = X2Model::model('Contacts')->count();
        $totalMyContacts = X2Model::model('Contacts')->count('assignedTo="'.Yii::app()->user->getName().'"');
        $totalNewContacts = X2Model::model('Contacts')->count('assignedTo="'.Yii::app()->user->getName().'" AND createDate >= '.mktime(0, 0, 0));

        $allContacts = new X2List;
        $allContacts->attributes = array(
            'id' => 'all',
            'name' => Yii::t('contacts', 'All Contacts'),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $newContacts = new X2List;
        $newContacts->attributes = array(
            'id' => 'new',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('contacts', 'New Contacts'),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalNewContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $myContacts = new X2List;
        $myContacts->attributes = array(
            'id' => 'my',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('contacts', 'My Contacts'),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalMyContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $contactListData = array(
            $allContacts,
            $myContacts,
            $newContacts,
        );

        $dataProvider = new CArrayDataProvider(array_merge($contactListData, $contactLists), array(
                    'pagination' => array('pageSize' => $perPage),
                    'totalItemCount' => count($contactLists) + 3,
                ));

        $this->render('listIndex', array(
            'contactLists' => $dataProvider,
        ));
    }

    // Lists all contacts assigned to this user
    public function actionMyContacts(){
        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', 'myContacts');
        $this->render('index', array('model' => $model));
    }

    // Lists all contacts assigned to this user
    public function actionNewContacts(){
        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', 'newContacts');
        $this->render('index', array('model' => $model));
    }

    // Lists all visible contacts
    public function actionIndex(){
        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', 'index');
        $this->render('index', array('model' => $model));
    }

    // Shows contacts in the specified list
    public function actionList($id = null){
        $list = X2List::load($id);

        if(!isset($list)){
            Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('lists'));
        }

        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', $id);
        $dataProvider = $model->searchList($id);
        $list->count = $dataProvider->totalItemCount;
        $list->save();

        $this->render('list', array(
            'listModel' => $list,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    }

    public function actionCreateList(){
        $list = new X2List;
        $list->modelName = 'Contacts';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Contacts;
        $comparisonList = array(
            '=' => Yii::t('contacts', 'equals'),
            '>' => Yii::t('contacts', 'greater than'),
            '<' => Yii::t('contacts', 'less than'),
            '<>' => Yii::t('contacts', 'not equal to'),
            'contains' => Yii::t('contacts', 'contains'),
            'noContains' => Yii::t('contacts', 'does not contain'),
            'empty' => Yii::t('contacts', 'empty'),
            'notEmpty' => Yii::t('contacts', 'not empty'),
            'list' => Yii::t('contacts', 'in list'),
            'notList' => Yii::t('contacts', 'not in list'),
        );

        if(isset($_POST['X2List'])){

            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Contacts';
            $list->createDate = time();
            $list->lastUpdated = time();

            if($list->type == 'dynamic')
                $criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if(isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])){

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if(count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)){

                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Contacts';

                    $list->lastUpdated = time();

                    if($list->save()){

                        X2ListCriterion::model()->deleteAllByAttributes(array('listId' => $list->id)); // delete old criteria

                        for($i = 0; $i < count($attributes); $i++){ // create new criteria
                            if((array_key_exists($attributes[$i], $contactModel->attributeLabels()) || $attributes[$i] == 'tags')
                                    && array_key_exists($comparisons[$i], $comparisonList)){  //&& $values[$i] != ''
                                $fieldRef = Fields::model()->findByAttributes(array('modelName' => 'Contacts', 'fieldName' => $attributes[$i]));
                                if(isset($fieldRef) && $fieldRef->type == 'link'){
                                    $lookup = X2Model::model(ucfirst($fieldRef->linkType))->findByAttributes(array('name' => $values[$i]));
                                    if(isset($lookup))
                                        $values[$i] = $lookup->id;
                                }
                                $criterion = new X2ListCriterion;
                                $criterion->listId = $list->id;
                                $criterion->type = 'attribute';
                                $criterion->attribute = $attributes[$i];
                                $criterion->comparison = $comparisons[$i];
                                $criterion->value = $values[$i];
                                $criterion->save();
                            }
                        }
                        $this->redirect(array('/contacts/contacts/list','id'=>$list->id));
                    }
                }
            }
        }

        if(empty($criteriaModels)){
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        }

        $this->render('createList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('contacts', 'Dynamic'),
                'static' => Yii::t('contacts', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }

    public function actionUpdateList($id){
        $list = X2List::model()->findByPk($id);

        if(!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if(!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new Contacts;
        $comparisonList = array(
            '=' => Yii::t('contacts', 'equals'),
            '>' => Yii::t('contacts', 'greater than'),
            '<' => Yii::t('contacts', 'less than'),
            '<>' => Yii::t('contacts', 'not equal to'),
            'contains' => Yii::t('contacts', 'contains'),
            'noContains' => Yii::t('contacts', 'does not contain'),
            'empty' => Yii::t('contacts', 'empty'),
            'notEmpty' => Yii::t('contacts', 'not empty'),
            'list' => Yii::t('contacts', 'in list'),
            'notList' => Yii::t('contacts', 'not in list'),
        );

        if($list->type == 'dynamic'){
            $criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if(isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])){

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if(count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)){

                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Contacts';
                    $list->lastUpdated = time();

                    if($list->save()){

                        X2ListCriterion::model()->deleteAllByAttributes(array('listId' => $list->id)); // delete old criteria

                        for($i = 0; $i < count($attributes); $i++){ // create new criteria
                            if((array_key_exists($attributes[$i], $contactModel->attributeLabels()) || $attributes[$i] == 'tags')
                                    && array_key_exists($comparisons[$i], $comparisonList)){  //&& $values[$i] != ''
                                $fieldRef = Fields::model()->findByAttributes(array('modelName' => 'Contacts', 'fieldName' => $attributes[$i]));
                                if(isset($fieldRef) && $fieldRef->type == 'link'){
                                    $lookup = X2Model::model(ucfirst($fieldRef->linkType))->findByAttributes(array('name' => $values[$i]));
                                    if(isset($lookup))
                                        $values[$i] = $lookup->id;
                                }
                                $criterion = new X2ListCriterion;
                                $criterion->listId = $list->id;
                                $criterion->type = 'attribute';
                                $criterion->attribute = $attributes[$i];
                                $criterion->comparison = $comparisons[$i];
                                $criterion->value = $values[$i];
                                $criterion->save();
                            }
                        }
                        $this->redirect(array('/contacts/contacts/list','id'=>$list->id));
                    }
                }
            }
        } else{ //static or campaign lists
            if(isset($_POST['X2List'])){
                $list->attributes = $_POST['X2List'];
                $list->modelName = 'Contacts';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/contacts/contacts/list','id'=>$list->id));
            }
        }

        if(empty($criteriaModels)){
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        }

        $this->render('updateList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('contacts', 'Dynamic'),
                'static' => Yii::t('contacts', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }

    // Yii::app()->db->createCommand()->select('id')->from($tableName)->where(array('like','name',"%$value%"))->queryColumn();
    public function actionRemoveFromList(){

        if(isset($_POST['gvSelection'], $_POST['listId']) && !empty($_POST['gvSelection']) && is_array($_POST['gvSelection'])){

            foreach($_POST['gvSelection'] as $contactId)
                if(!ctype_digit((string) $contactId))
                    throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));

            $list = CActiveRecord::model('X2List')->findByPk($_POST['listId']);

            // check permissions
            if($list !== null && $this->checkPermissions($list, 'edit'))
                $list->removeIds($_POST['gvSelection']);

            echo 'success';
        }
    }

    public function actionDeleteList(){

        $id = isset($_GET['id']) ? $_GET['id'] : 'all';

        if(is_numeric($id))
            $list = X2Model::model('X2List')->findByPk($id);
        if(isset($list)){

            // check permissions
            if($this->checkPermissions($list, 'edit'))
                $list->delete();
            else
                throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        }
        $this->redirect(array('/contacts/contacts/lists'));
    }

    /**
     * This function now just redirects to the "exportContacts" action which has
     * this functionality included in it now.
     * @param int $id ID of the list
     * @deprecated
     */
    public function actionExportList($id){
        $this->redirect('exportContacts?listId='.$id);
        /* $list = X2Model::model('X2List')->findByPk($id);
          if(isset($list)) {
          if(!$this->checkPermissions($list, 'view')) // check permissions
          throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
          } else
          throw new CHttpException(404, Yii::t('app', 'The requested list does not exist.'));



          $dataProvider = X2Model::model('Contacts')->searchList($id); // get the list

          $totalItemCount = $dataProvider->getTotalItemCount();
          $dataProvider->pagination->itemCount = $totalItemCount;
          $dataProvider->pagination->pageSize = 1000;  // process list in blocks of 1000

          $allFields = X2Model::model('Contacts')->getFields(true); // get associative array of fields

          $gvSettings = ProfileChild::getGridviewSettings('contacts_list' . $id);

          $selectedColumns = array();
          $columns = array();

          if($gvSettings === null) {
          $selectedColumns = array(// default columns
          'firstName',
          'lastName',
          'phone',
          'email',
          'leadSource',
          'createDate',
          'lastUpdated',
          );
          } else {
          $selectedColumns = array_keys($gvSettings);
          }

          foreach($selectedColumns as &$colName) {

          if($colName == 'tags') {
          $columns[$colName]['label'] = Yii::t('app', 'Tags');
          $columns[$colName]['type'] = 'tags';
          } elseif ($colName == 'name') {
          $columns[$colName]['label'] = Yii::t('contacts', 'Name');
          $columns[$colName]['type'] = 'name';
          } else {
          if(array_key_exists($colName, $allFields)) {

          $columns[$colName]['label'] = $allFields[$colName]['attributeLabel'];

          if(in_array($colName, array('annualRevenue', 'quoteAmount')))
          $columns[$colName]['type'] = 'currency';
          else
          $columns[$colName]['type'] = $allFields[$colName]['type'];

          $columns[$colName]['linkType'] = $allFields[$colName]['linkType'];
          }
          }
          }
          unset($colName);

          $fileName = 'list' . $id . '.csv';
          $fp = fopen($fileName, 'w+');

          // output column labels for the first line
          $columnLabels = array();
          foreach($columns as $colName => &$field)
          $columnLabels[] = $field['label'];
          unset($field);

          fputcsv($fp, $columnLabels);

          for ($i = 0; $i < $dataProvider->pagination->pageCount; ++$i) {
          $dataProvider->pagination->currentPage = $i;

          $dataSet = $dataProvider->getData(true);
          foreach($dataSet as &$model) {

          $row = array();

          foreach($columns as $fieldName => &$field) {

          if($field['type'] == 'tags') {
          $row[] = Tags::getTags('Contacts', $model->id, 10);
          } elseif ($field['type'] == 'date') {
          $row[] = date('c', $model->$fieldName);
          } elseif ($field['type'] == 'visibility') {
          switch ($model->$fieldName) {
          case '1':
          $row[] = Yii::t('app', 'Public');
          break;
          case '0':
          $row[] = Yii::t('app', 'Private');
          break;
          case '2':
          $row[] = Yii::t('app', 'User\'s Groups');
          break;
          }
          } elseif ($field['type'] == 'link') {
          if(is_numeric($model->$fieldName)) {
          $className = ucfirst($field['linkType']);
          if(class_exists($className)) {
          $lookupModel = X2Model::model($className)->findByPk($model->$fieldName);
          if(isset($lookupModel))
          $row[] = $lookupModel->name;
          }
          } else {
          $row[] = $model->$fieldName;
          }
          } elseif ($field['type'] == 'currency') {
          if($model instanceof Product) // products have their own currency
          $row[] = Yii::app()->locale->numberFormatter->formatCurrency($model->$fieldName, $model->currency);
          elseif (!empty($model->$fieldName))
          $row[] = Yii::app()->locale->numberFormatter->formatCurrency($model->$fieldName, Yii::app()->params['currency']);
          else
          $row[] = '';
          } elseif ($field['type'] == 'dropdown') {
          $row[] = Yii::t(strtolower(Yii::app()->controller->id), $model->$fieldName);
          } else {
          $row[] = $model->$fieldName;

          }
          }
          fputcsv($fp, $row);
          }
          unset($model);
          }
          fclose($fp);

          $file = Yii::app()->file->set($fileName);
          $file->download(); */
    }

//	public function actionImportContacts() {
//		if(isset($_FILES['contacts'])) {
//
//			$temp = CUploadedFile::getInstanceByName('contacts');
//			$temp->saveAs('contacts.csv');
//			$this->import('contacts.csv');
//		}
//		$this->render('importContacts');
//	}
//	private function import($file) {
//		$arr = file($file);
//
//		for ($i = 1; $i < count($arr) - 1; $i++) {
//
//			$str = $arr[$i] . $arr[$i + 1];
//			$i++;
//			$pieces = explode(',', $str);
//
//			$model = new Contacts;
//
//			$model->visibility = 1;
//			$model->createDate = time();
//			$model->lastUpdated = time();
//			$model->updatedBy = 'admin';
//			$model->backgroundInfo = $this->stripquotes($pieces[77]);
//			$model->firstName = $this->stripquotes($pieces[1]);
//			$model->lastName = $this->stripquotes($pieces[3]);
//			$model->assignedTo = Yii::app()->user->getName();
//			$model->company = $this->stripquotes($pieces[5]);
//			$model->title = $this->stripquotes($pieces[7]);
//			$model->email = $this->stripquotes($pieces[57]);
//			$model->phone = $this->stripquotes($pieces[31]);
//			$model->address = $this->stripquotes($pieces[8]) . ' ' . $this->stripquotes($pieces[9]) . ' ' . $this->stripquotes($pieces[10]);
//			$model->city = $this->stripquotes($pieces[11]);
//			$model->state = $this->stripquotes($pieces[12]);
//			$model->zipcode = $this->stripquotes($pieces[13]);
//			$model->country = $this->stripquotes($pieces[14]);
//
//			if($model->save()) {
//
//			}
//		}
//		unlink($file);
//		$this->redirect('index');
//	}



    /**
     * The Contacts import page. See inline documentation for a thorough explanation
     * of what is going on.
     */
    public function actionImportExcel(){

        /**
         * Internal method... could probably be refactored out but I wanted to
         * keep this compartmentalized and easy to keep track of in the event it
         * needed to be called more often. The goal of this function is to attempt
         * to map meta into a series of Contact attributes, which it will do via
         * string comparison on the Contact attribute names, the Contact attribute
         * labels and a pattern match.
         * @param array $attributes Contact model's attributes
         * @param array $meta Provided metadata in the CSV
         */
        function createImportMap($attributes, $meta){
            // We need to do data processing on attributes, first copy & preserve
            $originalAttributes = $attributes;
            // Easier to just do both strtolower than worry about case insensitive comparison
            $attributes = array_map('strtolower', $attributes);
            $processedMeta = array_map('strtolower', $meta);
            // Remove any non word characters or underscores
            $processedMeta = preg_replace('/[\W|_]/', '', $processedMeta);
            // Now do the same with Contact attribute labels
            $labels = Contacts::model()->attributeLabels();
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

        if(isset($_FILES['contacts'])){
            $temp = CUploadedFile::getInstanceByName('contacts');
            $temp->saveAs('contacts.csv');
            ini_set('auto_detect_line_endings', 1); // Account for Mac based CSVs if possible
            if(file_exists('contacts.csv'))
                $fp = fopen('contacts.csv', 'r+');
            else
                throw new Exception('There was an error saving the contacts file.');
            $meta = fgetcsv($fp);
            if($meta === false)
                throw new Exception('There was an error parsing the contents of the CSV.');
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
                $_SESSION['errors'] = "Empty CSV or no metadata specified";
                $this->redirect('importExcel');
            }
            // Set our file offset for importing Contacts
            $_SESSION['offset'] = ftell($fp);
            $_SESSION['metaData'] = $meta;
            $failedContacts = fopen('failedContacts.csv', 'w+');
            fputcsv($failedContacts, $meta);
            fclose($failedContacts);
            $x2attributes = array_keys(X2Model::model('Contacts')->attributes);
            while("" === end($x2attributes)){
                array_pop($x2attributes);
            }
            // Initialize session data
            $_SESSION['importMap'] = array();
            $_SESSION['imported'] = 0;
            $_SESSION['failed'] = 0;
            $_SESSION['created'] = 0;
            $_SESSION['fields'] = X2Model::model('Contacts')->getFields(true);
            $_SESSION['x2attributes'] = $x2attributes;
            // Set up import map via the internal function
            createImportMap($x2attributes, $meta);

            $importMap = $_SESSION['importMap'];
            // We need the flipped version to display to users more easily which
            // of their fields maps to what X2 field
            $importMap = array_flip($importMap);
            // This grabs 5 sample records from the CSV to get an example of what
            // the data looks like.
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
            fclose($fp);

            $this->render('processContacts', array(
                'attributes' => $x2attributes,
                'meta' => $meta,
                'fields' => $_SESSION['fields'],
                'sampleRecords' => $sampleRecords,
                'importMap' => $importMap,
            ));
        }else{
            if(isset($_SESSION['errors'])){
                $errors = $_SESSION['errors'];
            }else{
                $errors = "";
            }
            $this->render('importExcel', array(
                'errors' => $errors,
            ));
        }
    }

    /**
     * Helper function called via AJAX to prepare the import process.
     */
    public function actionPrepareImport(){
        // Keys & attributes are our finalized import map
        if(isset($_POST['attributes']) && isset($_POST['keys'])){
            $keys = $_POST['keys'];
            $attributes = $_POST['attributes'];
            $_SESSION['tags'] = array();
            // Grab any tags that need to be added to each record
            if(isset($_POST['tags']) && !empty($_POST['tags'])){
                $tags = explode(',', $_POST['tags']);
                foreach($tags as $tag){
                    if(substr($tag, 0, 1) != "#")
                        $tag = "#".$tag;
                    $_SESSION['tags'][] = $tag;
                }
            }
            // The override allows the user to specify fixed values for certain fields
            $_SESSION['override'] = array();
            if(isset($_POST['forcedAttributes']) && isset($_POST['forcedValues'])){
                $override = array_combine($_POST['forcedAttributes'], $_POST['forcedValues']);
                $_SESSION['override'] = $override;
            }
            // Comments will log a comment on the record
            $_SESSION['comment'] = "";
            if(isset($_POST['comment']) && !empty($_POST['comment'])){
                $_SESSION['comment'] = $_POST['comment'];
            }
            // Whether to use lead routing
            $_SESSION['leadRouting'] = 0;
            if(isset($_POST['routing']) && $_POST['routing'] == 1){
                $_SESSION['leadRouting'] = 1;
            }
            $criteria = new CDbCriteria;
            $criteria->order = "importId DESC";
            $criteria->limit = 1;
            $import = Imports::model()->find($criteria);
            // Figure out which import this is so we can set up the Imports models
            // for this import.
            if(isset($import)){
                $_SESSION['importId'] = $import->importId + 1;
            }else{
                $_SESSION['importId'] = 1;
            }
            $_SESSION['createRecords'] = $_POST['createRecords'] == "checked" ? "1" : "0";
            $_SESSION['imported'] = 0;
            $_SESSION['failed'] = 0;
            $_SESSION['created'] = array();
            if(!empty($keys) && !empty($attributes)){
                // New import map is the provided data
                $importMap = array_combine($keys, $attributes);
                foreach($importMap as $key => &$value){
                    // Loop through and figure out if we need to create new fields
                    $key = Formatter::deCamelCase($key);
                    $key = preg_replace('/\[W|_]/', ' ', $key);
                    $key = mb_convert_case($key, MB_CASE_TITLE, "UTF-8");
                    $key = preg_replace('/\W/', '', $key);
                    if($value == 'createNew'){
                        $fieldLookup = Fields::model()->findByAttributes(array('modelName' => 'Contacts', 'fieldName' => $key));
                        if(isset($fieldLookup)){
                            echo "2 ".$key;
                            break;
                        }else{
                            $columnName = strtolower($key);
                            $field = new Fields;
                            $field->modelName = "Contacts";
                            $field->type = "varchar";
                            $field->fieldName = $columnName;
                            $field->required = 0;
                            $field->searchable = 1;
                            $field->relevance = "Medium";
                            $field->custom = 1;
                            $field->modified = 1;
                            $field->attributeLabel = $field->generateAttributeLabel($key);
                            if($field->save()){
                                try{
                                    $fieldType = "VARCHAR(250)";
                                    $sql = "ALTER TABLE x2_contacts ADD COLUMN `$columnName` $fieldType";
                                    $command = Yii::app()->db->createCommand($sql);
                                    $result = $command->query();
                                    $value = $columnName;
                                }catch(CDbException $e){
                                    $field->delete();
                                    throw $e;
                                }
                            }
                        }
                    }
                }
                $_SESSION['importMap'] = $importMap;
            }else{
                $_SESSION['importMap'] = array();
            }
            $cache = Yii::app()->cache;
            if(isset($cache)){
                $cache->flush();
            }
        }
    }

    /**
     * Post-processing for the import process to clean out the SESSION vars.
     */
    public function actionCleanUpImport(){
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
        if(file_exists('contacts.csv')){
            unlink('contacts.csv');
        }
    }

    /**
     * The actual meat of the import process happens here, this is called
     * recursively via AJAX to import sets of records.
     */
    public function actionImportRecords(){
        if(isset($_POST['count']) && file_exists('contacts.csv')){
            $count = $_POST['count']; // Number of records to import
            $metaData = $_SESSION['metaData'];
            $importMap = $_SESSION['importMap'];
            $fp = fopen('contacts.csv', 'r+');
            fseek($fp, $_SESSION['offset']); // Seek to the right file offset
            for($i = 0; $i < $count; $i++){
                $arr = fgetcsv($fp); // Loop through and start importing
                if($arr !== false && !is_null($arr)){
                    if(count($arr) > count($metaData)){
                        $arr = array_slice($arr, 0, count($metaData));
                    }
                    if(count($arr) < count($metaData)){
                        $arr = array_pad($arr, count($metaData), null);
                    }
                    unset($_POST);
                    $relationships = array();
                    if(!empty($metaData) && !empty($arr)){
                        $importAttributes = array_combine($metaData, $arr);
                    }else{
                        continue;
                    }
                    $model = new Contacts;
                    /*
                     * This import assumes we have human readable data in the CSV
                     * and will thus need to convert. The loop below converts link,
                     * date, and dateTime fields to the appropriate machine friendly
                     * data.
                     */
                    foreach($metaData as $attribute){
                        if($model->hasAttribute($importMap[$attribute])){
                            $fieldRecord = Fields::model()->findByAttributes(array('modelName' => 'Contacts', 'fieldName' => $importMap[$attribute]));
                            switch($fieldRecord->type){
                                case "link":
                                    $className = ucfirst($fieldRecord->linkType);
                                    if(is_numeric($importAttributes[$attribute])){
                                        $model->$importMap[$attribute] = $importAttributes[$attribute];
                                        $relationship = new Relationships;
                                        $relationship->firstType = 'Contacts';
                                        $relationship->secondType = $className;
                                        $relationship->secondId = $importAttributes[$attribute];
                                        $relationships[] = $relationship;
                                    }else{
                                        $lookup = X2Model::model(ucfirst($fieldRecord->linkType))->findByAttributes(array('name' => $importAttributes[$attribute]));
                                        if(isset($lookup)){
                                            $model->$importMap[$attribute] = $lookup->id;
                                            $relationship = new Relationships;
                                            $relationship->firstType = 'Contacts';
                                            $relationship->secondType = $className;
                                            $relationship->secondId = $lookup->id;
                                            $relationships[] = $relationship;
                                        }elseif(isset($_SESSION['createRecords']) && $_SESSION['createRecords'] == 1){
                                            $className = ucfirst($fieldRecord->linkType);
                                            if(class_exists($className)){
                                                $lookup = new $className;
                                                $lookup->name = $importAttributes[$attribute];
                                                if($lookup->hasAttribute('visibility')){
                                                    $lookup->visibility = 1;
                                                }
                                                if($lookup->hasAttribute('description')){
                                                    $lookup->description = "Generated by Contacts import.";
                                                }
                                                if($lookup->hasAttribute('createDate')){
                                                    $lookup->createDate = time();
                                                }
                                                if($lookup->save()){
                                                    if(isset($_SESSION['created'][$className])){
                                                        $_SESSION['created'][$className]++;
                                                    }else{
                                                        $_SESSION['created'][$className] = 1;
                                                    }
                                                    $model->$importMap[$attribute] = $lookup->id;
                                                    $relationship = new Relationships;
                                                    $relationship->firstType = 'Contacts';
                                                    $relationship->secondType = $className;
                                                    $relationship->secondId = $lookup->id;
                                                    $relationships[] = $relationship;
                                                }
                                            }
                                        }else{
                                            $model->$importMap[$attribute] = $importAttributes[$attribute];
                                        }
                                    }
                                    break;
                                case "dateTime":
                                case "date":
                                    if(strtotime($importAttributes[$attribute]) !== false){
                                        $model->$importMap[$attribute] = strtotime($importAttributes[$attribute]);
                                    }elseif(is_numeric($importAttributes[$attribute])){
                                        $model->$importMap[$attribute] = $importAttributes[$attribute];
                                    }else{

                                    }
                                    break;
                                default:
                                    $model->$importMap[$attribute] = $importAttributes[$attribute];
                            }

                            $_POST[$importMap[$attribute]] = $model->$importMap[$attribute];
                        }
                    }
                    // Nobody every remembers to set visibility... set it for them
                    if(empty($model->visibility) && ($model->visibility !== 0 || $model->visibility !== "0") || $model->visibility == 'Public'){
                        $model->visibility = 1;
                    }elseif($model->visibility == 'Private'){
                        $model->visibility = 0;
                    }
                    // If date fields were provided, do not create new values for them
                    if(!empty($model->createDate) || !empty($model->lastUpdated) || !empty($model->lastActivity)){
                        $model->disableBehavior('X2TimestampBehavior');
                        if(empty($model->createDate)){
                            $model->createDate = time();
                        }
                        if(empty($model->lastUpdated)){
                            $model->lastUpdated = time();
                        }
                        if(empty($model->lastActivity)){
                            $model->lastActivity = time();
                        }
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
                    if($model->validate()){
                        if(!empty($model->id)){
                            $lookup = Contacts::model()->findByPk($model->id);
                            if(isset($lookup)){
                                Relationships::model()->deleteAllByAttributes(array('firstType' => 'Contacts', 'firstId' => $lookup->id));
                                Relationships::model()->deleteAllByAttributes(array('secondType' => 'Contacts', 'secondId' => $lookup->id));
                                $lookup->delete();
                                unset($lookup);
                            }
                        }
                        // Save our model & create the import records and relationships
                        if($model->save()){
                            $importLink = new Imports;
                            $importLink->modelType = "Contacts";
                            $importLink->modelId = $model->id;
                            $importLink->importId = $_SESSION['importId'];
                            $importLink->timestamp = time();
                            $importLink->save();
                            $_SESSION['imported']++;
                            foreach($relationships as $relationship){
                                $relationship->firstId = $model->id;
                                if($relationship->save()){
                                    $importLink = new Imports;
                                    $importLink->modelType = $relationship->secondType;
                                    $importLink->modelId = $relationship->secondId;
                                    $importLink->importId = $_SESSION['importId'];
                                    $importLink->timestamp = time();
                                    $importLink->save();
                                }
                            }
                            // Add all listed tags
                            foreach($_SESSION['tags'] as $tag){
                                $tagModel = new Tags;
                                $tagModel->taggedBy = 'Import';
                                $tagModel->timestamp = time();
                                $tagModel->type = 'Contacts';
                                $tagModel->itemId = $model->id;
                                $tagModel->tag = $tag;
                                $tagModel->itemName = $model->name;
                                $tagModel->save();
                            }
                            // Log a comment if one was requested
                            if(!empty($_SESSION['comment'])){
                                $action = new Actions;
                                $action->associationType = "contacts";
                                $action->associationId = $model->id;
                                $action->actionDescription = $_SESSION['comment'];
                                $action->createDate = time();
                                $action->updatedBy = Yii::app()->user->getName();
                                $action->lastUpdated = time();
                                $action->complete = "Yes";
                                $action->completeDate = time();
                                $action->completedBy = Yii::app()->user->getName();
                                $action->type = "note";
                                $action->visibility = 1;
                                $action->reminder = "No";
                                $action->priority = "Low";
                                if($action->save()){
                                    $importLink = new Imports;
                                    $importLink->modelType = "Actions";
                                    $importLink->modelId = $action->id;
                                    $importLink->importId = $_SESSION['importId'];
                                    $importLink->timestamp = time();
                                    $importLink->save();
                                }
                            }
                        }
                    }else{
                        // If the import failed, then put the data into the failedContacts CSV for easy recovery.
                        $failedContacts = fopen('failedContacts.csv', 'a+');
                        fputcsv($failedContacts, $arr);
                        fclose($failedContacts);
                        $_SESSION['failed']++;
                    }
                }else{
                    echo json_encode(array(
                        '1',
                        $_SESSION['imported'],
                        $_SESSION['failed'],
                        json_encode($_SESSION['created']),
                    ));
                    return;
                }
            }
            // Change the offset to wherever we got to and continue.
            $_SESSION['offset'] = ftell($fp);
            echo json_encode(array(
                '0',
                $_SESSION['imported'],
                $_SESSION['failed'],
                json_encode($_SESSION['created']),
            ));
        }
    }

    /**
     * Deprecated function which now just points to "exportContacts" for posterity
     * @deprecated
     */
    public function actionExport(){
        $this->redirect('exportContacts');
        /* $this->exportToTemplate();
          $this->render('export', array(
          )); */
    }

    /*
      protected function exportToTemplate() {
      ini_set('memory_limit', -1);
      $contacts = X2Model::model('Contacts')->findAll();
      $file = 'contact_export.csv';
      $fp = fopen($file, 'w+');
      if(!empty($contacts) && !empty($contacts[0]) && is_array($contacts[0]->attributes)){
      fputcsv($fp,array_keys($contacts[0]->attributes));
      $fields=X2Model::model('Contacts')->getFields();
      foreach($contacts as $contact) {
      foreach($fields as $field){
      $fieldName=$field->fieldName;
      if($field->type=='date' || $field->type=='dateTime'){
      if(is_numeric($contact->$fieldName))
      $contact->$fieldName=date("c",$contact->$fieldName);
      }elseif($field->type=='link'){
      try{
      $linkModel=X2Model::model($field->linkType)->findByPk($contact->$fieldName);
      if(isset($linkModel) && $linkModel->hasAttribute('name')){
      $contact->$fieldName=$linkModel->name;
      }
      }catch(Exception $e){

      }
      }elseif($fieldName=='visibility'){
      $contact->$fieldName=$contact->$fieldName==1?'Public':'Private';
      }
      }
      fputcsv($fp, $contact->attributes);
      }
      }
      fclose($fp);
      }
     */

    /**
     * Contacts export function which generates human friendly data and also
     * works for exporting particular lists of Contacts
     * @param int $listId The ID of the list to be exported, if null it will be all Contacts
     */
    public function actionExportContacts($listId = null){
        unset($_SESSION['contactExportFile'], $_SESSION['exportContactCriteria'], $_SESSION['contactExportMeta']);
        if(is_null($listId)){
            $file = "contact_export.csv";
            $listName = CHtml::link(Yii::t('contacts', 'All Contacts'), array('/contacts/contacts/index'), array('style' => 'text-decoration:none;'));
        }else{
            $list = X2List::load($listId);
            $_SESSION['exportContactCriteria'] = $list->queryCriteria();
            $file = "list".$listId.".csv";
            $listName = CHtml::link(Yii::t('contacts', 'List')." $listId: ".$list->name, array('/contacts/contacts/list','id'=>$listId), array('style' => 'text-decoration:none;'));
        }
        $_SESSION['contactExportFile'] = $file;
        $attributes = X2Model::model('Contacts')->attributes;
        $meta = array_keys($attributes);
        if(isset($list)){
            // Figure out gridview settings to export those columns
            $gridviewSettings = json_decode(Yii::app()->params->profile->gridviewSettings, true);
            if(isset($gridviewSettings['contacts_list'.$listId])){
                $tempMeta = array_keys($gridviewSettings['contacts_list'.$listId]);
                $meta = array_intersect($tempMeta, $meta);
            }
        }
        // Set up metadata
        $_SESSION['contactExportMeta'] = $meta;
        $fp = fopen($file, 'w+');
        fputcsv($fp, $meta);
        fclose($fp);
        $this->render('exportContacts', array(
            'listId' => $listId,
            'listName' => $listName,
        ));
    }

    /**
     * An AJAX called function which exports Contact data to a CSV via pagination
     * @param int $page The page of the data provider to export
     */
    public function actionExportSet($page){
        $file = $_SESSION['contactExportFile'];
        $fields = X2Model::model('Contacts')->getFields();
        $fp = fopen($file, 'a+');
        // Load data provider based on export criteria
        $dp = new CActiveDataProvider('Contacts', array(
                    'criteria' => isset($_SESSION['exportContactCriteria']) ? $_SESSION['exportContactCriteria'] : array(),
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
        foreach($records as $record){
            foreach($fields as $field){
                $fieldName = $field->fieldName;
                if($field->type == 'date' || $field->type == 'dateTime'){
                    if(is_numeric($record->$fieldName))
                        $record->$fieldName = Formatter::formatLongDateTime($record->$fieldName);
                }elseif($field->type == 'link'){
                    try{
                        $linkModel = X2Model::model($field->linkType)->findByPk($record->$fieldName);
                        if(isset($linkModel) && $linkModel->hasAttribute('name')){
                            $record->$fieldName = $linkModel->name;
                        }
                    }catch(Exception $e){

                    }
                }elseif($fieldName == 'visibility'){
                    $record->$fieldName = $record->$fieldName == 1 ? 'Public' : 'Private';
                }
            }
            // Enforce metadata to ensure accuracy of column order, then export.
            $combinedMeta = array_combine($_SESSION['contactExportMeta'], $_SESSION['contactExportMeta']);
            $tempAttributes = array_intersect_key($record->attributes, $combinedMeta);
            $tempAttributes = array_merge($combinedMeta, $tempAttributes);
            fputcsv($fp, $tempAttributes);
        }

        unset($dp);

        fclose($fp);
        if($page + 1 < $pageCount){
            echo $page + 1;
        }
    }

    public function actionDelete($id){
        if(Yii::app()->request->isPostRequest){
            $model = $this->loadModel($id);
            $model->clearTags();
            $model->delete();

            Actions::model()->deleteAllByAttributes(array('associationType' => 'contacts', 'associationId' => $id));
        } else {
            throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    public function actionSubscribe(){
        if(isset($_POST['ContactId']) && isset($_POST['Checked'])){
            $id = $_POST['ContactId'];

            $checked = json_decode($_POST['Checked']);

            if($checked){ // user wants to subscribe to this contact
                $result = Yii::app()->db->createCommand()
                        ->select()
                        ->from('x2_subscribe_contacts')
                        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $id, 'user_id' => Yii::app()->user->id))
                        ->queryAll();
                if(empty($result)){ // ensure user isn't already subscribed to this contact
                    Yii::app()->db->createCommand()->insert('x2_subscribe_contacts', array('contact_id' => $id, 'user_id' => Yii::app()->user->id));
                }
            }else{ // user wants to unsubscribe to this contact
                $result = Yii::app()->db->createCommand()
                        ->select()
                        ->from('x2_subscribe_contacts')
                        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $id, 'user_id' => Yii::app()->user->id))
                        ->queryAll();
                if(!empty($result)){ // ensure user is subscribed before unsubscribing
                    Yii::app()->db->createCommand()->delete('x2_subscribe_contacts', array('contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $id, ':user_id' => Yii::app()->user->id));
                }
            }
        }
    }

    public function actionQtip($id){
        $contact = $this->loadModel($id);

        $this->renderPartial('qtip', array('contact' => $contact));
    }

    public function actionCleanFailedLeads(){
        $file = 'failed_leads.csv';

        if(file_exists($file)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize($file));
            ob_clean();
            flush();
            readfile($file);
            unlink($file);
        }
    }

}
