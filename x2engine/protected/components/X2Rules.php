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

			Description							type						Parameters															Response Variables
			-------------------------------------------------------------------------------------------------------------------------------------------
			
			
			Record Activity													model type, filters, list
			---------------													
			View								record_view					model type, model attributes										record, user
			Field change						record_field_change			model type, model attributes, fieldName, comparison type/value		record, old attributes, user
			Edit								record_update				model type, model attributes, user									record, user
			Create action						record_action_create		model type, model attributes, user									record, user
			Complete action						record_action_complete		model type, model attributes, user									record, user
			Create								record_create				model type, model attributes, user									record, user
			Delete								record_delete				model type, model attributes, user									record, user
			Inactive (no edits, actions, etc)	record_inactive				model type, model attributes, user, duration						record, last activity, user
			Tags (added, removed)				record_tag_add	
												record_tag_remove	
													
			Workflow - start					workflow_start				workflowId, stage number, user										record, action, user
			Workflow - complete					workflow_complete			workflowId, stage number, user										record, action, user
			Workflow - start stage				workflow_stage_start		workflowId, stage number, user										record, action, user
			Workflow - complete stage			workflow_stage_complete		workflowId, stage number, user										record, action, user
			Workflow - undo stage				workflow_stage_undo			workflowId, stage number, user										record, action, user
				
			Generic action - complete			
			Generic action - uncomplete			
							
			Weblead								weblead						model type, lead source, model attributes							record, lead source
			Web activity						record_webtracker			model attributes, campaign, 
			

		Parameters:
		
			model type
			model attributes (=, <, >, <>, in list, not in list, empty, not empty, contains)
			linked model attributes
			Current time (day of week, hours, etc)
			Current time in record's timezone
			Is user X active
			Workflow status (in workflow X, started stage Y, completed Y, completed all)
			
			* Any condition parameter can be a variable from the record
				Example 1: test if current user = {assignedTo} (if the user initiating the event is the owner of the record)
				Example 2: test if current_time > {dueDate} on an action (if the action is overdue)
				Example 3: test if user {assignedTo} is logged in (if the record's owner is logged in)
				Example 4: 
			* Conditions can be chained with nested AND/OR
				Example: if (account="Black Mesa" OR city="17") AND lastName="freeman"
			
			
			
			
		Actions
		
		
			Action								type									Parameters (can use response variables)
			-------------------------------------------------------------------------------------------------------------------------------------------
			Email								email								to, from, subject, body
			Create Event						new_event							type (automatic, custom), text (optional), user (optional), create notification?
			Reminder							reminder							text, timestamp (creates an event)
			Create Action						new_action							assignedTo, type, dueDate, priority, description
			Change Field						field_change						attribute, value
			Start workflow stage				workflow_start						workflow, stage number(s)
			Complete workflow stage				workflow_complete					workflow, stage number(s)
			Undo workflow stage					workflow_revert						workflow, stage number(s)
			Create Record						new_record							type, all attributes
			Create/Remove Tags					add_tag								tags
												remove_tag
			Request URL (for APIs)				API_call							url, GET and POST variables
			Add to List (static only)			list_add							list name
			Remove from List					list_remove							list name

		
		
		Value calculation:
		
			Example: when 
			
			record_inactive(attributes={},duration='1 day') => notification (user="{record.assignedTo}",message="{record.linkTo} has been inactive for {{now}-{lastActivity}}"
				
				
				
				
		Variables:
		
			
				
				
				
				
				
				
				
				
				
		Tables:
		
		
			CREATE TABLE x2_flows(
				id						INT				AUTO_INCREMENT PRIMARY KEY,
				active					TINYINT			NOT NULL DEFAULT 1,
				name					VARCHAR(100)	NOT NULL,
				createDate				BIGINT			NOT NULL,
				lastUpdated				BIGINT			NOT NULL
			) ENGINE InnoDB  COLLATE = utf8_general_ci;
			
			CREATE TABLE x2_flow_items(
				id						INT				AUTO_INCREMENT PRIMARY KEY,
				flowId					INT				NOT NULL,
				active					TINYINT			NOT NULL DEFAULT 1,
				type					VARCHAR(40)		NOT NULL,
				parent					INT				NOT NULL,
				
				FOREIGN KEY (flowId) REFERENCES x2_flows(id) ON UPDATE CASCADE ON DELETE CASCADE
				
			) ENGINE InnoDB  COLLATE = utf8_general_ci;
			
			CREATE TABLE x2_flow_params(
				id						INT				AUTO_INCREMENT PRIMARY KEY,
				flowId					INT				NOT NULL,
				itemId					INT				NOT NULL,
				type					VARCHAR(40)		NOT NULL,
				attribute				VARCHAR(100)	NULL,
				operator				VARCHAR(40)		NULL,
				value					VARCHAR(500)	NULL,
				
				FOREIGN KEY (flowId) REFERENCES x2_flows(id) ON UPDATE CASCADE ON DELETE CASCADE,
				FOREIGN KEY (itemId) REFERENCES x2_flow_items(id) ON UPDATE CASCADE ON DELETE CASCADE
				
			) ENGINE InnoDB  COLLATE = utf8_general_ci;
			
			
			

			
			places to check for triggers:
			
			
			X2Model::create()
			X2Model::calculateChanges()			field change, reassignment, 
			
			ActionsController::actionComplete()
			ActionsController::actionUncomplete()
			
			
			SiteController::actionLogin()
			SiteController::actionLogout()
			
			ApiController::actionWebLead()

		 */
		 
		// $flow = new X2Flow;
		// $flow->active = true;
		// $flow->name = 'testFlow';
		// if($flow->save()) {
		
			// $flowItem = new X2FlowItem;
			// $flowItem->flowId = $flow->id;
			// $flowItem->type = 'record_update';
			// $flowItem->active = true;
			// $flowItem->save();
		// }
		// $flowParam = new X2FlowParam;
		// $flowParam->
	}
	
	
	public static function getRules($trigger) {
	
		
		// CActiveRecord::model('
		
	
	
	
	
	
	
	}

}
?>
