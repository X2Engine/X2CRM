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

Yii::import('application.components.ResponseBehavior');

/**
 * Behavior class with application updater/upgrader utilities.
 * 
 * @property string $dbBackupCommand (read-only) command to be used for backing up the database
 * @property string $dbBackupPath (read-only) Full path to the database backup file.
 * @property string $dbCommand (read-only) command to be used for running SQL from files
 * @property array $dbParams (read-only) Database information retrieved from {@link CDbConnection}
 * @property boolean $keepDbBackup If true, updater will not remove database backup after restoring.
 * @property boolean $noHalt Whether to terminate the PHP process if errors occur
 * @property string $thisPath (read-only) Absolute path to the current working directory
 * @property string $webRoot (read-only) Absolute path to the web root, even if not in a web request
 */
class UpdaterBehavior extends ResponseBehavior {
	/**
	 * SQL backup dump
	 */

	const BAKFILE = 'update_backup.sql';
	/**
	 * SDERR output from backup/recovery process 
	 */
	const ERRFILE = 'update.err';
	/**
	 * STDOUT output from backup/recovery process
	 */
	const LOGFILE = 'update.log';

	/**
	 * SQL file with drop table statements, for clearing out tables.
	 */
	const DRPFILE = 'drop_tables.sql';

	/**
	 * Core configuration file name.
	 */
	public static $configFilename = 'X2Config.php';

	/**
	 * Configuration file variables as [variable name] => [value quote]
	 * @var array
	 */
	public static $confVars = array(
		'appName' => "'",
		'email' => "'",
		'language' => "'",
		'host' => "'",
		'user' => "'",
		'pass' => "'",
		'dbname' => "'",
		'version' => "'",
		'updaterVersion' => "'",
		'buildDate' => "",
	);

	/**
	 * Command to use for backing up the database.
	 * @var string
	 */
	private $_dbBackupCommand;
	
	/**
	 * Full path to the database backup file
	 * @var type 
	 */
	private $_dbBackupPath;

	/**
	 * Command to use for explicitly running SQL commands from a file:
	 * @var string
	 */
	private $_dbCommand;

	/**
	 * DSN parameters taken from {@link CDbConnection}
	 * @var array
	 */
	private $_dbParams;


	/**
	 * Specifies that the behavior is being applied to a console command
	 * @var bool
	 */
	public static $_isConsole = true;

	/**
	 * Set to true to retain database backups after using them to recover from a failed update.
	 * @var boolean
	 */
	private $_keepDbBackup = true;

	/**
	 * Set to true in cases of testing, to avoid having errors end PHP execution.
	 * @var boolean
	 */
	private static $_noHalt = false;

	/**
	 * Current working directory.
	 * @var string 
	 */
	private $_thisPath;

	/**
	 * Absolute path to the web root
	 * @var string
	 */
	private $_webRoot;

	/**
	 * Checks to see if a file exists and isn't very old..
	 * @param type $bakFile
	 * @throws Exception 
	 */
	public function checkDatabaseBackup($bakFile=null) {
		if($bakFile == null)
			$bakFile = $this->dbBackupPath;
		$bakFile = realpath($bakFile);
		if (!(bool) $bakFile)
			throw new Exception(Yii::t('admin', 'Cannot restore database; backup missing.'),1);
		else { // Test the timestamp of the backup copy, just to be extra sure it's safe to use
			$backupTime = filemtime($bakFile);
			$currenTime = time();
			if ($currenTime-$backupTime > 86400) // Updating the software should NEVER take a whole day!
				throw new Exception(Yii::t('admin','Cannot restore database; the backup is over 24 hours old and may thus be unreliable.'),2);
		}
		return true;
	}
	
