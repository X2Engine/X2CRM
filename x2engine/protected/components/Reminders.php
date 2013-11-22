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
 * Class for rendering the "reminders" widget
 * 
 * @package X2CRM.components 
 */
class Reminders extends X2Widget {
	public function init() {
		parent::init();
	}

	public function run() {
		$today = array();
		$tomorrow = array();
		$nextDay = array();
		$name = Yii::app()->user->getName();
		$tD = mktime(0,0,0,date("m"),date("d"),date("Y"));
		$tM = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
		$nD = mktime(0,0,0,date("m"),date("d")+2,date("Y"));
		$query = "SELECT * FROM x2_actions WHERE assignedTo = '".$name."' OR 'Anyone'";
		$command = Yii::app()->db->createCommand($query);
		$result = $command->queryAll();
		foreach ($result as $row){
			$dueDate = $row['dueDate'];
			if ($row['dueDate'] == $tD){$today[] = $row;}
			else if($row['dueDate'] == $tM){$tomorrow[] = $row;}
			else if($row['dueDate'] == $nD){$nextDay[] = $row;}
		}
		$this->render('reminders',array(
			'tD' => $tD,
			'today'=>$today,
			'tomorrow'=>$tomorrow,
			'nextDay'=>$nextDay,
		));
	}
}
?>
