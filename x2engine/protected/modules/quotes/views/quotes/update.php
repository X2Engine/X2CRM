<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
$authParams['X2Model'] = $model;

$menuOptions = array(
    'index', 'invoices', 'create', 'view', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);

?>
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<?php echo $quick?'':'<div class="page-title icon quotes">'; ?>
<h2><span class="no-bold"><?php echo ($model->type == 'invoice')? Yii::t('quotes','Update Invoice:') : Yii::t('quotes','Update {quote}:', array('{quote}'=>Modules::displayName(false))); ?></span> <?php echo $model->name==''?'#'.$model->id:CHtml::encode($model->name); ?></h2>
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
	    							<label><?php echo Yii::t('media', 'Invoice Paid'); ?></label>
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
		'form'=>$form,
        'namespacePrefix' => 'quotes'
	)
);

$templateRec = Yii::app()->db->createCommand()->select('nameId,name')->from('x2_docs')->where("type='quote'")->queryAll();
$templates = array();
$templates[null] = '(none)';
foreach($templateRec as $tmplRec){
	$templates[$tmplRec['nameId']] = $tmplRec['name'];
}
echo '<div style="display:inline-block; margin: 8px 11px;" ' .
    'id="quote-template-dropdown">';
echo '<strong>'.$form->label($model, 'template').'</strong>&nbsp;';
echo $form->dropDownList($model, 'template', $templates).'&nbsp;'
    .CHtml::tag('span', array(
        'class' => 'x2-hint',
        'title' => Yii::t('quotes', 'To create a template for {quotes} and invoices, go to the {docs} module and select "Create {quote}".', array(
            '{quotes}' => lcfirst(Modules::displayName()),
            '{quote}' => Modules::displayName(false),
            '{docs}' => Modules::displayName(true, "Docs"),
        ))
    ), '[?]');
echo '</div><br />'; 
echo '	<div class="row buttons" style="padding-left:0;">'."\n";
echo CHtml::submitButton(Yii::t('app', 'Update'), array('class' => 'x2-button'.($quick?' highlight':''), 'id' => 'quote-save-button', 'tabindex' => 25, 'onClick' => 'return x2.quoteslineItems.validateAllInputs ();'))."\n";
echo $quick?CHtml::button(Yii::t('app','Cancel'),array('class'=>'x2-button right','id'=>'quote-cancel-button','tabindex'=>24))."\n":'';
echo "	</div>\n";
echo '<div id="quotes-errors"></div>';

$this->endWidget();
?>
