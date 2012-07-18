<?php
return array(
	'name'=>"Template",
	'install'=>array(
		'CREATE TABLE x2_cases(
		id INT NOT NULL AUTO_INCREMENT primary key,
		assignedTo VARCHAR(250),
		name VARCHAR(250) NOT NULL,
		description TEXT,
		createDate INT,
		lastUpdated INT,
		updatedBy VARCHAR(250)
		)',
		'INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES 
		("Cases", "id", "ID", "0", "int"),
		("Cases", "name", "Name", "0", "varchar"),
		("Cases", "assignedTo", "Assigned To", "0", "assignment"),
		("Cases", "description", "Description", "0", "text"),
		("Cases", "createDate", "Create Date", "0", "date"),
		("Cases", "lastUpdated", "Last Updated", "0", "date"),
		("Cases", "updatedBy", "Updated By", "0", "assignment")'
    ),
	'uninstall'=>array(
		'DELETE FROM x2_fields WHERE modelName="Cases"',
		'DROP TABLE x2_cases',
	),
	'editable'=>true,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>true,
	'toggleable'=>true,
);
?>
