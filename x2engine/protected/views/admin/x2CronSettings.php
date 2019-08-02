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






Yii::app()->clientScript->registerScript('x2CronSettingsJS',"
$(function () {
    // show cron log
    $('#view-log-button').on ('click', function () {
        if ($(this).attr ('disabled')) return;
        $('#view-log-button').attr ('disabled', 'disabled');
        $.ajax ({
            'url': ".CJSON::encode(
                $this->createUrl(
                    '/admin/viewLog',
                    array('name' => 'automation.log'))).",
            'success': function (data) {
                console.log('huh');
                var dia = $('<div>', {
                    id: 'cron-log-dialog',
                    html: data
                });
                $('body').append(dia);
                dia.dialog ({
                    autoOpen: true,
                    maxHeight: '500px',
                    title: ".CJSON::encode(
                        Yii::t(
                            'studio', 'Viewing log file {file}',
                            array('{file}' => 'automation.log'))).",
                    width: 'auto',
                    resizable: true,
                    close: function (event, ui) {
                        $('#cron-log-dialog').remove ();
                        $('#view-log-button').removeAttr ('disabled');
                    }
                });
                dia.attr('style', 'max-height:500px;overflow-x:hidden;overflow-y:scrollable;');
            }
        });
    });
})
", CClientScript::POS_END);


?>
<div class="page-title">
    <h2><?php echo Yii::t('admin', 'Cron Table') ?></h2>
    <a class="x2-button right" id="view-log-button" href="javascript:void(0);">
        <?php echo Yii::t('studio', 'View Cron Log'); ?>
    </a>
</div>
<div class="span-24" style="width:99%;">
    <div class="form" style="width:100%;">

        <h3><?php echo Yii::t('admin','Disclaimer'); ?></h3>
        <p><?php echo Yii::t('admin','Using this form may interfere with third-party cron table managers.')
                .'&nbsp;'.Yii::t('admin','If you are not using X2Engine Cloud / On Demand, and your hosting service provides a scheduled tasks manager, it is recommended that you use that instead, with the commands as listed here.'); ?></p>
        <?php
        $form = Yii::app()->controller->beginWidget('CActiveForm', array(
            'id' => 'cron-settings-form',
                ));
        $this->widget('CronForm', array(
            'labelCssClass' => 'cron-checkitem big',
            'formData' => $_POST,
            'displayCmds' => $commands,
            'jobs' => array(
                'default' => array(
                    'title' => Yii::t('admin', 'Run scheduled X2Engine tasks via web request'),
                    'longdesc' => Yii::t('admin', 'If enabled, a web request will be made from this web server to itself at the scheduled task runner URL.* This will trigger events such as X2Flow delayed actions and periodic triggers, and will attempt to send a batch of unsent email campaign messages.'),
                    'instructions' => Yii::t('admin', 'Specify a cron schedule below. Note that for this to work properly requires that the domain name of the server can be resolved from itself, and there is a valid network route to its public/external network address. To check whether this is true, use the {link}.',array('{link}' => CHtml::link('local API resolvability test', Yii::app()->baseUrl.'/resolve_self.php', array('target' => '_blank'))))
                    .'<br /><br />'.Yii::t('admin','If the above link does not work, download the script from {link} and copy it to the web root of X2Engine. If the script produces a message saying that it cannot resolve the local server, consider disabling this and enabling the alternate scheduled task running method, below.',array('{link}'=>CHtml::link(Yii::t('admin','here'),'https://raw.github.com/X2Engine/X2Engine/master/x2engine/resolve_self.php')) ).'<br /><br />* '.Yii::app()->controller->createAbsoluteUrl('/api/x2cron'),
                ),
                'default_console' => array(
                    'title' => Yii::t('admin','Run scheduled X2Engine tasks via command line interface'),
                    'longdesc' => Yii::t('admin','If enabled, the Yii console command runner will be used to perform scheduled tasks.'),
                    'instructions' => Yii::t('admin', 'Specify a cron schedule below. This will perform all of the same tasks as the web-based scheduled task runner, except for sending batches of campaign emails.'),
                ),
                'app_update' => array(
                    'title' => Yii::t('admin', 'Update automatically'),
                    'longdesc' => Yii::t('admin', 'If enabled, X2Engine will periodically check for updates and update automatically if a new version is available.'),
                    'instructions' => Yii::t('admin', 'Specify an update schedule below. Note, X2Engine will be locked when the update is being applied, and so it is recommended to schedule updates at times when the application will encounter the least use. If any compatibility issues are detected, the update package will not be applied, but will be retrieved and unpacked for manual review and confirmation.'),
                ),
                'email_logInbound' => array(
                    'title' => Yii::t('admin','Auto-Log Emails'),
                    'longdesc' => Yii::t('admin','If enabled, X2Engine will automatically poll for new inbound and outbound email messages in all of the Email Inboxes with inbound or outbound logging enabled.'),
                    'instructions' => Yii::t('admin', ''),
                ),
                'app_emailBounceHandling' => array(
                    'title' => Yii::t('admin','Run Bounce Handling Process'),
                    'longdesc' => Yii::t('admin','X2Engine will automatically poll for new inbound bounced email messages and update the related campaigns and contacts.'),
                    'instructions' => Yii::t('admin', ''),
                ),
            ),
        ));
        echo '<hr />';
        echo CHtml::hiddenField('crontab_submit',1);
        echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n";
        Yii::app()->controller->endWidget();
        ?>
    </div>
</div>
