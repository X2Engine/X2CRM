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
$x2Version = '1.6.1';
$buildDate = 1343253555;

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

$contents=file_get_contents('webLeadConfig.php');
$contents=preg_replace('/\$url=\"\";/',"\$url='$webLeadUrl'",$contents);
file_put_contents('leadCapture.php',$contents);

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
\$updaterVersion='1.2';
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
	x2_lists,
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
	x2_phone_numbers,
	x2_relationships,
	x2_notifications,
	x2_quotes_products,
	x2_criteria,
	x2_lead_routing,
	x2_sessions,
	x2_workflows,
	x2_workflow_stages,
	x2_role_to_workflow,
	x2_fields,
   	x2_urls,
	x2_form_layouts,
	x2_roles,
	x2_role_to_user,
	x2_role_to_permission,
	x2_role_exceptions,
	x2_dropdowns,
	x2_groups,
	x2_group_to_user,
	x2_users,
	x2_contacts,
	x2_subscribe_contacts,
	x2_actions,
	x2_sales,
	x2_quotes,
	x2_products,
	x2_projects,
	x2_marketing,
	x2_campaigns,
	x2_calendars,
    x2_modules,
    x2_calendar_permissions,
    x2_temp_files
') or addSqlError('Unable to delete exsting tables.'.mysql_error());

// visibility check MySQL procedure
// example: "... select * from x2_contacts where x2_checkViewPermission(visibility,assignedTo,'jvaleria') > 0 ..."
// DROP function IF EXISTS `x2_func_strSplit`;
// CREATE FUNCTION `x2_func_strSplit`(x varchar(255), delim varchar(12), pos int) RETURNS varchar(255)
// begin
  // return replace(substring(substring_index(x, delim, pos), length(substring_index(x, delim, pos - 1)) + 1), delim, '');
// end;

