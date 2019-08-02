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
 * @package application.tests.unit.modules.contacts.models
 */
class X2LeadsTest extends X2DbTestCase {

    public $fixtures = array(
        'x2Leads' => array('X2Leads', '.X2LeadsTest'),
    );


    public function testConvertToOpportunity () {
        $lead1 = $this->x2Leads ('lead1');

        $leadAttrs = $lead1->getAttributes ();

        $opportunity = $lead1->convert ('Opportunity');

        $opportunityAttrs = $opportunity->getAttributes ();

        unset ($leadAttrs['id']);
        unset ($leadAttrs['nameId']);
        unset ($leadAttrs['firstName']);
        unset ($leadAttrs['lastName']);
        unset ($leadAttrs['createDate']);
        unset ($leadAttrs['converted']);
        unset ($leadAttrs['conversionDate']);
        unset ($leadAttrs['convertedToType']);
        unset ($leadAttrs['convertedToId']);
        unset ($opportunityAttrs['id']);
        unset ($opportunityAttrs['nameId']);
        unset ($opportunityAttrs['createDate']);

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($leadAttrs);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($opportunityAttrs);

        // ensure that opportunity has all attributes of lead, with exceptions
        $this->assertTrue (sizeof (array_diff_assoc ($leadAttrs, $opportunityAttrs)) === 0);

        // test the testing method itself
        $leadAttrs['name'] = '';
        $this->assertFalse (sizeof (array_diff_assoc ($leadAttrs, $opportunityAttrs)) === 0);

    }

    public function testConvertToContact () {
        $lead2 = $this->x2Leads ('lead2');
        $targetModel = $lead2->convert ('Contacts');

        $this->assertFalse ($targetModel->hasErrors ());
        $this->assertTrue (!isset ($lead2->errorModel));

        // lead3 is missing required fields
        $lead3 = $this->x2Leads ('lead3');
        $targetModel = $lead3->convert ('Contacts');
        $this->assertTrue ($targetModel->hasErrors ('firstName'));
        $this->assertTrue ($targetModel->hasErrors ('lastName'));
        $this->assertTrue ($lead3->errorModel->hasErrors ('firstName'));
        $this->assertTrue ($lead3->errorModel->hasErrors ('lastName'));
    }   

}

?>
