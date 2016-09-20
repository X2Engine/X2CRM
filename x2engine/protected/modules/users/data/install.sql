DROP TABLE IF EXISTS x2_users;
/*&*/
CREATE TABLE x2_users (
    id                     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    firstName              VARCHAR(100),
    lastName               VARCHAR(120),
    username               VARCHAR(50),
    userAlias              VARCHAR(50) DEFAULT NULL,
    password               VARCHAR(100),
    title                  VARCHAR(50),
    department             VARCHAR(40),
    officePhone            VARCHAR(40),
    cellPhone              VARCHAR(40),
    homePhone              VARCHAR(40),
    address                VARCHAR(100),
    backgroundInfo         TEXT,
    emailAddress           VARCHAR(100) DEFAULT NULL,
    status                 TINYINT NOT NULL,
    temporary              TINYINT DEFAULT 0,
    lastUpdated            VARCHAR(50),
    createDate             BIGINT,
    updatedBy              VARCHAR(50),
    recentItems            VARCHAR(100),
    topContacts            VARCHAR(100),
    lastLogin              INT DEFAULT 0,
    login                  INT DEFAULT 0,
    showCalendars          TEXT,
    inviteKey              VARCHAR(16),
    userKey                VARCHAR(64),
    calendarKey            VARCHAR(64),
    UNIQUE(username, emailAddress),
    INDEX (username)
) COLLATE = utf8_general_ci;
/*&*/
ALTER TABLE `x2_calendar_permissions` ADD CONSTRAINT FOREIGN KEY (`userId`) REFERENCES x2_users(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
/*&*/
DROP TABLE IF EXISTS x2_password_reset;
/*&*/
CREATE TABLE x2_password_reset (
    id          CHAR(64) NOT NULL PRIMARY KEY,
    ip          VARCHAR(39) DEFAULT NULL,
    requested   BIGINT,
    email       VARCHAR(250) DEFAULT NULL,
    userId      INT DEFAULT NULL
) COLLATE = utf8_general_ci, ENGINE = InnoDB;
/*&*/
INSERT INTO `x2_modules`
(`name`,            title,            visible,     menuPosition,    searchable,    editable,    adminOnly,    custom,    toggleable)
VALUES
("users",            "Users",            1,            18,                0,            0,            1,            0,        0);
