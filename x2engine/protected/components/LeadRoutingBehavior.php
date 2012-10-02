<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

 
/**
 * Logic attributes/methods for lead distribution.
 * 
 * LeadRouting is a CBehavior that provides logic for simple or complex 
 * distribution of leads to users
 * @package X2CRM.components
 */
class LeadRoutingBehavior extends CBehavior {

	public function cleanUpSessions() {
		Session::model()->deleteAll('lastUpdated < :cutoff', array(':cutoff'=>time() - Yii::app()->params->admin->timeout));
	}

	/**
	 * Picks the next asignee based on the routing type
	 * 
	 * @return string Username that should be assigned the next lead
	 */
	public function getNextAssignee() {
		$admin = &Yii::app()->params->admin;
		$type = $admin->leadDistribution;
		if ($type == "") {
			return "Anyone";
		} elseif ($type == "evenDistro") {
			return $this->evenDistro();
		} elseif ($type == "trueRoundRobin") {
			return $this->roundRobin();
		} elseif ($type == "customRoundRobin") {
			$arr = $_POST;
			$users = $this->getRoutingRules($arr);
			if (!empty($users) && is_array($users) && count($users)>1) {
				$rrId = $users[count($users) - 1];
				unset($users[count($users) - 1]);
				$i = $rrId % count($users);
				return $users[$i];
			}else{
                return "Anyone";
            }
		}elseif($type=='singleUser'){
            $user=User::model()->findByPk($admin->rrId);
            if(isset($user)){
                return $user->username;
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
		$online = $admin->onlineOnly;
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
			if ($user != 'admin') {
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
		$online = $admin->onlineOnly;
		$this->cleanUpSessions();
		$usernames = array();
		$sessions = Session::getOnlineUsers();
		$users = CActiveRecord::model('User')->findAll();
		foreach ($users as $user) {
			//exclude admin from candidates
			if ($user->username != 'admin') $usernames[] = $user->username;
		}
		if ($online == 1) {
			$user = array();
			foreach ($usernames as $user) {
				if (in_array($user, $sessions))
					$users[] = $user;
			}
		}else {
			$users = $usernames;
		}
		$rrId = $this->getRoundRobin();
        if(count($users)>0){
            $i = $rrId % count($users);
            $this->updateRoundRobin();
            return $users[$i];
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
		$rrId = $admin->rrId;
		return $rrId;
	}

	/**
	 * Stores the round-robin state. 
	 */
	public function updateRoundRobin() {
		$admin = &Yii::app()->params->admin;
		$admin->rrId = $admin->rrId + 1;
		$admin->save();
	}

	/**
	 * Obtains lead routing rules.
	 * @param type $data
	 * @return type 
	 */
	public function getRoutingRules($data) {
		$admin = &Yii::app()->params->admin;
		$online = $admin->onlineOnly;
		$this->cleanUpSessions();
		$sessions = Session::getOnlineUsers();

		$rules = CActiveRecord::model('LeadRouting')->findAll("", array('order' => 'priority'));
		foreach ($rules as $rule) {
			$arr = LeadRouting::parseCriteria($rule->criteria);
			$flagArr = array();
			foreach ($arr as $criteria) {
				if (isset($data[$criteria['field']])) {
					$val = $data[$criteria['field']];
					$operator = $criteria['comparison'];
					$target = $criteria['value'];
					if ($operator != 'contains') {
						switch ($operator) {
							case '>':
								$flag = ($val >= $target);
								break;
							case '<':
								$flag = ($val <= $target);
								break;
							case '=':
								$flag = ($val == $target);
								break;
							case '!=':
								$flag = ($val != $target);
								break;
							default:
								$flag = false;
						}
					} else {
						$flag = preg_match("/$target/i", $val) != 0;
					}
					$flagArr[] = $flag;
				}
			}
			if (!in_array(false, $flagArr) && count($flagArr) > 0) {
				$users = $rule->users;
				$users = explode(", ", $users);
				if (is_null($rule->groupType)) {
					if ($online == 1)
						$users = array_intersect($users, $sessions);
				}else {
					$groups = $rule->users;
					$groups = explode(", ", $groups);
					$users = array();
					foreach ($groups as $group) {
						if ($rule->groupType == 0) {
							$links = GroupToUser::model()->findAllByAttributes(array('groupId' => $group));
							foreach ($links as $link) {
								$users[] = User::model()->findByPk($link->userId)->username;
							}
						} else {
							$users[] = $group;
						}
					}
					if ($online == 1 && $rule->groupType == 0) {
						foreach ($usernames as $user) {
							if (in_array($user, $sessions))
								$users[] = $user;
						}
					}
				}
				$users[] = $rule->rrId;
				$rule->rrId++;
				$rule->save();
				return $users;
			}
		}
	}
}
