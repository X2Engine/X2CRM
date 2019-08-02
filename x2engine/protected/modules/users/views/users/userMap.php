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




$this->insertMenu(array(
    'feed', 'admin', 'create', 'map', 'invite',
));

$key = '';
$settings = Yii::app()->settings;
$key = $settings->getGoogleApiKey('maps');
$assetUrl = 'https://maps.googleapis.com/maps/api/js?libraries=visualization&callback=initializeMap';
if (!empty($key))
    $assetUrl .= '&key=' . $key;
$userLinks = array();
foreach ($locations as $location) {
    $user = User::model()->findByPk($location['recordId']);
    if ($user) {
        $userLinks[$location['recordId']] = $user->getLink(array('style' => 'text-decoration:none;'));
    }
}
Yii::app()->clientScript->registerScript('maps-initialize', "
var corporateAddress=".CJSON::encode(Yii::app()->settings->corporateAddress).";
var personalAddress=".CJSON::encode(Yii::app()->params->profile->address).";
var map, directionsService, directionsDisplay;
function initializeMap() {
    var pointarray, ge, latlngbounds;
    var center=$center;
    var zoom=" . (isset($zoom) ? $zoom : "0") . ";
        
    directionsDisplay = new google.maps.DirectionsRenderer();
    directionsService=new google.maps.DirectionsService();
    var latLng = new google.maps.LatLng(center['lat'],center['lng']);
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

    var locations = " . json_encode($locations) . ";
    var userLinks = " . json_encode($userLinks) . ";
    $.each(locations, function(i, loc) {
        var details = userLinks[loc['recordId']];
        if(loc.type){
            details += '<br>'+loc.type;
        }
        if(loc.info){
            details += '<br>'+loc.info;
        }
        if(loc.time){
            details += '<br>'+loc.time;
        }
        addLargeMapMarker(loc, details, true);
    });
    google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
        this.setZoom(map.getZoom()-1);
      });
    map.fitBounds(latlngbounds);

    function addLargeMapMarker(pos, contents, directionsLink = false, open = true) {
        latlngbounds = new google.maps.LatLngBounds();
        var latLng = new google.maps.LatLng(pos['lat'],pos['lng']);
        latlngbounds.extend(latLng);
        var marker = new google.maps.Marker({
            position: latLng,
            map: map
        });

        if (directionsLink) {
            if (corporateAddress)
                contents += '<br /><a class=\"directions-link\" data-type=corporate data-lat=' + pos['lat'] + ' data-lng=' + pos['lng'] + ' href=\"#\">".Yii::t('contacts', 'Directions from Corporate')."</a>';
            if (personalAddress) {
                contents += '<br /><a class=\"directions-link\" data-type=personal data-lat=' + pos['lat'] + ' data-lng=' + pos['lng'] + ' href=\"#\">".Yii::t('contacts', 'Directions from Personal Address')."</a>';
            }
        }
        if(typeof infowindow==='undefined'){
            var infowindow = new google.maps.InfoWindow({
                content: contents
            });
            if (open)
                infowindow.open(map, marker);
            google.maps.event.addListener(infowindow,'domready',function(){ // Set up directions handlers
                $('#corporate-directions').click(function(e){
                    e.preventDefault();
                    getDirections('corporate');
                    $('#clear-route').show();
                });
                $('#personal-directions').click(function(e){
                    e.preventDefault();
                    getDirections('personal');
                    $('#clear-route').show();
                });
                $('.directions-link').click(function(evt) {
                    evt.preventDefault();
                    var type = $(this).data('type'),
                        lat = $(this).data('lat'),
                        lng = $(this).data('lng');
                    getDirections(type, lat, lng);
                    $('#clear-route').show();
                });
            });
        }

        google.maps.event.addListener(marker,'click',function(){
            infowindow.open(map,marker);
        });

        return marker;
    }
}

