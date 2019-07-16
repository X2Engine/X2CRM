/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




DROP TABLE IF EXISTS x2_charts;
/*&*/
DROP TABLE IF EXISTS x2_reports_2;
/*&*/
CREATE TABLE x2_reports_2 (
    id         INT SIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    createDate BIGINT,
    createdBy  VARCHAR(250),
    lastUpdated BIGINT,
    name       VARCHAR(128),
    settings   TEXT,
    dataWidgetLayout   TEXT,
    version    VARCHAR(16),
    type       VARCHAR(250)
) Engine=InnoDB, AUTO_INCREMENT=1000, COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_charts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    createDate BIGINT,
    createdBy  VARCHAR(250),
    reportId   INT SIGNED,
    lastUpdated BIGINT,
    name       VARCHAR(128),
    settings   TEXT,
    version    VARCHAR(16),
    type       VARCHAR(250)
) Engine=InnoDB, COLLATE = utf8_general_ci;
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Charts', 'id', 'ID', 0, 0, 'varchar', 0, 1, NULL, 0, 0, '', 1, 1, 'PRI'),
('Charts', 'reportId', 'Report ID', 0, 0, 'INT', 0, 0, 'Reports', 0, 0, '', 0, 1, 'FOR'),
('Charts', 'name', 'Name', 0, 0, 'varchar', 1, 0, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'createDate', 'Create Date', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'createdBy', 'Created By', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'lastUpdated', 'Last Updated', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'settings', 'Settings', 0, 0, 'text', 0, 0, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'version', 'Version', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'type', 'Chart Type', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL);
/*&*/
INSERT INTO x2_modules
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable, moduleType)
VALUES
("reports", "Reports", 1, 15, 0, 0, 0, 0, 0, 'module'),
("charts", "Charts", 1, 16, 0, 0, 0, 0, 0, 'pseudoModule');
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Reports', 'id', 'ID', 0, 0, 'varchar', 0, 1, NULL, 0, 0, '', 1, 1, 'PRI'),
('Reports', 'name', 'Name', 0, 0, 'varchar', 1, 0, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'createDate', 'Create Date', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'createdBy', 'Created By', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'lastUpdated', 'Last Updated', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'dataWidgetLayout', 'Chart Layout', 0, 0, 'text', 0, 0, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'version', 'Version', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'type', 'Report Type', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL);
