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
 * X2FlowTrigger
 *
 * @package X2CRM.components.x2flow.triggers
 */
class WebleadTrigger extends BaseTagTrigger {

    public $title = 'New Web Lead';
    public $info = 'Triggers when a new contact fills out your web lead capture form.';

    public function paramRules(){
        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelClass' => 'Contacts',
            'options' => array(
                array('name' => 'tags', 'label' => Yii::t('studio', 'Tags (optional)'), 'operators' => array('=', 'list', 'notList'), 'optional' => 1),
                ));
    }

    public function check(&$params){
        $tagOptions = $this->config['options']['tags'];
        $tags = $tagOptions['value'];
        $tags = is_array($tags) ? $tags : Tags::parseTags($tags);
        if(!empty($tags) && isset($params['tags'])){
            if(!is_array($params['tags'])){
                $params['tags']=explode(',',$params['tags']);
            }
            //$params['tags']=array_map(function($item){ return str_replace('#','',$item); },$params['tags']);
            // must have at least 1 tag in the list:
            return array (
                count(array_intersect($params['tags'], $tags)) > 0 ? 
                    $this->checkConditions($params) : false, '');
        }else{
            return array (true, '');
        }
    }

}
