<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
?>
<div class='webform-tab' id='generate-tab' data-title='<?php echo Yii::t('marketing','Generate Records'); ?>' >
    <div class='tab-content'>
        <div class="row">
            <label class='left-label' 
             for='generateLead'><?php echo Yii::t('app', 'Generate {Lead}: ', array(
                 '{Lead}'=>Modules::displayName(false, 'X2Leads')
             )); ?></label>
            <input id='generate-lead-checkbox' type='checkbox'  name='generateLead'>
            <?php
            echo X2Html::hint (
                Yii::t('app', 'If you have this box checked, a new {lead} record will be associated '.
                    'with the new {contact} when the web lead form is submitted. The web lead form '.
                    'must be saved for this feature to take effect.', array(
                        '{lead}'=>strtolower(Modules::displayName(false, 'X2Leads')),
                        '{contact}'=>strtolower(Modules::displayName(false, 'Contacts'))
                    )), false, null, true);
            ?>
            <div id='generate-lead-form' style='display: none;'>
            <?php
            echo CHtml::activeLabel (X2Model::model ('Contacts'), 'leadSource');
            echo X2Model::model ('X2Leads')->renderInput (
                'leadSource', array ('class' => 'left-label', 'name' => 'leadSource'));
            ?>
            </div>
        </div>
        <div class="row">
            <label class='left-label' 
             for='generateAccount'><?php echo Yii::t('app', 'Generate {Account}: ', array(
                 '{Account}'=>Modules::displayName(false, 'Accounts')
            )); ?></label>
            <input id='generate-account-checkbox' type='checkbox'  name='generateAccount'>
            <?php
            echo X2Html::hint (
                Yii::t('app', 'If you have this box checked, a new {account} record will be generated '.
                    'using the new {contact}\'s company field when the web lead form is submitted. The '.
                    'web lead form must be saved for this feature to take effect.', array(
                        '{account}'=>strtolower(Modules::displayName(false, 'Accounts')),
                        '{contact}'=>strtolower(Modules::displayName(false, 'Contacts'))
                    )), false, null, true);
            ?>
        </div>
    </div>
</div>

<?php  ?>
