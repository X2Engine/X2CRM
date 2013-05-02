DROP TABLE IF EXISTS `x2_campaigns`,`x2_campaigns_attachments`,`x2_web_forms`;
/*&*/
CREATE TABLE x2_campaigns (
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT,
	masterId				INT				UNSIGNED NULL,
	name					VARCHAR(250)	NOT NULL,
	assignedTo				VARCHAR(20),
	listId					VARCHAR(100),
	active					TINYINT			DEFAULT 1,
	description				TEXT,
	type					VARCHAR(100)	DEFAULT NULL,
	cost					VARCHAR(100)	DEFAULT NULL,
	template				INT				DEFAULT 0,
	subject					VARCHAR(250),
	content					TEXT,
	createdBy				VARCHAR(20)		NOT NULL,
	complete				TINYINT 		DEFAULT 0,
	visibility				INT				NOT NULL,
	createDate				BIGINT	 		NOT NULL,
	launchDate				BIGINT,
	lastUpdated				BIGINT	 		NOT NULL,
	lastActivity			BIGINT,
	updatedBy				VARCHAR(20),

	PRIMARY KEY (id),
	FOREIGN KEY (masterId) REFERENCES x2_campaigns(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_campaigns_attachments (
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT,
	campaign				INT				UNSIGNED,
	media					INT				UNSIGNED,

	PRIMARY KEY (id)
) COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_web_forms(
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
	userEmailTemplate		INT,
	webleadEmailTemplate	INT,

	PRIMARY KEY (id)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("marketing",		"Marketing",		1,			3,				0,			1,			0,			0,		0);
/*&*/
INSERT INTO x2_fields
(modelName,			fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance)
VALUES
("Campaign",		"id",					"ID",					0,		0,	"int",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"masterId",				"Master Campaign ID",	0,		0,	"int",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"name",					"Name",					0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"High"),
("Campaign",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	1,			0,		NULL,			0,		0,			""),
("Campaign",		"listId",				"Contact List",			0,		0,	"link",			0,			0,		"X2List",		0,		0,			""),
("Campaign",		"active",				"Active",				0,		0,	"boolean",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Campaign",		"type",					"Type",					0,		0,	"dropdown",		0,			0,		"107",			0,		0,			""),
("Campaign",		"template",				"Template",				0,		0,	"link",			0,			0,		"Docs",			0,		0,			""),
("Campaign",		"cost",					"Cost",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"subject",				"Subject",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"content",				"Content",				0,		0,	"text",			0,			0,		NULL,			0,		0,			""),
("Campaign",		"complete",				"Complete",				0,		0,	"boolean",		0,			1,		NULL,			0,		0,			""),
("Campaign",		"visibility",			"Visibility",			0,		0,	"visibility",	1,			0,		NULL,			0,		0,			""),
("Campaign",		"createDate",			"Create Date",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			""),
("Campaign",		"launchDate",			"Launch Date",			0,		0,	"dateTime",		0,			0,		NULL,			0,		0,			""),
("Campaign",		"lastUpdated",			"Last Updated",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			""),
("Campaign",		"lastActivity",			"Last Activity",		0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			""),
("Campaign",		"updatedBy",			"Updated By",			0,		0,	"assignment",	0,			1,		NULL,			0,		0,			"");