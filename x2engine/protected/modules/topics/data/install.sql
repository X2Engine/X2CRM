DROP TABLE IF EXISTS x2_topic_replies;
/*&*/
DROP TABLE IF EXISTS x2_topics;
/*&*/
CREATE TABLE x2_topics(
    id           INT NOT NULL AUTO_INCREMENT primary key,
    assignedTo   VARCHAR(250),
    `name`       VARCHAR(250) NOT NULL,
    nameId       VARCHAR(250) DEFAULT NULL,
    createDate   BIGINT,
    lastUpdated  BIGINT,
    updatedBy    VARCHAR(250),
    sticky       TINYINT,
    UNIQUE(nameId)
) Engine = InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_topic_replies (
    id          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    topicId     INT NOT NULL,
    text        TEXT NOT NULL,
    assignedTo  VARCHAR(250),
    createDate  BIGINT,
    lastUpdated BIGINT,
    updatedBy   VARCHAR(250)
) Engine = InnoDB COLLATE = utf8_general_ci;
/*&*/
ALTER TABLE `x2_topic_replies` ADD CONSTRAINT FOREIGN KEY (`topicId`) REFERENCES x2_topics(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('topics', 'Topics', 1, 9, 1, 0, 0, 0, 0);
/*&*/
INSERT INTO `x2_mobile_layouts`
(`modelName`, `layout`, `defaultView`, `defaultForm`, `version`)
VALUES
('Topics', '["name","text"]', 0, 1, '5.4');
/*&*/
INSERT INTO `x2_mobile_layouts`
(`modelName`, `layout`, `defaultView`, `defaultForm`, `version`)
VALUES
('TopicReplies', '["text"]', 0, 1, '5.4');
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Topics', 'id',           'ID',            0, 'int',        0, 1, NULL, 0, 0, '',       1, 1, 'PRI'),
('Topics', 'name',         'Name',          0, 'varchar',    1, 0, NULL, 0, 0, 'High',   0, 1, NULL),
('Topics', 'text',         'Text',          0, 'text',       1, 0, NULL, 0, 0, 'High',   0, 1, NULL),
('Topics', 'nameId',       'NameID',        0, 'varchar',    0, 1, NULL, 0, 0, 'High',   0, 1, 'FIX'),
('Topics', 'assignedTo',   'Assigned To',   0, 'assignment', 0, 0, NULL, 0, 0, '',       0, 1, NULL),
('Topics', 'createDate',   'Create Date',   0, 'dateTime',   0, 1, NULL, 0, 0, '',       0, 1, NULL),
('Topics', 'lastUpdated',  'Last Updated',  0, 'dateTime',   0, 1, NULL, 0, 0, '',       0, 1, NULL),
('Topics', 'updatedBy',    'Updated By',    0, 'assignment', 0, 1, NULL, 0, 0, '',       0, 1, NULL),
('Topics', 'sticky',       'Sticky',        0, 'int',        0, 0, NULL, 0, 0, '',       0, 1, NULL);
