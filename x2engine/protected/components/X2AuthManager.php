<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * X2AuthManager class file.
 */
class X2AuthManager extends CDbAuthManager {

	/**
	 * Performs access check for the specified user.
	 * @param string $itemName the name of the operation that need access check
	 * @param mixed $userId the user ID. This should can be either an integer and a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($itemName,$userId,$params=array()) {
		$assignments=$this->getAuthAssignments($userId);
		return $this->checkAccessRecursive($itemName,$params,$assignments);
	}

	/**
	 * Performs access check for the specified user.
	 * This method is internally called by {@link checkAccess}.
	 * @param string $itemName the name of the operation that need access check
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @param array $assignments the assignments to the specified user
	 * @param boolean $accessLevel access level of this item's child (previous iteration, for recursive calls only)
	 * @return boolean whether the operations can be performed by the user.
	 */
	protected function checkAccessRecursive($itemName,$params,$assignments,$accessLevel=null) {
		
		if(($item=$this->getAuthItem($itemName))===null)
			return false;
		Yii::trace('Checking permission "'.$item->getName().'"','system.web.auth.CDbAuthManager');
		if($this->executeBizRule($item->getBizRule(),$params,$item->getData())) {
			if(in_array($itemName,$this->defaultRoles))
				return true;
			if(isset($assignments[$itemName])) {
				$assignment = $assignments[$itemName];
				if($this->executeBizRule($assignment->getBizRule(),$params,$assignment->getData()))
					return true;
			}
			
			// this item isn't directly assigned to any of the specified roles,
			// so check the parents (starting with the one granting the highest accessLevel) 
			$parents = $this->db->createCommand()
				->select('parent,accessLevel')
				->from($this->itemChildTable)
				->where('child=:name', array(':name'=>$itemName))
				->order('accessLevel DESC')
				->queryAll(false);
				
			
			
				
			foreach($parents as $parent) {
				
				
				$parentAccess = $this->checkAccessRecursive($parent['parent'],$params,$assignments,$parent['accessLevel']);
				
				if($parentAccess)
					return $parentAccess;
			}
		}
		return false;
	}

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the item is added successfully
	 * @throws CException if either parent or child doesn't exist or if a loop has been detected.
	 */
	public function addItemChild($itemName,$childName)
	{
		if($itemName===$childName)
			throw new CException(Yii::t('yii','Cannot add "{name}" as a child of itself.',
					array('{name}'=>$itemName)));

		$rows=$this->db->createCommand()
			->select()
			->from($this->itemTable)
			->where('name=:name1 OR name=:name2', array(
				':name1'=>$itemName,
				':name2'=>$childName
			))
			->queryAll();

		if(count($rows)==2)
		{
			if($rows[0]['name']===$itemName)
			{
				$parentType=$rows[0]['type'];
				$childType=$rows[1]['type'];
			}
			else
			{
				$childType=$rows[0]['type'];
				$parentType=$rows[1]['type'];
			}
			$this->checkItemChildType($parentType,$childType);
			if($this->detectLoop($itemName,$childName))
				throw new CException(Yii::t('yii','Cannot add "{child}" as a child of "{name}". A loop has been detected.',
					array('{child}'=>$childName,'{name}'=>$itemName)));

			$this->db->createCommand()
				->insert($this->itemChildTable, array(
					'parent'=>$itemName,
					'child'=>$childName,
				));

			return true;
		}
		else
			throw new CException(Yii::t('yii','Either "{parent}" or "{child}" does not exist.',array('{child}'=>$childName,'{parent}'=>$itemName)));
	}

	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName,$childName)
	{
		return $this->db->createCommand()
			->delete($this->itemChildTable, 'parent=:parent AND child=:child', array(
				':parent'=>$itemName,
				':child'=>$childName
			)) > 0;
	}

	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName,$childName)
	{
		return $this->db->createCommand()
			->select('parent')
			->from($this->itemChildTable)
			->where('parent=:parent AND child=:child', array(
				':parent'=>$itemName,
				':child'=>$childName))
			->queryScalar() !== false;
	}

	/**
	 * Returns the children of the specified item.
	 * @param mixed $names the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return array all child items of the parent
	 */
	public function getItemChildren($names)
	{
		if(is_string($names))
			$condition='parent='.$this->db->quoteValue($names);
		else if(is_array($names) && $names!==array())
		{
			foreach($names as &$name)
				$name=$this->db->quoteValue($name);
			$condition='parent IN ('.implode(', ',$names).')';
		}

		$rows=$this->db->createCommand()
			->select('name, type, description, bizrule, data')
			->from(array(
				$this->itemTable,
				$this->itemChildTable
			))
			->where($condition.' AND name=child')
			->queryAll();

		$children=array();
		foreach($rows as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$children[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $children;
	}

	/**
	 * Assigns an authorization item to a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @param string $bizRule the business rule to be executed when {@link checkAccess} is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return CAuthAssignment the authorization assignment information.
	 * @throws CException if the item does not exist or if the item has already been assigned to the user
	 */
	public function assign($itemName,$userId,$bizRule=null,$data=null)
	{
		if($this->usingSqlite() && $this->getAuthItem($itemName)===null)
			throw new CException(Yii::t('yii','The item "{name}" does not exist.',array('{name}'=>$itemName)));

		$this->db->createCommand()
			->insert($this->assignmentTable, array(
				'itemname'=>$itemName,
				'userid'=>$userId,
				'bizrule'=>$bizRule,
				'data'=>serialize($data)
			));
		return new CAuthAssignment($this,$itemName,$userId,$bizRule,$data);
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether removal is successful
	 */
	public function revoke($itemName,$userId)
	{
		return $this->db->createCommand()
			->delete($this->assignmentTable, 'itemname=:itemname AND userid=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId
			)) > 0;
	}

	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($itemName,$userId)
	{
		return $this->db->createCommand()
			->select('itemname')
			->from($this->assignmentTable)
			->where('itemname=:itemname AND userid=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId))
			->queryScalar() !== false;
	}

	/**
	 * Returns the item assignment information.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return CAuthAssignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAuthAssignment($itemName,$userId)
	{
		$row=$this->db->createCommand()
			->select()
			->from($this->assignmentTable)
			->where('itemname=:itemname AND userid=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId))
			->queryRow();
		if($row!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
		}
		else
			return null;
	}

	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return array the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAuthAssignments($userId)
	{
		$rows=$this->db->createCommand()
			->select()
			->from($this->assignmentTable)
			->where('userid=:userid', array(':userid'=>$userId))
			->queryAll();
		$assignments=array();
		foreach($rows as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$assignments[$row['itemname']]=new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
		}
		return $assignments;
	}

	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment $assignment the assignment that has been changed.
	 */
	public function saveAuthAssignment($assignment)
	{
		$this->db->createCommand()
			->update($this->assignmentTable, array(
				'bizrule'=>$assignment->getBizRule(),
				'data'=>serialize($assignment->getData()),
			), 'itemname=:itemname AND userid=:userid', array(
				'itemname'=>$assignment->getItemName(),
				'userid'=>$assignment->getUserId()
			));
	}

	/**
	 * Returns the authorization items of the specific type and user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @return array the authorization items of the specific type.
	 */
	public function getAuthItems($type=null,$userId=null)
	{
		if($type===null && $userId===null)
		{
			$command=$this->db->createCommand()
				->select()
				->from($this->itemTable);
		}
		else if($userId===null)
		{
			$command=$this->db->createCommand()
				->select()
				->from($this->itemTable)
				->where('type=:type', array(':type'=>$type));
		}
		else if($type===null)
		{
			$command=$this->db->createCommand()
				->select('name,type,description,t1.bizrule,t1.data')
				->from(array(
					$this->itemTable.' t1',
					$this->assignmentTable.' t2'
				))
				->where('name=itemname AND userid=:userid', array(':userid'=>$userId));
		}
		else
		{
			$command=$this->db->createCommand()
				->select('name,type,description,t1.bizrule,t1.data')
				->from(array(
					$this->itemTable.' t1',
					$this->assignmentTable.' t2'
				))
				->where('name=itemname AND type=:type AND userid=:userid', array(
					':type'=>$type,
					':userid'=>$userId
				));
		}
		$items=array();
		foreach($command->queryAll() as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$items[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $items;
	}

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string $name the item name. This must be a unique identifier.
	 * @param integer $type the item type (0: operation, 1: task, 2: role).
	 * @param string $description description of the item
	 * @param string $bizRule business rule associated with the item. This is a piece of
	 * PHP code that will be executed when {@link checkAccess} is called for the item.
	 * @param mixed $data additional data associated with the item.
	 * @return CAuthItem the authorization item
	 * @throws CException if an item with the same name already exists
	 */
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null)
	{
		$this->db->createCommand()
			->insert($this->itemTable, array(
				'name'=>$name,
				'type'=>$type,
				'description'=>$description,
				'bizrule'=>$bizRule,
				'data'=>serialize($data)
			));
		return new CAuthItem($this,$name,$type,$description,$bizRule,$data);
	}

	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeAuthItem($name)
	{
		if($this->usingSqlite())
		{
			$this->db->createCommand()
				->delete($this->itemChildTable, 'parent=:name1 OR child=:name2', array(
					':name1'=>$name,
					':name2'=>$name
			));
			$this->db->createCommand()
				->delete($this->assignmentTable, 'itemname=:name', array(
					':name'=>$name,
			));
		}

		return $this->db->createCommand()
			->delete($this->itemTable, 'name=:name', array(
				':name'=>$name
			)) > 0;
	}

	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return CAuthItem the authorization item. Null if the item cannot be found.
	 */
	public function getAuthItem($name)
	{
		$row=$this->db->createCommand()
			->select()
			->from($this->itemTable)
			->where('name=:name', array(':name'=>$name))
			->queryRow();

		if($row!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		else
			return null;
	}

	/**
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	public function saveAuthItem($item,$oldName=null)
	{
		if($this->usingSqlite() && $oldName!==null && $item->getName()!==$oldName)
		{
			$this->db->createCommand()
				->update($this->itemChildTable, array(
					'parent'=>$item->getName(),
				), 'parent=:whereName', array(
					':whereName'=>$oldName,
				));
			$this->db->createCommand()
				->update($this->itemChildTable, array(
					'child'=>$item->getName(),
				), 'child=:whereName', array(
					':whereName'=>$oldName,
				));
			$this->db->createCommand()
				->update($this->assignmentTable, array(
					'itemname'=>$item->getName(),
				), 'itemname=:whereName', array(
					':whereName'=>$oldName,
				));
		}

		$this->db->createCommand()
			->update($this->itemTable, array(
				'name'=>$item->getName(),
				'type'=>$item->getType(),
				'description'=>$item->getDescription(),
				'bizrule'=>$item->getBizRule(),
				'data'=>serialize($item->getData()),
			), 'name=:whereName', array(
				':whereName'=>$oldName===null?$item->getName():$oldName,
			));
	}

	/**
	 * Saves the authorization data to persistent storage.
	 */
	public function save()
	{
	}

	/**
	 * Removes all authorization data.
	 */
	public function clearAll()
	{
		$this->clearAuthAssignments();
		$this->db->createCommand()->delete($this->itemChildTable);
		$this->db->createCommand()->delete($this->itemTable);
	}

	/**
	 * Removes all authorization assignments.
	 */
	public function clearAuthAssignments()
	{
		$this->db->createCommand()->delete($this->assignmentTable);
	}

	/**
	 * Checks whether there is a loop in the authorization item hierarchy.
	 * @param string $itemName parent item name
	 * @param string $childName the name of the child item that is to be added to the hierarchy
	 * @return boolean whether a loop exists
	 */
	protected function detectLoop($itemName,$childName)
	{
		if($childName===$itemName)
			return true;
		foreach($this->getItemChildren($childName) as $child)
		{
			if($this->detectLoop($itemName,$child->getName()))
				return true;
		}
		return false;
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	protected function getDbConnection()
	{
		if($this->db!==null)
			return $this->db;
		else if(($this->db=Yii::app()->getComponent($this->connectionID)) instanceof CDbConnection)
			return $this->db;
		else
			throw new CException(Yii::t('yii','CDbAuthManager.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
				array('{id}'=>$this->connectionID)));
	}
}
