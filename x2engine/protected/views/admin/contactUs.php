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
?><h1><?php echo Yii::t('admin','Contact Us');?></h1>
<?php echo Yii::t('admin','X2Engine Inc. is the company behind X2Engine CRM - a high-performance contact management and customer relations management web application. X2Engine Inc. can offer to your organization professional support and training on X2Engine CRM.  Please fill out the form below to contact us.');?>
<form name="contact-us" method="POST"><br />
	<b><?php echo Yii::t('app','E-Mail');?>:</b><br /><input type="text" name="email" /><br />
	<b><?php echo Yii::t('admin','Subject');?>:</b><br /><input type="text" name="subject" size="60" /><br />
	<b><?php echo Yii::t('app','Message Body');?>:</b><br /><textarea style="height:200px;width:590px;" name="body"></textarea><br />
	<input class="x2-button" type="submit" value="<?php echo Yii::t('app','Send Email');?>" />
</form>