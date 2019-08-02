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


<?php 
echo '<div class="form no-border" style="float:left;width:590px;">';
	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'opportunities-form',
		'enableAjaxValidation'=>false,
	));
$attributeLabels = Opportunity::attributeLabels();
$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Opportunity'));
if(isset($_GET['version'])){
    $version=$_GET['version'];
    $version=FormVersions::model()->findByAttributes(array('name'=>$version));
    $sizes=json_decode($version->sizes, true);
    $positions=json_decode($version->positions, true);
    $tempArr=array();
    foreach($fields as $field){
        if(isset($positions[$field->fieldName])){
            $field->coordinates=$positions[$field->fieldName];
            $field->size=$sizes[$field->fieldName];
            $tempArr[]=$field;
        }
    }
    $fields=$tempArr;
}
$nonCustom=array();
$custom=array();
foreach($fields as $field){
    if($field->custom==0){
        $nonCustom[$field->fieldName]=$field;
    }else{
        $custom[$field->fieldName]=$field;
    }
}


$temp=RoleToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
$roles=array();
foreach($temp as $link){
    $roles[]=$link->roleId;
}
/* x2temp */
$groups=GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
foreach($groups as $link){
    $tempRole=RoleToUser::model()->findByAttributes(array('userId'=>$link->groupId, 'type'=>'group'));
    $roles[]=$tempRole->roleId; 
}
/* end x2temp */
echo $form->errorSummary($model);
?>
<div class="span-15" id="form-box" style="position:relative;overflow:hidden;height:700px;">
<?php
foreach($fields as $field){ ?>
    <?php if($field->fieldName!="id"){ 
        $size=$field->size;
        $pieces=explode(":",$size);
        $width=$pieces[0];
        $height=$pieces[1];
        $position=$field->coordinates;
        $pieces=explode(":",$position);
        $left=$pieces[0];
        $top=$pieces[1];
        
        ?> 
    <?php if(($field->fieldName!='assignedTo' && $field->fieldName!='associatedContacts')){ ?>
    <div class="draggable" style="padding:10px;border:solid;border-width:1px;position:absolute;left:<?php echo $left;?>px;top:<?php echo $top;?>px;" id="<?php echo $field->fieldName ?>">
    
        <div class="label"><label for="Contacts_<?php echo $field->fieldName;?>"><?php echo Yii::t('contacts',$field->attributeLabel); ?></label></div>
                <?php
                    $fieldPerms=RoleToPermission::model()->findAllByAttributes(array('fieldId'=>$field->id));
                    $perms=array();
                    foreach($fieldPerms as $permission){
                        $perms[$permission->roleId]=$permission->permission;
                    }
                    $tempPerm=2;
                    foreach($roles as $role){
                        if(array_search($role,array_keys($perms))!==false){
                            if($perms[$role]<$tempPerm)
                                $tempPerm=$perms[$role];
                        }
                    }
                    $fieldName=$field->fieldName;(isset($editor) && $editor)?$disabled='disabled':$disabled="";
                    $tempPerm==1?$disabled='disabled':$disabled=$disabled;
                    
                        if($field->type=='varchar'){
                            $default = empty($model->$fieldName);
                            if($default) 
                                    $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
                            echo $form->textField($model, $fieldName, array(
                                    'class'=>'resizable',
                                    'style'=>($default?'color:#aaa;':'')."height:".$height.";width:".$width.";",
                                    'onfocus'=>$default? 'x2.forms.toggleText(this);' : null,
                                    'onblur'=>$default? 'x2.forms.toggleText(this);' : null,
                                    'tabindex'=>$field->tabOrder,
                                    'disabled'=>$disabled,
                            ));

                            }elseif($field->type=='text'){
                               $default = empty($model->$fieldName);
                            if($default) 
                                    $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
                            echo $form->textArea($model, $fieldName, array(
                                    'class'=>'resizable',
                                    'style'=>($default?'color:#aaa;':'')."height:".$height.";width:".$width.";",
                                    'onfocus'=>$default? 'x2.forms.toggleText(this);' : null,
                                    'onblur'=>$default? 'x2.forms.toggleText(this);' : null,
                                    'tabindex'=>$field->tabOrder,
                                    'disabled'=>$disabled,
                            )); 
                            }elseif($field->type=='date'){
                                $default = empty($model->$fieldName);
                                if($default) 
                                        $model->$fieldName = date("Y-m-d H:i:s");
                                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                                $this->widget('CJuiDateTimePicker',array(
                                    'model'=>$model, //Model object
                                    'attribute'=>$field->fieldName, //attribute name
                                    'mode'=>'datetime', //use "time","date" or "datetime" (default)
                                    'options'=>array(
                                        'dateFormat'=>'yy-mm-dd',

                                    ), // jquery plugin options
                                    'htmlOptions'=>array(
                                        'class'=>'resizable',
                                        'disabled'=>$disabled,
                                        'style'=>"height:".$height.";width:".$width.";",
                                        'tabindex'=>$field->tabOrder,
                                    ),
                                    'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
                                )); 
                            }elseif(preg_match('/dropdown/',$field->type) && !isset($editor)){
                                $pieces=explode(":",$field->type);
                                $id=$pieces[1];
                                $dropdown=Dropdowns::model()->findByPk($id);
                                $default = empty($model->$fieldName);
                                if($default) 
                                        $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
                                echo $form->dropDownList($model, $fieldName,json_decode($dropdown->options), array(
                                        'class'=>'resizable',
                                        'style'=>"width:".$width.";",
                                        'onfocus'=>$default? 'x2.forms.toggleText(this);' : null,
                                        'onblur'=>$default? 'x2.forms.toggleText(this);' : null,
                                        'tabindex'=>$field->tabOrder,
                                        'disabled'=>$disabled,
                                ));
                            }
                       
                        
                        
                        ?>
    </div>
 <?php }elseif($field->fieldName=='assignedTo'){ ?>
     <div class="draggable" style="padding:10px;border:solid;border-width:1px;position:absolute;left:<?php echo $left;?>px;top:<?php echo $top;?>px;" id="<?php echo $field->fieldName ?>">
    
        <div class="label"><label for="Contacts_<?php echo $field->fieldName;?>"><?php echo Yii::t('contacts',$field->attributeLabel); ?></label></div>
                <?php
                    $fieldPerms=RoleToPermission::model()->findAllByAttributes(array('fieldId'=>$field->id));
                    $perms=array();
                    foreach($fieldPerms as $permission){
                        $perms[$permission->roleId]=$permission->permission;
                    }
                    $tempPerm=2;
                    foreach($roles as $role){
                        if(array_search($role,array_keys($perms))!==false){
                            if($perms[$role]<$tempPerm)
                                $tempPerm=$perms[$role];
                        }
                    }
                    $fieldName=$field->fieldName;(isset($editor) && $editor)?$disabled='disabled':$disabled="";
                    $tempPerm==1?$disabled='disabled':$disabled=$disabled;
                   
                            $default = empty($model->$fieldName);
                            if($default) 
                                    $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
                            echo $form->dropDownList($model, $fieldName, $users, array(
                                    'class'=>'resizable',
                                    'style'=>"width:".$width.";",
                                    'tabindex'=>$field->tabOrder,
                                    'disabled'=>$disabled,
                                    'id'=>'assignedToDropdown',
                                    'multiple'=>'multiple',
                                    'size'=>7,
                            ));
                            

                       
                        
                        
                        ?>
    </div>
 <?php }elseif($field->fieldName=='associatedContacts'){ ?>
     <div class="draggable" style="padding:10px;border:solid;border-width:1px;position:absolute;left:<?php echo $left;?>px;top:<?php echo $top;?>px;" id="<?php echo $field->fieldName ?>">
    
        <div class="label"><label for="Contacts_<?php echo $field->fieldName;?>"><?php echo Yii::t('contacts',$field->attributeLabel); ?></label></div>
                <?php
                    $fieldPerms=RoleToPermission::model()->findAllByAttributes(array('fieldId'=>$field->id));
                    $perms=array();
                    foreach($fieldPerms as $permission){
                        $perms[$permission->roleId]=$permission->permission;
                    }
                    $tempPerm=2;
                    foreach($roles as $role){
                        if(array_search($role,array_keys($perms))!==false){
                            if($perms[$role]<$tempPerm)
                                $tempPerm=$perms[$role];
                        }
                    }
                    $fieldName=$field->fieldName;(isset($editor) && $editor)?$disabled='disabled':$disabled="";
                    $tempPerm==1?$disabled='disabled':$disabled=$disabled;
                   
                            $default = empty($model->$fieldName);
                            if($default) 
                                    $model->$fieldName = Yii::t('contacts',$field->attributeLabel);
                            echo $form->dropDownList($model, $fieldName, $contacts, array(
                                    'class'=>'resizable',
                                    'style'=>"width:".$width.";",
                                    'tabindex'=>$field->tabOrder,
                                    'disabled'=>$disabled,
                                    'id'=>'associatedContacts',
                                    'multiple'=>'multiple',
                                    'size'=>7,
                            ));
                            

                       
                        
                        
                        ?>
    </div>
 <?php }
                        
                        if($field->visible==0 || $tempPerm==0){
                            Yii::app()->clientScript->registerScript($field->fieldName,'
                                $("#'.$field->fieldName.'").css({"visibility":"hidden"});
                            ');
                        }
                        
                        }
}



?>

<?php

?>

</div>
<?php



if (!isset($isQuickCreate)) {	//if we're not in quickCreate, end the form
    if(!isset($editor)){
        echo '	<div class="row buttons">'."\n";
        echo '		'.CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
        echo "	</div>\n";
    }
$this->endWidget();
echo "</div>\n";
}


if(isset($editor)){
    if($editor){
        ?>
        <script>
            $(function(){
                $('.draggable').draggable({ grid: [10, 10], containment:'parent' });
                $('.resizable').resizable({ grid: [5, 5] });
            });
        </script>
        <?php
    }
}else{
    ?>
    <script>
        $(".draggable").css({border: 'none'});
    </script>
    <?php
}
?>
