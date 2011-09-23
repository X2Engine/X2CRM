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
$x2Version = '0.9';
 
$host=$_POST['host'];
$db=$_POST['db'];
$user=$_POST['user'];
$pass=$_POST['password'];
$app=$_POST['app'];
$currency=$_POST['currency'];
$lang=$_POST['lang'];
if($lang=="")
	$lang="en";
//$gii=$_POST['gii'];
$adminEmail=$_POST['adminEmail'];
$adminPassword=$_POST['adminPass'];
$app=mysql_escape_string($app);
if(!preg_match('/[a-zA-Z0-9]+@/',$adminEmail))
	die('Please enter a valid email address.');

$gii=1;
if($gii=='1'){
	$gii="array('class'=>'system.gii.GiiModule',
		'password'=>'$adminPassword',
		// If removed, Gii defaults to localhost only. Edit carefully to taste.
		'ipFilters'=>false,
	)";
		
}else{
	$gii=
"array(
	'class'=>'system.gii.GiiModule',
	'password'=>'password',
	// If removed, Gii defaults to localhost only. Edit carefully to taste.
	'ipFilters'=>array('127.0.0.1', '::1'),
	)";
}

$fileName='protected/config/dbConfig.php';
$handle = fopen($fileName,'w+') or die("Couldn't create config file");
$write = 
"<?php
\$db=array(
	'connectionString' => 'mysql:host=".$host.";dbname=".$db."',
	'emulatePrepare' => true,
	'username' => '".$user."',
	'password' => '".$pass."',
	'charset' => 'utf8',
);
\$appName='$app';
\$gii=$gii;
\$email='$adminEmail';\n";
$write .= (empty($lang))? '$language=null;' : "\$language='$lang';";

fwrite($handle,$write);
fclose($handle);

$filename='protected/config/emailConfig.php';
$handle = fopen($filename, 'w') or die ("Couldn't create e-mail drop box config");
$write = 
"<?php
\$host='$host';
\$user='$user';
\$pass='$pass';
\$dbname='$db';
?>";
fwrite($handle,$write);
fclose($handle);


$con = mysql_connect($host,$user,$pass) or die("Unable to connect to database.  Check connection info.");

mysql_select_db($db,$con) or die ("Unable to select database.  Please make sure the name is spelled properly and that the database exists.".mysql_error());

