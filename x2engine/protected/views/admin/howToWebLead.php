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
?>
<h2><?php echo Yii::t('admin','How to set up a Web Lead form'); ?></h2>
<p>
<?php echo CHtml::image(Yii::app()->getBaseUrl().'/images/webLead.gif','',array('style'=>'float:left;margin-right:10px;border:1px solid #ddd;')); ?>
<?php echo Yii::t('admin','The Web Lead capture form is very useful if you have a public website. Visitors can submit their contact information and questions, and X2Contacts will automatically create contact records for them.'); ?>
<br /><br />
<?php echo Yii::t('admin','To install the web lead form, simply copy the following HTML into your website:'); ?>

<div style="width:520px; float:right;" id="code-snippet">
<?php
$webLead = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME'];
$webLead .= Yii::app()->getBaseUrl().'/webLead.php';


echo CHtml::encode('<iframe hspace="0" scrolling="no" src="'.$webLead.'" frameborder="0" width="300" height="320"></iframe>'); ?>
</div>
</p>