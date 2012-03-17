<?php

return array(
    'name'=>"Workflow",
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

    ),
    'uninstall'=>array(
        'DROP TABLE x2_workflow',
    ),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
