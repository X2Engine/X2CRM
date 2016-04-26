<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

/**
 * X2FlowAction that adds a comment to a record
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordListRemove extends X2FlowAction {
	public $title = 'Remove from List';
	public $info = 'Remove this record from a static list.';

	public function paramRules() {
		return array_merge (parent::paramRules (), array (
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelRequired' => 'Contacts',
			'options' => array(
				array(
                    'name'=>'listId',
                    'label'=>Yii::t('studio','List'),
                    'type'=>'link',
                    'linkType'=>'X2List',
                    'linkSource'=>Yii::app()->controller->createUrl(
					    CActiveRecord::model('X2List')->autoCompleteSource, array (
                            'static' => 1
                        )
                    )
                ),
			)));
	}

	public function execute(&$params) {
        $listId = $this->parseOption('listId',$params);
        if(is_numeric($listId)){
            $list = CActiveRecord::model('X2List')->findByPk($listId);
        }else{
            $list = CActiveRecord::model('X2List')->findByAttributes(
                array('name'=>$listId));
        }
		
        if($list === null) {
            return array (false, Yii::t('studio', 'List could not be found'));
        } else if ($list->modelName !== get_class($params['model'])) {
            return array (false, Yii::t('studio', 'The selected list does not contain records '.
                'of this type'));
        } else { // $list !== null && $list->modelName === get_class($params['model'])
			if ($list->removeIds($params['model']->id)) {  
                return array (true, "");
            }
        }
        return array (false, "");
	}
}
