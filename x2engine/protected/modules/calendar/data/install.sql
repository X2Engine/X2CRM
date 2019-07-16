DROP TABLE IF EXISTS x2_calendar_invites;
/*&*/
DROP TABLE IF EXISTS x2_calendar_permissions;
/*&*/
DROP TABLE IF EXISTS x2_calendars;
/*&*/
CREATE TABLE x2_calendars (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100) NOT NULL, 
    remoteSync         TINYINT,
    createDate         BIGINT,
    createdBy          VARCHAR(40),
    lastUpdated        BIGINT,
    updatedBy          VARCHAR(40),
    syncType           VARCHAR(255),
    remoteCalendarId   VARCHAR(255),
    remoteCalOutlook   VARCHAR(255),
    remoteCalendarUrl  VARCHAR(512),
    syncToken          VARCHAR(255),
    ctag               VARCHAR(255),
    credentials        TEXT
) ENGINE InnoDB, COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_calendar_permissions (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    calendarId    INT UNSIGNED,
    userId        INT UNSIGNED,
    view          TINYINT,
    edit          TINYINT,
    FOREIGN KEY (`calendarId`) REFERENCES x2_calendars(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB, COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_calendar_invites (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actionId        INT UNSIGNED NOT NULL,
    email           VARCHAR(255),
    status          ENUM ('Yes', 'No', 'Maybe'),
    inviteKey       VARCHAR(255),
    FOREIGN KEY (`actionId`) REFERENCES x2_actions(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB, COLLATE utf8_general_ci;
/*&*/
ALTER TABLE `x2_actions` ADD CONSTRAINT calendarId FOREIGN KEY (`calendarId`) REFERENCES x2_calendars(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
("calendar", "Calendar", 1, 8, 0, 0, 0, 0, 0);
/*&*/
INSERT INTO `x2_fields`
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance)
VALUES
("Calendar", "name",           "Name",            0, 0, "varchar",    0, 0, NULL,       1, 0, "High"),
("Calendar", "viewPermission", "View Permission", 0, 0, "assignment", 0, 0, "multiple", 0, 0, ""),
("Calendar", "editPermission", "Edit Permission", 0, 0, "assignment", 0, 0, "multiple", 0, 0, "");
