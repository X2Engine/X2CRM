<?php
/*********************************************************************************
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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 ********************************************************************************/

/**
 * Input form element that provides a fontface selection dropdown menu.
 * 
 * @package X2CRM.components 
 */
class FontPickerInput extends CFormElement {
	//http://www.w3schools.com/cssref/css_websafe_fonts.asp
	protected static $fontsCss = array(
		'arial'=>'Arial, Helvetica, sans-serif',
		'arialblack'=>'"Arial Black", Gadget, sans-serif',
		'comicsans'=>'"Comic Sans MS", cursive, sans-serif',
		'courier'=>'"Courier New", Courier, monospace',
		'georgia'=>'Georgia, serif',
		'impact'=>'Impact, Charcoal, sans-serif',
		'lucidaconsole'=>'"Lucida Console", Monaco, monospace',
		'lucidasans'=>'"Lucida Sans Unicode", "Lucida Grande", sans-serif',
		'palatino'=>'"Palatino Linotype", "Book Antiqua", Palatino, serif',
		'tahoma'=>'Tahoma, Geneva, sans-serif',
		'times'=>'"Times New Roman", Times, serif',
		'trebuchet'=>'"Trebuchet MS", Helvetica, sans-serif',
		'verdana'=>'Verdana, Geneva, sans-serif',
	);

	protected static $fontsDisplay = array(
		''=>'',
		'arial'=>'Arial, Helvetica',
		'arialblack'=>'Arial Black, Gadget',
		'comicsans'=>'Comic Sans MS',
		'courier'=>'Courier New, Courier',
		'georgia'=>'Georgia',
		'impact'=>'Impact, Charcoal',
		'lucidaconsole'=>'Lucida Console, Monaco',
		'lucidasans'=>'Lucida Sans Unicode, Lucida Grande',
		'palatino'=>'Palatino Linotype, Book Antiqua',
		'tahoma'=>'Tahoma, Geneva',
		'times'=>'Times New Roman',
		'trebuchet'=>'Trebuchet MS, Helvetica',
		'verdana'=>'Verdana, Geneva',
	);

	public static function getFontCss($key) {
		return isset(self::$fontsCss[$key]) ? self::$fontsCss[$key] : self::$fontsCss['arial'];
	}

	public static function getFontsCss() {
		return self::$fontsCss;
	}

	public static function getFontDisplay($key) {
		return isset(self::$fontsDisplay[$key]) ? self::$fontsDisplay[$key] : self::$fontsDisplay['arial'];
	}

	public static function getFontsDisplay() {
		return self::$fontsDisplay;
	}

	public function __construct($config, $parent=null) {
		if ($parent == null) $parent = Yii::app()->controller;
		parent::__construct($config, $parent);
	}

	public function render() {
		try { $name = $this->name; } catch (Exception $e) { $name = ''; }
		try { $value = $this->value; } catch (Exception $e) { $value = ''; }
		return CHtml::dropDownList($name, $value, $this->fontsDisplay, $this->attributes);
	}
}
