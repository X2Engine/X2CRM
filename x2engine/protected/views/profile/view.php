<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$this->menu=array(
	array('label'=>Yii::t('profile','View Profile')),
	array('label'=>Yii::t('profile','Update Profile'), 'url'=>array('update','id'=>$model->id)),
);
if($model->id==Yii::app()->user->getId())
	$this->menu[]=array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id));
?>

<h2><?php echo Yii::t('profile','Profile:'); ?> <b><?php echo $model->fullName; ?></b></h2>

<?php $this->renderPartial('_detailView',array('model'=>$model)); ?>
<?php //echo CHtml::mailto(Yii::t('profile','Send E-Mail'),$model->emailAddress,array('class'=>'x2-button')); ?>

<div class="form">
	<?php $feed=new Social; 
	
	$feed->data = Yii::t('app','Enter text here...');

	$form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'action'=>array('addPost','id'=>$model->id,'redirect'=>'view'),
)); ?>
	<div class="float-row">
		<?php
		if($model->allowPost==1)
			echo $form->textArea($feed,'data',array('onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'width:558px;height:50px;color:#aaa;display:block;clear:both;'));
		else
			echo "This user does not allow posting on their feed.";
		if($model->allowPost==1) {
			echo $form->dropDownList($feed,'private',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private')));
			echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button'));
			echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','type'=>'button','onclick'=>"$('#attachments').toggle();return false;"));
		}
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>

<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('type'=>'feed','associationId'=>$model->id)); ?>
</div>
<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'../social/_viewFull',
	'template'=> '<h3>'.Yii::t('profile','Feed').'</h3>{summary}{sorter}{items}{pager}',
));
?>
<script>
var ar_ext = ['png', 'gif', 'jpg'];        // array with allowed extensions

function checkPictureExt(el, sbm) {
// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = el.value;
	var ar_name = name.split('.');

	// check the file extension
	var re = 0;
	for(var i=0; i<ar_ext.length; i++) {
		if(ar_ext[i] == ar_name[1]) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re==1) {
		// enable submit
		document.getElementById(sbm).disabled = false;
	}
	else {
		// delete the file name, disable Submit, Alert message
		el.value = '';
		document.getElementById(sbm).disabled = true;
		alert('".'+ ar_name[1]+ '" is not an file type allowed for upload');
	}
}
</script>
