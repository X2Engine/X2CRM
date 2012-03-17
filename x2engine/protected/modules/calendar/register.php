<?php

return array(
    'name'=>"Calendar",
    'install'=>array(
        "CREATE TABLE x2_templates(
                        id INT NOT NULL AUTO_INCREMENT primary key,
                        assignedTo VARCHAR(250),
                        name VARCHAR(250) NOT NULL,
                        description TEXT,
                        createDate INT,
                        lastUpdated INT,
                        updatedBy VARCHAR(250)
                        )",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom) VALUES ('Templates', 'id', 'ID', '0')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('Templates', 'name', 'Name', '0', 'varchar')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('Templates', 'assignedTo', 'Assigned To', '0', 'assignment')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('Templates', 'description', 'Description', '0', 'text')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('Templates', 'createDate', 'Create Date', '0', 'date')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('Templates', 'lastUpdated', 'Last Updated', '0', 'date')",
                        "INSERT INTO x2_fields (modelName, fieldName, attributeLabel, custom, type) VALUES ('Templates', 'updatedBy', 'Updated By', '0', 'assignment')"
    ),
    'uninstall'=>array(
        'DELETE FROM x2_fields WHERE modelName="Templates"',
        'DROP TABLE x2_templates',
    ),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
