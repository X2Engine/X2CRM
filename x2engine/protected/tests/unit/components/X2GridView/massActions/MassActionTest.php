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






Yii::import ('application.modules.contacts.controllers.*');
Yii::import ('application.modules.contacts.*');
Yii::import ('application.modules.accounts.controllers.*');
Yii::import ('application.modules.accounts.*');
Yii::import ('application.modules.services.controllers.*');
Yii::import ('application.modules.services.*');
Yii::import ('application.modules.x2Leads.controllers.*');
Yii::import ('application.modules.x2Leads.*');
Yii::import ('application.components.X2GridView.massActions.*');

class MassActionTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
        'tags' => array ('Tags', '.MassActionTest'),
    );
    
    private $_oldServer;
    private $_oldController;
    
    public function setUp(){
        $this->_oldServer = $_SERVER;
        $this->_oldController = Yii::app()->controller;
        return parent::setUp();
    }
    
    public function tearDown(){
        $_SERVER = $this->_oldServer;
        Yii::app()->controller = $this->_oldController;
        parent::tearDown();
    }
    
    
    /**
     * @param array $filters grid filters
     * @param array $fields fields to mass update
     */
    private function runMassUpdateWithFilters (array $filters=array (), array $fields=array ()) {
        $_SESSION = array ();
        $massUpdate = new MassUpdateFields;
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal

        $filterKeys = array_keys ($filters);
        $modelName = array_shift ($filterKeys);
        $controllerName = $modelName.'Controller'; 
        $moduleName = $modelName.'Module'; 
        Yii::app()->controller = new $controllerName (
            $modelName, new $moduleName ($modelName, null));
        $_GET = $filters;
        $model = new $modelName ('search');
        $tableName = $model->tableName ();
        $dataProvider = $model->search (0);
        $dataProvider->calculateChecksum = true;
        $dataProvider->getData ();
        $filteredRecordsCount = intval ($dataProvider->totalItemCount);
        $idChecksum = $dataProvider->getIdChecksum ();

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be updated
        $_POST = $filters;
        $_POST['modelType'] = $modelName;
        $_POST['fields'] = $fields;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $uid = null;
        while (true) {
            $this->obStart ();
            $massUpdate->superExecute ($uid, $filteredRecordsCount, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $uid = $retVal['uid'];

            $_GET = array_merge ($filters, array ($modelName => $fields));
            $dataProvider = $model->search (0);
            $remainingIdCount = $filteredRecordsCount - $dataProvider->totalItemCount;
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, $remainingIdCount);
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                $storedIds = $_SESSION[MassAction::SESSION_KEY_PREFIX.$uid];
                sort ($storedIds);
                //$this->assertEquals ($remainingIds, $storedIds);
            }
        }

    }

    /**
     * Calls super mass delete with different filters set for multiple model types 
     */
    public function testX2ModelSuperExecuteFilters () {
        $testFilters = array (
            'Contacts' => array (
                'lastActivity' => '>Aug 5, 2014',
                'phone' => '<>555',
                'id' => '<20',
            ),
//            'Accounts' => array (
//                'annualRevenue' => '>107',
//                'type' => 'O',
//                'id' => '<20',
//            ),
//            'X2Leads' => array (
//                'assignedTo' => '<>Chris',
//                'id' => '<20',
//            ),
//            'Services' => array (
//                'assignedTo' => 'Chris',
//                'id' => '<20',
//            ),
        );
        foreach ($testFilters as $class => $filters) {
            $this->runMassUpdateWithFilters (array ($class => $filters), array ('name' => 'test'));
        }
    }

    /**
     * Super mass update records using tag and rating filters
     */
    public function testSuperExecuteFilters () {
        $_SESSION = array ();
        $massUpdate = new MassUpdateFields;
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));

        $this->assertEquals (0, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE firstName="test" AND lastName="test"
        ')->queryScalar ());

        $filteredRecordsCount = intval (Yii::app()->db->createCommand ('
            SELECT COUNT(DISTINCT(t.id))
            FROM x2_contacts AS t
            JOIN x2_tags ON itemId=t.id
            WHERE type="Contacts" AND tag LIKE binary "#test1" AND rating>2
        ')->queryScalar ());

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app()->db->createCommand ("
                SELECT DISTINCT(t.id)
                FROM x2_contacts AS t
                JOIN x2_tags ON itemId=t.id
                WHERE type='Contacts' AND tag LIKE binary '#test1' AND rating>2
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be updated
        $_POST['modelType'] = 'Contacts';
        $_POST['fields'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
        );
        $_POST['Contacts'] = array (
            'rating' => '>2',
            'tags' => 'test1'
        );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $uid = null;
        while (true) {
            $this->obStart ();
            $massUpdate->superExecute ($uid, $filteredRecordsCount, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); 
            $this->obEndClean ();
            $this->assertTrue (!isset ($retVal['errorCode']));
            $uid = $retVal['uid'];

            $remainingIds = Yii::app()->db->createCommand ('
                SELECT x2_contacts.id
                FROM x2_contacts
                JOIN x2_tags ON itemId=x2_contacts.id AND tag LIKE BINARY "#test1"
                WHERE (firstName!="test" OR lastName!="test") AND rating>2
            ')->queryColumn ();
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                $storedIds = $_SESSION[MassAction::SESSION_KEY_PREFIX.$uid];
                sort ($storedIds);
                $this->assertEquals ($remainingIds, $storedIds);
            }
        }

    }

    /**
     * Attempt to mass delete contacts and ensure that if item count is reported incorrectly,
     * mass deletion does not occur
     */
    public function testTotalItemCountSafeGuard () {
        $_SESSION = array ();
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal

        // confirm super mass deletion via password
        $_POST['password'] = 'admin';
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massDelete = new MassDelete;
        $this->assertEquals (0, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));
        $this->obStart ();
        $massDelete->superMassActionPasswordConfirmation ();
        $retVal = CJSON::decode (ob_get_contents ()); 
        $this->obEndClean ();
        $this->assertTrue ($retVal[0]);
        $uid = $retVal[1];
        $this->assertEquals (1, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                WHERE id < 20
                ORDER by firstName DESC
            ")->queryColumn ()
        );

        unset ($_POST['password']);
        $_POST['modelType'] = 'Contacts';
        $_POST['Contacts'] = array (
            'id' => '<20'
        );
        $_POST['Contacts_sort'] = 'firstName.desc';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        while (true) {
            $this->obStart ();
            // item COUNT passed to massDelete is intentionally incorrect
            $massDelete->superExecute ($uid, 15, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); 
            $this->obEndClean ();
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal);
            $this->assertTrue ($retVal['errorCode'] === MassAction::BAD_ITEM_COUNT);
            $this->assertTrue (isset ($retVal['failure']));
            $this->assertTrue (isset ($retVal['errorMessage']));
            $this->assertTrue (!isset ($retVal['successes']));
            $this->assertTrue (!isset ($retVal['complete']));
            $this->assertTrue (!isset ($retVal['batchComplete']));
            break;
        }
    }

    /**
     * Attempt to mass delete contacts and ensure that if id checksum is reported incorrectly,
     * mass deletion does not occur
     */
    public function testIdChecksumSafeGuard () {
        $_SESSION = array ();
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal

        // confirm super mass deletion via password
        $_POST['password'] = 'admin';
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massDelete = new MassDelete;
        $this->assertEquals (0, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));
        $this->obStart ();
        $massDelete->superMassActionPasswordConfirmation ();
        $retVal = CJSON::decode (ob_get_contents ()); 
        $this->obEndClean ();
        $this->assertTrue ($retVal[0]);
        $uid = $retVal[1];
        $this->assertEquals (1, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                WHERE id < 15;
            ")->queryColumn ()
        );

        $count = intval (Yii::app ()->db->createCommand ("
            SELECT count(id)
            FROM x2_contacts
            WHERE id < 20;
        ")->queryScalar ());

        unset ($_POST['password']);
        $_POST['modelType'] = 'Contacts';
        $_POST['Contacts'] = array (
            'id' => '<20'
        );
        $_POST['Contacts_sort'] = 'firstName.desc';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        while (true) {
            $this->obStart ();
            // item count passed to massDelete is intentionally incorrect
            $massDelete->superExecute ($uid, $count, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); 
            $this->obEndClean ();
            $this->assertTrue ($retVal['errorCode'] === MassAction::BAD_CHECKSUM);
            $this->assertTrue (isset ($retVal['failure']));
            $this->assertTrue (isset ($retVal['errorMessage']));
            $this->assertTrue (!isset ($retVal['successes']));
            $this->assertTrue (!isset ($retVal['complete']));
            $this->assertTrue (!isset ($retVal['batchComplete']));
            break;
        }
    }

}

?>
