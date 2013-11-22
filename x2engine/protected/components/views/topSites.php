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

//Reset widget settings if implementing TopSites widget
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
$siteContainerFixHeight = 337;

Yii::app()->clientScript->registerCss ('topSitesCss', "
#top-sites-form {
    padding: 2px;
}
#site-box {
    padding: 5px;
}
#sites-box table td:first-child {
    width: 80%;
}
#sites-box table th {
    font-size: 13px;
    font-family: Arial, Helvetica, sans-serif;
}
#top-sites-container .delete-top-site-link-container {
    text-align: center;
}
#top-sites-container .site-delete-button-row-header {
}
");

Yii::app()->clientScript->registerScript('updateURLs', "
x2.topSites = {};
/*$(document).ready(updateURLs());
x2.topSites.updateURLs = function (){
    $.ajax({
        type: 'POST',
        url: '".$this->controller->createUrl('/site/getURLs',array('url'=>Yii::app()->request->requestUri))."',
        success:
        function(data){
            $('#sites-box').html(data);
        }
    });
}*/

$('#sites-container').resizable({
    handles: 's',
    minHeight: 75,
    alsoResize: '#sites-container, #sites-box',
    stop: function(event, ui){
        $.post(
            '".Yii::app()->createUrl("/site/saveWidgetHeight")."',
            {
                Widget: 'TopSites',
                Height: {topsitesHeight: parseInt($('topsites-box').css('height'))}
            }
        );
    }
});

$(document).on ('click', '#top-sites-container .delete-top-site-link', function (evt) {
    evt.preventDefault ();
    var that = $(this);
    $.ajax ({
        url: $(this).attr ('href'),
        success: function () {
            $(that).closest ('tr').remove ();
        }
    });
    return false;
});

x2.topSites.addSite = function (links) {
    var newRow = $('<tr>').append (
        $('<td>').append ($(links[0])),
        $('<td>', {'class': 'delete-top-site-link-container'}).append ($(links[1]))
    );
    $('#top-sites-table').append ($(newRow));
};

",CClientScript::POS_HEAD);

?>
<div id="sites-container-fix" style="height:<?php echo $siteContainerFixHeight; ?>px">
<div id="sites-container" style="height:<?php echo $siteContainerHeight; ?>px">
<div id="top-sites-container" style="height:<?php echo $topsitesHeight;?>px; margin-bottom: 20px;">
<div id="sites-box" style="height:<?php echo $topsitesHeight;?>px">

<table id='top-sites-table'>
    <tr>
        <th>
            <?php echo Yii::t('app',"Link") ?>
        </th>
        <th class='site-delete-button-row-header'>
            <?php echo Yii::t('app',"Delete"); ?>
        </th>
    </tr>
<?php
foreach($data as $entry){
?>
    <tr>
        <td>
        <?php
            echo CHtml::link(
                Yii::t('app', $entry['title']), $entry['url'], array('target'=>'_blank'));
        ?>
        </td>
        <td class='delete-top-site-link-container'>
        <?php
        if(isset($entry['id']))
            echo CHtml::link(
                '[x]', array ('site/DeleteURL', 'id' => isset($entry['id'])?$entry['id']:'#'),
                array (
                    'title' => Yii::t('app', 'Delete Link'),
                    'class' => 'delete-top-site-link',
                    'target' => '_blank'
                ));
        ?>
        </td>
    </tr>
<?php
}
?>
</table>
</div>
<form id='top-sites-form'>
    <div id='site-url-container'
     style="height: <?php echo $urlTitleContainerHeight;?>px; margin-bottom:35px;">
        <?php echo Yii::t('app','Title:').
            CHtml::textField('url-title', '',array('style'=>"height: ".$urlTitleHeight."px;"));?>
        <br/>
        <?php echo Yii::t('app','Link:').
            CHtml::textField('url-url', '',array('style'=>"height: ".$urlTitleHeight."px;"));?>
    </div>
<?php
echo CHtml::ajaxSubmitButton(
    Yii::t('app','Add Site'),
    array('/site/addSite'),
    array(
        'update'=>'site-box',
        'success'=>"function(response){
            x2.topSites.addSite (JSON.parse (response));
            $('#url-title').val('');
            $('#url-url').val('');
        }",
    ),
    array('class'=>'x2-button','id'=>'top-site-submit-button')
);?>
</form>
</div>
</div>
</div>
