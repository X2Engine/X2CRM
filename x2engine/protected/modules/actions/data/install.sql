DROP TABLE IF EXISTS x2_actions;
/*&*/
CREATE TABLE x2_actions	(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	assignedTo				VARCHAR(20),
	calendarId				INT,
    subject                 VARCHAR(255),
	visibility				INT				NOT NULL DEFAULT 1,
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
    sticky                  TINYINT         DEFAULT 0,
	INDEX (assignedTo),
	INDEX (type),
	INDEX (associationType,associationId)
) COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_action_text;
/*&*/
CREATE TABLE x2_action_text	(
	actionId				INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	text                    LONGTEXT
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(name,				title,				visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("actions",			"Actions",			1,			0,				1,			0,			0,			0,		0);
/*&*/
INSERT INTO `x2_fields`
(modelName,	fieldName,				attributeLabel,		modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance)
VALUES
("Actions",	"id",					"ID",					0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"assignedTo",			"Assigned To",			0,		0,		"assignment",	0,			0,		NULL,			0,		0,			""),
("Actions",	"subject",              "Subject",              0,		0,		"varchar",		1,			0,		NULL,			1,		0,			"High"),
("Actions",	"visibility",			"Visibility",			0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"associationId",		"Contact",				0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"associationType",		"Association Type",		0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"associationName",		"Association",			0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"dueDate",				"Due Date",				0,		0,		"dateTime",		0,			0,		NULL,			0,		0,			""),
("Actions",	"priority",				"Priority",				0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"type",					"Action Type",			0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"createDate",			"Create Date",			0,		0,		"dateTime",		0,			0,		NULL,			0,		0,			""),
("Actions",	"complete",				"Complete",				0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"reminder",				"Reminder",				0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"completedBy",			"Completed By",			0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"completeDate",			"Date Completed",		0,		0,		"dateTime",		0,			0,		NULL,			0,		0,			""),
("Actions",	"lastUpdated",			"Last Updated",			0,		0,		"dateTime",		0,			0,		NULL,			0,		0,			""),
("Actions",	"updatedBy",			"Updated By",			0,		0,		"varchar",		0,			0,		NULL,			0,		0,			""),
("Actions",	"allDay",				"All Day",				0,		0,		"boolean",		0,			0,		NULL,			0,		0,			""),
("Actions",	"color",				"Color",				0,		0,		"varchar",		0,			0,		NULL,			0,		0,			"");
