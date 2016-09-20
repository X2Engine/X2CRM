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

    public function run() {
    
        if((!isset($this->location) || empty($this->location)) && (!isset($this->activityLocations) || empty($this->activityLocations)))
            return;

        Yii::app()->clientScript->registerScript('setupGoogleMapsWidget','
        x2.googleMapsWidget = {};
        x2.googleMapsWidget.instantiated = false;
        x2.googleMapsWidget.markers = [];

        $(document).ready (function () {
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
            var infowindow = new google.maps.InfoWindow({
                content:infoContents
            });
            google.maps.event.addListener(marker,"click",function(){
                infowindow.open(map,marker);
            });
            x2.googleMapsWidget.markers.push(marker);
        };

        function runGoogleMapsWidget() {
            x2.googleMapsWidget.instantiated = true;
            geocoder = new google.maps.Geocoder();
            var geolocationCoords = '.CJSON::encode($this->activityLocations).';
            geocoder.geocode( {"address": "'.CJavaScript::quote($this->location).'"}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $.ajax({
                        url:"'.Yii::app()->controller->createUrl('/contacts/contacts/updateLocation').'",
                        type:"GET",
                        data:{contactId:'.$_GET['id'].',lat:results[0].geometry.location.lat(),lon:results[0].geometry.location.lng()},
                    });
                    window.map = new google.maps.Map(document.getElementById("googleMapsCanvas"),{
                        center: results[0].geometry.location,
                        zoom: 8,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: false
                    });
                        
                    var content =
                        \'<span>'.
                            '<a style="text-decoration:none;"'.
                            ' href="'.CHtml::normalizeUrl(array('googleMaps','contactId'=>$_GET['id'],'noHeatMap'=>1)).'">'.
                                Yii::t('contacts','View on Large Map').
                            '</a>'.
                            '<br /><br />'.
                            '<a style="text-decoration:none;" href="'.CHtml::normalizeUrl(array('googleMaps','contactId'=>$_GET['id'])).'">'.
                                Yii::t('contacts','View on Heat Map').
                            '</a>'.
                          '</span>\';
                    x2.googleMapsWidget.addMarker(results[0].geometry.location, "address", content, ("'.$this->defaultFilter.'" === "address"));

                    $.each(geolocationCoords, function(i, coords) {
                        if (typeof coords.lat != "undefined" && typeof coords.lng != "undefined") {
                            var content =
                                \'<span>'.
                                    '<a style="text-decoration:none;"'.
                                    ' href="'.CHtml::normalizeUrl(array('googleMaps','contactId'=>$_GET['id'],'noHeatMap'=>1)).'">'.
                                        Yii::t('contacts','View on Large Map').
                                    '</a>'.
                                    '<br /><br />'.
                                    '<a style="text-decoration:none;" href="'.CHtml::normalizeUrl(array('googleMaps','contactId'=>$_GET['id'])).'">'.
                                        Yii::t('contacts','View on Heat Map').
                                    '</a>'.
                                    '<br /><br /><small>\' + coords.infoText + \'</small>'.
                                  '</span>\';
                            x2.googleMapsWidget.addMarker(coords, coords.type, content, ("'.$this->defaultFilter.'" === coords.type));
                        }
                    });
                } else if (geolocationCoords.length > 0) {
                    window.map = new google.maps.Map(document.getElementById("googleMapsCanvas"),{
                        center: geolocationCoords[0],
                        zoom: 8,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: false
                    });
                    $.each(geolocationCoords, function(i, coords) {
                        if (typeof coords.lat != "undefined" && typeof coords.lng != "undefined") {
                            var content =
                                \'<span>'.
                                    '<a style="text-decoration:none;"'.
                                    ' href="'.CHtml::normalizeUrl(array('googleMaps','contactId'=>$_GET['id'],'noHeatMap'=>1)).'">'.
                                        Yii::t('contacts','View on Large Map').
                                    '</a>'.
                                    '<br /><br />'.
                                    '<a style="text-decoration:none;" href="'.CHtml::normalizeUrl(array('googleMaps','contactId'=>$_GET['id'])).'">'.
                                        Yii::t('contacts','View on Heat Map').
                                    '</a>'.
                                    '<br /><br /><small>\' + coords.infoText + \'</small>'.
                                  '</span>\';
                            x2.googleMapsWidget.addMarker(coords, coords.type, content, ("'.$this->defaultFilter.'" === coords.type));
                        }
                    });
                } else {
                    $("#widget_GoogleMaps").remove();
                }
            });
        }
        ',CClientScript::POS_HEAD);

        $key = '';
        $settings = Yii::app()->settings;
        $creds = Credentials::model()->findByPk($settings->googleCredentialsId);
        if ($creds && $creds->auth)
            $key = $creds->auth->apiKey;
        $proto = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
        $assetUrl = $proto.'://maps.googleapis.com/maps/api/js';
        if (!empty($key))
            $assetUrl .= '?key='.$key;
        Yii::app()->clientScript->registerScriptFile($assetUrl);

        Yii::app()->clientScript->registerCss('GoogleMapsWidgetStyle','
            .mapsHeader {
                background-color: #F5F5F5;
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
        if (Yii::app()->controller instanceof ContactsController) {
            echo CHtml::link (
                Yii::t('contacts', 'View on Large Map'),
                array('contacts/googleMaps','contactId'=>$_GET['id'],'noHeatMap'=>1),
                array('class' => 'x2-button x2-small-button right')
            );
        }
        ?></div><?php
        echo '<div id="googleMapsCanvas" style="width:100%;height:250px"></div>';
    }
}

