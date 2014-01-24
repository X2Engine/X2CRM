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

$pieces = explode(", ",$model->editPermissions);
$user = Yii::app()->user->getName();

$actionMenu = array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('/docs/index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('/docs/create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('/docs/createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('/docs/createQuote')),
);
if(!$model->isNewRecord) {
    $actionMenu[] = array('label'=>Yii::t('docs','View'), 'url'=>array('/docs/view','id'=>$model->id));
    
}

if(!$model->isNewRecord){
    // Menu items that apply only to existing docs
    if(array_search($user, $pieces) !== false || $user == $model->editPermissions || Yii::app()->user->checkAccess('DocsDelete', array('createdBy' => $model->createdBy)))
        $actionMenu[] = array('label' => Yii::t('docs', 'Edit Doc'), 'url' => array('/docs/update', 'id' => $model->id));
    if(Yii::app()->user->checkAccess('DocsDelete', array('createdBy' => $model->createdBy)))
        $actionMenu[] = array('label' => Yii::t('docs', 'Delete Doc'), 'url' => 'javascript:void(0);', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => Yii::t('docs', 'Are you sure you want to delete this item?')));
    if(Yii::app()->user->checkAccess('DocsChangePermissions', array('createdBy' => $model->createdBy)))
        $actionMenu[] = array('label' => Yii::t('docs', 'Edit Doc Permissions'), 'url' => array('changePermissions', 'id' => $model->id));
    $actionMenu[] = array('label' => Yii::t('docs', 'Export Doc'), 'url' => array('exportToHtml', 'id' => $model->id));
}

$action = $this->action->id;
foreach(array_keys($actionMenu) as $ind) {
    $menuActionRoute = explode('/',$actionMenu[$ind]['url'][0]);
    $menuAction = array_pop($menuActionRoute);
    if($menuAction == $action) {
        unset($actionMenu[$ind]['url']);
    }
}

$this->actionMenu = $this->formatMenu($actionMenu);

?>
<div class="page-title icon docs"><h2><span class="no-bold"><?php echo $title; ?></span> <?php echo CHtml::encode($model->name); ?></h2>
<?php
if(!$model->isNewRecord){
    if($model->checkEditPermission() && $action != 'update'){
        echo CHtml::link('<span></span>', array('/docs/docs/update', 'id' => $model->id), array('class' => 'x2-button x2-hint icon edit right', 'title' => Yii::t('docs', 'Edit')));
    }
    echo CHtml::link('<span></span>', array('/docs/docs/create', 'duplicate' => $model->id), array('class' => 'x2-button icon copy right x2-hint', 'title' => Yii::t('docs', 'Make a copy')));
    echo "<br>\n";
}
?>
</div>