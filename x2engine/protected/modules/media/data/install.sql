DROP TABLE IF EXISTS x2_media;
CREATE TABLE x2_media(
	id						INT				NOT NULL AUTO_INCREMENT PRIMARY KEY,
	associationType			VARCHAR(40)		NOT NULL,
	associationId			INT,
	uploadedBy				VARCHAR(40),
	fileName				VARCHAR(100),
	createDate				BIGINT,
	lastUpdated				BIGINT,
	private					TINYINT,
	description				TEXT
) COLLATE = utf8_general_ci;

INSERT INTO `x2_modules` 
			(`name`,			title,			visible, 	menuPosition,	searchable,	editable,	adminOnly,	custom,	toggleable) 
	VALUES	("media",			"Media",			1,			10,				0,			0,			0,			0,		0);

INSERT INTO x2_media 
(associationType, fileName) 
VALUES
('bg','santacruznight_blur.jpg'),
('bg','santa_cruz.jpg'),
('bg','santa_cruz_blur.jpg'),
('bg','devilsgolfb.jpg'),
('bg','eastroad6b.jpg'),
('bg','pigeon_point.jpg'),
('bg','pigeon_point_blur.jpg'),
('bg','redwoods.jpg'),
('bg','redwoods_blur.jpg'),
('bg','laguna_blur.jpg'),
('bg','laguna_seca.jpg');