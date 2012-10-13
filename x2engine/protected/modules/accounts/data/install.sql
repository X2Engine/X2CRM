DROP TABLE IF EXISTS `x2_accounts`;
CREATE TABLE `x2_accounts` (
	id					INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name				VARCHAR(40)		NOT NULL,
	website				VARCHAR(40),
	type				VARCHAR(60), 
	annualRevenue		FLOAT,
	phone				VARCHAR(40),
	tickerSymbol		VARCHAR(10),
	employees			INT,
	assignedTo			TEXT,
	createDate			BIGINT,
	associatedContacts	TEXT,
	description			TEXT,
	lastUpdated			BIGINT,
	updatedBy			VARCHAR(20)
) COLLATE = utf8_general_ci;
INSERT INTO `x2_modules` 
			(name,				title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable) 
	VALUES	("accounts",		"Accounts",		1,			1,				1,			1,			0,			0,		0);

INSERT INTO x2_fields
(modelName,			fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance)
VALUES
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
("Accounts",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			"");