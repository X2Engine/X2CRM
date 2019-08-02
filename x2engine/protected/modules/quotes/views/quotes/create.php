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




$menuOptions = array(
    'index', 'invoices', 'create',
);
$this->insertMenu($menuOptions);

$title = CHtml::tag(
    'h2',array('class' =>'quotes-section-title'),Yii::t('quotes','Create {quote}', array(
    '{quote}' => Modules::displayName(false),
)));
echo $quick?$title:CHtml::tag('div',array('class'=>'page-title icon quotes'),$title);

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'quotes-form',
	'enableAjaxValidation'=>false,
));

if($model->hasLineItemErrors) { ?>
<div class="errorSummary">
    <h3>
        <?php echo Yii::t('quotes','Could not save {quote} due to line item errors:',  array(
            '{quote}' => lcfirst(Modules::displayName(false)),
        )); ?>
    </h3>
	<ul>
	<?php foreach($model->lineItemErrors as $error) { ?>
		<li><?php echo CHtml::encode($error); ?></li>
	<?php } ?>
	</ul>
</div>
<?php 
}


$this->widget ('FormView', array(
	'model' => $model,
	'form' => $form,
	'scenario' => $quick ? 'Inline' : 'Default'
));
//echo $this->renderPartial('application.components.views.@FORMVIEW',
// 	array(
// 		'model'=>$model,
// 		'form'=>$form,
// 		'users'=>$users,
// 		'modelName'=>'Quote',
// 		'suppressForm'=>true, // let us create the CActiveForm in this file
// 		'scenario' => $quick ? 'Inline' : 'Default',
// 	)
// );

echo $this->renderPartial('_lineItems', array(
	'model' => $model,
	'products' => $products,
	'readOnly' => false,
    'namespacePrefix' => 'quotes'
));

echo $this->renderPartial('_sharedView', array (
    'quick' => $quick,
    'action' => 'Create',
    'model' => $model,
    'form' => $form,
));

$this->endWidget();
?>
