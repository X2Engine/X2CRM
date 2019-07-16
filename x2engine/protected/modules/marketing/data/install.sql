DROP TABLE IF EXISTS `x2_campaigns`,`x2_campaigns_attachments`,`x2_web_forms`;
/*&*/
CREATE TABLE x2_campaigns (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    masterId     INT UNSIGNED NULL,
    name         VARCHAR(250) NOT NULL,
    nameId       VARCHAR(250) DEFAULT NULL,
    assignedTo   VARCHAR(50),
    email        VARCHAR(250),
    phone        VARCHAR(40),
    leadstatus   VARCHAR(250),
    listId       VARCHAR(100),
    suppressionListId VARCHAR(100),
    active       TINYINT DEFAULT 1,
    description  TEXT,
    type         VARCHAR(100) DEFAULT NULL,
    cost         VARCHAR(100) DEFAULT NULL,
    leadSource   VARCHAR(40) DEFAULT NULL,
    template     VARCHAR(250) DEFAULT '0',
    subject      VARCHAR(250),
    content      TEXT,
    createdBy    VARCHAR(50) NOT NULL,
    complete     TINYINT DEFAULT 0,
    visibility   INT NOT NULL,
    createDate   BIGINT NOT NULL,
    launchDate   BIGINT,
    lastUpdated  BIGINT NOT NULL,
    lastActivity BIGINT,
    updatedBy    VARCHAR(50),
    sendAs       INT DEFAULT NULL,
    bouncedAccount INT DEFAULT NULL,
    enableRedirectLinks TINYINT DEFAULT 0,
    enableBounceHandling TINYINT NOT NULL DEFAULT 0,
    openRate     FLOAT DEFAULT NULL,
    clickRate    FLOAT DEFAULT NULL,
    unsubscribeRate FLOAT DEFAULT NULL,
    category     VARCHAR(250) DEFAULT 'Marketing',
    categoryListId    VARCHAR(100),
    parent VARCHAR(250),
    children VARCHAR(250),
    PRIMARY KEY (id),
    UNIQUE (nameId),
    INDEX(listId),
    INDEX(suppressionListId),
    INDEX(template),
    FOREIGN KEY (masterId) REFERENCES x2_campaigns(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_campaigns_attachments (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    campaign INT UNSIGNED,
    media    INT UNSIGNED,
    PRIMARY KEY (id)
) COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_web_forms(
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`               VARCHAR(100) NOT NULL,
    `type`               VARCHAR(100) NOT NULL,
    description          VARCHAR(255) DEFAULT NULL,
    modelName            VARCHAR(100) DEFAULT NULL,
    fields               TEXT,
    params               TEXT,
    css                  TEXT,
    header               TEXT,
    visibility           INT NOT NULL,
    assignedTo           VARCHAR(50) NOT NULL,
    createdBy            VARCHAR(50) NOT NULL,
    updatedBy            VARCHAR(50) NOT NULL,
    createDate           BIGINT NOT NULL,
    lastUpdated          BIGINT NOT NULL,
    userEmailTemplate    INT,
    webleadEmailTemplate INT,
    leadSource           VARCHAR(100),
    redirectUrl          VARCHAR(255),
    generateLead         TINYINT DEFAULT 0,
    generateAccount      TINYINT DEFAULT 0,
    requireCaptcha       TINYINT DEFAULT 0,
    fingerprintDetection TINYINT DEFAULT 1,
    thankYouText         TEXT DEFAULT NULL,
    PRIMARY KEY (id)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('marketing', 'Marketing', 1, 3, 0, 1, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe,keyType, description)
VALUES
('Campaign', 'id',           'ID',                 0, 0, 'int',         0, 0, NULL,              0, 0, '',       1, 1, 'PRI', NULL),
('Campaign', 'masterId',     'Master Campaign ID', 0, 0, 'int',         0, 0, NULL,              0, 0, '',       0, 1, 'FIX', NULL),
('Campaign', 'name',         'Name',               0, 0, 'varchar',     1, 0, NULL,              1, 0, 'High',   0, 1, NULL, NULL),
('Campaign', 'nameId',       'NameID',             0, 0, 'varchar',     0, 1, NULL,              1, 0, 'High',   0, 1, 'FIX', NULL),
('Campaign', 'assignedTo',   'Assigned To',        0, 0, 'assignment',  1, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'listId',       'Contact List',       0, 0, 'link',        1, 0, 'X2List',          0, 0, '',       0, 1, 'MUL', NULL),
('Campaign', 'suppressionListId',
                             'Suppression List',   0, 0, 'link',        0, 0, 'X2List',          0, 0, '',       0, 1, 'MUL', NULL),
('Campaign', 'categoryListId',
                             'category List Id',   0, 0, 'link',        0, 0, 'X2List',          0, 0, '',       0, 1, 'MUL', NULL),
('Campaign', 'category',       'Category',       0, 0, 'dropdown',        0, 0, '155',          0, 0, '',       0, 1, 'MUL', NULL),
('Campaign', 'active',       'Active',             0, 0, 'boolean',     0, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'description',  'Description',        0, 0, 'text',        0, 0, NULL,              1, 0, 'Medium', 0, 1, NULL, NULL),
('Campaign', 'type',         'Type',               0, 0, 'dropdown',    0, 0, '107',             0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'template',     'Template',           0, 0, 'link',        0, 0, 'Docs',            0, 0, '',       0, 1, 'MUL', NULL),
('Campaign', 'enableRedirectLinks','Enable redirect links?',     
                                                   0, 0, 'boolean',     0, 0, NULL,              0, 0, '',       0, 1, NULL, "When this is enabled, all links in the email template will be replaced with links that will track when they have been clicked."),
('Campaign', 'cost',         'Cost',               0, 0, 'varchar',     0, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'subject',      'Subject',            0, 0, 'varchar',     0, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'content',      'Content',            0, 0, 'text',        0, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'complete',     'Complete',           0, 0, 'boolean',     0, 1, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'visibility',   'Visibility',         0, 0, 'visibility',  1, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'createDate',   'Create Date',        0, 0, 'dateTime',    0, 1, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'launchDate',   'Launch Date',        0, 0, 'dateTime',    0, 0, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'lastUpdated',  'Last Updated',       0, 0, 'dateTime',    0, 1, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'lastActivity', 'Last Activity',      0, 0, 'dateTime',    0, 1, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'updatedBy',    'Updated By',         0, 0, 'assignment',  0, 1, NULL,              0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'sendAs',       'Send As',            0, 0, 'credentials', 0, 0, 'email:bulkEmail', 0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'openRate',     'Open Rate',          0, 0, 'percentage',  0, 1, NULL,              0, 0, '',       0, 0, NULL, NULL),
('Campaign', 'clickRate',    'Click Rate',         0, 0, 'percentage',  0, 1, NULL,              0, 0, '',       0, 0, NULL, NULL),
('Campaign', 'bouncedAccount',
                             'Bounce Handling Account',
                                                   0, 0, 'credentials', 0, 0, 'email:bulkEmail:bounced', 0, 0, '',       0, 1, NULL, NULL),
('Campaign', 'enableBounceHandling',
                             'Enable bounce handling?',     
                                                   0, 0, 'boolean',     0, 0, NULL,              0, 0, '',       0, 1, NULL, "When this is enabled, all emails which are sent by campign but unable to deliver to receiver will be catched and reason will be analysed."),
('Campaign', 'unsubscribeRate','Unsubscribe Rate', 0, 0, 'percentage',  0, 1, NULL,              0, 0, '',       0, 0, NULL, NULL);
/*&*/
INSERT INTO `x2_form_layouts`
(`id`, `model`, `version`, `scenario`, `layout`, `defaultView`, `defaultForm`, `createDate`, `lastUpdated`)
VALUES
(13,'Campaign','Form','Default','{"version":"5.2","sections":[{"rows":[{"cols":[{"items":[{"name":"formItem_description","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_visibility","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_category","labelType":"left","readOnly":0},{"name":"formItem_enableRedirectLinks","labelType":"left","readOnly":"0","tabindex":"undefined"}],"width":"50.16%"},{"items":[{"name":"formItem_type","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_sendAs","labelType":"left","readOnly":"0","tabindex":"undefined"},{"name":"formItem_enableBounceHandling","labelType":"left","readOnly":0},{"name":"formItem_bouncedAccount","labelType":"left","readOnly":0}],"width":"49.45%"}]}],"collapsible":false,"title":"Info"},{"rows":[{"cols":[{"items":[{"name":"formItem_subject","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_content","labelType":"none","readOnly":"0","tabindex":"0"}],"width":"99.81%"}]}],"collapsible":false,"title":"Email Template"}]}',0,1,1429041237,1429041237),
(14, 'Campaign','View','Default','{"version":"5.2","sections":[{"rows":[{"cols":[{"items":[{"name":"formItem_name","labelType":"left","readOnly":0},{"name":"formItem_listId","labelType":"left","readOnly":0},{"name":"formItem_description","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_category","labelType":"left","readOnly":0},{"name":"formItem_enableRedirectLinks","labelType":"left","readOnly":"0","tabindex":"undefined"}],"width":"50.16%"},{"items":[{"name":"formItem_visibility","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_suppressionListId","labelType":"left","readOnly":0},{"name":"formItem_type","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_sendAs","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_bouncedAccount","labelType":"left","readOnly":0},{"name":"formItem_enableBounceHandling","labelType":"left","readOnly":0}],"width":"49.45%"}]}],"collapsible":false,"title":"Info"},{"rows":[{"cols":[{"items":[{"name":"formItem_active","labelType":"left","readOnly":0},{"name":"formItem_complete","labelType":"left","readOnly":0},{"name":"formItem_launchDate","labelType":"left","readOnly":0},{"name":"formItem_openRate","labelType":"left","readOnly":1},{"name":"formItem_clickRate","labelType":"left","readOnly":1},{"name":"formItem_unsubscribeRate","labelType":"left","readOnly":1}],"width":"99.81%"}]}],"collapsible":false,"title":"Status"},{"rows":[{"cols":[{"items":[{"name":"formItem_subject","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_content","labelType":"none","readOnly":"0","tabindex":"0"}],"width":"99.81%"}]}],"collapsible":true,"collapsedByDefault":false,"title":"Email Template"}]}',1,0,1429041237,1429041237);
