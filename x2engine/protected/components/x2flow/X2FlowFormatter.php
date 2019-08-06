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
 * @package application.components
 */
class X2FlowFormatter extends Formatter {

    /**
     * Parses a "short code" as a part of variable replacement.
     *
     * Short codes are defined in the file protected/components/x2flow/shortcodes.php
     * and are a list of manually defined pieces of code to be run in variable replacement.
     * Because they are stored in a protected directory, validation on allowed
     * functions is not performed, as it is the user's responsibility to edit this file.
     *
     * @param String $key The key of the short code to be used
     * @param X2Model $model The model having variables replaced, some short codes
     * use a model
     * @return mixed Returns the result of code evaluation if a short code
     * existed for the index $key, otherwise null
     */
    private static function parseShortCode($key, array $params) {
        if (isset($params['model'])) {
            $model = $params['model'];
        }
        $path = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'components', 'x2flow', 'shortcodes.php'));
        if (file_exists($path)) {
            $shortCodes = include(Yii::getCustomPath($path));
            if (isset($shortCodes[$key])) {
                return eval($shortCodes[$key]);
            }
        }
        return null;
    }

    /**
     * Parses text for short codes and returns an associative array of them.
     * Overrides parent method to add support for shortcodes, missing model param, and insertable
     * attributes referencing param names
     *
     * @param string $value The value to parse
     * @param X2Model $model The model on which to operate with attribute replacement
     * @param bool $renderFlag The render flag to pass to {@link X2Model::getAttribute()}
     * @param bool $makeLinks If the render flag is set, determines whether to render attributes
     *  as links
     */
    protected static function getReplacementTokens($value, array $params, $renderFlag, $makeLinks) {
        // Checks if model is included
        $model = isset($params['model']) ? $params['model'] : null;
        
        // Pattern will match {attr}, {attr1.attr2}, {attr1.attr2.attr3}, etc.
        $codes = array();
        // Types of each value for the short codes:
        $codeTypes = array();
        $fieldTypes = array_map(function($f) {
            return $f['phpType'];
        }, Fields::getFieldTypes());
        $fields = $model ? $model->getFields(true) : array();

        // check for variables
        $matches = array();
        preg_match_all('/{([a-z]\w*)(\.[a-z]\w*)*?}/i', trim($value), $matches);

        $isRenderException = function ($match) use ($fields) {
            return isset($fields[$match]) && $fields[$match]->fieldName === 'id';
        };

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $match = substr($match, 1, -1); // Remove the "{" and "}" characters

                $attr = $match;
                if (strpos($match, '.') !== false) {
                    // We found a link attribute (i.e. {company.name})
                    $newModel = $model;
                    $newModelFields = $fields;

                    $pieces = explode('.', $match);
                    $first = array_shift($pieces);

                    // First check if the first piece is part of a short code, like "user"
                    $tmpModel = self::parseShortCode(
                                    $first, array_merge($params, array('model' => $newModel)));

                    if (isset($tmpModel) && $tmpModel instanceof CActiveRecord) {
                        // If we got a model from our short code, use that
                        $newModel = $tmpModel;
                        // Also, set the attribute to have the first item removed.
                        $attr = implode('.', $pieces);
                        if ($newModel instanceof X2Model) {
                            $newModelFields = $newModel->getFields(true);
                        } else {
                            $newModelFields = array();
                        }
                    }

                    if ($newModel) {
                        $codes['{' . $match . '}'] = $newModel->getAttribute(
                                $attr, $isRenderException($match) ? true : $renderFlag, $makeLinks);
                        $codeTypes[$match] = isset($newModelFields[$attr]) && isset($fieldTypes[$newModelFields[$attr]->type]) ? $fieldTypes[$newModelFields[$attr]->type] : 'string';
                    }
                } else { // Standard attribute
                    // Check if value is location
                    if ($match === 'location') {
                        $user = Locations::getRecentUserLoginRecord();
                        $location = Locations::getRecentModelLocation(
                                        $model->id, /* get_class($model) */ "Contacts");
                        $string = $user->getTravelTime($location);
                        $link = $user->getDirectionsLink($location, $string);
                        $codes['{' . $match . '}'] = $link;
                        $codeTypes[$match] = gettype($string);
                    }
                    // check if we provided a value for this attribute
                    else if (isset($params[$match]) && is_scalar($params[$match])) {
                        $codes['{' . $match . '}'] = $params[$match];
                        $codeTypes[$match] = gettype($params[$match]);
                        // Next check if the attribute exists on the model
                    } elseif ($model && $model->hasAttribute($match)) {
                        $codes['{' . $match . '}'] = $model->getAttribute(
                                $match, $isRenderException($match) ? true : $renderFlag, $makeLinks);
                        $codeTypes[$match] = isset($fields[$match]) && isset($fieldTypes[$fields[$match]->type]) ? $fieldTypes[$fields[$match]->type] : 'string';
                    } else {
                        // Finally, try to parse it as a short code if nothing else worked
                        $shortCodeValue = self::parseShortCode($match, $params);
                        if (!is_null($shortCodeValue) && is_scalar($shortCodeValue)) {
                            $codes['{' . $match . '}'] = $shortCodeValue;
                            $codeTypes[$match] = gettype($shortCodeValue);
                        }
                    }
                }
            }
        }

        $codes = self::castReplacementTokenTypes($codes, $codeTypes);
        return $codes;
    }

}

?>
