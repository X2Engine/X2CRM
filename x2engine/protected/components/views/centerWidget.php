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

// check if we need to load a model
if(!isset($model) && isset($modelType) && isset($modelId)) {
	// didn't get passed a model, but we have the modelType and modelId, so load the model
	$model = CActiveRecord::model($modelType)->findByPk($modelId);
}

$relationshipCount = ""; // only used in InlineRelationships title; shows the number of relationships
if($name == "InlineRelationships") {
	$modelName = ucwords($modelType);
	$count = Relationships::model()->count(array(
	    'condition' => "(firstType=\"$modelName\" AND firstId=\"{$model->id}\") OR (secondType=\"$modelName\" AND secondId=\"{$model->id}\")",
	));
	if(is_numeric($count)) {
		$relationshipCount = " ($count)";
	}
}

?>

<div class="x2-widget form" id="x2widget_<?php echo $name; ?>">
	<div class="x2widget-header" onclick="$('#x2widget_<?php echo $name; ?>').minimizeWidget(); return false">
		<span class="x2widget-title">
			<b><?php echo Yii::t('app', $widget['title']) . $relationshipCount; ?></b>
		</span>
    	<div class="portlet-minimize">
    		<a onclick="$('#x2widget_<?php echo $name; ?>').minimizeWidget(); return false" href="#" class="x2widget-minimize"><?php echo $widget['minimize']? '[+]' : '[&ndash;]'; ?></a>
    		<a onclick="$('#x2widget_<?php echo $name; ?>').hideWidget(); return false" href="#"><?php echo '['.Yii::t('app','Hide').']'; ?></a>
    	</div>
    </div>
    <div class="x2widget-container" style="<?php echo $widget['minimize']? 'display: none;' : ''; ?>">
    	<?php if(isset($this->controller)) { // not ajax ?>
    		<?php $this->render('x2widget', array('widget'=>$widget, 'name'=>$name, 'model'=>$model, 'modelType'=>$modelType)); ?>
    	<?php } else { // we are in an ajax call ?>
    		<?php $this->renderPartial('application.components.views.x2widget', array('widget'=>$widget, 'name'=>$name, 'model'=>$model, 'modelType'=>$modelType)); ?>
    	<?php } ?>
    </div>
</div>