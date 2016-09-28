<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

$this->insertMenu(array(
    'feed', 'admin', 'create', 'map','invite',
));

$key = '';
$settings = Yii::app()->settings;
$creds = Credentials::model()->findByPk($settings->googleCredentialsId);
if ($creds && $creds->auth)
    $key = $creds->auth->apiKey;
$assetUrl = 'https://maps.googleapis.com/maps/api/js?libraries=visualization';
if (!empty($key))
    $assetUrl .= '&key='.$key;
Yii::app()->clientScript->registerScriptFile($assetUrl);

Yii::app()->clientScript->registerScript('maps-initialize',"
    var map, pointarray, ge, directionsDisplay;
    var center=$center;
    var markerFlag=true;
    var zoom=".(isset($zoom)?$zoom:"0").";
    var noHeatMap=true;
    var bounds=new google.maps.LatLngBounds();
    var directionsService=new google.maps.DirectionsService();
    function initialize() {
        directionsDisplay = new google.maps.DirectionsRenderer();
        var latLng = new google.maps.LatLng(center['lat'],center['lng']);
        bounds.extend(latLng);
        var mapOptions = {
            zoom: 3,
            mapTypeId: google.maps.MapTypeId.SATELLITE,
            center: latLng
        };
        if (zoom != 0) {
            mapOptions.zoom = zoom;
        }
        map = new google.maps.Map(document.getElementById('map_canvas'),
            mapOptions);
        directionsDisplay.setMap(map);
        directionsDisplay.setPanel(document.getElementById('directions-panel'));
    }

initialize();
");

Yii::app()->clientScript->registerScript('maps-qtip', "
var center=$center;

function addLargeMapMarker(pos, contents, open = false) {
        var latLng = new google.maps.LatLng(pos['lat'],pos['lng']);
        var marker = new google.maps.Marker({
            position: latLng,
            map: map
        });
    if(typeof infowindow==='undefined'){
        var infowindow = new google.maps.InfoWindow({
            content: contents
        });
        if (open)
            infowindow.open(map, marker);
    }
    google.maps.event.addListener(infowindow,'domready',function(){
        $('#corporate-directions').click(function(e){
            e.preventDefault();
            getDirections('corporate');
        });
        $('#personal-directions').click(function(e){
            e.preventDefault();
            getDirections('personal');
        });
    });

    google.maps.event.addListener(marker,'click',function(){
        infowindow.open(map,marker);
    });

    return marker;
}

function refreshQtip() {
        var fields=new Array('link','directions');
        if (center) {
            addLargeMapMarker(center, '".CHtml::link(Yii::t('contacts', 'Link to {User} Record', array(
                    '{User}' => (Modules::displayName(false, 'Users')),
                )), array('users/view', 'id' => 1))."');
        }

        if (noHeatMap) {
            var locations = ".$locations.";
            $.each(locations, function(i, loc) {
                var details = loc.info + '<br />' + loc.time;
                addLargeMapMarker(loc, details);
            });
        }
}
refreshQtip();
");

Yii::app()->clientScript->registerScript('map-controls',"
$('#mapControlForm').submit(function(){
    var tags=new Array();
    $.each($(this).find ('.x2-tag-list a'),function(){
        tags.push($(this).text());
    });
    $('#params_tags').val(tags);
});
$(window).resize(function(){
   google.maps.event.trigger(map,'resize');
});
");
?>

<div class='page-title icon contacts'>
    <h2><?php echo Yii::t('users','User Map');?></h2>
</div>
<div id="controls" class="form">

<?php
   $form = $this->beginWidget('CActiveForm', array(
        'action' => 'googleMaps',
        'id' => 'mapControlForm',
        'enableAjaxValidation' => false,
        'method' => 'POST',
    ));
    // $range = 30; //$model->dateRange;
    // echo $startDate .' '.$endDate;

    ?>
    <div class="row">
        <h2 style='margin-top: 5px'><?php echo Yii::t('contacts','Filters');?></h2>
        <div class="cell">
            <label><?php echo Yii::t('contacts','Location Type'); ?></label>
            <?php echo CHtml::dropDownList (
                'params[locationType]',
                null,
                Locations::getLocationTypes(),
                array (
                    'multiple' => 'multiple',
                    'data-selected-text' => Yii::t('app', 'filters(s)'),
                    'class' => 'x2-multiselect-dropdown'
                )
            ); ?>
        </div>

        <div class="cell">
            <?php echo CHtml::submitButton(Yii::t('charts', 'Go'), array('class' => 'x2-button', 'style' => 'margin-top:13px;')); ?>
        </div>
    </div>
    <div class="row">



    </div>
    <?php $this->endWidget();?>
</div>
<div style="width:30%;float:left;display:none;" id="directions-box">
    <div style="width:auto;height:788px;margin-bottom:0px;overflow-y:scroll;" class="form" id="directions-panel"></div>
</div>
<div id="map_canvas" style="height: 800px; width:100%;float:right;"></div>

