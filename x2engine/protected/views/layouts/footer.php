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

Yii::app()->getClientScript()->registerScript('logos',base64_decode(
	'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
	.'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
	.'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));
		
// Yii::app()->params->edition = 'opensource';
?>
<div id="footer">
	<b>X2CRM <?php echo Yii::app()->params->version; ?>
	<?php echo Yii::app()->params->edition==='pro'? 'Professional Edition' : 'Open Source Edition'; ?></b> | 
	<?php echo CHtml::link('X2Touch',array('/x2touch')); ?> | 
	<?php echo CHtml::link(Yii::t('app','About'),array('/site/page','view'=>'about')); ?> | 
	
	<a href="http://www.x2engine.com/">Powered by X2Engine</a>. <br>Copyright &copy; 2011-<?php echo date('Y'); ?> X2Engine Inc.
	<?php if(Yii::app()->params->edition==='opensource') { ?>
		Released as free software without warranties under the <a href="<?php echo Yii::app()->getBaseUrl(); ?>/LICENSE.txt" title="GNU Affero General Public License version 3">GNU Affero GPL v3</a>.
	<?php } /* else { ?>
		<a href="<?php echo Yii::app()->getBaseUrl(); ?>/LICENSE.txt">License</a>
	<?php } */ ?>
	<br>
	<?php echo CHtml::link(CHtml::image(Yii::app()->getBaseUrl().'/images/powered_by_x2engine.png','',array('id'=>'powered-by-x2engine')),'http://www.x2engine.com/'); ?>
	<div id="response-time">
	<?php
	echo round(Yii::getLogger()->getExecutionTime()*1000), 'ms ';
	$peak_memory = memory_get_peak_usage(true);
    echo FileUtil::formatSize($peak_memory,2);
	?></div>
	
</div>
