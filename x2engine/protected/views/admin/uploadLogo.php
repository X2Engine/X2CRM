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
    Yii::app()->theme->baseUrl.'/css/views/admin/uploadLogo.css');

?>
<div class="page-title"><h2><?php echo Yii::t('admin','Upload Your Logo'); ?></h2></div>
<div id='upload-logo-form-container' class="form">
<?php echo Yii::t('admin','To upload your logo for display on the top menu bar or login screen, please upload the files here using the form below.');
$this->beginWidget ('CActiveForm', array (
    'htmlOptions' => array (
        'enctype'=>'multipart/form-data'
    )
));
echo X2Html::getFlashes ();
echo CHtml::errorSummary ($formModel);
?>
<br>
<h3><?php echo Yii::t('contacts','Top Menu Bar Logo'); ?></h3>
<?php 
echo X2Html::hint2 (Yii::t('admin', 'The expected height of this image is 30 pixels'), array (
));
echo '<br>';
echo CHtml::activeFileField($formModel, 'menuLogoUpload'); 
echo CHtml::link(
    Yii::t('admin','Restore Default Logo'),
    array('/admin/toggleDefaultLogo?logoType=logo'),
    array ('class' => 'x2-button'));
echo CHtml::error ($formModel, 'menuLogoUpload');

?><br>
<?php

if (Yii::app()->contEd ('pro')) { 
?>
<h3><?php echo Yii::t('contacts','Login Screen Logo'); ?></h3>
<?php 
echo X2Html::hint2 (Yii::t('admin', 'The expected height of this image is 70 pixels'));
echo '<br>';
echo CHtml::activeFileField($formModel, 'loginLogoUpload'); 
echo CHtml::link(
    Yii::t('admin','Restore Default Logo'),
    array('/admin/toggleDefaultLogo?logoType=loginLogo'),
    array ('class' => 'x2-button'));
echo CHtml::error ($formModel, 'loginLogoUpload');
?><br>
<?php
}

echo '<br>';
echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); 
$this->endWidget (); 
?> 
</div>
