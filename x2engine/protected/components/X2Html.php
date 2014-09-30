<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

 class X2Html extends CHtml {

    /**
     * Safeguarded override of {@link CHtml::encode()}
     * 
     * Converts the text's encoding to avoid "invalid multibyte sequence" errors
     */
    public static function encode($text) {
        return parent::encode(Formatter::mbSanitize($text));
    }

    /**
     * Returns html for error, success, and notice flashes. 
     */
    public static function getFlashes () {
        if (Yii::app()->user->hasFlash('error')) {
            echo "<div class='flash-error'>";
            echo Yii::app()->user->getFlash('error');
            echo "</div>";
        }
        if (Yii::app()->user->hasFlash('notice')) {
            echo "<div class='flash-notice'>";
            echo Yii::app()->user->getFlash('notice');
            echo "</div>";
        }
        if (Yii::app()->user->hasFlash('success')) {
            echo "<div class='flash-success'>";
            echo Yii::app()->user->getFlash('success');
            echo "</div>";
        }
    }

    /**
     * Provides a way to add a '?' with a tooltip to show users how to use the app
     * 
     * @param type $text
     * @param type $superScript
     * @param type $id
     * @param type $brackets
     * @param type $encode
     * @return type
     */
    public static function hint(
        $text, $superScript = true, $id = null, $brackets = false, $encode = true){

        $text = Formatter::mbSanitize ($text);
        $htmlOptions = array(
            'class' => 'x2-hint x2-question-mark',
            'title' => $encode ? htmlentities($text, ENT_QUOTES, Yii::app()->charset) : $text,
        );
        if($id !== null){
            $htmlOptions['id'] = $id;
        }
        if($brackets){
            $mark = '[?]';
        }else{
            $mark = '?';
        }
        /*return parent::image (Yii::app()->theme->getBaseUrl ().'/images/hint_icon.png',
            $mark, $htmlOptions);*/
        return parent::tag($superScript ? 'sup' : 'span', $htmlOptions, $mark);
    }

    /**
     * Generates a settings button 
     * @param string $alt the image alt
     * @param array $htmlOptions options to be applied to the settings button
     * @return string 
     */
    public static function settingsButton ($alt='', $htmlOptions) {
        if (!isset ($htmlOptions['class'])) {
            $htmlOptions['class'] = '';
        }
        $htmlOptions['class'] .= ' x2-settings-button';

        return self::image(
            Yii::app()->theme->baseUrl.'/images/widgets.png', $alt, 
            $htmlOptions);
    }

}
