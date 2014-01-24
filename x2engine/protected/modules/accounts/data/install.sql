DROP TABLE IF EXISTS `x2_accounts`;
/*&*/
CREATE TABLE `x2_accounts` (
	id					INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name				VARCHAR(255)	NOT NULL,
	website				VARCHAR(255),
	type				VARCHAR(255),
    visibility          TINYINT         DEFAULT 1,
	annualRevenue		DECIMAL(18,2),
	phone				VARCHAR(40),
	tickerSymbol		VARCHAR(10),
	employees			INT,
    address             VARCHAR(250),
    city                VARCHAR(250),
    state               VARCHAR(250),
    country             VARCHAR(250),
    zipcode             VARCHAR(250),
    parentAccount       VARCHAR(250),
    primaryContact      VARCHAR(250),
	assignedTo			TEXT,
	createDate			BIGINT,
	associatedContacts	TEXT,
	description			TEXT,
	lastUpdated			BIGINT,
	lastActivity		BIGINT,
	updatedBy			VARCHAR(50)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(name,				title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("accounts",		"Accounts",		1,			1,				1,			1,			0,			0,		0);
/*&*/
INSERT INTO x2_fields
(modelName,			fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance, uniqueConstraint, safe)
VALUES
("Accounts",		"name",					"Name",					0,		0,	"varchar",		1,			0,		NULL,			1,		0,			"High",     0,                  1),
("Accounts",		"id",					"ID",					0,		0,	"varchar",		0,			1,		NULL,			0,		0,			"",         1,                  1),
("Accounts",		"website",				"Website",				0,		0,	"url",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"type",					"Type",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"visibility",			"Visibility",			0,		0,	"visibility",	0,			0,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"annualRevenue",		"Revenue",				0,		0,	"currency",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"phone",				"Phone",				0,		0,	"phone",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"tickerSymbol",			"Symbol",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High",     0,                  1),
("Accounts",		"address",              "Address",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"",         0,                  1),
("Accounts",		"city",                 "City",                 0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"",         0,                  1),
("Accounts",		"state",                "State",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"",         0,                  1),
("Accounts",		"country",              "Country",				0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"",         0,                  1),
("Accounts",		"zipcode",              "Postal Code",			0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"",         0,                  1),
("Accounts",		"parentAccount",		"Parent",               0,		0,	"link",         0,			0,		"Accounts",		1,		0,			"",         0,                  1),
("Accounts",		"primaryContact",		"Primary Contact",		0,		0,	"link",         0,			0,		"Contacts",		1,		0,			"",         0,                  1),
("Accounts",		"employees",			"Employees",			0,		0,	"int",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"multiple",	 	0,		0,			"",         0,                  1),
("Accounts",		"createDate",			"Create Date",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium",   0,                  1),
("Accounts",		"lastUpdated",			"Last Updated",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"lastActivity",			"Last Activity",		0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Accounts",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			"",         0,                  1);
