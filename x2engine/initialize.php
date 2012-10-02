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
 ********************************************************************************/
include(dirname(__FILE__).'/protected/config/emailConfig.php');
$x2Version = $version;

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
	if(file_exists('installConfig.php')){
		require('installConfig.php');
    }else
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
	
	$lang = $_POST['language'];
	$timezone = $_POST['timezone'];
	
	$adminEmail = $_POST['adminEmail'];
	$adminPassword = $_POST['adminPass'];
	$adminPassword2 = $_POST['adminPass2'];
	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$dummy_data = (isset($_POST['dummy_data']) && $_POST['dummy_data']==1)? 1 : 0;
	$receiveUpdates = (isset($_POST['receiveUpdates']) && $_POST['receiveUpdates']==1)? 1 : 0;
	$userData .= "&unique_id={$_POST['unique_id']}";
	$userData .= "&dbHost=$host&dbName=$db&dbUser=$user&app=$app&currency=".$_POST['currency']."&currency2=$currency2&language=$lang&adminEmail=$adminEmail&dummy_data=$dummy_data&receiveUpdates=$receiveUpdates&timezone=".urlencode($timezone);
	$webLeadUrl=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	$unique_id = isset($_POST['unique_id']) ? $_POST['unique_id'] : 'none';
}

$editions = array('pro'); // Add editions as necessary
if (!isset($_POST['edition'])) {
// Data not available from install form
	$edition = 'opensource';
	foreach ($editions as $ed) 
		if (file_exists("initialize_$ed.php"))
			$edition = $ed;
} else {
	$edition = $_POST['edition'];
}

$dummy_data = isset($dummy_data) ? $dummy_data : (isset($dummyData)?$dummyData:0);


$apiKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',16)),0,16);
$webLeadUrl=substr($webLeadUrl,0,-15);
$contents=file_get_contents('webLeadConfig.php');
$contents=preg_replace('/\$url=\'\';/',"\$url='$webLeadUrl'",$contents);
$contents=preg_replace('/\$user=\'\';/',"\$user='api'",$contents);
$contents=preg_replace('/\$password=\'\';/',"\$password='$apiKey'",$contents);
file_put_contents('webLeadConfig.php',$contents);


if(empty($lang))
	$lang='en';
	
if(empty($timezone))
	$timezone='UTC';

date_default_timezone_set($timezone);
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
	addError('adminEmail--Please enter a valid email address.');
	
if($adminPassword == '')
	addError('adminPass--Admin password cannot be blank.');

if(isset($adminPassword2) && $adminPassword != $adminPassword2)
	addError('adminPass2--Admin passwords did not match.');
 

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
\$updaterVersion='2.0';
?>";
fwrite($handle,$write);
fclose($handle);

outputErrors();

