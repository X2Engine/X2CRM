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
 * Widget for an input which posts text to a social feed.
 * 
 * @package X2CRM.components 
 */
class SocialForm extends X2Widget {
	public $vars;

	public function init() {
		Yii::app()->clientScript->registerScript('hideActionForm',
			"$(document).ready(hideActionForm);
			function hideActionForm() {
				$('#action-form').hide();
			}
			",CClientScript::POS_HEAD);
		
		if (!$this->startHidden) {
			Yii::app()->clientScript->registerScript('gotoActionForm',
				"$('#action-form').ready(gotoActionForm);
				function gotoActionForm() {
					$('#action-form').show();
					//toggleForm('#action-form',400);
					$('#action-form #Actions_actionDescription').focus();
				}
				",CClientScript::POS_HEAD);
		}
		parent::init();
	}

	public function run() {
		echo $this->render('socialForm', array());
	}
}