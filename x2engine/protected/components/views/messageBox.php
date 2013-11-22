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
<div id="motd-box"><?php echo Yii::t('app',$content);?></div>
<?php
if(Yii::app()->params->isAdmin)
	echo CHtml::link(Yii::t('app','Edit Message'),'#',array('onclick'=>"$('#motd-form').show(); $('#motd-link').hide(); return false;",'id'=>'motdLink'));
?>
<div id="motd-form" style="display:none;">
<?php
echo CHtml::beginForm();

echo CHtml::textArea('message','',array('cols'=>16,'onclick'=>'clearText(this)'));
echo CHtml::ajaxSubmitButton('Submit',
	array('/site/motd'),
	array('update'=>'#motd-box'),
	array('onclick'=>"$('#motd-form').hide(); $('#motd-link').show();", 'class'=>'x2-button')
	);

echo CHtml::endForm();
?>
</div>