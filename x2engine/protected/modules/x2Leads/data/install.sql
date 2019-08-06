DROP TABLE IF EXISTS `x2_x2leads`;
/*&*/
CREATE TABLE x2_x2leads(
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`             VARCHAR(255)    NOT NULL,
    firstName          VARCHAR(255) NOT NULL,
    lastName           VARCHAR(255) NOT NULL,
    nameId             VARCHAR(250) DEFAULT NULL,
    accountName        VARCHAR(100),
    title              VARCHAR(100),
    company            VARCHAR(250),
    email              VARCHAR(250),
    phone              VARCHAR(40),
    phone2             VARCHAR(40),
    address            VARCHAR(250),
    address2           VARCHAR(250),
    city               VARCHAR(40),
    `state`            VARCHAR(40),
    zipcode            VARCHAR(20),
    country            VARCHAR(40),
    backgroundInfo TEXT,
    priority           VARCHAR(40),
    leadtype           VARCHAR(250),
    dealstatus         VARCHAR(250),
    trackingKey        VARCHAR(32),
    dupeCheck          INT DEFAULT 0,
    leadstatus         VARCHAR(250),
    quoteAmount        DECIMAL(18,2),
    salesStage         VARCHAR(20),
    expectedCloseDate  BIGINT,
    probability        FLOAT,
    website            VARCHAR(250),
    leadSource         VARCHAR(100),
    description        TEXT,
    assignedTo         TEXT,
    createDate         BIGINT,
    visibility         TINYINT DEFAULT 1,
    lastUpdated        BIGINT,
    lastActivity       BIGINT,
    updatedBy          VARCHAR(50),
    converted          TINYINT DEFAULT 0,
    conversionDate     BIGINT,
    convertedToType    VARCHAR(255),
    convertedToId      INT,

    doNotCall      TINYINT DEFAULT 0,
    doNotEmail     TINYINT DEFAULT 0,
  


   
    twitter        VARCHAR(50) NULL,
    linkedin       VARCHAR(100) NULL,
    skype          VARCHAR(32) NULL,
    googleplus     VARCHAR(100) NULL,
    facebook       VARCHAR(100) NULL,
    otherUrl       VARCHAR(100) NULL,
    leadDate       BIGINT,
    interest       VARCHAR(250),
    dealvalue      DECIMAL(18,2),
    leadscore      INT,


    businessEmail              VARCHAR(250),
    personalEmail              VARCHAR(250),
    alternativeEmail              VARCHAR(250),
    preferredEmail              VARCHAR(250),

    UNIQUE(nameId),
    INDEX (email),
    INDEX(accountName),
    INDEX (company)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('x2Leads', 'Leads', 1, 4, 1, 1, 0, 0, 0);
/*&*/
INSERT INTO `x2_mobile_layouts`
(`modelName`, `layout`, `defaultView`, `defaultForm`, `version`)
VALUES
('X2Leads', '["name","accountName","salesStage","leadSource","expectedCloseDate","quoteAmount","probability","description","assignedTo"]', 1, 0, '5.4'),
('X2Leads', '["firstName","lastName","accountName","salesStage","leadSource","expectedCloseDate","quoteAmount","probability","description","assignedTo"]', 0, 1, '5.4');
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('X2Leads', 'id',                'ID',                  0, 0, 'varchar',    0, 0, NULL,       0, 0, '',       1, 1, 'PRI'),
('X2Leads', 'name',              'Name',                0, 0, 'varchar',    0, 0, NULL,       1, 0, 'High',   0, 1, NULL),
('X2Leads', 'nameId',            'NameID',              0, 0, 'varchar',    0, 1, NULL,       1, 0, 'High',   0, 1, 'FIX'),
('X2Leads', 'firstName',      'First Name',             0, 0, 'varchar',    1, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('X2Leads', 'lastName',       'Last Name',              0, 0, 'varchar',    1, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('X2Leads', 'company',        'Company',                0, 0, 'link',       0, 0, 'Accounts',   0, 0, '',       0, 1, 'MUL'),
('X2Leads', 'phone2',         'Phone 2',                0, 0, 'phone',      0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'website',        'Website',                0, 0, 'url',        0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'address',        'Address',                0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'address2',       'Address 2',              0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'city',           'City',                   0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'state',          'State',                  0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'zipcode',        'Postal Code',            0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'country',        'Country',                0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'backgroundInfo', 'Background Info',        0, 0, 'text',       0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'priority',          'Priority',         0, 0, 'dropdown',    0, 0, 124, 0, 0, '',     0, 1, NULL),
('X2Leads', 'leadDate',       'Lead Date',              0, 0, 'date',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'leadtype',       'Lead Type',              0, 0, 'dropdown',   0, 0, '102',        0, 0, '',       0, 1, NULL),
('X2Leads', 'dealstatus',     'Deal Status',            0, 0, 'dropdown',   0, 0, '105',        0, 0, '',       0, 1, NULL),
('X2Leads', 'trackingKey',    'Web Tracking Key',       0, 0, 'varchar',    0, 1, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'dupeCheck',      'Duplicate Check',        0, 0, 'boolean',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'accountName',       'Account',             0, 0, 'link',       0, 0, 'Accounts', 0, 0, '',       0, 1, 'MUL'),
('X2Leads', 'email',            'Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('X2Leads', 'phone',          'Phone',                  0, 0, 'phone',      0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'leadstatus',     'Lead Status',            0, 0, 'dropdown',   0, 0, '104',        0, 0, '',       0, 1, NULL),
('X2Leads', 'quoteAmount',       'Quote Amount',        0, 0, 'currency',   0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'salesStage',        'Sales Stage',         0, 0, 'dropdown',   0, 0, '105',      0, 0, '',       0, 1, NULL),
('X2Leads', 'expectedCloseDate', 'Expected Close Date', 0, 0, 'date',       0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'conversionDate', 'Conversion Date', 0, 0, 'date',       0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'converted',        'Converted',          0, 0, 'boolean', 0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'convertedToType',        'Converted To',          0, 0, 'varchar', 0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'convertedToId',        'Converted To',          0, 0, 'varchar', 0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'probability',       'Probability',         0, 0, 'percentage', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'leadSource',        'Lead Source',         0, 0, 'dropdown',   0, 0, '103',      0, 0, '',       0, 1, NULL),
('X2Leads', 'description',       'Description',         0, 0, 'text',       0, 0, NULL,       1, 0, 'Medium', 0, 1, NULL),
('X2Leads', 'assignedTo',        'Assigned To',         0, 0, 'assignment', 0, 0, 'multiple', 0, 0, '',       0, 1, NULL),
('X2Leads', 'visibility',        'Visibility',          0, 0, 'visibility', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'createDate',        'Create Date',         0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'lastUpdated',       'Last Updated',        0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'lastActivity',      'Last Activity',       0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'updatedBy',         'Updated By',          0, 0, 'varchar',    0, 1, NULL,       0, 0, '',       0, 1, NULL),
('X2Leads', 'doNotCall',      'Do Not Call',            0, 0, 'boolean',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'doNotEmail',     'Do Not Email',           0, 0, 'boolean',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'title',          'Title',                  0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),



('X2Leads', 'twitter',        'Twitter',                0, 0, 'url',        0, 0, 'twitter',    0, 0, '',       0, 1, NULL),
('X2Leads', 'linkedin',       'Linkedin',               0, 0, 'url',        0, 0, 'linkedin',   0, 0, '',       0, 1, NULL),
('X2Leads', 'skype',          'Skype',                  0, 0, 'url',        0, 0, 'skype',      0, 0, '',       0, 1, NULL),
('X2Leads', 'googleplus',     'Googleplus',             0, 0, 'url',        0, 0, 'googleplus', 0, 0, '',       0, 1, NULL),
('X2Leads', 'facebook',       'Facebook',               0, 0, 'url',        0, 0, 'facebook',   0, 0, '',       0, 1, NULL),
('X2Leads', 'otherUrl',       'Other',                  0, 0, 'url',        0, 0, NULL,         0, 0, '',       0, 1, NULL),

('X2Leads', 'interest',       'Interest',               0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'dealvalue',      'Deal Value',             0, 0, 'currency',   0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2Leads', 'leadscore',      'Lead Score',             0, 0, 'rating',     0, 0, NULL,         0, 0, '',       0, 1, NULL),

('X2Leads', 'businessEmail',            'Business Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('X2Leads', 'personalEmail',            'Personal Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('X2Leads', 'alternativeEmail',            'Alternative Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('X2Leads', 'preferredEmail',          'Preferred Email',         0, 0, 'dropdown',    0, 0, -1, 0, 0, '',     0, 1, NULL);

