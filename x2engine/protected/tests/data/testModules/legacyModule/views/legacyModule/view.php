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




Yii::app()->clientScript->registerCss('contactRecordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');

include("protected/modules/templates/templatesConfig.php");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create {X}',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('create')),
	array('label'=>Yii::t('module','View {X}',array('{X}'=>$moduleConfig['recordName']))),
	array('label'=>Yii::t('module','Edit {X}',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Delete {X}',array('{X}'=>$moduleConfig['recordName'])), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
    array(
        'label' => Yii::t('app', 'Send Email'), 'url' => '#',
        'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')),
    array('label' => Yii::t('app', 'Attach A File/Photo'), 'url' => '#', 'linkOptions' => array('onclick' => 'toggleAttachmentForm(); return false;')),
    array('label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)', 'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
    array(
        'label' => Yii::t('app', 'Print Record'),
        'url' => '#',
        'linkOptions' => array (
            'onClick'=>"window.open('".
                Yii::app()->createUrl('/site/printRecord', array (
                    'modelClass' => "Templates",
                    'id' => $model->id,
                    'pageTitle' => 
                        Yii::t('app', '{X}', array ('{X}' => $moduleConfig['recordName'])).': '.$model->name
                ))."');"
        ),
    ),
));

$modelType = json_encode("Templates");
$modelId = json_encode($model->id);

Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
?>
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
<div class="page-title icon">
    <h2>
        <?php 
        echo Yii::t('module','View {X}',array('{X}'=>$moduleConfig['recordName'])); ?>: <?php 
        echo $model->name; 
        ?>
    </h2>
    <?php
    echo CHtml::link(
        '<span></span>', $this->createUrl('update', array('id' => $model->id)),
        array(
            'class' => 'x2-button icon edit right',
            'title' => Yii::t('app', 'Edit {X}', array('{X}'=>$moduleConfig['recordName'])),
        )
    );
    echo CHtml::link(
        '<img src="'.Yii::app()->request->baseUrl.'/themes/x2engine/images/icons/email_button.png'.
            '"></img>', '#',
        array(
            'class' => 'x2-button icon right email',
            'title' => Yii::t('app', 'Open email form'),
            'onclick' => 'toggleEmailForm(); return false;'
        )
    );
    ?>
</div>
</div>
</div>
<div id="main-column" class="half-width">
<?php 
    $this->widget ('DetailView', array(
        'model' => $model
    ));

//$this->renderPartial('application.components.views.@DETAILVIEW',array('model'=>$model, 'modelName'=>'templates')); ?>

<?php

$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>implode (', ', $model->getRelatedContactsEmails ()),
			'modelName'=> get_class ($model),
			'modelId'=>$model->id,
		),
		'insertableAttributes' => 
            array(
                Yii::t('module','{modelName} Attributes',
                    array ('{modelName}' => get_class ($model))) => 
                        $model->getEmailInsertableAttrs ($model)
            ),
		'startHidden'=>true,
	)
);

$this->widget('Attachments', array('associationType' => 'templates', 'associationId' => $model->id, 'startHidden' => true)); 

$this->widget('X2WidgetList', array('model'=>$model));
?>
<div id="quote-form-wrapper">
    <?php
    $this->widget('InlineQuotes', array(
        'startHidden' => true,
        'contactId' => $model->id,
        'modelName' => X2Model::getModuleModelName ()
    ));
    ?>
</div>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'templates',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'calendar' => false
	)
);
$this->widget('History',array('associationType'=>'templates','associationId'=>$model->id));
?>
</div>
