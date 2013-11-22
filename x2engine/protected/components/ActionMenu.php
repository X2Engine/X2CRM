<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * Widget class for rendering a user's actions widget.
 * 
 * Renders the actions widget with action statistics, i.e. how many actions total,
 * how many actions complete, how many incomplete, titled "My Actions"
 * @package X2CRM.components 
 */
class ActionMenu extends X2Widget {

	public $visibility;
	public function init() {
		parent::init();
	}

	/**
	 * Creates the widget. 
	 */
	public function run() {
		$total = Actions::model()->countByAttributes(array('assignedTo' => Yii::app()->user->getName()),'type="" OR type IS NULL');

		$unfinished = Actions::model()->countByAttributes(array('assignedTo' => Yii::app()->user->getName(), 'complete' => 'No'),'type="" OR type IS NULL');

		$overdue = Actions::model()->countByAttributes(array('assignedTo' => Yii::app()->user->getName(), 'complete' => 'No'),'dueDate < '.time().' AND type="" OR type IS NULL');

		$complete = Actions::model()->countByAttributes(array('completedBy' => Yii::app()->user->getName(), 'complete' => 'Yes'),'type="" OR type IS NULL');


		$this->render('actionMenu', array(
			'total' => $total,
			'unfinished' => $unfinished,
			'overdue' => $overdue,
			'complete' => $complete,
		));
	}

}

?>