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
 * 
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class Api2TestBase extends CURLDbTestCase {

    public $action;
     
    public function urlFormat(){
        $urlFormats = array(
            'model' => 'api2/{modelAction}',
            'relationships' => 'api2/{_class}/{_id}/relationships',
            'relationships_get' => 'api2/{_class}/{_id}/relationships/{_relatedId}.json',
            'tags' => 'api2/{_class}/{_id}/tags',
            'tags_get' => 'api2/{_class}/{_id}/tags/{tagname}.json',
        );
        return $urlFormats[$this->action];
    }

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
        );
    }

    /**
     *
     * @param type $method
     * @param type $params
     * @param type $user
     * @param type $postData
     * @param type $options
     * @return type
     */
    public function getCurlResponse($method='GET',$params = array(),$user='admin',
            $postData = array(),$options = array()){
        $ch = $this->getCurlHandle($method,$params,$user,$postData,$options);
        return curl_exec();
    }

    /**
     * Obtains the cURL handle and adds authentication parameters
     *
     * @param type $method The request method type, i.e. GET, POST, PUT, DELETE
     * @param type $params Parameters with which to format the request URI
     * @param type $user Row alias in the users fixture to use for authentication
     * @param type $postData Optional post data array to send
     * @param type $options
     * @return type
     */
    public function getCurlHandle($method='GET',$params = array(),$user='admin',
            $postData = array(),$options = array()){
        // Set the request method
        if($method != 'GET')
            $options[CURLOPT_CUSTOMREQUEST] = $method;

        // Enable authentication
        if(!empty($user))
            foreach($this->authCurlOpts($user) as $opt => $optVal)
                $options[$opt] = $optVal;
        // Additional headers to set
        if(in_array($method, array('PATCH', 'POST', 'PUT'))
                && !isset($options[CURLOPT_HTTPHEADER])){
            // By default: post a JSON
            $postData = json_encode($postData);
            $options[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json; charset=utf-8'
            );
        }
        return parent::getCurlHandle($params, $postData, $options);
    }

    public function authCurlOpts($user='admin') {
        return array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->user($user)->username
                    .':'.$this->user($user)->userKey
        );
    }

}

?>
