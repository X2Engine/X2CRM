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




DROP TABLE IF EXISTS `x2_email_inboxes`;
/*&*/
CREATE TABLE x2_email_inboxes (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(255) NOT NULL DEFAULT '',
    /* pseudo-foreign key which points to a credentials record */
    credentialId       INT UNSIGNED NULL,
    /* whether or not the inbox is shared or personal */
    shared             TINYINT DEFAULT 0,
    /* users who can view the inbox */
    assignedTo         VARCHAR(255),
    lastUpdated        BIGINT,
    settings           VARCHAR(1024)
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('emailInboxes', 'Email', 1, 10, 0, 0, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
("EmailInboxes", "assignedTo", "Visible To", 0, 0, "assignment", 1, 0, 'multiple', 0, 0, "", 0, 1, NULL),
("EmailInboxes", "shared", "Shared", 0, 0, "boolean", 0, 1, null, 0, 1, "", 0, 1, NULL),
('EmailInboxes', 'name', 'Name', 0, 0, 'varchar', 1, 0, NULL, 1, 0, 'High', 0, 1, NULL), 
('EmailInboxes', 'lastUpdated', 'LastUpdated', 0, 0, 'dateTime', 0, 1, NULL, 0, 0, '', 0, 1, NULL), 
('EmailInboxes', 'credentialId', 'Email Credentials', 0, 0, 'int', 1, 0, 'Credentials', 0, 0, '', 0, 1, 'FOR');
