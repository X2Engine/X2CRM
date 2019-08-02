<?php
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




$migrationScript2plaphp = function () {
    Yii::app()->db->createCommand ("INSERT INTO `x2_fields` (`attributeLabel`,`custom`,`data`,`defaultValue`,`fieldName`,`isVirtual`,`keyType`,`linkType`,`modelName`,`modified`,`readOnly`,`relevance`,`required`,`safe`,`searchable`,`type`,`uniqueConstraint`) VALUES ('Subtype',0,NULL,NULL,'eventSubtype',0,NULL,'121','Actions',0,0,'',0,1,0,'dropdown',0),('Status',0,NULL,NULL,'eventStatus',0,NULL,'122','Actions',0,0,'',0,1,0,'dropdown',0)")->execute ();
    Yii::app()->db->createCommand ("UPDATE `x2_fields` SET `linkType`='123',`type`='dropdown' WHERE `modelName`='Actions' AND `fieldName`='color'")->execute ();
    Yii::app()->db->createCommand ("UPDATE `x2_fields` SET `required`=1 WHERE `modelName`='Actions' AND `fieldName`='actionDescription'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item` WHERE `name`='ChartsFullAccess'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item` WHERE `name`='ReportsFullAccess'")->execute ();
    Yii::app()->db->createCommand ("INSERT INTO `x2_auth_item` (`bizrule`,`data`,`description`,`name`,`type`) VALUES (NULL,'N;','','ChartsReadOnlyAccess',1),(NULL,'N;','','ReportsReadOnlyAccess',1),(NULL,'N;','','CalendarIcal',0)")->execute ();
    Yii::app()->db->createCommand ('INSERT INTO `x2_dropdowns` (`id`,`multi`,`name`,`options`,`parent`,`parentVal`) VALUES (122,0,\'Event Statuses\',\'{"Confirmed":"Confirmed","Cancelled":"Cancelled"}\',NULL,NULL),(123,0,\'Event Colors\',\'{"#008000":"Green","#3366CC":"Blue","#FF0000":"Red","#FFA500":"Orange","#000000":"Black"}\',NULL,NULL),(121,0,\'Event Subtypes\',\'{"Meeting":"Meeting","Appointment":"Appointment","Call":"Call"}\',NULL,NULL)')->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsPipeline'")->execute ();
     
     
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsWorkflow'")->execute ();
     
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='DefaultRole' AND `child`='ChartsFullAccess'")->execute ();
     
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsSales'")->execute ();
     
     
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsDeleteNote'")->execute ();
     
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='DefaultRole' AND `child`='DocsUpdatePrivate'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsLeadVolume'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsMinimumRequirements'")->execute ();
     
     
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsMarketing'")->execute ();
     
     
     
    Yii::app()->db->createCommand (
        "INSERT INTO `x2_auth_item_child` (`child`,`parent`) VALUES 
            ('ChartsDeleteNote','ChartsReadOnlyAccess'),
            ('ChartsLeadVolume','ChartsReadOnlyAccess'),
            ('ChartsMarketing','ChartsReadOnlyAccess'),
            ('ChartsMinimumRequirements','ChartsReadOnlyAccess'),
            ('ChartsPipeline','ChartsReadOnlyAccess'),
            
            ('ChartsReadOnlyAccess','administrator'),
            ('ChartsReadOnlyAccess','DefaultRole'),
            ('ChartsSales','ChartsReadOnlyAccess'),
             
            ('ChartsWorkflow','ChartsReadOnlyAccess')
        ")->execute ();
};
$migrationScript2plaphp ();