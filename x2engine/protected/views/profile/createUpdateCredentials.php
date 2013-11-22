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
$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile'), 'url'=>array('view','id'=>$profile->id)),
	array('label'=>Yii::t('profile','Edit Profile'),'url'=>array('update','id'=>$profile->id)),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$profile->id),'visible'=>($profile->id==Yii::app()->user->id)),
	array('label'=>Yii::t('profile','Change Password'),'url'=>array('changePassword','id'=>$profile->id),'visible'=>($profile->id==Yii::app()->user->id)),
	array('label'=>Yii::t('profile','Manage Apps'),'url'=>array('manageCredentials','id'=>$profile->id))
);

?>

<div class="page-title"><h2><?php echo $model->pageTitle; ?></h2></div>
<div style="padding:10px; display:inline-block;">
<?php

$this->renderPartial('_credentialsForm', array('model' => $model, 'includeTitle' => false,'user'=>$profile->user));

echo "<span>$message</span>";
?>

</div>
