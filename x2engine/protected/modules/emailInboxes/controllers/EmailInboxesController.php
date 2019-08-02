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
 * @package application.modules.emailInboxes.controllers
 */
class EmailInboxesController extends x2base {

    public $modelClass = 'EmailInboxes';

    public static $emailActions = array(
        'refresh',
        'selectFolder',
    );

    /**
     * @var array|null $_searchCriteria
     */
    private $_searchCriteria; 

    /**
     * @var EmailInbox  $_selectedMailbox
     */
    private $_selectedMailbox;

    public function actions() {
        return array_merge(parent::actions (), array(
            'getItems' => array(
                'class' => 'application.components.actions.GetItemsAction',
            ),
        ));
    }

//    public function behaviors () {
//        return array_merge (parent::behaviors (), array (
//            'ImportExportBehavior' => array ('class' => 'ImportExportBehavior'),
//        ));
//    }

    /**
     * Custom permissions for email inbox records. 
     * All access rights given to module admins for shared inboxes.
     * For shared inboxes, view access given to all assignees. 
     * For non-shared inboxes, access rights given only to inbox owner.
     * App admin gets all rights for all inboxes.
     */
    public function checkPermissions (&$model, $action=null) {
        if (Yii::app()->params->isAdmin) return true;

        $module = Yii::app()->controller->module;
        $user = Yii::app()->getSuModel ();

        $moduleAdmin = Yii::app()->user->checkAccess(ucfirst($module->name) . 'Admin');
        switch ($action) {
            case 'view':
                return $model->shared && ($moduleAdmin || $model->isAssignedTo ($user->username)) ||
                    !$model->shared && $model->assignedTo === $user->username;
            case 'edit':
            case 'delete':
                return $model->shared && $moduleAdmin || 
                    !$model->shared && $model->assignedTo === $user->username;
            default:
                return false;
        }
    }

    /**
     * View inboxes
     */
    public function actionIndex () {
        if (!extension_loaded ('imap')) { 
            $this->render ('error', array ( 
                'message' => 
                    Yii::t('app', 'The Email Module requires the PHP IMAP extension.'),
            ));
        }

        $mailbox = $this->getSelectedMailbox ();
        if ($mailbox instanceof EmailInboxes) {
            if (!$this->checkPermissions ($mailbox, 'view')) $this->denied ();
            if ($mailbox->credentialId == null) {
                $this->render ('noCredentials');
                return;
            }
        }

        $loadMessagesOnPageLoad = true;
        if (!is_null($this->getEmailAction ())) {
            $this->dispatchEmailAction ($this->getEmailAction ());
        }
        
        if ($mailbox instanceof EmailInboxes) {
            try {
                $mailbox->getStream ();
            } catch (EmailConfigException $e) {
                $this->render ('badCredentials', array(
                    'error' => $e->getMessage()
                ));
                Yii::app ()->end ();
            }
            // only search cache on initial page load, otherwise load page
            // and fetch messages via ajax
            $searchCacheOnly = !isset ($_GET['ajax']);
            $dataProvider = $this->loadMailbox ($searchCacheOnly);
            if (!$dataProvider) {
                $dataProvider = new CArrayDataProvider(array());
            } else {
                $loadMessagesOnPageLoad = false;
            }
        } else {
            $dataProvider = new CArrayDataProvider(array());
        }

        $pollTimeout = Yii::app()->settings->imapPollTimeout;
        $myEmailInboxIsSetUp = EmailInboxes::model ()->myEmailInboxIsSetUp ();
        $notConfigured = !$myEmailInboxIsSetUp || !($mailbox instanceof EmailInboxes && 
            $dataProvider instanceof CArrayDataProvider);

        $this->noBackdrop = true;
        $this->render (
            'emailInboxes',
            array (
                'dataProvider' => $dataProvider,
                'mailbox' => $mailbox,
                'pollTimeout' => $pollTimeout,
                'loadMessagesOnPageLoad' => $loadMessagesOnPageLoad,
                'notConfigured' => $notConfigured,
                'uid' => isset ($_GET['uid']) ? $_GET['uid'] : null,
            )
        );
    }

