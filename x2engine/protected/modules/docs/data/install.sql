DROP TABLE IF EXISTS `x2_docs`;
CREATE TABLE `x2_docs` (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title					VARCHAR(100)	NOT NULL,
	type					VARCHAR(10)		NOT NULL DEFAULT "",
	text					LONGTEXT		NOT NULL,
	createdBy				VARCHAR(60)		NOT NULL,
	createDate				BIGINT,
	editPermissions			VARCHAR(250), 
	updatedBy				VARCHAR(40),
	lastUpdated				BIGINT
) COLLATE = utf8_general_ci;

INSERT INTO `x2_modules` 
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("docs",			"Docs",				1,			5,				0,			0,			0,			0,		0);