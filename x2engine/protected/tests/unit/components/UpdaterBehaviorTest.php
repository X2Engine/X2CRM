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

/**
 * Test suite for the application updater.
 * 
 * @package X2CRM.tests.unit.components 
 */
class UpdaterBehaviorTest extends FileOperTestCase {
	
	/**
	 * - Set to 0 to skip all tests that require reloading the database (fastest)
	 * - Set to 1 to perform backup/restore tests specifically, but no higher-level tests.
	 * - Set to 2 to perform all updater/upgrader tests (which run backup/restore 
	 * operations), but not backup tests specifically (it's assumed they work)
	 */
	const TEST_LEVEL = 0;

	/**
	 * Array of tables used in update/upgrade SQL testing.
	 * @var array 
	 */
	public $testTables;

	public function instantiateUBe() {
		$comp = new CComponent();
		$ubconfig = array(
			'class' => 'UpdaterBehavior',
			'isConsole' => true,
			'noHalt' => true,
			'keepDbBackup' => true,
		);
		$comp->attachBehavior('UpdaterBehavior', $ubconfig);
		return $comp;
	}

	public function backupDb() {
		if (file_exists(Yii::app()->basePath . '/data/'))
			$ube = $this->instantiateUBe();
		$ube->makeDatabaseBackup();
		$this->assertEquals();
	}

	/**
	 * Creates column definition statements.
	 * @param type $columns
	 * @return type 
	 */
	public static function colDefs($columns) {
		return array_map(function($c) {
							return "{$c[0]} {$c[1]}";
						}, $columns);
	}

	/**
	 * Returns a create table statement given a table description array.
	 * @param type $table
	 * @return type 
	 */
	public function createTable($table) {
		return sprintf("CREATE TABLE {$table['name']}(%s) COLLATE = utf8_general_ci;", implode(", ", self::colDefs($table['columns'])));
	}

	/**
	 * Returns an alter table statement given a table description array.
	 * @param type $table
	 * @return type 
	 */
	public function addColumns($table) {
		$statement = '';
		$statement .= sprintf("ALTER TABLE {$table['name']} ADD COLUMN ");
		$statement .= implode(', ADD COLUMN', self::colDefs($table['newColumns']));
		return $statement;
	}

	public function tableExists($tblName) {
		try {
			$description = Yii::app()->db->createCommand("DESCRIBE $tblName")->queryAll();
			return true;
		} catch (CDbException $e) {
			return false;
		}
	}

	public function columnExists($tblName, $colName) {
		try {
			$col = Yii::app()->db->createCommand()->select($colName)->from($tblName)->queryAll();
			return true;
		} catch (CDbException $e) {
			return false;
		}
	}

	public function assertTableExists($tblName) {
		$this->assertTrue($this->tableExists($tblName), "Failed asserting that database table $tblName exists.");
	}

	public function assertTableNotExists($tblName) {
		$this->assertFalse($this->tableExists($tblName), "Failed asserting that database table $tblName does not exist.");
	}

	public function assertColumnExists($tblName, $colName) {
		$this->assertTrue($this->columnExists($tblName, $colName), "Failed asserting that column $tblName.$colName exists.");
	}

	public function assertColumnNotExists($tblName, $colName) {
		$this->assertFalse($this->columnExists($tblName, $colName), "Failed asserting that column $tblName.$colName does not exist.");
	}

	/**
	 * Sets up tables in the database for testing update SQL runs.
	 * 
	 * Comment symbols in this method, what they mean:
	 * ^1: Should exist after update, but not after failed update/restore
	 * ^2: Should not exist after update, but should after failed update/restore
	 * ^3: Can exist both after update and after failed update/restore
	 */
	public function setupTestTables() {
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
						array('description','TEXT NULL')
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
		try {
			foreach ($this->testTables as $type => $tables) {
				if ($type != 'new') {
					foreach ($tables as $table) {
						Yii::app()->db->createCommand($this->createTable($table))->execute();
						$tablesUp[] = $table['name'];
					}
				}
			}
		} catch (CDbException $e) {
			echo $e->getMessage();
			// Try to delete the tables that got made successfully before
			foreach ($tablesUp as $table) {
				Yii::app()->db->createCommand("DROP TABLE $table")->execute();
			}
		}
	}

