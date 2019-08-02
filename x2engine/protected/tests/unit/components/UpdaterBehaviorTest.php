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




Yii::import('application.components.util.*');

/**
 * Test suite for the application updater.
 * 
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdaterBehaviorTest extends FileOperTestCase {

    private $_admin;

    /**
     * For operations when the configuration will be modified, this stores its 
     * location so that it (and the backup copy of it) can be found in any non-
     * static method.
     * @var type 
     */
    public $configFile;

    /**
     * Array of tables used in update/upgrade SQL testing.
     * @var array 
     */
    public $testTables;


    public static function setUpBeforeClass(){
        X2DbTestCase::setUpAppEnvironment();
        return parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(){
        X2DbTestCase::tearDownAppEnvironment();
        return parent::tearDownAfterClass();
    }

    //////////////////////
    // NON-TEST METHODS //
    //////////////////////
    // These methods are used by test methods and assertions. They may contain
    // assertions, but they are not called directly in PHPUnit.

    /**
     * Returns an alter table statement given a table description array.
     * @param type $table
     * @return type
     */
    public function addColumns($table){
        $statement = '';
        $statement .= sprintf("ALTER TABLE {$table['name']} ADD COLUMN ");
        $statement .= implode(', ADD COLUMN', self::colDefs($table['newColumns']));
        return $statement;
    }

    /**
     * Returns true or false based on whether a table has a named column.
     *
     * @param type $tblName Table name
     * @param type $colName Column name
     * @return boolean True if the column exists in the table, false otherwise.
     */
    public function columnExists($tblName, $colName){
        try{
            $col = Yii::app()->db->createCommand()->select($colName)->from($tblName)->queryAll();
            return true;
        }catch(CDbException $e){
            return false;
        }
    }

    /**
     * Creates column definition statements.
     * @param type $columns
     * @return type 
     */
    public static function colDefs($columns){
        return array_map(function($c){
                            return "{$c[0]} {$c[1]}";
                        }, $columns);
    }

    /**
     * Returns a create table statement given a table description array.
     * @param type $table
     * @return type
     */
    public function createTable($table){
        return sprintf("CREATE TABLE {$table['name']} (%s) COLLATE = utf8_general_ci;", implode(", ", self::colDefs($table['columns'])));
    }

    /**
     * Remove testing tables.
     */
    public function dropTestTables(){
        foreach($this->testTables as $type => $tables){
            foreach($tables as $table){
                Yii::app()->db->createCommand("DROP TABLE IF EXISTS {$table['name']}")->execute();
            }
        }
    }

    public function getAdmin() {
        if(!isset($this->_admin))
            $this->_admin = Admin::model()->find();
        return $this->_admin;
    }

    /**
     * Instantiates a new CComponent object with {@link UpdaterBehavior attached
     * to it, and returns it.
     * @return CComponent
     */
    public function instantiateUBe($properties = array()){
        $comp = new CComponent();
        $ubconfig = array_merge(array(
            'class' => 'UpdaterBehavior',
            'isConsole' => true,
            'noHalt' => true,
        ),$properties);
        $comp->attachBehavior('UpdaterBehavior', $ubconfig);
        return $comp;
    }

    /**
     * Sets up or tears down some prerequisite for a test case running that are
     * re-used in more than one test.
     * @param type $name
     */
    public function prereq($ube,$name,$setUp = true) {
        if($setUp){
            switch($name){
                case 'updateDir':
                    if(!is_dir($ube->updateDir)) 
                        mkdir($ube->updateDir);
                    break;
                case 'sourceDir':
                    $this->prereq($ube,'updateDir');
                    if(!is_dir($ube->sourceDir))
                        mkdir($ube->sourceDir);
                case 'checksums for parse testing':
                    $this->prereq($ube,'updateDir');
                    $checkSums = "c2ac53d8a42168b605d2985c82a71157  X2WebApplication.php\nac412eb98e8d8d1e7a28b95019f4d2ba  X2WebUser.php\nddc3c3563489271a10f03b4fe3f6efb0  X2WidgetList.php\ned41f84aefee9c0a566cee2bab1d167b  X2Widget.php\ned41f84aefeadfdfdfdfdfdfddaaaaaa  some/really bad/path/with spaces/in.it\n";
                    $expect = array(
                        'X2WebApplication.php' => 'c2ac53d8a42168b605d2985c82a71157',
                        'X2WebUser.php' => 'ac412eb98e8d8d1e7a28b95019f4d2ba',
                        'X2WidgetList.php' => 'ddc3c3563489271a10f03b4fe3f6efb0',
                        'X2Widget.php' => 'ed41f84aefee9c0a566cee2bab1d167b',
                        'some/really bad/path/with spaces/in.it' => 'ed41f84aefeadfdfdfdfdfdfddaaaaaa'
                    );
                    file_put_contents($ube->updateDir.DIRECTORY_SEPARATOR.'contents.md5', $checkSums);
                    return $expect;
                    break;
                case 'checksums with actual files':
                    $this->prereq($ube,'sourceDir');
                    $md5sums = array(
                        'b04ba88d02b42650363e8a302a9be0ef' => 'empty.php',
                        '30490168a7a13733e66e57b826a4b6da' => 'corrupt.php',
                        '76016fc958cb657e9ad54f92abb9da78' => 'file with spaces.js',
                        '1bc166e024f19c3da04a00714551224a' => 'manifest.json'
                    );
                    // Prepare the file with digests in it
                    foreach($md5sums as $digest => $file){
                        copy(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'tests', 'data', 'updatemd5', $file)), $ube->updateDir.DIRECTORY_SEPARATOR.$file);
                    }
                    $md5sums['40490168a7a13733e66e57b826a4b6da'] = 'nonexist.php';
                    $md5sumfile = '';
                    foreach($md5sums as $digest => $file){
                        $md5sumfile .= "$digest  $file\n";
                    }
                    file_put_contents($md5sumfilename = $ube->updateDir.DIRECTORY_SEPARATOR.'contents.md5', $md5sumfile);
                    break;
            }
        }else{ // Teardowns
            switch($name){
                default:
                    FileUtil::rrmdir($ube->updateDir);
                    
            }
        }
    }

    /**
     * Error-handling function that resets everything to the pre-test state and
     * ensures the test tables and test files don't end up multiplying like bunnies
     *
     * @param type $e Error caught
     * @param array $params "Original" parameters
     * @param array $absDeletionList Files in the deletion list
     */
    public function resetAfterChanges($ube,$e = null){
        // Get back to pre-changes state, with test tables:
        $ube->restoreDatabaseBackup();
        // Back to square one completely (if in the scope of an error)
        if(!empty($e)){
            $this->dropTestTables();
        }
        // Restore the original configuration file:
        copy("{$this->configFile}.bak",$this->configFile);

        // Throw (to resume normal chain of assertion failure)
        if(!empty($e))
            throw $e;
    }

    /**
     * Sets up tables in the database for testing update SQL runs.
     *
     * Comment symbols in this method, what they mean:
     * ^1: Should exist after update, but not after failed update/restore
     * ^2: Should not exist after update, but should after failed update/restore
     * ^3: Can exist both after update and after failed update/restore
     */
    public function setupTestTables(){
        $time = $this->testTime;
        $this->testTables = array(
            'new' => array(// ^1
                array(
                    'name' => sprintf('test_new_table_%d', $time),
                    'columns' => array(
                        array('id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY'),
                        array('type', 'VARCHAR(50) NOT NULL'),
                        array('itemId', 'INT NOT NULL')
                    ),
                ),
            ),
            'alter' => array(// ^3
                array(
                    'name' => sprintf('test_altered_table_%d', $time),
                    'columns' => array(
                        array('id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY'),
                        array('type', 'VARCHAR(50) NOT NULL'),
                        array('itemId', 'INT NOT NULL')
                    ),
                    'newColumns' => array(// ^1
                        array('description', 'TEXT NULL')
                    ),
                ),
            ),
            'drop' => array(// ^2
                array(
                    'name' => sprintf('test_dropped_table_%d', $time),
                    'columns' => array(
                        array('id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY'),
                        array('type', 'VARCHAR(50) NOT NULL'),
                        array('itemId', 'INT NOT NULL')
                    ),
                ),
            ),
        );

        $tablesUp = array();
        try{
            foreach($this->testTables as $type => $tables){
                if($type != 'new'){
                    foreach($tables as $table){
                        Yii::app()->db->createCommand($this->createTable($table))->execute();
                        $tablesUp[] = $table['name'];
                    }
                }
            }
        }catch(CDbException $e){
            echo $e->getMessage();
            // Try to delete the tables that got made successfully before
            foreach($tablesUp as $table){
                Yii::app()->db->createCommand("DROP TABLE $table")->execute();
            }
        }
    }

    /**
     * Returns true or false based on whether a named table exists.
     * @param type $tblName Table name
     * @return boolean
     */
    public function tableExists($tblName){
        try{
            $description = Yii::app()->db->createCommand("DESCRIBE $tblName")->queryAll();
            return true;
        }catch(CDbException $e){
            return false;
        }
    }

    ///////////////////////
    // ASSERTION METHODS //
    ///////////////////////

    /**
     * Test that changes were applied (database and file), and resets the files.
     *
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function assertChangesApplied($ube){
        // Now that it's all said and done, verify: did the update/upgrade happen properly?
        try{
            foreach($this->testTables as $type => $tables){
                // New tables that should be there now:
                if($type == 'new'){
                    foreach($tables as $table){
                        $this->assertTableExists($table['name']);
                    }
                }
                // Dropped tables should be gone:
                if($type == 'drop'){
                    foreach($tables as $table){
                        $this->assertTableNotExists($table['name']);
                    }
                }
                // New columns should be there:
                foreach($tables as $table){
                    if(array_key_exists('newColumns', $table)){
                        foreach($table['newColumns'] as $c){
                            $this->assertColumnExists($table['name'], $c[0]);
                        }
                    }
                }
            }

            // Now, some testing that's specific to the scenario of upgrade:
            if($ube->scenario == 'upgrade'){
                $verEd = Yii::app()->db->createCommand()->select('edition,unique_id')->from('x2_admin')->queryRow();
                $this->assertEquals($ube->uniqueId, $verEd['unique_id']);
                $this->assertEquals($ube->manifest['targetEdition'], $verEd['edition']);
            }
            // In the "update" scenario isn't necessary to test the
            // configuration changes; that is already covered by
            // testRegenerateConfig.
        }catch(PHPUnit_Framework_AssertionFailedError $e){
            $this->resetAfterChanges($ube, $e);
        }
    }

    /**
     * Test whether database changes were properly reverted after a restore from a backup
     * @param type $ube
     * @param type $scenario
     * @param type $absDeletionList
     */
    public function assertChangesReverted($ube){
        // Now that it's all said and done, verify: did we restore properly?
        try{
            foreach($this->testTables as $type => $tables){
                // New tables aren't there yet:
                if($type == 'new'){
                    foreach($tables as $table){
                        $this->assertTableNotExists($table['name']);
                    }
                }
                // Dropped tables are restored:
                if($type == 'drop'){
                    foreach($tables as $table){
                        $this->assertTableExists($table['name']);
                    }
                }
                // New columns aren't there yet:
                foreach($tables as $table){
                    if(array_key_exists('newColumns', $table)){
                        foreach($table['newColumns'] as $c){
                            $this->assertColumnNotExists($table['name'], $c[0]);
                        }
                    }
                }
            }
        }catch(PHPUnit_Framework_AssertionFailedError $e){
            // Need to clean up before exiting so that the test tables and test
            // files don't end up multiplying like bunnies:
            $this->resetAfterChanges($ube,$e);
        }
    }

    /**
     * Assert that a column exists in a table.
     * @param string $tblName
     * @param string $colName
     */
    public function assertColumnExists($tblName, $colName){
        $this->assertTrue($this->columnExists($tblName, $colName), "Failed asserting that column $tblName.$colName exists.");
    }

    /**
     * Assert that a column does not exits in a table
     * @param type $tblName
     * @param type $colName
     */
    public function assertColumnNotExists($tblName, $colName){
        $this->assertFalse($this->columnExists($tblName, $colName), "Failed asserting that column $tblName.$colName does not exist.");
    }

    /**
     * Assert a table exists.
     * @param type $tblName
     */
    public function assertTableExists($tblName){
        $this->assertTrue($this->tableExists($tblName), "Failed asserting that database table $tblName exists.");
    }

    /**
     * Assert that a table does not exist.
     * @param type $tblName
     */
    public function assertTableNotExists($tblName){
        $this->assertFalse($this->tableExists($tblName), "Failed asserting that database table $tblName does not exist.");
    }

    ////////////////////////////////
    ////// BEGIN TEST METHODS //////
    ////////////////////////////////

    /**
     * A quick sanity check; FileUtil::rrmdir is called on the return value of 
     * these properties, and so it is prudent to see if they return correctly.
     */
    public function testAllPaths() {
        $ube = $this->instantiateUBe();
        $webRoot = realpath(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
        if($ube->webRoot != $webRoot)
            die('Unsafe to proceed through tests; property webRoot of UpdaterBehavior did not return the expected value; it was '.$ube->webRoot);
        if($ube->updateDir != $webRoot.DIRECTORY_SEPARATOR.'update')
            die('Unsafe to proceed through tests; property updateDir of UpdaterBehavior did not return the expected value; it was '.$ube->updateDir);
        if($ube->sourceDir != $webRoot.DIRECTORY_SEPARATOR.'update'.DIRECTORY_SEPARATOR.'source')
            die('Unsafe to proceed through tests; property sourceDir of UpdaterBehavior did not return the expected value; it was '.$ube->sourceDir);
        $this->assertEquals($ube->webRoot.DIRECTORY_SEPARATOR.'update.zip',$ube->updatePackage);
        $this->assertEquals(Yii::app()->basePath.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.UpdaterBehavior::LOCKFILE,$ube->lockFile);
    }

    public function testApplyFiles() {
        $ube = $this->instantiateUBe();
        // Create test files (pseudo-files that will be replaced at the end of the test)
        FileUtil::rrmdir($ube->updateDir);
        $basePathNodes = array('protected','tests','data','output','appliedfiles');
        FileUtil::rrmdir($newDirPath = implode(DIRECTORY_SEPARATOR,array_merge(array($ube->webRoot),$basePathNodes)));
        $newFileContents = array(
            'NewFileSample.php'=>'<?php class NewFileSample {} ?>',
            'NewFileSample2.php'=>'<?php class NewFileSample2 {} ?>'
        );
        $copiedBasePath = implode('/',$basePathNodes);
        $sourcePath = $ube->sourceDir;
        $pkgFile = $ube->webRoot.DIRECTORY_SEPARATOR.UpdaterBehavior::PKGFILE;
        // Set up all files for the test:
        $setupApplyFiles = function() use($ube, $newDirPath, $ube, $basePathNodes, $copiedBasePath, $newFileContents,$sourcePath,$pkgFile){
                    mkdir($ube->updateDir);
                    mkdir($ube->sourceDir);
                    foreach($basePathNodes as $node){
                        $sourcePath .= DIRECTORY_SEPARATOR.$node;
                        mkdir($sourcePath);
                    }
                    // Explicitly set the manifest
                    $manifest = array('fileList' => array());
                    foreach($newFileContents as $file => $contents){
                        $manifest['fileList'][] = "$copiedBasePath/$file";
                        file_put_contents($sourcePath.DIRECTORY_SEPARATOR.$file, $contents);
                    }
                    $manifest['fileList'][] = "$copiedBasePath/UpdaterBehavior.php";
                    // Also, let's toss in the class itself for good measure
                    copy(Yii::getPathOfAlias('application.components.UpdaterBehavior').'.php', $sourcePath.DIRECTORY_SEPARATOR.'UpdaterBehavior.php');
                    // Create the package file just to test that it gets deleted:
                    touch($pkgFile);
                    return $manifest;
                };
        // Test that they got copied over properly:
        $that = $this;
        $testApplyFiles = function() use($newFileContents, $pkgFile, $ube,$basePathNodes,$that){
                    foreach($newFileContents as $file => $contents){
                        $that->assertFileExists($filePath = $ube->webRoot.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_merge($basePathNodes, array($file))));
                        $that->assertEquals($contents, file_get_contents($filePath));
                    }
                    $that->assertFileExists($filePath = $ube->webRoot.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_merge($basePathNodes, array('UpdaterBehavior.php'))));
                    $that->assertFileEquals(Yii::getPathOfAlias('application.components.UpdaterBehavior').'.php', $filePath);
                    $that->assertFileNotExists($pkgFile);
                    $that->assertFileNotExists($ube->sourceDir);
                    $that->assertFileNotExists($ube->updateDir);
                };

        // Explicitly set manifest:
        $ube->manifest = $setupApplyFiles();
        // Let's roll!
        $ube->applyFiles();
        $testApplyFiles();
        // Now test in the use case where we are copying from a temporary
        // directory (encountered in the use case where the updater needs to do
        // a self-update or fetch dependencies)
        $setupApplyFiles();
        $ube->applyFiles(UpdaterBehavior::UPDATE_DIR.DIRECTORY_SEPARATOR.'source');
        $testApplyFiles();
        
        $this->prereq('updateDir',false);
        FileUtil::rrmdir($newDirPath);
    }

    public function testApplyFilesFtp() {
        $this->useFtp("testApplyFiles");
    }

    public function testCheckFiles() {
        $ube = $this->instantiateUBe();
        $this->prereq($ube,'checksums with actual files');
        $this->assertEquals(array(
            'nonexist.php'=>UpdaterBehavior::FILE_MISSING,
            'corrupt.php' => UpdaterBehavior::FILE_CORRUPT,
            'empty.php' => UpdaterBehavior::FILE_PRESENT,
            'file with spaces.js' => UpdaterBehavior::FILE_PRESENT,
            'manifest.json' => UpdaterBehavior::FILE_PRESENT,
        ),$ube->checkFiles(),"checkFiles did not return as expected.");
        $this->prereq($ube,'checksums with actual files',false);
        FileUtil::rrmdir($ube->updateDir);
    }

    /**
     * Checks the full hierarchy of checkIf functions.
     *
     * Expected behavior: if the return argument is false, it should throw
     * exceptions with appropriate error codes when failing. When not failing,
     * it should return true. Furthermore, if a condition is satisfied but
     * one if its prerequisites is not, the exception's code should match that
     * of its prerequisite.
     */
    public function testCheckIf() {
        $ube = $this->instantiateUBe(array(
            'scenario' => 'upgrade'
        ));
        // First: checkIfPackageExists, called by itself
        $exc = false;
        try {
            $ube->checkIf('packageExists');
        } catch(Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_NOUPDATE,$e->getCode(),'Wrong exception thrown (error code mismatch) in base-level checkIf; message was: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,"No exception was thrown where one should have been (1)");
        $this->assertFalse($ube->checkIf('packageExists',false),"checkIf('packageExists',false) should have returned false.");

        // Next: checkIfPackageExists should be the first thing called by all other file checks.
        $exc = false;
        try {
            $ube->checkIf('packageApplies');
        } catch(Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_NOUPDATE,$e->getCode(),'Wrong exception thrown (error code mismatch) in top-level checkIf; message was: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,"No exception was thrown where one should have been (2)");
        $exc = false;
        $this->assertFalse($ube->checkIf('packageApplies',false),"checkIf('packageApplies',false) should have returned false; package doesn't exist");

        // Now check if the error comes from the next level up in the stack, 
        // which is checksumsAvail, and checked when packageApplies is satisfied
        $this->prereq($ube,'updateDir');
        $exc = false;
        try {
            $ube->checkIf('packageApplies');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_CHECKSUM,$e->getCode(),'Wrong exception thrown (error code mismatch) when packageApplies satisfied but checkSums not satisfied');
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown where one should have been (3)');
        $this->assertFalse($ube->checkIf('packageApplies',false),"checkIf('packageApplies',false) should have returned false; checksums file missing.");
        // Go back and check the "packageExists" checkIf:
        $this->assertTrue($ube->checkIf('packageExists'),'checkIfPackageExists should have returned true by now.');
        // Check persistence (storage of check already made in the private property):
        rmdir($ube->updateDir);
        $this->assertTrue($ube->checkIf('packageExists'),'checkIfPackageExists not storing its value in the designated property!');
        mkdir($ube->updateDir);

        // Next level up: checkSums
        $this->prereq($ube,'checksums with actual files');
        $checksumContent = file_get_contents($checksumFile = $ube->updateDir.DIRECTORY_SEPARATOR.'contents.md5');
        file_put_contents($checksumFile,'');
        $exc = false;
        try {
            $ube->checkIf('checksumsAvail');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_CHECKSUM,$e->getCode(),'Exception with wrong code in checkIfChecksumAvail and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (4)');
        $this->assertFalse($ube->checkIf('checksumsAvail',false),"checkIfChecksumsAvail should have returned false; checksums file is empty.");
        $moddedChecksumContent = str_replace('1bc166e024f19c3da04a00714551224a','ffffffffffffffffffffffffffffffff',$checksumContent);
        // Contents present but not entirely correct: should pass.
        file_put_contents($checksumFile,$moddedChecksumContent);
        // Reset properties:
        $ube->checksums = null;
        $ube->checksumsContent = null;
        $this->assertTrue($ube->checkIf('checksumsAvail',false),"checkIfChecksumsAvail should have returned true.");
        file_put_contents($checksumFile,'');
        $this->assertTrue($ube->checkIf('checksumsAvail',false),"value not retained in _checksumsAvail");
        file_put_contents($checksumFile,$moddedChecksumContent);

        $ube->checksums = null;
        $ube->checksumsContent = null;
        // Manifest file missing.
        $exc = false;
        rename($manifestFile = $ube->updateDir.DIRECTORY_SEPARATOR.'manifest.json', $modManifestFile = $ube->updateDir.DIRECTORY_SEPARATOR.'manifest.json.1');
        try {
            $ube->checkIf('manifestAvail');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_MANIFEST,$e->getCode(),'Exception with wrong code in checkIfManifestAvail and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (5)');
        $this->assertFalse($ube->checkIf('manifestAvail',false),'checkIfManifestAvail should have returned false; manifest file is missing.');
        rename($modManifestFile,$manifestFile);
        // Manifest file corrupt.
        $exc = false;
        try {
            $ube->checkIf('manifestAvail');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_MANIFEST,$e->getCode(),'Exception with wrong code in checkIfManifestAvail and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (6)');
        $this->assertFalse($ube->checkIf('manifestAvail',false),'checkIfManifestAvail should have returned false; manifest file is corrupt. Checksums = '.var_export($ube->checksums,1));
        // Effectively unsets checksums so they'll be re-fetched and re-parsed
        file_put_contents($checksumFile,$checksumContent);
        // Reset properties:
        $ube->checksums = null;
        $ube->checksumsContent = null;
        // Now the manifest file is valid
        $this->assertTrue($ube->checkIf('manifestAvail'),'checkIfManifestAvail should have returned true.');
        file_put_contents($checksumFile,'');
        $ube->checksums = null;
        $ube->checksumsContent = null;
        $this->assertTrue($ube->checkIf('manifestAvail'),'value not retained in _manifestAvail');
        file_put_contents($checksumFile,$checksumContent);
        $ube->checksums = null;
        $ube->checksumsContent = null;

        // Now let us say the version, edition, scenario or updater version did not apply:
        $configVars = $ube->configVars;
        $manifest = $ube->manifest;
        $ube->version = '3.5';
        $ube->edition = 'opensource';
        $originalManifest = $manifest;
        $manifest['fromVersion'] = '3.5';
        $manifest['fromEdition'] = 'opensource';
        $manifest['updaterVersion'] = '3.1415926';
        // Updater version bad, version fine. Should fail at updater version first.
        $ube->manifest = $manifest;
        $exc = false;
        try {
            $ube->checkIf('packageApplies');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_NOTAPPLY,$e->getCode(),'Exception with wrong code in checkIfPackageApplies (wrong updater version) and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (7)');
        

        $manifest['updaterVersion'] = $configVars['updaterVersion'];
        $manifest['fromVersion'] = '9.9';
        $ube->manifest = $manifest;
        $exc = false;
        // Version bad, edition fine. Should fail at version first.
        try {
            $ube->checkIf('packageApplies');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_NOTAPPLY,$e->getCode(),'Exception with wrong code in checkIfPackageApplies (wrong version) and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (8)');
        $manifest['fromVersion'] = '3.5';
        $manifest['fromEdition'] = 'pro';
        $ube->manifest = $manifest;
        // Version fine, edition bad. Should fail at edition now.
        $exc = false;
        try {
            $ube->checkIf('packageApplies');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_NOTAPPLY,$e->getCode(),'Exception with wrong code in checkIfPackageApplies (wrong edition) and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (9)');
        $manifest['fromEdition'] = 'opensource';
        $ube->manifest = $manifest;
        // Version and edition fine, scenario bad. Should fail there now.
        $exc = false;
        try {
            $ube->checkIf('packageApplies');
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_NOTAPPLY,$e->getCode(),'Exception with wrong code in checkIfPackageApplies (wrong scenario) and message: '.$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'No exception was thrown when one should have been (10)');

        $ube->scenario = 'update'; // Same as in mock-up of manifest
        // Nothing wrong with package now
        $this->assertTrue($ube->checkIf('packageApplies'),'checkIfPackageApplies should have returned true.');
        $manifest['fromEdition'] = 'pro';
        $this->assertTrue($ube->checkIf('packageApplies'),'value not retained in _packageApplies');

        $this->prereq($ube,'updateDir',false);   
    }

    // The following methods have already been tested:
    // 
    // checkIfChecksumsAvail
    // checkIfManifestAvail
    // checkIfPackageApplies
    // checkIfPackageExists

    public function testCheckIfCanSpawnChildren() {
        $ube = $this->instantiateUBe();
        $this->assertTrue($ube->checkIf('canSpawnChildren'));
    }

    public function testCheckIfDatabaseBackupExists(){
        $backupFile = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'data',UpdaterBehavior::BAKFILE));
        $ube = $this->instantiateUBe();
        if(file_exists($backupFile))
            unlink($backupFile);
        $file = 'nothing.file';
        try{
            $ube->checkIfDatabaseBackupExists();
            $noExc = true;
        }catch(Exception $e){
            $noExc = false;
            $this->assertEquals(UpdaterBehavior::ERR_DBNOBACK, $e->getCode(),'Wrong error code thrown; exception message was '.$e->getMessage());
        }
        if($noExc)
            $this->assertTrue(false, 'No exception was thrown, where one should have been (file does not exist).');

        
        touch($backupFile, time() - 86401);
        try{
            $noExc = true;
            $ube->checkIfDatabaseBackupExists();
        }catch(Exception $e){
            $noExc = false;
            $this->assertEquals(UpdaterBehavior::ERR_DBOLDBAK, $e->getCode());
        }

    }

    
    /**
     * No longer used after edition change.
     * @deprecated
     */
    /*
    public function testCheckPartner() {
        
        $ube = $this->instantiateUBe();
        $expected = array(
            'about' => '3ba18b7816cfbae73f59c1a86a860f1d',
            'footer' => '75c98b390e04c0f48dd7d8b948da2905',
            'login' => '0c9b402dbca317a1cee1a9a85b19b559',
        );
        $this->assertEquals($expected,$ube->checkPartner());
    }
     * 
     */
    

    public function testCheckUpdates() {
        $ube = $this->instantiateUBe();
        $version = $ube->checkUpdates(true);
        $this->assertTrue(version_compare(Yii::app()->params->version,$version)>=0);
    }

    public function testClassAliasPath() {
        $this->assertEquals('components/UpdaterBehavior.php',UpdaterBehavior::classAliasPath('application.components.UpdaterBehavior'));
    }

    // Methods copyFile and cleanUp are already covered by testApplyFiles.
    // UpdaterBehavior::applyFiles() calls these methods.

    public function testDownloadPackage() {
        $ube = $this->instantiateUBe(array('scenario' => 'update'));
        $ube->downloadPackage('6.0',null,'opensource');
        $this->assertFileExists($ube->updatePackage);
    }

    public function testDownloadPackageFtp() {
        $this->useFtp('testDownloadPackage');
    }

    public function testDownloadSourceFile(){
        $ube = $this->instantiateUBe();
        $file = "protected/components/views/requirements.php";

        if(is_dir($tmpdir = $ube->webRoot.DIRECTORY_SEPARATOR.'tmp'))
            FileUtil::rrmdir($tmpdir); //  rename($file, "{$ube->webRoot}/requirements-original.php");
        $ube->downloadSourceFile($file, $ube->getSourceFileRoute('opensource','none'));
        $this->assertFileExists($tmpdir.DIRECTORY_SEPARATOR.$file);
        FileUtil::rrmdir($tmpdir);
    }

    public function testDownloadSourceFileFtp() {
        $this->useFtp('testDownloadSourceFile');
    }

    /**
     * Runs all the actions to perform tests on the database.
     *
     * @todo UPDATE THIS ENTIRE FUNCTION SO THAT IT REFLECT THE CHANGES AS NECESSARY
     *  THAT INCLUDES THE RUNNING OF UPDATE MIGRATION SCRIPTS
     * 
     * @throws PHPUnit_Framework_AssertionFailedError 
     */
    public function testEnactChanges(){
        $ube = $this->instantiateUBe(array(
            'scenario'=>'update',
            'version'=>'999',
            'edition'=>'opensource'
        ));
        // We're going to construct it so that it just passes all the checks for
        // compatibility and whatnot, because *those checks are covered by other
        // test methods*.
        $manifest = array(
            'fromVersion' => '999',
            'targetVersion' => '1000',
            'updaterVersion' => '1000',
            'buildDate' => '999999999999',
            'fromEdition' => 'opensource',
            'targetEdition' => 'opensource',
            'fileList' => array(), // applyFiles is already covered in another
            // test case and we don't want to deal with hunting down and
            // resetting stray files that are modified by this test
            'deletionList' => array(), // ditto
            'scenario' => 'update',
            'data' => array(
                array(
                    'fileList' => array(),
                    'deletionList' => array(),
                    'sqlList' => array(),
                    'sqlForce' => array(),
                    'version' => '1000',
                    'edition' => 'opensource',
                    'migrationScripts' => array(), // running migration scripts covered in testRunMigrationScripts
                ),
            )
        );
        $lockFile = $ube->lockFile;
        if(file_exists($lockFile))
            unlink($lockFile);
        
        // Prepare the database:
        $this->setupTestTables();
        $sqlToRun = array();
        foreach($this->testTables as $type => $tables){ // Compose update SQL
            if($type == 'new'){
                foreach($tables as $table){
                    $sqlToRun[] = $this->createTable($table);
                }
            }
            if($type == 'drop'){
                foreach($tables as $table){
                    $sqlToRun[] = "DROP TABLE {$table['name']}";
                }
            }
            foreach($tables as $table){
                if(array_key_exists('newColumns', $table))
                    $sqlToRun[] = $this->addColumns($table);
            }
        }

        $sqlToRun[] = 'INVALID SQL INVALID SQL INVALID SQL';

        $manifest['data'][0]['sqlList'] = $sqlToRun;
        $ube->manifest = $manifest;
        $ube->version = $manifest['fromVersion'];
        $ube->edition = $manifest['fromEdition'];

        // Back up configuration (it will be overwritten in the test, eventually)
        $this->configFile = implode(DIRECTORY_SEPARATOR,array($ube->webRoot,'protected','config','X2Config.php'));
        copy($this->configFile,"{$this->configFile}.bak");
        $ube->regenerateConfig($manifest['fromVersion'],$manifest['updaterVersion'],$manifest['buildDate']);

        // Make the backup (expected to happen before running enactChanges):
        $ube->makeDatabaseBackup();

        // Make a copy that won't get deleted until later (because we can use it
        // to rewind after each test):
        $dbBackupFile = implode(DIRECTORY_SEPARATOR,array($ube->webRoot,'protected','data',UpdaterBehavior::BAKFILE));
        copy($dbBackupFile, "$dbBackupFile.bak");

        // Test the database failure recovery mechanism (the last line of SQL
        // currently in sqlList should fail).
        //
        // Fake the checksums (so it ignores the bad files; it will throw an 
        // exception as it should, and we already know it works as it should in
        // that regard; we just need to get it past that point)
        $this->prereq($ube,'checksums with actual files');
        $ube->checkSums = array_intersect_key($ube->checkSums,array('manifest.json'=>$ube->checkSums['manifest.json']));
        
        try{
            $this->obStart();
            $ube->enactChanges(true);
            $this->obEndClean();
        }catch(Exception $e){
            $this->assertEquals(UpdaterBehavior::ERR_DATABASE, $e->getCode(),"Wrong error code thrown in an exception thrown by enactChanges. The message was: ".$e->getMessage());
        }
        $this->assertChangesReverted($ube);
        $this->assertFileNotExists($lockFile, "Failed asserting that the lock file was deleted after a failed update.");

        // Test exiting with a lock file. It should not exist at this point.
        $now = time();
        file_put_contents($lockFile, $now);
        $exc = false;
        try {
            $this->obStart();
            $ube->enactChanges(true);
            $this->obEndClean();
        } catch (Exception $e) {
            $this->assertEquals(UpdaterBehavior::ERR_ISLOCKED,$e->getCode(),"enactChanges didn't throw an exception with the appropriate code. Message: ".$e->getMessage());
            $exc = true;
        }
        $this->assertTrue($exc,'enactChanges did not throw exception upon finding the lockfile');
        unlink($lockFile);

        // Now, test the updater itself (going all the way through).
        // Begin by removing the invalid SQL:
        array_pop($manifest['data'][0]['sqlList']);
        $ube->manifest = $manifest;

        $this->obStart();
        $ube->enactChanges(true);
        $this->obEndClean();
        $this->assertChangesApplied($ube);
        $this->assertFileNotExists($lockFile, "Failed asserting that the lock file was deleted after a successful update.");
        $this->resetAfterChanges($ube);

        // Prepare files:
        $ube->makeDatabaseBackup();
        $this->removeTestDirs(false);

        $this->prereq($ube,'checksums with actual files');
        // Test successful upgrade (updates and upgrades are identical in the
        // initial stage, where database changes are applied, and thus there
        // is no need to test a second time whether the databse restore process
        // works properly in an upgrade):
        $admin = $this->getAdmin();
        $edition = $admin->edition;
        $unique_id = $admin->unique_id;
        $this->obStart();
        $ube->enactChanges(true);
        $this->obEndClean();
        $this->assertChangesApplied($ube);
        $this->resetAfterChanges($ube);
        // Reset edition/unique_id:
        $admin->edition = $edition;
        $admin->unique_id = $unique_id;
        $admin->save();

        // All done.
        $this->dropTestTables();
        $this->removeTestDirs();
    }

    public function testEnactChangesFtp() {
        $this->useFtp('testEnactChanges');
    }

    // enactDatabaseChanges is protected and called in enactChanges, so it's
    // covered by testEnactChanges

    // formatDefinitionList is more practical test manually, i.e. seeing the
    // output, because the output is going to be markup.

    ///////////////////////////
    // GETTER FUNCTION TESTS //
    ///////////////////////////
    // Not needed:
    // getChecksumsContent; covered by testGetChecksums and testCheckIf
    // getEdition, getVersion; covered by testCheckIf
    // getManifest; covered by testCheckIf
    // getNoHalt; covered by testSetNoHalt
    // getScenario; covered (sort of) by testDownloadPackage and anything
    //  else testing a method that calls getUpdateDataRoute
    // getSourceDir, getThisPath, getUpdateDir, getUpdatePackage
    //  getLockFile; these are covered by testAllPaths
    // getSourceFileRoute; this is covered by testDownloadSourceFile
    // getUpdateData,getUpdatePackage; these are only a few small, easy to
    //  spot-check things on top of getUpdateDataRoute, which is covered by
    //  testGetUpdateDataRoute. The environmental factors (server connection)
    //  also need to be tested manually.
    //  

    public function testGetChecksums(){
        $ube = $this->instantiateUBe();
        $expect = $this->prereq($ube,'checksums for parse testing');
        $checkSums = $ube->getCheckSums();
        foreach($expect as $file => $digest){
            $this->assertArrayHasKey($file, $checkSums);
            $this->assertEquals($digest, $checkSums[$file]);
        }
    }

    public function testGetCompatibilityStatus(){
        $ube = $this->instantiateUBe();
        $ube->scenario = 'update';
        $ube->manifest = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'tests', 'data', 'updatecompat', 'manifest.json'))), 1);
        $expected = array(
            'req' =>
            array(
                'requirements' =>
                array(
                    'functions' =>
                    array(
                        'mb_regex_encoding' => true,
                        'getcwd' => true,
                        'chmod' => true,
                        'proc_open' => true,
                        'php_sapi_name' => true,
                        'hash_algos' => true,
                        'mt_rand' => true,
                        'md5' => true,
                    ),
                    'classes' =>
                    array(
                        'Reflection' => true,
                    ),
                    'extensions' =>
                    array(
                        'pcre' => true,
                        'SPL' => true,
                        'pdo_mysql' => true,
                        'ctype' => true,
                        'mbstring' => true,
                        'json' => true,
                        'hash' => true,
                        'curl' => true,
                        'mcrypt' => true,
                        'openssl' => true,
                        'zip' => true,
                        'fileinfo' => true,
                        'gd' => true,
                        'posix' => true,
                        'imap' => true
                    ),
                    'environment' =>
                    array(
                        'filesystem_ownership' => 1,
                        'filesystem_permissions' => 1,
                        'open_basedir' => 1,
                        'php_version' => 1,
                        'php_server_superglobal' => 0,
                        'pcre_version' => true,
                        'chmod' => 1,
                        'allow_url_fopen' => '1',
                        'updates_connection' => 1,
                        'outbound_connection' => 1,
                        'shell' => true,
                        'fsockopen' => true
                    ),
                ),
                'reqMessages' =>
                array(
                    1 =>
                    array(
                    ),
                    2 =>
                    array(
                    ),
                    3 =>
                    array(
                    ),
                ),
                'canInstall' => true,
                'hasMessages' => false,
            ),
            'databasePermissionError' => false,
            'modules' =>
            array(
            ),
            'conflictingFields' =>
            array(
                'Accounts' =>
                array(
                    0 => 'primaryContact',
                ),
                'Campaign' =>
                array(
                    0 => 'sendAs',
                ),
                'Media' =>
                array(
                    0 => 'drive',
                ),
                'Opportunity' =>
                array(
                    0 => 'visibility',
                ),
                'Services' =>
                array(
                    0 => 'email',
                ),
            ),
            'customFiles' =>
            array(
            ),
            'allClear' => false,
        );

        $compatStatus = $ube->compatibilityStatus;
        $this->assertArrayHasKey('req', $compatStatus);
        $this->assertArrayHasKey('requirements', $compatStatus['req']);
        $this->assertArrayHasKey('canInstall', $compatStatus['req']);
        $this->assertArrayHasKey('conflictingFields', $compatStatus);
        $this->assertEquals($expected['conflictingFields'],$compatStatus['conflictingFields']);
        if($compatStatus['req']['canInstall']){
            $this->assertEquals($expected,$compatStatus);
        }
        
    }

    public function testGetConfigVars(){
        $ube = $this->instantiateUBe();
        $confVars = $ube->configVars;
        include(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'config','X2Config.php')));
        foreach(UpdaterBehavior::$_configVarNames as $name) {
            $this->assertEquals(${$name},$confVars[$name],"Failed asserting config variable $name imported correctly");
        }
    }

    public function testGetDbBackupCommand(){
        $ube = $this->instantiateUBe();
        $this->assertEquals(0,strpos($ube->dbBackupCommand,'mysqldump'));
    }

    public function testGetDbBackupPath(){
        $ube = $this->instantiateUBe();
        $this->assertEquals(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'data','update_backup.sql')),$ube->dbBackupPath);
    }

    public function testGetDbCommand(){
        $ube = $this->instantiateUBe();
        $this->assertEquals(0,strpos($ube->dbCommand,'mysql'));
    }

    public function testGetDbParams(){
        $ube = $this->instantiateUBe();
        include(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'config','X2Config-test.php')));
        $param = $ube->dbParams;
        $this->assertEquals($host,$param['dbhost']);
        $this->assertEquals($user,$param['dbuser']);
        $this->assertEquals($pass,$param['dbpass']);
        $this->assertEquals($dbname,$param['dbname']);
    }

    public function testGetFiles(){
        $ube = $this->instantiateUBe();
        $this->prereq($ube,'checksums with actual files');
        $expected = array(
            'empty.php' => 0,
            'corrupt.php' => 1,
            'file with spaces.js' => 0,
            'manifest.json' => 0,
            'nonexist.php' => 2,
        );
        $this->assertEquals($expected,$ube->files);
        $this->prereq($ube,'checksums with actual files',1);
    }

    public function testGetFilesByStatus(){
        $ube = $this->instantiateUBe();
        $this->prereq($ube,'checksums with actual files');
        $expected = array(
            0 =>
            array(
                0 => 'empty.php',
                1 => 'file with spaces.js',
                2 => 'manifest.json',
            ),
            1 =>
            array(
                0 => 'corrupt.php',
            ),
            2 =>
            array(
                0 => 'nonexist.php',
            ),
        );
        $this->assertEquals($expected,$ube->filesByStatus);
        $this->prereq($ube,'checksums with actual files',1);
    }

    public function testGetFilesStatus(){
        $ube = $this->instantiateUBe();
        $this->prereq($ube,'checksums with actual files');
        $expected = array(
            0 => 3,
            1 => 1,
            2 => 1,
        );
        $this->assertEquals($expected,$ube->filesStatus);
        $this->prereq($ube,'checksums with actual files',1);

    }

    public function testGetLatestUpdaterVersion(){
        $ube = $this->instantiateUBe();
        $this->assertEquals('string',gettype($ube->latestUpdaterVersion));
    }

    public function testGetUniqueId(){
        $ube = $this->instantiateUBe();
        $admin = $this->getAdmin();
        $this->assertEquals($admin->unique_id,$ube->uniqueId);
    }

    public function testGetUpdateDataRoute(){
        $ube = $this->instantiateUBe();
        $ube->edition = 'pro';
        $ube->version = '3.5';
        $ube->scenario = 'update';
        $ube->uniqueId = 'TTTT-TTTTT-TTTTT';
        $this->assertEquals(0,strpos($ube->updateDataRoute,'/installs/updates/3.5/TTTT-TTTTT-TTTTT_pro_'));
        $ube->edition = 'opensource';
        $ube->scenario = 'upgrade';
        $this->assertEquals(0,strpos($ube->updateDataRoute,'/installs/upgrades/TTTT-TTTTT-TTTTT/opensource_'));
    }

    /**
     * Test the backup & restore functionality.
     *
     * This won't be necessary most of the time. It is a time-consuming test.
     */
    public function testMakeDatabaseBackup(){
        $ube = $this->instantiateUBe();
        $this->setupTestTables();
        $ube->makeDatabaseBackup();
        foreach($this->testTables['new'] as $table)
            Yii::app()->db->createCommand($this->createTable($table))->execute();
        $ube->restoreDatabaseBackup();
        foreach($this->testTables as $type => $tables){
            if($type != 'new'){
                foreach($tables as $table)
                    $this->assertTableExists($table['name']);
            }else{
                foreach($tables as $table)
                    $this->assertTableNotExists($table['name']);
            }
        }
        $this->dropTestTables();

    }

    public function testRegenerateConfig(){
        $configFilename = UpdaterBehavior::$configFilename; // Copy of the original filename
        $testConfigFilename = 'X2Config-testRegen.php'; // Temporary new config filename
        $filesThatShouldBe600 = array('encryption.key', 'encryption.iv'); // Files for testing that permissions are set properly
        // Make test backups of the keys
        foreach(array('key', 'iv') as $cryptExt){
            $cryptFile = Yii::app()->basePath."/config/encryption.$cryptExt";
            if(file_exists($cryptFile)){
                rename($cryptFile, "$cryptFile.testbackup");
            }
        }
        UpdaterBehavior::$configFilename = $testConfigFilename;
        copy(Yii::app()->basePath."/config/$configFilename", Yii::app()->basePath."/config/$testConfigFilename");
        $this->assertTrue(file_exists(Yii::app()->basePath.'/config/'.UpdaterBehavior::$configFilename));

        $ube = $this->instantiateUBe();
        $newversion = '3.3.3.3.3.3.3.3.3.3.3.3.3';
        $newupdaterVersion = '2.2.2.2.2.2.2.2.2';
        $newbuildDate = 12345678910;
        $ube->regenerateConfig($newversion, $newupdaterVersion, $newbuildDate);
        include(Yii::app()->basePath."/config/".$testConfigFilename);
        foreach(array('version', 'updaterVersion', 'buildDate') as $var)
            $this->assertEquals(${"new$var"}, ${"$var"},"Failed asserting that $var was set properly");
        // Test that permissions were set properly on the config/encryption files
        foreach($filesThatShouldBe600 as $file){
            $this->assertEquals(100600, decoct(fileperms(Yii::app()->basePath."/config/$file")), "Failed asserting that $file had its permissions set properly.");
        }
        foreach($filesThatShouldBe600 as $file){
            // Forcefully change so we can properly test the permission-setting method
            chmod(Yii::app()->basePath."/config/$file", octdec(100666));
        }
        // Now, run the method:
        $ube->setConfigPermissions(100600);
        // Test that the permissions were set back to their proper values
        foreach($filesThatShouldBe600 as $file){
            $this->assertEquals(100600, decoct(fileperms(Yii::app()->basePath."/config/$file")), "Failed asserting that $file had its permissions set properly.");
        }

        UpdaterBehavior::$configFilename = $configFilename;
        unlink(Yii::app()->basePath."/config/$testConfigFilename");
        // Restore test backups of the keys
        foreach(array('key', 'iv') as $cryptExt){
            $cryptFile = Yii::app()->basePath."/config/encryption.$cryptExt";
            if(file_exists("$cryptFile.testbackup")){
                rename("$cryptFile.testbackup", $cryptFile);
            }
        }
    }

    /**
     * Test the function that processes deletionList
     */
    public function testRemoveFiles(){
        $this->setupTestDirs();
        $ube = $this->instantiateUBe();
        $deletionList = $this->fileList;
        $ube->removeFiles($deletionList);
        foreach($deletionList as $deletedFile)
            $this->assertFileNotExists("{$ube->webRoot}/$deletedFile");
        $this->removeTestDirs();
    }

    // function renderCompatibilityMessages needs manual testing (it's more
    // a visual test of output)

    // function requireDependencies messes with the live fileset and is thus
    // really tough to replicate. However, the most essential parts of it
    // (downloadSourceFile and applyFiles) are covered by testDownloadSourceFile.
    

    /**
     * Test the function that cleans out the assets folder.
     */
    public function testResetAssets(){
        $ube = $this->instantiateUBe();
        $crcDir = sprintf('%x', crc32('cockadoodledoo'));
        mkdir("{$ube->webRoot}/assets/$crcDir");
        $file = "{$ube->webRoot}/assets/$crcDir/something.js";
        file_put_contents($file, '/* cockadoodledoo */');
        $ube->resetAssets();
        $this->assertFileNotExists($file);
    }

    public function testRunMigrationScripts() {
        $ube = $this->instantiateUBe();
        $ran = array();
        $this->prereq($ube,'sourceDir');
        $nodes = array('protected','tests','data','updatemigration');
        $allNodes = '';
        foreach($nodes as $node) {
            if(!is_dir($absNode = $ube->sourceDir.$allNodes.DIRECTORY_SEPARATOR.$node))
                mkdir($absNode);
            $allNodes .= DIRECTORY_SEPARATOR.$node;
        }
        $migdir = implode(DIRECTORY_SEPARATOR,array_merge(array($ube->sourceDir),$nodes));
        $script = 'protected/tests/data/updatemigration/touch.php';
        copy($ube->webRoot.DIRECTORY_SEPARATOR.FileUtil::rpath($script),$ube->sourceDir.DIRECTORY_SEPARATOR.FileUtil::rpath($script));
        $this->obStart();
        $scripts = array($script);
        $ran = $ube->runMigrationScripts($scripts,$ran,false);
        $this->obEndClean();
        $this->assertEquals(1,count($ran));
        $this->assertFileExists($testfile = $ube->webRoot.DIRECTORY_SEPARATOR.'testfile');
        unlink($testfile);
    }

    /**
     * This test ensures that the updater will recover from failed migrations if
     * specified, and won't otherwise
     */
    public function testRecoveryFromFailedMigration() {
        // Prepare for the test
        $ube = $this->instantiateUBe();
        $this->prereq($ube,'sourceDir');
        $nodes = array('protected','tests','data','updatemigration');
        $allNodes = '';
        foreach($nodes as $node) {
            if(!is_dir($absNode = $ube->sourceDir.$allNodes.DIRECTORY_SEPARATOR.$node))
                mkdir($absNode);
            $allNodes .= DIRECTORY_SEPARATOR.$node;
        }
        $migdir = implode(DIRECTORY_SEPARATOR,array_merge(array($ube->sourceDir),$nodes));

        // Copy the migration scripts
        $exceptionScript = 'protected/tests/data/updatemigration/failure-except.php';
        $errorScript = 'protected/tests/data/updatemigration/failure-error.php';
        copy($ube->webRoot.DIRECTORY_SEPARATOR.FileUtil::rpath($exceptionScript),$ube->sourceDir.DIRECTORY_SEPARATOR.FileUtil::rpath($exceptionScript));
        copy($ube->webRoot.DIRECTORY_SEPARATOR.FileUtil::rpath($errorScript),$ube->sourceDir.DIRECTORY_SEPARATOR.FileUtil::rpath($errorScript));

        // Ensure that backups can be restored when an exception is raised
        Yii::app()->db->createCommand("DROP TABLE IF EXISTS some_new_table;")->execute();
        $ube->makeDatabaseBackup();
        $scripts = array($exceptionScript);
        $ran = array();

        $this->obStart();
        try {
            $ube->runMigrationScripts($scripts, $ran, true);
        } catch(Exception $e) {
            $this->assertEquals (42000, $e->getCode(), 'Incorrect exception raised. This test '.
                'expects a PDO syntax error');
            $ube->restoreDatabaseBackup();
        }
        $this->obEndClean();
        // Should have reverted
        $this->assertTableNotExists ('some_new_table');

        // Ensure that backups can be restored when an error is raised
        $ran = array();
        $scripts = array($errorScript);
        $this->obStart();
        $ube->runMigrationScripts($scripts, $ran, true);
        // Retrieve error code from output
        $output = ob_get_contents();
        $this->obEndClean();
        preg_match ('/\[(\d+)\]/', $output, $matches);
        $this->assertArrayHasKey (1, $matches);
        $this->assertEquals (8, $matches[1], 'Incorrect error occured. This test '.
            'expects an E_NOTICE: undefined variable.');
        // Should have reverted
        $this->assertTableNotExists ('some_new_table');

        // Ensure that the database is in an 'unexpected' state when no recovery is performed
        $ran = array();
        $scripts = array($exceptionScript);
        $this->obStart();
        try {
            $ube->runMigrationScripts($scripts,$ran, false);
            $this->obEndClean();
        } catch(Exception $e) {
            $this->obEndClean();
            $this->assertEquals (42000, $e->getCode(), 'Incorrect exception raised. This test '.
                'expects a PDO syntax error');
        }
        $this->assertTableExists ('some_new_table');
        Yii::app()->db->createCommand("DROP TABLE IF EXISTS some_new_table;")->execute();
    }

    // function restoreDatabaseBackup is covered by testEnactChanges.
    // function testRunMigrationScripts is covered by testEnactChanges (#TODO)


    ///////////////////////////
    // SETTER FUNCTION TESTS //
    ///////////////////////////
    // Not needed:
    // testSetChecksums,testSetChecksumsContent; covered by testCheckIf
    // testSetConfigPermissions; covered by testEnactChanges
    // testSetEdition, testSetManifest, testSetVersion; covered by testCheckIf
    // testSetScenario; covered by testDownloadPackage

    public function testSetNoHalt(){
        $ube = $this->instantiateUBe();
        $ube->setNoHalt(true);
        $this->assertEquals(true,$ube->noHalt);
    }

    public function testSetUniqueId(){
        $ube = $this->instantiateUBe();
        $ube->setUniqueId($exp = 'nonenonenone');
        $this->assertEquals($exp,$ube->uniqueId);
    }
    
    /**
     * Tests the SQL error printer (which should never fail)
     *
     * The call to sqlError should not halt PHP execution, and there should be
     * no fatal errors. This test will always "pass"; it "fails" if phpunit
     * exits entirely, or encounters a fatal error (obviously).
     */
    public function testSqlError(){
        $ube = $this->instantiateUBe();
        try{
            $ube->sqlError('HOW DO I WROTE SQL', array('UPDATE x2_actions SET actionDescription="stuff and things"'), 'SQL syntax error.', true);
            $this->assertFalse(true,'sqlError should throw an exception.');
        }catch(Exception $e){
            $this->assertRegExp('/SQL syntax error/m', $e->getMessage());
        }
    }

    public function testUnpack () {
        // Copy the zip file...
        $ube = $this->instantiateUBe();
        $this->prereq($ube,'updateDir',false);
        copy(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'tests','data','update.zip')),$ube->webRoot.DIRECTORY_SEPARATOR.'update.zip');
        $ube->unpack();
        $this->assertTrue(is_dir($ube->updateDir),'Update directory not created');
        $this->assertTrue(is_dir($ube->sourceDir),'Source directory not unpacked');
        $this->assertFileExists($ube->updateDir.DIRECTORY_SEPARATOR.'manifest.json','Manifest file not unpacked');
        $this->assertFileExists($ube->updateDir.DIRECTORY_SEPARATOR.'contents.md5','Contents digest file not unpacked');
        $ube->cleanUp();
    }

    public function testFinalizeUpdate () {
        $updaterBehavior = $this->instantiateUBe();
        $unique_id = Yii::app()->settings->unique_id;
        $version = Yii::app()->params->version;
        $edition = Yii::app()->edition;

        // default function return value since finalizeUpdate ignores upgrade scenario
        $this->assertNull (
            $updaterBehavior->finalizeUpdate ('upgrade', $unique_id, $version, $edition));

        $this->assertNotNull (
            gettype ($updaterBehavior->finalizeUpdate ('update', $unique_id, $version, $edition)));
    }

}

?>
