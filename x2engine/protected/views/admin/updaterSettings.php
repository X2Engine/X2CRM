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
 ?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Updater Settings'); ?></h2></div>
<div class="span-24">
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
        <p><?php echo Yii::t('admin','As often as specified, X2CRM will check for updates and display a system notification message if a new version is available.'); ?></p>
        <hr /><?php

        //////////////////////////////////////////////////
        // Auto-updater cron job schedule form elements //
        //////////////////////////////////////////////////
        $this->widget('CronForm',array(
            'formData' => $_POST,
            'jobs' => array(
                'app_update' => array(
                    'title' => Yii::t('admin', 'Update Automatically'),
                    'longdesc' => Yii::t('admin', 'If enabled, X2CRM will periodically check for updates and update automatically if a new version is available.'),
                    'instructions' => Yii::t('admin', 'Specify an update schedule below. Note, X2CRM will be locked when the update is being applied, and so it is recommended to schedule updates at times when the application will encounter the least use. If any compatibility issues are detected, the update package will not be applied, but will be retrieved and unpacked for manual review and confirmation.'),
                )
            ),
        ));

        ?>
        <hr />
        <span class="mock-x2-form-label"><?php echo Yii::t('admin','Manual / Offline Update'); ?></span><br />
                <?php
                echo CHtml::tag('p',array(),Yii::t('admin','To update manually, if using X2CRM offline or if something goes wrong, see the instructions given in {wikilink}.',array(
                    '{wikilink}' => CHtml::link(Yii::t('admin','The X2CRM Update Guide'),'http://wiki.x2engine.com')
                )));
                echo CHtml::tag('p',array(),Yii::t('admin','Download links you will need:'));
                $edition = Yii::app()->params->admin->edition;
                $uniqueId = Yii::app()->params->admin->unique_id;
                ?>
                <ul>
                    <li><?php echo CHtml::link(Yii::t('admin','Latest Update Package for Version {version}',array('{version}'=>Yii::app()->params->version)),array('/admin/updater','redirect'=>1)); ?></li>
                    <li><?php echo CHtml::link(Yii::t('admin','Latest Updater Utility Patch'),$edition=='opensource' ? "https://x2planet.com/installs/updater.zip" : "https://x2planet.com/installs/{$uniqueId}/updater-{$edition}.zip");?></li>
                    <li><?php echo CHtml::link(Yii::t('admin','File Set Refresh Package'),$edition=='opensource'?"https://x2planet.com/installs/refresh.zip":"https://x2planet.com/installs/{$uniqueId}/refresh-{$edition}.zip");?></li>
                </ul>
        <hr />
        <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n"; ?>
           <?php $this->endWidget(); ?>

    </div><!-- .form -->
</div><!-- .span-24 -->
