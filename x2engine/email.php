#!usr/bin/php
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

require_once(__DIR__.'/protected/config/emailConfig.php');

// fetch data from stdin
$data = file_get_contents("php://stdin");

// extract the body
// NOTE: a properly formatted email's first empty line defines the separation between the headers and the message body
list($data, $body) = explode("\n\n", $data, 2);

// explode on new line
$data = explode("\n", $data); 

// define a variable map of known headers
$patterns = array(
  'Return-Path',
  'X-Original-To',
  'Delivered-To',
  'Received',
  'To',
  'Message-Id',
  'Date',
  'From',
  'Subject',
);

// define a variable to hold parsed headers
$headers = array();

// loop through data
foreach ($data as $data_line) {

  // for each line, assume a match does not exist yet
  $pattern_match_exists = false;

  // check for lines that start with white space
  // NOTE: if a line starts with a white space, it signifies a continuation of the previous header
  if ((substr($data_line,0,1)==' ' || substr($data_line,0,1)=="\t") && $last_match) {

    // append to last header
    $headers[$last_match][] = $data_line;
    continue;

  }

  // loop through patterns
  foreach ($patterns as $key => $pattern) {

    // create preg regex
    $preg_pattern = '/^' . $pattern .': (.*)$/';

    // execute preg
    preg_match($preg_pattern, $data_line, $matches);

    // check if preg matches exist
    if (count($matches)) { 

      $headers[$pattern][] = $matches[1];
      $pattern_match_exists = true;
      $last_match = $pattern;

    }

  }

  // check if a pattern did not match for this line
  if (!$pattern_match_exists) {
    $headers['UNMATCHED'][] = $data_line;
  }

}

$toEmail=$headers['To'][0];
$fromEmail=$headers['From'][0];

$bits=explode("<",$headers['From'][0]);
$names=explode(" ",$bits[0]);

$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$fromEmail,$matches);
$fromEmail=$matches[0];
$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$toEmail,$matches);
$toEmail=$matches[0];

