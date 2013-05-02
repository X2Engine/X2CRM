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
    if(!isset($_SESSION['stickyFlag']) || !$_SESSION['stickyFlag']){
        $_SESSION['stickyFlag']=true;
        echo "<div class='view top-level date-break'>- Sticky -</div>";
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
            echo CHtml::link(CHtml::image($themeUrl.'/images/icons/Edit.png'),array('profile/updatePost','id'=>$data->id))." ";
        }
		if((($data->user==Yii::app()->user->getName() || $data->associationId==Yii::app()->user->getId()) && ($data->type=='feed')) || Yii::app()->user->checkAccess('AdminIndex'))
			echo CHtml::link(CHtml::image($themeUrl.'/images/icons/Delete_Activity.png'),'#',array('class'=>'delete-link','id'=>$data->id.'-delete'));?>
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
        echo CHtml::link($author,array('profile/view','id'=>$authorRecord->id)).$modifier.CHtml::link($recipient,array('profile/view','id'=>$data->associationId)).": ".Media::attachmentSocialText($data->text,true,true);
    }else{
        echo Formatter::convertLineBreaks(x2base::convertUrls($data->getText()));
    }
    ?>
    </span>
        <br />
    <span class="comment-age"><?php echo Formatter::formatFeedTimestamp($data->timestamp); ?></span> | <span>
        <?php echo CHtml::link(Yii::t('app','Comments').' (<span id="'.$data->id.'-comment-count">'.($commentCount>0?"<b>".$commentCount."</b>":$commentCount).'</span>)','#',array('class'=>'comment-link','id'=>$data->id.'-link')); ?> 
        <?php echo CHtml::link(Yii::t('app','Hide comments'),'#',array('class'=>'comment-hide-link','id'=>$data->id.'-hide-link','style'=>'display:none;')); ?> 
        | 
        <?php 
        $important=($data->important==1);
        echo CHtml::link(Yii::t('app','Broadcast Event'),'#',array('class'=>'important-link x2-hint','id'=>$data->id.'-important-link','style'=>$important?'display:none;':'','title'=>Yii::t('app','Broadcasting an event will make it visible to any user viewing your events on the activity feed--regardless of type filters.')));
        echo CHtml::link(Yii::t('app','Cancel Broadcast'),'#',array('class'=>'unimportant-link','id'=>$data->id.'-unimportant-link','style'=>$important?'':'display:none;')); ?>
        
        <?php 
        if(Yii::app()->user->checkAccess('AdminIndex')){
            echo " | ";
            $sticky=($data->sticky==1);
            echo CHtml::link(Yii::t('app','Make Sticky'),'#',array('class'=>'sticky-link x2-hint','id'=>$data->id.'-sticky-link','style'=>$sticky?'display:none;':'','title'=>Yii::t('app','Making an event sticky will cause it to always show up at the top of the feed.')));
            echo CHtml::link(Yii::t('app','Undo Sticky'),'#',array('class'=>'unsticky-link','id'=>$data->id.'-unsticky-link','style'=>$sticky?'':'display:none;'));
        }?>
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
