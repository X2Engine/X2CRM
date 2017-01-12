<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

////////////////////
// Global Objects //
////////////////////
// Run the silent installer with default values?
$silent = php_sapi_name() === 'cli' || isset($_GET['silent']) || (isset($argv) && in_array('silent', $argv));
// Whether a response is already in progress
$responding = false;
// Configuration values passed to PDOStatement::execute for parameter binding
$dbConfig = array();
// All configuration values, including statistics
$config = array();
// Values from the form sent back to the form, url-encoded, in GET parameters
// (so it works w/o javascript, in the case of the user visiting initialize.php
// before the installation is complete)
$userData = array();
// Configuration/info variables from everywhere:
$confKeys = array(
    'adminUsername',
    'adminEmail',
    'adminPass',
    'adminPass2',
    'apiKey',
    'app',
    'baseUrl',
    'buildDate',
    'currency',
    'currency2',
    'dbHost',
    'dbName',
    'dbUser',
    'dbPass',
    'dummy_data',
    'db_type',
    'edition',
    'GD_support',
    'language',
    'php_version',
    'receiveUpdates',
    'test_db',
    'test_url',
    'time',
    'timezone',
    'unique_id',
    'updaterVersion',
    'user_agent',
    'visibleModules',
    'x2_version',
    'type',
    'startCron',
    'cronSched',
    'cron'
);
// Values that are safe to return in the configuration (in $_GET) in the case
// that the user visits initialize.php before installing or is not using JavaScript
$returnKeys = array(
    'dbHost',
    'dbName',
    'dbUser',
    'app',
    'currency',
    'currency2',
    'language',
    'adminUsername',
    'adminEmail',
    'dummy_data',
    'receiveUpdates',
    'timezone',
    'unique_id',
);
// Configuration keys to be used in $dbConfig. Must coincide with those in $config,
// and be present in protected/data/config.sql
$dbKeys = array(
    'adminEmail',
    'adminUsername',
    'adminPass',
    'apiKey',
    'currency',
    'time',
    'unique_id',
    'edition',
    'bulkEmail',
    'language',
    'timezone',
    'visibleModules',
    'app',
    'baseUrl',
    'baseUri'
);
// Values gathered for statistical/anonymous survey purposes:
$sendArgs = array(
    'language',
    'currency',
    'x2_version',
    'dummy_data',
    'php_version',
    'db_type',
    'GD_support',
    'user_agent',
    'timezone',
    'unique_id',
    'type'
);
// Old or inconsistent variable names in installConfig.php and the config file(s)
$confMap = array(
    'host' => 'dbHost',
    'db' => 'dbName',
    'dbname' => 'dbName',
    'email' => 'adminEmail',
    'user' => 'dbUser',
    'pass' => 'dbPass',
    'adminPassword' => 'adminPass',
    'x2Version' => 'x2_version',
    'lang' => 'language',
    'dummyData' => 'dummy_data',
    'appName' => 'app',
    'version' => 'x2_version',
    'installType' => 'type',
);

