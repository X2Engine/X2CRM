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






$menuOptions = array(
    'inbox', 'sharedInboxesIndex', 'createSharedInbox',
);
$this->insertMenu ($menuOptions);

$this->widget('X2GridView', array(
	'id'=>'shared-email-inboxes-grid',
    'enableQtips' => false,
	'title'=>Yii::t('emailInboxes', 'Shared Inboxes'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon emailInboxes x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}{massActionButtons}{summary}{topPager}'.
        '{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$emailInboxesDataProvider,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	'modelName'=>'EmailInboxes',
	'viewName'=>'sharedInboxesIndex',
	'defaultGvSettings'=>array(
		'gvCheckbox' => 30,
		'name' => 125,
		'credentialId' => 165,
        'assignedTo' => 125,
		'gvControls' => 73,
	),
    'specialColumns' => array (
        'gvControls' => array (
            'template' => '{update}{delete}',
            'buttons' => array (
                'update' => array (
                    'url' => 
                        'Yii::app()->controller->createUrl (
                            "updateSharedInbox", array ("id" => $data->id));',
                ),
                'delete' => array (
                    'url' => 
                        'Yii::app()->controller->createUrl (
                            "deleteSharedInbox", array ("id" => $data->id));',
                ),
            ),
            'id' => 'C_gvControls',
            'class' => 'X2ButtonColumn',
            'header' => Yii::t('app','Tools'),
        ),
        'name' => array (
            'name' => 'name',
            'header' => Yii::t('app', 'Name'),
            'value' => 'CHtml::link (
                $data->name,
                Yii::app()->controller->createUrl ("updateSharedInbox", array (  
                    "id" => $data->id
                )))',
            'type' => 'raw',
        ),
        'credentialId' => array (
            'name' => 'credentialId',
            'header' => Yii::t('app', 'Email Credentials') ,
			'value'=>'$data->credentialsName',
			'type'=>'raw',
        )
    ),
    'massActions'=>array(
        'MassDelete', 'MassUpdateFields',
    ),
	'enableControls'=>true,
	'enableTags'=>false,
	'fullscreen'=>true,
));
?>

