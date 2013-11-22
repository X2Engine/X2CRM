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
 * X2FlowAction that calls a remote API
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowApiCall extends X2FlowAction {

    public $title = 'Remote API Call';
    public $info = 'Call a remote API by requesting the specified URL. You can specify the request type and any variables to be passed with the request. To improve performance, he request will be put into a job queue unless you need it to execute immediately.';

    public function paramRules(){
        $httpVerbs = array(
            'GET' => Yii::t('studio', 'GET'),
            'POST' => Yii::t('studio', 'POST'),
            'PUT' => Yii::t('studio', 'PUT'),
            'DELETE' => Yii::t('studio', 'DELETE')
        );

        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelClass' => 'API_params',
            'options' => array(
                array('name' => 'url', 'label' => Yii::t('studio', 'URL')),
                array('name' => 'method', 'label' => Yii::t('studio', 'Method'), 'type' => 'dropdown', 'options' => $httpVerbs),
                array('name' => 'attributes', 'optional' => 1),
            // array('name'=>'immediate','label'=>'Call immediately?','type'=>'boolean','defaultVal'=>true),
                ));
    }

    public function execute(&$params){
        $url = $this->parseOption('url', $params);
        if(strpos($url,'http')===false){
            $url = 'http://'.$url;
        }
        $method = $this->parseOption('method', $params);

        if($this->parseOption('immediate', $params) || true){
            $headers = array();
            if(isset($this->config['attributes']) && !empty($this->config['attributes'])){
                $httpOptions = array(
                    'timeout' => 5, // 5 second timeout
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                );
                $data=array();
                foreach($this->config['attributes'] as $param){
                    if(isset($param['name'],$param['value'])){
                        $data[$param['name']]=$param['value'];
                    }
                }
                $data = http_build_query($data);
                if($method === 'GET'){
                    $url .= strpos($url, '?') === false ? '?' : '&'; // make sure the URL is ready for GET params
                    $url .= $data;
                }else{
                    $headers[] = 'Content-type: application/x-www-form-urlencoded'; // set up headers for POST style data
                    $headers[] = 'Content-Length: '.strlen($data);
                    $httpOptions['content'] = $data;
                    $httpOptions['header'] = implode("\r\n", $headers);
                }
            }
            $context = stream_context_create(array('http' => $httpOptions));
            if(file_get_contents($url, false, $context)!==false){
                return array(true, "");
            }else{
                return array(false, "");
            }
        }
    }

}
