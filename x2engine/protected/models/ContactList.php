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

Yii::import('application.models.X2LinkableBehavior');

/**
 * Model for managing lists of contacts
 * @package X2CRM.models
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
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
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
