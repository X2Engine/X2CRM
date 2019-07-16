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
 * X2FlowTrigger
 *
 * @package application.components.x2flow.triggers
 */
abstract class BaseTagTrigger extends X2FlowTrigger {
	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelClass' => 'modelClass',
			'options' => array(
				array(
                    'name'=>'modelClass',
                    'label'=>Yii::t('studio','Record Type'),
                    'type'=>'dropdown',
                    'options'=>X2Flow::getModelTypes(true)
                ),
				array(
                    'name'=>'tags',
                    'label'=>Yii::t('studio','Tags'),
                    'type'=>'tags'
                ),
			)
        );
	}

	public function check(&$params){
        $tags = $this->config['options']['tags']['value'];
        $tags = is_array($tags) ? $tags : Tags::parseTags($tags, true);
        if(!empty($tags) && isset($params['tags'])){ // Check passed params to be sure they're set
            if(!is_array($params['tags'])){
                $params['tags'] = explode(',', $params['tags']);
            }
            $params['tags'] = array_map(function($item){ 
                return preg_replace('/^#/','', $item); 
            }, $params['tags']);

            // must have at least 1 tag in the list:
            if(count(array_intersect($params['tags'], $tags)) > 0){
                return $this->checkConditions($params);
            }else{
                return array(
                    false, 
                    Yii::t(
                        'studio',
                        'No tags on the record matched those in the tag trigger criteria.'));
            }
        }else{ // config is invalid or record has no tags (tags are not optional)
            return array(
                false, 
                empty($tags) ? 
                    Yii::t('studio','No tags in the trigger criteria!') : 
                    Yii::t('studio','Tags parameter missing!'));
        }
    }
}
