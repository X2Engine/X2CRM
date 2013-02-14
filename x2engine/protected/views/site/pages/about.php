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


$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('app','About');
?>
<?php
$logo = Yii::app()->baseUrl.'/images/x2engine_crm.png';
if(Yii::app()->params->edition==='pro')
	$logo = Yii::app()->baseUrl.'/images/x2engine_crm_pro.png';
echo CHtml::image($logo,'',array('class'=>'left'));
Yii::app()->clientScript->registerScript('loadJqueryVersion',"$('#jqueryVersion').html($().jquery);",CClientScript::POS_READY);
?>

<div class="form left" style="margin-left:10px;width:500px;clear:none;">
	<b>Version <?php echo Yii::app()->params->version;?><br>
	<?php if(Yii::app()->params->edition==='pro') echo 'Professional Edition'; ?></b><br>
	<?php echo Yii::app()->dateFormatter->formatDateTime(Yii::app()->params->buildDate,'medium',null); ?>.<br><br>
	<?php
	if(Yii::app()->params->edition==='opensource')
		echo Yii::t('app','X2Engine is an open source Customer Relationship Management application <br>designed by John Roberts and licensed under the {link}.',array(
			'{link}'=>CHtml::link(Yii::t('app','BSD License'),Yii::app()->getBaseUrl().'/LICENSE.txt')
		));
	else
		echo Yii::t('app','X2Engine is a Customer Relationship Management application <br>designed by John Roberts.');
	?>
	<?php //echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/x2footer.png','',array('style'=>'display:block;margin:5px 0;')); ?><br><br>
	<div style="clear:both">For customer and community support: <a href="http://www.x2engine.com/">www.x2engine.com</a></div>
	<div id="about-intro">
		<h4><?php echo Yii::t('app','Address');?></h4>
		<ul>
		<li>X2Engine Inc.</li>
		<li>PO Box 66752</li>
		<li>Scotts Valley, California 95067</li>
		<li>USA</li>
		<li><a href="http://www.x2engine.com/">www.x2engine.com</a></li>
		</ul>
		<h4><?php echo Yii::t('app','Core team');?></h4>
		<ul>
		<li>John Roberts</li>
		<li>Jake Houser</li>
		<li>Matthew Pearson</li>
		<li>Demitri Morgan</li>
		</ul>
		<h4><?php echo Yii::t('app','Contributors');?></h4>
		<ul>
		<li>DJ Visbal</li>
		<li>John Mendonca</li>
		<li>Haley Sedam</li>
		<li>Andrew Hoffman</li>
		<li>Zach Louden</li>
		<li>Steve Lance</li>
		</ul>
	</div>
	<div id="about-legal">
		Copyright © 2013 X2Engine Inc. The Program is provided AS IS, without warranty.
		<?php if(Yii::app()->params->edition==='opensource') echo 'Licensed under the ',CHtml::link('BSD License',Yii::app()->getBaseUrl().'/LICENSE.txt'),'.'; ?>
	</div>
	<br><hr>
	<div id="about-credits">
		<!--<div class="about-list" style="height:450px;width:auto;overflow-y:scroll;border:1px solid #ddd;padding:10px;"></div>
		<hr>-->
		<h4><?php echo Yii::t('about','Version Info'); ?></h4>
		<ul>
			<li>X2Engine: <?php echo Yii::app()->params->version;?></li>
			<!--<?php echo Yii::t('about','Build'); ?>: 1234<br>-->
			<li>Yii: <?php echo Yii::getVersion(); ?></li>
			<li>jQuery: <span id="jqueryVersion"></span></li>
			<li>PHP: <?php echo phpversion(); ?></li>
			<!--jQuery Mobile: 1.0b2<br>-->
		</ul>
		<h4><?php echo Yii::t('about','Code Base'); ?></h4>
		<ul>
			<li>GitHub: <a href="https://github.com/X2Engine/X2Engine" target="_blank">https://github.com/X2Engine/X2Engine</a></li>
			<li>Google Code: <a href="http://code.google.com/p/x2engine/" target="_blank">http://code.google.com/p/x2engine/</a></li>
			<li>SourceForge: <a href="https://sourceforge.net/projects/x2engine/" target="_blank">https://sourceforge.net/projects/x2engine/</a></li>
			<!--BitBucket: <a href="https://bitbucket.org/X2Engine/X2Engine" target="_blank">https://bitbucket.org/X2Engine/X2Engine</a></li>-->
		</ul>
		
		<h4><?php echo Yii::t('about','Plugins/Extensions'); ?></h4>
		<ul>
			<li>Google API PHP Client:
				<a href="http://code.google.com/p/google-api-php-client/" target="_blank"><?php echo Yii::t('about','Project'); ?></a>
				<a href="http://www.apache.org/licenses/" target="_blank" class="no-underline" title="Apache License 2.0">[Apache]</a></li>
			<li>Modernizr:
				<a href="http://http://modernizr.com" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://modernizr.com/license/" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
			<li>ExColor:
				<a href="http://modcoder.org" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://modcoder.org/?page=license" target="_blank" class="no-underline" title="License">[License]</a></li>
			<li>Dragtable:
				<a href="http://jebaird.com/blog/dragtable-jquery-ui-widget-re-arrange-table-columns-drag-drop" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>colResizable: <a href="http://quocity.com/colresizable/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>formatCurrency: <a href="http://code.google.com/p/jquery-formatcurrency/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
			<li>phpMailer: <a href="http://quocity.com/colresizable/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
			<li>FullCalendar: <a href="http://arshaw.com/fullcalendar/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<!--<li>JS SHA-256: <a href="http://www.webtoolkit.info/javascript-sha256.html" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.webtoolkit.info/license" target="_blank" class="no-underline" title="License">[License]</a></li>-->
			<li>CKEditor: <a href="http://www.ckeditor.com/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
			<li>CFile Class:
				<a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>EZip Class:
				<a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>CSaveRelationsBehavior Class:
				<a href="http://www.yiiframework.com/extension/save-relations-ar-behavior/" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
			<li>ERememberFiltersBehavior Class:
				<a href="http://www.yiiframework.com/extension/remember-filters-gridview/" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
			<li>qTip2: <a href="http://craigsworks.com/projects/qtip2/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>jStorage: <a href="http://www.jstorage.info/" target="_blank"><?php echo Yii::t('about','Developer'); ?></a>
				<a href="http://www.jstorage.info/static/license.txt" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
		</ul>
	</div>
</div>
<div id="about-map">
<a title="<?php echo Yii::t('about','Our office in downtown Santa Cruz'); ?>" target="_blank" href="http://maps.google.com/maps?q=1101+Pacific+Avenue+Suite+309+Santa+Cruz,+CA+95060+USA&hl=en&ll=37.03764,-122.189941&spn=3.231366,4.762573&sll=36.978421,-122.0327&sspn=0.404269,0.595322&vpsrc=6&hnear=1101+Pacific+Ave+%23210,+Santa+Cruz,+California+95060&t=m&z=8">
<?php //echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/office.png',''); ?>
<img src="http://maps.googleapis.com/maps/api/staticmap?sensor=false&size=260x300&markers=|36.972164,-122.025898&hl=en&center=37.4,-122.1&zoom=8">
</a>
X2Engine Inc. is headquartered in beautiful Santa Cruz, California. We really enjoy meeting customers and partners whenever possible and encourage you to visit our offices when you find yourself in the San Francisco bay area.
</div>












