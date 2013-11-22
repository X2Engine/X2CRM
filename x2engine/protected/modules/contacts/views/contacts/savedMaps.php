<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
$this->pageTitle=Yii::t('contacts','Saved Maps');
$menuItems = array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
    array('label'=>Yii::t('contacts','Import Contacts'),'url'=>array('importExcel')),
	array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('export')),
    array('label'=>Yii::t('contacts','Contact Map'),'url'=>array('googleMaps')),
    array('label'=>Yii::t('contacts','Saved Maps')),
);
$this->actionMenu = $this->formatMenu($menuItems);
?>
<?php

?>
<div class='flush-grid-view'>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'maps-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title icon contacts"><h2>'.Yii::t('contacts','Saved Maps').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
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
            'value'=>'!is_null(Contacts::model()->findByPk($data->contactId))?CHtml::link(Contacts::model()->findByPk($data->contactId)->name,array("/contacts/contacts/view","id"=>$data->contactId)):"None"',
        ),
        'zoom',
        array(
            'header'=>Yii::t('contacts','Center Coordinates'),
            'type'=>'raw',
            'value'=>'"(".$data->centerLat.", ".$data->centerLng.")"'
        ),
        array(
            'header'=>Yii::t('contacts','Delete Map'),
            'type'=>'raw',
            'value'=>'CHtml::link("Delete","#",array("submit"=>"deleteMap?id=".$data->id,"confirm"=>"Are you sure you want to delete this map?"))',
        ),

    ),
));
?>
</div>
