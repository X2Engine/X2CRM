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
class FieldsTest extends X2DbTestCase {
	
//	public $fixtures = array(
//		'opportunities' => 'Opportunity'
//	);
	
	public function testStrToNumeric() {
		
		$cur =  Yii::app()->locale->getCurrencySymbol(Yii::app()->params->admin->currency);
		$input = " $cur 123.45 % ";
		$this->assertEquals(123.45,Fields::strToNumeric($input,'currency'));
		$this->assertEquals(123,Fields::strToNumeric($input,'int'));
		$this->assertEquals(123.45,Fields::strToNumeric($input,'float'));
		$this->assertEquals(123.45,Fields::strToNumeric($input,'percentage'));
		$this->assertEquals(0,Fields::strToNumeric(null,'float'));
		$type = 'notanint';
		$value = Fields::strToNumeric($input, $type);
		$this->assertEquals(123.45, $value);

		// Randumb string comes back as itself
		$input = 'cockadoodledoo';
		$value = Fields::strToNumeric($input,'int');
		$this->assertEquals($input,$value);

		// Null always evaluates to zero
		$value = Fields::strToNumeric('');
		$this->assertEquals(0,$value);

		// Parsing of parenthesized notation for negative currency values
		$value = Fields::strToNumeric('($45.82)','currency');
		$this->assertEquals(-45.82,$value);

		// Negative percentage values:
		$value = Fields::strToNumeric('-12.5%','percentage');
		$this->assertEquals(-12.5,$value);

		// Comma notation for thousands:
		$value = Fields::strToNumeric('$9,888.77','currency');
		$this->assertEquals(9888.77,$value);
		// Comma plus parentheses notation
		$value = Fields::strToNumeric('($9,888.77)','currency');
		$this->assertEquals(-9888.77,$value);
		// Comma and minus sign notation:
		$value = Fields::strToNumeric('-$9,888.77','currency');
		$this->assertEquals(-9888.77,$value);
		// Rounded to integer, over 10^6:
		$value = Fields::strToNumeric('$10,000,000','currency');
		$this->assertEquals(10000000,$value);
		// ...negative
		$value = Fields::strToNumeric('($10,000,000)','currency');
		$this->assertEquals(-10000000,$value);
		// ...with decimal places
		$value = Fields::strToNumeric('($10,000,000.01)','currency');
		$this->assertEquals(-10000000.01,$value);
	}
}

?>
