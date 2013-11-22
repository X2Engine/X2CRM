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
 * MOTD widget class.
 * 
 * Class for rendering the widget that displays the message of the day.
 * 
 * @package X2CRM.components 
 */
class MessageBox extends X2Widget {

	public $visibility;
	public function init() {	
		parent::init();
	}

	public function run() {
		$content=Social::model()->findByAttributes(array('type'=>'motd'));
		if(isset($content))
			$content=$content->data;
		else
			$content=Yii::t('app','Please enter a message of the day!');
		$this->render('messageBox', array(
			'content'=>$content,
		));
	}
}
