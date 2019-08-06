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




Tours::loadTips('studio.flowEditor');

/*
  $goto = Yii::app()->controller->attachBehavior('GotoMeetingBehavior', new GotoMeetingBehavior);
  $goto->initialize(array(
  'consumerKey' => 'yvgggPiLwWJ7jnnuoFS0yqKmW6jGiaW2',
  'consumerSecret' => '3bPMT49GsNq6TDgr',
  ));
 * $goto->getLoginUrl()
 */

$miscLayoutSettings = Yii::app()->params->profile->miscLayoutSettings;

$showLabels = isset($miscLayoutSettings['x2flowShowLabels']) ? $miscLayoutSettings['x2flowShowLabels'] : true;

$modelsWithInsertableAttrs = X2Model::$modelsWithInsertableAttributes;
$insertableAttributes = array();
foreach ($modelsWithInsertableAttrs as $modelName) {
    $insertableAttributes[$modelName] = array();
    foreach (X2Model::model($modelName)->attributeLabels() as $fieldName => $label) {
        $insertableAttributes[$modelName][$label] = '{' . $fieldName . '}';
    }
}

$translations = array(
    'idHintX2FlowWait' =>
    Yii::t('app', 'This ID is unique to this wait action. Workflows that pause at this wait action will resume at the wait action with this ID.'),
    'idHintX2FlowEmail' =>
    Yii::t('studio', 'This ID is unique to this email. Conditions checking for email opens can refer to this ID'),
    'idHintX2FlowRecordEmail' =>
    Yii::t('studio', 'This ID is unique to this email. Conditions checking for email opens can refer to this ID'),
    'templateChangeConfirm' =>
    Yii::t('app', 'Note: you have entered text into the email that will be lost.' .
            ' Are you sure you want to continue?'),
    'targetedContentTriggerChange' =>
    Yii::t('app', 'Note: you have entered text into the default content editor that will ' .
            'be lost. Are you sure you want to continue?'),
    'targetedPageTriggerChange' =>
    Yii::t('app', 'Note: you have entered text into the default content editor that will ' .
            'be lost. Are you sure you want to continue?'),
);

Yii::app()->clientScript->registerPackages(array(
    'X2Fields' => array(
        'baseUrl' => Yii::app()->request->baseUrl,
        'js' => array(
            'js/X2Fields.js',
        ),
        'depends' => array('auxlib'),
    ),
    'X2FlowFields' => array(
        'baseUrl' => Yii::app()->request->baseUrl,
        'js' => array(
            'js/X2FlowFields.js',
        ),
        'depends' => array('X2Fields', 'auxlib'),
    ),
        ), true);

