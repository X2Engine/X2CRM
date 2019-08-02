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



 ?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Updater Settings'); ?></h2></div>
<div class='span-24'>
    <div class="form">
        <?php

        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'settings-form',
            'enableAjaxValidation' => false,
                ));
        ?><?php
        Yii::app()->clientScript->registerScriptfile(Yii::app()->baseUrl.'/js/webtoolkit.sha256.js');
        $updatesForm = new UpdatesForm(
                        array(
                            'x2_version' => Yii::app()->params['version'],
                            'unique_id' => $model->unique_id,
                            'formId' => 'settings-form',
                            'submitButtonId' => 'save-button',
                            'statusId' => 'error-box',
                            'themeUrl' => Yii::app()->theme->baseUrl,
                            'serverInfo' => True,
                            'edition' => $model->edition,
                            'titleWrap' => array('<span class="mock-x2-form-label">', '</span>'),
                            'receiveUpdates' => isset($_POST['receiveUpdates']) ? $_POST['receiveUpdates'] : 0,
                        ),
                        'Yii::t',
                        array('install')
        );
        $this->renderPartial('stayUpdated', array('form' => $updatesForm));
        if(Yii::app()->params->isAdmin && Yii::app()->contEd('pro') && Yii::app()->settings->unique_id != '') {
            echo '<strong>'.Yii::t('app','Your license key is:').'</strong>&nbsp;<tt>'.Yii::app()->settings->unique_id.'</tt><br /><br />';
        }
        ?>
        <input type="hidden" id="adminEmail" name="adminEmail" value="<?php echo $model->emailFromAddr; ?>" />
        <input type="hidden" id="language" name="language" value="<?php echo Yii::app()->language; ?>" />
        <input type="hidden" id="currency" name="currency" value="<?php echo $model->currency; ?>" />
        <input type="hidden" id="timezone" name="timezone" value="<?php echo Yii::app()->params['profile']->timeZone; ?>" />
        <div id="error-box" class="form" style="display:none"></div>
        <hr />

        <?php
        echo $form->labelEx($model, 'updateInterval');
        echo $form->dropDownList($model, 'updateInterval', array(
            '0' => Yii::t('admin', 'Every Login'),
            '86400' => Yii::t('admin', 'Daily'),
            '604800' => Yii::t('admin', 'Weekly'),
            '2592000' => Yii::t('admin', 'Monthly'),
            '-1' => Yii::t('admin', 'Never'),
        ));
        ?>
        <p><?php echo Yii::t('admin','As often as specified, X2Engine will check for updates and display a system notification message if a new version is available.'); ?></p>
        <hr /><?php

        //////////////////////////////////////////////////
        // Auto-updater cron job schedule form elements //
        //////////////////////////////////////////////////
        ?>
                    <h3><?php echo Yii::t('admin','Disclaimer'); ?></h3>
        <p><?php echo Yii::t('admin','Using this form may interfere with third-party cron table managers.')
                .'&nbsp;'.Yii::t('admin','If you are not using X2Engine Cloud / On Demand, and your hosting service provides a scheduled tasks manager, it is recommended that you use that instead, with the commands as listed here.'); ?></p>

            <?php
        $this->widget('CronForm',array(
            'formData' => $_POST,
            'displayCmds' => $displayCmds,
            'jobs' => array(
                'app_update' => array(
                    'title' => Yii::t('admin', 'Update Automatically'),
                    'longdesc' => Yii::t('admin', 'If enabled, X2Engine will periodically check for updates and update automatically if a new version is available.'),
                    'instructions' => Yii::t('admin', 'Specify an update schedule below. Note, X2Engine will be locked when the update is being applied, and so it is recommended to schedule updates at times when the application will encounter the least use. If any compatibility issues are detected, the update package will not be applied, but will be retrieved and unpacked for manual review and confirmation.'),
                )
            ),
        ));

        ?>
        <hr />
        <span class="mock-x2-form-label"><?php echo Yii::t('admin','Manual / Offline Update'); ?></span><br />
                <?php
                echo CHtml::tag('p',array(),Yii::t('admin','To update manually, if using X2Engine offline or if something goes wrong, see the instructions given in {wikilink}.',array(
                    '{wikilink}' => CHtml::link(Yii::t('admin','The X2Engine Update Guide'),'http://wiki.x2engine.com/wiki/Software_Updates_and_Upgrades#Performing_.22Offline.22_Updates')
                )));
                echo CHtml::tag('p',array(),Yii::t('admin','Links you will need:'));
                $edition = Yii::app()->settings->edition;
                $uniqueId = Yii::app()->settings->unique_id;
                $this->scenario = 'update';
                ?>
                <ul>
                    <li><?php echo CHtml::link(Yii::t('admin','Latest Update Package for Version {version}',array('{version}'=>Yii::app()->params->version)),$this->updateServer.'/'.$this->getUpdateDataRoute()); ?></li>
                    <li><?php echo CHtml::link(Yii::t('admin','Latest Updater Utility Patch'),$edition=='opensource' ? "https://x2planet.com/installs/updater.zip" : "https://x2planet.com/installs/{$uniqueId}/updater-{$edition}.zip");?></li>
                    <li><?php echo CHtml::link(Yii::t('admin','File Set Refresh Package'),$edition=='opensource' ? "https://x2planet.com/installs/refresh.zip" : "https://x2planet.com/installs/{$uniqueId}/refresh-{$edition}.zip");?></li>
                    <li><?php echo CHtml::link(Yii::t('admin','Latest updater utility version number'),$this->updateServer.'/installs/updates/updateCheck'); ?></li>
                </ul>
        <hr />
        <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n"; ?>
           <?php $this->endWidget(); ?>

    </div><!-- .form -->
</div><!-- .span-24 -->
