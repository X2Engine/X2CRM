DROP TABLE IF EXISTS x2_calendars,x2_calendar_permissions;
/*&*/
CREATE TABLE x2_calendars (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100) NOT NULL, 
    googleCalendar     TINYINT,
    googleFeed         VARCHAR(255),
    createDate         BIGINT,
    createdBy          VARCHAR(40),
    lastUpdated        BIGINT,
    updatedBy          VARCHAR(40),
    googleCalendarId   VARCHAR(255),
    googleAccessToken  VARCHAR(512),
    googleRefreshToken VARCHAR(255)
) ENGINE InnoDB, COLLATE utf8_general_ci;
/*&*/
CREATE TABLE x2_calendar_permissions (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    calendarId    INT UNSIGNED,
    userId        INT UNSIGNED,
    view          TINYINT,
    edit          TINYINT
) ENGINE InnoDB, COLLATE utf8_general_ci;
/*&*/
ALTER TABLE `x2_calendar_permissions` ADD CONSTRAINT FOREIGN KEY (`calendarId`) REFERENCES x2_calendars(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
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
