<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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
 * (unused) ???
 * 
 * @package X2CRM.components 
 */
class X2Rules {

	// public $model;
	
	public function run() {

		
		/*  Notification Engine
		
		Events
		
			Example 1: record_field_change, fieldName=>'dealValue', 'comparison'=>'>', 'value'=>'20000'
			
			Example 2: record_inactive, attributes = (('dealValue', '>', '20000'), ('account','not_empty')), duration = '5 days'

			Event										Parameters															Response Variables
			-------------------------------------------------------------------------------------------------------------------------------------------
			Record - view								model type, model attributes										record, user
			Record - field change						model type, model attributes, fieldName, comparison type/value		record, old attributes, user
			Record - edit								model type, model attributes, user									record, user
			Record - create action						model type, model attributes, user									record, user
			Record - complete action					model type, model attributes, user									record, user
			Record - create								model type, model attributes, user									record, user
			Record - delete								model type, model attributes, user									record, user
			Record - inactive (no edits, actions, etc)	model type, model attributes, user, duration						record, last activity, user
								
			Workflow - start							workflowId, stage number, user										record, action, user
			Workflow - complete							workflowId, stage number, user										record, action, user
			Workflow - start stage						workflowId, stage number, user										record, action, user
			Workflow - complete stage					workflowId, stage number, user										record, action, user
			Workflow - undo stage						workflowId, stage number, user										record, action, user

			Generic action - complete							
			Generic action - uncomplete							
			
			Weblead										model type, lead source, model attributes							record, lead source
			Web activity								model attributes, campaign, 

		Conditions *
		
			Record attribute (=, <, >, <>, in list, not in list, empty, not empty, contains)
			Linked record attribute (eg. a contact's account has > 30 employees)
			Current user
			Current time (day of week, hours, etc)
			Current time in record's timezone
			Is user X logged in
			Workflow status (in workflow X, started stage Y, completed Y, completed all)
			
			* Any condition parameter can be a variable from the record
				Example 1: test if current user = {assignedTo} (if the user initiating the event is the owner of the record)
				Example 2: test if current_time > {dueDate} on an action (if the action is overdue)
				Example 3: test if user {assignedTo} is logged in (if the record's owner is logged in)
				Example 4: 
			* Conditions can be chained with nested AND/OR
				Example: if (account="Black Mesa" OR city="17") AND lastName="freeman"
			
			
			
			
		Actions
		
		
			Action										Parameters (can use response variables)
			-------------------------------------------------------------------------------------------------------------------------------------------
			Email										to, from, subject, body

			Notification								user, message

			Create Action								assignedTo, type, dueDate, priority, description

			Change Field								attribute, value

			Start workflow stage						workflow, stage number

			Complete workflow stage						workflow, stage number

			Undo workflow stage							workflow, stage number
				
				
			Create Record								type, all attributes
			
			Create/Remove Tags							tags
			
			Request URL (for APIs)						url, GET and POST variables

		
		
		Value calculation:
		
			Example: when 
			
			record_inactive(attributes={},duration='1 day') => notification (user="{record.assignedTo}",message="{record.linkTo} has been inactive for {{now}-{lastActivity}}"
				
				
				
				
				
				
			
			
			
			
			
			
			
			
		 */
	}
}
?>