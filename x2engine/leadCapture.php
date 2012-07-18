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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
?>

<?php
include('webLeadConfig.php');

if($url==""){
    $url='http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl;
}

$date=mktime(0,0,0,date('m'),date('d'),date('Y'));
$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
if($count==0){
    die("Invalid e-mail address!");
}

$ccUrl = 'http://'.$url.'/index.php/api/lookUp/model/Contacts/email/'.$email; 
$ccSession = curl_init($ccUrl);
curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
$ccResult = curl_exec($ccSession);
curl_close($ccSession);
if($ccResult!="No Item found with specified attributes."){
		$pieces=explode(",",$ccResult);
		$oldInfo=$pieces[16];
		$oldInfo=explode(":",$oldInfo);
		$oldInfo=substr($oldInfo[1],1,-1);
		$pieces=explode(":",$pieces[0]);
		$id=substr($pieces[1],1,-1);
		$newInfo=$oldInfo."\n\n".$info;
		 
		$ccUrl = 'http://'.$url.'/index.php/api/update/model/Contacts/id/'.$id;
		$ccSession = curl_init($ccUrl);
		$data=array('backgroundInfo'=>$newInfo); 
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
}else{
                $time=time();
		$data=array(
			'assignedTo'=>'Anyone',
			'visibility'=>'1',
			'createDate'=>$time,
			'lastUpdated'=>$time,
			'updatedBy'=>'admin',
		);
                foreach($_POST as $field=>$value){
                    $data[$field]=$value;
                }
                
                $actionData=array(
			'type'=>'',
			'actionDescription'=>'Web Lead',
			'assignedTo'=>'Anyone',
			'visibility'=>'1',
			'dueDate'=>$time,
			'associationType'=>'contacts',
			'associationId'=>'',
			'associationName'=>$data['firstName']." ".$data['lastName'],
			'priority'=>'High',
			'createDate'=>$time,
			'lastUpdated'=>$time,
			'updatedBy'=>'admin',
		); 
                $ccUrl = 'http://'.$url.'/index.php/admin/getRoutingType';
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
                echo $ccResult;exit;
                $data['assignedTo']=$ccResult;
                $actionData['assignedTo']=$ccResult;
                
                $ccUrl = 'http://'.$url.'/index.php/api/create/model/Contacts';
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
		
		$ccUrl = 'http://'.$url.'/index.php/api/lookUp/model/Contacts/email/'.$email; 
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		
		$pieces=explode(",",$ccResult);
		$pieces=explode(":",$pieces[0]);
		$id=substr($pieces[1],1,-1);
		
                $actionData['associationId']=$id;
                
		curl_close($ccSession);
		
		$ccUrl = 'http://'.$url.'/index.php/api/create/model/Actions';
		$ccSession = curl_init($ccUrl);
		
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ccSession, CURLOPT_USERPWD, "x3engine:x32011!!"); 
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$actionData);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
		
		if(!empty($photourl)) {
			// save profile picture
			$ccUrl = 'http://'.$url.'/index.php/site/uploadProfilePicture';
			$postdata['photourl'] = $photourl;
			$postdata['type'] = 'contacts';
			$postdata['associationId'] = $id;
			$ccSession = curl_init($ccUrl);
			curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ccSession,CURLOPT_POST,1);
			curl_setopt($ccSession,CURLOPT_POSTFIELDS,$postdata);
			curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
			$ccResult = curl_exec($ccSession);
			curl_close($ccSession);
		}
}
    
?>

<html>
    <h1>
        Thank You!
    </h1>
    <p>Thank you for your interest!</p>
    <p>Someone will be in touch shortly.</p>
</html>

