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
 * Create Record action
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowEmail extends X2FlowAction {
	public $title = 'Send Email';
	public $info = 'Send a template or custom email to the specified address.';
	
	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'options' => array(
				array('name'=>'to','label'=>'To:','type'=>'email'),
				array('name'=>'from','label'=>'From:','type'=>'email'),
				array('name'=>'template','label'=>'Template','type'=>'dropdown','options'=>array_merge(array(''=>Yii::t('studio','Custom')),Docs::getEmailTemplates()),'optional'=>1),
				array('name'=>'subject','label'=>'Subject'),
				array('name'=>'cc','label'=>'CC:','optional'=>1,'type'=>'email'),
				array('name'=>'bcc','label'=>'BCC:','optional'=>1,'type'=>'email'),
				array('name'=>'body','label'=>'Message','optional'=>1,'type'=>'richtext'),
				// 'time','dateTime'),
			));
	}
	
	public function execute(&$params) {
		// die(var_dump(array_keys($params)));
		$eml = new InlineEmail;
		$options = &$this->config['options'];
		
		if(isset($options['cc']['value']))
			$eml->cc = $this->parseOption('cc',$params);
		if(isset($options['bcc']['value']))
			$eml->bcc = $this->parseOption('bcc',$params);
		$eml->to = $this->parseOption('to',$params);
		
		$eml->from = array('address'=>$this->parseOption('from',$params),'name'=>'');
		$eml->subject = $this->parseOption('subject',$params);
		
		if(isset($options['body']['value']) && !empty($options['body']['value'])) {	// "body" option (deliberately-entered content) takes precedence over template
			$eml->scenario = 'custom';
			$eml->message = InlineEmail::emptyBody($this->parseOption('body',$params));
			$eml->prepareBody();
			// $eml->insertSignature(array('<br /><br /><span style="font-family:Arial,Helvetica,sans-serif; font-size:0.8em">','</span>'));
		} elseif(!empty($options['template']['value'])) {
			$eml->scenario = 'template';
			$eml->template = $this->parseOption('template',$params);
			$eml->prepareBody();
		}
		$result = $eml->send(false);
		// die(var_dump($result));
		return isset($result['code']) && $result['code'] == 200;
	}
}











