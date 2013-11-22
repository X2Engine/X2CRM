<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

Yii::import('application.components.util.*');

/**
 * X2CRM command line updater.
 * 
 * @package X2CRM.commands
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdateCommand extends CConsoleCommand {

    public function beforeAction($action, $params){
        $this->attachBehaviors(array(
            'UpdaterBehavior' => array(
                'class' => 'application.components.UpdaterBehavior',
                'isConsole' => true,
                'scenario' => 'update'
            )
        ));
        $this->requireDependencies();
        set_exception_handler('ResponseBehavior::respondWithException');
        set_error_handler('ResponseBehavior::respondWithError');
        return parent::beforeAction($action, $params);
    }

    public function actionIndex(){
        echo $this->help;
    }

    /**
     * Update the application.
     * @param int $force "force" parameter sent to {@link runOperation}
     * @param int $backup "backup" parameter sent to {@link runOperation}
     */
    public function actionApp($force = 0,$backup = 1, $lock=0) {
        // Check updater version, update updater itself, etc.
        $this->runOperation('update',(bool) $force, (bool) $backup, (bool) $lock);
        return 0;
    }

    /**
     * Performs registration and upgrades the application to a different edition.
     *
     * @param type $key Product key
     * @param type $firstName First name
     * @param type $lastName Last name
     * @param type $email Email address
     * @param bool $force Same as the $force argument of {@link actionApp()}
     */
    public function actionUpgrade($key,$firstName,$lastName,$email,$force=0,$backup=1) {
        $this->uniqueId = $key;
        // Check for curl:
        if(!$this->requirements['extensions']['curl'])
            $this->output(Yii::t('admin','Cannot proceed; cURL extension is required for registration.'),1,1);
        // Let's see if we're clear to proceed first:
        $ch = curl_init($this->updateServer.'/installs/registry/register');
        curl_setopt_array($ch, array(
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => array(
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'unique_id' => $uid
            ),
        ));
        $cr = json_curl_exec($ch);


        // Now proceed:
        $this->runOperation('upgrade',(bool) $force, (bool) $backup);
    }

    /**
     * Runs the actual update/upgrade.
     * 
     * @param string $scenario The scenario (update or upgrade)
     * @param bool $force False to halt on encountering any
     *  compatibility issues; true to continue through issues
     * @param bool $backup If enabled: create database backup before running
     *  operations, and restore to the backup if operations fail.
     */
    public function runOperation($scenario,$force=false,$backup=true,$lock=false) {
        $this->scenario = $scenario;
        $unpacked = $this->checkIf('packageExists',false);
        if($this->checkIf('packageApplies',false)) {
            // All the data is here and ready to go
            
        } else if($unpacked) {
            // A package is present but cannot be used.
            // 
            // Re-invoke the check method to throw the necessary exception, so
            // that its output can be captured/displayed/logged.
            $this->checkIf('packageApplies');
        } else {
            // No existing package waiting is present.
            // 
            // Prepare for update from square one by first doing an updater
            // version check:
            $this->runUpdateUpdater();
            // Check version:
            $latestVersion = $this->checkUpdates(true);
            if(version_compare($this->configVars['version'], $latestVersion) >= 0) {
                if($scenario != 'upgrade') {
                    $this->output(Yii::t('admin', 'X2CRM is at the latest version!'));
                    Yii::app()->end();
                }
            } else if($scenario == 'upgrade') {
                $this->output(Yii::t('admin',"Before upgrading, you must update to the latest version ({latestver}). ",array('{latestVer}'=>$latestVersion)),1,1);
            }
            $data = $this->getUpdateData();
            if(array_key_exists('errors', $data)){
                // The update server doesn't like us.
                $this->output($data['errors'], 1,1);
            }
            $this->manifest = $data;
        }

        // Check compatibility status:
        $this->output($this->renderCompatibilityMessages());
        if(!$this->compatibilityStatus['allClear'] && !$force) {
            Yii::app()->end();
        }

        // Download and unpack the package:
        if(!$unpacked) {
            $this->downloadPackage();
            $this->unpack();
            $this->checkIf('packageApplies');
            if(!((bool) $this->files) || $this->filesStatus[UpdaterBehavior::FILE_CORRUPT] > 0 || $this->filesStatus[UpdaterBehavior::FILE_MISSING] > 0) {
                $this->output(Yii::t('admin','Could not apply package. {n_mis} files are missing, {n_cor} are corrupt', array(
                            '{n_mis}' => $this->filesStatus[UpdaterBehavior::FILE_MISSING],
                            '{n_cor}' => $this->filesStatus[UpdaterBehavior::FILE_CORRUPT]
                        )), 1, 1);
            }
        }

        // Lock (if specified)
        if($lock) {
            $this->output(Yii::t('admin','Locking the app to prevent data entry during update.'));
            Yii::app()->locked = time();
        }

        try{
            // Backup
            if($backup)
                $this->makeDatabaseBackup();

            // Run
            $this->enactChanges($backup);
            
        }catch(Exception $e){

            if($lock){
                $this->output(Yii::t('admin', 'Unlocking the app.'));
                Yii::app()->locked = time();
            }
            throw $e;
        }

        if($lock) {
            $this->output(Yii::t('admin','Unlocking the app.'));
            Yii::app()->locked = time();
        }
        $this->output(Yii::t('admin','All done.'));
    }


    /**
     *
     * @return int 1 to indicate that a self-update was performed; 0 to indicate
     *  that the updater utility is already the latest version.
     */
    public function runUpdateUpdater() {
        $config = $this->configVars;
        extract($config);
        $status = 0;
        $latestUpdaterVersion = $this->getLatestUpdaterVersion();
        if($latestUpdaterVersion){
            if(version_compare($updaterVersion,$latestUpdaterVersion) < 0 && $autoRefresh){
                $classes = $this->updateUpdater($latestUpdaterVersion);
                if(empty($classes)){
                    $this->output(Yii::t('admin', 'The updater is now up-to-date and compliant with the updates server.'));
                } else {
                    $this->output(Yii::t('admin', 'One or more dependencies of AdminController are missing and could not be automatically retrieved. They are {classes}', array('{classes}' => implode(', ', $classes))),1,1);
                }
                Yii::app()->end();
            } else {
                $this->output(Yii::t('admin','The updater is up-to-date and safe to use.'));
                return;
            }
        }else{
            if(!$this->requirements['environment']['updates_connection']) {
                $this->output(Yii::t('admin','Could not connect to the updates server, or an error occurred on the updates server.').' '.(
                        $this->requirements['extensions']['curl'] || $this->requirements['environment']['allow_url_fopen']
                        ? ''
                        : Yii::t('admin','Note, this system does not permit outbound HTTP requests via PHP.')
                        ),1,1);
            }
        }
    }

}

?>
