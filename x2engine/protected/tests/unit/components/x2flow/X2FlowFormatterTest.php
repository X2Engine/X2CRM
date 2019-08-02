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




Yii::import ('application.components.x2flow.X2FlowFormatter');
Yii::import ('application.tests.unit.components.FormatterTestBase');

function falseand () {
    return 'EVIL CODE';
}

function evilcode () {
    return 'EVIL CODE';
}

function false0 () {
    return 'EVIL CODE';
}

function falseecho () {
    return 'EVIL CODE';
}

/**
 */
class X2FlowFormatterTest extends FormatterTestBase {

    public $fixtures = array(
        'contacts' => 'Contacts',
        'accounts' => 'Accounts'
    );

    public function testParseFormula() {
        $this->parseFormulaAssertions ('X2FlowFormatter');

        $str = "={user.username}.'suffix'"; // formula with shortcode
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertEquals ('adminsuffix', $str);

        $contact = $this->contacts ('testUser');
        $account = $this->accounts ('testQuote');
        $contact->company = $account->nameId;
        $this->assertSaves ($contact);
        $str = "={user.username}.{company.name}.'suffix'"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ('model' => $contact));
        $this->assertEquals ('admin'.$account->name.'suffix', $str);

        $str = "=(true ? 'a' : 'b') === 'a' ? 'c' : 'd'"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ('model' => $contact));
        $this->assertEquals ('c', $str);

        $str = "=(true ? 'a' : 'b') === 'a' ? {email} : {user.username}"; 
        $email = 'test@example.com';
        list ($err, $str) = X2FlowFormatter::parseFormula (
            $str, 
            array (
                'model' => $contact,
                'email' => $email
            ));
        $this->assertEquals ($email, $str);

        $str = "=false and true"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertEquals (false, $str);

        $str = "=return true and true;"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertEquals (true, $str);

        $str = "=falseand ()"; 
        list ($err, $str) = X2FlowFormatter::parseFormula (
            $str, 
            array (
                'model' => $contact,
                'email' => $email
            ));
        $this->assertNotEquals ('EVIL CODE', $str);

        $str = "=false0 ()"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertNotEquals ('EVIL CODE', $str);

        $str = "=falseecho ()"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertNotEquals ('EVIL CODE', $str);

        $str = "=\$a"; // no variables
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertFalse ($err);

        $str = "=time()"; // whitelisted function
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertEquals (time (), $str);

        $str = "=''"; // strings
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertEquals ('', $str);

        $str = "='\'.evilcode ()'"; // no unbalanced single quotes
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertFalse ($err);

        $str = "='\''"; // no escaped single quotes
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertFalse ($err);

        $str = "='\'"; // no escaped final quote
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertFalse ($err);

        $str = "='test'.'concat'"; 
        list ($err, $str) = X2FlowFormatter::parseFormula ($str, array ());
        $this->assertEquals ('testconcat', $str);
    }

    public function testReplaceVariables () {
        TestingAuxLib::suLogin ('admin');
        $str = '{user.username}'; // shortcode which returns model
        $str = X2FlowFormatter::replaceVariables ($str, array ());
        $this->assertEquals ('admin', $str);

        $str = '{company.name}'; // link type
        $contact = $this->contacts ('testUser');
        $contact->company = $this->accounts ('testQuote')->nameId;
        $this->assertSaves ($contact);
        $str = X2FlowFormatter::replaceVariables ($str, array ('model' => $contact));
        $this->assertEquals (preg_replace ('/_.*$/', '', $contact->company), $str);

        $str = '{company}'; // model attribute replacement
        $contact = $this->contacts ('testUser');
        $str = X2FlowFormatter::replaceVariables (
            $str, array ('model' => $contact), '', true, false);
        $this->assertEquals (preg_replace ('/_.*$/', '', $contact->company), $str);

        $str = '{email}'; // param extraction
        $email = 'test@example.com';
        $str = X2FlowFormatter::replaceVariables ($str, array ('email' => $email));
        $this->assertEquals ($email, $str);
    }

}

?>
