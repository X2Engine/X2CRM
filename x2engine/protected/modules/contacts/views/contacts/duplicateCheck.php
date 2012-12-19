<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$this->pageTitle = $newRecord->name; 
$authParams['assignedTo'] = $newRecord->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View')),
));
?>
<h1><span style="color:red">This record may be a duplicate!</span></h1><br />
<h2><u>You Entered</u><br />
<?php

echo $newRecord->firstName." ".$newRecord->lastName;
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
<br />
<h2><u>Possible Matches</u></h2>
<?php
foreach($duplicates as $duplicate){
	echo "<div id=".$duplicate->firstName."-".$duplicate->lastName."><br /><h2>".$duplicate->firstName." ".$duplicate->lastName."</h2>";
	$this->renderPartial('application.components.views._detailView',array('model'=>$duplicate,'modelName'=>'contacts'));
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton("Discard New Record",$this->createUrl('/contacts/discardNew'),array(
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
    echo "<br /><br />";
}