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




Yii::app()->clientScript->registerCss ('topSitesCss', "
#sites-box{
    min-height: 25px;
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
#widget_TopSites .portlet-content{
    padding: 0;
}
#site-url-container input {
    width: 120px;
    padding: 5px;
}
#site-url-container #top-site-submit-button {
    width: 60px;
}
#top-sites-form {
    padding: 2px;
}
#sites-box {
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

.top-sites-input {
    margin-left: 5px;
    display:inline-block;
}
.top-sites-input label{
    display:block;
    font-weight:bold;
}
.top-sites-input.submit {
    vertical-align: top;
}

#top-sites-table a.delete-top-site-link {
    display: none;
    text-decoration: none;
}
#top-sites-table tr:hover {
    background-color: #F5F4DE;
}

#top-sites-table tr:hover a.delete-top-site-link {
    display: block;
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
<div id="sites-container-fix">
<div id="sites-container">
<div id="top-sites-container">
<div id="sites-box">

<table id='top-sites-table'>
<?php
foreach($data as $entry){
?>
    <tr>
        <td>
        <?php
            echo CHtml::link(
               $entry['title'], URL::prependProto($entry['url']), array('target'=>'_blank'));
        ?>
        </td>
        <td class='delete-top-site-link-container'>
        <?php
        if(isset($entry['id']))
            echo CHtml::link(
                '[x]', array ('site/deleteURL', 'id' => isset($entry['id'])?$entry['id']:'#'),
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
</div><!-- #sites-box -->
<form id='top-sites-form'>
    <div id='site-url-container'>
        <div class="top-sites-input">
        <?php
            echo CHtml::label(Yii::t('app', 'Title:'), 'url-title');
            echo CHtml::textField('url-title', '', array('class'=>'x2-textfield'));
        ?>
        </div><!-- .top-sites-input -->
        <div class="top-sites-input">
        <?php 
            echo CHtml::label(Yii::t('app','Link:'),'url-url');
            echo CHtml::textField('url-url', '',array('class'=>'x2-textfield'));
        ?>
        </div><!-- .top-sites-input -->
        <div class="top-sites-input submit">
        <?php
        echo CHtml::ajaxSubmitButton(
            Yii::t('app','Add Site'),
            array('/site/addSite'),
            array(
                'update'=>'sites-box',
                'success'=>"function(response){
                    x2.topSites.addSite (JSON.parse (response));
                    $('#url-title').val('');
                    $('#url-url').val('');
                }",
            ),
            array('class'=>'x2-button','id'=>'top-site-submit-button')
        );?>
        </div><!-- .top-sites-input -->
    </div>
    <!-- Submitted via ajax, no CSRF Token Required -->
</form>
</div>
</div>
</div>
