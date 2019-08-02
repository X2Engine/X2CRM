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




Yii::app()->clientScript->registerScriptFile(
    Yii::app()->request->baseUrl.'/js/WorkflowDragAndDropSortable.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/WorkflowManagerBase.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/DragAndDropViewManager.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->request->baseUrl.'/js/QtipManager.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->request->baseUrl.'/js/X2GridView/X2GridViewQtipManager.js', CClientScript::POS_END);

$listItemColors = Workflow::getPipelineListItemColors ($colors, true);
$listItemColorCss = '';
for ($i = 1; $i <= count ($listItemColors); ++$i) {
    $listItemColorCss .= 
    "#workflow-stage-$i .stage-member-container {
        background-color: ".$listItemColors[$i - 1][0].";
    }
    #workflow-stage-$i .stage-member-container:hover {
        background-color: ".$listItemColors[$i - 1][1].";
    }";
}
Yii::app()->clientScript->registerCss('stageMemberColorCss',$listItemColorCss);


$stages = $model->stages;

Yii::app()->clientScript->registerScript('dragAndDropScript',"
x2.dragAndDropViewManager = new x2.DragAndDropViewManager ({
    workflowId: ".$model->id.",
    currency: '".Yii::app()->params->currency."',
    stageCount: ".count ($stages).",
    connectWithClass: '.stage-members-container',
    memberListContainerSelectors: ".CJSON::encode ($memberListContainerSelectors).",
    memberContainerSelector: '.stage-member-container',
    memberContainerSelector: '.stage-member-container',
    moveFromStageAToStageBUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/moveFromStageAToStageB')."',
    completeStageUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/completeStage')."',
    revertStageUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/revertStage')."',
    startStageUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/ajaxAddADeal')."',
    ajaxGetModelAutocompleteUrl: '".
        Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete')."',
    stagePermissions: ".CJSON::encode ($stagePermissions).",
    stagesWhichRequireComments: ".CJSON::encode ($stagesWhichRequireComments)." ,
    stageNames: ".CJSON::encode ($stageNames).",
    translations: ".CJSON::encode (array (
        'Stage {n}' => addslashes (Yii::t('workflow', 'Stage {n}')),
        'Save' => addslashes (Yii::t('app', 'Save')),
        'Loading...' => addslashes (Yii::t('app', 'Loading...')),
        'deal' => addslashes (Yii::t('app', 'deal')),
        'deals' => addslashes (Yii::t('app', 'deals')),
        'Submit' => addslashes (Yii::t('app', 'Submit')),
        'Comments Required' => addslashes (Yii::t('app', 'Comments Required')),
        'Add a Deal' => addslashes (Yii::t('app', 'Add a Deal')),
        'Edit' => addslashes (Yii::t('app', 'Edit')),
        'Cancel' => addslashes (Yii::t('app', 'Cancel')),
        'Close' => addslashes (Yii::t('app', 'Close')),
        'No results found.' => addslashes (Yii::t('app', 'No results found.')),
        'addADealError' => addslashes (Yii::t('app', 'Deal could not be added: ')),
        'permissionsError' => addslashes (
                Yii::t('workflow', 'You do not have permission to perform that stage change.'))
    )).",
    getStageDetailsUrl: '".CHtml::normalizeUrl(array('/workflow/workflow/getStageDetails'))."',
    stageListItemColors: ".CJSON::encode (
        array_map (function ($a) { return $a[0]; }, $listItemColors))."
});

", CClientScript::POS_READY);


?>

<!-- dialog to contain Workflow Stage Details-->
<div id="workflowStageDetails"></div>

<!-- used to set up the add a deal form -->
<div id="add-a-deal-form-dialog" style="display: none;" class='form'>
    <form><!-- submitted via ajax, so it doesn't need a CSRF token hidden input -->
    <div class='dialog-description'>
        <?php echo Yii::t(
            'workflow', 'Start the {workflowName} {process} for the following record:', array (
                '{workflowName}' => CHtml::encode($model->name),
                '{process}' => Modules::displayName(false),
            )); ?> 
    </div>
    <div id='record-name-container'>
        <?php
        echo CHtml::label(Yii::t('app', 'Record Name'),'recordName'); 
        X2Model::renderModelAutocomplete ('Contacts');
        ?>
        <input type="hidden" id='new-deal-id' name="newDealId">
    </div>
    <?php
    echo CHtml::label(Yii::t('app', 'Record Type'),'modelType');
    echo CHtml::dropDownList('modelType',$modelType,array(
        'Contacts'=>Yii::t('workflow','{contacts}', array(
            '{contacts}'=>Modules::displayName(true, "Contacts")
        )),
        'Opportunity'=>Yii::t('workflow','{opportunities}', array(
            '{opportunities}'=>Modules::displayName(true, "Opportunities")
        )),
        'Accounts'=>Yii::t('workflow','{accounts}', array(
            '{accounts}'=>Modules::displayName(true, "Accounts")
        )),
    ),array(
        'id'=>'new-deal-type'
    ));
    ?>
    </form>
