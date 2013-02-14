<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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


/**
 * Miscellaneous file system utilities. It is not a child class of CComponent or 
 * the like in order to be portable/stand-alone (i.e. so it can be used outside
 * the app by the installer).
 * 
 * @package X2CRM.components 
 */
class FileUtil {

	/**
	 * Copies a file. 
	 * 
	 * If the local filesystem directory to where the file will be copied does 
	 * not exist yet, it will be created automatically.
	 * 
	 * @param string $filepath The source file
	 * @param strint $file The destination path.
	 * @return boolean 
	 */
	public static function ccopy($filepath, $file) {

		$pieces = explode('/', $file);
		unset($pieces[count($pieces)]);
		for ($i = 0; $i < count($pieces); $i++) {
			$str = "";
			for ($j = 0; $j < $i; $j++) {
				$str.=$pieces[$j] . '/';
			}

			if (!is_dir($str) && $str != "") {
				mkdir($str);
			}
		}
		return copy($filepath, $file);
	}

	/**
	 * Format a path so that it is platform-independent. Doesn't return false
	 * if the path doesn't exist (so unlike realpath() it can be used to create 
	 * new filess).
	 * 
	 * @param string $path
	 * @return string 
	 */
	public static function rpath($path) {
		return implode(DIRECTORY_SEPARATOR, explode('/', $path));
	}

	/**
	 * Recursively remove a directory.
	 * 
	 * Walks a directory structure, removing files recursively. An optional
	 * exclusion pattern can be included. If a directory contains a file that
	 * matches the exclusion pattern, the directory and its ancestors will not 
	 * be deleted.
	 * 
	 * @param string $path 
	 * @param string $noDelPat PCRE pattern for excluding files in deletion.
	 */
	public static function rrmdir($path, $noDelPat=null) {
		$useExclude = $noDelPat != null;
		$special = '/.*\/?\.+\/?$/';
		$excluded = false;
		if(!realpath($path))
			return false;
		$path = realpath($path);
		if (filetype($path)=='dir') {
			$objects = scandir($path);
			foreach ($objects as $object) {
				if (!preg_match($special, $object)) {
					if ($useExclude) {
						if (!preg_match($noDelPat, $object)) {
							$excludeThis = self::rrmdir($path . DIRECTORY_SEPARATOR . $object, $noDelPat);
							$excluded = $excluded || $excludeThis;
						} else {
							$excluded = true;
						}	
					} else
						self::rrmdir($path . DIRECTORY_SEPARATOR . $object, $noDelPat);
				}
			}
			reset($objects);
			if (!$excluded)
				if(!preg_match($special, $path))
					rmdir($path);
		} else
			unlink($path);
		return $excluded;
	}

}

?>
