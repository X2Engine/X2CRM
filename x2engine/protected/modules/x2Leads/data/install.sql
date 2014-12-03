DROP TABLE IF EXISTS `x2_x2leads`;
/*&*/
CREATE TABLE x2_x2leads(
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`             VARCHAR(255)    NOT NULL,
    firstName          VARCHAR(255) NOT NULL,
    lastName           VARCHAR(255) NOT NULL,
    nameId             VARCHAR(250) DEFAULT NULL,
    accountName        VARCHAR(100),
    quoteAmount        DECIMAL(18,2),
    salesStage         VARCHAR(20),
    expectedCloseDate  BIGINT,
    probability        FLOAT,
    leadSource         VARCHAR(100),
    description        TEXT,
    assignedTo         TEXT,
    createDate         BIGINT,
    visibility         TINYINT DEFAULT 1,
    lastUpdated        BIGINT,
    lastActivity       BIGINT,
    updatedBy          VARCHAR(50),
    UNIQUE(nameId),
    INDEX(accountName)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('x2Leads', 'Leads', 1, 4, 1, 1, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('X2Leads', 'id',                'ID',                  0, 0, 'varchar',    0, 0, NULL,       0, 0, '',       1, 1, 'PRI'),
('X2Leads', 'name',              'Name',                0, 0, 'varchar',    0, 0, NULL,       1, 0, 'High',   0, 1, NULL),
('X2Leads', 'nameId',            'NameID',              0, 0, 'varchar',    0, 1, NULL,       1, 0, 'High',   0, 1, 'FIX'),
('X2Leads', 'firstName',      'First Name',             0, 0, 'varchar',    1, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('X2Leads', 'lastName',       'Last Name',              0, 0, 'varchar',    1, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('X2Leads', 'accountName',       'Account',             0, 0, 'link',       0, 0, 'Accounts', 0, 0, '',       0, 1, 'MUL'),
('X2Leads', 'quoteAmount',       'Quote Amount',        0, 0, 'currency',   0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'salesStage',        'Sales Stage',         0, 0, 'dropdown',   0, 0, '105',      0, 0, '',       0, 1, NULL),
('X2Leads', 'expectedCloseDate', 'Expected Close Date', 0, 0, 'date',       0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'probability',       'Probability',         0, 0, 'percentage', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'leadSource',        'Lead Source',         0, 0, 'dropdown',   0, 0, '103',      0, 0, '',       0, 1, NULL),
('X2Leads', 'description',       'Description',         0, 0, 'text',       0, 0, NULL,       1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'assignedTo',        'Assigned To',         0, 0, 'assignment', 0, 0, 'multiple', 0, 0, '',       0, 1, NULL),
('X2Leads', 'visibility',        'Visibility',          0, 0, 'visibility', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'createDate',        'Create Date',         0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'lastUpdated',       'Last Updated',        0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'lastActivity',      'Last Activity',       0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'updatedBy',         'Updated By',          0, 0, 'varchar',    0, 1, NULL,       0, 0, '',       0, 1, NULL);
