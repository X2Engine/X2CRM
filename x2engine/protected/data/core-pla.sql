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





ALTER TABLE x2_admin ADD COLUMN api2 TEXT;
/*&*/
ALTER TABLE x2_admin ADD accessControlMethod VARCHAR(15) DEFAULT 'blacklist';
/*&*/
ALTER TABLE x2_admin ADD ipWhitelist TEXT NULL;
/*&*/
ALTER TABLE x2_admin ADD ipBlacklist TEXT NULL;
/*&*/
ALTER TABLE x2_admin ADD loginTimeout INT DEFAULT 900;
/*&*/
ALTER TABLE x2_admin ADD failedLoginsBeforeCaptcha INT DEFAULT 5;
/*&*/
ALTER TABLE x2_admin ADD maxFailedLogins INT DEFAULT 100;
/*&*/
ALTER TABLE x2_admin ADD maxLoginHistory INT DEFAULT 5000;
/*&*/
ALTER TABLE x2_admin ADD maxFailedLoginHistory INT DEFAULT 5000;
/*&*/
ALTER TABLE x2_admin ADD scanUploads TINYINT DEFAULT 0;
/*&*/
DROP TABLE IF EXISTS `x2_login_history`;
/*&*/
CREATE TABLE x2_login_history (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username				VARCHAR(50)		NOT NULL,
	IP VARCHAR(40),
	timestamp BIGINT DEFAULT NULL
) COLLATE = utf8_general_ci, ENGINE=INNODB;
