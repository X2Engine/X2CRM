<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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
