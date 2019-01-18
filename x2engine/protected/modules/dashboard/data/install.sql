DROP TABLE IF EXISTS x2_widgets,x2_dashboard_settings;
/*&*/
CREATE TABLE x2_widgets(
	id						INT				UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name					VARCHAR(255),
	showPROFILE				INT				DEFAULT 1,
	adminALLOWS				INT				DEFAULT 1,
	showDASH				INT	 			DEFAULT 1,
	userID					INT,
	posPROF					INT,
	posDASH					INT,
	widgetSettings			TEXT,
	dispNAME				VARCHAR(255),
	needUSER				INT				DEFAULT 0,
	userALLOWS				INT				DEFAULT 1,
	UNIQUE(name),
	INDEX(name)	
) COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_dashboard_settings(
	userID					INT,
	numCOLS					INT				DEFAULT 2,
	hideINTRO				INT				DEFAULT 0,
	unique(userID)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO x2_widgets (name, userID, posPROF, posDASH, dispNAME, needUSER) VALUES
("OnlineUsers", 1, 1, 1, "Active Users",0),
("MessageBox",1,2,2,"Message Box",0),
("QuickContact",1,3,3,"Quick Contact",0),
("GoogleMaps",1,4,4,"Google Map",1),
("Twitter Feed",1,5,5,"Twitter Feed",1),
("ChatBox",1,6,6,"Activity Feed",0),
("NoteBox",1,7,7,"Note Pad",0),
("ActionMenu",1,8,8,"My Actions",0),
("TagCloud",1,9,9,"Tag Cloud",0),
("DocViewer",1,10,10,"Doc Viewer",0),
("MediaBox",1,11,11,"Media Box",0),
("TimeZone",1,12,12,"Time Zone",1),
("TopSites",1,13,13,"Top Sites",0);
/*&*/
INSERT INTO x2_dashboard_settings(userID) VALUES (1);