$body_array = explode("\n",$body);  
$message = "";  
foreach($body_array as $value){  
  
    //remove hotmail sig  
    if($value == "_________________________________________________________________"){  
        continue;  
  
    //original message quote  
    } elseif(preg_match("/^-*(.*)Original Message(.*)-*/i",$value,$matches)){  
        continue;  
  
    //check for date wrote string  
    } elseif(preg_match("/^On(.*)wrote:(.*)/i",$value,$matches)) {  
        continue;  
  
    //check for From Name email section  
    } elseif(preg_match("/^(.*)$toEmail(.*)wrote:(.*)/i",$value,$matches)) {  
        continue;  
  
    //check for From Email email section  
    } elseif(preg_match("/^(.*)$fromEmail(.*)wrote:(.*)/i",$value,$matches)) {  
        continue;  
  
    //check for quoted ">" section  
    } elseif(preg_match("/^>(.*)/i",$value,$matches)){  
        continue;  
  
    //check for date wrote string with dashes  
    } elseif(preg_match("/^---(.*)On(.*)wrote:(.*)/i",$value,$matches)){  
        continue;  
  
    } elseif(preg_match("/^--[a-z0-9]+/",$value,$matches)){
        continue;
        
    } elseif(preg_match("/This is a multi-part/",$value,$matches)){
        continue;
        
    } elseif(preg_match("/^------_=_(.*?)\n/",$value,$matches)){
        continue;
        
    }elseif(preg_match("/^Content-Transfer-Encoding/",$value,$matches)){
        continue;
        
    } elseif(preg_match("/^Content-Type: text\/plain/",$value,$matches)){
            continue;
        
    } elseif(preg_match("/^Content-Type: text\/html/",$value,$matches)){
            break;
        
    }elseif(preg_match("/=20/",$value,$matches)){ 
            continue;
        
    }elseif(preg_match("/^-+_((.*?)_)+/",$value,$matches)){
            continue;
        
    }elseif(preg_match("/^-+_(.*?)+/",$value,$matches)){
            continue;
        
    }elseif(preg_match("/charset=\"(.*?)\"/",$value,$matches)){
            continue;
        
    }elseif(preg_match("/From:(.*)/",$value,$matches)){
            continue;
        
    }elseif(preg_match("/Sent:(.*)/",$value,$matches)){
            continue;
        
    }elseif(preg_match("/To:(.*)/",$value,$matches)){
            continue;
        
    }elseif(preg_match("/CC:(.*)/i",$value,$matches)){
            continue;
        
    }elseif(preg_match("/Subject:(.*)/",$value,$matches)){
            continue;
        
    }else {   
        $message .= "$value\n";  
    }  
  
} 

    $firstName=$names[0];
    $lastName=$names[1];
    $note=$message;

    $con=mysql_connect($host,$user,$pass) or die(mysql_error());
    mysql_select_db($dbname) or die(mysql_error());
    
    $firstName=mysql_real_escape_string($firstName);
	$lastName=mysql_real_escape_string($lastName);
	$fullName=mysql_real_escape_string($fullName);
	$note=mysql_real_escape_string($note);
	
    $sql="SELECT * FROM x2_contacts WHERE email='$fromEmail'";
    $sql2="SELECT * FROM x2_contacts WHERE email='$toEmail'";
    
    $selection=mysql_query($sql) or die(mysql_error());
    $selection2=mysql_query($sql2) or die(mysql_error());
    
    $time=time();
    $message="0";
    if($contact=mysql_fetch_array($selection)){	// contact already exists, sent by contact
        $message="1";
        $email=$fromEmail;
        $id=$contact['id'];
        $note.="\n\nSent from Contact";
        $fullName=$contact['firstName']." ".$contact['lastName'];
        $sql="INSERT INTO x2_actions (actionDescription, createDate, dueDate, completeDate, complete, visibility, completedBy, assignedTo, type, associationType, associationId, associationName) 
            VALUES ('$note','$time','$time','$time','Yes','1','Email','Anyone','note','contacts','$id','$fullName')";
        mysql_query($sql);
    }else if($contact=mysql_fetch_array($selection2)){		// contact already exists, email sent by user
        $message="2";
        $email=$toEmail;
        $id=$contact['id'];
        $note.="\n\nSent to Contact";
        $fullName=$contact['firstName']." ".$contact['lastName'];
        $sql="INSERT INTO x2_actions (actionDescription, createDate, dueDate, completeDate, complete, visibility, completedBy, assignedTo, type, associationType, associationId, associationName) 
            VALUES ('$note','$time','$time','$time','Yes','1','Email','Anyone','note','contacts','$id','$fullName')";
        mysql_query($sql) or $message="FAILURE";
    }else{		// contact does not exist
        $message=$firstName." ".$lastName." : ".$toEmail." : ".$note;
        $sql="INSERT INTO x2_contacts (firstName, lastName, email, visibility, assignedTo, createDate, lastUpdated) VALUES ('$firstName','$lastName','$toEmail', '1', 'Anyone', '$time', '$time')";
        mysql_query($sql);
        $sql="SELECT * FROM x2_contacts WHERE email='$toEmail'";
        $selection=mysql_query($sql) or $message="FAILURE";
        if($contact=mysql_fetch_array($selection)){
            $id=$contact['id'];
            $fullName=$contact['firstName']." ".$contact['lastName'];
            $note.="\n\nSent to Contact";
            $sql="INSERT INTO x2_actions (actionDescription, createDate, dueDate, completeDate, complete, visibility, completedBy, assignedTo, type, associationType, associationId, associationName) 
                VALUES ('$note','$time','$time','$time','Yes','1','Email','Anyone','note','contacts','$id','$fullName')";
            mysql_query($sql) or die(mysql_error());
        }
    }
   
?>