<?php

return array(
    'name'=>"Accounts",
    'install'=>array(
        'CREATE TABLE x2_accounts(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(40) NOT NULL,
	website VARCHAR(40),
	type VARCHAR(60), 
	annualRevenue FLOAT,
	phone VARCHAR(40),
	tickerSymbol VARCHAR(10),
	employees INT,
	assignedTo TEXT,
	createDate INT,
	associatedContacts TEXT,
	description TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20)
) COLLATE = utf8_general_ci',
            'INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, required, linkType) VALUES 
                    ("Accounts",	"name",					"Name",					0,	0,	"varchar",		0,	NULL),
                    ("Accounts",	"id",					"ID",					0,	0,	"varchar",		0,	NULL),
                    ("Accounts",	"website",				"Website",				0,	0,	"url",			0,	NULL),
                    ("Accounts",	"type",					"Type",					0,	0,	"varchar",		0,	NULL),
                    ("Accounts",	"annualRevenue",                        "Revenue",				0,	0,	"currency",		0,	NULL),
                    ("Accounts",	"phone",				"Phone",				0,	0,	"varchar",		0,	NULL),
                    ("Accounts",	"tickerSymbol",                         "Symbol",				0,	0,	"varchar",		0,	NULL),
                    ("Accounts",	"employees",                            "Employees",                            0,	0,	"int",			0,	NULL),
                    ("Accounts",	"assignedTo",                           "Assigned To",                          0,	0,	"assignment",           0,	"multiple"),
                    ("Accounts",	"createDate",                           "Create Date",                          0,	0,	"date",			0,	NULL),
                    ("Accounts",	"associatedContacts",                   "Contacts",				0,	0,	"varchar",		0,	NULL),
                    ("Accounts",	"description",                          "Description",                          0,	0,	"text",			0,	NULL),
                    ("Accounts",	"lastUpdated",                          "Last Updated",                         0,	0,	"date",			0,	NULL);',
    ),
    'uninstall'=>array(
        'DELETE FROM x2_fields WHERE modelName="Accounts"',
        'DROP TABLE x2_accounts',
    ),
    'editable'=>true,
    'searchable'=>true,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
