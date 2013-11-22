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
 * Custom extension of CClientScript used by the app.
 * 
 * @package X2CRM.components 
 */
class X2ClientScript extends CClientScript {
	/**
	 * Inserts the scripts at the beginning of the body section.
	 * @param boolean $includeScriptFiles whether to include external files, or just dynamic scripts
	 * @return string the output to be inserted with scripts.
	 */
	public function renderOnRequest($includeScriptFiles = false) {
		$html='';
		if($includeScriptFiles) {
			foreach($this->scriptFiles as $scriptFiles) {
				foreach($scriptFiles as $scriptFile)
					$html.=CHtml::scriptFile($scriptFile)."\n";
			}
		}
		foreach($this->scripts as $script)	// the good stuff!
			$html.=CHtml::script(implode("\n",$script))."\n";

		if($html!=='')
			return $html;
	}
}