mysql_query("DROP TABLE IF EXISTS x2_users, x2_contacts, x2_actions, x2_sales, x2_projects, x2_marketing, x2_cases, x2_profile,
	x2_accounts, x2_notes, x2_social, x2_docs, x2_media, x2_admin") or die("Unable to update tables, check user permissions.".mysql_error());

mysql_query("CREATE TABLE x2_users(
	id INT NOT NULL AUTO_INCREMENT primary key,
	firstName VARCHAR(20) NOT NULL,
	lastName VARCHAR(40) NOT NULL,
	username VARCHAR(20) NOT NULL,
	password VARCHAR(100) NOT NULL,
	title VARCHAR(20),
	department VARCHAR(40),
	officePhone VARCHAR(40),
	cellPhone VARCHAR(40),
	homePhone VARCHAR(40),
	address VARCHAR(100),
	backgroundInfo TEXT,
	emailAddress VARCHAR(100) NOT NULL,
	status TINYINT NOT NULL,
	updatePassword TINYINT,
	lastUpdated VARCHAR(30),
	updatedBy VARCHAR(20),
	recentItems VARCHAR(100),
	topContacts VARCHAR(100),
	lastLogin INT DEFAULT 0,
	UNIQUE(username, emailAddress))
	COLLATE = utf8_general_ci
	") or die('Unable to create table x2_users, check user permissions.'.mysql_error());

mysql_query("CREATE TABLE x2_contacts(
	id INT NOT NULL AUTO_INCREMENT primary key,
	firstName VARCHAR(40) NOT NULL,
	lastName VARCHAR(40) NOT NULL,
	title VARCHAR(40),
	company VARCHAR(100),
	accountId INT DEFAULT 0,
	phone VARCHAR(40),
	email VARCHAR(100),
	website VARCHAR(100),
	address VARCHAR(100),
	city VARCHAR(40),
	state VARCHAR(40),
	zipcode VARCHAR(20),
	country VARCHAR(40),
	visibility INT NOT NULL,
	assignedTo VARCHAR(20),
	backgroundInfo TEXT,
	twitter VARCHAR(20) NULL,
	linkedin VARCHAR(100) NULL,
	skype VARCHAR(32) NULL,
	googleplus VARCHAR(100) NULL,
	lastUpdated VARCHAR(30),
	updatedBy VARCHAR(20),
	priority VARCHAR(40),
	leadSource VARCHAR(40),
	rating INT,
	createDate INT,
	facebook VARCHAR(100) NULL,
	otherUrl VARCHAR(100) NULL)
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_contacts, check user permissions.'.mysql_error());

//mysql_query("SOURCE /x2engine/install.sql; ") or die(mysql_error();

mysql_query("CREATE TABLE x2_actions(
	id INT NOT NULL AUTO_INCREMENT primary key,
	assignedTo VARCHAR(20),
	actionDescription text NOT NULL,
	visibility INT NOT NULL,
	associationId INT NOT NULL,
	associationType VARCHAR(20),
	associationName VARCHAR(100),
	dueDate INT,
	showTime TINYINT NOT NULL DEFAULT 0,
	priority VARCHAR(10),
	type VARCHAR(20),
	createDate INT,
	complete VARCHAR(5) default 'No',
	reminder VARCHAR(5),
	completedBy VARCHAR(20),
	completeDate INT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_actions, check user permissions.'.mysql_error());

 mysql_query("CREATE TABLE x2_sales(
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(40) NOT NULL,
	accountName VARCHAR(100),
	accountId INT DEFAULT 0,
	quoteAmount INT,
	salesStage VARCHAR(20),
	expectedCloseDate VARCHAR(20),
	probability INT,
	leadSource VARCHAR(10),
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	associatedContacts TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_sales, check user permissions.'.mysql_error());

 mysql_query("CREATE TABLE x2_projects(
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(60) NOT NULL,
	status VARCHAR(20),
	type VARCHAR(20), 
	priority VARCHAR(20),
	assignedTo TEXT,
	endDate DATETIME,
	timeframe VARCHAR(40),
	createDate INT,
	associatedContacts TEXT,
	description TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_projects, check user permissions.'.mysql_error());

 mysql_query("CREATE TABLE x2_marketing(
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(20) NOT NULL,
	cost INT,
	result TEXT,
	createDate INT,
	description TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_marketing, check user permissions.'.mysql_error());

 mysql_query("CREATE TABLE x2_cases(
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(60) NOT NULL,
	status VARCHAR(20) NOT NULL,
	type VARCHAR(20), 
	priority VARCHAR(20),
	assignedTo TEXT,
	endDate DATETIME,
	timeframe VARCHAR(40),
	createDate INT,
	associatedContacts TEXT,
	description TEXT,
	resolution TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_cases, check user permissions.'.mysql_error());

 mysql_query("CREATE TABLE x2_profile(
	id INT NOT NULL AUTO_INCREMENT primary key,
	fullName VARCHAR(60) NOT NULL,
	username VARCHAR(20) NOT NULL,
	officePhone VARCHAR(40),
	cellPhone VARCHAR(40),
	emailAddress VARCHAR(40) NOT NULL,
	notes TEXT,
	status TINYINT(1) NOT NULL,
	tagLine VARCHAR(250),
	lastUpdated INT,
	updatedBy VARCHAR(20),
	avatar TEXT,
	allowPost TINYINT(1) DEFAULT 1,
	language VARCHAR(40) DEFAULT '$lang',
	timeZone VARCHAR(100) DEFAULT 'Europe/London',
	resultsPerPage INT DEFAULT 20,
	widgets VARCHAR(255) DEFAULT '1:1:1:1:1:0:1',
	widgetOrder VARCHAR(255) DEFAULT 'MessageBox:QuickContact:GoogleMaps:TwitterFeed:ChatBox:NoteBox:ActionMenu',
	backgroundColor VARCHAR(6) NULL,
	menuBgColor VARCHAR(6) NULL,
	menuTextColor VARCHAR(6) NULL,
	backgroundImg VARCHAR(100) NULL DEFAULT 'santa_cruz_blur.jpg',
	pageOpacity INT NULL,
	startPage VARCHAR(30) NULL,
	showSocialMedia TINYINT(1) NOT NULL DEFAULT 1,
	UNIQUE(username, emailAddress))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_profile, check user permissions.'.mysql_error());

 mysql_query("CREATE TABLE x2_accounts(
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(40) NOT NULL,
	website VARCHAR(40),
	type VARCHAR(60), 
	annualRevenue INT,
	phone VARCHAR(40),
	tickerSymbol VARCHAR(10),
	employees INT,
	assignedTo TEXT,
	createDate INT,
	associatedContacts TEXT,
	description TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_accounts, check user permissions.'.mysql_error());


 mysql_query("CREATE TABLE x2_social(
	id INT NOT NULL AUTO_INCREMENT primary key,
	type VARCHAR(40) NOT NULL,
	data text,
	user VARCHAR(40),
	associationId INT,
	private TINYINT(1) DEFAULT 0,
	timestamp INT,
	lastUpdated INT)
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_social, check user permissions.'.mysql_error());

mysql_query("CREATE TABLE x2_docs(
	id INT NOT NULL AUTO_INCREMENT primary key,
	title VARCHAR(100) NOT NULL,
	text LONGTEXT NOT NULL,
	createdBy VARCHAR(60) NOT NULL,
	createDate INT,
	editPermissions VARCHAR(100),
	updatedBy VARCHAR(40),
	lastUpdated INT)
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_docs, check user permissions.'.mysql_error());

mysql_query("CREATE TABLE x2_media(
	id INT NOT NULL AUTO_INCREMENT primary key,
	associationType VARCHAR(40) NOT NULL,
	associationId INT,
	uploadedBy VARCHAR(40),
	fileName VARCHAR(100),
	createDate INT)
	COLLATE = utf8_general_ci
 ") or die('Unable to create table x2_social, check user permissions.'.mysql_error());

mysql_query("CREATE TABLE x2_admin(
	id INT NOT NULL AUTO_INCREMENT primary key,
	accounts INT,
	sales INT,
	timeout INT,
	webLeadEmail VARCHAR(200),
	currency VARCHAR(3) NULL,
	menuOrder VARCHAR(255),
	menuVisibility VARCHAR(100),
	menuNicknames VARCHAR(255),
	chatPollTime INT DEFAULT 2000)
	COLLATE = utf8_general_ci
	") or die('Unable to create table x2_social, check user permissions.'.mysql_error());


$adminPassword=md5($adminPassword); 
$adminEmail=mysql_escape_string($adminEmail);

mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin) VALUES ('web','admin','admin','$adminPassword',
			'$adminEmail' ,'1', '0')") or die("Error inserting admin information.");
mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Web Admin', 'admin', '831-555-5555', '$adminEmail','1')") or die("Error inserting dummy data");
mysql_query("INSERT INTO x2_social (type, data) VALUES ('motd', 'Please enter a message of the day!')") or die("Unable to set starting MOTD.");
mysql_query("INSERT INTO x2_admin (accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, menuVisibility, currency) VALUES ('0','1','3600','$adminEmail',
		'contacts:actions:sales:accounts:docs','Contacts:Actions:Sales:Accounts:Docs','1:1:1:0:1','$currency')") or die("Unable to input admin config");

$backgrounds = array(
	'screens2cc.jpg',
	'calico.jpg',
	'calico_blur.jpg',
	'santa_cruz.jpg',
	'santa_cruz_blur.jpg',
	'moss_landing.jpg',
	'moss_landing_blur.jpg',
	'pigeon_point.jpg',
	'pigeon_point_blur.jpg',
	'redwoods.jpg',
	'redwoods_blur.jpg',
	'laguna_blur.jpg',
	'laguna_seca.jpg',
);
foreach($backgrounds as $background) {
	//if(file_exists("uploads/$background")) {
		mysql_query("INSERT INTO x2_media (associationType, fileName) VALUES ('bg', '$background')"); // or die("Unable to install background image $background.");
	//}
}

$data=$_POST['data'];

if($data==1){
	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Chris','Hames','chames',md5('password'),
		'831-555-5555','10 Downing St. Santa Cruz, CA 95060', 'chris@hames.com','1')") or die("Error inserting dummy data");
	 mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Chris Hames', 'chames', '831-555-5555', 'chris@hames.com','1')") or die("Error inserting dummy data");
	 
	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('James','Valerian','jvalerian',md5('password'),
		'831-555-5555','123 Main St. Santa Cruz, CA 95060', 'james@valerian.com','1')") or die("Error inserting dummy data");
	 mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('James Valerian', 'jvalerian', '831-555-5555', 'james@valerian.com','1')") or die("Error inserting dummy data");
	 
	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Sarah','Smith','ssmith',md5('password'),
		'831-555-5555','467 2nd Ave. Santa Cruz, CA 95060', 'sarah@smith.com','1')") or die("Error inserting dummy data");
	 mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Sarah Smith', 'ssmith', '831-555-5555', 'sarah@smith.com','1')") or die("Error inserting dummy data");
	 
	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Kevin','Flynn','kflynn',md5('password'),
		'831-555-5555','10 Flynn\'s Arcade Way', 'flynn@encom.com','1')") or die("Error inserting dummy data");
	 mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Kevin Flynn', 'kflynn', '831-555-5555', 'flynn@encom.com','1')") or die("Error inserting dummy data");
	 
	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Malcolm','Reynolds','mreynolds',md5('password'),
		'831-555-5555','290 Serenity Valley Road Santa Cruz, CA 95060', 'malcolm@reynolds.com','1')") or die("Error inserting dummy data");
	 mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Malcolm Reynolds', 'mreynolds', '831-555-5555', 'malcolm@reynolds.com','1')") or die("Error inserting dummy data");
	 
	 include("dummydata.php");
	 /*
	 
	mysql_query("INSERT INTO x2_contacts (firstName, lastName, phone, email, visibility, assignedTo, address, city, state, zipcode, company, title)
		VALUES ('John','Smith','831-555-5555','john@smith.com','1','chames', '123 Main St.', 'Santa Cruz', 'CA', '95060', 'ACME Co.', 'Vice President')")
	 or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_contacts (firstName, lastName, phone, email, visibility, assignedTo, address, city, state, zipcode, company, title)
		VALUES ('David','Tennant','831-555-5555','david@tennant.com','1','jvalerian', '421 Gallifrey Ave.', 'Santa Cruz', 'CA', '95060', 'TARDIS Inc.', 'CEO')")
	 or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_contacts (firstName, lastName, phone, email, visibility, assignedTo, address, city, state, zipcode, company, title)
		VALUES ('William','Adama','831-555-5555','adama@galactica.com','1','ssmith', '224 Cobol Way', 'Santa Cruz', 'CA', '95060', 'Battlestar Ltd.', 'CFO')")
	 or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_contacts (firstName, lastName, phone, email, visibility, assignedTo, address, city, state, zipcode, company, title)
		VALUES ('Jean','Valjean','831-555-5555','jean@lesmis.com','1','kflynn', '123 France Ct.', 'Santa Cruz', 'CA', '95060', 'Miserables Foster Home', 'President')")
	 or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_contacts (firstName, lastName, phone, email, visibility, assignedTo, address, city, state, zipcode, company, title)
		VALUES ('Thor','Odinson','831-555-5555','thunder@gods.com','1','mreynolds', '123 Valhalla Way', 'Santa Cruz', 'CA', '95060', 'Odin & Sons', 'Regional Manager')")
	 or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_contacts (firstName, lastName, phone, email, visibility, assignedTo, address, city, state, zipcode, company, title)
		VALUES ('James','Raynor','831-555-5555','james@korhal.com','1','Anyone', '123 Korhal Ave.', 'Santa Cruz', 'CA', '95060', 'Terran Dominion', 'Sales Rep')")
	 or die("Error inserting dummy data");
		
	
	mysql_query("INSERT INTO x2_sales (name, quoteAmount, salesStage, probability, leadSource, description, assignedTo)
		VALUES ('Trade-in 2006 R1200GS', '6500', 'Working', '90', 'Walk-in', 'New superbike for the the track', 'Anyone')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_sales (name, quoteAmount, salesStage, probability, leadSource, description, assignedTo)
		VALUES ('New S1000RR', '13000', 'Working', '70', 'Walk-in', 'New superbike for the the track', 'Anyone')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_sales (name, quoteAmount, salesStage, probability, leadSource, description, assignedTo)
		VALUES ('New K1300s', '27500', 'Working', '40', 'Walk-in', 'New Enduro for the the track', 'Anyone')") or die("Error inserting dummy data");

	mysql_query("INSERT INTO x2_projects (name, status, type, priority, assignedTo, timeframe, associatedContacts, description) VALUES(
		'Fix Website','Staging','Web', 'Medium', 'chames, kflynn', 'Two Weeks', '2 4', 'Website needs to be fixed up and put back into working order. 
		  Please finish in the next two weeks.')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_projects (name, status, type, priority, assignedTo, timeframe, associatedContacts, description) VALUES(
		'Generate Leads','Staging','Sales', 'High', 'jvalerian, ssmith, mreynolds', 'Three Weeks', '', 'Need to work on generation more leads.
		  Take full advantage of the CRM system and website.')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_projects (name, status, type, priority, assignedTo, timeframe, associatedContacts, description) VALUES(
		'Send Emails','Staging','Web', 'Low', 'mreynolds , kflynn', 'Ongoing', '1 3', 'Make sure to use the e-mail blaster to get the word out to our contacts.
		  Send specific emails when information is available.')") or die("Error inserting dummy data");
		
	mysql_query("INSERT INTO x2_cases (name, status, type, priority, assignedTo, timeframe, associatedContacts, description) VALUES(
		'Issue with SR-1000','Open','On-Site', 'Medium', 'mreynolds', 'One Week', '4', 'Issues with the SR-1000 Model, bringing it into the 
		shop within the week.')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_cases (name, status, type, priority, assignedTo, timeframe, associatedContacts, description) VALUES(
		'Complaint About BMW','Open','Web', 'Medium', 'jvalerian', 'Two Weeks', '2', 'Issues with the BMW manufacturer, 
		need to contact them shortly.')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_cases (name, status, type, priority, assignedTo, timeframe, associatedContacts, description) VALUES(
		'Webpage Error','Open','Web', 'Medium', 'ssmith', 'One Week', '1', 'Error on the website preventing lead-capture from working.
		  Investigate if it\'s our system or contact X2.')") or die("Error inserting dummy data");
	
	mysql_query("INSERT INTO x2_accounts (name, website, type, annualRevenue, phone, tickerSymbol, employees, assignedTo, associatedContacts, description)
		VALUES ('Black Mesa', 'www.blackmesa.com', 'Manufacturing', '67000', '831-555-5555', 'MESA', '30', 'jvalerian', 
			'3', 'Specializes in Resonance Cascades.')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_accounts (name, website, type, annualRevenue, phone, tickerSymbol, employees, assignedTo, associatedContacts, description)
		VALUES ('X2 Engine Inc.', 'www.x2engine.com', 'Web Service', '500000', '831-555-5555', 'XENG', '5', 'mreynolds', 
			'1', 'Designs CRM applications, like this one!')") or die("Error inserting dummy data");
	mysql_query("INSERT INTO x2_accounts (name, website, type, annualRevenue, phone, tickerSymbol, employees, assignedTo, associatedContacts, description)
		VALUES ('Aperture Science', 'www.aperture.com', 'Science', '9000000', '831-555-5555', 'PRTL', '1', 'chames', 
			'4', 'Currently working on transportation technologies.')") or die("Error inserting dummy data");
		*/
}

$GDSupport = function_exists('gd_info')? '1':'0';
$browser = urlencode($_SERVER['HTTP_USER_AGENT']);
$phpVersion = urlencode(phpversion());
$x2Version = urlencode($x2Version);
$dbType = urlencode('MySQL');
$stats = "lang=$lang&currency=$currency&x2Version=$x2Version&dummyData=$data&phpVersion=$phpVersion&dbType=$dbType&GD=$GDSupport&browser=$browser";

if(ini_get('allow_url_fopen') == 1) {
	$context = stream_context_create(array(
		'http' => array(
			'timeout' => 2		// Timeout in seconds
		)
	));
	$ping = file_get_contents('http://x2planet.com/listen.php?'.$stats,0,$context);
}



// Generate splash page

if (!empty($lang))
	$installMessageFile = "protected/messages/$lang/install.php";

$installMessages = array();

if(isset($installMessageFile) && file_exists($installMessageFile)) {	// attempt to load installer messages
	$installMessages = include($installMessageFile);					// from the chosen language
	if (!is_array($installMessages))
		$installMessages = array();						// ...or return an empty array
}

function installer_t($str) {	// translates by looking up string in install.php language file
	global $installMessages;
	if(isset($installMessages[$str]) && $installMessages[$str]!='')		// if the chosen language is available
		return $installMessages[$str];									// and the message is in there, use it
	return $str;
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8" />
<meta name="language" content="en" />
<?php $themeURL = 'themes/x2engine'; ?>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />
<style type="text/css">
body {
	background-color:black;
	padding-top:50px;
}
</style>
</head>
<body>
<div id="installer-box">
 	<h1><?php echo installer_t('Installation Complete!'); ?></h1>
	<ul>
		<li>Able to connect to database</li>
		<li>Dropped old X2Engine tables (if any)</li>
		<li>Created new tables for X2Engine</li>
		<li>Created login for admin account</li>
		<li>Created config file</li>
	</ul>
	<h2>Next Steps</h2>
	<ul>
		<li>Log in to app</li>
		<li>Create new users</li>
		<li>Set location</li>
		<li>Explore the app</li>
	</ul>
 	<h3><a class="x2-button" href="index.php"><?php echo installer_t('Click here to log in to X2Engine'); ?></a></h3><br />
	<?php echo installer_t('X2Engine successfully installed on your web server!  You may now log in with username "admin" and the password you provided during the install.'); ?><br /><br />
	
<a href="http://www.x2engine.com"><?php echo installer_t('For help or more information - X2Engine.com'); ?></a><br /><br />

<hr />
<div id="footer">
	<img src="images/x2engine_big.png">
	Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2engine.com">X2Engine Inc.</a><br />
	<?php echo installer_t('All Rights Reserved.'); ?>
</div>
</div>
</body>
</html>
<?php
// delete install files (including self)
unlink('install.php');
unlink(__FILE__);
?>