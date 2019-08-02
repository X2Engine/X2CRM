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






$this->pageTitle = Yii::t('marketing','Anonymous Contacts');
$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'weblead', 'webtracker', 'anoncontacts', 'fingerprints',
    'x2flow',
);
$this->insertMenu($menuOptions);
?>

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
	echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}

//Tours::tips (array(
//    array(
//        'content' => 'Unidentified website visitors will be entered into this list. Once they fill out a web lead form, all of their data will be converted to a full contact. You can limit the amount of contacts {here}.',
//        'type' => 'popup',
//        'replace' => array (
//            '{here}' => X2Html::link(
//                'here', 
//                Yii::app()->createUrl('/marketing/webTracker')
//            )
//        )
//    )
//));

$this->widget('X2GridView', array(
	'id'=>'anonContact-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'title'=>Yii::t('marketing','Anonymous Contacts'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon marketing x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        '{massActionButtons}'.
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'AnonContact',
	'viewName'=>'anonContacts',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
                'id' => 40,
                'fingerprintId' => 60,
                'trackingKey' => 120,
                'email' => 100,
                'leadscore' => 60,
                'createDate' => 80,
                'lastUpdated' => 80,
	),
	'specialColumns'=>array(
                'id' => array(
                    'name'=>'id',
                    'value'=>'CHtml::link($data->id, array("marketing/anonContactView", "id"=>$data->id))',
                    'type'=>'raw',
                ),
//		'name'=>array(
//			'name'=>'name',
//			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
//			'type'=>'raw',
//		),
//		'description'=>array(
//			'name'=>'description',
//			'header'=>Yii::t('marketing','Description'),
//			'value'=>'Formatter::trimText($data->description)',
//			'type'=>'raw',
//		),
	),
    'massActions'=>array('MassDelete', 'MassTag', 'MassTagRemove'),
	'enableControls'=>false,
	'fullscreen'=>true,
));

?>
