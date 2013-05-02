<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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
	$memory_units = array('b','kb','mb','gb','tb','pb');
	echo round($peak_memory/pow(1024,($memory_log=floor(log($peak_memory,1024)))),2).$memory_units[$memory_log];
	?></div>
	
</div>
