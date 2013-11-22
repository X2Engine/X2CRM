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
 * Logic attributes/methods for lead distribution.
 * 
 * LeadRouting is a CBehavior that provides logic for simple or complex 
 * distribution of leads to users
 * @package X2CRM.components
 */
class LeadRoutingBehavior extends CBehavior {

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
		$type = $admin->leadDistribution;
		if ($type == "") {
			return "Anyone";
		} elseif ($type == "evenDistro") {
			return $this->evenDistro();
		} elseif ($type == "trueRoundRobin") {
			return $this->roundRobin();
		} elseif ($type == "customRoundRobin") {
			$arr = $_POST;
			// for new lead capture form:
			//     "Contacts" maps to an array of fields, check if this array exists and has fields, if so, set arr
			if(isset($arr['Contacts']) && is_array($arr['Contacts']) && count($arr['Contacts']) > 0)
				$arr = $arr['Contacts'];
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
		$online = $admin->onlineOnly;
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
		$rrId = $this->getRoundRobin();
        if(count($userList)>0){
            $i = $rrId % count($userList);
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
        $criteria=new CDbCriteria;
        $criteria->order="priority ASC";
		$rules = X2Model::model('LeadRouting')->findAll($criteria);
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
								$usernames[] = User::model()->findByPk($link->userId)->username;
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
					}elseif($rule->groupType == 0){
                        $users=$usernames;
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
