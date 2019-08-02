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






Yii::import ('application.tests.unit.components.X2GridView.massActions.*');

class MassTagTest extends TagActionTestBase {

    /**
     * Attempt to mass tag range of records in fixture file
     */
    public function testExecute () {
        $_POST['modelType'] = 'Contacts';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_POST['tags'] = array ('#test1', '#test2');
        $gvSelection = range (1, 24);
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massTag = new MassTag;
        $this->assertEquals (19, Yii::app()->db->createCommand ('
            SELECT COUNT(DISTINCT(t.id))
            FROM x2_contacts AS t
            JOIN x2_tags ON itemId=t.id
            WHERE type="Contacts" AND tag LIKE binary "#test1" or tag like binary "#test2" 
        ')->queryScalar ());
        $massTag->execute ($gvSelection);
        $this->assertEquals (24, Yii::app()->db->createCommand ('
            SELECT COUNT(DISTINCT(t.id))
            FROM x2_contacts AS t
            JOIN x2_tags ON itemId=t.id
            WHERE type="Contacts" AND tag LIKE binary "#test1" or tag like binary "#test2" 
        ')->queryScalar ());
    }

    /**
     * Attempt to super mass tag range of records in fixture file
     */
    public function testSuperExecute () {
        $_SESSION = array ();
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal

        // confirm super mass deletion via password
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massTag = new MassTag;

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be tagged
        $_POST['modelType'] = 'Contacts';
        $_POST['tags'] = array ('#test3', '#test4');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $uid = null;
        while (true) {
            $this->obStart ();
            $massTag->superExecute ($uid, 24, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ()); $this->obEndClean ();
            $uid = $retVal['uid'];
            $this->assertTrue (!isset ($retVal['errorCode']));
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal);
            $remainingIds = Yii::app ()->db->createCommand ("
                SELECT DISTINCT(t.id)
                FROM x2_contacts AS t
                WHERE t.id NOT in (
                    SELECT itemId AS id
                    FROM x2_tags
                    WHERE itemId=t.id and tag like binary '#test3'
                ) and t.id not in (
                    SELECT itemId AS id
                    FROM x2_tags
                    WHERE itemId=t.id and tag like binary '#test4'
                )
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ();
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($remainingIds);
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($_SESSION);
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                $this->assertEquals (
                    $remainingIds, array_reverse ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
            }
        }
    }

}

?>
