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

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
    array('label'=>Yii::t('contacts','View List'),'url'=>array('list','id'=>$model->id)),
	array('label'=>Yii::t('contacts','Edit List')),
	array('label'=>Yii::t('contacts','Delete List'),'url'=>'#', 'linkOptions'=>array('submit'=>array('deleteList','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);

?>

<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Edit List'); ?></h2></div>

<?php
echo $this->renderPartial('_listForm', array(
	'model'=>$model,
	'criteriaModels'=>$criteriaModels,
	// 'attributeList'=>$attributeList,
	'comparisonList'=>$comparisonList,
	'users'=>$users,
	'listTypes'=>$listTypes,
	'itemModel'=>$itemModel,
));
?>
