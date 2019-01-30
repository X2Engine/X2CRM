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




Yii::import('application.tests.mocks.*');
Yii::import('application.models.*');
Yii::import('application.components.*');
Yii::import('application.components.behaviors.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.bugReports.models.*');

/**
 * Base class for database unit testing that performs additional preparation
 * 
 * @package application.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class X2DbTestCase extends CDbTestCase {

    public static $iv;
    public static $key;

    /**
     * Fixtures that need to be loaded for reference but won't be touched
     * throughout the entire case, only looked up. This is to speed things up a
     * bit by eliminating the need to load everything multiple times throughout
     * the class.
     * @var array
     */
    public static function referenceFixtures() {
        return array();
    }

    protected static $skipAllTests = false;
    
    protected static $loadFixtures = X2_LOAD_FIXTURES;
    protected static $loadFixturesForClassOnly = X2_LOAD_FIXTURES_FOR_CLASS_ONLY;

    private static $_referenceFixtureRecords = array();

    private static $_referenceFixtureRows = array();
    
    private $_oldSession;
    
    public function setUp () {
        // if loadFixturesForClassOnly was true, reenable fixture loading since we've already
        // skipped the loading of the fixtures directory and still want to have fixtures loaded
        // on a per test case basis
        if (self::$loadFixturesForClassOnly)
            $this->getFixtureManager ()->loadFixtures = true;

        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getCaseTimer ();
            $timer->start ();
        }
        TestingAuxLib::log ("running test case: ".$this->getName ());

        if (static::$skipAllTests) {
            $this->markTestSkipped ();
        }
        if(isset($_SESSION)){
            $this->_oldSession = $_SESSION;
        }
        $this->fixtures = is_array ($this->fixtures) ? $this->fixtures : array ();
        parent::setUp ();
    }

    public static function getPath ($arg) {
        $reflect = new ReflectionClass ($arg);
        return ltrim (
            preg_replace ('/^'.preg_quote (__DIR__, '/').'/', '', $reflect->getFileName ()), '/');
    }

    /**
     * Performs environmental set-up similar to that in {@link ApplicationConfigBehavior}
     */
    public static function setUpAppEnvironment($full=false) {
        // uses a specific key/iv for unit testing
        foreach(array('iv','key') as $ext) {
            $file = Yii::app()->basePath."/config/encryption.$ext";
            $testFile = Yii::app()->basePath."/tests/data/encryption/encryption.$ext";
            self::${$ext} = $file;
            if(file_exists($file)){
                rename($file,"$file.bak");
                copy($testFile, $file);
            }
        }
        EncryptedFieldsBehavior::setup(self::$key,self::$iv);
        if ($full) self::setUpAppEnvironment2 ();
    }

    /**
     * For environment setup actions which can't be performed until after the reference fixtures
     * have been set up.
     */
    public static function setUpAppEnvironment2 () {
        Yii::app()->beginRequest();
        Yii::app()->suModel = User::model()->findByPk(1);
    }

    public static function tearDownAppEnvironment() {
        foreach(array('iv','key') as $ext) {
            rename(self::${$ext}.'.bak',self::${$ext});
        }
    }

    /**
     * Loads "reference fixtures" defined in {@link referenceFixtures()} and
     * sets up some special environment variables before proceeding.
     */
    public static function setUpBeforeClass(){
        if (!YII_UNIT_TESTING) throw new CException ('YII_UNIT_TESTING must be set to true');
        Yii::app()->cache->flush ();
        self::setUpAppEnvironment(); 

        // Load "reference fixtures", needed for reference, which do not need
        // to be reloaded after every single test method:
        $testClass = get_called_class();
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getClassTimer ();
            $timer->start ();
        }
        TestingAuxLib::log ("running test class: ".self::getPath ($testClass));

        $refFix = call_user_func("$testClass::referenceFixtures");
        $fm = Yii::app()->getComponent('fixture');
        self::$_referenceFixtureRows = array();
        self::$_referenceFixtureRecords = array();
        if(is_array($refFix)){
            Yii::import('application.components.X2Settings.*');
            $fm->load($refFix);
            if(self::$loadFixtures || self::$loadFixturesForClassOnly){
                foreach($refFix as $alias => $table){
                    $tableName = is_array($table) ? $table[0] : $table;
                    self::$_referenceFixtureRows[$alias] = $fm->getRows($alias);
                    if(strpos($tableName, ':') !== 0){
                        foreach(self::$_referenceFixtureRows[$alias] as $rowAlias => $row){
                            $model = CActiveRecord::model($tableName);
                            $key = $model->getTableSchema()->primaryKey;
                            if(is_string($key))
                                $pk = $row[$key];
                            else{
                                foreach($key as $k)
                                    $pk[$k] = $row[$k];
                            }
                            self::$_referenceFixtureRecords[$alias][$rowAlias] = 
                                $model->findByPk($pk);
                        }
                    }
                }
            }
        }

        self::setUpAppEnvironment2(); 
        parent::setUpBeforeClass();
    }

    /**
     * Override that copies the original key/iv back
     */
    public static function tearDownAfterClass(){
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getClassTimer ();
            TestingAuxLib::log ("time elapsed for test class: {$timer->stop ()->getTime ()}");
        }

        parent::tearDownAfterClass();
        self::tearDownAppEnvironment();
    }

    public function tearDown () {
        // try to replace mocks with original components in case mocks were set during test case
        TestingAuxLib::restoreX2WebUser ();
        TestingAuxLib::restoreX2AuthManager ();
        TestingAuxLib::restoreController ();
        self::$skipAllTests = false;
        self::$loadFixtures = X2_LOAD_FIXTURES;
        self::$loadFixturesForClassOnly = X2_LOAD_FIXTURES_FOR_CLASS_ONLY;
        if(isset($this->_oldSession)){
            $_SESSION = $this->_oldSession;
        }
        if(X2_TEST_DEBUG_LEVEL > 0){
            $timer = TestingAuxLib::getCaseTimer ();
            TestingAuxLib::log ("time elapsed for test case: {$timer->stop ()->getTime ()}");
        }
        return parent::tearDown ();
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

    public function assertUpdates (CActiveRecord $model, array $attrs=null) {
        $this->assertTrue ($model->update ($attrs));
    }
    
    /**
     * Checks that two arrays have the same values regardless of order
     */
    public function assertArrayEquals(array $a, array $b) {
        $equality = false;
        if (count(array_diff($a, $b)) === 0) {
            foreach ($a as $k => $v) {
                if (!in_array($v, $b)) {
                    break;
                }
            }
            $equality = true;
        }
        $this->assertTrue($equality);
    }

    public function __get($name) {
        if(array_key_exists($name,self::$_referenceFixtureRows)) {
            return self::$_referenceFixtureRows[$name];
        } else {
            return parent::__get($name);
        }
    }

    public function __call($name, $params){
        if(array_key_exists($name,self::$_referenceFixtureRecords)) {
            if(isset($params[0])) {
                if(array_key_exists($params[0],self::$_referenceFixtureRecords[$name])) {
                    return self::$_referenceFixtureRecords[$name][$params[0]];
                }
            }
            throw new Exception('Record alias invalid/not specified.');
        } else {
            return parent::__call($name, $params);
        }
    }

    /**
     * Polyfill for PHPUnit v4.4+ since PHPUnit now aborts test and marks as risky if ob_start is
     * used
     */
    public $_outputBuffer = '';
    public $_oldBuffer = '';
    public function obStart () {
        // PHPUnit seems to be doing internal output buffering, this is intended to clear that 
        // buffer
        if($this->_oldBuffer = ob_get_contents()){
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
        echo $this->_oldBuffer; // add old buffer contents to PHPUnit's output buffer
    }

    protected function assertModelArrayEquality (array $expected, array $actual, $sort=true) {
        $expectedIds = array_map (function ($model) {
            return $model->id; 
        }, $expected);
        if ($sort) sort ($expectedIds);
        $actualIds = array_map (function ($model) {
            return $model->id; 
        }, $actual);
        if ($sort) sort ($actualIds);
        $this->assertEquals ($expectedIds, $actualIds);
    }

}

?>
