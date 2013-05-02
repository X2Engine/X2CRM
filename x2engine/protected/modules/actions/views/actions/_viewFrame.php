<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
$jsVersion = '?'.Yii::app()->params->buildDate;
$themeUrl = Yii::app()->theme->getBaseUrl();
$baseUrl = Yii::app()->request->getBaseUrl();
$dateFormat = (isset($this->controller) ? Formatter::formatDatePicker('medium') : Formatter::formatDatePicker('medium') );
$timeFormat = (isset($this->controller) ? Formatter::formatTimePicker() : Formatter::formatTimePicker() );
$language = (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage();
?>
<!DOCTYPE html>
<!--[if lt IE 9]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>" class="lt-ie9">
<![endif]-->
<!--[if gt IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<![endif]-->
<!--[if !IE]> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
    <!-- <![endif]-->
    <head>
        <meta charset="UTF-8" />
        <script src='<?php echo $baseUrl; ?>/js/jquery-1.6.2.min.js'></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/main.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/details.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/form.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/css/base/jquery-ui.css'; ?>" />
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jquery.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/js/jquery-ui.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/js/jquery-ui-i18n.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/jquery-ui-timepicker-addon.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/qtip/jquery.qtip.min.js'; ?>"></script>
        <style>
            .control-button{
                display:inline-block;
                margin-top:-5px;
                padding-right:10px;
                vertical-align:middle;
                cursor:pointer;
            }
            a.vcr-button{
                padding: 1px 15px;
                margin-top:-5px;
            }
            #actionHeader{
                background: rgb(252,252,252); /* Old browsers */
                background: -moz-linear-gradient(top, rgba(252,252,252,1) 0%, rgba(232,232,232,1) 100%); /* FF3.6+ */
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(252,252,252,1)), color-stop(100%,rgba(232,232,232,1))); /* Chrome,Safari4+ */
                background: -webkit-linear-gradient(top, rgba(252,252,252,1) 0%,rgba(232,232,232,1) 100%); /* Chrome10+,Safari5.1+ */
                background: -o-linear-gradient(top, rgba(252,252,252,1) 0%,rgba(232,232,232,1) 100%); /* Opera 11.10+ */
                background: -ms-linear-gradient(top, rgba(252,252,252,1) 0%,rgba(232,232,232,1) 100%); /* IE10+ */
                background: linear-gradient(to bottom, rgba(252,252,252,1) 0%,rgba(232,232,232,1) 100%); /* W3C */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fcfcfc', endColorstr='#e8e8e8',GradientType=0 ); /* IE6-9 */
            }
            .model-link a{
                text-decoration:none;
                color:#06c;
            }
            .hidden-frame-form{
                display:none;
            }
        </style>
        <title>Action View Frame</title>
    </head>
    <body>
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'actions-frameUpdate-form',
            'enableAjaxValidation' => false,
            'action'=>'update?id='.$model->id
        ));
        ?>
        <div id="actionHeader" style="border-bottom:1px solid #ccc;margin-top:10px;padding-bottom:10px;">
            <span id="header-content" style="margin-left:5px;">
                <span id="header-info" style="font-weight:bold;">
                    <?php
                    if($model->complete != 'Yes'){
                        if(!empty($model->dueDate)){
                            echo "<span class='hidden-frame-form'>";
                            echo $form->label($model, 'dueDate');
                            if(is_numeric($model->dueDate)){
                                $model->dueDate = Formatter::formatDateTime($model->dueDate);
                            }
                            echo $form->textField($model, 'dueDate');
                            echo "</span>";
                            echo "<span class='field-value'>";
                            echo "<span style='color:grey'>".Yii::t('actions', 'Due: ')."</span>".Actions::parseStatus($model->dueDate).'</b>';
                            echo "</span>";
                        }elseif(!empty($model->createDate)){
                            echo Yii::t('actions', 'Created: ').Formatter::formatLongDateTime($model->createDate).'</b>';
                        }else{
                            echo "&nbsp;";
                        }
                    }else{
                        echo Yii::t('actions', 'Completed {date}', array('{date}' => Formatter::formatCompleteDate($model->completeDate)));
                    }
                    ?>
                </span>
                <span>
                    <span id="controls">
                        <?php if(Yii::app()->user->checkAccess('ActionsComplete',array('assignedTo'=>$model->assignedTo))){
                        if($model->complete != 'Yes'){ ?>
                            <div class="control-button icon complete-button"></div>
                        <?php }else{ ?>
                            <div class="control-button icon uncomplete-button"></div>
                        <?php }
                        } ?>
                        <?php if(Yii::app()->user->checkAccess('ActionsUpdate',array('assignedTo'=>$model->assignedTo))){ ?>
                            <div class="control-button icon edit-button"></div>
                        <?php } ?>
                        <?php if(Yii::app()->user->checkAccess('ActionsDelete',array('assignedTo'=>$model->assignedTo))){ ?>
                            <div class="control-button icon delete-button" style="background:url('<?php echo Yii::app()->theme->baseUrl; ?>/images/icons/Delete.png') no-repeat center center;height:21px;width:21px;"></div>
                        <?php } ?>
                        <?php if(Yii::app()->user->checkAccess('ActionsToggleSticky',array('assignedTo'=>$model->assignedTo))){
                        if(!$model->sticky){ ?>
                            <div class="control-button icon sticky-button" title="Click to flag this action as sticky."></div>
                        <?php } else { ?>
                            <div class="control-button icon sticky-button unsticky" title="Click to unpin this action."></div>
                        <?php }
                        } ?>
                    </span>
                    <?php if(!$publisher){ ?>
                        <div class="vcrPager" style="margin-right:15px;">
                            <?php echo CHtml::link('<', '#', array('class' => 'x2-button vcr-button control-button', 'id' => 'back-button')); ?>
                            <?php echo CHtml::link('>', '#', array('class' => 'x2-button vcr-button control-button', 'id' => 'forward-button')); ?>
                        </div>
                    <?php } ?>
                </span>
            </span>
            <br />
        </div>
        <br />
        <div id="content" style="margin-left:5px;margin-right:5px;">
            <div id="actionBody" class="form">
                <?php
                echo "<span class='hidden-frame-form'><span style='display:inline-block;'>";
                echo $form->labelEx($model, 'subject');
                echo $form->textField($model, 'subject', array('size' => 80));
                echo "</span></span> ";
                echo "<span class='hidden-frame-form'><span style='display:inline-block;'>";
                echo $form->labelEx($model, 'priority');
                echo $form->dropDownList($model, 'priority', array(1 => 'Low', 2 => 'Medium', 3 => 'High'));
                echo "</span></span>";
                echo "<span class='field-value'>";
                if(!empty($model->subject)){
                    echo "<b>".$model->subject."</b><br><br>";
                }elseif(!empty($model->type)){
                    echo "<b>".ucfirst($model->type)."</b><br><br>";
                }
                echo "</span>";
                echo "<span class='hidden-frame-form'>";
                echo $form->labelEx($model, 'actionDescription');
                echo $form->textArea($model, 'actionDescription', array('rows' => (6), 'cols' => 40));
                echo "</span>";
                echo "<span class='field-value'>";
                echo Formatter::convertLineBreaks($model->actionDescription);
                echo "</span>";
                ?>
                <?php echo CHtml::ajaxSubmitButton(Yii::t('app', 'Submit'),'update?id='.$model->id,array(),array('style'=>'display:none;float:left;','class'=>'hidden-frame-form x2-button highlight')); ?>
                <?php echo CHtml::link('View Full Edit Page',array('update','id'=>$model->id),array('style'=>'float:right;display:none;','target'=>'_parent','class'=>'x2-button hidden-frame-form')); ?>
            </div>
            <?php $this->endWidget(); ?>
            <?php if(!empty($model->associationType) && is_numeric($model->associationId) && !is_null(X2Model::getAssociationModel($model->associationType, $model->associationId)) && ($publisher == 'false' || !$publisher)){ ?>
                <div id="recordBody" class="form">
                    <?php echo '<div class="page-title"><h3>'.Yii::t('actions', 'Associated Record').'</h3></div>'; ?>
                    <?php
                    if($model->associationType == 'contacts'){
                        $this->renderPartial('application.modules.contacts.views.contacts._detailViewMini', array(
                            'model' => X2Model::model('Contacts')->findByPk($model->associationId),
                            'actionModel' => $model,
                        ));
                    }else{
                        echo ucwords(Events::parseModelName(X2Model::getModelName($model->associationType))).": <span class='model-link'>".X2Model::getModelLink($model->associationId, X2Model::getModelName($model->associationType))."</span>";
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
        <div id="actionFooter">
        </div>
    </body>
</html>
<script>
    $('#Actions_dueDate').datetimepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['<?php echo $language; ?>'], {'dateFormat':'<?php echo $dateFormat; ?>','timeFormat':'<?php echo $timeFormat; ?>','ampm':true,'changeMonth':true,'changeYear':true}));
    $('#actions-frameUpdate-form').submit(function(e){
        var data=$(this).serializeArray();
        var id=<?php echo $model->id; ?>;
        e.preventDefault();
        $.ajax({
            url:'update?id='+id,
            type:'POST',
            data:data,
            success:function(data){
                $('iframe', parent.document).attr('src', $('iframe', parent.document).attr('src'));
                window.parent.$('#history-'+id).replaceWith(data);
            }
        });
    });
</script>