<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
$siteContainerFixHeight = 315;
?>
<div id="sites-container-fix" style="height:<?php echo $siteContainerFixHeight; ?>px">
<div id="sites-container" style="height:<?php echo $siteContainerHeight; ?>px">
<div id="top-sites-container" style="height:<?php echo $topsitesHeight;?>px; margin-bottom: 20px;">
<div id="sites-box" style="height:<?php echo $topsitesHeight;?>px">
<?php
$echoSTR = "<table><tr><th>".Yii::t('app',"Link")."</th><th>".Yii::t('app',"Delete")."</th></tr>";
foreach($data as $entry){
	$echoSTR .=  "<tr><td>". CHtml::link(Yii::t('app', $entry['title']), $entry['url'], array('target'=>'_blank')) ."</td>
                      <td>". CHtml::link(Yii::t('app',"Delete"), $entry['url'])."</td></tr>";
}
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
	}
});
",CClientScript::POS_HEAD);
?>
</div>
<form>
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
);?>
</form>
</div></div></div>
