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

/**
 * Consolidated class for common string formatting and parsing functions.
 *
 * @package X2CRM.components
 */
class Formatter {

    /**
     * Converts a record's Description or Background Info to deal with the discrepancy
     * between MySQL/PHP line breaks and HTML line breaks.
     */
    public static function convertLineBreaks($text, $allowDouble = true, $allowUnlimited = false){

        if(preg_match("/<br \/>/", $text)){
            $text = preg_replace("/<\/b>/", "</b><br />", $text, 1);
            $text = preg_replace("/\s<b>/", "<br /><b>", $text, 1);
            return $text;
        }

        $text = mb_ereg_replace("\r\n", "\n", $text);  //convert microsoft's stupid CRLF to just LF

        if(!$allowUnlimited)
            $text = mb_ereg_replace("[\r\n]{3,}", "\n\n", $text); // replaces 2 or more CR/LF chars with just 2

        if($allowDouble)
            $text = mb_ereg_replace("[\r\n]", '<br />', $text); // replaces all remaining CR/LF chars with <br />
        else
            $text = mb_ereg_replace("[\r\n]+", '<br />', $text);

        return $text;
    }

    /*     * * Date Format Functions ** */

    /**
     * A function to convert a timestamp into a string stated how long ago an object
     * was created.
     *
     * @param $timestamp The time that the object was posted.
     * @return String How long ago the object was posted.
     */
    public static function timestampAge($timestamp){
        $age = time() - strtotime($timestamp);
        //return $age;
        if($age < 60)
            return Yii::t('app', 'Just now'); // less than 1 min ago
        if($age < 3600)
            return Yii::t('app', '{n} minutes ago', array('{n}' => floor($age / 60))); // minutes (less than an hour ago)
        if($age < 86400)
            return Yii::t('app', '{n} hours ago', array('{n}' => floor($age / 3600))); // hours (less than a day ago)

        return Yii::t('app', '{n} days ago', array('{n}' => floor($age / 86400))); // days (more than a day ago)
    }