mysql_query('DROP FUNCTION IF EXISTS `x2_checkViewPermission`;') or addSqlError('Unable to drop function x2_checkViewPermission.'.mysql_error());
mysql_query('CREATE FUNCTION `x2_checkViewPermission` (mode INT,assignedTo VARCHAR(20),user VARCHAR(20)) RETURNS TINYINT DETERMINISTIC 
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
		IF mode = 0 AND STRCMP(assignedTo, user) = 0 THEN	-- private, must be assigned to user
			RETURN 1;
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
END;') or addSqlError('Unable to create function x2_checkOwnership.'.mysql_error());

/* CREATE FUNCTION `x2_checkViewPermission`(mode int, user varchar(255), acl varchar(255)) RETURNS int(11)
begin
	declare pos int default 1;
	declare retv int default 0;
	declare memb varchar(255);

	-- clean up 2 char sep token in acl
	set acl = replace(acl,', ',',');

	-- check for public
	if mode = 1 then
		return 1;
	end if;

	-- check for admin
	if strcmp(user, 'admin') = 0 then
		return 1;
	end if;

	-- check for private and single assigment to user
	if mode = 0 and strcmp(acl, user) = 0 then
		return 1;
	end if;

  -- check for private 
	if mode = 0 then
		if instr(acl,',') = 0 and (0 + acl) > 0 then -- single assigment to group
			select count(*) into retv from x2_group_to_user where groupId = (0 + acl) and username = user; 
			return retv;
		else -- multiple assignment, fetch acl tokens
			set memb = x2_func_strSplit(acl,',',pos);
			while length(memb) > 0 do
				if (0 + memb) > 0 then -- group
					select count(*) into retv from x2_group_to_user where groupId = (0 + memb) and username = user; 
					if retv > 0 then
						return 1;
					end if;
				else -- user
					if strcmp(acl, user) = 0 then
						return 1;
					end if;
				end if;

				set pos = pos + 1;
				set memb = x2_func_strSplit(acl,',',pos);
			end while;
		end if;
	end if;

	-- check for shared and single assigment to user
	if mode = 2 and instr(acl,',') = 0 then
		if (0 + acl) <= 0 then
			select count(*) into retv from x2_group_to_user a, x2_group_to_user b where a.username = acl and b.username = user and b.groupId = a.groupId; 
			return retv;
		else  -- if user then not defined
			return 0;  
		end if;
	else  -- if multiple assignment then not defined
		return 0;
	end if;

	-- default is false
	return 0;
end; */
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
	INDEX (associationType,associationId)
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_actions.'.mysql_error());

 mysql_query('CREATE TABLE x2_sales(
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
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_sales.'.mysql_error());

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
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_sales.'.mysql_error());

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
	createDate				BIGINT	 		NOT NULL,
	launchDate				BIGINT	 		NOT NULL,
	lastUpdated				BIGINT	 		NOT NULL,
	updatedBy				VARCHAR(20),
	
	PRIMARY KEY (id),
	FOREIGN KEY (masterId) REFERENCES x2_campaigns(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci') or addSqlError('Unable to create table x2_campaigns.'.mysql_error());

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
	contactId				INT				UNSIGNED NOT NULL,
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
	widgets					VARCHAR(255)	DEFAULT "1:1:1:1:1:1:0:1:1",
	widgetOrder				VARCHAR(512)	DEFAULT "OnlineUsers:ChatBox:MessageBox:QuickContact:GoogleMaps:TwitterFeed:NoteBox:ActionMenu:TagCloud",
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
	createDate				BIGINT
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
	accounts				INT,
	sales					INT,
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
	workflowBackdateReassignment	TINYINT		NOT NULL DEFAULT 1
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
(name,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable) VALUES 
("calendar",	"Calendar",		1,			0,				0,			0,			0,			0,		0),
("contacts",	"Contacts",		1,			1,				1,			1,			0,			0,		0),
("workflow",	"Workflow",		1,			3,				0,			0,			0,			0,		0),
("actions",		"Actions",		1,			2,				1,			0,			0,			0,		0),
("marketing",	"Marketing",	1,			4,				0,			1,			0,			0,		0),
("docs",		"Docs",			1,			5,				0,			0,			0,			0,		0),
("accounts",	"Accounts",		1,			6,				1,			1,			0,			0,		0),
("sales",		"Sales",		1,			7,				1,			1,			0,			0,		0),
("products",	"Products",		1,			8,				1,			1,			0,			0,		0),
("quotes",		"Quotes",		1,			9,				1,			1,			0,			0,		0),
("groups",		"Groups",		1,			10,				0,			0,			0,			0,		0),
("charts",		"Charts",		1,			11,				0,			0,			0,			0,		0),
("users",		"Users",		1,			12,				0,			0,			1,			0,		0)

'
// ("dashboard",   "Widget Dashboard",	1,       13,             0,          1,          0,          0,      0)'
) or addSqlError("Unable to initialize modules ".mysql_error());
mysql_query('INSERT INTO x2_form_layouts (model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES 
("Contacts","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":286,\"items\":[{\"name\":\"formItem_firstName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotCall\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]},{\"width\":301,\"items\":[{\"name\":\"formItem_lastName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotEmail\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales &amp; Marketing\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"180\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"183\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"180\",\"tabindex\":\"0\"}]},{\"width\":302,\"items\":[{\"name\":\"formItem_interest\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealvalue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"198\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Address\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_address2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":302,\"items\":[{\"name\":\"formItem_state\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"200\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"102\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Background Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"100\",\"width\":\"488\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Social Media\",\"rows\":[{\"cols\":[{\"width\":79,\"items\":[]},{\"width\":508,\"items\":[{\"name\":\"formItem_skype\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_linkedin\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_twitter\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_facebook\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_googleplus\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_otherUrl\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[]}]}]}]}","0","1","'.time().'","'.time().'"),
("Contacts","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":286,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotCall\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]},{\"width\":301,\"items\":[{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_doNotEmail\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales &amp; Marketing\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"180\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"183\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"}]},{\"width\":302,\"items\":[{\"name\":\"formItem_interest\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealvalue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"198\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Address\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_address2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":302,\"items\":[{\"name\":\"formItem_state\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"82\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Background Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"51\",\"width\":\"488\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Social Media\",\"rows\":[{\"cols\":[{\"width\":79,\"items\":[]},{\"width\":508,\"items\":[{\"name\":\"formItem_skype\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_otherUrl\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_googleplus\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_facebook\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_twitter\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_linkedin\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"top\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"157\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Sales","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Other Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"184\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"482\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Sales","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Other Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"184\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"482\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Products","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"1\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"2\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"3\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"4\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"5\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"6\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Products","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"205\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"3\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"4\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"203\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"6\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"203\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Accounts","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Additional Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Accounts","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Additional Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Quotes","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"0\",\"width\":\"0\",\"tabindex\":\"NaN\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"undefined\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"52\",\"width\":\"430\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Quotes","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":true,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_id\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"0\",\"width\":\"0\",\"tabindex\":\"NaN\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Dates\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_updatedBy\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"57\",\"width\":\"431\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Calendar","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Calendar\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Permissions\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_viewPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_editPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Calendar","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Calendar\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Permissions\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_viewPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_editPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'"),
("Campaign","Form","{\"version\":\"1.1\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"498\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"311\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"359\",\"width\":\"578\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
("Campaign","View","{\"version\":\"1.1\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"498\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"311\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"259\",\"width\":\"578\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Status\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_active\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"},{\"name\":\"formItem_complete\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'")'
) or addSqlError("Unable to create contacts layout.".mysql_error());

