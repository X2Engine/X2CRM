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




Yii::app()->clientScript->registerCss('appSettingsCss',"
#settings-form {
    padding-bottom: 1px;
}
");

Yii::app()->clientScript->registerScript('updateChatPollSlider', "
$('#settings-form input, #settings-form select, #settings-form textarea').change(function() {
	$('#save-button').addClass('highlight'); 
});

$('#locationTrackingFrequency').change(function() {
	$('#chatPollSlider').slider('value',$(this).val());
});
$('#timeout').change(function() {
	$('#timeoutSlider').slider('value',$(this).val());
});
$('#locationTrackingDistance').change(function(){
    $('#locationTrackingDistanceSlider').slider('value',$(this).val());
});
$('#massActionsBatchSize').change(function(){
    $('#massActionsBatchSizeSlider').slider('value',$(this).val());
});

$('#currency').change(function() {
	if($('#currency').val() == 'other')
		$('#currency2').fadeIn(300);
	else
		$('#currency2').fadeOut(300);
});
", CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Location Settings'); ?></h2></div>
<div class="admin-form-container">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
            ));
    ?>

    <div class="form">
        <?php
        echo $form->labelEx($model, 'locationTrackingFrequency');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->locationTrackingFrequency,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 1,
                'max' => 60,
                'step' => 1,
                'change' => "js:function(event,ui) {
					$('#locationTrackingFrequency').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#locationTrackingFrequency').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'id' => 'locationTrackingFrequencySlider',
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider'
            ),
        ));

        echo $form->textField($model, 'locationTrackingFrequency', array('id' => 'locationTrackingFrequency'));
        ?><br>
        <?php echo Yii::t('admin', 'Set the time between location requests in minutes.'); ?>
        <br><br>
        <?php echo Yii::t('admin', 'Decreasing this number allows for more instantaneous location fetching, but generates more server requests, so adjust it to taste. The default value is 3600 (1 hour).'); ?>
    </div>
    <div class="form">
        <?php
        echo $form->labelEx($model,'locationTrackingDistance');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->locationTrackingDistance,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'change' => "js:function(event,ui) {
					$('#locationTrackingDistance').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#locationTrackingDistance').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'style' => 'margin:10px 0;',
                'id' => 'locationTrackingDistanceSlider',
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider',
            ),
        ));
        echo $form->textField($model,'locationTrackingDistance',array('style'=>'width:50px;','id'=>'locationTrackingDistance'));
        echo '<p>'.Yii::t('admin','The number in kilometers they have to move in order to track their location.').'</p>';
        
         ?>
         
    </div>
    <div class="form">
        <label class='left-label' for="Admin_locationTrackingSwitch"><?php echo Yii::t('admin', 'Turn on Location Tracking'); ?></label>
        <?php echo X2Html::clearfix ();
        echo $form->checkBox($model, 'locationTrackingSwitch'); ?>
        <br><br>
        <label class='left-label' for="Admin_checkinByDefault"><?php echo Yii::t('admin', 'Enable Checkin Posts by Default'); ?></label>
        <?php echo X2Html::clearfix ();
        echo $form->checkBox($model, 'checkinByDefault'); ?>
    </div>
    
    <div class="error">
<?php echo $form->errorSummary($model); ?>
    </div>

<?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button'))."\n"; ?>
<?php $this->endWidget(); ?>
</div>
