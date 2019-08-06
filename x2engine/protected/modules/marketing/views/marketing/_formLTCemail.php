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
foreach(X2Model::model('Contacts')->attributeLabels() as $fieldName => $label)
    $insertableAttributes[$label] = '{'.$fieldName.'}';
$insertableAttributes[Yii::t('profile','Signature')] = '{signature}';

$contacts = Yii::t('contacts','{module} Attributes', array (
    '{module}' => Modules::displayName (false, "Contacts"))
);

$JSParams = CJSON::encode (array(
    'insertableAttributes' => array(
        $contacts => $insertableAttributes
)));

//Yii::app()->clientScript->registerScriptFile ($this->module->assetsUrl.'/js/CampaignForm.js');
//Yii::app()->clientScript->registerScript('editorSetup',"
//    x2.CampaignForm ($JSParams);
//", CClientScript::POS_READY);

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
        <table>
              <tr>
                <th>Send email from</th>
                <th>Template</th> 
                <th>Send Date</th>
            </tr>
        <?php 
            
      
             echo CHtml::hiddenField('name' , $model->name);
             echo CHtml::hiddenField('listId' , $model->listId);
             echo CHtml::hiddenField('suppressionListId' , $model->suppressionListId);
             echo CHtml::hiddenField('EmailCount' , $EmailCount);
            $safe = 0;
            $CampModel = new Campaign;
            $field = CActiveRecord::model('Fields')->findAllByAttributes(
                        array('fieldName' => 'sendAs', 'isVirtual' => 0));
            $templates = CHtml::listData (Docs::getEmailTemplates2('email', 'Contacts'), 'id', 'name');
            for($safe = 0; $safe < $EmailCount; $safe++ ){
           
                  //echo Api2Controller::fieldOptions($field);
                
                  echo '<tr><td>';
                  echo Credentials::selectorField(new InlineEmail, 'credId' , 'email', null, array ('id' => 'sendAs'. $safe , 'name' => 'sendAs'. $safe )); 
                  echo '</td><td>';
                  echo X2Html::activeDropDownList (new Campaign, 'template', $templates,  array ('id' => 'template'. $safe, 'name' => 'template'. $safe  ));
                  echo '</td><td>';
                  
                   echo X2Html::activeDatePicker (new Campaign, 'launchDate', array ('id' => 'launchDate'. $safe, 'name' => 'launchDate'. $safe  ), 'datetime');
                   echo '</td></tr>';
                  
                  
                //this is a check just to make sure my loop never gets stuck running forever 
                if($safe > 1111){
                    return;
                }
            }
        ?>
        </table>
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

<?php $this->endWidget();?>

