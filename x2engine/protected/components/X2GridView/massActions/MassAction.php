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

Yii::import('application.components.X2GridView.massActions.*');

abstract class MassAction extends CComponent {

    const SESSION_KEY_PREFIX = 'superMassAction';
    const SESSION_KEY_PREFIX_PASS_CONFIRM = 'superMassActionPassConfirm';
    const BAD_CHECKSUM = 1;
    const BAD_ITEM_COUNT = 2;
    const BAD_COUNT_AND_CHECKSUM = 3;

    /**
     * @var bool $hasButton If true, mass action has a button, otherwise it is assumed that the
     *  mass action can only be accessed from the dropdown list
     */
    public $hasButton = false; 

    /**
     * If true, user must enter their password before super mass action can proceed 
     */
    protected $requiresPasswordConfirmation = false;

    protected $_label;

    private $_packages;

    /**
     * @return string label to display in the dropdown list
     */
    abstract public function getLabel ();

    /**
     * @param array $gvSelection array of ids of records to perform mass action on
     */
    abstract public function execute (array $gvSelection);

    public function renderDialog ($gridId, $modelName) {}

    public function beforeExecute () {}

    /**
     * Instantiates mass action classes
     * @return array  
     */
    public static function getMassActionObjects (array $classNames) {
        $objs = array ();
        foreach ($classNames as $className) {
            $objs[] = new $className; 
        }
        return $objs;
    }