require_once(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected', 'components', 'util', 'ResponseUtil.php')));

// AJAX-driven installation needs "OK" status to work properly, even if there
// are errors to display:
ResponseUtil::$errorCode = 200;

// Response object for AJAX-driven installation
$response = new ResponseUtil;

/**
 * Convenience wrapper for ResponseUtil::respond
 *
 * @param string $message
 */
function RIP($message) {
    ResponseUtil::respond($message, 1);
}

set_error_handler('ResponseUtil::respondWithError');
set_exception_handler('ResponseUtil::respondWithException');
register_shutdown_function('ResponseUtil::respondFatalErrorMessage');
ini_set('display_errors', 0);
// Test the connection and exit:
if (isset($_POST['testDb'])) {
    // First open the connection
    $con = null;
    try {
        $con = new PDO("mysql:host={$_POST['dbHost']};dbname={$_POST['dbName']}", $_POST['dbUser'], $_POST['dbPass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    } catch (PDOException $e) {
        RIP(installer_t('Could not connect to host or select database.'));
    }
    $permsError = 'User {u} does not have adequate permisions on database {db}';

    // Now test creating a table:
    try {
        $con->exec("CREATE TABLE IF NOT EXISTS `x2_test_table` (
			    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			    `a` varchar(10) NOT NULL,
			    PRIMARY KEY (`id`))");
    } catch (PDOException $e) {
        RIP(installer_tr($permsError, array('{db}' => $_POST['dbName'], '{u}' => $_POST['dbUser'])) . '; ' . installer_t('cannot create tables'));
    }

    // Test inserting data:
    try {
        $con->exec("INSERT INTO `x2_test_table` (`id`,`a`) VALUES (1,'a')");
    } catch (PDOException $e) {
        RIP(installer_tr($permsError, array('{db}' => $_POST['dbName'], '{u}' => $_POST['dbUser'])) . '; ' . installer_t('cannot insert data'));
    }

    // Test deleting data:
    try {
        $con->exec("DELETE FROM `x2_test_table`");
    } catch (PDOException $e) {
        RIP(installer_tr($permsError, array('{db}' => $_POST['dbName'], '{u}' => $_POST['dbUser'])) . '; ' . installer_t('cannot delete data'));
    }

    // Test altering tables
    try {
        $con->exec("ALTER TABLE `x2_test_table` ADD COLUMN `b` varchar(10) NULL;");
    } catch (PDOException $e) {
        RIP(installer_tr($permsError, array('{db}' => $_POST['dbName'], '{u}' => $_POST['dbUser'])) . '; ' . installer_t('cannot alter tables'));
    }


    // Test removing the table:
    try {
        $con->exec("DROP TABLE `x2_test_table`");
    } catch (PDOException $e) {
        RIP(installer_tr($permsError, array('{db}' => $_POST['dbName'], '{u}' => $_POST['dbUser'])) . '; ' . installer_t('cannot drop tables'));
    }

    // Now test creating a table, with the InnoDB storage engine (required!):
    try {
        $con->exec("CREATE TABLE IF NOT EXISTS `x2_test_table` (
			    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			    `a` varchar(10) NOT NULL,
			    PRIMARY KEY (`id`)) ENGINE=INNODB");
    } catch (PDOException $e) {
        RIP(installer_tr($permsError, array('{db}' => $_POST['dbName'], '{u}' => $_POST['dbUser'])) . '; ' . installer_t('the InnoDB storage engine is not available'));
    }
    $con->exec("DROP TABLE `x2_test_table`");

    ResponseUtil::respond(installer_t("Connection successful!"));
} elseif (isset($_POST['testCron'])) {
    require_once implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected','components','util','CommandUtil.php'));
    $command = new CommandUtil();
    try {
        $command->loadCrontab();
        ResponseUtil::respond(installer_t('Cron can be used on this system'));
    } catch (Exception $e) {
        if ($e->getCode() == 1)
            RIP(installer_t('The "crontab" command does not exist on this system, so there is no way to set up cron jobs.'));
        else
            RIP(installer_t('There is a cron service available on this system, but PHP is running as a system user that does not have permission to use it.'));
    }
}

/////////////////////////////////
// Declare Installer Functions //
/////////////////////////////////

/**
 * Collect base configuration from the default pre-install app config file
 *
 * @global array $config
 * @global array $confMap
 */
function baseConfig() {
    global $config, $confKeys, $confMap;
    $confFile = realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected','config','X2Config.php')));
    if ($confFile) {
        include($confFile);
        foreach ($confMap as $name2 => $name1) {
            if (isset(${$name2})) {
                $config[$name1] = ${$name2};
            }
        }
        foreach ($confKeys as $name) {
            if (isset(${$name})) {
                $config[$name] = ${$name};
            }
        }
    } else {
        RIP('Could not find essential configuration file at protected/config/X2Config.php');
    }
}

/**
 * Collect variables into the main configuration (command-line installation)
 *
 * @global array $config
 * @global array $confMap
 */
function installConfig() {
    global $config, $confMap, $confKeys;
    if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'installConfig.php')) {
        require(__DIR__.DIRECTORY_SEPARATOR.'installConfig.php');
    } else
        RIP('Error: Installer config file not found.');

    // Collect configuration values from the configuration file(s)
    foreach ($confKeys as $name)
        if (isset(${$name}))
            $config[$name] = ${$name};
    // If they're set in installConfig.php, override:
    foreach ($confMap as $name2 => $name1)
        if (isset(${$name2}))
            $config[$name1] = ${$name2};
}

/*
 * Translation function
 */

function installer_t($str) { // translates by looking up string in install.php language file
    global $installMessages;
    if (isset($installMessages[$str]) && $installMessages[$str] != '')  // if the chosen language is available
        return $installMessages[$str];   // and the message is in there, use it
    return $str;
}

/**
 * Translation function wrapper (for using parameters)
 */
function installer_tr($str, $params) {
    return strtr(installer_t($str), $params);
}

/**
 * Redirect to the installer with errors.
 */
function outputErrors() {
    global $response, $userData, $silent;
    if (!$silent) {
        if (!isset($_GET['stage'])) {
            if (isset($response['errors'])) {
                foreach ($response['errors'] as &$error)
                    $error = urlencode($error);  // url encode errors

                if (count($response['errors']) > 0) {
                    $errorData = implode('&errors%5B%5D=', $response['errors']);
                    $url = preg_replace('/initialize/', 'install', $_SERVER['REQUEST_URI']);
                    header("Location: $url?errors%5B%5D=" . $errorData . '&' . http_build_query($userData));
                    die();
                }
            }
        }
    } else {
        if (!empty($response['errors'])) {
            echo installer_t("One or more configuration variables have been invalidly set:") . "\n";
            foreach ($response['errors'] as $name => $error) {
                echo "\t$name:\t$error\n";
            }
            die();
        }
    }
}

/**
 * Add an error message to the response array.
 *
 * @global type $response
 * @param type $message
 */
function addError($message) {
    global $response;
    if (!array_key_exists('errors', $response)) {
        $response['errors'] = array();
    }
    $response['errors'][] = $message;
}

$sqlError = '';

function addSqlError($message) {
    global $sqlError;
    if (empty($sqlError))
        $sqlError = $message;
}

/**
 * Backwards-compatible wrapper function for adding validation errors.
 *
 * @param type $attr
 * @param type $error
 */
function addValidationError($attr, $error) {
    global $response, $silent;
    $errors = array();
    if (isset($response['errors'])) {
        // We have to extract the damn thing and then set it after appending
        // error messages rather than simply setting elements of the nested
        // array because PHP.
        //
        // http://stackoverflow.com/a/2881533/1325798
        $errors = $response['errors'];
    }
    if (isset($_GET['stage']) || $silent) {
        $errors[$attr] = installer_t($error);
    } else {
        // Slip the validation error into the GET parameters as [attribute]--[errormessage]
        $errors[] = "$attr--$error";
    }
    $response['errors'] = $errors;
}

/**
 * Installs a named module
 *
 * @global PDO $dbo
 * @param type $module
 */
function installModule($module, $respond = True) {
    if ($module === 'x2Activity' || $module === 'charts')
        return;
    global $dbo;
    $moduleName = installer_t($module);
    $regPath = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected','modules',$module,'register.php'));
    $regFile = realpath($regPath);
    if ($regFile) {
        $install = require_once($regFile);
        foreach ($install['install'] as $sql) {
            // Install a module.
            // For each element in the register script's "install" array, if it's a
            // string, treat it as a path to an SQL script. Otherwise, if an array,
            // treat as a list of SQL statements.
            $sqlComm = $sql;
            if (is_string($sql)) {
                if (file_exists($sql)) {
                    $sqlComm = explode('/*&*/', file_get_contents($sql));
                } else {
                    RIP("Error installing module \"$module\"; file does not exist: $sql");
                }
            }
            foreach ($sqlComm as $sqlLine) {
                try {
                    $statement = $dbo->prepare($sqlLine);
                    $statement->execute() or RIP(installer_tr('Error installing module "{module}". SQL statement "{sql}" failed;', array('{sql}' => substr(trim($sqlLine), 0, 50) . (strlen(trim($sqlLine)) > 50 ? '...' : ''), '{module}' => $moduleName)) . implode(',', $statement->errorInfo()));
                } catch (PDOException $e) {
                    RIP(installer_tr('Could not install module "{module}"; ', array('{module}' => $moduleName)) . $e->getMessage());
                }
            }
        }
        if ($respond)
            ResponseUtil::respond(installer_tr('Module "{module}" installed.', array('{module}' => $moduleName)));
    } else {
        RIP(installer_tr('Failed to install module "{module}"; could not find configuration file at {path}.', array('{module}' => $moduleName, '{path}' => $regPath)));
    }
}

/**
 * Runs a named stage of the installation.
 *
 * @param $stage The named stage of installation.
 */
function installStage($stage) {
    global $editions, $dbConfig, $dbKeys, $dateFields, $enabledModules, $dbo,
    $config, $confMap, $response, $silent, $stageLabels, $write,
    $nonFreeTables, $editionHierarchy;

    switch ($stage) {
        case 'validate':
            if ($config['dummy_data'] == 1 && $config['adminUsername'] != 'admin')
                addValidationError('adminUsername', 'Cannot change administrator username if installing with sample data.');
            else {
                if (empty($config['adminUsername']))
                    addValidationError('adminUsername', 'Admin username cannot be blank.');
                elseif (is_int(strpos($config['adminUsername'], "'")))
                    addValidationError('adminUsername', 'Admin username cannot contain apostrophes');
                elseif (preg_match('/^\d+$/', $config['adminUsername']))
                    addValidationError('adminUsername', 'Admin username must contain at least one non-numeric character.');
                elseif (!preg_match('/^\w+$/', $config['adminUsername']))
                    addValidationError('adminUsername', 'Admin username may contain only alphanumeric characters and underscores.');
            }
            if (empty($config['adminEmail']) || !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $config['adminEmail']))
                addValidationError('adminEmail', 'Please enter a valid email address.');
            if ($config['adminPass'] == '')
                addValidationError('adminPass', 'Admin password cannot be blank.');
            if (!$silent && !isset($_POST['adminPass2']))
                addValidationError('adminPass2', 'Please confirm the admin password.');
            else if (!$silent && $config['adminPass'] != $_POST['adminPass2'])
                addValidationError('adminPass2', 'Admin passwords did not match.');
            if (!empty($response['errors'])) {
                if (!$silent) {
                    RIP(installer_t('Please correct the following errors:'));
                } else {
                    outputErrors();
                }
            }
            break;
        case 'module':
            if (isset($_GET['module'])) {
                // Install only a named module
                installModule($_GET['module']);
            } else {
                // Install all modules:
                foreach ($enabledModules as $module)
                    installModule($module, $silent);
            }
            break;
        case 'config':
            // Configure with initial data and write files
            // Generate config file content:
            $gii = 1;
            if ($gii == '1') {
                $gii = "array(\n\t'class'=>'system.gii.GiiModule',\n\t'password'=>'" . str_replace("'", "\\'", $config['adminPass']) . "', \n\t/* If the following is removed, Gii defaults to localhost only. Edit carefully to taste: */\n\t 'ipFilters'=>false,\n)";
            } else {
                $gii = "array(\n\t'class'=>'system.gii.GiiModule',\n\t'password'=>'password',\n\t/* If the following is removed, Gii defaults to localhost only. Edit carefully to taste: */\n\t 'ipFilters'=>array('127.0.0.1', '::1'),\n)";
            }
            $X2Config = "<?php\n";
            foreach (array('appName', 'email', 'host', 'user', 'pass', 'dbname', 'version') as $confKey)
                $X2Config .= "\$$confKey = " . var_export($config[$confMap[$confKey]], 1) . ";\n";
            $X2Config .= "\$buildDate = {$config['buildDate']};\n\$updaterVersion = '{$config['updaterVersion']}';\n";
            $X2Config .= (empty($config['language'])) ? '$language=null;' : "\$language='{$config['language']}';\n?>";

            // Save config values to be inserted in the database:
            $config['time'] = time();
            foreach ($dbKeys as $property)
                $dbConfig['{' . $property . '}'] = $config[$property];
            $contents = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'webConfig.php');
            $contents = preg_replace('/\$url\s*=\s*\'\'/', "\$url=" . var_export($config['baseUrl'].$config['baseUri'], 1), $contents);
            file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'webConfig.php', $contents);
            if ($config['test_db']) {
                $filename = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected', 'config', 'X2Config-test.php'));
                if (!empty($config['test_url'])) {
                    $defaultConfig = file_get_contents(implode(DIRECTORY_SEPARATOR, array(
                        __DIR__,
                        'protected',
                        'tests',
                        'WebTestConfig_example.php'
                    )));
                    $webTestConfigFile = implode(DIRECTORY_SEPARATOR, array(
                        __DIR__,
                        'protected',
                        'tests',
                        'WebTestConfig.php'
                    ));
                    $webTestUrl = rtrim($config['test_url'], '/') . '/';
                    $webTestRoot = rtrim(preg_replace('#index-test\.php/?$#', '', trim($config['test_url'])), '/') . '/';
                    $testConstants = array(
                        'TEST_BASE_URL' => var_export($webTestUrl, 1),
                        'TEST_WEBROOT_URL' => var_export($webTestRoot, 1)
                    );
                    $webTestConfig = $defaultConfig;
                    foreach ($testConstants as $name => $value) {
                        $webTestConfig = preg_replace("/^defined\('$name'\) or define\('$name'\s*,.*$/m", "defined('$name') or define('$name',$value);", $webTestConfig);
                    }
                    file_put_contents($webTestConfigFile, $webTestConfig);
                }
            } else
                $filename = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected', 'config', 'X2Config.php'));
            $handle = fopen($filename, 'w') or RIP(installer_tr('Could not create configuration file: {filename}.', array('{filename}' => $filename)));

            // Write core application configuration:
            fwrite($handle, $X2Config);
            fclose($handle);

            // Create an encryption key for credential storage:
            if (extension_loaded('openssl') && extension_loaded('mcrypt')) {
                $encryption = new EncryptUtil(
                        implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected','config','encryption.key')),
                        implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected','config','encryption.iv'))
                    );
                $encryption->saveNew();
            }

            $dbConfig['{adminPass}'] = md5($config['adminPass']);
            $dbConfig['{adminUserKey}'] = $config['adminUserKey'];
            try {
                foreach (array('', '-pro', '-pla') as $suffix) {
                    $sqlPath = implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data',"config$suffix.sql"));
                    $sqlFile = realpath($sqlPath);
                    if ($sqlFile) {
                        $sql = explode('/*&*/', strtr(file_get_contents($sqlFile), $dbConfig));
                        foreach ($sql as $sqlLine) {
                            $installConf = $dbo->prepare($sqlLine);
                            if (!$installConf->execute())
                                RIP(installer_t('Error applying initial configuration') . ': ' . implode(',', $installConf->errorInfo()));
                        }
                    } else if ($suffix == '') { // Minimum requirement
                        RIP(installer_t('Could not find database configuration script') . " $sqlPath");
                    }
                }
            } catch (PDOException $e) {
                die($e->getMessage());
            }
