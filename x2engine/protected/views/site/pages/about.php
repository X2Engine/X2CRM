<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('app','About');

Yii::app()->clientScript->registerScript('loadJqueryVersion',"
$(function() {
	$('#jqueryVersion').html($().jquery);
});
",CClientScript::POS_HEAD);
?>
<a title="<?php echo Yii::t('about','Our office in downtown Santa Cruz'); ?>" target="_blank" href="http://maps.google.com/maps?q=877+Cedar+Street+Suite+150+Santa+Cruz,+CA+95060+USA&hl=en&ll=36.971838,-122.211914&spn=3.444774,4.586792&sll=37.926868,-95.712891&sspn=51.515218,73.388672&vpsrc=6&t=m&z=8">
<?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/office.png','',array('style'=>'float:right;margin-top:50px;margin-right:-20px;')); ?>
</a>
<h3><b><?php echo Yii::t('about','About X2Engine'); ?></b></h3><b>Beta <?php echo Yii::app()->params->version;?></b> November 28, 2011.<br /><br />
<div id="about-intro">
	<?php echo Yii::t('app','X5CRM is a Customer Relationship Management application designed by John Roberts. Special thanks to software engineers Jake Houser and Matthew Pearson, along with many other folks who helped make this release possible.'); ?><br /><br />
</div>
<div style="width:200px;float:left;margin-bottom:10px;">
	<b>Headquarters</b><br />
	University Town Center<br />
	1101 Pacific Avenue<br />
	Suite 210<br />
	Santa Cruz, California 95060<br />
	USA
</div>

<div style="clear:both">For customer and community support: <a href="http://www.x2engine.com/">www.x2engine.com</a><br /><br /></div>
<div id="about-legal">
	Copyright © 2011 X2Engine Inc. The Program is provided AS IS, without warranty. Licensed under <?php echo CHtml::link('GPLv3',Yii::app()->getBaseUrl().'/GPL-3.0 License.txt'); ?>.
	This program is free software; you can redistribute it and/or modify it under the terms of the <?php echo CHtml::link('GNU General Public License version 3',Yii::app()->getBaseUrl().'/GPL-3.0 License.txt'); ?> as published by the Free Software Foundation including the additional permission set forth in the source code header.
	<?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/x2footer.png','',array('style'=>'display:block;clear:both;')); ?>
	The interactive user interfaces in modified source and object code versions of this program must display Appropriate Legal Notices, as required under Section 5 of the GNU General Public License version 3. In accordance with Section 7(b) of the GNU General Public License version 3, these Appropriate Legal Notices must retain the display of the "X2Engine Social Sales Management" logo. If the display of the logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices must display the words "X2Engine Social Sales Management".
	X2Engine and X2Contacts are trademarks of X2Contacts Inc.
</div>

<div class="form" id="about-credits">
<!--<div class="about-list" style="height:450px;width:auto;overflow-y:scroll;border:1px solid #ddd;padding:10px;"></div>
<hr>-->
<h4><?php echo Yii::t('about','Version Info'); ?></h4>
X5<b style="color:#666;">CRM</b>: <?php echo Yii::app()->params->version;?><br />
<!--<?php echo Yii::t('about','Build'); ?>: 1234<br />-->
Yii: <?php echo Yii::getVersion(); ?><br />
jQuery: <span id="jqueryVersion"></span><br />
PHP: <?php echo phpversion(); ?><br /><br />
<!--jQuery Mobile: 1.0b2<br />-->
<h4><?php echo Yii::t('about','Code Base'); ?></h4>
GitHub: <a href="https://github.com/X2Engine/X2Engine" target="_blank">https://github.com/X2Engine/X2Engine</a><br />
Google Code: <a href="http://code.google.com/p/x2engine/" target="_blank">http://code.google.com/p/x2engine/</a><br />
SourceForge: <a href="https://sourceforge.net/projects/x2engine/" target="_blank">https://sourceforge.net/projects/x2engine/</a><br />
<!--BitBucket: <a href="https://bitbucket.org/X2Engine/X2Engine" target="_blank">https://bitbucket.org/X2Engine/X2Engine</a><br />--><br />
<h4><?php echo Yii::t('about','Plugins/Extensions'); ?></h4>
YUI Editor: <a href="http://developer.yahoo.com/yui" target="_blank"><?php echo Yii::t('about','Developer'); ?></a><br />
CFile Class: <a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a><br />
EZip Class: <a href="http://www.yiiframework.com/extension/ezip" target="_blank"><?php echo Yii::t('about','Yii Extension'); ?></a><br />
ExColor: <a href="http://modcoder.org" target="_blank"><?php echo Yii::t('about','Developer'); ?></a><br />

</div>
