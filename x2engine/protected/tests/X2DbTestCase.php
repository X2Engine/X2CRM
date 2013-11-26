<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::import('application.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');

/**
 * Class for database unit testing that performs additional preparation
 * 
 * @package X2CRM.tests
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
    public static abstract function referenceFixtures();

    private static $_referenceFixtureRecords = array();

    private static $_referenceFixtureRows = array();


    /**
     * Loads "reference fixtures" defined in {@link referenceFixtures()} and
     * sets up some special environment variables before proceeding.
     */
    public static function setUpBeforeClass(){
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
        
        $admin = CActiveRecord::model('Admin')->findByPk(1);
        $admin->emailDropbox_logging = 1;
        Yii::app()->params->admin = $admin;
        Yii::app()->params->profile = CActiveRecord::model('Profile')->findByPk(1);
        Yii::app()->params->noSession = true;
        // Create inverse mapping between currency symbols and their corresponding 3-letter codes:
        $locale = Yii::app()->locale;
        $curSyms = array();
        foreach(Yii::app()->params->supportedCurrencies as $curCode) {
            $curSyms[$curCode] = $locale->getCurrencySymbol($curCode);
        }
        Yii::app()->params->supportedCurrencySymbols = $curSyms; // Code to symbol

        // Load "reference fixtures", needed for reference, which do not need
        // to be reloaded after every single test method:
        $testClass = get_called_class();
        $refFix = call_user_func("$testClass::referenceFixtures");
        $fm = Yii::app()->getComponent('fixture');
        if(is_array($refFix)){
            $fm->load($refFix);
            foreach($refFix as $alias => $table){
                self::$_referenceFixtureRows[$alias] = $fm->getRows($alias);
                if(strpos($table, ':') !== 0){
                    foreach(self::$_referenceFixtureRows[$alias] as $rowAlias => $row){
                        $model = CActiveRecord::model($table);
                        $key = $model->getTableSchema()->primaryKey;
                        if(is_string($key))
                            $pk = $row[$key];
                        else{
                            foreach($key as $k)
                                $pk[$k] = $row[$k];
                        }
                        self::$_referenceFixtureRecords[$alias][$rowAlias] = $model->findByPk($pk);
                    }
                }
            }
        }
        parent::setUpBeforeClass();
    }

    /**
     * Override that copies the original key/iv back
     */
    public static function tearDownAfterClass(){
        parent::tearDownAfterClass();
        foreach(array('iv','key') as $ext) {
            rename(self::${$ext}.'.bak',self::${$ext});
        }
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
        } else
            return parent::__call($name, $params);
    }
}

?>
