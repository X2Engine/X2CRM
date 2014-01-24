<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
