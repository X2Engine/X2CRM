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

class TimeZone extends CWidget {

	//public $visibility;
	public function init() {	
		parent::init();
	}

    public function run() {
        $ch = curl_init();

        $address = '';
        $array = array();
        $lat ='';
        //For Profile
        $lang = Yii::app()->language;
        $contact = array();
        $actionParams = Yii::app()->controller->getActionParams();
		if(Yii::app()->controller->module != null && Yii::app()->controller->module->id=='contacts'			// must be a contact
			&& Yii::app()->controller->action->id=='view'	// must be viewing it
            && isset($actionParams['id'])) {				// must have an actual ID value
            $currentRecord = Contacts::model()->findByPk($actionParams['id']);
            $lang = Yii::app()->language;
            $currentRecord = Contacts::model()->findByPk($actionParams['id']);
            //Compose an address to be appended to google maps URL to be 
            //implemented through the google api.
			if(!empty($currentRecord->city)) {
				if(!empty($currentRecord->address))
					$address .= $currentRecord->address . ',+';

				$address .= $currentRecord->city . ',+';
			}
			
			if(!empty($currentRecord->state))
				$address .= $currentRecord->state;
            $address=str_replace(" ","+",$address);
            $address.="&sensor=true";//Necessary to obtain privilege to see results.
            if ($lang != "en"){$address .= "&region=".$lang;} //If contact isn't in the US, find location.
            $url="http://maps.googleapis.com/maps/api/geocode/json?address=".$address;
            //Set up a way to obtain results from URL
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $data = curl_exec($ch);
            $array = json_decode($data);
            //Get latitude and longitude from results.
            $long= $array->results[0]->geometry->location->lng;
            $lat = $array->results[0]->geometry->location->lat;
            //Use of earth tools api to obtain time zone.
            $url = "http://www.earthtools.org/timezone/".$lat."/".$long;
            curl_setopt($ch,CURLOPT_URL,$url);
            $data = curl_exec($ch);
            $contact = json_decode(json_encode((array) simplexml_load_string($data)),1);
            }
		$this->render('timeZone', array(
            'contact'=>$contact,
		));
	}
}
?>
