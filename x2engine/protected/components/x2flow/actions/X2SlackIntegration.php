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
 * @edition: ent
 */

/**
 * Create Record action
 *
 * @package application.components.x2flow.actions
 */
class X2SlackIntegration extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Slack Message';
    public $info = 'Creates and sends a slack message.';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $info = Yii::t('studio', $this->info);
        $option = array(
                array(
                'name' => 'channels',
                'label' => Yii::t('studio', 'Channel To Send'),
                'type' => 'dropdown',
                'defaultVal' => '',
                'options' => array('' => Yii::t('studio', 'Select a Channel')) +
                Profile::getChannelList()
                ),
                array(
                    'name' => 'body',
                    'label' => Yii::t('studio', 'Message'),
                    'optional' => 1,
                    'type' => 'text'
                ),

        );
        if(!Yii::app()->settings->slackIntegration) {
            $info = 'Slack Integration is disabled';
            $option = array();
        }
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => $info,
            'modelClass' => 'API_params',
            'options' => $option
        ));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {

        if(!Yii::app()->settings->slackIntegration) {
            return array(false, Yii::t('app', 'Slack Integration is not enabled'));
        }
        
        $channel = $this->parseOption('channels', $params);
        $body = $this->parseOption('body', $params);
        
        $currentuser = Yii::app()->user->getName();
        $profile = Profile::model()->findByAttributes(array('username'=>$currentuser));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://slack.com/api/chat.postMessage");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array('token' => $profile->slackRefreshToken,
                                   'channel' => $channel,
                                   'text' => json_encode($body)
        )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute url
        $server_output = curl_exec($ch);
        curl_close ($ch);
        
        if(isset($server_output)){
            $result = CJSON::decode($server_output);
            if($result['ok'] == true){
                return array(true, Yii::t('app', 'Slack Message Successfully sent'));
            }
        }
        return array(false, Yii::t('app', "Slack Message could not be sent"));
    }

}
