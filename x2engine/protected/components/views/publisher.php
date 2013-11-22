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

<?php $users = User::getNames(); ?>
<?php $form = $this->beginWidget('CActiveForm', array('id' => 'publisher-form')); ?>

<div id="tabs">
    <ul <?php echo ($showNewEvent && !$halfWidth ? 'style="display: none;"' : ''); ?>>
        <?php if(!$halfWidth) { ?>
        <li class="publisher-label">
            <?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif', Yii::t('app', 'Loading'), array('id' => 'publisher-saving-icon', 'style' => 'position: absolute; width: 14px; opacity: 0.0')); ?>
            <span class="publisher-text"> <?php echo Yii::t('actions', 'Publisher'); ?></span>
        </li>
        <?php } ?>
        <?php if($showLogACall){ ?><li><a href="#log-a-call"><?php echo Yii::t('actions', 'Log A Call'); ?></a></li><?php } ?>
        <?php if($showLogTimeSpent){ ?><li><a href="#log-time-spent"><?php echo Yii::t('actions', 'Log Time'); ?></a></li><?php } ?>
        <?php if($showNewAction){ ?><li><a href="#new-action"><b>+</b><?php echo Yii::t('actions', 'Action'); ?></a></li><?php } ?>
        <?php if($showNewComment){ ?><li style='margin-right: 0'><a href="#new-comment"><b>+</b><?php echo Yii::t('actions', 'Comment'); ?></a></li><?php } ?>
        <?php if($showNewEvent){ ?><li><a href="#new-event"><b>+</b><?php echo Yii::t('actions', 'Event'); ?></a></li><?php } ?>
    </ul>
    <?php $this->render($halfWidth ? '_publisherHalfWidth' : '_publisher', call_user_func_array('compact',array_merge(array('form'),$this->viewParams))); ?>
</div>
<?php
$this->endWidget();

?>
