<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
<div class="page-title">
<h2><span class="no-bold"><?php echo ($model->type == 'invoice')? Yii::t('quotes','Update Invoice:') : Yii::t('quotes','Update Quote:'); ?></span> <?php echo $model->name; ?></h2>
<a class="x2-button right" href="javascript:void(0);" onclick="$('#save-button').click();"><?php echo Yii::t('app','Save'); ?></a>
</div>

<?php 
/*
echo $this->renderPartial('_form',
	array(
		'model'=>$model, 
		'users'=>$users, 
		'contacts'=>$contacts,
		'selectedContacts'=>$selectedContacts,
		'products'=>$products,
		'orders'=>$orders,
	)
);
*/

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
	    
	</div>
	<br />
<?php }

echo $this->renderPartial('productTable',
	array(
		'model'=>$model,
		'products'=>$products,
		'orders'=>$orders,
	)
);

echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";
$this->endWidget();
 ?>