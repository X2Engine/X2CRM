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

// get field names
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Quote'));
$attributeLabel=array();
$fieldType = array();
foreach($fields as $field) {
	$attributeLabel[$field->fieldName]=$field->attributeLabel;
	$fieldType[$field->fieldName] = $field->type;
}

?>

<div class="row viewQuote" style="overflow: visible;">
<?php
$viewButton = CHtml::link(
	'['. Yii::t('products', 'View') .']',
	Yii::app()->createUrl('/quotes/quotes/view', array('id'=>$quote->id)),
	array('title'=>'View Quote')
);
$strict = Yii::app()->params['admin']['quoteStrictLock'];
$updateButton = $canDo['QuickUpdate'] ? ' '. CHtml::link(
	'['. Yii::t('products', 'Update') .']',
	'javascript:void(0);',
	array('title'=>'Update Quote', 'onclick'=>"x2.inlineQuotes.toggleUpdateQuote({$quote->id}, {$quote->locked}, $strict);")
):'';
$deleteButton = $canDo['QuickDelete'] ? ' '. CHtml::ajaxLink(
	'['. Yii::t('quotes', 'Delete') .']', 
	Yii::app()->createUrl('/quotes/quotes/quickDelete', array('id'=>$quote->id, 'contactId'=>$contactId)),
	array(
		'success' => "function(html) { x2.inlineQuotes.reloadAll(); }",
        'beforeSend' => 'function(){return confirm('.json_encode(Yii::t('quotes','Are you sure you want to delete this quote?')).');}'
	),
	array('id'=> "delete-quote-{$quote->id}", 'title'=>Yii::t('quotes', "Delete Quote"), 'live'=>false)
):'';
$emailButton = CHtml::link('['. Yii::t('products','Email') .']', 'javascript:void(0)', array('id'=>"email-quote-{$quote->id}", 'onClick'=>"x2.inlineQuotes.sendEmail({$quote->id},".json_encode($quote->template).")"));
$printButton = CHtml::link('['. Yii::t('quotes','Print') .']', 'javascript:void(0)', array('id'=>"print-quote-{$quote->id}", 'onClick'=>"window.open('".Yii::app()->controller->createUrl('/quotes/quotes/print', array('id'=>$quote->id))."')"));
$duplicateButton = CHtml::link('['.Yii::t('quotes','Duplicate').']','javascript:void(0)',array('id'=>"duplicate-quote-{$quote->id}",'onClick'=>"x2.inlineQuotes.openForm(0,{$quote->id})"));
$convertToInvoiceButton = '';

if($quote->type != 'invoice') {
	$convertToInvoiceButton = CHtml::ajaxLink(
	'['. Yii::t('quotes', 'Invoice') .']', 
	Yii::app()->createUrl('/quotes/quotes/convertToInvoice', array('id'=>$quote->id, 'contactId'=>$contactId)),
	     array(
	     	'success'=>"function(html) { x2.inlineQuotes.reloadAll()}",
	     ),
	     array('id'=>"convert-to-invoice-quote-{$quote->id}", 'title'=> Yii::t('quotes', 'Convert To Invoice'), 'live'=>false)
	);
}

?>

<?php /*** Begin Quote Details ***/ ?>
<div id="quote-detail-<?php echo $quote->id; ?>">

<table class="quote-detail-table">
	<tbody>
		<tr>
			<th><?php echo Yii::t('quotes', 'ID'); ?></th>
			<th><?php echo Yii::t('quotes', 'Name'); ?></th>
			<th><?php echo Yii::t('quotes', 'Options'); ?></th>
		</tr>
		<tr>
			<td style="font-size: 1.5em;">
				<?php echo $quote->id; ?>
			</td>
			<td>
				<?php echo $quote->name; ?>
			</td>
			<td style="font-weight: normal;">
				<?php echo $viewButton; ?>
				<?php echo $updateButton; ?>
				<?php echo $deleteButton; ?>
				<?php echo $emailButton; ?>
				<?php echo $printButton; ?>
				<?php echo $duplicateButton; ?>
				<?php echo $convertToInvoiceButton; ?>
			</td>
		</tr>
		<tr>
			<th><?php echo Yii::t('quotes', 'Created'); ?></th>
			<th><?php echo Yii::t('quotes', 'Updated'); ?></th>
			<th><?php echo Yii::t('quotes', 'Expires'); ?></th>
		</tr>
		<tr>
			<td>
				<?php echo Formatter::formatLongDate($quote->createDate); ?>
			</td>
			<td>
				<?php echo Formatter::formatLongDate($quote->lastUpdated); ?>
			</td>
			<td>
				<?php echo Formatter::formatLongDate($quote->expirationDate); ?>
			</td>
		</tr>
		<tr>
			<th><?php echo Yii::t('quotes', 'Created By'); ?></th>
			<th><?php echo Yii::t('quotes', 'Updated By'); ?></th>
			<th><?php echo Yii::t('quotes', 'Status'); ?></th>
		</tr>	
		<tr>
			<td><?php echo $quote->createdBy; ?></td>
			<td><?php echo $quote->updatedBy; ?></td>
			<td><?php echo $quote->status; ?></td>
		</tr>
		<?php if(!empty($quote->description)) { ?>
		<tr>
			<th><?php echo Yii::t('quotes', 'Notes/Terms'); ?></th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<td colspan="3"><?php echo $quote->description; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table><br />

<?php

echo $quote->productTable();

?>

</div>

<br />
<br />
<hr />
<br />
</div>


