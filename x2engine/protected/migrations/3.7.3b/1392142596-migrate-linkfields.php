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




Yii::import('application.models.*');
Yii::import('application.controllers.X2Controller');
Yii::import('application.controllers.x2base');
Yii::import('application.components.*');
Yii::import('application.components.util.*');
Yii::import('application.components.permissions.*');
Yii::import('application.modules.media.models.Media');
Yii::import('application.modules.groups.models.Groups');
Yii::import('application.extensions.gallerymanager.models.*');

$arr = array();
$modulePath = implode(DIRECTORY_SEPARATOR,array(
    Yii::app()->basePath,
    'modules'
));
foreach(scandir($modulePath) as $module){
    $regScript = implode(DIRECTORY_SEPARATOR,array(
        $modulePath,
        $module,
        'register.php'
    ));
    if(file_exists($regScript)){
        $arr[$module] = ucfirst($module);
        Yii::import("application.modules.$module.models.*");
    }
}


/**
 * @file 1392142596-migrate-linkfields.php
 * 
 * Update link-type fields so that they follow the new convention.
 *
 * Note, this assumes that the necessary database columns (for non-custom
 * modules) have already been constructed. It will construct these columns
 * for custom modules, however.
 */
$migrateLinkFields = function(){
            if(!class_exists('X2Model')) {
                return 'Your installation of X2Engine is WAY too old to run this '
                        .'migration script; class X2Model does not even exist yet. '
                        .'Restore your backup, update manually, and run this script when done.';
            } elseif(!method_exists('X2Model','getModelName')) {
                return 'Your installation of X2Engine is WAY too old to run this '
                        .'migration script; class X2Model does not have required '
                        .'method "getModelName". '
                        .'Restore your backup, update manually, and run this script when done.';
            }
            $debug = 0;
            // Get all fields with the "name" attribute:
            $namedModels = Yii::app()->db->createCommand()
                    ->select('modelName')
                    ->from('x2_fields')
                    ->where("fieldName='name'")
                    ->queryColumn();
            // Get all models that already have the nameId field:
            $nameIdModels = Yii::app()->db->createCommand()
                    ->select('modelName')
                    ->from('x2_fields')
                    ->where("fieldName='nameId'")
                    ->queryColumn();
            
            // All models whose table needs a nameId column:
            $customModels = array_diff($namedModels, $nameIdModels);
            foreach($customModels as $modelName){
                $class = X2Model::getModelName($modelName);
                if(empty($class) || !class_exists($class))
                    continue;
                $model = X2Model::model($class);
                if($model->asa('LinkableBehavior') instanceof LinkableBehavior && $model->hasAttribute('name')){
                    $table = $model->tableName();
                    // Create the field; it's a custom module's table that
                    // hasn't been created yet:
                    if($debug){ echo "creating new nameId column in $table for custom model ".get_class($model)."\n"; }
                    Yii::app()->db->createCommand("
                                 ALTER TABLE $table
                                 ADD COLUMN `nameId` VARCHAR(250) DEFAULT NULL AFTER `name`")->execute();
                    if($debug){ echo "creating new nameId x2_fields record corresponding to custom model ".get_class($model)."\n"; }
                    Yii::app()->db->createCommand()
                            ->insert('x2_fields', array(
                                'modelName' => get_class($model),
                                'fieldName' => 'nameId',
                                'attributeLabel' => 'NameID',
                                'readOnly' => 1,
                                'type' => 'varchar',
                                'keyType' => 'FIX',
                            ));
                }
            }

            // Next, populate the nameId fields:
            foreach($namedModels as $modelName){
                $class = X2Model::getModelName($modelName);
                if(empty($class) || !class_exists($class))
                    continue;
                $model = X2Model::model($class);
                if($model->asa('LinkableBehavior') instanceof LinkableBehavior && $model->hasAttribute('name')){
                    $table = $model->tableName();
                    // Populate
                    if($debug) { echo 'updating nameId in '.$model->tableName()."\n"; }
                    Yii::app()->db->createCommand("
                             UPDATE `".$model->tableName()."`
                             SET `nameId`=CONCAT(`name`,'_',`id`)
                             WHERE 1")->execute();
                }
            }

            // Now that that's done, it is safe to create unique keys for
            // existing custom modules:
            foreach($customModels as $modelName){
                $class = X2Model::getModelName($modelName);
                if(empty($class) || !class_exists($class))
                    continue;
                $model = X2Model::model($class);
                if($model->asa('LinkableBehavior') instanceof LinkableBehavior && $model->hasAttribute('name')){
                    $table = $model->tableName();
                    // Create the field; it's a custom module's table that
                    // hasn't been created yet:
                    if($debug){ echo "Adding UNIQUE key to  $table for custom model ".get_class($model)."\n"; }
                    Yii::app()->db->createCommand("
                                 ALTER TABLE $table
                                 ADD UNIQUE(`nameId`)")->execute();
                }
            }

            // At this stage, all nameId fields have been created and populated.
            //
            // Now it's time to update the referencing columns:
            $linkFields = Yii::app()->db->createCommand()
                    ->select('modelName,fieldName,linkType')
                    ->from('x2_fields')
                    ->where('type="link" AND linkType IS NOT NULL AND linkType != ""')
                    ->queryAll(true);
            foreach($linkFields as $field){
                if($debug){ echo "updating refs for field {$field['modelName']}.{$field['fieldName']}\n"; }
                $class = X2Model::getModelName($field['modelName']);
                if(empty($class) || !class_exists($class))
                    continue;
                $model = X2Model::model($class);
                $referencedClass = X2Model::getModelName($field['linkType']);
                if(empty($referencedClass) || !class_exists($referencedClass))
                    continue;
                $referencedModel = X2Model::model($referencedClass);
                if($referencedModel->asa('LinkableBehavior') instanceof LinkableBehavior){
                    // Referenced model exists and is linkable. Update refs:
                    $table = $model->tableName();
                    $referencedTable = $referencedModel->tableName();
                    $column = $field['fieldName'];
                    $query = "UPDATE `$table` AS `t1`
                                 INNER JOIN `$referencedTable` AS `t2`
                                 ON CAST(`t1`.`$column` AS CHAR)=`t2`.`id`
                                 SET `t1`.`$column`=CAST(`t2`.`nameId` AS CHAR)";
                    if($debug){
                        echo "Running: $query\n";
                    }
                    Yii::app()->db->createCommand($query)->execute();
                }
            }
            return false;
        };

$success = !($migrateLinkFields());
?>
