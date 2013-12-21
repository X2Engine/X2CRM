DROP TABLE IF EXISTS `x2_campaigns`,`x2_campaigns_attachments`,`x2_web_forms`;
/*&*/
CREATE TABLE x2_campaigns (
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT,
	masterId				INT				UNSIGNED NULL,
	name					VARCHAR(250)	NOT NULL,
	assignedTo				VARCHAR(50),
	listId					VARCHAR(100),
	active					TINYINT			DEFAULT 1,
	description				TEXT,
	type					VARCHAR(100)	DEFAULT NULL,
	cost					VARCHAR(100)	DEFAULT NULL,
	template				INT				DEFAULT 0,
	subject					VARCHAR(250),
	content					TEXT,
	createdBy				VARCHAR(50)		NOT NULL,
	complete				TINYINT 		DEFAULT 0,
	visibility				INT				NOT NULL,
	createDate				BIGINT	 		NOT NULL,
	launchDate				BIGINT,
	lastUpdated				BIGINT	 		NOT NULL,
	lastActivity			BIGINT,
	updatedBy				VARCHAR(50),
	sendAs					INT DEFAULT NULL,
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
	header					TEXT,
	visibility				INT				NOT NULL,
	assignedTo				VARCHAR(50)		NOT NULL,
	createdBy				VARCHAR(50)		NOT NULL,
	updatedBy				VARCHAR(50)		NOT NULL,
	createDate				BIGINT	 		NOT NULL,
	lastUpdated				BIGINT	 		NOT NULL,
	userEmailTemplate		INT,
	webleadEmailTemplate	INT,

	PRIMARY KEY (id)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("marketing",		"Marketing",		1,			2,				0,			1,			0,			0,		0);
/*&*/
INSERT INTO x2_fields
(modelName,			fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance, uniqueConstraint, safe)
VALUES
("Campaign",		"id",					"ID",					0,		0,	"int",			0,			0,		NULL,			0,		0,			"",         1,                  1),
("Campaign",		"masterId",				"Master Campaign ID",	0,		0,	"int",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"name",					"Name",					0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"High",     0,                  1),
("Campaign",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	1,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"listId",				"Contact List",			0,		0,	"link",			1,			0,		"X2List",		0,		0,			"",         0,                  1),
("Campaign",		"active",				"Active",				0,		0,	"boolean",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium",   0,                  1),
("Campaign",		"type",					"Type",					0,		0,	"dropdown",		0,			0,		"107",			0,		0,			"",         0,                  1),
("Campaign",		"template",				"Template",				0,		0,	"link",			0,			0,		"Docs",			0,		0,			"",         0,                  1),
("Campaign",		"cost",					"Cost",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"subject",				"Subject",				0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"content",				"Content",				0,		0,	"text",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"complete",				"Complete",				0,		0,	"boolean",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"visibility",			"Visibility",			0,		0,	"visibility",	1,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"createDate",			"Create Date",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"launchDate",			"Launch Date",			0,		0,	"dateTime",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"lastUpdated",			"Last Updated",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"lastActivity",			"Last Activity",		0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"updatedBy",			"Updated By",			0,		0,	"assignment",	0,			1,		NULL,			0,		0,			"",         0,                  1),
("Campaign",		"sendAs",				"Send As",				0,		0,	"credentials",	0,			0,		"email:bulkEmail",	0,	0,          "",         0,                  1);
/*&*/
INSERT INTO `x2_form_layouts` (`id`, `model`, `version`, `scenario`, `layout`, `defaultView`, `defaultForm`, `createDate`, `lastUpdated`) VALUES (13,'Campaign','Form','Default','{\"version\":\"3.2\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"483\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_sendAs\",\"labelType\":\"left\",\"readOnly\":\"undefined\",\"height\":\"22\",\"width\":\"154\",\"tabindex\":\"undefined\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"226\",\"tabindex\":\"0\"},{\"name\":\"formItem_template\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"133\",\"tabindex\":\"0\"},{\"name\":\"formItem_content\",\"labelType\":\"none\",\"readOnly\":\"0\",\"height\":\"229\",\"width\":\"563\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}',0,1,1373388579,1373388579);