    /**
     * Format a date to be long (September 25, 2011)
     * @param integer $timestamp Unix time stamp
     */
    public static function formatLongDate($timestamp){
        if(empty($timestamp))
            return '';
        else
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $timestamp);
    }

    /**
     * Format dates for the date picker.
     * @param string $width A length keyword, i.e. "medium"
     * @return string
     */
    public static function formatDatePicker($width = ''){
        if(Yii::app()->locale->getId() == 'en'){
            if($width == 'medium')
                return "M d, yy";
            else
                return "MM d, yy";
        } else{
            $format = Yii::app()->locale->getDateFormat('short'); // translate Yii date format to jquery
            $format = str_replace('yy', 'y', $format);
            $format = str_replace('MM', 'mm', $format);
            $format = str_replace('M', 'm', $format);
            return $format;
        }
    }

    /**
     * Formats time for the time picker.
     *
     * @param string $width
     * @return string
     */
    public static function formatTimePicker($width = ''){
        if(Yii::app()->locale->getLanguageId(Yii::app()->locale->getId()) == 'zh'){
            return "HH:mm";
        }
        $format = Yii::app()->locale->getTimeFormat('short');
        //$format = strtolower($format); // jquery specifies hours/minutes as hh/mm instead of HH//MM
        $format = str_replace('a', 'TT', $format); // yii and jquery have different format to specify am/pm
        return $format;
    }

    /**
     * Check if am/pm is being used in this locale.
     */
    public static function formatAMPM(){
        if(strstr(Yii::app()->locale->getTimeFormat(), "a") === false)
            return false;
        else if(Yii::app()->locale->getLanguageId(Yii::app()->locale->getId()) == 'zh') // 24 hour format for china
            return false;
        else
            return true;
    }

    /*     * * Date Time Format Functions ** */

    public static function formatFeedTimestamp($timestamp){
        if(Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium'), $timestamp) == Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium'), time())){
            $str = Yii::t('app', 'Today').' '.Yii::app()->dateFormatter->format(Yii::app()->locale->getTimeFormat('short'), $timestamp);
        }else{
            $str = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium'), $timestamp)." ".Yii::app()->dateFormatter->format(Yii::app()->locale->getTimeFormat('short'), $timestamp);
        }
        return $str;
    }

    /**
     * Returns a formatted string for the end of the day.
     * @param integer $timestamp
     * @return string
     */
    public static function formatDateEndOfDay($timestamp){
        if(empty($timestamp))
            return '';
        else
        if(Yii::app()->locale->getId() == 'en')
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium').' '.Yii::app()->locale->getTimeFormat('short'), strtotime("tomorrow", $timestamp) - 60);
        else if(Yii::app()->locale->getLanguageId(Yii::app()->locale->getId()) == 'zh')
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short').' '.'HH:mm', strtotime("tomorrow", $timestamp) - 60);
        else
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short').' '.Yii::app()->locale->getTimeFormat('short'), strtotime("tomorrow", $timestamp) - 60);
    }

    /**
     * Cuts string short.
     * @param string $str String to be truncated.
     * @param integer $length Maximum length of the string
     * @return string
     */
    public static function truncateText($str, $length = 30){

        if(mb_strlen($str, 'UTF-8') > $length - 3){
            if($length < 3)
                $str = '';
            else
                $str = trim(mb_substr($str, 0, $length - 3, 'UTF-8'));
            $str .= '...';
        }
        return $str;
    }

    /**
     * Converts CamelCased words into first-letter-capitalized, spaced words.
     * @param type $str
     * @return type
     */
    public static function deCamelCase($str){
        $str = preg_replace("/(([a-z])([A-Z])|([A-Z])([A-Z][a-z]))/", "\\2\\4 \\3\\5", $str);
        return ucfirst($str);
    }

    /**
     * Locale-dependent date string formatting.
     * @param integer $date Timestamp
     * @param string $width A length keyword, i.e. "medium"
     * @return string
     */
    public static function formatDate($date, $width = 'long', $informal = true){
        if(empty($date)){
            return '';
        }
        if(!is_numeric($date))
            $date = strtotime($date); // make sure $date is a proper timestamp

        $now = getDate();   // generate date arrays
        $due = getDate($date); // for calculations
        //$date = mktime(23,59,59,$due['mon'],$due['mday'],$due['year']);	// give them until 11:59 PM to finish the action
        //$due = getDate($date);
        $ret = '';

        if($informal && $due['year'] == $now['year']){  // is the due date this year?
            if($due['yday'] == $now['yday'] && $width == 'long')  // is the due date today?
                $ret = Yii::t('app', 'Today');
            else if($due['yday'] == $now['yday'] + 1 && $width == 'long') // is it tomorrow?
                $ret = Yii::t('app', 'Tomorrow');
            else
                $ret = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat($width), $date); // any other day this year
        } else{
            $ret = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat($width), $date); // due date is after this year
        }
        return $ret;
    }

    public static function formatTime($date, $width = 'medium'){
        return Yii::app()->dateFormatter->formatDateTime($date, null, $width);
    }

    public static function formatDueDate($date){
        if(!is_numeric($date))
            $date = strtotime($date); // make sure $date is a proper timestamp
        return date('l', $date)." ".Yii::app()->dateFormatter->formatDateTime($date, 'long', null)." - ".Yii::app()->dateFormatter->formatDateTime($date, null, 'short');
    }

    public static function formatCompleteDate($date){
        return Yii::app()->dateFormatter->formatDateTime($date, 'long');
    }

    /**
     * Returns a formatted string for the date.
     *
     * @param integer $timestamp
     * @return string
     */
    public static function formatLongDateTime($timestamp){
        if(empty($timestamp))
            return '';
        else
            return Yii::app()->dateFormatter->formatDateTime($timestamp, 'long', 'medium');
    }

    /**
     * Formats the date and time for a given timestamp.
     * @param type $timestamp
     * @return string
     */
    public static function formatDateTime($timestamp){
        if(empty($timestamp))
            return '';
        else
        if(Yii::app()->locale->getId() == 'en')
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium').' '.Yii::app()->locale->getTimeFormat('short'), $timestamp);
        else if(Yii::app()->locale->getLanguageId(Yii::app()->locale->getId()) == 'zh')
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short').' '.'HH:mm', $timestamp);
        else
            return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short').' '.Yii::app()->locale->getTimeFormat('short'), $timestamp);
    }

    /**
     * Obtain a Unix-style integer timestamp for a date format.
     *
     * @param string $date
     * @return integer
     */
    public static function parseDate($date){
        if(Yii::app()->locale->getId() == 'en')
            return strtotime($date);
        else
            return CDateTimeParser::parse($date, Yii::app()->locale->getDateFormat('short'));
    }

    /**
     * Parses both date and time into a Unix-style integer timestamp.
     * @param string $date
     * @return integer
     */
    public static function parseDateTime($date){
        if($date === null)
            return null;
        elseif(is_numeric($date))
            return $date;
        elseif(Yii::app()->locale->getId() == 'en')
            return strtotime($date);
        else
            return CDateTimeParser::parse($date, Yii::app()->locale->getDateFormat('short').' hh:mm');
    }

    /**
     * Convert currency to the proper format
     *
     * @param String $str The currency string
     * @param Boolean $keepCents Whether or not to keep the cents
     * @return String $str The modified currency string.
     */
    public static function parseCurrency($str, $keepCents){

        $cents = '';
        if($keepCents){
            $str = mb_ereg_match('[\.,]([0-9]{2})$', $str, $matches); // get cents
            $cents = $matches[1];
        }
        $str = mb_ereg_replace('[\.,][0-9]{2}$', '', $str); // remove cents
        $str = mb_ereg_replace('[^0-9]', '', $str);  //remove all non-numbers

        if(!empty($cents))
            $str .= ".$cents";

        return $str;
    }

    /**
     * Returns the body of an email without any HTML markup.
     *
     * This function will strip out email header tags, opened email tags, and all
     * HTML markup present in an Email type action so that the Action link can be
     * properly displayed without looking terrible
     * @param String $str Input string to be formatted
     * @return String The formatted string
     */
    public static function parseEmail($str){
        $str = preg_replace('/<\!--BeginOpenedEmail-->(.*?)<\!--EndOpenedEmail--!>/s', '', $str);
        $str = preg_replace('/<\!--BeginActionHeader-->(.*?)<\!--EndActionHeader--!>/s', '', $str);
        $str = strip_tags($str);
        return $str;
    }

    public static function replaceVariables($value, $model, $type = '', $params = array()){
        $matches = array();
        if($type === '' || $type === 'text' || $type === 'richtext'){
            $renderFlag = true;
        }else{
            $renderFlag = false;
        }
        preg_match_all('/{([a-z]\w*)(\.[a-z]\w*)*?}/i', trim($value), $matches); // check for variables
        if(isset($matches[0])){
            foreach($matches[0] as $match){
                $match = substr($match, 1, -1);
                if(strpos($match, '.') !== false){
                    $value = preg_replace('/{'.$match.'}/i', $model->getAttribute($match, $renderFlag), $value);
                }else{
                    if(isset($params[$match])){
                        $value = $params[$match]; // don't return
                    }elseif($model->hasAttribute($match)){
                        $value = preg_replace('/{'.$match.'}/i', $model->getAttribute($match, $renderFlag), $value);
                    }else{
                        $shortCodeValue = Formatter::parseShortCode($match, $model);
                        if(!is_null($shortCodeValue)){
                            $value = preg_replace('/{'.$match.'}/i', $shortCodeValue, $value);
                        }
                    }
                }
            }
            return $value;
        }
    }

    public static function parseFormula($formula, $type = '', $params = array()){
        $formula = substr($formula, 1);
        if(isset($params['model'])){
            $formula = Formatter::replaceVariables($formula, $params['model'], 'formula', $params);
        }
        if(strpos($formula, ';') !== strlen($formula) - 1){
            $formula.=';';
        }
        if(strpos($formula, 'return ') !== 0){
            $formula = 'return '.$formula;
        }
        $formula = preg_replace(array(
            '!/\*.*?\*/!s',
            '/\n\s*\n/',
            '/(\S*)\w(?<!'.self::getSafeWords().')(\s*)\((.*?)\)/'
            ),array(
                '',
                "\n",
                'null'
            ),$formula);
        try{
            return eval($formula);
        }catch(Exception $e){

        }
    }

    public static function parseShortCode($key, $model){
        $shortCodes = include('protected/components/x2flow/shortcodes.php');
        if(isset($shortCodes[$key])){
            return eval($shortCodes[$key]);
        }else{
            return null;
        }
    }

    private static function getSafeWords(){
        function encapsulateWords($word){
            return "\s".$word;
        }
        $safeWords = array(
            'echo',
            'time',
            'return',
        );
        $safeWords = array_map('encapsulateWords',$safeWords);
        return implode('|',$safeWords);
    }

}

?>
