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

/**
 * @package application.modules.contacts.controllers
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
    public function accessRules() {

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
     * @return array A merge of the parent class's imported actions in addition to the ones that 
     *  are specific to the Contacts controller
     */
    public function actions() {
        $actions = array_merge(parent::actions(), array(
            'weblead' => array(
                'class' => 'WebFormAction',
            ),
        ));
        return $actions;
    }

    /**
     * Return a list of external behaviors which are necessary.
     * @return array A merge of the parent class's behaviors with the ContactsController specific 
     *  ones
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'X2MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.X2MobileControllerBehavior'
            ),
            'LeadRoutingBehavior' => array(
                'class' => 'LeadRoutingBehavior'
            ),
            'ImportExportBehavior' => array(
                'class' => 'ImportExportBehavior'
            ),
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
                'attributesOfNewRecordToUpdate' => array(
                    'Accounts' => array(
                        'website' => 'website',
                        'phone' => 'phone',
                    ),
                    'Opportunity' => array(
                        'accountName' => 'company',
                    )
                )
            ),
        ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->noBackdrop = true;
        $contact = $this->loadModel($id);
        if ($this->checkPermissions($contact, 'view')) {

            // Modify the time zone widget to display Contact time
            if (isset($this->portlets['TimeZone'])) {
                $this->portlets['TimeZone']['params']['localTime'] = true;
                $this->portlets['TimeZone']['params']['model'] = &$contact;
            }

            // Update the VCR list information to preserve what list we came from
            if (isset($_COOKIE['vcr-list'])) {
                Yii::app()->user->setState('vcr-list', $_COOKIE['vcr-list']);
            }
            if ($contact->checkForDuplicates()) {
                $this->redirect($this->createUrl('/site/duplicateCheck', array(
                            'moduleName' => 'contacts',
                            'modelName' => 'Contacts',
                            'id' => $id,
                            'ref' => 'view',
                )));
            } else {
                $contact->duplicateChecked();
                // add contact to user's recent item list
                User::addRecentItem('c', $id, Yii::app()->user->getId()); 
                parent::view($contact, 'contacts');
            }
        } else {
            $this->denied ();
        }
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
    public function actionRevisions($id, $timestamp) {
        $contact = $this->loadModel($id);
        // Find all the changelog entries associated with this Contact after the given
        // timestamp. Realistically, this would be more accurate if Changelog ID
        // was used instead of the timestamp.
        $changes = X2Model::model('Changelog')->findAll('type="Contacts" AND itemId="' . $contact->id . '" AND timestamp > ' . $timestamp . ' ORDER BY timestamp DESC');
        // Loop through the changes and apply each one retroactively to the Contact record.
        foreach ($changes as $change) {
            $fieldName = $change->fieldName;
            if ($contact->hasAttribute($fieldName) && $fieldName != 'id')
                $contact->$fieldName = $change->oldValue;
        }
        // Set our widget info
        if (isset($this->portlets['TimeZone']))
            $this->portlets['TimeZone']['params']['model'] = &$contact;

        if ($this->checkPermissions($contact, 'view')) {

            if (isset($_COOKIE['vcr-list']))
                Yii::app()->user->setState('vcr-list', $_COOKIE['vcr-list']);

            User::addRecentItem('c', $id, Yii::app()->user->getId()); ////add contact to user's recent item list
            // View the Contact with the data modified to this point
            parent::view($contact, 'contacts');
        } else
            $this->redirect('index');
    }

    /**
     * @deprecated as of 4.1.6b
     *
     * Displays the a model's relationships with other models.
     * This has been largely replaced with the relationships widget.
     * @param type $id The id of the model to display relationships of
     * @deprecated
     *
     */
    /*public function actionViewRelationships($id) {
        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Relationships', array(
            'criteria' => array(
                'condition' => '(firstType="Contacts" AND firstId="' . $id . '") OR (secondType="Contacts" AND secondId="' . $id . '")',
            )
        ));
        $this->render('viewOpportunities', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    } */

    /**
     * Used for accounts auto-complete method.  May be obsolete.
     */
    public function actionGetTerms() {
        $sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'] . '%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     * Used for auto-complete methods.  This method is likely obsolete.
     */
    public function actionGetContacts() {
        $sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm OR CONCAT(firstName," ",lastName) LIKE :qterm ORDER BY firstName ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'] . '%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     *  Used for auto-complete methods
     */
    public function actionGetItems() {
        $model = new Contacts('search');
        $visCriteria = $model->getAccessCriteria();
        list($fullNameCol,$fullNameParam) = Formatter::fullNameSelect(
            'firstName', 'lastName', 'value');
        // This is necessary because the query won't work if one simply compares
        // the alias "value" as "value LIKE :qterm".
        list($fullNameCol2, $fullNameParam2) = Formatter::fullNameSelect('firstName', 'lastName');
        $sql = 'SELECT id, city, state, country, email, assignedTo, ' . $fullNameCol . '
            FROM x2_contacts t 
            WHERE (firstName LIKE :qterm OR lastName LIKE :qterm OR 
                ' . $fullNameCol2 . ' LIKE :qterm) AND (' . $visCriteria->condition . ')
            ORDER BY firstName ASC';
        $command = Yii::app()->db->createCommand($sql);

        $params = array(':qterm' => $_GET['term'] . '%') + $fullNameParam + $fullNameParam2 + 
            $visCriteria->params;
        $result = $command->queryAll(true, $params);
        foreach (array_keys($result) as $key) {
            $result[$key]['assignedTo'] = implode(
                ', ', $model->getAssigneeNames($result[$key]['assignedTo']));
        }
        echo CJSON::encode($result);
        exit;
    }

    /**
     * Return a JSON encoded list of Contact lists
     */
    public function actionGetLists() {
        if (!Yii::app()->user->checkAccess('ContactsAdminAccess')) {
            $condition = ' AND (visibility="1" OR assignedTo="Anyone"  OR assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks))
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';

            $condition .= ' OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                (SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . '))))';
        } else {
            $condition = '';
        }
        // Optional search parameter for autocomplete
        $qterm = isset($_GET['term']) ? $_GET['term'] . '%' : '';
        $static = isset($_GET['static']) && $_GET['static'];
        $result = Yii::app()->db->createCommand()
                ->select('id,name as value')
                ->from('x2_lists')
                ->where(
                    ($static ? 'type="static" AND ' : '').
                    'modelName="Contacts" AND type!="campaign" 
                    AND name LIKE :qterm' . $condition, 
                    array(':qterm' => $qterm))
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
    public function actionSyncAccount($id) {
        $contact = $this->loadModel($id);
        if ($contact->hasAttribute('company') && is_numeric($contact->company)) {
            $account = X2Model::model('Accounts')->findByPk($contact->company);
            if (isset($account)) {
                foreach ($account->attributes as $key => $value) {
                    // Don't change ID or any of the date fields.
                    if ($contact->hasAttribute($key) && $key != 'id' && $key != 'createDate' && $key != 'lastUpdated' && $key != 'lastActivity') {
                        $contact->$key = $value;
                    }
                }
            }
        }
        $contact->save();
        $this->redirect(array('view', 'id' => $id));
    }

    /**
     * Generates an email template to share Contact data
     * @param int $id The ID of the Contact
     */
    public function actionShareContact($id) {
        $users = User::getNames();
        $model = $this->loadModel($id);
        $body = "\n\n\n\n".Yii::t('contacts', '{module} Record Details', array(
            '{module}'=>Modules::displayName(false)
        ))." <br />
<br />".Yii::t('contacts', 'Name').": $model->firstName $model->lastName
<br />".Yii::t('contacts', 'E-Mail').": $model->email
<br />".Yii::t('contacts', 'Phone').": $model->phone
<br />".Yii::t('contacts', 'Account').": $model->company
<br />".Yii::t('contacts', 'Address').": $model->address
<br />$model->city, $model->state $model->zipcode
<br />" . Yii::t('contacts', 'Background Info') . ": $model->backgroundInfo
<br />" . Yii::t('app', 'Link') . ": " . CHtml::link($model->name,
            $this->createAbsoluteUrl('/contacts/contacts/view', array('id' => $model->id)));

        $body = trim($body);

        $errors = array();
        $hasError = false;
        $status = array();
        $email = array();
        if (isset($_POST['email'], $_POST['body'])) {

            $subject = Yii::t('contacts', 'Contact Record Details');
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if ($email['to'] === false)
                $errors[] = 'email';
            if (empty($body))
                $errors[] = 'body';

            $emailFrom = Credentials::model()->getDefaultUserAccount(Credentials::$sysUseId['systemNotificationEmail'], 'email');
            if ($emailFrom == Credentials::LEGACY_ID)
                if (!Yii::app()->params->profile->emailAddress) {
                    Yii::app()->user->setFlash ('error', Yii::t('app', 'Email could not be sent: user profile does not have an email address.'));
                    $hasError = true;
                } else {
                    $emailFrom = array(
                        'name' => Yii::app()->params->profile->fullName,
                        'address' => Yii::app()->params->profile->emailAddress
                    );
                }

            if (empty($errors) && !$hasError)
                $status = $this->sendUserEmail($email, $subject, $body, null, $emailFrom);

            if (array_search('200', $status)) {
                $this->redirect(array('view', 'id' => $model->id));
                return;
            }
            if ($email['to'] === false)
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

    /**
     * Called by the duplicate checker to keep the current record
     */
    public function actionIgnoreDuplicates() {
        if (isset($_POST['data'])) {

            $arr = json_decode($_POST['data'], true);
            if ($_POST['ref'] != 'view') {
                if ($_POST['ref'] == 'create')
                    $model = new Contacts;
                else {
                    $id = $arr['id'];
                    $model = Contacts::model()->findByPk($id);
                }
                $temp = $model->attributes;
                foreach ($arr as $key => $value) {
                    $model->$key = $value;
                }
            } else {
                $id = $arr['id'];
                $model = X2Model::model('Contacts')->findByPk($id);
            }
            $model->dupeCheck = 1;
            $model->disableBehavior('X2TimestampBehavior');
            if ($model->save()) {
                
            }
            // Optional parameter to determine what other steps to take, default null
            $action = $_POST['action'];
            if (!is_null($action)) {
                $criteria = new CDbCriteria();
                if (!empty($model->firstName) && !empty($model->lastName))
                    $criteria->compare('CONCAT(firstName," ",lastName)', $model->firstName . " " . $model->lastName, false, "OR");
                if (!empty($model->email))
                    $criteria->compare('email', $model->email, false, "OR");
                $criteria->compare('id', "<>" . $model->id, false, "AND");
                if (!Yii::app()->user->checkAccess('ContactsAdminAccess')) {
                    $condition = 'visibility="1" OR (assignedTo="Anyone" AND visibility!="0")  OR assignedTo="' . Yii::app()->user->getName() . '"';
                    /* x2temp */
                    $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' . Yii::app()->user->getId())->queryColumn();
                    if (!empty($groupLinks))
                        $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';

                    $condition .= 'OR (visibility=2 AND assignedTo IN
                        (SELECT username FROM x2_group_to_user WHERE groupId IN
                            (SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . ')))';
                    $criteria->addCondition($condition);
                }
                // If the action was hide all, hide all the other records.
                if ($action == 'hideAll') {
                    $duplicates = Contacts::model()->findAll($criteria);
                    foreach ($duplicates as $duplicate) {
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
                } elseif ($action == 'deleteAll') {
                    Contacts::model()->deleteAll($criteria);
                }
            }
            echo CHtml::encode ($model->id);
        }
    }

    /**
     * Called by the duplicate checker when discarding the new record.
     */
    public function actionDiscardNew() {

        if (isset($_POST['id'])) {
            $ref = $_POST['ref']; // Referring action
            $action = $_POST['action'];
            $oldId = $_POST['id'];
            if ($ref == 'create' && is_null($action) || $action == 'null') {
                echo CHtml::encode ($oldId);
                return;
            } elseif ($ref == 'create') {
                $oldRecord = X2Model::model('Contacts')->findByPk($oldId);
                if (isset($oldRecord)) {
                    $oldRecord->disableBehavior('X2TimestampBehavior');
                    Relationships::model()->deleteAllByAttributes(array('firstType' => 'Contacts', 'firstId' => $oldRecord->id));
                    Relationships::model()->deleteAllByAttributes(array('secondType' => 'Contacts', 'secondId' => $oldRecord->id));
                    if ($action == 'hideThis') {
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
                        return;
                    } elseif ($action == 'deleteThis') {
                        $oldRecord->delete();
                        return;
                    }
                }
            } elseif (isset($_POST['newId'])) {
                $newId = $_POST['newId'];
                $oldRecord = X2Model::model('Contacts')->findByPk($oldId);
                $oldRecord->disableBehavior('X2TimestampBehavior');
                $newRecord = Contacts::model()->findByPk($newId);
                $newRecord->disableBehavior('X2TimestampBehavior');
                $newRecord->dupeCheck = 1;
                $newRecord->save();
                if ($action === '') {
                    $newRecord->delete();
                    echo CHtml::encode ($oldId);
                    return;
                } else {
                    if (isset($oldRecord)) {

                        if ($action == 'hideThis') {
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
                        } elseif ($action == 'deleteThis') {
                            Relationships::model()->deleteAllByAttributes(array('firstType' => 'Contacts', 'firstId' => $oldRecord->id));
                            Relationships::model()->deleteAllByAttributes(array('secondType' => 'Contacts', 'secondId' => $oldRecord->id));
                            Tags::model()->deleteAllByAttributes(array('type' => 'Contacts', 'itemId' => $oldRecord->id));
                            Actions::model()->deleteAllByAttributes(array('associationType' => 'Contacts', 'associationId' => $oldRecord->id));
                            $oldRecord->delete();
                        }
                    }

                    echo CHtml::encode ($newId);
                }
            }
        }
    }

    /**
     * Creates a new Contact record
     */
    public function actionCreate() {
        $model = new Contacts;
        $name = 'Contacts';
        $users = User::getNames();

        if (isset($_POST['Contacts'])) {
            $model->setX2Fields($_POST['Contacts']);
            $model->setName();
            if (isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate($model);
            } else {
                if ($model->validate () && $model->checkForDuplicates()) {
                    Yii::app()->user->setState('json_attributes', json_encode($model->attributes));
                    $this->redirect($this->createUrl('/site/duplicateCheck', array(
                        'moduleName' => 'contacts',
                        'modelName' => 'Contacts',
                        'id' => null,
                        'ref' => 'create',
                    )));
                } else {
                    if ($model->save()) {
                        $this->redirect(array('view', 'id' => $model->id));
                    }
                }
            }
        }

        if (isset($_POST['x2ajax'])) {
            $this->renderInlineCreateForm($model, isset($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create', array(
                'model' => $model,
                'users' => $users,
            ));
        }
    }

    /**
     * Method of creating a Contact called by the Quick Create widget
     */
    public function actionQuickContact() {

        $model = new Contacts;
        // collect user input data
        if (isset($_POST['Contacts'])) {
            // clear values that haven't been changed from the default
            //$temp=$model->attributes;
            $model->setX2Fields($_POST['Contacts']);

            $model->visibility = 1;
            // validate user input and save contact
            // $changes = $this->calculateChanges($temp, $model->attributes, $model);
            // $model = $this->updateChangelog($model, $changes);
            $model->createDate = time();
            //if($model->validate()) {
            if ($model->save()) {
                
            } else {
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

    // Controller/action wrapper for update()
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();
        $renderFlag = true;

        if (isset($_POST['Contacts'])) {
            $oldAttributes = $model->attributes;

            $model->setX2Fields($_POST['Contacts']);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }
        if ($renderFlag) {
            if (isset($_POST['x2ajax'])) {
                Yii::app()->clientScript->scriptMap['*.js'] = false;
                Yii::app()->clientScript->scriptMap['*.css'] = false;
                if (isset($x2ajaxCreateError) && $x2ajaxCreateError == true) {
                    $this->widget ('FormView', array(
                        'model' => $model
                    ), true, true);
                    // $page = $this->renderPartial(
                    //     'application.components.views.@FORMVIEW', 
                    //     array(
                    //         'model' => $model,
                    //         'users' => $users,
                    //         'modelName' => 'contacts'
                    //     ),
                    //     true,
                    //     true
                    // );
                    echo json_encode(
                        array(
                            'status' => 'userError',
                            'page' => $page,
                        )
                    );
                } else {
                    $this->widget ('FormView', array(
                        'model' => $model,
                    ), false, true);
                    // $this->renderPartial(
                    //    'application.components.views.@FORMVIEW', 
                    //     array(
                    //         'model' => $model,
                    //         'users' => $users,
                    //         'modelName' => 'contacts'
                    //     ),
                    //     false,
                    //     true
                    // );
                }
            } else {
                $this->render('update', array(
                    'model' => $model,
                    'users' => $users,
                ));
            }
        }
    }

    // Displays all visible Contact Lists
    public function actionLists() {
        $filter = new X2List ('search');
        $criteria = new CDbCriteria();
        $criteria->addCondition('type="static" OR type="dynamic"');
        if (!Yii::app()->params->isAdmin) {
            $condition = 
                'visibility="1" OR assignedTo="Anyone" OR 
                 assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()
                ->select('groupId')
                ->from('x2_group_to_user')
                ->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks))
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';

            $condition .= 'OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId 
                     FROM x2_group_to_user 
                     WHERE userId=' . Yii::app()->user->getId() . ')
                )
            )';
            $criteria->addCondition($condition);
        }

        $perPage = Profile::getResultsPerPage();

        //$criteria->offset = isset($_GET['page']) ? $_GET['page'] * $perPage - 3 : -3;
        //$criteria->limit = $perPage;
        $criteria->order = 'createDate DESC';
        $filter->compareAttributes ($criteria);

        $contactLists = X2Model::model('X2List')->findAll($criteria);

        $totalContacts = X2Model::model('Contacts')->count();
        $totalMyContacts = X2Model::model('Contacts')->count('assignedTo="' . Yii::app()->user->getName() . '"');
        $totalNewContacts = X2Model::model('Contacts')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));

        $allContacts = new X2List;
        $allContacts->attributes = array(
            'id' => 'all',
            'name' => Yii::t('contacts', 'All {module}', array('{module}'=>Modules::displayName())),
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
            'name' => Yii::t('contacts', 'New {module}', array('{module}'=>Modules::displayName())),
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
            'name' => Yii::t('contacts', 'My {module}', array('{module}'=>Modules::displayName())),
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

        $filteredPseudoLists = $filter->filter ($contactListData);
        $lists = array_merge($filteredPseudoLists, $contactLists);
        $dataProvider = new CArrayDataProvider($lists, array(
            'pagination' => array('pageSize' => $perPage),
            'sort' => array(
                'attributes' => array(
                    'name' => array (
                        'asc' => 'name asc, id desc',
                        'desc' => 'name desc, id desc',
                    ),
                    // secondary order is needed to fix https://github.com/yiisoft/yii/issues/2082
                    'type' => array (
                        'asc' => 'type asc, id desc',
                        'desc' => 'type desc, id desc',
                    ),
//                    'count' => array (
//                        'asc' => 'count asc, id desc',
//                        'desc' => 'count desc, id desc',
//                    ),
                    'assignedTo' => array (
                        'asc' => 'assignedTo asc, id desc',
                        'desc' => 'assignedTo desc, id desc',
                    ),
                )),
            'totalItemCount' => count($contactLists) + 3,
        ));

        $this->render('listIndex', array(
            'contactLists' => $dataProvider,
            'filter' => $filter,
        ));
    }

    // Lists all contacts assigned to this user
    public function actionMyContacts() {
        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', 'myContacts');
        $this->render('index', array('model' => $model));
    }

    // Lists all contacts assigned to this user
    public function actionNewContacts() {
        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', 'newContacts');
        $this->render('index', array('model' => $model));
    }

    // Lists all visible contacts
    public function actionIndex() {
        $model = new Contacts('search');

        Yii::app()->user->setState('vcr-list', 'index');
        $this->render('index', array('model' => $model));
    }

    // Shows contacts in the specified list
    public function actionList($id = null) {
        $list = X2List::load($id);

        if (!isset($list)) {
            Yii::app()->user->setFlash(
                    'error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('lists'));
        }

        $model = new Contacts('search');
        Yii::app()->user->setState('vcr-list', $id);
        $dataProvider = $model->searchList($id);
        $list->count = $dataProvider->totalItemCount;
        $list->runWithoutBehavior('X2FlowTriggerBehavior', function () use ($list) {
            $list->save();
        });

        X2Flow::trigger('RecordViewTrigger', array('model' => $list));
        $this->render('list', array(
            'listModel' => $list,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    }

    public function actionCreateList($ajax=false) {
        $list = new X2List;
        $list->modelName = 'Contacts';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Contacts;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {

            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Contacts';
            $list->createDate = time();
            $list->lastUpdated = time();

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Contacts';

                    $list->lastUpdated = time();

                    if ($list->save()) {
                        if ($ajax) {
                            echo CJSON::encode($list->attributes);
                            return;
                        }
                        $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
                    }
                }
            }
        }

        if (empty($criteriaModels)) {
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        }

        if ($ajax) {
            $html = $this->renderPartial('createList', array(
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
            ), false);
            echo $this->processOutput($html);
            return;
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

    public function actionUpdateList($id) {
        $list = X2List::model()->findByPk($id);

        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new Contacts;
        $comparisonList = X2List::getComparisonList();
        $fields = $contactModel->getFields(true);

        if ($list->type == 'dynamic') {
            $criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Contacts';
                    $list->lastUpdated = time();

                    if ($list->save()) {
                        $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['X2List'])) {
                $list->attributes = $_POST['X2List'];
                $list->modelName = 'Contacts';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        } else {
            if ($list->type = 'dynamic') {
                foreach ($criteriaModels as $criM) {
                    if (isset($fields[$criM->attribute])) {
                        if ($fields[$criM->attribute]->type == 'link') {
                            $criM->value = implode(',', array_map(function($c) {
                                        list($name, $id) = Fields::nameAndId($c);
                                        return $name;
                                    }, explode(',', $criM->value)
                                    )
                            );
                        }
                    }
                }
            }
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
    public function actionRemoveFromList() {

        if (isset($_POST['gvSelection'], $_POST['listId']) && !empty($_POST['gvSelection']) && is_array($_POST['gvSelection'])) {

            foreach ($_POST['gvSelection'] as $contactId)
                if (!ctype_digit((string) $contactId))
                    throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));

            $list = CActiveRecord::model('X2List')->findByPk($_POST['listId']);

            // check permissions
            if ($list !== null && $this->checkPermissions($list, 'edit'))
                $list->removeIds($_POST['gvSelection']);

            echo 'success';
        }
    }

    public function actionDeleteList() {

        $id = isset($_GET['id']) ? $_GET['id'] : 'all';

        if (is_numeric($id))
            $list = X2Model::model('X2List')->findByPk($id);
        if (isset($list)) {

            // check permissions
            if ($this->checkPermissions($list, 'edit'))
                $list->delete();
            else
                throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        }
        $this->redirect(array('/contacts/contacts/lists'));
    }

    /**
     * @deprecated This functionality is superceded by the generalized
     * import functionalitity in AdminController
     * Contacts export function which generates human friendly data and also
     * works for exporting particular lists of Contacts
     * @param int $listId The ID of the list to be exported, if null it will be all Contacts
     */
    /*public function actionExportContacts($listId = null){
        unset($_SESSION['contactExportFile'], $_SESSION['exportContactCriteria'], $_SESSION['contactExportMeta']);
        if(is_null($listId)){
            $file = "contact_export.csv";
            $listName = CHtml::link(Yii::t('contacts', 'All Contacts'), array('/contacts/contacts/index'), array('style' => 'text-decoration:none;'));
            $_SESSION['exportContactCriteria'] = array('with' => array()); // Forcefully disable eager loading so it doesn't go super-slow)
        }else{
            $list = X2List::load($listId);
            $criteria = $list->queryCriteria();
            $criteria->with = array();
            $_SESSION['exportContactCriteria'] = $criteria;
            $file = "list".$listId.".csv";
            $listName = CHtml::link(Yii::t('contacts', 'List')." $listId: ".$list->name, array('/contacts/contacts/list','id'=>$listId), array('style' => 'text-decoration:none;'));
        }
        $filePath = $this->safePath($file);
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
        $fp = fopen($filePath, 'w+');
        fputcsv($fp, $meta);
        fclose($fp);
        $this->render('exportContacts', array(
            'listId' => $listId,
            'listName' => $listName,
        ));
    } */
    
    public function actionDelete($id){
        if(Yii::app()->request->isPostRequest){
            $model = $this->loadModel($id);
            $model->clearTags();
            $model->delete();
        } else {
            throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    public function actionSubscribe() {
        if (isset($_POST['ContactId']) && isset($_POST['Checked'])) {
            $id = $_POST['ContactId'];

            $checked = json_decode($_POST['Checked']);

            if ($checked) { // user wants to subscribe to this contact
                $result = Yii::app()->db->createCommand()
                        ->select()
                        ->from('x2_subscribe_contacts')
                        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $id, 'user_id' => Yii::app()->user->id))
                        ->queryAll();
                if (empty($result)) { // ensure user isn't already subscribed to this contact
                    Yii::app()->db->createCommand()->insert('x2_subscribe_contacts', array('contact_id' => $id, 'user_id' => Yii::app()->user->id));
                }
            } else { // user wants to unsubscribe to this contact
                $result = Yii::app()->db->createCommand()
                        ->select()
                        ->from('x2_subscribe_contacts')
                        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $id, 'user_id' => Yii::app()->user->id))
                        ->queryAll();
                if (!empty($result)) { // ensure user is subscribed before unsubscribing
                    Yii::app()->db->createCommand()->delete('x2_subscribe_contacts', array('contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $id, ':user_id' => Yii::app()->user->id));
                }
            }
        }
    }

    public function actionQtip($id) {
        $contact = $this->loadModel($id);

        $this->renderPartial('qtip', array('contact' => $contact));
    }

    public function actionCleanFailedLeads() {
        $file = $this->safePath('failed_leads.csv');

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            unlink($file);
        }
    }

    /**
     * Create a menu for Contacts
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Contacts = Modules::displayName();
        $Contact = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        $menuItems = array(
            array(
                'name'=>'all',
                'label'=>Yii::t('contacts','All {module}', array('{module}'=>$Contacts)),
                'url'=>array('index')
            ),
            array(
                'name'=>'lists',
                'label'=>Yii::t('contacts','Lists'),
                'url'=>array('lists')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('contacts','Create {module}', array('{module}'=>$Contact)),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label' => Yii::t('contacts', 'Edit {module}', array('{module}' => $Contact)), 
                'url' => array('update', 'id' => $modelId),
            ),
            array(
                'name'=>'save',
                'label'=>Yii::t('contacts','Save {module}', array('{module}'=>$Contact)),
                'url'=>'#',
                'linkOptions'=>array('onclick'=>"$('#save-button').click();return false;")
            ),
            array(
                'name'=>'share',
                'label' => Yii::t('contacts', 'Share {module}', array('{module}' => $Contact)), 
                'url' => array('shareContact', 'id' => $modelId)
            ),
            array(
                'name'=>'delete',
                'label' => Yii::t('contacts', 'Delete {module}', array('{module}' => $Contact)), 
                'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $modelId),
                'confirm' => 'Are you sure you want to delete this item?')
            ),
            array(
                'name'=>'email',
                'label' => Yii::t('app', 'Send Email'), 'url' => '#',
                'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')),
            ModelFileUploader::menuLink(),
            array(
                'name'=>'quotes',
                'label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)',
                'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
            array(
                'name'=>'subscribe',
                'label' => Yii::t('quotes', 'Subscribe'),
                'url' => '#',
                'linkOptions' => array(
                    'class' => 'x2-subscribe-button',
                    'onclick' => 'return subscribe($(this));',
                    'title' => Yii::t('contacts', 'Receive email updates every time information for {name} changes',
                        array('{name}' => (isset($model->firstName, $model->lastName) ?
                            CHtml::encode($model->firstName.' '.$model->lastName) : "")))
            )),
            array(
                'name'=>'unsubscribe',
                'label' => Yii::t('quotes', 'Unsubscribe'),
                'url' => '#',
                'linkOptions' => array(
                    'class' => 'x2-subscribe-button',
                    'onclick' => 'return subscribe($(this));',
                    'title' => Yii::t('contacts', 'Receive email updates every time information for {name} changes',
                        array('{name}' => (isset($model->firstName, $model->lastName) ?
                            CHtml::encode($model->firstName.' '.$model->lastName) : "")))
            )),
            array(
                'name'=>'createList',
                'label'=>Yii::t('contacts','Create List'),
                'url'=>array('createList')
            ),
            array(
                'name'=>'viewList',
                'label'=>Yii::t('contacts','View List'),
                'url'=>array('list','id'=>$modelId)
            ),
            array(
                'name'=>'editList',
                'label'=>Yii::t('contacts','Edit List'),
                'url'=>array('updateList','id'=>$modelId)
            ),
            array(
                'name'=>'deleteList',
                'label'=>Yii::t('contacts','Delete List'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('deleteList','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('contacts','Import {module}', array('{module}'=>$Contacts)),
                'url'=>array('admin/importModels', 'model'=>'Contacts')
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('contacts', 'Export {module}', array('{module}'=>$Contacts)),
                'url'=>array('admin/exportModels', 'model'=>'Contacts')
            ),
            array(
                'name'=>'quick',
                'label'=>Yii::t('app', 'Quick Create'),
                'url'=>array('/site/createRecords', 'ret'=>'contacts'),
                'linkOptions'=>array(
                    'id'=>'x2-create-multiple-records-button',
                    'class'=>'x2-hint',
                    'title'=>Yii::t('app', 'Create a {contact}, {account}, and {opportunity}.', 
                        array(
                            '{contact}' => $Contact,
                            '{account}' => Modules::displayName(false, "Accounts"),
                            '{opportunity}' => Modules::displayName(false, "Opportunities"),
                        )))
            ),
            array(
                'name' => 'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array (
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/site/printRecord', array (
                            'modelClass' => 'Contacts',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', 'Contact').': '.(isset($model) ? 
                                $model->name : "")
                        ))."');"
                )
            ),
            array(
                'name'=>'addRecordAlias',
                'label'=>Yii::t('contacts', 'Add Social Profile'),
                'url' => '#',
                'linkOptions' => array (
                    'id' => 'record-aliases-action-menu-link'
                )
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
