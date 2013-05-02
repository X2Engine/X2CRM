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
 * X2FlowAction that creates a notification
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateNotif extends X2FlowAction {
	public $title = 'Create Popup Notification';
	// public $info = 'You can type a custom message, or X2CRM will automatically choose one based on the event that triggered this flow.';
	
	public function paramRules() {
		$notifTypes = array('auto'=>'Auto','custom'=>'Custom');
		$assignmentOptions = array('{assignedTo}'=>'{'.Yii::t('studio','Owner of Record').'}') + X2Model::getAssignmentOptions(false,false);	// '{assignedTo}', no groups, no 'anyone'
		
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'options' => array(
				array('name'=>'user','label'=>'User','type'=>'dropdown','options'=>$assignmentOptions),	// just users, no groups or 'anyone'
				// array('name'=>'type','label'=>'Type','type'=>'dropdown','options'=>$notifTypes),
				array('name'=>'text','label'=>'Message','optional'=>1),
			));
	}

	public function execute(&$params) {
		$options = &$this->config['options'];
		
		$notif = new Notification;
		$notif->user = $options['user'];
		$notif->createdBy = 'API';
		$notif->createDate = time();
		
		// if($options['type'] == 'auto') {
			// if(!isset($params['model']))
				// return false;
			// $notif->modelType = get_class($params['model']);
			// $notif->modelId = $params['model']->id;
			// $notif->type = $this->getNotifType();
		// } else {
			$notif->type = 'custom';
			$notif->text = $options['text'];
		// }
		return $notif->save();
	}
}