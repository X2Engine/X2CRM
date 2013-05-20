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
DROP TABLE IF EXISTS x2_admin;
/*&*/
CREATE TABLE x2_admin(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	timeout					INT,
	webLeadEmail			VARCHAR(255),
	webTrackerCooldown		INT				DEFAULT 60,
	enableWebTracker		TINYINT			DEFAULT 1,
	currency				VARCHAR(3)		NULL,
	chatPollTime			INT				DEFAULT 2000,
	ignoreUpdates			TINYINT			DEFAULT 0,
	rrId					INT				DEFAULT 0,
	leadDistribution		VARCHAR(255),
	onlineOnly				TINYINT,
	emailFromName			VARCHAR(255)	NOT NULL DEFAULT "X2CRM",
	emailFromAddr			VARCHAR(255)	NOT NULL DEFAULT '',
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
	edition			VARCHAR(10) NOT NULL DEFAULT "opensource",
	serviceCaseFromEmailAddress	TEXT,
	serviceCaseFromEmailName	TEXT,
	serviceCaseEmailSubject		TEXT,
	serviceCaseEmailMessage		TEXT,
	srrId						INT				DEFAULT 0,
	sgrrId						INT				DEFAULT 0,
	serviceDistribution			varchar(255),
	serviceOnlineOnly			TINYINT,
    corporateAddress            TEXT,
    eventDeletionTime           INT,
    eventDeletionTypes          TEXT,
    properCaseNames             INT             DEFAULT 1,
    contactNameFormat           VARCHAR(255),
	gaTracking_public			VARCHAR(20) NULL,
	gaTracking_internal			VARCHAR(20) NULL,
    sessionLog                  TINYINT         DEFAULT 0,
    userActionBackdating        TINYINT         DEFAULT 0,
	emailDropbox_alias			VARCHAR(50) DEFAULT NULL,
	emailDropbox_createContact	TINYINT	DEFAULT 1,
	emailDropbox_zapLineBreaks	TINYINT DEFAULT 0,
	emailDropbox_emptyContact	TINYINT DEFAULT 1,
	emailDropbox_logging		TINYINT DEFAULT 0
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_changelog;
/*&*/
CREATE TABLE x2_changelog(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
    recordName              VARCHAR(255),
	changedBy				VARCHAR(50)		NOT NULL,
	changed					TEXT			NULL,
    fieldName				VARCHAR(255),
    oldValue				TEXT,
    newValue				TEXT,
    diff					TINYINT			NOT NULL DEFAULT 0,
	timestamp				INT				NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_criteria;
/*&*/
CREATE TABLE x2_criteria(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	modelType				VARCHAR(100),
	modelField				VARCHAR(250),
	modelValue				TEXT,
	comparisonOperator		VARCHAR(10),
	users					TEXT,
	type					VARCHAR(250)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_dropdowns;
/*&*/
CREATE TABLE x2_dropdowns (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	options					TEXT
) AUTO_INCREMENT=1000 COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_fields;
/*&*/
CREATE TABLE x2_fields (
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
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_form_layouts;
/*&*/
CREATE TABLE x2_form_layouts (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	model					VARCHAR(250)	NOT NULL,
	version					VARCHAR(250)	NOT NULL,
	layout					TEXT,
	defaultView				TINYINT			NOT NULL DEFAULT 0,
	defaultForm				TINYINT			NOT NULL DEFAULT 0,
	createDate				BIGINT,
	lastUpdated				BIGINT
) AUTO_INCREMENT=1000 COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_lead_routing;
/*&*/
CREATE TABLE x2_lead_routing(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	criteria				TEXT,
	users					TEXT,
	priority				INT,
	rrId					INT				DEFAULT 0,
	groupType				INT
) COLLATE = utf8_general_ci;
/*&*/
/* These have foreign key constraints in them and should thus be dropped first: */
DROP TABLE IF EXISTS x2_list_criteria,x2_list_items;
/*&*/
DROP TABLE IF EXISTS x2_lists;
/*&*/
CREATE TABLE x2_lists (
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
	lastUpdated				BIGINT			NOT NULL,
	INDEX(assignedTo),
	INDEX(type)
) ENGINE InnoDB COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_list_criteria (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	listId					INT				UNSIGNED NOT NULL,
	type					VARCHAR(20)		NULL,
	attribute				VARCHAR(40)		NULL,
	comparison				VARCHAR(10)		NULL,
	value					VARCHAR(100)	NOT NULL,
	INDEX (listId),
	FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_list_items (
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
	INDEX (uniqueId),
	FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_modules;
/*&*/
CREATE TABLE x2_modules (
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
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_notifications;
/*&*/
CREATE TABLE x2_notifications(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(20),
	comparison				VARCHAR(20),
	value					VARCHAR(250),
	text					TEXT,
	modelType				VARCHAR(250),
	modelId					INT				UNSIGNED,
	fieldName				VARCHAR(250),
	user					VARCHAR(20),
	createdBy				VARCHAR(20),
	viewed					TINYINT			DEFAULT 0,
	createDate				BIGINT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_events;
/*&*/
CREATE TABLE x2_events(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(250),
    subtype                 VARCHAR(250),
    level                   INT,
    visibility              TINYINT             DEFAULT 1,
    text                    TEXT,
	associationType			VARCHAR(250),
	associationId			INT,
	user					VARCHAR(250),
	timestamp				BIGINT,
    lastUpdated             BIGINT,
    important               TINYINT             DEFAULT 0,
    sticky                  TINYINT             DEFAULT 0,
    color                   VARCHAR(10),
    fontColor               VARCHAR(10),
    linkColor               VARCHAR(10)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_events_data;
/*&*/
CREATE TABLE x2_events_data(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(250),
    count                   INT,
	user					VARCHAR(250)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_phone_numbers;
/*&*/
CREATE TABLE x2_phone_numbers(
	modelId					INT				UNSIGNED NOT NULL,
	modelType				VARCHAR(100)	NOT NULL,
	number					VARCHAR(40)		NOT NULL,
    fieldName               VARCHAR(255),
	INDEX (modelType,modelId),
	INDEX (number)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_profile;
/*&*/
CREATE TABLE x2_profile(
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
	language				VARCHAR(40)		DEFAULT "",
	timeZone				VARCHAR(100)	DEFAULT "",
	resultsPerPage			INT DEFAULT		20,
	widgets					VARCHAR(255),
	widgetOrder				TEXT,
	widgetSettings			TEXT,
	activityFeedOrder       TINYINT         DEFAULT 0,
    backgroundColor			VARCHAR(6)		NULL,
	menuBgColor				VARCHAR(6)		NULL,
	menuTextColor			VARCHAR(6)		NULL,
	pageHeaderBgColor		VARCHAR(6)		NULL,
	pageHeaderTextColor		VARCHAR(6)		NULL,
    activityFeedWidgetBgColor       VARCHAR(6)              NULL,
    activityFeedWidgetTextColor     VARCHAR(6)              NULL,
	backgroundImg			VARCHAR(100)	NULL DEFAULT "",
    loginSound              VARCHAR(100)    NULL DEFAULT "",
    notificationSound       VARCHAR(100)    NULL DEFAULT "X2_Notification.mp3",
	backgroundTiling		VARCHAR(10)		NULL DEFAULT "",
	pageOpacity				INT				NULL,
	startPage				VARCHAR(30)		NULL,
	showSocialMedia			TINYINT			NOT NULL DEFAULT 0,
	showDetailView			TINYINT			NOT NULL DEFAULT 1,
	gridviewSettings		TEXT,
	formSettings			TEXT,
	emailUseSignature		VARCHAR(5)		DEFAULT "user",
	emailSignature			LONGTEXT,
	enableFullWidth			TINYINT			DEFAULT 1,
	showActions				VARCHAR(20),
	syncGoogleCalendarId	TEXT,
	syncGoogleCalendarAccessToken TEXT,
	syncGoogleCalendarRefreshToken TEXT,
	googleId				VARCHAR(250),
	userCalendarsVisible	TINYINT			DEFAULT 1,
	groupCalendarsVisible	TINYINT			DEFAULT 1,
	tagsShowAllUsers		TINYINT,
	hideCasesWithStatus		TEXT,
    hiddenTags              TEXT,
    address                 TEXT,
    defaultFeedFilters      TEXT,
    layout					TEXT,
    minimizeFeed            TINYINT         DEFAULT 0,
    fullscreen              TINYINT         DEFAULT 0,
    fullFeedControls        TINYINT         DEFAULT 0,
    feedFilters             TEXT,
    hideBugsWithStatus		TEXT,
    actionFilters           TEXT,
	UNIQUE(username, emailAddress),
	INDEX (username)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_relationships;
/*&*/
CREATE TABLE x2_relationships (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	firstType				VARCHAR(100),
	firstId					INT,
	secondType				VARCHAR(100),
	secondId				INT
) COLLATE = utf8_general_ci;
/*&*/
/* The following needs to be dropped first; there is a foreign key constraint */
DROP TABLE IF EXISTS x2_role_to_workflow;
/*&*/
DROP TABLE IF EXISTS x2_roles;
/*&*/
CREATE TABLE x2_roles (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	users					TEXT
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_role_exceptions;
/*&*/
CREATE TABLE x2_role_exceptions (
	id						INT				NOT NULL AUTO_INCREMENT primary key,
	workflowId				INT,
	stageId					INT,
	roleId					INT,
	replacementId int
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_role_to_permission;
/*&*/
CREATE TABLE x2_role_to_permission (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	fieldId					INT,
	permission				INT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_role_to_user;
/*&*/
CREATE TABLE x2_role_to_user (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	userId					INT,
	type					VARCHAR(250)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_sessions;
/*&*/
CREATE TABLE x2_sessions(
	id						CHAR(40)		PRIMARY KEY,
	user					VARCHAR(20),
	lastUpdated				BIGINT,
	IP						VARCHAR(40)		NOT NULL,
	status					TINYINT			NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_session_log;
/*&*/
CREATE TABLE x2_session_log(
	id						INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sessionId               CHAR(40),
	user					VARCHAR(40),
	timestamp				BIGINT,
	status					VARCHAR(250)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_social;
/*&*/
CREATE TABLE x2_social(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(40)		NOT NULL,
    subtype                 VARCHAR(250),
	data					TEXT,
	user					VARCHAR(20),
	associationId			INT,
	visibility				TINYINT			DEFAULT 1,
	timestamp				INT,
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_tags;
/*&*/
CREATE TABLE x2_tags(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
	taggedBy				VARCHAR(50)		NOT NULL,
	tag						VARCHAR(250)	NOT NULL,
	itemName				VARCHAR(250),
	timestamp				INT				NOT NULL DEFAULT 0,
	INDEX (tag)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_temp_files;
/*&*/
CREATE TABLE x2_temp_files (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	folder					VARCHAR(10),
	name					TEXT,
	createDate				INT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_urls;
/*&*/
CREATE TABLE x2_urls(
	id					INT					NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title					VARCHAR(20)				NOT NULL,
	url					VARCHAR(256),
	userid					INT,
	timestamp				INT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_track_emails;
/*&*/
CREATE TABLE x2_track_emails(
	id					INT					NOT NULL AUTO_INCREMENT PRIMARY KEY,
	actionId			INT,
	uniqueId			VARCHAR(32),
	opened				INT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_imports;
/*&*/
CREATE TABLE x2_imports(
	id					INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	importId			INT				NOT NULL,
	modelId				INT				NOT NULL,
	modelType			VARCHAR(250)	NOT NULL,
	timestamp			BIGINT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_locations;
/*&*/
CREATE TABLE x2_locations(
	id					INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	contactId			INT				NOT NULL,
	lat                 FLOAT			NOT NULL,
	lon                 FLOAT           NOT NULL
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_maps;
/*&*/
CREATE TABLE x2_maps(
    id			INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
    owner               VARCHAR(250),
    name    		VARCHAR(250),
    contactId           INT,
    params              TEXT,
    centerLat           FLOAT,
    centerLng           FLOAT,
    zoom                INT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_tips;
/*&*/
CREATE TABLE x2_tips(
    id                  INT                             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tip                 TEXT,
    edition             VARCHAR(10),
    admin               TINYINT,
    module              VARCHAR(255)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_flows;
/*&*/
CREATE TABLE x2_flows(
	id						INT				AUTO_INCREMENT PRIMARY KEY,
	active					TINYINT			NOT NULL DEFAULT 1,
	name					VARCHAR(100)	NOT NULL,
	triggerType				VARCHAR(40)		NOT NULL,
	modelClass				VARCHAR(40),
	flow					TEXT,
	createDate				BIGINT			NOT NULL,
	lastUpdated				BIGINT			NOT NULL
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_like_to_post;
/*&*/
CREATE TABLE `x2_like_to_post` (
  userId             int unsigned     NOT NULL,
  postId             int unsigned     NOT NULL,
  INDEX (postId)
) COLLATE = utf8_general_ci;
