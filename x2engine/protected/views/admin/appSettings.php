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

$('#chatPollTime').change(function() {
	$('#chatPollSlider').slider('value',$(this).val());
});
$('#timeout').change(function() {
	$('#timeoutSlider').slider('value',$(this).val());
});
$('#batchTimeout').change(function(){
    $('#batchTimeoutSlider').slider('value',$(this).val());
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
<div class="page-title"><h2><?php echo Yii::t('admin', 'General Settings'); ?></h2></div>
<div class="admin-form-container">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
            ));
    ?>

    <div class="form">
        <?php
        echo $form->labelEx($model, 'chatPollTime');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->chatPollTime,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 1000,
                'max' => 100000,
                'step' => 100,
                'change' => "js:function(event,ui) {
					$('#chatPollTime').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#chatPollTime').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'id' => 'chatPollSlider',
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider'
            ),
        ));

        echo $form->textField($model, 'chatPollTime', array('id' => 'chatPollTime'));
        ?><br>
        <?php echo Yii::t('admin', 'Set the duration between notification requests in milliseconds.'); ?>
        <br><br>
        <?php echo Yii::t('admin', 'Decreasing this number allows for more instantaneous notifications, but generates more server requests, so adjust it to taste. The default value is 3000 (3 seconds).'); ?>
    </div>
    <div class="form">
        <?php
        echo $form->labelEx($model, 'timeout');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->timeout,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 5,
                'max' => 1440,
                'step' => 5,
                'change' => "js:function(event,ui) {
					$('#timeout').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#timeout').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider',
                'id' => 'timeoutSlider'
            ),
        ));

        echo $form->textField($model, 'timeout', array('id' => 'timeout'));
        ?>
        <br>
        <?php echo Yii::t('admin', 'Set user session expiration time (in minutes). Default is 60.'); ?><br>
        <br>
        <label for="Admin_sessionLog"><?php echo Yii::t('admin', 'Log user sessions?'); ?></label>
        <?php echo $form->checkBox($model, 'sessionLog'); ?>
    </div>
    <div class="form">
        <?php
        echo $form->labelEx($model, 'loginCredsTimeout');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->loginCredsTimeout,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 1,
                'max' => 365,
                'step' => 1,
                'change' => "js:function(event,ui) {
					$('#loginCredsTimeout').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#loginCredsTimeout').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider',
                'id' => 'loginCredsTimeoutSlider'
            ),
        ));

        echo $form->textField($model, 'loginCredsTimeout', array('id' => 'loginCredsTimeout'));
        ?>
        <br>
        <?php echo Yii::t('admin', 'Set all mobile users Single sign-on token expiration time (in days). Default is 30.'); ?><br>
        <br>
        <label for="Admin_tokenPersist"><?php echo Yii::t('admin', 'Persist token indefinitely?'); ?></label>
        <?php echo $form->checkBox($model, 'tokenPersist'); ?>
    </div>
    <div class="form">
        <?php
        echo $form->labelEx($model,'batchTimeout');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->batchTimeout,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 5,
                'max' => 600,
                'step' => 5,
                'change' => "js:function(event,ui) {
					$('#batchTimeout').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#batchTimeout').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'style' => 'margin:10px 0;',
                'id' => 'batchTimeoutSlider',
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider',
            ),
        ));
        echo $form->textField($model,'batchTimeout',array('style'=>'width:50px;','id'=>'batchTimeout'));
        echo '<p>'.Yii::t('admin','When running actions in batches, this (number of seconds) constrains the amount of time that can be spent doing so. It is recommended to set this lower than the maximum PHP execution time on your web server.').'</p>';
        ?>
    </div>
    <div class="form">
        <?php
        echo $form->labelEx($model,'massActionsBatchSize');
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $model->massActionsBatchSize,
            // additional javascript options for the slider plugin
            'options' => array(
                'min' => 5,
                'max' => 100,
                'step' => 5,
                'change' => "js:function(event,ui) {
					$('#massActionsBatchSize').val(ui.value);
					$('#save-button').addClass('highlight');
				}",
                'slide' => "js:function(event,ui) {
					$('#massActionsBatchSize').val(ui.value);
				}",
            ),
            'htmlOptions' => array(
                'style' => 'margin:10px 0;',
                'id' => 'massActionsBatchSizeSlider',
                'style' => 'margin:10px 0;',
                'class'=>'x2-wide-slider',
            ),
        ));
        echo $form->textField($model,'massActionsBatchSize',array('style'=>'width:50px;','id'=>'massActionsBatchSize'));
        ?>
    </div>
    <div class="form">
        <label class='left-label' for="Admin_quoteStrictLock"><?php echo Yii::t('admin', 'Enable Strict Lock on Quotes'); ?></label><?php echo X2Html::hint2 (Yii::t('admin', 'Enabling strict lock completely disables locked quotes from being edited. While this setting is off, there will be a confirm dialog before editing a locked quote.'));
        echo X2Html::clearfix (); 
        echo $form->checkBox($model, 'quoteStrictLock'); ?>
        <br><br>
        <label class='left-label' for="Admin_userActionBackdating"><?php echo Yii::t('admin', 'Allow Users to Backdate Actions'); ?></label><?php echo X2Html::hint2 (Yii::t('admin', 'Enabling action backdating will allow any user to change the automatically set date fields (i.e. create date). While this setting is off, only those with Admin access to the Actions module will be allowed to backdate actions.'));
        echo X2Html::clearfix ();
        echo $form->checkBox($model, 'userActionBackdating'); ?>
        <br><br>
        <?php
        echo $form->label ($model, 'disableAutomaticRecordTagging', array (
            'class' => 'left-label',
        ));
        echo X2Html::hint2 (Yii::t('admin', 'Enabling action backdating will allow any user to change the automatically set date fields (i.e. create date). While this setting is off, only those with Admin access to the Actions module will be allowed to backdate actions.'));
        echo X2Html::clearfix ();
        echo $form->checkBox($model, 'disableAutomaticRecordTagging'); ?>
    </div>
    <div class="form">
        <label class='left-label' for="Admin_historyPrivacy"><?php echo Yii::t('admin', 'Event/Action History Privacy'); ?></label><?php echo X2Html::hint2 (Yii::t('admin', 'Default will allow users to see actions/events which are public or assigned to them. User Only will allow users to only see actions/events assigned to them. Group Only will allow users to see actions/events assigned to members of their groups.'));
        echo X2Html::clearfix ();
        echo $form->dropDownList($model, 'historyPrivacy', array(
            'default' => Yii::t('admin', 'Default'),
            'user' => Yii::t('admin', 'User Only'),
            'group' => Yii::t('admin', 'Group Only'),
        ));
        ?>
        <br><br>
        <?php echo Yii::t('admin', 'Choose a privacy setting for the Action History widget and Activity Feed. Please note that any user with Admin level access to the module that the History is on will ignore this setting. Only users with full Admin access will ignore this setting on the Activity Feed.') ?>
    </div>
    <div class="form">
        <?php echo $form->labelEx($model, 'corporateAddress'); ?>
        <div>
        <?php echo Yii::t('admin', 'Enter your corporate address to enable directions on the Google Maps widget.') ?>
        </div>
        <?php echo $form->textArea($model, 'corporateAddress', array('id' => 'corporateAddress', 'style' => 'height:100px;', 'class'=>'x2-extra-wide-input')); ?>
        <br><br>
        <?php echo $form->labelEx($model, 'properCaseNames'); ?>
        <?php echo Yii::t('admin', 'Attempt to format Contact names to have proper case?') ?><br>
        <?php echo $form->dropDownList($model, 'properCaseNames', array(1 => Yii::t('app', 'Yes'), 0 => Yii::t('app', 'No'))); ?>
        <br><br>
        <?php echo $form->labelEx($model, 'contactNameFormat'); ?>
        <?php echo Yii::t('admin', 'Select a name format to use for Contact names throughout the app.') ?><br>
