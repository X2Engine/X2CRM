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




$this->pageTitle = $newRecord->renderAttribute('name');
$authParams['X2Model'] = $newRecord;
?>
<h1><span style="color:#f00;font-weight:bold;margin-left: 5px;"><?php echo Yii::t('app', 'This record may be a duplicate!'); ?></span></h1>
<div class="page-title rounded-top"><h2> <?php echo $newRecord->renderAttribute('name'); ?></h2>
    <?php
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams) && $ref != 'create') {
        echo CHtml::link(Yii::t('app', 'Edit'), $this->createUrl($moduleName . '/update', array('id' => $newRecord->id)), array('class' => 'x2-button', 'style' => 'vertical-align:baseline;'));
    }
    ?>
</div>
<?php 
$this->widget ('DetailView', array(
    'model' => $newRecord
));
$viewRedirect = (($ref == 'massDedupe') ? $this->createUrl('/admin/massDedupe') . '"' : $this->createUrl($moduleName . '/view') . '?id="+data;');
$indexRedirect = (($ref === 'massDedupe') ? $this->createUrl('/admin/massDedupe') : $this->createUrl($moduleName . '/index'));
//$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $newRecord, 'modelName' => $modelName)); ?>
<div class="buttons">
    <?php
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('app', "Keep"), $this->createUrl('/site/resolveDuplicates'), array(
        'type' => 'POST',
        'data' => array(
            'data' => json_encode($newRecord->attributes),
            'ref' => $ref,
            'action' => 'keepThis',
            'modifier' => null,
            'modelName' => $modelName,
        ),
        'success' => 'function(data){
		window.location="' . $viewRedirect . '
	}'
            ), array(
        'class' => 'x2-button highlight x2-hint',
        'title' => 'This record is not a duplicate.',
    ));
    echo "</span>";
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('app', "Mark as Duplicate"), $this->createUrl('/site/resolveDuplicates'), array(
        'type' => 'POST',
        'data' => array(
            'data' => json_encode($newRecord->attributes),
            'ref' => $ref,
            'action' => 'ignoreNew',
            'modifier' => null,
            'modelName' => $modelName,
        ),
        'success' => 'window.location="' . $indexRedirect . '"'
            ), array(
        'class' => 'x2-button highlight x2-hint',
        'title' => 'This record is a duplicate and should be hidden.',
    ));
    echo "</span>";
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Delete"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'data' => json_encode($newRecord->attributes),
                'ref' => $ref,
                'action' => 'deleteNew',
                'modifier' => null,
                'modelName' => $modelName,
            ),
            'success' => 'window.location="' . $indexRedirect . '"'
                ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 'This record is a duplicate and should be deleted.',
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Keep + Hide Others"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'data' => json_encode($newRecord->attributes),
                'ref' => $ref,
                'action' => 'keepThis',
                'modifier' => 'hideAll',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
                window.location="' . $viewRedirect . '
            }'
                ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 'This record is not a duplicate and all possible matches should be hidden.',
            'confirm' => Yii::t('app', 'Are you sure you want to hide all other records?')
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Keep + Delete Others"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'data' => json_encode($newRecord->attributes),
                'ref' => $ref,
                'action' => 'keepThis',
                'modifier' => 'deleteAll',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
            window.location="' . $viewRedirect . '
        }'
                ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 'This record is not a duplicate and all possible matches should be deleted.',
            'confirm' => Yii::t('app', 'Are you sure you want to delete all other records?')
        ));
        echo "</span>";
    }
    
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(
            Yii::t('app', "Merge Records"), $this->createUrl('/site/resolveDuplicates'), array(
                'type' => 'POST',
                'data' => array(
                    'data' => CJSON::encode ($newRecord->attributes),
                    'ref' => $ref,
                    'action' => 'mergeRecords',
                    'modelName' => $modelName,
                ),
                'success' => 'function(data){
                    window.location="' . $this->createUrl('/site/mergeRecords') .
                    '?modelName="+"' . urlencode ($modelName) . '"+"&"+data;
                }'
            ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 
                CHtml::encode (Yii::t('app', 
                    'This record is a duplicate and all possible matches should be merged together.'
                )),
        ));
        echo "</span>";
    }
    
    ?>
</div>
<div style="clear:both;"></div>
<br>
<?php
if ($count > count($duplicates)) {
    echo "<div style='margin-bottom:10px;margin-left:15px;'>";
    echo "<h2 style='color:red;display:inline;'>" .
    Yii::t('app', '{dupes} records shown out of {count} records found.', array(
        '{dupes}' => count($duplicates),
        '{count}' => $count,
    ))
    . "</h2>";
    echo CHtml::link(Yii::t('app', 'Show All'), "?showAll=true", array('class' => 'x2-button', 'confirm' => Yii::t('app', 'WARNING: loading too many records on this page may tie up the server significantly. Are you sure you want to continue?')));
    echo "</div>";
}
foreach ($duplicates as $duplicate) {
    echo '<div id="' . str_replace(' ', '-', $duplicate->name) . '-' . $duplicate->id . '">';
    echo '<div class="page-title rounded-top"><h2><span class="no-bold">', Yii::t('app', 'Possible Match:'), '</span> ';
    echo $duplicate->name, '</h2></div>';

    $this->widget ('DetailView', array(
        'model' => $duplicate, 
        'modelName' => $moduleName
    ));

    //$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $duplicate, 'modelName' => $moduleName));
    echo "<div style='margin-bottom:10px;'>";
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Hide This"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'ref' => $ref,
                'action' => null,
                'data' => json_encode($duplicate->attributes),
                'modifier' => 'hideThis',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
                $("#' . str_replace(' ', '-', $duplicate->name) . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('app', 'Are you sure you want to hide this record?')
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Delete This"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'ref' => $ref,
                'action' => null,
                'data' => json_encode($duplicate->attributes),
                'modifier' => 'deleteThis',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
                $("#' . str_replace(' ', '-', $duplicate->name) . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('app', 'Are you sure you want to delete this record?'),
        ));
        echo "</span></div>";
    }
    echo "</div><br><br>";
}