	/**
	 * Remove testing tables.
	 */
	public function dropTestTables() {
		foreach ($this->testTables as $type => $tables) {
			foreach ($tables as $table) {
				Yii::app()->db->createCommand("DROP TABLE IF EXISTS {$table['name']}")->execute();
			}
		}
	}
	
	public function testDownloadSourceFile() {
		$ube = $this->instantiateUBe();
		$file = "requirements.php";
		
		if(is_dir("{$ube->webRoot}/temp"))
			FileUtil::rrmdir("{$ube->webRoot}/temp"); //  rename($file, "{$ube->webRoot}/requirements-original.php");
		$ube->downloadSourceFile('updates/x2engine',$file);
		$this->assertFileExists("{$ube->webRoot}/temp/$file");
		$ube->removeBackup('temp');
	}

	public function testRegenerateConfig() {
		$configFilename = UpdaterBehavior::$configFilename;
		$testConfigFilename = 'X2Config-testRegen.php';
		UpdaterBehavior::$configFilename = $testConfigFilename;
		copy(Yii::app()->basePath . "/config/$configFilename", Yii::app()->basePath . "/config/$testConfigFilename");
		$this->assertTrue(file_exists(Yii::app()->basePath . '/config/' . UpdaterBehavior::$configFilename));
		$ube = $this->instantiateUBe();
		$newversion = '3.3.3.3.3.3.3.3.3.3.3.3.3';
		$newupdaterVersion = '2.2.2.2.2.2.2.2.2';
		$newbuildDate = 12345678910;
		$ube->regenerateConfig($newversion, $newupdaterVersion, $newbuildDate);
		include(Yii::app()->basePath . "/config/" . $testConfigFilename);
		foreach (array('version', 'updaterVersion', 'buildDate') as $var)
			$this->assertEquals(${"new$var"}, ${$var});
		UpdaterBehavior::$configFilename = $configFilename;
		unlink(Yii::app()->basePath . "/config/$testConfigFilename");
	}

	public function testMakeBackup() {
		$this->setupTestDirs();
		$budir = 'backup';
		$ube = $this->instantiateUBe();
		$fileList = $this->fileList;
		$buFiles = $ube->makeBackup($fileList, $budir);
		foreach ($fileList as $file) {
			$buFile = $ube->webRoot . "/$budir/$file";
			$this->assertFileExists($buFile);
			$this->assertFileEquals($ube->webRoot . "/$file", $buFile);
		}
		$this->removeTestDirs();
		// Wipe it out. We're done with it.
		FileUtil::rrmdir("{$ube->webRoot}/$budir");
	}

	public function testRemoveBackup() {
		$this->setupTestDirs();

		$ube = $this->instantiateUbe();
		// Test that removal works properly:
		$budir = 'backup';
		$ube->removeBackup($budir);
		$fileList = $this->fileList;
		foreach ($fileList as $file) {
			$buFile = $ube->webRoot . "/$budir/$file";
			$this->assertFileNotExists($buFile);
		}
		$this->assertFileNotExists($ube->webRoot . "/$budir");
		$this->removeTestDirs();
	}

	public function testRestoreBackup() {
		$this->setupTestDirs();
		$ube = $this->instantiateUBe();
		$fileList = $this->fileList;
		$budir = 'backup';
		$buFiles = $ube->makeBackup($fileList, $budir);
		// Mess up the test files, restore the backup, and then test that they're
		// back to normal
		$testText = 'HAW HAW HAW';
		foreach ($fileList as $file) {
			file_put_contents("{$ube->webRoot}/$file", $testText);
		}
		$success = $ube->restoreBackup($budir);
		$this->assertTrue((bool) $success);
		foreach ($fileList as $file) {
			$this->assertNotEquals($testText, file_get_contents("{$ube->webRoot}/$file"));
			// Check that the backup file was removed properly:
			$this->assertFileNotExists("{$ube->webRoot}/$budir/$file");
		}

		// Clean up:
		$ube->removeBackup($budir);
		$this->removeTestDirs();
	}

