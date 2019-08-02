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
 * Adds HTML templating mechanism to owner widget.
 */

class WidgetTemplateBehavior extends CBehavior {

    public $template = '';

    /**
     * Renders widget components by parsing the template string and calling rendering methods
     * for each template item. HTML contained in the template gets echoed out.
     */
    public function renderTemplate () {

        // extract html strings and template strings from template
        $itemMatches = array ();
        $htmlMatches = array ();
        // TODO: rename template property _template and add getTemplate method to base class
        if (method_exists ($this->owner, 'getTemplate')) {
            $template = $this->owner->getTemplate ();
        } else {
            $template = $this->owner->template;
        }
        preg_match_all ("/(?:\}|^)([^{]+)(?:\{|$)/", $template, 
            $htmlMatches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        preg_match_all ("/{([^}]+)}/", $template, $itemMatches,
            PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        $templateHTML = array ();
        $templateItems = array ();
       //AuxLib::debugLogR ('$template = ');
        //AuxLib::debugLogR ($template);


        // organize html string matches into a 2d array
        for ($i = 1; $i < sizeof ($htmlMatches); ++$i) {
            for ($j = 0; $j < sizeof ($htmlMatches[$i]); ++$j) {
                if (is_array ($htmlMatches[$i][$j]) && $htmlMatches[$i][$j][1] >= 0) {
                    $templateHTML[] = array_merge ($htmlMatches[$i][$j], array ('html'));
                }
            }
        }

        // organize template string matches into a 2d array
        for ($i = 0; $i < sizeof ($itemMatches[1]); ++$i) {
            if (is_array ($itemMatches[1][$i]) && $itemMatches[1][$i][1] >= 0) {
                $templateItems[] = array_merge ($itemMatches[1][$i], array ('item'));
            }
        }
        //AuxLib::debugLogR ($templateItems);
       //AuxLib::debugLogR ('$templateHTML = ');
        //AuxLib::debugLogR ($templateHTML);

        // merge the 2 arrays and sort them by string offset
        $allTemplateItems = array_merge ($templateItems, $templateHTML);
        usort ($allTemplateItems, array ('self', 'compareOffset'));

        //AuxLib::debugLogR ($allTemplateItems);

        // echo html, call functions corresponding to template items
        $output = '';
        for ($i = 0; $i < sizeof ($allTemplateItems); ++$i) {
            if ($allTemplateItems[$i][2] == 'html') {
                $output .= $allTemplateItems[$i][0];
            } else { // $allTemplateItems[$i][2] === 'item'
                $fnName = 'render' . ucfirst ($allTemplateItems[$i][0]); 
                if (method_exists ($this->owner, $fnName)) {
                    $output .= $this->owner->$fnName ();
                }
            }
        }
        return $output;
    }

    private static function compareOffset ($a, $b) {
        return $a[1] > $b[1];
    }

}

?>
