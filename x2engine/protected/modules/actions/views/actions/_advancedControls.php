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



?>
<div id="advanced-controls" class="form" style="display:none;">
<?php
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
    echo CHtml::form();
    echo Yii::t('actions',"Show me")." "
        .CHtml::dropDownList('complete',!empty($complete)?$complete:'No',array('No'=>Yii::t('actions','unfinished'),'Yes'=>Yii::t('actions','complete'),'all'=>Yii::t('actions','all')))
        ." ".Yii::t('actions',"{actions} assigned to", array('{actions}'=>Modules::displayName()))." "
        .CHtml::dropDownList('assignedTo',!empty($assignedTo)?$assignedTo:'me',array('me'=>Yii::t('actions','me'),'both'=>Yii::t('actions','me or anyone'),'all'=>Yii::t('actions','everyone')))
        ." ".Yii::t('actions',"that")." "
        .CHtml::dropDownList('dateType',!empty($dateType)?$dateType:'due',array('due'=>Yii::t('actions','are due'),'create'=>Yii::t('actions','were created')))
        ." "
        .CHtml::dropDownList('dateRange',!empty($dateRange)?$dateRange:'today',array(
            'today'=>Yii::t('actions','today'),
            'tomorrow'=>Yii::t('actions','tomorrow'),
            'week'=>Yii::t('actions','this week'),
            'month'=>Yii::t('actions','this month'),
            'all'=>Yii::t('actions','any time'),
            'range'=>Yii::t('actions','between these dates'),
        ));
    echo "<span id='date-controls' style='".((!empty($dateRange) && $dateRange=='range')?"":"display:none")."'> (";
    Yii::app()->controller->widget('CJuiDateTimePicker', array(
                'name' => 'start',
                'value'=>!empty($start)?$start:'',
                // 'title'=>Yii::t('actions','Start Date'),
                // 'model'=>$model, //Model object
                // 'attribute'=>$field->fieldName, //attribute name
                'mode' => 'date', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'htmlOptions' => array('id' => 'startDate', 'width' => 20),
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
            ));
    echo " and ";
    Yii::app()->controller->widget('CJuiDateTimePicker', array(
                'name' => 'end',
                // 'value'=>$startDate,
                'value'=>!empty($end)?$end:'',
                // 'title'=>Yii::t('actions','Start Date'),
                // 'model'=>$model, //Model object
                // 'attribute'=>$field->fieldName, //attribute name
                'mode' => 'date', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'htmlOptions' => array('id' => 'endDate', 'width' => 20),
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
            ));
    echo ") </span>";
    echo" ".Yii::t('actions',"and order them by")." "
        .CHtml::dropDownList('orderType',!empty($orderType)?$orderType:'desc',array('desc'=>Yii::t('actions','descending'),'asc'=>Yii::t('actions','ascending')))
        ." "
        .CHtml::dropDownList('order',!empty($order)?$order:'due',array('due'=>Yii::t('actions','due date'),'create'=>Yii::t('actions','create date'),'priority'=>Yii::t('actions','priority')));
    echo " ".CHtml::submitButton(Yii::t('app','Go'),array('class'=>'x2-button','style'=>'padding: 1px 15px;display:inline;'));
    echo CHtml::endForm();
?>
</div>
<script>
    $('#dateRange').on('change',function(){
       if($('#dateRange').val()=='range'){
           $('#date-controls').fadeIn();
       }else{
           $('#date-controls').fadeOut();
       }
    });
</script>
