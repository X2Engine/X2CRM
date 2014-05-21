<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/

/**
 * Handles External API subscriptions (for pushing data).
 *
 * This model record is for managing "subscriptions" to events in X2Engine. The
 * purpose behind this all is to allow a means to enact changes to other systems
 * in response to events in X2Engine without the need for polling.
 *
 * Each time {@link X2Flow::trigger} is called, all hooks matching the name of
 * the triggering event will also be called. Then, POST requests will be sent to
 * the URL specified by the "target_url" attribute, and the payload will be
 * either arbitrary data, or a URL within the REST API at which to retrieve the
 * payload (if the payload is an instance of {@link X2Model}). Each record of
 * this model type is thus in effect a request to either send data to a remote
 * service, or notify that remote service that it needs to fetch data from
 * X2Engine at a given resource location.
 *
 * @package application.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApiHook extends CActiveRecord {

    /**
     * No more than this number of remote hooks can run at a time.
     */
    const MAX_API_HOOK_BATCH = 3;

    /**
     * Hook timeout after 3 seconds
     */
    const MAX_WAIT_TIME = 3;

    /**
     * Response data, or false if no requests have yet been sent.
     * @var mixed
     */
    public $sent = false;
    
    /**
     * Most-recently-created CURL session handle
     * @var type 
     */
    private $_ch;

    public function attributeLabels() {
        return array(
            'id' => Yii::t('app','ID'),
            'target_url' => Yii::t('app','Target URL'),
            'event' => Yii::t('app','Event')
        );
    }

    /**
     * Sends a deletion request to the "subscription" URL
     */
    public function beforeDelete(){
        if($this->scenario != 'delete.remote')
            $this->send('DELETE');
        return parent::beforeDelete();
    }

    /**
     * Composes and returns a {@link CDbCriteria} property array for getting hooks
     * 
     * Creates the criteria properties for fetching all hooks for a specified 
     * model name, event name and user ID.
     *
     * @param string $event Event name
     * @param string $modelName Model name associated with the hook; used for
     *  distinguishing generic events such as "a record was created"
     * @param integer $userId run hooks for a user with this ID.
     * @return array
     */
    public static function criteria($event,$modelName,$userId) {
        $criteria = array(
            'condition' => "`t`.`event`=:event "
                . "AND `t`.`userId`".($userId != X2_PRIMARY_ADMIN_ID
                    ? " IN (".X2_PRIMARY_ADMIN_ID.",:userId)"
                    : "=:userId"),
            'params' => array(
                ':event' => $event,
                ':userId' => $userId
            ),
            'alias' => 't'
        );
        if(!empty($modelName)) {
            $criteria['condition'] .= ' AND `t`.`modelName`=:modelName';
            $criteria['params'][':modelName'] = $modelName;
        }
        return $criteria;
    }

    /**
     * Getter w/stripped code (Platinum-only settings) for the maximum number of
     * API hooks to send.
     *
     * @return type
     */
    public function getMaxNHooks() {
        $max = self::MAX_API_HOOK_BATCH;
        
        return $max;
    }

    /**
     * Returns the last status code
     */
    public function getStatus() {
        if(isset($this->_ch)) {
            return curl_getinfo($this->_ch,CURLINFO_HTTP_CODE);
        }
        return 0;
    }

    public function getTimeout() {
        $timeout = self::MAX_WAIT_TIME;
        
        return $timeout;
    }
    
    /**
     * Validator for limiting the number of hooks on a given action.
     *
     * @param type $attribute
     * @param type $params
     */
    public function maxBatchSize($attribute,$params=array()) {
        $max = $this->getMaxNHooks();
        $params = compact($attribute);
        $criteria = self::criteria($this->$attribute, $this->modelName,
                $this->userId);
        if(self::model()->count($criteria)>=$max) {
            $this->addError($attribute,Yii::t('app','The maximum number of '
                    . 'hooks ({n}) has been reached for events of this type.',
                    array('{n}'=>$max)));
        }
    }

    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public function rules() {
        return array(
            array('event','maxBatchSize'),
            array('target_url','required'),
            array('target_url','unique')
        );
    }

    /**
     * Runs the API hook; sends data to the third-party service.
     *
     * @param X2Model $output The relevant model object
     * @return \ApiHook
     */
    public function run($output) {
        $this->send('POST',$output);
        return $this;
    }

    /**
     * Runs all API hooks corresponding to an event, a model (or arbitrary
     * payload), and a user.
     *
     * @param string $event Name of the event
     * @param mixed $output The relevant model object
     * @param integer $userId the ID of the acting user in running the API hook
     */
    public static function runAll($event,$output=null,$userId=null) {
        $modelName = ($output instanceof X2Model)
                ? get_class($output)
                : null;
        $userId = $userId === null
                ? Yii::app()->getSuId()
                : $userId;            

        return array_map(function($h)use($output){
            return $h->run($output);
        },self::model()->findAll(self::criteria($event, $modelName, $userId)));
    }

    /**
     * Sends a request to pull data from X2Engine, or to delete/unsubscribe.
     *
     * @param string $method Request method to use
     * @param mixed $data an explicit array to JSON-encode and send, or a model
     *  that is an instance of {@link X2Model}. In the latter case, a resource
     *  URL within the API will be sent.
     */
    public function send($method,$data = null) {
        // Determine the body of the request to send
        if($data instanceof X2Model) {
            // Send a resource URL for the remote end to retrieve
            $payload = json_encode(array(
                'resource_url' => Yii::app()->createExternalUrl('/api2/model',array(
                    '_class' => get_class($data),
                    '_id' => $data->id
                ))
            ));
        } elseif(!is_object($data) && !is_resource($data)) {
            // Just JSON-encode and directly send the payload
            $output = $data;
            if(is_array($output)) {
                foreach(array_keys($output) as $i) { // models to attributes
                    if($output[$i] instanceof CActiveRecord) {
                        $output[$i] = $output[$i]->attributes;
                    }
                }
            }
            $payload = json_encode($output);
        }

        // Start a cURL session and configure the request
        $this->_ch = curl_init($this->target_url);
        curl_setopt_array($this->_ch,array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->getTimeout(),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json; charset=utf-8'),
            CURLOPT_HTTP200ALIASES => array_keys(ResponseUtil::getStatusMessages()),
        ));
        if(!empty($payload))
            curl_setopt($this->_ch,CURLOPT_POSTFIELDS,$payload);

        // Send the request
        $this->sent = curl_exec($this->_ch);

        // If the remote end is no longer listening, we can stop sending data
        if($this->getStatus() == 410) {
            $this->setScenario('delete.remote');
            $this->delete();
        }
    }

    public function tableName() {
        return 'x2_api_hooks';
    }

}

?>
