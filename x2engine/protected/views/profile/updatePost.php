<?php
/*********************************************************************************
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
 ********************************************************************************/
?>

<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/emailEditor.js');
Yii::app()->clientScript->registerCss("ckeditorStyling", "
#cke_Events_text {
  margin-bottom: 6px !important;
}

/* collapse bottom bar */
.cke_bottom {
    background: none !important;
    border-top: none !important;
    display: inline !important;
    height: 0px !important;
    width: 0px !important;
    padding: 0 0 0 0 !important;
    margin: 0 0 0 0 !important;
}

/* move resizing handle */
.cke_resizer_ltr { 
    /*margin-right: 0px !important;
    margin-top: -14px !important;*/
    margin-right: 2px !important;
    /*position: relative !important;*/
}
");
Yii::app()->clientScript->registerScript('instantiateCKEditor','
window.newPostEditor = createCKEditor (
    "Events_text", { height:120, toolbarStartupExpanded: false, placeholder: "' . Yii::t('app','Enter text here...') . '"});
');
?>

<h2>Edit Social Post</h2>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'event-update-form',
	'enableAjaxValidation'=>false,
)); ?>

	<div class="top row">
		<?php echo $form->labelEx($model,'text'); ?>
		<?php echo $form->textArea($model,'text',array('style'=>'width:1000px;height:150px;')); ?>
		<?php echo $form->error($model,'text'); ?>
	</div>



	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><br>
<?php

    $this->widget('zii.widgets.CListView', array(
                'dataProvider'=>$commentDataProvider,
                'itemView'=>'../social/_view',
                'template'=>'<h2>Comments</h2>{pager}{items}',
                'id'=>$model->id.'-comments',
        ));

?>

