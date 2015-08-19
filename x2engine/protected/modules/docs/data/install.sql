DROP TABLE IF EXISTS `x2_docs`;
/*&*/
DROP TABLE IF EXISTS `x2_doc_folders`;
/*&*/
CREATE TABLE `x2_doc_folders` (
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(250) NOT NULL,
    createdBy       VARCHAR(250),
    createDate      BIGINT,
    lastUpdated     BIGINT,
    updatedBy       VARCHAR(250),
    visibility      TINYINT DEFAULT 1,
    parentFolder    INT,
    FOREIGN KEY (parentFolder) REFERENCES x2_doc_folders(id) ON UPDATE CASCADE ON DELETE CASCADE
) Engine = InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE `x2_docs` (
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    nameId          VARCHAR(250) DEFAULT NULL,
    subject         VARCHAR(255),
    emailTo         TEXT, 
    type            VARCHAR(10) NOT NULL DEFAULT '',
    associationType VARCHAR(250) DEFAULT NULL,
    text            LONGTEXT NOT NULL,
    createdBy       VARCHAR(60) DEFAULT NULL,
    createDate      BIGINT,
    updatedBy       VARCHAR(50),
    lastUpdated     BIGINT,
    visibility      TINYINT,
    folderId        INT DEFAULT NULL,
    UNIQUE(nameId),
    FOREIGN KEY (folderId) REFERENCES x2_doc_folders(id) ON UPDATE CASCADE ON DELETE CASCADE
) Engine = InnoDB COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('docs', 'Docs', 1, 11, 0, 0, 0, 0, 0);
/*&*/
INSERT INTO `x2_fields`
(`modelName`, `fieldName`, `attributeLabel`, `modified`, `custom`, `type`, `required`, `readOnly`, `linkType`, `searchable`, `relevance`, `isVirtual`,`keyType`)
VALUES
('Docs', 'createDate',      'Created',          0, 0, 'date',       0, 1, NULL,   0, NULL, 0, NULL),
('Docs', 'createdBy',       'Created By',       0, 0, 'varchar',    0, 1, NULL,   0, NULL, 0, NULL),
('Docs', 'id',              'ID',               0, 0, 'int',        0, 1, NULL,   0, NULL, 0, 'PRI'),
('Docs', 'lastUpdated',     'Last Updated',     0, 0, 'date',       0, 1, NULL,   0, NULL, 0, NULL),
('Docs', 'name',            'Name',             0, 0, 'varchar',    1, 0, NULL,   0, NULL, 0, NULL),
('Docs', 'nameId',          'NameID',           0, 0, 'varchar',    0, 1, NULL,   0, NULL, 0, 'FIX'),
('Docs', 'subject',         'Subject',          0, 0, 'varchar',    0, 0, NULL,   0, NULL, 0, NULL),
('Docs', 'emailTo',         'To:',              0, 0, 'text',       0, 0, NULL,   0, NULL, 0, NULL),
('Docs', 'text',            'Body',             0, 0, 'text',       1, 0, NULL,   0, NULL, 0, NULL),
('Docs', 'type',            'Type',             0, 0, 'varchar',    0, 0, NULL,   0, NULL, 0, NULL),
('Docs', 'associationType', 'Record Type',      0, 0, 'varchar',    0, 0, NULL,   0, NULL, 0, NULL),
('Docs', 'updatedBy',       'Updated By',       0, 0, 'assignment', 0, 1, NULL,   0, NULL, 0, NULL),
('Docs', 'visibility',      'Visibility',       0, 0, 'visibility', 0, 0, NULL,   0, NULL, 0, NULL);
/*&*/
INSERT INTO `x2_doc_folders` (`id`, `name`, `visibility`) VALUES (-1, 'Templates', 1);
