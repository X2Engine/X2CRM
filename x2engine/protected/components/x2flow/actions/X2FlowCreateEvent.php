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
 * X2FlowAction that creates an event
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateEvent extends X2FlowAction {
	public $title = 'Post to Activity Feed';
	public $info = 'Creates an activity event.'; // You can write your own message, or X2CRM will automatically choose one based on what triggered this flow.';
	
	public function paramRules() {
		// $eventTypes = array('auto'=>Yii::t('app','Auto')) + Dropdowns::getItems(113,'app');
		$eventTypes = Dropdowns::getItems(113,'app');
		
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'options' => array(
				array('name'=>'type','label'=>'Post Type','type'=>'dropdown','options'=>$eventTypes),
				array('name'=>'text','label'=>'Text','type'=>'text'),
				// array('name'=>'user','label'=>'User','type'=>'dropdown','options'=>array(''=>'----------')+X2Model::getAssignmentOptions(false,false)),
				array('name'=>'createNotif','label'=>'Create Notification?','type'=>'boolean','defaultVal'=>true),
			));
	}

	public function execute(&$params) {
		$options = &$this->config['options'];
		
		$event = new Events;
		$notif = new Notification;
		
		// $event->user = $this->parseOption('user'];
		
		$type = $this->parseOption('type',$params);
		
		if($type === 'auto') {
			if(!isset($params['model']))
				return false;
			$notif->modelType = get_class($params['model']);
			$notif->modelId = $params['model']->id;
			$notif->type = $this->getNotifType();
			
			$event->associationType = get_class($params['model']);
			$event->associationId = $params['model']->id;
			$event->type = $this->getEventType();
			$event->visibility = $params['model']->visibility;
			// $event->user = $this->parseOption('user',$params);
		} else {
			$text = $this->parseOption('text',$params);
			
			$notif->type = 'custom';
			$notif->text = $text;
			
			$event->type = 'feed';
			$event->subtype = $type;
			$event->text = $text;
			$event->user = 'admin';
		}
		if(!$this->parseOption('createNotif',$params))
			$notif->save();
		$event->save();
	}
}