// Declare variables to pass to JS files
$passVarsToClientScript = '
    x2.flow = {};
    x2.flow_counter = {};
    x2.flow.modelClass = "' . $model->modelClass . '";
    x2.flow.translations = {};
    x2.flow.requiresCron = ' . CJSON::encode($requiresCron) . ';
    x2.flow.showLabels = ' . ($showLabels ? 'true' : 'false') . ';
    x2.flow.insertableAttributes = ' . CJSON::encode($insertableAttributes) . ';   
    x2.flowData = ' . CJSON::encode($model->flow) . ';
    x2.fieldUtils = new x2.FlowFields ({
        operatorList: ' . CJSON::encode(X2FlowTrigger::getFieldComparisonOptions()) . ',
        visibilityOptions: ' . CJSON::encode(array(
            array(1, Yii::t('app', 'Public')),
            array(0, Yii::t('app', 'Private')),
            array(2, Yii::t('app', 'User\'s Groups'))
        )) . ',
        allTags: ' . CJSON::encode(Tags::getAllTags()) . ',
        templateSelector: "#condition-templates"
    });' .
        'x2.actionModelTriggers = JSON.parse(' . CJSON::encode(json_encode(X2FlowTrigger::getActionModelTriggers())) . ');
    x2.userModelTriggers = JSON.parse(' . CJSON::encode(json_encode(X2FlowTrigger::getUserModelTriggers())) . ');
    x2.processModelTriggers = JSON.parse(' . CJSON::encode(json_encode(X2FlowTrigger::getProcessModelTriggers())) . ');
    x2.recordModelTriggers = JSON.parse(' . CJSON::encode(json_encode(X2FlowTrigger::getRecordModelTriggers())) . ');
        
    x2.processModelActions = JSON.parse(' . CJSON::encode(json_encode(X2FlowAction::getProcessModelActions())) . ');
    x2.recordModelActions = JSON.parse(' . CJSON::encode(json_encode(X2FlowAction::getRecordModelActions())) . ');
    x2.test = JSON.parse(' . CJSON::encode(json_encode(array_keys(CActiveRecord::model("Locations")->getAttributes()))) .
        ');
';

// pass array of predefined theme uploadedBy attributes to client
foreach ($translations as $key => $val) {
    $passVarsToClientScript .= "x2.flow.translations['" .
            $key . "'] = '" . addslashes($val) . "';\n";
}

// Include variable script
Yii::app()->clientScript->registerScript('passVarsToX2FlowScript', $passVarsToClientScript, CClientScript::POS_END);



$assets = Yii::app()->getAssetManager()->publish(
        Yii::getPathOfAlias('application.extensions.CJuiDateTimePicker') . DIRECTORY_SEPARATOR . 'assets'
);

// Url paths
$baseUrl = Yii::app()->getBaseUrl();
$themeBaseUrl = Yii::app()->theme->getBaseUrl() . '/css';
$workflowJsUrl = $baseUrl . '/js/X2Flow';

/**
 * JavaScript scripts
 */
$cs = Yii::app()->getClientScript();

$cs->registerPackage('emailEditor');

$cs->registerCssFile($assets . '/jquery-ui-timepicker-addon.css');
$cs->registerScriptFile($assets . '/jquery-ui-timepicker-addon.js', CClientScript::POS_END);

$cs->registerCssFile($themeBaseUrl . '/x2flow.css');
$cs->registerScriptFile($themeBaseUrl . '/listview/jquery.yiigridview.js');

$cs->registerScriptFile($workflowJsUrl . '/X2FlowItem.js', CClientScript::POS_END);
$cs->registerScriptFile($workflowJsUrl . '/X2FlowEditor.js', CClientScript::POS_END);
$cs->registerScriptFile($workflowJsUrl . '/X2FlowApiCall.js', CClientScript::POS_END);

/**
 * Action Menu UI
 */
$this->actionMenu = array(
    array(
        'label' => Yii::t('studio', 'Manage Workflows'),
        'url' => array('flowIndex')
    )
);

if ($model->isNewRecord) {
    $this->actionMenu[] = array('label' => Yii::t('studio', 'Create Workflow'));
} else {
    $this->actionMenu[] = array('label' => Yii::t('studio', 'Create Workflow'), 'url' => array('flowDesigner'));
    $this->actionMenu[] = array('label' => Yii::t('module', 'Update'));
    $this->actionMenu[] = array('label' => Yii::t('module', 'Delete'), 'url' => '#', 'linkOptions' => array('csrf' => true, 'submit' => array('deleteFlow', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?')));
}

$this->actionMenu[] = array(
    'label' => Yii::t('studio', 'All Trigger Logs'),
    'url' => array('triggerLogs')
);

if (!$model->isNewRecord) {
    $this->actionMenu[] = array(
        'label' => Yii::t('studio', 'Export Workflow'),
        'url' => array('exportFlow', 'flowId' => $model->id),
    );
}

$this->actionMenu[] = array(
    'label' => Yii::t('studio', 'Import Workflow'),
    'url' => array('importFlow'),
);

/**
 * Top actions bar
 */
?>
<div class="page-title icon x2flow">
    <h2>
<?php echo $model->isNewRecord ? Yii::t('studio', 'Create Workflow') : Yii::t('admin', 'Update Workflow');
?>
    </h2>
    <a class="x2-button highlight right" id="save-button" href="javascript:void(0);">
<?php echo Yii::t('app', 'Save'); ?>
    </a>
        <?php if (isset($triggerLogsDataProvider)): ?>
        <a class="x2-button right" id="show-trace-button" href="javascript:void(0);">
    <?php echo Yii::t('studio', 'Show Trigger Logs'); ?>
        </a>
        <?php endif; ?>
</div>

        <?php
        /**
         * Flow Fields
         */
        ?>
<div>
    <div id="x2flow-start" class="form x2flow-start">
<?php
$form = $this->beginWidget('CActiveForm', array('id' => 'submitForm',
    'enableAjaxValidation' => false));
echo $form->errorSummary($model);
echo X2Html::getFlashes();
?>

        <div class="row">
            <div class="cell">
                <?php
                asort($triggerTypes);
                $allTriggers = array_merge(array('x2flow-empty' => Yii::t(
                            'studio', 'Select a trigger')), $triggerTypes);
                echo $form->label($model, 'triggerType');
                echo CHtml::dropdownList('trigger-selector', '', $allTriggers, array('id' => 'trigger-selector'
                ));
                ?>
            </div>
            <div class="cell">
                <?php echo $form->label($model, 'active'); ?>
                <?php echo $form->dropdownList($model, 'active', array(1 => Yii::t(
                            'app', 'Yes'), 0 => Yii::t('app', 'No')));
                ?>
            </div>
            <div class="cell right" id='x2flow-show-labels-checkbox-container'>
                <input id='x2flow-show-labels-checkbox' type='checkbox' class='right'
                <?php echo ($showLabels ? "checked='checked'" : ''); ?>>
                <label for='x2flow-show-labels-checkbox' class='right'>
                <?php echo CHtml::encode(Yii::t('studio', 'Toggle Node Labels')); ?>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="cell">
                    <?php echo $form->label($model, 'name'); ?>
                    <?php echo $form->textField($model, 'name'); ?>
                    <?php echo $form->hiddenField($model, 'flow', array('id' => 'flowDataField')); ?>
            </div>
        </div>
        <div class="row" style="width:100%">
            <div class="cell" style="width:99%">
                <?php echo $form->label($model, 'description'); ?>
                <?php echo $form->textArea($model, 'description', array('style' => 'width:100%'));
                ?>
            </div>
        </div>
        <div id='targeted-content-embed-code-container'
                <?php echo ($model->triggerType !== 'TargetedContentRequestTrigger') ?
                        'style="display: none;"' : '';
                ?> class='row'>

            <h4><?php echo Yii::t('app', 'Embed Code:'); ?></h4>
            <span class='x2-hint'
                  title='<?php
             echo Yii::t('app', 'The web content returned by this flow will replace the embed '
                     . 'code when a visitor comes to your page. Use the Push Web'
                     . ' Content flow action to create targeted web content.');
             ?>'>&nbsp;[?]</span>
            <p>
                  <?php
                  echo Yii::t('app', 'Copy and paste this code into your website.');
                  ?>
            </p>
            <textarea <?php
                echo (!isset($model->id) ? // flow not yet saved
                        'disabled="true" placeholder="' .
                        Yii::t('app', 'Saving the flow will generate an embed code') . '"' : '');
                ?>><?php
                if (isset($model->id)) { // flow has been saved
                    echo "<script type='text/javascript' src='" .
                    Yii::app()->createExternalUrl('/api/targetedContent') . '?flowId=' . $model->id .
                    "'></script>";
                }
                ?></textarea>
        </div>
                    <?php $this->endWidget(); ?>
    </div>
</div>

        <?php
        /**
         * Main flow stage
         */
        ?>
<div id="x2flow-stage">
    <div id="actions-bank">
        <h2 style="margin: 10px; color: #555555 !important;">Actions Bank</h2>
    <?php
    $actions = array_filter($actionTypes, function($element) {
        return in_array($element, array_merge(X2FlowAction::getAnyModelActions(), X2FlowAction::getRecordModelActions(), X2FlowAction::getProcessModelActions()));
    });
    
    /**
     * Actions bank
     */
    ?>
        <div id="all" class="actions">
            <div class="x2flow-node X2FlowSwitch">
                <span><?php echo Yii::t('studio', 'Condition'); ?></span>
                <div class="icon">
                    <div class="x2flow-yes-label"><?php echo Yii::t('app', 'Yes') ?></div>
                    <div class="x2flow-no-label"><?php echo Yii::t('app', 'No') ?></div>
                </div>
                <div class="x2flow-branch-wrapper">
                    <div class="x2flow-branch">
                        <div class="bracket"></div>
                        <div class="x2flow-node x2flow-empty"></div>
                    </div>
                    <div class="x2flow-branch">
                        <div class="bracket"></div>
                        <div class="x2flow-node x2flow-empty"></div>
                    </div>
                </div>
            </div>
            <div class="x2flow-node X2FlowSplitter">
                <span><?php echo Yii::t('studio', 'Split Path'); ?></span>
                <div class="icon">
                    <div class="icon-inner">
                    </div>
                </div>
                <div class="x2flow-branch-wrapper">
                    <div class="x2flow-branch">
                        <div class="bracket"></div>
                        <div class="x2flow-node x2flow-empty"></div>
                    </div>
                    <div class="x2flow-branch">
                        <div class="bracket"></div>
                        <div class="x2flow-node x2flow-empty"></div>
                    </div>
                </div>
            </div>

<?php
foreach ($actions as $type => $title):
    ?>

                <div class="x2flow-node x2flow-action
                <?php echo $type . ($showLabels ? "" : " no-label") ?>"
                     title="<?php echo addslashes(Yii::t('studio', $title)) ?>"
                <?php
                echo ((($type === 'X2FlowPushWebContent' && $model->triggerType !== 'TargetedContentRequestTrigger') || ($type === 'X2FlowPushWebPage' && $model->triggerType !== 'TargetedPageRequestTrigger')) ? 'style="display: none;"' : '')
                ?>>
                    <div class="x2flow-icon-label"
                <?php echo ($showLabels ? "" : "style='display: none;'") ?>>
                     <?php echo Yii::t('studio', $title) ?>
                    </div>
                    <span> <?php echo Yii::t('studio', $title) ?> </span>
                </div>
                         <?php
                     endforeach;
                     ?>
        </div>
    </div>

    <div class="form x2flow-main" id="x2flow-main">  
        <div class="x2flow-node x2flow-trigger x2flow-empty
<?php echo ($showLabels ? "" : " no-label"); ?>" id="trigger"
             title="<?php echo addslashes(Yii::t('studio', 'Select a trigger')); ?>">
            
            <div class="x2flow-icon-label"
<?php echo ($showLabels ? "" : "style='display: none;'") ?>>
            </div>
        </div>
        <div class="x2flow-branch">
            <div class="bracket hidden"></div>
            <div class="x2flow-node x2flow-empty"></div>
        </div>
    </div>

</div>

<?php
/*
 * Node config area 
 */
?>
<div class="row" style="width:100%">
    <div class="form cell" id="x2flow-config-box">
        <div id="x2flow-main-config"></div>
        <hr id="x2flow-conditions-hr" style="margin: 5px 0px 5px 0px;">
        <div id="x2flow-conditions" class="x2-sortlist"><ol></ol></div>
        <div id="x2flow-attributes" class="x2-sortlist">
            <label class='x2flow-api-attributes-section-header' style='display: none;'><?php echo Yii::t('studio', 'Attributes:'); ?></label>
            <ol></ol>
        </div>
        <div id="x2flow-headers" class="x2-sortlist">
            <label class='x2flow-api-attributes-section-header' style='display: none;'><?php echo Yii::t('studio', 'Headers:'); ?></label>
            <ol></ol>
        </div>
        <div style="padding-top: 5px; padding-bottom: 5px;">
<?php
echo CHtml::dropdownList(
        'type', '', X2FlowTrigger::getGenericConditions(), array(
    'id' => 'x2flow-condition-type',
    'style' => 'display:none;height:30px;margin-right:10px;'
));
echo CHtml::button(
        Yii::t('studio', 'Add Condition'), array(
    'id' => 'x2flow-add-condition',
    'class' => 'x2-button',
    'style' => 'display:none;height:30px;padding-top:3px;'
    . 'padding-bottom:3px;padding-left:20px;padding-right:20px;'
));
?>
        </div>
            <?php
            echo CHtml::button(
                    Yii::t('studio', 'Add Attribute'), array(
                'id' => 'x2flow-add-attribute',
                'class' => 'x2-button',
                'style' => 'display:none;'
            ));
            echo CHtml::button(
                    Yii::t('studio', 'Add Header'), array(
                'id' => 'x2flow-add-header',
                'class' => 'x2-button',
                'style' => 'display:none;'
            ));
            ?>
    </div>
</div>

        <?php
        if (isset($triggerLogsDataProvider) && isset($model->id)) {
            $this->renderPartial(
                    '_triggerLogsGridView', array(
                'triggerLogsDataProvider' => $triggerLogsDataProvider,
                'flowId' => $model->id,
                'parentView' => 'flowEditor'
                    )
            );
        }
        ?>

<!-- HTML templates -->
<div id="item-delete"></div>
<div id="condition-templates" style="display:none; margin-top: 15px;">
    <ol>
        <li>
            <fieldset></fieldset>
            <a href="javascript:void(0)" class="del"></a>
        </li>
    </ol>
    <div class="cell x2fields-attribute">
        <!--<label><?php echo Yii::t('studio', 'Attribute'); ?></label>-->
        <select name="attribute"></select>
    </div>
    <div class="cell x2fields-operator">
        <!--<label><?php echo Yii::t('studio', 'Comparison'); ?></label>-->
        <select name="operator"></select>
    </div>
    <div class="cell x2fields-value">
        <!--<label><?php echo Yii::t('studio', 'Value'); ?></label>-->
        <input type="text" />
    </div>
    <fieldset class="API_params">
        <div class="cell x2fields-attribute">
            <label><?php echo Yii::t('studio', 'Name'); ?></label>
            <input type="text" name="attribute" />
        </div>
        <div class="cell x2fields-value">
            <label><?php echo Yii::t('studio', 'Value'); ?></label>
            <input type="text" name="value" />
        </div>
    </fieldset>
    <fieldset class="APIHeaders">
        <div class="cell x2fields-attribute">
            <label><?php echo Yii::t('studio', 'Name'); ?></label>
            <input type="text" name="attribute" />
        </div>
        <div class="cell x2fields-value">
            <label><?php echo Yii::t('studio', 'Value'); ?></label>
            <input type="text" name="value" />
        </div>
    </fieldset>
</div>
<?php
// workflow status condition is handled as a special case

$workflows = Workflow::getList(false); // no "none" options
$workflowIds = array_keys($workflows);
$stages = count($workflowIds) ?
        Workflow::getStagesByNumber($workflowIds[0]) : array('---');
?>
<div id="workflow-condition-template" style="display:none; margin-top: 15px;">
    <ol>
        <li>
            <fieldset>
                <div class="cell x2fields-workflow-id">
                    <div class="cell inline-label"><?php echo Yii::t('studio', 'Process: '); ?></div>
<?php
echo CHtml::dropDownList('workflowId', '', $workflows, array('id' => false));
?> 
                </div>
                <div class="cell x2fields-workflow-stage-number">
                    <div class="cell inline-label"><?php echo Yii::t('studio', 'Stage: '); ?></div>
<?php
echo CHtml::dropDownList('stageNumber', '', $stages, array('id' => false));
?> 
                </div>
                <div class="cell x2fields-workflow-stage-state">
                    <div class="cell inline-label"><?php echo Yii::t('studio', 'State: '); ?></div>
                        <?php
                        echo CHtml::dropDownList('stageState', '', array(
                            'completed' => Yii::t('admin', 'Completed'),
                            'started' => Yii::t('admin', 'Started'),
                            'notStarted' => Yii::t('admin', 'Not Started'),
                            'notCompleted' => Yii::t('admin', 'Not Completed'),
                                ), array('id' => false));
                        ?> 
                </div>
            </fieldset>
            <a href="javascript:void(0)" class="del"></a>
        </li>
    </ol>
</div>
<!-- end templates l -->