//			saveCrontab();
            break;
        case 'finalize':
            /**
             * Look for additional initialization files and perform final tasks
             */
            foreach ($editions as $ed) // Add editional prefixes as necessary
                if (file_exists(__DIR__.DIRECTORY_SEPARATOR."initialize_$ed.php"))
                    include(__DIR__.DIRECTORY_SEPARATOR."initialize_$ed.php");
            break;
        default:
            // Look for a named SQL file and run it:
            $stagePath = implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data',"$stage.sql"));
            if ($stage == 'dummy_data')
                $stageLabels['dummy_data'] = sprintf($stageLabels['dummy_data'], $config['dummy_data'] ? 'insert' : 'delete');
            if ((bool) ((int) $config['dummy_data']) || $stage != 'dummy_data') {
                if ($sqlFile = realpath($stagePath)) {
                    $sql = explode('/*&*/', file_get_contents($sqlFile));
                    foreach ($sql as $sqlLine) {
                        $statement = $dbo->prepare($sqlLine);
                        try {
                            if (!$statement->execute())
                                RIP(installer_tr('Could not {stage}. SQL statement "{sql}" from {file} failed', array('{stage}' => $stageLabels[$stage], '{sql}' => substr(trim($sqlLine), 0, 50) . (strlen(trim($sqlLine)) > 50 ? '...' : ''), '{file}' => $sqlFile)) . '; ' . implode(',', $statement->errorInfo()));
                        } catch (PDOException $e) {
                            RIP(installer_tr("Could not {stage}", array('{stage}' => $stageLabels[$stage])) . '; ' . $e->getMessage());
                        }
                    }
                    // Hunt for init SQL files associated with other editions:
                    foreach ($editions as $ed) {
                        if ($sqlFile = realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data',"$stage-$ed.sql")))) {
                            $sql = explode('/*&*/', file_get_contents($sqlFile));
                            foreach ($sql as $sqlLine) {
                                $statement = $dbo->prepare($sqlLine);
                                try {
                                    if (!$statement->execute())
                                        RIP(installer_tr('Could not {stage}. SQL statement "{sql}" from {file} failed', array('{stage}' => $stageLabels[$stage], '{sql}' => substr(trim($sqlLine), 0, 50) . (strlen($sqlLine) > 50 ? '...' : ''), '{file}' => $sqlFile)) . '; ' . implode(',', $statement->errorInfo()));
                                } catch (PDOException $e) {
                                    RIP(installer_tr("Could not {stage}", array('{stage}' => $stageLabels[$stage])) . '; ' . $e->getMessage());
                                }
                            }
                        }
                    }

                    if ($stage == 'dummy_data') {
                        // Need to update the timestamp fields on all the sample data that has been inserted.
                        $dateGen = @file_get_contents(realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','dummy_data_date')))) or RIP("Sample data generation date not set.");
                        $time = time();
                        $time2 = $time * 2;
                        $timeDiff = $time - (int) trim($dateGen);
                        foreach ($dateFields as $table => $fields) {
                            $tableEdition = 'opensource';
                            foreach ($editions as $ed) {
                                if (in_array($table, $nonFreeTables[$ed])) {
                                    $tableEdition = $ed;
                                    break;
                                }
                            }
                            if (!(bool) $editionHierarchy[$config['edition']][$tableEdition]) {
                                // Table not "contained" in the current edition
                                continue;
                            }

                            foreach ($fields as $field) {
                                try {
                                    $dbo->exec("UPDATE `$table` SET `$field`=`$field`+$timeDiff WHERE `$field` IS NOT NULL AND `$field`!=0 AND `$field`!=''");
                                } catch (Exception $e) {
                                    // Ignore it and move on; table/column doesn't exist.
                                    continue;
                                }
                            }
                            // Fix timestamps that are in the future.
                            /*
                              $ordered = array('lastUpdated','createDate');
                              if(count(array_intersect($ordered,$fields)) == count($ordered)) {
                              $affected = 0;
                              foreach($ordered as $field) {
                              $affected += $dbo->exec("UPDATE `$table` SET `$field`=$time2-`$field` WHERE `$field` > $time");
                              }
                              if($affected)
                              $dbo->exec("UPDATE `$table` set `lastUpdated`=`createDate`,`createDate`=`lastUpdated` WHERE `createDate` > `lastUpdated`");
                              }
                             */
                        }
                    }
                } else {
                    RIP(installer_t("Could not find installation stage database script") . " $stagePath");
                }
            } else {
                // This is the dummy data stage, and we need to clear out all unneeded files.
                // However, we should leave the files alone if this is a testing database reinstall.
                $stageLabels[$stage] = sprintf($stageLabels[$stage], 'remove');
                if (($paths = @require_once(realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','dummy_data_files.php'))))) && !$config['test_db']) {
                    foreach ($paths as $pathClear) {
                        if ($path = realpath($pathClear)) {
                            FileUtil::rrmdir($path, '/\.htaccess$/');
                        }
                    }
                }
            }
            break;
    }
    if (in_array($stage, array_keys($stageLabels)) && $stage != 'finalize' && !($stage == 'validate' && $silent))
        ResponseUtil::respond(installer_tr("Completed: {stage}", array('{stage}' => $stageLabels[$stage])));
}

require_once(implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','components','util','FileUtil.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','components','util','EncryptUtil.php')));


//////////////////////////////////
// Load Installer Configuration //
//////////////////////////////////
//
// Initialize config with empty values:
foreach ($confKeys as $key) {
    $config[$key] = Null;
}
// Load static app configuration files:
$staticConfig = array(
    'stageLabels' => implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','installStageLabels.php')),
    'enabledModules' => implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','enabledModules.php')),
    'dateFields' => implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','dateFields.php')),
    'nonFreeTables' => implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','nonFreeTables.php')),
    'editionHierarchy' => implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','data','editionHierarchy.php')),
);
foreach ($staticConfig as $varName => $path) {
    $realpath = realpath($path);
    if ($realpath) {
        ${$varName} = require($realpath);
    } else {
        RIP("Could not find static configuration file $path.");
    }
}
// Non-free editions to consider
$editions = array_diff(array_keys($editionHierarchy), array('opensource'));

baseConfig();

if ($silent) {
    installConfig();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect configuration values from install form
    foreach ($confKeys as $var)
        if (isset($_POST[$var]))
            $config[$var] = $_POST[$var];
    // Determine currency
    $config['currency2'] = strtoupper($config['currency2']);
    if ($config['currency'] == 'other')
        $config['currency'] = $config['currency2'];
    if (empty($config['currency']))
        $config['currency'] = 'USD';
    // Checkbox fields
    foreach (array('dummy_data', 'receiveUpdates', 'test_db') as $checkbox) {
        $config[$checkbox] = (isset($_POST[$checkbox]) && $_POST[$checkbox] == 1) ? 1 : 0;
    }
    $config['unique_id'] = isset($_POST['unique_id']) ? $_POST['unique_id'] : 'none';
    $config['baseUrl'] = (empty($_SERVER['HTTPS'])?"http://":"https://").$_SERVER['SERVER_NAME'];
}   $config['baseUri'] = is_int(strpos($_SERVER['REQUEST_URI'], 'initialize.php')) ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'initialize.php')) : $_SERVER['REQUEST_URI'];
//if(!in_array($config['type'],array('Silent','Bitnami','Testing'))) // Special installation types
//	$config['type'] = $config['test_db']==1?'Testing':($silent ? 'Silent' : 'On Premise');
$config['GD_support'] = function_exists('gd_info') ? '1' : '0';
$config['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$config['php_version'] = phpversion();
$config['db_type'] = 'MySQL';


// Determine whether we're setting up a test database
if ($config['test_db']) {
    if (!isset($config['test_url']))
        $config['test_url'] = null;
}

// Determine which modules are designated visible
if (empty($config['visibleModules'])) { // Web install. Determine based on $_POST fields
    $config['visibleModules'] = array();
    foreach ($enabledModules as $moduleName) {
        if (isset($_POST["menu_$moduleName"]))
            $config['visibleModules'][] = $moduleName;
    }
    $config['visibleModules'] = "('" . implode("','", $config['visibleModules']) . "')";
} else { // Silent install. Modules should be in a comma-delineated list.
    $config['visibleModules'] = "('" . implode("','", explode(',', $config['visibleModules'])) . "')";
}


// Deterine edition info
$config['edition'] = 'opensource';

//////////////////////////////
// Post-configuration tasks //
//////////////////////////////
// Generate API Key
$config['adminUserKey'] = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);

