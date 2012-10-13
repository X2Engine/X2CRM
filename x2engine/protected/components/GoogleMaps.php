<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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
		if($("#widget_GoogleMaps .portlet-content").is(":visible"))
			runGoogleMapsWidget();
		else
			$("#widget_GoogleMaps .portlet-minimize a").click(function() { runGoogleMapsWidget(); });
		
		function runGoogleMapsWidget() {
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( {"address": "'.addslashes($this->location).'"}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {

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
				} else {
					$("#widget_GoogleMaps").remove();
				}
			});
		}
		',CClientScript::POS_READY);
		
		Yii::app()->clientScript->registerScriptFile('http://maps.googleapis.com/maps/api/js?sensor=false');

		echo '<div id="googleMapsCanvas" style="width:100%;height:250px"></div>';
	}
}