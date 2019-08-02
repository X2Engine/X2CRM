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




// get field names
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Quote'));
$attributeLabel=array();
$fieldType = array();
foreach($fields as $field) {
	$attributeLabel[$field->fieldName]=$field->attributeLabel;
	$fieldType[$field->fieldName] = $field->type;
}

?>

<div class="row viewQuote" style="overflow: visible;" >
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
	array('title'=>'Update Quote', 'onclick'=>"x2.inlineQuotes.toggleUpdateQuote({$quote->id}, {$quote->locked}, ".($strict ? 'true' : 'false') .");")
):'';
$deleteButton = $canDo['QuickDelete'] ? ' '. CHtml::ajaxLink(
	'['. Yii::t('quotes', 'Delete') .']', 
	Yii::app()->createUrl(
        '/quotes/quotes/quickDelete', array('id'=>$quote->id, 'recordId'=>$recordId)),
	array(
		'success' => "function(html) { x2.inlineQuotes.reloadAll(); }",
        'beforeSend' => 'function(){
            return confirm('.
                json_encode(Yii::t('quotes','Are you sure you want to delete this quote?')).');
        }'
	),
	array(
        'id'=> "delete-quote-{$quote->id}",
        'title'=>Yii::t('quotes', "Delete Quote"),
        'live'=>false
    )
):'';
$emailButton = 
    CHtml::link(
        '['. Yii::t('products','Email') .']',
        'javascript:void(0)',
        array(
            'id'=>"email-quote-{$quote->id}", 
            'onClick'=>"x2.inlineQuotes.sendEmail(
                {$quote->id},".json_encode($quote->templateModel?$quote->templateModel->id:0).")"
        )
    );
$printButton = CHtml::link(
    '['. Yii::t('quotes','Print') .']',
    'javascript:void(0)',
    array('id'=>"print-quote-{$quote->id}", 'onClick'=>"window.open('".Yii::app()->controller->createUrl('/quotes/quotes/print',array('id'=>$quote->id))."')")
);
$duplicateButton = CHtml::link(
    '['.Yii::t('quotes','Duplicate').']',
    'javascript:void(0)',
    array('id'=>"duplicate-quote-{$quote->id}", 'onClick'=>"x2.inlineQuotes.openForm(0,{$quote->id})")
);
$convertToInvoiceButton = '';

if($quote->type != 'invoice') {
	$convertToInvoiceButton = CHtml::ajaxLink(
	'['. Yii::t('quotes', 'Invoice') .']', 
	Yii::app()->createUrl(
        '/quotes/quotes/convertToInvoice', 
        array(
            'id'=>$quote->id, 'recordId'=>$recordId,
            'modelName' => $modelName
        )),
	     array(
	     	'success'=>"function(html) { x2.inlineQuotes.reloadAll()}",
	     ),
	     array('id'=>"convert-to-invoice-quote-{$quote->id}", 'title'=> Yii::t('quotes', 'Convert To Invoice'), 'live'=>false)
	);
}

?>

<?php /*** Begin Quote Details ***/ ?>
<div id="quote-detail-<?php echo $quote->id; ?>" class='quote-detail-container'>
<div class='quote-detail-container-inner'>

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
</div>

<br />
<br />
<hr />
<br />
</div>


