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
<h2><?php echo Yii::t('admin','Tag Manager'); ?></h2>
<div style="width:600px;" class="form">
    <?php echo Yii::t('admin',"This is a list of all tags currently used within the app."); ?><br />
    <?php echo Yii::t('admin',"To delete a tag, click the delete link in the grid below.  This will remove any relationship between that tag and records, but textual references to the tag will be preserved.") ?><br /><br />
    <?php echo Yii::t('admin','To delete all tags, use the "Delete All" button at the bottom of the grid.'); ?>
</div>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'tags-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('admin','Tags').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
        array(
            'header'=>'Tag',
            'name'=>'tag',
            'type'=>'raw',
            'value'=>"CHtml::link(\$data->tag,array('/search/search?term=%23'.substr(\$data->tag,1)), array('class'=>'x2-link x2-tag'))"
        ),
        array(
            'header'=>'# of Records',
            'type'=>'raw',
            'value'=>"CActiveRecord::model('Tags')->countByAttributes(array('tag'=>\$data->tag))"
        ),
        array(
            'header'=>'Delete Tag',
            'type'=>'raw',
            'value'=>"CHtml::link('Delete Tag','#',array('class'=>'x2-button', 'submit'=>'deleteTag?tag='.\substr(\$data->tag,1),'confirm'=>'Are you sure you want to delete this tag?'))"
        ),
	),
));
?><br>
<?php echo CHtml::link(Yii::t('admin','Delete All'),'#',array('class'=>'x2-button', 'submit'=>'deleteTag?tag=all','confirm'=>'Are you sure you want to delete all tags?')) ?>
