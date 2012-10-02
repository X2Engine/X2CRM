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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * This is the model class for table "x2_admin".
 * @package X2CRM.models
 */
class Admin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Admin the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_admin';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('emailFromName, emailFromAddr', 'required'),
			array('timeout, chatPollTime, ignoreUpdates, rrId, onlineOnly, emailBatchSize, emailInterval, emailPort, installDate, updateDate, updateInterval, workflowBackdateWindow, workflowBackdateRange', 'numerical', 'integerOnly'=>true),
			// accounts, sales, 
			array('chatPollTime', 'numerical', 'max'=>10000, 'min'=>100),
			array('currency', 'length', 'max'=>3),
			array('emailUseAuth, emailUseSignature', 'length', 'max'=>10),
			array('emailType, emailSecurity', 'length', 'max'=>20),
			array('webLeadEmail, leadDistribution, emailFromName, emailFromAddr, emailHost, emailUser, emailPass', 'length', 'max'=>255),
			// array('emailSignature', 'length', 'max'=>512),
			array('emailSignature', 'length', 'max'=>512),
			array('quoteStrictLock, workflowBackdateReassignment', 'boolean'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			// array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
		);
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			// 'accounts' => Yii::t('admin','Accounts'),
			// 'sales' => Yii::t('admin','Opportunities'),
			'timeout' => Yii::t('admin','Session Timeout'),
			'webLeadEmail' => Yii::t('admin','Web Lead Email'),
			'currency' => Yii::t('admin','Currency'),
			'chatPollTime' => Yii::t('admin','Chat Poll Time'),
			'ignoreUpdates' => Yii::t('admin','Ignore Updates'),
			'rrId' => Yii::t('admin','Rr'),
			'leadDistribution' => Yii::t('admin','Lead Distribution'),
			'onlineOnly' => Yii::t('admin','Online Only'),
			'emailFromName' => Yii::t('admin','Sender Name'),
			'emailFromAddr' => Yii::t('admin','Sender Email Address'),
			'emailBatchSize' => Yii::t('admin','Batch Size'),
			'emailInterval' => Yii::t('admin','Interval (Minutes)'),
			'emailUseSignature' => Yii::t('admin','Email Signatures'),
			'emailSignature' => Yii::t('admin','Default Signature'),
			'emailType' => Yii::t('admin','Method'),
			'emailHost' => Yii::t('admin','Hostname'),
			'emailPort' => Yii::t('admin','Port'),
			'emailUseAuth' => Yii::t('admin','Authentication'),
			'emailUser' => Yii::t('admin','Username'),
			'emailPass' => Yii::t('admin','Password'),
			'emailSecurity' => Yii::t('admin','Security'),
			'installDate' => Yii::t('admin','Installed'),
			'updateDate' => Yii::t('admin','Last Update'),
			'updateInterval' => Yii::t('admin','Update Interval'),
			'googleClientId' => Yii::t('admin', 'Google Client ID'),
			'googleClientSecret' => Yii::t('admin', 'Google Client Secret'),
			'googleAPIKey' => Yii::t('admin', 'Google API Key'),
			'googleIntegration' => Yii::t('admin', 'Activate Google Integration'),
			'inviteKey' => Yii::t('admin','Invite Key'),
			'workflowBackdateWindow' => Yii::t('admin','Workflow Backdate Window'),
			'workflowBackdateRange' => Yii::t('admin','Workflow Backdate Range'),
			'workflowBackdateReassignment' => Yii::t('admin','Workflow Backdate Reassignment'),
		);
	}

}
