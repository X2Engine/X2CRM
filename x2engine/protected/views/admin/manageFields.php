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
?>
<div class="page-title"><h2><?php echo Yii::t('admin','Modified Fields'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','This page has a list of all fields that have been modified, and allows you to add or remove your own fields, as well as customizing the pre-set fields.'); ?>
</div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'fields-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('accounts','Fields').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'modelName',
		'fieldName',
		'attributeLabel',
		// array(
			// 'name'=>'visible',
                        // 'header'=>'Visibility',
			// 'value'=>'$data->visible==1?"Shown":"Hidden"',
			// 'type'=>'raw',
		// ),
                array(
			'name'=>'required',
                        'header'=>'Required',
			'value'=>'$data->required==1?"Yes":"No"',
			'type'=>'raw',
		),
		/*
		'tickerSymbol',
		'employees',
		'associatedContacts',
		'notes',
		*/
	),
)); ?>
<br>
<a href="#" onclick="$('#addField').toggle();$('#removeField').hide();$('#customizeField').hide();" class="x2-button">Add Field</a>
<a href="#" onclick="$('#removeField').toggle();$('#addField').hide();$('#customizeField').hide();" class="x2-button">Remove Field</a>
<a href="#" onclick="$('#customizeField').toggle();$('#addField').hide();$('#removeField').hide();" class="x2-button">Customize Field</a>
<br>
<br>
<div id="addField" style="display:none;">
<?php $this->renderPartial('addField',array(
    'model'=>$model,
)); ?>
</div>

<div id="removeField" style="display:none;">
<?php $this->renderPartial('removeFields',array(
    'fields'=>$fields,
)); ?>
</div>

<div id="customizeField" style="display:none;">
<?php $this->renderPartial('customizeFields',array(
    'model'=>$model,
)); ?>
</div>