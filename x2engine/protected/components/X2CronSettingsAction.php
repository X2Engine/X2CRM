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






/**
 * Admin section settings for controlling the cron 
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2CronSettingsAction extends CAction {

    public function run(){
        $cf = new CronForm;
        $cf->jobs = array(
            'app_update' => array(
                'cmd' => Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic update app --lock=1',
                'desc' => Yii::t('admin', 'Automatic software updates cron job'),
            ),
            'default' => array(
                'cmd' => 'curl '.Yii::app()->createAbsoluteUrl('/api/x2cron').' &>/dev/null',
                'desc' => 'Run delayed or recurring tasks within X2Engine using the scheduled tasks request URL'
            ),
            'default_console' => array(
                'cmd' => Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic cron &>/dev/null',
                'desc' => 'Run delayed or recurring tasks within X2Engine using the scheduled tasks request URL'
            ),
            'email_logInbound' => array(
                'cmd' => Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic logEmail &>/dev/null',
                'desc' => 'Poll for new email messages to log'
            ),
            'app_emailBounceHandling' => array(
                'cmd' => Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic emailBounceHandling &>/dev/null',
                'desc' => 'Poll for new email messages to log'
            ),
        );
        foreach($cf->jobs as $tag => $attributes) {
            $commands[$tag] = $attributes['cmd'];
        }
        if(isset($_POST['crontab_submit'])){
            // Save new updater cron settings in crontab
            $cf->save($_POST);
        }
        $this->controller->render('x2CronSettings',compact('commands'));
    }
}

?>
