<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

/**
 * Provides an inline form for sending email from a view page.
 * 
 * @package X2CRM.components 
 */
class InlineEmailForm extends X2Widget {

	public $model;
	public $attributes;

	public $errors = array();
	public $startHidden = false;

	public function init() {
		// $this->startHidden = false;
	
		$this->model = new InlineEmail;
		$this->model->attributes = $this->attributes;
		$signature = Yii::app()->params->profile->getSignature(true);
		
		//if message comes prepopulated, don't overwrite with signature
		if (empty($this->model->message)) {
			$this->model->message = empty($signature)? '' : '<br><br><!--BeginSig--><font face="Arial" size="2">'.$signature.'</font><!--EndSig-->';
		}
		
		// die(var_dump($this->model->attributes));
		
		if(isset($_POST['InlineEmail'])) {
			$this->model->attributes = $_POST['InlineEmail'];
			$this->startHidden = false;
		}

 		Yii::app()->clientScript->registerScript('toggleEmailForm',
		($this->startHidden? "window.hideInlineEmail = true;\n" : "window.hideInlineEmail = false;\n") .
		"function toggleEmailForm() {
			setupEmailEditor();
			
			if($('#inline-email-form .wide.form').hasClass('hidden')) {
				$('#inline-email-form .wide.form').removeClass('hidden');
				$('#inline-email-form .form.email-status').remove();
				return;
			}
			
			if($('#inline-email-form').is(':hidden')) {
				$('.focus-mini-module').removeClass('focus-mini-module');
				$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
				$('html,body').animate({
					scrollTop: ($('#inline-email-top').offset().top - 100)
				}, 300);
			}
			
			$('#inline-email-form').animate({
				opacity: 'toggle',
				height: 'toggle'
			}, 300); // ,function() {  $('#inline-email-form #InlineEmail_subject').focus(); }
			
			$('#InlineEmail_subject').addClass('focus');
			$('#InlineEmail_subject').focus();
			$('#InlineEmail_subject').blur(function() {
				$(this).removeClass('focus');
			});
		}
		
		$(function() {
			// give send-email module focus when clicked
		    $('#inline-email-form').click(function() {
		    	if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
		    		$('.focus-mini-module').removeClass('focus-mini-module');
		    		$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
		    	}
		    });
		    
		    // give send-email module focus when tinyedit clicked
		    $('#email-message').click(function() {
		        if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
		        	$('.focus-mini-module').removeClass('focus-mini-module');
		        	$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
		        }
		    });
		});
		",CClientScript::POS_HEAD);
		
		Yii::app()->clientScript->registerScript('inlineEmailFormCC',
		"$(document).delegate('#email-template','change',function() {
			if($(this).val() != '0') // && $('#email-subject').val() == ''
				$('#email-subject').val($(this).find(':selected').text());
			$('#preview-email-button').click();
		});
		
		",CClientScript::POS_READY);
		
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() .'/js/inlineEmailForm.js');
		
		parent::init();
	}

	public function run() {
		$action = new InlineEmailAction($this->controller,'inlineEmail');
		$action->model = &$this->model;
		$action->run(); 
	}
}
