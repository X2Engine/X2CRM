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
$x2Version = '0.9.10';
$buildDate = 1328920341;

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
	x2_quotes,
	x2_products,
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
	x2_quotes_products,
	x2_criteria,
	x2_lead_routing,
	x2_sessions,
	x2_workflows,
	x2_workflow_stages,
	x2_fields,
	x2_form_layouts,
	x2_roles,
	x2_role_to_user,
	x2_role_to_permission,
	x2_dropdowns,
	x2_groups,
	x2_group_to_user
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
	UNIQUE(username, emailAddress),
	INDEX (username))
	COLLATE = utf8_general_ci
	") or addSqlError('Unable to create table x2_users.'.mysql_error());

mysql_query("CREATE TABLE x2_contacts(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
	firstName VARCHAR(40) NOT NULL,
	lastName VARCHAR(40) NOT NULL,
	title VARCHAR(40),
	company VARCHAR(250),
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
        phone2 VARCHAR(40),
        leadtype VARCHAR(250),
        closedate VARCHAR(250),
        interest VARCHAR(250),
        leadstatus VARCHAR(250),
        dealvalue VARCHAR(250))
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
	updatedBy VARCHAR(20),
	workflowId INT UNSIGNED,
	stageNumber INT UNSIGNED)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_actions.'.mysql_error());

 mysql_query("CREATE TABLE x2_sales(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(40) NOT NULL,
	accountName VARCHAR(100),
	quoteAmount INT,
	salesStage VARCHAR(20),
	expectedCloseDate VARCHAR(20),
	probability INT,
	leadSource VARCHAR(100),
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	associatedContacts TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_sales.'.mysql_error());

 mysql_query("CREATE TABLE x2_quotes(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(40) NOT NULL,
	accountName VARCHAR(100),
	accountId INT DEFAULT 0,
	salesStage VARCHAR(20),
	expectedCloseDate VARCHAR(20),
	probability INT,
	leadSource VARCHAR(10),
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	associatedContacts TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20),
	expirationDate INT,
	status VARCHAR(20),
	currency VARCHAR(40))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_quotes.'.mysql_error());

 mysql_query("CREATE TABLE x2_products(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(100) NOT NULL,
	type VARCHAR(100),
	price FLOAT,
	inventory INT,
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	lastUpdated INT,
	updatedBy VARCHAR(20),
	active TINYINT,
	currency VARCHAR(40),
	adjustment FLOAT)
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
	formSettings TEXT,
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
	type VARCHAR(10) NOT NULL DEFAULT '',
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

mysql_query("CREATE TABLE x2_quotes_products( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	quoteId INT,
	productId INT,
	quantity INT,
	name VARCHAR(100) NOT NULL,
	type VARCHAR(100),
	price FLOAT,
	inventory INT,
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	lastUpdated INT,
	updatedBy VARCHAR(20),
	active TINYINT,
	currency VARCHAR(40),
	adjustment FLOAT,
	adjustmentType VARCHAR(20))
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
	rrId INT DEFAULT 0
) COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_lead_routing.'.mysql_error());

