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

$menuOptions = array(
    'all', 'lists', 'create', 'view',
);
$this->insertMenu($menuOptions, null, $authParams);

?>
<h1><span style="color:#f00;font-weight:bold;margin-left: 5px;"><?php echo Yii::t('app', 'This record may be a duplicate!'); ?></span></h1>
<div class="page-title rounded-top"><h2><span class="no-bold"><?php echo Yii::t('app', 'You Entered:'); ?></span> <?php echo $newRecord->renderAttribute('name'); ?></h2>
    <?php
    if (Yii::app()->user->checkAccess('ContactsUpdate', $authParams) && $ref != 'create')
        echo CHtml::link(Yii::t('app', 'Edit'), $this->createUrl('update', array('id' => $newRecord->id)), array('class' => 'x2-button'));
    ?>
</div>
<?php 
$this->widget('DetailView', array(
    'model'   => $newRecord,
));
// $this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $newRecord, 'modelName' => 'contacts')); ?>
<div class="buttons">
    <?php
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('contacts', "Keep This Record"), $this->createUrl('/contacts/contacts/ignoreDuplicates'), array(
        'type' => 'POST',
        'data' => array('data' => json_encode($newRecord->attributes), 'ref' => $ref, 'action' => null),
        'success' => 'function(data){
		window.location="' . $this->createUrl('/contacts/contacts/view') . '?id="+data;
	}'
            ), array(
        'class' => 'x2-button highlight'
    ));
    echo "</span>";
    if (Yii::app()->user->checkAccess('ContactsUpdate', $authParams)) {
        echo "<span style='float:left'>";
        if ($count < 100) {
            echo CHtml::ajaxButton(Yii::t('contacts', "Keep + Hide Others"), $this->createUrl('/contacts/contacts/ignoreDuplicates'), array(
                'type' => 'POST',
                'data' => array('data' => json_encode($newRecord->attributes), 'ref' => $ref, 'action' => 'hideAll'),
                'success' => 'function(data){
                window.location="' . $this->createUrl('/contacts/contacts/view') . '?id="+data;
            }'
                    ), array(
                'class' => 'x2-button highlight',
                'confirm' => Yii::t('contacts', 'Are you sure you want to hide all other records?')
            ));
        } else {
            echo CHtml::link(Yii::t('contacts', 'Keep + Hide Others'), '#', array(
                'class' => 'x2-button x2-hint',
                'style' => 'margin-top:5px;color:black;',
                'title' => Yii::t('contacts', 'This operation is disabled because the data set is too large.'),
                'onclick' => 'return false;'
            ));
        }
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess('ContactsDelete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('contacts', "Keep + Delete Others"), $this->createUrl('/contacts/contacts/ignoreDuplicates'), array(
            'type' => 'POST',
            'data' => array('data' => json_encode($newRecord->attributes), 'ref' => $ref, 'action' => 'deleteAll'),
            'success' => 'function(data){
            window.location="' . $this->createUrl('/contacts/contacts/view') . '?id="+data;
        }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('contacts', 'Are you sure you want to delete all other records?')
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
    Yii::t('contacts', '{dupes} records shown out of {count} records found.', array(
        '{dupes}' => count($duplicates),
        '{count}' => $count,
    ))
    . "</h2>";
    echo CHtml::link(Yii::t('app', 'Show All'), "?showAll=true", array('class' => 'x2-button', 'confirm' => Yii::t('contacts', 'WARNING: loading too many records on this page may tie up the server significantly. Are you sure you want to continue?')));
    echo "</div>";
}
foreach ($duplicates as $duplicate) {
    echo '<div id="' . $duplicate->firstName . '-' . $duplicate->lastName . '-' . $duplicate->id . '">';
    echo '<div class="page-title rounded-top"><h2><span class="no-bold">', Yii::t('app', 'Possible Match:'), '</span> ';
    echo $duplicate->name, '</h2></div>';

    $this->widget('DetailView', array(
        'model'   => $duplicate,
    ));
    // $this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $duplicate, 'modelName' => 'contacts'));
    echo "<div style='margin-bottom:10px;'><span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('contacts', "Keep This Record"), $this->createUrl('/contacts/contacts/discardNew'), array(
        'type' => 'POST',
        'data' => array('ref' => $ref, 'action' => null, 'id' => $duplicate->id, 'newId' => $newRecord->id),
        'success' => 'function(data){
            window.location="' . $this->createUrl('/contacts/contacts/view') . '?id=' . $duplicate->id . '";
        }'
            ), array(
        'class' => 'x2-button highlight'
    ));
    echo "</span>";
    if (Yii::app()->user->checkAccess('ContactsUpdate', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('contacts', "Hide This Record"), $this->createUrl('/contacts/contacts/discardNew'), array(
            'type' => 'POST',
            'data' => array('ref' => $ref, 'action' => 'hideThis', 'id' => $duplicate->id, 'newId' => $newRecord->id),
            'success' => 'function(data){
                $("#' . $duplicate->firstName . "-" . $duplicate->lastName . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('contacts', 'Are you sure you want to hide this record?')
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess('ContactsDelete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('contacts', "Delete This Record"), $this->createUrl('/contacts/contacts/discardNew'), array(
            'type' => 'POST',
            'data' => array('ref' => $ref, 'action' => 'deleteThis', 'id' => $duplicate->id, 'newId' => $newRecord->id),
            'success' => 'function(data){
                $("#' . $duplicate->firstName . "-" . $duplicate->lastName . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('contacts', 'Are you sure you want to delete this record?'),
        ));
        echo "</span></div>";
    }
    echo "</div><br><br>";
}
