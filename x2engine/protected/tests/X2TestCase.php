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




Yii::import('application.components.sortableWidget.*');
Yii::import('application.components.X2Settings.*');
Yii::import('application.components.behaviors.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.bugReports.models.*');

/**
 * Base non-database test class
 * @package application.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2TestCase extends CTestCase {
    
    private $_oldSession;
    
    public function setUp() {
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getCaseTimer ();
            $timer->start ();
        }
        TestingAuxLib::log ("running test case: ".$this->getName ());
        if(isset($_SESSION)){
            $this->_oldSession = $_SESSION;
        }
        parent::setUp();
    }
    
    public function tearDown() {
        if(isset($this->_oldSession)){
            $_SESSION = $this->_oldSession;
        }
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getCaseTimer ();
            TestingAuxLib::log ("time elapsed for test case: {$timer->stop ()->getTime ()}");
        }

        parent::tearDown();
    }

    public static function getPath ($arg) {
        $reflect = new ReflectionClass ($arg);
        return ltrim (
            preg_replace ('/^'.preg_quote (__DIR__, '/').'/', '', $reflect->getFileName ()), '/');
    }
    
    public static function setUpBeforeClass(){
        if (!YII_UNIT_TESTING) throw new CException ('YII_UNIT_TESTING must be set to true');
        $testClass = get_called_class();
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getClassTimer ();
            $timer->start ();
        }
        TestingAuxLib::log ("running test class: ".self::getPath ($testClass));

        Yii::app()->beginRequest();
        Yii::app()->fixture->load(array(
            'profile'=>'Profile',
            'user' => 'User'));
        parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass(){
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getClassTimer ();
            TestingAuxLib::log ("time elapsed for test class: {$timer->stop ()->getTime ()}");
        }

        parent::tearDownAfterClass();
    }

    /**
     * Assert thet the model can be saved without error and, if errors are present, print
     * out the corresponding error messages.
     * @param CActiveRecord $model
     */
    public function assertSaves (CActiveRecord $model) {
        $saved = $model->save ();
        if ($model->hasErrors ()) {
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($model->getErrors ());
        }
        $this->assertTrue ($saved);
    }
    
    /**
     * Checks that two arrays have the same values regardless of order
     */
    public function assertArrayEquals(array $a, array $b) {
        $equality = false;
        if (count(array_diff($a, $b)) === 0) {
            foreach ($a as $v) {
                if (!in_array($v, $b)) {
                    break;
                }
            }
            $equality = true;
        }
        $this->assertTrue($equality);
    }

    /**
     * Polyfill for PHPUnit v4.4+ since PHPUnit now aborts test and marks as risky if ob_start is
     * used
     */
    public $_outputBuffer = '';
    public function obStart () {
        // PHPUnit seems to be doing internal output buffering, this is intended to clear that 
        // buffer
        if(ob_get_contents()){
            ob_clean (); 
        }
        $that = $this;
        // documentation for setOutputCallback method is poor, but seems to do what 
        // $output_callback parameter of ob_start does: 
        // http://php.net/manual/en/function.ob-start.php
        $this->setOutputCallback (function ($output) use ($that) {
            $that->_outputBuffer .= $output; // collect output
            return ''; // hide output
        });
    }

    /**
     * Polyfill for PHPUnit v4.4+ since PHPUnit now aborts test and marks as risky if ob_start is
     * used
     */
    public function obClean () {
        $this->_outputBuffer = '';
    }

    /**
     * Polyfill for PHPUnit v4.4+ since PHPUnit now aborts test and marks as risky if ob_start is
     * used
     */
    public function obEndClean () {
        if(ob_get_contents()){
            ob_clean (); 
        }
        $this->_outputBuffer = '';
        $this->setOutputCallback (function ($output) {
            return $output; // display output 
        });
    }

    public function assertUpdates (CActiveRecord $model, array $attrs=null) {
        $this->assertTrue ($model->update ($attrs));
    }

}

?>
