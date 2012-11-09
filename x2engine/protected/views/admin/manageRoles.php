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
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
Yii::app()->clientScript->registerCss('multiselectCss',"
.multiselect {
	width: 460px;
	height: 200px;
}
#switcher {
	margin-top: 20px;
}
",'screen, projection');
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'roles-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('accounts','Role List').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'name',
	),
)); ?>
<br />
<a href="#" onclick="$('#addRole').toggle();$('#deleteRole').hide();$('#editRole').hide();$('#exception').hide();" class="x2-button">Add Role</a>
<a href="#" onclick="$('#deleteRole').toggle();$('#addRole').hide();$('#editRole').hide();$('#exception').hide();" class="x2-button">Delete Role</a>
<a href="#" onclick="$('#editRole').toggle();$('#addRole').hide();$('#deleteRole').hide();$('#exception').hide();" class="x2-button">Edit Role</a>
<a href="#" onclick="$('#exception').toggle();$('#addRole').hide();$('#deleteRole').hide();$('#editRole').hide();" class="x2-button">Add Exception</a>
<br />
<br />
<div id="addRole" style="display:none;">
<?php $this->renderPartial('roleEditor',array(
    'model'=>$model,
)); ?>
</div>

<div id="deleteRole" style="display:none;">
<?php $this->renderPartial('deleteRole',array(
    'roles'=>$roles,
)); ?>
</div>

<div id="editRole" style="display:none;">
<?php $this->renderPartial('editRole',array(
    'model'=>$model,
)); ?>
</div>
<div id="exception" style="display:none;">
<?php $this->renderPartial('roleException',array(
    'model'=>$model,
    'workflows'=>$workflows,
)); ?>
</div>