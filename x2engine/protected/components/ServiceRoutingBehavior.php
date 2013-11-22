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


class ServiceRoutingBehavior extends CBehavior {

	public function cleanUpSessions() {
		X2Model::model('Session')->deleteAll('lastUpdated < :cutoff', array(':cutoff'=>time() - Yii::app()->params->admin->timeout));
	}

	/**
	 * Picks the next asignee based on the routing type
	 * 
	 * @return string Username that should be assigned the next lead
	 */
	public function getNextAssignee() {
		$admin = &Yii::app()->params->admin;
		$type = $admin->serviceDistribution;
		if ($type == "") {
			return "Anyone";
		} elseif ($type == "evenDistro") {
			return $this->evenDistro();
		} elseif ($type == "trueRoundRobin") {
			return $this->roundRobin();
		} elseif($type=='singleUser') {
            $user=User::model()->findByPk($admin->srrId);
            if(isset($user)){
                return $user->username;
            }else{
                return "Anyone";
            }
        } elseif($type=='singleGroup') {
            $group=Groups::model()->findByPk($admin->sgrrId);
            if(isset($group)){
                return $group->id;
            }else{
                return "Anyone";
            }
        }
	}

	/**
	 * Picks the next asignee such that the resulting routing distribution 
	 * would be even.
	 * 
	 * @return mixed
	 */
	public function evenDistro() {
		$admin = &Yii::app()->params->admin;
		$online = $admin->serviceOnlineOnly;
		$this->cleanUpSessions();
		$usernames = array();
		$sessions = Session::getOnlineUsers();
		$users = X2Model::model('User')->findAll();
		foreach ($users as $user) {
			$usernames[] = $user->username;
		}

		if ($online == 1) {
			foreach ($usernames as $user) {
				if (in_array($user, $sessions))
					$users[] = $user;
			}
		}else {
			$users = $usernames;
		}

		$numbers = array();
		foreach ($users as $user) {
			if ($user != 'admin' && $user!='api') {
				$actions = X2Model::model('Actions')->findAllByAttributes(array('assignedTo' => $user, 'complete' => 'No'));
				if (isset($actions))
					$numbers[$user] = count($actions);
				else
					$numbers[$user] = 0;
			}
		}
		asort($numbers);
		reset($numbers);
		return key($numbers);
	}

	/**
	 * Picks the next assignee in a round-robin manner.
	 * 
	 * Users get a chance to be picked in this manner only if online. In the
	 * round-robin distribution of leads, the last person who was picked for
	 * a lead assignment is stored using {@link updateRoundRobin()}. If no 
	 * one is online, the lead will be assigned to "Anyone".
	 * @return mixed 
	 */
	public function roundRobin() {
		$admin = &Yii::app()->params->admin;
		$online = $admin->serviceOnlineOnly;
		$this->cleanUpSessions();
		$usernames = array();
		$sessions = Session::getOnlineUsers();
		$users = X2Model::model('User')->findAll();
		foreach ($users as $userRecord) {
			//exclude admin from candidates
			if ($userRecord->username != 'admin' && $userRecord->username!='api') $usernames[] = $userRecord->username;
		}
		if ($online == 1) {
			$userList = array();
			foreach ($usernames as $user) {
				if (in_array($user, $sessions))
					$userList[] = $user;
			}
		}else {
			$userList = $usernames;
		}
		$srrId = $this->getRoundRobin();
        if(count($userList)>0){
            $i = $srrId % count($userList);
            $this->updateRoundRobin();
            return $userList[$i];
        }else{
            return "Anyone";
        }
	}

	/**
	 * Returns the round-robin state
	 * @return integer
	 */
	public function getRoundRobin() {
		$admin = &Yii::app()->params->admin;
		$srrId = $admin->srrId;
		return $srrId;
	}

	/**
	 * Stores the round-robin state. 
	 */
	public function updateRoundRobin() {
		$admin = &Yii::app()->params->admin;
		$admin->srrId = $admin->srrId + 1;
		$admin->save();
	}


}
