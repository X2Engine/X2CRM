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

$menuItems = array(
	array('label'=>Yii::t('docs','List Docs'),'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'),'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'),'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
);

?>
<div class="page-title icon docs"><h2><?php
	if($this->action->id=='createEmail') {
		unset($menuItems[2]['url']);
		echo Yii::t('docs','Create Email Template');
	} else if($this->action->id == 'createQuote') {
		unset($menuItems[3]['url']);
		echo Yii::t('docs','Create Quote');
	} else {
		unset($menuItems[1]['url']);
		echo Yii::t('docs','Create Document');
	}

	?></h2>
</div>

<?php
$this->actionMenu = $this->formatMenu($menuItems);

echo $this->renderPartial('_form', array('model'=>$model,'users'=>$users));






