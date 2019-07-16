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




$model->renderConvertedNotice ();

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

$this->pageTitle = CHtml::encode(
                Yii::app()->settings->appName . ' - ' . Yii::t('x2Leads', 'View Lead'));

$authParams['assignedTo'] = $model->assignedTo;

$menuOptions = array(
    'index', 'create', 'view', 'edit', 'delete', 'attach', 'quotes',
    'convertToContact', 'convert', 'print', 'editLayout',
);
$this->insertMenu($menuOptions, $model, $authParams);


Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');
Yii::app()->clientScript->registerCss('leadViewCss', "

#content {
    background: none !important;
    border: none !important;
}

#conversion-warning-dialog ul {
    padding-left: 25px !important;
}

");

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/Relationships.js');

Yii::app()->clientScript->registerScript('leadsJS', "

// widget data
$(function() {
	$('body').data('modelType', 'x2Leads');
	$('body').data('modelId', $model->id);
});

");

$themeUrl = Yii::app()->theme->getBaseUrl();
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">

        <div class="page-title icon x2Leads">
            <h2><span class="no-bold"><?php echo Yii::t('x2Leads', 'Leads:'); ?> </span><?php echo CHtml::encode($model->name); ?></h2>
            <?php
            echo X2Html::editRecordButton($model);
            echo X2Html::inlineEditButtons();
            echo X2Html::emailFormButton();
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
    <?php
    $this->beginWidget('CActiveForm', array(
        'id' => 'contacts-form',
        'enableAjaxValidation' => false,
        'action' => array('saveChanges', 'id' => $model->id),
    ));

    $this->widget ('DetailView', array(
        'model' => $model
    ));
//    $this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $model, 'modelName' => 'X2Leads'));
    $this->endWidget();

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'modelName' => 'X2Leads',
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
    ));

    ?>
    <div id="quote-form-wrapper">
    <?php
    $this->widget('InlineQuotes', array(
        'startHidden' => true,
        'recordId' => $model->id,
        'account' => $model->getLinkedAttribute('accountName', 'name'),
        'modelName' => X2Model::getModuleModelName()
    ));
    ?>
    </div>

<?php
$this->widget ('ModelFileUploader', array(
    'associationType' => 'x2Leads',
    'associationId' => $model->id,
));
?>
</div>

<?php
$this->widget(
    'X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'block' => 'center',
        'model' => $model,
        'modelType' => 'x2Leads'
    ));
$this->widget('CStarRating', array('name' => 'rating-js-fix', 'htmlOptions' => array('style' => 'display:none;')));

$this->widget('X2ModelConversionWidget', array(
    'buttonSelector' => '#convert-lead-button',
    'targetClass' => 'Opportunity',
    'namespace' => 'Opportunity',
    'model' => $model,
));

$this->widget('X2ModelConversionWidget', array(
    'buttonSelector' => '#convert-lead-to-contact-button',
    'targetClass' => 'Contacts',
    'namespace' => 'Contacts',
    'model' => $model,
));
?>
