<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




// Authorized partner/reseller "About" page content

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/about-page.css');

$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->settings->appName . ' - ' . Yii::t('app','About');

Yii::app()->clientScript->registerScript('partnerAboutJS','
if(typeof x2 == "undefined") {
    x2 = {};
}
$("#about-map a").click(function(event) {
    if(this.getAttribute("href") == "") {
        event.preventDefault();
        alert('.json_encode(Yii::t('app','Replace the "href" attribute in this link to a Google Maps link to your headquarters, and the "title" attribute with an optional title, to produce a link to open it in Google Maps in a new tab.')).');
    }
});

',CClientScript::POS_READY);

?>
<?php
/* @start:about */
$logo = 'data:image/gif;base64,'.base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'partnerLogoBig_example.gif'));
echo CHtml::image($logo,'',array('class'=>'left'));
Yii::app()->clientScript->registerScript('loadJqueryVersion',"$('#jqueryVersion').html($().jquery);",CClientScript::POS_READY);
?>

<div class='center-column-container form left' >
    <em><?php echo Yii::t('app','Note, you are viewing content rendered from the file:');?></em><br /><br />
    <strong><?php echo 'protected'.str_replace(Yii::app()->basePath,'',__FILE__); ?></strong><br /><br />
    <em><?php echo Yii::t('app','See {howtolink} for instructions on how to edit this page.',array('{howtolink}'=>CHtml::link('Partner Branding How-To',array('/site/page','view'=>'brandingHowto')))); ?></em><br /><br />
    
	[Date of the current version]<br><br>
	<?php echo CHtml::encode(X2_PARTNER_PRODUCT_NAME); ?>
	<div id="about-intro">
        [Your company's info here]
	</div><!-- #about-intro -->
	<hr>
	<div id="about-credits">
		<h4><?php echo Yii::t('app','Version Info'); ?></h4>
		<ul>
            <li>[Your product name here] [Your version here]</li>
			<li><?php echo CHtml::link('X2Engine:',array('/site/page','view'=>'about'));?> <?php echo Yii::app()->params->version;?></li>
			<!--<?php echo Yii::t('app','Build'); ?>: 1234<br>-->
			<li>Yii: <?php echo Yii::getVersion(); ?></li>
			<li>jQuery: <span id="jqueryVersion"></span></li>
			<li>PHP: <?php echo phpversion(); ?></li>
			<!--jQuery Mobile: 1.0b2<br>-->
		</ul>
		<h4><?php echo Yii::t('app','Plugins/Extensions'); ?></h4>
        [List of extra plugins/extensions used by product (beyond those used by X2Engine) here]
	</div><!-- #about-credits -->
	<hr>
	<div id="about-legal">
        [Legal disclaimer here]
	</div><!-- #about-credits -->
    <br>
</div>
<div class='clearfix'></div>
<?php /* @end:about */ ?>
