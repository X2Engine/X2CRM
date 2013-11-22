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

Yii::import('system.test.CDbFixtureManager');

/**
 * @package X2CRM.components
 */
class X2FixtureManager extends CDbFixtureManager {

	/**
	 * Override of {@link CDbFixtureManager}'s resetTable 
	 * 
	 * Permits array-style definition of fixtures much like fixture files themselves
	 */
	public function resetTable($tableName) {
		$initFile = $this->basePath . DIRECTORY_SEPARATOR . $tableName . $this->initScriptSuffix;
		if (is_file($initFile)) {
			$tbl_data = require($initFile);
			if (is_array($tbl_data)) {
				Yii::app()->db->createCommand()->truncateTable($tableName);
				foreach ($tbl_data as $rec)
					Yii::app()->db->createCommand()->insert($tableName, $rec);
			}
		} else
			$this->truncateTable($tableName);
	}

}

?>
