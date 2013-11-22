<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

if(!Yii::app()->user->isGuest) {

    // get a list of statuses the user wants to hide
    $hideStatus = CJSON::decode(
        Yii::app()->params->profile->hideBugsWithStatus); 
    if(!$hideStatus) {
        $hideStatus = array();
    }

    $this->beginWidget('zii.widgets.CPortlet',
        array(
            'title'=>Yii::t('services', 'Filter By Status'),
            'id'=>'service-case-status-filter',
        )
    );

    echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
    $i = 1;

    foreach(Dropdowns::getItems(115) as $status) {

        $checked = !in_array($status, $hideStatus);

        echo "<li>\n";
        echo CHtml::checkBox("service-case-status-filter-$i",$checked,
            array(
                'id'=>"service-case-status-filter-$i",
                // add or remove user's actions to calendar if checked/unchecked
                // 'onChange'=>"toggleUserCalendarSource(
                //     this.name, this.checked, $editable);", 
                'ajax' => array(
                    'type' => 'POST', //request type
                    //url to call
                    'url' => Yii::app()->controller->createUrl('/bugReports/bugReports/statusFilter'), 
                    //selector to update
                    'success' => 'js:function(response) { 
                        $.fn.yiiGridView.update("bugReports-grid"); }', 
                    'data' => 'js:{
                        checked: $(this).attr("checked")=="checked", status:"'.$status.'"
                    }',
                    // check / uncheck the checkbox after the ajax call
                    'complete'=>'function(){
                        if($("#service-case-status-filter-'.$i.'").
                            attr("checked")=="checked") {

                            $("#service-case-status-filter-'.$i.'").removeAttr(
                                "checked","checked");
                        } else {
                            $("#service-case-status-filter-'.$i.'").attr(
                                "checked","checked");
                        }
                    }'
                )
            )
        );
        echo CHtml::label(CHtml::encode($status), "service-case-status-filter-$i");
        echo "</li>";
        $i++;
    }
    echo "</ul>\n";
    echo '<div class="x2-button-group">';
    echo CHtml::link(
        Yii::t('app','All'),'javascript:void(0);',
        array('id'=>'checkAllServiceFilters','class'=>'x2-button','style'=>'width:47px;',
        'ajax'=>array(
            'type' => 'POST', //request type
            //url to call
            'url' => Yii::app()->controller->createUrl('/bugReports/bugReports/statusFilter'), 
            'success' => 'function(response) {
                $.fn.yiiGridView.update("bugReports-grid");
                $("#service-case-status-filter li input").attr("checked","checked");
            }',
            'data' => 'js:{all:1}',
        )
    ));
    echo CHtml::link(
        Yii::t('app','None'),'javascript:void(0);',array('id'=>'uncheckAllServiceFilters',
            'class'=>'x2-button','style'=>'width:47px;',
        'ajax'=>array(
            'type' => 'POST', //request type
            // url to call
            'url' => Yii::app()->controller->createUrl('/bugReports/bugReports/statusFilter'),
            'success' => 'function(response) {
                $.fn.yiiGridView.update("bugReports-grid");
                $("#service-case-status-filter li input").removeAttr("checked");
            }',
            'data' => 'js:{none:1}',
        )
    ));
    echo '</div>';


    $this->endWidget();
}
