<?php
return array(
	'name'=>'Marketing',
	'install'=>array(
		'CREATE TABLE x2_campaigns(
		id INT NOT NULL AUTO_INCREMENT primary key,
		assignedTo VARCHAR(250),
		name VARCHAR(250) NOT NULL,
		description TEXT,
		createDate INT,
		lastUpdated INT,
		updatedBy VARCHAR(250)
		)',
		'INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES
		("Campaign", "id", "ID", "0","int"),
		("Campaign", "name", "Name", "0", "varchar"),
		("Campaign", "assignedTo", "Assigned To", "0", "assignment"),
		("Campaign", "description", "Description", "0", "text"),
		("Campaign", "createDate", "Create Date", "0", "date"),
		("Campaign", "lastUpdated", "Last Updated", "0", "date"),
		("Campaign", "updatedBy", "Updated By", "0", "assignment")'
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
