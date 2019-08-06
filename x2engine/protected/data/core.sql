/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



DROP TABLE IF EXISTS x2_admin;
/*&*/
CREATE TABLE x2_admin(
	id                              INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	timeout                         INT,
        maxUserCount                    INT                             DEFAULT 200,
        loginCredsTimeout               INT                             DEFAULT 30,
        tokenPersist                    TINYINT         DEFAULT 1,
	webLeadEmail			VARCHAR(255),
	webLeadEmailAccount		INT NOT NULL DEFAULT -1,
	webTrackerCooldown		INT				DEFAULT 60,
	enableWebTracker		TINYINT			DEFAULT 1,
	enableGeolocation		TINYINT			DEFAULT 1,
	currency                        VARCHAR(3)		NULL,
	duplicateFields			VARCHAR(255)	NOT NULL DEFAULT "name",
        chatPollTime			INT				DEFAULT 3000,
        maxFileSize			INT				DEFAULT 10,
        locationTrackingFrequency       INT				DEFAULT 60,
        defaultTheme                    INT             NULL,
	ignoreUpdates			TINYINT			DEFAULT 0,
	rrId                            INT				DEFAULT 0,
	leadDistribution		VARCHAR(255),
	onlineOnly                      TINYINT,
        actionPublisherTabs             TEXT,
	disableAutomaticRecordTagging   TINYINT			DEFAULT 0,
	emailBulkAccount		INT	NOT NULL DEFAULT -1,
	emailNotificationAccount	INT NOT NULL DEFAULT -1,
	emailFromName			VARCHAR(255)	NOT NULL DEFAULT "X2Engine",
	emailFromAddr			VARCHAR(255)	NOT NULL DEFAULT '',
	emailBatchSize			INT				NOT NULL DEFAULT 200,
	emailInterval			INT				NOT NULL DEFAULT 60,
        emailCount                      INT              DEFAULT 0,
        emailStartTime                  BIGINT          DEFAULT NULL,
	emailUseSignature		VARCHAR(5)		DEFAULT "user",
	emailSignature			TEXT,
	emailType                       VARCHAR(20)		DEFAULT "mail",
	emailHost                       VARCHAR(255),
	emailPort                       INT				DEFAULT 25,
	emailUseAuth			VARCHAR(5)		DEFAULT "user",
	emailUser                       VARCHAR(255),
	emailPass                       VARCHAR(255),
	emailSecurity			VARCHAR(10),
	enableColorDropdownLegend       TINYINT         DEFAULT 0,
        enforceDefaultTheme             TINYINT         DEFAULT 0,
	installDate                     BIGINT			NOT NULL,
	updateDate                      BIGINT			NOT NULL,
	updateInterval			INT				NOT NULL DEFAULT 0,
	quoteStrictLock			TINYINT,
        locationTrackingSwitch          TINYINT,
        checkinByDefault                TINYINT DEFAULT 1,
	googleIntegration		TINYINT,
	outlookIntegration              TINYINT,
        inviteKey				VARCHAR(255),
	workflowBackdateWindow			INT			NOT NULL DEFAULT -1,
	workflowBackdateRange			INT			NOT NULL DEFAULT -1,
	workflowBackdateReassignment	TINYINT		NOT NULL DEFAULT 1,
	unique_id		VARCHAR(32) NOT NULL DEFAULT "none",
	edition			VARCHAR(10) NOT NULL DEFAULT "opensource",
	serviceCaseEmailAccount		INT NOT NULL DEFAULT -1,
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
    historyPrivacy              VARCHAR(20) DEFAULT "default",
    batchTimeout                INT DEFAULT 300,
    locationTrackingDistance    INT DEFAULT 1,
    massActionsBatchSize        INT DEFAULT 10,
    externalBaseUrl             VARCHAR(255) DEFAULT NULL,
    externalBaseUri             VARCHAR(255) DEFAULT NULL,
    assetBaseUrls               VARCHAR(255) DEFAULT NULL,
    enableAssetDomains          TINYINT NOT NULL DEFAULT 0,
    appName                     VARCHAR(255) DEFAULT NULL,
    appDescription              VARCHAR(255) DEFAULT NULL,
    /* If set to 1, prevents X2Flow from sending emails to contacts that have doNotEmail set to 1 */
    x2FlowRespectsDoNotEmail    TINYINT DEFAULT 0,
    /* This is the rich text that gets displayed to contacts after they've clicked a do not email 
       link */
    doNotEmailPage   LONGTEXT DEFAULT NULL,
    EmailUnSubPage   LONGTEXT DEFAULT NULL,
    doNotEmailLinkText          VARCHAR(255) DEFAULT NULL,
    enableUnsubscribeHeader     TINYINT DEFAULT 0,
    twitterCredentialsId        INT UNSIGNED,
    dropboxCredentialsId        INT UNSIGNED,
    linkedInCredentialsId       INT UNSIGNED,
    twitterRateLimits           TEXT DEFAULT NULL,
    linkedInRateLimits          TEXT DEFAULT NULL,
    dropboxRateLimits           TEXT DEFAULT NULL,
    triggerLogMax               INT UNSIGNED DEFAULT 1000000,
    googleCredentialsId         INT UNSIGNED,
    jasperCredentialsId         INT UNSIGNED,
    hubCredentialsId            INT UNSIGNED,
    twoFactorCredentialsId      INT UNSIGNED,
    disableAnonContactNotifs    TINYINT DEFAULT 0,
    outlookCredentialsId        INT UNSIGNED
) ENGINE=InnoDB, COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_api_hooks;
/*&*/
CREATE TABLE x2_api_hooks (
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    event           VARCHAR(50), -- Event name. Should be named after an X2Flow trigger.
    modelName       VARCHAR(100), -- Class of the model to which the hook corresponds.
    target_url      VARCHAR(255), -- Target URL that the REST hook will ping.
    userId          INT NOT NULL DEFAULT 1, -- ID of user who created the subscription.
    directPayload   TINYINT DEFAULT 0, -- Send the model directly, as part of the payload.
    createDate  BIGINT DEFAULT NULL, -- Creation timestamp
    INDEX(event,modelName),
    INDEX(createDate),
    INDEX(target_url)
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_changelog;
/*&*/
CREATE TABLE x2_changelog(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
    recordName              VARCHAR(255),
	changedBy				VARCHAR(255)	NOT NULL,
	changed					TEXT			NULL,
    fieldName				VARCHAR(255),
    oldValue				TEXT,
    newValue				TEXT,
    diff					TINYINT			NOT NULL DEFAULT 0,
	timestamp				INT				NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_email_inboxes;
/*&*/
DROP TABLE IF EXISTS x2_credentials;
/*&*/
CREATE TABLE x2_credentials(
	id			INT UNSIGNED	NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name		VARCHAR(50)	NOT NULL, -- Descriptive title
	userId		INT	NULL, -- Null userId indicates system-wide account, i.e. marketing email
	private		TINYINT NOT NULL DEFAULT 1, -- If userId is null, anyone can use it
	isEncrypted	TINYINT NOT NULL DEFAULT 0, -- Set to 1 when encryption was used on save.
	disableInbox	TINYINT NOT NULL DEFAULT 0, -- Set to 1 to disable Email module usage.
	modelClass	VARCHAR(50)	NOT NULL, -- The class of embedded model used for handling authentication data
	createDate	BIGINT DEFAULT NULL,
	lastUpdated	BIGINT DEFAULT NULL,
	isBounceAccount TINYINT NOT NULL DEFAULT 0,
	lastRunDate	BIGINT DEFAULT NULL,
	auth		TEXT, -- encrypted (hopefully) authentication data
	INDEX(userId)
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
ALTER TABLE `x2_admin` ADD CONSTRAINT FOREIGN KEY (`twitterCredentialsId`) REFERENCES x2_credentials(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
/*&*/
ALTER TABLE `x2_admin` ADD CONSTRAINT FOREIGN KEY (`linkedInCredentialsId`) REFERENCES x2_credentials(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
/*&*/
ALTER TABLE `x2_admin` ADD CONSTRAINT FOREIGN KEY (`dropboxCredentialsId`) REFERENCES x2_credentials(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
/*&*/
ALTER TABLE `x2_admin` ADD CONSTRAINT FOREIGN KEY (`googleCredentialsId`) REFERENCES x2_credentials(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
/*&*/
DROP TABLE IF EXISTS x2_credentials_default;
/*&*/
CREATE TABLE x2_credentials_default(
	userId		INT NOT NULL, -- User ID
	serviceType	VARCHAR(50) NOT NULL, -- "email", "google" etc.
	credId		INT UNSIGNED NOT NULL, -- Credentials record id
	PRIMARY KEY(`userId`,`serviceType`)
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
	options					TEXT,
	multi					TINYINT DEFAULT 0,
    parent                  INT,
    parentVal               VARCHAR(250)
) AUTO_INCREMENT=1000 COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_role_to_permission;
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
    uniqueConstraint        TINYINT         DEFAULT 0,
    safe                    TINYINT         DEFAULT 1,
	readOnly				TINYINT			DEFAULT 0,
	linkType				VARCHAR(250),
	searchable				TINYINT			DEFAULT 0,
	relevance				VARCHAR(250),
	isVirtual				TINYINT			DEFAULT 0,
    defaultValue            TEXT,
    keyType                 CHAR(3) DEFAULT NULL,
    data                    TEXT,
    description             TEXT,
	INDEX (modelName),
	UNIQUE (modelName, fieldName)
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_form_layouts;
/*&*/
CREATE TABLE x2_form_layouts (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	model					VARCHAR(250)	NOT NULL,
	version					VARCHAR(250)	NOT NULL,
	scenario				VARCHAR(20)		NOT NULL DEFAULT 'Default',
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
	custom					INT,
	enableRecordAliasing    TINYINT 	    DEFAULT 0,
	itemName				VARCHAR(100),
    defaultWorkflow         INT,
    linkRecordType          VARCHAR(250),
    linkRecordId            INT,
    linkHref                VARCHAR(250),
    linkOpenInNewTab        TINYINT         DEFAULT 0,
    linkOpenInFrame         TINYINT         DEFAULT 0,
    moduleType              ENUM('module', 'link', 'recordLink', 'pseudoModule') DEFAULT 'module'
) ENGINE InnoDB COLLATE = utf8_general_ci;
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
	createDate				BIGINT,
        INDEX(user)
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS `x2_events_to_media`;
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
    linkColor               VARCHAR(10),
    locationId              INT UNSIGNED,
    recordLinks             TEXT,
    INDEX (locationId)
) COLLATE = utf8_general_ci, ENGINE = InnoDB;
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
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
	fullName				VARCHAR(255)	NOT NULL,
	username				VARCHAR(50)		NOT NULL,
	officePhone				VARCHAR(40),
    extension               VARCHAR(40),
	cellPhone				VARCHAR(40),
	emailAddress			VARCHAR(255)	NOT NULL,
	notes					TEXT,
	status					TINYINT			NOT NULL,
	tagLine					VARCHAR(255),
	lastUpdated				BIGINT,
	updatedBy				VARCHAR(255),
	avatar					TEXT,
	allowPost				TINYINT			DEFAULT 1,
	disablePhoneLinks       TINYINT			DEFAULT 0,
	disableNotifPopup		TINYINT			DEFAULT 0,
	disableAutomaticRecordTagging		TINYINT			DEFAULT 0,
    disableTimeInTitle      TINYINT DEFAULT 0,
	language				VARCHAR(40)		DEFAULT "",
	timeZone				VARCHAR(100)	DEFAULT "",
	resultsPerPage			INT DEFAULT		20,
	widgets					VARCHAR(255),
	widgetOrder				TEXT,
	widgetSettings			TEXT,
	defaultEmailTemplates	TEXT,
	activityFeedOrder       TINYINT         DEFAULT 0,
    theme                   TEXT,
    showActions				VARCHAR(20),
    miscLayoutSettings      TEXT,
    notificationSound       VARCHAR(100)    NULL DEFAULT "X2_Notification.mp3",
    loginSound              VARCHAR(100)    NULL DEFAULT "",
	startPage				VARCHAR(30)		NULL,
	showSocialMedia			TINYINT			NOT NULL DEFAULT 0,
	showDetailView			TINYINT			NOT NULL DEFAULT 1,
	gridviewSettings		TEXT,
	generalGridViewSettings	TEXT,
	formSettings			TEXT,
	emailUseSignature		VARCHAR(5)		DEFAULT "user",
	emailSignature			LONGTEXT,
	enableFullWidth			TINYINT			DEFAULT 1,
	googleId				VARCHAR(250),
	userCalendarsVisible	TINYINT			DEFAULT 1,
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
    oldActions              TINYINT         DEFAULT 0,
    mediaWidgetDrive        TINYINT         DEFAULT 0,
    historyShowAll          TINYINT         DEFAULT 0,
    historyShowRels         TINYINT         DEFAULT 0,
    googleRefreshToken      VARCHAR(255),
    outlookRefreshToken     VARCHAR(1000),
	leadRoutingAvailability	TINYINT			DEFAULT 1,
	showTours 				TINYINT			DEFAULT 1,
        defaultCalendar     INT,
    enableTwoFactor         TINYINT DEFAULT 0,
	UNIQUE(username, emailAddress),
	INDEX (username)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_settings;
/*&*/
CREATE TABLE x2_settings (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	recordId				INT             NOT NULL,
	recordType              VARCHAR(31)     NOT NULL,
	name                    VARCHAR(255),
	embeddedModelName       VARCHAR(31),
	settings                TEXT,
	isDefault               TINYINT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_relationships;
/*&*/
CREATE TABLE x2_relationships (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	firstType				VARCHAR(100),
	firstId					INT,
	firstLabel				VARCHAR(100),
	secondType				VARCHAR(100),
	secondId				INT,
	secondLabel				VARCHAR(100),
	INDEX (firstId),
	INDEX (secondId)
) COLLATE = utf8_general_ci;
/*&*/
/* The following needs to be dropped first; there is a foreign key constraint */
DROP TABLE IF EXISTS x2_role_to_workflow;
/*&*/
DROP TABLE IF EXISTS x2_role_exceptions;
/*&*/
DROP TABLE IF EXISTS x2_role_to_user;
/*&*/
DROP TABLE IF EXISTS x2_roles;
/*&*/
CREATE TABLE x2_roles (
	id					INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	users					TEXT,
        timeout                                 INT
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_role_to_permission (
	id					INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	fieldId					INT NOT NULL,
	`permission`				INT,
        UNIQUE (`roleId`,`fieldId`),
        FOREIGN KEY (roleId) REFERENCES x2_roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
        FOREIGN KEY (fieldId) REFERENCES x2_fields(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_role_to_user (
	id                                      INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	userId					INT,
	type					VARCHAR(250),
        FOREIGN KEY (roleId) REFERENCES x2_roles(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_sessions;
/*&*/
CREATE TABLE x2_sessions(
	id						CHAR(128)		PRIMARY KEY,
	user					VARCHAR(255),
	lastUpdated				BIGINT,
	IP						VARCHAR(40)		NOT NULL,
	status					TINYINT			NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_sessions_token;
/*&*/
CREATE TABLE x2_sessions_token(
	id						CHAR(128)		PRIMARY KEY,
	user					VARCHAR(255),
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
	user					VARCHAR(255),
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
	INDEX (tag),
	INDEX (itemId)
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
DROP TABLE IF EXISTS x2_twofactor_auth;
/*&*/
CREATE TABLE x2_twofactor_auth (
    userId      INT NOT NULL PRIMARY KEY,
    code        VARCHAR(6) NOT NULL,
    requested   BIGINT NOT NULL
) COLLATE = utf8_general_ci, ENGINE = InnoDB;
/*&*/
DROP TABLE IF EXISTS x2_urls;
/*&*/
CREATE TABLE x2_urls(
	id					INT					NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title				VARCHAR(20)				NOT NULL,
	url					VARCHAR(256),
	userid				INT,
	timestamp			INT
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
	recordId			INT				NOT NULL,
	recordType			VARCHAR(250)	NOT NULL,
	lat                 FLOAT			NOT NULL,
	lon                 FLOAT           NOT NULL,
        altitude            FLOAT           DEFAULT NULL,
        accuracy            FLOAT           DEFAULT NULL,
        altitudeAccuracy    FLOAT           DEFAULT NULL,
        heading             FLOAT           DEFAULT NULL,
        speed               FLOAT           DEFAULT NULL,
	type                VARCHAR(50)     DEFAULT NULL,
	ipAddress           VARCHAR(250),
	comment             VARCHAR(250),
	seen                TEXT,
	createDate			BIGINT          NOT NULL
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_maps;
/*&*/
CREATE TABLE x2_maps(
    id                  INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
    owner               VARCHAR(250),
    name                VARCHAR(250),
    contactId           INT,
    params              TEXT,
    centerLat           FLOAT,
    centerLng           FLOAT,
    zoom                INT,
	locationType        VARCHAR(250)
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
DROP TABLE IF EXISTS x2_like_to_post;
/*&*/
CREATE TABLE `x2_like_to_post` (
  userId             int unsigned     NOT NULL,
  postId             int unsigned     NOT NULL,
  INDEX (postId)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_view_log;
/*&*/
CREATE TABLE `x2_view_log` (
	id						INT				AUTO_INCREMENT PRIMARY KEY,
	user                    VARCHAR(255),
	recordType				VARCHAR(255),
	recordId				INT,
	timestamp				BIGINT
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_chart_settings;
/*&*/
CREATE TABLE `x2_chart_settings` (
  `id`                      int(11)         NOT NULL AUTO_INCREMENT,
  `userId`                  int(11)         NOT NULL,
  `name`                    varchar(100)    NOT NULL,
  `chartType`               varchar(100)    NOT NULL,
  `settings`                TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`name`)
) COLLATE = utf8_general_ci;
/*&*/
/* This table has a foreign key constraint in it and should thus be dropped first: */
DROP TABLE IF EXISTS x2_trigger_logs;
/*&*/
DROP TABLE IF EXISTS x2_flows;
/*&*/
CREATE TABLE x2_flows(
    id                      INT                 AUTO_INCREMENT PRIMARY KEY,
    active                  TINYINT             NOT NULL DEFAULT 1,
    name                    VARCHAR(100)        NOT NULL,
    description             TEXT,
    triggerType             VARCHAR(40)         NOT NULL,
    modelClass              VARCHAR(40),
    flow                    LONGTEXT,
    flow_counter            LONGTEXT,
    createDate              BIGINT              NOT NULL,
    lastUpdated             BIGINT              NOT NULL
) ENGINE=InnoDB, COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE `x2_trigger_logs` (
  `id`                          INT             NOT NULL AUTO_INCREMENT,
  `flowId`                      INT             NOT NULL,
  `triggeredAt`                 BIGINT          NOT NULL,
  `triggerLog`                  TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`flowId`) REFERENCES x2_flows(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB, COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS `x2_record_aliases`;
/*&*/
CREATE TABLE `x2_record_aliases` (
  `id`                          INT             NOT NULL AUTO_INCREMENT,
  `recordId`                    INT             NOT NULL,
  `recordType`                  VARCHAR(100)    NOT NULL,
  `alias`                       VARCHAR(100)    NOT NULL,
  `aliasType`                   VARCHAR(100)    NOT NULL,
  `label`                       VARCHAR(100)    NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB, COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS `x2_failed_logins`;
/*&*/
CREATE TABLE x2_failed_logins (
	`id` INT NOT NULL AUTO_INCREMENT,
	IP VARCHAR(40) NOT NULL,
    attempts INTEGER UNSIGNED,
	active TINYINT DEFAULT 1,
	lastAttempt BIGINT DEFAULT NULL,
	PRIMARY KEY (`id`),
    INDEX(IP)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS `x2_tours`;
/*&*/
CREATE TABLE `x2_tours` (
	`id`                             INT             NOT NULL AUTO_INCREMENT,
	`profileId`                      INT             NOT NULL,
	`description`                    VARCHAR(32),
	`seen`                           TINYINT,
	PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS `x2_mobile_layouts`;
/*&*/
CREATE TABLE `x2_mobile_layouts` (
	`id`                             INT             NOT NULL AUTO_INCREMENT,
    `version`                        VARCHAR(16)     NOT NULL,
	`modelName`                      VARCHAR(100)    NOT NULL,
	`layout`                         TEXT,
	`defaultForm`                    TINYINT         NOT NULL DEFAULT 0,     
	`defaultView`                    TINYINT         NOT NULL DEFAULT 1,     
	PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE TABLE `x2_events_to_media` (
	`id`                             INT             NOT NULL AUTO_INCREMENT,
    `eventsId`                       INT UNSIGNED    NOT NULL,
    `mediaId`                        INT             NOT NULL,
	PRIMARY KEY (`id`),
    FOREIGN KEY (`eventsId`) REFERENCES x2_events(id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (`eventsId`, `mediaId`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS `x2_actions_to_media`;
/*&*/
CREATE TABLE `x2_actions_to_media` (
	`id`                             INT             NOT NULL AUTO_INCREMENT,
    `actionsId`                       INT UNSIGNED    NOT NULL,
    `mediaId`                        INT             NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE (`actionsId`, `mediaId`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
