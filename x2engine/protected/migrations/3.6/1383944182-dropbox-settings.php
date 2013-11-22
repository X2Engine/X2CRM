<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * @file 1383944182-dropbox-settings.php
 *
 * Migrate email dropbox settings into the new consolidated JSON settings storage
 * field.
 */

$migrateDropboxSettings = function(){
            $attr = array('alias', 'createContact', 'zapLineBreaks', 'emptyContact', 'logging');
            $new_settings = array();
            $attr_existing = array();
            $attr_map = array();
            foreach($attr as $name){
                $oldName = "emailDropbox_$name";
                $attr_existing[$name] = $oldName;
                $attr_map[$oldName] = $name;
            }
            $settings = Yii::app()->db->createCommand()->select(implode(',', $attr_existing))->from('x2_admin')->queryRow(true);
            foreach($settings as $oldName => $value){
                $new_settings[$attr_map[$oldName]] = $value;
            }
            Yii::app()->db->createCommand()->update('x2_admin', array('emailDropbox'=>json_encode($new_settings)));
        };

$migrateDropboxSettings();
?>
