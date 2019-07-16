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
class ApiX2FlowTest extends Api2TestBase {

    public $fixtures = array(
        'contacts' => 'Contacts',
        'user' => 'User',
        'flows' => array ('X2Flow', '.ApiX2FlowTest'),
    );

    /**
     * Ensure that update and create trigger appropriate flows
     */
    public function testContacts() {
        X2FlowTestingAuxLib::clearLogs ($this);
        $this->action = 'model';

        $this->assertEquals (array (), TriggerLog::model ()->findAll ());

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

        $logs = TriggerLog::model ()->findAll ();
        $this->assertEquals (1, count ($logs));
        $trace = X2FlowTestingAuxLib::getTraceByFlowId ($this->flows ('flow1')->id);
        $this->assertTrue (is_array ($trace));
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($trace));

        // Update
        $contact['firstName'] = 'Walter';
        $ch = $this->getCurlHandle('PUT',array('{modelAction}'=>"Contacts/$id.json"),'admin',$contact);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(200, $ch);
        $newContact->refresh();
        $this->assertEquals($contact['firstName'],$newContact['firstName']);

        $logs = TriggerLog::model ()->findAll ();
        $this->assertEquals (2, count ($logs));
        $trace = X2FlowTestingAuxLib::getTraceByFlowId ($this->flows ('flow2')->id);
        $this->assertTrue (is_array ($trace));
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($trace));

    }
}

?>
