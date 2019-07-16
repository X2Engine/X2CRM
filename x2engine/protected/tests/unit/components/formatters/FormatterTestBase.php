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
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class FormatterTestBase extends X2DbTestCase {

    public function parseFormulaAssertions ($formatterClass) {
        $formula = "='My name is '.".'{name}'.".' and I work at '.".'{company.name}'.".' which '.".'{company.description};';
        $contact = $this->contacts('testFormula');
        $evald = $formatterClass::parseFormula($formula,array('model'=>$contact));
        $this->assertTrue($evald[0],$evald[1]);
        $this->assertEquals('My name is '.$this->contacts('testFormula')->name
                .' and I work at '.$this->accounts('testQuote')->name
                .' which '.$this->accounts('testQuote')->description,
                $evald[1]);

        // Now let's throw some bad code in there:
        //
        // system call:
        $formula = '=exec("echo YOU SHOULD NOT SEE THIS MESSAGE, EVER");';
        $evald = $formatterClass::parseFormula($formula,array('model'=>$contact));
        $this->assertFalse($evald[0]);
        $formula = '="Unfortunately, string expressions in formulae with anything
            aside from spaces, alphanumerics and underscores aren\'t supported yet."';
        $evald = $formatterClass::parseFormula($formula,array('model' => $contact));
        $this->assertFalse($evald[0]);

        // Test typecasting:
        //
        // integer:
        $contact->createDate = '1';
        $evald = $formatterClass::parseFormula("={createDate}+2",array('model'=>$contact));
        $this->assertEquals(3,$evald[0]);
        // boolean:
        $contact->doNotEmail = true;
        $evald = $formatterClass::parseFormula("={doNotEmail} or false",array('model'=>$contact));
        $this->assertTrue($evald[0]);
        // double:
        $contact->dealvalue = '25.3';
        $evald = $formatterClass::parseFormula("={dealvalue}*44.1",array('model'=>$contact));
        $this->assertEquals(1115.73,$evald[0]);
    }

}

?>
