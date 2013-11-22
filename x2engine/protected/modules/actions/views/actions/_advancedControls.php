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
<div id="advanced-controls" class="form" style="display:none;">
<?php
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
    echo CHtml::form();
    echo Yii::t('actions',"Show me")." "
        .CHtml::dropDownList('complete',!empty($complete)?$complete:'No',array('No'=>Yii::t('actions','unfinished'),'Yes'=>Yii::t('actions','complete'),'all'=>Yii::t('actions','all')))
        ." ".Yii::t('actions',"Actions assigned to")." "
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
