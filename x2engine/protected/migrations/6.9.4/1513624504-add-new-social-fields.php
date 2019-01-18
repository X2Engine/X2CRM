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
    $addTwitterColumnSqlCheck = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME= 'x2_admin' AND COLUMN_NAME = 'loginCredsTimeout'";

    $addTwitterColumnSql = 'ALTER TABLE x2_admin ADD loginCredsTimeout INT DEFAULT 30';
                                            
    // Execute command
    if (Yii::app()->db->createCommand($addTwitterColumnSqlCheck)->execute() === 0) {
        Yii::app()->db->createCommand($addTwitterColumnSql)->execute();
    }
};

$addNewSocialColumns();