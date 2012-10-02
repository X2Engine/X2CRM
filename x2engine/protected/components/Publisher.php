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
 * Widget class for displaying all available inline actions.
 * 
 * Displays tabs for "log a call","new action" and the like.
 * 
 * @package X2CRM.components 
 */
class Publisher extends X2Widget {
	public $associationType;		// type of record to associate actions with
	public $associationId = '';		// record to associate actions with
	public $assignedTo = null;	// user actions will be assigned to by default
	
	// show all tabs by default
	public $showLogACall = true;
	public $showNewAction = true;
	public $showNewComment = true;
	public $showNewEvent = false;
	public $halfWidth = false;
	
	public function run() {
		$model = new Actions;
		$model->associationType = $this->associationType;
		$model->associationId = $this->associationId;
		if($this->assignedTo)
			$model->assignedTo = $this->assignedTo;
		else
			$model->assignedTo = Yii::app()->user->getName();
		
		$this->render($this->halfWidth? 'publisherHalfWidth':'publisher',
			array(
				'model' => $model,
				'showLogACall'=>$this->showLogACall,
				'showNewAction'=>$this->showNewAction,
				'showNewComment'=>$this->showNewComment,
				'showNewEvent'=>$this->showNewEvent,
			)
		);
	}
}