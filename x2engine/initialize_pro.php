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



/**
 * @file initialize_pro.php Additional actions to take in Professional Edition
 * 
 * This file, even if empty of any PHP to execute, should be left in place; the 
 * installer uses its existence or lack thereof to determine the edition.
 */

require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__,'protected','components','util','CommandUtil.php')));
require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__,'protected','components','util','CrontabUtil.php')));


/**
 * Adds new call to X2Engine scheduled task runner to the cron table
 */
function editCrontab() {
    global $config,$silent;
    if((isset($config['startCron']) ? !$config['startCron'] : true) || empty($config['cron'])) // User doesn't want cron
        return false;
    // Test the waters one last time:
    $u = new CommandUtil();
    try{
        $crontab = $u->run('crontab -l')->output();
    }catch(Exception $e){
        return false;
    }
    // Generate the cron array:
    $ca = $silent ? CrontabUtil::parseCrontabLine($config['cron']) :  CrontabUtil::processForm($config['cron']['default']);
    $ca['tag'] = 'default';
    $ca = array('default'=>$ca);
    // Merge with $ca the second arg so that it overwrites preexisting jobs with the unique tag "default" (in the case of reinstallation)
    $ca = array_merge(CrontabUtil::crontabToArray($crontab),$ca);
    CrontabUtil::arrayToCrontab($crontab,$ca);
    $ctFile = implode(DIRECTORY_SEPARATOR,array(__DIR__,'.crontab.tmp'));
    file_put_contents($ctFile,$crontab);
    $u->run("crontab $ctFile")->complete();
    unlink($ctFile);
}

editCrontab();

?>
