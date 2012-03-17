<?php

return array(
    'name'=>"Users",
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
        'DROP TABLE x2_users',
    ),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>true,
    'custom'=>false,
    'toggleable'=>false,
    
);
?>