mysql_query("CREATE TABLE x2_sessions(
	id INT NOT NULL AUTO_INCREMENT primary key,
	user VARCHAR(250),
	lastUpdated INT,
	IP VARCHAR(40) NOT NULL,
	status TINYINT NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_sessions.'.mysql_error());

mysql_query("CREATE TABLE x2_workflows( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	name VARCHAR(250),
	lastUpdated INT
) COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_workflows.'.mysql_error());

mysql_query("CREATE TABLE x2_workflow_stages( 
	id INT NOT NULL AUTO_INCREMENT primary key,
	workflowId INT NOT NULL,
	stageNumber INT,
	name VARCHAR(40),
	conversionRate DECIMAL(10,2),
	value DECIMAL(10,2),
	requirePrevious TINYINT DEFAULT 0,
	requireComment TINYINT DEFAULT 0,
	FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_workflow_stages.'.mysql_error());

mysql_query("CREATE TABLE x2_fields (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	modelName varchar(250) ,
	fieldName varchar(250),
	attributeLabel varchar(250),
	modified INT DEFAULT 0,
	type VARCHAR(250) DEFAULT 'varchar',
        required INT DEFAULT 0,
        linkType VARCHAR(250)
) COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_fields.'.mysql_error());

mysql_query("CREATE TABLE x2_form_layouts (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	model VARCHAR(250) NOT NULL,
	version VARCHAR(250) NOT NULL,
	layout TEXT,
	defaultView TINYINT NOT NULL DEFAULT 0,
	defaultForm TINYINT NOT NULL DEFAULT 0,
	createDate INT UNSIGNED,
	lastUpdated INT UNSIGNED)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_form_versions.'.mysql_error());

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
	userId int,
	type VARCHAR(250))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_role_to_user.'.mysql_error());

mysql_query("CREATE TABLE x2_role_to_permission (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	roleId int ,
	fieldId int,
	permission int)
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_role_to_user.'.mysql_error());

mysql_query("CREATE TABLE x2_groups (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	name varchar(250))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_roles.'.mysql_error());

mysql_query("CREATE TABLE x2_group_to_user (
	id int(11) NOT NULL AUTO_INCREMENT primary key,
	groupId INT,
        userId INT,
	username VARCHAR(250))
	COLLATE = utf8_general_ci
") or addSqlError('Unable to create table x2_roles.'.mysql_error());


mysql_query('INSERT INTO x2_form_layouts (id, model,version,layout, defaultView,defaultForm,createDate,lastUpdated) VALUES 
("1","Contacts","Form","{\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":286,\"items\":[{\"name\":\"formItem_firstName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":301,\"items\":[{\"name\":\"formItem_lastName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales &amp; Marketing\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"180\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"183\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"}]},{\"width\":302,\"items\":[{\"name\":\"formItem_interest\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealvalue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Address\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"312\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"167\",\"tabindex\":\"0\"},{\"name\":\"formItem_state\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Background Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"51\",\"width\":\"488\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Social Media\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_skype\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_linkedin\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_twitter\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_facebook\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_googleplus\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_otherUrl\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("2","Sales","Form","{\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Other Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"184\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"482\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("3","Contacts","View","{\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":286,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":301,\"items\":[{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales &amp; Marketing\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"180\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"183\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"}]},{\"width\":302,\"items\":[{\"name\":\"formItem_interest\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealvalue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Address\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"312\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"167\",\"tabindex\":\"0\"},{\"name\":\"formItem_state\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"inline\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Background Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"51\",\"width\":\"488\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Social Media\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_skype\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_linkedin\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_twitter\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_facebook\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_googleplus\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_otherUrl\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("4","Sales","View","{\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Other Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"184\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"482\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("5","Products","Form","{\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"1\"},{\"name\":\"formItem_active\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"3\"},{\"name\":\"formItem_adjustment\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"6\"},{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"4\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"8\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("6","Products","View","{\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"9\"},{\"name\":\"formItem_active\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"10\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"3\"},{\"name\":\"formItem_adjustment\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"6\"},{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"4\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"8\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("7","Accounts","Form","{\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Additional Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("8","Accounts","View","{\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Additional Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'")')
or addSqlError("Unable to create contacts layout.".mysql_error());

mysql_query("INSERT INTO `x2_fields` VALUES (1,'Contacts','id','ID',0,'varchar',0,''),(2,'Contacts','firstName','First Name',0,'varchar',1,''),(3,'Contacts','lastName','Last Name',0,'varchar',1,''),(4,'Contacts','title','Title',0,'varchar',0,''),(5,'Contacts','company','Account',0,'link',0,'accounts'),(6,'Contacts','phone','Phone',0,'varchar',0,''),(7,'Contacts','phone2','Phone 2',0,'varchar',0,''),(8,'Contacts','email','Email',0,'email',0,''),(9,'Contacts','website','Website',0,'url',0,''),(10,'Contacts','twitter','Twitter',0,'varchar',0,''),(11,'Contacts','linkedin','Linkedin',0,'varchar',0,''),(12,'Contacts','skype','Skype',0,'varchar',0,''),(13,'Contacts','googleplus','Googleplus',0,'varchar',0,''),(14,'Contacts','address','Address',0,'varchar',0,''),(15,'Contacts','city','City',0,'varchar',0,''),(16,'Contacts','state','State',0,'varchar',0,''),(17,'Contacts','zipcode','Postal Code',0,'varchar',0,''),(18,'Contacts','country','Country',0,'varchar',0,''),(19,'Contacts','visibility','Visibility',0,'visibility',1,''),(20,'Contacts','assignedTo','Assigned To',0,'assignment',0,''),(21,'Contacts','backgroundInfo','Background Info',0,'text',0,''),(22,'Contacts','lastUpdated','Last Updated',0,'date',0,''),(23,'Contacts','updatedBy','Updated By',0,'varchar',0,''),(24,'Contacts','leadSource','Lead Source',0,'dropdown',0,'4'),(25,'Contacts','priority','Priority',0,'varchar',0,''),(26,'Contacts','rating','Rating',0,'rating',0,''),(27,'Contacts','createDate','Create Date',0,'date',0,''),(28,'Contacts','facebook','Facebook',0,'varchar',0,''),(29,'Contacts','otherUrl','Other',0,'varchar',0,''),(30,'Contacts','leadtype','Lead Type',0,'dropdown',0,'3'),(31,'Contacts','closedate','Est. Close Date',0,'date',0,''),(32,'Contacts','interest','Interest',0,'varchar',0,''),(33,'Products','currency','Currency',0,'dropdown',0,'2'),(34,'Products','active','Active',0,'dropdown',0,'1'),(35,'Sales','id','ID',0,'varchar',0,''),(36,'Sales','name','Name',0,'varchar',0,''),(37,'Sales','accountName','Account',0,'link',0,'accounts'),(38,'Sales','quoteAmount','Quote Amount',0,'currency',0,''),(39,'Sales','salesStage','Sales Stage',0,'dropdown',0,'6'),(40,'Sales','expectedCloseDate','Expected Close Date',0,'date',0,''),(41,'Sales','probability','Probability',0,'int',0,''),(42,'Sales','leadSource','Lead Source',0,'dropdown',0,'4'),(43,'Sales','description','Description',0,'text',0,''),(44,'Sales','assignedTo','Assigned To',0,'assignment',0,''),(45,'Sales','createDate','Create Date',0,'date',0,''),(46,'Sales','associatedContacts','Contacts',0,'varchar',0,''),(47,'Sales','lastUpdated','Last Updated',0,'date',0,''),(48,'Sales','updatedBy','Updated By',0,'varchar',0,''),(49,'Accounts','name','Name',0,'varchar',0,NULL),(50,'Accounts','id','ID',0,'varchar',0,NULL),(51,'Accounts','website','Website',0,'url',0,NULL),(52,'Accounts','type','Type',0,'varchar',0,NULL),(53,'Accounts','annualRevenue','Revenue',0,'currency',0,NULL),(54,'Accounts','phone','Phone',0,'varchar',0,NULL),(55,'Accounts','tickerSymbol','Symbol',0,'varchar',0,NULL),(56,'Accounts','employees','Employees',0,'int',0,NULL),(57,'Accounts','assignedTo','Assigned To',0,'assignment',0,'multiple'),(58,'Accounts','createDate','Create Date',0,'date',0,NULL),(59,'Accounts','associatedContacts','Contacts',0,'varchar',0,NULL),(60,'Accounts','description','Description',0,'text',0,NULL),(61,'Accounts','lastUpdated','Last Updated',0,'date',0,NULL),(62,'Accounts','updatedBy','Updated By',0,'varchar',0,NULL),(63,'Actions','id','ID',0,'varchar',0,NULL),(64,'Actions','assignedTo','Assigned To',0,'varchar',0,NULL),(65,'Actions','actionDescription','Description',0,'varchar',0,NULL),(66,'Actions','visibility','Visibility',0,'varchar',0,NULL),(67,'Actions','associationId','Contact',0,'varchar',0,NULL),(68,'Actions','associationType','Association Type',0,'varchar',0,NULL),(69,'Actions','associationName','Association',0,'varchar',0,NULL),(70,'Actions','dueDate','Due Date',0,'varchar',0,NULL),(71,'Actions','priority','Priority',0,'varchar',0,NULL),(72,'Actions','type','Action Type',0,'varchar',0,NULL),(73,'Actions','createDate','Create Date',0,'varchar',0,NULL),(74,'Actions','complete','Complete',0,'varchar',0,NULL),(75,'Actions','reminder','Reminder',0,'varchar',0,NULL),(76,'Actions','completedBy','Completed By',0,'varchar',0,NULL),(77,'Actions','completeDate','Date Completed',0,'varchar',0,NULL),(78,'Actions','lastUpdated','Last Updated',0,'varchar',0,NULL),(79,'Actions','updatedBy','Updated By',0,'varchar',0,NULL),(80,'Quotes','id','ID',0,'varchar',0,NULL),(81,'Quotes','name','Name',0,'varchar',0,NULL),(82,'Quotes','accountId','Account ID',0,'varchar',0,NULL),(83,'Quotes','accountName','Account',0,'varchar',0,NULL),(84,'Quotes','existingProducts','Existing Products',0,'varchar',0,NULL),(85,'Quotes','salesStage','Sales Stage',0,'varchar',0,NULL),(86,'Quotes','expectedCloseDate','Expected Close Date',0,'varchar',0,NULL),(87,'Quotes','probability','Probability',0,'varchar',0,NULL),(88,'Quotes','leadSource','Lead Source',0,'varchar',0,NULL),(89,'Quotes','description','Notes',0,'varchar',0,NULL),(90,'Quotes','assignedTo','Assigned To',0,'varchar',0,NULL),(91,'Quotes','createDate','Create Date',0,'varchar',0,NULL),(92,'Quotes','associatedContacts','Contacts',0,'varchar',0,NULL),(93,'Quotes','lastUpdated','Last Updated',0,'varchar',0,NULL),(94,'Quotes','updatedBy','Updated By',0,'varchar',0,NULL),(95,'Quotes','status','Status',0,'varchar',0,NULL),(96,'Quotes','expirationDate','Expiration Date',0,'varchar',0,NULL),(97,'Quotes','products','Products',0,'varchar',0,NULL),(98,'Products','id','ID',0,'varchar',0,NULL),(99,'Products','name','Name',0,'varchar',0,NULL),(100,'Products','type','Type',0,'varchar',0,NULL),(101,'Products','price','Price',0,'currency',0,NULL),(102,'Products','inventory','Inventory',0,'varchar',0,NULL),(103,'Products','description','Description',0,'text',0,NULL),(104,'Products','assignedTo','Assigned To',0,'assignment',0,NULL),(105,'Products','createDate','Create Date',0,'date',0,NULL),(106,'Products','lastUpdated','Last Updated',0,'date',0,NULL),(107,'Products','updatedBy','Updated By',0,'varchar',0,NULL),(108,'Products','adjustment','Adjustment',0,'varchar',0,NULL),(109,'contacts','dealvalue','Deal Value',1,'currency',0,NULL),(110,'contacts','leadstatus','Lead Status',1,'dropdown',0,'5');") or addSqlError("Unable to create fields".mysql_error());

mysql_query("INSERT INTO  x2_dropdowns (name, options) VALUES 
	('Product Status','". json_encode(array("1"=>"Active", "0"=>"Inactive")) ."'),
	('Currency List','". json_encode(array("USD"=>"USD", "EUR"=>"EUR", "GBP"=>"GBP", "CAD"=>"CAD", "JPY"=>"JPY", "CNY"=>"CNY", "CHF"=>"CHF", "INR"=>"INR", "BRL"=>"BRL")) ."'),
        ('Lead Type','". json_encode(array("None"=>"None", "Web"=>"Web", "In Person"=>"In Person", "Phone"=>"Phone", "E-Mail"=>"E-Mail")) ."'),
        ('Lead Source','". json_encode(array("None"=>"None", "Google"=>"Google","Facebook"=>"Facebook","Walk In"=>"Walk In")) ."'),
        ('Lead Status', '{\"Unassigned\":\"Unassigned\",\"Assigned\":\"Assigned\",\"Accepted\":\"Accepted\",\"Working\":\"Working\",\"Dead\":\"Dead\",\"Rejected\":\"Rejected\"}'),
        ('Sales Stage', '{\"Working\":\"Working\",\"Won\":\"Won\",\"Lost\":\"Lost\"}')

;")
or addSqlError("Unable to create product fields.".mysql_error());

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
	'actions:contacts:docs:sales:accounts:workflow:groups',
	'Actions:Contacts:Docs:Sales:Accounts:Workflow:Groups',
	'1:1:1:1:1:1:1',
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
	<!--<img src="images/x2engine_big.png">-->
	Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2engine.com">X2Engine Inc.</a><br />
	<?php echo installer_t('All Rights Reserved.'); ?>
	<img src="http://x2planet.com/listen.php?<?php echo $stats; ?>" style="display:none">
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