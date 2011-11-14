<?php 
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/
?>


<?php

$firstName=$_POST['firstName'];
$lastName=$_POST['lastName'];
$email=$_POST['email'];
$phone=$_POST['phone'];
$info=$_POST['info'];

if($info=="Enter any additional information or questions regarding your interest here."){
    $info="";
}

$url=""; // Add your server URL here, including any folders the app may be in.  i.e.  www.x2engine.com or www.x2engine.com/x2engine etc. 

echo $ccResult;



$date=mktime(0,0,0,date('m'),date('d'),date('Y'));
$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
if($count==0){
    die("Invalid e-mail address!");
}

$ccUrl = 'http://'.$url.'/index.php/api/lookUp?model=Contacts&email='.$email; 
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
		 
		$ccUrl = 'http://'.$url.'/index.php/api/update?model=Contacts&id='.$id;
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
		
		
			
		$ccUrl = 'http://'.$url.'/index.php/api/create?model=Contacts';
		$ccSession = curl_init($ccUrl);
		$data=array(
			'firstName'=>$firstName,
			'lastName'=>$lastName,
			'assignedTo'=>'Anyone',
			'visibility'=>'1',
			'phone'=>$phone,
			'email'=>$email,
			'createDate'=>$time,
			'lastUpdated'=>$time,
			'updatedBy'=>'admin',
			'backgroundInfo'=>$info,
		); 
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
		
		$ccUrl = 'http://'.$url.'/index.php/api/lookUp?model=Contacts&email='.$email; 
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		
		$pieces=explode(",",$ccResult);
		$pieces=explode(":",$pieces[0]);
		$id=substr($pieces[1],1,-1);
		
		curl_close($ccSession);
		
		$ccUrl = 'http://'.$url.'/index.php/api/create?model=Actions';
		$ccSession = curl_init($ccUrl);
		$data=array(
			'type'=>'Web Lead',
			'actionDescription'=>'Web Lead',
			'assignedTo'=>'Anyone',
			'visibility'=>'1',
			'dueDate'=>$date,
			'associationType'=>'contacts',
			'associationId'=>$id,
			'associationName'=>$firstName." ".$lastName,
			'priority'=>'High',
			'createDate'=>$time,
			'lastUpdated'=>$time,
			'updatedBy'=>'admin',
		); 
		curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ccSession,CURLOPT_POST,1);
		curl_setopt($ccSession,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
    }
    
?>

<html>
    <h1>
        Thank You!
    </h1>
    <p>Thank you for your interest!</p>
    <p>Someone will be in touch shortly.</p>
</html>
