DROP TABLE IF EXISTS x2_quotes,x2_quotes_products;
/*&*/
CREATE TABLE x2_quotes(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(255)		NOT NULL,
	accountName				VARCHAR(250),
	salesStage				VARCHAR(20),
	expectedCloseDate		BIGINT,
	probability				INT,
	leadSource				VARCHAR(10),
	description				TEXT,
	assignedTo				TEXT,
	createDate				BIGINT,
	createdBy				VARCHAR(50),
	associatedContacts		TEXT,
	lastUpdated				BIGINT,
    lastActivity            BIGINT,
	updatedBy				VARCHAR(50),
	expirationDate			BIGINT,
	status					VARCHAR(20),
	currency				VARCHAR(40),
	locked					TINYINT,
	type					VARCHAR(20),
	invoiceStatus			VARCHAR(40),
	invoiceCreateDate		BIGINT,
	invoiceIssuedDate		BIGINT,
	invoicePayedDate		BIGINT,
	template				INT	DEFAULT NULL,
	total					DECIMAL(18,2) DEFAULT 0.0,
	subtotal				DECIMAL(18,2) DEFAULT 0.0
) COLLATE = utf8_general_ci AUTO_INCREMENT = 1000;
/*&*/
CREATE TABLE x2_quotes_products(
	id						INT	NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quoteId					INT,
	productId				INT,
	quantity				DECIMAL(18,2) DEFAULT 0.0,
	name					VARCHAR(100)	NOT NULL,
	type					VARCHAR(100),
	price					DECIMAL(18,2) DEFAULT 0.0,
	inventory				INT,
	description				TEXT,
	assignedTo				TEXT,
	createDate				BIGINT,
	lastUpdated				BIGINT,
	lastActivity			BIGINT,
	updatedBy				VARCHAR(50),
	active					TINYINT,
	currency				VARCHAR(40),
	adjustment				DECIMAL(18,2) DEFAULT 0.0,
	adjustmentType			VARCHAR(20),
	lineNumber				INT NOT NULL DEFAULT 0,
	total					DECIMAL(18,2) DEFAULT 0.0
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("quotes",			"Quotes",			1,			13,				1,			1,			0,			0,		0);
/*&*/
INSERT INTO x2_fields
(modelName,	fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance, uniqueConstraint, safe)
VALUES
("Quote",	"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"",         1,                  1),
("Quote",	"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High",     0,                  1),
("Quote",	"accountName",			"Account",				0,		0,	"link",			0,			0,		"Accounts",	 	0,		0,			"",         0,                  1),
("Quote",	"salesStage",			"Opportunity Stage",	0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"expectedCloseDate",	"Expected Close Date",	0,		0,	"date",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"probability",			"Probability",			0,		0,	"percentage",	0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"leadSource",			"Lead Source",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"description",			"Notes",				0,		0,	"text",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"assignedTo",			"Assigned To",			0,		0,	"assignment",	0,			0,		"multiple",	 	0,		0,			"",         0,                  1),
("Quote",	"createDate",			"Create Date",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"associatedContacts",	"Contact",				0,		0,	"link",			0,			0,		"Contacts",		0,		0,			"",         0,                  1),
("Quote",	"lastUpdated",			"Last Updated",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"lastActivity",			"Last Activity",		0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"status",				"Status",				0,		0,	"dropdown",		0,			0,		"106",			0,		0,			"",         0,                  1),
("Quote",	"expirationDate",		"Expiration Date",		0,		0,	"date",			0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"existingProducts",		"Existing Products",	0,		0,	"varchar",		0,			0,		NULL,			0,		1,			"",         0,                  1),
("Quote",	"products",				"Products",				0,		0,	"varchar",		0,			0,		NULL,			0,		1,			"",         0,                  1),
("Quote",	"locked",				"Locked",				0,		0,	"boolean",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote", 	"invoiceStatus", 		"Invoice Status", 		0, 		0,	"dropdown", 	0, 			0, 		"114", 			0, 		0,			"",         0,                  1),
("Quote", 	"invoiceCreateDate", 	"Invoice Created", 		0, 		0,	"date", 		0, 			0, 		NULL, 			0, 		0,			"",         0,                  1),
("Quote", 	"invoiceIssuedDate", 	"Invoice Issued", 		0, 		0,	"date", 		0, 			0, 		NULL, 			0, 		0,			"",         0,                  1),
("Quote", 	"invoicePayedDate", 	"Invoice Payed", 		0, 		0,	"date", 		0, 			0, 		NULL, 			0, 		0,			"",         0,                  1),
("Quote",	"template",				"Quote Template",		0,		0,	"link",			0,			0,		"Docs",			0,		0,			"",         0,                  1),
("Quote",	"total",				"Total",				0,		0,	"currency",		0,			0,		NULL,			0,		0,			"",         0,                  1),
("Quote",	"subtotal",				"Subtotal",				0,		0,	"currency",		0,			0,		NULL,			0,		0,			"",         0,                  1);
