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
$x2Version = '0.9.9.1';
$buildDate = 1326314361;

$userData = '';

if(isset($_POST['testDb'])) {
	$con = @mysql_connect($_POST['dbHost'],$_POST['dbUser'],$_POST['dbPass']);
	
	if($con !== false) {
		if($selectDb = @mysql_select_db($_POST['dbName'],$con))
			echo 'DB_OK';
		else
			echo 'DB_COULD_NOT_SELECT';
			
		@mysql_close($con);
	} else
		echo 'DB_CONNECTION_FAILED';
	exit;
}

// run silent installer with default values?
$silent = isset($_GET['silent']) || (isset($argv) && in_array('silent',$argv));

if($silent) {
	if(file_exists('installConfig.php'))
		require('installConfig.php');
	else
		die('Error: Installer config file not found.');
} else {
	$host = $_POST['dbHost'];
	$db = $_POST['dbName'];
	$user = $_POST['dbUser'];
	$pass = $_POST['dbPass'];
	$app = $_POST['app'];
	
	
	$currency = $_POST['currency'];
	$currency2 = strtoupper($_POST['currency2']);
	if($currency == 'other')
		$currency = $currency2;
	if(empty($currency))
		$currency = 'USD';
	$userData .= "";
	
	$lang = $_POST['lang'];
	$timezone = $_POST['timezone'];
	
	$adminEmail = $_POST['adminEmail'];
	$adminPassword = $_POST['adminPass'];
	$adminPassword2 = $_POST['adminPass2'];
	$dummyData = (isset($_POST['data']) && $_POST['data']==1)? 1 : 0;
	
	$userData .= "&dbHost=$host&dbName=$db&dbUser=$user&app=$app&currency=".$_POST['currency']."&currency2=$currency2&lang=$lang&adminEmail=$adminEmail&data=$dummyData&timezone=".urlencode($timezone);
	
}

$webLeadUrl=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$webLeadUrl=substr($webLeadUrl,0,-15);

$contents=file_get_contents('leadCapture.php');
$contents=preg_replace('/\$url=\"\";/',"\$url='$webLeadUrl'",$contents);
file_put_contents('leadCapture.php',$contents);

if(empty($lang))
	$lang='en';
	
if(empty($timezone))
	$timezone='UTC';

//$gii=$_POST['gii'];

$errors = array();

// function test() {
	// if(is_array($errors))
		// die('ok');
	// else
		// die('not ok');
// }
// test();

$app = mysql_escape_string($app);
if(!empty($adminEmail) && !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$adminEmail))
	addError('Please enter a valid email address.');
	
if($adminPassword == '')
	addError('Admin password cannot be blank.');

if(isset($adminPassword2) && $adminPassword != $adminPassword2)
	addError('Admin passwords did not match.');
 

$con = @mysql_connect($host,$user,$pass) or addError('DB_CONNECTION_FAILED');
@mysql_select_db($db,$con) or addError('DB_COULD_NOT_SELECT');


if(!empty($adminEmail))
	$bulkEmail = $adminEmail;
else
	$bulkEmail = 'contact@'.preg_replace('/^www\./','',$_SERVER['HTTP_HOST']);

outputErrors();


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
$handle = fopen($fileName,'w+') or addError('Couldn\'t create config file');
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
$handle = fopen($filename, 'w') or addError('Couldn\'t create e-mail drop box config');
$write = 
"<?php
\$host='$host';
\$user='$user';
\$pass='$pass';
\$dbname='$db';
\$version='$x2Version';
\$buildDate=$buildDate;
\$updaterVersion='1.0';
?>";
fwrite($handle,$write);
fclose($handle);

outputErrors();

function outputErrors() {
	global $errors;
	global $userData;
	if(count($errors)>0) {
		$errorData = implode('&errors%5B%5D=',$errors);
		$url = preg_replace('/initialize/','install',$_SERVER['REQUEST_URI']);
		header("Location: $url?errors%5B%5D=".$errorData.$userData);
		die();
	}
}

function addError($message) {
	global $errors;
	$errors[] = $message;
}

