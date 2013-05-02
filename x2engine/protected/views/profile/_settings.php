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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/modcoder_excolor/jquery.modcoder.excolor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/profileSettings.js');

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'settings-form',
	'enableAjaxValidation'=>false,
)); ?>
<div class="form">
	<?php echo $form->errorSummary($model); ?>

	<div class="row" style="margin-bottom:10px;">
		<div class="cell">
			<?php echo $form->checkBox($model,'allowPost',array('onchange'=>'js:highlightSave();')); ?>
			<?php echo $form->labelEx($model,'allowPost',array('style'=>'display:inline;')); ?>
		</div>
		<!--<div class="cell">
			<?php //echo $form->checkBox($model,'showSocialMedia',array('onchange'=>'js:highlightSave();')); ?>
			<?php //echo $form->labelEx($model,'showSocialMedia',array('style'=>'display:inline;')); ?>
			<?php //echo $form->dropDownList($model,'showSocialMedia',array(1=>Yii::t('actions','Yes'),0=>Yii::t('actions','No')),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
		</div>-->
	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'startPage'); ?>
			<?php echo $form->dropDownList($model,'startPage',$menuItems,array('onchange'=>'js:highlightSave();','style'=>'min-width:140px;')); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'resultsPerPage'); ?>
			<?php echo $form->dropDownList($model,'resultsPerPage',Profile::getPossibleResultsPerPage(),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
		</div>

	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'language'); ?>
			<?php echo $form->dropDownList($model,'language',$languages,array('onchange'=>'js:highlightSave();')); ?>
		</div>
		<div class="cell">
			<?php
			if(!isset($model->timeZone))
				$model->timeZone="Europe/London";
			?>
			<?php echo $form->labelEx($model,'timeZone'); ?>
			<?php echo $form->dropDownList($model,'timeZone',$times,array('onchange'=>'js:highlightSave();')); ?>
		</div>
	</div>
	<div class="cell">
		<h3><?php echo Yii::t('app','Theme'); ?></h3>
		<div class="row">
			<?php echo $form->labelEx($model,'backgroundColor'); ?>
			<?php echo $form->textField($model,'backgroundColor',array('id'=>'backgroundColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'menuBgColor'); ?>
			<?php echo $form->textField($model,'menuBgColor',array('id'=>'menuBgColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'menuTextColor'); ?>
			<?php echo $form->textField($model,'menuTextColor',array('id'=>'menuTextColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'pageHeaderBgColor'); ?>
			<?php echo $form->textField($model,'pageHeaderBgColor',array('id'=>'pageHeaderBgColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'pageHeaderTextColor'); ?>
			<?php echo $form->textField($model,'pageHeaderTextColor',array('id'=>'pageHeaderTextColor')); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'backgroundTiling'); ?>
			<?php echo $form->dropDownList($model,'backgroundTiling',array(
				'stretch'=>Yii::t('app','Stretch'),
				'center'=>Yii::t('app','Center'),
				'repeat'=>Yii::t('app','Tile'),
				'repeat-x'=>Yii::t('app','Tile Horizontally'),
				'repeat-y'=>Yii::t('app','Tile Vertically'),
			),array('id'=>'backgroundTiling')); ?>
		</div>
	</div>
	<div class="cell">
		<h3><?php echo Yii::t('profile','Background Image'); ?></h3>

		<?php
		echo CHtml::link(
			Yii::t('app','None'),
			'#',
			array(
				'onclick'=>"setBackground(''); return false;"
			)
		);
		$this->widget('zii.widgets.CListView', array(
			'dataProvider'=>$myBackgrounds,
			'template'=>'{items}{pager}',
			'itemView'=>'//media/_background',	// refers to the partial view named '_post'
			'sortableAttributes'=>array(
				'fileName',
				'createDate',
			),
		)); ?>
	</div>

        <div class="cell">
            <h3><?php echo Yii::t('profile', 'Login Sound'); ?></h3>
            <?php
            echo CHtml::link(
                    Yii::t('app','None'),
                    '#',
                    array ( 'onclick'=>"setLoginSound(null,null,null); return false;")
            );
            $this->widget('zii.widgets.CListView', array(
                'dataProvider'=>$myLoginSounds,
                'template'=>'{items}{pager}',
                'itemView'=>'//media/_loginSound',
                'sortableAttributes'=>array(
                    'fileName',
                    'createDate',
                ),
            ))
            ?>
        </div>

        <div class="cell">
            <h3><?php echo Yii::t('profile', 'Notification Sound'); ?></h3>
            <?php
            echo CHtml::link(
                    Yii::t('app','None'),
                    '#',
                    array ( 'onclick'=>"setNotificationSound(null,null,null); return false;")
            );
            $this->widget('zii.widgets.CListView', array(
                'dataProvider'=>$myNotificationSounds,
                'template'=>'{items}{pager}',
                'itemView'=>'//media/_notificationSound',
                'sortableAttributes'=>array(
                    'fileName',
                    'createDate',
                ),
            ))
            ?>
        </div>

	<?php /*<div class="row">
		<?php echo $form->checkBox($model,'enableFullWidth'); ?>
		<?php echo $form->labelEx($model,'enableFullWidth',array('style'=>'display:inline;')); ?>
	</div> */ ?>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
	</div>
</div>
<?php $this->endWidget(); ?>
<div class="form">
	<div class="row">
		<h3><?php echo Yii::t('profile','Upload a Background'); ?></h3>
		<?php echo CHtml::form(array('site/upload','id'=>$model->id),'post',array('enctype'=>'multipart/form-data')); ?>
		<?php echo CHtml::dropDownList('private','public',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private'))); ?>
		<?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
		<?php echo CHtml::hiddenField('associationType', 'bg'); ?>
		<?php echo CHtml::fileField('upload','',array('id'=>'backgroundImg','onchange'=>"checkName();")); ?>
		<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'upload-button','disabled'=>'disabled','class'=>'x2-button')); ?>
		<?php echo CHtml::endForm(); ?>
	</div>
</div>

<!-- DO THIS -->
<div class="form">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Login or Notification Sound'); ?></h3>
        <?php echo CHtml::form(array('site/upload','id'=>$model->id),'post',array('enctype'=>'multipart/form-data')); ?>
        <?php echo CHtml::dropDownList('private','public',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private'))); ?>
        <?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
        <?php echo CHtml::dropDownList('associationType','Login',array('loginSound'=>'Login', 'notificationSound'=>'Notification'));?>
        <?php echo CHtml::fileField('upload','',array('id'=>'sound','onchange'=>"checkSoundName();")); ?>
        <?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'sound-upload-button','disabled'=>'disabled','class'=>'x2-button')); ?>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form">
    <div class="row">
        <h3><?php   echo Yii::t('profile','Unhide Tags'); ?></h3>
        <?php   foreach($allTags as &$tag) {
                    echo '<span class="tag unhide" tag-name="'.substr($tag['tag'],1).'">'.CHtml::link($tag['tag'],array('/search/search?term=%23'.substr($tag['tag'],1)), array('class'=>'x2-link x2-tag')).' </span>';
                }
        ?>
    </div>
</div>

<style>
.tag{
	-moz-border-radius:4px;
	-o-border-radius:4px;
	-webkit-border-radius:4px;
	border-radius:4px;
	border-style:solid;
	border-width:1px;
	border-color:gray;
	margin:2px 2px;
	display:block;
	float:left;
	padding:2px;
	background-color:#f0f0f0;
}
.tag a {
	text-decoration:none;
	color:black;
}

</style>
<script>
    $('.unhide').mouseenter(function(){
        var tag=$(this).attr('tag-name');
        var elem=$(this);
        var content='<span class="hide-link-span"><a href="#" class="hide-link" style="color:#06C;">[+]</a></span>';
        $(content).hide().delay(500).appendTo($(this)).fadeIn(500);
        $('.hide-link').click(function(e){
           e.preventDefault();
           $.ajax({
              url:'<?php echo CHtml::normalizeUrl(array('/profile/unhideTag')); ?>'+'?tag='+tag,
              success:function(){
                  $(elem).closest('.tag').fadeOut(500);
              }
           });

        });
    }).mouseleave(function(){
        $('.hide-link-span').remove();
    });
</script>







