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
 * 2nd generation REST API for X2Engine.
 *
 * Has the following conventions:
 *
 * General:
 * 
 * - The bodies of all requests sent and all responses received to/from this
 *   controller shall be JSON-encoded, not URL-encoded as in traditional POST
 *   (i.e. as if to mimic form submission)
 * - HTTP Basic authentication in use. Note, this allows direct browser
 *   explorability via entering the username and API key when prompted.
 * - The status code is to be included in the "status" property of the response,
 *   if it is not in the "success" category.
 * - The "Content-Type" header in all responses shall be "application/json"
 *
 * Model-Centric (Active Record) Actions:
 * - If the request is successful, the returned object should not be within an
 *   envelope.
 * - All URLs referring to operations on existing records shall end in ".json"
 * - In responses with errors, the "errors" property is to contain the
 *   validation errors as returned from {@link CActiveRecord.getErrors()}
 *
 * @property CActiveDataProvider $dataProvider A data provider for performing
 *  searches via API using special underscore-prefixed query parameters: _page,
 *  _limit and _order.
 * @property boolean $enabled Returns true or false based on whether API access
 *  is enabled.
 * @property array $jpost (read-only) JSON data posted to the server. This 
 *  should be used instead of the superglobal $_POST because PHP does not
 *  natively support parsing the request body into $_POST unless it's URL-form
 *  -encoded data.
 * @property integer $maxPageSize Maximum page size.
 * @property X2Model $model Active record instance, when/where applicable
 * @property array $reservedParams Underscore-prefixed parameters used by the API
 * @property string $responseBody (write-only) The body of the response to be sent
 * @property Api2Settings $settings (Platinum Edition only) Advanced API settings
 * @property X2Model $staticModel Static model instance, when/where applicable
 * @package application.controllers
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2Controller extends CController {

    const ENABLED = true;
    const MAX_PAGE_SIZE = 1000;

    const FIND_DELIM = ';';
    const FIND_EQUAL = '=';

    /**
     * Stores {@link post}
     * @var array
     */
    private $_jpost;

    /**
     * Active record model currently being operated on, if applicable.
     * @var X2Model
     */
    private $_model;

    /**
     * Stores {@link reqHeaders}
     * @var array
     */
    private $_reqHeaders;

    /**
     * If the "model" parameter is specified, this should be a "static" instance
     * of that model.
     *
     * @var X2Model
     */
    private $_staticModel;

    /**
     * Stores {@link user}
     * @var type 
     */
    private $_user;

    /////////////
    // ACTIONS //
    /////////////
    //
    // The following methods define the available API functionality:

    /**
     * "Hello world" action.
     *
     * Prints application and network info
     */
    public function actionAppInfo() {
        $this->response['message'] = "Welcome to the X2Engine REST API!";
        $this->response['name'] = Yii::app()->settings->appName;
        $this->response['version'] = Yii::app()->params->version;
        $this->response['edition'] = Yii::app()->editionLabel;
        $this->response['buildDate'] = Yii::app()->params->buildDate;
        $this->response['clientAddress'] = Yii::app()->request->userHostAddress;
        $this->response['serverName'] = $_SERVER['SERVER_NAME'];
        $this->response['baseUrl'] = Yii::app()->getRequest()->getBaseUrl();
    }

    /**
     * Retrieve the number of models of a specific class or matching query criteria
     *
     * @param string $_class Model name
     * @param array $_findBy Model query criteria
     */
    public function actionCount($_class,$_findBy=null) {
        $staticModel = $this->getStaticModel();

        if(!empty($_findBy)) {
            // Use case: count models by uniquely identifying attributes
            $attributeConditions = $this->findConditions(
                $_findBy,
                $staticModel->attributes
            );
            $criteria = new CDbCriteria ();
            foreach ($attributeConditions as $field => $value)
                $criteria->compare ($field, $value);
            $count = $this->getDataProvider(null, $criteria)->getTotalItemCount();
        } else {
            // Use case: count all models
            $count = $this->getDataProvider()->getTotalItemCount();
        }
        $this->responseBody = $count;
    }

    /**
     * Responds with dropdown list metadata
     *
     * @param integer $_id
     */
    public function actionDropdowns($_id=null) {
        if($_id !== null){
            // Look for a specific dropdown record
            if(!(($dropdown = Dropdowns::model()->findByPk($_id)) instanceof Dropdowns))
                $this->send(404);
            $dropdown->options = json_decode($dropdown->options, 1);
            $this->responseBody = $dropdown;
        } else {
            // Query dropdowns
            $this->responseBody = array_map(function($d){
                $d->options = json_decode($d->options, 1);
                return $d;
            }, $this->getDataProvider('Dropdowns')->getData());
        }
    }

    /**
     * Returns an array of role-specific field-level permissions
     */
    public function actionFieldPermissions($_class) {
        $this->responseBody = $this->staticModel->fieldPermissions;
    }

    /**
     * Access fields for a given X2Model class
     * 
     * @param string $_class Model name
     * @param string $_fieldName Field name
     */
    public function actionFields($_class,$_fieldName=null) {
        $c = new CDbCriteria;
        $c->compare('modelName',$_class);
        if(!empty($_fieldName))
            $c->compare('fieldName',$_fieldName);
        $dp = $this->getDataProvider('Fields',$c);
        $dp->pagination = false; // ALL fields
        $fields = $dp->getData();
        if(!empty($_fieldName)) {
            if(count($fields) === 0 && $dp->pagination->pageSize !== 0)
                $this->send(404,"Model $_class does not have a field named "
                        . "\"$_fieldName\"");
            $this->responseBody = reset($fields);
        } else
            $this->responseBody = $fields;
    }

    /**
     * Action for the creation of "hooks" (see {@link ApiHook})
     *
     * @param integer $_id ID of the hook.
     * @param string $_class Subclass of {@link X2Model} to which the hook pertains
     */
    public function actionHooks($_id=null,$_class=null) {
        $method = Yii::app()->request->getRequestType();
        if($method=='DELETE') {
            $hook = ApiHook::model()->findByPk($_id);
            if(!$hook instanceof ApiHook)
                $this->send(404,'"Hook not found." -Smee');
            elseif($hook->userId != Yii::app()->getSuId())
                $this->send(403,'You cannot delete other API users\' hooks in X2Engine.');
            $hook->setScenario('delete.remote');
            if($hook->delete()) {
                $this->sendEmpty("Successfully unsubscribed from hook with "
                        . "return URL {$hook->target_url}.");
            }
        } else { // POST (will respond to all other methods with 405)
            if($_id !== null)
                $this->send(405,'Cannot manipulate preexisting hooks with POST.');
            $hook = new ApiHook;
            $hook->attributes = $this->getJpost();
            $hook->userId = Yii::app()->getSuId();
            if(!empty($_class))
                $hook->modelName = get_class($this->staticModel);
            if(!$hook->validate('event')) {
                $this->send(429, "The maximum number of hooks ({$maximum}) has "
                . "been reached for events of this type.");
            }
            if(!$hook->validate()) {
                $this->response['errors'] = $hook->errors;
                $this->send(422);
            }
            if($hook->save()) {
                $this->response->httpHeader['Location'] = 
                        $this->createAbsoluteUrl('/api2/hooks',array(
                            '_id' => $hook->id
                        ));
                $this->responseBody = $hook;
                $this->send(201);
            } else {
                $this->send(500,'Could not save hook due to unexpected '
                        . 'internal server error.');
            }
        }
    }

    /**
     * Basic operations on an X2Engine model.
     *
     * @param string $class
     * @param integer $id
     * @param array $modelInput Model parameters, i.e. if doing a lookup
     */
    public function actionModel($_class,$_id=null,$_findBy=null) {
        $method = Yii::app()->request->getRequestType();

        // Run extra special stuff for the Actions class
        $this->kludgesForActions();

        switch($method) {
            case 'GET':
                if((!empty($_id) && ctype_digit((string) $_id)) ||
                        !empty($_findBy)) {
                    // Use case: directly access the model by ID or uniquely
                    // identifying attributes
                    $this->responseBody = $this->model;
                } else {
                    // Use case: if no model was directly accessed by ID,
                    // perform a search using the available parameters
                    $this->responseBody = $this->getDataProvider()->getData();
                }
                break;
            case 'PATCH':
            case 'POST':
            case 'PUT':
                // Additional check for request validity
                if($method == 'POST') {
                    if(!(empty($_id) && empty($_findBy))) // POST on an existing record
                        $this->send(400,'POST should be used for creating new '
                                . 'records and cannot be used to update an '
                                . 'existing model. PUT or PATCH should be used '
                                . 'instead.');
                    // Instantiate a new active record model, but go through
                    // getStaticModel to check for class validity:
                    $class = get_class($this->getStaticModel());
                    if (!isset ($this->_model)) $this->model = new $class;
                }

                // Set attributes
                $this->setModelAttributes();
                
                // Validate/save
                $saved = false;
                if($method == 'POST') {
                    if($this->model->asa('DuplicateBehavior') && $this->model->checkForDuplicates()){
                        $duplicates = $this->model->getDuplicates();
                        $oldest = $duplicates[0];
                        $fields = $this->model->getFields(true);
                        foreach ($fields as $field) {
                            if (!in_array($field->fieldName,
                                            $this->model->MergeableBehavior->restrictedFields)
                                    && !is_null($this->model->{$field->fieldName})) {
                                if ($field->fieldName === 'assignedTo' &&
                                        !in_array($oldest->{$field->fieldName}, array('Anyone', ''))) {
                                    // Don't resassign if the duplicate was already assigned
                                    continue;
                                }
                                if ($field->type === 'text' && !empty($oldest->{$field->fieldName})) {
                                    $oldest->{$field->fieldName} .= "\n--\n" . $this->model->{$field->fieldName};
                                } else {
                                    $oldest->{$field->fieldName} = $this->model->{$field->fieldName};
                                }
                            }
                        }
                        $this->model = $oldest;
                    }

                    // Save the model, check for errors, and respond if necessary.
                    $saved = $this->model->save( !$this->settings->rawInput );
                    if($this->model->hasErrors()) {
                        $this->response['errors'] = $this->model->errors;
                        $this->send(422,"Model failed validation.");
                    }
                } else {
                    // Update existing
                    $attributes = array_intersect(array_keys($this->jpost),
                            $this->staticModel->attributeNames());
                    if( $this->settings->rawInput || $this->model->validate($attributes)) {

                        if ($this->model->asa('FlowTriggerBehavior') &&
                                $this->model->asa('FlowTriggerBehavior')->enabled) {
                            $this->model->enableUpdateTrigger();
                        }
                        $saved = $this->model->update($attributes);
                        if ($this->model->asa('FlowTriggerBehavior') &&
                                $this->model->asa('FlowTriggerBehavior')->enabled) {

                            $this->model->disableUpdateTrigger();
                        }
                    }
                }

                // Check for errors and respond if necessary.
                if($this->model->hasErrors()) {
                    $this->response['errors'] = $this->model->errors;
                    $this->send(422,"Model failed validation.");
                } else if(!$saved) {
                    $this->send(500,"Model passed validation but still could not "
                            . "be saved due to an unexpected internal server error.");
                }

                // Set body
                $this->responseBody = $this->model;

                // Add resource location header for a newly created record
                // and send with 201 status
                if($method == 'POST') {
                    $this->response->httpHeader['Location'] = $this->createAbsoluteUrl('/api2/model', array(
                        '_class' => $class,
                        '_id' => $this->model->id
                    ));
                    $this->send(201,"Model of class \"$class\" created successfully.");
                }
                break;
            case 'DELETE':
                if($this->model->delete()) {
                    $this->sendEmpty("Model of class \"$_class\" with id=$_id "
                            . "deleted successfully.");
                }
                else
                    $this->send(500);
                break;
        }
    }

    /**
     * Responds with a JSON-encoded list of model classes.
     *
     * @param integer $partialSupport 1 to include partially-supported models,
     *  0 to include only fully-supported models.
     */
    public function actionModels($partialSupport=1) {
        // To obtain all models: iterate through modules
        $modelNames = X2Model::getModelNames();
        // Partially-supported models
        $partial = array(
            'Actions'=>Yii::t('app','Actions'),
            'Docs'=>Yii::t('app','Docs'),
            'Groups'=>Yii::t('app','Groups'),
            'Media'=>Yii::t('app','Media'),
            'Quote'=>Yii::t('app','Quotes'),
            'X2List'=>Yii::t('app','Contact Lists')
        );
        if((boolean) (integer) $partialSupport) {
            $modelNames = array_unique(array_merge($modelNames,$partial));
        } else {
            $modelNames = array_diff($modelNames,$partial);
        }
        asort($modelNames);

        $models = array();
        foreach($modelNames as $modelName => $title) {
            $attributes = X2Model::model($modelName)->attributeNames();
            $models[] = compact('modelName','title','attributes');
        }

        $this->responseBody = $models;
    }

    /**
     * Action for viewing or modifying relationships on a model.
     * 
     * @param type $_class
     * @param type $_id
     * @param type $_relatedId
     */
    public function actionRelationships($_class=null,$_id=null,$_relatedId=null) {
        $method = Yii::app()->request->requestType;

        $relationship = null;
        if($_relatedId !== null) {
            $relationship = Relationships::model()->findByPk($_relatedId);
            if(!($relationship instanceof Relationships)) {
                $this->send(404,"Relationship with id=$_relatedId not found.");
            }
            // Check whether the relationship is actually attached to this model:
            if($_class !== null
                    && $_id !== null
                    && $relationship->firstId != $this->model->id
                    && $relationship->secondId != $this->model->id
                    && $relationship->firstType != $_class
                    && $relationship->secondType != $_class) {
                $this->response->httpHeader['Location'] = $this->createAbsoluteUrl('/api2/relationships', array(
                    '_class' => $relationship->firstType,
                    '_id' => $relationship->firstId,
                    '_relatedId' => $relationship->id
                ));
                $this->send(303,"Specified relationship does not correspond "
                        . "to $_class record $_id.");
            }
        }

        switch($method) {
            case 'GET':
                if($relationship !== null) {
                    // Get an individual relationship record. Also, include the
                    // resource URL of the related model.
                    $which = $relationship->firstId == $_id
                            && $relationship->firstType == $_class
                            ? 'second' : 'first';
                    $relId = $which.'Id';
                    $relType = $which.'Type';
                    $this->response->httpHeader['Location'] = $this->createAbsoluteUrl('/api2/model',array(
                        '_class' => $relationship->$relType,
                        '_id' => $relationship->$relId
                    ));
                    $this->responseBody = $relationship;
                } else {
                    // Query relationships on a model.
                    $criteria = null;
                    if(!($relationship instanceof Relationships)) {
                        // Both ingoing and outgoing relationships.
                        $from = new CDbCriteria;
                        $to = new CDbCriteria;
                        $from->compare('firstType',$_class);
                        $from->compare('firstId',$_id);
                        $to->compare('secondType',$_class);
                        $to->compare('secondId',$_id);
                        $criteria = new CDbCriteria;
                        $criteria->mergeWith($from,'OR');
                        $criteria->mergeWith($to,'OR');
                    }
                    $this->responseBody = $this
                            ->getDataProvider('Relationships',$criteria)
                            ->getData();
                }
                break;
            case 'PATCH':
            case 'POST':
            case 'PUT':
                if(!$relationship instanceof Relationships) {
                    if($method !== 'POST') {
                        // Cannot PUT on a nonexistent model
                        $this->send(405,"Method \"POST\" is required to create new relationships.");
                    }
                    $relationship = new Relationships;
                }
                // Scenario kludge that adds special validation rule for the
                // Relations model class, which dicates that it must point to
                // an existing record on both ends:
                $relationship->setScenario('api');
                $relationship->setAttributes($this->jpost);
                // Set missing attributes, if any:
                if(empty($relationship->firstType)) {
                    $relationship->firstType = $_class;
                    $relationship->firstId = $_id;
                } elseif (empty($relationship->secondType)) {
                    $relationship->secondType = $_class;
                    $relationship->secondId = $_id;
                }
                if(!$relationship->save()) {
                    // Validation errors
                    $this->response['errors'] = $relationship->errors;
                    $this->send(422);
                } else {
                    $this->responseBody = $relationship;
                    if($method === 'POST'){
                        // Set location header and respond with "201 Created"
                        $this->response->httpHeader['Location'] = $this->createAbsoluteUrl('/api2/relationships', array(
                            '_class' => $_class,
                            '_id' => $_id,
                            '_relatedId' => $_relatedId
                        ));
                        $this->send(201,"Relationship created successfully");
                    }
                }
                break;
            case 'DELETE':
                if(!($relationship instanceof Relationships)) {
                    $this->send(400,"Cannot delete relationships without specifying which one to delete.");
                }
                if($relationship->delete()) {
                    $this->sendEmpty("Relationship $_relatedId deleted successfully.");
                } else {
                    $this->send(500,"Failed to delete relationship #$_relatedId. It may have been deleted already.");
                }
                break;
        }
    }

    /**
     * Query, add, or remove tags on a model.
     *
     * The body sent to this method in POST/PUT should be a JSON-encoded array
     * of tag names.
     *
     * @param string $_class The active record model class being tagged
     * @param integer $_id The ID of the active record being tagged
     * @param type $_relatedId The ID of a tag itself
     */
    public function actionTags($_class=null,$_id=null,$_tagName=null) {
        $method = Yii::app()->request->requestType;

        // Get the current tag being acted upon, if applicable:
        $tag = null;
        if($_class !== null && $_id !== null && $_tagName != null){
            // Use case: operating on a tag of a specific model by its name in
            // order to get or delete it.
            $tag = Tags::model()->findByAttributes(array(
                'type' => $_class,
                'itemId' => $this->model->id, // Look up model
                'tag' => '#'.ltrim($_tagName, '#') // Auto-prepend "#" if missing
            ));
            if(!($tag instanceof Tags))
                $this->send(404,"Tag \"$_tagName\" not found on $_class id=$_id.");
        }

        switch($method){
            case 'GET':
                if(!($tag instanceof Tags)){
                    // Use case: no tag ID could be found, either directly or in
                    // association with a X2Model model record. Search tags:
                    $criteria = new CDbCriteria();
                    if($_class !== null && !isset($_GET['type']))
                        $criteria->compare('type',$_class);
                    if($_id !== null && !isset($_GET['itemId']))
                        $criteria->compare('itemId',$_id);
                    if($_tagName !== null && !isset($_GET['tag']))
                        $criteria->compare('tag','#'.ltrim($_tagName, '#'));
                    $this->responseBody = $this
                            ->getDataProvider('Tags',$criteria)
                            ->getData();
                    $this->send(200);
                }else{
                    // Get an individual tag by name, one way or another:
                    $this->responseBody = $tag;
                }
                break;
            case 'POST':
                if($tag instanceof Tags) {
                    // This is not the appropriate way to modify tags.
                    $this->send(405,"Tags cannot be individually modified.");
                }
                // Add tags using the native method in TagBehavior:
                $this->model->addTags($this->jpost);
                $this->response['message'] = 'Tags added successfully.';
                break;
            case 'DELETE':
                if(!($tag instanceof Tags)) {
                    $this->send(400,"Tag name must be specified when deleting a tag.");
                }
                if($this->model->removeTags('#'.ltrim($_tagName,'#'))) {
                    $this->sendEmpty("Tag #$_tagName deleted from $_class id=$_id.");
                } else {
                    $this->send(500);
                }
                break;
        }
    }

    /**
     * Hello-world error action; test for support of unconventional status code.
     */
    public function actionTeapot(){
        $this->send(418, "I'm a teapot.");
    }

    /**
     * Prints user metadata.
     * @param type $_id
     */
    public function actionUsers($_id=null) {
        if($_id !== null) {
            if((bool) ($user=User::model()->findByPk($_id))) {
                $this->responseBody = $user;
            } else {
                $this->send(404,"User with specified ID $_id not found.");
            }
        } else {
            $this->responseBody = $this->getDataProvider('User')->getData();
        }
    }

    /**
     * Weblead form API endpoint to allow weblead submission integration when
     * using API-based webforms, including lead routing, tracking, weblead and
     * assigned user email notifications, duplicate detection, and new
     * weblead X2Workflow triggering.
     *
     * The body sent to this method in POST should be a JSON-encoded array.
     */
    public function actionWeblead() {
        // Set staticModel and model properties for Contact creation, then assign attributes
        $this->_staticModel = X2Model::model ('Contacts');
        $this->model = new $this->_staticModel;
        $webForm = null;
        if (isset ($this->jpost['webFormId'])) {
            // Prepare webform parameters
            $webForm = WebForm::model()->findByPk($this->jpost['webFormId']);
            $extractedParams['leadSource'] = $webForm->leadSource;
            $extractedParams['generateLead'] = $webForm->generateLead;
            $extractedParams['generateAccount'] = $webForm->generateAccount;
            $extractedParams['userEmailTemplate'] = $webForm->userEmailTemplate;
            $extractedParams['webleadEmailTemplate'] = $webForm->webleadEmailTemplate;
        }
        $this->setModelAttributes();
        if (empty($this->model->trackingKey)) {
            $this->model->trackingKey = Contacts::getNewTrackingKey();
        }

        $newRecord = true;
        if($this->model->asa('DuplicateBehavior') && $this->model->checkForDuplicates()){
            $duplicates = $this->model->getDuplicates();
            $oldest = $duplicates[0];
            $fields = $this->model->getFields(true);
            foreach ($fields as $field) {
                if (!in_array($field->fieldName,
                                $this->model->MergeableBehavior->restrictedFields)
                        && !is_null($this->model->{$field->fieldName})) {
                    if ($field->fieldName === 'assignedTo' &&
                            !in_array($oldest->{$field->fieldName}, array('Anyone', ''))) {
                        // Don't resassign if the duplicate was already assigned
                        continue;
                    }
                    if ($field->type === 'text' && !empty($oldest->{$field->fieldName})) {
                        $oldest->{$field->fieldName} .= "\n--\n" . $this->model->{$field->fieldName};
                    } else {
                        $oldest->{$field->fieldName} = $this->model->{$field->fieldName};
                    }
                }
            }
            $this->model = $oldest;
            $newRecord = $this->model->isNewRecord;
        }
        if($newRecord){
            $this->model->createDate = $now;
            if (!isset($this->jpost['assignedTo'])) {
                // Allow assignedTo to be directly set if desired, otherwise use lead routing
                $this->model->assignedTo = $this->getNextAssignee();
            }
        }

        // Save the model, check for errors, and respond if necessary.
        $saved = $this->model->save( !$this->settings->rawInput );
        if($this->model->hasErrors()) {
            $this->response['errors'] = $this->model->errors;
            $this->send(422,"Model failed validation.");
        }

        // Check for fingerprint and attributes
        // if there's not an anonyomous contact, then the fingerprint match
        // was for an actual contact.
        if (Yii::app()->contEd('pla') && Yii::app()->settings->enableFingerprinting &&
            isset ($this->jpost['fingerprint'])) {
            $attributes = (isset($this->jpost['fingerprintAttributes']))?
                json_decode($this->jpost['fingerprintAttributes'], true) : array();
            $anonContact = AnonContact::model ()
                ->findByFingerprint ($this->jpost['fingerprint'], $attributes);
            if ($anonContact !== null) {
                $this->model->mergeWithAnonContact ($anonContact);
            } else {
                $this->model->setFingerprint ($this->jpost['fingerprint'], $attributes);
            }
        }

        if ($extractedParams['generateLead'])
            self::generateLead ($this->model, $extractedParams['leadSource']);
        if ($extractedParams['generateAccount'])
            self::generateAccount ($this->model);

        // Create an Action, Event, Notification for the new web lead
        $this->createWebleadAction($this->model);
        $this->createWebleadEvent($this->model);

        if($this->model->assignedTo != 'Anyone' && $this->model->assignedTo != '') {
            $this->createWebleadNotification($this->model);
        }

        // Read selected Tags and trigger X2Workflows on new weblead
        if (!isset($this->jpost['tags']) || empty($this->jpost['tags']))
            $tags = array();
        else
            $tags = explode(',', $this->jpost['tags']);

        X2Flow::trigger('WebleadTrigger', array(
            'model' => $this->model,
            'tags' => $tags,
        ));

        if (Yii::app()->contEd('pro')) {
            // email to send from
            $emailFrom = Credentials::model()->getDefaultUserAccount(
                Credentials::$sysUseId['systemResponseEmail'], 'email');
            if($emailFrom == Credentials::LEGACY_ID)
                $emailFrom = array(
                    'name' => Yii::app()->settings->emailFromName,
                    'address' => Yii::app()->settings->emailFromAddr
                );
        }

        if($this->model->assignedTo != 'Anyone' && $this->model->assignedTo != '') {
            $profile = Profile::model()->findByAttributes(
                array('username' => $this->model->assignedTo));

            /* send user that's assigned to this weblead an email if the user's email
            address is set and this weblead has a user email template */
            if($profile !== null && !empty($profile->emailAddress)){

                if (Yii::app()->contEd('pro') &&
                    $extractedParams['userEmailTemplate']) {

                    /* We'll be using the user's own email account to send the
                    web lead response (since the contact has been assigned) and
                    additionally, if no system notification account is available,
                    as the account for sending the notification to the user of
                    the new web lead (since $emailFrom is going to be modified,
                    and it will be that way when this code block is exited and the
                    time comes to send the "welcome aboard" email to the web lead)*/
                    $emailFrom = Credentials::model()->getDefaultUserAccount(
                        $profile->user->id, 'email');
                    if($emailFrom == Credentials::LEGACY_ID)
                        $emailFrom = array(
                            'name' => $profile->fullName,
                            'address' => $profile->emailAddress
                        );

                    $this->sendUserNotificationEmail($this->model, $profile->emailAddress, $emailFrom, $extractedParams['userEmailTemplate']);
                } else {
                    $emailFrom = Credentials::model()->getDefaultUserAccount(
                        Credentials::$sysUseId['systemNotificationEmail'], 'email');
                    if($emailFrom == Credentials::LEGACY_ID)
                        $emailFrom = array(
                            'name' => $profile->fullName,
                            'address' => $profile->emailAddress
                        );
                    $this->sendLegacyUserNotificationEmail($this->model, $profile->emailAddress, $emailFrom);
                }
            }

        }

        /* send new weblead an email if we have their email address and this web
        form has a weblead email template */
        if(Yii::app()->contEd('pro') && $extractedParams['webleadEmailTemplate'] &&
           !empty($this->model->email)) {
            $this->sendWebleadNotificationEmail($this->model, $emailFrom, $extractedParams['webleadEmailTemplate']);
        }

        if (!empty($tags)){
            X2Flow::trigger('RecordTagAddTrigger', array(
                'model' => $this->model,
                'tags' => $tags,
            ));
        }

        // Set body
        $this->responseBody = $this->model;

        // Add resource location header for a newly created record
        // and send with 201 status
        $this->response->httpHeader['Location'] = $this->createAbsoluteUrl('/api2/model', array(
            '_class' => 'Contacts',
            '_id' => $this->model->id
        ));
        $this->send(201,"Model of class \"$class\" created successfully.");
    }

    /**
     * Returns a list of fields in the format required by Zapier's custom action
     * fields feature.
     *
     * @param type $_class
     */
    public function actionZapierFields($_class,$_permissionLevel=1) {
        $fieldModels = $this->staticModel->fields;
        $fieldPermissions = $this->staticModel->fieldPermissions;
        $fields = array();
        $typeMapping = array(
            'assignment' => 'unicode',
            'boolean' => 'bool',
            'credentials' => 'int',
            'currency' => 'unicode',
            'date' => 'datetime',
            'dateTime' => 'datetime',
            'dropdown' => 'unicode',
            'email' => 'unicode',
            'integer' => 'int',
            'optionalAssignment' => 'unicode',
            'percentage' => 'float',
            'phone' => 'unicode',
            'rating' => 'int',
            'text' => 'text',
            'url' => 'unicode',
            'varchar' => 'unicode',
            'visibility' => 'int',
            '' => 'unicode'
        );
        foreach($fieldModels as $field) {
            if($fieldPermissions[$field->fieldName] < $_permissionLevel)
                continue;
            $fieldOut = array(
                'type' => isset($typeMapping[$field->type])
                    ? $typeMapping[$field->type]
                    : 'unicode',
                'key' => $field->fieldName,
                'required' => (boolean) (integer) $field->required,
                'label' => $this->staticModel->getAttributeLabel($field->fieldName),
            );
            
            // Populate the "choices" array for dropdowns in the Zap editing UI:
            //TODO: some $options are in an array format. Translate them to a map to work with Zapier
            //(i.e., "choices": [1,2] to "choices: {"1":"one","2":"two"}.
            $options = $this->fieldOptions($field);
            if(!empty($options))
                $fieldOut['choices'] = $options;

            $fields[] = $fieldOut;
        }
        $this->responseBody = $fields;
    }

    /**
     * Respond if a response hasn't already been sent.
     *
     * If a response hasn't been sent yet and the action has executed fully,
     * this method sends an empty response with the 204 status if the body has
     * not been set, and the body itself with 200 otherwise.
     *
     * This eliminates the need to call {@link send} at the end of every action
     * where content would be sent.
     *
     * @param type $action
     */
    public function afterAction($action){
        if(isset($this->response->body) || count($this->response) > 0)
            $this->send();
        else
            $this->sendEmpty();
    }

    /**
     * Returns the viewable attributes of an active record model in an array.
     * 
     * @param CActiveRecord $model
     */
    public function attributesOf(CActiveRecord $model){
        if($model instanceof X2Model){
            $attributes = $model->getAttributes($model->getReadableAttributeNames());

            // Kludge for including actionDescription in Actions:
            if($model instanceof Actions && $model->fieldPermissions['actionDescription'] >=1) {
                $attributes['actionDescription'] = $model->getActionDescription();
            }
            return $attributes;
        }elseif($model instanceof User){
            $excludeAttributes = array_fill_keys(array('password','userKey','googleRefreshToken'),'');
            $attributes = array_diff_key(array_merge($model->attributes,
                    $model->profile->attributes),$excludeAttributes);
            $uid = Yii::app()->getSuId();
            if(!Yii::app()->authManager->checkAccess('UsersAdmin',$uid)
                    && $model->id != $uid) {
                // Attribute whitelisting for privacy
                $attributes = array_intersect_key($attributes,array_fill_keys(array(
                    'id','firstName','lastName','emailAddress','username',
                    'userAlias','fullName'
                ),''));
            }
            return $attributes;
        }else{
            return $model->attributes;
        }
    }

    /**
     * Sends an authentication failure message to the client.
     *
     * @param string $message The message to include
     */
    public function authFail($message){
        // Set "realm" header:
        $this->response->httpHeader['WWW-Authenticate'] =
                'Basic realm="X2Engine API v2"';

        
        // Record this authentication failure in the cache, and permanently ban
        // the client IP address if applicable
        $ip = Yii::app()->request->userHostAddress;
        if($this->settings->maxAuthFail > 0 && !$this->settings->bruteforceExempt($ip)){
            // Count the authentication failure using the system cache
            $cache = Yii::app()->cache;
            $cacheId = 'n_api_authfail_'.$ip;
            if(!($n_authfail = $cache->get($cacheId))) {
                $n_authfail = 1;
            } else {
                $n_authfail++;
            }

            // Save the new failure count
            $cache->set($cacheId,$n_authfail,$this->settings->lockoutTime);

            // Append the IP address to the blacklist if it exceeds the maximum
            // acceptable authentication failure count
            if($this->settings->permaBan
                    && $n_authfail >= $this->settings->maxAuthFail) {
                $this->settings->banIP($ip);
                Yii::app()->settings->save();
            }
        }
        

        $this->send(401, $message);
    }

    /**
     * Special behaviors for the controller.
     *
     * This should be really basic/minimal.
     * 
     * @return type
     */
    public function behaviors() {
        set_exception_handler(array($this,'handleException'));
        return array(
            'ResponseBehavior' => array(
                'class' => 'application.components.ResponseBehavior',
                'isConsole' => false,
                'exitNonFatal' => false,
                'longErrorTrace' => false,
                'handleErrors' => true,
                'handleExceptions' => false,
                'errorCode' => 500
            ),
            'UserMailerBehavior' => array(
                'class' => 'UserMailerBehavior'
            ),
            'LeadRoutingBehavior' => array(
                'class' => 'LeadRoutingBehavior'
            ),
            'WebFormBehavior' => array(
                'class' => 'WebFormBehavior'
            ),
        );
    }

    /**
     * Gets possible values for a field.
     *
     * Note, this is meant to be a stripped-down imitation of what is in
     * {@link X2Model} already. I know this is code duplication, but considering 
     *
     * Note, does not yet handle multiple choice (selecting more than one).
     * 
     * @param Fields $field
     */
    public function fieldOptions(Fields $field) {
        switch($field->type){
            case 'assignment':
                return X2Model::getAssignmentOptions(true, true, false);
            case 'credentials':
                $typeArr = explode(':',$field->linkType);
                $type = $typeArr[0];
                if(count($typeAlias) > 1){
                    $uid = Credentials::$sysUseId[$typeAlias[1]];
                }else{
                    $uid = Yii::app()->getSuId();
                }
                if(count($typeArr>0))
                    $uid = $typeArr[1];
                $config = Credentials::getCredentialOptions($this->staticModel,
                        $field->fieldName, $type, $uid);
                return $config['credentials'];
            case 'dropdown':
                // Dropdown options
                $dropdown = Dropdowns::model()->findByPk($field->linkType);
                if($dropdown instanceof Dropdowns){
                    return json_decode($dropdown->options, 1);
                }
                break;
            case 'optionalAssignment':
                $options = X2Model::getAssignmentOptions(true, true, false);
                unset($options['Anyone']);
                $options[''] = '';
                return $options;
            case 'rating':
                return range(Fields::RATING_MIN,Fields::RATING_MAX);
            case 'varchar':
                // Special kludge for actions priority dropdown mapping
                if($field->modelName == 'Actions' && $field->fieldName == 'priority'){
                    return Actions::getPriorityLabels();
                }
                break;
            case 'visibility':
                $permissionsBehavior = Yii::app()->params->modelPermissions;
                return $permissionsBehavior::getVisibilityOptions();
        }
        return array();
    }

    /////////////
    // FILTERS //
    /////////////
    //
    // These define access control/denial to the API.

    /**
     * Sets the user for a stateless API request
     */
    public function filterAuthenticate($filterChain) {
        // Check for the availability of authentication:
        if(!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) && isset($_SERVER['HTTP_AUTHORIZATION'])){
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) 
                    = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        }
        foreach(array('user','pw') as $field) {
            $srvKey = 'PHP_AUTH_'.strtoupper($field);
            if(!isset($_SERVER[$srvKey]) || empty($_SERVER[$srvKey])) {
                $this->authFail("Missing user credentials: $field");
                return;
            }
            ${$field} = $_SERVER[$srvKey];
        }
        $userModel = User::model()->findByAlias($user);
        // Invalid/not found
        if(!($userModel instanceof User) || !PasswordUtil::slowEquals($userModel->userKey, $pw))
            $this->authFail("Invalid user credentials.");
        elseif(trim($userModel->userKey)==null) // Null user key = disabled
            $this->authFail("API access has been disabled for the specified user.");

        // Set user model and profile to respect permissions
        Yii::app()->setSuModel($userModel);
        $profile = $userModel->profile;
        if($profile instanceof Profile)
            Yii::app()->params->profile = $profile;

        $filterChain->run();
    }

    /**
     * Ends the request if the app is locked.
     * 
     * @param CFilterChain $filterChain
     */
    public function filterAvailable($filterChain) {
        $this->response->httpHeader['Content-Type'] = 'application/json; '
                . 'charset=utf-8';
        if(is_int(Yii::app()->locked)){
            $this->send(503,"X2Engine is currently locked. "
                    . "It may be undergoing maintenance. Please try again later.");
        }
        if(!$this->enabled) {
            $this->send(503,"API access has been disabled on this system.");
        }
        $filterChain->run();
    }

    /**
     * JSON-only enforcement for input data
     *
     * Rejects POST/PUT requests with improper content type request header.
     */
    public function filterContentType($filterChain) {
        if(isset($_SERVER['CONTENT_TYPE'])
                && strpos($_SERVER['CONTENT_TYPE'],'application/json') !== 0) {
            $this->send(415);
        }
        $filterChain->run();
    }
    /**
     * Halts execution if the method does not match the list of acceptable
     * methods for the action.
     *
     * @param type $filterChain
     */
    public function filterMethods($filterChain) {
        $id = $filterChain->action->id;
        $methods = self::methods();
        if(isset($methods[$id])){
            // List of methods specified for action. Check.
            $acceptMethods = explode(',', $methods[$id]);
            if(in_array($method = Yii::app()->request->getRequestType(), $acceptMethods)){
                // Method OK; it's listed as an accepted request type
                $filterChain->run();
            } else {
                // Method NOT OK; not listed.
                $this->send(405,"Action \"$id\" does not support $method.");
            }
        } else {
            // No list of acceptable types specified in methods(), so just run:
            $filterChain->run();
        }
    }

    /**
     * Performs RBAC permission checks before allowing access to something.
     *
     * This is to make permissions consistent with  normal use of te app
     *
     * @param type $filterChain
     */
    public function filterRbac($filterChain) {
        $action = null; // The name of the RBAC item to check
        $data = array(); // Additional parameters for RBAC
        $method = Yii::app()->request->requestType;
        $user = Yii::app()->getSuModel();
        $username = $user->username;
        $userId = $user->id;
        $denial = "User $username does not have permission to perform action {action}";

        // Include module-specific, assignment-based permissions if operating
        // on a model (as opposed to, say, querying all tags regardless of the
        // type of record they're attached to)
        if(isset($_GET['_class'])){
            $linkable = $this->staticModel->asa('LinkableBehavior');
            $module = !empty($linkable) ? ucfirst($linkable->module) : $_GET['_class'];
            // Assignment/ownership as stored in the model should be
            // included in the RBAC parameters for business rules to execute
            // properly, if an ID is specified:
            if(isset($_GET['_id'])){
                $data['X2Model'] = $this->model;
            }
        }
        
        // Resolve the name of the auth item to check.
        //
        // There are three actions and five different request types (DELETE,
        // GET, PATCH, POST, PUT) two of which (PATCH/PUT) are indistinct.
        switch($this->action->id) {
            case 'count':
                switch($method) {
                    case 'GET':
                        $action = "{$module}Index";
                        break;
                }
                break;
            case 'model':
                switch($method) {
                    case 'DELETE':
                        $action = "{$module}Delete";
                        break;
                    case 'GET':
                        // Query or view individual:
                        $action = isset($_GET['_id']) 
                            ? "{$module}View"
                            : "{$module}Index";
                        break;
                    case 'PATCH':
                    case 'PUT':
                        $action = "{$module}Update";
                }
                if ($_GET['_class'] === 'Locations') {
                    // Restrict Location operations to admin access
                    $action = 'GeneralAdminSettingsTask';
                }
                break;
            case 'relationships':
            case 'tags':
                switch($method) {
                    case 'DELETE':
                    case 'PATCH':
                    case 'PUT':
                    case 'POST':
                        // As long as the user has permission to view the
                        // record, they should have permission to alter these
                        // metadata (this is the behavior of the base app, as
                        // of this writing):
                        $action = "{$module}View";
                        break;
                    case 'GET':
                        if(isset($_GET['_class']) && isset($_GET['_id'])) {
                            // Respect the permissions of that particular model,
                            // so that URI's corresponding to a given model
                            // record respond consistently:
                            $action = "{$module}View";
                        } else {
                            // Querying all relationships/tags. Simply allow
                            // access because there's no analogue of this
                            // functionality in the application (as of this 
                            // writing), let alone permission entries for them,
                            // and thus nothing on which to base permissions.
                            $filterChain->run();
                        }
                        break;
                }
                break;
            case 'weblead':
                $action = "ContactsUpdate";
                break;
        }

        // Use RBAC to check permission if an auth item exists.
        if(Yii::app()->authManager->getAuthItem($action) instanceof CAuthItem){
            if(!Yii::app()->authManager->checkAccess($action, $userId, $data)){
                $this->send(403, "You do not have permission to perform this action..");
            }
        }
        $filterChain->run();
    }

    
    /**
     * Additional pre-authentication access restrictions.
     * 
     * @param type $filterChain
     */
    public function filterRestrictions($filterChain){
        $ip = Yii::app()->request->userHostAddress;
        $cache = Yii::app()->cache;

        // Enforce whitelist/blacklist:
        if($this->settings->isIpBlocked($ip))
            $this->send(403,'IP address blocked.');

        // Enforce the authentication failure lockout setting:
        if($this->settings->maxAuthFail > 0 && $this->settings->lockoutTime > 0){
            $cache = Yii::app()->cache;
            $cacheId = 'n_api_authfail_'.$ip;
            if(($n_authfail = $cache->get($cacheId))
                    && $n_authfail >= $this->settings->maxAuthFail)
                $this->send(403, "You have been temporarily locked out due to "
                        . "repeated authentication failures.");
        }

        // Enforce the "max requests per interval" setting
        $reqInterval = $this->settings->requestInterval;
        $maxRequests = $this->settings->maxRequests;
        if($maxRequests > 0 && $reqInterval > 0){
            $cache = Yii::app()->cache;
            $cacheId = 'n_api_requests_'.$ip;
            if(!($n_req = $cache->get($cacheId))){
                $cache->set($cacheId, 1, $reqInterval);
                $filterChain->run();
            }
            $n_req++;
            $cache->set($cacheId, $n_req, $reqInterval);
            if($n_req > $maxRequests) {
                $this->response->httpHeader['Retry-After'] = $reqInterval;
                $this->send(429, "You have made too many requests ($n_req). "
                        ."Please wait at least $reqInterval "
                        ."seconds before trying again.");
            }
        }


        $filterChain->run();
    }
    

    public function filters() {
        return array(
            'available', // Application not locked
            
            'restrictions', // Pre-authentication restrictions
            
            'authenticate', // Valid user
            'methods', // Valid request method for the given action
            'contentType', // Valid content type when submitting data
            'rbac + count,model,relationships,tags', // Checks permission
        );
    }

    /**
     * Generates attributes from a query parameter
     * 
     * Takes a special format of query parameter and returns an array of
     * attributes. The parameter should be formatted as:
     * name1**value1,,name2**value2[...]
     * 
     * @param string $condition The condition parameter
     * @return array Associative array of key=>value pairs.
     */
    public function findConditions($condition,$validAttributes = array()) {
        $conditions = explode(self::FIND_DELIM,$condition);

        $attributeConditions = array();
        foreach($conditions as $condition) {
            $attrVal = explode(self::FIND_EQUAL,$condition);
            if(count($attrVal) < 2) {
                continue;
            }
            $attribute = array_shift($attrVal);
            
            $attributeConditions[$attribute] = implode(
                self::FIND_EQUAL,$attrVal);
        }
        
        if(!empty($validAttributes)) {
            // Filter out attributes not present among those that are allowable
            $attributeConditions = array_intersect_key(
                $attributeConditions,
                $validAttributes
            );
        }
        return $attributeConditions;
    }

    //////////////////////
    // PROPERTY GETTERS //
    //////////////////////
    //
    // Functions that provide the value of properties

    /**
     * Creates a {@link CActiveDataProvider} object for search queries.
     *
     * @param string $modelClass Optional, class of {@link CActiveRecord}. If
     *  unspecified, the class of {@link staticModel} will be used.
     * @param CDbCriteria $extraCriteria Criteria to merge with the automatically
     *  created criteria.
     * @param string $combineOp How to combine the custom/extra criteria
     */
    public function getDataProvider($modelClass=null,$extraCriteria = null,$combineOp = 'AND') {
        // Check for model
        $class = $modelClass == null && isset($_GET['_class'])
                ? get_class($this->staticModel)
                : $modelClass;
        if(empty($class) || !class_exists($class))
            $this->send(500, 'Method getDataProvider called without specifying '
                    . 'a valid model class, in action "'.$this->action->id.'".');

        $staticModel = CActiveRecord::model($class);
        $model = new $class('search');

        // Compose attributes in the query parameters for the comparison:
        $searchAttributes = array_intersect_key($_GET, $staticModel->attributes);

        // Get search option parameters
        $optionParams = array_fill_keys($this->reservedParams['search'],0);
        $searchOptions = array_intersect_key($_GET, $optionParams);

        // Configure the CDbCriteria object
        $criteria = new CDbCriteria;
        $criteria->alias = 't';

        if($model instanceof X2Model){
            // Special handling of X2Model subclasses:

            if($model->asa('permissions') && $model->asa('permissions')->enabled){
                // Working with an X2Model instance having its permissions behavior
                // enabled. Include access/permissions criteria.
                $criteria->mergeWith($model->getAccessCriteria());
            }
            if(isset($searchOptions['_tags'])) {
                // Add tag search criteria
                $criteria->distinct = true;

                $tags = array_map(function($t){return '#'.$t;},explode(',',$_GET['_tags']));
                $tagTable = Tags::model()->tableName();
                if(empty($searchOptions['_tagOr']) || !(bool)(int)$searchOptions['_tagOr']){
                    // Perform an "and" tag search (must have all tags)
                    $i_tag = 0;
                    $joins = array();
                    foreach($tags as $tag){
                        $tagParam = ":apiSearchTag$i_tag";
                        $classParam = ":apiTagItemClass$i_tag";
                        $joinAlias = "tag$i_tag";
                        $joins[] = "INNER JOIN `$tagTable` `$joinAlias` "
                                ."ON `$joinAlias`.`type`= $classParam "
                                ."AND `$joinAlias`.`itemId`=`{$criteria->alias}`.`id` "
                                ."AND `$joinAlias`.`tag`=$tagParam";
                        $criteria->params[$tagParam] = $tag;
                        $criteria->params[$classParam] = get_class($model);
                        $i_tag++;
                    }
                    $criteria->join .= implode(' ', $joins);
                } else {
                    // Perform an "or" tag search (could have any one of the
                    // tags in the list)
                    $tagParam = AuxLib::bindArray($tags,'apiSearchTag');
                    $tagIn = AuxLib::arrToStrList(array_keys($tagParam));
                    $criteria->join .= "INNER JOIN `$tagTable` `tag`"
                            . "ON `tag`.`type`=:apiTagItemClass "
                            . "AND `tag`.`itemId`=`{$criteria->alias}`.`id` "
                            . "AND `tag`.`tag` IN $tagIn";
                    $tagParam[":apiTagItemClass"] = get_class($model);
                    foreach($tagParam as $param=>$value) {
                        $criteria->params[$param] = $value;
                    }
                }
            }
            // Special "codes" in comparison values.
            //
            // Not intended for more advanced formulae parsing but for basic
            // stuff, i.e. dynamic points in time like "yesterday"
            $now = time();
            $yesterday = $now - 86400;
            $tomorrow = $now + 86400;
            $codes = array(
                'date' => compact('now','yesterday','tomorrow'),
                'dateTime' => compact('now','yesterday','tomorrow'),
            );
            $fields = $model->getFields();
            foreach($fields as $field) {
                if(isset($searchAttributes[$field->fieldName])) {
                    if(isset($codes[$field->type])) {
                        foreach($codes[$field->type] as $name => $value){
                            $searchAttributes[$field->fieldName] =
                                    preg_replace('/'.$name.'$/',$value,$searchAttributes[$field->fieldName]);
                        }
                    }   
                }
            }
        }

        // Search options:
        //
        // Send with parameter _partial=1 to enable partial match in searches
        $partialMatch = isset($searchOptions['_partial'])
                ? (boolean) (integer) $searchOptions['_partial']
                : false;
        // Send with parameter _or=1 to enable the "OR" operator in the search
        $operator = isset($searchOptions['_or']) && (boolean) (integer) $searchOptions['_or']
                ? 'OR'
                : 'AND';
        // Send with parameter _escape=0 to enable searching with MySQL wildcards
        $escape = isset($searchOptions['_escape'])
                ? (boolean) (integer) $searchOptions['_escape']
                : true;

        // If searching for Actions, perform additional stuff first:
        if($class === 'Actions'){
            $this->kludgesForSearchingActions($searchAttributes,$criteria);
        }

        // Run comparisons:
        $searchCriteria = new CDbCriteria;
        foreach($searchAttributes as $column => $value){
            $searchCriteria->compare($column,$value,$partialMatch,$operator,$escape);
        }
        $criteria->mergeWith ($searchCriteria);

        // Merge extra criteria:
        if($extraCriteria instanceof CDbCriteria) {
            $criteria->mergeWith($extraCriteria,$combineOp);
        }

        // Interpret "order" configuration from parameters:
        if(isset($searchOptions['_order'])) {
            $orderBy = $searchOptions['_order'];
            if(preg_match('/^(?P<asc>[\+\-\s])?(?P<col>[^\+\-\s]+)$/',$orderBy,$match)) {
                $col = $match['col'];
                if(!in_array($col,$staticModel->attributeNames())) {
                    $this->send(400,"Specified attribute to order results by ($col) "
                            . "does not exist in active record class \"$class\".");
                }
                $ascMap = array(
                    '+' => 'ASC',
                    ' ' => 'ASC', // "+" translates to a space on some servers
                    '-' => 'DESC'
                );
                $criteria->order = $col
                        .(empty($match['asc']) ? '' : ' '.$ascMap[$match['asc']]);
            }
        }

        // Interpret pagination from parameters:
        $pageSize = null; // Default query size
        $pageInd = 0; // Default page
        if(isset($searchOptions['_limit']) && ctype_digit((string)$searchOptions['_limit']))
            $pageSize = (integer) $searchOptions['_limit'];
        if(isset($searchOptions['_page']) && ctype_digit((string)$searchOptions['_page']))
            $pageInd = (integer) $searchOptions['_page'];
        $pagination = array(
            'currentPage' => $pageInd,
            'pageSize' => $pageSize !== null ? min($pageSize, $this->maxPageSize) : $this->maxPageSize
        );

        // Construct the data provider object
        return new CActiveDataProvider($class, compact('model', 'criteria', 'pagination'));
    }

    /**
     * Returns {@link enabled}
     */
    public function getEnabled() {
        return self::ENABLED  && $this->settings->enabled ;
    }


    /**
     * Gets POST-ed, JSON-encoded data.
     *
     * @return array
     */
    public function getJpost() {
        if(!isset($this->_jpost)) {
            $this->_jpost = json_decode(file_get_contents('php://input'),1);
            if(!is_array($this->_jpost))
                $this->send(400,"Missing or malformed data sent to server.");
        }
        return $this->_jpost;
    }

    /**
     *
     */
    public function getMaxPageSize() {
        
        if($this->settings->maxPageSize === null 
                || $this->settings->maxPageSize === '') {
            // Unspecified maximum page size
            return self::MAX_PAGE_SIZE;
        } else {
            return $this->settings->maxPageSize;
        }
        
        return self::MAX_PAGE_SIZE;
    }

    /**
     * Returns the current active record currently being operated on.
     *
     * Checks for a valid model type/ID and sets the {@link model} property.
     * 
     * @return X2Model
     */
    public function getModel() {
        if(!isset($this->_model)) {
            if(!(isset($_GET['_id']) || isset($_GET['_findBy']))){
                $method = Yii::app()->request->requestType;
                $this->send(400, "Cannot use method $method in action "
                        ."\"{$this->action->id}\" without specifying a valid "
                        ."record ID or finding condition.");
            }
            if(isset($_GET['_id'])) {
                $this->_model = $this->getStaticModel()->findByPk($_GET['_id']);
            } else {
                // Find model by attributes.
                // 
                // First transform the _findBy parameter into conditions
                $staticModel = $this->getStaticModel();
                $attributeConditions = $this->findConditions(
                    $_GET['_findBy'],
                    $staticModel->attributes
                );
                
                // No conditions present
                if(count($attributeConditions) == 0) {
                    $this->send(400,"Invalid/improperly formatted attribute".
                        " conditions: \"{$_GET['_findBy']}\"");
                }

                // Find:
                $models = $staticModel->findAllByAttributes($attributeConditions);
                $count = count($models);
                switch($count) {
                    case 0:
                        $this->send(404,"No matching record of class ".
                            "{$_GET['_class']} found");
                    default:
                        $this->_model = reset($models);

                        // Return with status 300 (multiple choices) and point 
                        // the client to the query URL if more than one result
                        // was found, and the
                        if($count > 1 && empty($_GET['_useFirst'])) {
                            $queryUri = $this->createUrl('/api2/model',array_merge(
                                array('_class' => $_GET['_class']),
                                $attributeConditions
                            ));
                            $directUris = array();
                            foreach($models as $model) {
                                $directUris[] = $this->createUrl(
                                    '/api2/model',
                                    array(
                                        '_class' => $_GET['_class'],
                                        '_id' => $model->id
                                    )
                                );
                            }
                            $this->response->httpHeader['Location'] = $queryUri;
                            $this->response['queryUri'] = $queryUri;
                            $this->response['directUris'] = $directUris;
                            $this->send(300,"Multiple records match.");
                        }
                }
            }
            if(!(($this->_model) instanceof X2Model))
                $this->send(404, "Record {$_GET['_id']} of class \""
                        .get_class($this->getStaticModel())."\" not found.");
        }
        return $this->_model;
    }

    /**
     * Returns an array listing all "reserved" query parameters.
     */
    public function getReservedParams() {
        return array(
            // Basic query parameters
            'default' => array(
                '_class', // Model class when acting on an X2Model child
                '_id', // ID of a specific record
                '_tagName', // Tag name
                '_relatedId', // ID of relationship record
            ),
            // Search queries
            'search' => array(
                '_escape', // Escape input (i.e. "%")
                '_limit', // Page size
                '_or', // Use the "OR" operator instead of "AND" in searches
                '_order', // Sorting
                '_page', // Page offset
                '_partial', // Enable partial matching
                '_tagOr', // Use "or" for tag searches (default: false)
                '_tags', // Comma-delineated list of tags to search for
            ),
        );
    }

    
    /**
     * Advanced API settings for Platinum Edition
     */
    public function getSettings() {
        return Yii::app()->settings->api2;
    }
    

    /////////////////////////////
    // MISCELLANEOUS FUNCTIONS //
    /////////////////////////////

    /**
     * Returns a static instance of the current effective model type.
     *
     * Performs a check for valid model class.
     *
     * @return X2Model
     */
    public function getStaticModel() {
        if(!isset($this->_staticModel)) {
            if(!isset($_GET['_class']))
                $this->send(400,'Required parameter "class" missing.');
            $this->_staticModel = X2Model::model($_GET['_class']);
            if(!($this->_staticModel instanceof X2Model) && !($this->_staticModel instanceof Locations))
                $this->send(400,"Invalid model class \"{$_GET['_class']}\".");
        }
        return $this->_staticModel;
    }
    
    /**
     * Exception handling for the web API
     *
     * Handle CHttpException instances more gracefully, and defer to the
     * exception handler of {@link ResponseUtil} in all other cases.
     *
     * @param Exception $e
     */
    public function handleException($e) {
        if($e instanceof CHttpException) {
            $this->send((integer) $e->statusCode, $e->statusCode == 404
                    ? "Invalid URI: ".Yii::app()->request->requestUri
                    : $e->getMessage());
        } else {
            $this->log("Uncaught exception [".$e->getCode()."]: ".$e->getMessage());
            ResponseUtil::respondWithException($e);
        }
    }

    /**
     * Special checks and operations to perform when working with Actions.
     */
    public function kludgesForActions(){
        $method = Yii::app()->request->requestType;
        if($_GET['_class'] == 'Actions'){
            // Check association:
            if(isset($_GET['associationType'], $_GET['associationId'])){
                // Must check to see if association type is valid:
                if(!($staticAssocModel = X2Model::model($_GET['associationType']))){
                    $this->send(400, 'Invalid association type.');
                }
                // Check to see if associated record exists:
                $associatedModel = $staticAssocModel->findByPk($_GET['associationId']);
                if(!(bool) $associatedModel) {
                    $this->send(404, 'Associated record not found.');
                }
                
                // Check if the association matches:
                if(isset($_GET['_id']) 
                        && $this->model->associationId != $_GET['associationId']) {
                    // Looking at an action that exists but isn't associated
                    // with the current model. Construct a proper URI for the
                    // client to follow:
                    $params = array(
                        '_id' => $_GET['_id'],
                        '_class' => 'Actions'
                    );
                    if($this->model->associationType != '') {
                        $params['associationType'] = get_class(X2Model::model($this->model->associationType));
                        $params['associationId'] = $this->model->associationId;
                    }
                    $this->response->httpHeader['Location'] = $this->createAbsoluteUrl('/api2/model',$params);
                    $this->send(303,'Action has a different association than '
                            . 'the one specified.');
                }
            }

            // Special attribute override, i.e. when POST is sent to {model}/{id}/Actions:
            if($method == 'POST'){
                $this->model = new Actions;
                if(isset($_GET['associationId'], $_GET['associationType'])){
                    $this->model->associationId = $_GET['associationId'];
                    $this->model->associationType = X2Model::model($_GET['associationType'])->module;
                }
            }
        }
    }

    /**
     * Work-arounds for querying records of the Actions class, which is special
     *
     * The {@link Actions} class is special and different from all the other
     * {@link X2Model} sub-classes, hence this function was written to deal with
     * the differences.
     * 
     * @param type $searchAttributes Search parameters
     * @param CDbCriteria $criteria The search criteria to modify
     */
    public function kludgesForSearchingActions(&$searchAttributes,$criteria){
        // Searching through actionDescription
        //
        // The property Actions.actionDescription is actually a TEXT column in a
        // related table, x2_action_text. That's why searching based on
        // actionDescription is NOT recommended; it will be very, very slow.
        //
        // Also, note (THIS IS IMPORTANT) because it's in a joined table, we 
        // cannot use the elegant CDbCriteria.compare() function to perform
        // the comparison. We thus lose all the advanced comparison and sorting
        // options. Just know that whatever the 'actionDescription' parameter
        // equals will be included directly as a parameter to a "LIKE"
        // comparison statement.
        if(isset($_GET['actionDescription'])){
            $atTable = ActionText::model()->tableName();
            $atAlias = 'at';
            $criteria->join .= " INNER JOIN `$atTable` `$atAlias` "
                    ."ON `$atAlias`.`actionId` = `{$criteria->alias}`.`id` "
                    ."AND `$at`.`text` LIKE :apiSearchActionDescription";
            $criteria->params[':apiSearchActionDescription'] = $_GET['actionDescription'];
        }

        // Awful, ugly kludge for validating Actions' associationType field:
        //
        // The following lines should be removed as soon as associationType in
        // Actions is "fixed" (meaning, it references actual class names instead
        // of module names). The following line was added to account for the
        // case of a database with case-sensitive collation, whereupon a query
        // for associated actions using api2/[class]/[id]/Actions would for
        // instance always return zero results, because associationType (which
        // by URL rules maps to the "_class" parameter) is "Contacts" (the
        // actual class name) rather than "contacts" (the "association type" as
        // dictated by the unwieldy convention that we've had for Actions almost
        // from the very beginnings of X2Engine).
        if(isset($searchAttributes['associationType'])){
            $associationClass = isset(X2Model::$associationModels[$searchAttributes['associationType']]) 
                    ? X2Model::$associationModels[$searchAttributes['associationType']]
                    : $searchAttributes['associationType'];
            $staticSearchModel = X2Model::model($associationClass);
            $searchAttributes['associationType'] = $staticSearchModel->asa('LinkableBehavior') === null 
                    ? lcfirst(get_class($staticSearchModel))
                    : $staticSearchModel->asa('LinkableBehavior')->module;
        }
    }

    /**
     * Logs an API message.
     *
     * @param type $message
     * @param type $level
     * @param type $category
     */
    public function log($message, $level = 'info', $category = 'application.api'){
        $ip = Yii::app()->request->userHostAddress;
        $user = Yii::app()->getSuName();
        Yii::log("[client $ip, user $user, action {$this->action->id}]: ".$message, $level, $category);
    }

    /**
     * Returns an array describing acceptable methods for each API action
     *
     * - Each key in the returned array is a controller ID;
     * - Each value in the array is a comma-delineated list (string) of methods
     * - If a controller's ID is not in this array, it is assumed that it should
     *   accept any request method used.
     * @return type
     */
    public static function methods() {
        return array(
            'appInfo' => 'GET',
            'count' => 'GET',
            'dropdowns' => 'GET',
            'fieldPermissions' => 'GET',
            'fields' => 'GET',
            'hooks' => 'POST,DELETE',
            'model' => 'DELETE,GET,PATCH,POST,PUT',
            'models' => 'GET',
            'relationships' => 'DELETE,GET,PATCH,POST,PUT',
            'tags' => 'GET,POST,DELETE',
            'weblead' => 'POST',
            'users' => 'GET',
            'zapierFields' => 'GET'
        );
    }
    
    /**
     * Sends a HTTP response and logs the message that was sent.
     *
     * @param integer $status
     * @param string $message
     */
    public function send($status=200,$message = '') {
        $statMessage = ResponseUtil::statusMessage($status);
        if(!isset($this->response->body)) {
            // Copy the headers into the response JSON for inferior HTTP client
            // libraries that don't know how to read response headers:
            $this->response['httpHeaders'] = $this->response->httpHeader;
            if(function_exists('getallheaders')) {
                $this->response['reqHeaders'] = getallheaders();
            }
        }
        $this->log("sent [$status $statMessage]".(empty($message)?'':": $message"));
        $this->response->sendHttp($status,$message);
    }

    /**
     * Respond with empty body and optionally log a message.
     * 
     * @param type $message
     */
    public function sendEmpty($message = ''){
        $this->responseBody = '';
        $this->send(204, $message);
    }

    /**
     * Sets the current working model.
     * @param X2Model $model
     */
    public function setModel(X2Model $model) {
        $this->_model = $model;
    }

    /**
     * Sets the current acting model with data submitted to the server.
     * @param type $fields
     * @return type
     */
    public function setModelAttributes($fields = array()) {
        if(empty($fields)) {
            $fields = $this->jpost;
        }
        
        if($this->settings->rawInput) {
            foreach(array('changelog', 'TimestampBehavior') as $behavior){
                if(!$this->model->asa($behavior) instanceof CBehavior
                        || !$this->model->asa($behavior)->enabled)
                    continue;
                $this->model->disableBehavior($behavior);
            }
            $this->model->setAttributes($fields,false);
            if($this->model instanceof Actions && isset($fields['actionDescription']))
                $this->model->actionDescription = $fields['actionDescription'];
            return;
        }
        

        // Kludge to allow setting special fields like "type" directly in
        // Actions (which is necessary in order to properly work with action
        // records that have an association, i.e. call log on a lead)
        $specialActionFields = array_fill_keys(array(
            'type',
            'complete'
                ), 0);
        if($this->model instanceof Actions && count(array_intersect_key($fields,$specialActionFields)>0)) {
            foreach($specialActionFields as $attribute => $placeholder){
                $this->model->$attribute = $fields[$attribute];
                unset($fields[$attribute]);
            }
        }

        $this->model->setX2Fields($fields);

        if(get_class ($this->model) === 'Contacts' && isset($fields['trackingKey'])){
            // key is read-only, won't be set by setX2Fields
            $this->model->trackingKey = $fields['trackingKey']; 
        }
    }

    /**
     * Sets the body of the response
     *
     * @param mixed $object The object to JSON encode and include in the response
     */
    public function setResponseBody($object) {
        switch(gettype($object)) {
            case 'string':
                $this->response->body = $object;
                break;
            case 'array':
                // Assume all elements are of the same type
                $firstElement = reset($object);
                if($firstElement instanceof CActiveRecord) {
                    $records = array();
                    if($firstElement instanceof Tags){
                        // For tags: just get the tag name of each tag (flat list)
                        $records = array_map(function($t){return $t->tag;},$object);
                    }else{
                        // For everything else: get full list of attributes
                        $records = array_map(array($this, 'attributesOf'), $object);
                    }
                    $this->response->body = json_encode($records);

                }else{
                    $this->response->body = json_encode($object);
                }
                break;
            case 'object':
                if($object instanceof CActiveRecord) {
                    $this->response->body = json_encode($this->attributesOf($object));
                }
                break;
            default:
                $this->response->body = json_encode($object);
        }
    }

}

?>