$sqlError = '';
function addSqlError($message) {
	global $sqlError;
	if(empty($sqlError))
		$sqlError = $message;
}

// $dbSetupResult = setupDb();

// function setupDb() {
// global $sqlError;
// global $lang;

mysql_query("DROP TABLE IF EXISTS
	x2_users,
	x2_contacts,
	x2_actions,
	x2_sales,
	x2_projects,
	x2_marketing,
	x2_campaigns,
	x2_contact_lists,
	x2_list_items,
	x2_list_criteria,
	x2_cases,
	x2_profile,
	x2_accounts,
	x2_notes,
	x2_social,
	x2_docs,
	x2_media,
	x2_admin,
	x2_changelog,
	x2_tags,
	x2_relationships,
	x2_notifications,
	x2_criteria,
	x2_lead_routing,
	x2_sessions,
	x2_workflows,
	x2_workflow_stages,
	x2_fields,
	x2_roles,
	x2_role_to_user,
	x2_role_to_permission,
        x2_dropdowns
") or addSqlError('Unable to delete exsting tables.'.mysql_error());

// if(!empty($sqlError)) return $sqlError;

mysql_query("CREATE TABLE x2_users(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
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
	lastUpdated VARCHAR(30),
	updatedBy VARCHAR(20),
	recentItems VARCHAR(100),
	topContacts VARCHAR(100),
	lastLogin INT DEFAULT 0,
	login INT DEFAULT 0,
	UNIQUE(username, emailAddress))
	COLLATE = utf8_general_ci
	") or addSqlError('Unable to create table x2_users.'.mysql_error());

mysql_query("CREATE TABLE x2_contacts(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
	firstName VARCHAR(40) NOT NULL,
	lastName VARCHAR(40) NOT NULL,
	title VARCHAR(40),
	company VARCHAR(100),
	accountId INT DEFAULT 0,
	phone VARCHAR(40),
	email VARCHAR(250),
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
	otherUrl VARCHAR(100) NULL,
        phone2 VARCHAR(40))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_contacts.'.mysql_error());

//mysql_query("SOURCE /x2engine/install.sql; ") or die(mysql_error();

mysql_query("CREATE TABLE x2_actions(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
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
") or addSqlError('Unable to create table x2_actions.'.mysql_error());

 mysql_query("CREATE TABLE x2_sales(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
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
") or addSqlError('Unable to create table x2_sales.'.mysql_error());

mysql_query("CREATE TABLE x2_projects(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
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
") or addSqlError('Unable to create table x2_projects.'.mysql_error());

// mysql_query("CREATE TABLE x2_marketing(
	// id INT NOT NULL AUTO_INCREMENT primary key,
	// name VARCHAR(20) NOT NULL,
	// cost INT,
	// result TEXT,
	// createDate INT,
	// description TEXT,
	// lastUpdated INT,
	// updatedBy VARCHAR(20))
	// COLLATE = utf8_general_ci
// ") or die('Unable to create table x2_marketing.'.mysql_error());

mysql_query("CREATE TABLE x2_campaigns (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	masterId INT UNSIGNED NOT NULL,
	name VARCHAR(100) NOT NULL,
	description TEXT NULL,
	type VARCHAR(20) NULL,
	cost VARCHAR(100) NULL,
	result TEXT NULL,
	content TEXT NULL,
	createdBy VARCHAR(20) NOT NULL,
	createDate INT UNSIGNED NOT NULL,
	launchDate INT UNSIGNED NOT NULL,
	lastUpdated INT UNSIGNED NOT NULL
	) COLLATE utf8_general_ci
") or addSqlError('Unable to create table x2_campaigns.'.mysql_error());

mysql_query("CREATE TABLE x2_contact_lists (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	campaignId INT UNSIGNED NOT NULL DEFAULT 0,
	assignedTo VARCHAR(20),
	name VARCHAR(100) NOT NULL,
	description VARCHAR(250) NULL,
	type VARCHAR(20) NULL,
	visibility INT NOT NULL DEFAULT 1,
	count INT UNSIGNED NOT NULL DEFAULT 0,
	createDate INT UNSIGNED NOT NULL,
	lastUpdated INT UNSIGNED NOT NULL
	) COLLATE utf8_general_ci
") or addSqlError('Unable to create table x2_lists.'.mysql_error());

mysql_query("CREATE TABLE x2_list_items (
	contactId INT UNSIGNED NOT NULL,
	listId INT UNSIGNED NOT NULL,
	code VARCHAR(32) NULL,
	result TINYINT UNSIGNED NOT NULL DEFAULT 0,
	INDEX (contactId),
	FOREIGN KEY (listId) REFERENCES x2_contact_lists(id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (contactId) REFERENCES x2_contacts(id) ON UPDATE CASCADE ON DELETE CASCADE
	) COLLATE utf8_general_ci
") or addSqlError('Unable to create table x2_listItems.'.mysql_error());


mysql_query("CREATE TABLE x2_list_criteria (
	listId INT UNSIGNED NOT NULL,
	type VARCHAR(20) NULL,
	attribute VARCHAR(40) NULL,
	comparison VARCHAR(10) NULL,
	value VARCHAR(100) NOT NULL,
	FOREIGN KEY (listId) REFERENCES x2_contact_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
	) COLLATE utf8_general_ci
") or addSqlError('Unable to create table x2_listCriteria.'.mysql_error());

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
") or addSqlError('Unable to create table x2_cases.'.mysql_error());

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
	timeZone VARCHAR(100) DEFAULT '$timezone',
	resultsPerPage INT DEFAULT 20,
	widgets VARCHAR(255) DEFAULT '1:1:1:1:1:1:0:1:1',
	widgetOrder VARCHAR(255) DEFAULT 'OnlineUsers:ChatBox:MessageBox:QuickContact:GoogleMaps:TwitterFeed:NoteBox:ActionMenu:TagCloud',
	backgroundColor VARCHAR(6) NULL,
	menuBgColor VARCHAR(6) NULL,
	menuTextColor VARCHAR(6) NULL,
	backgroundImg VARCHAR(100) NULL DEFAULT 'santacruznight_blur.jpg',
	pageOpacity INT NULL,
	startPage VARCHAR(30) NULL,
	showSocialMedia TINYINT(1) NOT NULL DEFAULT 0,
	showDetailView TINYINT(1) NOT NULL DEFAULT 1,
	showWorkflow TINYINT(1) NOT NULL DEFAULT 1,
	gridviewSettings TEXT,
	emailUseSignature VARCHAR(5) DEFAULT 'user',
	emailSignature VARCHAR(512),
	enableBgFade TINYINT DEFAULT 0,
	UNIQUE(username, emailAddress))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_profile.'.mysql_error());

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
") or addSqlError('Unable to create table x2_accounts.'.mysql_error());


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
") or addSqlError('Unable to create table x2_social.'.mysql_error());

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
") or addSqlError('Unable to create table x2_docs.'.mysql_error());

mysql_query("CREATE TABLE x2_media(
	id INT NOT NULL AUTO_INCREMENT primary key,
	associationType VARCHAR(40) NOT NULL,
	associationId INT,
	uploadedBy VARCHAR(40),
	fileName VARCHAR(100),
	createDate INT)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_media.'.mysql_error());

mysql_query("CREATE TABLE x2_admin(
	id INT NOT NULL AUTO_INCREMENT primary key,
	accounts INT,
	sales INT,
	timeout INT,
	webLeadEmail VARCHAR(255),
	currency VARCHAR(3) NULL,
	menuOrder VARCHAR(255),
	menuVisibility VARCHAR(100),
	menuNicknames VARCHAR(255),
	chatPollTime INT DEFAULT 2000,
	ignoreUpdates TINYINT DEFAULT 0,
	rrId INT DEFAULT 0, 
	leadDistribution VARCHAR(255),
	onlineOnly TINYINT,
	emailFromName VARCHAR(255) NOT NULL DEFAULT 'X2CRM',
	emailFromAddr VARCHAR(255) NOT NULL DEFAULT '$bulkEmail',
	emailUseSignature VARCHAR(5) DEFAULT 'user',
	emailSignature VARCHAR(512),
	emailType VARCHAR(20) DEFAULT 'mail',
	emailHost VARCHAR(255),
	emailPort INT DEFAULT 25,
	emailUseAuth VARCHAR(5) DEFAULT 'user',
	emailUser VARCHAR(255),
	emailPass VARCHAR(255),
	emailSecurity VARCHAR(10),
	installDate INT UNSIGNED NOT NULL,
	updateDate INT UNSIGNED NOT NULL,
	updateInterval INT NOT NULL
	)
	COLLATE = utf8_general_ci
	") or addSqlError('Unable to create table x2_admin.'.mysql_error());

mysql_query("CREATE TABLE x2_changelog( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	type VARCHAR(50) NOT NULL,
	itemId INT NOT NULL,
	changedBy VARCHAR(50) NOT NULL,
	changed TEXT NOT NULL,
	timestamp INT NOT NULL DEFAULT 0)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_changelog.'.mysql_error());

mysql_query("CREATE TABLE x2_tags( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	type VARCHAR(50) NOT NULL,
	itemId INT NOT NULL,
	taggedBy VARCHAR(50) NOT NULL,
	tag VARCHAR(250) NOT NULL,
	itemName VARCHAR(250),
	timestamp INT NOT NULL DEFAULT 0)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_tags.'.mysql_error());

mysql_query("CREATE TABLE x2_relationships( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	firstType VARCHAR(100),
	firstId INT,
	secondType VARCHAR(100),
        secondId INT)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_relationshps.'.mysql_error());

mysql_query("CREATE TABLE x2_notifications( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	text TEXT,
	record VARCHAR(250), 
	user VARCHAR(100),
	viewed INT,
	createDate INT)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_notifications.'.mysql_error());

mysql_query("CREATE TABLE x2_criteria( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	modelType VARCHAR(100),
	modelField VARCHAR(250),
	modelValue TEXT,
        comparisonOperator VARCHAR(10),
        users TEXT,
        type VARCHAR(250))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_criteria.'.mysql_error());

mysql_query("CREATE TABLE x2_lead_routing( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	field VARCHAR(250),
	value VARCHAR(250),
	users TEXT,
        rrId INT DEFAULT 0)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_lead_routing.'.mysql_error());

mysql_query("CREATE TABLE x2_sessions(
	id INT NOT NULL AUTO_INCREMENT primary key,
	user VARCHAR(250),
	lastUpdated INT)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_sessions.'.mysql_error());

mysql_query("CREATE TABLE x2_workflows( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(250),
	lastUpdated INT)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_workflows.'.mysql_error());

mysql_query("CREATE TABLE x2_workflow_stages( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	workflowId INT NOT NULL,
	stageNumber INT,
	name VARCHAR(40),
	conversionRate DECIMAL(10,2),
	value DECIMAL(10,2),
	FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_workflow_stages.'.mysql_error());

mysql_query("CREATE TABLE x2_fields (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	modelName varchar(250) ,
	fieldName varchar(250),
	attributeLabel varchar(250),
	visible int,
	custom int,
	modified INT DEFAULT 0,
        coordinates VARCHAR(250) DEFAULT '0:0',
        size VARCHAR(250) DEFAULT '350px:30px',
        tabOrder INT,
        type VARCHAR(250) DEFAULT 'varchar')
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_fields.'.mysql_error());

mysql_query("CREATE TABLE x2_dropdowns (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	name varchar(250) ,
	options text)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_dropdowns.'.mysql_error());

mysql_query("CREATE TABLE x2_roles (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	name varchar(250) ,
	users text)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_roles.'.mysql_error());

mysql_query("CREATE TABLE x2_role_to_user (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	roleId int ,
	userId int)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_role_to_user.'.mysql_error());

mysql_query("CREATE TABLE x2_role_to_permission (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	roleId int ,
	fieldId int,
        permission int)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_role_to_user.'.mysql_error());

mysql_query("INSERT INTO  x2_fields (modelName, fieldName, attributeLabel, visible, custom, coordinates, size, type) VALUES 

	('Contacts',	'id',				'ID',			1,	0,  '0:0',      '0px:0px',     'varchar'),
	('Contacts',	'firstName',                    'First Name',		1,	0,  '0:0',      '155px:20px',  'varchar'),
	('Contacts',	'lastName',			'Last Name',		1,	0,  '170:0',    '160px:20px',  'varchar'),
	('Contacts',	'title',			'Title',		1,	0,  '0:40',     '175px:20px',  'varchar'),
	('Contacts',	'company',			'Account',		1,	0,  '190:40',   '285px:20px',  'varchar'),
	('Contacts',	'accountId',                    'Account ID',		0,	0,  '0:0',      '0px:0px',     'varchar'),
	('Contacts',	'phone',			'Phone',		1,	0,  '0:80',     '125px:20px',  'varchar'),
	('Contacts',	'phone2',			'Phone 2',		1,	0,  '0:120',    '125px:20px',  'varchar'),
	('Contacts',	'email',			'Email',		1,	0,  '150:120',  '325px:20px',  'varchar'),
	('Contacts',	'website',			'Website',		1,	0,  '150:80',   '325px:20px',  'varchar'),
	('Contacts',	'twitter',			'Twitter',		1,	0,  '0:480',    '225px:20px',  'varchar'),
	('Contacts',	'linkedin',			'Linkedin',		1,	0,  '0:520',    '225px:20px',  'varchar'),
	('Contacts',	'skype',			'Skype',		1,	0,  '250:440',  '225px:20px',  'varchar'),
	('Contacts',	'googleplus',                   'Googleplus',		1,	0,  '250:480',  '225px:20px',  'varchar'),
	('Contacts',	'address',			'Address',		1,	0,  '0:160',    '325px:20px',  'varchar'),
	('Contacts',	'city',				'City',			1,	0,  '340:160',  '135px:20px',  'varchar'),
	('Contacts',	'state',			'State',		1,	0,  '0:200',    '140px:25px',  'varchar'),
	('Contacts',	'zipcode',			'Postal Code',		1,	0,  '160:200',  '80px:20px',   'varchar'),
	('Contacts',	'country',			'Country',		1,	0,  '260:200',  '215px:20px',  'varchar'),
	('Contacts',	'visibility',                   'Visibility',		1,	0,  '330:400',  '145px:20px',  'varchar'),
	('Contacts',	'assignedTo',                   'Assigned To',		1,	0,  '0:400',    '170px:20px',  'varchar'),
	('Contacts',	'backgroundInfo',               'Background Info',	1,	0,  '0:280',    '475px:95px',  'text'),
	('Contacts',	'lastUpdated',                  'Last Updated',		0,	0,  '0:0',      '350px:30px',  'varchar'),
	('Contacts',	'updatedBy',                    'Updated By',		0,	0,  '0:0',      '350px:30px',  'varchar'),
	('Contacts',	'leadSource',                   'Lead Source',		1,	0,  '0:240',    '240px:20px',  'varchar'),
	('Contacts',	'priority',			'Priority',		1,	0,  '190:400',  '120px:20px',  'varchar'),
	('Contacts',	'rating',			'Rating',		1,	0,  '350:0',    '125px:20px',  'varchar'),
	('Contacts',	'createDate',                   'Create Date',		0,	0,  '0:0',      '350px:30px',  'varchar'),
	('Contacts',	'facebook',			'Facebook',		1,	0,  '0:440',    '225px:20px',  'varchar'),
	('Contacts',	'otherUrl',			'Other',		1,	0,  '250:520',  '225px:20px',  'varchar')
;")
or addSqlError("Unable to create contact fields.".mysql_error());	
mysql_query("INSERT INTO  x2_fields (modelName, fieldName, attributeLabel, visible, custom) VALUES 
    
	('Accounts',	'name',				'Name',				1,	0),
	('Accounts',	'id',				'ID',				1,	0),
	('Accounts',	'website',			'Website',			1,	0),
	('Accounts',	'type',				'Type',				1,	0),
	('Accounts',	'annualRevenue',		'Revenue',			1,	0),
	('Accounts',	'phone',			'Phone',			1,	0),
	('Accounts',	'tickerSymbol',			'Symbol',			1,	0),
	('Accounts',	'employees',			'Employees',		1,	0),
	('Accounts',	'assignedTo',			'Assigned To',		1,	0),
	('Accounts',	'createDate',			'Create Date',		1,	0),
	('Accounts',	'associatedContacts',           'Contacts',			1,	0),
	('Accounts',	'description',			'Description',		1,	0),
	('Accounts',	'lastUpdated',			'Last Updated',		1,	0),
	('Accounts',	'updatedBy',			'Updated By',		1,	0),
	
	('Actions',		'id',				'ID',				1,	0),
	('Actions',		'assignedTo',			'Assigned To',		1,	0),
	('Actions',		'actionDescription',            'Description',		1,	0),
	('Actions',		'visibility',			'Visibility',		1,	0),
	('Actions',		'associationId',		'Contact',			1,	0),
	('Actions',		'associationType',		'Association Type',	1,	0),
	('Actions',		'associationName',		'Association',		1,	0),
	('Actions',		'dueDate',			'Due Date',			1,	0),
	('Actions',		'priority',			'Priority',			1,	0),
	('Actions',		'type',				'Action Type',		1,	0),
	('Actions',		'createDate',			'Create Date',		1,	0),
	('Actions',		'complete',			'Complete',			1,	0),
	('Actions',		'reminder',			'Reminder',			1,	0),
	('Actions',		'completedBy',			'Completed By',		1,	0),
	('Actions',		'completeDate',			'Date Completed',	1,	0),
	('Actions',		'lastUpdated',			'Last Updated',		1,	0),
	('Actions',		'updatedBy',			'Updated By',		1,	0),
	
	('Sales',	'id',				'ID',					1,	0),
	('Sales',	'name',                         'Name',					1,	0),
	('Sales',	'accountId',			'Account ID',			1,	0),
	('Sales',	'accountName',			'Account',				1,	0),
	('Sales',	'quoteAmount',			'Quote Amount',			1,	0),
	('Sales',	'salesStage',			'Sales Stage',			1,	0),
	('Sales',	'expectedCloseDate',            'Expected Close Date',	1,	0),
	('Sales',	'probability',			'Probability',			1,	0),
	('Sales',	'leadSource',			'Lead Source',			1,	0),
	('Sales',	'description',			'Description',			1,	0),
	('Sales',	'assignedTo',			'Assigned To',			1,	0),
	('Sales',	'createDate',			'Create Date',			1,	0),
	('Sales',	'associatedContacts',           'Contacts',				1,	0),
	('Sales',	'lastUpdated',			'Last Updated',			1,	0),
	('Sales',	'updatedBy',			'Updated By',			1,	0)
;")
or addSqlError("Unable to create fields.".mysql_error());

// if(!empty($sqlError)) return $sqlError;
//UNSIGNED


$adminPassword = md5($adminPassword); 
$adminEmail = mysql_escape_string($adminEmail);

mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin) VALUES ('web','admin','admin','$adminPassword',
			'$adminEmail' ,'1', '0')") or addSqlError("Error inserting admin information.");
mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Web Admin', 'admin', '831-555-5555', '$adminEmail','1')") or addSqlError("Error inserting dummy data");
mysql_query("INSERT INTO x2_social (type, data) VALUES ('motd', 'Please enter a message of the day!')") or addSqlError("Unable to set starting MOTD.");

mysql_query("INSERT INTO x2_admin (
	accounts,
	sales,
	timeout,
	webLeadEmail,
	menuOrder,
	menuNicknames,
	menuVisibility,
	currency,
	installDate,
	updateDate
) VALUES (
	'0',
	'1',
	'3600',
	'$adminEmail',
	'actions:contacts:docs:sales:accounts:workflow',
	'Actions:Contacts:Docs:Sales:Accounts:Workflow',
	'1:1:1:1:1:1',
	'$currency',
	'".time()."',
	0
)") or addSqlError("Unable to input admin config");

$backgrounds = array(
	'santacruznight_blur.jpg',
	// 'screens2cc.jpg',
	// 'calico.jpg',
	// 'calico_blur.jpg',
	'santa_cruz.jpg',
	'santa_cruz_blur.jpg',
	// 'moss_landing.jpg',
	// 'moss_landing_blur.jpg',
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

if($dummyData){

	mysql_query("INSERT INTO x2_workflows (id, name) VALUES (1,'General Sales')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, stageNumber, name) VALUES (1,1,1,'Lead')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, stageNumber, name) VALUES (2,1,2,'Suspect')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, stageNumber, name) VALUES (3,1,3,'Prospect')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, stageNumber, name) VALUES (4,1,4,'Customer')") or addSqlError("Error inserting workflow data.");
	
	

	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Chris','Hames','chames',md5('password'),
		'831-555-5555','10 Downing St. Santa Cruz, CA 95060', 'chris@hames.com','1')") or addSqlError("Error inserting dummy data");
	mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Chris Hames', 'chames', '831-555-5555', 'chris@hames.com','1')") or addSqlError("Error inserting dummy data");

	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('James','Valerian','jvalerian',md5('password'),
		'831-555-5555','123 Main St. Santa Cruz, CA 95060', 'james@valerian.com','1')") or addSqlError("Error inserting dummy data");
	mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('James Valerian', 'jvalerian', '831-555-5555', 'james@valerian.com','1')") or addSqlError("Error inserting dummy data");

	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Sarah','Smith','ssmith',md5('password'),
		'831-555-5555','467 2nd Ave. Santa Cruz, CA 95060', 'sarah@smith.com','1')") or addSqlError("Error inserting dummy data");
	mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Sarah Smith', 'ssmith', '831-555-5555', 'sarah@smith.com','1')") or addSqlError("Error inserting dummy data");

	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Kevin','Flynn','kflynn',md5('password'),
		'831-555-5555','10 Flynn\'s Arcade Way', 'flynn@encom.com','1')") or addSqlError("Error inserting dummy data");
	mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Kevin Flynn', 'kflynn', '831-555-5555', 'flynn@encom.com','1')") or addSqlError("Error inserting dummy data");

	mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, officePhone, address, emailAddress, status) VALUES ('Malcolm','Reynolds','mreynolds',md5('password'),
		'831-555-5555','290 Serenity Valley Road Santa Cruz, CA 95060', 'malcolm@reynolds.com','1')") or addSqlError("Error inserting dummy data");
	mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Malcolm Reynolds', 'mreynolds', '831-555-5555', 'malcolm@reynolds.com','1')") or addSqlError("Error inserting dummy data");

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
	// return $sqlErrors;
}
mysql_close($con);

// }

if(!empty($sqlError)) {
	$errors[] = 'MySQL Error: '.$sqlError;
	outputErrors();
	// die();
}
outputErrors();



$GDSupport = function_exists('gd_info')? '1':'0';
$browser = urlencode($_SERVER['HTTP_USER_AGENT']);
$phpVersion = urlencode(phpversion());
$x2Version = urlencode($x2Version);
$timezone = urlencode($timezone);
$dbType = urlencode('MySQL');
$stats = "lang=$lang&currency=$currency&x2Version=$x2Version&dummyData=$dummyData&phpVersion=$phpVersion&dbType=$dbType&GD=$GDSupport&browser=$browser&timezone=$timezone";

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
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/install.css" />
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
	<img src="http://x2planet.com/listen.php?<?php echo $stats; ?>" style="display:inline">
</div>
</div>
</body>
</html>
<?php
// delete install files (including self)
if(file_exists('install.php'))
	unlink('install.php');
if(file_exists('installConfig.php'))
	unlink('installConfig.php');
unlink(__FILE__);
?>