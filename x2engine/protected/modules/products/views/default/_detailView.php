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

$attributeLabels = Product::attributeLabels();
include("protected/config/productConfig.php");
Yii::app()->clientScript->registerScript('stopEdit','
	$(document).ready(function(){
		$("td#description a").click(function(e){
			e.stopPropagation();
		});
	});
');

$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Product'));
$nonCustom=array();
$custom=array();
foreach($fields as $field){
    if($field->custom==0){
        $nonCustom[$field->fieldName]=$field;
    }else{
        $custom[$field->fieldName]=$field;
    }
}
?>
<table class="details">
        <?php if($nonCustom['name']->visible==1) { ?>
	<tr>
		<td class="label" width="25%"><?php echo $attributeLabels['name']; ?></td>
		<td><?php echo $model->name; ?></td>
	</tr>
        <?php } ?>
        <?php if($nonCustom['active']->visible==1) { ?>
    <tr>
    	<td class="label">
    		<?php echo $attributeLabels['active']; ?>
    	</td>
    	<td>
    		<?php
    			if($model->active)
    				echo Yii::t('product', 'Active');
    			else
    				echo Yii::t('product', 'Inactive');
    		?>
    	</td>
    </tr>
    	<?php } ?>
        <?php if($nonCustom['type']->visible==1) { ?>
    <tr>
    	<td class="label">
    		<?php echo $attributeLabels['type']; ?>
    	</td>
    	<td><?php echo $model->type ?></td>
    </tr>
    	<?php } ?>
        <?php if($nonCustom['price']->visible==1) { ?>
    <tr>
    	<td class="label">
    		<?php echo $attributeLabels['price']; ?>
    	</td>
    	<td><?php echo Yii::app()->locale->numberFormatter->formatCurrency($model->price, $model->currency) ?></td>
    </tr>
    	<?php } ?>
        <?php if($nonCustom['currency']->visible==1) { ?>
    <tr>
    	<td class="label">
    		<?php echo $attributeLabels['currency']; ?>
    	</td>
    	<td><?php echo $model->currency ?></td>
    </tr>
    	<?php } ?>
        <?php if($nonCustom['inventory']->visible==1) { ?>
    <tr>
    	<td class="label">
    		<?php echo $attributeLabels['inventory']; ?>
    	</td>
    	<td><?php echo $model->inventory ?></td>
    </tr>
    	<?php } ?>
        <?php if($nonCustom['description']->visible==1) { ?>
	<tr>
		<td class="label">
			<?php echo $attributeLabels['description']; ?>
		</td>
		<td class="text-field"><div class="spacer"></div>
			<?php echo $this->convertUrls($model->description); ?>
		</td>
	</tr>
        <?php } ?>
        <?php if($nonCustom['assignedTo']->visible==1) { ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['assignedTo']; ?></td>
		<td><?php echo ($model->assignedTo=='Anyone')? $model->assignedTo : UserChild::getUserLinks($model->assignedTo); ?></td>
	</tr>
        <?php } ?>
        <?php 
            foreach($custom as $fieldName=>$field){
                if($field->visible==1){ 
                    echo "<tr>
                    <td class=\"label\"><b>".$attributeLabels[$fieldName]."</b></td>
                    <td colspan='5'>".Yii::t('actions',$model->$fieldName)."</td>
                    </tr>";    
                }
            }
        
        ?>
</table>