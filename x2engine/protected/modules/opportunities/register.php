<?php

return array(
    'name'=>"Sales",
    'install'=>array(
        'CREATE TABLE x2_sales(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(40) NOT NULL,
	accountName VARCHAR(100),
	quoteAmount FLOAT,
	salesStage VARCHAR(20),
	expectedCloseDate VARCHAR(20),
	probability INT,
	leadSource VARCHAR(100),
	description TEXT,
	assignedTo TEXT,
	createDate INT,
	associatedContacts TEXT,
	lastUpdated INT,
	updatedBy VARCHAR(20)
) COLLATE = utf8_general_ci',
            'INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, required, linkType) VALUES 
                    ("Sales",		"id",				"ID",					0,	0,	"varchar",		0,	""),
                    ("Sales",		"name",				"Name",					0,	0,	"varchar",		0,	""),
                    ("Sales",		"accountName",			"Account",				0,	0,	"link",			0,	"accounts"),
                    ("Sales",		"quoteAmount",			"Quote Amount",                         0,	0,	"currency",		0,	""),
                    ("Sales",		"salesStage",			"Sales Stage",                          0,	0,	"dropdown",		0,	"6"),
                    ("Sales",		"expectedCloseDate",            "Expected Close Date",                  0,	0,	"date",			0,	""),
                    ("Sales",		"probability",			"Probability",                          0,	0,	"int",			0,	""),
                    ("Sales",		"leadSource",			"Lead Source",                          0,	0,	"dropdown",		0,	"4"),
                    ("Sales",		"description",			"Description",                          0,	0,	"text",			0,	""),
                    ("Sales",		"assignedTo",			"Assigned To",                          0,	0,	"assignment",           0,	"multiple"),
                    ("Sales",		"createDate",			"Create Date",                          0,	0,	"date",			0,	""),
                    ("Sales",		"associatedContacts",           "Contacts",				0,	0,	"varchar",		0,	""),
                    ("Sales",		"lastUpdated",			"Last Updated",                         0,	0,	"date",			0,	""),
                    ("Sales",		"updatedBy",			"Updated By",                           0,	0,	"varchar",		0,	"");',
    ),
    'uninstall'=>array(
        'DELETE FROM x2_fields WHERE modelName="Sales"',
        'DROP TABLE x2_sales',
    ),
    'editable'=>true,
    'searchable'=>true,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
