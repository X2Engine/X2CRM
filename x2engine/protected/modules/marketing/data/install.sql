DROP TABLE IF EXISTS `x2_campaigns`,`x2_campaigns_attachments`,`x2_web_forms`;
/*&*/
CREATE TABLE x2_campaigns (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    masterId     INT UNSIGNED NULL,
    name         VARCHAR(250) NOT NULL,
    nameId       VARCHAR(250) DEFAULT NULL,
    assignedTo   VARCHAR(50),
    listId       VARCHAR(100),
    active       TINYINT DEFAULT 1,
    description  TEXT,
    type         VARCHAR(100) DEFAULT NULL,
    cost         VARCHAR(100) DEFAULT NULL,
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
    enableRedirectLinks TINYINT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE (nameId),
    INDEX(listId),
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
    generateLead         TINYINT DEFAULT 0,
    generateAccount      TINYINT DEFAULT 0,
    PRIMARY KEY (id)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('marketing', 'Marketing', 1, 3, 0, 1, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe,keyType)
VALUES
('Campaign', 'id',           'ID',                 0, 0, 'int',         0, 0, NULL,              0, 0, '',       1, 1, 'PRI'),
('Campaign', 'masterId',     'Master Campaign ID', 0, 0, 'int',         0, 0, NULL,              0, 0, '',       0, 1, 'FIX'),
('Campaign', 'name',         'Name',               0, 0, 'varchar',     1, 0, NULL,              1, 0, 'High',   0, 1, NULL),
('Campaign', 'nameId',       'NameID',             0, 0, 'varchar',     0, 1, NULL,              1, 0, 'High',   0, 1, 'FIX'),
('Campaign', 'assignedTo',   'Assigned To',        0, 0, 'assignment',  1, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'listId',       'Contact List',       0, 0, 'link',        1, 0, 'X2List',          0, 0, '',       0, 1, 'MUL'),
('Campaign', 'active',       'Active',             0, 0, 'boolean',     0, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'description',  'Description',        0, 0, 'text',        0, 0, NULL,              1, 0, 'Medium', 0, 1, NULL),
('Campaign', 'type',         'Type',               0, 0, 'dropdown',    0, 0, '107',             0, 0, '',       0, 1, NULL),
('Campaign', 'template',     'Template',           0, 0, 'link',        0, 0, 'Docs',            0, 0, '',       0, 1, 'MUL'),
('Campaign', 'enableRedirectLinks',     'Enable redirect links?',           0, 0, 'boolean',        0, 0, NULL,            0, 0, '',       0, 1, NULL),
('Campaign', 'cost',         'Cost',               0, 0, 'varchar',     0, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'subject',      'Subject',            0, 0, 'varchar',     0, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'content',      'Content',            0, 0, 'text',        0, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'complete',     'Complete',           0, 0, 'boolean',     0, 1, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'visibility',   'Visibility',         0, 0, 'visibility',  1, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'createDate',   'Create Date',        0, 0, 'dateTime',    0, 1, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'launchDate',   'Launch Date',        0, 0, 'dateTime',    0, 0, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'lastUpdated',  'Last Updated',       0, 0, 'dateTime',    0, 1, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'lastActivity', 'Last Activity',      0, 0, 'dateTime',    0, 1, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'updatedBy',    'Updated By',         0, 0, 'assignment',  0, 1, NULL,              0, 0, '',       0, 1, NULL),
('Campaign', 'sendAs',       'Send As',            0, 0, 'credentials', 0, 0, 'email:bulkEmail', 0, 0, '',       0, 1, NULL);
/*&*/
INSERT INTO `x2_form_layouts`
(`id`, `model`, `version`, `scenario`, `layout`, `defaultView`, `defaultForm`, `createDate`, `lastUpdated`)
VALUES
(13,'Campaign','Form','Default','
{
    "sections": [
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_name", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "230"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "39", 
                                    "labelType": "left", 
                                    "name": "formItem_description", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "483"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_listId", 
                                    "readOnly": "0", 
                                    "tabindex": "NaN", 
                                    "width": "135"
                                }, 
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_type", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "135"
                                }, 
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_sendAs", 
                                    "readOnly": "undefined", 
                                    "tabindex": "undefined", 
                                    "width": "154"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_enableRedirectLinks", 
                                    "readOnly": "undefined", 
                                    "tabindex": "undefined", 
                                    "width": "154"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_subject", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "226"
                                }, 
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_template", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "133"
                                }, 
                                {
                                    "height": "229", 
                                    "labelType": "none", 
                                    "name": "formItem_content", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "563"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": "Email Template"
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "24", 
                                    "labelType": "left", 
                                    "name": "formItem_assignedTo", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "145"
                                }, 
                                {
                                    "height": "24", 
                                    "labelType": "left", 
                                    "name": "formItem_visibility", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "145"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }
    ], 
    "version": "3.2"
}
',0,1,1373388579,1373388579);

