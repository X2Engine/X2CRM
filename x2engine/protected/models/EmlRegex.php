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
 * Class for handling patterns in email bodies and headers.
 * 
 * The primary purpose of this class is to facilitate flexible storage of 
 * patterns specific to each email client. To support more clients, more entries
 * in the x2_email_patterns should be used. Each pattern is assumed to be atomic
 * (i.e. a quantifier could be placed after it and it would be applied to the 
 * whole thing). The group names for name and address must match the patterns 
 * [^_]+_name and [^_]+_address, respectively, but the part preceding "_" must
 * be unique for regexp compilation to succeed properly. Note that keys of the
 * $patterns array will be replaced with values of that array.
 * 
 * @package application.models
 */
class EmlRegex extends CActiveRecord {

	public function tableName() {
		return 'x2_forwarded_email_patterns';
	}
	
	public static $authorEmail = 'customersupport@x2engine.com';
	
	/**
	 * Commonly used patterns.
	 * 
	 * Patterns that show up often are abbreviated as the following keywords.
	 */
	public static $patterns = array(
		'{month}' => '(?:Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|June?|July?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)',
		'{day}' => '(?:(?:Sun|Mon|Tues?|Wed(?:nes)?|Thu(?:rs)?|Fri|Sat(?:ur)?)(?:day)?)',
		'{time}' => '\d{1,2}:?\d{1,2}[\s\n]+(?:AM|PM)',
		'{calDate}' => '\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4,}',
		'{wrote}' => '(?:[\w ]+)[\s\n]+(?:\<([^\>]+@[^\>]+)\>)?[\s\n]*wrote',
		'{romanNum}' => '(?:(?:[MDCLXVI])M*(?:C[MD]|D?C{0,3})(?:X[CL]|L?X{0,3})(?:I[XV]|V?I{0,3}))',
		'{s}' => '(?:[\s\n]|\=20\n?|\=\n?)', // Whitespace, carriage returns and linefeeds in multiline mode
		'{name}' => '[\w\s\.\-\',]',
		'{field}' => '(?:Return-Path|X-Original-To|Delivered-To|Received|To|CC|Message-ID|Date|From|Subject|Content-Type|Content-Transfer-Encoding|Content-Language|Thread-Topic|Thread-Index|References|Accept-Language|X-MS-Has-Attach|X-MS-TNEF-Correlator|x-originating-ip|MIME-Version|Sent)',
        '{emailAddr}' => null // to be filled later, deferring to CEmailValidator
	);

	/**
	 * Patterns that use the above patterns.
	 * 
	 * @var array 
	 */
	public static $metaPatterns = array(
		'{fullField}' => '^{field}:[^\n]*',
		'{junkFields}' => '(?:^>?\s*{field}:[^\n]+{s}*)',
                '{formattedName}' => '\"?({name}*)\"?'
	);

	/**
	 * Name title prefixes and suffixes.
	 * @var array
	 */
	public static $titles = array(
		/* prefixes */ '/^\s*(?:Judge|Officer|RN|MT\/CLS|AICP|Attorney|Physician|Doctor|Accountant|ACMA|CA|CPA|CIA|CGA|CMA|CFM|CFP|CFE|CFA|MAcy|MBA|Advocate|Ambassador|Bailiff|Barrister|Coach|Esquire|Mrs\.?|Mr\.?|Ms\.?|Prof\.?|Dr\.?|Gen\.?|Rep\.?|Sen\.?|St\.?)\s+/i',
		/* suffixes */ '/,?\s+(?:{romanNum}|AA|AAS|AS|BArch|BBA|BDS|BChD|BDes|BD|BDiv|BEd|BEng|BFA|LLB|MB|ChB|MB|BS|BM|BCh|MB|BChir|BMus|BPhil|STB|BSc|BSN|BTh|ThB|BVSc|Dean Emeritus|Dz|DA|DBA|D\.?D\.?|Ed\.?D\.?|EngD|DEng|DFA|DMA|D\.?Min\.?|D\.?Mus\.?|D\.?Prof|DPA|D\.?Sc\.?|JD|LL\.?D\.?|Pharm\.?D\.?|Ph\.?D\.?|D\.?Phil\.?|PsyD|Th\.?D\.?|DC|D\.?O\.?|DMD|O\.?D\.?|DPT|DPM|DVM|MArch|MAL|MBA|MPA|MPS|MPl|MChem|MC|M\.? Des|MDiv|MEd|MEng|MFA|MHA|LL\.?M|MLA|MMath|MPhil|MRes|MSc|MScBMC|MPhys|MPharm|MSE|MSRE|MSW|Magister|S\.?T\.?M\.?|ThM|Sr\.?|Jr\.?|Ph\.?D\.?|M\.?D\.?|B\.?A\.?|M\.?A\.?|D\.?D\.?S\.?)\s*$/i',
	);

	
	public static $_fieldPattern;
	
