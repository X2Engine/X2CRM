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
