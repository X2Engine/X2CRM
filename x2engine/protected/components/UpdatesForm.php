<?php

/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

// Messages for the installation contact & updates registry form. These messages all require translations
class UpdatesForm {

    public $label = array(
	'receiveUpdates' => 'Notify me of software updates',
	'firstName' => 'First Name',
	'lastName' => 'Last Name',
	'email' => 'Email',
	'phone' => 'Phone Number',
	'source' => 'How you found X2EngineCRM',
	'subscribe' => 'Subscribe to the newsletter',
	'info' => 'Comments',
	'requestContact' => 'Request a follow-up contact',
	'company' => 'Company',
	'position' => 'Position',
    );
    public $message = array(
	'updatesTitle' => 'Software Updates',
	'emailIni' => 'If different from Administrator Email',
	'infoIni' => 'Intended use of X2EngineCRM, goals, etc.',
	'intro' => 'Please help us improve X2EngineCRM by providing the following information:',
	'already' => 'Software update notifications enabled.',
	'optionalTitle' => 'Optional Information',
	'title' => 'Software Updates',
	'emailValidation' => 'Please enter a valid email address.', // This one has a translation already.
	'connectionErrHeader' => 'Could not connect to the updates server at this time.',
	'connectionErrMessage' => 'You can continue installing the application without enabling updates and try again later by going into "General Settings" under the section "App Settings" in the Admin console.',
    );
    public $leadSources = array(
	Null => '-----',
	'Google' => 'Google',
	'Sourceforge' => 'Sourceforge',
	'Github' => 'Github',
	'News outlet' => 'News Outlet',
	'Other' => 'Other',
    );
    public $config = array();

    public function __construct($config, $transFunc, $transFuncArgs = array()) {
	$this->config = array(
	    'x2_version' => '',
	    'php_version' => phpversion(),
	    'GD_support' => function_exists('gd_info') ? 1 : 0,
	    'db_type' => 'MySQL',
	    'db_version' => '',
	    'unique_id' => 'none',
	    'formId' => '',
	    'submitButtonId' => '',
	    'statusId' => '',
	    'themeUrl' => '',
	    'titleWrap' => array('<h2>', '</h2>'),
	    'serverInfo' => False,
	    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
	);
	foreach ($config as $key => $value)
	    $this->config[$key] = $value;


	// Translate all messages for the updates form:
	foreach (array('label', 'message', 'leadSources') as $attr) {
	    $attrArr = $this->$attr;
	    foreach (array_keys($this->$attr) as $key)
		$attrArr[$key] = call_user_func_array($transFunc, array_merge($transFuncArgs, array($attrArr[$key])));
	    $this->attr = $attrArr;
	}
    }

    public function wrapTitle($title) {
	return $this->config['titleWrap'][0] . $title . $this->config['titleWrap'][1];
    }

}

?>
