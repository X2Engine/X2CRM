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




class StringUtil {

    /**
     * Like preg_replace but with option to have exception thrown if error occurs
     */
    public static function pregReplace (
        $pattern, $replacement, $subject, $limit = null, &$count = null, $throws=true) {

        // precondition check
        assert (!($limit === null && $count !== null));

        if ($count !== null) {
            $retVal = preg_replace ($pattern, $replacement, $subject, $limit, $count);
        } elseif ($limit !== null) {
            $retVal = preg_replace ($pattern, $replacement, $subject, $limit);
        } else {
            $retVal = preg_replace ($pattern, $replacement, $subject);
        }

        if ($throws && $retVal === null) {
            throw new StringUtilException(
            Yii::t('app', 'preg_replace error: {error}',
                    array(
                '{error}' => StringUtilException::getErrorMessage(preg_last_error())
            )), StringUtilException::PREG_REPLACE_ERROR);
        }
        return $retVal;
    }

    /**
     * Like preg_replace_callback but with option to have exception thrown if error occurs
     */
    public static function pregReplaceCallback (
        $pattern, $callback, $subject, $limit = null, &$count = null, $throws=true) {

        // precondition check
        assert (!($limit === null && $count !== null));

        if ($count !== null) {
            $retVal = preg_replace_callback ($pattern, $callback, $subject, $limit, $count);
        } elseif ($limit !== null) {
            $retVal = preg_replace_callback ($pattern, $callback, $subject, $limit);
        } else {
            $retVal = preg_replace_callback ($pattern, $callback, $subject);
        }
        if ($throws && $retVal === null) {
            throw new StringUtilException(
            Yii::t('app', 'preg_replace_callback error: {error}',
                    array(
                '{error}' => StringUtilException::getErrorMessage(preg_last_error())
            )), StringUtilException::PREG_REPLACE_CALLBACK_ERROR);
        }
        return $retVal;
    }

    /**
     * Like json_decode, but returns $subject if decoding fails 
     */
    public static function jsonDecode ($subject, $assoc=false, $depth=512) {
        $decoded = json_decode ($subject, $assoc, $depth);
        if ($decoded !== null) return $decoded;
        return $subject;
    }

    /**
     * @return bool true if string is json, false otherwise
     */
    public static function isJson ($string) {
        json_decode ($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}

class StringUtilException extends Exception {
    const PREG_REPLACE_ERROR = 1;
    const PREG_REPLACE_CALLBACK_ERROR = 2;

    /**
     * Convert PCRE error constant into an error message 
     */
    public static function getErrorMessage ($pcreConstant) {
        $definedConstants = get_defined_constants (true);
        $pcreConstantsByErrorCodes = array_flip ($definedConstants['pcre']);
        return isset ($pcreConstantsByErrorCodes[$pcreConstant]) ?
            $pcreConstantsByErrorCodes[$pcreConstant] : '';
    }
}

?>
