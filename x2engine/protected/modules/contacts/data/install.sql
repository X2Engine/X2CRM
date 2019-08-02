DROP TABLE IF EXISTS `x2_contacts`,`x2_subscribe_contacts`;
/*&*/
CREATE TABLE `x2_contacts` (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(255),
    nameId         VARCHAR(250) DEFAULT NULL,
    firstName      VARCHAR(255) NOT NULL,
    lastName       VARCHAR(255) NOT NULL,
    title          VARCHAR(100),
    company        VARCHAR(250),
    phone          VARCHAR(40),
    phone2         VARCHAR(40),
    email          VARCHAR(250),
    website        VARCHAR(250),
    address        VARCHAR(250),
    address2       VARCHAR(250),
    city           VARCHAR(40),
    `state`        VARCHAR(40),
    zipcode        VARCHAR(20),
    country        VARCHAR(40),
    visibility     INT NOT NULL,
    assignedTo     VARCHAR(50),
    backgroundInfo TEXT,
    twitter        VARCHAR(50) NULL,
    linkedin       VARCHAR(100) NULL,
    skype          VARCHAR(32) NULL,
    googleplus     VARCHAR(100) NULL,
    lastUpdated    BIGINT,
    lastActivity   BIGINT,
    updatedBy      VARCHAR(50),
    priority       VARCHAR(40),
    leadSource     VARCHAR(40),
    leadDate       BIGINT,
    rating         TINYINT,
    createDate     BIGINT,
    facebook       VARCHAR(100) NULL,
    otherUrl       VARCHAR(100) NULL,
    leadtype       VARCHAR(250),
    closedate      BIGINT,
    expectedCloseDate  BIGINT,
    interest       VARCHAR(250),
    leadstatus     VARCHAR(250),
    dealvalue      DECIMAL(18,2),
    leadscore      INT,
    dealstatus     VARCHAR(250),
    timezone       VARCHAR(250) NULL,
    doNotCall      TINYINT DEFAULT 0,
    doNotEmail     TINYINT DEFAULT 0,
    trackingKey    VARCHAR(32),
    dupeCheck      INT DEFAULT 0,
    fingerprintId  INT DEFAULT NULL,
    accountName        VARCHAR(100),


    businessEmail              VARCHAR(250),
    personalEmail              VARCHAR(250),
    alternativeEmail              VARCHAR(250),
    preferredEmail              VARCHAR(250),

    UNIQUE (nameId),
    INDEX (email),
    INDEX (assignedTo),
    INDEX (company)
) COLLATE = utf8_general_ci;
/*&*/
/* These have foreign key constraints in them and should thus be dropped first: */
DROP TABLE IF EXISTS x2_list_criteria,x2_list_items;
/*&*/
DROP TABLE IF EXISTS x2_lists;
/*&*/
CREATE TABLE x2_lists (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    assignedTo  VARCHAR(255),
    name        VARCHAR(238) NOT NULL,
    nameId      VARCHAR(250) DEFAULT NULL,
    description VARCHAR(250) NULL,
    type        VARCHAR(20) NULL,
    logicType   VARCHAR(20) DEFAULT "AND",
    modelName   VARCHAR(100),
    visibility  INT NOT NULL DEFAULT 1,
    count       INT UNSIGNED NOT NULL DEFAULT 0,
    createDate  BIGINT NOT NULL,
    lastUpdated BIGINT NOT NULL,
    INDEX(assignedTo),
    INDEX(type),
    UNIQUE(nameId)
) ENGINE InnoDB COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_list_criteria (
    id         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    listId     INT UNSIGNED NOT NULL,
    type       VARCHAR(20) NULL,
    attribute  VARCHAR(40) NULL,
    comparison VARCHAR(10) NULL,
    value      VARCHAR(255) NOT NULL,
    INDEX (listId),
    FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_list_items (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    emailAddress VARCHAR(255) NULL,
    contactId    INT UNSIGNED,
    listId       INT UNSIGNED NOT NULL,
    uniqueId     VARCHAR(32) NULL,
    sent         INT NOT NULL DEFAULT 0,
    opened       INT UNSIGNED NOT NULL DEFAULT 0,
    clicked      INT UNSIGNED NOT NULL DEFAULT 0,
    unsubscribed INT UNSIGNED NOT NULL DEFAULT 0,
    sending      TINYINT NOT NULL DEFAULT 0,
    suppressed   INT NOT NULL DEFAULT 0,
    bounced      INT NOT NULL DEFAULT 0,
    urls         TEXT DEFAULT NULL,
    INDEX (listId),
    INDEX (uniqueId),
    FOREIGN KEY (listId) REFERENCES x2_lists(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_subscribe_contacts(
    contact_id INT UNSIGNED,
    user_id    INT UNSIGNED
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable, enableRecordAliasing)
VALUES
('contacts', 'Contacts', 1, 1, 1, 1, 0, 0, 0, 1);
/*&*/
INSERT INTO `x2_mobile_layouts`
(`modelName`, `layout`, `defaultView`, `defaultForm`, `version`)
VALUES
('Contacts', '["name","company","title","email","phone","backgroundInfo","address","city","state","zipcode","country","assignedTo","visibility"]', 1, 0, '5.4'),
('Contacts', '["firstName","lastName","company","title","email","phone","backgroundInfo","address","city","state","zipcode","country","assignedTo","visibility"]', 0, 1, '5.4');
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Contacts', 'leadscore',      'Lead Score',             0, 0, 'rating',     0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'dealstatus',     'Deal Status',            0, 0, 'dropdown',   0, 0, '105',        0, 0, '',       0, 1, NULL),
('Contacts', 'id',             'ID',                     0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       1, 1, 'PRI'),
('Contacts', 'name',           'Full Name',              0, 0, 'varchar',    0, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('Contacts', 'nameId',         'NameID',                 0, 0, 'varchar',    0, 1, NULL,         1, 0, 'High',   0, 1, 'FIX'),
('Contacts', 'firstName',      'First Name',             0, 0, 'varchar',    1, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('Contacts', 'lastName',       'Last Name',              0, 0, 'varchar',    1, 0, NULL,         1, 0, 'High',   0, 1, NULL),
('Contacts', 'title',          'Title',                  0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'company',        'Company',                0, 0, 'link',       0, 0, 'Accounts',   0, 0, '',       0, 1, 'MUL'),
('Contacts', 'phone',          'Phone',                  0, 0, 'phone',      0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'phone2',         'Phone 2',                0, 0, 'phone',      0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'email',          'Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('Contacts', 'website',        'Website',                0, 0, 'url',        0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'twitter',        'Twitter',                0, 0, 'url',        0, 0, 'twitter',    0, 0, '',       0, 1, NULL),
('Contacts', 'linkedin',       'Linkedin',               0, 0, 'url',        0, 0, 'linkedin',   0, 0, '',       0, 1, NULL),
('Contacts', 'skype',          'Skype',                  0, 0, 'url',        0, 0, 'skype',      0, 0, '',       0, 1, NULL),
('Contacts', 'googleplus',     'Googleplus',             0, 0, 'url',        0, 0, 'googleplus', 0, 0, '',       0, 1, NULL),
('Contacts', 'address',        'Address',                0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'address2',       'Address 2',              0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'city',           'City',                   0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'state',          'State',                  0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'zipcode',        'Postal Code',            0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'country',        'Country',                0, 0, 'varchar',    0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'visibility',     'Visibility',             0, 0, 'visibility', 1, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'assignedTo',     'Assigned To',            0, 0, 'assignment', 0, 0, NULL,         0, 0, '',       0, 1, 'MUL'),
('Contacts', 'backgroundInfo', 'Background Info',        0, 0, 'text',       0, 0, NULL,         1, 0, 'Medium', 0, 1, NULL),
('Contacts', 'lastUpdated',    'Last Updated',           0, 0, 'dateTime',   0, 1, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'lastActivity',   'Last Activity',          0, 0, 'dateTime',   0, 1, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'updatedBy',      'Updated By',             0, 0, 'varchar',    0, 1, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'leadSource',     'Lead Source',            0, 0, 'dropdown',   0, 0, '103',        0, 0, '',       0, 1, NULL),
('Contacts', 'leadDate',       'Lead Date',              0, 0, 'date',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'priority',       'Priority',               0, 0, 'dropdown',   0, 0, 124,          0, 0, '',       0, 1, NULL),
('Contacts', 'rating',         'Confidence',             0, 0, 'rating',     0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'createDate',     'Create Date',            0, 0, 'dateTime',   0, 1, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'facebook',       'Facebook',               0, 0, 'url',        0, 0, 'facebook',   0, 0, '',       0, 1, NULL),
('Contacts', 'otherUrl',       'Other',                  0, 0, 'url',        0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'leadtype',       'Lead Type',              0, 0, 'dropdown',   0, 0, '102',        0, 0, '',       0, 1, NULL),
('Contacts', 'closedate',      'Close Date',             0, 0, 'date',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'expectedCloseDate', 'Expected Close Date', 0, 0, 'date',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'interest',       'Interest',               0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'dealvalue',      'Deal Value',             0, 0, 'currency',   0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'leadstatus',     'Lead Status',            0, 0, 'dropdown',   0, 0, '104',        0, 0, '',       0, 1, NULL),
('Contacts', 'doNotCall',      'Do Not Call',            0, 0, 'boolean',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'timezone',       'Timezone',               0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'dupeCheck',      'Duplicate Check',        0, 0, 'boolean',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'doNotEmail',     'Do Not Email',           0, 0, 'boolean',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'trackingKey',    'Web Tracking Key',       0, 0, 'varchar',    0, 1, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'id',             'id',                     0, 0, 'varchar',    0, 1, NULL,         0, 0, '',       0, 1, 'PRI'),
('X2List',   'assignedTo',     'Assigned To',            0, 0, 'assignment', 0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'name',           'Name',                   0, 0, 'varchar',    1, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'nameId',         'NameId',                 0, 0, 'varchar',    0, 1, NULL,         0, 0, '',       0, 1, 'FIX'),
('X2List',   'description',    'Description',            0, 0, 'text',       0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'type',           'Type',                   0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'logicType',      'Logic Type',             0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'modelName',      'Model Name',             0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'visibility',     'Visibility',             0, 0, 'visibility', 0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'count',          'Count',                  0, 0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'createDate',     'Date Created',           0, 0, 'date',       0, 1, NULL,         0, 0, '',       0, 1, NULL),
('X2List',   'lastUpdated',    'lastUpdated',            0, 0, 'date',       0, 1, NULL,         0, 0, '',       0, 1, NULL),
('Contacts', 'accountName',       'Account',             0, 0, 'link',       0, 0, 'Accounts', 0, 0, '',       0, 1, 'MUL'),

('Contacts', 'businessEmail',            'Business Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('Contacts', 'personalEmail',            'Personal Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('Contacts', 'alternativeEmail',            'Alternative Email',                  0, 0, 'email',      0, 0, NULL,         1, 0, 'Medium', 0, 1, 'MUL'),
('Contacts', 'preferredEmail',          'Preferred Email',         0, 0, 'dropdown',    0, 0, -1, 0, 0, '',     0, 1, NULL);
