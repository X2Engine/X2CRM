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
Yii::import ('application.components.X2GridView.massActions.*');

class MassUpdateFieldsTest extends X2DbTestCase {

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
     * Mass update firstName and lastName for fixture records 
     */
    public function testExecute () {
        $_POST['modelType'] = 'Contacts';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_POST['fields'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
        );
        $gvSelection = range (1, 24);
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massUpdate = new MassUpdateFields;
        $this->assertEquals (0, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE firstName="test" and lastName="test"
        ')->queryScalar ());
        $massUpdate->execute ($gvSelection);
        $this->assertEquals (24, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE firstName="test" and lastName="test"
        ')->queryScalar ());
    }

    /**
     * Super mass update firstName and lastName for fixture records 
     */
    public function testSuperExecute () {
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

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
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
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $uid = null;
        while (true) {
            $this->obStart ();
            $massUpdate->superExecute ($uid, 24, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $this->assertTrue (!isset ($retVal['errorCode']));
            $uid = $retVal['uid'];

            $remainingIds = array_map (
                function ($a) { return $a['id']; }, 
                Yii::app()->db->createCommand ('
                    SELECT id
                    FROM x2_contacts
                    WHERE firstName!="test" OR lastName!="test"
                ')->queryAll ());
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
}

?>
