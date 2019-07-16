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
<div class='stage-member-container stage-member-id-<?php echo $data['id']; 
 ?> stage-member-type-<?php echo $recordType; ?>'> 

<?php
$modelName = X2Model::getModelName ($recordType);
?>

<div class='stage-icon-container' 
 title='<?php echo Yii::t('workflow', '{recordName}', array ('{recordName}' => $modelName)); ?>'>
    <img src='<?php 
  if(file_exists(substr(Yii::app()->theme->getBaseUrl() . '/images/workflow_stage_' . $recordType .'.png',1))) {
             echo Yii::app()->theme->getBaseUrl() . '/images/workflow_stage_' . $recordType .
             '.png';
         } else {
             echo Yii::app()->theme->getBaseUrl() . '/images/workflow_stage_model.png';
         }
         ?>' 
     class='stage-member-type-icon left' alt=''>
</div>
<div class='stage-member-name left'><?php 
    if (!$dummyPartial) {
        echo X2Model::getModelLinkMock (
            $modelName,
            $data['nameId'],
            array (
                'data-qtip-title' => $data['name']
            )
        );
    }
?></div>
<div class='stage-member-button-container'>
    <a class='stage-member-button complete-stage-button right x2-button x2-minimal-button' 
     style='display: none;' title='<?php echo Yii::t('app', 'Complete Stage'); ?>'>&gt;</a>
    <a class='stage-member-button undo-stage-button x2-button x2-minimal-button right' 
     style='display: none;' title='<?php echo Yii::t('app', 'Undo Stage'); ?>'>&lt;</a>
    <a class='stage-member-button edit-details-button right' style='display: none;'
     title='<?php echo Yii::t('app', 'View/Edit Workflow Details'); ?>'>
        <span class='x2-edit-icon'></span>
    </a>
</div>
    <div class='stage-member-info'>
        <?php if($workflow->financial && $workflow->financialModel === $recordType){ ?>
        <span class='stage-member-value'>
            <?php
            if (!$dummyPartial && array_key_exists($workflow->financialField, $data)) {
                echo Yii::app()->locale->numberFormatter->formatCurrency(
                        $data[$workflow->financialField],
                        Yii::app()->params->currency);
            }
            ?>
        </span>
        <?php } ?>
    </div>
    
    
</div>
