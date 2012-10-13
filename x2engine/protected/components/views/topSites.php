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
//Reset widget settings if implementing TopSites 
if(!isset(ProfileChild::getWidgetSettings()->TopSites)){
    Yii::app()->params->profile->widgetSettings = null;
}
//Create variables corresponding to pre-defined heights gained 
//through calling ProfileChild.
$widgetSettings = ProfileChild::getWidgetSettings();
$sitesSettings = $widgetSettings->TopSites;
$topsitesHeight = $sitesSettings->topsitesHeight;
$urlTitleHeight = $sitesSettings->urltitleHeight;

//Set variables to implement HTML divs
$topsitesContainerHeight = $topsitesHeight + 2;
$urlTitleContainerHeight = $urlTitleHeight + 30;
$siteContainerHeight = $topsitesHeight + $urlTitleHeight + 45;
$siteContainerFixHeight = 250;
?>
<div id="sites-container-fix" style="height:<?php echo $siteContainerFixHeight; ?>px">
<div id="sites-container" style="height:<?php echo $siteContainerHeight; ?>px">
<div id="top-sites-container" style="height:<?php echo $topsitesHeight;?>px; margin-bottom: 20px;">
<div id="sites-box" style="height:<?php echo $topsitesHeight;?>px">
<?php
$echoSTR = "<table><tr><th>".Yii::t('app',"Title")."</th><th>".Yii::t('app',"Link")."</th></tr>";
foreach($data as $entry)
	$echoSTR .=  "<tr><td>".$entry['title']."</td><td><a href='".$entry['url']."'>".Yii::t('app',"Link")."</a></td></tr>";

$echoSTR .= "</table>";
echo $echoSTR;
?>
<?php
$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');
Yii::app()->clientScript->registerScript('updateURLs', "
	$(document).ready(updateURLs());
	function updateURLs(){
		$.ajax({
			type: 'POST',
			url: '".$this->controller->createUrl('/site/getURLs?url='.Yii::app()->request->requestUri)."',
			success:
			function(data){
				$('#sites-box').html(data);
			}
		});
	}
;
",CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript("topSitesResize","
$('#sites-container').resizable({
	handles: 's',
	minHeight: 75,
	alsoResize: '#sites-container, #sites-box',
	stop: function(event, ui){
		$.post('".Yii::app()->createUrl("/site/saveWidgetHeight")."',           {Widget: 'TopSites',
			Height: {topsitesHeight: parseInt($('topsites-box').css('height'))}
			});
	},
});
",CClientScript::POS_HEAD);
?>
</div>
<?php echo CHtml::beginForm();?>
<div id='site-url-container' style="height: <?php echo $urlTitleContainerHeight;?>px; margin-bottom:35px;">
	<?php echo Yii::t('app','Title:').CHtml::textField('url-title', '',array('style'=>"height: ".$urlTitleHeight."px;"));?>
	<br/>
	<?php echo Yii::t('app','Link:').CHtml::textField('url-url', '',array('style'=>"height: ".$urlTitleHeight."px;"));?>
</div>
<?php
echo CHtml::ajaxSubmitButton(
	Yii::t('app','Add Site'),
	array('/site/addSite'),
	array(
		'update'=>'site-box',
		'success'=>"function(response){
			updateURLs();
			$('#url-title').val('');
			$('#url-url').val('');
		}",
	),
	array('class'=>'x2-button','id'=>'submit-button')
);
echo CHtml::endForm();?>
</div></div></div>
