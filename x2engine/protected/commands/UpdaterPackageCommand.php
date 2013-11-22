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
 * Displays the declaration of an array containing the names of all files in the
 * updater utility.
 * 
 * @package X2CRM.commands
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdaterPackageCommand extends CConsoleCommand {
	public function run() {
        $comp = new CComponent();
        $ubconfig = array_merge(array(
            'class' => 'UpdaterBehavior',
            'isConsole' => true,
            'noHalt' => true,
        ));

        $comp->attachBehavior('UpdaterBehavior', $ubconfig);
        // The files directly involved in the update process:
        $updaterFiles = $comp->updaterFiles;
        // The web-based updater's action classes, which are defined separately:
        $updaterActions = $comp->getWebUpdaterActions(false);
        foreach($updaterActions as $name => $properties){
            $updaterFiles[] = UpdaterBehavior::classAliasPath($properties['class']);
        }
        echo "\$deps = ";
        var_export($updaterFiles);
        
    }
}
?>
