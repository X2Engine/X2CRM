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




Yii::import('application.components.ResponseBehavior');
Yii::import('application.models.Admin');

// Extra safeguard, in case automatic creation fails, to maintain that the
// sub-components-directories aliases are valid:
foreach(array('util') as $compDir){
    $compDirPath = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'components', $compDir));
    if(!is_dir($compDirPath))
        @mkdir($compDirPath);
    if(is_dir($compDirPath))
        Yii::import("application.components.$compDir.*");
}



defined('X2_FTP_FILEOPER') or define('X2_FTP_FILEOPER', false);
defined('X2_FTP_HOST') or define('X2_FTP_HOST', 'localhost');
defined('X2_FTP_USER') or define('X2_FTP_USER', 'root');
defined('X2_FTP_PASS') or define('X2_FTP_PASS', '');
defined('X2_FTP_CHROOT_DIR') or define('X2_FTP_CHROOT_DIR', false);
defined('X2_UPDATE_BETA') or define('X2_UPDATE_BETA',false);

/**
 * Behavior class with application updater/upgrader utilities.
 *
 * Note to all future developers: it is important to bear in mind that if you
 * need to make changes to the updates system or the updater in general, they
 * must be backwards-compatible with all earlier versions of the software (or as
 * far back as possible).
 *
 * @property string $backCompatFile Path to the backwards compatibility flag file.
 * @property array $checksums When running an update, this is a list of all MD5 hashes of files to be applied, with filenames their keys and checksums their values.
 * @property string $checksumsContent The contents of the package contents digest file.
 * @property array $compatibilityStatus An array specifying compatibility issues.
 * @property array $configVars (read-only) variables imported from the configuration
 * @property string $dbBackupCommand (read-only) command to be used for backing up the database
 * @property string $dbBackupPath (read-only) Full path to the database backup file.
 * @property string $dbCommand (read-only) command to be used for running SQL from files
 * @property array $dbParams (read-only) Database information retrieved from {@link CDbConnection}
 * @property string $edition (read-only) The edition of the installation of X2Engine.
 * @property array $files (read-only) A list of files and their statuses (present, missing or corrupt).
 * @property array $filesByStatus (read-only) An array of files in each status category
 * @property array $filesStatus (read-only) A summary (showing counts) of all files' statuses.
 * @property string $latestUpdaterVersion (read-only) The latest version of the updater utility according to the updates server
 * @property string $lockFile Path to the file to use for locking when applying changes
 * @property array $manifest When running an update, this is the change manifest as retrieved from the update package
 * @property boolean $noHalt Whether to terminate the PHP process if errors occur
 * @property PDO $pdo (read-only) The app's PDO instance
 * @property array $requirements (read-only) Requirements script output.
 * @property string $scenario Usage scenario, i.e. update/upgrade
 * @property string $sourceDir (read-only) Absolute path to the base directory of source files to be applied in the update/upgrade
 * @property string $sourceFileRoute (read-only) Route (relative URL on the updates server) from which to download source files in a pinch
 * @property string $thisPath (read-only) Absolute path to the current working directory
 * @property string $uniqueId (read-only) Unique ID of the installation
 * @property string $updateDataRoute (read-only) Relative URL (to the base URL of the update server) from which to get update manifests.
 * @property string $updateDir (read-only) the directory of updates.
 * @property string $updatePackage (read-only) destination path for the update package.
 * @property string $updateServer Base URL of the web server from which to fetch data and files
 * @property string $version Version of X2Engine
 * @property string $webRoot (read-only) Absolute path to the web root, even if not in a web request
 * @property array $webUpdaterActions (read-only) array of actions in the web-based updater utility.
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdaterBehavior extends ResponseBehavior {
    ///////////////
    // CONSTANTS //
    ///////////////

    /**
     * SQL backup dump file
     */

    const BAKFILE = 'update_backup.sql';

    /**
     * Defines a file that (for extra security) prevents race conditions in the unlikely event that 
     * multiple requests to the web updater to enact file/database changes are made.
     */
    const LOCKFILE = 'app_update.lock';

    const PKGFILE = 'update.zip';

    const TMP_DIR = 'tmp';

    const ERRFILE = 'update_db_restore.err';

    const LOGFILE = 'update_db_restore.log';

    const BCOFILE = 'backcompat.run';

    // Whatever you do, DO NOT change this to a blank string. It WILL result in
    // the obliteration of all files in the app!
    const UPDATE_DIR = 'update';

    const SECURITY_IMG = 'cG93ZXJlZF9ieV94MmVuZ2luZS5wbmc=';

    // Error codes:
    const ERR_ISLOCKED = 1;

    const ERR_CHECKSUM = 2;

    const ERR_MANIFEST = 3;

    const ERR_NOUPDATE = 4;

    const ERR_FILELIST = 5;

    const ERR_NOTAPPLY = 6;

    const ERR_UPSERVER = 7;

    const ERR_DBNOBACK = 8;

    const ERR_DBOLDBAK = 9;

    const ERR_SCENARIO = 10;

    const ERR_NOPROCOP = 11;

    const ERR_DATABASE = 12;

    // File statuses:

    const FILE_PRESENT = 0;

    const FILE_CORRUPT = 1;

    const FILE_MISSING = 2;


    ///////////////////////
    // STATIC PROPERTIES //
    ///////////////////////

    /**
     * Set to true in cases of testing, to avoid having errors end PHP execution.
     * @var boolean
     */
    private static $_noHalt = false;

    /**
     * Core configuration file name.
     */
    public static $configFilename = 'X2Config.php';

    /**
     * Configuration file variables as [variable name] => [value quote wrap]
     * as can be found in the file protected/config/X2Config.php
     * @var array
     */
    public static $_configVarNames = array(
        'appName',
        'email',
        'host',
        'user',
        'pass',
        'dbname',
        'version',
        'buildDate',
        'updaterVersion',
        'language',
    );

    /**
     * Explicit override of default {@link ResponseBehavior::$_logCategory}
     * @var string
     */
    public static $_logCategory = 'application.updater';

    ///////////////////////////
    // NON-STATIC PROPERTIES //
    ///////////////////////////

    private $_canSpawnChildren = false;

    /**
     * Holds the value of {@link checksums}
     * @var array
     */
    private $_checksums;

    /**
     * Holds the contents of the checksums file.
     * @var type
     */
    private $_checksumsContent;

    private $_checksumsAvail = false;

    private $_compatibilityStatus;

    private $_configVars;

    /**
     * True if a backup of the database is available.
     * @var bool
     */
    private $_databaseBackupExists = false;

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
     * The application's edition.
     */
    private $_edition;

    /**
     * An array showing the status of files to be applied.
     * @var array
     */
    private $_files;

    /**
     * An array indexed by status of arrays of files with each status.
     */
    private $_filesByStatus;

    /**
     * An array showing file stats based on {@link files}
     * @var type
     */
    private $_filesStatus;

    /**
     * Latest version of the updater utility according to the updates server.
     * @var string
     */
    private $_latestUpdaterVersion;

    /**
     * Stores the manifest as retrieved from the file.
     * @var array
     */
    private $_manifest;

    /**
     * If true, indicates that the manifest file is available.
     * @var bool
     */
    private $_manifestAvail = false;

    /**
     * If true, indicates that the package to be applied is actually applicable,
     * i.e. if it's an update from the current version to a later version.
     * @var type
     */
    private $_packageApplies = false;
    
    /**
     * If true, indicates that the update/upgrade package indeed exists on the
     * local filesystem.
     * @var bool
     */
    private $_packageExists = false;

    /**
     * Stores output of the requirements check script
     * @var array
     */
    private $_requirements;

    /**
     * stores {@link scenario}
     */
    private $_scenario;

    /**
     * Stores the application settings object
     * @var Admin
     */
    private $_settings;

    /**
     * Stores {@link sourceDir}
     * @var string
     */
    private $_sourceDir;

    /**
     * Current working directory.
     * @var string 
     */
    private $_thisPath;

    /**
     * Unique ID of the install.
     * @var type
     */
    private $_uniqueId;

    /**
     * Version of X2Engine.
     */
    private $_version;

    /**
     * Absolute path to the web root
     * @var string
     */
    private $_webRoot;

    private $_webUpdaterActions;
    
    /**
     * List of files used by the behavior
     * @var array
     */
    public $updaterFiles = array(
        "views/admin/updater.php",
        "components/UpdaterBehavior.php",
        "components/util/FileUtil.php",
        "components/util/EncryptUtil.php",
        "components/util/ResponseUtil.php",
        "components/ResponseBehavior.php",
        "components/views/requirements.php",
        "commands/UpdateCommand.php"
    );

    /**
     * Converts an array formatted like a behavior or controller actions array
     * entry and returns the path (relative to {@link X2WebApplication.basePath}
     * to the class file. {@link Yii::getPathOfAlias()} is unsafe to use,
     * because in cases where this function is to be used, the files may not
     * exist yet.
     *
     * @param array $classes An array containing a "class" => [Yii path alias] entry
     */
    public static function classAliasPath($alias){
        return preg_replace(':^application/:', '', str_replace('.', '/', $alias)).'.php';
    }

    /**
     * In the case of a failed update or other event, restore files from a
     * backup location.
     *
     * @param array $fileList Array of paths relative to webroot to restore from backup.
     * @param string $dir Backup directory
     */
    public function applyFiles($dir=null){
        $success = true;
        $copiedFiles = array();
        
        if(!empty($dir)) // Recursively copy a folder relative to webroot
            $success = $this->copyFile($dir);
        else{ // Copy files individually from source according to the manifest
            $dir = self::UPDATE_DIR.DIRECTORY_SEPARATOR.'source';
            foreach($this->manifest['fileList'] as $path){
                $copied = $this->copyFile($path, $dir);
                $success = $success && $copied;
                if(!$copied)
                    $copiedFiles[] = $path;
            }
        }
        if($success)
            $this->cleanUp();
        else{
            $message = Yii::t('admin', 'Failed to copy one or more files from {dir} into X2Engine. You may need to copy them manually.', array('{dir}' => $dir));
            if(!empty($copiedFiles)){
                $message .= ' '.Yii::t('admin', 'Check that they exist: {fileList}', array('{fileList}' => implode(', ', $copiedFiles)));
            }
            throw new CException($message);
        }
        return $success;
    }

    /**
     * Backwards compatibility hacks - I mean, hooks - to run after self-updating.
     *
     * Sometimes, downloading a copy of itself isn't enough. The updater must do
     * additional work after it self-updates in order to resolve unforeseen
     * post-refresh issues.
     *
     * This works by creating a file in the runtime folder that counts as
     * evidence that it has been run already and thus does not need to be run
     * again (to avoid endless redirect loops in the web updater, for instance).
     *
     * This was added because the decision was made to add ResponseUtil as a
     * dependency, yet because the file already exists as of many versions
     * before, it wouldn't be automatically fetched, because the earlier version
     * of the updater wouldn't have known that it needed to be updated first.
     * 
     * @return bool True or false; true indicates that action has been taken,
     *  whereas false indicates no action needs to be taken nor has been taken.
     */
    public function backCompatHooks($latestUpdaterVersion) {
        $runFlag = $this->backCompatFile;
        if(file_exists($runFlag)) {
            return false;
        }
        // Create the "flag" file as evidence that this function has been run:
        if(@file_put_contents($runFlag,time()) === false)
            return false; // Nothing more that can or should be done past here.


        $version = $this->configVars['version'];
        $updaterVersion = $this->configVars['updaterVersion'];
        
        $this->output(Yii::t('admin', 'Running backwards compatibility actions for this version.'));

        // This variable indicates that a second self-update should be performed:
        $action = false;

        // Missing requirement ResponseUtil before 4.0:
        if (version_compare($version, '4.0') < 0
                && version_compare($version,'3.4') >= 0) {
            $action = true;
            $this->downloadSourceFile("protected/components/util/ResponseUtil.php") ;
            $this->applyFiles(self::TMP_DIR);
        }

        // Any other problems that arise in future versions to go here

        return $action;
    }

    public function attach($owner) {
        if (X2_FTP_FILEOPER && ! $this->isConsole){
            $dir = str_replace('protected', '', Yii::app()->basePath);
            FileUtil::ftpInit(X2_FTP_HOST, X2_FTP_USER, X2_FTP_PASS, $dir, X2_FTP_CHROOT_DIR);
        }
        parent::attach($owner);
    }

    /**
     * Checks for the existence of an unpacked update package folder, and if
     * present, whether all files are present and complete.
     */
    public function checkFiles(){
        // Check integrity of files:
        $files = array();
        foreach($this->checksums as $file => $digest){
            if(!file_exists($path = $this->updateDir.DIRECTORY_SEPARATOR.FileUtil::rpath($file))){
                $files[$file] = self::FILE_MISSING;
            }else if(md5_file($path) != $digest){
                $files[$file] = self::FILE_CORRUPT;
            }else{
                $files[$file] = self::FILE_PRESENT;
            }
        }
        return $files;
    }


    /**
     * Generic dependency and prerequisite checking function.
     *
     * A wrapper for all functions with names beginning with "checkIf"; runs a
     * test if it hasn't been run already and returns the result (or throws an
     * exception). Stores the result of a check so that it isn't necessary to
     * run the check again.
     *
     * All "checkIf" functions must have the rest of their names named after a
     * condition, i.e. "AllClear" to "checkIfAllClear", and have a corresponding 
     * private property named accordingly (i.e. for "checkIfFoo" the property
     * must be named "_foo"). The correspoding property must have a default
     * value of false (boolean).
     */
    public function checkIf($name,$throw = true) {
        if($this->{"_$name"})
            return true;
        return $this->{"_$name"} = $this->{"checkIf".ucfirst($name)}($throw);
    }

    /**
     * Checks whether it is possible to run system commands using PHP's
     * {@link proc_open()} function.
     * 
     * @param type $throw
     * @return boolean
     */
    public function checkIfCanSpawnChildren($throw = true) {
        if(!@function_exists('proc_open')){
            if($throw) {
                throw new CException(Yii::t('admin', 'Unable to spawn child processes on the server because the "proc_open" function is not available.'),self::ERR_NOPROCOP);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if the package content digests file is present and not empty.
     *
     * Said file (specifically, its content) is necessary for checking whether
     * files were downloaded and extracted properly.
     * 
     * @param type $throw
     * @return boolean
     * @throws CException
     */
    public function checkIfChecksumsAvail($throw = true) {
        if(!$this->checkIf('packageExists',$throw))
            return false;
        if(!file_exists($checksumsFile = $this->updateDir.DIRECTORY_SEPARATOR.'contents.md5')) {
            if($throw)
                throw new CException(Yii::t('admin', 'Cannot verify package contents.').' '.Yii::t('admin', 'Checksum file is missing.'), self::ERR_CHECKSUM);
            else
                return false;
        }
        $checksums = $this->checksumsContent;
        
        if(empty($checksums)) {
            if($throw)
                throw new CException(Yii::t('admin', 'Cannot verify package contents.').' '.Yii::t('admin', 'Checksum file is empty.'), self::ERR_CHECKSUM);
            else
                return false;
        }
        return true;
    }

    /**
     * Checks to see if a file exists and isn't very old..
     * @param type $bakFile
     * @throws Exception
     */
    public function checkIfDatabaseBackupExists($throw = true){
        $bakFile = $this->dbBackupPath;
        if(!file_exists($bakFile)) {
            if($throw)
                throw new CException(Yii::t('admin', 'Database backup not present.'), self::ERR_DBNOBACK);
            else
                return false;
        }else{ // Test the timestamp of the backup copy, just to be extra sure it's safe to use
            $backupTime = filemtime($bakFile);
            $currenTime = time();
            if($currenTime - $backupTime > 86400) { // Updating the software should NEVER take a whole day!
                if($throw)
                    throw new CException(Yii::t('admin', 'The database backup is over 24 hours old and may thus be unreliable.'), self::ERR_DBOLDBAK);
                else
                    return false;
            }
        }
        return true;
    }

    /**
     * Checks if the manifest file is present and intact.
     * 
     * @param type $throw If false, returns; if true, throws or returns based on
     *  the success of the check
     * @return bool|string If not throwing: it will be the string representing
     *  the relative path to the manifest if it exists, and false if it doesn't.
     * @throws CException
     */
    public function checkIfManifestAvail($throw = true){
        if(!$this->checkIf('checksumsAvail',$throw))
            return false;
        $manifestFile = $this->updateDir.DIRECTORY_SEPARATOR.'manifest.json';
        if(!file_exists($manifestFile)){
            if($throw) {
                throw new CException(Yii::t('admin', 'Manifest file at {file} is missing.', array('{file}' => $manifestFile)), self::ERR_MANIFEST);
            } else {
                return false;
            }
        }

        if(md5_file($manifestFile) != $this->checksums['manifest.json']) {
            if($throw) {
                throw new CException(Yii::t('admin','Manifest file at {file} is corrupt.', array('{file}' => $manifestFile)),self::ERR_MANIFEST);
            } else {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Ensures that the package actually applies to the current version and
     * edition.
     * @throws CException
     */
    public function checkIfPackageApplies($throw = true) {
        if(!$this->checkIf('manifestAvail',$throw))
            return false;
        // Wrong updater version
        if($this->manifest['updaterVersion'] != $this->configVars['updaterVersion']) {
            if($throw)
                throw new CException(Yii::t('admin','The package to be applied is not compatible with the current updater version.'),self::ERR_NOTAPPLY);
            else
                return false;
        }
        // Wrong initial app version
        if($this->manifest['fromVersion'] != $this->version) {
            if($throw)
                throw new CException(Yii::t('admin','The package to be applied does not correspond to this version of X2Engine; it was meant for version {fv} and this installation is at version {av}.',array(
                    '{fv}'=>$this->manifest['fromVersion'],
                    '{av}'=>$this->version
                )),self::ERR_NOTAPPLY);
            else
                return false;
        }
        // Wrong initial app edition
        if($this->manifest['fromEdition'] != $this->edition) {
            if($throw)
                throw new CException(Yii::t('admin','The package to be applied does not correspond to this edition of X2Engine; it was meant for edition "{fe}" and this installation is edition "{ae}".',array(
                    '{fe}' => $this->manifest['fromEdition'],
                    '{ae}' => $this->edition,
                )),self::ERR_NOTAPPLY);
            else
                return false;
        }
        // Wrong scenario
        if($this->manifest['scenario'] != $this->scenario) {
            if($throw)
                throw new CException(Yii::t('admin','The package is designated for the scenario "{pscen}" but the updater is being run in the scenario "{bscen}"',array('{pscen}'=>$this->manifest['scenario'],'{bscen}'=>$this->scenario)),self::ERR_NOTAPPLY);
            else
                return false;
        }
        return true;
    }

    /**
     * Check to see if there is an update package present in the filesystem.
     *
     * @param bool $throw Whether or not to throw an exception instead of returning false
     * @return boolean True if the update package directory and contents digest
     *  file are present; false otherwise
     * @throws CException
     */
    public function checkIfPackageExists($throw = true) {
        if(!is_dir(FileUtil::relpath($this->updateDir, $this->thisPath.DIRECTORY_SEPARATOR))) {
            if($throw)
                throw new CException(Yii::t('admin', 'There is no package to apply.'),self::ERR_NOUPDATE);
            else
                return false;
        }
        return true;
    }

    
    /**
     * Branding validity check.
     */
    public function checkPartner($content=false) {
        $partnerFiles = array(
            'about' => array('about'),
            'footer' => array('footer'),
            'login' => array('login'),
        );
        $fileStatus = array_fill_keys(array_keys($partnerFiles),false);
        foreach($partnerFiles as $name=>$sections) {
            $path = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'partner',''));
            if(!file_exists($file = $path."$name.php"))
                $file = $path.$name.'_example.php';
            if(!file_exists($file))
                continue;
            $delimPatterns = array();
            foreach($sections as $secName) {
                $delimPatterns[] = sprintf('/\* @start:%s \*/.*?/\* @end:%s \*/',$secName,$secName);
            }
            $defaultContent = trim(preg_replace('%(?:'.implode('|',$delimPatterns).')%ms','',file_get_contents($file)));
            $fileStatus[$name] = $content ? $defaultContent : md5($defaultContent);
        }
        return $fileStatus;
    }
    


    /**
     * Securely obtain the latest version.
     */
    public function checkUpdates($returnOnly = false){
        $i = empty($this->uniqueId)?'none':$this->uniqueId;
        $v = $this->version;
        $e = $this->edition;
        $secImage = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..', 'images', base64_decode(self::SECURITY_IMG)));
        $context = stream_context_create(array(
            'http' => array('timeout' => 4)  // set request timeout in seconds
                ));
        $updateCheckUrl = $this->updateServer.'/installs/updates/check?'.http_build_query(compact('i', 'v'), '', '&');
        // Get a "secure" code from the server
        if(($securityKey = FileUtil::getContents($updateCheckUrl, 0, $context)) === false) {
            if(!$returnOnly)
                Yii::app()->session['versionCheck'] = true;
            return Yii::app()->params->version;
        }
        $h = hash('sha512', base64_encode(file_exists($secImage) ? file_get_contents($secImage) : null).$securityKey);
        $n = null;
        if(!($e == 'opensource' || empty($e)))
            $n = Yii::app()->db->createCommand()->select('COUNT(*)')->from('x2_users')->queryScalar();
        $newVersion = FileUtil::getContents($this->updateServer.'/installs/updates/check?'.http_build_query(compact('i', 'v', 'h', 'n'), '', '&'), 0, $context);
        if(empty($newVersion)) {
            if(!$returnOnly)
                Yii::app()->session['versionCheck'] = true;
            return $this->version;
        }

        if(!($this->isConsole || $returnOnly)){
            Yii::app()->session['versionCheck'] = true;
            if(version_compare($newVersion, $v) > 0 && $i !== 'none'){ // if the latest version is newer than our version and updates are enabled
                Yii::app()->session['versionCheck'] = false;
                Yii::app()->session['newVersion'] = $newVersion;
            }
        }
        return $newVersion;
    }

    /**
     * Deletes the update package folder
     *
     * @param string $dir
     */
    public function cleanUp(){
        FileUtil::rrmdir($this->updateDir);
        FileUtil::rrmdir($this->updatePackage);
    }

    /**
     * Copies files out of a folder and into the live installation. 
     * 
     * Wrapper for {@link FileUtil::ccopy} for updates that can operate
     * recursively without requiring a list of files.
     *
     * @param string $path Path relative to the web root to be copied 
     *  (this is the target, unless dir is null, in which case it's the source)
     * @param string $file The path to copy (assumed relative to the webroot)
     * @param string $dir The name of the backup directory; "." means top-level directory
     */
    public function copyFile($path, $dir = null, $ds = DIRECTORY_SEPARATOR){

        // Resolve paths
        $bottomLevel = $dir === null;
        if($bottomLevel)
            $dir = $path;
        $absPath = $bottomLevel ? $this->webRoot.$ds.$path : $this->webRoot.$ds.$dir.$ds.$path;
        $relPath = FileUtil::relpath($absPath, $this->thisPath.$ds);
        $absLivePath = $this->webRoot.$ds.$path;
        $relLivePath = FileUtil::relpath($absLivePath, $this->thisPath.$ds);
        $success = file_exists($relPath);
        if($success){
            if(is_dir($relPath) || $bottomLevel){
                $objects = scandir($relPath);
                foreach($objects as $object){
                    if($object != "." && $object != ".."){
                        // The target shall be the object itself if in the
                        // root level of the backup directory; otherwise,
                        // prepend the path up to the current point (which is
                        // copied in through the recursion levels in the stack)
                        $copyTarget = $bottomLevel ? $object : $path.$ds.$object;
                        $success == $success && $this->copyFile($copyTarget, $dir);
                        if(!$success)
                            throw new CException(Yii::t('admin', 'Failed to copy from {relPath}; working directory = {cwd}', array('{relPath}' => $relPath, '{cwd}' => $this->$thisPath)));
                    }
                }
            } else{
                return FileUtil::ccopy($relPath, $relLivePath);
            }
        }
        if(!$success)
            throw new CException(Yii::t('admin', 'Failed to copy from {relPath} (path does not exist); working directory = {cwd}', array('{relPath}' => $relPath, '{cwd}' => $this->thisPath)));
        return (bool) $success;
    }

    /**
     * Obtains update/upgrade data package from the server.
     * @param type $version
     * @param type $uniqueId
     * @param type $edition
     */
    public function downloadPackage($version=null,$uniqueId = null, $edition = null) {
        if(empty($version))
            $version = $this->configVars['version'];
        if(empty($uniqueId))
            $uniqueId = $this->uniqueId;
        if(empty($edition))
            $edition = $this->edition;
        $url = $this->updateServer.'/'.$this->getUpdateDataRoute($version, $uniqueId, $edition);
        if(!FileUtil::ccopy($url,$this->updatePackage,true))
            throw new CException(Yii::t('admin','Could not download package; update server error.'),self::ERR_UPSERVER);
    }

    /**
     * Retrieves a file from the update server. It will be stored in a temporary
     * directory, "tmp", in the web root. To copy it into the live install, use
     * restoreBackup on target "tmp".
     *
     * @param string $route Route relative to the web root of the web root path in the X2Engine source code
     * @param string $file Path relative to the X2Engine web root of the file to be downloaded
     * @param integer $maxAttempts Maximum times to attempt to download the file before giving up and throwing an exception.
     * @return boolean
     * @throws Exception
     */
    public function downloadSourceFile($file, $route = null, $maxAttempts = 5){
        if(empty($route)) // Auto-construct a route based on ID & edition info:
            $route = $this->sourceFileRoute;
        $fileUrl = "{$this->updateServer}/{$route}/".strtr($file, array(' ' => '%20'));
        $i = 0;
        if($file != ""){
            $target = FileUtil::relpath(implode(DIRECTORY_SEPARATOR, array($this->webRoot, self::TMP_DIR, FileUtil::rpath($file))), $this->thisPath.DIRECTORY_SEPARATOR);
            while(!FileUtil::ccopy($fileUrl, $target) && $i < $maxAttempts){
                $i++;
            }
        }
        if($i >= $maxAttempts){
            throw new CException(Yii::t('admin', "Failed to download source file {file}. Check that the file is available on the update server at {fileUrl}, and that x2planet.com can be accessed from this web server.", array('{file}' => $file, '{fileUrl}' => $fileUrl)),self::ERR_UPSERVER);
        }
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
    private function dropAllTables(){
        if($this->dbParams['server'] == 'mysql'){
            // Generator command for the drop statements:
            $dtGen = $this->dbBackupCommand.' --no-data --add-drop-table';
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
            if((bool) $dtGen && (bool) $dtRun){
                // Generate drop commands:
                $dtGenProc = proc_open($dtGen, $descriptorGen, $pipesGen);
                $sqlLines = explode("\n", stream_get_contents($pipesGen[1]));
                $ret = proc_close($dtGenProc);

                if($ret == -1)
                    throw new CException(Yii::t('admin', 'Failed to generate drop table statements in the process of restoring the database to a prior state.'));
                // Open the SQL runner command:
                $dtRunProc = proc_open($dtRun, $descriptorRun, $pipesRun);
                // Prevent foreign key constraints from halting progress:
                fwrite($pipesRun[0], 'SET FOREIGN_KEY_CHECKS=0;');
                // Loop through output and run the drop commands (which should
                // each be contained within single lines):
                foreach($sqlLines as $sqlPart){
                    if(preg_match('/^DROP TABLE (IF EXISTS)?/', $sqlPart)){
                        fwrite($pipesRun[0], $sqlPart);
                    }
                }
                fwrite($pipesRun[0], 'SET FOREIGN_KEY_CHECKS=1;');
                $ret = proc_close($dtRunProc);
                if($ret == -1)
                    throw new CException(Yii::t('admin', 'Failed to run drop table statements in the process of restoring the database to a prior state.'));
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
     * @param array $params parameters for update or upgrade
     */
    public function enactChanges($autoRestore = false){
        // Check for a lockfile:
        $lockFile = $this->lockFile;
        if(file_exists($lockFile)) {
            $lockTime =  (int) trim(file_get_contents($lockFile));
            if(time()-$lockTime > 3600) // No operation should take longer than an hour
                FileUtil::removeLockfile($lockFile);
            else
                throw new CException(Yii::t('admin', 'An operation that began {t} is in progress (to apply database and file changes to X2Engine). If you are seeing this message, and the stated time is less than a minute ago, this is most likely because your web browser made a duplicate request to the server. Please stand by while the operation completes. Otherwise, you may delete the lock file {file} and try again.',array('{t}'=>strftime('%h %e, %r',$lockTime),'{file}'=>$this->lockFile)),self::ERR_ISLOCKED);
        }

        // One last check: that the package exists and is applicable:
        $this->checkIf('packageApplies');

        // Check that all the files in the update package are present and intact:
        $corrupt = $this->filesStatus[self::FILE_CORRUPT];
        $missing = $this->filesStatus[self::FILE_MISSING];
        if($missing || $corrupt){
            $badFiles = array_merge($this->filesByStatus[self::FILE_CORRUPT], $this->filesByStatus[self::FILE_MISSING]);
            $msg = Yii::t('admin', 'Unable to apply changes.');
            $msg .= Yii::t('admin','The following files are corrupt or missing: {list}', array('{list}' => implode(',', $badFiles)));
            throw new CException($msg, self::ERR_FILELIST);
        }

        // No turning back now. This is it!
        //
        // Create the lockfile:
        FileUtil::createLockfile($lockFile);

        // Run the necessary database changes:
        try{
            $this->output(Yii::t('admin','Enacting changes to the database...'));
            $this->enactDatabaseChanges($autoRestore);
        }catch(Exception $e){
            // The operation cannot proceed and is technically finished 
            // executing, so there's no use keeping the lock file around except
            // to frustrate and confuse the end user.
            FileUtil::removeLockfile($lockFile);
            // Toss the Exception back up so it propagates through the stack and
            // the caller can use its message for responding to the user:
            throw $e;
        }

        $lastException = null;

        try{
            // The hardest part of the update (database changes) is now done. If any
            // errors occurred in the database changes, they should have thrown
            // exceptions with appropriate messages by now.
            //
			// Now, copy the cache of downloaded files into the live install:
            $this->output(Yii::t('admin','Enacting changes to the fileset...'));
            $this->applyFiles();
            // Delete old files:
            $this->removeFiles($this->manifest['deletionList']);
            $this->output(Yii::t('admin','Cleaning up...'));
            if($this->scenario == 'update'){
                $this->resetAssets();
                // Apply configuration changes and clear out the assets folder:
                $this->regenerateConfig($this->manifest['targetVersion'], $this->manifest['updaterVersion'], $this->manifest['buildDate']);
                $this->version = $this->manifest['targetVersion'];
            }else if($this->scenario == 'upgrade'){
                // Change the edition and product key to reflect the upgrade:
                $admin = CActiveRecord::model('Admin')->findByPk(1);
                // refresh admin schema since it may have changed during db changes
                Yii::app()->db->schema->refresh ();
                $admin->refreshMetaData ();
                $admin->edition = $this->manifest['targetEdition'];
                if(!(empty($this->uniqueId)||$this->uniqueId=='none')) // Set new unique id
                    $admin->unique_id = $this->uniqueId;
                $admin->save();
                $this->edition = $admin->edition;
            }
        }catch(Exception $e){
            $lastException = $e;
        }

        // Remove the lock file
        FileUtil::removeLockfile($lockFile);
        // Remove the backwards compatibility flag since the update is now done
        if(file_exists($bcFile = $this->backCompatFile))
            unlink($bcFile);

        // Clear the cache
        $cache = Yii::app()->cache;
        if(!empty($cache))
            $cache->flush();
        if (isset (Yii::app()->cache2)) {
            Yii::app()->cache2->flush ();
        }
        // Clear the auth cache
        Yii::app()->db->createCommand('DELETE FROM x2_auth_cache WHERE 1')->execute();
        if($this->scenario == 'update'){
            // Log everyone out; session data may now be obsolete.
            Yii::app()->db->createCommand('DELETE FROM x2_sessions')->execute();
        }

        // Done.
        if($lastException instanceof Exception) {
            throw new CException(Yii::t('admin','Encountered an issue after applying database changes. The error message given was {msg}.',array('{msg}'=>$lastException->getMessage())));
        }else{
            return false;
        }
    }
    
    /**
     * Runs a list of SQL commands.
     *
     * @param bool $backup Whether to restore the database backup in the case of a database failure
     */
    private function enactDatabaseChanges($backup = false){
        $sqlRun = array();
        $sqlLists = $this->scenario == 'upgrade' ? array('sqlUpgrade') : array('sqlForce', 'sqlList');
        $skipOnFail = array('sqlUpgrade' => 0, 'sqlList' => 0, 'sqlForce' => 1);
        $pdo = Yii::app()->db->getPdoInstance();

        foreach($this->manifest['data'] as $part){
            foreach($sqlLists as $delta){
                foreach($part[$delta] as $sql){
                    if($sql != ""){
                        try{ // Run the update SQL.
                            $this->output(Yii::t('admin','Running SQL:').' '.$sql);
                            $command = $pdo->prepare($sql);
                            $result = $command->execute();
                            if($result !== false)
                                $sqlRun[] = $sql;
                            else{
                                $errorInfo = $command->errorInfo();
                                $this->sqlError($sql, $sqlRun, '('.$errorInfo[0].') '.$errorInfo[2]);
                            }
                        }catch(PDOException $e){ // A database change failed to apply
                            if($skipOnFail[$delta])
                                continue;
                            $sqlErr = $e->getMessage();
                            try{
                                $this->handleSqlFailure ($sql, $sqlRun, $sqlErr, $backup);
                            }catch(Exception $re){ // Database recovery failed. We're SOL
                                $dbRestoreMessage = $re->getMessage();
                                $this->sqlError($sql, $sqlRun, "$sqlErr\n$dbRestoreMessage");
                            }
                        }
                    }
                }
            }
            if(count($part['migrationScripts'])){
                $this->output(Yii::t('admin', 'Running migration scripts for version {ver}...', array('{ver}' => $part['version'])));
                $sqlRun = $this->runMigrationScripts($part['migrationScripts'], $sqlRun, $backup);
            }
        }
        return true;
    }

    /**
     * Handle database backups in the event of failure
     * @param string $error SQL Error
     * @param bool $backup Whether to restore from backup
     */
    public function handleSqlFailure($sql, $sqlRun, $sqlErr, $backup, $throw = true) {
        if ($backup) { // Run the recovery
            $this->restoreDatabaseBackup();
            $dbRestoreMessage = Yii::t('admin', 'The database has been restored to the backup copy.');
        } else { // No recovery available; print messages instead
            if((bool) realpath($this->dbBackupPath)) // Backup available
                $dbRestoreMessage = Yii::t('admin', 'To restore the database to its previous state, use the database dump file {file} stored in {dir}', array('{file}' => self::BAKFILE, '{dir}' => 'protected/data'));
            else // No backup available
                $dbRestoreMessage = Yii::t('admin', 'If you made a backup of the database before running the updater, you will need to apply it manually.');
        }
        $this->sqlError($sql, $sqlRun, "$sqlErr\n$dbRestoreMessage", $throw);
    }

    /**
     * Notify the server that the update has finished
     */
    public function finalizeUpdate($scenario, $unique_id, $version, $edition) {
        if ($scenario !== 'update')
            return;
        $params = array(
            'unique_id' => $unique_id,
            'version' => $version,
            'edition' => $edition,
        );
        return FileUtil::getContents (
            $this->updateServer . '/installs/updates/finalizeUpdate?' . 
                http_build_query ($params, '', '&'));
    }

    /**
     * Generates a "definition list"
     * @param type $list Array with keys the terms and values their definition entries
     * @param type $web Whether to generate markup (if true) or console output (if false)
     * @return type
     */
    public function formatDefinitionList($list,$web) {
        $messages = $web ? '<dl>' : "\n";
        foreach($list as $term => $definition) {
            $messages .= $web ? '<dt>'.$term.'</dt>': "\n$term"; // .implode("\n\t\t",$compat['req']['reqMessages'][$level]);
            if(is_array($definition)){
                $messages .=  $web ? '<dd><ul><li>'.implode('</li><li>', $definition).'</li></ul></dd>' : "\n\t".implode("\n\t", $definition);
            } else {
                $messages .=  $web ? "<dd>$definition</dd>" : "\n\t$definition";
            }
        }
        $messages .= $web ? "</dl>" : "\n";
        return $messages;
    }

    /**
     * Returns the path to the backwards-compatibility flag file.
     * @return string
     */
    public function getBackCompatFile() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'runtime',self::BCOFILE));
    }

    /**
     * Parse output formatted according to that of md5sum into an array of
     * hashes indexed by filename. If no output is specified, the update
     * package's content digest will be used as input.
     *
     * Obtains {@link checksums}.
     * @param string $checksums Optional, to override package digest file
     *  content. If specified, the property won't be altered; the getter will be
     *  used as an auxiliary parsing function.
     * @return array
     */
    public function getChecksums(){
        if(empty($this->_checksums)){
            $this->checkIf('checksumsAvail');
            preg_match_all('/^(?<md5sum>[a-f0-9]{32})\s+(?<filename>\S.*)$/m', $this->checksumsContent, $cs);
            $checksums = array();
            for($i = 0; $i < count($cs[0]); $i++){
                $checksums[trim($cs['filename'][$i])] = $cs['md5sum'][$i];
            }
            $this->_checksums = $checksums;
        }
        return $this->_checksums;
    }
    
    public function getChecksumsContent() {
        if(empty($this->_checksumsContent))
            $this->_checksumsContent = trim(file_get_contents($this->updateDir.DIRECTORY_SEPARATOR.'contents.md5'));
        return $this->_checksumsContent;
    }

    /**
     * Checks the current X2Engine installation for compatibility issues with the
     * current package as defined in the manifest and requirements check script.
     *
     * @return array
     */
    public function getCompatibilityStatus(){
        if(!isset($this->_compatibilityStatus)){
            // A variable which is the catch-all flag for there being messages for
            // the user to heed:
            $allClear = true;

            ////////////////////////////////
            // Check system requirements: //
            ////////////////////////////////
            
            $req = $this->requirements;
            $allClear = $allClear && $req['canInstall'];

            /////////////////////////////////
            // Check database permissions: //
            /////////////////////////////////
            $databasePermissionError = $this->testDatabasePermissions();
            $allClear = $allClear && !$databasePermissionError;

            /////////////////////////////////////////////////////////////////////////
            // Check that user hasn't created any custom modules that are the same //
            // name as new modules added in the update:                            //
            /////////////////////////////////////////////////////////////////////////
            $modulesInUpdate = array();
            foreach($this->manifest['fileList'] as $file){
                if(preg_match(':protected/modules/([a-zA-Z0-9]+)/.*:', $file, $match)){
                    $modulesInUpdate[$match[1]] = 1;
                }
            }
            $modulesInUpdate = array_keys($modulesInUpdate);
            $crit = new CDbCriteria();
            $crit->addInCondition('name', $modulesInUpdate);
            $crit->addColumnCondition(array('custom' => 1));
            $modRec = Modules::model()->findAll($crit);
            if(!empty($modRec)){
                $allClear = false;
                $modules = array_map(function($m){
                            return $m->name;
                        }, $modRec);
            }else{
                $modules = array();
            }

            ////////////////////////////////////////////////////////////
            // Check fields records for conflicts with custom fields: //
            ////////////////////////////////////////////////////////////
            $Dsql = $this->scenario == 'upgrade' ? 'sqlUpgrade' : 'sqlList';
            $n_p = 0; // Parameter counter
            $params = array(); // Parameters
            $fieldsEntries = array(); // Array of (modelName,fieldName) pairs to test against (as SQL, with parameters)
            foreach($this->manifest['data'] as $version){
                foreach($version[$Dsql] as $sql){
                    if(preg_match('/INSERT INTO `?x2_fields`?\s+\((?<columns>[a-zA-Z0-9_,`\s]+)\)\s+VALUES\s+(?<records>.+);?$/im', $sql, $match)){
                        // Array representing the column description for the insert statement:
                        $columns = array_map(function($c){
                                    return trim($c, ' `');
                                }, explode('`,`', $match['columns']));
                        $n_col = count($columns);
                        // Array of arrays, each nested array containing the column values for the fields record:
                        $records = array_filter(array_map(function($r){
                                            return explode(',', trim($r, ' \'"()'));
                                        }, explode('),(', $match['records'])), function($r) use($n_col){
                                    return count($r) == $n_col; // Ignore records with commas in them
                                });
                        // Indices of the columns with the unique constraint:
                        $i_mn = array_search('modelName', $columns);
                        $i_fn = array_search('fieldName', $columns);
                        foreach($records as $record){
                            $p_mn = ":modelName$n_p";
                            $p_fn = ":fieldName$n_p";
                            $params[$p_fn] = trim($record[$i_fn],'"\'');
                            $params[$p_mn] = trim($record[$i_mn],'"\'');
                            $fieldsEntries[] = "($p_mn,$p_fn)";
                            // Increment parameter counter:
                            $n_p++;
                        }
                    }
                }
            }
            
            $conflictingFields = array();
            if(!empty($fieldsEntries)){
                try{
                    // The full query to find conflicting fields:
                    $fields = Yii::app()->db->createCommand('SELECT `modelName`,`fieldName` FROM x2_fields WHERE (`modelName`,`fieldName`) IN ('.implode(',', $fieldsEntries).');')->queryAll(true, $params);
                    $conflictingFields = array_fill_keys(array_unique(array_map(function($f){
                                                return $f['modelName'];
                                            }, $fields)), array());
                    foreach($fields as $f){
                        $conflictingFields[$f['modelName']][] = $f['fieldName'];
                    }
                } catch(Exception $e){
                    // If anything goes wrong... Just ignore it. This was
                    // meant to work with specifically-formatted SQL
                    // statements generated by the update builder.
                }
            }
            // Special case for Actions.actionDescription, which was deleted
            // from the fields table in 3.0 due to a structural change and and
            // added back in 3.5.5:
            if(version_compare($this->version,'3.0') < 0 && isset($conflictingFields['Actions']['actionDescription'])) {
                unset($conflictingFields['Actions']['actionDescription']);
                if(count($conflictingFields['Actions']) == 0) {
                    unset($conflictingFields['Actions']);
                }
            }


            $allClear = $allClear && empty($conflictingFields);

            ///////////////////////////////////////////////////
            // Check updated PHP files for custom analogues: //
            ///////////////////////////////////////////////////
            $customFiles = array();
            foreach($this->manifest['fileList'] as $file){
                if(preg_match('/^.+\.php$/', $file)){
                    $localFile = preg_match(':/controllers/:', $file) ? preg_replace('/(\w+)Controller\.php$/', 'My${1}Controller.php', $file) : $file;
                    $customFile = implode(DIRECTORY_SEPARATOR, array($this->webRoot, 'custom', FileUtil::rpath($localFile)));
                    if(file_exists($customFile)){
                        $customFiles[] = $file;
                        $allClear = false;
                    }
                }
            }

            $this->_compatibilityStatus = compact('req','databasePermissionError', 'modules', 'conflictingFields', 'customFiles', 'allClear');
        }
        return $this->_compatibilityStatus;
    }

    /**
     * Gets configuration variables from the configuration file(s).
     * @return array
     */
    public function getConfigVars(){
        if(!isset($this->_configVars)){
            $configPath = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'config', self::$configFilename));
            if(!file_exists($configPath))
                $this->regenerateConfig();
            $populateVars = function($path) {
                include($path);
                $vars = compact(array_keys(get_defined_vars()));
                unset($vars['path']);
                return $vars;
            };
            $this->_configVars = $populateVars($configPath);
            $this->version = $this->_configVars['version'];
        }
        return $this->_configVars;
    }

    /**
     * Magic getter for {@link dbBackupCommand}
     * @return string
     * @throws Exception
     */
    public function getDbBackupCommand(){
        if(!isset($this->_dbBackupCommand)){
            $this->checkIf('canSpawnChildren');
            if($this->dbParams['server'] == 'mysql'){
                // Test for the availability of mysqldump:
                $descriptor = array(
                    0 => array('pipe', 'r'),
                    1 => array('pipe', 'w'),
                    2 => array('pipe', 'w'),
                );
                $testProc = proc_open('mysqldump --help', $descriptor, $pipes);
                $ret = proc_close($testProc);
                $prog = 'mysqldump';
                unset($pipes);

                if($ret !== 0){
                    $testProc = proc_open('mysqldump.exe --help', $descriptor, $pipes);
                    $ret = proc_close($testProc);
                    if($ret !== 0)
                        throw new CException(Yii::t('admin', 'Unable to perform database backup; the "mysqldump" utility is not available on this system.'));
                    else
                        $prog = 'mysqldump.exe';
                }
                $passArg = '';
                if(!empty($this->dbParams['dbpass'])){
                    $quotedPass = escapeshellarg($this->dbParams['dbpass']);
                    $passArg = " -p{$quotedPass}";
                }
                $this->_dbBackupCommand = $prog." -h{$this->dbParams['dbhost']} -u{$this->dbParams['dbuser']}$passArg {$this->dbParams['dbname']}";
            } else{ // no other database types supported yet...
                return null;
            }
        }
        return $this->_dbBackupCommand;
    }

    /**
     * Magic getter for {@link dbBackupPath}
     * @return string
     */
    public function getDbBackupPath(){
        if(!isset($this->_dbBackupPath))
            $this->_dbBackupPath = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::BAKFILE));
        return $this->_dbBackupPath;
    }

    /**
     * Magic getter for {@link dbCommand}
     * @return string
     * @throws Exception
     */
    public function getDbCommand(){
        if(!isset($this->_dbCommand)){
            $this->checkIf('canSpawnChildren');
            // Test for the availability of mysql command line client/utility:
            if($this->dbParams['server'] == 'mysql'){
                $descriptor = array(
                    0 => array('pipe', 'r'),
                    1 => array('pipe', 'w'),
                    2 => array('pipe', 'w'),
                );
                $testProc = proc_open('mysql --help', $descriptor, $pipes);
                $ret = proc_close($testProc);
                $prog = 'mysql';
                unset($pipes);
                
                if($ret !== 0){
                    $testProc = proc_open('mysql.exe --help', $descriptor, $pipes);
                    $ret = proc_close($testProc);
                    if($ret !== 0)
                        throw new CException(Yii::t('admin', 'Cannot restore database; the MySQL command line client is not available on this system.'));
                    else
                        $prog = 'mysql.exe';
                }
                $passArg = '';
                if(!empty($this->dbParams['dbpass'])){
                    $quotedPass = escapeshellarg($this->dbParams['dbpass']);
                    $passArg = " -p{$quotedPass}";
                }
                $this->_dbCommand = $prog." -h{$this->dbParams['dbhost']} -u{$this->dbParams['dbuser']}$passArg {$this->dbParams['dbname']}";
            } else{ // no other DB types supported yet..
                return null;
            }
        }
        return $this->_dbCommand;
    }

    /**
     * Magic getter for database parameters from the application's DSN and {@link CDbConnection}
     * @return array
     */
    public function getDbParams(){
        if(!isset($this->_dbParams)){
            $this->_dbParams = array();
            if(preg_match('/mysql:host=([^;]+);dbname=([^;]+)/', Yii::app()->db->connectionString, $param)){
                $this->_dbParams['dbhost'] = $param[1];
                $this->_dbParams['dbname'] = $param[2];
                $this->_dbParams['server'] = 'mysql';
            }else{
                // No other DBMS's supported yet...
                return false;
            }
            $this->_dbParams['dbuser'] = Yii::app()->db->username;
            $this->_dbParams['dbpass'] = Yii::app()->db->password;
        }
        return $this->_dbParams;
    }

    /**
     * Backwards-compatible function for obtaining the edition of the
     * installation. Attempts to not fail and return a valid value even if the
     * application singleton doesn't store the information.
     *
     * It uses try/catch blocks because Yii's way of checking if properties
     * exist as of 1.1.x does not include properties "inherited" from behaviors.
     *
     * @return string
     */
    public function getEdition(){
        if(!isset($this->_edition)){
            // Safe default for versions too early to have "admin" in the
            // params, or for the "edition" attribute to exist
            $this->_edition = 'opensource';
            try{
                // Versions 4.0 and later:
                $this->_edition = Yii::app()->edition;
            }catch(Exception $e){
                if(Yii::app()->params->hasProperty('admin')){
                    // Most versions before 4.0:
                    $admin = Yii::app()->params->admin;
                    if($admin->hasAttribute('edition')){
                        $this->_edition = $admin->edition == null ? 'opensource' : $admin->edition;
                    }
                }
            }
        }
        return $this->_edition;
    }

    /**
     * Obtains the list of files and their statuses (essentially a wrapper
     * function for {@link checkFiles})
     * 
     * @return array
     */
    public function getFiles(){
        if(empty($this->_files)){
            $files = $this->checkFiles();
            if(empty($files)){
                return $files;
            }
            $this->_files = $files;
        }
        return $this->_files;
    }

    /**
     * Return an array of arrays of files each indexed by the status (present,
     * corrupt or missing) of those sets of files.
     * @return array
     */
    public function getFilesByStatus() {
        if(!isset($this->_filesByStatus)) {
            if(isset($this->_filesStatus))
                $this->_filesStatus = null;
            $this->getFilesStatus();
        }
        return $this->_filesByStatus;
    }

    /**
     * Obtains {@link filesStatus}
     * @return array
     */
    public function getFilesStatus(){
        if(empty($this->_filesStatus)){
            $files = $this->files;
            $statusCodes = array(self::FILE_PRESENT, self::FILE_CORRUPT, self::FILE_MISSING);
            $filesByStatus = array_fill_keys($statusCodes,array());
            $fss = array_fill_keys($statusCodes, 0);
            if(is_array($files)){
                foreach($files as $file => $status) {
                    $filesByStatus[$status][] = $file;
                    $fss[$status]++;
                }
                $this->_filesByStatus = $filesByStatus;
                $this->_filesStatus = $fss;
            }else{
                $this->_filesByStatus = false;
                return $files;
            }
        }
        return $this->_filesStatus;
    }

    /**
     * Gets the latest version of the updater utility
     *
     * @return string
     */
    public function getLatestUpdaterVersion(){
        if(!isset($this->_latestUpdaterVersion)){
            $context = stream_context_create(array(
                'http' => array(
                    'timeout' => 15  // Timeout in seconds
                    )));
            $this->_latestUpdaterVersion = FileUtil::getContents($this->updateServer.'/installs/updates/updateCheck', 0, $context);
        }
        return $this->_latestUpdaterVersion;
    }

    /**
     * Magic getter for {@link lockFile}
     */
    public function getLockFile(){
        return implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'runtime', self::LOCKFILE));
    }

    /**
     * Obtains {@link manifest}
     */
    public function getManifest(){
        if(!isset($this->_manifest)){
            $this->checkIf('manifestAvail');
            $manifestFile = $this->updateDir.DIRECTORY_SEPARATOR.'manifest.json';
            $this->_manifest = json_decode(file_get_contents($manifestFile),1);
            if(empty($this->_manifest))
                throw new CException(Yii::t('admin', 'Manifest file at {file} contains malformed JSON.', array('{file}' => $manifestFile)), self::ERR_MANIFEST);
        }
        return $this->_manifest;
    }

    /**
     * Magic getter for {@link noHalt}
     * @return bool
     */
    public function getNoHalt(){
        return self::$_noHalt;
    }

    /**
     * Magic getter for {@link requirements}
     * @return type
     * @throws CException
     */
    public function getRequirements() {
        if(!isset($this->_requirements)){
            $reqScript = implode(DIRECTORY_SEPARATOR, array(
                Yii::app()->basePath,
                'components',
                'views',
                'requirements.php'
            ));
            if(!is_readable($reqScript))
                throw new CException(Yii::t('admin', "Requirements check script at {path} is missing or not readable.",array('{path}'=>$reqScript)));
            // The following two variables used internally by the requirements
            // checking script:
            $returnArray = true;
            $thisFile = Yii::app()->request->scriptFile;
            $this->_requirements = @require_once($reqScript);
            if(!$this->_requirements) {
                CException(Yii::t('admin', "Requirements check script encountered an internal error."));
            }
        }
        return $this->_requirements;
    }

    public function getScenario() {
        if(!isset($this->_scenario)) {
            throw new CException(Yii::t('admin','Scenario not set.'),self::ERR_SCENARIO);
        }
        return $this->_scenario;
    }

    /**
     * Getter for {@link settings}
     */
    public function getSettings() {
        if(!isset($this->_settings)){
            if(Yii::app()->hasProperty('settings')){
                $this->_settings = Yii::app()->settings;
            } else if(Yii::app()->params->hasProperty('admin')) {
                $this->_settings = Yii::app()->params->admin;
            } else {
                $this->_settings = CActiveRecord::model('Admin')->findByPk(1);
            }
        }
        return $this->_settings;

    }

    /**
     * Obtains {@link sourceDir}
     * @return string
     */
    public function getSourceDir(){
        if(!isset($this->_sourceDir)){
            $this->_sourceDir = implode(DIRECTORY_SEPARATOR, array($this->updateDir, 'source'));
        }
        return $this->_sourceDir;
    }

    /**
     * Auto-construct a relative base URL on the updates server from which to retrieve
     * source files.
     *
     * @param type $edition
     * @param type $uniqueId
     * @return string
     */
    public function getSourceFileRoute($edition = null, $uniqueId = null){
        foreach(array('edition', 'uniqueId') as $attr)
            if(empty(${$attr}))
                ${$attr} = $this->$attr;
        return "installs/update/$edition/$uniqueId";
    }

    /**
     * Magic getter for {@link getThisPath}
     * @return string
     */
    public function getThisPath(){
        if(!isset($this->_thisPath))
            $this->_thisPath = realpath('./');
        return $this->_thisPath;
    }

    /**
     * Backwards-compatible function for obtaining the unique id. Very similar
     * to getEdition in regard to its backwards compatibility.
     * @return type
     */
    public function getUniqueId(){
        if(!isset($this->_uniqueId)){
            try {
                $this->_uniqueId = Yii::app()->settings->unique_id;
            } catch(Exception $e) {
                $admin = Yii::app()->params->admin;
                if($admin->hasAttribute('unique_id')){
                    $this->_uniqueId = empty($admin->unique_id) ? 'none' : $admin->unique_id;
                }else{
                    $this->_uniqueId = 'none';
                }
            }
        }
        return $this->_uniqueId;
    }

    /**
     * Retrieves update data from the server. For previewing an update before
     * downloading it; this essentially retrieves the manifest without
     * retrieving the full package.
     * 
     * @param string $version Version updating/upgrading from
     * @param string $uniqueId The identifier/product key for this installation of X2Engine
     * @param string $edition The edition updating/upgrading from
     * @return array
     */
    public function getUpdateData($version = null, $uniqueId = null, $edition = null){
        $updateData = FileUtil::getContents($this->updateServer.'/'.$this->getUpdateDataRoute($version,$uniqueId,$edition).'/manifest.json');
        if(!$updateData) {
            throw new CException(Yii::t('admin','Update server error.'),self::ERR_UPSERVER);
        }
        $updateData = json_decode($updateData,1);
        if(!(bool) $updateData || !is_array($updateData)) {
            throw new CException(Yii::t('admin','Malformed data in updates server response; invalid JSON.'));
        }
        return $updateData;
    }

    /**
     * Gets a relative URL on the update server from which to obtain update data
     *
     * @param string $version
     * @param string $edition
     * @param string $uniqueId
     * @return string
     */
    public function getUpdateDataRoute($version = null, $uniqueId = null, $edition = null){
        $route = $this->scenario == 'upgrade' ? 'installs/upgrades/{unique_id}/{edition}_{n_users}' : 'installs/updates/{version}/{unique_id}';
        $configVars = $this->configVars;
        if(!isset($this->version) && empty($version))
            extract($configVars);
        foreach(array('version', 'uniqueId', 'edition') as $attr) // Use current properties as defaults
            if(empty(${$attr}))
                ${$attr} = $this->$attr;
        $params = array('{version}' => $version, '{unique_id}' => $uniqueId, '{scenario}'=>$this->scenario);
        if($edition != 'opensource' || $this->scenario == 'upgrade'){
            $route .= $this->scenario == 'upgrade' ? '': '_{edition}_{n_users}';
            $params['{edition}'] = $edition;
            $params['{n_users}'] = Yii::app()->db->createCommand()->select('COUNT(*)')->from('x2_users')->queryScalar();
        }
        return strtr($route, $params);
    }

    public function getUpdateDir(){
        return $this->webRoot.DIRECTORY_SEPARATOR.self::UPDATE_DIR;
    }

    public function getUpdatePackage() {
        return $this->webRoot.DIRECTORY_SEPARATOR.self::PKGFILE;
    }

    /**
     * Base URL of the web server from which to fetch data and files
     */
    public function getUpdateServer() {
        return X2_UPDATE_BETA ? 'http://beta.x2planet.com' : 'https://x2planet.com';
    }

    public function getVersion() {
        if(!isset($this->_version))
            $this->_version = Yii::app()->params->version;
        return $this->_version;
    }

    /**
     * Web root magic getter.
     *
     * Resolves the absolute path to the webroot of the application without using
     * the 'webroot' alias, which only works in web requests. Note, the {@link realpath()}
     * function will strip off the trailing directory separator
     * @return string
     */
    public function getWebRoot(){
        if(!isset($this->_webRoot))
            $this->_webRoot = realpath(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..','')));
        return $this->_webRoot;
    }

    /**
     * Returns the actions associated with the web-based updater.
     *
     * @param bool $getter If being called as a getter, this method will attempt
     * 	to download actions if they don't exist on the server yet. Otherwise, if
     * 	this parameter is explicitly set to False, the return value will include
     * 	the abstract base action class (in which case it should not be used in
     * 	the return value of {@link CController::actions()} ) for purposes of
     * 	checking dependencies.
     * @return array An array of actions appropriate for inclusion in the return
     * 	value of {@link CController::actions()}.
     */
    public function getWebUpdaterActions($getter = true){
        if(!isset($this->_webUpdaterActions) || !$getter){
            $this->_webUpdaterActions = array(
                'backup' => array('class' => 'application.components.webupdater.DatabaseBackupAction'),
                'updateStage' => array('class' => 'application.components.webupdater.UpdateStageAction'),
                'updater' => array('class' => 'application.components.webupdater.UpdaterAction'),
            );
            $allClasses = array_merge($this->_webUpdaterActions, array('base' => array('class' => 'application.components.webupdater.WebUpdaterAction')));
            if($getter){
                $this->requireDependencies();
            }else{
                return $allClasses;
            }
        }
        return $this->_webUpdaterActions;
    }

    /**
     * Back up the application database.
     *
     * Attempts to perform a database backup using mysqldump or any other tool
     * that might exist.
     * @return bool
     */
    public function makeDatabaseBackup(){
        try{
            $this->checkIf('canSpawnChildren');
        }catch(Exception $e){
            throw new CException(Yii::t('admin', 'Could not perform database backup. {reason}', array('{reason}' => $e->getMessage())));
        }
        $dataDir = Yii::app()->basePath.DIRECTORY_SEPARATOR.'data';
        if(!is_dir($dataDir))
            mkdir($dataDir);
        $errFile = self::ERRFILE;
        $descriptor = array(
            1 => array('file', $this->dbBackupPath, 'w'),
            2 => array('file', implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', $errFile)), 'w'),
        );
        $pipes = array();

        // Run the backup!
        $prog = $this->dbBackupCommand;
        if((bool) $prog){
            $backup = proc_open($this->dbBackupCommand, $descriptor, $pipes, $this->webRoot);
            $return = proc_close($backup);
            if($return !== 0)
                throw new CException(Yii::t('admin', "Database backup process did not exit cleanly. See the file {file} for error output details.", array('{file}' => "protected/data/$errFile")));
            else
                return True;
        }
    }

    /**
     * Rebuilds the configuration file and performs the final few little update tasks.
     * 
     * @param type $newversion If set, change the version to this value in the resulting config file
     * @param type $newupdaterVersion If set, change the updater version to this value in the resulting config file
     * @param type $newbuildDate If set, change the build date to this value in the resulting config file
     * @param string $newAppName If set, will be used to replace the app name in the config file. 
     * @return bool
     * @throws Exception
     */
    public function regenerateConfig($newversion = Null, $newupdaterVersion = Null, $newbuildDate = null, $newAppName=null){

        $newbuildDate = $newbuildDate == null ? time() : $newbuildDate;
        $basePath = Yii::app()->basePath;
        $configPath = implode(DIRECTORY_SEPARATOR, array($basePath, 'config', self::$configFilename));
        if(!file_exists($configPath)){
            // App is using the old config files. New ones will be generated.
            include(implode(DIRECTORY_SEPARATOR, array($basePath, 'config', 'emailConfig.php')));
            include(implode(DIRECTORY_SEPARATOR, array($basePath, 'config', 'dbConfig.php')));
        }else{
            include($configPath);
        }

        if(!isset($appName)){
            if(!empty(Yii::app()->name))
                $appName = Yii::app()->name;
            else
                $appName = "X2Engine";
        }
        if ($newAppName) {
            $appName = $newAppName;
        }
        if(!isset($email)){
            if(!empty($this->settings->emailFromAddr))
                $email = $this->settings->emailFromAddr;
            else
                $email = 'contact@'.$_SERVER['SERVER_NAME'];
        }
        if(!isset($language)){
            if(!empty(Yii::app()->language))
                $language = Yii::app()->language;
            else
                $language = 'en';
        }

        $config = "<?php\n";
        if(!isset($buildDate))
            $buildDate = $newbuildDate;
        if(!isset($updaterVersion))
            $updaterVersion = '';
        foreach(array('version', 'updaterVersion', 'buildDate') as $var)
            if(${'new'.$var} !== null)
                ${$var} = ${'new'.$var};
        foreach(self::$_configVarNames as $var) {
            if(!empty(${"new$var"}))
                ${$var} = ${"new$var"};
            $config .= "\$$var=".var_export(${$var},1).";\n";
        }
        $config .= "?>";


        if(file_put_contents($configPath, $config) === false){
            $contents = $this->isConsole ? "\n$config" : "<br /><pre>\n$config\n</pre>";
            throw new CException(Yii::t('admin', "Failed to set version info in the configuration. To fix this issue, edit {file} and ensure its contents are as follows: {contents}", array('{file}' => $configPath, '{contents}' => $contents)));
        }else{
            // Create a new encryption key if none exists
            $key = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'config','encryption.key'));
            $iv = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'config','encryption.iv'));
            if(!file_exists($key) || !file_exists($iv)){
                try{
                    $encryption = new EncryptUtil($key, $iv);
                    $encryption->saveNew();
                }catch(Exception $e){
                    throw new CException(Yii::t('admin', "Succeeded in setting the version info in the configuration, but failed to create a secure encryption key. The error message was: {message}", array('{message}' => $e->getMessage())));
                }
            }
            // Set permissions on encryption
            $this->configPermissions = 100600;
            // Reset config vars property
            if(isset($this->_configVars))
                unset($this->_configVars);
            
            // Finally done.
            return true;
        }
    }

    /**
     * Deletes the database backup file.
     */
    public function removeDatabaseBackup(){
        $dbBackup = realpath($this->dbBackupPath);
        if((bool) $dbBackup)
            unlink($dbBackup);
    }

    /**
     * Deletes a list of files.
     * @param array $deletionList
     */
    public function removeFiles($deletionList){
        foreach($deletionList as $file){
            // Use realpath to get platform-dependent path
            $absFile = realpath("{$this->webRoot}/$file");
            if((bool) $absFile){
                // Get existing file's name to ensure that we're deleting the correct file.
                // This check is only necessary on case-insensitive file systems
                $basename = pathinfo ($absFile, PATHINFO_BASENAME);
                if (basename ($file) === $basename)
                    unlink($absFile);
            }
        }
    }

    /**
     * Generates user-friendly messages for letting users know about update
     * compatibility issues.
     *
     * @param string $h Tag in which to wrap "section" titles
     * @param string $htmlOptions Options for the titles of each section
     */
    public function renderCompatibilityMessages($h="h3",$htmlOptions=array()) {
        $compat = $this->getCompatibilityStatus();
        $web = !$this->isConsole;
        if($compat['allClear']) {
            return Yii::t('admin','No potential compatibility issues could be found.');
        }
        $messages = '';

        // Section: missing system requirements
        if($compat['req']['hasMessages']) {
            $reqLevels = array(
                1 => Yii::t('admin', 'Minor'),
                2 => Yii::t('admin', 'Major'),
                3 => Yii::t('admin', 'Critical')
            );
            $messages .= $web ? '<dl>' : "\n";
            $definitions = array();
            foreach($reqLevels as $level => $label) {
                if(!empty($compat['req']['reqMessages'][$level])) {
                    $definitions[$label] = $compat['req']['reqMessages'][$level];
                }
            }
            if(!empty($definitions)) {
                $header = Yii::t('admin','Some requirements for running X2Engine at the latest version are not met on this server:');
                $messages .= $web ? CHtml::tag($h,$htmlOptions,$header) : "$header";
                $messages .= $this->formatDefinitionList($definitions,$web);
            }
        }

        if($compat['databasePermissionError']) {
            $header = $compat['databasePermissionError'];
            $messages .= $web ? CHtml::tag($h,$htmlOptions,$header) : "$header\n";
        }

        // Section: conflicting custom modules
        if(count($compat['modules']) > 0) {
            $header = Yii::t('admin','The following custom modules conflict with new modules to be added:');
            $messages .= $web ? CHtml::tag($h,$htmlOptions,$header) : $header;
            $messages .= $web ? "<ul><li>".implode('</li><li>',$compat['modules'])."</li></ul>" : "\n\t".implode("\n\t",$compat['modules']);
        }

        // Section: conflicting custom fields
        if(count($compat['conflictingFields']) > 0) {
            $header = Yii::t('admin','The following preexisting fields conflict with fields to be added:');
            $messages .= $web ? CHtml::tag($h,$htmlOptions,$header) : $header;
            $messages .= $this->formatDefinitionList($compat['conflictingFields'],$web);
        }

        // Section: files to be updated that have been customized
        if(count($compat['customFiles']) > 0) {
            $header = Yii::t('admin','Note that the following files, of which there are local custom derivatives, will be changed:');
            $messages .= $web ? CHtml::tag($h,$htmlOptions,$header) : $header;
            $messages .= $web ? "<ul><li>".implode('</li><li>',$compat['customFiles'])."</li></ul>" : "\n\t".implode("\n\t",$compat['customFiles']);
        }
        
        return $messages;
    }

    /**
     * Checks whether all dependencies of the updater exist on the server, and
     * downloads any that don't.
     */
    public function requireDependencies(){
        // Check all dependencies:
        $dependencies = $this->updaterFiles;
        // Add web updater actions to the files to be checked
        $webUpdaterActions = $this->getWebUpdaterActions(false);
        foreach($webUpdaterActions as $name => $properties)
            $dependencies[] = self::classAliasPath($properties['class']);
        $actionsDir = Yii::app()->basePath.'/components/webupdater/';
        $utilDir = Yii::app()->basePath.'/components/util/';
        $refresh = !is_dir($actionsDir) || !is_dir($utilDir); // We're downloading/saving new files
        foreach($dependencies as $relPath){
            $absPath = Yii::app()->basePath."/$relPath";
            if(!file_exists($absPath)){
                $refresh = true;
                $this->downloadSourceFile("protected/$relPath");
            }
        }
        // Copy files into the live installation:
        if($refresh)
            $this->applyFiles(self::TMP_DIR);
    }

    /**
     * Removes everything in the assets folder.
     */
    public function resetAssets(){
        $assetsDir = realpath($this->webRoot.DIRECTORY_SEPARATOR.'assets');
        if(!(bool) $assetsDir)
            throw new CException(Yii::t('admin', 'Assets folder does not exist.'));
        $assets = array();
        foreach(scandir($assetsDir) as $n) {
            if (!in_array($n, array('..', '.'))) {
                    $assets[] = $n;
            }
        }
        foreach($assets as $crcDir)
            FileUtil::rrmdir($assetsDir.DIRECTORY_SEPARATOR.$crcDir);
    }

    /**
     * Uses a database dump to reinstate the database backup.
     * @return boolean
     * @throws Exception 
     */
    public function restoreDatabaseBackup(){
        try{
            $this->checkIf('canSpawnChildren');
        }catch(Exception $e){
            throw new CException(Yii::t('admin', 'Cannot restore database. {reason}', array('{reason}' => $e->getMessage())));
        }
        $bakFile = $this->dbBackupPath;
        $logFile = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::ERRFILE));
        $errFile = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', self::LOGFILE));
        $this->checkIfDatabaseBackupExists($bakFile);
        $descriptor = array(
            0 => array('file', $bakFile, 'r'),
            1 => array('file', $logFile, 'a'),
            2 => array('file', $errFile, 'a'),
        );
        // Restore the backup!
        if((bool) $this->dbCommand){
            // A backup copy should exist at this point in the execution,
            // so it should be safe to call the dreaded dropAllTables method:
            $this->dropAllTables();
            $backup = proc_open($this->dbCommand, $descriptor, $pipes, $this->webRoot);
            $ret = proc_close($backup);
            if($ret == -1)
                throw new CException(Yii::t('admin', "Database restore process did not exit cleanly. See the files {err} and {res} for output details.", array('{err}' => "protected/data/$errFile", '{res}' => "protected/data/$logFile")));
            else{
                return True;
            }
        }
    }

    /**
     * Runs a list of migration scripts.
     * 
     * @param type $scripts
     * @param type $ran List of database changes and other scripts that have
     *  already been run
     */
    public function runMigrationScripts($scripts, $ran, $backup){
        $that = $this;
        $script = '';
        $scriptExc = function($e) use(&$ran, &$script, $that, $backup){
                    $that->handleSqlFailure ($script, $ran, $e->getMessage(), $backup, false);
                };
        $scriptErr = function($errno, $errstr, $errfile, $errline, $errcontext) use(&$ran, &$script, $that, $backup) {
            if (error_reporting () === 0) { // handle case of '@' error suppression
                return false;
            }
                    $unrecoverable = array(
                        E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING
                    );
                    if (!in_array($errno, $unrecoverable)) {
                        $that->handleSqlFailure ($script, $ran,
                            "$errstr [$errno] : $errfile L$errline;", $backup, false);
                    }
                };
        set_error_handler($scriptErr);
        set_exception_handler($scriptExc);
        sort($scripts);
        // add in case this is a version before introduction of this constant
        defined('YII_UNIT_TESTING') or define('YII_UNIT_TESTING',false);
        foreach($scripts as $script){
            $this->output(Yii::t('admin', 'Running migration script: {script}', array('{script}' => $script)));
            if (YII_UNIT_TESTING) {
                // To allow the same migration script to be executed twice in testing
                require($this->sourceDir.DIRECTORY_SEPARATOR.FileUtil::rpath($script));
            } else {
                require_once($this->sourceDir.DIRECTORY_SEPARATOR.FileUtil::rpath($script));
            }
            $ran[] = Yii::t('admin', 'migration script {file}', array('{file}' => $script));
        }
        restore_exception_handler();
        restore_error_handler();
        return $ran;
    }

    /**
     * Set the checksum contents to a specific value. Resets _checksumsContent;
     * it no longer is applicable.
     * 
     * @param string $value
     */
    public function setChecksums($value) {
        $this->_checksums = $value;
        $this->_checksumsContent = null;
    }

    public function setChecksumsContent($value) {
        $this->_checksumsContent = $value;
    }

    /**
     * Magic setter that changes the file permissions of sensitive files in
     * protected/config
     * @param type $value
     */
    public function setConfigPermissions($value){
        $mode = is_int($value) ? octdec($value) : octdec((int) "100$value");
        foreach(array('encryption.key', 'encryption.iv') as $file){
            $path = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,"config",$file));
            if(file_exists($path))
                chmod($path, $mode);
        }
    }

    public function setEdition($value) {
        $this->_edition = $value;
    }
    
    /**
     * Sets the update data to a specific value
     * @param array $value
     */
    public function setManifest(array $value) {
        $this->_manifest = $value;
    }

    /**
     * Magic setter for {@link noHalt}. Kept here so that console applications
     * can use it to stop more gracefully.
     * @param type $value
     */
    public function setNoHalt($value){
        self::$_noHalt = $value;
    }

    public function setScenario($value) {
        // Check for valid scenario:
        if(!in_array($value, array('update', 'upgrade'))) {
            throw new CException(Yii::t('admin','Invalid scenario: "{scenario}"',array('{scenario}'=>$this->_scenario)),self::ERR_SCENARIO);
        }
        $this->_scenario = $value;
    }

    /**
     * Sets the unique ID for the installation.
     */
    public function setUniqueId($value) {
        $this->_uniqueId = $value;
        $this->settings->unique_id = $value;
    }

    public function setVersion($value) {
        $this->_version = $value;
    }

    /**
     * Exits, returning SQL error messages
     *
     * @param type $sqlRun
     * @param type $errorMessage
     */
    public function sqlError($sqlFail, $sqlRun = array(), $errorMessage = null, $throw = true){
        if(!$this->isConsole)
            $errorMessage = CHtml::encode($errorMessage);
        $message = Yii::t('admin', 'A database change failed to apply: {sql}.', array('{sql}' => $sqlFail)).' ';
        if(count($sqlRun)){
            $message .= Yii::t('admin', '{n} changes were applied prior to this failure:', array('{n}' => count($sqlRun)));

            $sqlList = '';
            foreach($sqlRun as $sqlStatemt)
                $sqlList .= ($this->isConsole ? "\n$sqlStatemt" : '<li>'.CHtml::encode($sqlStatemt).'</li>');
            $message .= $this->isConsole ? $sqlList : "<ol>$sqlList</ol>";
            $message .= "\n".Yii::t('admin', "Please save the above list.")." \n\n";
        }
        if($errorMessage !== null){
            $message .= Yii::t('admin', "The error message given was:")." $errorMessage";
        }

        $message .= "\n\n".Yii::t('admin', "Update failed.");
        if(!$this->isConsole)
            $message = str_replace("\n", "<br />", $message);
        if($throw) {
            throw new CException($message,self::ERR_DATABASE);
        } else {
            $this->respond($message,1,1);
        }

    }

    public function testDatabasePermissions(){
        $missingPerms = array();
        $con = Yii::app()->db->pdoInstance;
        // Test creating a table:
        try{
            $con->exec("CREATE TABLE IF NOT EXISTS `x2_test_table` (
			    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			    `a` varchar(10) NOT NULL,
			    PRIMARY KEY (`id`))");
        }catch(PDOException $e){
            $missingPerms[] = 'create';
        }

        // Test inserting data:
        try{
            $con->exec("INSERT INTO `x2_test_table` (`id`,`a`) VALUES (1,'a')");
        }catch(PDOException $e){
            $missingPerms[] = 'insert';
        }

        // Test deleting data:
        try{
            $con->exec("DELETE FROM `x2_test_table`");
        }catch(PDOException $e){
            $missingPerms[] = 'delete';
        }

        // Test altering tables
        try{
            $con->exec("ALTER TABLE `x2_test_table` ADD COLUMN `b` varchar(10) NULL;");
        }catch(PDOException $e){
            $missingPerms[] = 'alter';
        }

        // Test removing the table:
        try{
            $con->exec("DROP TABLE `x2_test_table`");
        }catch(PDOException $e){
            $missingPerms[] = 'drop';
        }

        if(empty($missingPerms)){
            return false;
        }else{
            return Yii::t('admin', 'Database user {u} does not have adequate permisions on database {db} to perform updates; it does not have the following permissions: {perms}', array(
                        '{u}' => $this->dbParams['dbuser'],
                        '{db}' => $this->dbParams['dbname'],
                        '{perms}' => implode(',', array_map(function($m){
                                            return Yii::t('app', $m);
                                        }, $missingPerms))
                    ));
        }
    }

    /**
     * Unzips the package.
     * @throws Exception
     */
    public function unpack() {
        $package = $this->updatePackage;
        if(!file_exists($package))
            throw new Exception(Yii::t('admin','No update package could be found.'),self::ERR_NOUPDATE);
        if(file_exists($this->updateDir))
            throw new Exception(Yii::t('admin','Could not extract package; destination path {path} already exists.',array('{path}'=>$this->updateDir)),self::ERR_ISLOCKED);
        mkdir($this->updateDir);
        $zip = new ZipArchive;
        $zip->open($package);
        $zip->extractTo($this->updateDir);
        // Block direct web access to the extracted folder:
        if(file_exists($htaccess = Yii::app()->basePath.DIRECTORY_SEPARATOR.'.htaccess'))
            copy($htaccess,$this->updateDir.DIRECTORY_SEPARATOR.'.htaccess');
    }

    /**
     * In which the updater downloads a new version of itself.
     * 
     * @param type $updaterCheck New version of the update utility
     * @return array
     */
    public function updateUpdater($updaterCheck){
        
        if(version_compare($this->configVars['updaterVersion'], $updaterCheck) >= 0)
            return array();

        $updaterFiles = $this->updaterFiles;

        // Retrieve the update package contents' files' digests:
        $md5sums_content = FileUtil::getContents($this->updateServer.'/'.$this->getUpdateDataRoute($this->configVars['updaterVersion']).'/contents.md5');
        // If there's an error on the server end the response will be a JSON
        $tryJson = json_decode($md5sums_content,1);
        if(!(bool) $md5sums_content) {
            $admin = CActiveRecord::model('Admin')->findByPk(1);
            if ($this->scenario === 'upgrade' && isset($admin) && empty($admin->unique_key)) {
                $updaterSettingsLink = CHtml::link(Yii::t('admin', 'Updater Settings page'), array('admin/updaterSettings'));
                throw new CException(Yii::t('admin','You must first set a product key on the '.$updaterSettingsLink));
            } else {
                throw new CException(Yii::t('admin','Unknown update server error.'),self::ERR_UPSERVER);
            }
        } else if(is_array($tryJson)) {
            // License key error
            if(isset($tryJson['errors'])) {
                throw new CException($tryJson['errors']);
            } else {
                throw new CException(Yii::t('admin','Unknown update server error.').' '.$md5sums_content);
            }
        }
        preg_match_all(':^(?<md5sum>[a-f0-9]{32})\s+source/protected/(?<filename>\S.*)$:m',$md5sums_content,$md5s);
        $md5sums = array();
        for($i=0;$i<count($md5s[0]);$i++) {
            $md5sums[$md5s['md5sum'][$i]] = $md5s['filename'][$i];
        }
        // These are the files that need to be downloaded -- only those which have changed:
        $updaterFiles = array_intersect($md5sums,$updaterFiles);

        // Try to retrieve the files:
        $failed2Retrieve = array();
        foreach($updaterFiles as $md5 => $file){
            $pass = 0;
            $tries = 0;
            $downloadedFile = FileUtil::relpath(implode(DIRECTORY_SEPARATOR, array($this->webRoot,self::TMP_DIR,'protected',FileUtil::rpath($file))), $this->thisPath.DIRECTORY_SEPARATOR);
            while(!$pass && $tries < 2){
                $remoteFile = $this->updateServer.'/'.$this->sourceFileRoute."/protected/$file";
                try{
                    $this->downloadSourceFile("protected/$file");
                }catch(Exception $e){
                    break;
                }
                // Only call it done if it's intact and ready for use:
                $pass = md5_file($downloadedFile) == $md5;
                $tries++;
            }
            if(!$pass)
                $failed2Retrieve[] = "protected/$file";
        }

        $failedDownload = (bool) count($failed2Retrieve);
        // Copy the files into the live install
        if(!$failedDownload && (bool) count($updaterFiles)) {
            $this->applyFiles(self::TMP_DIR);
            // Remove the temporary directory:
            FileUtil::rrmdir($this->webRoot.DIRECTORY_SEPARATOR.self::TMP_DIR);
        } else {
            $errorResponse = json_decode($md5sums_content,1);
            if(isset($errorResponse['errors'])) {
                throw new CException($errorResponse['errors']);
            }
        }

        // Write the new updater version into the configuration; else
        // the app will get stuck in a redirect loop
        if(!$failedDownload) {
            $this->regenerateConfig(Null, $updaterCheck, Null);
        }
        return $failed2Retrieve;
    }

}

?>