<?php echo $form->dropDownList($model, 'contactNameFormat', array('firstName lastName' => '{'.Yii::t('contacts', 'First Name').'} {'.Yii::t('contacts', 'Last Name').'}', 'lastName, firstName' => '{'.Yii::t('contacts', 'Last Name').'}, {'.Yii::t('contacts', 'First Name').'}')); ?>
    </div>

    <div class="form">
        <?php echo $form->labelEx($model, 'currency'); ?>
            <?php echo Yii::t('admin', 'Select a default currency for quotes and invoices.') ?><br>
        <select name="currency" id="currency">
            <?php
            $curFound = false;
            foreach(Yii::app()->params->supportedCurrencies as $currency):
                ?>
                <option value="<?php echo $currency ?>"<?php if($model->currency == $currency){
                    $curFound = true;
                    echo ' selected="true"';
                } ?>><?php echo $currency; ?></option>
        <?php endforeach; ?>
            <option value="other"<?php if(!$curFound){
            echo ' selected="true"';
        } ?>><?php echo Yii::t('admin', 'Other'); ?></option>
        </select>
        <input type="text" name="currency2" id="currency2" style="width:120px;<?php if($curFound) echo 'display:none;'; ?>" value="<?php echo $curFound ? '' : $model->currency; ?>" />
    </div>
    
    <div class="form">
        <?php echo $form->labelEx($model, 'duplicateFields'); ?>
        <?php echo Yii::t('admin', 'To choose which fields are checked for when finding duplicates, enter the fields here separated by commas.'); 
        ?> 
        <br />
            <?php
              echo $form->textArea ($model, 'duplicateFields', array(
                   'id' => 'duplicateFields', 'style' => 'height:100px;', 'class'=>'x2-extra-wide-input', 'value' => Yii::app()->settings->duplicateFields
              ));
              echo $form->error ($model, 'duplicateFields').'<br /><br />';  
        ?><br>
    </div>

    <div class="error">
<?php echo $form->errorSummary($model); ?>
    </div>

<?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button'))."\n"; ?>
<?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n";  ?>
<?php $this->endWidget(); ?>
</div>
