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






Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/views/flowMacros.css');

Yii::app()->clientScript->registerScript('flowDescriptions',"
;(function () { 
    var flowDescriptions = $flowDescriptions;   
    $('#flow-macro-flow-id').on('change',function(data){
        $('#flow-macro-flow-id').removeClass ('x2-error');
        if (!$(this).val ()) {
            $('#flow-macros-description-box').html (''); 
        } else {
            var flow = $(this).val();
            if (typeof flowDescriptions[flow] !== 'undefined') {
                var html = flowDescriptions[$(this).val()];
                $('#flow-macros-description-box').html(html);
            }
        }
    });
    $('#form-macro-submit').on ('click', function () {
        $('#flow-macro-flow-id').removeClass ('x2-error');
        if (!$('#flow-macro-flow-id').val ()) {
            $('#flow-macro-flow-id').addClass ('x2-error');
            return false;
        } else {
            return confirm ('".
                addslashes (Yii::t('app','Are you sure you want to execute this workflow?'))."');
        }
    });
}) ();
",CClientScript::POS_READY);
?>
<div id="flow-macro-container">
    <div id="flow-macros" style='text-align:center;'>
        <?php
        echo "<div id='flow-macro-error-response' class='error-message' 
            style='display:none;'></div>";
        echo X2Html::form('flow-macro-form');
        echo X2Html::hiddenField('modelType', $modelType);
        echo X2Html::hiddenField('modelId', $modelId);
        echo X2Html::dropDownList(
            'flowId', '', $flows, 
            array('empty' => Yii::t('admin','Select a workflow'), 'id'=>'flow-macro-flow-id'));
        echo "<div id='flow-macros-description-box'></div>";
        echo X2Html::ajaxSubmitButton(
            Yii::t('app','Execute'), 
            Yii::app()->controller->createUrl('/studio/executeMacro'), array(
                'beforeSend' => 'function(){ 
                    $("#flow-macro-error-response").html(""); 
                    x2.forms.inputLoading($("#form-macro-submit")); 
                }',
                'success' => 'function(data){ 
                    $("#flow-macro-error-response").hide(); 
                }',
                'error' => 'function(data){ 
                    $("#flow-macro-error-response").html(data.responseText).show(); 
                }',
                'complete' => 'function(){ 
                    x2.forms.inputLoadingStop($("#form-macro-submit")); 
                }',
            ), 
            array(
                'id' => 'form-macro-submit',
                'class' => 'x2-button',
                'style' => 'display:inline',
            ));
        echo X2Html::endForm();
        ?>
    </div>
</div>
