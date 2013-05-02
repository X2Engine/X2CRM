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
 * X2FlowAction that creates a new action
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateAction extends X2FlowAction {
	public $title = 'Create Action';
	public $info = 'Creates a new action for the specified user.';
	
	public function paramRules() {
		$visOptions = array(
			1 => Yii::t('actions','Public'),
			0 => Yii::t('actions','Private'),
			2 => Yii::t('actions','User\'s Groups')
		);
		$priorityOptions = array(
			'Low' => Yii::t('actions','Low'),
			'Medium' => Yii::t('actions','Medium'),
			'High' => Yii::t('actions','High')
		);
		// $assignmentOptions = array('{assignedTo}'=>'{'.Yii::t('studio','Owner of Record').'}') + X2Model::getAssignmentOptions(false,true);	// '{assignedTo}', groups, no 'anyone'
		$assignmentOptions = X2Model::getAssignmentOptions(false,true);	// '{assignedTo}', groups, no 'anyone'
		
		return array(
			'title' => Yii::t('studio',$this->title),
			'options' => array(
				// array('name'=>'attributes'),
				array('name'=>'subject','label'=>Yii::t('actions','Subject'),'optional'=>1),
				array('name'=>'description','label'=>Yii::t('actions','Description'),'type'=>'text'),
				array('name'=>'assignedTo','label'=>Yii::t('actions','Assigned To'),'type'=>'dropdown','options'=>$assignmentOptions),
				array('name'=>'priority','label'=>Yii::t('actions','Priority'),'type'=>'dropdown','options'=>$priorityOptions),
				array('name'=>'visibility','label'=>Yii::t('actions','Visibility'),'type'=>'dropdown','options'=>$visOptions),
				// array('name'=>'reminder','label'=>Yii::t('actions','Remind Me'),'type'=>'checkbox','default'=>false),
			));
	}
	
	public function execute(&$params) {
		$options = $this->config['options'];
		
		$action = new Actions;
		
		$action->subject = $options['subject'];
		$action->actionDescription = $options['description'];
		$action->priority = $options['priority'];
		$action->visibility = $options['visibility'];
		// $action->
		
		if(isset($params['model']))
			$action->assignedTo = X2Flow::parseValue($options['assignedTo'],'assignment',$params['model']);
		
		// replaceVariables($str, &$model, $vars = array(), $encode = false)
		
		// if(isset($this->config['attributes']))
			// $this->setModelAttributes($action,$this->config['attributes']);
		
		return $action->save();
		
		
		
		// if($options['reminder']) {
			// $notif=new Notification;
			// $notif->modelType='Actions';
			// $notif->createdBy=Yii::app()->user->getName();
			// $notif->modelId=$model->id;
			// if($_POST['notificationUsers']=='me'){
				// $notif->user=Yii::app()->user->getName();
			// }else{
				// $notif->user=$model->assignedTo;
			// }
			// $notif->createDate=$model->dueDate-($_POST['notificationTime']*60);
			// $notif->type='action_reminder';
			// $notif->save();
			// if($_POST['notificationUsers']=='both' && Yii::app()->user->getName()!=$model->assignedTo){
				// $notif2=new Notification;
				// $notif2->modelType='Actions';
				// $notif2->createdBy=Yii::app()->user->getName();
				// $notif2->modelId=$model->id;
				// $notif2->user=Yii::app()->user->getName();
				// $notif2->createDate=$model->dueDate-($_POST['notificationTime']*60);
				// $notif2->type='action_reminder';
				// $notif2->save();
			// }
		// }
	}
}