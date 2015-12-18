ALTER TABLE `x2_contacts` ADD reverseIp VARCHAR(250) DEFAULT NULL;
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Contacts', 'reverseIp', 'Reverse IP',        0,    0,'varchar',        0,       1,     NULL,           1,         0,       '',                0,     1, NULL),
('Contacts', 'fingerprintId', 'Fingerprint ID',0,    0,'int',            0,       1,     NULL,           1,         0,       '',                1,     1, NULL);

