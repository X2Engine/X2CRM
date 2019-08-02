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






Yii::import ('application.components.X2GridView.massActions.*');
Yii::import ('application.modules.contacts.ContactsModule');
Yii::import ('application.modules.contacts.controllers.*');

class MassDeleteTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
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
     * Attempt to mass delete range of records in fixture file
     */
    public function testExecute () {
        $_POST['modelType'] = 'Contacts';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $gvSelection = range (1, 24);
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massDelete = new MassDelete;
        $this->assertEquals (24, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE id > 0 AND id < 25
        ')->queryScalar ());
        $massDelete->execute ($gvSelection);
        $this->assertEquals (0, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE id > 0 AND id < 25
        ')->queryScalar ());
    }

    /**
     * Ensure that password confirmation method works correctly
     */
    public function testSuperMassActionPasswordConfirmation  () {
        $_SESSION = array ();
        Yii::app()->user; // initializes $_SESSION superglobal

        // correct password
        $_POST['password'] = 'admin';
        $massDelete = new MassDelete;
        $this->assertEquals (0, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));
        $this->obStart ();
        $massDelete->superMassActionPasswordConfirmation ();
        $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
        $this->assertTrue ($retVal[0]);
        $this->assertEquals (1, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));

        // incorrect password
        $_SESSION = array ();
        $_POST['password'] = 'notadmin';
        $massDelete = new MassDelete;
        $this->assertEquals (0, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));
        $this->obStart ();
        $massDelete->superMassActionPasswordConfirmation ();
        $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
        $this->assertFalse ($retVal[0]);
        $this->assertEquals (0, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));
    }

    /**
     * Attempt to super mass delete range of records in fixture file
     */
    public function testSuperExecute () {
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
        $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
        $this->assertTrue ($retVal[0]);
        $uid = $retVal[1];
        $this->assertEquals (1, count (
            preg_grep (
                '/^'.MassAction::SESSION_KEY_PREFIX_PASS_CONFIRM.'/', array_keys ($_SESSION))));

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be deleted
        unset ($_POST['password']);
        $_POST['modelType'] = 'Contacts';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        while (true) {
            $this->obStart ();
            $massDelete->superExecute ($uid, 24, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $this->assertTrue (!isset ($retVal['errorCode']));
            $remainingIds = array_map (
                function ($a) { return $a['id']; }, 
                Yii::app()->db->createCommand ('
                    SELECT id
                    FROM x2_contacts
                ')->queryAll ());
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                sort ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]);
                $this->assertEquals ($remainingIds, $_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]);
            }
        }
    }

    /**
     * Attempt to super mass delete records in fixture file filtered and sorted as specified in
     * POST parameters.
     */
    public function testSuperExecuteWithFiltersAndSortOrder () {
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
        $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
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
                ORDER by firstName desc
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be deleted
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
            $massDelete->superExecute ($uid, 19, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $remainingIds = array_map (
                function ($a) { return $a['id']; }, 
                Yii::app()->db->createCommand ('
                    SELECT id
                    FROM x2_contacts
                    WHERE id < 20
                    ORDER by firstName desc
                ')->queryAll ());
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                // reverse stored ids since they're kept in reverse order
                $this->assertEquals (
                    $remainingIds, array_reverse ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
            }
        }
    }

    /**
     * Attempt to super mass delete records in fixture file filtered and sorted as specified in
     * POST parameters.
     */
    public function testSuperExecuteBatchSize () {
        Yii::app()->settings->massActionsBatchSize = 100;
        //$this->assertSaves (Yii::app()->settings);

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
        $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
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
                ORDER by firstName desc
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be deleted
        unset ($_POST['password']);
        $_POST['modelType'] = 'Contacts';
        $_POST['Contacts'] = array (
            'id' => '<20'
        );
        $_POST['Contacts_sort'] = 'firstName.desc';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $batches = 0;
        while (true) {
            $this->obStart ();
            $massDelete->superExecute ($uid, 19, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $batches++;
            $remainingIds = array_map (
                function ($a) { return $a['id']; }, 
                Yii::app()->db->createCommand ('
                    SELECT id
                    FROM x2_contacts
                    WHERE id < 20
                    ORDER by firstName desc
                ')->queryAll ());
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                // reverse stored ids since they're kept in reverse order
                $this->assertEquals (
                    $remainingIds, array_reverse ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
            }
        }
        $this->assertEquals (1, $batches);

        // restore default
        Yii::app()->settings->massActionsBatchSize = 10;
        //$this->assertSaves (Yii::app()->settings);
    }

    /**
     * Attempt to super mass delete records in fixture file filtered and sorted as specified in
     * POST parameters.
     */
    public function testSuperExecuteSmallBatchSize () {
        $batchSize = 7;
        Yii::app()->settings->massActionsBatchSize = $batchSize;
        //$this->assertSaves (Yii::app()->settings);

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
        $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
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
                ORDER by firstName desc
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be deleted
        unset ($_POST['password']);
        $_POST['modelType'] = 'Contacts';
        $_POST['Contacts'] = array (
            'id' => '<20'
        );
        $_POST['Contacts_sort'] = 'firstName.desc';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $totalItemCount = 19;
        $expectedBatches = ceil ($totalItemCount / $batchSize);
        $batches = 0;
        while (true) {
            $this->obStart ();
            $massDelete->superExecute ($uid, $totalItemCount, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $batches++;
            $remainingIds = array_map (
                function ($a) { return $a['id']; }, 
                Yii::app()->db->createCommand ('
                    SELECT id
                    FROM x2_contacts
                    WHERE id < 20
                    ORDER by firstName desc
                ')->queryAll ());
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                // reverse stored ids since they're kept in reverse order
                $this->assertEquals (
                    $remainingIds, array_reverse ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
            }
        }
        $this->assertEquals ($expectedBatches, $batches);

        // restore default
        Yii::app()->settings->massActionsBatchSize = 10;
        //$this->assertSaves (Yii::app()->settings);
    }

}

?>
