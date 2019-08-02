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
<div class="flush-grid-view">
    <?php
    if (is_null($model)) {
        ?><div class="page-title">
            <h2><?php echo Yii::t('admin', 'Locate Missing Records'); ?></h2>
        </div><?php
        echo '<p>'.Yii::t('admin', 'This tool allows you to search through all of '.
            'the records that have been marked as hidden. This is ordinarily performed by the '.
            'duplicate checker, but may occur inadvertantly if visibility is set to private '.
            'while the record is assigned to Anyone.').'</p>'; 
        echo '<p>', Yii::t('admin', 'Please select a model to search for missing records:').'</p>';
        echo '<ul>';
        foreach ($models as $modelName) {
            echo '<li>';
            echo CHtml::link($modelName, $this->createUrl('', array('modelName' => $modelName))).'<br />';
            echo '</li>';
        }
        echo '</ul><br />';
        ?>
    <?php } else {
        $this->widget('X2GridView', array(
            'id' => 'missing-records-grid',
	        'title' => Yii::t('admin','Locate Missing {model}', array('{model}'=>Modules::displayName(true, $moduleName))),
	        'buttons'=>array('clearFilters','columnSelector','autoResize'),
            'template'=> 
                '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
                '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
                '<div id="x2-gridview-page-title" '.
                 'class="page-title icon '.lcfirst($modelName).' x2-gridview-fixed-title">'.
                '{title}{buttons}{filterHint}'.
                '{massActionButtons}'.
                '{summary}{topPager}{items}{pager}',
            'fixedHeader' => true,
	        'modelName'=>$modelName,
	        'moduleName'=>$moduleName,
            'viewName' => 'missingRecords',
            'dataProvider' => $dataProvider,
	        'filter'=>$model,
	        'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
            'defaultGvSettings'=>array(
                'gvCheckbox' => 30,
                'name' => 244,
            ),
            'specialColumns'=>array(
                'name'=>array(
                    'name'=>'name',
                    'header'=>Yii::t('admin','Name'),
                    'value'=>'$data->link',
                    'type'=>'raw',
                ),
            ),
            'enableControls'=>true,
            'fullscreen'=>true,
        ));
    } ?>
</div>
