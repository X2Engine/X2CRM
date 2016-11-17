DROP TABLE IF EXISTS x2_role_to_workflow;
/*&*/
DROP TABLE IF EXISTS x2_role_exceptions;
/*&*/
DROP TABLE IF EXISTS x2_workflow_stages;
/*&*/
DROP TABLE IF EXISTS x2_workflows;
/*&*/
CREATE TABLE x2_workflows(
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(250),
    isDefault       TINYINT NOT NULL DEFAULT 0,
    lastUpdated     BIGINT,
    colors          TEXT, /* contains JSON of stage colors */
    financial       TINYINT NOT NULL DEFAULT 0,
    financialModel  VARCHAR (250),
    financialField  VARCHAR (250)
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
ALTER TABLE `x2_modules` ADD CONSTRAINT FOREIGN KEY (`defaultWorkflow`) REFERENCES x2_workflows(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
/*&*/
ALTER TABLE `x2_actions` ADD CONSTRAINT FOREIGN KEY (`workflowId`) REFERENCES x2_workflows(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
/*&*/
CREATE TABLE x2_workflow_stages(
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    workflowId      INT NOT NULL,
    stageNumber     INT,
    name            VARCHAR(40),
    description     TEXT,
    conversionRate  DECIMAL(18,2),
    value           DECIMAL(18,2),
    requirePrevious INT DEFAULT 0,
    requireComment  TINYINT DEFAULT 0,
    FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
ALTER TABLE `x2_actions` ADD CONSTRAINT FOREIGN KEY (`stageNumber`) REFERENCES x2_workflow_stages(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
/*&*/
CREATE TABLE x2_role_to_workflow(
    id         INT                NOT NULL AUTO_INCREMENT PRIMARY KEY,
    roleId     INT,
    stageId    INT,
    workflowId INT,
    FOREIGN KEY (roleId) REFERENCES x2_roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (stageId) REFERENCES x2_workflow_stages(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE InnoDB COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_role_exceptions (
	id					INT				NOT NULL AUTO_INCREMENT primary key,
	workflowId				INT,
	stageId					INT,
	roleId					INT, /* points to id of an x2_roles record */
	replacementId                           INT, /* points to id of a dummy x2_roles record */
        UNIQUE (workflowId, stageId, roleId),
        FOREIGN KEY (roleId) REFERENCES x2_roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
        FOREIGN KEY (replacementId) REFERENCES x2_roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
        FOREIGN KEY (stageId) REFERENCES x2_workflow_stages(id) ON UPDATE CASCADE ON DELETE CASCADE,
        FOREIGN KEY (workflowId) REFERENCES x2_workflows(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
("workflow", "Process", 1, 12, 0, 0, 0, 0, 0);
