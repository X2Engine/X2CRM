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
 * X2FlowAction that calls a remote API
 *
 * @package application.components.x2flow.actions
 */
class X2FlowApiCall extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Remote API Call';
    public $info = "Call a remote API by requesting the specified URL. You can specify the request type, HTTP headers, and any variables to be passed with the request.";

    /**
     * This will only ever by false during unit tests 
     */
    private static $_makeRequest;

    /**
     * Allows request behavior of this class to be toggled during unit tests
     */
    public function getMakeRequest() {
        if (!isset(self::$_makeRequest)) {
            self::$_makeRequest = true;
        }
        return self::$_makeRequest;
    }

    /**
     * Gets api headers
     * 
     * @param array $headerRows 
     * @return array
     */
    public function getHeaders($headerRows, $params) {
        $headers = array();
        foreach ($headerRows as $row) {
            $name = X2Flow::parseValue($row['name'], '', $params, false);
            $value = X2Flow::parseValue($row['value'], '', $params, false);
            $headers[$name] = $value;
        }
        return $headers;
    }

    /**
     * Formats api headers
     * 
     * @param array $headers 
     * @return string
     */
    public function formatHeaders($headers) {
        $formattedHeaders = array();
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = $name . ': ' . $value;
        }
        return $formattedHeaders;
    }

    /**
     * Overrides
     * 
     * Adds url validation. Presents warning to user on flow save if
     * specified url points to same server as the one X2Engine is hosted on.
     */
    public function validateOptions(&$paramRules, $params = null, $staticValidation = false) {
        list ($success, $message) = parent::validateOptions($paramRules, $params, $staticValidation);
        if (!$success)
            return array($success, $message);
        $url = $this->config['options']['url']['value'];

        $hostInfo = preg_replace('/^https?:\/\//', '', Yii::app()->getAbsoluteBaseUrl());
        $url = preg_replace('/^https?:\/\//', '', $url);
        if ($staticValidation &&
                gethostbyname($url) === gethostbyname($hostInfo)) {

            return array(
                self::VALIDATION_WARNING,
                Yii::t(
                        'studio', 'Warning: The url specified in your Remote API Call flow action points to the ' .
                        'same server that X2Engine is hosted on. This could mean that this flow makes ' .
                        'a request to X2Engine\'s API. Calling X2Engine\'s API from X2Flow is not ' .
                        'advised since it could potentially trigger this flow, resulting in an ' .
                        'infinite loop.'));
        } else {
            return array(true, $message);
        }
    }

    /**
     * Tries to prevent api requests to X2Engine's api. This is a looser check
     * than validateOptions(). validateOptions() is more likely to produce 
     * false positives which we wouldn't want to have effect flow execution.
     */
    private function validateUrl($url) {
        $formattedUrl = preg_replace('/^https?:\/\//', '', $url);
        $absoluteBaseUrl = Yii::app()->getAbsoluteBaseUrl();
        $formattedAbsoluteBaseUrl = preg_replace('/^https?:\/\//', '', $absoluteBaseUrl);
        return !preg_match("/^" . preg_quote($formattedAbsoluteBaseUrl, '/')
                        . ".*\/api2?\/.*/", $formattedUrl);
    }

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $httpVerbs = array(
            'GET' => Yii::t('studio', 'GET'),
            'POST' => Yii::t('studio', 'POST'),
            'PUT' => Yii::t('studio', 'PUT'),
            'DELETE' => Yii::t('studio', 'DELETE')
        );

        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelClass' => 'API_params',
            'options' => array(
                array(
                    'name' => 'url', 'label' => Yii::t('studio', 'URL')
                ),
                array(
                    'name' => 'method',
                    'label' => Yii::t('studio', 'Method'),
                    'type' => 'dropdown',
                    'options' => $httpVerbs
                ),
                array(
                    'name' => 'jsonPayload',
                    'label' => Yii::t('studio', 'Use JSON payload?'),
                    'type' => 'boolean',
                    'defaulVal' => 0
                ),
                array(
                    'name' => 'jsonBlob',
                    'label' => Yii::t('studio', 'JSON'),
                    'type' => 'text',
                    'optional' => 1,
                    'htmlOptions' => array(
                        'style' => 'display: none;'
                    )
                ),
                array(
                    'name' => 'attributes', 'optional' => 1
                ),
                array(
                    'name' => 'headers',
                    'type' => 'attributes',
                    'optional' => 1
                ),
        )));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $url = $this->parseOption('url', $params);
        if (strpos($url, 'http') === false) {
            $url = 'http://' . $url;
        }
        $method = $this->parseOption('method', $params);

        if ($this->parseOption('immediate', $params) || true) {
            $headers = array();
            $httpOptions = array(
                'timeout' => 5, // 5 second timeout
                'method' => $method,
            );
            if (isset($this->config['headerRows'])) {
                $headers = $this->getHeaders($this->config['headerRows'], $params);
            }

            if ($method !== 'GET' && $this->parseOption('jsonPayload', $params)) {
                $data = $this->parseOption('jsonBlob', $params, false);
            } elseif (isset($this->config['attributes']) && !empty($this->config['attributes'])) {
                $data = array();
                foreach ($this->config['attributes'] as $param) {
                    if (isset($param['name'], $param['value'])) {
                        $data[$param['name']] = X2Flow::parseValue(
                                        $param['value'], '', $params, false);
                    }
                }
            }

            if (isset($data)) {
                if ($method === 'GET') {
                    $data = http_build_query($data);
                    // make sure the URL is ready for GET params
                    $url .= strpos($url, '?') === false ? '?' : '&';
                    $url .= $data;
                } else {
                    if ($this->parseOption('jsonPayload', $params)) {
                        // nested JSON option
                        if (!isset($headers['Content-Type']))
                            $headers['Content-Type'] = 'application/json';
                        $httpOptions['content'] = $data;
                    } else {
                        // set up default header for POST style data
                        if (!isset($headers['Content-Type']))
                            $headers['Content-Type'] = 'application/x-www-form-urlencoded';

                        if (preg_match("/application\/json/", $headers['Content-Type'])) {
                            // legacy flat JSON object support
                            $data = CJSON::encode($data);
                            $httpOptions['content'] = $data;
                        } else {
                            $data = http_build_query($data);
                            $httpOptions['content'] = $data;
                        }
                    }

                    // set up default header for POST style data
                    if (!isset($headers['Content-Length']))
                        $headers['Content-Length'] = strlen($data);
                }
            }
            if (count($headers)) {
                $formattedHeaders = $this->formatHeaders($headers);
                $httpOptions['header'] = implode("\r\n", $formattedHeaders);
            }

            $context = stream_context_create(array('http' => $httpOptions));
            if (!$this->validateUrl($url)) {
                if (YII_UNIT_TESTING) {
                    return array(
                        false,
                        array('url' => $url)
                    );
                } else {
                    return array(
                        false,
                        Yii::t('studio', 'Requests cannot be made to X2Engine\'s API from X2Flow.')
                    );
                }
            }
            if (!$this->getMakeRequest()) {
                return array(true, array_merge(array('url' => $url), $httpOptions));
            } else {
                $response = @file_get_contents($url, false, $context);
                $params['returnValue'] = $response;
                if ($response !== false) {
                    if (YII_UNIT_TESTING) {
                        return array(true, $response);
                    } else {
                        return array(true, Yii::t('studio', "Remote API call succeeded"));
                    }
                } else {
                    if (YII_UNIT_TESTING) {
                        return array(false, print_r($http_response_header, true));
                    } else {
                        return array(false, Yii::t('studio', "Remote API call failed!"));
                    }
                }
            }
        }
    }

}
