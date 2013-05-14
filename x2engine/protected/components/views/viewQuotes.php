<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/
//
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

/* Javascript */
/*
$productNames = json_encode((object)$quote->productNames());
$prices = json_encode((object)$quote->productPrices());

$productTableInit = "$(function() {\n";
foreach($orders as $order) {
	if($order->adjustmentType == 'percent')
		$order->adjustment = "'{$order->adjustment}%'";
	$productTableInit .= "	addFilledProduct({$quote->id}, ".(empty($order->productId)?'""':$order->productId).", {$order->price}, {$order->quantity}, ".(!empty($order->adjustment)?$order->adjustment:0).", '{$quote->currency}', $productNames, $prices);\n";
}
$productTableInit .= "	$('#quote-update-{$quote->id}').hide();\n";
$productTableInit .= "});\n";

Yii::app()->clientScript->registerScript("productTableQuote{$quote->id}", $productTableInit, CClientScript::POS_END);

// End JavaScript

*/
?>

<div class="row viewQuote" style="overflow: visible;">
<?php
$viewButton = CHtml::link(
	'['. Yii::t('products', 'View') .']',
	Yii::app()->createUrl('/quotes/quotes/view', array('id'=>$quote->id)),
	array('title'=>'View Quote')
);
$strict = Yii::app()->params['admin']['quoteStrictLock'];
$updateButton = ' '. CHtml::link(
	'['. Yii::t('products', 'Update') .']',
	'javascript:void(0);',
	array('title'=>'Update Quote', 'onclick'=>"toggleUpdateQuote({$quote->id}, {$quote->locked}, $strict);")
);
$deleteButton = ' '. CHtml::ajaxLink(
	'['. Yii::t('quotes', 'Delete') .']', 
	Yii::app()->createUrl('/quotes/quotes/quickDelete', array('id'=>$quote->id, 'contactId'=>$contactId)),
	array(
		'success' => "function(html) { quickQuote.reloadAll(); }",
	),
	array('id'=> "delete-quote-{$quote->id}", 'title'=>Yii::t('quotes', "Delete Quote"), 'live'=>false)
);
?>

<?php /*** Email Quote ***/

//$emailName = "<br /><br /><br />
//<table style=\"width:100%;\">
//	<tbody>
//		<tr>
//			<td><b>{$quote->name}</b></td>
//			<td style=\"text-align:right;font-weight:bold;\">
//				<span>". ( $quote->type == 'invoice'? Yii::t('quotes', 'Invoice') : Yii::t('quotes','Quote')) ." # {$quote->id}</span><br />
//				<span>".date("F d, Y", time())."</span>
//			</td>
//		</tr>
//	</tbody>
//</table><br />
//";
//$emailName = str_replace("\n", "", $emailName); // fixed for history
//$emailProducts = $quote->productTable(true) ."<br />";
//$emailProducts = str_replace("\n", "", $emailProducts); // fixed for history
//$emailNotes = array();
//if(empty($quote->description))
//	$emailNotes['label'] = '';
//else
//	$emailNotes['label'] = '<b>'. Yii::t('quotes', $attributeLabel['description']) .'</b><br />';
//$emailNotes['notes'] = $quote->description .'<br /><br />';
//
//$jsEmailMessage = array('name'=>$emailName, 'products'=>$emailProducts, 'notes'=>$emailNotes, 'subject'=>( $quote->type == 'invoice'? Yii::t('quotes', 'Invoice') : Yii::t('quotes','Quote')) );
//$jsEmailMessage = json_encode($jsEmailMessage); // encode for javascript

$emailButton = CHtml::link('['. Yii::t('products','Email') .']', 'javascript:void(0)', array('id'=>"email-quote-{$quote->id}", 'onClick'=>"sendQuoteEmail({$quote->id})"));

/*** End Email Quote ***/
?>

<?php /*** Print Quote ***/

$printButton = CHtml::link('['. Yii::t('quotes','Print') .']', 'javascript:void(0)', array('id'=>"print-quote-{$quote->id}", 'onClick'=>"window.open('".Yii::app()->controller->createUrl('/quotes/quotes/print', array('id'=>$quote->id))."')"));
/*** End Print Quote ***/
?>


<?php /*** Duplicate Quote ***/
/*
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
$jsDuplicateQuote .= "'expirationDate': '". date("F d, Y",$quote->expirationDate) ."', ";
$jsDuplicateQuote .= "'products': $jsProductArray, ";
$jsDuplicateQuote .= "'currency': '{$quote->currency}',";
$jsDuplicateQuote .= "'productNames': $productNames,";
$jsDuplicateQuote .= "'prices': $prices,";
$jsDuplicateQuote .= ' }';

$duplicateButton = CHtml::link('['. Yii::t('products', 'Duplicate') .']', 'javascript:void(0);', array('onClick'=>"duplicateQuote($jsDuplicateQuote)"));
*/
/*** End Duplicate Quote ***/


