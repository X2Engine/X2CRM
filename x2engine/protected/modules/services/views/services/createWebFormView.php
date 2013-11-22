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
    array('label'=>Yii::t('services','All Cases'), 'url'=>array('index')),
    array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
    array('label'=>Yii::t('services','Create Web Form')),
);

$this->actionMenu = $this->formatMenu($menuItems);

?>
<div class="page-title icon services">
    <h2><?php echo Yii::t('marketing','Service Cases Web Form'); ?></h2>
</div>
<div class="form">
<?php 
echo Yii::t('marketing',
    'Create a public form to receive new services cases. When the form is submitted, a new '.
    'service case will be created, and the case # will be sent to the email address '.
    'provided in the form.'); 
?>
</div>
<?php
$this->renderPartial ('application.components.views._createWebForm', 
    array(
        'forms'=>$forms,
        'webFormType'=>'service'
    )
);
?>
