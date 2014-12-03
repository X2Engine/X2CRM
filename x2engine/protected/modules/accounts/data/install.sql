DROP TABLE IF EXISTS `x2_accounts`;
/*&*/
CREATE TABLE `x2_accounts` (
    id                 INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`             VARCHAR(255) NOT NULL,
    nameId             VARCHAR(250) DEFAULT NULL,
    website            VARCHAR(255),
    `type`             VARCHAR(255),
    visibility         TINYINT DEFAULT 1,
    annualRevenue      DECIMAL(18,2),
    phone              VARCHAR(40),
    tickerSymbol       VARCHAR(10),
    employees          INT,
    address            VARCHAR(250),
    city               VARCHAR(250),
    `state`            VARCHAR(250),
    country            VARCHAR(250),
    zipcode            VARCHAR(250),
    parentAccount      VARCHAR(250),
    primaryContact     VARCHAR(250),
    assignedTo         TEXT,
    createDate         BIGINT,
    associatedContacts TEXT,
    description        TEXT,
    lastUpdated        BIGINT,
    lastActivity       BIGINT,
    updatedBy          VARCHAR(50),
    dupeCheck          TINYINT DEFAULT 0,

    /* sales and marketing attributes */
    leadtype       VARCHAR(250),
    leadSource     VARCHAR(40),
    leadstatus     VARCHAR(250),
    leadDate       BIGINT,
    leadscore      INT,
    interest       VARCHAR(250),
    dealvalue      DECIMAL(18,2),
    closedate      BIGINT,
    rating         TINYINT,
    dealstatus     VARCHAR(250),
    expectedCloseDate  BIGINT,


    UNIQUE(nameId),
    INDEX(parentAccount),
    INDEX(primaryContact)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('accounts', 'Accounts', 1, 2, 1, 1, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES

/* sales and marketing fields */
('Accounts', 'leadtype',       'Lead Type',        0, 0, 'dropdown',   0, 0, '102',        0, 0, '',       0, 1, NULL),
('Accounts', 'leadSource',     'Lead Source',      0, 0, 'dropdown',   0, 0, '103',        0, 0, '',       0, 1, NULL),
('Accounts', 'leadstatus',     'Lead Status',      0, 0, 'dropdown',   0, 0, '104',        0, 0, '',       0, 1, NULL),
('Accounts', 'leadDate',       'Lead Date',        0, 0, 'date',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Accounts', 'leadscore',      'Lead Score',       0, 0, 'rating',     0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Accounts', 'interest',       'Interest',         0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Accounts', 'dealvalue',      'Deal Value',       0, 0, 'currency',   0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Accounts', 'closedate',      'Close Date',       0, 0, 'date',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Accounts', 'rating',         'Confidence',       0, 0, 'rating',     0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Accounts', 'dealstatus',     'Deal Status',      0, 0, 'dropdown',   0, 0, '105',        0, 0, '',       0, 1, NULL),
('Accounts', 'expectedCloseDate', 'Expected Close Date', 0, 0, 'date',       0, 0, NULL,       0, 0, '',       0, 1, NULL),

('Accounts', 'name',           'Name',            0, 0, 'varchar',    1, 0, NULL,       1, 0, 'High',   0, 1, NULL),
('Accounts', 'nameId',         'NameID',          0, 0, 'varchar',    0, 1, NULL,       1, 0, 'High',   0, 1, 'FIX'),
('Accounts', 'id',             'ID',              0, 0, 'varchar',    0, 1, NULL,       0, 0, '',       1, 1, 'PRI'),
('Accounts', 'website',        'Website',         0, 0, 'url',        0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'type',           'Type',            0, 0, 'varchar',    0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'visibility',     'Visibility',      0, 0, 'visibility', 0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'annualRevenue',  'Revenue',         0, 0, 'currency',   0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'phone',          'Phone',           0, 0, 'phone',      0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'tickerSymbol',   'Symbol',          0, 0, 'varchar',    0, 0, NULL,       1, 0, 'High',   0, 1, NULL),
('Accounts', 'address',        'Address',         0, 0, 'varchar',    0, 0, NULL,       1, 0, '',       0, 1, NULL),
('Accounts', 'city',           'City',            0, 0, 'varchar',    0, 0, NULL,       1, 0, '',       0, 1, NULL),
('Accounts', 'state',          'State',           0, 0, 'varchar',    0, 0, NULL,       1, 0, '',       0, 1, NULL),
('Accounts', 'country',        'Country',         0, 0, 'varchar',    0, 0, NULL,       1, 0, '',       0, 1, NULL),
('Accounts', 'zipcode',        'Postal Code',     0, 0, 'varchar',    0, 0, NULL,       1, 0, '',       0, 1, NULL),
('Accounts', 'parentAccount',  'Parent',          0, 0, 'link',       0, 0, 'Accounts', 1, 0, '',       0, 1, 'MUL'),
('Accounts', 'primaryContact', 'Primary Contact', 0, 0, 'link',       0, 0, 'Contacts', 1, 0, '',       0, 1, 'MUL'),
('Accounts', 'employees',      'Employees',       0, 0, 'int',        0, 0, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'assignedTo',     'Assigned To',     0, 0, 'assignment', 0, 0, 'multiple', 0, 0, '',       0, 1, NULL),
('Accounts', 'createDate',     'Create Date',     0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'description',    'Description',     0, 0, 'text',       0, 0, NULL,       1, 0, 'Medium', 0, 1, NULL),
('Accounts', 'lastUpdated',    'Last Updated',    0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'lastActivity',   'Last Activity',   0, 0, 'dateTime',   0, 1, NULL,       0, 0, '',       0, 1, NULL),
('Accounts', 'updatedBy',      'Updated By',      0, 0, 'varchar',    0, 1, NULL,       0, 0, '',       0, 1, NULL);