	/**
	 * Test the backup & restore functionality.
	 * 
	 * This won't be necessary most of the time. It is a time-consuming test.
	 */
	public function testDatabaseBackups() {
		$ube = $this->instantiateUBe();
		if (self::TEST_LEVEL == 1) {
			$this->setupTestTables();
			$ube->makeDatabaseBackup();
			foreach ($this->testTables['new'] as $table)
				Yii::app()->db->createCommand($this->createTable($table))->execute();
			$ube->restoreDatabaseBackup();
			foreach ($this->testTables as $type => $tables) {
				if ($type != 'new') {
					foreach ($tables as $table)
						$this->assertTableExists($table['name']);
				} else {
					foreach ($tables as $table)
						$this->assertTableNotExists($table['name']);
				}
			}
			$this->dropTestTables();
		}
	}
	
	public function testCheckDatabaseBackup() {
		$ube = $this->instantiateUBe();
		$file = 'nothing.file';
		try {
			$ube->checkDatabaseBackup($file);
			$this->assertTrue(false,'No exception was thrown, where one should have been (file does not exist).');
		} catch (Exception $e) {
			$this->assertEquals(1,$e->getCode());
		}
		$this->setupTestDirs();
		$oldFile = "{$this->baseDir}/{$this->relFileList[0]}";
		touch($oldFile,time()-86401);
		try {
			$ube->checkDatabaseBackup($oldFile);
			$this->assertTrue(false,'No exception was thrown, where one should have been (file is too old).');
		} catch (Exception $e) {
			$this->assertEquals(2,$e->getCode());
		}
		$this->removeTestDirs();
	}

	/**
	 * Test the function that cleans out the assets folder. 
	 */
	public function testResetAssets() {
		$ube = $this->instantiateUBe();
		$crcDir = sprintf('%x', crc32('cockadoodledoo'));
		mkdir("{$ube->webRoot}/assets/$crcDir");
		$file = "{$ube->webRoot}/assets/$crcDir/something.js";
		file_put_contents($file, '/* cockadoodledoo */');
		$ube->resetAssets();
		$this->assertFileNotExists($file);
	}

	/**
	 * Test the function that processes deletionList
	 */
	public function testRemoveFiles() {
		$this->setupTestDirs();
		$ube = $this->instantiateUBe();
		$deletionList = $this->fileList;
		$ube->removeFiles($deletionList);
		foreach ($deletionList as $deletedFile)
			$this->assertFileNotExists("{$ube->webRoot}/$deletedFile");
		$this->removeTestDirs();
	}

	/**
	 * Tests the SQL error printer (which should never fail)
	 * 
	 * The call to sqlError should not halt PHP execution, and there should be 
	 * no fatal errors. This test will always "pass"; it "fails" if phpunit 
	 * exits entirely, or encounters a fatal error (obviously).
	 */
	public function testSqlError() {
		$ube = $this->instantiateUBe();
		try {
			$ube->sqlError('HOW DO I WROTE SQL', array('UPDATE x2_actions SET actionDescription="stuff and things"'), 'SQL syntax error.', true);
		} catch (Exception $e) {
			$this->assertRegExp('/SQL syntax error/m', $e->getMessage());
		}
	}

	/**
	 * Test the enactChanges method. 
	 * 
	 * This is a very time-consuming test that involves dropping and reloading the 
	 * database multiple times, and so only if {@link TEST_LEVEL} is set to 2 will 
	 * the test actually run.
	 */
	public function testEnactChanges() {
		if (self::TEST_LEVEL == 2)
			$this->enactChanges();
	}

