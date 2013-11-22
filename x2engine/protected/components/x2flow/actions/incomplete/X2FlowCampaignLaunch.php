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
 * X2FlowAction that launches a campaign
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCampaignLaunch extends X2FlowAction {
	public $title = 'Launch Campaign';
	public $info = 'Immediately begin emailing contacts on the selected campaign';
	
	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'options' => array(
				array('name'=>'campaign','label'=>'Campaign','type'=>'link','linkType'=>'Campaign','linkSource'=>Yii::app()->controller->createUrl(
					CActiveRecord::model('Campaign')->autoCompleteSource
				)),
			));
	}
	
	public function execute(&$params) {
		$campaign = CActiveRecord::model('Campaign')->findByPk($this->config['options']['campaign']);
		if($campaign === null || ($campaign->launchDate != 0 && $campaign->launchDate < time()) || empty($campaign->subject))
			return false;
		
		if(!isset($campaign->list) || ($campaign->list->type == 'dynamic' && X2Model::model($campaign->list->modelName)->count($campaign->list->queryCriteria()) < 1))
			return false;
		
		// check if there's a template, and load that into the content field
		if($campaign->template != 0) {
			$template = X2Model::model('Docs')->findByPk($campaign->template);
			if(isset($template))
				$campaign->content = $template->text;
		}
		
		//Duplicate the list for campaign tracking, leave original untouched
		//only if the list is not already a campaign list
		if($campaign->list->type != 'campaign') {
			$newList = $campaign->list->staticDuplicate();
			if(!isset($newList))
				return false;
			
			$newList->type = 'campaign';
			if(!$newList->save())
				return false;
			$campaign->list = $newList;
			$campaign->listId = $newList->id;
		}

		$campaign->launchDate = time();
		return $campaign->save();
	}
}