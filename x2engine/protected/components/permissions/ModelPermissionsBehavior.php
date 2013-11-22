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
 * 
 *
 * @package X2CRM.components.permissions
 */
abstract class ModelPermissionsBehavior extends CActiveRecordBehavior {

    /**
     * Returns a CDbCriteria containing record-level access conditions.
     * @return CDbCriteria
     */
    abstract function getAccessCriteria();

    /**
     * Returns a number from 0 to 3 representing the current user's access level using the Yii auth manager
     * Assumes authItem naming scheme like "ContactsViewPrivate", etc.
     * This method probably ought to overridden, as there is no reliable way to determine the module a model "belongs" to.
     * @return integer The access level. 0=no access, 1=own records, 2=public records, 3=full access
     */
    abstract function getAccessLevel();

    /**
     * Generates SQL condition to filter out records the user doesn't have permission to see.
     * This method is used by the 'accessControl' filter.
     * @param Integer $accessLevel The user's access level. 0=no access, 1=own records, 2=public records, 3=full access
     * @param Boolean $useVisibility Whether to consider the model's visibility setting
     * @param String $user The username to use in these checks (defaults to current user)
     * @return String The SQL conditions
     */
    abstract function getAccessConditions($accessLevel, $useVisibility = true, $user = null);

}

?>
