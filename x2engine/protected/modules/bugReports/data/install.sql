DROP TABLE IF EXISTS x2_bug_reports;
/*&*/
CREATE TABLE x2_bug_reports(
    id           INT NOT NULL AUTO_INCREMENT primary key,
    assignedTo   VARCHAR(250),
    `name`       VARCHAR(250),
    nameId       VARCHAR(250) DEFAULT NULL,
    description  TEXT,
    createDate   INT,
    lastUpdated  INT,
    lastActivity BIGINT,
    updatedBy    VARCHAR(250),
    blocks       VARCHAR(250),
    duplicate    VARCHAR(250),
    `file`       VARCHAR(250),
    line         VARCHAR(250),
    phpVersion   VARCHAR(250),
    severity     VARCHAR(250),
    status       VARCHAR(250),
    subject      VARCHAR(250),
    `type`       VARCHAR(250),
    visibility   TINYINT,
    x2Version    VARCHAR(250),
    errorCode    VARCHAR(250),
    UNIQUE(nameId),
    INDEX(blocks),
    INDEX(duplicate)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('bugReports', 'Bug Reports', 1, 20, 1, 1, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('BugReports', 'id',           'ID',            0, 'int',        0, 0, NULL,         0, 0, '',       1, 1, 'PRI'),
('BugReports', 'name',         'Name',          0, 'varchar',    0, 0, NULL,         0, 0, 'High',   0, 1, NULL),
('BugReports', 'nameId',       'NameID',        0, 'varchar',    0, 1, NULL,         0, 0, 'High',   0, 1, 'FIX'),
('BugReports', 'assignedTo',   'Assigned To',   0, 'assignment', 0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'description',  'Description',   0, 'text',       0, 0, NULL,         0, 0, 'Medium', 0, 1, NULL),
('BugReports', 'createDate',   'Create Date',   0, 'dateTime',   0, 1, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'lastUpdated',  'Last Updated',  0, 'dateTime',   0, 1, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'lastActivity', 'Last Activity', 0, 'dateTime',   0, 1, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'updatedBy',    'Updated By',    0, 'assignment', 0, 1, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'blocks',       'Blocks',        0, 'link',       0, 0, 'BugReports', 0, 0, '',       0, 1, 'MUL'),
('BugReports', 'duplicate',    'Duplicate Of',  0, 'link',       0, 0, 'BugReports', 0, 0, '',       0, 1, 'MUL'),
('BugReports', 'file',         'File',          0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'line',         'Line',          0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'phpVersion',   'PHP Version',   0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'severity',     'Severity',      0, 'dropdown',   0, 0, 116,          0, 0, '',       0, 1, NULL),
('BugReports', 'status',       'Status',        0, 'dropdown',   0, 0, 115,          0, 0, '',       0, 1, NULL),
('BugReports', 'subject',      'Subject',       0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'type',         'Type',          0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'visibility',   'Visibility',    0, 'visibility', 0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'x2Version',    'X2 Version',    0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL),
('BugReports', 'errorCode',    'Error Code',    0, 'varchar',    0, 0, NULL,         0, 0, '',       0, 1, NULL);
/*&*/
INSERT INTO x2_form_layouts (id, model, version, layout, defaultView, defaultForm, createDate, lastUpdated) VALUES ('17', 'BugReports', 'Form', '{"version":"1.2","sections":[{"collapsible":false,"title":"Bug Info","rows":[{"cols":[{"width":287,"items":[{"name":"formItem_status","labelType":"left","readOnly":"0","height":"24","width":"191","tabindex":"0"},{"name":"formItem_phpVersion","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_type","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_severity","labelType":"left","readOnly":"0","height":"24","width":"192","tabindex":"0"},{"name":"formItem_file","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"}]},{"width":284,"items":[{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","height":"24","width":"190","tabindex":"0"},{"name":"formItem_x2Version","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_errorCode","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_line","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"}]}]}]},{"collapsible":false,"title":"Description","rows":[{"cols":[{"width":572,"items":[{"name":"formItem_subject","labelType":"left","readOnly":"0","height":"22","width":"428","tabindex":"0"},{"name":"formItem_description","labelType":"left","readOnly":"0","height":"109","width":"426","tabindex":"0"}]}]}]},{"collapsible":false,"title":"","rows":[{"cols":[{"width":572,"items":[{"name":"formItem_visibility","labelType":"top","readOnly":"0","height":"24","width":"179","tabindex":"0"},{"name":"formItem_duplicate","labelType":"top","readOnly":"0","height":"22","width":"188","tabindex":"0"},{"name":"formItem_blocks","labelType":"top","readOnly":"0","height":"22","width":"178","tabindex":"0"}]}]}]}]}', '0', '1', '1361818099', '1361818099');
/*&*/
INSERT INTO x2_form_layouts (id, model, version, layout, defaultView, defaultForm, createDate, lastUpdated) VALUES ('18', 'BugReports', 'View', '{"version":"1.2","sections":[{"collapsible":false,"title":"Bug Info","rows":[{"cols":[{"width":285,"items":[{"name":"formItem_createDate","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","height":"24","width":"195","tabindex":"0"},{"name":"formItem_status","labelType":"left","readOnly":"0","height":"24","width":"196","tabindex":"0"},{"name":"formItem_phpVersion","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_severity","labelType":"left","readOnly":"0","height":"24","width":"197","tabindex":"0"},{"name":"formItem_file","labelType":"left","readOnly":"0","height":"22","width":"198","tabindex":"0"}]},{"width":286,"items":[{"name":"formItem_lastUpdated","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_updatedBy","labelType":"left","readOnly":"0","height":"24","width":"190","tabindex":"0"},{"name":"formItem_x2Version","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_type","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_errorCode","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"},{"name":"formItem_line","labelType":"left","readOnly":"0","height":"22","width":"193","tabindex":"0"}]}]}]},{"collapsible":false,"title":"Description","rows":[{"cols":[{"width":572,"items":[{"name":"formItem_subject","labelType":"left","readOnly":"0","height":"22","width":"483","tabindex":"0"},{"name":"formItem_description","labelType":"left","readOnly":"0","height":"79","width":"481","tabindex":"0"}]}]}]},{"collapsible":false,"title":"","rows":[{"cols":[{"width":572,"items":[{"name":"formItem_visibility","labelType":"top","readOnly":"0","height":"24","width":"199","tabindex":"0"},{"name":"formItem_duplicate","labelType":"top","readOnly":"0","height":"22","width":"208","tabindex":"0"},{"name":"formItem_blocks","labelType":"top","readOnly":"0","height":"22","width":"208","tabindex":"0"}]}]}]}]}', '1', '0', '1361818105', '1361818105');
