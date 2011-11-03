#!usr/bin/php
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
    if($contact=mysql_fetch_array($selection)){
        $message="1";
        $email=$fromEmail;
        $id=$contact['id'];
        $note.="\n\nSent from Contact";
        $fullName=$contact['firstName']." ".$contact['lastName'];
        $sql="INSERT INTO x2_actions (actionDescription, createDate, dueDate, completeDate, complete, visibility, completedBy, assignedTo, type, associationType, associationId, associationName) 
            VALUES ('$note','$time','$time','$time','Yes','1','Email','Anyone','note','contacts','$id','$fullName')";
        mysql_query($sql);
    }else if($contact=mysql_fetch_array($selection2)){
        $message="2";
        $email=$toEmail;
        $id=$contact['id'];
        $note.="\n\nSent to Contact";
        $fullName=$contact['firstName']." ".$contact['lastName'];
        $sql="INSERT INTO x2_actions (actionDescription, createDate, dueDate, completeDate, complete, visibility, completedBy, assignedTo, type, associationType, associationId, associationName) 
            VALUES ('$note','$time','$time','$time','Yes','1','Email','Anyone','note','contacts','$id','$fullName')";
        mysql_query($sql) or $message="FAILURE";
    }else{
        $message=$firstName." ".$lastName." : ".$toEmail." : ".$note;
        $sql="INSERT INTO x2_contacts (firstName, lastName, email, visibility, assignedTo, createDate) VALUES ('$firstName','$lastName','$toEmail', '1', 'Anyone', '$time')";
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