DROP TABLE IF EXISTS `x2_opportunities`;
/*&*/
CREATE TABLE x2_opportunities(
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`             VARCHAR(255)    NOT NULL,
    nameId             VARCHAR(250) DEFAULT NULL,
    firstName          VARCHAR(255),
    lastName           VARCHAR(255),
    accountName        VARCHAR(100),
    contactName        VARCHAR(100),
    email              VARCHAR(250),
    phone              VARCHAR(40),
    leadstatus         VARCHAR(250),
    modelName          VARCHAR(100),
    quoteAmount        DECIMAL(18,2),
    salesStage         VARCHAR(20),
    expectedCloseDate  BIGINT,
    probability        FLOAT,
    leadSource         VARCHAR(100),
    description        TEXT,
    assignedTo         TEXT,
    createDate         BIGINT,
    visibility         TINYINT DEFAULT 1,
    associatedContacts TEXT,
    lastUpdated        BIGINT,
    lastActivity       BIGINT,
    updatedBy          VARCHAR(50),
    doNotCall          TINYINT DEFAULT 0,
    doNotEmail         TINYINT DEFAULT 0,

    businessEmail      VARCHAR(250),
    personalEmail      VARCHAR(250),
    alternativeEmail   VARCHAR(250),
    preferredEmail     VARCHAR(250),

    UNIQUE(nameId),
    INDEX (email),
    INDEX(accountName)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('opportunities', 'Opportunities', 1, 5, 1, 1, 0, 0, 0);
/*&*/
INSERT INTO `x2_mobile_layouts`
(`modelName`, `layout`, `defaultView`, `defaultForm`, `version`)
VALUES
('Opportunity', '["name","accountName","salesStage","leadSource","expectedCloseDate","quoteAmount","probability","description","assignedTo"]', 1, 0, '5.4'),
('Opportunity', '["name","accountName","salesStage","leadSource","expectedCloseDate","quoteAmount","probability","description","assignedTo"]', 0, 1, '5.4');
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Opportunity', 'id',                'ID',                  0, 0, 'varchar',    0, 0, NULL,       0, 0, '',       1, 1, 'PRI'),
('Opportunity', 'name',              'Name',                0, 0, 'varchar',    1, 0, NULL,       1, 0, 'High',   0, 1, NULL),
('Opportunity', 'nameId',            'NameID',              0, 0, 'varchar',    0, 1, NULL,       1, 0, 'High',   0, 1, 'FIX'),
('Opportunity', 'accountName',       'Account',             0, 0, 'link',       0, 0, 'Accounts', 0, 0, '',       0, 1, 'MUL'),
('Opportunity', 'contactName',       'Contact',             0, 0, 'link',       0, 0, 'Contacts', 0, 0, '',       0, 1, 'MUL'),
('Opportunity', 'quoteAmount',       'Quote Amount',        0, 0, 'currency',   0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'email',             'Email',               0, 0, 'email',      0, 0, NULL,       1, 0, 'Medium', 0, 1, 'MUL'),
('Opportunity', 'phone',             'Phone',               0, 0, 'phone',      0, 0, NULL,       1, 0, 'Medium', 0, 1, NULL),
('Opportunity', 'leadstatus',        'Lead Status',         0, 0, 'dropdown',   0, 0, '104',      0, 0, '',       0, 1, NULL),
('Opportunity', 'modelName',         'Model Name',          0, 0, 'varchar',    0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'salesStage',        'Sales Stage',         0, 0, 'dropdown',   0, 0, '105',      0, 0, '',       0, 1, NULL),
('Opportunity', 'expectedCloseDate', 'Expected Close Date', 0, 0, 'date',       0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'probability',       'Probability',         0, 0, 'percentage', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'leadSource',        'Lead Source',         0, 0, 'dropdown',   0, 0, '103',      0, 0, '',       0, 1, NULL),
('Opportunity', 'description',       'Description',         0, 0, 'text',       0, 0, NULL,       1, 0, 'Medium', 0, 1, NULL),
('Opportunity', 'assignedTo',        'Assigned To',         0, 0, 'assignment', 0, 0, 'multiple', 0, 0, '',       0, 1, NULL),
('Opportunity', 'visibility',        'Visibility',          0, 0, 'visibility', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'createDate',        'Create Date',         0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'lastUpdated',       'Last Updated',        0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'lastActivity',      'Last Activity',       0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'updatedBy',         'Updated By',          0, 0, 'varchar',    0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'doNotCall',         'Do Not Call',         0, 0, 'boolean',    0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'doNotEmail',        'Do Not Email',        0, 0, 'boolean',    0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Opportunity', 'businessEmail',     'Business Email',      0, 0, 'email',      0, 0, NULL,       1, 0, 'Medium', 0, 1, 'MUL'),
('Opportunity', 'personalEmail',     'Personal Email',      0, 0, 'email',      0, 0, NULL,       1, 0, 'Medium', 0, 1, 'MUL'),
('Opportunity', 'alternativeEmail',  'Alternative Email',   0, 0, 'email',      0, 0, NULL,       1, 0, 'Medium', 0, 1, 'MUL'),
('Opportunity', 'preferredEmail',    'Preferred Email',     0, 0, 'dropdown',   0, 0, -1,         0, 0, '',       0, 1, NULL);
