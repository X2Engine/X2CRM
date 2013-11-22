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
 * @package X2CRM.models 
 */
class Rules {
	
	public static function applyRules($model, $version){
		if($model instanceof Contacts){
			$model=Rules::contactRules($model, $version);
		}else if($model instanceof Users){
			$model=Rules::userRules($model, $version);
		}else if($model instanceof Opportunity){
			$model=Rules::opportunityRules($model, $version);
		}else if($model instanceof Actions){
			$model=Rules::actionsRules($model, $version);
		}else if($model instanceof Accounts){
			$model=Rules::accountRules($model, $version);
		}else if($model instanceof Profile){
			$model=Rules::profileRules($model, $version);
		}else{
			
		}
		return $model;
	}
	
	private static function contactRules($model, $version){
		return $model;
	}
	private static function userRules($model, $version){
		return $model;
	}
	
	private static function opportunityRules($model, $version){
		return $model;
	}
	
	private static function actionsRules($model, $version){
		return $model;
	}
	
	private static function accountRules($model, $version){
		return $model;
	}
	
	private static function profileRules($model, $version){
		return $model;
	}
}

?>
