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
	private			TINYINT,
	description		TEXT,
	mimetype		VARCHAR(250),
	filesize		INT,
	dimensions		VARCHAR(40)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules` 
			(`name`,	title,	visible,	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable) 
	VALUES	("media",	"Media",1,			10,				0,			0,			0,			0,		0);
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
('bg','BeerCanRace.jpg'),
('bg','CanneryRow.jpg'),
('bg','DeathValley.jpg'),
('bg','Divers.jpg'),
('bg','Lassen.jpg'),
('bg','MBayInn.jpg'),
('bg','Ravendale.jpg'),
('bg','Redwoods.jpg'),
('bg','SantaCruzSummer.jpg'),
('bg','ThreeMile.jpg');
