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
 * X2FlowAction that updates a new record
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowRecordUpdate extends X2FlowAction {

    public $title = 'Update Record';
    public $info = 'Change one or more fields on an existing record.';

    public function paramRules(){
        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelReqired' => 1,
            // 'modelClass' => 'modelClass',
            'options' => array(
                array('name' => 'attributes'),
                ));
    }

    public function execute(&$params){
        if(!isset($this->config['attributes']) || empty($this->config['attributes'])) {
            return array (
                false, 
                Yii::t('studio', "Flow item configuration error: No attributes added"));
        }
        $model = $params['model'];

        $this->setModelAttributes($model, $this->config['attributes'], $params);
        if ($model->updateByPk($model->id, $model->attributes)) {
		    if(is_subclass_of($model,'X2Model')) {
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