    /**
     * Fetch latest messages
     */
    public function refreshInbox() {
        $mailbox = $this->getSelectedMailbox ();
        $mailbox->fetchLatest ();
    }

    /**
     * Change the current mailbox folder
     * @param string $folder Folder to select
     */
    public function selectFolder($folder) {
        $mailbox = $this->getSelectedMailbox ();
        if (!in_array($folder, $mailbox->folders))
            throw new CException (Yii::t('emailInboxes',
                'Requested folder does not exist'));
        $mailbox->selectFolder ($folder);
    }

    /**
     * View grid view of of shared inboxes
     */
    public function actionSharedInboxesIndex () {
        $model = new EmailInboxes ('search');
        $sharedCriteria = new CDbCriteria;
        $sharedCriteria->compare('shared', true);
        $emailInboxesDataProvider = $model->searchBase ($sharedCriteria);

        $this->render (
            'sharedInboxesIndex',
            array (
                'model' => $model,
                'emailInboxesDataProvider' => $emailInboxesDataProvider,
            )
        );
    }

    /**
     * Create a new shared inbox
     */
    public function actionCreateSharedInbox  () {
        $model = new EmailInboxes;
        if (isset ($_POST['EmailInboxes'])) {
            
            $model->setX2Fields ($_POST['EmailInboxes']);
            $model->password = $_POST['EmailInboxes']['password'];
            $model->settings = $_POST['EmailInboxes']['settings'];
            $model->shared = true;
            if ($model->save ()) {
                $tabs = CJSON::decode (Yii::app()->params->profile->emailInboxes);
                if (is_array($tabs)) {
                    $tabs[] = $model->id;
                    Yii::app()->params->profile->setEmailInboxes ($tabs);
                    Yii::app()->params->profile->save();
                }
                $this->redirect ('sharedInboxesIndex');
            }
        } 
        $this->render (
            'createSharedInbox',
            array (
                'model' => $model,
            )
        );
    }

    /**
     * Update a shared inbox 
     * @param int $id id of shared inbox
     */
    public function actionUpdateSharedInbox ($id) {
        $model = $this->loadModel($id);
        if (isset ($_POST['EmailInboxes'])) {
            
            $model->setX2Fields ($_POST['EmailInboxes']);
            $model->password = $_POST['EmailInboxes']['password'];
            $model->settings = $_POST['EmailInboxes']['settings'];
            if ($model->save ()) {
                $this->redirect (array('/emailInboxes/sharedInboxesIndex'));
            }
        } 
        $this->render (
            'updateSharedInbox',
            array (
                'model' => $model,
            )
        );
    }

    /**
     * Delete a shared inbox 
     * @param int $id id of shared inbox
     */
    public function actionDeleteSharedInbox ($id) {
        $model = $this->loadModel($id);
        $model->delete ();
    }

    /**
     * Save user's tab settings 
     */
    public function actionSaveTabSettings () {
        if (isset ($_POST['Profile']['emailInboxes']) && 
            is_array ($_POST['Profile']['emailInboxes'])) {

            $emailInboxes = $_POST['Profile']['emailInboxes'];
            $username = Yii::app()->user->getName ();
            $tabs = array ();

            // ensure that user has view permissions for all selected inboxes
            foreach ($emailInboxes as $id) {
                $mailbox = EmailInboxes::model ()->findByPk ($id);
                if (!$mailbox ||
                    !$this->checkPermissions ($mailbox, 'view')) {

                    throw $this->badRequestException ();
                }
                $tabs[] = $id;
            }

            // ensure that user's personal inbox is selected
            if (!in_array (EmailInboxes::model ()->getMyEmailInbox ()->id, $tabs)) {
                throw $this->badRequestException ();
            }
            Yii::app()->params->profile->setEmailInboxes ($tabs);
            if (Yii::app()->params->profile->save ()) {
                echo 'success';
            } else {
                echo 'failure';
            }
        } else {
            throw $this->badRequestException ();
        }

    }

