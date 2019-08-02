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
 * @file 1395878402-list-criterion-update.php
 *
 * Update and fix dynamic list criterion.
 */

$listCriterionUpdate = function(){
            // Step 1: get all link-type fields of the contacts model:
            $attributes = Yii::app()->db->createCommand()
                    ->select('fieldName,linkType')
                    ->from(Fields::model()->tableName())
                    ->where("modelName='Contacts' AND type='link'")
                    ->queryAll();
            foreach($attributes as $attribute){
                if($model = X2Model::model($attribute['linkType'])){
                    $params[':attr'] = $attribute['fieldName'];
                    $sql = 'UPDATE '.X2ListCriterion::model()->tableName().' lc INNER JOIN '.$model->tableName().' c'
                            .' ON lc.value=c.id SET lc.value=c.nameId WHERE lc.type="attribute" AND lc.attribute=:attr';
                    Yii::app()->db->createCommand($sql)
                            ->execute($params);
                }
            }
        };

$listCriterionUpdate();
?>
