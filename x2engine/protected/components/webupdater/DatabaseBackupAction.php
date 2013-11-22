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

Yii::import('application.components.webupdater.*');

/**
 * Back up the database and existing files to be deleted or replaced in an
 * update or upgrade.
 * 
 * @package X2CRM.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class DatabaseBackupAction extends WebUpdaterAction {

    public function run($download = false){
        set_error_handler('ResponseBehavior::respondWithError');
        set_exception_handler('ResponseBehavior::respondWithException');
        if(!$download){
            if($this->controller->makeDatabaseBackup())
                self::respond(Yii::t('admin', 'Backup saved to').' protected/data/'.UpdaterBehavior::BAKFILE);
        } else {
            $backup = realpath($this->dbBackupPath);
            if((bool) $backup){
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=".UpdaterBehavior::BAKFILE);
                header("Content-type: application/octet");
                header("Content-Transfer-Encoding: binary");
                readfile($backup);
            }else{
                if(!empty($_SERVER['HTTP_REFERER']))
                    header("Location: {$_SERVER['HTTP_REFERER']}");
                else
                    $this->controller->redirect('index');
            }
        }
    }

}

?>
