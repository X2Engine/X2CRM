<?php

return array(
    'name'=>"Products",
    'install'=>array(
        'CREATE TABLE x2_products(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	type VARCHAR(100),
	price FLOAT,
	inventory INT,
	description TEXT,
	createDate INT,
	lastUpdated INT,
	updatedBy VARCHAR(20),
	status VARCHAR(20),
	currency VARCHAR(40),
	adjustment FLOAT
) COLLATE = utf8_general_ci',
            'INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, required, linkType) VALUES 
                    ("Products",	"id",					"ID",					0,	0,	"varchar",		0,	NULL),
                    ("Products",	"name",					"Name",					0,	0,	"varchar",		0,	NULL),
                    ("Products",	"type",					"Type",					0,	0,	"varchar",		0,	NULL),
                    ("Products",	"price",				"Price",				0,	0,	"currency",		0,	NULL),
                    ("Products",	"inventory",                            "Inventory",                            0,	0,	"varchar",		0,	NULL),
                    ("Products",	"description",                          "Description",                          0,	0,	"text",			0,	NULL),
                    ("Products",	"createDate",                           "Create Date",                          0,	0,	"date",			0,	NULL),
                    ("Products",	"lastUpdated",                          "Last Updated",                         0,	0,	"date",			0,	NULL),
                    ("Products",	"updatedBy",                            "Updated By",                           0,	0,	"varchar",		0,	NULL),
                    ("Products",	"currency",				"Currency",				0,	0,	"dropdown",		0,	"2"),
                    ("Products",	"status",				"Status",				0,	0,	"dropdown",		0,	"1"),
                    ("Products",	"adjustment",                           "Adjustment",                           0,	0,	"varchar",		0,	NULL);',
    ),
    'uninstall'=>array(
        'DELETE FROM x2_fields WHERE modelName="Products"',
        'DROP TABLE x2_products',
    ),
    'editable'=>true,
    'searchable'=>true,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