	public $_fwHeader;
	public static $_fwHeaders;
	public static $_titlesC; // Compiled regex

    public function __construct($scenario = 'insert'){
        if(empty(self::$patterns['{emailAddr}'])) {
            $ev = new CEmailValidator;
            self::$patterns['{emailAddr}'] = '(?:'.trim($ev->pattern,'/^$').')';
        }
        parent::__construct($scenario);
    }

	public function attributeLabels() {
		return array(
			'groupName' => 'Regular expressions group name prefix',
			'pattern' => 'Pattern code',
			'bodyFrom' => 'Original email',
			'description' => 'Description'
		);
	}
	
	/**
	 * Pattern for matching RFC822 email header fields (and EOL)
	 * 
	 * @return string
	 */
	public static function fieldPattern() {
		if(!isset(self::$_fieldPattern))
			self::$_fieldPattern = self::insertPatterns(self::$metaPatterns['{fullField}']);
		return self::$_fieldPattern;
	}
	
	/**
	 * Full name as an array with two elements, first and last.
	 * 
	 * Given a name field of an email, parse the first and last name out of it.
	 * 
	 * @param str $name The full name
	 */
	public static function fullName($name) {
            $name = str_replace('"', '', $name);
//		$fullName = explode(' ', preg_replace(self::getTitleRe(), array('', ''), $name));
            // Begin by stripping out titles and salutations:
            $fullName = preg_replace(self::getTitleRe(), array('', ''), $name);
            // Treat the special case first:
            if (preg_match('/([^,]+),\s*([^,]+)/', $fullName, $lastFirst))
                    $fullName = array_reverse(array_slice($lastFirst, 1));
            else {
                    $fullName = explode(' ', $fullName);
                    // Assume, for longer multi-part names, that everything past the 
                    // first part is the last name. Let the assignee and/or web 
                    // admin deal with correcting it later.
                    if (isset($fullName[2]))
                            for ($i = 2; $i < count($fullName); $i++)
                                    $fullName[1].= ' ' . $fullName[$i];
            }
            // No last name?
            if (!isset($fullName[1]))
                    $fullName[] = "UnknownLastName";
            return array_slice($fullName, 0, 2);
	}

	/**
	 * Compose the consolidated pattern for matching forwarded message headers
	 * 
	 * Looks in the database for available message header patterns and puts them
	 * together in an omnibus pattern to match any supported email.
	 * 
	 * @return str
	 */
	public static function getAllFwHeaders() {
		if(!isset(self::$_fwHeaders)) {
			$fwREs = X2Model::model(__CLASS__)->findAll();
			self::$_fwHeaders = '/^(?:' . self::insertPatterns(implode('|', array_map(function($r){return $r->pattern;},$fwREs))) . ')/im';
		}
		return self::$_fwHeaders;
	}
	/**
	 * Compile and return the title regexp 
	 */
	public static function getTitleRE() {
		if(!isset(self::$_titlesC)) {
			self::$_titlesC = array();
			foreach(self::$titles as $titleRE)
				self::$_titlesC[] = self::insertPatterns($titleRE);
		}
		return self::$_titlesC;
	}
	
	/**
	 * Magic getter method for the regular expression used to search for forwarded messages
	 * 
	 * @return string
	 */
	public function getFwHeader() {
		if(!isset($this->_fwHeader))
			$this->_fwHeader = self::insertPatterns($this->pattern);
		return $this->_fwHeader;
	}
		
	/**
	 * Replace all shortcuts in a string with their full pattern.
	 * 
	 * @param type $string
	 * @return type 
	 */
	public static function insertPatterns($string) {
		return strtr(strtr($string, self::$metaPatterns), self::$patterns);
	}
	
	/**
	 * Search for a forwarded message header in an email body
	 * 
	 * @param string $body Email body in which to search for a forwarded message header
	 * @return (boolean|array) Array of matches if regex matched the body, false if not
	 */
	public function matchHeader($body) {
		if(preg_match('/(?<'.$this->groupName.'_junk>'.$this->getFwHeader().')/im',$body,$matches))
			return $matches;
		else
			return false;
	}

	public function rules() {
		return array(
			array('id', 'numeric', 'allowEmpty' => false),
			array('custom', 'boolean'),
			array('groupName', 'length', 'min' => 1, 'max' => 20),
			array('groupName', 'required', 'strict' => true),
			array('groupName', 'match', 'pattern' => '^[A-Za-z0-9]$', 'message' => 'must consist only of alphanumeric characters'),
		);
	}

}

?>
