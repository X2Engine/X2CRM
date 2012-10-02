<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * @package X2CRM.modules.contacts.controllers 
 */
class MyContactsController extends ContactsController {

	public function actionView($id) {
		$contact = $this->loadModel($id);
		
		if(isset($this->portlets['TimeZone']))
			$this->portlets['TimeZone']['params']['model'] = &$contact;
		if(isset($this->portlets['GoogleMaps']))
			$this->portlets['GoogleMaps']['params']['location'] = $contact->cityAddress;

		if ($this->checkPermissions($contact,'view')) {
		
			if(isset($_COOKIE['vcr-list']))
				Yii::app()->user->setState('vcr-list',$_COOKIE['vcr-list']);
		
			if ($contact->dupeCheck != '1') {
				$criteria = new CDbCriteria();
				$criteria->compare('CONCAT(firstName," ",lastName)', $contact->firstName . " " . $contact->lastName, false, "OR");
				$criteria->compare('email', $contact->email, false, "OR");
				$criteria->compare('phone', $contact->phone, false, "OR");
				$criteria->compare('phone2', $contact->phone2, false, "OR");
				$criteria->compare('id', "<>" . $contact->id, false, "AND");
				$duplicates = Contacts::model()->findAll($criteria);
				if (count($duplicates) > 0) {
					$this->render('duplicateCheck', array(
						'newRecord' => $contact,
						'duplicates' => $duplicates,
						'ref' => 'view'
					));
				} else {
					User::addRecentItem('c', $id, Yii::app()->user->getId()); ////add contact to user's recent item list
					parent::view($contact, 'contacts');
				}
			} else {
				User::addRecentItem('c', $id, Yii::app()->user->getId()); ////add contact to user's recent item list
				parent::view($contact, 'contacts');
			}
		} else
			$this->redirect('index');
	}
}