    public function registerPackages () {
        Yii::app()->clientScript->registerPackages ($this->getPackages (), true);
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'X2MassAction' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2GridView/MassAction.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
            );
        }
        return $this->_packages;
    }

    /**
     * Echoes flashes in the flashes arrays
     */
    public static function echoFlashes () {
        echo CJSON::encode (self::getFlashes ());
    }

    // used to hold success, warning, and error messages
    protected static $successFlashes = array ();
    protected static $noticeFlashes = array ();
    protected static $errorFlashes = array ();

    protected static function getFlashes () {
        return array (
            'notice' => self::$noticeFlashes,
            'success' => self::$successFlashes,
            'error' => self::$errorFlashes
        );
    }

    /**
     * @param string $gridId id of grid view
     */
    public function getDialogId ($gridId) {
        return "$gridId-".get_class ($this)."-dialog'" ;
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             class='mass-action-button x2-button mass-action-button-".get_class ($this)."'>
                <span></span>
            </a>";
    }

    /**
     * Renders the list item for the mass action dropdown 
     */
    public function renderListItem () {
        echo "
            <li class='mass-action-button mass-action-".get_class ($this)."' ".
            ($this->hasButton ? 'style="display: none;"' : '').">
            ".CHtml::encode ($this->getLabel ())."
            </li>";
    }

    /**
     * Check user password and echo either an error message or a unique id which gets used on
     * subsequent requests to ensure that the user confirmed the action with their password
     */
    public static function superMassActionPasswordConfirmation () {
        if (!isset ($_POST['password'])) 
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        $loginForm = new LoginForm;
        $loginForm->username = Yii::app()->params->profile->username;
        $loginForm->password = $_POST['password'];
        if ($loginForm->validate ()) {
            do {
                $uid = EncryptUtil::secureUniqueIdHash64 ();
            } while (isset ($_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid]));
            $_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid] = true;
            echo CJSON::encode (array (true, $uid));
        } else {
            echo CJSON::encode (array (false, Yii::t('app', 'incorrect password')));
        }
    }

    /**
     * Helper method for superExecute. Returns array of ids of records in search results.
     * @param string $modelClass
     * @return array array of ids and their checksum
     */
    protected function getIdsFromSearchResults ($modelClass) {
        // copy sort & filter parameters from POST data to GET superglobal so
        // that ERememberFiltersBehavior and SmartDataProviderBehavior will filter/sort records
        // properly
        if (isset ($_POST[$modelClass])) {
            $_GET[$modelClass] = $_POST[$modelClass];

            // ensure that specified filter attributes are valid
            foreach ($_GET[$modelClass] as $attr => $val) {

                if (!X2Model::model ($modelClass)->hasAttribute ($attr)) {
                    throw new CHttpException (400, Yii::t('app', 'Bad Request'));
                }
            }
        }

        // the tag filter is a special case since tags don't have a Fields record
        if (isset ($_POST['tagField'])) {
            $_GET['tagField'] = $_POST['tagField'];
        }

        if (isset ($_POST[$modelClass.'_sort'])) {
            $_GET[$modelClass.'_sort'] = $_POST[$modelClass.'_sort'];

            // ensure that specified sort order attribute is valid
            $sortAttr = preg_replace ('/\.desc$/', '', $_GET[$modelClass.'_sort']);

            if (!X2Model::model ($modelClass)->hasAttribute ($sortAttr)) {
                throw new CHttpException (400, Yii::t('app', 'Bad Request'));
            }
        }

        // data provider is retrieved for the sole purpose of using it's criteria object
        $model = new $modelClass ('search', null, false, true);
        $dataProvider = $model->search (0); // page size set to 0 to improve performance
        $dataProvider->calculateChecksum = true;
        $dataProvider->getData (); // force checksum to be calculated
        $ids = $dataProvider->getRecordIds ();
        //AuxLib::debugLogR ($ids);
        $idChecksum = $dataProvider->getidChecksum ();

        // reverse sort order so that we can pop from id list instead of pushing
        $ids = array_reverse ($ids);

        return array ($ids, $idChecksum);
    }

    /**
     * Execute mass action on next batch of records
     * @param string $uid unique id
     * @param int $totalItemCount total number of records to operate on
     * @param string $expectedIdChecksum checksum of ids of records in data provider used to 
     *  generate the grid view
     */
    public function superExecute ($uid, $totalItemCount, $expectedIdChecksum) {
        //$timer = new TimerUtil;
        //$timer->start ();
        // clear saved ids if user clicked the stop button
        if (isset ($_POST['clearSavedIds']) && $_POST['clearSavedIds']) {
            if (!empty ($uid)) {
                unset ($_SESSION[self::SESSION_KEY_PREFIX.$uid]);
                unset ($_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid]);
            }
            echo 'success';
            return;
        }

        // ensure that for super mass deletion, user confirmed deletion with password
        if ($this->requiresPasswordConfirmation && (empty ($uid) || 
            !isset ($_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid]) ||
            !$_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid])) {

            throw new CHttpException (
                401, Yii::t('app', 'You are not authorized to perform this action'));
        }
        if (!$this->requiresPasswordConfirmation && !empty ($uid) && 
            !isset ($_SESSION[self::SESSION_KEY_PREFIX.$uid])) { 

            /**/AuxLib::debugLogR ('Error: $uid is not empty and SESSION key is not set');
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }

        $modelClass = Yii::app()->controller->modelClass;

        //$timer->stop ()->read ('first')->reset ()->start ();

        // if super mass operation hasn't started, initialize id list from which batches will
        // be retrieved
        if (empty ($uid) ||
            (!isset ($_SESSION[self::SESSION_KEY_PREFIX.$uid]) &&
             $this->requiresPasswordConfirmation)) {

            if (!$this->requiresPasswordConfirmation) {
                // removes the even the remote possibility of a key collision
                do {
                    $uid = uniqid (false, true);
                } while (isset ($_SESSION[self::SESSION_KEY_PREFIX.$uid]));
            }
            list ($ids, $idChecksum) = $this->getIdsFromSearchResults ($modelClass);

            // This important check ensures that the number of records displayed in the grid view
            // is equal to the number of records filtered by the specified filters. This check
            // greatly reduces that chance of an incorrect update/deletion.
            if (count ($ids) !== $totalItemCount || $idChecksum !== $expectedIdChecksum) {
                if (count ($ids) !== $totalItemCount && $idChecksum !== $expectedIdChecksum) {
                    $errorCode = self::BAD_COUNT_AND_CHECKSUM;
                } else if (count ($ids) !== $totalItemCount) {
                    $errorCode = self::BAD_ITEM_COUNT;
                } else {
                    $errorCode = self::BAD_CHECKSUM;
                }
                echo CJSON::encode (array (
                    'failure' => true, 
                    'errorMessage' => Yii::t('app', 
                        'The data being displayed in this grid view is out of date. Close '.
                        'this dialog and allow the grid to refresh before attempting this '.
                        'mass action again.'),
                    'errorCode' => $errorCode,
                ));
                return;
            }
            $_SESSION[self::SESSION_KEY_PREFIX.$uid] = $ids;
        }

        //$timer->stop ()->read ('second')->reset ()->start ();

        // grab next batch of ids from session
        $selectedRecords = $_SESSION[self::SESSION_KEY_PREFIX.$uid];
        $selectedRecordsCount = count ($selectedRecords);
        $batchSize = Yii::app()->settings->massActionsBatchSize;
        $batchSize = $selectedRecordsCount < $batchSize ? $selectedRecordsCount : $batchSize;
        $batch = array ();
        for ($i = 0; $i < $batchSize; $i++) {
            // for efficiency reasons, record ids are stored in reverse order and popped.
            // array_shift = O(n), array_pop = O(1)
            $batch[] = array_pop ($selectedRecords);
        }
        $_SESSION[self::SESSION_KEY_PREFIX.$uid] = $selectedRecords;

        // execute mass action on batch
        $successes = $this->execute ($batch);

        // clear session once all batches have been completed
        if (count ($selectedRecords) === 0) {
            unset ($_SESSION[self::SESSION_KEY_PREFIX.$uid]);
            unset ($_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid]);
        }

        $response = $this->generateSuperMassActionResponse ($successes, $selectedRecords, $uid);

        //$timer->stop ()->read ('third')->reset ()->start ();

        echo CJSON::encode ($response);
    }

    /**
     * @param int $successes number of records for which mass action was successful
     * @param array records yet to be acted upon
     * @return array response data for super mass action request 
     */
    protected function generateSuperMassActionResponse ($successes, $selectedRecords, $uid) {
        $flashes = self::getFlashes ();
        $response = $flashes;
        $response['successes'] = $successes;
        $response['uid'] = $uid;
        if (count ($selectedRecords) === 0) {
            $response['complete'] = true;
        } else {
            $response['batchComplete'] = true;
        }
        return $response;
    }

}
