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

$namespacedId = $this->namespace."-multi-type-autocomplete-container";

Yii::app()->clientScript->registerScript('multiTypeAutocompleteJS'.$this->namespace,"

$(function () {
    var container$ = $('#".$namespacedId."');

    container$.find ('select').change (function () {
        var modelType = container$.find ('select').val ();
        x2.forms.inputLoading (container$.find ('.record-name-autocomplete'));
        $.ajax ({
            type: 'GET',
            url: 'ajaxGetModelAutocomplete',
            data: {
                modelType: modelType
            },
            success: function (data) {
                // remove span element used by jQuery widget
                container$.find ('input').
                    first ().next ('span').remove ();

                // replace old autocomplete with the new one
                container$.find ('input').first ().replaceWith (data); 
     
                // remove the loading gif
                x2.forms.inputLoadingStop (container$.find ('.record-name-autocomplete'));
            }
        });
    });
});

", CClientScript::POS_END);

?>
<div id="<?php echo $namespacedId ?>" 
 class="multi-type-autocomplete-container form2">
<?php
    echo CHtml::dropDownList (
        $this->selectName, $this->value, $this->options, 
        array (
            'class' => 'x2-select type-select',
        ));

    X2Model::renderModelAutocomplete ($this->value, false, array ());
    echo CHtml::hiddenField ($this->hiddenInputName, '', array (
        'class' => 'hidden-id',
    ));
?>
</div>
