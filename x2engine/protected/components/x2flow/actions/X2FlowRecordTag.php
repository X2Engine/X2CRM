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
 * X2FlowAction that adds, removes or clears all tags on a record
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowRecordTag extends X2FlowAction {
	public $title = 'Add or Remove Tags';
	public $info = 'Enter a comma-separated list of tags to add to the record';

	public function paramRules() {
		$tagActions = array(
			'add' => Yii::t('studio','Add'),
			'remove' => Yii::t('studio','Remove'),
			'clear' => Yii::t('studio','Clear All'),
		);
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelRequired' => 1,
			'options' => array(
				array('name'=>'tags','label'=>Yii::t('studio','Tags'),'type'=>'tags'),
				array('name'=>'action','label'=>Yii::t('studio','Action'),'type'=>'dropdown','options'=>$tagActions),
			));
	}

	public function execute(&$params) {
		$tags = Tags::parseTags($this->parseOption('tags',$params));

        $retVal;
        $model = $params['model'];
		switch($this->parseOption('action',$params)) {
			case 'add':
				$retVal = $model->addTags($tags);
                break;
			case 'remove':
				$retVal = $model->removeTags($tags);
                break;
			case 'clear':
				$retVal = $model->clearTags();
                break;
		}
        if ($retVal) {
		    if(is_subclass_of ($model,'X2Model')) {
                return array (
                    true,
                    Yii::t('studio', 'View updated record: ').$model->getLink ()
                );
            } else {
                return array (true, "");
            }
        } else {
            return array (false, "");
        }
	}
}
