DROP TABLE IF EXISTS x2_users;
/*&*/
CREATE TABLE x2_users (
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	firstName				VARCHAR(100),
	lastName				VARCHAR(120),
	username				VARCHAR(50),
	password				VARCHAR(100),
	title					VARCHAR(50),
	department				VARCHAR(40),
	officePhone				VARCHAR(40),
	cellPhone				VARCHAR(40),
	homePhone				VARCHAR(40),
	address					VARCHAR(100),
	backgroundInfo			TEXT,
	emailAddress			VARCHAR(100)	NOT NULL,
	status					TINYINT			NOT NULL,
    temporary               TINYINT         DEFAULT 0,
	lastUpdated				VARCHAR(50),
	updatedBy				VARCHAR(50),
	recentItems				VARCHAR(100),
	topContacts				VARCHAR(100),
	lastLogin				INT				DEFAULT 0,
	login					INT				DEFAULT 0,
	showCalendars			TEXT,
	calendarViewPermission	TEXT,
	calendarEditPermission	TEXT,
	calendarFilter			TEXT,
	setCalendarPermissions	TINYINT,
    inviteKey               VARCHAR(16),
    userKey                 VARCHAR(32),
	UNIQUE(username, emailAddress),
	INDEX (username)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("users",			"Users",			1,			14,				0,			0,			1,			0,		0);