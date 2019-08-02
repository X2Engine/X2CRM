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




 
Yii::import ('application.components.sortableWidget.components.GooglePlusResources');
 

$aliasTypeOptions = RecordAliases::model ()->getAliasTypeOptions ();

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/views/recordAliasesWidget.css');
if (Yii::app()->request->isSecureConnection) {
    Yii::app()->clientScript->registerScriptFile(
        'https://secure.skypeassets.com/i/scom/js/skype-uri.js');
} else {
    Yii::app()->clientScript->registerScriptFile(
        'http://www.skypeassets.com/i/scom/js/skype-uri.js');
}
Yii::app()->clientScript->registerScript('recordAliasesWidgetJS',"

$(function () {
    new x2.RecordAliasesWidget ({
        element: '.record-aliases-dropdown-container',
        aliasOptions: ".CJSON::encode ($aliasTypeOptions).",
        aliasTypeIcons: ".CJSON::encode ($aliasModel->getAllIcons ()).",
        baseUrl: yii.scriptUrl + 
            '/".Yii::app()->controller->module->name."/".Yii::app()->controller->module->name."',
         
        googlePlusIntegrationEnabled:".
            (GooglePlusResources::integrationIsEnabled () ? 'true' : 'false').",
         
        translations: ".CJSON::encode (array (
            'dialogTitle' => Yii::t('app', 'Create Social Profile'),
            'cancel' => Yii::t('app', 'Cancel'),
            'create' => Yii::t('app', 'Create'),
            'createProfile' => Yii::t('app', 'Add Profile'),
            'confirmDeletion' => Yii::t(
                'app', 'Are you sure you want to delete this Social Profile?'),
            'confirmDeletionTitle' => Yii::t('app', 'Delete Social Profile?'),
            'OK' => Yii::t('app', 'OK'),
            'skypeQtipLoadingText' => Yii::t('app', 'Loading...'),
            'googleSearchdialogTitle' => Yii::t('app', 'Find Google+ Profile'),
        )).",
        recordId: ".$this->model->id."
    });
});

", CClientScript::POS_END);

echo CHtml::tag(
    'button', 
    array(
        'id' => 'view-aliases-button',
        'class' => 'x2-button right view-aliases-button icon',
        'title' => Yii::t('app', 'Add Social Profile'),
    ), '');
?>
<div class='record-aliases-dropdown-container'>
    <ul class='alias-dropdown x2-dropdown-list' style='display: none;'>
        <span>
        <?php
        foreach ($this->getAliases () as $alias) {
        ?>
        <li data-alias-type='<?php echo CHtml::encode ($aliasTypeOptions[$alias->aliasType]); ?>'
         data-id='<?php echo $alias->id ?>'><?php 
            echo $alias->getIcon (true); 
            ?>
            <span class='record-alias'>
            <?php
                echo $alias->renderAlias ();
            ?>
            </span>
            <span class='delete-alias-button fa fa-times' 
             title="<?php echo CHtml::encode (Yii::t('app', 'Delete Social Profile')); ?>"></span>
            <?php
        ?></li>     
        <?php
        }
        ?>
        <li class='alias-template' style='display: none;'>
            <span class='record-alias'>
            </span>
            <span class='delete-alias-button fa fa-times' 
             title="<?php echo CHtml::encode (Yii::t('app', 'Delete Social Profile')); ?>"></span>
            <?php
        ?></li>     
        </span>
        <?php
         
        if (GooglePlusResources::integrationIsEnabled ()) {
        ?>
        <li class='find-google-plus-profile x2-button'><?php 
            echo CHtml::encode (Yii::t('app', 'Find Google+ Profile')) 
        ?></li>     
        <?php
        }
         
        ?>
        <li class='new-alias-button x2-button'><?php 
            echo CHtml::encode (Yii::t('app', 'Add Social Profile')) 
        ?></li>     
    </ul>
    <?php
    $form = $this->beginWidget('CActiveForm', array (
        'htmlOptions' => array (
            'class' => 'add-alias-dialog form2',
            'id' => 'record-alias-form',
            'style' => 'display: none;',
        ),
    ));
    echo $form->errorSummary ($aliasModel);
    ?>
        <?php
        echo $form->hiddenField ($aliasModel, 'recordId');
        echo $form->label ($aliasModel, 'alias', array (
            'class' => 'left-label',
            'label' => CHtml::encode (Yii::t('app', 'Social Profile:')),
        ));
        echo $form->TextField ($aliasModel, 'alias');
        $i = 0;
        if ($aliasModel->aliasType) {
            $checkedType = $aliasModel->aliasType;
        } else {
            $checkedType = 'email';
        }
        foreach ($aliasModel->getAliasTypeOptions () as $val => $label) {
            $aliasModel->aliasType = $val;
            if ($i % 3 === 0) {
                echo '<div class="bs-row">';
            }
            echo '<div class="alias-type-cell">';
                echo '<span class="'.($val === $checkedType ? ' selected' : '').'" 
                    title='.CHtml::encode ($aliasTypeOptions[$val]).'>';
                if ($val === 'other') {
                    echo $form->label ($aliasModel, CHtml::encode (Yii::t('app', 'other')), 
                        array ('class' => 'left-label'));
                } else {
                    echo $aliasModel->getIcon (false, true); 
                }
                echo $form->radioButton (
                    $aliasModel, 'aliasType', array (
                        'id' => 'alias-type-'.$val,
                        'value' => $val,
                        'data-default' => $val,
                        'checked' => $val === $checkedType ? 'checked' : '',
                        'uncheckValue' => null,
                    ));
                echo '</span>';
            echo '</div>';
            if ($i % 3 === 2) {
                echo '</div>';
            }
            $i++;
        }
        if ($i % 3 !== 0) {
            echo '</div>';
        }
    $this->endWidget ();
    ?>
</div>
<?php
 
if (GooglePlusResources::integrationIsEnabled ()) {
?>
<form id='google-plus-profile-search-form' class='form2' style='display: none;'>
    <input type='text' class='search-string'
     placeholder='<?php echo CHtml::encode (Yii::t('app', 'Search Google+ Profiles')); ?>' 
     value='<?php echo CHtml::encode ($this->model->name); ?>'/>
    <?php
    $aliasModel->aliasType = 'googlePlus';
    echo CHtml::activeHiddenField ($aliasModel, 'aliasType', array ());
    echo CHtml::activeHiddenField ($aliasModel, 'recordId');
    ?>
    <div class='search-results-container'></div>
    <div class='search-result search-result-template' style='display: none;'>
        <img class='search-result-profile-image' />
        <?php
        echo CHtml::activeHiddenField ($aliasModel, 'label', array (
            'disabled' => 'disabled',
            'class' => 'label-input',
        ));
        echo CHtml::activeHiddenField ($aliasModel, 'alias', array (
            'disabled' => 'disabled',
            'class' => 'alias-input'
        ));
        ?>
        <a class='search-result-display-name pseudo-link' target='_blank'
         title='<?php echo CHtml::encode (Yii::t('app', 'Visit Google+ Profile')); ?>'></a>
    </div>
    <?php
    echo CHtml::errorSummary ($aliasModel);
    ?>
</form>
<?php
}

?>
