<?php

return array(
    'name'=>"Quotes",
    'install'=>array(
        'CREATE TABLE x2_quotes(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(40) NOT NULL,
	accountName VARCHAR(250),
	salesStage VARCHAR(20),
	expectedCloseDate VARCHAR(20),
	probability INT,
	leadSource VARCHAR(10),
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	createdBy VARCHAR(20),
	associatedContacts TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20),
	expirationDate INT,
	status VARCHAR(20),
	currency VARCHAR(40),
	locked TINYINT
) COLLATE = utf8_general_ci',
            'INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, required, linkType) VALUES 
                    ("Quotes",		"id",					"ID",					0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"name",					"Name",					0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"accountName",                          "Account",				0,	0,	"link",                 0,	"accounts"),
                    ("Quotes",		"existingProducts",                     "Existing Products",                    0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"salesStage",                           "Sales Stage",                          0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"expectedCloseDate",                    "Expected Close Date",                  0,	0,	"date",			0,	NULL),
                    ("Quotes",		"probability",                          "Probability",                          0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"leadSource",                           "Lead Source",                          0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"description",                          "Notes",				0,	0,	"text",			0,	NULL),
                    ("Quotes",		"assignedTo",                           "Assigned To",                          0,	0,	"assignment",           0,	""),
                    ("Quotes",		"createDate",                           "Create Date",                          0,	0,	"date",			0,	NULL),
                    ("Quotes",		"associatedContacts",                   "Contacts",				0,	0,	"link",                 0,	"contacts"),
                    ("Quotes",		"lastUpdated",                          "Last Updated",                         0,	0,	"date",			0,	NULL),
                    ("Quotes",		"updatedBy",                            "Updated By",                           0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"status",				"Status",				0,	0,	"dropdown",		0,	"7"),
                    ("Quotes",		"expirationDate",                       "Expiration Date",                      0,	0,	"date",                 0,	NULL),
                    ("Quotes",		"products",				"Products",				0,	0,	"varchar",		0,	NULL),
                    ("Quotes",		"locked",				"Locked",				0,	0,	"boolean",		0,	NULL);',
    ),
    'uninstall'=>array(
        'DELETE FROM x2_fields WHERE modelName="Quotes"',
        'DROP TABLE x2_quotes',
    ),
    'editable'=>true,
    'searchable'=>true,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
