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

/**
 * Google maps display widget.
 * 
 * Before running, the $location attribute needs to be set.
 * @package X2CRM.components 
 */
class GoogleMaps extends X2Widget {

	//public $visibility;
	public function init() {
		parent::init();
	}
	
	public $location;

	public function run() {
	
		if(!isset($this->location) || empty($this->location))
			return;

		Yii::app()->clientScript->registerScript('setupGoogleMapsWidget','
		x2.googleMapsWidget = {};
		x2.googleMapsWidget.instantiated = false;

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
		});
		
		function runGoogleMapsWidget() {
			x2.googleMapsWidget.instantiated = true;
			geocoder = new google.maps.Geocoder();
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
						
					var marker = new google.maps.Marker({
						map: window.map,
						position: results[0].geometry.location
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
                    var infowindow = new google.maps.InfoWindow({
                                content:content
                            });
                    google.maps.event.addListener(marker,"click",function(){
                            infowindow.open(map,marker);
                        });
				} else {
					$("#widget_GoogleMaps").remove();
				}
			});
		}
		',CClientScript::POS_HEAD);
		if(isset($_SERVER['HTTPS'])){
            Yii::app()->clientScript->registerScriptFile('https://maps.googleapis.com/maps/api/js?sensor=false');
        }else{
            Yii::app()->clientScript->registerScriptFile('http://maps.googleapis.com/maps/api/js?sensor=false');
        }
		echo '<div id="googleMapsCanvas" style="width:100%;height:250px"></div>';
	}
}
