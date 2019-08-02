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




$namespacedId = $this->htmlOptions['id'];

Yii::app()->clientScript->registerScript('multiTypeAutocompleteJS'.$this->namespace,"

$(function () {
    var container$ = $('#".$namespacedId."');
    var excludeList = ".CJSON::encode ($this->staticOptions).";

    container$.find ('select').change (function () {
        var autocomplete$ = container$.find ('.record-name-autocomplete');
        var modelType = container$.find ('select').val ();
        if ($.inArray (modelType, excludeList) >= 0) {
            container$.find ('input').css ('visibility', 'hidden')
            return;
        }
        var throbber$ = x2.forms.inputLoading (container$.find ('.record-name-autocomplete'));
        $.ajax ({
            type: 'GET',
            url: '".Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete')."',
            data: {
                modelType: modelType
            },
            success: function (data) {
                if (data === 'failure') {
                    autocomplete$.attr ('disabled', 'disabled');
                    autocomplete$.siblings ('label').hide ();
                } else {
                    autocomplete$.siblings ('label').show ();
                    // remove span element used by jQuery widget
                    container$.find ('input').
                        first ().next ('span').remove ();

                    // replace old autocomplete with the new one
                    container$.find ('input').first ().replaceWith (data); 
         
                }
                // remove the loading gif
                throbber$.remove ();
            }
        });
    });
});

", CClientScript::POS_END);

echo CHtml::openTag ('div', X2Html::mergeHtmlOptions (array (
    'class' => "multi-type-autocomplete-container",
    'id' => $namespacedId,
), $this->htmlOptions));
    if (isset ($this->model)) {
        echo CHtml::activeDropDownList (
            $this->model, $this->selectName, $this->options, 
            array (
                'class' => '',
            ));
    } else {
        echo CHtml::dropDownList (
            $this->selectName, $this->selectValue, $this->options, 
            array (
                'class' => 'x2-select type-select',
            ));
    }

    $htmlOptions = array ();
    if (isset ($this->model)) {
        echo CHtml::activeLabel ($this->model, $this->autocompleteName, array (
            'style' => $this->selectValue === 'calendar' ? 'display: none;' : ''
        ));
    }

    if (isset ($this->autocompleteName)) {
        $htmlOptions['name'] = isset ($this->model) ? 
            CHtml::resolveName ($this->model, $this->autocompleteName) : 
            $this->autocompleteName;
    }
    if ($this->selectValue === 'calendar') {
        $htmlOptions['disabled'] = 'disabled';
        $htmlOptions['style'] = 'display: none;';
        $this->selectValue = 'Contacts';
    }


    if (!in_array ($this->selectValue, $this->staticOptions)) {
        X2Model::renderModelAutocomplete (
            $this->selectValue, false, $htmlOptions, 
            $this->autocompleteValue);
    } else {
        ?>
        <input class="record-name-autocomplete" type="hidden"></input>
        <?php
    }
    if (isset ($this->model)) {
        echo CHtml::activeHiddenField (
            $this->model,
            $this->hiddenInputName, 
            array (
                'class' => 'hidden-id',
            ));
    } else {
        echo CHtml::hiddenField (
            $this->hiddenInputName, 
            $this->hiddenInputValue, array (
                'class' => 'hidden-id',
            ));
    }
echo CHtml::closeTag ('div');
?>