/*** Begin Convert to Invoice ***/

$convertToIncoieButton = '';

if($quote->type != 'invoice') {
	$convertToIncoieButton = CHtml::ajaxLink(
	'['. Yii::t('quotes', 'Invoice') .']', 
	Yii::app()->createUrl('/quotes/quotes/convertToInvoice', array('id'=>$quote->id, 'contactId'=>$contactId)),
	     array(
	     	'success'=>"function(html) { quickQuote.reloadAll()}",
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
				<?php // echo $duplicateButton; ?>
				<?php echo $convertToIncoieButton; ?>
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

<?php /*** End Quote Detail View ***/ ?>


<?php /*** Begin Quote Update View ***/ ?>
<?php /*

<div id="quote-update-<?php echo $quote->id; ?>">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>"update-quote-form-{$quote->id}",
	'enableAjaxValidation'=>false,
)); ?>
<table class="quote-detail-table">
	<tbody>
		<tr>
			<th><?php echo Yii::t('quotes', 'Created'); ?></th>
			<th><?php echo Yii::t('quotes', 'Updated'); ?></th>
			<th><?php echo Yii::t('quotes', 'Expires'); ?></th>
		</tr>
		<tr>
			<td>
				<?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $quote->createDate); ?>
			</td>
			<td>
				<?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $quote->lastUpdated); ?>
			</td>
			<td>
				<?php Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
				$quote->expirationDate = Formatter::formatDate($quote->expirationDate);
				$this->widget('CJuiDateTimePicker',array(
					'model'=>$quote, //Model object
					'attribute'=>'expirationDate', //attribute name
					'mode'=>'date', //use "time","date" or "datetime" (default)
					'options'=>array(
						'dateFormat'=>Formatter::formatDatePicker(),
					), // jquery plugin options
					'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
					'htmlOptions' => array('id'=>"quote-{$quote->id}-expires"),
				));?>
				<?php echo $form->error($quote,'expirationDate'); ?>
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
			<td>
					<?php echo $form->dropDownList($quote, 'status', Quote::getStatusList()); ?>
					<?php echo $form->error($quote,'status'); ?>
					<span style="padding-left: 5px;">
						<?php echo Yii::t('quotes', $attributeLabel['locked']); ?>
						<?php echo $form->checkBox($quote, 'locked', array('id'=>"quote-{$quote->id}-locked")); ?>
					</span>
			</td>
			<td>

			</td>
		</tr>
		<tr>
			<th><?php echo Yii::t('quotes', 'Notes/Terms'); ?></th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<td colspan="3">
				<?php echo $form->textArea($quote,'description',array('rows'=>3, 'cols'=>50)); ?>
				<?php echo $form->error($quote,'description'); ?>
			</td>
		</tr>
	</tbody>
</table>

    <input type="hidden" name="contactId" value="<?php echo $contactId; ?>">
	<table frame="border" id="product-table-<?php echo $quote->id; ?>" class="product-table">
		<thead>
	    	<tr>
	    		<th style="padding: 0;"></th>
	    		<th><?php echo Yii::t('products', 'Line Item'); ?></th>
	    		<th><?php echo Yii::t('products', 'Unit Price'); ?></th>
	    		<th><?php echo Yii::t('products', 'Quantity'); ?></th>
	    		<th><?php echo Yii::t('products', 'Adjustments'); ?></th>
	    		<th><?php echo Yii::t('products', 'Price'); ?></th>
	    	</tr>
	    </thead>
	    <tbody>
	    </tbody>
	    <tfoot>
	    	<tr id="product-list-footer-<?php echo $quote->id; ?>">
	    		<td></td>
	    		<td>
    				<?php echo CHtml::link('['. Yii::t('workflow','Add') .']', 'javascript:void(0)', array('class'=>"add-workflow-stage", 'onClick'=>"addProduct({$quote->id}, '{$quote->currency}', $productNames, $prices);"));?>

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
    	array('/quotes/quotes/quickUpdate', 'id'=>$quote->id),
    	array(
    		'success'=>"function(html) { jQuery('#quote-form-wrapper').html(html); }",
			'complete'=>"function(response) { $.fn.yiiListView.update('history'); }",
    		'type'=>'POST',
    	),
    	array('id'=>"update-quote-button-{$quote->id}", 'class'=>'x2-button', 'style'=>'display:inline; padding:3px;', 'live'=>false)
    ); ?>

<?php $this->endWidget() ?>

</div>

 */ ?>
<?php /*** End Quote Update View ***/ ?>

<br />
<br />
<hr />
<br />
</div>


