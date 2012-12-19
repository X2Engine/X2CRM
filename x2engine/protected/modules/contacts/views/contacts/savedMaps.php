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
$this->pageTitle=Yii::t('contacts','Saved Maps');
$menuItems = array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
    array('label'=>Yii::t('contacts','Import Contacts'),'url'=>array('importExcel')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('export')),
    array('label'=>Yii::t('contacts','Contact Map'),'url'=>array('googleMaps')),
    array('label'=>Yii::t('contacts','Saved Maps')),
);
$this->actionMenu = $this->formatMenu($menuItems);
?>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'maps-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>Saved Maps</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	// 'columns'=>$columns,
	// 'columnSelectorId'=>'contacts-column-selector',
    'columns'=>array(
        array(
            'name'=>'name',
            'type'=>'raw',
            'value'=>'CHtml::link($data->name,Yii::app()->controller->createUrl("googleMaps",array("loadMap"=>$data->id)))',
        ),
        array(
            'name'=>'owner',
            'type'=>'raw',
            'value'=>'User::getUserLinks($data->owner)'
        ),
        array(
            'name'=>'contactId',
            'type'=>'raw',
            'value'=>'!is_null(Contacts::model()->findByPk($data->contactId))?CHtml::link(Contacts::model()->findByPk($data->contactId)->name,Yii::app()->controller->createUrl("/contacts/contacts/view/",array("id"=>$data->contactId))):"None"',
        ),
        'zoom',
        array(
            'header'=>Yii::t('contacts','Center Coordinates'),
            'type'=>'raw',
            'value'=>'"(".$data->centerLat.", ".$data->centerLng.")"'
        ),
        
    ),
));