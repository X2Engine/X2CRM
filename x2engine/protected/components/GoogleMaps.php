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




/**
 * Google maps display widget.
 * 
 * Before running, the $location attribute needs to be set.
 * @package application.components 
 */
class GoogleMaps extends X2Widget {

    //public $visibility;
    public function init() {
        parent::init();
    }
    
    public $location;
    public $activityLocations;
    public $defaultFilter = 'address';
    public $modelParam = 'contactId';

    public function run() {
        if (!Yii::app()->settings->enableMaps)
            return;
    
        if((!isset($this->location) || empty($this->location)) && (!isset($this->activityLocations) || empty($this->activityLocations)))
            return;

        Yii::app()->clientScript->registerScript('setupGoogleMapsWidget','
        x2.googleMapsWidget = {};
        x2.googleMapsWidget.instantiated = false;
        x2.googleMapsWidget.markers = [];

        function initializeGoogleMapsWidget() {
            if($("#widget_GoogleMaps .portlet-content").is(":visible")) {
                runGoogleMapsWidget();
            } else {
                $(document).on ("showWidgets", function () { 
                    if($("#widget_GoogleMaps .portlet-content").is(":visible") && 
                       !x2.googleMapsWidget.instantiated) {
                        runGoogleMapsWidget(); 
                    } 
                });
            }
        }

        $(document).ready (function () {
            $("#locationType").change(function() {
                var locationType = $(this).val();
                if (locationType) {
                    $.each(x2.googleMapsWidget.markers, function(i, marker) {
                        if(locationType.indexOf(marker.category) === -1)
                            marker.setVisible(false);
                        else
                            marker.setVisible(true);
                    });
                } else {
                    $.each(x2.googleMapsWidget.markers, function(i, marker) {
                        marker.setVisible(false);
                    });
                }
            });
        });

        x2.googleMapsWidget.addMarker = function(location, type, infoContents, visible = true) {
            var marker = new google.maps.Marker({
                map: window.map,
                position: location,
                category: type,
                visible: visible
            });
            if ("'.$this->modelParam.'" === "userId") {
                marker.setIcon("https://maps.google.com/mapfiles/ms/icons/green-dot.png");
            }
            var infowindow = new google.maps.InfoWindow({
                content:infoContents
            });
            google.maps.event.addListener(marker,"click",function(){
                infowindow.open(map,marker);
            });
            x2.googleMapsWidget.markers.push(marker);
        };

        x2.googleMapsWidget.renderMarkers = function(geolocationCoords) {
            $.each(geolocationCoords, function(i, coords) {
                if (typeof coords.lat != "undefined" && typeof coords.lng != "undefined") {
                    var content =
                        \'<span>'.
                            '<a style="text-decoration:none;"'.
                            ' href="'.CHtml::normalizeUrl(array('/contacts/contacts/googleMaps',$this->modelParam=>$_GET['id'],'noHeatMap'=>1)).'&locationType[]=\'+coords.type+\'">'.
                                Yii::t('contacts','View on Large Map').
                            '</a>'.
                            '<br /><br />'.
                            '<a style="text-decoration:none;" href="'.CHtml::normalizeUrl(array('/contacts/contacts/googleMaps',$this->modelParam=>$_GET['id'])).'&locationType[]=\'+coords.type+\'">'.
                                Yii::t('contacts','View on Heat Map').
                            '</a>'.
                            '<br /><br /><small>\' + coords.infoText + \''.
                            '<br />\' + coords.time + \'</small>'.
                          '</span>\';
                    x2.googleMapsWidget.addMarker(coords, coords.type, content);
                }
            });
        };

        function runGoogleMapsWidget() {
            x2.googleMapsWidget.instantiated = true;
            geocoder = new google.maps.Geocoder();
            var geolocationCoords = '.CJSON::encode($this->activityLocations).';
            geocoder.geocode( {"address": "'.CJavaScript::quote($this->location).'"}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if ("'.$this->modelParam.'" === "contactId") {
                        $.ajax({
                            url:"'.Yii::app()->controller->createUrl('/contacts/contacts/updateLocation').'",
                            type:"GET",
                            data:{contactId:'.$_GET['id'].',lat:results[0].geometry.location.lat(),lon:results[0].geometry.location.lng()},
                        });
                    }
                    window.map = new google.maps.Map(document.getElementById("googleMapsCanvas"),{
                        center: results[0].geometry.location,
                        zoom: 8,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: false
                    });
                        
                    var content =
                        \'<span>'.
                            '<a style="text-decoration:none;"'.
                            ' href="'.CHtml::normalizeUrl(array('/contacts/contacts/googleMaps',$this->modelParam=>$_GET['id'],'noHeatMap'=>1)).'">'.
                                Yii::t('contacts','View on Large Map').
                            '</a>'.
                            '<br /><br />'.
                            '<a style="text-decoration:none;" href="'.CHtml::normalizeUrl(array('/contacts/contacts/googleMaps',$this->modelParam=>$_GET['id'])).'">'.
                                Yii::t('contacts','View on Heat Map').
                            '</a>'.
                            '<br /><br /><small>'.Yii::t('contacts', 'Stated Address').'</small>'.
                          '</span>\';
                    x2.googleMapsWidget.addMarker(results[0].geometry.location, "address", content);

                    x2.googleMapsWidget.renderMarkers(geolocationCoords);
                } else if (geolocationCoords.length > 0) {
                    window.map = new google.maps.Map(document.getElementById("googleMapsCanvas"),{
                        center: geolocationCoords[0],
                        zoom: 8,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: false
                    });
                    x2.googleMapsWidget.renderMarkers(geolocationCoords);
                } else {
                    $("#widget_GoogleMaps").remove();
                }
            });
        }
        ',CClientScript::POS_HEAD);

        $settings = Yii::app()->settings;
        $key = $settings->getGoogleApiKey('maps');
        $assetUrl = 'https://maps.googleapis.com/maps/api/js?callback=initializeGoogleMapsWidget';
        if (!empty($key))
            $assetUrl .= '&key='.$key;
        Yii::app()->clientScript->registerScriptFile($assetUrl, CClientScript::POS_END);

        $colors = ThemeGenerator::generatePalette(Yii::app()->params->profile->getTheme());
        $bgcolor = $colors['themeName'] === 'Default' ? '#F5F5F5' : $colors['highlight1'];
        Yii::app()->clientScript->registerCss('GoogleMapsWidgetStyle','
            .mapsHeader {
                background-color: '.$bgcolor.';
                width: 100%;
                height: 32px;
            }
        ');
        ?><div class="mapsHeader"><?php
        echo CHtml::dropDownList (
            'locationType',
            $this->defaultFilter,
            Locations::getLocationTypes(),
            array (
                'multiple' => 'multiple',
                'data-selected-text' => Yii::t('app', 'filters(s)'),
                'class' => 'x2-multiselect-dropdown'
            )
        );
        echo CHtml::link (
            Yii::t('contacts', 'Large Map'),
            array('/contacts/contacts/googleMaps',$this->modelParam=>$_GET['id'],'noHeatMap'=>1,'locationType' => $this->defaultFilter),
            array(
                'class' => 'x2-button right',
                'onclick' => '$(this).attr("href", $(this).attr("data-map-url") + "&"+jQuery.param({"locationType": $("#locationType").val()}))',
                'data-map-url' => $this->controller->createAbsoluteUrl('/contacts/contacts/googleMaps',array($this->modelParam=>$_GET['id'],'noHeatMap'=>1))
            )
        );
        ?></div><?php
        echo '<div id="googleMapsCanvas" style="width:100%;height:250px"></div>';
    }
}

