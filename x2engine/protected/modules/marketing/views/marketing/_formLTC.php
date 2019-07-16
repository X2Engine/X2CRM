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






Yii::app()->clientScript->registerPackage ('emailEditor');
Yii::app()->clientScript->registerCssFile ($this->module->assetsUrl.'/css/campaignForm.css');

$insertableAttributes = array();
foreach(X2Model::model('Contacts')->attributeLabels() as $fieldName => $label){
    $insertableAttributes[$label] = '{'.$fieldName.'}';
}
$insertableAttributes[Yii::t('profile','Signature')] = '{signature}';

$contacts = Yii::t('contacts','{module} Attributes', array (
    '{module}' => Modules::displayName (false, "Contacts"))
);

$JSParams = CJSON::encode (array(
    'insertableAttributes' => array(
        $contacts => $insertableAttributes
)));


Yii::app()->clientScript->registerScriptFile ($this->module->assetsUrl.'/js/CampaignFormAB.js');

Yii::app()->clientScript->registerScript('editorSetup',"
    x2.CampaignForm ($JSParams);
", CClientScript::POS_READY);


$contactLists = CHtml::listData (Campaign::getValidContactLists(), 'id', 'name');
if ($model->list && !in_array ($model->list->id, array_keys ($contactLists))) {
    $contactLists[$model->list->id] = $model->list->name;
    $contactLists = ArrayUtil::asorti ($contactLists);
}

$templates = CHtml::listData (Docs::getEmailTemplates2('email', 'Contacts'), 'id', 'name');
$templates[0] = Yii::t('marketing',"Custom");

$form = $this->beginWidget('CActiveForm', array(
    'id'=>'LTcampaign-form',
    'enableAjaxValidation'=>false
));
?>

<div id='top-container'>
    <div id='campaign-basic-form'>
        <div class='row'>
            <label><?php echo Yii::t('marketing', 'Campaign Name:')?></label>
            <?php echo $model->renderInput('name');?>
        </div>
        <div class='row'>
            <label><?php echo Yii::t('marketing', 'List:')?></label>
            <?php 
            if (isset($model->list)) {
                $model->listId = $model->list->id;
            }
            echo X2Html::activeDropDownList ($model, 'listId', $contactLists, array(
                'prompt' => Yii::t('marketing','Select a Contact List'),
            ))?>
            <?php echo X2Html::hint (Yii::t('marketing', 'Choose a contact list to send the campaign out to, or create one here.'));?>
            <span id='quick-create-list'>
                <?php echo X2Html::fa ('plus')?>
            </span>
        </div>

          <div class ='row' id='supButton'>
            <label><?php echo Yii::t('marketing', 'Suppression List(s):')?></label>
            <?php    echo CHtml::button(
                Yii::t('app', 'Add Suppression List(s)'), 
                array(
                    
                ));
            ?>
            </div>
        
        <div id= 'supRow' class='row' style = 'display:none;'>
            <label><?php echo Yii::t('marketing', 'Suppression List(s):')?></label>
            <?php
            if (isset($model->suppressionList)) {
                $model->suppressionListId = $model->suppressionList->id;
            }
            echo X2Html::activeDropDownList ($model, 'suppressionListId', $contactLists, array(
        
                'multiple' => "multiple",
                'class' => 'multiselect',
                'size' => 8,
                'style'=> "height:100px;",
                
            ))?>
            <?php echo X2Html::hint (Yii::t('marketing', 'Choose a Suppression list to avoid sending the campaign email, or create one here. Do not'
                    . ' mix types of list as this can cuase unpredictable error. Controls CTRL + CLICK to pick multiple list or unselect a list.'));?>
            <span id='quick-create-suppression-list'>
                <?php echo X2Html::fa ('plus')?>
            </span>
        </div>
    <div id='quick-create-list-form' style='display:none'>
        <h3><?php echo Yii::t('contacts', 'New Contact List');?></h3>
    </div>
    <div id='quick-create-suppression-list-form' style='display:none'>
        <h3><?php echo Yii::t('contacts', 'New Suppression List');?></h3>
    </div>
        <div class='row'>
            <label><?php echo Yii::t('marketing', 'Number of Emails')?></label>
             <?php echo X2Html::numberField ('email_count', '', array('integerOnly' => true, 'min' => 1));?>
            <?php echo X2Html::hint (Yii::t('marketing', "The number of emails that will be sent in total.")); ?>
        </div>
    </div>

</div>



<div id='bottom-container'>
    <div class="row buttons">
        <?php 
        echo CHtml::submitButton(
            $model->isNewRecord ? 
                Yii::t('app','Create') : 
                Yii::t('app','Save'),
            array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); 
        ?>
    </div>
</div>

<?php $this->endWidget(); ?>

