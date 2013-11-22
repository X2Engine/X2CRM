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
 * Widget class for the attatchment form
 * 
 * @package X2CRM.components 
 */
class Attachments extends X2Widget {

	public $associationType = '';
	public $startHidden = false;
	public $associationId='';
	public function init() {
	
 		Yii::app()->clientScript->registerScript('toggleAttachmentFormWidget',
		"function toggleAttachmentForm() {			
			if($('#attachment-form').is(':hidden')) {
				$('.focus-mini-module').removeClass('focus-mini-module');
				$('#attachment-form').find('.form').addClass('focus-mini-module');
				$('html,body').animate({
					scrollTop: ($('#attachment-form-top').offset().top - 100)
				}, 300);
			}
			$('#attachment-form').animate({
				opacity: 'toggle',
				height: 'toggle'
			}, 300);
		}
		
		$(function() {
			// give attachment module focus when clicked
		    $('#attachment-form').click(function() {
		    	if(!$('#attachment-form').find('.form').hasClass('focus-mini-module')) {
		    		$('.focus-mini-module').removeClass('focus-mini-module');
		    		$('#attachment-form').find('.form').addClass('focus-mini-module');
		    	}
		    });
		});
		",CClientScript::POS_HEAD);
	
		parent::init();
	}

	public function run() {
		$this->render('attachments',array('startHidden'=>$this->startHidden));
	}
}

?>
