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

/**
 * A Campaign represents a one time mailing to a list of contacts.
 *
 * When a campaign is created, a contact list must be specified. When a campaing is 'launched'
 * a duplicate list is created leaving the original unchanged. The duplicate 'campaign' list 
 * will keep track of which contacts were sent email, who opened the mail, and who unsubscribed.
 * A campaign is 'active' after it has been launched and ready to send mail. A campaign is 'complete'
 * when all applicable email has been sent. This is the model class for table "x2_campaigns".
 *
 * @package X2CRM.modules.marketing.models
 */
class CampaignAttachment extends X2Model {
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName()	{ return 'x2_campaigns_attachments'; }

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/marketing'
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
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