function getDirections(type, lat = null, lng = null){
    var center=map.getCenter();
    if (!lat || !lng)
        var latLng = new google.maps.LatLng(center['lat'],center['lng']);
    else
        var latLng = new google.maps.LatLng(lat,lng);
    if(type=='corporate') {
        if (corporateAddress){
            var request = {
                origin:corporateAddress,
                destination:latLng,
                travelMode: google.maps.TravelMode.DRIVING
            };
        }else{
            alert('Invalid corporate address.');
        }
    }
    if(type=='personal') {
        if (personalAddress){
            var request = {
                origin:personalAddress,
                destination:latLng,
                travelMode: google.maps.TravelMode.DRIVING
            };
        }else{
            alert('Invalid personal address.');
        }
    }
    if(typeof request!=='undefined'){
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
                $('#map_canvas').width('70%');
                $('#directions-box').show();
            }else if(status=='ZERO_RESULTS'){
                alert('No valid route found.');
            }
        });
    }
}
", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('user-map-controls', "
$('#clear-route').click(function() {
    directionsDisplay.setMap(null); // Reset DirectionsRenderer
    directionsDisplay = new google.maps.DirectionsRenderer();
    directionsDisplay.setMap(map);
    $('#directions-panel').text('');
    directionsDisplay.setPanel(document.getElementById('directions-panel'));
    $('#clear-route').hide();
    $('#hide-directions').click();
});
$('#hide-directions').click(function() {
    $('#directions-box').hide();
    $('#map_canvas').width('100%');
});
");
Yii::app()->clientScript->registerScriptFile($assetUrl, CClientScript::POS_END);
?>

<div class='page-title icon contacts'>
    <h2><?php echo Yii::t('users', 'User Location Map'); ?></h2>
</div>
<div id="controls" class="form">

    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'action' => 'userMap',
        'id' => 'mapControlForm',
        'enableAjaxValidation' => false,
        'method' => 'POST',
    ));
    // $range = 30; //$model->dateRange;
    // echo $startDate .' '.$endDate;
    ?>
    <div class="row">
        <h2 style='margin-top: 5px'><?php echo Yii::t('contacts', 'Filters'); ?></h2>
        <div class="cell">
            <label><?php echo Yii::t('users', 'User'); ?></label>
            <?php
            echo CHtml::dropDownList(
                    'params[users]', $selectedUsers, $users, array(
                'multiple' => 'multiple',
                'data-selected-text' => Yii::t('app', 'filters(s)'),
                'class' => 'x2-multiselect-dropdown'
                    )
            );
            ?>
        </div>
        <div class="cell">
            <label><?php echo Yii::t('users', 'Date/Time'); ?></label>
            <?php
            Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
            echo Yii::app()->controller->widget('CJuiDateTimePicker', array(
                'name' => 'params[timestamp]',
                'value' => Formatter::formatDateTime($timestamp),
                'mode' => 'datetime', //use "time","date" or "datetime" (default)
                'options' => array(// jquery options
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'ampm' => Formatter::formatAMPM(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ),
                'htmlOptions' => array(
                    'title' => Yii::t('users', 'Date/Time'),
                ),
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                    ), true);
            ?>
        </div>

        <div class="cell">
            <?php echo CHtml::submitButton(Yii::t('charts', 'Go'), array('class' => 'x2-button', 'style' => 'margin-top:13px;')); ?>
        </div>
    </div>
    <div class="row">



    </div>
    <?php $this->endWidget(); ?>
</div>
<div style="width:30%;float:left;display:none;" id="directions-box">
    <div style="width:auto;height:788px;margin-bottom:0px;overflow-y:scroll;" class="form" id="directions-panel"></div>
    <button id="hide-directions" class="x2-button"><?php echo Yii::t('contacts', 'Hide Directions'); ?></button>
</div>
<div id="map_canvas" style="height: 800px; width:100%;float:right;"></div>

<button id="clear-route" class="x2-button" style="display:none"><?php echo Yii::t('contacts', 'Clear Route'); ?></button>
