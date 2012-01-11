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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

Yii::import('zii.widgets.CWidget');

class InlineEmailForm extends CWidget {
	// public $name;
	// public $address;
	public $to;
	public $subject;
	public $message;
	public $redirect;
	public $redirectId;
	public $redirectType;

	public $errors = array();
	public $startHidden = false;

	public function init() {
	
		if(isset($_POST))
			$startHidden = false;
	
		if(isset($_POST['inlineEmail_to']))
			$this->name = $_POST['inlineEmail_to'];
	
		// if(isset($_POST['inlineEmail_name']))
			// $this->name = $_POST['inlineEmail_name'];
			
		// if(isset($_POST['inlineEmail_address']))
			// $this->address = $_POST['inlineEmail_address'];
			
		if(isset($_POST['inlineEmail_subject']))
			$this->subject = $_POST['inlineEmail_subject'];
			
		if(isset($_POST['inlineEmail_message']))
			$this->message = $_POST['inlineEmail_message'];
	
		
		Yii::app()->clientScript->registerScript('toggleEmailForm',
			($this->startHidden? "$(document).ready(function() { $('#email-form').hide(); });\n" : '')
			. "function toggleEmailForm() {
				$('#email-form').toggle('blind',300,function() {
					$('#email-form #email-subject').focus();
				});
			}
			",CClientScript::POS_HEAD);
		parent::init();
	}

	public function run() {
		// $actionModel = new Actions;
		// $actionModel->associationType = $this->associationType;
		// $actionModel->associationId = $this->associationId;
		// $actionModel->assignedTo = $this->assignedTo;
		echo $this->render('emailForm',array(
			// 'name'=>$this->name,
			// 'address'=>$this->address,
			'to'=>$this->to,
			'subject'=>$this->subject,
			'message'=>$this->message,
			'redirect'=>$this->redirect,
			// 'redirectId'=>$this->redirectId,
			// 'redirectType'=>$this->redirectType,
			'errors'=>$this->errors
		));	//, array('actionModel'=>$actionModel,'users'=>$this->users,'inlineForm'=>true)
	}
}
?>