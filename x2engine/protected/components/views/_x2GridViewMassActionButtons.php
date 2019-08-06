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




/*
Parameters:
    massActions - array of strings - list of available mass actions to select from
    gridId - the id property of the X2GridView instance
    modelName - the modelName property of the X2GridView instance
    selectedAction - string - if set, used to select option from mass actions dropdown
    gridObj - object - the x2gridview instance
*/

Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2GridView/X2GridViewMassActionsManager.js', CClientScript::POS_END);

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/views/massActions.css');

Yii::app()->clientScript->registerResponsiveCss ('massActionsCssResponsive', "

@media (max-width: 657px) {
    .x2-gridview-mass-action-buttons {
        position: absolute;
        width: 137px;
        top: -41px;
        right: -179px;
        margin: 0px;
    }
    .show-top-buttons .x2-gridview-mass-action-buttons {
        right: -183px; 
    }
    body > .grid-view-more-drop-down-list.fixed-header {
        position: absolute;
    }
}

@media (min-width: 658px) {
    body > .grid-view-more-drop-down-list.fixed-header {
        position: fixed;
    }
}

");

// destroy mass action dialogs, save checks so that can be preserved through grid update
$beforeUpdateJSString = "
    x2.DEBUG && console.log ('beforeUpdateJSString');
     
    if ($.inArray ('tag', x2.".$namespacePrefix."MassActionsManager._massActions) !== -1) 
        x2.".$namespacePrefix."MassActionsManager.tagContainer.destructor ();
    
    
    $('.mass-action-dialog').each (function () {
        //x2.massActions.DEBUG && console.log ('destroying dialog loop');
        if ($(this).closest ('.ui-dialog').length) {
            //x2.massActions.DEBUG && console.log ('destroying dialog');
            $(this).dialog ('destroy');
            $(this).hide ();
        }
    });

    // save to preserve checks
    x2.".$namespacePrefix."MassActionsManager.saveSelectedRecords ();

    // show loading overlay to prevent grid view user interaction
    $('#".$gridId." .x2-gridview-updating-anim').show ();
";

$gridObj->addToBeforeAjaxUpdate ($beforeUpdateJSString);

// reapply event handlers and checks
$afterUpdateJSString = "
    if (typeof x2.".$namespacePrefix."MassActionsManager !== 'undefined') 
        x2.".$namespacePrefix."MassActionsManager.reinit (); 
    $('#".$gridId." .x2-gridview-updating-anim').hide ();
";

$gridObj->addToAfterAjaxUpdate ($afterUpdateJSString);

foreach ($massActionObjs as $obj) {
    $obj->registerPackages ();
}

Yii::app()->clientScript->registerScript($namespacePrefix.'massActionsInitScript',"
    if (typeof x2.".$namespacePrefix."MassActionsManager === 'undefined') {
        x2.".$namespacePrefix."MassActionsManager = new x2.GridViewMassActionsManager ({
            massActions: ".CJSON::encode ($massActions).",
            gridId: '".$gridId."',
            namespacePrefix: '".$namespacePrefix."',
            gridSelector: '#".$gridId."',
            fixedHeader: ".($fixedHeader ? 'true' : 'false').",
            massActionUrl: '".Yii::app()->request->getScriptUrl () . '/' . 
                lcfirst ($gridObj->moduleName) .  '/x2GridViewMassAction'."',
             
            updateFieldInputUrl: '".Yii::app()->request->getScriptUrl () . '/' . 
                lcfirst ($gridObj->moduleName) .  '/getX2ModelInput'."',
             
            modelName: '".$modelName."',
            paramsByClass: ".CJSON::encode (array_combine (
                array_map (function ($obj) { return get_class ($obj); }, $massActionObjs),
                array_map (function ($obj) { return $obj->getJSClassParams (); }, $massActionObjs)
            )).",
            translations: ".CJSON::encode (array (
                'deleteprogressBarDialogTitle' => Yii::t('app', 'Mass Deletion in Progress'),
                'updateFieldprogressBarDialogTitle' => Yii::t('app', 'Mass Update in Progress'),
                'progressBarDialogTitle' => Yii::t('app', 'Mass Action in Progress'),
                'deleted' => Yii::t('app', 'deleted'),
                'tagged' => Yii::t('app', 'tagged'),
                'added' => Yii::t('app', 'added'),
                'updated' => Yii::t('app', 'updated'),
                'removed' => Yii::t('app', 'removed'),
                'executed' => Yii::t('app', 'executed'),
                'doubleConfirmDialogTitle' => Yii::t('app', 'Confirm Deletion'),
                'addedItems' => Yii::t('app', 'Added items to list'),
                'addToList' => Yii::t('app', 'Add selected to list'),
                'removeFromList' => Yii::t('app', 'Remove selected from list'),
                'newList' => Yii::t('app', 'Create new list from selected'),
                'moveToFolder' => Yii::t('app', 'Move selected messages'),
                'moveOneToFolder' => Yii::t('app', 'Move message'),
                'moveFileSysObjToFolder' => Yii::t('app', 'Move selected'),
                'moveFileSysObjOneToFolder' => Yii::t('app', 'Move selected'),
                'renameFileSysObj' => Yii::t('app', 'Rename'),
                'move' => Yii::t('app', 'Move'),
                'add' => Yii::t('app', 'Add to list'),
                'remove' => Yii::t('app', 'Remove from list'),
                'rename' => Yii::t('app', 'Rename'),
                'noticeFlashList' => Yii::t('app', 'Mass action exectuted with'),
                'errorFlashList' => Yii::t('app', 'Mass action exectuted with'),
                'noticeItemName' => Yii::t('app', 'warnings'),
                'errorItemName' => Yii::t('app', 'errors'),
                'successItemName' => Yii::t('app', 'Close'),
                'blankListNameError' => Yii::t('app', 'Cannot be left blank'),
                'passwordError' => Yii::t('app', 'Password cannot be left blank'),
                'close' => Yii::t('app', 'Close'),
                'cancel' => Yii::t('app', 'Cancel'),
                'create' => Yii::t('app', 'Create'),
                'pause' => Yii::t('app', 'Pause'),
                'stop' => Yii::t('app', 'Stop'),
                'resume' => Yii::t('app', 'Resume'),
                'complete' => Yii::t('app', 'Complete'),
                'tag' => Yii::t('app', 'Tag'),
                'untag' => Yii::t('app', 'Remove tag'),
                'update' => Yii::t('app', 'Update'),
                'execute' => Yii::t('app', 'Execute'),
                'tagSelected' => Yii::t('app', 'Tag selected'),
                'untagSelected' => Yii::t('app', 'Remove tags from selected'),
                'deleteSelected' => Yii::t('app', 'Delete selected'),
                'macroExecute' => Yii::t('app','Execute macro'),
                'delete' => Yii::t('app', 'Delete'),
                'updateField' => Yii::t('app', 'Update fields of selected'),
                'emptyTagError' => Yii::t('app', 'At least one tag must be included'),
                'emptyUntagError' => Yii::t('app', 'At least one tag must be specified'),
                'emptyMacroError' => Yii::t('app','You must select a macro to be executed'),
                'add' => Yii::t('app', 'Add'),
                'MassPublishActionDialogTitle' => Yii::t('app', 'Add Action'),
                'MassPublishActionGoButton' => Yii::t('app', 'Add'),
                'MassPublishNoteDialogTitle' => Yii::t('app', 'Add Note'),
                'MassPublishNoteGoButton' => Yii::t('app', 'Add'),
                'MassPublishCallDialogTitle' => Yii::t('app', 'Log Call'),
                'MassPublishCallGoButton' => Yii::t('app', 'Log'),
                'MassPublishTimeDialogTitle' => Yii::t('app', 'Log Time'),
                'MassPublishTimeGoButton' => Yii::t('app', 'Log'),
                'MassConvertRecordDialogTitle' => Yii::t('app', 'Convert Records'),
                'MassConvertRecordGoButton' => Yii::t('app', 'Convert'),
                'MassConvertRecordGoButtonForce' => Yii::t('app', 'Convert Anyway'),
                'MassAddRelationshipDialogTitle' => Yii::t('app', 'Add Relationships'),
                'MassAddRelationshipGoButton' => Yii::t('app', 'Add Relationships'),
            )).",
            expandWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                '/images/icons/Expand_Widget.png'."',
            collapseWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                '/images/icons/Collapse_Widget.png'."',
            closeWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                '/images/icons/Close_Widget.png'."',
            progressBarDialogSelector: '#$namespacePrefix-progress-dialog',
            enableSelectAllOnAllPages: ".
                ($gridObj->enableSelectAllOnAllPages ? 'true' : 'false').",
            totalItemCount: {$gridObj->dataProvider->totalItemCount},
            idChecksum: '$idChecksum',
        });
    } else {
        // grid was refreshed, total item count may have changed
        x2.{$namespacePrefix}MassActionsManager.totalItemCount = 
            {$gridObj->dataProvider->totalItemCount};
        x2.{$namespacePrefix}MassActionsManager.idChecksum = 
            '$idChecksum';
    }
