
DROP TABLE IF EXISTS x2_fingerprint;
/*&*/
CREATE TABLE x2_fingerprint (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fingerprint         BIGINT UNSIGNED DEFAULT NULL, /* the murmurhash3 browser fingerprint */
    createDate          BIGINT NOT NULL,
    anonymous           TINYINT,
    /* Fingerprint Attributes */
    plugins             VARCHAR(255),
    userAgent           VARCHAR(255),
    `language`          VARCHAR(40),
    screenRes           VARCHAR(40),
    timezone            INT,
    cookiesEnabled      TINYINT,
    indexedDB           TINYINT,
    addBehavior         TINYINT,
    javaEnabled         TINYINT,
    canvasFingerprint   INT UNSIGNED DEFAULT NULL,
    localStorage        TINYINT,
    sessionStorage      TINYINT,
    fonts               INT UNSIGNED DEFAULT NULL
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
DROP TABLE IF EXISTS x2_anon_contact;
/*&*/
CREATE TABLE x2_anon_contact (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    createDate          BIGINT NOT NULL,
    lastUpdated         BIGINT,
    fingerprintId       INT NOT NULL,   /* References an entry in x2_fingerprint */
    trackingKey         VARCHAR(32),
    email               VARCHAR(250),
    reverseIp           VARCHAR(250),
    leadscore           INT
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
INSERT INTO x2_fields
(modelName,     fieldName,            attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Fingerprint', 'id',                'ID',                      0, 0,   'int',      0,      1,      NULL,       1,      0,          '',         1,              1,      'PRI'),
('Fingerprint', 'createDate',        'Create Date',             0, 0,   'dateTime', 1,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'anonymous',         'Anonymous',               0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'fingerprint',       'Fingerprint',             0, 0,   'int',      1,      1,      NULL,       1,      0,          '',         1,              1,      NULL),
('Fingerprint', 'plugins',           'Plugins',                 0, 0,   'varchar',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'userAgent',         'User Agent',              0, 0,   'varchar',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'language',          'Language',                0, 0,   'varchar',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'screenRes',         'Screen Resolution',       0, 0,   'varchar',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'timezone',          'Timezone Offset',         0, 0,   'int',      0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'cookiesEnabled',    'Cookies Enabled',         0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'indexedDB',         'IndexedDB Supoprt',       0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'addBehavior',       'IE addBehavior Support',  0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'javaEnabled',       'Java Enabled',            0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'canvasFingerprint', 'HTML5 Canvas Fingerprint',0, 0,   'int',      0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'localStorage',      'Local Storage Support',   0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'sessionStorage',    'Session Storage Support', 0, 0,   'boolean',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('Fingerprint', 'fonts',             'Fonts Checksum',          0, 0,   'int',      0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('AnonContact', 'id',                'ID',                      0, 0,   'int',      0,      1,      NULL,       1,      0,          '',         1,              1,      'PRI'),
('AnonContact', 'createDate',        'Create Date',             0, 0,   'dateTime', 1,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('AnonContact', 'lastUpdated',       'Last Updated',            0, 0,   'dateTime', 0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('AnonContact', 'fingerprintId',     'Fingerprint ID',          0, 0,   'int',      1,      1,      NULL,       1,      0,          '',         1,              1,      NULL),
('AnonContact', 'trackingKey',       'Tracking Key',            0, 0,   'varchar',  1,      1,      NULL,       1,      0,          '',         1,              1,      NULL),
('AnonContact', 'email',             'Email',                   0, 0,   'varchar',  0,      1,      NULL,       1,      0,          '',         1,              1,      NULL),
('AnonContact', 'reverseIp',         'Reverse IP',              0, 0,   'varchar',  0,      1,      NULL,       1,      0,          '',         0,              1,      NULL),
('AnonContact', 'leadscore',         'Lead Score',              0, 0,   'int',      0,      1,      NULL,       1,      0,          '',         0,              1,      NULL);
/*&*/
INSERT INTO `x2_form_layouts`
(`id`, `model`, `version`, `scenario`, `layout`, `defaultView`, `defaultForm`, `createDate`, `lastUpdated`)
VALUES
(21,'AnonContact','Form','Default','{\"version\":\"4.1\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":578,\"items\":[{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_trackingKey\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"202\",\"tabindex\":\"0\"}]}]},{\"cols\":[{\"width\":578,\"items\":[{\"name\":\"formItem_fingerprintId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}           ]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"undefined\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"undefined\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"undefined\"}]}]}]}]}',1,0,1373388579,1373388579);
