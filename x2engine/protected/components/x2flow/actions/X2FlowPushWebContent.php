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
 * Push Web Content Action
 *
 * @package application.components.x2flow.actions
 */
class X2FlowPushWebContent extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Push Web Content';
    public $info = 'Displays custom web content to a record visiting your website. This action terminates the workflow.';

    /**
     * Returns a JS script which inserts the specified content into the DOM by replacing the
     * targeted content script.
     * 
     * @param string $content the html content to place in the DOM
     * @param object $model the model with which to perform attribute replacement
     */
    public static function getPushWebContentScript($content, $model = null, $flowId) {
        //AuxLib::debugLog ('getPushWebContentScript');
        if ($model) {
            $targetedContent = Formatter::replaceVariables(
                            $content, $model);
        } else {
            $targetedContent = $content;
        }

        //AuxLib::debugLogR ($_COOKIE);

        $targetedContent = preg_replace("/\n/", '', $targetedContent);
        $targetedContentScript = 'document.write (' .
                //CJSON::encode (html_entity_decode ($targetedContent)) .  ');';
                CJSON::encode($targetedContent) . ');';
        return array(true, "", $targetedContentScript);
    }
    
    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'content',
                    'label' => Yii::t('studio', 'Message'),
                    'optional' => 1,
                    'type' => 'richtext'
                ),
            )
        ));
    }
    
    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params, $triggerLogId = null, $flow = null) {
        if (!isset($params['model'])) {
            return array(false, '');
        }
        return self::getPushWebContentScript(
                        $this->parseOption('content', $params), $params['model'], $flow->id
        );
    }

}
