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
 * This is the model class for table "x2_flow_items".
 *
 * The followings are the available columns in table 'x2_flow_items':
 * @property integer $id
 * @property integer $flowId
 * @property integer $active
 * @property string $type
 * @property integer $parent
 *
 * The followings are the available model relations:
 * @property Flows $flow
 * @property FlowParams[] $flowParams
 */
class X2FlowItem extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return X2FlowItem the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_flow_items';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('flowId, type', 'required'),
			array('flowId, parent', 'numerical', 'integerOnly'=>true),
			array('active', 'boolean'),
			array('type, modelClass', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, flowId, active, type, parent', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'flow' => array(self::BELONGS_TO, 'X2Flow', 'flowId'),
			'actionParams' => array(self::HAS_MANY, 'X2FlowParam', 'itemId', 'condition'=>'actionParams.type="param"'),
			'criteriaParams' => array(self::HAS_MANY, 'X2FlowParam', 'itemId', 'condition'=>'criteriaParams.type!="param"'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'flowId' => 'Flow',
			'active' => 'Active',
			'type' => 'Type',
			'modelClass' => 'Model Class',
			'parent' => 'Parent',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('flowId',$this->flowId);
		$criteria->compare('active',$this->active);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('modelClass',$this->modelClass,true);
		$criteria->compare('parent',$this->parent);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	
	public static $triggers = array(
		'record_created' => '',
		'record_viewed' => '',
		'record_updated' => '',
		'record_deleted' => '',
		'record_field_changed' => '',

		'record_action_created' => '',
		'record_action_completed' => '',
		'record_action_uncompleted' => '',

		'action_overdue' => '',
		'record_inactive' => '',
		'timer' => '',
		'campaign_timer' => '',

		'record_tag_added' => '',
		'record_tag_removed' => '',

		'workflow_started' => '',
		'workflow_completed' => '',
		'workflow_stage_started' => '',
		'workflow_stage_completed' => '',
		'workflow_stage_reverted' => '',

		'action_completed' => '',
		'action_uncompleted' => '',

		'weblead' => '',

		'campaign_opened' => '',
		'campaign_clicked' => '',
		'campaign_unsubscribed' => '',

		'newsletter_opened' => '',
		'newsletter_clicked' => '',
		'newsletter_unsubscribed' => '',

		'campaign_webactivity' => '',
		'newsletter_webactivity' => '',

		'record_webactivity' => '',

		'user_login' => '',
		'user_logout' => '',
	);
	
	public static $actions = array(
		// 'record_email' => 'Email Record',
		// 'email' => 'Send Email',
		
		// 'record_create' => 'Create Record',
		// 'record_update' => 'Update Record',
		// 'record_delete' => 'Delete Record',
		// 'record_reassign' => 'Reassign Record',
		// 'record_comment' => 'Add Comment',
		
		// 'create_action' => 'Create Action',
		// 'complete_action' => 'Complete Action',
		// 'uncomplete_action' => 'Uncomplete Action',
		
		// 'event_create' => 'Post to Activity Feed',
		// 'action_create' => 'Create Action',
		// 'notif_create' => 'Create Popup Notification',
		// 'reminder' => 'Create Action Reminder',
		
		// 'workflow_start' => 'Start Workflow Stage',
		// 'workflow_complete' => 'Complete Workflow Stage',
		// 'workflow_revert' => 'Revert Workflow Stage',
		
		// 'campaign_launch' => 'Launch Campaign',
		
		// 'record_tags_add' => 'Add Tags',
		// 'record_tags_remove' => 'Remove Tags',
		
		// 'record_list_add' => 'Add to List',
		// 'record_list_remove' => 'Remove from List',
		
		// 'API_call' => 'Remote API Call',
	);
	
	public static function getActionParamRules($type) {
		// if(!isset(self::$actions[$type]))
			// return false;
		
		// $title = self::$actions[$type];
		
		$flowAction = X2FlowAction::create($type);
		if($flowAction !== null)
			return $flowAction->paramRules();
		else
			return false;
		
		
	// }
		return;
		
		switch($type) {
			case 'record_email':
				return array('title'=>$title,'info'=>'Send a template or custom email to this record\'s email address. Uses the assignee\'s email unless specified.','fields'=>array(
					'model'			=>	array(),
					'from'			=>	array('label'=>'From:',				'optional'=>1,'type'=>'email'),
					'template'		=>	array('label'=>'Template',			'dropdown'=>Docs::getEmailTemplates()),
					'subject'		=>	array('label'=>'Subject',			'optional'=>1),
					'cc'			=>	array('label'=>'CC:',				'optional'=>1,'type'=>'email'),
					'bcc'			=>	array('label'=>'BCC:',				'optional'=>1,'type'=>'email'),
					'body'			=>	array('label'=>'Message',			'optional'=>1,'type'=>'richtext'),
					//'time'		=> array('timestamp'),
				));
			case 'email':
				return array('title' => $title, 'info' => 'Send a template or custom email.', 'fields' => array(
					'to'			=>	array('label'=>'To:',				'email'),
					'from'			=>	array('label'=>'From:',				'email'),
					'template'		=>	array('label'=>'Template',			'email','dropdown'=>Docs::getEmailTemplates()),
					'subject'		=>	array('label'=>'Subject'),
					'cc'			=>	array('label'=>'CC:',				'optional'=>1,'email'),
					'bcc'			=>	array('label'=>'BCC:',				'optional'=>1,'email'),
					'body'			=>	array('label'=>'Message',			'optional'=>1,'richtext'),
					//'time'		=> array('timestamp'),
				));
				
			case 'record_create':
				$modelTypes = Yii::app()->db->createCommand()
					->selectDistinct('modelName')
					->from('x2_fields')
					->where('modelName!="Calendar"')
					->queryColumn();
				$modelTypes = array_combine($modelTypes,$modelTypes);
				
				return array('title'=>$title,'fields'=>array(
					'modelType'		=>	array('label'=>'Record Type',			'type'=>'dropdown','options'=>$modelTypes),
					'attributes'	=>	array(),
				));
			
			case 'record_update':
				return array('title' => $title, 'info' => 'Change one or more fields on an existing record.', 'fields' => array(
					'model'			=>	array(),
					'attributes'	=>	array(),
				));
				
			case 'record_delete':
				return array('title' => $title, 'info' => 'Delete this record.', 'fields' => array(
					'model'			=>	array(),
				));
				
			case 'record_reassign':
				$leadRoutingModes = array(
					''=>'Free For All',
					'roundRobin'=>'Round Robin',
					'roundRobin'=>'Sequential Distribution',
					'singleUser'=>'Simple User'
				);
				return array('title' => $title, 'info' => 'Assign the record to a user or group, or automatically using lead routing.', 'fields' => array(
					'model'			=>	array(),
					'routeMode'		=>	array('label'=>'Routing Method',	'type'=>'dropdown','options'=>$leadRoutingModes),
					'user'			=>	array('label'=>'User',				'type'=>'assignment','multiple'=>1),
					'onlineOnly'	=>	array('label'=>'Online Only?',		'optional'=>1,'type'=>'boolean','default'=>false),
				));
				
			case 'record_comment':
				return array('title' => $title, 'fields' => array(
					'model'			=>	array(),
					'comment'		=>	array('label'=>'Comment',			'text'),
				));
				
			case 'record_add_tags':
				return array('title' => $title, 'info' => 'Enter a commna-separated list of tags to add to the record.', 'fields' => array(
					'model'			=>	array(),
					'tags'			=>	array('label'=>'Tags'),
				));
				
			case 'record_remove_tags':
				return array('title' => $title, 'info' => 'Enter a commna-separated list of tags to remove from the record.', 'fields' => array(
					'model'			=>	array(),
					'tags'			=>	array('label'=>'Tags'),
				));
				
			case 'record_add_to_list':
				return array('title' => $title, 'info' => 'Add this record to a static list.', 'fields' => array(
					'model'			=>	array(),
					'listId'		=>	array('label'=>'List',				'type'=>'lookup','linkType'=>'X2List'),
				));
				
			case 'record_remove_from_list':
				return array( 'title' => $title, 'info' => 'Remove this record from a static list.', 'fields' => array(
					'model'			=>	array(),
					'listId'		=>	array('label'=>'List',				'type'=>'lookup','linkType'=>'X2List'),
				));
				
			case 'record_action_create':
				return array('title' => $title, 'fields' => array(
					'model'			=>	array(),
					'attributes'	=>	array(),
				));
				
			case 'record_action_complete':
				return array(
					'title'			=>	$title,
				);
				
			case 'record_action_uncomplete':
				return array(
					'title'			=>	$title,
				);
				
			case 'event_create':
				$eventTypes = array('auto'=>Yii::t('app','Auto')) + Dropdowns::getItems(113,'app');
				return array('title'=>$title,'fields' => array(
					'type'			=>	array('label'=>'Post Type',				'type'=>'dropdown','options'=>$eventTypes),
					'text'			=>	array('label'=>'Text',					'type'=>'text'),
					'user'			=>	array('label'=>'User',					'type'=>'assignment'),
					'createNotif'	=>	array('label'=>'Create Notification?',	'type'=>'boolean','default'=>true),
				));
				
			case 'action_create':
				return array('title' => $title, 'fields' => array(
					'attributes'	=>	array(),
					'model'			=>	array(),
				));
				
			case 'notif_create':
				$notifTypes = array('auto'=>'Auto','custom'=>'Custom');
				return array('title' => $title, 'fields' => array(
					'user'			=>	array('label'=>'User',				'type'=>'assignment'),
					'text'			=>	array('label'=>'Message',			'optional'=>1),
					'type'			=>	array('label'=>'Type',				'type'=>'dropdown','options'=>$notifTypes),
				));
				
			case 'reminder':
				return array('title' => $title, 'info' => 'At the specified time, this user will receive a reminder on their activity feed.', 'fields' => array(
					'user'			=>	array('label'=>'User',				'type'=>'assignment'),
					'text'			=>	array('label'=>'Message',			'type'=>'text'),
					'timestamp'		=>	array('label'=>'User',				'type'=>'timestamp'),
				));
				
			case 'workflow_start':
			case 'workflow_complete':
			case 'workflow_revert':
				$workflows = Workflow::getList(false);	// no "none" options
				$workflowIds = array_keys($workflows);
				$stages = count($workflowIds)? Workflow::getStages($workflowIds[0]) : array('---');
				return array('title' => $title, 'fields' => array(
					'model'			=>	array(),
					'workflowId'	=>	array('label'=>'Workflow',			'type'=>'dropdown','options'=>$workflows),
					'stageNumber'	=>	array('label'=>'Stage',				'type'=>'dropdown','options'=>$stages),
				));
				
			case 'campaign_launch':
				return array('title' => $title, 'info' => 'Immediately begin emailing contacts on the selected campaign', 'fields' => array(
					'campaignId'	=>	array('label'=>'Campaign',			'type'=>'lookup','linkType'=>'Campaign'),
				));
				
			case 'API_call':
				$info = 'Call a remote API by requesting the specified URL. '
						.'You can specify the request type and any variables to be passed with the request. '
						.'To improve performance, he request will be put into a job queue unless you need it to execute immediately.';
				$httpVerbs = array(
					'get'=>'GET',
					'post'=>'POST',
					'put'=>'PUT',
					'delete'=>'DELETE'
				);
				return array('title' => $title, 'info' => $info,'fields' => array(
					'url'			=>	array('label'=>'URL'),
					'method'		=>	array('label'=>'Method',			'type'=>'dropdown','options'=>$httpVerbs),
					'attributes'	=>	array('optional'=>1),
					'immediate'		=>	array('label'=>'Call immediately?',	'type'=>'boolean','default'=>true),
				));
			default:
				return false;
		}
	}

	public function execute() {
	
	
		// $rules = self::getParamRules($this->type);
		
	
		// if(isset($rules[$this->type]))
			// $rules = $rules[$this->type];
		// else
			// throw new Exception('Unrecognized automation action: '.$this->type);	// make sure the action type is valid
		
		
		// foreach($rules as $key => $val) {
		// }
		
		// $requiredParams = isset($rules['required'])? preg_split('/[\s,]+/',$rules['required'],null,PREG_SPLIT_NO_EMPTY) : array();
		// $optionalParams = isset($rules['optional'])? preg_split('/[\s,]+/',$rules['optional'],null,PREG_SPLIT_NO_EMPTY) : array();
		// $multiParams = isset($rules['multivalue'])? preg_split('/[\s,]+/',$rules['multivalue'],null,PREG_SPLIT_NO_EMPTY) : array();
		
		// $params = array();
		
		// loop through this item's params and parse the values into $params
		// foreach($this->actionParams as &$flowParam) {
			// if(in_array($flowParam,$multiParams)) {
				// if(!isset($params[$flowParam->variable]))	// if its a multivalue param, make it an array
					// $params[$flowParam->variable] = array();
				// $params[$flowParam->variable][] = $flowParam->parseValue();
			// } else {
				// $params[$flowParam->variable][] = $flowParam->parseValue();
			// }
		// }
		
		// if(isset($params['model']) && !is_object($params['model']) || !($params['model'] instanceof X2Model))
			// throw new Exception('Invalid model parameter');
		
		// foreach($requiredParams as $param) {	// make sure all the required params have been provided
			// if(!isset($params[$param]))
				// return false;
		// }
		
		// switch($this->type) {
			// case 'update_record':
				// if(!is_subclass_of($params['modelType'],'X2Model'))	// make sure this is a valid model type
					// return false;
					
				// $model = new $params['modelType'];
				// return $this->setModelAttributes($model,$params) && $model->save();
		
			// case 'update_record':
				// for($i=0;$i<count($params['attributes']); $i++) {	// loop through attributes and set them in the model
					// if(!$params['model']->hasAttribute($params['attributes'][$i]))	// fail if the attribute doesn't exist
						// return false;
					// $params['model']->setAttribute($params['attributes'][$i],$params['values'][$i]);
				// }
				// return $params['model']->save();
				
			// case 'reassign_record':
				// if(CActiveRecord::model('User')->exists('username=?',array($params['user']))) {	// make sure the user exists
					// $params['model']->assignedTo = $params['user'];
					// return $params['model']->save();
				// }
				// return false;
				
			// case 'email':
				// if(isset($params['model']))
				
				
				
				
			// case 'campaign_launch':
				
				
			// case 'create_event':
			// case 'create_action':
				// $action = new Actions;
				// return $this->setModelAttributes($action,$params) && $model->save();
			
			// case 'create_notif':
			
			// case 'workflow_start':
			// case 'workflow_complete':
			// case 'workflow_revert':
				
				
				
			// case 'add_tags':
				// $tags = Tags::parseTags($params['tags']);
				// return $model->addTags($tags);
				
			// case 'remove_tags':
				// $tags = Tags::parseTags($params['tags']);
				// return $model->removeTags($tags);
				
			// case 'list_add':
				// return (null !== ($list = CActiveRecord::model('X2List')->findByPk($params['listId'])) && $list->modelName === get_class($model))?
					// $list->addIds($model->id) : false;
				
			// case 'list_remove':
				// return (null !== ($list = CActiveRecord::model('X2List')->findByPk($params['listId'])) && $list->modelName === get_class($model))?
					// $list->removeIds($model->id) : false;
				
			// case 'API_call':
			// optional:
				// 'method'
				// 'post_var'
				// 'value'

			// default:
		// }
	}
}