    /**
     * Configure the current user's personal email inbox
     */
    public function actionConfigureMyInbox () {
        $model = EmailInboxes::model ()->getMyEmailInbox ();   
        if (!$model) {
            $model = new EmailInboxes;
            // set default personal inbox name
            $model->name = Yii::t('emailInboxes', 'My Inbox');
        }
        if (isset ($_POST['EmailInboxes'])) {
            $model->setX2Fields ($_POST['EmailInboxes'], false, true);
            $model->password = $_POST['EmailInboxes']['password'];
            $model->assignedTo = Yii::app ()->user->getName ();
            $model->settings = $_POST['EmailInboxes']['settings'];
            $model->shared = 0;
            $emailInboxes = Yii::app()->params->profile->getEmailInboxes ();
            if ($model->save ()) {
                $model->refresh ();
                $model->deleteCache ();
                if (!count ($emailInboxes)) {
                    Yii::app()->params->profile->setEmailInboxes (array ($model->id));
                    Yii::app()->params->profile->save ();
                }
                $this->redirect ('index');
            }
        } 
        $this->render (
            'configureMyInbox',
            array (
                'model' => $model,
            )
        );
    }

    /**
     * Render a JSON-encoded array with message details
     * @param int $uid Unique ID of the email message
     */
    public function actionViewMessage($uid) {
        $mailbox = $this->getSelectedMailbox ();
        if (!$this->checkPermissions ($mailbox, 'view')) $this->denied ();
        if (!isset($mailbox))
            $this->redirect('index');

        $this->getCurrentFolder(true);
        echo $mailbox->renderMessage ($uid, isset ($_GET['ajax']));
    }

    /**
     * Download a specific attachment
     * @param int $id Unique ID of the message
     * @param float $part Multipart part number
     */
    public function actionDownloadAttachment($uid, $part) {
        $mailbox = $this->getSelectedMailbox ();
        if (!$this->checkPermissions ($mailbox, 'view')) $this->denied ();
        $this->fetchAttachment ($uid, $part);
    }

    /**
     * Saves a specific attachment as a Media object, creating relationships
     * to the associated records
     * @param int $uid Unique ID of the message
     * @param float $part Multipart part number
     */
    public function actionAssociateAttachment($uid, $part) {
        $mailbox = $this->getSelectedMailbox ();
        if (!$this->checkPermissions ($mailbox, 'view')) $this->denied ();
        $message = $mailbox->fetchMessage ($uid);
        $contacts = $message->getAssociatedContacts (true);
        $type = 'error';
        if (!empty($contacts)) {
            list($mimeType, $filename, $size, $attachment, $encoding) = $this->fetchAttachment ($uid, $part, false, true);
            if ($this->associateAttachment($contacts, $filename, $attachment)) {
                $result = Yii::t('emailInboxes', 'Attachment successfully associated');
                $type = 'success';
            } else {
                $result = Yii::t('emailInboxes', 'Association failed: the attachment could not be saved');
            }
        } else {
            $result = Yii::t('emailInboxes', 'Association failed: there are no related records');
        }

        echo CJSON::encode(array(
            'message' => $result,
            'type' => $type,
        ));
    }

    /**
     * View an inline attachment
     * @param int $id Unique ID of the message
     * @param float $part Multipart part number
     */
    public function actionViewAttachment($uid, $part) {
        $mailbox = $this->getSelectedMailbox ();
        if (!$this->checkPermissions ($mailbox, 'view')) $this->denied ();
        $this->fetchAttachment ($uid, $part, true);
    }

    /**
     * Toggle flags for the given messages
     * @param int|array $id Unique ids of the specified messages
     * @param string $flag Message flag to set
     */
    public function actionMarkMessages() {
        if (!Yii::app()->request->isPostRequest)
            throw new CException('Invalid request');
        $flag = isset($_POST['flag']) ? $_POST['flag'] : null;
        $uids = $this->specifiedUids;
        $mailbox = $this->getSelectedMailbox ();
        if (!$this->checkPermissions ($mailbox, 'view')) $this->denied ();
        $this->getCurrentFolder(true);
        $success = true;
        switch ($flag) {
            case 'read':         $success = $mailbox->markRead ($uids);         break;
            case 'unread':       $success = $mailbox->markUnread ($uids);       break;
            case 'important':    $success = $mailbox->markImportant ($uids);    break;
            case 'notimportant': $success = $mailbox->markNotImportant ($uids); break;
            default:             throw new CException(Yii::t('emailInboxes',
                                    "Unknown flag: ".CHtml::encode($flag)));
        }
        echo $success ? 'success' : 'failure';
    }

