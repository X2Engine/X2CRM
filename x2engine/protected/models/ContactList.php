<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




Yii::import('application.models.LinkableBehavior');

/**
 * Model for managing lists of contacts
 * @package application.models
 */
class ContactList extends X2List {

	public static $modelName = 'Contacts';
	public static $linkRoute = '/contacts/contacts/list';

	/**
	 * Behaviors for the model.
	 * @return array 
	 */
	public function behaviors() {
		return array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'baseRoute'=>'/contacts/contacts',
				'viewRoute'=>'/contacts/contacts/list',
				'autoCompleteSource'=>'/contacts/contacts/getLists'
			)
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return ContactList the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Returns the route for viewing contact lists.
	 * @param integer $id
	 * @return array
	 */
	public static function getRoute($id) {
		if($id=='all')
			return array('/contacts/contacts/index');
		else if (empty($id) || $id=='my')
			return array('/contacts/contacts/viewMy');
		else
			return array('/contacts/contacts/list','id'=>$id);
	}
	
	/**
	 * Creates a link (or displays the name, if the ID is not available) of 
	 * the contact list.
	 * @return string 
	 */
	public function createLink() {
		if(isset($this->id))
			return CHtml::link($this->name,array($this->getDefaultRoute(),'id'=>$this->id));
		else
			return $this->name;
	}
}
