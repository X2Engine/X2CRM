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
 * Create Record action
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowEmail extends X2FlowAction {
	public $title = 'Email';
	public $info = 'Send a template or custom email to the specified address.';

	public function paramRules() {
        $credOptsDict = Credentials::getCredentialOptions (null, true);
        $credOpts = $credOptsDict['credentials'];
        $selectedOpt = $credOptsDict['selectedOption'];
        foreach ($credOpts as $key=>$val) {
            if ($key == $selectedOpt) {
                $credOpts = array ($key => $val) + $credOpts; // move to beginning of array
                break;
            }
        }

		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'options' => array(
				array('name'=>'from','label'=>Yii::t('studio','Send As:'),'type'=>'dropdown',
                    'options'=>$credOpts),
				array('name'=>'to','label'=>Yii::t('studio','To:'),'type'=>'email'),
				//array('name'=>'from','label'=>Yii::t('studio','From:'),'type'=>'email'),
				array('name'=>'template','label'=>Yii::t('studio','Template'),'type'=>'dropdown','options'=>array(''=>Yii::t('studio','Custom'))+Docs::getEmailTemplates(),'optional'=>1),
				array('name'=>'subject','label'=>Yii::t('studio','Subject'),'optional'=>1),
				array('name'=>'cc','label'=>Yii::t('studio','CC:'),'optional'=>1,'type'=>'email'),
				array('name'=>'bcc','label'=>Yii::t('studio','BCC:'),'optional'=>1,'type'=>'email'),
				array('name'=>'body','label'=>Yii::t('studio','Message'),'optional'=>1,'type'=>'richtext'),
				// 'time','dateTime'),
			));
	}

	public function execute(&$params) {
		// die(var_dump(array_keys($params)));
		$eml = new InlineEmail;
        $historyFlag = false;
		$options = &$this->config['options'];
        if(isset($params['model'])){
            $historyFlag = true;
            $eml->targetModel=$params['model'];
        }
		if(isset($options['cc']['value']))
			$eml->cc = $this->parseOption('cc',$params);
		if(isset($options['bcc']['value'])){
			$eml->bcc = $this->parseOption('bcc',$params);
        }
		$eml->to = $this->parseOption('to',$params);

		//$eml->from = array('address'=>$this->parseOption('from',$params),'name'=>'');
        $eml->credId = $this->parseOption('from',$params);
        //printR ($eml->from, true);
		$eml->subject = Formatter::replaceVariables($this->parseOption('subject',$params),$params['model']);

		if(isset($options['body']['value']) && !empty($options['body']['value'])) {	// "body" option (deliberately-entered content) takes precedence over template
            $eml->scenario = 'custom';
			$eml->message = InlineEmail::emptyBody(Formatter::replaceVariables($this->parseOption('body',$params),$params['model']));
			$eml->prepareBody();
			// $eml->insertSignature(array('<br /><br /><span style="font-family:Arial,Helvetica,sans-serif; font-size:0.8em">','</span>'));
		} elseif(!empty($options['template']['value'])) {
			$eml->scenario = 'template';
			$eml->template = $this->parseOption('template',$params);
			$eml->prepareBody();
		}
		$result = $eml->send($historyFlag);
		if (isset($result['code']) && $result['code'] == 200) {
            return array (true, "");
        } else {
            return array (false, Yii::t('app', "Email could not be sent"));
        }
	}
}