function outputErrors() {
	global $errors;
	global $userData;
	
	foreach($errors as &$error)
		$error = urlencode($error);		// url encode errors
	
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


//mysql_query("SOURCE /x2engine/install.sql; ") or die(mysql_error();

mysql_query('DROP TABLE IF EXISTS
	x2_dashboard_settings,
	x2_widgets,
    x2_role_to_workflow,
    x2_list_items,
    x2_list_criteria,
	x2_lists,
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
	x2_phone_numbers,
	x2_relationships,
	x2_notifications,
	x2_quotes_products,
	x2_criteria,
	x2_lead_routing,
	x2_sessions,
    x2_workflow_stages,
	x2_workflows,
	x2_fields,
   	x2_urls,
	x2_form_layouts,
    x2_role_to_user,
    x2_role_to_permission,
	x2_roles,
	x2_role_exceptions,
	x2_dropdowns,
	x2_groups,
	x2_group_to_user,
	x2_users,
	x2_contacts,
	x2_subscribe_contacts,
	x2_actions,
	x2_opportunities,
	x2_quotes,
	x2_products,
	x2_projects,
	x2_marketing,
	x2_campaigns,
    x2_campaigns_attachments,
	x2_calendars,
	x2_modules,
	x2_calendar_permissions,
	x2_temp_files,
	x2_timezones,
	x2_timezone_points,
	x2_web_forms
') or addSqlError('Unable to delete exsting tables.'.mysql_error());

// visibility check MySQL procedure
// example: "... select * from x2_contacts where x2_checkViewPermission(visibility,assignedTo,'jvaleria') > 0 ..."
// DROP function IF EXISTS `x2_func_strSplit`;
// CREATE FUNCTION `x2_func_strSplit`(x varchar(255), delim varchar(12), pos int) RETURNS varchar(255)
// begin
  // return replace(substring(substring_index(x, delim, pos), length(substring_index(x, delim, pos - 1)) + 1), delim, '');
// end;
/*
mysql_query('DROP FUNCTION IF EXISTS `x2_checkViewPermission`;') or addSqlError('Unable to drop function x2_checkViewPermission.'.mysql_error());
mysql_query('CREATE FUNCTION `x2_checkViewPermission` (`mode` INT,`assignedTo` VARCHAR(20),`user` VARCHAR(20)) RETURNS TINYINT DETERMINISTIC 
BEGIN
	DECLARE retv INT DEFAULT 0;

	-- record is public
	IF mode = 1 THEN
		RETURN 1;
	END IF;

	-- admin override
	IF STRCMP(user, "admin") = 0 THEN
		RETURN 1;
	END IF;

	IF CAST(assignedTo AS UNSIGNED) > 0 THEN	-- assigned is numeric (a group)
	
		IF mode = 0 THEN -- private, user must be in group
			SELECT COUNT(*) INTO retv FROM x2_group_to_user WHERE groupId = CAST(assignedTo AS UNSIGNED) AND username = user; 
			RETURN retv;
		ELSE
			RETURN 0;	-- mode should never be 2 for a group...if it is, its stupid
		END IF;

	ELSE	-- assigned is text (a user)
		IF mode = 0 THEN
			IF STRCMP(assignedTo, user) = 0 THEN	-- private, must be assigned to user
				RETURN 1;
			END IF;
		ELSE
			SELECT COUNT(*) INTO retv FROM x2_group_to_user a, x2_group_to_user b WHERE a.username = assignedTo AND b.username = user AND b.groupId = a.groupId;
				RETURN retv;
		END IF;
	END IF;
	
	RETURN 0;	-- default is false
END;') or addSqlError('Unable to create function x2_checkViewPermission.'.mysql_error());

mysql_query('DROP FUNCTION IF EXISTS `x2_checkOwnership`;') or addSqlError('Unable to drop function x2_checkOwnership.'.mysql_error());
mysql_query('CREATE FUNCTION `x2_checkOwnership` (assignedTo VARCHAR(20),user VARCHAR(20)) RETURNS TINYINT DETERMINISTIC 
BEGIN
	DECLARE retv INT DEFAULT 0;
	
	IF assignedTo=user THEN
		RETURN 1;
	END IF;

	IF CAST(assignedTo AS UNSIGNED) > 0 THEN	-- assigned is numeric (a group)
		SELECT COUNT(*) INTO retv FROM x2_group_to_user WHERE groupId = CAST(assignedTo AS UNSIGNED) AND username = user;
		RETURN retv;
	END IF;
	RETURN 0;	-- default is false
END;') or addSqlError('Unable to create function x2_checkOwnership.'.mysql_error());*/
mysql_query('drop table if exists `x2_auth_assignment`,
                                    `x2_auth_item_child`,
                                    `x2_auth_item`');

mysql_query('create table `x2_auth_item`
(
   `name`                 varchar(64) not null,
   `type`                 integer not null,
   `description`          text,
   `bizrule`              text,
   `data`                 text,
   primary key (`name`)
) engine InnoDB');

mysql_query('create table `x2_auth_item_child`
(
   `parent`               varchar(64) not null,
   `child`                varchar(64) not null,
   primary key (`parent`,`child`),
   foreign key (`parent`) references `x2_auth_item` (`name`) on delete cascade on update cascade,
   foreign key (`child`) references `x2_auth_item` (`name`) on delete cascade on update cascade
) engine InnoDB');

mysql_query('create table `x2_auth_assignment`
(
   `itemname`             varchar(64) not null,
   `userid`               varchar(64) not null,
   `bizrule`              text,
   `data`                 text,
   primary key (`itemname`,`userid`),
   foreign key (`itemname`) references `x2_auth_item` (`name`) on delete cascade on update cascade
) engine InnoDB') or addSqlError('Unable to create auth tables '.mysql_error());
mysql_query('CREATE TABLE x2_dashboard_settings(
	userID					INT,
	numCOLS					INT				DEFAULT 2,
	hideINTRO				INT				DEFAULT 0,
	unique(userID)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_dashboard_settings. '.mysql_error());
mysql_query('CREATE TABLE x2_widgets(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(255),
	showPROFILE				INT				DEFAULT 1,
	adminALLOWS				INT				DEFAULT 1,
	showDASH				INT	 			DEFAULT 1,
	userID					INT,
	posPROF					INT,
	posDASH					INT,
	widgetSettings			TEXT,
	dispNAME				VARCHAR(255),
	needUSER				INT				DEFAULT 0,
	userALLOWS				INT				DEFAULT 1,
	UNIQUE(name),
	INDEX(name)	
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_widgets. '.mysql_error());

mysql_query('CREATE TABLE x2_users(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	firstName				VARCHAR(20)		NOT NULL,
	lastName				VARCHAR(40)		NOT NULL,
	username				VARCHAR(20)		NOT NULL,
	password				VARCHAR(100)	NOT NULL,
	title					VARCHAR(20),
	department				VARCHAR(40),
	officePhone				VARCHAR(40),
	cellPhone				VARCHAR(40),
	homePhone				VARCHAR(40),
	address					VARCHAR(100),
	backgroundInfo			TEXT,
	emailAddress			VARCHAR(100)	NOT NULL,
	status					TINYINT			NOT NULL,
	lastUpdated				VARCHAR(30),
	updatedBy				VARCHAR(20),
	recentItems				VARCHAR(100),
	topContacts				VARCHAR(100),
	lastLogin				INT				DEFAULT 0,
	login					INT				DEFAULT 0,
	showCalendars			TEXT,
	calendarViewPermission	TEXT,
	calendarEditPermission	TEXT,
	calendarFilter			TEXT,
	setCalendarPermissions	TINYINT,
	
	UNIQUE(username, emailAddress),
	INDEX (username)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_users.'.mysql_error());

mysql_query('CREATE TABLE x2_contacts(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(200),
	firstName				VARCHAR(80)		NOT NULL,
	lastName				VARCHAR(80)		NOT NULL,
	title					VARCHAR(40),
	company					VARCHAR(250),
	phone					VARCHAR(40),
	phone2					VARCHAR(40),
	email					VARCHAR(250),
	website					VARCHAR(250),
	address					VARCHAR(250),
	address2				VARCHAR(250),
	city					VARCHAR(40),
	state					VARCHAR(40),
	zipcode					VARCHAR(20),
	country					VARCHAR(40),
	visibility				INT NOT NULL,
	assignedTo				VARCHAR(20),
	backgroundInfo			TEXT,
	twitter					VARCHAR(20)		NULL,
	linkedin				VARCHAR(100)	NULL,
	skype					VARCHAR(32)		NULL,
	googleplus				VARCHAR(100)	NULL,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20),
	priority				VARCHAR(40),
	leadSource				VARCHAR(40),
	leadDate				BIGINT,
	rating					TINYINT,
	createDate				BIGINT,
	facebook				VARCHAR(100)	NULL,
	otherUrl				VARCHAR(100)	NULL,
	leadtype				VARCHAR(250),
	closedate				BIGINT,
	interest				VARCHAR(250),
	leadstatus				VARCHAR(250),
	dealvalue				FLOAT,
	leadscore				INT,
	dealstatus				VARCHAR(250),
	timezone				VARCHAR(250)	NULL,
	doNotCall				TINYINT			DEFAULT 0,
	doNotEmail				TINYINT			DEFAULT 0,
	dupeCheck				INT			DEFAULT 0,
	
	INDEX (email),
	INDEX (assignedTo)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_contacts.'.mysql_error());

mysql_query('CREATE TABLE x2_subscribe_contacts(
	contact_id				INT				UNSIGNED,
	user_id					INT				UNSIGNED
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_subscribe_contacts.'.mysql_error());

mysql_query('CREATE TABLE x2_actions(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	assignedTo				VARCHAR(20),
	calendarId				INT,
	actionDescription		text NOT NULL,
	visibility				INT				NOT NULL,
	associationId			INT				NOT NULL,
	associationType			VARCHAR(20),
	associationName			VARCHAR(100),
	dueDate					BIGINT,
	showTime				TINYINT			NOT NULL DEFAULT 0,
	priority				VARCHAR(10),
	type					VARCHAR(20),
	createDate				BIGINT,
	complete				VARCHAR(5)		DEFAULT "No",
	reminder				VARCHAR(5),
	completedBy				VARCHAR(20),
	completeDate			BIGINT,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20),
	workflowId				INT				UNSIGNED,
	stageNumber				INT				UNSIGNED,
	allDay					TINYINT,
	color					VARCHAR(20),
	syncGoogleCalendarEventId TEXT,
	
	INDEX (assignedTo),
	INDEX (type),
	INDEX (associationType,associationId)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_actions.'.mysql_error());

 mysql_query('CREATE TABLE x2_opportunities(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(40)		NOT NULL,
	accountName				VARCHAR(100),
	quoteAmount				FLOAT,
	salesStage				VARCHAR(20),
	expectedCloseDate		BIGINT,
	probability				INT,
	leadSource				VARCHAR(100),
	description				TEXT,
	assignedTo				TEXT,
	createDate				BIGINT,
	associatedContacts		TEXT,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_opportunities.'.mysql_error());

 mysql_query('CREATE TABLE x2_quotes(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(40)		NOT NULL,
	accountName				VARCHAR(250),
	salesStage				VARCHAR(20),
	expectedCloseDate		BIGINT,
	probability				INT,
	leadSource				VARCHAR(10),
	description				TEXT,
	assignedTo				TEXT,
	createDate				BIGINT,
	createdBy				VARCHAR(20),
	associatedContacts		TEXT,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20),
	expirationDate			BIGINT,
	status					VARCHAR(20),
	currency				VARCHAR(40),
	locked					TINYINT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_quotes.'.mysql_error());

mysql_query("ALTER TABLE x2_quotes AUTO_INCREMENT=301;
")or addSqlError('Unable to alter table x2_quotes.'.mysql_error());

 mysql_query('CREATE TABLE x2_products(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(100)	NOT NULL,
	type					VARCHAR(100),
	price					FLOAT,
	inventory				INT,
	description				TEXT,
	createDate				BIGINT,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20),
	status					VARCHAR(20),
	currency				VARCHAR(40),
	adjustment				FLOAT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_products.'.mysql_error());

// mysql_query('CREATE TABLE x2_projects(
	// id					INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	// name					VARCHAR(60)		NOT NULL,
	// status				VARCHAR(20),
	// type					VARCHAR(20), 
	// priority				VARCHAR(20),
	// assignedTo			TEXT,
	// endDate				BIGINT,
	// timeframe			VARCHAR(40),
	// createDate			BIGINT,
	// associatedContacts	TEXT,
	// description			TEXT,
	// lastUpdated			BIGINT,
	// updatedBy			VARCHAR(20)
// ) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_projects.'.mysql_error());

mysql_query('CREATE TABLE x2_campaigns(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT,
	masterId				INT				UNSIGNED NULL,
	name					VARCHAR(250)	NOT NULL,
	assignedTo				VARCHAR(20),
	listId					VARCHAR(100),
	active					TINYINT			DEFAULT 1,
	description				TEXT,
	type					VARCHAR(100)	DEFAULT NULL,
	cost					VARCHAR(100)	DEFAULT NULL,
	subject					VARCHAR(250),
	content					TEXT,
	createdBy				VARCHAR(20)		NOT NULL,
	complete				TINYINT 		DEFAULT 0,
	visibility				INT				NOT NULL,
	createDate				BIGINT	 		NOT NULL,
	launchDate				BIGINT	 		NOT NULL,
	lastUpdated				BIGINT	 		NOT NULL,
	updatedBy				VARCHAR(20),
	
	PRIMARY KEY (id),
	FOREIGN KEY (masterId) REFERENCES x2_campaigns(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_campaigns.'.mysql_error());

mysql_query('CREATE TABLE x2_campaigns_attachments(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT,
	campaign				INT				UNSIGNED,
	media					INT				UNSIGNED,

	PRIMARY KEY (id)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_campaigns_attachments.'.mysql_error());

mysql_query('CREATE TABLE x2_calendars (
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(100)	NOT NULL,
	viewPermission			TEXT,
	editPermission			TEXT,
	googleCalendar			TINYINT,
	googleFeed				VARCHAR(255),
	createDate				BIGINT,
	createdBy				VARCHAR(40),
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(40),
	googleCalendarId		VARCHAR(255),
	googleAccessToken		VARCHAR(512),
	googleRefreshToken		VARCHAR(255)
) COLLATE utf8_general_ci') or addSqlError('Unable to create table x2_calendars.'.mysql_error());

mysql_query('CREATE TABLE x2_calendar_permissions (
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user_id					INT,
	other_user_id			INT,
	view					TINYINT,
	edit					TINYINT
) COLLATE utf8_general_ci') or addSqlError('Unable to create table x2_calendars.'.mysql_error());

mysql_query('CREATE TABLE x2_lists (
	id						INT UNSIGNED	NOT NULL AUTO_INCREMENT PRIMARY KEY,
	assignedTo				VARCHAR(20),
	name					VARCHAR(100)	NOT NULL,
	description				VARCHAR(250)	NULL,
	type					VARCHAR(20)		NULL,
	logicType				VARCHAR(20)		DEFAULT "AND",
	modelName				VARCHAR(100)	NOT NULL,
	visibility				INT NOT NULL	DEFAULT 1,
	count					INT UNSIGNED	NOT NULL DEFAULT 0,
	createDate				BIGINT			NOT NULL,
	lastUpdated				BIGINT			NOT NULL
) COLLATE utf8_general_ci') or addSqlError('Unable to create table x2_lists.'.mysql_error());

mysql_query('CREATE TABLE x2_list_items (
	id						INT UNSIGNED	NOT NULL AUTO_INCREMENT PRIMARY KEY,
	emailAddress			VARCHAR(255)	NULL,
	contactId				INT				UNSIGNED,
	listId					INT				UNSIGNED NOT NULL,
	uniqueId				VARCHAR(32)		NULL,
	sent					INT				UNSIGNED NOT NULL DEFAULT 0,
	opened					INT				UNSIGNED NOT NULL DEFAULT 0,
	clicked					INT				UNSIGNED NOT NULL DEFAULT 0,
	unsubscribed			INT				UNSIGNED NOT NULL DEFAULT 0,
	
	INDEX (listId),
	FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE utf8_general_ci') or addSqlError('Unable to create table x2_listItems.'.mysql_error());


mysql_query('CREATE TABLE x2_list_criteria (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	listId					INT				UNSIGNED NOT NULL,
	type					VARCHAR(20)		NULL,
	attribute				VARCHAR(40)		NULL,
	comparison				VARCHAR(10)		NULL,
	value					VARCHAR(100)	NOT NULL,
	
	INDEX (listId),
	FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE utf8_general_ci') or addSqlError('Unable to create table x2_listCriteria.'.mysql_error());

// mysql_query('CREATE TABLE x2_cases(
	// id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	// name					VARCHAR(60)		NOT NULL,
	// status					VARCHAR(20)		NOT NULL,
	// type					VARCHAR(20), 
	// priority				VARCHAR(20),
	// assignedTo				TEXT,
	// endDate					BIGINT,
	// timeframe				VARCHAR(40),
	// createDate				BIGINT,
	// associatedContacts		TEXT,
	// description				TEXT,
	// resolution				TEXT,
	// lastUpdated				BIGINT,
	// updatedBy				VARCHAR(20)
// ) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_cases.'.mysql_error());

 mysql_query('CREATE TABLE x2_profile(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	fullName				VARCHAR(60)		NOT NULL,
	username				VARCHAR(20)		NOT NULL,
	officePhone				VARCHAR(40),
	cellPhone				VARCHAR(40),
	emailAddress			VARCHAR(255)	NOT NULL,
	notes					TEXT,
	status					TINYINT			NOT NULL,
	tagLine					VARCHAR(255),
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20),
	avatar					TEXT,
	allowPost				TINYINT			DEFAULT 1,
	language				VARCHAR(40)		DEFAULT "'.$lang.'",
	timeZone				VARCHAR(100)	DEFAULT "'.$timezone.'",
	resultsPerPage			INT DEFAULT		20,
	widgets					VARCHAR(255),
	widgetOrder				TEXT,
	widgetSettings			TEXT,
	backgroundColor			VARCHAR(6)		NULL,
	menuBgColor				VARCHAR(6)		NULL,
	menuTextColor			VARCHAR(6)		NULL,
	backgroundImg			VARCHAR(100)	NULL DEFAULT "",
	pageOpacity				INT				NULL,
	startPage				VARCHAR(30)		NULL,
	showSocialMedia			TINYINT			NOT NULL DEFAULT 0,
	showDetailView			TINYINT			NOT NULL DEFAULT 1,
	showWorkflow			TINYINT			NOT NULL DEFAULT 1,
	gridviewSettings		TEXT,
	formSettings			TEXT,
	emailUseSignature		VARCHAR(5)		DEFAULT "user",
	emailSignature			VARCHAR(512),
	enableFullWidth			TINYINT			DEFAULT 1,
	showActions				VARCHAR(20),
	syncGoogleCalendarId	TEXT,
	syncGoogleCalendarAccessToken TEXT,
	syncGoogleCalendarRefreshToken TEXT,
	googleId				VARCHAR(250),
	userCalendarsVisible	TINYINT			DEFAULT 1,
	groupCalendarsVisible	TINYINT			DEFAULT 1,
	tagsShowAllUsers		TINYINT,
	
	UNIQUE(username, emailAddress),
	INDEX (username)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_profile.'.mysql_error());

 mysql_query('CREATE TABLE x2_accounts(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(40)		NOT NULL,
	website					VARCHAR(40),
	type					VARCHAR(60), 
	annualRevenue			FLOAT,
	phone					VARCHAR(40),
	tickerSymbol			VARCHAR(10),
	employees				INT,
	assignedTo				TEXT,
	createDate				BIGINT,
	associatedContacts		TEXT,
	description				TEXT,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_accounts.'.mysql_error());


 mysql_query('CREATE TABLE x2_social(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(40)		NOT NULL,
	data					TEXT,
	user					VARCHAR(20),
	associationId			INT,
	private					TINYINT			DEFAULT 0,
	timestamp				INT,
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_social.'.mysql_error());

mysql_query('CREATE TABLE x2_docs(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title					VARCHAR(100)	NOT NULL,
	type					VARCHAR(10)		NOT NULL DEFAULT "",
	text					LONGTEXT		NOT NULL,
	createdBy				VARCHAR(60)		NOT NULL,
	createDate				BIGINT,
	editPermissions			VARCHAR(250), 
	updatedBy				VARCHAR(40),
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_docs.'.mysql_error());

mysql_query('CREATE TABLE x2_media(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	associationType			VARCHAR(40)		NOT NULL,
	associationId			INT,
	uploadedBy				VARCHAR(40),
	fileName				VARCHAR(100),
	createDate				BIGINT,
	lastUpdated				BIGINT,
	private					TINYINT,
	description				TEXT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_media.'.mysql_error());
 mysql_query('CREATE TABLE x2_urls(
	 id					INT					NOT NULL AUTO_INCREMENT PRIMARY KEY,
	 title					VARCHAR(20)				NOT NULL,
	 url					VARCHAR(256),
	 userid					INT,
	 timestamp				INT
 ) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_urls.'.mysql_error());
mysql_query('CREATE TABLE x2_admin(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	timeout					INT,
	webLeadEmail			VARCHAR(255),
	currency				VARCHAR(3)		NULL,
	chatPollTime			INT				DEFAULT 2000,
	ignoreUpdates			TINYINT			DEFAULT 0,
	rrId					INT				DEFAULT 0, 
	leadDistribution		VARCHAR(255),
	onlineOnly				TINYINT,
	emailFromName			VARCHAR(255)	NOT NULL DEFAULT "X2CRM",
	emailFromAddr			VARCHAR(255)	NOT NULL DEFAULT "'.$bulkEmail.'",
	emailBatchSize			INT				NOT NULL DEFAULT 200,
	emailInterval			INT				NOT NULL DEFAULT 60,
	emailUseSignature		VARCHAR(5)		DEFAULT "user",
	emailSignature			VARCHAR(512),
	emailType				VARCHAR(20)		DEFAULT "mail",
	emailHost				VARCHAR(255),
	emailPort				INT				DEFAULT 25,
	emailUseAuth			VARCHAR(5)		DEFAULT "user",
	emailUser				VARCHAR(255),
	emailPass				VARCHAR(255),
	emailSecurity			VARCHAR(10),
	installDate				BIGINT			NOT NULL,
	updateDate				BIGINT			NOT NULL,
	updateInterval			INT				NOT NULL DEFAULT 0,
	quoteStrictLock			TINYINT,
	googleIntegration		TINYINT,
	googleClientId			VARCHAR(255),
	googleClientSecret		VARCHAR(255),
	googleAPIKey			VARCHAR(255),
	inviteKey				VARCHAR(255),
	workflowBackdateWindow			INT			NOT NULL DEFAULT -1,
	workflowBackdateRange			INT			NOT NULL DEFAULT -1,
	workflowBackdateReassignment	TINYINT		NOT NULL DEFAULT 1,
	unique_id		VARCHAR(32) NOT NULL DEFAULT "none",
	edition			VARCHAR(10) NOT NULL DEFAULT "opensource"
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_admin.'.mysql_error());

mysql_query('CREATE TABLE x2_changelog(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
	changedBy				VARCHAR(50)		NOT NULL,
	changed					TEXT			NOT NULL,
	timestamp				INT				NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_changelog.'.mysql_error());

mysql_query('CREATE TABLE x2_tags( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
	taggedBy				VARCHAR(50)		NOT NULL,
	tag						VARCHAR(250)	NOT NULL,
	itemName				VARCHAR(250),
	timestamp				INT				NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_tags.'.mysql_error());

mysql_query('CREATE TABLE x2_phone_numbers(
	modelId					INT				UNSIGNED NOT NULL,
	modelType				VARCHAR(100)	NOT NULL,
	number					VARCHAR(40)		NOT NULL,
    fieldName               VARCHAR(255),
	
	INDEX (modelType,modelId),
	INDEX (number)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_tags.'.mysql_error());

mysql_query('CREATE TABLE x2_relationships( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	firstType				VARCHAR(100),
	firstId					INT,
	secondType				VARCHAR(100),
	secondId				INT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_relationshps.'.mysql_error());

mysql_query('CREATE TABLE x2_quotes_products( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quoteId					INT,
	productId				INT,
	quantity				INT,
	name					VARCHAR(100)	NOT NULL,
	type					VARCHAR(100),
	price					FLOAT,
	inventory				INT,
	description				TEXT,
	assignedTo				TEXT,
	createDate				BIGINT,
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(20),
	active					TINYINT,
	currency				VARCHAR(40),
	adjustment				FLOAT,
	adjustmentType			VARCHAR(20)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_relationshps.'.mysql_error());

mysql_query('CREATE TABLE x2_notifications( 
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(20),
	comparison				VARCHAR(20),
	value					VARCHAR(250),
	modelType				VARCHAR(250),
	modelId					INT				UNSIGNED,
	fieldName				VARCHAR(250),
	user					VARCHAR(20),
	createdBy				VARCHAR(20),
	viewed					TINYINT			DEFAULT 0,
	createDate				BIGINT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_notifications.'.mysql_error());

mysql_query('CREATE TABLE x2_criteria(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	modelType				VARCHAR(100),
	modelField				VARCHAR(250),
	modelValue				TEXT,
	comparisonOperator		VARCHAR(10),
	users					TEXT,
	type					VARCHAR(250)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_criteria.'.mysql_error());

mysql_query('CREATE TABLE x2_lead_routing( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	criteria				TEXT,
	users					TEXT,
	priority				INT, 
	rrId					INT				DEFAULT 0,
	groupType				INT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_lead_routing.'.mysql_error());

mysql_query('CREATE TABLE x2_sessions(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user					VARCHAR(250),
	lastUpdated				BIGINT,
	IP						VARCHAR(40)		NOT NULL,
	status					TINYINT			NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_sessions.'.mysql_error());

mysql_query('CREATE TABLE x2_workflows(
	id						INT					NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_workflows.'.mysql_error());

mysql_query('CREATE TABLE x2_workflow_stages( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	workflowId				INT				NOT NULL,
	stageNumber				INT,
	name					VARCHAR(40),
	description				TEXT,
	conversionRate			DECIMAL(10,2),
	value					DECIMAL(10,2),
	requirePrevious			INT				DEFAULT 0,
	requireComment			TINYINT			DEFAULT 0,
	
	FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_workflow_stages.'.mysql_error());

mysql_query('CREATE TABLE x2_fields (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	modelName				VARCHAR(100),
	fieldName				VARCHAR(100),
	attributeLabel			VARCHAR(250),
	modified				INT				DEFAULT 0,
	custom					INT				DEFAULT 1,
	type					VARCHAR(20)		DEFAULT "varchar",
	required				TINYINT			DEFAULT 0,
	readOnly				TINYINT			DEFAULT 0,
	linkType				VARCHAR(250),
	searchable				TINYINT			DEFAULT 0,
	relevance				VARCHAR(250),
	isVirtual				TINYINT			DEFAULT 0,
	INDEX (modelName),
	UNIQUE (modelName, fieldName)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_fields.'.mysql_error());

mysql_query('CREATE TABLE x2_form_layouts (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	model					VARCHAR(250)	NOT NULL,
	version					VARCHAR(250)	NOT NULL,
	layout					TEXT,
	defaultView				TINYINT			NOT NULL DEFAULT 0,
	defaultForm				TINYINT			NOT NULL DEFAULT 0,
	createDate				BIGINT,
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_form_versions.'.mysql_error());

mysql_query('CREATE TABLE x2_dropdowns (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	options					TEXT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_downs.'.mysql_error());

mysql_query('CREATE TABLE x2_roles (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	users					TEXT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_roles.'.mysql_error());

mysql_query('CREATE TABLE x2_role_to_user (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	userId					INT,
	type					VARCHAR(250)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_role_to_user.'.mysql_error());

mysql_query('CREATE TABLE x2_role_to_permission (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	fieldId					INT,
	permission				INT
) COLLATE = utf8_general_ci')or addSqlError('Unable to create table x2_role_to_permission.'.mysql_error());

mysql_query('CREATE TABLE x2_role_exceptions (
	id						INT				NOT NULL AUTO_INCREMENT primary key,
	workflowId				INT,
	stageId					INT,
	roleId					INT,
	replacementId int
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_role_to_exceptions.'.mysql_error());

mysql_query('CREATE TABLE x2_role_to_workflow( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	stageId					INT,
	workflowId				INT,
	
	FOREIGN KEY (roleId) REFERENCES x2_roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (stageId) REFERENCES x2_workflow_stages(id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_workflow_stages.'.mysql_error());

mysql_query('CREATE TABLE x2_groups (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250)
)COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_roles.'.mysql_error());

mysql_query('CREATE TABLE x2_group_to_user (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupId					INT,
	userId					INT,
	username				VARCHAR(250)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_roles.'.mysql_error());

mysql_query('CREATE TABLE x2_temp_files (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	folder					VARCHAR(10),
	name					TEXT,
	createDate				INT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_temp_files.'.mysql_error());

mysql_query('CREATE TABLE IF NOT EXISTS x2_timezone_points (
	lat						FLOAT			NOT NULL,
	lon						FLOAT			NOT NULL,
	tz_id					INT				NOT NULL,
	INDEX (lat),
	INDEX (lon)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_timezone_points.'.mysql_error());

mysql_query('CREATE TABLE IF NOT EXISTS x2_timezones (
	id						INT(11)			NOT NULL,
	name					VARCHAR(40)		NOT NULL,
	PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_timezones.'.mysql_error());

mysql_query('CREATE TABLE x2_web_forms(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT,
	name					VARCHAR(100)	NOT NULL,
	type					VARCHAR(100)	NOT NULL,
	description				VARCHAR(255)	DEFAULT NULL,
	modelName				VARCHAR(100)	DEFAULT NULL,
	fields					TEXT,
	params					TEXT,
	css						TEXT,
	visibility				INT				NOT NULL,
	assignedTo				VARCHAR(20)		NOT NULL,
	createdBy				VARCHAR(20)		NOT NULL,
	updatedBy				VARCHAR(20)		NOT NULL,
	createDate				BIGINT	 		NOT NULL,
	lastUpdated				BIGINT	 		NOT NULL,
	
	PRIMARY KEY (id)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_web_forms.'.mysql_error());

mysql_query("CREATE OR REPLACE VIEW `x2_bi_leads` AS
	(
	SELECT
	`a`.`id` AS `id`,
	`a`.`dealvalue` AS `dealValue`,
	`a`.`leadDate` AS `leadDate`,
	`a`.`createDate` AS `createDate`,
	`a`.`leadstatus` AS `leadStatus`,
	`a`.`leadSource` AS `leadSource`,
	`a`.`leadtype` AS `leadType`,
	`a`.`assignedTo` AS `assignedTo`,
	concat(`b`.`firstName`, ' ',`b`.`lastName`) AS `assignedToName`,
	`a`.`interest` AS `interest`,
	`a`.`closedate` AS `closeDate`,
	`a`.`rating` AS `confidence`,
	`a`.`visibility` AS `visibility`,
	`a`.`leadscore` AS `leadScore`,
	`a`.`dealstatus` AS `dealStatus`
	FROM (`x2_contacts` `a` JOIN `x2_users` `b`)
	WHERE ((`a`.`assignedTo` <= 0) AND (`b`.`userName` = `a`.`assignedTo`))
	)
	UNION
	(
	SELECT
	`a`.`id` AS `id`,
	`a`.`dealvalue` AS `dealValue`,
	`a`.`leadDate` AS `leadDate`,
	`a`.`createDate` AS `createDate`,
	`a`.`leadstatus` AS `leadStatus`,
	`a`.`leadSource` AS `leadSource`,
	`a`.`leadtype` AS `leadType`,
	`a`.`assignedTo` AS `assignedTo`,
	`b`.`name` AS `assignedToName`,
	`a`.`interest` AS `interest`,
	`a`.`closedate` AS `closeDate`,
	`a`.`rating` AS `confidence`,
	`a`.`visibility` AS `visibility`,
	`a`.`leadscore` AS `leadScore`,
	`a`.`dealstatus` AS `dealStatus`
	FROM (`x2_contacts` `a` JOIN `x2_groups` `b`)
	WHERE ((`a`.`assignedTo` > 0) AND (`b`.`id` = `a`.`assignedTo`))
	)
	ORDER BY leadDate ASC;") or addSqlError("Unable to initialize dashboard ".mysql_error());

mysql_query('CREATE TABLE x2_modules (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(100),
	title					VARCHAR(250),
	visible					INT,
	menuPosition			INT,
	searchable				INT,
	toggleable				INT,
	adminOnly				INT,
	editable				INT,
	custom					INT
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_modules.'.mysql_error());

mysql_query('INSERT INTO x2_modules 
(name,				title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable) VALUES 
("contacts",		"Contacts",			1,			0,				1,			1,			0,			0,		0),
("accounts",		"Accounts",			1,			1,				1,			1,			0,			0,		0),
("marketing",		"Marketing",		1,			2,				0,			1,			0,			0,		0),
("opportunities",	"Opportunities",	1,			3,				1,			1,			0,			0,		0),
("workflow",		"Workflow",			1,			4,				0,			0,			0,			0,		0),
("docs",			"Docs",				1,			5,				0,			0,			0,			0,		0),
("calendar",		"Calendar",			1,			6,				0,			0,			0,			0,		0),
("actions",			"Actions",			1,			7,				1,			0,			0,			0,		0),
("charts",			"Charts",			1,			9,				0,			0,			0,			0,		0),
("media",			"Media",			1,			10,				0,			0,			0,			0,		0),
("products",		"Products",			1,			11,				1,			1,			0,			0,		0),
("quotes",			"Quotes",			1,			12,				1,			1,			0,			0,		0),
("groups",			"Groups",			1,			13,				0,			0,			0,			0,		0),
("users",			"Users",			1,			14,				0,			0,			1,			0,		0)
'
// ("dashboard",   "Widget Dashboard",	1,       13,             0,          1,          0,          0,      0)'
) or addSqlError("Unable to initialize modules ".mysql_error());

mysql_query("
INSERT INTO `x2_auth_item` VALUES ('AccountsAddUser',0,'',NULL,'N;'),
('AccountsAdmin',0,'',NULL,'N;'),
('AccountsAdminAccess',1,'',NULL,'N;'),
('AccountsBasicAccess',1,'',NULL,'N;'),
('AccountsCreate',0,'',NULL,'N;'),
('AccountsDelete',0,'',NULL,'N;'),
('AccountsDeleteNote',0,'',NULL,'N;'),
('AccountsDeletePrivate',1,'Delete their own records.','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('AccountsFullAccess',1,'',NULL,'N;'),
('AccountsGetItems',0,'',NULL,'N;'),
('AccountsIndex',0,'',NULL,'N;'),
('AccountsMinimumRequirements',1,'',NULL,'N;'),
('AccountsPrivateFullAccess',1,'',NULL,'N;'),
('AccountsPrivateReadOnlyAccess',1,'',NULL,'N;'),
('AccountsPrivateUpdateAccess',1,'',NULL,'N;'),
('AccountsReadOnlyAccess',1,'',NULL,'N;'),
('AccountsRemoveUser',0,'',NULL,'N;'),
('AccountsSearch',0,'',NULL,'N;'),
('AccountsShareAccount',0,'',NULL,'N;'),
('AccountsUpdate',0,'',NULL,'N;'),
('AccountsUpdateAccess',1,'',NULL,'N;'),
('AccountsUpdatePrivate',1,'Update their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('AccountsView',0,'',NULL,'N;'),
('AccountsViewPrivate',1,'View their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ActionsAdmin',0,'',NULL,'N;'),
('ActionsAdminAccess',1,'The user has administrative access to the Actions module.',NULL,'N;'),
('ActionsBasicAccess',1,'The user can create and view records.',NULL,'N;'),
('ActionsComplete',0,'',NULL,'N;'),
('ActionsCompleteSelected',0,'',NULL,'N;'),
('ActionsCreate',0,'',NULL,'N;'),
('ActionsDelete',0,'',NULL,'N;'),
('ActionsDeleteNote',0,'',NULL,'N;'),
('ActionsDeletePrivate',1,'Delete assigned records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ActionsFullAccess',1,'The user is able to create, read, update, and delete actions but lacks adminstrative functions.',NULL,'N;'),
('ActionsGetTerms',0,'',NULL,'N;'),
('ActionsIndex',0,'',NULL,'N;'),
('ActionsInvalid',0,'',NULL,'N;'),
('ActionsMinimumRequirements',1,'Minimum requirements to access the actions module.',NULL,'N;'),
('ActionsParseType',0,'',NULL,'N;'),
('ActionsPrivateFullAccess',1,'The user is able to create and read all actions, and able to update and delete actions assigned to them.',NULL,'N;'),
('ActionsPrivateReadOnlyAccess',1,'The user can only view their own actions.',NULL,'N;'),
('ActionsPrivateUpdateAccess',1,'The user is able to update their own actions.',NULL,'N;'),
('ActionsPublisherCreate',0,'',NULL,'N;'),
('ActionsQuickUpdate',0,'',NULL,'N;'),
('ActionsReadOnlyAccess',1,'The user can only view records.',NULL,'N;'),
('ActionsSaveShowActions',0,'',NULL,'N;'),
('ActionsSearch',0,'',NULL,'N;'),
('ActionsSendReminder',0,'',NULL,'N;'),
('ActionsShareAction',0,'',NULL,'N;'),
('ActionsTomorrow',0,'',NULL,'N;'),
('ActionsUncomplete',0,'',NULL,'N;'),
('ActionsUncompleteSelected',0,'',NULL,'N;'),
('ActionsUpdate',0,'',NULL,'N;'),
('ActionsUpdateAccess',1,'The user is able to create, read, and update all actions.',NULL,'N;'),
('ActionsUpdatePrivate',1,'Update assigned records.','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ActionsUpdateSelected',0,'',NULL,'N;'),
('ActionsView',0,'',NULL,'N;'),
('ActionsViewAll',0,'',NULL,'N;'),
('ActionsViewGroup',0,'',NULL,'N;'),
('ActionsViewPrivate',1,'View assigned records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('admin',2,'Default admin user','return Yii::app()->user->name === \"admin\";','N;'),
('administrator',2,'Admin user','','N;'),
('AdminAddCriteria',0,'Access the page to create criteria which will trigger notifications.',NULL,'N;'),
('AdminAddField',0,'Create a new field.',NULL,'N;'),
('AdminAppSettings',0,'General settings for the application.',NULL,'N;'),
('AdminCreateModule',0,'Create a new module.',NULL,'N;'),
('AdminCreatePage',0,'Create a static page for the top menu bar.',NULL,'N;'),
('AdminCustomizeFields',0,'Edit fields.',NULL,'N;'),
('AdminDeleteDropdown',0,'Delete a dropdown.',NULL,'N;'),
('AdminDeleteField',0,'Delete a custom field.',NULL,'N;'),
('AdminDeleteModule',0,'Delete a module or static page.',NULL,'N;'),
('AdminDeleteRole',0,'',NULL,'N;'),
('AdminDropDownEditor',0,'Create a new dropdown.',NULL,'N;'),
('AdminEditDropdown',0,'Customize a dropdown.',NULL,'N;'),
('AdminEditor',0,'Form editor control.',NULL,'N;'),
('AdminEditRole',0,'',NULL,'N;'),
('AdminEditRoleAccess',0,'',NULL,'N;'),
('AdminEmailSetup',0,'Configure email settings for the application.',NULL,'N;'),
('AdminExport',0,'Global data export.',NULL,'N;'),
('AdminExportModule',0,'Export a module to a .zip file.',NULL,'N;'),
('AdminGoogleIntegration',0,'Permissions for integrating the application with Google.',NULL,'N;'),
('AdminImport',0,'Global data import.',NULL,'N;'),
('AdminImportModule',0,'Import a zip file of a module.',NULL,'N;'),
('AdminIndex',0,'Access the index page of the administrator tab.',NULL,'N;'),
('AdminManageDropDowns',0,'General dropdown management.',NULL,'N;'),
('AdminManageFields',0,'Manage created fields.',NULL,'N;'),
('AdminManageModules',0,'Manage top bar menu items.',NULL,'N;'),
('AdminManageRoles',0,'',NULL,'N;'),
('AdminRenameModules',0,'Rename a module in the top menu bar.',NULL,'N;'),
('AdminRoleEditor',0,'',NULL,'N;'),
('AdminRoleException',0,'',NULL,'N;'),
('AdminRoundRobinRules',0,'Edit custom round robin lead distribution rules.',NULL,'N;'),
('AdminSetLeadRouting',0,'Manage lead distribution methods.',NULL,'N;'),
('AdminToggleDefaultLogo',0,'Toggle the logo in the top left corner.',NULL,'N;'),
('AdminTranslationManager',0,'Translation manager for the application',NULL,'N;'),
('AdminUploadLogo',0,'Upload your own logo for the top left corner.',NULL,'N;'),
('AdminViewChangelog',0,'View a list of all changes made by users.',NULL,'N;'),
('authenticated',2,'Authenticated user','return !Yii::app()->user->isGuest;','N;'),
('AuthenticatedSiteFunctionsTask',1,'A set of permissions required to use the site while logged in.',NULL,'N;'),
('CalendarAdmin',0,'',NULL,'N;'),
('CalendarAdminAccess',1,'',NULL,'N;'),
('CalendarBasicAccess',1,'',NULL,'N;'),
('CalendarCompleteAction',0,'',NULL,'N;'),
('CalendarCreate',0,'',NULL,'N;'),
('CalendarDelete',0,'',NULL,'N;'),
('CalendarDeleteAction',0,'',NULL,'N;'),
('CalendarDeleteGoogleEvent',0,'',NULL,'N;'),
('CalendarDeleteNote',0,'',NULL,'N;'),
('CalendarEditAction',0,'',NULL,'N;'),
('CalendarEditGoogleEvent',0,'',NULL,'N;'),
('CalendarFullAccess',1,'',NULL,'N;'),
('CalendarIndex',0,'',NULL,'N;'),
('CalendarJsonFeed',0,'',NULL,'N;'),
('CalendarJsonFeedGoogle',0,'',NULL,'N;'),
('CalendarJsonFeedGroup',0,'',NULL,'N;'),
('CalendarJsonFeedShared',0,'',NULL,'N;'),
('CalendarMinimumRequirements',1,'',NULL,'N;'),
('CalendarMoveAction',0,'',NULL,'N;'),
('CalendarMoveGoogleEvent',0,'',NULL,'N;'),
('CalendarMyCalendarPermissions',0,'',NULL,'N;'),
('CalendarReadOnlyAccess',1,'',NULL,'N;'),
('CalendarResizeAction',0,'',NULL,'N;'),
('CalendarResizeGoogleEvent',0,'',NULL,'N;'),
('CalendarSaveCheckedCalendar',0,'',NULL,'N;'),
('CalendarSaveCheckedCalendarFilter',0,'',NULL,'N;'),
('CalendarSaveGoogleEvent',0,'',NULL,'N;'),
('CalendarSearch',0,'',NULL,'N;'),
('CalendarSyncActionsToGoogleCalendar',0,'',NULL,'N;'),
('CalendarTogglePortletVisible',0,'',NULL,'N;'),
('CalendarToggleUserCalendarsVisible',0,'',NULL,'N;'),
('CalendarUncompleteAction',0,'',NULL,'N;'),
('CalendarUpdate',0,'',NULL,'N;'),
('CalendarUpdateAccess',1,'',NULL,'N;'),
('CalendarUserCalendarPermissions',0,'',NULL,'N;'),
('CalendarView',0,'',NULL,'N;'),
('CalendarViewAction',0,'',NULL,'N;'),
('CalendarViewGoogleEvent',0,'',NULL,'N;'),
('ChartsAdmin',0,'',NULL,'N;'),
('ChartsAdminAccess',1,'',NULL,'N;'),
('ChartsDeleteNote',0,'',NULL,'N;'),
('ChartsFullAccess',1,'',NULL,'N;'),
('ChartsGetFieldData',0,'',NULL,'N;'),
('ChartsIndex',0,'',NULL,'N;'),
('ChartsLeadVolume',0,'',NULL,'N;'),
('ChartsMarketing',0,'',NULL,'N;'),
('ChartsMinimumRequirements',1,'',NULL,'N;'),
('ChartsPipeline',0,'',NULL,'N;'),
('ChartsSales',0,'',NULL,'N;'),
('ChartsSearch',0,'',NULL,'N;'),
('ChartsWorkflow',0,'',NULL,'N;'),
('ContactsAddToList',0,'',NULL,'N;'),
('ContactsAdmin',0,'',NULL,'N;'),
('ContactsAdminAccess',1,'The user has administrative access to the contacts module.',NULL,'N;'),
('ContactsBasicAccess',1,'The user can create and read.',NULL,'N;'),
('ContactsCreate',0,'',NULL,'N;'),
('ContactsCreateList',0,'',NULL,'N;'),
('ContactsCreateListFromSelection',0,'',NULL,'N;'),
('ContactsDelete',0,'',NULL,'N;'),
('ContactsDeleteList',0,'',NULL,'N;'),
('ContactsDeleteNote',0,'',NULL,'N;'),
('ContactsDeletePrivate',1,'This task allows a user to delete their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ContactsDiscardNew',0,'',NULL,'N;'),
('ContactsExport',0,'',NULL,'N;'),
('ContactsExportList',0,'',NULL,'N;'),
('ContactsFullAccess',1,'The user has full access to read, create, update, and delete features but lacks administrative permissions such as import/export.',NULL,'N;'),
('ContactsGetContacts',0,'',NULL,'N;'),
('ContactsGetItems',0,'',NULL,'N;'),
('ContactsGetLists',0,'',NULL,'N;'),
('ContactsGetTerms',0,'',NULL,'N;'),
('ContactsIgnoreDuplicates',0,'',NULL,'N;'),
('ContactsImportContacts',0,'',NULL,'N;'),
('ContactsImportExcel',0,'',NULL,'N;'),
('ContactsIndex',0,'',NULL,'N;'),
('ContactsList',0,'',NULL,'N;'),
('ContactsLists',0,'',NULL,'N;'),
('ContactsMinimumRequirements',1,'Permissions required by anyone able to access the contacts module.',NULL,'N;'),
('ContactsMyContacts',0,'',NULL,'N;'),
('ContactsNewContacts',0,'',NULL,'N;'),
('ContactsPrivateFullAccess',1,'The user has full access to read and create, but can only update or delete records that are assigned to them.',NULL,'N;'),
('ContactsPrivateReadOnlyAccess',1,'The user can only view their own records.',NULL,'N;'),
('ContactsPrivateUpdateAccess',1,'The user can create and read, but only update their own records.',NULL,'N;'),
('ContactsQtip',0,'',NULL,'N;'),
('ContactsQuickContact',0,'',NULL,'N;'),
('ContactsReadOnlyAccess',1,'The user can only view records.',NULL,'N;'),
('ContactsRemoveFromList',0,'',NULL,'N;'),
('ContactsSearch',0,'',NULL,'N;'),
('ContactsShareContact',0,'',NULL,'N;'),
('ContactsSubscribe',0,'',NULL,'N;'),
('ContactsUpdate',0,'',NULL,'N;'),
('ContactsUpdateAccess',1,'The user has access to create, read, and update.',NULL,'N;'),
('ContactsUpdateList',0,'',NULL,'N;'),
('ContactsUpdatePrivate',1,'This task allows a user to update their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ContactsView',0,'',NULL,'N;'),
('ContactsViewPrivate',1,'This task allows a user to view their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ContactsViewRelationships',0,'',NULL,'N;'),
('DocsAdmin',0,'',NULL,'N;'),
('DocsAdminAccess',1,'',NULL,'N;'),
('DocsAutosave',0,'',NULL,'N;'),
('DocsBasicAccess',1,'',NULL,'N;'),
('DocsChangePermissions',0,'',NULL,'N;'),
('DocsCreate',0,'',NULL,'N;'),
('DocsCreateEmail',0,'',NULL,'N;'),
('DocsDelete',0,'',NULL,'N;'),
('DocsDeleteNote',0,'',NULL,'N;'),
('DocsDeletePrivate',1,'Delete their own docs','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('DocsExportToHtml',0,'',NULL,'N;'),
('DocsFullAccess',1,'',NULL,'N;'),
('DocsGetItem',0,'',NULL,'N;'),
('DocsGetItems',0,'',NULL,'N;'),
('DocsIndex',0,'',NULL,'N;'),
('DocsMinimumRequirements',1,'',NULL,'N;'),
('DocsPrivateFullAccess',1,'',NULL,'N;'),
('DocsPrivateReadOnlyAccess',1,'',NULL,'N;'),
('DocsPrivateUpdateAccess',1,'',NULL,'N;'),
('DocsReadOnlyAccess',1,'',NULL,'N;'),
('DocsSearch',0,'',NULL,'N;'),
('DocsUpdate',0,'',NULL,'N;'),
('DocsUpdateAccess',1,'',NULL,'N;'),
('DocsUpdatePrivate',1,'Update their own docs','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('DocsView',0,'',NULL,'N;'),
('DocsViewPrivate',1,'View their own docs','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('DropDownsTask',1,'Dropdown editor control.',NULL,'N;'),
('FieldsTask',1,'Field editor control.',NULL,'N;'),
('GeneralAdminSettingsTask',1,'A suite of application wide settings configurable by the administrator.',NULL,'N;'),
('GroupsAdmin',0,'',NULL,'N;'),
('GroupsAdminAccess',1,'',NULL,'N;'),
('GroupsBasicAccess',1,'',NULL,'N;'),
('GroupsCreate',0,'',NULL,'N;'),
('GroupsDelete',0,'',NULL,'N;'),
('GroupsDeleteNote',0,'',NULL,'N;'),
('GroupsFullAccess',1,'',NULL,'N;'),
('GroupsGetGroups',0,'',NULL,'N;'),
('GroupsIndex',0,'',NULL,'N;'),
('GroupsMinimumRequirements',1,'',NULL,'N;'),
('GroupsReadOnlyAccess',1,'',NULL,'N;'),
('GroupsSearch',0,'',NULL,'N;'),
('GroupsUpdate',0,'',NULL,'N;'),
('GroupsUpdateAccess',1,'',NULL,'N;'),
('GroupsView',0,'',NULL,'N;'),
('guest',2,'Guest user','return Yii::app()->user->isGuest;','N;'),
('GuestSiteFunctionsTask',1,'A set of permissions required for guests to be able to log in and see the website.',NULL,'N;'),
('LeadRoutingTask',1,'A set of operations for configuring lead distribution.',NULL,'N;'),
('MarketingAdmin',0,'',NULL,'N;'),
('MarketingAdminAccess',1,'',NULL,'N;'),
('MarketingBasicAccess',1,'',NULL,'N;'),
('MarketingBasicPrivate',1,'Control their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('MarketingClick',0,'',NULL,'N;'),
('MarketingComplete',0,'',NULL,'N;'),
('MarketingCreate',0,'',NULL,'N;'),
('MarketingCreateFromTag',0,'',NULL,'N;'),
('MarketingDelete',0,'',NULL,'N;'),
('MarketingDeleteNote',0,'',NULL,'N;'),
('MarketingDeletePrivate',1,'Delete their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('MarketingFullAccess',1,'',NULL,'N;'),
('MarketingGetItems',0,'',NULL,'N;'),
('MarketingIndex',0,'',NULL,'N;'),
('MarketingLaunch',0,'',NULL,'N;'),
('MarketingMail',0,'',NULL,'N;'),
('MarketingMinimumRequirements',1,'',NULL,'N;'),
('MarketingPrivateBasicAccess',1,'',NULL,'N;'),
('MarketingPrivateFullAccess',1,'',NULL,'N;'),
('MarketingPrivateReadOnlyAccess',1,'',NULL,'N;'),
('MarketingPrivateUpdateAccess',1,'',NULL,'N;'),
('MarketingReadOnlyAccess',1,'',NULL,'N;'),
('MarketingSearch',0,'',NULL,'N;'),
('MarketingToggle',0,'',NULL,'N;'),
('MarketingUpdate',0,'',NULL,'N;'),
('MarketingUpdateAccess',1,'',NULL,'N;'),
('MarketingUpdatePrivate',1,'Update their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('MarketingView',0,'',NULL,'N;'),
('MarketingViewPrivate',1,'View their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('MarketingWebLeadForm',0,'Edit the lead capture form.',NULL,'N;'),
('MediaAdmin',0,'',NULL,'N;'),
('MediaAdminAccess',1,'',NULL,'N;'),
('MediaBasicAccess',1,'',NULL,'N;'),
('MediaDelete',0,'',NULL,'N;'),
('MediaDeleteNote',0,'',NULL,'N;'),
('MediaDownload',0,'',NULL,'N;'),
('MediaFullAccess',1,'',NULL,'N;'),
('MediaIndex',0,'',NULL,'N;'),
('MediaMinimumRequirements',1,'',NULL,'N;'),
('MediaReadOnlyAccess',1,'',NULL,'N;'),
('MediaSearch',0,'',NULL,'N;'),
('MediaToggleUserMediaVisible',0,'',NULL,'N;'),
('MediaUpdate',0,'',NULL,'N;'),
('MediaUpdateAccess',1,'',NULL,'N;'),
('MediaUpload',0,'',NULL,'N;'),
('MediaView',0,'',NULL,'N;'),
('OpportunitiesAddContact',0,'',NULL,'N;'),
('OpportunitiesAddUser',0,'',NULL,'N;'),
('OpportunitiesAdmin',0,'',NULL,'N;'),
('OpportunitiesAdminAccess',1,'',NULL,'N;'),
('OpportunitiesBasicAccess',1,'',NULL,'N;'),
('OpportunitiesCreate',0,'',NULL,'N;'),
('OpportunitiesDelete',0,'',NULL,'N;'),
('OpportunitiesDeleteNote',0,'',NULL,'N;'),
('OpportunitiesDeletePrivate',1,'Delete their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('OpportunitiesFullAccess',1,'',NULL,'N;'),
('OpportunitiesGetItems',0,'',NULL,'N;'),
('OpportunitiesGetTerms',0,'',NULL,'N;'),
('OpportunitiesIndex',0,'',NULL,'N;'),
('OpportunitiesMinimumRequirements',1,'',NULL,'N;'),
('OpportunitiesPrivateReadOnlyAccess',1,'',NULL,'N;'),
('OpportunitiesPrivateUpdateAccess',1,'',NULL,'N;'),
('OpportunitiesReadOnlyAccess',1,'',NULL,'N;'),
('OpportunitiesRemoveContact',0,'',NULL,'N;'),
('OpportunitiesRemoveUser',0,'',NULL,'N;'),
('OpportunitiesSearch',0,'',NULL,'N;'),
('OpportunitiesShareOpportunity',0,'',NULL,'N;'),
('OpportunitiesUpdate',0,'',NULL,'N;'),
('OpportunitiesUpdateAccess',1,'',NULL,'N;'),
('OpportunitiesUpdatePrivate',1,'Update their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('OpportunitiesView',0,'',NULL,'N;'),
('OpportunitiesViewPrivate',1,'View their own record','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('OpporunitiesPrivateFullAccess',1,'',NULL,'N;'),
('ProductsAdmin',0,'',NULL,'N;'),
('ProductsAdminAccess',1,'',NULL,'N;'),
('ProductsBasicAccess',1,'',NULL,'N;'),
('ProductsCreate',0,'',NULL,'N;'),
('ProductsDelete',0,'',NULL,'N;'),
('ProductsDeleteNote',0,'',NULL,'N;'),
('ProductsDeletePrivate',1,'Delete their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ProductsFullAccess',1,'',NULL,'N;'),
('ProductsGetItems',0,'',NULL,'N;'),
('ProductsIndex',0,'',NULL,'N;'),
('ProductsMinimumRequirements',1,'',NULL,'N;'),
('ProductsPrivateFullAccess',1,'',NULL,'N;'),
('ProductsPrivateReadOnlyAccess',1,'',NULL,'N;'),
('ProductsPrivateUpdateAccess',1,'',NULL,'N;'),
('ProductsReadOnlyAccess',1,'',NULL,'N;'),
('ProductsSearch',0,'',NULL,'N;'),
('ProductsUpdate',0,'',NULL,'N;'),
('ProductsUpdateAccess',1,'',NULL,'N;'),
('ProductsUpdatePrivate',1,'Update their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ProductsView',0,'',NULL,'N;'),
('ProductsViewPrivate',1,'View their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('QuotesAddContact',0,'',NULL,'N;'),
('QuotesAddProduct',0,'',NULL,'N;'),
('QuotesAddUser',0,'',NULL,'N;'),
('QuotesAdmin',0,'',NULL,'N;'),
('QuotesAdminAccess',1,'',NULL,'N;'),
('QuotesBasicAccess',1,'',NULL,'N;'),
('QuotesCreate',0,'',NULL,'N;'),
('QuotesDelete',0,'',NULL,'N;'),
('QuotesDeleteNote',0,'',NULL,'N;'),
('QuotesDeletePrivate',1,'Delete their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('QuotesDeleteProduct',0,'',NULL,'N;'),
('QuotesFullAccess',1,'',NULL,'N;'),
('QuotesGetItems',0,'',NULL,'N;'),
('QuotesGetTerms',0,'',NULL,'N;'),
('QuotesIndex',0,'',NULL,'N;'),
('QuotesMinimumRequirements',1,'',NULL,'N;'),
('QuotesPrint',0,'',NULL,'N;'),
('QuotesPrivateFullAccess',1,'',NULL,'N;'),
('QuotesPrivateReadOnlyAccess',1,'',NULL,'N;'),
('QuotesPrivateUpdateAccess',1,'',NULL,'N;'),
('QuotesQuickCreate',0,'',NULL,'N;'),
('QuotesQuickDelete',0,'',NULL,'N;'),
('QuotesQuickUpdate',0,'',NULL,'N;'),
('QuotesReadOnlyAccess',1,'',NULL,'N;'),
('QuotesRemoveContact',0,'',NULL,'N;'),
('QuotesRemoveUser',0,'',NULL,'N;'),
('QuotesSearch',0,'',NULL,'N;'),
('QuotesShareQuote',0,'',NULL,'N;'),
('QuotesUpdate',0,'',NULL,'N;'),
('QuotesUpdateAccess',1,'',NULL,'N;'),
('QuotesUpdatePrivate',1,'Update their own records','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('QuotesView',0,'',NULL,'N;'),
('QuotesViewPrivate',1,'View their own record','return Yii::app()->user->getName()==\$params[\'assignedTo\'];','N;'),
('ReportsActivityReport',0,'',NULL,'N;'),
('ReportsAdmin',0,'',NULL,'N;'),
('ReportsAdminAccess',1,'',NULL,'N;'),
('ReportsDealReport',0,'',NULL,'N;'),
('ReportsDelete',0,'',NULL,'N;'),
('ReportsDeleteNote',0,'',NULL,'N;'),
('ReportsFullAccess',1,'',NULL,'N;'),
('ReportsGetOptions',0,'',NULL,'N;'),
('ReportsGridReport',0,'',NULL,'N;'),
('ReportsIndex',0,'',NULL,'N;'),
('ReportsLeadPerformance',0,'',NULL,'N;'),
('ReportsMinimumRequirements',1,'',NULL,'N;'),
('ReportsPrintReport',0,'',NULL,'N;'),
('ReportsSavedReports',0,'',NULL,'N;'),
('ReportsSaveReport',0,'',NULL,'N;'),
('ReportsSaveTempImage',0,'',NULL,'N;'),
('ReportsSearch',0,'',NULL,'N;'),
('ReportsWorkflow',0,'',NULL,'N;'),
('RoleAccessTask',1,'A set of operations for managing roles.',NULL,'N;'),
('SiteIndex',0,'Index of SiteController.',NULL,'N;'),
('SiteLogin',0,'Log in to the software.',NULL,'N;'),
('SiteLogout',0,'Lout out of the software.',NULL,'N;'),
('SiteWhatsNew',0,'What\'s New page of the app.',NULL,'N;'),
('TranslationsTask',1,'A set of permissions required to access translation features.',NULL,'N;'),
('UsersAddTopContact',0,'',NULL,'N;'),
('UsersAdmin',0,'',NULL,'N;'),
('UsersAdminAccess',1,'',NULL,'N;'),
('UsersBasicAccess',1,'',NULL,'N;'),
('UsersCreate',0,'',NULL,'N;'),
('UsersCreateAccount',0,'',NULL,'N;'),
('UsersDelete',0,'',NULL,'N;'),
('UsersDeleteNote',0,'',NULL,'N;'),
('UsersFullAccess',1,'',NULL,'N;'),
('UsersIndex',0,'',NULL,'N;'),
('UsersInviteUsers',0,'',NULL,'N;'),
('UsersMinimumRequirements',1,'',NULL,'N;'),
('UsersReadOnlyAccess',1,'',NULL,'N;'),
('UsersRemoveTopContact',0,'',NULL,'N;'),
('UsersSearch',0,'',NULL,'N;'),
('UsersUpdate',0,'',NULL,'N;'),
('UsersUpdateAccess',1,'',NULL,'N;'),
('UsersView',0,'',NULL,'N;'),
('WeblistCreate',0,'',NULL,'N;'),
('WeblistDelete',0,'',NULL,'N;'),
('WeblistIndex',0,'',NULL,'N;'),
('WeblistUpdate',0,'',NULL,'N;'),
('WeblistView',0,'',NULL,'N;'),
('WorkflowAdmin',0,'',NULL,'N;'),
('WorkflowAdminAccess',1,'',NULL,'N;'),
('WorkflowBasicAccess',1,'',NULL,'N;'),
('WorkflowCompleteStage',0,'',NULL,'N;'),
('WorkflowCreate',0,'',NULL,'N;'),
('WorkflowDelete',0,'',NULL,'N;'),
('WorkflowDeleteNote',0,'',NULL,'N;'),
('WorkflowFullAccess',1,'',NULL,'N;'),
('WorkflowGetStageDetails',0,'',NULL,'N;'),
('WorkflowGetStageMembers',0,'',NULL,'N;'),
('WorkflowGetStages',0,'',NULL,'N;'),
('WorkflowGetWorkflow',0,'',NULL,'N;'),
('WorkflowIndex',0,'',NULL,'N;'),
('WorkflowMinimumRequirements',1,'',NULL,'N;'),
('WorkflowReadOnlyAccess',1,'',NULL,'N;'),
('WorkflowRevertStage',0,'',NULL,'N;'),
('WorkflowSearch',0,'',NULL,'N;'),
('WorkflowStartStage',0,'',NULL,'N;'),
('WorkflowUpdate',0,'',NULL,'N;'),
('WorkflowUpdateAccess',1,'',NULL,'N;'),
('WorkflowUpdateStageDetails',0,'',NULL,'N;'),
('WorkflowView',0,'',NULL,'N;'),
('WorkflowViewStage',0,'',NULL,'N;'),
('X2StudioTask',1,'A set of permissions for the use of X2Studio.',NULL,'N;')
") or addSqlError('Unable to create default roles '.mysql_error());

mysql_query("
INSERT INTO `x2_auth_item_child` VALUES ('AccountsUpdateAccess','AccountsAddUser'),
('AccountsUpdatePrivate','AccountsAddUser'),
('AccountsAdminAccess','AccountsAdmin'),
('administrator','AccountsAdminAccess'),
('AccountsPrivateUpdateAccess','AccountsBasicAccess'),
('AccountsUpdateAccess','AccountsBasicAccess'),
('AccountsBasicAccess','AccountsCreate'),
('AccountsDeletePrivate','AccountsDelete'),
('AccountsFullAccess','AccountsDelete'),
('AccountsFullAccess','AccountsDeleteNote'),
('AccountsPrivateFullAccess','AccountsDeleteNote'),
('AccountsPrivateFullAccess','AccountsDeletePrivate'),
('AccountsAdminAccess','AccountsFullAccess'),
('AccountsMinimumRequirements','AccountsGetItems'),
('AccountsMinimumRequirements','AccountsIndex'),
('AccountsPrivateReadOnlyAccess','AccountsMinimumRequirements'),
('AccountsReadOnlyAccess','AccountsMinimumRequirements'),
('AccountsPrivateFullAccess','AccountsPrivateUpdateAccess'),
('AccountsBasicAccess','AccountsReadOnlyAccess'),
('AccountsUpdateAccess','AccountsRemoveUser'),
('AccountsUpdatePrivate','AccountsRemoveUser'),
('AccountsMinimumRequirements','AccountsSearch'),
('AccountsReadOnlyAccess','AccountsShareAccount'),
('AccountsViewPrivate','AccountsShareAccount'),
('AccountsUpdateAccess','AccountsUpdate'),
('AccountsUpdatePrivate','AccountsUpdate'),
('AccountsFullAccess','AccountsUpdateAccess'),
('authenticated','AccountsUpdateAccess'),
('AccountsPrivateUpdateAccess','AccountsUpdatePrivate'),
('AccountsReadOnlyAccess','AccountsView'),
('AccountsViewPrivate','AccountsView'),
('AccountsPrivateReadOnlyAccess','AccountsViewPrivate'),
('ActionsAdminAccess','ActionsAdmin'),
('administrator','ActionsAdminAccess'),
('ActionsPrivateUpdateAccess','ActionsBasicAccess'),
('ActionsUpdateAccess','ActionsBasicAccess'),
('ActionsReadOnlyAccess','ActionsComplete'),
('ActionsViewPrivate','ActionsComplete'),
('ActionsReadOnlyAccess','ActionsCompleteSelected'),
('ActionsViewPrivate','ActionsCompleteSelected'),
('ActionsBasicAccess','ActionsCreate'),
('ActionsDeletePrivate','ActionsDelete'),
('ActionsFullAccess','ActionsDelete'),
('ActionsFullAccess','ActionsDeleteNote'),
('ActionsPrivateFullAccess','ActionsDeleteNote'),
('ActionsPrivateFullAccess','ActionsDeletePrivate'),
('ActionsAdminAccess','ActionsFullAccess'),
('ActionsMinimumRequirements','ActionsGetTerms'),
('ActionsMinimumRequirements','ActionsIndex'),
('ActionsMinimumRequirements','ActionsInvalid'),
('ActionsPrivateReadOnlyAccess','ActionsMinimumRequirements'),
('ActionsReadOnlyAccess','ActionsMinimumRequirements'),
('ActionsMinimumRequirements','ActionsParseType'),
('ActionsPrivateFullAccess','ActionsPrivateUpdateAccess'),
('ActionsBasicAccess','ActionsPublisherCreate'),
('ActionsUpdateAccess','ActionsQuickUpdate'),
('ActionsUpdatePrivate','ActionsQuickUpdate'),
('ActionsBasicAccess','ActionsReadOnlyAccess'),
('ActionsMinimumRequirements','ActionsSaveShowActions'),
('ActionsMinimumRequirements','ActionsSearch'),
('ActionsReadOnlyAccess','ActionsSendReminder'),
('ActionsViewPrivate','ActionsSendReminder'),
('ActionsReadOnlyAccess','ActionsShareAction'),
('ActionsViewPrivate','ActionsShareAction'),
('ActionsReadOnlyAccess','ActionsTomorrow'),
('ActionsViewPrivate','ActionsTomorrow'),
('ActionsReadOnlyAccess','ActionsUncomplete'),
('ActionsViewPrivate','ActionsUncomplete'),
('ActionsReadOnlyAccess','ActionsUncompleteSelected'),
('ActionsViewPrivate','ActionsUncompleteSelected'),
('ActionsUpdateAccess','ActionsUpdate'),
('ActionsUpdatePrivate','ActionsUpdate'),
('ActionsFullAccess','ActionsUpdateAccess'),
('authenticated','ActionsUpdateAccess'),
('ActionsPrivateUpdateAccess','ActionsUpdatePrivate'),
('ActionsUpdateAccess','ActionsUpdateSelected'),
('ActionsUpdatePrivate','ActionsUpdateSelected'),
('ActionsReadOnlyAccess','ActionsView'),
('ActionsViewPrivate','ActionsView'),
('ActionsMinimumRequirements','ActionsViewAll'),
('ActionsMinimumRequirements','ActionsViewGroup'),
('ActionsPrivateReadOnlyAccess','ActionsViewPrivate'),
('LeadRoutingTask','AdminAddCriteria'),
('FieldsTask','AdminAddField'),
('GeneralAdminSettingsTask','AdminAppSettings'),
('X2StudioTask','AdminCreateModule'),
('GeneralAdminSettingsTask','AdminCreatePage'),
('FieldsTask','AdminCustomizeFields'),
('DropDownsTask','AdminDeleteDropdown'),
('FieldsTask','AdminDeleteField'),
('X2StudioTask','AdminDeleteModule'),
('RoleAccessTask','AdminDeleteRole'),
('DropDownsTask','AdminDropDownEditor'),
('DropDownsTask','AdminEditDropdown'),
('X2StudioTask','AdminEditor'),
('RoleAccessTask','AdminEditRole'),
('GeneralAdminSettingsTask','AdminEmailSetup'),
('GeneralAdminSettingsTask','AdminExport'),
('X2StudioTask','AdminExportModule'),
('GeneralAdminSettingsTask','AdminGoogleIntegration'),
('GeneralAdminSettingsTask','AdminImport'),
('X2StudioTask','AdminImportModule'),
('GeneralAdminSettingsTask','AdminIndex'),
('TranslationsTask','AdminIndex'),
('DropDownsTask','AdminManageDropDowns'),
('FieldsTask','AdminManageFields'),
('GeneralAdminSettingsTask','AdminManageModules'),
('RoleAccessTask','AdminManageRoles'),
('X2StudioTask','AdminRenameModules'),
('RoleAccessTask','AdminRoleEditor'),
('RoleAccessTask','AdminRoleException'),
('LeadRoutingTask','AdminRoundRobinRules'),
('LeadRoutingTask','AdminSetLeadRouting'),
('GeneralAdminSettingsTask','AdminToggleDefaultLogo'),
('TranslationsTask','AdminTranslationManager'),
('GeneralAdminSettingsTask','AdminUploadLogo'),
('GeneralAdminSettingsTask','AdminViewChangelog'),
('administrator','authenticated'),
('authenticated','AuthenticatedSiteFunctionsTask'),
('CalendarAdminAccess','CalendarAdmin'),
('administrator','CalendarAdminAccess'),
('CalendarUpdateAccess','CalendarBasicAccess'),
('CalendarUpdateAccess','CalendarCompleteAction'),
('CalendarBasicAccess','CalendarCreate'),
('CalendarFullAccess','CalendarDelete'),
('CalendarFullAccess','CalendarDeleteAction'),
('CalendarFullAccess','CalendarDeleteGoogleEvent'),
('CalendarFullAccess','CalendarDeleteNote'),
('CalendarUpdateAccess','CalendarEditAction'),
('CalendarUpdateAccess','CalendarEditGoogleEvent'),
('CalendarAdminAccess','CalendarFullAccess'),
('CalendarMinimumRequirements','CalendarIndex'),
('CalendarMinimumRequirements','CalendarJsonFeed'),
('CalendarMinimumRequirements','CalendarJsonFeedGoogle'),
('CalendarMinimumRequirements','CalendarJsonFeedGroup'),
('CalendarMinimumRequirements','CalendarJsonFeedShared'),
('CalendarReadOnlyAccess','CalendarMinimumRequirements'),
('CalendarUpdateAccess','CalendarMoveAction'),
('CalendarUpdateAccess','CalendarMoveGoogleEvent'),
('CalendarMinimumRequirements','CalendarMyCalendarPermissions'),
('CalendarBasicAccess','CalendarReadOnlyAccess'),
('CalendarUpdateAccess','CalendarResizeAction'),
('CalendarUpdateAccess','CalendarResizeGoogleEvent'),
('CalendarMinimumRequirements','CalendarSaveCheckedCalendar'),
('CalendarMinimumRequirements','CalendarSaveCheckedCalendarFilter'),
('CalendarBasicAccess','CalendarSaveGoogleEvent'),
('CalendarMinimumRequirements','CalendarSearch'),
('CalendarBasicAccess','CalendarSyncActionsToGoogleCalendar'),
('CalendarMinimumRequirements','CalendarTogglePortletVisible'),
('CalendarMinimumRequirements','CalendarToggleUserCalendarsVisible'),
('CalendarUpdateAccess','CalendarUncompleteAction'),
('CalendarUpdateAccess','CalendarUpdate'),
('authenticated','CalendarFullAccess'),
('CalendarFullAccess','CalendarUpdateAccess'),
('CalendarAdminAccess','CalendarUserCalendarPermissions'),
('CalendarReadOnlyAccess','CalendarView'),
('CalendarReadOnlyAccess','CalendarViewAction'),
('CalendarReadOnlyAccess','CalendarViewGoogleEvent'),
('ChartsAdminAccess','ChartsAdmin'),
('administrator','ChartsAdminAccess'),
('ChartsFullAccess','ChartsDeleteNote'),
('authenticated','ChartsFullAccess'),
('ChartsAdminAccess','ChartsFullAccess'),
('ChartsMinimumRequirements','ChartsGetFieldData'),
('ChartsMinimumRequirements','ChartsIndex'),
('ChartsFullAccess','ChartsLeadVolume'),
('ChartsFullAccess','ChartsMarketing'),
('ChartsFullAccess','ChartsMinimumRequirements'),
('ChartsFullAccess','ChartsPipeline'),
('ChartsFullAccess','ChartsSales'),
('ChartsMinimumRequirements','ChartsSearch'),
('ChartsFullAccess','ChartsWorkflow'),
('ContactsBasicAccess','ContactsAddToList'),
('ContactsAdminAccess','ContactsAdmin'),
('administrator','ContactsAdminAccess'),
('ContactsPrivateUpdateAccess','ContactsBasicAccess'),
('ContactsUpdateAccess','ContactsBasicAccess'),
('ContactsBasicAccess','ContactsCreate'),
('ContactsBasicAccess','ContactsCreateList'),
('ContactsBasicAccess','ContactsCreateListFromSelection'),
('ContactsDeletePrivate','ContactsDelete'),
('ContactsFullAccess','ContactsDelete'),
('ContactsDeletePrivate','ContactsDeleteList'),
('ContactsFullAccess','ContactsDeleteList'),
('ContactsFullAccess','ContactsDeleteNote'),
('ContactsPrivateFullAccess','ContactsDeleteNote'),
('ContactsPrivateFullAccess','ContactsDeletePrivate'),
('ContactsMinimumRequirements','ContactsDiscardNew'),
('ContactsAdminAccess','ContactsExport'),
('ContactsAdminAccess','ContactsExportList'),
('ContactsAdminAccess','ContactsFullAccess'),
('ContactsMinimumRequirements','ContactsGetContacts'),
('ContactsMinimumRequirements','ContactsGetItems'),
('ContactsMinimumRequirements','ContactsGetLists'),
('ContactsMinimumRequirements','ContactsGetTerms'),
('ContactsMinimumRequirements','ContactsIgnoreDuplicates'),
('ContactsAdminAccess','ContactsImportContacts'),
('ContactsAdminAccess','ContactsImportExcel'),
('ContactsMinimumRequirements','ContactsIndex'),
('ContactsMinimumRequirements','ContactsList'),
('ContactsMinimumRequirements','ContactsLists'),
('ContactsPrivateReadOnlyAccess','ContactsMinimumRequirements'),
('ContactsReadOnlyAccess','ContactsMinimumRequirements'),
('ContactsMinimumRequirements','ContactsMyContacts'),
('ContactsMinimumRequirements','ContactsNewContacts'),
('ContactsPrivateFullAccess','ContactsPrivateUpdateAccess'),
('ContactsMinimumRequirements','ContactsQtip'),
('ContactsBasicAccess','ContactsQuickContact'),
('ContactsBasicAccess','ContactsReadOnlyAccess'),
('ContactsUpdateAccess','ContactsRemoveFromList'),
('ContactsUpdatePrivate','ContactsRemoveFromList'),
('ContactsMinimumRequirements','ContactsSearch'),
('ContactsReadOnlyAccess','ContactsShareContact'),
('ContactsViewPrivate','ContactsShareContact'),
('ContactsReadOnlyAccess','ContactsSubscribe'),
('ContactsViewPrivate','ContactsSubscribe'),
('ContactsUpdateAccess','ContactsUpdate'),
('ContactsUpdatePrivate','ContactsUpdate'),
('authenticated','ContactsUpdateAccess'),
('ContactsFullAccess','ContactsUpdateAccess'),
('ContactsPrivateUpdateAccess','ContactsUpdateList'),
('ContactsUpdateAccess','ContactsUpdateList'),
('ContactsPrivateUpdateAccess','ContactsUpdatePrivate'),
('ContactsReadOnlyAccess','ContactsView'),
('ContactsViewPrivate','ContactsView'),
('ContactsPrivateReadOnlyAccess','ContactsViewPrivate'),
('ContactsReadOnlyAccess','ContactsViewRelationships'),
('ContactsViewPrivate','ContactsViewRelationships'),
('DocsAdminAccess','DocsAdmin'),
('administrator','DocsAdminAccess'),
('DocsMinimumRequirements','DocsAutosave'),
('DocsPrivateUpdateAccess','DocsBasicAccess'),
('DocsUpdateAccess','DocsBasicAccess'),
('DocsUpdateAccess','DocsChangePermissions'),
('DocsUpdatePrivate','DocsChangePermissions'),
('DocsBasicAccess','DocsCreate'),
('DocsBasicAccess','DocsCreateEmail'),
('DocsDeletePrivate','DocsDelete'),
('DocsFullAccess','DocsDelete'),
('DocsFullAccess','DocsDeleteNote'),
('DocsPrivateFullAccess','DocsDeleteNote'),
('DocsPrivateFullAccess','DocsDeletePrivate'),
('DocsReadOnlyAccess','DocsExportToHtml'),
('DocsViewPrivate','DocsExportToHtml'),
('DocsAdminAccess','DocsFullAccess'),
('DocsMinimumRequirements','DocsGetItem'),
('DocsMinimumRequirements','DocsGetItems'),
('DocsMinimumRequirements','DocsIndex'),
('DocsPrivateReadOnlyAccess','DocsMinimumRequirements'),
('DocsReadOnlyAccess','DocsMinimumRequirements'),
('DocsPrivateFullAccess','DocsPrivateUpdateAccess'),
('DocsBasicAccess','DocsReadOnlyAccess'),
('DocsMinimumRequirements','DocsSearch'),
('DocsUpdateAccess','DocsUpdate'),
('DocsUpdatePrivate','DocsUpdate'),
('authenticated','DocsUpdateAccess'),
('DocsFullAccess','DocsUpdateAccess'),
('DocsPrivateUpdateAccess','DocsUpdatePrivate'),
('DocsReadOnlyAccess','DocsView'),
('DocsViewPrivate','DocsView'),
('DocsPrivateReadOnlyAccess','DocsViewPrivate'),
('X2StudioTask','DropDownsTask'),
('X2StudioTask','FieldsTask'),
('administrator','GeneralAdminSettingsTask'),
('GroupsAdminAccess','GroupsAdmin'),
('administrator','GroupsAdminAccess'),
('GroupsUpdateAccess','GroupsBasicAccess'),
('GroupsBasicAccess','GroupsCreate'),
('GroupsFullAccess','GroupsDelete'),
('GroupsFullAccess','GroupsDeleteNote'),
('GroupsAdminAccess','GroupsFullAccess'),
('GroupsMinimumRequirements','GroupsGetGroups'),
('GroupsMinimumRequirements','GroupsIndex'),
('GroupsReadOnlyAccess','GroupsMinimumRequirements'),
('authenticated','GroupsReadOnlyAccess'),
('GroupsBasicAccess','GroupsReadOnlyAccess'),
('GroupsMinimumRequirements','GroupsSearch'),
('GroupsUpdateAccess','GroupsUpdate'),
('GroupsFullAccess','GroupsUpdateAccess'),
('GroupsReadOnlyAccess','GroupsView'),
('guest','GuestSiteFunctionsTask'),
('administrator','LeadRoutingTask'),
('MarketingAdminAccess','MarketingAdmin'),
('administrator','MarketingAdminAccess'),
('MarketingUpdateAccess','MarketingBasicAccess'),
('MarketingPrivateBasicAccess','MarketingBasicPrivate'),
('GuestSiteFunctionsTask','MarketingClick'),
('MarketingUpdateAccess','MarketingComplete'),
('MarketingUpdatePrivate','MarketingComplete'),
('MarketingBasicAccess','MarketingCreate'),
('MarketingPrivateBasicAccess','MarketingCreate'),
('MarketingBasicAccess','MarketingCreateFromTag'),
('MarketingPrivateBasicAccess','MarketingCreateFromTag'),
('MarketingDeletePrivate','MarketingDelete'),
('MarketingFullAccess','MarketingDelete'),
('MarketingDeletePrivate','MarketingDeleteNote'),
('MarketingFullAccess','MarketingDeleteNote'),
('MarketingPrivateFullAccess','MarketingDeletePrivate'),
('MarketingAdminAccess','MarketingFullAccess'),
('MarketingMinimumRequirements','MarketingGetItems'),
('MarketingMinimumRequirements','MarketingIndex'),
('MarketingBasicAccess','MarketingLaunch'),
('MarketingBasicPrivate','MarketingLaunch'),
('MarketingMinimumRequirements','MarketingMail'),
('MarketingPrivateReadOnlyAccess','MarketingMinimumRequirements'),
('MarketingReadOnlyAccess','MarketingMinimumRequirements'),
('authenticated','MarketingPrivateBasicAccess'),
('MarketingPrivateUpdateAccess','MarketingPrivateBasicAccess'),
('MarketingPrivateBasicAccess','MarketingReadOnlyAccess'),
('MarketingPrivateFullAccess','MarketingPrivateUpdateAccess'),
('MarketingBasicAccess','MarketingReadOnlyAccess'),
('MarketingMinimumRequirements','MarketingSearch'),
('MarketingBasicAccess','MarketingToggle'),
('MarketingBasicPrivate','MarketingToggle'),
('MarketingUpdateAccess','MarketingUpdate'),
('MarketingUpdatePrivate','MarketingUpdate'),
('MarketingFullAccess','MarketingUpdateAccess'),
('MarketingPrivateUpdateAccess','MarketingUpdatePrivate'),
('MarketingReadOnlyAccess','MarketingView'),
('MarketingViewPrivate','MarketingView'),
('MarketingPrivateReadOnlyAccess','MarketingViewPrivate'),
('LeadRoutingTask','MarketingWebLeadForm'),
('MarketingAdminAccess','MarketingWebleadForm'),
('MediaAdminAccess','MediaAdmin'),
('administrator','MediaAdminAccess'),
('MediaUpdateAccess','MediaBasicAccess'),
('MediaFullAccess','MediaDelete'),
('MediaFullAccess','MediaDeleteNote'),
('MediaReadOnlyAccess','MediaDownload'),
('MediaAdminAccess','MediaFullAccess'),
('MediaMinimumRequirements','MediaIndex'),
('MediaReadOnlyAccess','MediaMinimumRequirements'),
('MediaBasicAccess','MediaReadOnlyAccess'),
('MediaMinimumRequirements','MediaSearch'),
('AuthenticatedSiteFunctionsTask','MediaToggleUserMediaVisible'),
('MediaUpdateAccess','MediaUpdate'),
('authenticated','MediaUpdateAccess'),
('MediaFullAccess','MediaUpdateAccess'),
('MediaBasicAccess','MediaUpload'),
('MediaReadOnlyAccess','MediaView'),
('OpportunitiesUpdateAccess','OpportunitiesAddContact'),
('OpportunitiesUpdatePrivate','OpportunitiesAddContact'),
('OpportunitiesUpdateAccess','OpportunitiesAddUser'),
('OpportunitiesUpdatePrivate','OpportunitiesAddUser'),
('OpportunitiesAdminAccess','OpportunitiesAdmin'),
('administrator','OpportunitiesAdminAccess'),
('OpportunitiesPrivateUpdateAccess','OpportunitiesBasicAccess'),
('OpportunitiesUpdateAccess','OpportunitiesBasicAccess'),
('OpportunitiesBasicAccess','OpportunitiesCreate'),
('OpportunitiesDeletePrivate','OpportunitiesDelete'),
('OpportunitiesFullAccess','OpportunitiesDelete'),
('OpportunitiesDeletePrivate','OpportunitiesDeleteNote'),
('OpportunitiesFullAccess','OpportunitiesDeleteNote'),
('OpporunitiesPrivateFullAccess','OpportunitiesDeletePrivate'),
('OpportunitiesAdminAccess','OpportunitiesFullAccess'),
('GuestSiteFunctionsTask','OpportunitiesGetItems'),
('OpportunitiesMinimumRequirements','OpportunitiesGetTerms'),
('OpportunitiesMinimumRequirements','OpportunitiesIndex'),
('OpportunitiesPrivateReadOnlyAccess','OpportunitiesMinimumRequirements'),
('OpportunitiesReadOnlyAccess','OpportunitiesMinimumRequirements'),
('OpporunitiesPrivateFullAccess','OpportunitiesPrivateUpdateAccess'),
('OpportunitiesBasicAccess','OpportunitiesReadOnlyAccess'),
('OpportunitiesUpdateAccess','OpportunitiesRemoveContact'),
('OpportunitiesUpdatePrivate','OpportunitiesRemoveContact'),
('OpportunitiesUpdateAccess','OpportunitiesRemoveUser'),
('OpportunitiesUpdatePrivate','OpportunitiesRemoveUser'),
('OpportunitiesMinimumRequirements','OpportunitiesSearch'),
('OpportunitiesReadOnlyAccess','OpportunitiesShareOpportunity'),
('OpportunitiesViewPrivate','OpportunitiesShareOpportunity'),
('OpportunitiesUpdateAccess','OpportunitiesUpdate'),
('OpportunitiesUpdatePrivate','OpportunitiesUpdate'),
('authenticated','OpportunitiesUpdateAccess'),
('OpportunitiesFullAccess','OpportunitiesUpdateAccess'),
('OpportunitiesPrivateUpdateAccess','OpportunitiesUpdatePrivate'),
('OpportunitiesReadOnlyAccess','OpportunitiesView'),
('OpportunitiesViewPrivate','OpportunitiesView'),
('OpportunitiesPrivateReadOnlyAccess','OpportunitiesViewPrivate'),
('ProductsAdminAccess','ProductsAdmin'),
('administrator','ProductsAdminAccess'),
('ProductsPrivateUpdateAccess','ProductsBasicAccess'),
('ProductsUpdateAccess','ProductsBasicAccess'),
('ProductsBasicAccess','ProductsCreate'),
('ProductsDeletePrivate','ProductsDelete'),
('ProductsFullAccess','ProductsDelete'),
('ProductsDeletePrivate','ProductsDeleteNote'),
('ProductsFullAccess','ProductsDeleteNote'),
('ProductsPrivateFullAccess','ProductsDeletePrivate'),
('ProductsAdminAccess','ProductsFullAccess'),
('ProductsMinimumRequirements','ProductsGetItems'),
('ProductsMinimumRequirements','ProductsIndex'),
('ProductsPrivateReadOnlyAccess','ProductsMinimumRequirements'),
('ProductsReadOnlyAccess','ProductsMinimumRequirements'),
('ProductsPrivateFullAccess','ProductsPrivateUpdateAccess'),
('ProductsBasicAccess','ProductsReadOnlyAccess'),
('ProductsMinimumRequirements','ProductsSearch'),
('ProductsUpdateAccess','ProductsUpdate'),
('ProductsUpdatePrivate','ProductsUpdate'),
('authenticated','ProductsUpdateAccess'),
('ProductsFullAccess','ProductsUpdateAccess'),
('ProductsPrivateUpdateAccess','ProductsUpdatePrivate'),
('ProductsReadOnlyAccess','ProductsView'),
('ProductsViewPrivate','ProductsView'),
('ProductsPrivateReadOnlyAccess','ProductsViewPrivate'),
('QuotesUpdateAccess','QuotesAddContact'),
('QuotesUpdatePrivate','QuotesAddContact'),
('QuotesUpdateAccess','QuotesAddProduct'),
('QuotesUpdatePrivate','QuotesAddProduct'),
('QuotesUpdateAccess','QuotesAddUser'),
('QuotesUpdatePrivate','QuotesAddUser'),
('QuotesAdminAccess','QuotesAdmin'),
('administrator','QuotesAdminAccess'),
('QuotesPrivateUpdateAccess','QuotesBasicAccess'),
('QuotesUpdateAccess','QuotesBasicAccess'),
('QuotesBasicAccess','QuotesCreate'),
('QuotesDeletePrivate','QuotesDelete'),
('QuotesFullAccess','QuotesDelete'),
('QuotesDeletePrivate','QuotesDeleteNote'),
('QuotesFullAccess','QuotesDeleteNote'),
('QuotesPrivateFullAccess','QuotesDeletePrivate'),
('QuotesUpdateAccess','QuotesDeleteProduct'),
('QuotesUpdatePrivate','QuotesDeleteProduct'),
('QuotesAdminAccess','QuotesFullAccess'),
('GuestSiteFunctionsTask','QuotesGetItems'),
('QuotesMinimumRequirements','QuotesGetTerms'),
('QuotesMinimumRequirements','QuotesIndex'),
('QuotesPrivateReadOnlyAccess','QuotesMinimumRequirements'),
('QuotesReadOnlyAccess','QuotesMinimumRequirements'),
('QuotesReadOnlyAccess','QuotesPrint'),
('QuotesViewPrivate','QuotesPrint'),
('QuotesPrivateFullAccess','QuotesPrivateUpdateAccess'),
('QuotesBasicAccess','QuotesQuickCreate'),
('QuotesDeletePrivate','QuotesQuickDelete'),
('QuotesFullAccess','QuotesQuickDelete'),
('QuotesUpdateAccess','QuotesQuickUpdate'),
('QuotesUpdatePrivate','QuotesQuickUpdate'),
('QuotesBasicAccess','QuotesReadOnlyAccess'),
('QuotesUpdateAccess','QuotesRemoveContact'),
('QuotesUpdatePrivate','QuotesRemoveContact'),
('QuotesUpdateAccess','QuotesRemoveUser'),
('QuotesUpdatePrivate','QuotesRemoveUser'),
('QuotesMinimumRequirements','QuotesSearch'),
('QuotesReadOnlyAccess','QuotesShareQuote'),
('QuotesViewPrivate','QuotesShareQuote'),
('QuotesUpdateAccess','QuotesUpdate'),
('QuotesUpdatePrivate','QuotesUpdate'),
('authenticated','QuotesUpdateAccess'),
('QuotesFullAccess','QuotesUpdateAccess'),
('QuotesPrivateUpdateAccess','QuotesUpdatePrivate'),
('QuotesReadOnlyAccess','QuotesView'),
('QuotesViewPrivate','QuotesView'),
('QuotesPrivateReadOnlyAccess','QuotesViewPrivate'),
('administrator','RoleAccessTask'),
('AuthenticatedSiteFunctionsTask','SiteIndex'),
('GuestSiteFunctionsTask','SiteIndex'),
('GuestSiteFunctionsTask','SiteLogin'),
('AuthenticatedSiteFunctionsTask','SiteLogout'),
('AuthenticatedSiteFunctionsTask','SiteWhatsNew'),
('administrator','TranslationsTask'),
('AuthenticatedSiteFunctionsTask','UsersAddTopContact'),
('UsersMinimumRequirements','UsersAdmin'),
('administrator','UsersAdminAccess'),
('UsersUpdateAccess','UsersBasicAccess'),
('UsersBasicAccess','UsersCreate'),
('GuestSiteFunctionsTask','UsersCreateAccount'),
('UsersFullAccess','UsersDelete'),
('UsersFullAccess','UsersDeleteNote'),
('UsersAdminAccess','UsersFullAccess'),
('UsersMinimumRequirements','UsersIndex'),
('UsersAdminAccess','UsersInviteUsers'),
('UsersReadOnlyAccess','UsersMinimumRequirements'),
('UsersBasicAccess','UsersReadOnlyAccess'),
('AuthenticatedSiteFunctionsTask','UsersRemoveTopContact'),
('UsersMinimumRequirements','UsersSearch'),
('UsersUpdateAccess','UsersUpdate'),
('UsersFullAccess','UsersUpdateAccess'),
('UsersReadOnlyAccess','UsersView'),
('WorkflowAdminAccess','WorkflowAdmin'),
('administrator','WorkflowAdminAccess'),
('WorkflowUpdateAccess','WorkflowBasicAccess'),
('AuthenticatedSiteFunctionsTask','WorkflowCompleteStage'),
('WorkflowBasicAccess','WorkflowCreate'),
('WorkflowFullAccess','WorkflowDelete'),
('WorkflowFullAccess','WorkflowDeleteNote'),
('WorkflowAdminAccess','WorkflowFullAccess'),
('AuthenticatedSiteFunctionsTask','WorkflowGetStageMembers'),
('AuthenticatedSiteFunctionsTask','WorkflowGetStages'),
('AuthenticatedSiteFunctionsTask','WorkflowGetWorkflow'),
('WorkflowMinimumRequirements','WorkflowIndex'),
('WorkflowReadOnlyAccess','WorkflowMinimumRequirements'),
('authenticated','WorkflowReadOnlyAccess'),
('WorkflowBasicAccess','WorkflowReadOnlyAccess'),
('AuthenticatedSiteFunctionsTask','WorkflowRevertStage'),
('WorkflowMinimumRequirements','WorkflowSearch'),
('AuthenticatedSiteFunctionsTask','WorkflowStartStage'),
('WorkflowUpdateAccess','WorkflowUpdate'),
('WorkflowFullAccess','WorkflowUpdateAccess'),
('AuthenticatedSiteFunctionsTask','WorkflowUpdateStageDetails'),
('WorkflowReadOnlyAccess','WorkflowView'),
('AuthenticatedSiteFunctionsTask','WorkflowViewStage'),
('administrator','X2StudioTask'),
('admin','administrator'),
('administrator','ReportsAdminAccess')
    
") or addSqlError('Unable to create default role permissions '.mysql_error());

mysql_query('INSERT INTO x2_form_layouts (model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES 
("Contacts","Form","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":278,\"items\":[{\"name\":\"formItem_firstName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotCall\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_lastName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotEmail\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales &amp; Marketing\",\"rows\":[{\"cols\":[{\"width\":278,\"items\":[{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"180\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"183\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"180\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_interest\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealvalue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"198\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Address\",\"rows\":[{\"cols\":[{\"width\":278,\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_address2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_state\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"200\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"102\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"100\",\"width\":\"488\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Social Media\",\"rows\":[{\"cols\":[{\"width\":79,\"items\":[]},{\"width\":508,\"items\":[{\"name\":\"formItem_skype\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_linkedin\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_twitter\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_facebook\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_googleplus\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_otherUrl\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[]}]}]}]}","0","1","'.time().'","'.time().'"),
("Contacts","View","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":278,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotCall\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotEmail\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales &amp; Marketing\",\"rows\":[{\"cols\":[{\"width\":278,\"items\":[{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"180\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"183\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_interest\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealvalue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"198\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Address\",\"rows\":[{\"cols\":[{\"width\":278,\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_address2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_state\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"82\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"51\",\"width\":\"488\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Social Media\",\"rows\":[{\"cols\":[{\"width\":79,\"items\":[]},{\"width\":508,\"items\":[{\"name\":\"formItem_skype\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_otherUrl\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_googleplus\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_facebook\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_twitter\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_linkedin\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Opportunity","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Other Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"184\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"482\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Opportunity","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Other Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"184\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"482\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Product","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"1\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"2\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"3\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"4\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"5\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"6\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Product","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"205\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"3\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"4\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"203\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"6\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"203\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Accounts","Form","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"193\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Accounts","View","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Quote","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"0\",\"width\":\"0\",\"tabindex\":\"NaN\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"undefined\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"52\",\"width\":\"430\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Quote","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":true,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_id\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"0\",\"width\":\"0\",\"tabindex\":\"NaN\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Dates\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_updatedBy\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"57\",\"width\":\"431\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Calendar","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Calendar\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Permissions\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_viewPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_editPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Calendar","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Calendar\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Permissions\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_viewPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_editPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Campaign","Form","{\"version\":\"1.1\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"498\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"311\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"359\",\"width\":\"578\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Campaign","View","{\"version\":\"1.1\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"498\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"311\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"259\",\"width\":\"478\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Status\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_active\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"},{\"name\":\"formItem_complete\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'")'
) or addSqlError("Unable to create contacts layout.".mysql_error());

// 'UPDATE x2_fields SET type="phone" WHERE fieldName IN("phone", "phone2")'

mysql_query('INSERT INTO x2_fields
(modelName,			fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance) VALUES 
("Contacts",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"name",					"Full Name",			0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Contacts",		"firstName",			"First Name",			0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"High"),
("Contacts",		"lastName",				"Last Name",			0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"High"),
("Contacts",		"title",				"Title",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"company",				"Account",				0,		0,	"link",			0,			0,		"Accounts",		0,		0,			""),
("Contacts",		"phone",				"Phone",				0,		0,	"phone",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"phone2",				"Phone 2",				0,		0,	"phone",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"email",				"Email",				0,		0,	"email",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"website",				"Website",				0,		0,	"url",			0,			0,		NULL,			0,		0,			""),
("Contacts",		"twitter",				"Twitter",				0,		0,	"url",			0,			0,		"twitter",		0,		0,			""),
("Contacts",		"linkedin",				"Linkedin",				0,		0,	"url",			0,			0,		"linkedin",	 	0,		0,				""),
("Contacts",		"skype",				"Skype",				0,		0,	"url",			0,			0,		"skype",		0,		0,			""),
("Contacts",		"googleplus",			"Googleplus",			0,		0,	"url",			0,			0,		"googleplus",	0,		0,			""),
("Contacts",		"address",				"Address",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"address2",				"Address 2",			0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"city",					"City",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"state",				"State",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"zipcode",				"Postal Code",			0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"country",				"Country",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"visibility",			"Visibility",			0,		0,	"visibility",	1,			0,		NULL,			0,		0,			""),
("Contacts",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		NULL,			0,		0,			""),
("Contacts",		"backgroundInfo",		"Background Info",		0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Contacts",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Contacts",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),
("Contacts",		"leadSource",			"Lead Source",			0,		0,	"dropdown",		0,			0,		"4",			0,		0,			""),
("Contacts",		"leadDate",				"Lead Date",			0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Contacts",		"priority",				"Priority",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"rating",				"Confidence",			0,		0,	"rating",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Contacts",		"facebook",				"Facebook",				0,		0,	"url",			0,			0,		"facebook",	 	0,		0,			""),
("Contacts",		"otherUrl",				"Other",				0,		0,	"url",			0,			0,		NULL,			0,		0,			""),
("Contacts",		"leadtype",				"Lead Type",			0,		0,	"dropdown",		0,			0,		"3",			0,		0,			""),
("Contacts",		"closedate",			"Close Date",			0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Contacts",		"interest",				"Interest",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"dealvalue",			"Deal Value",			0,		0,	"currency",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"leadstatus",			"Lead Status",			0,		0,	"dropdown",		0,			0,		"5",			0,		0,			""),
("Contacts",		"doNotCall",			"Do Not Call",			0,		0,	"boolean",		0,			0,		NULL,			0,		0,			""),
("Contacts",		"doNotEmail",			"Do Not Email",			0,		0,	"boolean",		0,			0,		NULL,			0,		0,			""),

("Actions",			"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		NULL,			0,		0,			""),
("Actions",			"actionDescription",	"Description",			0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"Medium"),
("Actions",			"visibility",			"Visibility",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"associationId",		"Contact",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"associationType",		"Association Type",		0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"associationName",		"Association",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"dueDate",				"Due Date",				0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Actions",			"priority",				"Priority",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"type",					"Action Type",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Actions",			"complete",				"Complete",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"reminder",				"Reminder",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"completedBy",			"Completed By",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",			"completeDate",			"Date Completed",		0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Actions",			"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Actions",			"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),
("Actions",			"allDay",				"All Day",				0,		0,	"boolean",		0,			0,		NULL,			0,		0,			""),
("Actions",			"color",				"Color",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),

("Opportunity",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Opportunity",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Opportunity",		"accountName",			"Account",				0,		0,	"link",			0,			0,		"Accounts",	 	0,		0,			""),
("Opportunity",		"quoteAmount",			"Quote Amount",			0,		0,	"currency",		0,			0,		NULL,			0,		0,			""),
("Opportunity",		"salesStage",			"Sales Stage",			0,		0,	"dropdown",		0,			0,		"6",			0,		0,			""),
("Opportunity",		"expectedCloseDate",	"Expected Close Date",	0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Opportunity",		"probability",			"Probability",			0,		0,	"int",			0,			0,		NULL,			0,		0,			""),
("Opportunity",		"leadSource",			"Lead Source",			0,		0,	"dropdown",		0,			0,		"4",			0,		0,			""),
("Opportunity",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Opportunity",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"multiple",		0,		0,			""),
("Opportunity",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Opportunity",		"associatedContacts",	"Contacts",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Opportunity",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Opportunity",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),

("Accounts",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Accounts",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Accounts",		"website",				"Website",				0,		0,	"url",			0,			0,		NULL,			0,		0,			""),
("Accounts",		"type",					"Type",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Accounts",		"annualRevenue",		"Revenue",				0,		0,	"currency",		0,			0,		NULL,			0,		0,			""),
("Accounts",		"phone",				"Phone",				0,		0,	"phone",		0,			0,		NULL,			0,		0,			""),
("Accounts",		"tickerSymbol",			"Symbol",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Accounts",		"employees",			"Employees",			0,		0,	"int",			0,			0,		NULL,			0,		0,			""),
("Accounts",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"multiple",	 	0,		0,			""),
("Accounts",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Accounts",		"associatedContacts",	"Contacts",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Accounts",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Accounts",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Accounts",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),

("Product",		"currency",				"Currency",				0,		0,	"dropdown",		0,			0,		"2",			0,		0,			""),
("Product",		"status",				"Status",				0,		0,	"dropdown",		0,			0,		"1",			0,		0,			""),
("Product",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Product",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Product",		"type",					"Type",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Product",		"price",				"Price",				0,		0,	"currency",		0,			0,		NULL,			0,		0,			""),
("Product",		"inventory",			"Inventory",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Product",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Product",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Product",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Product",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),
("Product",		"adjustment",			"Adjustment",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Contact",		"leadscore",			"Lead Score",			0,		0,	"rating",		0,			0,		NULL,			0,		0,			""),
("Contact",		"dealstatus",			"Deal Status",			0,		0,	"dropdown",		0,			0,		"6",			0,		0,			""),

("Quote",			"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Quote",			"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Quote",			"accountName",			"Account",				0,		0,	"link",			0,			0,		"Accounts",	 	0,		0,			""),
("Quote",			"salesStage",			"Opportunity Stage",	0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Quote",			"expectedCloseDate",	"Expected Close Date",	0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Quote",			"probability",			"Probability",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Quote",			"leadSource",			"Lead Source",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Quote",			"description",			"Notes",				0,		0,	"text",			0,			0,		NULL,			0,		0,			""),
("Quote",			"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"",			 	0,		0,			""),
("Quote",			"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Quote",			"associatedContacts",	"Contacts",				0,		0,	"link",			0,			0,		"Contacts",		0,		0,			""),
("Quote",			"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Quote",			"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),
("Quote",			"status",				"Status",				0,		0,	"dropdown",		0,			0,		"7",			0,		0,			""),
("Quote",			"expirationDate",		"Expiration Date",		0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Quote",			"existingProducts",		"Existing Products",	0,		0,	"varchar",		0,			0,		NULL,			0,		1,			""),
("Quote",			"products",				"Products",				0,		0,	"varchar",		0,			0,		NULL,			0,		1,			""),
("Quote",			"locked",				"Locked",				0,		0,	"boolean",		0,			0,		NULL,			0,		0,			""),

("Calendar",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Calendar",		"viewPermission",		"View Permission",		0,		0,	"assignment",	0,			0,		"multiple",		0,		0,			""),
("Calendar",		"editPermission",		"Edit Permission",		0,		0,	"assignment",	0,			0,		"multiple",		0,		0,			""),

("Campaign",		"id",					"ID",					0,		0,	"int",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"masterId",				"Master Campaign ID",	0,		0,	"int",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"name",					"Name",					0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"High"),
("Campaign",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	1,			0,		NULL,			0,		0,			""),
("Campaign",		"listId",				"Contact List",			0,		0,	"link",			0,			0,		"X2List",		0,		0,			""),
("Campaign",		"active",				"Active",				0,		0,	"boolean",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Campaign",		"type",					"Type",					0,		0,	"dropdown",		0,			0,		8,				0,		0,			""),
("Campaign",		"cost",					"Cost",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"subject",				"Subject",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"content",				"Content",				0,		0,	"text",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"complete",				"Complete",				0,		0,	"boolean",		0,			1,		NULL,			0,		0,			""),
("Campaign",		"visibility",			"Visibility",			0,		0,	"visibility",	1,			0,		NULL,			0,		0,			""),
("Campaign",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Campaign",		"launchDate",			"Launch Date",			0,		0,	"date",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		0,			""),
("Campaign",		"updatedBy",			"Updated By",			0,		0,	"assignment",	0,			1,		NULL,			0,		0,			"")
;') or addSqlError('Unable to create fields; '.mysql_error());

// mysql_query("INSERT INTO `x2_fields` (`attributeLabel`,`custom`,`fieldName`,`linkType`,`modelName`,`modified`,`readOnly`,`relevance`,`required`,`searchable`,`type`,`isVirtual`) VALUES 
    // ('Products',0,'products',NULL,'Quotes',0,0,'',0,0,'varchar',1),
    // ('Existing Products',0,'existingProducts',NULL,'Quotes',0,0,'',0,0,'varchar',1)") or addSqlError('Unable to create fields; '.mysql_error());
// SQL to fix fields data after class name refactor


// Core modules: Actions, Contacts, Opportunities, Accounts, Products, Quotes
// Other modules: Workflow, Groups, Docs, 

// UPDATE x2_fields SET linkType = 'Action' WHERE linkType = 'Actions';
// UPDATE x2_fields SET linkType = 'Contact' WHERE linkType = 'Contacts';
// UPDATE x2_fields SET linkType = 'Account' WHERE linkType = 'Accounts';
// UPDATE x2_fields SET linkType = 'Product' WHERE linkType = 'Products';
// UPDATE x2_fields SET linkType = 'Quote' WHERE linkType = 'Quotes';

// UPDATE x2_fields SET modelName = 'Action' WHERE modelName = 'Actions';
// UPDATE x2_fields SET modelName = 'Contact' WHERE modelName = 'Contacts';
// UPDATE x2_fields SET modelName = 'Account' WHERE modelName = 'Accounts';
// UPDATE x2_fields SET modelName = 'Product' WHERE modelName = 'Products';
// UPDATE x2_fields SET modelName = 'Quote' WHERE linkType = 'Quotes';

// UPDATE x2_form_layouts SET model = 'Contact' WHERE model = 'Contacts';
// UPDATE x2_form_layouts SET model = 'Account' WHERE model = 'Accounts';
// UPDATE x2_form_layouts SET model = 'Product' WHERE model = 'Products';
// UPDATE x2_form_layouts SET model = 'Quote' WHERE model = 'Quotes';

// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'contacts';
// UPDATE x2_actions SET associationType = 'Action' where associationType = 'actions';
// UPDATE x2_actions SET associationType = 'Opportunity' where associationType = 'opportunities';
// UPDATE x2_actions SET associationType = 'Account' where associationType = 'accounts';
// UPDATE x2_actions SET associationType = 'Product' where associationType = 'products' OR associationType = 'product';
// UPDATE x2_actions SET associationType = 'Quote' where associationType = 'quotes' OR associationType = 'quotes';

// UPDATE x2_media SET associationType = 'Contact' where associationType = 'contacts';
// UPDATE x2_media SET associationType = 'Action' where associationType = 'actions';
// UPDATE x2_media SET associationType = 'Opportunity' where associationType = 'opportunities';
// UPDATE x2_media SET associationType = 'Account' where associationType = 'accounts';
// UPDATE x2_media SET associationType = 'Product' where associationType = 'products' OR associationType = 'product';
// UPDATE x2_media SET associationType = 'Quote' where associationType = 'quotes' OR associationType = 'quotes';

// other things that need done:
// find and eliminate uses of x2base->getAssociationModel()
// replace all static model calls: Contacts::model() becomes CActiveRecord::model('Contact')
// replace all Contacts:: with Contact::
mysql_query('INSERT INTO x2_widgets (name, userID, posPROF, posDASH, dispNAME, needUSER) VALUES
("OnlineUsers", 1, 1, 1, "Active Users",0),
("MessageBox",1,2,2,"Message Box",0),
("QuickContact",1,3,3,"Quick Contact",0),
("GoogleMaps",1,4,4,"Google Map",1),
("Twitter Feed",1,5,5,"Twitter Feed",1),
("ChatBox",1,6,6,"Chat",0),
("NoteBox",1,7,7,"Note Pad",0),
("ActionMenu",1,8,8,"My Actions",0),
("TagCloud",1,9,9,"Tag Cloud",0),
("DocViewer",1,10,10,"Doc Viewer",0),
("MediaBox",1,11,11,"Media Box",0),
("TimeZone",1,12,12,"Time Zone",1),
("TopSites",1,13,13,"Top Sites",0)
;') or addSqlError("Unable to create widget fields.".mysql_error());
mysql_query('INSERT INTO x2_dropdowns (id, name, options) VALUES 
(1,	"Product Status",	"{\"Active\":\"Active\",\"Inactive\":\"Inactive\"}"),
(2,	"Currency List",	"{\"USD\":\"USD\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"CAD\":\"CAD\",\"JPY\":\"JPY\",\"CNY\":\"CNY\",\"CHF\":\"CHF\",\"INR\":\"INR\",\"BRL\":\"BRL\"}"),
(3,	"Lead Type",		"{\"None\":\"None\",\"Web\":\"Web\",\"In Person\":\"In Person\",\"Phone\":\"Phone\",\"E-Mail\":\"E-Mail\"}"),
(4,	"Lead Source",		"{\"None\":\"None\",\"Google\":\"Google\",\"Facebook\":\"Facebook\",\"Walk In\":\"Walk In\"}"),
(5,	"Lead Status",		"{\"Unassigned\":\"Unassigned\",\"Assigned\":\"Assigned\",\"Accepted\":\"Accepted\",\"Working\":\"Working\",\"Dead\":\"Dead\",\"Rejected\":\"Rejected\"}"),
(6,	"Sales Stage",		"{\"Working\":\"Working\",\"Won\":\"Won\",\"Lost\":\"Lost\"}"),
(7,	"Quote Status",		"{\"Draft\":\"Draft\",\"Presented\":\"Presented\",\"Issued\":\"Issued\",\"Won\":\"Won\"}"),
(8,	"Campaign Type",	"{\"Email\":\"Email\",\"Call List\":\"Call List\",\"Physical Mail\":\"Physical Mail\"}")
;') or addSqlError('Unable to create dropdown fields.'.mysql_error());

mysql_query("INSERT INTO  x2_dropdowns (name, options) VALUES 
	('Quote Status', 	'". json_encode(array("Draft"=>"Draft", "Presented"=>"Presented", "Issued"=>"Issued", "Won"=>"Won"))."')
;")
or addSqlError('Unable to create dropdown fields.'.mysql_error());

mysql_query("INSERT INTO x2_dashboard_settings(userID) VALUES (1);") or addSqlError('Unable to initialize dashboard settings. '.mysql_error());
// if(!empty($sqlError)) return $sqlError;
//UNSIGNED


$adminPassword = md5($adminPassword); 
$adminEmail = mysql_escape_string($adminEmail);

mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin) 
        VALUES ('web','admin','admin','$adminPassword','$adminEmail' ,'1', '0')") 
or addSqlError("Error inserting admin information.");
mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin) 
        VALUES ('API','User','api','$apiKey','$adminEmail' ,'0', '0')") 
or addSqlError("Error inserting admin information.");
mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Web Admin', 'admin', '831-555-5555', '$adminEmail','1')") or addSqlError("Error inserting admin information");
mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('API User', 'api', '831-555-5555', '$adminEmail','0')") or addSqlError("Error inserting admin information");

mysql_query("INSERT INTO x2_social (type, data) VALUES ('motd', 'Please enter a message of the day!')") or addSqlError("Unable to set starting MOTD.");

mysql_query('INSERT INTO x2_admin (timeout,webLeadEmail,currency,installDate,updateDate,quoteStrictLock,unique_id,edition) VALUES (
	"3600",
	"'.$adminEmail.'",
	"'.$currency.'",
	"'.time().'",
	0,
	0,
	"'.$unique_id.'",
	"'.$edition.'"
)') or addSqlError('Unable to input admin config'.mysql_error());

$backgrounds = array(
	'santacruznight_blur.jpg',
	'santa_cruz.jpg',
	'santa_cruz_blur.jpg',
	'devilsgolfb.jpg',
	'eastroad6b.jpg',
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


// populate timezone tables
$fh = fopen('timezoneData.sql','r') or addError('Couldn\'t load timezone data.');
while(($line = fgets($fh)) !== false)
	mysql_query($line) or addSqlError('Error inserting timezone data.'.mysql_error());;
fclose($fh);


if($dummy_data){

	mysql_query("INSERT INTO x2_workflows (id, name) VALUES (1,'General Sales')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, name) VALUES (1,1,'Lead')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, name) VALUES (2,1,'Suspect')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, name) VALUES (3,1,'Prospect')") or addSqlError("Error inserting workflow data.");
	mysql_query("INSERT INTO x2_workflow_stages (id, workflowId, name) VALUES (4,1,'Customer')") or addSqlError("Error inserting workflow data.");

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
// set default widget order
	mysql_query('UPDATE x2_profile SET widgets="0:1:1:1:1:0:0:0:0:0:0:0:0", 
	widgetOrder="OnlineUsers:TimeZone:GoogleMaps:TagCloud:TwitterFeed:MessageBox:ChatBox:QuickContact:NoteBox:ActionMenu:MediaBox:DocViewer:TopSites"') or addSqlError('Error setting default widget order');
		
/**
 * Look for additional initialization files
 */
foreach ($editions as $ed) // Add editional prefixes as necessary
	if (file_exists("initialize_$ed.php"))
		include("initialize_$ed.php");

mysql_close($con);

// }

if(!empty($sqlError))
	$errors[] = 'MySQL Error: '.$sqlError;
outputErrors();



$GDSupport = function_exists('gd_info')? '1':'0';
$browser = urlencode(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'');
$phpVersion = urlencode(phpversion());
$x2Version = urlencode($x2Version);
$timezone = urlencode($timezone);
$dbType = urlencode('MySQL');
$stats = "language=$lang&currency=$currency&x2_version=$x2Version&dummy_data=$dummy_data&php_version=$phpVersion&db_type=$dbType&GD_support=$GDSupport&user_agent=$browser&timezone=$timezone&unique_id=$unique_id";

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
<title><?php echo installer_t('Installation Complete'); ?></title>
<?php $themeURL = 'themes/x2engine'; ?>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/install.css" />
<link rel="icon" href="images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<style type="text/css">
body {
	background-color:#fff;
	padding-top:50px;
}
</style>
<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/backgroundImage.js"></script>
</head>
<body>
<!--<img id="bg" src="uploads/defaultBg.jpg" alt="">-->
<div id="installer-box" style="padding-top:20px;">
	<h1><?php echo installer_t('Installation Complete!'); ?></h1>
	<div id="install-form" class="wide form">
		<ul>
			<li><?php echo installer_t('Able to connect to database'); ?></li>
			<li><?php echo installer_t('Dropped old X2Engine tables (if any)'); ?></li>
			<li><?php echo installer_t('Created new tables for X2Engine'); ?></li>
			<li><?php echo installer_t('Created login for admin account'); ?></li>
			<li><?php echo installer_t('Created config file'); ?></li>
		</ul>
		<h2><?php echo installer_t('Next Steps'); ?></h2>
		<ul>
			<li><?php echo installer_t('Log in to app'); ?></li>
			<li><?php echo installer_t('Create new users'); ?></li>
			<li><?php echo installer_t('Set up Cron Job to deal with action reminders (see readme)'); ?></li>
			<li><?php echo installer_t('Set location'); ?></li>
			<li><?php echo installer_t('Explore the app'); ?></li>
		</ul>
		<h3><a class="x2-button" href="index.php"><?php echo installer_t('Click here to log in to X2Engine'); ?></a></h3><br />
		<?php echo installer_t('X2Engine successfully installed on your web server!  You may now log in with username "admin" and the password you provided during the install.'); ?><br /><br />
	</div>
<a href="http://www.x2engine.com"><?php echo installer_t('For help or more information - X2Engine.com'); ?></a><br /><br />
<div id="footer">
	<div class="hr"></div>
	<!--<img src="images/x2engine_big.png">-->
	Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2engine.com">X2Engine Inc.</a><br />
	<?php echo installer_t('All Rights Reserved.'); ?>
	<img style="height:0;width:0" src="http://x2planet.com/installs/registry/activity?<?php echo $stats; ?>">	
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
if(file_exists('initialize_pro.php'))
	unlink('initialize_pro.php');
unlink(__FILE__);
?>
