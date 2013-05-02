DROP TABLE IF EXISTS x2_media;
/*&*/
CREATE TABLE x2_media(
	id				INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	associationType	VARCHAR(40)		NOT NULL,
	associationId	INT,
	uploadedBy		VARCHAR(40),
	fileName		VARCHAR(100),
	createDate		BIGINT,
	lastUpdated		BIGINT,
	private			TINYINT         DEFAULT 0,
	description		TEXT,
	mimetype		VARCHAR(250),
	filesize		INT,
	dimensions		VARCHAR(40)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,	title,	visible,	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("media",	"Media",1,			11,				0,			0,			0,			0,		0);
/*&*/
INSERT INTO x2_fields
(modelName,	fieldName,			attributeLabel,			modified,	custom,		type,			required,	readOnly,	linkType,		searchable,	isVirtual,	relevance)
VALUES
("Media",	"id",				"ID",					0,			0,			"varchar",		0,			0,			NULL,			0,			0,			""),
("Media",	"associationType",	"Association Type",		0,			0,			"varchar",		1,			0,			NULL,			0,			0,			""),
("Media",	"associationId",	"Association ID",		0,			0,			"int",			0,			0,			NULL,			0,			0,			""),
("Media",	"uploadedBy",		"Uploaded By",			0,			0,			"assignment",	0,			1,			NULL,			0,			0,			""),
("Media",	"fileName",			"File Name",			0,			0,			"varchar",		0,			0,			NULL,			1,			0,			"High"),
("Media",	"createDate",		"Create Date",			0,			0,			"dateTime",		0,			1,			NULL,			0,			0,			""),
("Media",	"lastUpdated",		"Last Updated",			0,			0,			"dateTime",		0,			1,			NULL,			0,			0,			""),
("Media",	"private",			"Private",				0,			0,			"int",			0,			0,			NULL,			0,			0,			""),
("Media",	"description",		"Description",			0,			0,			"text",			0,			0,			NULL,			1,			0,			"Medium"),
("Media",	"mimetype",			"MIME Info",			0,			0,			"varchar",		0,			1,			NULL,			0,			0,			""),
("Media",	"filesize",			"File Size",			0,			0,			"int",			0,			1,			NULL,			0,			0,			""),
("Media",	"dimensions",		"Dimensions",			0,			0,			"varchar",		0,			1,			NULL,			0,			0,			"");
/*&*/
INSERT INTO x2_media
(associationType, fileName)
VALUES
('bg','santacruznight_blur.jpg'),
('bg','MBayInn.jpg'),
('bg','Lassen.jpg'),
('bg','Divers.jpg'),
('bg','Ravendale.jpg'),
('bg','DeathValley.jpg'),
('bg','Redwoods2.jpg'),
('bg','pigeon_point.jpg'),
('bg','CanneryRow.jpg'),
('bg','BeerCanRace.jpg');
/*&*/
INSERT INTO x2_media
(id, associationType, fileName)
VALUES
(1000, 'notificationSound','X2_Notification.mp3'),
(1001, 'loginSound','X2_Drums.mp3'),
(1002, 'loginSound','X2_EDM.mp3'),
(1003, 'loginSound','X2_Jazz.mp3'),
(1004, 'loginSound','X2_orchestra.mp3'),
(1005, 'loginSound','X2_piano.mp3'),
(1006, 'loginSound','X2_rock_and_roll.mp3');
