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
$profile=X2Model::model('Profile')->findByAttributes(array('username'=>$data->user));
if(isset($profile) && !empty($profile->avatar)){
	$avatar = Yii::app()->request->baseUrl.'/'.$profile->avatar;
}elseif(isset($profile)){
	$avatar = Yii::app()->request->baseUrl.'/uploads/default.jpg';
}else{
    $avatar="";
}
$themeUrl = Yii::app()->theme->getBaseUrl();
$imgUrl=$themeUrl."/images/eventIcons/".$data->type.".png";
$authorRecord = X2Model::model('User')->findByAttributes(array('username'=>$data->user));
if(isset($authorRecord)){
    if(Yii::app()->user->getName()==$data->user){
        $author=Yii::t('app','You');
    }else{
        $author = $authorRecord->name;
    }
}else{
    $author='';
}
$commentCount=X2Model::model('Events')->countByAttributes(array(
    'type'=>'comment',
    'associationType'=>'Events',
    'associationId'=>$data->id,
));
?>
    

    <?php
    if(!isset($noDateBreak) || !$noDateBreak){
        if(isset($_SESSION['lastDate']) && $_SESSION['lastDate']!==date("M j",$data->timestamp)){
            echo "<div class='view top-level date-break'>- ".(date("M j",time())==date("M j",$data->timestamp)?Yii::t('app',"Today"):Yii::app()->locale->dateFormatter->formatDateTime($data->timestamp,'long',null))." -</div>";
            $_SESSION['lastDate']=date("M j",$data->timestamp);
        }else{
            $_SESSION['lastDate']=date("M j",$data->timestamp);
        }
    }
    ?>
<div class="view top-level activity-feed <?php echo $data->important==1?"important":'' ?>">
    <div class="img-box" style="float:left;margin-right:5px;">
    <?php echo ($data->type!='feed')?CHtml::image($imgUrl,'',array('title'=>$data->parseType($data->type))):""; ?>
    <?php echo (!empty($avatar) && $data->type=='feed')?CHtml::image($avatar,'',array('height'=>32,'width'=>32)):""; ?>
    </div>
    <div class="event-text-box">
	<div class="deleteButton">
		<?php
        if(($data->type=='feed') && ($data->user==Yii::app()->user->getName()  || Yii::app()->user->checkAccess('AdminIndex'))){
            echo CHtml::link('['.Yii::t('app','Edit').']',array('profile/updatePost','id'=>$data->id))." ";
        }
		if((($data->user==Yii::app()->user->getName() || $data->associationId==Yii::app()->user->getId()) && ($data->type=='feed')) || Yii::app()->user->checkAccess('AdminIndex'))
			echo CHtml::link("[x]",'#',array('class'=>'delete-link','id'=>$data->id.'-delete'));?>
	</div>
    <span class="event-text">
	<?php
    if($data->associationType=='Media'){
        $authorRecord = X2Model::model('User')->findByAttributes(array('username'=>$data->user));
                if(Yii::app()->user->getName()==$data->user){
                    $author=Yii::t('app','You');
                }else{
                    $author = $authorRecord->name;
                }
                if($authorRecord->id != $data->associationId && $data->associationId != 0) {
                    $temp=Profile::model()->findByPk($data->associationId);
                    if(Yii::app()->user->getId()==$temp->id){
                        $recipient=Yii::t('app','You');
                    }else{
                        $recipient=$temp->fullName;
                    }
                    $modifier=' &raquo; ';
                } else {
                    $recipient='';
                    $modifier='';
                }
        echo CHtml::link($author,array('profile/view','id'=>$authorRecord->id)).$modifier.CHtml::link($recipient,array('profile/view','id'=>$data->associationId)).": ".MediaChild::attachmentSocialText($data->text,true,true);
    }else{
        echo x2base::convertLineBreaks(x2base::convertUrls($data->getText()));
    }
    ?>
    </span>
        <br />
    <span class="comment-age"><?php echo $this->formatFeedTimestamp($data->timestamp); ?></span> | <span>
        <?php echo CHtml::link(Yii::t('app','Show/add comments').' (<span id="'.$data->id.'-comment-count">'.($commentCount>0?"<b>".$commentCount."</b>":$commentCount).'</span>)','#',array('class'=>'comment-link','id'=>$data->id.'-link')); ?> 
        <?php echo CHtml::link(Yii::t('app','Hide comments'),'#',array('class'=>'comment-hide-link','id'=>$data->id.'-hide-link','style'=>'display:none;')); ?> 
        | 
        <?php 
        $important=($data->important==1);
        echo CHtml::link(Yii::t('app','Broadcast Event'),'#',array('class'=>'important-link x2-hint','id'=>$data->id.'-important-link','style'=>$important?'display:none;':'','title'=>Yii::t('app','Broadcasting an event will make it visible to any user viewing your events on the activity feed--regardless of type filters.')));
        echo CHtml::link(Yii::t('app','Cancel Broadcast'),'#',array('class'=>'unimportant-link','id'=>$data->id.'-unimportant-link','style'=>$important?'':'display:none;')); ?>
    </span>
	<?php 
        ?>
    </div>
    <div id="<?php echo $data->id ?>-comment-box" class="comment-box" style="display:none;clear:both;">
            <div id="<?php echo $data->id ?>-comments" ></div>
            <?php
            echo "<div style='margin-left:10px;margin-top:5px;'>".CHtml::link(CHtml::image(Yii::app()->theme->baseUrl.'/images/plus.gif')." ".Yii::t('app',"Add Comment"),'#',array('onclick'=>'$(this).toggle();$("#'.$data->id.'-comment-form").show();return false;'))."</div>";
            echo "<div style='margin-left:10px;display:none;' id='".$data->id."-comment-form'>";
            echo CHtml::beginForm(
                '',
                'get',
                array(
                    'id'=>'addReply-'.$data->id,
                    'onsubmit'=>'commentSubmit('.$data->id.');return false;'
                ));
            echo CHtml::textArea($data->id.'-comment','',array('class'=>'comment-textbox'));
            echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button comment-submit'));
            echo CHtml::endForm();
	
            echo "</div>";
	
	?>
        </div>
</div>
<?php
/*
<div class="view">
	<div class="deleteButton">
		<?php echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button') ?>
		<?php //echo CHtml::link("<img src='".Yii::app()->request->baseUrl."/images/deleteButton.png' />",array("deleteNote","id"=>$data->id)); ?>
	</div>

	<b><?php echo CHtml::encode($data->getAttributeLabel('createdBy')); ?>:</b>
	<?php echo CHtml::encode($data->createdBy); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('createDate')); ?>:</b>
	<?php echo CHtml::encode($data->createDate); ?>
	<br /><br />
	<b><?php echo CHtml::encode($data->getAttributeLabel('note')); ?>:</b>
	<?php echo CHtml::encode($data->note); ?>
	<br />
</div>
*/
?>