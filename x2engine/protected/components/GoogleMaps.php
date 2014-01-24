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