// Set up language & translations:
if (empty($config['language']))
    $config['language'] = 'en';
$installMessageFile = implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','messages',$config['language'],'install.php'));
$installMessages = array();
if (isset($installMessageFile) && file_exists($installMessageFile)) { // attempt to load installer messages
    $installMessages = include($installMessageFile);  // from the chosen language
    if (!is_array($installMessages))
        $installMessages = array();   // ...or return an empty array
}

// Timezone
if (empty($config['timezone']))
    $config['timezone'] = 'UTC';
date_default_timezone_set($config['timezone']);

// Email address for sending
if (!empty($config['adminEmail']))
    $config['bulkEmail'] = $config['adminEmail'];
else if (isset($_SERVER['HTTP_HOST']))
    $config['bulkEmail'] = 'contact@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
else
    $config['bulkEmail'] = 'contact@localhost';

// At this stage, all user-entered data should be avaliable. Populate response data:
foreach ($returnKeys as $key) {
    $userData[$key] = $config[$key];
}


// Translate response messages
foreach (array_keys($stageLabels) as $stage) {
    $stageLabels[$stage] = installer_t($stageLabels[$stage]);
}

// App name:
$config['app'] = addslashes($config['app']);

if (!$silent) {
    // Ad-hoc validation in the no-javascript case
    if (!isset($_GET['stage'])) {
        if (empty($config['adminEmail']) || !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $config['adminEmail']))
            addValidationError('adminEmail', 'Please enter a valid email address.');

        if (empty($config['adminPass']))
            addValidationError('adminPass', 'Admin password cannot be blank.');
        else if ($config['adminPass'] != $config['adminPass2'])
            addValidationError('adminPass2', 'Admin passwords did not match.');
    }
}