", CClientScript::POS_END);

?>

<span class='x2-gridview-mass-action-outer'>

<div id='<?php echo $gridId; ?>-mass-action-buttons' class='x2-gridview-mass-action-buttons' style='display: none;'>
    <?php
    // TODO: check if buttons are present before rendering button set
    $massActionButtons = true;
    if ($massActionButtons) {
    ?>
    <div class='mass-action-button-set x2-button-group'>
        <?php
        foreach ($massActionObjs as $obj) {
            $obj->renderButton ();
        }
        ?>
    </div>

    <?php
    }
    if (count ($massActionObjs) > 1) {
    ?>
        <div class='mass-action-more-button-container'>
            <button class='mass-action-more-button x2-button'  
             title='<?php echo Yii::t('app', 'More mass actions'); ?>'>
                <span class='more-button-label'>
                    <?php echo Yii::t('app', 'More'); ?>
                </span>
                <img class='more-button-arrow' 
                 src='<?php echo Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Collapse_Widget.png'; ?>' />
            </button>
        </div>
        <ul 
         id='<?php echo $gridId; ?>more-drop-down-list'
         style='display: none;'
         class="grid-view-more-drop-down-list<?php echo ($fixedHeader ? ' fixed-header' : ''); ?>"> 
        <?php
        usort ($massActionObjs, array ('X2GridViewBase', 'massActionLabelComparison'));
        foreach ($massActionObjs as $obj) {
            $obj->renderListItem ();
        }
    }
    ?>
    </ul>
    <?php
    foreach ($massActionObjs as $obj) {
        $obj->renderDialog ($gridId, $modelName);
    }
    ?>
