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




Yii::app()->clientScript->registerCss('changeApplicationNameCSS',"

#submit-app-name-settings {
    margin-top: 15px;
}

");

?>

<div class="page-title"><h2><?php echo Yii::t('admin','Change the Application Name'); ?></h2></div>
<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'change-the-app-name-form',
	'enableAjaxValidation'=>false,
)); 
?>
<div class="form">

<?php echo Yii::t('admin','Change the name of the application as displayed on page titles.');?>

<br><br>
<?php
echo CHtml::label (Yii::t('app', 'Application Name'), 'appName', array ());
echo $form->textField ($model, 'appName', array (
    'id' => 'application-name'
));
echo $form->error ($model, 'appDescription');
echo CHtml::label (Yii::t('app', 'Application Description'), 'appDescription', array ());
?>
<div>
<?php echo Yii::t('app', 'This will be displayed on the login page below the application name.'); ?>
</div>
<?php
echo $form->textField ($model, 'appDescription', array (
    'id' => 'application-description'
));
echo $form->error ($model, 'appDescription');
?>
<br>
<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('app','Submit'),
    array(
        'class'=>'x2-button',
        'id'=>'submit-app-name-settings'
    )); ?>
</div>

</div>
<?php $this->endWidget(); ?>
