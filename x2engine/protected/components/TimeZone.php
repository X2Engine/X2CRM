<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * Time Zone information widget.
 * 
 * A widget that displays time information (i.e. zone, current time there) 
 * specific to the contact being viewed.
 *  
 * @package X2CRM.components 
 */
class TimeZone extends X2Widget {

	//public $visibility;
	
	public $model;
	
	public function init() {	
		parent::init();
	}

    public function run() {
		$tzOffset = null;
		$address = '';
		
		if(!isset($this->model))
			return;
			
		if(!empty($this->model->city))
			$address .= $this->model->city.', ';
		if(!empty($this->model->state))
			$address .= $this->model->state;
		if(!empty($this->model->country))
			$address .= ' '.$this->model->country;
		
		
		// if there's no cached timezone, we have to look it up
		if (empty($this->model->timezone)) {

			// use google maps API to geocode the contact's address location
			$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=true";
			$url .= "&region=".Yii::app()->language; //If contact isn't in the US, find location.
			$url .= '&address='.preg_replace('/\s/','+',$address);

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,1);	// 1s timeout
			
			$data = CJSON::decode(@curl_exec($ch),true);
			// die(var_dump($url));
			//Get latitude and longitude from results.
			if(isset($data['results'][0]['geometry']['location'])) {
				$lon = $data['results'][0]['geometry']['location']['lng'] * 0.01745329;	// convert to radians
				$lat = $data['results'][0]['geometry']['location']['lat'] * 0.01745329;
				//Use of earth tools api to obtain time zone.
				
				$range = 0.02;
				
				$sql = 'SELECT x2_timezones.name FROM x2_timezone_points JOIN x2_timezones ON tz_id=x2_timezones.id 
					WHERE lat BETWEEN (:lat-:range) AND (:lat+:range) AND lon BETWEEN (:lon-:range) AND (:lon+:range)
					ORDER BY (POW(:lat-lat,2)+POW((:lon-lon)*COS((:lat-lat)/2),2)) ASC LIMIT 1';
				
				$tz = Yii::app()->db->createCommand($sql)->bindValues(array(':lat'=>$lat,':lon'=>$lon,':range'=>0.02))->queryScalar();

				
				if($tz === false)	// if we don't find anything, try again with a larger search box
					$tz = Yii::app()->db->createCommand($sql)->bindValues(array(':lat'=>$lat,':lon'=>$lon,':range'=>0.05))->queryScalar();
					
				if($tz !== false) {
					$contactTime = new DateTime();

					try {
						$dateTimeZone = new DateTimeZone($tz);
					} catch (Exception $e) {
						$dateTimeZone = null;
					}
					
					if(@date_timezone_set($contactTime,$dateTimeZone)) {
						$tzOffset = $contactTime->getOffset();
						
						$this->model->timezone = $tz;
						$this->model->update(array('timezone'));	// save the timezone
					}
				}

			}
		} else {	// timezone already saved, let's use it
			$contactTime = new DateTime();

			try {
				$dateTimeZone = new DateTimeZone($this->model->timezone);
			} catch (Exception $e) {
				$dateTimeZone = null;
			}
		
			$contactTime = new DateTime();
			if(@date_timezone_set($contactTime,$dateTimeZone)) {
				$tzOffset = $contactTime->getOffset();
			} else {										// if the timezone is messed up, 
				$this->model->timezone = '';				// clear it
				$this->model->update(array('timezone'));
			} 
		}

		
		if(isset($tzOffset)) {

	
			$offset = $tzOffset;
				
			$tzString = 'UTC';
			$tzString .= ($offset > 0)? '+' : '-';
			
			$offset = abs($offset);
			
			$offsetH = floor($offset/3600);
			$offset -= $offsetH*3600;
			$offsetM = floor($offset/60);
			
			$tzString .= $offsetH;
			if($offsetM > 0)
				$tzString .= ':'.$offsetM;

			echo Yii::t('app','Current time in').'<br><b>'.$address.'</b><span id="tzClock2"></span>';
				
				
			Yii::app()->clientScript->registerScript('timezoneClock','
			function updateTzClock() {
			
				var tzClock = new Date();
				var tzOffset = '.($tzOffset*1000).';
				var tzUtcOffset = "'.addslashes($tzString).'";
				tzClock.setTime(tzClock.getTime() + tzOffset + (tzClock.getTimezoneOffset()*60000));
			
				var h = tzClock.getHours();
				var m = tzClock.getMinutes();
				var s = tzClock.getSeconds() + tzClock.getMilliseconds()/1000;
				
				var ampm = "am";
				
				if(h>11)			// 0-11 -> am, 12-23 -> pm
					ampm = "pm";
				if(h>12)			// 13-23 -> 1->11
					h -= 12;
				if(h==0)
					h = 12;

				if(Modernizr.csstransforms) {

					var sAngle = Math.round(s * 6);
					var sCssAngle = "rotate(" + sAngle + "deg)";
					
					var hAngle = Math.round(h * 30 + (m / 2));
					var hCssAngle = "rotate(" + hAngle + "deg)";

					var mAngle = m * 6;
					var mCssAngle = "rotate(" + mAngle + "deg)";
					
					var browsers = ["-moz-transform","-webkit-transform","-o-transform","-ms-transform"];
					
					for(i in browsers) {
						$("#tzClock .sec").css(browsers[i],sCssAngle);
						$("#tzClock .min").css(browsers[i],mCssAngle);
						$("#tzClock .hour").css(browsers[i],hCssAngle);
					}
					
					$("#tzClock").attr("title",fixWidth(h)+":"+fixWidth(m)+" ("+tzUtcOffset+")");

				} else {
					$("#tzClock2").html(
						fixWidth(h)+":"+fixWidth(m)+":"+fixWidth(Math.floor(s))+" ("+tzUtcOffset+")"
						// h+":"+fixWidth(m)+":"+fixWidth(Math.floor(s))+ampm+" ("+tzUtcOffset+")"	// 12 hour time version
					);
				}
			}
			
			function fixWidth(x) {
				return (x<10)? "0"+x : x;
			}

			$(function() {
				if(Modernizr.csstransforms) {
					$("<ul id=\"tzClock\">\
						<li class=\"sec\"><div></div><div></div></li>\
						<li class=\"hour\"><div></div></li>\
						<li class=\"min\"><div></div></li>\
					</ul>").appendTo("#widget_TimeZone .portlet-content");
					setInterval(updateTzClock, 200);
				} else {
					$("<div id=\"tzClock2\"></div>").appendTo("#widget_TimeZone .portlet-content");
					setInterval(updateTzClock, 1000);
				}
				updateTzClock();
			});

			');
		} else
			echo Yii::t('app','Timezone not available');
	}
}