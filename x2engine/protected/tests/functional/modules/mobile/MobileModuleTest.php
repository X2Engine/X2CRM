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




Yii::import ('application.tests.functional.modules.mobile.*');

class MobileModuleTest extends MobileModuleTestBase {


    public $fixtures = array (
        'events' => array ('Events', '.MobileModuleTest'),
    );

    /**
     * Initiates a suite of QUnit tests and then parses the QUnit output. Asserts that all tests 
     * pass in time limit and prints QUnit test output otherwise.
     */
    public function testTest () {
        $this->setTimeout (60 * 1000); 
        $this->waitForElementPresent ("css=#qunit-testresult");

        $this->setTimeout (15 * 60 * 1000); 
        $this->waitForCondition (
            "!window.document.querySelector ('#qunit-testresult').innerHTML.match (/Running/)");
        $this->storeEval (
            "window.$('#qunit-testresult')[0].innerHTML",
            'text');
        $text = $this->getExpression ('${text}');

        $matches = array ();
        preg_match ('/class="passed">(\d+)</', $text, $matches);
        $this->assertTrue (isset ($matches[1]), Yii::t('app', 'Failed to parse qunit output'));
        $passed = $matches[1];

        $matches = array ();
        preg_match ('/class="total">(\d+)</', $text, $matches);
        $this->assertTrue (isset ($matches[1]), Yii::t('app', 'Failed to parse qunit output'));
        $total = $matches[1];

//        $matches = array ();
//        preg_match ('/class+"failed">(\d+)</', $text, $matches);
//        $this->assertTrue (isset ($matches[1]), Yii::t('app', 'Failed to parse qunit output'));
//        $failed = $matches[1];

        // TODO: use a php html parser and pretty-print the test output
        $this->storeEval (
            "window.$('#qunit-tests')[0].innerHTML",
            'testOutput');
        $testOutput = $this->getExpression ('${testOutput}');

        if ($total !== $passed) {
            TestingAuxLib::log ($testOutput);
        }

        $this->assertEquals ($total, $passed);

    }
}

?>