///////////////////////////////////
// Establish database connection //
///////////////////////////////////
try {
    $dbo = new PDO("mysql:host={$config['dbHost']};dbname={$config['dbName']}", $config['dbUser'], $config['dbPass']);
} catch (PDOException $e) {
    // Database connection failed. Send validation errors.
    foreach (array('dbHost' => 'Host Name', 'dbName' => 'Database Name', 'dbUser' => 'Username', 'dbPass' => 'Password') as $attr => $label) {
        if (empty($config[$attr])) {
            addValidationError($attr, installer_tr('{attr}: cannot be blank', array('{attr}' => installer_t($label))));
        } else {
            addValidationError($attr, installer_tr('{attr}: please check that it is correct', array('{attr}' => installer_t($label))));
        }
    }
    $response['errors'] = array(htmlentities($e->getMessage()));
    ResponseUtil::respond(installer_t('Database connection error'), 1);
}

//////////////////////////////
// Run Installation Task(s) //
//////////////////////////////
$complete = isset($_POST['complete']) ? $_POST['complete'] == 1 : False;

if (!$complete && !$silent)
    outputErrors();

// Install everything all at once:
if (($silent || !isset($_GET['stage'])) && !$complete) {
    // Install core schema/data, modules, and configure:
    ResponseUtil::respond("-- Installing version {$config['x2_version']} --");
    foreach (array('validate', 'core', 'RBAC', 'timezoneData', 'module', 'config', 'dummy_data', 'finalize') as $component)
        installStage($component);
} else if (isset($_GET['stage'])) {
    installStage($_GET['stage']);
}

