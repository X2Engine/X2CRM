<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Adds new columns for 6.9.1, as well as other columns that were not added in
 * previous versions that break new installations
 */

$addNewSocialColumns = function() {
    // New columns
    $addDropboxColumnSqlCheck = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME= 'x2_admin' AND COLUMN_NAME = 'dropboxCredentialsId'";
    $addLinkedInColumnSqlCheck = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME= 'x2_admin' AND COLUMN_NAME = 'linkedInCredentialsId'";
    $addDropboxRateColumnSqlCheck = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME= 'x2_admin' AND COLUMN_NAME = 'dropboxRateLimits'";
    $addLinkedInRateColumnSqlCheck = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME= 'x2_admin' AND COLUMN_NAME = 'linkedInRateLimits'";
    
    $addDropboxColumnSql = 'ALTER TABLE x2_admin ADD dropboxCredentialsId INT UNSIGNED';
    $addLinkedInColumnSql = 'ALTER TABLE x2_admin ADD linkedInCredentialsId INT UNSIGNED';
    $addDropboxRateColumnSql = 'ALTER TABLE x2_admin ADD dropboxRateLimits TEXT DEFAULT NULL';
    $addLinkedInRateColumnSql = 'ALTER TABLE x2_admin ADD linkedInRateLimits TEXT DEFAULT NULL';
    $addDropboxCredentialsColumnSql = 'ALTER TABLE x2_admin ADD CONSTRAINT fk_dropboxCredentialsId FOREIGN KEY (dropboxCredentialsId) REFERENCES x2_credentials(id) ON UPDATE CASCADE ON DELETE SET NULL';
    $addLinkedInCredentialsColumnSql = 'ALTER TABLE x2_admin ADD CONSTRAINT fk_linkedInCredentialsId FOREIGN KEY (linkedInCredentialsId) REFERENCES x2_credentials(id) ON UPDATE CASCADE ON DELETE SET NULL';

    // Execute command
    if (Yii::app()->db->createCommand($addDropboxColumnSqlCheck)->execute() === 0) {
        Yii::app()->db->createCommand($addDropboxColumnSql)->execute();
        Yii::app()->db->createCommand($addDropboxCredentialsColumnSql)->execute();
    }
    if (Yii::app()->db->createCommand($addLinkedInColumnSqlCheck)->execute() === 0) {
        Yii::app()->db->createCommand($addLinkedInColumnSql)->execute();
        Yii::app()->db->createCommand($addLinkedInCredentialsColumnSql)->execute();
    }
    if (Yii::app()->db->createCommand($addDropboxRateColumnSqlCheck)->execute() === 0) {
        Yii::app()->db->createCommand($addDropboxRateColumnSql)->execute();
    }
    if (Yii::app()->db->createCommand($addLinkedInRateColumnSqlCheck)->execute() === 0) {
        Yii::app()->db->createCommand($addLinkedInRateColumnSql)->execute();
    }
};

$addNewSocialColumns();