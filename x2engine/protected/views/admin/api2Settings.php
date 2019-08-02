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
 * Settings for the 2nd-gen REST API
 */
?>

<div class="page-title"><h2><?php echo Yii::t('admin', 'REST API Settings'); ?></h2></div>
<div class="admin-form-container">
    <div class="form">
        <div class="row">
            <p><?php echo Yii::t('admin', 'These settings configure the behavior '
                    . 'of the X2Engine REST API at: {url}. For more information '
                    . 'about this API and how to use it, see {docUrl}', array(
                '{url}' => '<strong>'.CHtml::encode($this->createAbsoluteUrl('/api2')).'</strong>',
                 '{docUrl}' => CHtml::link(Yii::t('admin','The X2Engine REST API Reference'),'http://wiki.x2engine.com/wiki/REST_API_Reference')

                )); ?></p>
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'settings-form',
            ));
            $model->api2->renderInputs();
            ?>
            <?php
            echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n";
            $this->endWidget();
            ?>
        </div>
    </div>
</div>
