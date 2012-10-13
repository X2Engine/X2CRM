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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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


    $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'changelog-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template'=> '<h2>Changelog</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Clear Filters'),array('viewChangelog','clearFilters'=>1))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
        'filter'=>$model,
	'columns'=>array(
		array(

			'name'=>'type',
			'header'=>Yii::t('admin','Type'),
			'value'=>'
				!is_null(CActiveRecord::model($data->type)->findByPk($data->itemId))?
				($data->type!="Actions"?CHtml::link(CActiveRecord::model($data->type)->findByPk($data->itemId)->name,Yii::app()->controller->createUrl(strtolower($data->type)."/".$data->itemId)):
					CHtml::link("Action",Yii::app()->controller->createUrl(strtolower($data->type)."/".$data->itemId))):
				"RECORD DELETED"',
			'type'=>'raw',
		),
		array(
			'name'=>'changedBy',
			'header'=>Yii::t('admin','Changed By'),
			'value'=>'$data->changedBy',
			'type'=>'raw',
		),
		array(
			'name'=>'timestamp',
			'header'=>Yii::t('admin','Timestamp'),
			'value'=>'date("Y-m-d H:i:s",$data->timestamp)',
			'type'=>'raw',
                        'htmlOptions'=>array('width'=>'20%'),
		),
		array(
			'name'=>'fieldName',
			'header'=>Yii::t('actions','Field Name'),
			'value'=>'$data->fieldName',
			'type'=>'raw',
		),
        array(
			'name'=>'oldValue',
			'header'=>Yii::t('actions','Old Value'),
			'value'=>'$data->oldValue',
			'type'=>'raw',
		),
        array(
			'name'=>'newValue',
			'header'=>Yii::t('actions','New Value'),
			'value'=>'$data->newValue',
			'type'=>'raw',
		)
	),
));

?>