if (!$complete || $silent) {
    if (!empty($sqlError))
        $errors[] = 'MySQL Error: ' . $sqlError;
    outputErrors();
    $installTime = time();
    file_put_contents(implode(DIRECTORY_SEPARATOR, array(realpath('protected'.DIRECTORY_SEPARATOR.'data'), 'install_timestamp')), $installTime);
    ResponseUtil::respond(installer_tr('Installation completed {time}.', array('{time}' => strftime('%D %T', $installTime))));
    if ($silent && function_exists('curl_init') && $config['type'] != 'Testing') {
        foreach ($sendArgs as $urlKey) {
            $stats[$urlKey] = $config[$urlKey];
        }
        $ch = curl_init('http://x2planet.com/installs/registry/activity?' . http_build_query($stats));
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $gif = curl_exec($ch);
    }
}

// Generate splash page
if (!$silent && $complete):
    foreach ($sendArgs as $urlKey) {
        $stats[$urlKey] = $config[$urlKey];
    }
    ?><!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
            <meta charset="UTF-8" />
            <meta name="language" content="en" />
            <title><?php echo installer_t('Installation Complete'); ?></title>
            <?php $themeURL = 'themes/x2engine'; ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
            <link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
            <link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />
            <link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/install.css" />
            <link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/ui-elements.css" />
            <link rel="icon" href="images/favicon.ico" type="image/x-icon">
                <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
                <style type="text/css">
                    body {
                        background-color:#fff;
                        padding-top:50px;
                    }
                </style>
                <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
                <script type="text/javascript" src="js/backgroundImage.js"></script>
        </head>
        <body>
        <!--<img id="bg" src="uploads/defaultBg.jpg" alt="">-->
            <div id="installer-box" style="padding-top:20px;">
                <h1><?php echo installer_t('Installation Complete!'); ?></h1>
                <div id="install-form" class="wide form">
                    <?php echo installer_tr('X2CRM has been successfully installed on your web server!  You may now log in with username "{user}" and the password you provided during the installation process.', array('{user}' => $config['adminUsername']));
                    echo "<br><br>" . installer_tr('New to X2? Check out our user reference guide {link}.', array('{link}' => '<a href="http://www.x2crm.com/reference_guide/" target="_blank">here</a>'));
                    ?><br /><br />
                    <h3><a class="x2-button" href="index<?php echo ($config['test_db'] ? '-test' : ''); ?>.php"><?php echo installer_t('Click here to log in to X2CRM'); ?></a></h3><br />
                </div>
                <a href="http://www.x2crm.com"><?php echo installer_t('For help or more information - X2CRM.com'); ?></a><br /><br />
                <div id="footer">
                    <div class="hr"></div>
                    <!--<img src="images/x2engine_big.png">-->
                    Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2crm.com">X2Engine Inc.</a><br />
                    <?php echo installer_t('All Rights Reserved.'); ?>
                        <?php if (!$config['test_db']): ?>
                        <img style="height:0;width:0" src="http://x2planet.com/installs/registry/activity?<?php echo http_build_query($stats); ?>">
    <?php endif; ?>
                </div>
            </div>
        </body>
    </html>
    <?php
endif;
// Delete install files
foreach (array(
    __DIR__.DIRECTORY_SEPARATOR.'install.php', 
    __DIR__.DIRECTORY_SEPARATOR.'installConfig.php', 
    __DIR__.DIRECTORY_SEPARATOR.'initialize_pro.php') as $file){
    if (file_exists($file)){
        unlink($file);
    }
}
// Delete self
unlink(__FILE__);
?>



