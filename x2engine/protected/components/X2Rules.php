<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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

			
			
			
		Trigger code:
		
		// run automation
		X2Flow::trigger('campaign_webtracker',array(
			'model'=>$contact,
			'campaign'=>$campaign,
			'url'=>$url,
		));
			
			

		Triggers					  Done	Trigger Location								Name									model type, filters, list
		------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		Create							x	X2ChangelogBehavior::afterCreate()				record_created							model type, model attributes, user									record, user
		View							x	x2base::view()									record_viewed							model type, model attributes										record, user
		Update								X2ChangeLogBehavior::beforeSave()				record_updated							model type, model attributes, user									record, user
		Delete							x	X2ChangelogBehavior::afterDelete()				record_deleted							model type, model attributes, user									record, user
		Field change						X2ChangeLogBehavior:: ??? ()					record_field_changed					model type, model attributes, fieldName, comparison type/value		record, old attributes, user
			
		Create action						ActionsController::actionPublisherCreate()		record_action_created					model type, model attributes, user									record, user
		Complete action						ActionsController::actionComplete()				record_action_completed					model type, model attributes, user									record, user
		Uncomplete action					ActionsController::actionUnomplete()			record_action_uncompleted				model type, model attributes, user									record, user
			
			
		Action Overdue						?												action_overdue
		Inactive							?												record_inactive							model type, model attributes, user, duration						record, last activity, user
		Timer								?												timer									timestamp, repeat
		Campaign Timer event				?												campaign_timer
		
		Tags (added, removed)			x	TagBehavior::addTags()							record_tag_added				
										x	TagBehavior::removeTags()						record_tag_removed
										x	TagBehavior::afterSave()						
		
		Workflow - start				x	WorkflowController::actionStartStage()			workflow_started							workflowId, stage number, user										record, action, user
		Workflow - complete				x	WorkflowController::actionCompleteStage()		workflow_completed						workflowId, stage number, user										record, action, user
		Workflow - start stage			x	WorkflowController::updateWorkflowChangelog()	workflow_stage_started					workflowId, stage number, user										record, action, user
		Workflow - complete stage		x	WorkflowController::updateWorkflowChangelog()	workflow_stage_completed					workflowId, stage number, user										record, action, user
		Workflow - undo stage			x	WorkflowController::updateWorkflowChangelog()	workflow_stage_reverted						workflowId, stage number, user										record, action, user
													
		Action - complete				x	Actions::complete()								action_completed
		Action - uncomplete				x	Actions::uncomplete()							action_uncompleted
																
		Weblead							x	ContactsController::actionWeblead()				weblead									model type, lead source, model attributes							record, lead source
										x	Contacts/pro/actionWeblead.php
				
													
		Campaign - email open			x	X2ListItem::markOpened()						campaign_opened
		Campaign - email click			x	X2ListItem::markClicked()						campaign_clicked
		Campaign - unsubscribe			x	X2ListItem::unsubscribe()						campaign_unsubscribed
		
		Newsletter - email open			x	X2ListItem::markOpened()						newsletter_opened
		Newsletter - email click		x	X2ListItem::markClicked()						newsletter_clicked
		Newsletter - unsubscribe		x	X2ListItem::unsubscribe()						newsletter_unsubscribed
			
		Campaign - web activity			x	WebListenerAction::trackCampaignClick()			campaign_webactivity
		Newsletter - web activity		x	WebListenerAction::trackCampaignClick()			newsletter_webactivity
													
		Web activity					x	WebListenerAction::trackGeneric()				record_webactivity			model, url
												
		User login						x	X2WebUser::afterLogin()							user_login					user, group, role													user
		User logout						x	X2WebUser::beoreLogout()						user_logout					user, group, role													user
		
		
		
		
		Trigger Criteria							Type					Operators														Params
		------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		Model class									modelType				=																modelType
		model attributes							attribute				=, <, >, <>, in list, not in list, empty, not empty, contains	
		linked model attributes						
		Current time (day of week, hours, etc)	
		Current time in record's timezone
		Is user X active							user_active				=
		Workflow status (in workflow X, started stage Y, completed Y, completed all)
			
			
			
			
			* Any condition parameter can be a variable from the record
				Example 1: test if current user = {assignedTo} (if the user initiating the event is the owner of the record)
				Example 2: test if current_time > {dueDate} on an action (if the action is overdue)
				Example 3: test if user {assignedTo} is logged in (if the record's owner is logged in)
				Example 4: 
			* Conditions can be chained with nested AND/OR
				Example: if (account="Black Mesa" OR city="17") AND lastName="freeman"
			
			
		Actions
		
			Action								type				Required Parameters											Optional Parameters
			-------------------------------------------------------------------------------------------------------------------------------------------
			Send Email							email				from, (to or model), (subject or template), (body or template)
			
			Reassign Record						reassign_record		model, user
			Update Record						update_record		model, attributes, values
			Create Record						create_record		modelType, attributes, values
			Create Event						create_event		type, text, user											create notification?
			Create Action						create_action		attribute, value											model
			Create Notification					create_notif		user, type, value											model
			Reminder							reminder			text, timestamp
			
			Start workflow stage				workflow_start		workflow, stage number(s)
			Complete workflow stage				workflow_complete	workflow, stage number(s)
			Undo workflow stage					workflow_revert		workflow, stage number(s)
			
			Launch Campaign						campaign_launch		campaignId
			
			Create/Remove Tags					add_tags			tags
												remove_tags			tags
			
			Add to List (static only)			list_add			listId
			Remove from List					list_remove			listId
			
			Request URL (for APIs)				API_call			url, GET and POST variables
		
		
		Value calculation:
		
			Example: when 
			
			record_inactive(attributes={},duration='1 day') => notification (user="{record.assignedTo}",message="{record.linkTo} has been inactive for {{now}-{lastActivity}}"
				

			
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
				variable				VARCHAR(100)	NULL,
				operator				VARCHAR(40)		NULL,
				value					VARCHAR(500)	NULL,
				
				FOREIGN KEY (flowId) REFERENCES x2_flows(id) ON UPDATE CASCADE ON DELETE CASCADE,
				FOREIGN KEY (itemId) REFERENCES x2_flow_items(id) ON UPDATE CASCADE ON DELETE CASCADE
				
			) ENGINE InnoDB  COLLATE = utf8_general_ci;
			
			
			
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
