<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

/**
 * View file for customizing and creating fields.
 *
 * Intended to be rendered partially, via AJAX, in {@link AdminController::actionCreateUpdateField()}
 */

?><div class="page-title"><h2><?php echo $new ? Yii::t('admin', "Add A Custom Field") : Yii::t('admin', 'Customize Fields'); ?></h2></div>
<?php echo '<h3 id="createUpdateField-message" style="color:'.($error ? 'red' : 'green').'">'.$message.'</h3>'; ?>

<div class="form" id="createUpdateField-container">
    <div style="width:600px">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'criteria-form',
            'enableAjaxValidation' => false,
            'action' => $this->createUrl('createUpdateField',$new?array():array('id'=>$model->id)),
                ));
        ?>
        <em><?php echo Yii::t('app', 'Fields with <span class="required">*</span> are required.'); ?></em><br>
        <?php if($new){ ?>
            <div class="row">
                <?php echo $form->labelEx($model, 'modelName'); ?>
                <?php echo $form->dropDownList($model, 'modelName', Fields::getModelNames()); ?>
                <?php echo $form->error($model, 'modelName'); ?>
            </div>

            <div class="row">
                <br><div><?php echo Yii::t('admin', 'No spaces are allowed.'); ?></div><br>
                <?php echo $form->labelEx($model, 'fieldName'); ?>
                <?php echo $form->textField($model, 'fieldName'); ?>
                <?php echo $form->error($model, 'fieldName'); ?>
            </div>
        <?php }else{ ?>
            <div class="row">
                <?php echo $form->labelEx($model, 'modelName'); ?>
                <?php
                foreach(X2Model::model('Modules')->findAllByAttributes(array('editable' => true)) as $module){
                    if(!($modelName = X2Model::getModelName($module->name))){
                        $modelName = ucfirst($module->name);
                    }

                    $modelList[$modelName] = Yii::t('app', $module->title);
                }
                echo $form->dropDownList($model, 'modelName', $modelList, array(
                    'empty' => Yii::t('admin', 'Select a model'),
                    'id' => 'modelName-existing'
                ));
                ?>
                <?php echo $form->error($model, 'modelName'); ?>
            </div>

            <div class="row">
                <?php echo $form->labelEx($model, 'fieldName'); ?>
                <?php
                $modelSet = !empty($model->modelName);
                $fieldList = array();
                if($modelSet) {
                    $fields = Fields::model()->findAllByAttributes(array('modelName'=>$model->modelName));
                    foreach($fields as $existingField) {
                        $fieldList[$existingField->fieldName] = $existingField->attributeLabel;
                    }
                }
                echo $form->dropDownList($model, 'fieldName', $fieldList, array(
                    'empty' => $modelSet ? Yii::t('admin', 'Select field to customize') : Yii::t('admin', 'Select a model first'),
                    'id' => 'fieldName-existing'
                ));
                ?>
            </div>
            <br>
        <?php } ?>
        <div class="row">
            <div>
            <br><div><?php echo Yii::t('admin', 'Attribute Label is what you want the field to be displayed as.'); ?><br>
            <?php echo Yii::t('admin', 'So for the field firstName, the label should probably be First Name'); ?></div><br>
            <?php echo $form->labelEx($model, 'attributeLabel'); ?>
            <?php echo $form->textField($model, 'attributeLabel', array('id' => 'attributeLabel')); ?>
            <?php echo $form->error($model, 'attributeLabel'); ?>
        </div>


        <div class="row">
            <?php echo $form->labelEx($model, 'type'); ?>
            <?php
            if(!$new && !$model->custom)
                echo '<span style="color:red">'.Yii::t('admin', 'Changing the type of a default field is strongly discouraged.')
                        .' '.Yii::t('admin','It may result in data loss or irregular application behavior.').'</span><br>';
            
            echo $form->dropDownList($model, 'type', Fields::getFieldTypes('title'), array(
                'id' => 'fieldType',
                'class' => ($new ? 'new' : 'existing')
            ));
            ?>
            <?php echo $form->error($model, 'type'); ?>
        </div>
            <div class="row">
                <?php
                if($model->type == "dropdown"){
                    $dropdowns = Dropdowns::model()->findAll();
                    $arr = array();
                    foreach($dropdowns as $dropdown){
                        $arr[$dropdown->id] = $dropdown->name;
                    }

                    echo CHtml::activeDropDownList($model, 'linkType', $arr, array(
                        'id' => 'dropdown-type',
                        'class' => ($new ? 'new' : 'existing')
                    ));
                }elseif($model->type == 'link'){
                    $query = Yii::app()->db->createCommand()
                            ->select('modelName')
                            ->from('x2_fields')
                            ->group('modelName')
                            ->queryAll();
                    $arr = array();
                    foreach($query as $array){
                        if($array['modelName'] != 'Calendar')
                            $arr[$array['modelName']] = $array['modelName'];
                    }
                    echo CHtml::activeDropDownList($model, 'linkType', $arr);
                }

                $dummyFieldName = 'customized_field';
                foreach($model->getErrors('defaultValue') as $index => $message){
                    $dummyModel->addError('customized_field', $message);
                }
                echo CHtml::label($model->getAttributeLabel('defaultValue'), CHtml::resolveName($dummyModel, $dummyFieldName));
                $model->fieldName = 'customized_field';
                echo X2Model::renderModelInput($dummyModel, $model,array('id'=>'defaultValue-input-'.$model->type));
                echo CHtml::error($dummyModel, 'customized_field');
                echo "<script id=\"input-clientscript-".time()."\">\n";
                Yii::app()->clientScript->echoScripts();
                echo "\n</script>";
            ?>
            </div>
        <br>


        <div class="row">
            <?php echo $form->checkBox($model, 'required', array('id' => 'required')); ?>
            <?php echo $form->labelEx($model, 'required', array('style' => 'display:inline;')); ?>
            <?php echo $form->error($model, 'required'); ?>
        </div>

        <div class="row">
            <?php echo $form->checkBox($model, 'uniqueConstraint', array('id' => 'uniqueConstraint')); ?>
            <?php echo $form->labelEx($model, 'uniqueConstraint', array('style' => 'display:inline;')); ?>
            <?php echo $form->error($model, 'uniqueConstraint'); ?>
        </div>

        <div class="row">
            <?php echo $form->checkBox($model, 'searchable', array('id' => 'searchable-custom', 'onclick' => '$("#relevance_box_custom").toggle();')); ?>
            <?php echo $form->labelEx($model, 'searchable', array('style' => 'display:inline;')); ?>
            <?php echo $form->error($model, 'searchable'); ?>
        </div>

        <div class="row" id ="relevance_box_custom" style="display:none">
            <?php echo $form->labelEx($model, 'relevance'); ?>
            <?php echo $form->dropDownList($model, 'relevance', Fields::searchRelevance(), array("id" => "relevance-custom", 'options' => array('Medium' => array('selected' => true)))); ?>
            <?php echo $form->error($model, 'relevance'); ?>
        </div>
        <br />

        <br>
        <div class="row buttons">
            <?php 
            echo CHtml::submitButton(Yii::t('app', 'Save'),array(
                'class' => 'x2-button '.($new ? 'new' : 'existing'),
                'id' => 'createUpdateField-savebutton'
            ));
            ?>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div>
