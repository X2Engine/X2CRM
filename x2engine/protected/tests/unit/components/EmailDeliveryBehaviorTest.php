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
 * 
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailDeliveryBehaviorTest extends X2TestCase {

    /**
     * Test the parsing of a recipient header (i.e. "To:") into a PHPMailer-compatible array.
     */
    public function testAddressHeaderToArray() {
        $addressHeader = '"Butts, Seymour" <seymour@butts.com>, "I.P. Freely"<ip@free.ly>, johnsmith@gmail.com, <only@email.com>';
        $addressArray = array(
            array('Butts, Seymour','seymour@butts.com'),
            array('I.P. Freely','ip@free.ly'),
            array('','johnsmith@gmail.com'),
            array('','only@email.com'),
        );
        $addressHeaderArray = EmailDeliveryBehavior::addressHeaderToArray($addressHeader);
        $this->assertEquals($addressArray,$addressHeaderArray);

        // Test adjustments to handle a few other unexpected formats
        $unexpectedAddressHeader = 'Quotes-Test, No <noquotes@email.com>, "\'Twice, Quoted\'" <twice@email.com>, \'Quoted, Single\' <single@email.com>, No Quotes <noquotes@email.com>, \'Single Quoted\' <single@email.com>, "\'Twice Quoted\'" <twice@email.com>';
        $unexpectedAddressArray = array(
            array('Quotes-Test, No','noquotes@email.com'),
            array("Twice, Quoted", 'twice@email.com'),
            array('Quoted, Single','single@email.com'),
            array('No Quotes','noquotes@email.com'),
            array('Single Quoted','single@email.com'),
            array("Twice Quoted", 'twice@email.com'),
        );
        $addressHeaderArray = EmailDeliveryBehavior::addressHeaderToArray($unexpectedAddressHeader);
        $this->assertEquals($unexpectedAddressArray,$addressHeaderArray);

        // Test compatibility with a header composed of combined formats
        $combined = $unexpectedAddressHeader . ', ' . $addressHeader;
        $addressHeaderArray = EmailDeliveryBehavior::addressHeaderToArray($combined);
        $this->assertEquals(array_merge($unexpectedAddressArray, $addressArray),$addressHeaderArray);
    }

}

?>