</div>

</span>
<?php
if (isset ($modelName)) {
?>
<div class='mass-action-dialog double-confirmation-dialog' style='display: none;'>
    <span><?php 
        echo Yii::t(
            'app', 'You are about to delete {count} {recordType}.', 
            array (
                '{recordType}' => X2Model::getRecordName ($modelName, true),
                '{count}' => '<b>'.$gridObj->dataProvider->totalItemCount.'</b>',
            ));
        echo Yii::t('app', 'This action cannot be undone.'); 
        ?>
        </br>
        </br>
        <?php
        echo Yii::t('app', 'Please enter your password to confirm that you want to '.
            'delete the selected records.'); 
    ?></span>
    </br>
    </br>
    <?php
    echo CHtml::label (Yii::t('app', 'Password:'), 'LoginForm[password]');
    ?>
    <input name="password" type='password'>
</div>
<?php
}
?>
<div id='<?php echo $namespacePrefix; ?>-progress-dialog' class='progress-dialog mass-action-dialog'
 style='display: none;'>
<?php
    $this->widget ('X2ProgressBar', array (
        'uid' => $gridObj->namespacePrefix,
        'max' => $gridObj->dataProvider->totalItemCount,
        'label' => '',
    ));
?>
<div class='super-mass-action-feedback-box'>
</div>
</div>