    /**
     * Helper function to grab the current folder from GET parameters. Also sets the selected 
     * mailbox's folder
     * @param bool $openImap Whether or not to open the IMAP stream
     * @return string|null The currently selected folder
     */
    public function getCurrentFolder($openImap = false) {
        $mailbox = $this->getSelectedMailbox ();

        if (isset($_GET['emailFolder'])) {
            $currentFolder = $_GET['emailFolder'];
        } else if (isset($_POST['emailFolder'])) {
            $currentFolder = $_POST['emailFolder'];
        } else {
            return null;
        }

        if ($openImap)
            $mailbox->selectFolder ($currentFolder);
        else
            $mailbox->setCurrentFolder ($currentFolder);
        return $currentFolder;
    }

    /**
     * Helper function to grab the mailbox id GET param
     * @return EmailInbox The currently selected mailbox
     */
    public function getSelectedMailbox() {
        if (!isset ($this->_selectedMailbox)) {
            $inboxModel = EmailInboxes::model();
            if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
                $this->_selectedMailbox = $inboxModel->findByPk ($_GET['id']);
            } else if ($inboxModel->myEmailInboxIsSetUp()) {
                $this->_selectedMailbox = $inboxModel->myEmailInbox;
            } else {
                $this->_selectedMailbox = null;
            }

            if ($this->_selectedMailbox !== null && $this->_selectedMailbox->credentials && $this->_selectedMailbox->credentials->auth->disableInbox) {
                $this->render ('badCredentials', array(
                    'error' => Yii::t('app', 'Inbox usage is disabled for these credentials. Please update the settings on the "Manage Apps" page to enable inbox access.'),
                ));
                Yii::app ()->end ();
            }
        }
        return $this->_selectedMailbox;
    }

    /**
     * Retrieve the specified UIDs from POST
     * @return array of message uids
     */
    public function getSpecifiedUids() {
        $uids = isset($_POST['uids']) ? $_POST['uids'] : null;
        if (!is_numeric($uids) && !is_array($uids))
            throw new CException(Yii::t('emailInboxes',
                'You must specify UIDs'));
        else if (is_array($uids)) {
            foreach ($uids as $uid) {
                if (!is_numeric($uid))
                    throw new CException(Yii::t('emailInboxes',
                        'Invalid UID specified!'));
            }
        }
        return $uids;
    }

    /**
     * Process and return the search terms and operators
     * @return array
     */
    public function getSearchCriteria () {
        if (!isset ($this->_searchCriteria)) {
            if (isset ($_GET['EmailInboxesSearchFormModel'])) {
                $formModel = new EmailInboxesSearchFormModel;
                $formModel->setAttributes ($_GET['EmailInboxesSearchFormModel'], false);
                $this->_searchCriteria = $formModel->composeSearchString ();
            } else {
                $this->_searchCriteria = null;
            }
        }
        return $this->_searchCriteria;
    }

    /**
     * Retrieve the chosen email action from POST
     * @return string|null The email action, or null if none is specified
     */
    public function getEmailAction() {
        if (!isset($_GET['emailAction']) && !isset($_POST['emailAction']))
            return null;
        else if (isset($_GET['emailAction']))
            $action = $_GET['emailAction'];
        else if (isset($_POST['emailAction']))
            $action = $_POST['emailAction'];

        if (!in_array($action, self::$emailActions)) {
            throw new CException (Yii::t('emailInboxes',
                'Unsupported email action '.CHtml::encode($action)));
        }
        return $action;
    }

    /**
     * Load message header overviews for the given inbox
     * @param bool $searchCacheOnly if true, inbox will only be searched if messages are cached 
     * @return false|CArrayDataProvider of message headers
     */
    public function loadMailbox($searchCacheOnly=false) {
        $this->getCurrentFolder (true);
        if (isset ($_GET['lastUid'])) $lastUid = $_GET['lastUid'];
        else $lastUid = null;
        return $this->getSelectedMailbox ()->searchInbox (
            $this->getSearchCriteria (), $searchCacheOnly, $lastUid);
    }

    /**
     * Create a menu for EmailInboxes
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        /**
         * To show all options:
         * $menuOptions = array(
         *     'inbox', 'configureMyInbox', 'sharedInboxesIndex', 'createSharedInbox',
         * );
         */

        $menuItems = array(
            array(
                'name' => 'inbox',
                'label' => Yii::t('emailInboxes', 'Inbox'),
                'url' => array('index')
            ),
            array(
                'name' => 'configureMyInbox',
                'label' => Yii::t('emailInboxes', 'Configure My Inbox'),
                'url' => array('configureMyInbox')
            ),
            array(
                'name' => 'forgetInbox',
                'label' => Yii::t('emailInboxes', 'Delete Inbox Settings'),
                'url' => array('forgetInbox')
            ),
            array(
                'name' => 'sharedInboxesIndex',
                'label' => Yii::t('emailInboxes', 'Shared Inboxes'),
                'url' => array('sharedInboxesIndex')
            ),
            array(
                'name' => 'createSharedInbox',
                'label' => Yii::t('emailInboxes', 'Create Shared Inbox'),
                'url' => array('createSharedInbox')
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

    public function actionForgetInbox () {
        $model = EmailInboxes::model ()->getMyEmailInbox ();
        if ($model) $model->delete ();
        $this->redirect ('index');
    }

    /**
     * Perform the specified email action, as allowed by self::$emailActions
     * @param string $action Email Action to perform
     */
    private function dispatchEmailAction($action) {
        switch ($action) {
            case 'refresh':
                $this->refreshInbox(); 
                break;
            case 'selectFolder':
                $this->getCurrentFolder(true);
                break;
            default:
                throw new CException (Yii::t('emailInboxes',
                    'Unsupported email action '.CHtml::encode($action)));
        }
    }

    /**
     * Helper function to handle retrieving attachments
     * @param int $uid IMAP Message UID
     * @param float $part IMAP multipart message part number
     * @param boolean $inline Whether it is an inline attachment
     * @param boolean $return
     */
    private function fetchAttachment($uid, $part, $inline = false, $return = false) {
        $mailbox = $this->getSelectedMailbox ();
        if (!isset($mailbox))
            $this->redirect('index');

        $this->getCurrentFolder(true);
        $message = $mailbox->fetchMessage ($uid);
        return $message->downloadAttachment ($part, $inline, $return);
    }

    /**
     * Helper function to handle associating attachments to the related record
     * @param array $contacts Array of related records to be associated with
     * @param string $filename Attachment file name
     * @param string $attachment Attachment data
     * @return boolean success
     */
    private function associateAttachment(array $contacts, $filename, $attachment) {
        if (empty($contacts)) return;
        $username = Yii::app()->user->name;
        $userFolderPath = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            '..',
            'uploads',
            'protected',
            'media',
            $username
        ));
        // if user folder doesn't exit, try to create it
        if (!(file_exists($userFolderPath) && is_dir($userFolderPath))) {
            if (!@mkdir($userFolderPath, 0777, true)) { // make dir with edit permission
                throw new CHttpException(500, "Couldn't create user folder $userFolderPath");
            }
        }

        $media = new Media;
        $media->fileName = $filename;
        $media->createDate = time();
        $media->lastUpdated = time();
        $media->uploadedBy = $username;
        $media->associationType = 'Contacts';
        $media->associationId = $contacts[0]->id;
        $media->resolveNameConflicts();
        $associatedMedia = Yii::app()->file->set($userFolderPath.DIRECTORY_SEPARATOR.$media->fileName);
        $associatedMedia->create();
        $associatedMedia->setContents($attachment);

        if ($associatedMedia->exists) {
            if ($media->save()) {
                $createdRelationships = true;
                foreach ($contacts as $contact) {
                    $createdRelationships = $createdRelationships && $contact->createRelationship($media);
                }
                return $createdRelationships;
            }
        }
        return false;
    }
}
