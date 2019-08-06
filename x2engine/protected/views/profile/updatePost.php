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

<?php

Yii::app()->clientScript->registerPackage('emailEditor');
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

<div class='page-title'>
<h2>Edit Social Post</h2>
</div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'event-update-form',
	'enableAjaxValidation'=>false,
    'action'=>array (
        '/profile/updatePost',
        'id' => $id,
        'profileId' => $profileId
    )
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
                'viewData' => array (
                    'profileId' => $profileId
                ),
                'itemView'=>'../social/_view',
                'template'=>'<h2>Comments</h2>{pager}{items}',
                'id'=>$model->id.'-comments',
        ));

?>

