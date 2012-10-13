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
/* These have foreign key constraints in them and should thus be dropped first: */
DROP TABLE IF EXISTS x2_list_criteria,x2_list_items;

DROP TABLE IF EXISTS x2_admin;
CREATE TABLE x2_admin(
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
	edition			VARCHAR(10) NOT NULL DEFAULT "opensource"
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_changelog;
CREATE TABLE x2_changelog(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
	changedBy				VARCHAR(50)		NOT NULL,
	changed					TEXT			NOT NULL,
    fieldName               VARCHAR(255),
    oldValue                TEXT,
    newValue                TEXT,
	timestamp				INT				NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_criteria;
CREATE TABLE x2_criteria(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	modelType				VARCHAR(100),
	modelField				VARCHAR(250),
	modelValue				TEXT,
	comparisonOperator		VARCHAR(10),
	users					TEXT,
	type					VARCHAR(250)
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_dropdowns;
CREATE TABLE x2_dropdowns (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	options					TEXT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_fields;
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

DROP TABLE IF EXISTS x2_form_layouts;
CREATE TABLE x2_form_layouts (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	model					VARCHAR(250)	NOT NULL,
	version					VARCHAR(250)	NOT NULL,
	layout					TEXT,
	defaultView				TINYINT			NOT NULL DEFAULT 0,
	defaultForm				TINYINT			NOT NULL DEFAULT 0,
	createDate				BIGINT,
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_lead_routing;
CREATE TABLE x2_lead_routing( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	criteria				TEXT,
	users					TEXT,
	priority				INT, 
	rrId					INT				DEFAULT 0,
	groupType				INT
) COLLATE = utf8_general_ci;


DROP TABLE IF EXISTS x2_lists;
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
	lastUpdated				BIGINT			NOT NULL
) COLLATE utf8_general_ci;

CREATE TABLE x2_list_criteria (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	listId					INT				UNSIGNED NOT NULL,
	type					VARCHAR(20)		NULL,
	attribute				VARCHAR(40)		NULL,
	comparison				VARCHAR(10)		NULL,
	value					VARCHAR(100)	NOT NULL,
	
	INDEX (listId),
	FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE utf8_general_ci;

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
	FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE utf8_general_ci;

DROP TABLE IF EXISTS x2_modules;
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

DROP TABLE IF EXISTS x2_notifications;
CREATE TABLE x2_notifications( 
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
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_phone_numbers;
CREATE TABLE x2_phone_numbers(
	modelId					INT				UNSIGNED NOT NULL,
	modelType				VARCHAR(100)	NOT NULL,
	number					VARCHAR(40)		NOT NULL,
    fieldName               VARCHAR(255),
	
	INDEX (modelType,modelId),
	INDEX (number)
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_profile;
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
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_relationships;
CREATE TABLE x2_relationships( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	firstType				VARCHAR(100),
	firstId					INT,
	secondType				VARCHAR(100),
	secondId				INT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_role_exceptions;
CREATE TABLE x2_role_exceptions (
	id						INT				NOT NULL AUTO_INCREMENT primary key,
	workflowId				INT,
	stageId					INT,
	roleId					INT,
	replacementId int
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_role_to_permission;
CREATE TABLE x2_role_to_permission (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	fieldId					INT,
	permission				INT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_role_to_user;
CREATE TABLE x2_role_to_user (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roleId					INT,
	userId					INT,
	type					VARCHAR(250)
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_roles;
CREATE TABLE x2_roles (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(250),
	users					TEXT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_sessions;
CREATE TABLE x2_sessions(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user					VARCHAR(250),
	lastUpdated				BIGINT,
	IP						VARCHAR(40)		NOT NULL,
	status					TINYINT			NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_social;
CREATE TABLE x2_social(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(40)		NOT NULL,
	data					TEXT,
	user					VARCHAR(20),
	associationId			INT,
	private					TINYINT			DEFAULT 0,
	timestamp				INT,
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_tags;
CREATE TABLE x2_tags( 
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type					VARCHAR(50)		NOT NULL,
	itemId					INT				NOT NULL,
	taggedBy				VARCHAR(50)		NOT NULL,
	tag						VARCHAR(250)	NOT NULL,
	itemName				VARCHAR(250),
	timestamp				INT				NOT NULL DEFAULT 0
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_temp_files;
CREATE TABLE x2_temp_files (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	folder					VARCHAR(10),
	name					TEXT,
	createDate				INT
) COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS x2_urls;
CREATE TABLE x2_urls(
	 id					INT					NOT NULL AUTO_INCREMENT PRIMARY KEY,
	 title					VARCHAR(20)				NOT NULL,
	 url					VARCHAR(256),
	 userid					INT,
	 timestamp				INT
 ) COLLATE = utf8_general_ci;
