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
 * @package application.components.x2flow.actions
 */
class TargetedContentRequestTrigger extends X2FlowTrigger {
	public $title = 'Targeted Content Requested';
	public $info = 'Triggered when a contact visits a page with embedded targeted content.';
	
	public function paramRules() {
		return array(
			'title' => Yii::t('studio', $this->title),
			'info' => Yii::t('studio', $this->info),
			'modelClass' => 'Contacts',
			'options' => array(
				array(
                    'name' => 'url', 
                    'label' => Yii::t ('studio', 'URL'), 
                    'optional' => 1,
                    'operators' => array ('=', '<>', 'list', 'notList', 'contains', 'noContains')
                ),
				array(
                    'name' => 'content', 
                    'comparison' => false,
                    'label' => 
                        Yii::t('studio', 'Default Web Content') . 
                        '<span class="x2-hint" title="'.
                        Yii::t('app', 'This web content gets displayed if the visitor doesn\'t '.
                        'have an associated contact record or if your flow terminates without '.
                        'pushing web content').'">&nbsp;[?]</span>', 
                    'optional' => 1,
                    'type' => 'richtext',
                    'htmlOptions' => array (
                        'class' => 'default-web-content-fieldset'
                    )
                ),

			)
        );
	}

    public function getDefaultReturnVal ($flowId) {
        $retValArr = X2FlowPushWebContent::getPushWebContentScript (
            $this->parseOption ('content', $params), null, $flowId);
        return $retValArr[2];
    }

    public function afterValidate (&$params, $defaultErrMsg='', $flowId) {
        if (!isset($params['model'])) { // no contact record available;
            return X2FlowPushWebContent::getPushWebContentScript (
                $this->parseOption ('content', $params), null, $flowId);
        }
    }
}