	/**
	 * Runs all the actions to perform tests on the database. Not named as a test
	 * method to allow easier disabling 
	 * "testEnactChanges" to make it easier to disable
	 * 
	 * @throws PHPUnit_Framework_AssertionFailedError 
	 */
	public function enactChanges() {
		// Test the "missing parameters" error:
		$ube = $this->instantiateUBe();
		try {
			$ube->enactChanges('update', array());
		} catch (Exception $e) {
			$this->assertEquals('Could not enact changes; missing the following parameters: version, buildDate', $e->getMessage());
		}

		// Prepare the database:
		$this->setupTestTables();
		$sqlToRun = array();
		foreach ($this->testTables as $type => $tables) { // Compose update SQL
			if ($type == 'new') {
				foreach ($tables as $table) {
					$sqlToRun[] = $this->createTable($table);
				}
			}
			if ($type == 'drop') {
				foreach ($tables as $table) {
					$sqlToRun[] = "DROP TABLE {$table['name']}";
				}
			}
			foreach ($tables as $table) {
				if (array_key_exists('newColumns', $table))
					$sqlToRun[] = $this->addColumns($table);
			}
		}
		$sqlToRun[] = 'INVALID SQL INVALID SQL INVALID SQL';

		// Back up configuration (it will be overwritten in the test, eventually)
		$ube->makeBackup(array('protected/config/X2Config.php'), 'confbackup');
		// Create the files to be copied in the update:
		$this->setupTestDirs();
		// "temp" is the backup location used for downloaded files. Copy the test
		// files over to there.
		$ube->makeBackup($this->fileList, 'temp');
		// The files will be copied back into the test area from the backup at 
		// the end of the update, but they should be removed so we can prove 
		// the updater copies them successfully:
		$this->removeTestDirs(false);
		// Now, create a file that will be deleted in the update:
		$deletionList = array('protected/tests/data/output/deleteMe');
		foreach ($deletionList as $file) {
			$abs = "{$ube->webRoot}/$file";
			$absDeletionList[] = $abs;
			file_put_contents($abs, 'I should be deleted in the update.');
		}

		// Make the backup (expected to happen before running enactChanges):
		$ube->makeDatabaseBackup();
		// Make a copy that won't get deleted until later (because we can use it 
		// to rewind after each test):
		$ube->makeBackup(array('protected/data/' . UpdaterBehavior::BAKFILE), 'dbbackup');
		// Set parameters for the update:
		$params = array(
			'sqlList' => $sqlToRun,
			'deletionList' => $deletionList,
			'version' => '9.9.9.9.9.9.9.9',
			'buildDate' => PHP_INT_MAX,
			'unique_id' => '1234-56789-01112',
			'edition' => 'gir'
		);

		// Test the database failure recovery mechanism (the last line of SQL 
		// currently in sqlList should fail).
		try {
			$ube->enactChanges('update', $params, true);
		} catch (Exception $e) {
			$this->assertRegExp('/changes were applied prior to this failure/m', $e->getMessage());
		}
		$this->verifyChangesReverted($ube,'update',$absDeletionList);


		// Now, test the updater itself (going all the way through).
		// Begin by removing the invalid SQL:
		array_pop($params['sqlList']);
		$ube->enactChanges('update', $params, true);
		$this->verifyChangesApplied($ube, 'update', $params, $absDeletionList);
		$this->resetAfterChanges($ube, $absDeletionList);

		// Prepare files:
		$ube->makeBackup($this->fileList, 'temp');
		$ube->makeDatabaseBackup();
		$ube->makeBackup(array('protected/config/X2Config.php'), 'confbackup');
		$ube->makeBackup(array('protected/data/' . UpdaterBehavior::BAKFILE), 'dbbackup');
		$this->removeTestDirs(false);
		
		// Test successful upgrade (updates and upgrades are identical in the
		// initial stage, where database changes are applied, and thus there
		// is no need to test a second time whether the databse restore process
		// works properly in an upgrade):
		$admin = Admin::model()->find();
		$edition = $admin->edition;
		$unique_id = $admin->unique_id;
		$ube->enactChanges('upgrade',$params,true);
		$this->verifyChangesApplied($ube,'upgrade',$params);
		$this->resetAfterChanges($ube,$absDeletionList);
		// Reset edition/unique_id:
		$admin->edition = $edition;
		$admin->unique_id = $unique_id;
		$admin->save();

		// All done.
		$this->dropTestTables();
		$this->removeTestDirs();
	}

