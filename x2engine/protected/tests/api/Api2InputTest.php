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




Yii::import('application.tests.api.Api2TestBase');

/**
 * Really basic data-saving and deletion tests.
 *
 * These tests are currently really shallow (as of 4.1) because time is running 
 * out. There may still thus be bugs lurking in the depths of {@link Api2Controller}
 *
 * @todo Expand upon and generalize the tests for completeness/thoroughness
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2InputTest extends Api2TestBase {

    public $fixtures = array(
        'contacts' => 'Contacts',
        'accounts' => 'Accounts',
        'relationships' => 'Relationships',
        'tags' => 'Tags',
        'user' => 'User',
    );

    /**
     * Test can be reinstated if API support for upserting is added
     */
//    public function testUpdate () {
//        $this->action = 'model';
//        // create with PUT
//        $emailNotFound = 'emailNotFound@example.com';
//        $contact = array(
//            'firstName' => 'notFound',
//            'lastName' => 'notFound',
//            'visibility' => 1,
//            'trackingKey' => '1234',
//        );
//        $ch = $this->getCurlHandle(
//            'PUT',
//            array('{modelAction}'=>"Contacts/by:email={$emailNotFound}.json"),
//            'admin',$contact);
//        $response = json_decode(curl_exec($ch),1);
//        print_r ($response);
//        $id = $response['id'];
//        $this->assertResponseCodeIs(200, $ch);
//        $this->assertTrue((bool) ($newContact = Contacts::model()->findBySql(
//                "SELECT * FROM x2_contacts
//                WHERE email='$emailNotFound'")));
//
//        // update with PUT
//        $contact['firstName'] = 'found';
//        $ch = $this->getCurlHandle(
//            'PUT',
//            array('{modelAction}'=>"Contacts/{$id}.json"),
//            'admin',$contact);
//        $response = json_decode(curl_exec($ch),1);
//        $this->assertResponseCodeIs(200, $ch);
//        $this->assertTrue((bool) ($newContact = Contacts::model()->findBySql(
//                "SELECT * FROM x2_contacts
//                WHERE id=$id AND firstName='found' AND
//                AND email='$emailNotFound'")));
//    }

    /**
     * Really rudimentary test: contact
     */
    public function testContacts() {
        $this->action = 'model';
        // Create
        $contact = array(
            'firstName' => 'Walt',
            'lastName' => 'White',
            'email' => 'walter.white@sandia.gov',
            'visibility' => 1,
            'trackingKey' => '1234',
        );
        $ch = $this->getCurlHandle('POST',array('{modelAction}'=>'Contacts'),'admin',$contact);
        $response = json_decode(curl_exec($ch),1);
        $id = $response['id'];
        $this->assertResponseCodeIs(201, $ch);
        $this->assertTrue((bool) ($newContact = Contacts::model()->findBySql(
                'SELECT * FROM x2_contacts
                WHERE firstName="Walt" 
                AND lastName="White" 
                AND email="walter.white@sandia.gov"
                AND trackingKey="1234"')));

        // Update
        $contact['firstName'] = 'Walter';
        $ch = $this->getCurlHandle('PUT',array('{modelAction}'=>"Contacts/$id.json"),'admin',$contact);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(200, $ch);
        $newContact->refresh();
        $this->assertEquals($contact['firstName'],$newContact['firstName']);

        // Update by attributes:
        $contact['firstName'] = 'Walter "Heisenberg"';
        $ch = $this->getCurlHandle('PUT',
                array(
                    '{modelAction}'=>"Contacts/by:email={$contact['email']}.json"
                ),
                'admin',
                $contact);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(200, $ch);
        $newContact->refresh();
        $this->assertEquals($contact['firstName'],$newContact['firstName']);


        // Delete
        $ch = $this->getCurlHandle('DELETE',array('{modelAction}'=>"Contacts/$id.json"),'admin');
        $response = curl_exec($ch);
        $this->assertEmpty($response);
        $this->assertResponseCodeIs(204, $ch);
        $this->assertFalse(Contacts::model()->exists('id='.$id));

        // Validation error (missing visibility):
        $contact = array(
            'firstName' => 'Hank',
            'lastName' => 'Schrader',
        );
        $ch = $this->getCurlHandle('POST', array('{modelAction}' => 'Contacts'), 'admin', $contact);
        $response = json_decode(curl_exec($ch), 1);
        $this->assertResponseCodeIs(422, $ch);

        // Incorrect method of creating new contact (should respond with 400)
        $contact = array(
            'firstName' => 'Hank',
            'lastName' => 'Schrader',
        );
        $ch = $this->getCurlHandle('PUT', array('{modelAction}' => 'Contacts'), 'admin', $contact);
        $response = json_decode(curl_exec($ch), 1);
        $this->assertResponseCodeIs(400, $ch);
    }

    /**
     * Tests the special alternate way of managing actions
     * (through "api2/{_class}/{_id}/Actions")
     */
    public function testActions() {
        $this->action = 'model';
        $action = array(
            'actionDescription' => 'Lunch meeting',
            'type' => 'event',
            // these should be set automatically by the api2 actions kludge
            //'associationType' => 'contacts',
            //'associationId' => $this->contacts('testFormula')->id,
            'dueDate' => 1398987130,
            'complete' => 'No',
        );
        $ch = $this->getCurlHandle('POST',array('{modelAction}'=>'Contacts/'.$this->contacts('testFormula')->id.'/Actions'),'admin',$action);
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(201, $ch,X2_TEST_DEBUG_LEVEL > 1?$response:'');
        $response = json_decode($response,1);
        $this->assertEquals($action['actionDescription'],$response['actionDescription']);
        $this->assertEquals($action['type'],$response['type']);
        $this->assertEquals($action['complete'],$response['complete']);
        $this->assertEquals($this->contacts('testFormula')->id,$response['associationId']);
        $this->assertEquals('contacts',$response['associationType']);
        // Do it again but with bad ID to test the actions association check kludge
        $ch = $this->getCurlHandle('POST',array('{modelAction}'=>'Contacts/242424242/Actions'),'admin',$action);
        curl_exec($ch);
        $this->assertResponseCodeIs(404, $ch);
        // Delete through a similar URL:
        $ch = $this->getCurlHandle('DELETE',array('{modelAction}'=>'Contacts/'.$this->contacts('testFormula')->id.'/Actions/'.$response['id'].'.json'));
        $response = curl_exec($ch);
        $this->assertEmpty($response);
        $this->assertResponseCodeIs(204, $ch);
    }

    public function testRelationships() {
        // Delete a relationship from the fixture data:
        $this->action = 'relationships_get';
        $ch = $this->getCurlHandle('DELETE',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id,
            '{_relatedId}' => $this->relationships('blackMesaContact')->id
        ),'admin');
        $oldRelationship = $this->relationships('blackMesaContact')->getAttributes(array(
            'id',
            'secondType',
            'secondId',
        ));
        $oldRelationId = $this->relationships('blackMesaContact')->id;
        $response = curl_exec($ch);
        $this->assertEmpty($response);
        $this->assertResponseCodeIs(204, $ch);
        $this->assertFalse((bool)Relationships::model()->findByPk($oldRelationId));

        // Re-create it from fixture data:
        $this->action = 'relationships';
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id
        ),'admin',$oldRelationship);
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(201, $ch);

        // Validation should fail due to duplicate record
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id
        ),'admin',$oldRelationship);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(422, $ch);

        // Create it once more but with a nonexistent ID to test that validation works
        $oldRelationship['secondId'] = 2424242;
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id
        ),'admin',$oldRelationship);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(422, $ch);
        
    }

    /**
     * Test saving tags
     */
    public function testTags() {
        $this->action = 'tags';
        // The following contact should start with no tags on it:
        $contact = $this->contacts('testFormula');
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $contact->id
        ),'admin',$tagsPut = array('#not-a-talker','#carries-a-crowbar','#enemy-of-vortigaunts'));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(200, $ch);
        $tags = $contact->getTags();
        $this->assertEquals($tagsPut,$tags);
        // Add some more tags
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $contact->id
        ),'admin',$moreTagsPut = array('#enemy-of-combine'));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(200, $ch);
        $contact = Contacts::model()->findByPk($contact->id);
        $tags = $contact->getTags();
        $this->assertEquals($allTags=array_merge($tagsPut,$moreTagsPut),$tags);
        // Delete a tag:
        $this->action = 'tags_get';
        $ch = $this->getCurlHandle('delete',array(
            '{_class}'=>'Contacts',
            '{_id}' => $contact->id,
            '{tagname}' => 'enemy-of-vortigaunts'
        ));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(204, $ch);
        $contact = Contacts::model()->findByPk($contact->id);
        $tags = $contact->getTags();
        $this->assertEquals(array_values(array_diff($allTags,array('#enemy-of-vortigaunts'))),$tags);
    }

}

?>
