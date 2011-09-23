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

// editor CSS file	
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/yui/build/editor/assets/skins/x2engine/simpleeditor.css');

// editor javascript files
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/yahoo-dom-event/yahoo-dom-event.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/element/element-min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/container/container_core-min.js');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/menu/menu-min.js');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/button/button-min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/editor/simpleeditor-min.js');

$pieces = explode(", ",$model->editPermissions);
$user = Yii::app()->user->getName();

$this->menu=array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','View Doc')),
);

if($user=='admin' || $user==$model->createdBy)
	$this->menu[] = array('label'=>Yii::t('docs','Edit Doc'), 'url'=>array('update', 'id'=>$model->id));
if($user=='admin')
	$this->menu[] = array('label'=>Yii::t('docs','Delete Doc'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('docs','Are you sure you want to delete this item?')));
if(array_search($user,$pieces)!=false || $user==$model->editPermissions || $user=='admin' || $user==$model->createdBy)
	$this->menu[]=array('label'=>Yii::t('docs','Edit Doc Permissions'), 'url'=>array('changePermissions', 'id'=>$model->id));
	
$this->menu[] = array('label'=>Yii::t('docs','Export Doc'),'url'=>array('exportToHtml','id'=>$model->id));
?>
<h2><?php echo Yii::t('docs','Document:'); ?> <b><?php echo CHtml::encode($model->title); ?></b></h2>
<div class="yui-skin-x2engine">
<textarea name="msgpost" id="msgpost" cols="50" rows="10"><?php echo$model->text; ?>
</textarea>
</div>
<script>
var myEditor = new YAHOO.widget.SimpleEditor('msgpost', {
	height: '800px',
	width: '590px',
	setDesignMode: false,
	handleSubmit: true,
	dompath: false, //Turns on the bar at the bottom
	animate: true, //Animates the opening, closing and moving of Editor windows
});

myEditor.render();
</script>