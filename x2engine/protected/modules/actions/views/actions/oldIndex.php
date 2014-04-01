<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

$menuItems = array(
	array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
	array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
	array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
	array('label'=>Yii::t('actions','Create'),'url'=>array('create')),
        array('label'=>Yii::t('actions', 'Import Actions'), 'url'=>array('admin/importModels', 'model'=>'Actions'), 'visible'=>Yii::app()->params->isAdmin),
        array('label'=>Yii::t('actions', 'Export Actions'), 'url'=>array('admin/exportModels', 'model'=>'Actions'), 'visible'=>Yii::app()->params->isAdmin),
);

if($this->route === 'actions/actions/index') {
	$heading = Yii::t('actions','Today\'s Actions');
	$dataProvider=$model->searchIndex();
	unset($menuItems[0]['url']);

} elseif($this->route === 'actions/actions/viewAll') {
	$heading = Yii::t('actions','All My Actions');
	$dataProvider=$model->searchAll();
	unset($menuItems[1]['url']);
} else {
	$heading = Yii::t('actions','Everyone\'s Actions');
	$dataProvider=$model->searchAllGroup();
	unset($menuItems[2]['url']);
}

$this->actionMenu = $this->formatMenu($menuItems);

// functions for completeing/uncompleting multiple selected actions
Yii::app()->clientScript->registerScript('oldActionsIndexScript', "
x2.actionFrames.afterActionUpdate = (function () {
    var fn = x2.actionFrames.afterActionUpdate;
    return function () {
        fn ();
        $('#actions-grid').yiiGridView ('update');
    };
}) ();
",CClientScript::POS_END);

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
$this->widget('X2GridView', array(
	'id'=>'actions-grid',
    'title'=>$heading,
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'enableQtips' => true,
    'qtipManager' => array (
        'X2QtipManager',
        'loadingText'=> addslashes(Yii::t('app','loading...')),
        'qtipSelector' => ".contact-name"
    ),
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
    'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon actions x2-gridview-fixed-title">'.
        '{title}{buttons}'.
        CHtml::link(
            Yii::t('actions','Switch to List'),
            array('index','toggleView'=>1),
            array('class'=>'x2-button')
        ).'{filterHint}'.'{summary}{topPager}'.
        '{items}{pager}',
    'fixedHeader' => true,
	'dataProvider'=>$dataProvider,
    'massActions' => array ('delete', 'tag', 'updateField', 'completeAction', 'uncompleteAction'),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Actions',
	'viewName'=>'actions',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'gvCheckbox' => 30,
		'actionDescription' => 140,
		'associationName' => 165,
		'assignedTo' => 105,
		'completedBy' => 86,
		'createDate' => 79,
		'dueDate' => 77,
		'lastUpdated' => 79,
	),
	'specialColumns'=>array(
		'actionDescription'=>array(
            'header'=>Yii::t('actions','Action Description'),
			'name'=>'actionDescription',
			'value'=>
                'CHtml::link(
                    ($data->type=="attachment") ? 
                        Media::attachmentActionText($data->actionDescription) : 
                        CHtml::encode(Formatter::trimText($data->actionDescription)),
                    array("view","id"=>$data->id))',
			'type'=>'raw',
            'filter' => false,
		),
		'associationName'=>array(
			'name'=>'associationName',
			'header'=>Yii::t('actions','Association Name'),
			'value'=>
                'strcasecmp($data->associationName,"None") == 0 ? 
                    Yii::t("app","None") : 
                    CHtml::link(
                        $data->associationName,
                        array("/".$data->associationType."/".$data->associationType."/".
                            $data->associationId),
                        array("class"=>($data->associationType=="contacts" ? 
                            "contact-name" : null)))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));
