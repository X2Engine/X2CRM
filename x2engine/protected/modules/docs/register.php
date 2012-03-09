<?php

return array(
    'name'=>"Docs",
    'install'=>array(
        'CREATE TABLE x2_docs(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(100) NOT NULL,
	type VARCHAR(10) NOT NULL DEFAULT "",
	text LONGTEXT NOT NULL,
	createdBy VARCHAR(60) NOT NULL,
	createDate INT,
	editPermissions VARCHAR(250),
	updatedBy VARCHAR(40), 
	lastUpdated INT
) COLLATE = utf8_general_ci',
    ),
    'uninstall'=>array(
        'DROP TABLE x2_docs',
    ),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