</div>

<div id='workflow-filters-container' style="display: none;" class='pipeline-view'>
<?php
$this->renderPartial ('_processStatus', array (
    'dateRange' => $dateRange,
    'model' => $model,
    'modelType' => $modelType,
    'users' => $users,
    'parentView' => '_dragAndDropView'
));
?>
</div>


<div id='stage-member-list-container-top-scrollbar-outer'><div id='stage-member-list-container-top-scrollbar'><div></div></div></div>
<div id='stage-member-lists-container' class='x2-layout-island x2-layout-island-merge-top clearfix'>
    <div id='stage-member-lists-container-inner'>
<?php
$modelTypes = array_flip (X2Model::$associationModels);
$recordNames = X2Model::getAllRecordNames ();
?>
<div id='stage-member-prototype' style='display: none;'>
<?php
// render a dummy item view so that it can be cloned on the client
$this->renderpartial ('_dragAndDropItemView', array (
    'data' => array (
        'id' => null,
        'name' => null,
    ),
    'recordNames' => $recordNames,
    'dummyPartial' => true,
    'recordType' => 'contacts',
    'workflow' => $model,
));
?>
</div>
<?php

$colorGradients = array (); 

for ($i = 0; $i < sizeof ($colors); $i++) {
    list($r,$g,$b) = X2Color::hex2rgb2 ($colors[$i][0]);
    list($r2,$g2,$b2) = X2Color::hex2rgb2 ($colors[$i][1]);
    $colorStr1 = "rgba($r, $g, $b, 0.65)";
    $colorStr2 = "rgba($r2, $g2, $b2, 0.65)";
    $colorGradients[] = 
       'background: '.$colors[$i][0].';
        background: -moz-linear-gradient(top,    '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: -webkit-linear-gradient(top,    '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: -o-linear-gradient(top,        '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: -ms-linear-gradient(top,        '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: linear-gradient(to bottom, '.$colorStr1.' 0%, '.$colorStr2.' 100%);';
}


for ($i = 0; $i < count ($stages); ++$i) {
    $stage = $stages[$i];
    ?>
    <div class='stage-members'>
    <div class='stage-member-staging-area'></div>
    <?php
    $this->widget ('zii.widgets.CListView', array (
        'pager' => array (
            /*'class' => 'CLinkPager',
            'header' => '',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'maxButtonCount' => 3,
            'htmlOptions' => array (
                'class' => 'button-group-pager'
            )*/
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.stage-member-container',
            'listViewId' => 'workflow-stage-'.($i + 1),
            'header' => '',
            'options'=>array(
                'onRenderComplete'=>'js:function(){
                    x2.dragAndDropViewManager.refresh ();
                }',
                'history' => false
            ),
        ),
        'id' => 'workflow-stage-'.($i + 1),
        'dataProvider' => $this->getStageMemberDataProvider ($modelType,
            $model->id, $dateRange, $i + 1, $users),
        'itemView' => '_dragAndDropItemView',
        'viewData' => array (
            'modelTypes' => $modelTypes,
            'recordNames' => $recordNames,
            'dummyPartial' => false,
            'recordType'=> $modelType,
            'workflow' => $model,
        ),
        'template' => 
            '<div class="stage-list-title" style="'.$colorGradients[$i].'">'.
                '<h2>'.$stage['name'].'</h2>
                <div class="stage-title-row">
                <div class="total-projected-stage-value">'.
                (is_null($stageValues[$i])?'':Formatter::formatCurrency ($stageValues[$i])).
                '</div>
                <div class="total-stage-deals">
                    <span class="stage-deals-num">'.$stageCounts[$i].'</span>
                    <span>'.
                        ($stageCounts[$i] === 1 ? 
                            Yii::t('workflow', 'deal') :
                            Yii::t('workflow', 'deals')).'</span>
                </div>
                <img class="workflow-stage-arrow" 
                 src="'.Yii::app()->theme->getBaseUrl ()."/images/workflowStageArrow.png".'" />
            </div>
            {pager}</div>{items}',
        'itemsCssClass' => 'items stage-members-container',
        'afterAjaxUpdate' => 'function (id, data) {
            x2.dragAndDropViewManager.refresh ();
        }'
    ));
    ?>
    </div>
    <?php
}

?>
    </div>
</div>
