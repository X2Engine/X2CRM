DROP TABLE IF EXISTS `x2_docs`;
/*&*/
CREATE TABLE `x2_docs` (
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(100)	NOT NULL,
    subject                 VARCHAR(255),
	type					VARCHAR(10)		NOT NULL DEFAULT "",
	text					LONGTEXT		NOT NULL,
	createdBy				VARCHAR(60)		NOT NULL,
	createDate				BIGINT,
	editPermissions			VARCHAR(250),
	updatedBy				VARCHAR(40),
	lastUpdated				BIGINT,
    visibility              TINYINT
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable)
	VALUES	("docs",			"Docs",				1,			8,				0,			0,			0,			0,		0);
/*&*/
INSERT INTO `x2_fields` (`modelName`, `fieldName`, `attributeLabel`, `modified`, `custom`, `type`, `required`, `readOnly`, `linkType`, `searchable`, `relevance`, `isVirtual`)
VALUES
('Docs','createDate','Created',0,0,'date',0,0,NULL,0,NULL,0),
('Docs','createdBy','Created By',0,0,'varchar',0,0,NULL,0,NULL,0),
('Docs','editPermissions','Edit Permissions',0,0,'varchar',0,0,NULL,0,NULL,0),
('Docs','id','ID',0,0,'int',0,1,NULL,0,NULL,0),
('Docs','lastUpdated','Last Updated',0,0,'date',0,0,NULL,0,NULL,0),
('Docs','name','Name',0,0,'varchar',1,0,NULL,0,NULL,0),
('Docs','subject','Subject',0,0,'varchar',0,0,NULL,0,NULL,0),
('Docs','text','Body',0,0,'text',1,0,NULL,0,NULL,0),
('Docs','type','Type',0,0,'varchar',0,0,NULL,0,NULL,0),
('Docs','updatedBy','Updated By',0,0,'link',0,0,'User',0,NULL,0),
('Docs','visibility','Visibility',0,0,'boolean',0,0,NULL,0,NULL,0);