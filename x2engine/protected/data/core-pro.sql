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






DROP TABLE IF EXISTS x2_forwarded_email_patterns;
/*&*/
CREATE TABLE x2_forwarded_email_patterns(
	id					INT			NOT NULL AUTO_INCREMENT PRIMARY KEY,
	custom				TINYINT		DEFAULT 1,
	groupName			VARCHAR(20)	NOT NULL,
	pattern				TEXT,
	bodyFrom			TEXT,
	description			TEXT,
	UNIQUE (groupName)
) COLLATE = utf8_general_ci;
/*&*/
/* drop this table first since one if its attributes is a foreign key to x2_cron_events */
DROP TABLE IF EXISTS x2_email_reports;
/*&*/
DROP TABLE IF EXISTS `x2_cron_events`;
/*&*/
CREATE TABLE `x2_cron_events` (
	`id`				int(11)		NOT NULL AUTO_INCREMENT,
	`type`				VARCHAR(20)	NOT NULL,
	`recurring`			TINYINT		DEFAULT 0,
	`priority`			INT			DEFAULT 1,
	`time`				BIGINT		DEFAULT NULL,
	`interval`			VARCHAR(20)	DEFAULT NULL,
	`schedule`          TEXT        DEFAULT NULL,	
	`data`				TEXT		NOT NULL,
	`createDate`		BIGINT		NOT NULL,
	`lastExecution`		BIGINT		DEFAULT NULL,
	`executionCount`	BIGINT		NOT NULL DEFAULT 0,
	`flowId`	        INT		    DEFAULT NULL,
    `associationId`     INT         NULL DEFAULT 0,
    `associationType`   VARCHAR(255),
	PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS `x2_gallery_photo`;
/*&*/
DROP TABLE IF EXISTS `x2_gallery_to_model`;
/*&*/
DROP TABLE IF EXISTS `x2_gallery`;
/*&*/
CREATE  TABLE IF NOT EXISTS `x2_gallery` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `versions_data` TEXT NOT NULL ,
  `name` TINYINT(1) NOT NULL DEFAULT 1 ,
  `description` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE  TABLE IF NOT EXISTS `x2_gallery_photo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `gallery_id` INT NOT NULL ,
  `rank` INT NOT NULL DEFAULT 0 ,
  `name` VARCHAR(512) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `file_name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) ,
  INDEX `fk_gallery_photo_gallery1` (`gallery_id` ASC) ,
  CONSTRAINT `fk_gallery_photo_gallery1`
    FOREIGN KEY (`gallery_id` )
    REFERENCES `x2_gallery` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE TABLE IF NOT EXISTS `x2_gallery_to_model` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `galleryId` INT NOT NULL ,
    `modelName` VARCHAR(255) NOT NULL ,
    `modelId` INT NOT NULL ,
     PRIMARY KEY (`id`) ,
  INDEX `fk_gallery_to_model` (`galleryId` ASC) ,
  CONSTRAINT `fk_gallery_to_model`
    FOREIGN KEY (`galleryId` )
    REFERENCES `x2_gallery` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE TABLE x2_email_reports (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    user VARCHAR(255),
    cronId INT(11) NOT NULL,
    schedule VARCHAR(255),
    CONSTRAINT `fk_report_to_cron`
        FOREIGN KEY (`cronId`)
        REFERENCES `x2_cron_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
ALTER TABLE x2_admin ADD imapPollTimeout INT DEFAULT 10;
/*&*/
ALTER TABLE x2_admin ADD COLUMN emailDropbox TEXT;
/*&*/
ALTER TABLE x2_profile ADD COLUMN emailInboxes VARCHAR(255) NOT NULL DEFAULT "";
/*&*/
ALTER TABLE x2_admin ADD COLUMN appliedPackages TEXT;
/*&*/
DROP TABLE IF EXISTS x2_merge_log;
/*&*/
CREATE TABLE x2_merge_log(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    modelType VARCHAR(255),
    modelId INT,
    mergeModelId INT,
    mergeData TEXT,
    mergeDate BIGINT
) COLLATE = utf8_general_ci, ENGINE=INNODB;