	/**
	 * Copies files out of a folder and into the live installation. 
	 * 
	 * Wrapper for {@link FileUtil::ccopy} for updates that can operate
	 * recursively without requiring a list of files.
	 * 
	 * @param string $file The path to copy (assumed relative to the webroot)
	 * @param string $dir The name of the backup directory; "." means top-level directory
	 */
	public function copyFile($path, $dir = null) {

		// Resolve paths
		$bottomLevel = $dir === null;
		if ($bottomLevel)
			$dir = $path;
		$absPath = $bottomLevel ? "{$this->webRoot}/$path" : "{$this->webRoot}/$dir/$path";
		$relPath = FileUtil::relpath($absPath, $this->thisPath . "/");
		$absLivePath = "{$this->webRoot}/$path";
		$relLivePath = FileUtil::relpath($absLivePath, $this->thisPath . "/");
		$success = file_exists($relPath);
		if ($success) {
			if (is_dir($relPath) || $bottomLevel) {
				$objects = scandir($relPath);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						// The target shall be the object itself if in the 
						// root level of the backup directory; otherwise, 
						// prepend the path up to the current point (which is
						// copied in through the recursion levels in the stack)
						$copyTarget = $bottomLevel ? $object : "$path/$object";
						$success == $success && $this->copyFile($copyTarget, $dir);
						if (!$success)
							throw new Exception(Yii::t('admin', 'Failed to copy {relPath}; working directory = {cwd}', array('{relPath}' => $relPath, '{cwd}' => $this->$thisPath)));
					}
				}
			} else {
				return FileUtil::ccopy($relPath, $relLivePath);
			}
		}
		if (!$success)
			throw new Exception(Yii::t('admin', 'Failed to copy {relPath} (path does not exist); working directory = {cwd}', array('{relPath}' => $relPath, '{cwd}' => $this->thisPath)));
		return (bool) $success;
	}
	
	/**
	 * Retrieves a file from the update server.
	 * 
	 * @param string $route Route relative to the web root of the web root path in the X2CRM source code
	 * @param string $file Path relative to the X2CRM web root of the file to be downloaded
	 * @param integer $maxAttempts Maximum times to attempt to download the file before giving up and throwing an exception.
	 * @return boolean
	 * @throws Exception 
	 */
	public function downloadSourceFile($route, $file,$maxAttempts = 5) {
		$fileUrl = "http://x2planet.com/$route/". str_replace(' ','%20',$file);
		$i = 0;
		if ($file != "") {
			$target = FileUtil::relpath($this->webRoot . "/temp/" . $file, $this->thisPath.'/');
			while (!FileUtil::ccopy($fileUrl, $target) && $i < $maxAttempts) {
				$i++;
			}
		}
		if($i >= $maxAttempts)
			throw new Exception(Yii::t('admin',"Failed to download source file {file}. Check that the file is available on the update server at {fileUrl}, and that x2planet.com can be accessed from this web server.",array('{file}'=>$file,'{fileUrl}'=>$fileUrl)));
		return true;
	}

	/**
	 * Drops all tables in the database.
	 * 
	 * This function is used to eliminate new tables that get created during
	 * updates that fail. This allows the next update attempt to be compatible,
	 * because any tables that get created in the process won't then be included
	 * and thus won't interfere with the update. In other words, when restoring
	 * a database backup, tables created since the time of the backup won't get 
	 * dropped, and that is why this function is actually necessary.
	 * 
	 * This function SHOULD NOT BE CALLED ANYWHERE except in 
	 * {@link restoreDatabaseBackup}, and the test wrapper function, and only 
	 * if a backup copy of the database exists.
	 */
	private function dropAllTables() {
		if ($this->dbParams['server'] == 'mysql') {
			// Generator command for the drop statements:
			$dtGen = $this->dbBackupCommand . ' --no-data --add-drop-table';
			$dtRun = $this->dbCommand;
			$descriptorGen = array(
				1 => array('pipe', 'w'),
				2 => array('file', implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::ERRFILE)), 'a'),
			);
			$descriptorRun = array(
				0 => array('pipe', 'r'),
				1 => array('file', implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::LOGFILE)), 'a'),
				2 => array('file', implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::ERRFILE)), 'a'),
			);
			$pipesGen = array();
			$pipesRun = array();
			if ((bool) $dtGen && (bool) $dtRun) {
				// Generate drop commands:
				$dtGenProc = proc_open($dtGen, $descriptorGen, $pipesGen);
				$sqlLines = explode("\n", stream_get_contents($pipesGen[1]));
				$ret = proc_close($dtGenProc);

				if($ret == -1)
					throw new Exception(Yii::t('admin','Failed to generate drop table statements in the process of restoring the database to a prior state.'));
				// Open the SQL runner command:
				$dtRunProc = proc_open($dtRun, $descriptorRun, $pipesRun);
				// Prevent foreign key constraints from halting progress:
				fwrite($pipesRun[0],'SET FOREIGN_KEY_CHECKS=0;');
				// Loop through output and run the drop commands (which should 
				// each be contained within single lines):
				foreach ($sqlLines as $sqlPart) {
					if (preg_match('/^DROP TABLE (IF EXISTS)?/', $sqlPart)) {
						fwrite($pipesRun[0],$sqlPart);
					}
				}
				fwrite($pipesRun[0],'SET FOREIGN_KEY_CHECKS=1;');
				$ret = proc_close($dtRunProc);
				if($ret == -1)
					throw new Exception(Yii::t('admin','Failed to run drop table statements in the process of restoring the database to a prior state.'));
			}
		} // No other DB types supported yet
	}

	/**
	 * Finalizes an update/upgrade by applying file, database and configuration changes.
	 * 
	 * This method replaces the SQL method as well as finishing copying files over.
	 * Both of these happen at once to prevent issues from files depending on SQL
	 * changes or vice versa.
	 * 
	 * @param string $scenario "update" or "upgrade"
	 * @param array $params parameters for update or upgrade
	 */
	public function enactChanges($scenario, $params, $autoRestore=false) {
		// Check for valid scenario:
		if(!in_array($scenario,array('update','upgrade')))
			throw new Exception(Yii::t('admin', 'Cannot apply changes without specifying a valid scenario.'));
		if(!array_key_exists('sqlList',$params))
			$params['sqlList'] = array();
		
		// Check parameters:
		$fields = array();
		if ($scenario == 'update')
			$fields = array('version', 'buildDate');
		else if ($scenario == 'upgrade')
			$fields = array('edition', 'unique_id');
		$missingFields = array_fill_keys($fields, true);
		foreach (array_keys($missingFields) as $key)
			if (array_key_exists($key, $params))
				$missingFields[$key] = false;
		$missingFields = array_keys(array_filter($missingFields));
		if ((bool) count($missingFields)) {
			throw new Exception(Yii::t('admin', 'Could not enact changes; missing the following parameters: {fields}', array('{fields}' => implode(", ", $missingFields))));
			return false;
		}

		// Run the necessary database changes:
		if((bool) count($params['sqlList']))
			$this->enactDatabaseChanges($params['sqlList'],$autoRestore);

		// The hardest part of the update (database changes) is now done. If any
		// errors occurred in the database changes, they should have thrown 
		// exceptions with appropriate messages by now.
		// 
		// Now, copy the cache of downloaded files into the live install:
		$this->restoreBackup("temp");
		// Delete old files:
		if (array_key_exists('deletionList', $params))
			if (is_array($params['deletionList']) && !empty($params['deletionList']))
				$this->removeFiles($params['deletionList']);
		if ($scenario == 'update') {
			$this->resetAssets();
			// Apply configuration changes and clear out the assets folder:
			$this->regenerateConfig($params['version'], null, $params['buildDate']);
		} else if ($scenario == 'upgrade') {
			// Change the edition and product key to reflect the upgrade:
			$admin = Yii::app()->params->admin = CActiveRecord::model('Admin')->findByPk(1);
			$admin->edition = $params['edition'];
			$admin->unique_id = $params['unique_id'];
			$admin->save();
		}
		// Remove the database backup; it is now invalid/obsolete:
		if(!$this->keepDbBackup)
			$this->removeDatabaseBackup();
		// Done.
		return true;
	}

	/**
	 * Runs a list of SQL commands.
	 * 
	 * @param array $sqlList List of commands to run
	 * @param array $sqlRun List of commands previously run, if any
	 */
	public function enactDatabaseChanges($sqlList, $backup=false) {
		$pdo = Yii::app()->db->pdoInstance;
		$sqlRun = array();
		foreach ($sqlList as $query) {
			if ($query != "") {
				try { // Run the update SQL.
					$command = $pdo->prepare($query);
					$result = $command->execute();
					if ($result !== false)
						$sqlRun[] = $query;
					else {
						$errorInfo = $command->errorInfo();
						$this->sqlError($query, $sqlRun, '(' . $errorInfo[0] . ') ' . $errorInfo[2]);
					}
				} catch (PDOException $e) { // A database change failed to apply
					$sqlErr = $e->getMessage();
					try {
						if ($backup) { // Run the recovery
							$this->restoreDatabaseBackup();
							$dbRestoreMessage = Yii::t('admin', 'The database has been restored to the backup copy.');
						} else { // No recovery available; print messages instead
							if ((bool) realpath($this->dbBackupPath)) // Backup available
								$dbRestoreMessage = Yii::t('admin', 'To restore the database to its previous state, use the database dump file {file} stored in protected/data', array('{file}'=>self::BAKFILE));
							else // No backup available
								$dbRestoreMessage = Yii::t('admin','If you made a backup of the database before running the updater, you will need to apply it manually.');
						}
					} catch (Exception $re) { // Database recovery failed.
						$dbRestoreMessage = $re->getMessage();
					}
					$this->sqlError($query, $sqlRun, "$sqlErr\n$dbRestoreMessage");
				}
			}
		}
		return true;
	}

	/**
	 * Magic getter for {@link dbBackupCommand}
	 * @return string
	 * @throws Exception
	 */
	public function getDbBackupCommand() {
		if (!isset($this->_dbBackupCommand)) {
			if ($this->dbParams['server'] == 'mysql') {
				// Test for the availability of mysqldump:
				$prog = 'mysqldump';
				$ret = 0;
				$result = exec('mysqldump --help', $out, $ret);
				if ($ret !== 0) {
					$result = exec('mysqldump.exe --help', $out, $ret);
					if ($ret !== 0)
						throw new Exception(Yii::t('admin', 'Unable to perform database backup; the "mysqldump" utility is not available on this system.'));
					else
						$prog = 'mysqldump.exe';
				}
				$this->_dbBackupCommand = $prog . " -h{$this->dbParams['dbhost']} -u{$this->dbParams['dbuser']} -p{$this->dbParams['dbpass']} {$this->dbParams['dbname']}";
			} else { // no other database types supported yet...
				return null;
			}
		}
		return $this->_dbBackupCommand;
	}
	
	/**
	 * Magic getter for {@link dbBackupPath}
	 * @return type 
	 */
	public function getDbBackupPath() {
		if(!isset($this->_dbBackupPath))
			$this->_dbBackupPath = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath,'data',self::BAKFILE));
		return $this->_dbBackupPath;
	}

	/**
	 * Magic getter for {@link dbCommand}
	 * @return string
	 * @throws Exception 
	 */
	public function getDbCommand() {
		if (!isset($this->_dbCommand)) {
			// Test for the availability of mysql command line client/utility:
			if ($this->dbParams['server'] == 'mysql') {
				$prog = 'mysql';
				$ret = 0;
				$result = exec('mysql --help', $out, $ret);
				if ($ret !== 0) {
					$result = exec('mysql.exe --help', $out, $ret);
					if ($ret !== 0)
						throw new Exception(Yii::t('admin', 'Cannot restore database; the MySQL command line client is not available on this system.'));
					else
						$prog = 'mysql.exe';
				}
				$this->_dbCommand = $prog . " -h{$this->dbParams['dbhost']} -u{$this->dbParams['dbuser']} -p{$this->dbParams['dbpass']} {$this->dbParams['dbname']}";
			} else { // no other DB types supported yet..
				return null;
			}
		}
		return $this->_dbCommand;
	}

	/**
	 * Magic getter for database parameters from the application's DSN and {@link CDbConnection}
	 */
	public function getDbParams() {
		if (!isset($this->_dbParams)) {
			$this->_dbParams = array();
			if (preg_match('/mysql:host=([^;]+);dbname=([^;]+)/', Yii::app()->db->connectionString, $param)) {
				$this->_dbParams['dbhost'] = $param[1];
				$this->_dbParams['dbname'] = $param[2];
				$this->_dbParams['server'] = 'mysql';
			} else {
				// No other DBMS's supported yet...
				return false;
			}
			$this->_dbParams['dbuser'] = Yii::app()->db->username;
			$this->_dbParams['dbpass'] = Yii::app()->db->password;
		}
		return $this->_dbParams;
	}

	/**
	 * Magic getter for {@link keepDbBackup}
	 * @return bool
	 */
	public function getKeepDbBackup() {
		return $this->_keepDbBackup;
	}

	/**
	 * Magic getter for {@link noHalt}
	 * @return bool
	 */
	public function getNoHalt() {
		return self::$_noHalt;
	}

	/**
	 * Magic getter for {@link getThisPath}
	 * @return string
	 */
	public function getThisPath() {
		if (!isset($this->_thisPath))
			$this->_thisPath = realpath('./');
		return $this->_thisPath;
	}

	/**
	 * Web root magic getter.
	 * 
	 * Resolves the absolute path to the webroot of the application without using
	 * the 'webroot' alias, which only works in web requests.
	 * @return string 
	 */
	public function getWebRoot() {
		if (!isset($this->_webRoot))
			$this->_webRoot = realpath(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','')));
		return $this->_webRoot;
	}

	/**
	 * Creates a backup of a list of files in a specified folder.
	 * 
	 * @param array $fileList List of files with paths relative to the web root
	 * @param string $dir Directory in the webroot where the backup will be stored
	 * @return array List of files that were backed up successfully
	 * @throws Exception
	 */
	public function makeBackup($fileList, $dir = 'backup') {
		$copiedFiles = array();
		foreach ($fileList as $file) {
			$relFile = FileUtil::relpath($this->webRoot . "/$file", $this->thisPath . '/');
			$relBackup = FileUtil::relpath($this->webRoot . "/$dir/$file", $this->thisPath . '/');
			if (file_exists($relFile)) { // Just ignore it if it isn't there
				$succeeded = FileUtil::ccopy($relFile, $relBackup);
				if (!$succeeded)
					throw new Exception(Yii::t('admin', 'During backup, failed to copy file {relFile} to {relBackup}. Working directory: {cwd}', array('{relFile}' => $relFile, '{relBackup}' => $relBackup, '{cwd}' => $this->thisPath)));

				$copiedFiles[] = $relBackup;
			}
		}
		return $copiedFiles;
	}

	/**
	 * Back up the application database.
	 * 
	 * Attempts to perform a database backup using mysqldump or any other tool
	 * that might exist.
	 * @return bool
	 */
	public function makeDatabaseBackup() {
		if (function_exists('proc_open')) {
			$errFile = self::ERRFILE;
			$descriptor = array(
				1 => array('file', $this->dbBackupPath, 'w'),
				2 => array('file', implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', $errFile)), 'w'),
			);
			$pipes = array();

			// Run the backup!
			$prog = $this->dbBackupCommand;
			if ((bool) $prog) {
				$backup = proc_open($this->dbBackupCommand, $descriptor, $pipes, $this->webRoot);
				$return = proc_close($backup);
				if ($return == -1)
					throw new Exception(Yii::t('admin', "Database backup process did not exit cleanly. See the file {file} for error output details.", array('{file}' => "protected/data/$errFile")));
				else
					return True;
			}
		} else
			throw new Exception(Yii::t('admin', 'Could not perform database backup; unable to spawn child processes on the server.'));
	}

	/**
	 * Run both database and file backup.
	 * 
	 * @param array $backupFiles List of files to back up
	 * @param strin $dir Subdirectory of the web root to save the backup in
	 * @throws Exception 
	 */
	public function makeFullBackup($backupFiles, $dir = 'backup') {
		$backedUp = $this->makeBackup($backupFiles, $dir);
		if (is_array($backedUp)) {
			try {
				if ($this->makeDatabaseBackup())
					self::respond(Yii::t('admin', 'Backed up files and database successfully!'));
			} catch (Exception $e) {
				throw new Exception(Yii::t('admin', "Backed up files.") . ' ' . $e->getMessage());
			}
		} else
			throw new Exception(Yii::t('admin', 'Failed to create a backup!'));
	}

	/**
	 * Rebuilds the configuration file.
	 * 
	 * @param type $newversion If set, change the version to this value in the resulting config file
	 * @param type $newupdaterVersion If set, change the updater version to this value in the resulting config file
	 * @param type $newbuildDate If set, change the build date to this value in the resulting config file
	 * @return bool
	 * @throws Exception
	 */
	public function regenerateConfig($newversion = Null, $newupdaterVersion = Null, $newbuildDate = null) {

		$newbuildDate = $newbuildDate == null ? time() : $newbuildDate;
		$basePath = Yii::app()->basePath;
		if (!file_exists($basePath . '/config/' . self::$configFilename)) {
			// App is using old config file. New one will be generated.
			include($basePath . '/config/emailConfig.php');
			include($basePath . '/config/dbConfig.php');
		} else {
			include($basePath . '/config/' . self::$configFilename);
		}

		if (!isset($appName)) {
			if (!empty(Yii::app()->name))
				$appName = Yii::app()->name;
			else
				$appName = "X2EngineCRM";
		}
		if (!isset($email)) {
			if (!empty(Yii::app()->params->admin->emailFromAddr))
				$email = Yii::app()->params->admin->emailFromAddr;
			else
				$email = 'contact@' . $_SERVER['SERVER_NAME'];
		}
		if (!isset($language)) {
			if (!empty(Yii::app()->language))
				$language = Yii::app()->language;
			else
				$language = 'en';
		}

		$config = "<?php\n";
		if (!isset($buildDate))
			$buildDate = $newbuildDate;
		if (!isset($updaterVersion))
			$updaterVersion = '';
		foreach (array('version', 'updaterVersion', 'buildDate') as $var)
			if (${'new' . $var} !== null)
				${$var} = ${'new' . $var};
		foreach (self::$confVars as $var => $q)
			$config .= "\$$var=$q" . ${$var} . "$q;\n";
		$config .= "?>";

		if (file_put_contents($basePath . '/config/' . self::$configFilename, $config) === false) {
			$file = 'protected/config/'.self::$configFilename;
			$contents = $this->isConsole ? "\n$config" : "<br /><pre>\n$config\n</pre>";
			throw new Exception(Yii::t('admin',"Failed to set version info in the configuration. To fix this issue, edit {file} and ensure its contents are as follows: {contents}",array('{file}'=>$file,'{contents}'=>$contents)));
		} else 
			return true;
	}

	/**
	 * Deletes the backup folder.
	 * 
	 * A wrapper method for {@link FileUtil::rrmdir()} that includes a safeguard.
	 * 
	 * @param string $dir 
	 */
	public function removeBackup($dir) {
		$budir = realpath("{$this->webRoot}/$dir");
		if ((bool) $budir)
			FileUtil::rrmdir($this->webRoot . "/" . $dir);
	}

	/**
	 * Deletes the database backup file.
	 */
	public function removeDatabaseBackup() {
		$dbBackup = realpath($this->dbBackupPath);
		if ((bool) $dbBackup)
			unlink($dbBackup);
	}

	/**
	 * Deletes a list of files.
	 * @param array $deletionList 
	 */
	public function removeFiles($deletionList) {
		foreach ($deletionList as $file) {
			$absFile = realpath("{$this->webRoot}/$file");
			if ((bool) $absFile) {
				unlink($absFile);
			}
		}
	}

	/**
	 * Removes everything in the assets folder.
	 */
	public function resetAssets() {
		$assetsDir = realpath($this->webRoot . '/assets');
		if (!(bool) $assetsDir)
			throw new Exception(Yii::t('admin', 'Assets folder does not exist.'));
		$assets = array_filter(scandir($assetsDir), function($n) {
					return !in_array($n, array('..', '.'));
				});
		foreach ($assets as $crcDir)
			FileUtil::rrmdir("$assetsDir/$crcDir");
	}


	/**
	 * In the case of a failed update or other event, restore files from a 
	 * backup location.
	 * 
	 * @param array $fileList Array of paths relative to webroot to restore from backup.
	 * @param string $dir Backup directory
	 */
	public function restoreBackup($dir, $fileList = array()) {
		$success = true;
		$copiedFiles = array();
		if (empty($fileList)) // Recursively copy the whole backup folder
			$success = $this->copyFile($dir);
		else { // Copy each file individually from the backup folder
			foreach ($fileList as $path) {
				$copied = $this->copyFile($path, $dir);
				$success = $success && $copied;
				if(!$copied)
					$copiedFiles[] = $path;
			}
		}
		if ($success)
			$this->removeBackup($dir);
		else {
			$message = Yii::t('admin','Failed to copy one or more files from {dir} into X2CRM.',array('{dir}'=>$dir));
			if(!empty($copiedFiles)) {
				$message .= Yii::t('admin','Check that they exist: {fileList}',array('{fileList}' => implode(', ',$copiedFiles)));
			}
			throw new Exception($message);
		}
		return $success;
	}

	/**
	 * Uses a database dump to reinstate the database backup.
	 * @return boolean
	 * @throws Exception 
	 */
	public function restoreDatabaseBackup() {
		$bakFile = $this->dbBackupPath;
		$logFile = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::ERRFILE));
		$errFile = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::LOGFILE));
		$this->checkDatabaseBackup($bakFile);
		if (function_exists('proc_open')) {
			$descriptor = array(
				0 => array('file', $bakFile, 'r'),
				1 => array('file', $logFile, 'a'),
				2 => array('file', $errFile, 'a'),
			);
			// Restore the backup!
			if ((bool) $this->dbCommand) {
				// A backup copy should exist at this point in the execution,
				// so it should be safe to call the dreaded dropAllTables method:
				$this->dropAllTables();
				$backup = proc_open($this->dbCommand, $descriptor, $pipes, $this->webRoot);
				$ret = proc_close($backup);
				if ($ret == -1)
					throw new Exception(Yii::t('admin', "Database restore process did not exit cleanly. See the files {err} and {res} for output details.", array('{err}' => "protected/data/$errFile", '{res}' => "protected/data/$logFile")));
				else {
					if (!$this->keepDbBackup)
						$this->removeDatabaseBackup();
					return True;
				}
			}
		} else
			throw new Exception(Yii::t('admin', 'Cannot restore database; unable to spawn child process on the server.'));
	}



	/**
	 * Magic setter for {@link keepDbBackup}
	 * @param type $value 
	 */
	public function setKeepDbBackup($value) {
		$this->_keepDbBackup = $value;
	}

	/**
	 * Magic setter for {@link noHalt}
	 * @param type $value 
	 */
	public function setNoHalt($value) {
		self::$_noHalt = $value;
	}

	/**
	 * Exits, returning SQL error messages
	 * 
	 * @param type $sqlRun
	 * @param type $errorMessage 
	 */
	public function sqlError($sqlFail, $sqlRun = array(), $errorMessage = null) {
		if (!$this->isConsole)
			$errorMessage = CHtml::encode($errorMessage);
		$message = Yii::t('admin', 'A database change failed to apply: {sql}.', array('{sql}' => $sqlFail)) . ' ';
		if (count($sqlRun)) {
			$message .= Yii::t('admin', '{n} changes were applied prior to this failure:', array('{n}' => count($sqlRun)));

			$sqlList = '';
			foreach ($sqlRun as $sqlStatemt)
				$sqlList .= ($this->isConsole ? "\n$sqlStatemt" : '<li>' . CHtml::encode($sqlStatemt) . '</li>');
			$message .= $this->isConsole ? $sqlList : "<ol>$sqlList</ol>";
			$message .= "\n" . Yii::t('admin', "Please save the above list.") . " \n\n";
		}
		if ($errorMessage !== null) {
			$message .= Yii::t('admin', "The error message given was:") . " $errorMessage";
		}

		$message .= "\n\n" . Yii::t('admin', "Update failed.");
		if (!$this->isConsole)
			$message = str_replace("\n", "<br />", $message);
		throw new Exception($message);
	}
	
	/**
	 * In which the updater downloads a new version of itself.
	 * 
	 * @param type $updaterCheck New version of the update utility
	 * @return array
	 */
	public function updateUpdater($updaterCheck) {

		// The files involved in the update process:
		$updaterFiles = array(
			"controllers/AdminController.php",
			"views/admin/updater.php",
			"components/UpdaterBehavior.php",
			"components/FileUtil.php",
			"components/ResponseBehavior.php"
		);

		// Try to retrieve the files:
		$failed2Retrieve = array();
		foreach ($updaterFiles as $file) {
			$remoteFile = "http://x2planet.com/updates/x2engine/protected/$file";
			try {
				$this->downloadSourceFile("updates/x2engine","protected/$file");
			} catch (Exception $e) {
				$failed2Retrieve[] = "protected/$file";
			}
		}
		
		// Copy the files into the live install
		if(!(bool)count($failed2Retrieve))
			$this->restoreBackup('temp');
		// Write the new updater version into the configuration; else 
		// the app will get stuck in a redirect loop
		$this->regenerateConfig(Null, $updaterCheck, Null);
		return $failed2Retrieve;
	}

}

?>
