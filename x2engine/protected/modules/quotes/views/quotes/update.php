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
$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('quotes','Quotes List'), 'url'=>array('index')),
	array('label'=>Yii::t('quotes','Invoice List'), 'url'=>array('indexInvoice')),
	array('label'=>Yii::t('quotes','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('quotes','View'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('quotes','Update')),
	array('label'=>Yii::t('quotes','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);
?>
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<?php echo $quick?'':'<div class="page-title icon quotes">'; ?>
<h2><span class="no-bold"><?php echo ($model->type == 'invoice')? Yii::t('quotes','Update Invoice:') : Yii::t('quotes','Update Quote:'); ?></span> <?php echo $model->name==''?'#'.$model->id:$model->name; ?></h2>
<?php if(!$quick): ?>
<a class="x2-button right" href="javascript:void(0);" onclick="$('#quote-save-button').click();"><?php echo Yii::t('app','Update'); ?></a>
</div>
<?php endif; ?>

<?php 

$form=$this->beginWidget('CActiveForm', array(
   'id'=>'quotes-form',
   'enableAjaxValidation'=>false,
));
	
echo $this->renderPartial('application.components.views._form', 
	array(
		'model'=>$model,
		'form'=>$form,
		'users'=>$users,
		'modelName'=>'Quote',
		'isQuickCreate'=>true, // let us create the CActiveForm in this file
		'scenario' => $quick ? 'Inline' : 'Default',
	)
);

if($model->type == 'invoice') { ?>
	<div class="x2-layout form-view" style="margin-bottom: 0;">
	
	    <div class="formSection showSection">
	    	<div class="formSectionHeader">
	    		<span class="sectionTitle" title="Invoice"><?php echo Yii::t('quotes', 'Invoice'); ?></span>
	    	</div>
	    	<div class="tableWrapper">
	    		<table>
	    			<tbody>
	    				<tr class="formSectionRow">
	    					<td style="width: 300px">
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Status'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoiceStatus'); ?>
	    							</div>
	    						</div>
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Created'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoiceCreateDate'); ?>
	    							</div>
	    						</div>
	    					</td>
	    					<td style="width: 300px">
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Issued'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoiceIssuedDate'); ?>
	    							</div>
	    						</div>
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Payed'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoicePayedDate'); ?>
	    							</div>
	    						</div>
	    					</td>
	    				</tr>
	    			</tbody>
	    		</table>
	    	</div>
	    </div>
	    
	</div>
	<br />
<?php }

echo $this->renderPartial('_lineItems',
	array(
		'model'=>$model,
		'products'=>$products,
		'readOnly'=>false,
		'form'=>$form
	)
);

$templateRec = Yii::app()->db->createCommand()->select('id,name')->from('x2_docs')->where("type='quote'")->queryAll();
$templates = array();
$templates[null] = '(none)';
foreach($templateRec as $tmplRec){
	$templates[$tmplRec['id']] = $tmplRec['name'];
}
if(!$quick){
	echo '<div style="display:inline-block" ' .
          'id="quote-template-dropdown">';
	echo '<strong>'.$form->label($model, 'template').'</strong>&nbsp;';
	echo $form->dropDownList($model, 'template', $templates).'&nbsp;'.CHtml::tag('span', array('class' => 'x2-hint', 'title' => Yii::t('quotes', 'To create a template for quotes and invoices, go to the Docs module and select "{crQu}".', array('{crQu}' => Yii::t('docs', 'Create Quote')))), '[?]');
	echo '</div><br />';
} 
echo '	<div class="row buttons" style="padding-left:0;">'."\n";
echo CHtml::submitButton(Yii::t('app', 'Update'), array('class' => 'x2-button'.($quick?' highlight':''), 'id' => 'quote-save-button', 'tabindex' => 25))."\n";
echo $quick?CHtml::button(Yii::t('app','Cancel'),array('class'=>'x2-button right','id'=>'quote-cancel-button','tabindex'=>24))."\n":'';
echo "	</div>\n";
echo '<div id="quotes-errors"></div>';

$this->endWidget();

if($quick){
	echo '<br /><br /><script id="quick-quote-form">'."\n";
	foreach(Yii::app()->clientScript->scripts[CClientScript::POS_READY] as $id => $script) {
		if(strpos($id,'logo')===false)
			echo "$script\n";
	}
	echo "</script>";
}
?>
