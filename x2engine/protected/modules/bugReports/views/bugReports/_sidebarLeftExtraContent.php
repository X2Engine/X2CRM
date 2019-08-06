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




if(!Yii::app()->user->isGuest) {
    if($this->action->id === 'view'){
        
        if(Yii::app()->user->checkAccess('ActionsTimerControl', array(
                    'assignedTo' => $this->model->assignedTo))){
            $this->beginWidget('LeftWidget',
            array(
                'widgetLabel'=>Yii::t('actions', 'Action Timer'),
                'id'=>'service-case-status-filter',
                'widgetName' => 'ActionTimer'
            ));
    
            $this->widget('application.modules.actions.components.ActionTimerControl', array(
                'model' => $this->model
            ));

            $this->endWidget();
        }
        
    }else{

    // get a list of statuses the user wants to hide
        $hideStatus = CJSON::decode(
                        Yii::app()->params->profile->hideBugsWithStatus);
        if(!$hideStatus){
            $hideStatus = array();
        }

        $this->beginWidget('zii.widgets.CPortlet', array(
            'title' => Yii::t('services', 'Filter By Status'),
            'id' => 'service-case-status-filter',
                )
        );

        echo '<ul id="bug-statuses-picker" style="font-size: 0.8em; font-weight: bold; color: black;">';
        $i = 1;

        foreach(Dropdowns::getItems(115) as $status){

            $checked = !in_array($status, $hideStatus);

            echo "<li>\n";
            echo CHtml::checkBox("service-case-status-filter-$i", $checked, array(
                'id' => "service-case-status-filter-$i",
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
                    'complete' => 'function(){
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
                Yii::t('app', 'All'), 'javascript:void(0);', array('id' => 'checkAllServiceFilters', 'class' => 'x2-button', 'style' => 'width:47px;',
            'ajax' => array(
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
                Yii::t('app', 'None'), 'javascript:void(0);', array('id' => 'uncheckAllServiceFilters',
            'class' => 'x2-button', 'style' => 'width:47px;',
            'ajax' => array(
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
}
