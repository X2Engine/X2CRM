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
