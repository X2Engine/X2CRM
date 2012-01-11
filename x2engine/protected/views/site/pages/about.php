<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('app','About');

Yii::app()->clientScript->registerScript('loadJqueryVersion',"$('#jqueryVersion').html($().jquery);",CClientScript::POS_READY);
?>
<a title="<?php echo Yii::t('about','Our office in downtown Santa Cruz'); ?>" target="_blank" href="http://maps.google.com/maps?q=1101+Pacific+Avenue+Suite+210+Santa+Cruz,+CA+95060+USA&hl=en&ll=37.03764,-122.189941&spn=3.231366,4.762573&sll=36.978421,-122.0327&sspn=0.404269,0.595322&vpsrc=6&hnear=1101+Pacific+Ave+%23210,+Santa+Cruz,+California+95060&t=m&z=8">
<?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/office.png','',array('style'=>'float:right;margin-top:0px;margin-right:-20px;')); ?>
</a>
<h3><b><?php echo Yii::t('about','About X2Engine'); ?></b></h3><b>Beta <?php echo Yii::app()->params->version;?></b> <?php echo Yii::app()->dateFormatter->formatDateTime(Yii::app()->params->buildDate,'medium',null); ?>.
	<?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/x2engine_small.png','',array('style'=>'display:block;margin:5px 0;')); ?>

<div id="about-intro">
	<?php echo Yii::t('app','X2Engine is a Customer Relationship Management application designed by John Roberts. Special thanks to software engineers Jake Houser and Matthew Pearson, along with many other folks who helped make this release possible.'); ?><br><br>
</div>
<div style="width:200px;float:left;margin-bottom:10px;">
	<b>Headquarters</b><br>
	University Town Center<br>
	1101 Pacific Avenue<br>
	Suite 210<br>
	Santa Cruz, California 95060<br>
	USA
</div>

<div style="clear:both">For customer and community support: <a href="http://www.x2engine.com/">www.x2engine.com</a><br><br></div>
<div id="about-legal">
	Copyright © 2011 X2Engine Inc. The Program is provided AS IS, without warranty. Licensed under the <?php echo CHtml::link('BSD License',Yii::app()->getBaseUrl().'/LICENSE.txt'); ?>.
	<?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/x2footer.png','',array('style'=>'display:block;clear:both;')); ?>
</div>

<div class="form" id="about-credits">
	<!--<div class="about-list" style="height:450px;width:auto;overflow-y:scroll;border:1px solid #ddd;padding:10px;"></div>
	<hr>-->
	<h4><?php echo Yii::t('about','Version Info'); ?></h4>
	X2Engine: <?php echo Yii::app()->params->version;?><br>
	<!--<?php echo Yii::t('about','Build'); ?>: 1234<br>-->
	Yii: <?php echo Yii::getVersion(); ?><br>
	jQuery: <span id="jqueryVersion"></span><br>
	PHP: <?php echo phpversion(); ?><br><br>
	<!--jQuery Mobile: 1.0b2<br>-->
	<h4><?php echo Yii::t('about','Code Base'); ?></h4>
	GitHub: <a href="https://github.com/X2Engine/X2Engine" target="_blank">https://github.com/X2Engine/X2Engine</a><br>
	Google Code: <a href="http://code.google.com/p/x2engine/" target="_blank">http://code.google.com/p/x2engine/</a><br>
	SourceForge: <a href="https://sourceforge.net/projects/x2engine/" target="_blank">https://sourceforge.net/projects/x2engine/</a><br>
	<!--BitBucket: <a href="https://bitbucket.org/X2Engine/X2Engine" target="_blank">https://bitbucket.org/X2Engine/X2Engine</a><br>--><br>
	<h4><?php echo Yii::t('about','Plugins/Extensions'); ?></h4>
	YUI Editor:
	<a href="http://developer.yahoo.com/yui" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
	<a href="http://yuilibrary.com/license/" target="_blank" class="no-underline" title="License">[License]</a><br>
	CFile Class:
	<a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a>
	<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a><br>
	EZip Class:
	<a href="http://www.yiiframework.com/extension/ezip" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a>
	<a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a><br>
	ExColor:
	<a href="http://modcoder.org" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
	<a href="http://modcoder.org/?page=license" target="_blank" class="no-underline" title="License">[License]</a><br>
	Dragtable:
	<a href="http://jebaird.com/blog/dragtable-jquery-ui-widget-re-arrange-table-columns-drag-drop" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
	<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a><br>
	colResizable: <a href="http://quocity.com/colresizable/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
	<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a><br>
	phpMailer: <a href="http://quocity.com/colresizable/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
	<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a><br>
</div>













