DROP TABLE IF EXISTS x2_action_text;
/*&*/
DROP TABLE IF EXISTS x2_action_to_record;
/*&*/
DROP TABLE IF EXISTS x2_action_meta_data;
/*&*/
DROP TABLE IF EXISTS x2_calendar_invites;
/*&*/
DROP TABLE IF EXISTS x2_actions;
/*&*/
CREATE TABLE x2_actions (
    id                         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    assignedTo                 VARCHAR(255),
    calendarId                 INT UNSIGNED,
    subject                    VARCHAR(255),
    visibility                 INT NOT NULL DEFAULT 1,
    associationId              INT NOT NULL,
    associationType            VARCHAR(255),
    associationName            VARCHAR(100),
    dueDate                    BIGINT,
    showTime                   TINYINT NOT NULL DEFAULT 0,
    priority                   VARCHAR(10),
    type                       VARCHAR(20),
    createDate                 BIGINT,
    complete                   VARCHAR(5) DEFAULT "No",
    reminder                   VARCHAR(5),
    completedBy                VARCHAR(50),
    completeDate               BIGINT,
    lastUpdated                BIGINT,
    updatedBy                  VARCHAR(50),
    workflowId                 INT,
    quoteId                    INT UNSIGNED,
    stageNumber                INT,
    allDay                     TINYINT,
    color                      VARCHAR(20),
    syncGoogleCalendarEventId  TEXT,
    syncOutlookCalendarEventId TEXT,
    sticky                     TINYINT DEFAULT 0,
    flowTriggered              TINYINT DEFAULT 0,
    timeSpent                  INT DEFAULT 0,
    locationId                 INT UNSIGNED,
    INDEX (assignedTo),
    INDEX (type),
    INDEX (associationType,associationId),
    INDEX (locationId),
    UNIQUE (associationType, associationId, workflowId, stageNumber)
) COLLATE = utf8_general_ci, ENGINE = INNODB;
/*&*/
ALTER TABLE `x2_actions_to_media` ADD CONSTRAINT FOREIGN KEY (`actionsId`) REFERENCES x2_actions(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
/*&*/
CREATE TABLE x2_action_meta_data (
    id                        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actionId                  INT UNSIGNED NOT NULL,
    eventSubtype              VARCHAR(100),
    eventStatus               VARCHAR(100),
    etag                      VARCHAR(255),
    remoteCalendarUrl         VARCHAR(255),
    remoteSource              INT DEFAULT 0,
    INDEX (actionId),
    FOREIGN KEY (actionId) REFERENCES x2_actions(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci, ENGINE = INNODB;
/*&*/
/* Used for many-to-many actions associations */
CREATE TABLE x2_action_to_record (
    id                        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actionId                  INT UNSIGNED NOT NULL,
    recordId                  INT UNSIGNED NOT NULL,
    recordType                VARCHAR(32) NOT NULL,
    UNIQUE (actionId, recordId, recordType),
    FOREIGN KEY (actionId) REFERENCES x2_actions(id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci, ENGINE = INNODB;
/*&*/
CREATE TABLE x2_action_text (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    text     LONGTEXT,
    actionId INT UNSIGNED,
    INDEX(actionId),
    CONSTRAINT action_id_key FOREIGN KEY (actionId) REFERENCES x2_actions (id) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = utf8_general_ci, ENGINE = INNODB;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
("actions", "Actions", 1, 7, 1, 0, 0, 0, 0);
/*&*/
INSERT INTO `x2_fields`
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
("Actions", "id",                "ID",                 0, 0, "varchar",    0, 1, NULL, 0, 0, "",       1, 1, 'PRI'),
("Actions", "assignedTo",        "Assigned To",        0, 0, "assignment", 0, 0, 'multiple', 0, 0, "", 0, 1, NULL),
("Actions", "subject",           "Subject",            0, 0, "varchar",    1, 0, NULL, 1, 0, "High",   0, 1, NULL),
("Actions", "actionDescription", "Description",        0, 0, "text",       1, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "eventSubtype",      "Subtype",            0, 0, "dropdown",   0, 0, 121,  0, 0, "",       0, 1, NULL),
("Actions", "eventStatus",       "Status",             0, 0, "dropdown",   0, 0, 122,  0, 0, "",       0, 1, NULL),
("Actions", "visibility",        "Visibility",         0, 0, "visibility", 0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "associationId",     "Association ID",     0, 0, "varchar",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "associationType",   "Association Type",   0, 0, "varchar",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "associationName",   "Association",        0, 0, "varchar",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "dueDate",           "Due Date",           0, 0, "dateTime",   0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "priority",          "Priority",           0, 0, "varchar",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "type",              "Action Type",        0, 0, "varchar",    0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "createDate",        "Create Date",        0, 0, "dateTime",   0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "complete",          "Complete",           0, 0, "varchar",    0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "reminder",          "Reminder",           0, 0, "boolean",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "completedBy",       "Completed By",       0, 0, "assignment", 0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "completeDate",      "Date Completed",     0, 0, "dateTime",   0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "lastUpdated",       "Last Updated",       0, 0, "dateTime",   0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "updatedBy",         "Updated By",         0, 0, "varchar",    0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "allDay",            "All Day",            0, 0, "boolean",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "color",             "Color",              0, 0, "dropdown",   0, 0, 123,  0, 0, "",       0, 1, NULL),
("Actions", "timeSpent",         "Time Spent",         0, 0, "int",        0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "workflowId",        "Process",            0, 0, "int",        0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "stageNumber",       "Process Stage",      0, 0, "int",        0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "flowTriggered",     "Flow Triggered",     0, 0, "boolean",    0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "quoteId",           "Quote ID",           0, 0, "int",        0, 1, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "notificationUsers", "Notification Users", 0, 0, "varchar",    0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "notificationTime",  "Notification Time",  0, 0, "int",        0, 0, NULL, 0, 0, "",       0, 1, NULL),
("Actions", "calendarId",        "Calendar ID",        0, 0, "int",        0, 0, NULL, 0, 0, "",       0, 1, NULL);
