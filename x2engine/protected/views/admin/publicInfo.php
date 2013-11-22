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
<div class="span-16">
    <div class="page-title"><h2><?php echo Yii::t('admin', 'Public Info Settings'); ?></h2></div>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
            ));
    ?>
    <div class="form">
        <?php echo $form->labelEx($model, 'externalBaseUrl'); ?>
        <p><?php echo Yii::t('admin', 'This will be the web root URL to use for generating URLs to public-facing resources, i.e. email tracking images, the web listener, etc. You should use this if the CRM is behind a firewall and you access X2CRM using a different URL than one would use to access it from the internet (i.e. a host name / IP address on a private subnet or VPN).'); ?></p>
        <?php echo $form->textField($model, 'externalBaseUrl',array('style' => 'width: 90%')); ?>
        <?php echo CHtml::error($model, 'externalBaseUrl');?>
    </div><!-- .form -->

    <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button'))."\n"; ?>
    <?php $this->endWidget(); ?>
</div><!-- .span-16 -->