	/**
	 * Test that changes were applied (database and file)
	 * 
	 * @throws PHPUnit_Framework_AssertionFailedError 
	 */
	public function verifyChangesApplied($ube, $scenario, $params, $absDeletionList = array()) {
		// Now that it's all said and done, verify: did the update/upgrade happen properly?
		try {
			foreach ($this->testTables as $type => $tables) {
				// New tables that should be there now:
				if ($type == 'new') {
					foreach ($tables as $table) {
						$this->assertTableExists($table['name']);
					}
				}
				// Dropped tables should be gone:
				if ($type == 'drop') {
					foreach ($tables as $table) {
						$this->assertTableNotExists($table['name']);
					}
				}
				// New columns should be there:
				foreach ($tables as $table) {
					if (array_key_exists('newColumns', $table)) {
						foreach ($table['newColumns'] as $c) {
							$this->assertColumnExists($table['name'], $c[0]);
						}
					}
				}
			}

			// Verify that deletion list files were deleted.
			foreach ($absDeletionList as $file)
				$this->assertFileNotExists($file);
			// The test files that were deleted should now be back in place:
			foreach ($this->fileList as $file)
				$this->assertFileExists("{$ube->webRoot}/$file");

			// Now, some testing that's specific to the scenario:
			if ($scenario == 'update') {
				// Was the configuration updated properly?
				require(Yii::app()->basePath . '/config/X2Config.php');
				foreach (array('version', 'buildDate') as $var)
					$this->assertEquals($params[$var], ${$var});
			} else if ($scenario == 'upgrade') {
				$verEd = Yii::app()->db->createCommand()->select('edition,unique_id')->from('x2_admin')->queryRow();
				$this->assertEquals($params['unique_id'],$verEd['unique_id']);
				$this->assertEquals($params['edition'],$verEd['edition']);
			}
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			$this->resetAfterChanges($ube, $absDeletionList, $e);
		}
	}
	
	/**
	 * Test whether database changes were properly reverted after a restore from a backup
	 * @param type $ube
	 * @param type $scenario
	 * @param type $absDeletionList 
	 */
	public function verifyChangesReverted($ube,$scenario,$absDeletionList) {
		// Now that it's all said and done, verify: did we restore properly?
		try {
			foreach ($this->testTables as $type => $tables) {
				// New tables aren't there yet:
				if ($type == 'new') {
					foreach ($tables as $table) {
						$this->assertTableNotExists($table['name']);
					}
				}
				// Dropped tables are restored:
				if ($type == 'drop') {
					foreach ($tables as $table) {
						$this->assertTableExists($table['name']);
					}
				}
				// New columns aren't there yet:
				foreach ($tables as $table) {
					if (array_key_exists('newColumns', $table)) {
						foreach ($table['newColumns'] as $c) {
							$this->assertColumnNotExists($table['name'], $c[0]);
						}
					}
				}
			}
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			// Need to clean up before exiting so that the test tables and test
			// files don't end up multiplying like bunnies:
			$this->resetAfterChanges($ube, $absDeletionList, $e);
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
	public function resetAfterChanges($ube, $absDeletionList, $e = null) {
		// Get back to pre-changes state, with test tables:
		$ube->restoreBackup('dbbackup');
		$ube->restoreDatabaseBackup();
		// Back to square one completely (if in the scope of an error)
		if (!empty($e)) {
			$this->dropTestTables();
			// Reset the cache of files:
			$ube->removeBackup('temp');
		}
		foreach ($absDeletionList as $file)
			if (file_exists($file))
				unlink($file);
		// Restore the original configuration file:
		$ube->restoreBackup('confbackup');

		// Throw (to resume normal chain of assertion failure)
		if (!empty($e))
			throw $e;
	}

}

?>
