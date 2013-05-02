DROP TABLE IF EXISTS x2_products;
/*&*/
CREATE TABLE x2_products(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(100)	NOT NULL,
	type					VARCHAR(100),
	price					FLOAT,
	inventory				INT,
	description				TEXT,
	createDate				BIGINT,
	lastUpdated				BIGINT,
	lastActivity			BIGINT,
	updatedBy				VARCHAR(20),
	status					VARCHAR(20),
	currency				VARCHAR(40),
	adjustment				FLOAT
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("products",		"Products",			1,			12,				1,			1,			0,			0,		0);
/*&*/
INSERT INTO x2_fields
(modelName,		fieldName,				attributeLabel,	 modified,	custom,	type,		required,	readOnly,  linkType,   searchable,	isVirtual,	relevance)
VALUES
("Product",		"currency",				"Currency",				0,		0,	"dropdown",		0,			0,		"101",			0,		0,			""),
("Product",		"status",				"Status",				0,		0,	"dropdown",		0,			0,		"100",			0,		0,			""),
("Product",		"id",					"ID",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Product",		"name",					"Name",					0,		0,	"varchar",		0,			0,		NULL,			1,		0,			"High"),
("Product",		"type",					"Type",					0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Product",		"price",				"Price",				0,		0,	"currency",		0,			0,		NULL,			0,		0,			""),
("Product",		"inventory",			"Inventory",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			""),
("Product",		"description",			"Description",			0,		0,	"text",			0,			0,		NULL,			1,		0,			"Medium"),
("Product",		"createDate",			"Create Date",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			""),
("Product",		"lastUpdated",			"Last Updated",			0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			""),
("Product",		"lastActivity",			"Last Activity",		0,		0,	"dateTime",		0,			1,		NULL,			0,		0,			""),
("Product",		"updatedBy",			"Updated By",			0,		0,	"varchar",		0,			1,		NULL,			0,		0,			""),
("Product",		"adjustment",			"Adjustment",			0,		0,	"varchar",		0,			0,		NULL,			0,		0,			"");