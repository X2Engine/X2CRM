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




echo '<div>';
if ($model->launchDate == 0) {
    echo CHtml::beginForm(array('launch', 'id' => $model->id));
    echo CHtml::submitButton(
            Yii::t('marketing', 'Launch Now'), array('class' => 'x2-button highlight left', 'style' => 'margin-left:0;margin: 5px;'));
    echo CHtml::endForm();
    if ($model->type == 'Email') {
        echo CHtml::Button(
                Yii::t('marketing', 'Send Test Email'), array(
            'id' => 'test-email-button',
            'class' => 'x2-button left',
            'style' => 'margin: 5px;',
            'onclick' => 'toggleEmailForm(); return false;'
                )
        );
    }
} elseif ($model->active) {
    echo CHtml::beginForm(array('toggle', 'id' => $model->id));
    echo CHtml::submitButton(
            Yii::t('app', 'Stop'), array('id' => 'campaign-toggle-button', 'class' => 'x2-button left urgent', 'style' => 'margin-left:0;'));
    echo CHtml::endForm();
    echo CHtml::beginForm(array('complete', 'id' => $model->id));
    echo CHtml::submitButton(
            Yii::t('marketing', 'Complete'), array('id' => 'campaign-complete-button', 'class' => 'x2-button highlight left', 'style' => 'margin-left:0;'));
    echo CHtml::endForm();
} else { //active == 0
    echo CHtml::beginForm(array('toggle', 'id' => $model->id));
    echo CHtml::submitButton(
            Yii::t('app', 'Resume'), array('class' => 'x2-button highlight left', 'style' => 'margin-left:0;'));
    echo CHtml::endForm();
    echo CHtml::beginForm(array('complete', 'id' => $model->id));
    echo CHtml::submitButton(
            Yii::t('marketing', 'Complete'), array('class' => 'x2-button left', 'style' => 'margin-left:0;'));
    echo CHtml::endForm();
    if ($model->type == 'Email') {
        echo CHtml::Button(
                Yii::t('marketing', 'Send Test Email'), array(
            'id' => 'test-email-button',
            'class' => 'x2-button left',
            'onclick' => 'toggleEmailForm(); return false;'
                )
        );
    }
}
?>

</div>
