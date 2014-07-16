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

class X2ActiveForm extends CActiveForm {

    /**
     * Overrides parent method to add custom select class 
     */
    public function dropDownList($model,$attribute,$data,$htmlOptions=array())
    {
        /* x2modstart */    
        if (isset ($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' x2-select';
        } else {
            $htmlOptions['class'] = 'x2-select';
        }
        /* x2modend */ 
        return CHtml::activeDropDownList($model,$attribute,$data,$htmlOptions);
    }

    public function richTextArea (CModel $model, $attribute, array $htmlOptions=array ()) {
		Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/emailEditor.js', CClientScript::POS_END);
		Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js', CClientScript::POS_END);
		Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js', CClientScript::POS_END);

        if (isset ($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' x2-rich-textarea';
        } else {
            $htmlOptions['class'] = 'x2-rich-textarea';
        }

        if (!isset ($htmlOptions['width'])) {
            $htmlOptions['width'] = '725px';
        }
        if (!isset ($htmlOptions['height'])) {
            $htmlOptions['height'] = '125px';
        }

        return $this->textArea ($model, $attribute, $htmlOptions);
    }

}
