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


class ServiceRoutingBehavior extends CBehavior {

	public function cleanUpSessions() {
		CActiveRecord::model('Session')->deleteAll('lastUpdated < :cutoff', array(':cutoff'=>time() - Yii::app()->params->admin->timeout));
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
		$users = CActiveRecord::model('User')->findAll();
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
				$actions = CActiveRecord::model('Actions')->findAllByAttributes(array('assignedTo' => $user, 'complete' => 'No'));
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
		$users = CActiveRecord::model('User')->findAll();
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
