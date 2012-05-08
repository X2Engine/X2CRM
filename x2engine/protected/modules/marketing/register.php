<?php
return array(
	'name'=>'Marketing',
	'install'=>array(
		'CREATE TABLE x2_campaigns(
			id				INT(10)			UNSIGNED NOT NULL AUTO_INCREMENT,
			masterId		INT(10)			UNSIGNED NULL,
			name			VARCHAR(250)	NOT NULL,
			assignedTo		VARCHAR(20),
			listId			VARCHAR(100),
			description		TEXT,
			type			VARCHAR(100)	DEFAULT NULL,
			cost			VARCHAR(100)	DEFAULT NULL,
			content			TEXT,
			createdBy		VARCHAR(20)		NOT NULL,
			complete		TINYINT 		DEFAULT 0,
			createDate		INT(10) 		UNSIGNED NOT NULL,
			launchDate		INT(10) 		UNSIGNED NOT NULL,
			lastUpdated		INT(10) 		UNSIGNED NOT NULL,
			updatedBy		varchar(20),
			PRIMARY KEY		(id),
			FOREIGN KEY		(masterId) REFERENCES x2_marketing(id) ON UPDATE CASCADE ON DELETE CASCADE
		)',
		'INSERT INTO x2_fields	
			(modelName,		fieldName,				attributeLabel,		modified, custom, type, 	required, linkType) VALUES
			("Campaign",	"id",					"ID",					0,		0,	"int",			0,		NULL),
			("Campaign",	"masterId",				"Master Campaign ID",	0,		0,	"int",			0,		NULL),
			("Campaign",	"name",					"Name",					0,		0,	"varchar",		1,		NULL),
			("Campaign",	"assignedTo",			"Assigned To",			0,		0,	"assignment",	1,		NULL),
			("Campaign",	"listId",				"Contact List",			0,		0,	"list",			0,		NULL),
			("Campaign",	"description",			"Description",			0,		0,	"text",			0,		NULL),
			("Campaign",	"type",					"Type",					0,		0,	"varchar",		0,		NULL),
			("Campaign",	"cost",					"Cost",					0,		0,	"varchar",		0,		NULL),
			("Campaign",	"content",				"Content",				0,		0,	"text",			0,		NULL),
			("Campaign",	"complete",				"Complete",				0,		0,	"boolean",		0,		NULL),
			("Campaign",	"createDate",			"Create Date",			0,		0,	"date",			0,		NULL),
			("Campaign",	"launchDate",			"Launch Date",			0,		0,	"date",			0,		NULL),
			("Campaign",	"lastUpdated",			"Last Updated",			0,		0,	"date",			0,		NULL),
			("Campaign",	"updatedBy",			"Updated By",			0,		0,	"assignment",	0,		NULL)'
	),
	'uninstall'=>array(
		'DELETE FROM x2_fields WHERE modelName="Campaign"',
		'DROP TABLE x2_campaigns',
	),
	'editable'=>true,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>false,
	'toggleable'=>true,
);
?>
