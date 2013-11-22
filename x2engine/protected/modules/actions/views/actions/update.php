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
$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('actions','Action List'),'url'=>array('index')),
	array('label'=>Yii::t('actions','Create Action'),'url'=>array('create')), 
	array('label'=>Yii::t('actions','Edit Action')),
	array('label'=>Yii::t('contacts','Share Action'),'url'=>array('shareAction','id'=>$model->id)),
	array('label'=>Yii::t('actions','Delete Action'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);
?>
<div class="page-title icon actions">

<h2><?php
	if($model->type == 'event') {
		if($model->associationType=='none')
			echo Yii::t('actions','Update Event');
		else
			echo '<span class="no-bold">',Yii::t('actions','Update Event:'),'</span> ',$model->associationName;
	} else {
		if($model->associationType=='none')
			echo Yii::t('actions','Update Action');
		else
			echo '<span class="no-bold">',Yii::t('actions','Update Action:'),'</span> ',$model->associationName;
	}
?></h2>
</div>
<?php echo $this->renderPartial('_form', array('actionModel'=>$model, 'users'=>$users,'modelList'=>$modelList,'notifType'=>$notifType,'notifTime'=>$notifTime)); ?>