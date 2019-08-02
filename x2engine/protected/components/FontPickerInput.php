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
 * Input form element that provides a fontface selection dropdown menu.
 * 
 * @package application.components 
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
