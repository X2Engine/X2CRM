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
	
	// public function run() {

		
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
				modelClass				VARCHAR(40)		NULL,
				isTrigger				TINYINT			NOT NULL DEFAULT 0,
				nextIfTrue				INT				NULL,
				nextIfFalse				INT				NULL,
				config					TEXT			NULL,
				
				FOREIGN KEY (flowId) REFERENCES x2_flows(id) ON UPDATE CASCADE ON DELETE CASCADE
				
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
	// }
	
	
	// public static function getRules($trigger) {
	
		
		// CActiveRecord::model('
		
	
	
	
	
	
	
	// }

	
	
	
	
	// const T_VAR = 0;
	// const T_SPACE = 1;
	// const T_COMMA = 2;
	// const T_OPEN_BRACKET = 3;
	// const T_CLOSE_BRACKET = 4;
	// const T_PLUS = 5;
	// const T_MINUS = 6;
	// const T_TIMES = 7;
	// const T_DIVIDE = 8;
	// const T_OPEN_PAREN = 9;
	// const T_CLOSE_PAREN = 10;
	// const T_NUMBER = 11;
	// const T_ERROR = 12;

	protected static $_tokenChars = array(
		',' => 'COMMA',
		'{' => 'OPEN_BRACKET',
		'}' => 'CLOSE_BRACKET',
		'+' => 'ADD',
		'-' => 'SUBTRACT',
		'*' => 'MULTIPLY',
		'/' => 'DIVIDE',
		'%' => 'MOD',
		// '(' => 'OPEN_PAREN',
		// ')' => 'CLOSE_PAREN',
	);
	protected static $_tokenRegex = array(
		'\d+\.\d+\b|^\.?\d+\b' => 'NUMBER',
		'[a-zA-Z]\w*\.[a-zA-Z]\w*' => 'VAR_COMPLEX',
		'[a-zA-Z]\w*' => 'VAR',
		'\s+' => 'SPACE',
		'.' => 'UNKNOWN',
	);
	
	/**
	 * Breaks a string expression into an array of 2-element arrays (type, value) 
	 * using {@link $_tokenChars} and {@link $_tokenRegex} to identify tokens
	 * @param string $str the input expression
	 * @return array a flat array of tokens
	 */
	protected static function tokenize($str) {
		$tokens = array();
		$offset = 0;
		while($offset < mb_strlen($str)) {
			$token = array();
			
			$substr = mb_substr($str,$offset);	// remaining string starting at $offset
		
			foreach(self::$_tokenChars as $char => &$name) {	// scan single-character patterns first
				if(mb_substr($substr,0,1) === $char) {
					$tokens[] = array($name);	// add it to $tokens
					$offset++;
					continue 2;
				}
			}
			foreach(self::$_tokenRegex as $regex => &$name) {	// now loop through regex patterns
				$matches = array();
				if(preg_match('/^'.$regex.'/u',$substr,$matches) === 1) {
					$tokens[] = array($name,$matches[0]);	// add it to $tokens
					$offset += mb_strlen($matches[0]);
					continue 2;
				}
			}
			$offset++;	// no infinite looping, yo
		}
		return $tokens;
	}

	/**
	 * Adds a new node at the end of the specified branch
	 * @param array &$tree the tree object
	 * @param array $nodePath array of branch indeces leading to the target branch
	 * @value array an array containing the new node's type and value
	 */
	protected static function addNode(&$tree,$nodePath,$value) {
		if(count($nodePath) > 0)
			return self::addNode($tree[array_shift($nodePath)],$nodePath,$value);
		
		$tree[] = $value;
		return count($tree) - 1;
	}

	/**
	 * Checks if this branch has only one node and eliminates it by moving the child node up one level
	 * @param array &$tree the tree object
	 * @param array $nodePath array of branch indeces leading to the target node
	 */
	protected static function simplifyNode(&$tree,$nodePath) {
		if(count($nodePath) > 0)													// before doing anything, recurse down the tree using $nodePath  
			return self::simplifyNode($tree[array_shift($nodePath)],$nodePath);		// to get to the targeted node
			
		$last = count($tree) - 1;
		
		if(empty($tree[$last][1]))
			array_pop($tree);
		elseif(count($tree[$last][1]) === 1)
			$tree[$last] = $tree[$last][1][0];
	}

	/**
	 * Processes the expression tree and attempts to evaluate it
	 * @param array &$tree the tree object
	 * @param boolean $expression
	 * @return mixed the value, or false if the tree was invalid
	 */
	protected static function parseExpression(&$tree,$expression=false) {
	
		$answer = 0;
		
		// echo '1';
		for($i=0;$i<count($tree);$i++) {
			$prev = isset($tree[$i+1])? $tree[$i+1] : false;
			$next = isset($tree[$i+1])? $tree[$i+1] : false;
		
			
			switch($tree[$i][0]) {
			
				case 'VAR':
				case 'VAR_COMPLEX':
					continue 2;
				
				case 'EXPRESSION':	// please
					$subresult = self::parseExpression($tree[$i][1],true);	// the expression itself must be valid
					if($subresult === false)
						return $subresult; 
						
					// if($next !== false)
					break;
				
				case 'EXPONENT':	// excuse
					break;
				
				case 'MULTIPLY':	// my 
					break;
					
				case 'DIVIDE':	// dear
					break;
				
				case 'MOD':
					break;
				
				
				case 'ADD':	// aunt
					break;
				
				case 'SUBTRACT':	// sally
					break;
					
				case 'COMMA':

					break;
				case 'NUMBER':
					break;

					
				case 'SPACE':
				
				case 'UNKNOWN':
					return 'Unrecognized entity: "'.$tree[$i][1].'"';
				
				default:
					return 'Unknown entity type: "'.$tree[$i][0].'"';
			}
		}
		return true;
	}

	/**
	 * @param String $str string to be parsed into an expression tree
	 * @return mixed a variable depth array containing pairs of entity 
	 * types and values, or a string containing an error message
	 */
	public static function parseExpressionTree($str) {

		$tokens = self::tokenize($str);
		
		$tree = array();
		$nodePath = array();
		$error = false;
		
		for($i=0;$i<count($tokens);$i++) {
			switch($tokens[$i][0]) {
				case 'OPEN_BRACKET':
					$nodePath[] = self::addNode($tree,$nodePath,array('EXPRESSION',array()));	// add a new expression node, get its offset in the current branch, 
					$nodePath[] = 1;	// then move down to its 2nd element (1st element is the type, i.e. 'EXPRESSION')
					break;
				case 'CLOSE_BRACKET':
					if(count($nodePath) > 1) {
						$nodePath = array_slice($nodePath,0,-2);	// set node path to one level higher
						self::simplifyNode($tree,$nodePath);		// we're closing an expression node; check to see if its empty or only contains one thing
						
					} else {
						$error = 'unbalanced brackets';
					}
					break;
					
				case 'SPACE': break;
				default:
					self::addNode($tree,$nodePath,$tokens[$i]);
			}
		}
		
		if(count($nodePath) !== 0)
			$error = 'unbalanced brackets';
		
		if($error !== false)
			return 'ERROR: '.$error;
		else
			return $tree;
	}
}
?>
