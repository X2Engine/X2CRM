<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/
//
?>

<?php

// get field names
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Quotes'));
$attributeLabel=array();
foreach($fields as $field){
    if($field->custom==0)
        $attributeLabel[$field->fieldName]=$field->attributeLabel;
}

/* Javascript */

$productTableInit = "$(function() {\n";
foreach($orders as $order) {
	if($order->adjustmentType == 'percent')
		$order->adjustment = "'{$order->adjustment}%'";
	$productTableInit .= "	addFilledProduct({$quote->id}, {$order->productId}, {$order->price}, {$order->quantity}, {$order->adjustment}, '{$quote->currency}');\n";
}
$productTableInit .= "	$('#update-quote-form-{$quote->id}').hide();\n";
$productTableInit .= "});\n";

Yii::app()->clientScript->registerScript("productTableQuote{$quote->id}", $productTableInit, CClientScript::POS_HEAD);

/* End JavaScript */
?>

<div class="row viewQuote" style="overflow: visible;">

<span style="font-weight: bold; font-size: 1.25em;">ID: <?php echo $quote->id; ?></span>
<span style="font-weight: bold;"><?php echo $quote->name; ?></span>
<?php

if($quote->status)
	echo ' ('. Yii::t('quote', $quote->status) .') ';

echo CHtml::link(
	'['. Yii::t('product', 'View') .']',
	Yii::app()->createUrl('quotes/view', array('id'=>$quote->id)),
	array('title'=>'View Quote')
);
echo ' '. CHtml::link(
	'['. Yii::t('product', 'Update') .']',
	'javascript:void(0);',
	array('title'=>'Update Quote', 'onclick'=>"toggleUpdateProduct({$quote->id});")
);
echo ' '. CHtml::ajaxLink(
	'['. Yii::t('quote', 'Delete') .']', 
	Yii::app()->createUrl('quotes/quickDelete', array('id'=>$quote->id, 'contactId'=>$contactId)),
	array('success' => "function(html) {
		jQuery('#quote-form-wrapper').html(html);
		". CHtml::ajax(array('update'=>'#history-list-wrapper', 'url'=>Yii::app()->createUrl('contacts/quickUpdateHistory', array('id'=>$contactId)))) ."
	}"),
	array('id'=> "delete-quote-{$quote->id}", 'title'=>Yii::t('quote', "Delete Quote"), 'live'=>false)
);
?>


<?php /* Email Quote */
$emailName = "<b>{$quote->name}</b>";
$emailProducts = $quote->productTable(true);
if(empty($quote->description))
	$emailNotes = "{ 'label': '', ";
else
	$emailNotes = "{ 'label': '". Yii::t('quote', $attributeLabel['description']) ."', ";
$emailNotes .= "'notes': '{$quote->description}' }";
$jsEmailMessage = "{ 'name': '$emailName', 'products': '$emailProducts', 'notes': $emailNotes }";
?>
<a href="javascript:void(0)" onClick="sendQuoteEmail(<?php echo $jsEmailMessage; ?>)" id="email-quote-<?php echo $quote->id; ?>">[<?php echo Yii::t('product','Email'); ?>]</a>
<?php /* End Email Quote */ ?>


<?php /* Duplicate Quote */

$jsProductArray = '[ ';
foreach($orders as $order) {
	$jsProductArray .= '{ ';
	$jsProductArray .= "'id': '{$order->productId}', ";
	$jsProductArray .= "'price': '{$order->price}', ";
	$jsProductArray .= "'quantity': '{$order->quantity}', ";
	if($order->adjustmentType == 'percent')
		$jsProductArray .= "'adjustment': {$order->adjustment}";
	else
		$jsProductArray .= "'adjustment': '{$order->adjustment}'";
	$jsProductArray .= ' }, ';
}
$jsProductArray .= ' ]';
$jsDuplicateQuote = '{ ';
$jsDuplicateQuote .= "'status': '{$quote->status}', ";
$jsDuplicateQuote .= "'expirationDate': '". date("Y-m-d H:i",$quote->expirationDate) ."', ";
$jsDuplicateQuote .= "'products': $jsProductArray, ";
$jsDuplicateQuote .= ' }';
?>
<a href="javascript:void(0);" onClick="duplicateQuote(<?php echo $jsDuplicateQuote; ?>)">[<?php echo Yii::t('product', 'Duplicate'); ?>]</a>

<?php
/* End Duplicate Quote */
?>



