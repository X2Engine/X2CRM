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

$this->pageTitle = $newRecord->name;
$authParams['assignedTo'] = $newRecord->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View')),
));
?>
<div class="page-title icon contacts"></div>
<h1><span style="color:#f00;font-weight:bold;"><?php echo Yii::t('app','This record may be a duplicate!'); ?></span></h1>
<div class="page-title"><h2><span class="no-bold"><?php echo Yii::t('app','You Entered:'); ?></span> <?php echo $newRecord->firstName,' ',$newRecord->lastName; ?></h2></div>
<?php
if (Yii::app()->user->checkAccess('ContactsUpdate',$authParams) && $ref!='create')
	echo CHtml::link(Yii::t('app','Edit'),$this->createUrl('update',array('id'=>$newRecord->id)),array('class'=>'x2-button'));
echo "</h2>";
$this->renderPartial('application.components.views._detailView',array('model'=>$newRecord,'modelName'=>'contacts'));
echo "<span style='float:left'>";
echo CHtml::ajaxButton("Keep This Record",$this->createUrl('/contacts/ignoreDuplicates'),array(
	'type'=>'POST',
	'data'=>array('data'=>json_encode($newRecord->attributes),'ref'=>$ref,'action'=>null),
	'success'=>'function(data){
		window.location="'.$this->createUrl('/contacts/view?id=').'"+data;
	}'
),array(
	'class'=>'x2-button highlight'
));
echo "</span>";
if(Yii::app()->user->checkAccess('ContactsUpdate')){
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton("Keep + Hide Others",$this->createUrl('/contacts/ignoreDuplicates'),array(
        'type'=>'POST',
        'data'=>array('data'=>json_encode($newRecord->attributes),'ref'=>$ref,'action'=>'hideAll'),
        'success'=>'function(data){
            window.location="'.$this->createUrl('/contacts/view?id=').'"+data;
        }'
    ),array(
        'class'=>'x2-button highlight',
        'confirm'=>'Are you sure you want to hide all other records?'
    ));
    echo "</span>";
}
if(Yii::app()->user->checkAccess('ContactsDelete')){
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton("Keep + Delete Others",$this->createUrl('/contacts/ignoreDuplicates'),array(
        'type'=>'POST',
        'data'=>array('data'=>json_encode($newRecord->attributes),'ref'=>$ref,'action'=>'deleteAll'),
        'success'=>'function(data){
            window.location="'.$this->createUrl('/contacts/view?id=').'"+data;
        }'
    ),array(
        'class'=>'x2-button highlight',
        'confirm'=>'Are you sure you want to delete all other records?'
    ));
    echo "</span>";
}
?>
<div style="clear:both;"></div>
<br>
<?php
foreach($duplicates as $duplicate){
	echo '<div id="'.$duplicate->firstName.'-'.$duplicate->lastName.'">';
	echo '<div class="page-title"><h2><span class="no-bold">',Yii::t('app','Possible Match:'),'</span> ';
	echo $duplicate->firstName,' ',$duplicate->lastName,'</h2></div>';

	$this->renderPartial('application.components.views._detailView',array('model'=>$duplicate,'modelName'=>'contacts'));
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton("Keep This Record",$this->createUrl('/contacts/discardNew'),array(
        'type'=>'POST',
        'data'=>array('ref'=>$ref,'action'=>null,'id'=>$duplicate->id,'newId'=>$newRecord->id),
        'success'=>'function(data){
            window.location="'.$this->createUrl('/contacts/view?id=').'"+data;
        }'
    ),array(
        'class'=>'x2-button highlight'
    ));
    echo "</span>";
    if(Yii::app()->user->checkAccess('ContactsUpdate', array('assignedTo'=>$duplicate->assignedTo))){
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton("Hide This Record",$this->createUrl('/contacts/discardNew'),array(
            'type'=>'POST',
            'data'=>array('ref'=>$ref,'action'=>'hideThis','id'=>$duplicate->id,'newId'=>$newRecord->id),
            'success'=>'function(data){
                $("#'.$duplicate->firstName."-".$duplicate->lastName.'").hide();
            }'
        ),array(
            'class'=>'x2-button highlight',
            'confirm'=>'Are you sure you want to hide this record?'
        ));
        echo "</span>";
    }
    if(Yii::app()->user->checkAccess('ContactsDelete',array('assignedTo'=>$duplicate->assignedTo))){
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton("Delete This Record",$this->createUrl('/contacts/discardNew'),array(
            'type'=>'POST',
            'data'=>array('ref'=>$ref,'action'=>'deleteThis','id'=>$duplicate->id,'newId'=>$newRecord->id),
            'success'=>'function(data){
                $("#'.$duplicate->firstName."-".$duplicate->lastName.'").hide();
            }'
        ),array(
            'class'=>'x2-button highlight',
            'confirm'=>'Are you sure you want to delete this record?',
        ));
        echo "</span></div>";
    }
    echo "<br><br>";
}