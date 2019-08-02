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
 * Renders the action timers adjustment form.
 */

Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/actionTimersForm.js',CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/actionTimersForm.css');

$timers = $model->timers;
if(!empty($timers)) {
$timerTypes = Dropdowns::getItems(120);
?>
<div class="form">

<label style="font-weight: bold; color: black;">
    <?php echo Yii::t('actions','{action} timers', array(
        '{action}' => Modules::displayName(false),
    )); ?>
</label>
<br />
<table class="action-timers-form" id="action-timers-form">
<thead>
<th></th>
<th>Type</th>
<th>Started</th>
<th>Stopped</th>
<th class="timer-total-column">Total</th>
</thead>
<tbody>
<?php foreach($model->timers as $timer) { ?>
<tr class="timer-record" id="timer-record-<?php echo $timer->id; ?>">
<td>
    <a class="delete-timer" href="javascript:void(0);" title="Delete this time interval">
        <img src="<?php echo Yii::app()->theme->baseUrl;?>/css/gridview/delete.png" alt="Delete">
    </a>
    <?php
    $attr = 'id';
    echo CHtml::activeHiddenField($timer,$attr,array('name'=>CHtml::resolveName($timer,$attr).'[]')); 
    ?>
</td><!-- Delete button + hidden ID field -->
<td>
<?php 
$attr = 'type';
echo CHtml::activeDropDownList($timer,$attr,$timerTypes,array(
    'name' => CHtml::resolveName($timer,$attr).'[]',
));

?>
</td><!-- Timer type -->
<?php 
foreach(array('timestamp','endtime') as $attr) {
?><td><?php
    $timer->$attr = Yii::app()->dateFormatter->formatDateTime($timer->$attr, 'medium', 'medium');
    echo Yii::app()->controller->widget('CJuiDateTimePicker', array(
        'model' => $timer, //Model object
        'attribute' => $attr, //attribute name
        'mode' => 'datetime', //use "time","date" or "datetime" (default)
        'options' => array(// jquery options
            'dateFormat' => Formatter::formatDatePicker('medium'),
            'timeFormat' => Formatter::formatTimePicker('',true),
            'ampm' => Formatter::formatAMPM(),
            'changeMonth' => true,
            'changeYear' => true,
            'showSecond' => true
        ),
        'htmlOptions' => array(
            'name' => CHtml::resolveName($timer,$attr).'[]',
            'id' => 'timer-record-'.$attr.'-'.$timer->id,
            'class' => 'time-input time-at-'.$attr
        ),
        'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
    ), true);

?></td><?php } ?>
<td class="timer-total-column">
<input type="hidden" class="timer-total" name="timer-total-<?php echo $timer->id ?>" value="">
<span class="timer-total"></span>
</td><!-- Total -->
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr class="all-timers-total">
<td colspan="4"></td>
<td class="timer-total-column">
<input type="hidden" class="timer-total all-timers-total" name="timer-total-<?php echo $timer->id ?>" value="">
<span class="timer-total"></span>
</td>
</tfoot>
</table>

<?php
Yii::app()->clientScript->registerScript('action-timer-form-init-rows', "
    x2.actionTimersForm.getElement('tr.timer-record').each(function(){
        x2.actionTimersForm.recalculateLine($(this));
});");

} ?>
</div><!-- .form -->