<br />
<table class="date-table">
	<thead>
		<tr>
			<th><?php echo Yii::t('quote', 'Created'); ?></th>
			<th><?php echo Yii::t('quote', 'Updated'); ?></th>
			<th><?php echo Yii::t('quote', 'Expires'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $quote->createDate); ?>
			</td>
			<td>
				<?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $quote->lastUpdated); ?>
			</td>
			<td>
				<?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $quote->expirationDate); ?>
			</td>
		</tr>
	</tbody>
</table>

<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>"quote-{$quote['id']}-grid",
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'summaryText'=>'',
	'ajaxUpdateError'=>'function(xhr,ts,et,err){ alert(err); }',
	'ajaxUrl'=>Yii::app()->createUrl('contacts', array('id'=>$contactId)),
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
			'name'=>'name',
			'header'=>Yii::t('product','Product'),
			'value'=>'$data["name"]',
			'type'=>'raw',
		),
		array(
			'name'=>'unit',
			'header'=>Yii::t('product','Unit Price'),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data["unit"],"'.$quote->currency.'")',
			'type'=>'raw',
		),
		array(
			'name'=>'quantity',
			'header'=>Yii::t('product','Quantity'),
			'value'=>'$data["quantity"]',
			'type'=>'raw',
		),
		array(
			'name'=>'adjustment',
			'header'=>Yii::t('product', 'Adjustment'),
			'value'=>'$data["adjustment"]',
			'type'=>'raw',
			'footer'=>'<b>Total</b>',
		),
		array(
			'name'=>'price',
			'header'=>Yii::t('product', "Price"),
			'value'=>'Yii::app()->locale->numberFormatter->formatCurrency($data["price"],"'.$quote->currency.'")',
			'type'=>'raw',
			'footer'=>'<b>'. Yii::app()->locale->numberFormatter->formatCurrency($total,$quote->currency) .'</b>',
		),
	),
));
?>

<form id="update-quote-form-<?php echo $quote->id; ?>">
    <input type="hidden" name="contactId" value="<?php echo $contactId; ?>">
	<table frame="border" id="product-table-<?php echo $quote->id; ?>" class="product-table">
		<thead>
	    	<tr>
	    		<th style="padding: 0;"></th>
	    		<th><?php echo Yii::t('product', 'Name'); ?></th>
	    		<th><?php echo Yii::t('product', 'Unit Price'); ?></th>
	    		<th><?php echo Yii::t('product', 'Quantity'); ?></th>
	    		<th><?php echo Yii::t('product', 'Adjustments'); ?></th>
	    		<th><?php echo Yii::t('product', 'Price'); ?></th>
	    	</tr>
	    </thead>
	    <tbody>
	    </tbody>
	    <tfoot>
	    	<tr id="product-list-footer-<?php echo $quote->id; ?>">
	    		<td></td>
	    		<td>
	    			<a href="javascript:void(0)" onclick="addProduct(<?php echo $quote->id; ?>, '<?php echo $quote->currency; ?>');" class="add-workflow-stage">
	    				[<?php echo Yii::t('workflow','Add'); ?>]
	    			</a>
	    		</td>
	    		<td></td>
	    		<td></td>
	    		<td><b>Total</b></td>
	    		<td><label id="product-list-total-<?php echo $quote->id; ?>" style="font-weight: bold; width: auto;">0</label></td>
	    	</tr>
	    </tfoot>
	</table>
    <?php echo CHtml::ajaxSubmitButton(
    	Yii::t('app','Save'),
    	array('quotes/quickUpdate', 'id'=>$quote->id),
    	array('success'=>"function(html) {
			jQuery('#quote-form-wrapper').html(html);
			". CHtml::ajax(array('update'=>'#history-list-wrapper', 'url'=>Yii::app()->createUrl('contacts/quickUpdateHistory', array('id'=>$contactId)))) ."
    	}", 'type'=>'POST'),
    	array('id'=>"update-quote-button-{$quote->id}", 'class'=>'x2-button', 'style'=>'display:inline; padding:3px;', 'live'=>false)
    ); ?>
</form>

<?php if(!empty($quote->description)) { ?>
<div style="font-size: small;"><?php echo Yii::t('quote', $attributeLabel['description']); ?></div>
<div style="font-weight: bold;"><?php echo $quote->description; ?></div>
<?php } ?>

<br />
<br />
<hr />
<br />
</div>