// 'UPDATE x2_fields SET type="phone" WHERE fieldName IN("phone", "phone2")'

mysql_query('INSERT INTO x2_fields 
(modelName,		fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,	linkType,	searchable,	 relevance) VALUES 
("Contacts",	"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Contacts",	"name",					"Full Name",			0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Contacts",	"firstName",			"First Name",			0,		0,	"varchar",		1,			0,		NULL,			1,		"High"),
("Contacts",	"lastName",				"Last Name",			0,		0,	"varchar",		1,			0,		NULL,			1,		"High"),
("Contacts",	"title",				"Title",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Contacts",	"company",				"Account",				0,		0,	"link",			0,			0,		"Accounts",		0,		""),
("Contacts",	"phone",				"Phone",				0,		0,	"phone",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"phone2",				"Phone 2",				0,		0,	"phone",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"email",				"Email",				0,		0,	"email",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"website",				"Website",				0,		0,	"url",			0,			0,		NULL,			0,		""),
("Contacts",	"twitter",				"Twitter",				0,		0,	"url",			0,			0,		"twitter",		0,		""),
("Contacts",	"linkedin",				"Linkedin",				0,		0,	"url",			0,			0,		"linkedin",	 	0,			""),
("Contacts",	"skype",				"Skype",				0,		0,	"url",			0,			0,		"skype",		0,		""),
("Contacts",	"googleplus",			"Googleplus",			0,		0,	"url",			0,			0,		"googleplus",	0,		""),
("Contacts",	"address",				"Address",				0,		0,	"varchar",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"address2",				"Address 2",			0,		0,	"varchar",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"city",					"City",					0,		0,	"varchar",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"state",				"State",				0,		0,	"varchar",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"zipcode",				"Postal Code",			0,		0,	"varchar",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"country",				"Country",				0,		0,	"varchar",		0,			0,		NULL,			1,		"Medium"),
("Contacts",	"visibility",			"Visibility",			0,		0,	"visibility",	1,			0,		NULL,			0,		""),
("Contacts",	"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		NULL,			0,		""),
("Contacts",	"backgroundInfo",		"Background Info",		0,		0,	"text",			0,			0,		NULL,			1,		"Medium"),
("Contacts",	"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Contacts",	"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		""),
("Contacts",	"leadSource",			"Lead Source",			0,		0,	"dropdown",		0,			0,		"4",			0,		""),
("Contacts",	"leadDate",				"Lead Date",			0,		0,	"date",			0,			0,		NULL,			0,		""),
("Contacts",	"priority",				"Priority",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Contacts",	"rating",				"Confidence",			0,		0,	"rating",		0,			0,		NULL,			0,		""),
("Contacts",	"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Contacts",	"facebook",				"Facebook",				0,		0,	"url",			0,			0,		"facebook",	 	0,		""),
("Contacts",	"otherUrl",				"Other",				0,		0,	"url",			0,			0,		NULL,			0,		""),
("Contacts",	"leadtype",				"Lead Type",			0,		0,	"dropdown",		0,			0,		"3",			0,		""),
("Contacts",	"closedate",			"Close Date",			0,		0,	"date",			0,			0,		NULL,			0,		""),
("Contacts",	"interest",				"Interest",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Contacts",	"dealvalue",			"Deal Value",			0,		0,	"currency",		0,			0,		NULL,			0,		""),
("Contacts",	"leadstatus",			"Lead Status",			0,		0,	"dropdown",		0,			0,		"5",			0,		""),
("Contacts",	"doNotCall",			"Do Not Call",			0,		0,	"boolean",		0,			0,		NULL,			0,		""),
("Contacts",	"doNotEmail",			"Do Not Email",			0,		0,	"boolean",		0,			0,		NULL,			0,		""),

("Actions",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		NULL,			0,		""),
("Actions",		"actionDescription",	"Description",			0,		0,	"varchar",		1,			0,		NULL,			1,		"Medium"),
("Actions",		"visibility",			"Visibility",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"associationId",		"Contact",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"associationType",		"Association Type",		0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"associationName",		"Association",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"dueDate",				"Due Date",				0,		0,	"date",			0,			0,		NULL,			0,		""),
("Actions",		"priority",				"Priority",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"type",					"Action Type",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Actions",		"complete",				"Complete",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"reminder",				"Reminder",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"completedBy",			"Completed By",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Actions",		"completeDate",			"Date Completed",		0,		0,	"date",			0,			0,		NULL,			0,		""),
("Actions",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Actions",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		""),
("Actions",		"allDay",				"All Day",				0,		0,	"boolean",		0,			0,		NULL,			0,		""),
("Actions",		"color",				"Color",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),

("Sales",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Sales",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Sales",		"accountName",			"Account",				0,		0,	"link",			0,			0,		"Accounts",	 	0,		""),
("Sales",		"quoteAmount",			"Quote Amount",			0,		0,	"currency",		0,			0,		NULL,			0,		""),
("Sales",		"salesStage",			"Sales Stage",			0,		0,	"dropdown",		0,			0,		"6",			0,		""),
("Sales",		"expectedCloseDate",	"Expected Close Date",	0,		0,	"date",			0,			0,		NULL,			0,		""),
("Sales",		"probability",			"Probability",			0,		0,	"int",			0,			0,		NULL,			0,		""),
("Sales",		"leadSource",			"Lead Source",			0,		0,	"dropdown",		0,			0,		"4",			0,		""),
("Sales",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		"Medium"),
("Sales",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"multiple",		0,		""),
("Sales",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Sales",		"associatedContacts",	"Contacts",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Sales",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Sales",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		""),

("Accounts",	"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Accounts",	"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Accounts",	"website",				"Website",				0,		0,	"url",			0,			0,		NULL,			0,		""),
("Accounts",	"type",					"Type",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Accounts",	"annualRevenue",		"Revenue",				0,		0,	"currency",		0,			0,		NULL,			0,		""),
("Accounts",	"phone",				"Phone",				0,		0,	"phone",		0,			0,		NULL,			0,		""),
("Accounts",	"tickerSymbol",			"Symbol",				0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Accounts",	"employees",			"Employees",			0,		0,	"int",			0,			0,		NULL,			0,		""),
("Accounts",	"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"multiple",	 	0,		""),
("Accounts",	"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Accounts",	"associatedContacts",	"Contacts",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Accounts",	"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		"Medium"),
("Accounts",	"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Accounts",	"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		""),

("Products",	"currency",				"Currency",				0,		0,	"dropdown",		0,			0,		"2",			0,		""),
("Products",	"status",				"Status",				0,		0,	"dropdown",		0,			0,		"1",			0,		""),
("Products",	"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Products",	"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Products",	"type",					"Type",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Products",	"price",				"Price",				0,		0,	"currency",		0,			0,		NULL,			0,		""),
("Products",	"inventory",			"Inventory",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Products",	"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		"Medium"),
("Products",	"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Products",	"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Products",	"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		""),
("Products",	"adjustment",			"Adjustment",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Contacts",	"leadscore",			"Lead Score",			0,		0,	"rating",		0,			0,		NULL,			0,		""),
("Contacts",	"dealstatus",			"Deal Status",			0,		0,	"dropdown",		0,			0,		"6",			0,		""),

("Quotes",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Quotes",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Quotes",		"accountName",			"Account",				0,		0,	"link",			0,			0,		"Accounts",	 	0,		""),
("Quotes",		"existingProducts",		"Existing Products",	0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Quotes",		"salesStage",			"Sales Stage",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Quotes",		"expectedCloseDate",	"Expected Close Date",	0,		0,	"date",			0,			0,		NULL,			0,		""),
("Quotes",		"probability",			"Probability",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Quotes",		"leadSource",			"Lead Source",			0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Quotes",		"description",			"Notes",				0,		0,	"text",			0,			0,		NULL,			0,		""),
("Quotes",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"",			 	0,		""),
("Quotes",		"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Quotes",		"associatedContacts",	"Contacts",				0,		0,	"link",			0,			0,		"Contacts",		0,		""),
("Quotes",		"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Quotes",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		""),
("Quotes",		"status",				"Status",				0,		0,	"dropdown",		0,			0,		"7",			0,		""),
("Quotes",		"expirationDate",		"Expiration Date",		0,		0,	"date",			0,			0,		NULL,			0,		""),
("Quotes",		"products",				"Products",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Quotes",		"locked",				"Locked",				0,		0,	"boolean",		0,			0,		NULL,			0,		""),

("Calendar",	"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		"High"),
("Calendar",	"viewPermission",		"View Permission",		0,		0,	"assignment",	0,			0,		"multiple",		0,		""),
("Calendar",	"editPermission",		"Edit Permission",		0,		0,	"assignment",	0,			0,		"multiple",		0,		""),

("Campaign",	"id",					"ID",					0,		0,	"int",			0,			0,		NULL,			0,		""),
("Campaign",	"masterId",				"Master Campaign ID",	0,		0,	"int",			0,			0,		NULL,			0,		""),
("Campaign",	"name",					"Name",					0,		0,	"varchar",		1,			0,		NULL,			1,		"High"),
("Campaign",	"assignedTo",			"Assigned To",			0,		0,	"assignment",	1,			0,		NULL,			0,		""),
("Campaign",	"listId",				"Contact List",			0,		0,	"link",			0,			0,		"X2List",	0,		""),
("Campaign",	"active",				"Active",				0,		0,	"boolean",		0,			0,		NULL,			0,		""),
("Campaign",	"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		"Medium"),
("Campaign",	"type",					"Type",					0,		0,	"dropdown",		0,			0,		8,				0,		""),
("Campaign",	"cost",					"Cost",					0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Campaign",	"subject",				"Subject",				0,		0,	"varchar",		0,			0,		NULL,			0,		""),
("Campaign",	"content",				"Content",				0,		0,	"text",			0,			0,		NULL,			0,		""),
("Campaign",	"complete",				"Complete",				0,		0,	"boolean",		0,			1,		NULL,			0,		""),
("Campaign",	"createDate",			"Create Date",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Campaign",	"launchDate",			"Launch Date",			0,		0,	"date",			0,			0,		NULL,			0,		""),
("Campaign",	"lastUpdated",			"Last Updated",			0,		0,	"date",			0,			1,		NULL,			0,		""),
("Campaign",	"updatedBy",			"Updated By",			0,		0,	"assignment",	0,			1,		NULL,			0,		"")
;') or addSqlError('Unable to create fields'.mysql_error());

// SQL to fix fields data after class name refactor


// Core modules: Actions, Contacts, Sales, Accounts, Products, Quotes
// Other modules: Workflow, Groups, Docs, 

// UPDATE x2_fields SET linkType = 'Action' WHERE linkType = 'Actions';
// UPDATE x2_fields SET linkType = 'Contact' WHERE linkType = 'Contacts';
// UPDATE x2_fields SET linkType = 'Sale' WHERE linkType = 'Sales';
// UPDATE x2_fields SET linkType = 'Account' WHERE linkType = 'Accounts';
// UPDATE x2_fields SET linkType = 'Product' WHERE linkType = 'Products';
// UPDATE x2_fields SET linkType = 'Quote' WHERE linkType = 'Quotes';

// UPDATE x2_fields SET modelName = 'Action' WHERE modelName = 'Actions';
// UPDATE x2_fields SET modelName = 'Contact' WHERE modelName = 'Contacts';
// UPDATE x2_fields SET modelName = 'Sale' WHERE modelName = 'Sales';
// UPDATE x2_fields SET modelName = 'Account' WHERE modelName = 'Accounts';
// UPDATE x2_fields SET modelName = 'Product' WHERE modelName = 'Products';
// UPDATE x2_fields SET modelName = 'Quote' WHERE linkType = 'Quotes';

// UPDATE x2_form_layouts SET model = 'Contact' WHERE model = 'Contacts';
// UPDATE x2_form_layouts SET model = 'Sale' WHERE model = 'Sales';
// UPDATE x2_form_layouts SET model = 'Account' WHERE model = 'Accounts';
// UPDATE x2_form_layouts SET model = 'Product' WHERE model = 'Products';
// UPDATE x2_form_layouts SET model = 'Quote' WHERE model = 'Quotes';

// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'contacts';
// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'actions';
// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'sales';
// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'accounts';
// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'products' OR associationType = 'product';
// UPDATE x2_actions SET associationType = 'Contact' where associationType = 'quotes' OR associationType = 'quotes';

// UPDATE x2_media SET associationType = 'Contact' where associationType = 'contacts';
// UPDATE x2_media SET associationType = 'Contact' where associationType = 'actions';
// UPDATE x2_media SET associationType = 'Contact' where associationType = 'sales';
// UPDATE x2_media SET associationType = 'Contact' where associationType = 'accounts';
// UPDATE x2_media SET associationType = 'Contact' where associationType = 'products' OR associationType = 'product';
// UPDATE x2_media SET associationType = 'Contact' where associationType = 'quotes' OR associationType = 'quotes';

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
("TimeZone",1,11,11,"Time Zone",1),
("TopSites",1,12,12,"Top Sites",0)
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

mysql_query("INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin) VALUES ('web','admin','admin','$adminPassword',
			'$adminEmail' ,'1', '0')") or addSqlError("Error inserting admin information.");
mysql_query("INSERT INTO x2_profile (fullName, username, officePhone, emailAddress, status) 
		VALUES ('Web Admin', 'admin', '831-555-5555', '$adminEmail','1')") or addSqlError("Error inserting dummy data");
mysql_query("INSERT INTO x2_social (type, data) VALUES ('motd', 'Please enter a message of the day!')") or addSqlError("Unable to set starting MOTD.");

mysql_query('INSERT INTO x2_admin (accounts,sales,timeout,webLeadEmail,currency,installDate,updateDate,quoteStrictLock) VALUES (
	"0",
	"1",
	"3600",
	"'.$adminEmail.'",
	"'.$currency.'",
	"'.time().'",
	0,
	0
)') or addSqlError('Unable to input admin config');

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

if($dummyData){

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
mysql_close($con);

// }

if(!empty($sqlError))
	$errors[] = 'MySQL Error: '.$sqlError;
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
<title><?php echo installer_t('Installation Complete'); ?></title>
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
<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/backgroundImage.js"></script>
</head>
<body>
<img id="bg" src="uploads/defaultBg.jpg" alt="">
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
