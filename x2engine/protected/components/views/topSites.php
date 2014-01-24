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
$siteContainerFixHeight = 317;

Yii::app()->clientScript->registerCss ('topSitesCss', "
#sites-box{
    height: 200px;
    width: auto;
    margin: 5px;
    padding: 0 4px;
    overflow-y: auto;
    word-wrap: break-word;
    line-height: 1.1em;
    font-size: 9pt;
    color: #555;
    background: #fcfcfc;
    border: 1px solid #ddd;
}
#site-url-container{
    height: 100px;
}
#widget_TopSites .portlet-content{
    padding: 0;
}
#site-url-container input {
    width: 120px;
    padding: 5px;
    margin-top: 10px;
}
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
