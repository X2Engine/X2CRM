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






class MassUpdateTest extends X2DbTestCase {

    // skipped since it nukes the auth item data 
    protected static $skipAllTests = true;

//    public $fixtures = array (
//        'contacts' => array ('Contacts', '.MassDeleteTest'),
//        'users' => 'User',
//        'profiles' => 'Profile',
//        'authItems' => array (':x2_auth_item', '.MassDeleteTest'),
//        'authItemChildren' => array (':x2_auth_item_child', '.MassDeleteTest'),
//        'roles' => array ('Roles', '.MassDeleteTest'),
//        'roleToUser' => array (':x2_role_to_user', '.MassDeleteTest'),
//        'authAssignment' => array (':x2_auth_assignment', '.MassDeleteTest'),
//    );

    /**
     * Ensure that a user without delete access cannot mass delete records
     */
    public function testSuperExecutePermissions () {
        $contacts = Contacts::model ()->updateByPk (
            array (1, 2, 3, 4), array ('assignedTo' => 'testuser'));
        $expectedFailures = intval (Yii::app ()->db->createCommand ("
            SELECT count(*)
            FROM x2_contacts
            WHERE id < 20 and assignedTo!='testuser'
            ORDER by firstName desc
        ")->queryScalar ());
        $expectedSuccesses = intval (Yii::app ()->db->createCommand ("
            SELECT count(*)
            FROM x2_contacts
            WHERE id < 20 and assignedTo='testuser'
            ORDER by firstName desc
        ")->queryScalar ());
        $this->assertNotEquals ($expectedFailures, $expectedSuccesses);
        $this->assertNotEquals (0, $expectedSuccesses);
        $this->assertNotEquals (0, $expectedFailures);

        $sessionId = TestingAuxLib::curlLogin ('testuser', 'password');
        $cookies = "PHPSESSID=$sessionId;";

        // perform mass update
        $data = array (
            'modelType' => 'Contacts',
            'massAction' => 'updateFields',
            'gvSelection' => range (1, 19),
            'fields' => array (
                'firstName' => 'test'
            ),
        );
        $curlHandle = curl_init (TEST_BASE_URL.'contacts/x2GridViewMassAction');
        curl_setopt ($curlHandle, CURLOPT_POST, true);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt ($curlHandle, CURLOPT_HEADER, 1);
        curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, http_build_query ($data));
        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
        ob_start ();
        $response = CJSON::decode (curl_exec ($curlHandle));
        ob_clean ();
        print_r ($response);
        $this->assertEquals (
            $expectedSuccesses.' records updated', $response['success'][0]);
        $this->assertEquals (
            $expectedFailures, count ($response['notice']));
    }

}

?>
