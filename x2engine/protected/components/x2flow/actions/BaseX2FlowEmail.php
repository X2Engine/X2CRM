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




Yii::import('application.components.x2flow.actions.X2FlowAction');
/**
 * Base action class for email related x2flow actions
 *
 * @package application.components.x2flow.actions
 */
abstract class BaseX2FlowEmail extends X2FlowAction {

    public function paramRules(){
        if(Yii::app()->isInSession){
            $credOptsDict = Credentials::getCredentialOptions(null, true);
            $credOpts = $credOptsDict['credentials'];
            $selectedOpt = $credOptsDict['selectedOption'];
            foreach($credOpts as $key => $val){
                if($key == $selectedOpt){
                    $credOpts = array($key => $val) + $credOpts; // move to beginning of array
                    break;
                }
            }
        }else{
            $credOpts = array();
        }
        return array_merge (parent::paramRules (), array (
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'from', 
                    'label' => Yii::t('studio', 'Send As:'),
                    'type' => 'dropdown',
                    'options' => $credOpts
                ),
            // 'time' => array('dateTime'),
            )));
    }

    /**
     * @param <array of strings> comma separated recipient addresses
     * @return array error flag and message
     */
    protected function checkDoNotEmailFields (InlineEmail $eml) {
        if (Yii::app()->settings->x2FlowRespectsDoNotEmail &&  
            !$eml->checkDoNotEmailFields ()) {
            return array (
                false, Yii::t('studio', 'Email could not be sent because at least one of the '.
                    'addressees has their "Do not email" attribute checked'));
        }
        return array (true, '');
    }

}
