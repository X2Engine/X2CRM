<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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
 * Environmentally-agnostic content/message feedback utility.
 *
 * In the scope of a web request, it will respond via JSON (i.e. for use in an API
 * or AJAX response action). In the scope of a console command, it will echo messages 
 * sequentially. This is very much a work in progress and will eventually take over 
 * most of the roles previously filled by ResponseBehavior (which will become a 
 * wrapper of sorts for this class).
 *
 * The goal of developing this utility class is to consolidate JSON/AJAX response 
 * code in the X2CRM installer, the X2CRM API, a growing number of other places in
 * X2CRM, and the X2CRM update key server (which was the initial inspiration for 
 * developing ResponseBehavior). There was enough functional redundancy in all those
 * places, and so I decided that enough was enough.
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ResponseUtil {

	// What we need here:
	// 
	// Pretty much all the functions and class properties that are particular to handling
	// requests, especially the error handlers.

	/**
	 * Indicates whether a response is already in progress
	 * @var bool
	 */
	private static $_responding = false;

	/**
	 * Shutdown code. 
	 *
	 * Can be set to, for instance, "Yii::app()->end();" for a graceful Yii 
	 * shutdown that saves/rotates logs and performs other useful/appropriate
	 * operations before terminating the PHP thread.
	 * @var string
	 */
	public static $shutdown = 'die();';

	/**
	 * Runs the shutdown code.
	 */
	public static function end() {
		eval(self::$shutdown);
	}

	/**
	 * Returns true or false based on whether or not the current thread of PHP
	 * is being run from the command line.
	 * @return bool
	 */
	public static function isCli(){
		return php_sapi_name() == 'cli';
	}

	/**
	 * Universal, web-agnostic response function.
	 *
	 * Responds with a JSON and closes the connection if used in a web request; 
	 * merely echoes the response message (but optionally exits) otherwise.
	 *
	 * @param type $message The message to respond with.
	 * @param bool $error Indicates that an error has occurred
	 * @param bool $fatal Shut down PHP thread after printing the message
	 * @param string $shutdown Optional shutdown expression to be evaluated; it must halt the current PHP process.
	 */
	public static function respond($message, $error = false, $fatal = false){
		self::$_responding = true;
		if(self::isCli()){ // Command line interface message
			echo $message;
			if($error && $fatal && !self::$_noHalt)
				self::end();
		} else { // One-off JSON response to client
			if(!extension_loaded('json')){
				echo '{"error":true,"message":"The JSON PHP extension is required, but this server lacks it."}';
				self::end();
			}
			$response = self::$_response;
			$response['message'] = $message;
			$response['error'] = $error;
			header("Content-type: application/json");
			echo json_encode($response);
			self::end();
		}
	}


}
