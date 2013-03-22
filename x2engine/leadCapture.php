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
?>

<?php
include('webLeadConfig.php');
$authData=array('user'=>$user,'userKey'=>$userKey);

if($url==""){
    $url=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $url=substr($url,0,-15);
}
$email=$_POST['email'];
$date=mktime(0,0,0,date('m'),date('d'),date('Y'));
$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
if($count==0){
    die("Invalid e-mail address!");
}

$ccUrl = 'http://'.$url.'/index.php/api/lookUp/model/Contacts'; 

$ccSession = curl_init($ccUrl);
curl_setopt($ccSession,CURLOPT_POST,1);
curl_setopt($ccSession,CURLOPT_POSTFIELDS,array_merge($authData,array('email'=>$email)));
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
		$newInfo=$oldInfo."\n\n".$_POST['backgroundInfo'];
		 
		$ccUrl = 'http://'.$url.'/index.php/api/update/model/Contacts/id/'.$id;
		$ccSession = curl_init($ccUrl);
		$data=array('backgroundInfo'=>$newInfo,'user'=>$user, 'userKey'=>$userKey); 
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
                        'user'=>$user,
                        'userKey'=>$userKey,
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
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
                
		curl_close($ccSession);
                $data['assignedTo']=$ccResult;
                $data['user']=$user;
                $data['userKey']=$userKey;
                $actionData['assignedTo']=$ccResult;
                
                $ccUrl = 'http://'.$url.'/index.php/api/create/model/Contacts';
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
		
		$ccUrl = 'http://'.$url.'/index.php/api/lookUp/model/Contacts/email/'.$email; 
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession,CURLOPT_POST,1);
        curl_setopt($ccSession,CURLOPT_POSTFIELDS,$authData);
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

