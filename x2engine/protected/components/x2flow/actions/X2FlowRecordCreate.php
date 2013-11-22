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
 * X2FlowAction that creates a new record
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowRecordCreate extends X2FlowAction {
	public $title = 'Create Record';
	public $info = '';

	public function paramRules() {
		return array(
			'title' => $this->title,
			'modelClass' => 'modelClass',
			'options' => array(
				array('name'=>'attributes'),
				array('name'=>'modelClass','label'=>Yii::t('studio','Record Type'),'type'=>'dropdown','options'=>X2Model::getModelTypes(true)),
			)
		);
	}

	public function execute(&$params) {
		if(!is_subclass_of($this->config['modelClass'],'X2Model'))	// make sure this is a valid model type
			return array (false, "");
		if(!isset($this->config['attributes']) || empty($this->config['attributes']))
			return array (false, "");

		$model = new $this->config['modelClass'];
		if ($this->setModelAttributes($model,$this->config['attributes'],$params) && $model->save()) {
            return array (
                true,
                Yii::t('studio', 'View created record: ').$model->getLink ());
        } else {
            return array(false, array_shift($model->getErrors()));
        }
	}
}
