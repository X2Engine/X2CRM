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
 * Configuration object for the installation contact & updates registry form.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdatesForm {

	/**
	 * @var array Form input labels
	 */
	public $label = array(
		'receiveUpdates' => 'Notify me of software updates',
		'firstName' => 'First Name',
		'lastName' => 'Last Name',
		'email' => 'Email',
		'phone' => 'Phone Number',
		'source' => 'How you found X2CRM',
		'subscribe' => 'Subscribe to the newsletter',
		'info' => 'Comments',
		'requestContact' => 'Request a follow-up contact',
		'company' => 'Company',
		'position' => 'Position',
		'unique_id' => 'Product Key',
	);

	/**
	 * @var array Messages
	 */
	public $message = array(
		'updatesTitle' => 'Software Updates',
		'registrationTitle' => 'Registration',
		'registrationSuccess' => 'Software registration succeeded.',
		'registrationSubtext' => 'To receive software updates from X2Engine, please register your copy of X2CRM:',
		'registrationPostText' => 'For support or sales inquiries, please contact us',
		'emailIni' => 'If different from Administrator Email',
		'infoIni' => 'Intended use of X2CRM, goals, etc.',
		'intro' => 'Please help us improve X2CRM by providing the following information:',
		'already' => 'Software update notifications enabled.',
		'optionalTitle' => 'Optional Information',
		'title' => 'Software Updates',
		'emailValidation' => 'Please enter a valid email address.', // This one has a translation already.
		'connectionErrHeader' => 'Could not connect to the updates server at this time.',
		'connectionErrMessage' => 'You can continue installing the application without enabling updates and try again later by going into "General Settings" under the section "App Settings" in the Admin console.',
		'connectionNOsMessage' => 'Make sure you have an active internet connection. If the problem persists, please contact us',
	);

	/**
	 * @var array Contents of the "how you found X2CRM" selector
	 */
	public $leadSources = array(
		Null => '-----',
		'Google' => 'Google',
		'Sourceforge' => 'Sourceforge',
		'Github' => 'Github',
		'News outlet' => 'News Outlet',
		'Other' => 'Other',
	);

	/**
	 * @var array Configuration options
	 */
	public $config = array();

	/**
	 * @var boolean Form display flag
	 */
	public $os = True;


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
			'receiveUpdates' => 1,
			'serverInfo' => False, // If true, server info should be included in the data that is sent via AJAX.
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'registered' => 0,
			'edition' => 'opensource',
			'isUpgrade' => False,
		);

		foreach ($config as $key => $value)
			$this->config[$key] = $value;

		// Is it OS edition?
		$this->os = $config['edition']=='opensource' && ! $this->config['isUpgrade'];
		// Empty the unique_id field; user will fill it.
		if (!$this->os)
			if ($this->config['unique_id'] == 'none')
				$this->config['unique_id'] = '';

		// $this->config['unique_id'] = 'something'; // To test what the form looks like when unique_id is filled
			
		// Translate all messages for the updates form:
		foreach (array('label', 'message', 'leadSources') as $attr) {
			$attrArr = $this->$attr;
			foreach (array_keys($this->$attr) as $key)
				$attrArr[$key] = call_user_func_array($transFunc, array_merge($transFuncArgs, array($attrArr[$key])));
			$this->$attr = $attrArr;
		}
		$this->message['connectionNOsMessage'] .= ': <a href="http://www.x2crm.com/contact/">x2crm.com</a>.';
	}

	/**
	 * Wrap a title in tags specified by the titleWrap config parameter
	 * @param type $title
	 * @return type 
	 */
	public function wrapTitle($title) {
		return $this->config['titleWrap'][0] . $title . $this->config['titleWrap'][1];
	}

	/**
	 * Print fields of the form that are text inputs
	 * @param type $fields 
	 */
	public function textFields($fields = array()) {
		foreach ($fields as $field) {
			echo '<div class="row">'."\n";
			echo '<label for="' . $field . '">' . $this->label[$field] . '</label>';
			echo '<input type="text" name="' . $field . '" id="' . $field . '" />';
			echo '</div><!-- .row -->'."\n";
		}
	}

	/**
	 * Print hidden fields of the form
	 * @param array $fields 
	 */
	public function hiddenFields($fields = array()) {
		foreach ($fields as $field)
			echo '<input type="hidden" name="' . $field . '" id="' . $field . '" value="' . $this->config[$field] . '">';
	}

	public function testMessages() {
		echo "<dl>\n";
		foreach ($this->message as $key => $value)
			echo "<dt>$key</dt><dd>$value</dd>\n";
		echo "</dl>";
	}

}
?>
