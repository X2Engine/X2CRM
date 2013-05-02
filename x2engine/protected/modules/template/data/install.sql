DROP TABLE IF EXISTS x2_templates;
/*&*/
CREATE TABLE x2_templates(
		id INT NOT NULL AUTO_INCREMENT primary key,
		assignedTo VARCHAR(250),
		name VARCHAR(250) NOT NULL,
		description TEXT,
		createDate INT,
		lastUpdated INT,
		lastActivity BIGINT,
		updatedBy VARCHAR(250)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	('templates',		'Templates',	1,			1,				1,			1,			0,			1,		1);
/*&*/
INSERT INTO x2_fields
(modelName,		fieldName,			attributeLabel,		custom,		type,		required,	readOnly, 		linkType,   searchable,	isVirtual,	relevance)
VALUES
("Templates",	"id",				"ID",					0,		"int",			0,			0,			NULL,		0,			0,			""			),
("Templates",	"name",				"Name",					0,		"varchar",		0,			0,			NULL,		0,			0,			"High"		),
("Templates",	"assignedTo",		"Assigned To",			0,		"assignment",	0,			0,			NULL,		0,			0,			""			),
("Templates",	"description",		"Description",			0,		"text",			0,			0,			NULL,		0,			0,			"Medium"	),
("Templates",	"createDate",		"Create Date",			0,		"dateTime",		0,			0,			NULL,		0,			0,			""			),
("Templates",	"lastUpdated",		"Last Updated",			0,		"dateTime",		0,			0,			NULL,		0,			0,			""			),
("Templates",	"lastActivity",		"Last Activity",		0,		"dateTime",		0,			0,			NULL,		0,			0,			""			),
("Templates",	"updatedBy",		"Updated By",			0,		"assignment",	0,			0,			NULL,		0,			0,			""			);
