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
 * Logs translation activity.
 *
 * @package X2CRM.components
 */
class TranslationLogger extends CComponent {
	public function log($event) {
		// create_function('$event', 'Yii::log("[".$event->language."] [".$event->message."] \'".$event->category."\'","info","translations");');

		$str = '['.$event->language.'/'.$event->category.'.php]';
		for($i=0;$i<ceil(16 - (strlen($event->category))); $i++)
			$str .= ' ';
		$str .= '"'.$event->message.'"';

		Yii::log($str,'info','translations');
	}
}
?>