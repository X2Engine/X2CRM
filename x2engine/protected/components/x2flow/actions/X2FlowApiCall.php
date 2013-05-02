<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * X2FlowAction that calls a remote API
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowApiCall extends X2FlowAction {
	public $title = 'Remote API Call';
	public $info = 'Call a remote API by requesting the specified URL. You can specify the request type and any variables to be passed with the request. To improve performance, he request will be put into a job queue unless you need it to execute immediately.';
	
	public function paramRules() {
		$httpVerbs = array(
			'get'=>'GET',
			'post'=>'POST',
			'put'=>'PUT',
			'delete'=>'DELETE'
		);
		
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelClass' => 'API_params',
			'options' => array(
				array('name'=>'url','label'=>'URL'),
				array('name'=>'method','label'=>'Method','type'=>'dropdown','options'=>$httpVerbs),
				array('name'=>'attributes','optional'=>1),
				// array('name'=>'immediate','label'=>'Call immediately?','type'=>'boolean','defaultVal'=>true),
			));
	}
	
	public function execute(&$params) {
		$options = &$this->config['options'];
	
		if($options['immediate'] || true) {
			$headers = array();
			if(isset($this->config['attributes']) && !empty($this->config['attributes'])) {
				$data = http_build_query($this->config['attributes']);
			
				if($options['method'] === 'GET') {
					$options['url'] .= strpos($options['url'],'?')===false? '?' : '&';	// make sure the URL is ready for GET params
					$options['url'] .= $data;
				} else {
					$headers[] = 'Content-type: application/xml';	// set up headers for POST style data
					$headers[] = 'Content-Length: '.strlen($data);
					$httpOptions['content'] = $data;
				}
			}
			$httpOptions = array(
				'timeout' => 5,		// 5 second timeout
				'method' => $options['method'],
				'header' => implode("\r\n",$headers),
			);
			
			$context = stream_context_create(array('http'=>$httpOptions));
			
			return @FileUtil::getContents($options['url'],false,$context);
		}
	}
}