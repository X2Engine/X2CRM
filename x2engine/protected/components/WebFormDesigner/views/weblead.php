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

<?php  
if($this->edition == 'pro'):
?>
<div class="webform-tab" id="email-tab" data-title='<?php echo Yii::t('marketing','Email'); ?>'>
    <div class='tab-content'>
        <div class="cell">
            <h4><?php echo Yii::t('marketing','Email') .':'; ?></h4>
            <p class="fieldhelp" style="width: auto;">
                <?php
                echo Yii::t(
                    'marketing','Select email templates to send to the new web lead and the {user} '.
                    'assigned to the web lead.', array(
                        '{user}' => strtolower(Modules::displayName(false, 'Users')),
                    ));
                ?>
                <br />
            </p>
            <?php 
            $templateList = array(''=>'------------') + Docs::getEmailTemplates('email', 'Contacts'); 
            ?>
            <div class="cell">
                <?php echo CHtml::label(Yii::t('marketing','{user} Email', array(
                    '{user}' => Modules::displayName(false, 'Users'),
                )), ''); ?>
                <?php echo CHtml::dropDownList('user-email-template', '', $templateList); ?>
            </div>
            <div class="cell">
                <?php echo CHtml::label(Yii::t('marketing','Weblead Email'), ''); ?>
                <?php echo CHtml::dropDownList('weblead-email-template', '', $templateList); ?>
            </div>
        </div>
    </div>
</div>

<div class='webform-tab' id='tags-tab' data-title='<?php echo Yii::t('marketing','Add Tags'); ?>'>
    <div class='tab-content'>
        <div class="cell">
            <h4><?php echo Yii::t('marketing','Tags') .':'; ?></h4>
            <?php echo CHtml::textField('tags'); ?>
            <p class="fieldhelp" style="width: auto;">
                <em><?php echo Yii::t('marketing','Example') .': web,newlead,urgent'; ?></em>
                <br/>
                <?php
                echo Yii::t(
                    'marketing','These tags will be applied to any {contact} created by the form.', array(
                        '{contact}' => strtolower(Modules::displayName(false, 'Contacts')),
                    ));
                ?>
                <br />
            </p>
        </div>
    </div>
</div>

<div class="row webform-tab-content" id="custom-html-input-container" data-tab='advanced-tab'>
    <h4>
        <?php echo Yii::t('marketing','Custom &lt;HEAD&gt;') .':'; ?>
    </h4>
    <p class="fieldhelp" style="width: 100%">
        <?php echo Yii::t('marketing',
            'Enter any HTML you would like inserted into the &lt;HEAD&gt; tag.'); ?>
    </p>
        <?php echo CHtml::textArea('header', '<!-- custom html -->', array(
            'class'=> 'code', 
            'id'=>'custom-html',
            'data-mode' => 'xml'
        )); ?>
    <br/>
</div>
<div class="row webform-tab-content" id="redirect-url-container" data-tab='advanced-tab'>
    <h4>
        <?php echo Yii::t('marketing','Redirect URL') .':'; ?>
    </h4>
    <p class="fieldhelp" style="width: 100%">
        <?php echo Yii::t('marketing',
            'Enter a URL which the form will redirect to upon submission.'); ?>
    </p>
    <?php
    echo CHtml::textField ('redirectUrl', '', array (
        'id' => 'redirect-url'
    ))
    ?>
</div>
<div class="row webform-tab-content" id="fingerprint-detection-input-container" data-tab='advanced-tab'>
    <h4>
        <?php echo Yii::t('marketing','X2Identity duplicate detection') .':'; ?>
    </h4>
    <p class="fieldhelp" style="width: 100%">
        <?php echo Yii::t('marketing','Configure whether duplicate detection will be performed '.
            'using the lead\'s fingerprint. This setting should be disabled for any form used '.
            'from a single device to capture leads.'); ?>
    </p>
    <div class="row">
        <label class='left-label' for='fingerprintDetection'>
            <?php echo Yii::t('app', 'Enable duplicate detection by fingerprint: '); ?>
        </label>
        <input id='fingerprint-detection-checkbox' type='checkbox'  name='fingerprintDetection'>
    </div>

    <br/>
</div>
<?php endif;  ?>
