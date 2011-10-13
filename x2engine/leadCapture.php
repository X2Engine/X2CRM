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

require_once("webLeadConfig.php");

$firstName=$_POST['firstName'];
$lastName=$_POST['lastName'];
$email=$_POST['email'];
$phone=$_POST['phone'];
$info=$_POST['info'];

$con=mysql_connect($host,$user,$pass) or die(mysql_error());
    mysql_select_db($dbname) or die(mysql_error());

$firstName=mysql_real_escape_string($firstName);
$lastName=mysql_real_escape_string($lastName);
$info=mysql_real_escape_string($info);
$phone=mysql_real_escape_string($phone);

$date=date("Y-m-d");

$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
if($count==0){
    die("Invalid e-mail address!");
}

$admin=mysql_query("SELECT webLeadEmail FROM x2_admin WHERE id='1'") or die(mysql_error());
if($row=mysql_fetch_array($admin))
	$adminEmail=$row[0];

    
    $sql="SELECT * FROM x2_contacts WHERE email='$email'";
    
    $selection=mysql_query($sql) or die(mysql_error());
    
    if($row=mysql_fetch_array($selection)){
        $data=$row['backgroundInfo'];
        $info.="\n\n".$data;
        $id=$row['id'];
        $sql="UPDATE x2_contacts SET backgroundInfo='$info' WHERE id='$id'";
        
        mysql_query($sql) or die("Unable to upate contact record.");
    }else{
        $time=time();
        $sql="INSERT INTO x2_contacts (firstName, lastName, email, assignedTo, visibility, backgroundInfo, phone, lastUpdated, updatedBy) VALUES
            ('$firstName','$lastName','$email','Anyone','1','$info', '$phone', 'admin', '$time')";

        mysql_query($sql) or die(mysql_error());

        $sql="SELECT * FROM x2_contacts WHERE email='$email'";

        $selection=mysql_query($sql) or die("Unable to find new contact in database.");
        $row=mysql_fetch_array($selection);
        $id=$row['id'];

        $sql="INSERT INTO x2_actions (type, actionDescription, dueDate, visibility, associationType, associationId, associationName, assignedTo, priority) VALUES
        ('Web Lead', 'Web Lead', '$date', '1', 'contact', '$id', '$firstName $lastName', 'Anyone', 'High')";

        mysql_query($sql) or die(mysql_error());
    }
	
	$body="New Web Lead Contact!\n\n
			Name: $firstName $lastName \n\n
			Email: $email \n\n
			Info: $info\n\n";
    
?>

<html>
    <h1>
        Thank You!
    </h1>
    <p>Thank you for your interest in San Jose BMW!</p>
    <p>Someone will be in touch shortly.</p>
</html>
