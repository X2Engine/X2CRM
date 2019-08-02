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






Yii::app()->clientScript->registerCss('flowSettingsCSS',"

#disable-log-limit {
    display: block;
    float: left;
    margin-top: 10px;
}

#disable-log-limit + label {
    margin-top: 10px;
    position: relative;
    left: 2px;
    display: inline-block;
}

");


Yii::app()->clientScript->registerScript('flowSettingsJS',"
;(function () {
    $('#disable-log-limit').change (function () {
        $('#flow-limit-combo-elem').toggle (!$(this).is (':checked'));
    });
}) ();
", CClientScript::POS_END);
?>

<div class="page-title"><h2><?php echo Yii::t('admin','X2Workflow Settings'); ?></h2></div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'x2flow-settings',
	'enableAjaxValidation'=>false,
)); 
?>
<div class="form" id='x2flow-settings-form'>
<?php 
$disableLimit = !is_numeric ($model->triggerLogMax);
if ($disableLimit)
    $model->triggerLogMax = 500000;
echo X2Html::getFlashes ();
echo $form->labelEx(
    $model, 'triggerLogMax', array());
echo CHtml::checkBox(
    'disableLogLimit', $disableLimit, array('id' => 'disable-log-limit'));
echo CHtml::label(
    Yii::t('admin', 'Disable limit'), 'disableLogLimit', array('class' => ''));
?>
<div id='flow-limit-combo-elem' <?php echo $disableLimit ? 'style="display: none;"' : ''; ?>>
<?php
$this->widget('zii.widgets.jui.CJuiSlider', array(
    'value' => $model->triggerLogMax,
    'options' => array(
        'min' => 0,
        'max' => 1000000,
        'step' => 10000,
        'change' => "js:function(event,ui) {
            $('#trigger-log-max').val(ui.value);
            $('#x2flow-settings-form').find ('.save-button').addClass('highlight');
        }",
        'slide' => "js:function(event,ui) {
            $('#trigger-log-max').val(ui.value);
        }",
    ),
    'htmlOptions' => array(
        'id' => 'trigger-log-max-slider',
        'style' => 'margin:10px 0;',
        'class'=>'x2-wide-slider'
    ),
));
echo $form->textField ($model, 'triggerLogMax', array('id' => 'trigger-log-max'));
?>
</div>
<?php
?>
<br><br>
<?php
echo CHtml::hiddenField('formSubmit','1');
?>
<br>
<div class="row buttons">
	<?php 
    echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button save-button')); 
    ?>
</div>
</div>
<?php $this->endWidget(); ?>
