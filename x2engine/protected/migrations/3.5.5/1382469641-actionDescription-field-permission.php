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
 * @file 1382469641-actionDescription-field-permission.php
 *
 * Migration script that adds field-level permissions to Actions.actionDescription
 */


$run = function() {
$roleIDs = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_roles')
                    ->queryColumn();

            if(is_array($roleIDs) && !empty($roleIDs)){
                $fieldId = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from('x2_fields')
                        ->where('modelName=:modelName AND fieldName=:fieldName', array(
                            ':modelName' => 'Actions',
                            ':fieldName' => 'actionDescription'
                                )
                        )
                        ->queryScalar();

                $params = array();
                $records = array();
                $paramCount = 0;
                $cols = array('roleId', 'fieldId', 'permission');

                $permission = '2';
                $fieldId = (string) $fieldId;


                foreach($roleIDs as $roleId){
                    $record = array();
                    foreach($cols as $col){
                        $param = ":$col$paramCount";
                        $params[$param] = ${$col};
                        $record[] = $param;
                    }
                    $records[] = '('.implode(',', $record).')';
                    $roleIdParam = ":roleId$paramCount";
                    $paramCount++;
                }

                $sql = 'INSERT INTO `x2_role_to_permission` (`'.implode('`,`',$cols).'`) VALUES '.implode(',',$records);
                $command = Yii::app()->db->createCommand($sql);
                $command->execute($params);
            }
        };
$run();


?>
