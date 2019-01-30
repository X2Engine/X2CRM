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




/**
 * A Campaign represents a one time mailing to a list of contacts.
 *
 * When a campaign is created, a contact list must be specified. When a campaing is 'launched'
 * a duplicate list is created leaving the original unchanged. The duplicate 'campaign' list 
 * will keep track of which contacts were sent email, who opened the mail, and who unsubscribed.
 * A campaign is 'active' after it has been launched and ready to send mail. A campaign is 'complete'
 * when all applicable email has been sent. This is the model class for table "x2_campaigns".
 *
 * @package application.modules.marketing.models
 */
class CampaignAttachment extends CActiveRecord {
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName()	{ return 'x2_campaigns_attachments'; }

	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}

	public function relations() {
		return array(
			'compaign'=>array(self::BELONGS_TO, 'Campaign', 'campaign'),
			'mediaFile'=>array(self::BELONGS_TO, 'Media', 'media'),
		);
	}

	//Similar to X2Model but we had a special case with 'marketing'
	// CampaignAttachment doesn't have any labels, any labels associated
	// with campaign attachments would go in Campaign
	public function attributeLabels() {
		return array();
	}
		
	//Similar to X2Model but we had a special case with 'marketing'
	public function getAttributeLabel($attribute) {
	
		return array();
	}
}
