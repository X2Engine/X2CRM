<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */

/**
 * Remote data insertion & lookup API model. Has multiple magic methods and 
 * automatically makes cURL requests to API controller for ease of use. For each
 * kind of request, see the method in ApiController that corresponds to it. To 
 * view this reference, look at the URL path for the method. For example 'api/create'
 * corresponds to actionCreate in ApiController.
 * 
 * @package X2CRM.models
 * @author Jake Houser <jake@x2engine.com>
 */
class APIModel {
    
    private $_user='';
    private $_userKey='';
    private $_baseUrl='';
    private $_apiKey='';
    
    public function __construct($user=null, $userKey=null, $baseUrl=null){
        $this->_user=$user;
        $this->_userKey=$userKey;
        $this->_baseUrl=$baseUrl;
    }
    
    public function authenticate(){
        
        $ccUrl = 'http://'.$this->_baseUrl.'/index.php/api/authenticate'; 
        $ccSession = curl_init($ccUrl);
        curl_setopt($ccSession,CURLOPT_POST,1);
        curl_setopt($ccSession, CURLOPT_USERPWD, 'x3engine:x3dev!!123%%');
        curl_setopt($ccSession,CURLOPT_POSTFIELDS,array('username'=>$this->_user,'userKey'=>$this->_userKey));
        curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
        $ccResult = curl_exec($ccSession);
        
        if(preg_match('/^[a-f0-9]{32}$/', $ccResult)){
            $this->_apiKey=$ccResult;
            return "Authentication succeeded.";
        }else{
            return "Authentication failed.";
        }
    }
    
    public function contactCreate($data=array(),$leadRouting=false){
        if(empty($this->_apiKey)){
            $this->authenticate();
        }
        $attributes=array(
			'assignedTo'=>$this->_user,
			'visibility'=>'1',
			'createDate'=>$time,
			'lastUpdated'=>$time,
			'updatedBy'=>$this->_user,
		);
        foreach($data as $field=>$value){
            $attributes[$field]=$value;
        }
        $ccUrl = 'http://'.$this->_baseUrl.'/index.php/api/create/model/Contacts'; 
        $ccSession = curl_init($ccUrl);
        curl_setopt($ccSession,CURLOPT_POST,1);
        curl_setopt($ccSession,CURLOPT_POSTFIELDS,array_merge(array('apiKey'=>$this->_apiKey,'userKey'=>$this->_userKey),$attributes));
        curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
        $ccResult = curl_exec($ccSession);
        return $ccResult;
    }
}

?>
