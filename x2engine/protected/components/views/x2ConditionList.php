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





Yii::app()->clientScript->registerScript('x2ConditionListViewJS'.$this->id,"

;(function () {
    var condList = new x2.ConditionList ({
        containerSelector: '#$this->id',
        name: '$this->name',
        modelClass: '".get_class ($this->model)."',
        options: ".CJSON::encode ($this->attributes).",
        operatorList: ".CJSON::encode(X2Model::getFieldComparisonOptions()).",
        visibilityOptions: ".CJSON::encode(array(
            array(1, Yii::t('app', 'Public')),
            array(0, Yii::t('app', 'Private')),
            array(2, Yii::t('app', 'User\'s Groups'))
        )).",
        allTags: ".CJSON::encode(Tags::getAllTags()).",
        value: ".CJSON::encode ($this->value)."
    });

    // add cond list object to element data to allow access from outside this scope
    $('#$this->id').data ('x2ConditionList', condList);
}) ();

", CClientScript::POS_END);

?>
<div id='<?php echo $this->id ?>'>
    <div class="x2-cond-list"><ol></ol></div>
    <div class='x2fields-template' style='display: none;'>
        <ol>
            <li>
                <div class="handle"></div>
                <fieldset></fieldset>
                <a href="javascript:void(0)" class="del"></a>
            </li>
        </ol>
        <div class="cell x2fields-attribute">
            <!--<label><?php echo Yii::t('studio', 'Attribute'); ?></label>-->
            <select disabled='disabled' 
             name="<?php echo $this->name . '[i][name]'; ?>"></select>
        </div>
        <div class="cell x2fields-operator">
            <!--<label><?php echo Yii::t('studio', 'Comparison'); ?></label>-->
            <select disabled='disabled' 
             name="<?php echo $this->name . '[i][operator]'; ?>"></select>
        </div>
        <div class="cell x2fields-value">
            <!--<label><?php echo Yii::t('studio', 'Value'); ?></label>-->
            <input disabled='disabled' type="text" 
             name="<?php echo $this->name . '[i][value]'; ?>" />
        </div>
    </div>
    <button class='add-condition-button x2-button x2-small-button'><?php 
        echo Yii::t('app', 'Add Condition'); ?></button>
</div>
