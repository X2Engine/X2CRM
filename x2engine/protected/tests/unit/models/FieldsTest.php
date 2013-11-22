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
 * Test case for the {@link Fields} model class.
 * @package X2CRM.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class FieldsTest extends CTestCase {
	
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

		// Multibyte support:
		$curSym = Yii::app()->locale->getCurrencySymbol('INR');
		$value = Fields::strToNumeric("($curSym"."9,888.77)",'currency',$curSym);
		$this->assertEquals(-9888.77,$value,'Failed asserting proper conversion of multibyte strings to numbers.');
	}
}

?>
