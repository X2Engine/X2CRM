<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/
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
