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

Yii::app()->clientScript->registerScript('deleteActionJs',"
function deleteAction(actionId) {

	if(confirm('".Yii::t('app','Are you sure you want to delete this item?')."')) {
		$.ajax({
			url: '" . CHtml::normalizeUrl(array('/actions/actions/delete')) . "/'+actionId+'?ajax=1',
			type: 'POST',
			//data: 'id='+actionId,
			success: function(response) {
				if(response=='Success')
					$('#history-'+actionId).fadeOut(200,function() { $('#history-'+actionId).remove(); });
				}
		});
	}
}
",CClientScript::POS_HEAD);

if(empty($data->type)) {
	if($data->complete=='Yes')
		$type = 'complete';
	else if($data->dueDate < time())
		$type = 'overdue';
	else
		$type = 'action';
} else
	$type = $data->type;

if($type == 'workflow') {

	$workflowRecord = CActiveRecord::model('Workflow')->findByPk($data->workflowId);
	$stageRecords = CActiveRecord::model('WorkflowStage')->findAllByAttributes(
		array('workflowId'=>$data->workflowId),
		new CDbCriteria(array('order'=>'id ASC'))
	);
	
	// see if this stage even exists; if not, delete this junk
	if($workflowRecord === null || $data->stageNumber < 1 || $data->stageNumber > count($stageRecords)) {
		$data->delete();
		return;
	}
}

// if($type == 'call') {
	// $type = 'note';
	// $data->type = 'note';
// }

?>



<div class="view" id="history-<?php echo $data->id; ?>">
	<!--<div class="deleteButton">
		<?php //echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button') ?>
	</div>-->
	<div class="icon <?php echo $type; ?>"></div>
	<div class="header">
		<?php
		if(empty($data->type) || $data->type=='weblead') {
			if ($data->complete=='Yes') {
				echo CHtml::link(Yii::t('actions','Action').':',array('/actions/'.$data->id)).' ';
				echo Yii::t('actions','Completed {date}',array('{date}'=>Actions::formatCompleteDate($data->completeDate)));
			} else {
				echo '<b>'.CHtml::link(Yii::t('actions','Action').':',array('/actions/'.$data->id)).' ';
				echo Actions::parseStatus($data->dueDate).'</b>';
			}
		} elseif ($data->type == 'attachment') {
			if($data->completedBy=='Email')
				echo Yii::t('actions','Email Message:').' '.Actions::formatCompleteDate($data->completeDate);
			else
				echo Yii::t('actions','Attachment:').' '.Actions::formatCompleteDate($data->completeDate);
				//User::getUserLinks($data->completedBy);
				
			echo ' ';
			
			//if ($data->complete=='Yes')
				//echo Actions::formatDate($data->completeDate);
			//else
				//echo Actions::parseStatus($data->dueDate);
		} elseif ($data->type == 'workflow') {
			// $actionData = explode(':',$data->actionDescription);
			echo Yii::t('workflow','Workflow:').'<b> '.$workflowRecord->name .'/'.$stageRecords[$data->stageNumber-1]->name.'</b> ';
		} elseif($data->type == 'email') {
			echo Yii::t('actions','Email Message:').' '.Actions::formatCompleteDate($data->completeDate);
		} elseif($data->type == 'emailOpened') {
			echo Yii::t('actions', 'Email Opened:'). ' '.Actions::formatCompleteDate($data->completeDate);
		} elseif($data->type == 'webactivity') {
			echo Yii::t('actions','This contact visited your website');
		} elseif($data->type == 'note') {
			echo Actions::formatCompleteDate($data->completeDate);
		} elseif($data->type == 'call') {
			echo Yii::t('actions','Call:').' '.Actions::formatCompleteDate($data->completeDate); //Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"),$data->completeDate);
		} elseif($data->type == 'event') {
			echo '<b>'.CHtml::link(Yii::t('calendar','Event').':',array('/actions/'.$data->id)).' ';
			if($data->allDay) {
				echo Yii::app()->controller->formatLongDate($data->dueDate);
				if($data->completeDate)
					echo ' - '. Yii::app()->controller->formatLongDate($data->completeDate);
			} else {
				echo Yii::app()->controller->formatLongDateTime($data->dueDate);
				if($data->completeDate)
					echo ' - '. Yii::app()->controller->formatLongDateTime($data->completeDate);
			}
			echo '</b>';
		}
		?>
		<div class="buttons">
			<?php
			if (empty($data->type) || $data->type=='weblead') {
				if ($data->complete=='Yes')
					echo CHtml::link('['.Yii::t('actions','Uncomplete').']',array('/actions/actions/uncomplete','id'=>$data->id,'redirect'=>1),array());
				else {
					echo CHtml::link('['.Yii::t('actions','Complete').']',array('/actions/actions/complete','id'=>$data->id,'redirect'=>1),array());
				}
			}
			if ($data->type != 'workflow'){
				echo $data->type!='attachment'?' '.CHtml::link('['.Yii::t('app','Edit').']',array('/actions/actions/update','id'=>$data->id,'redirect'=>1),array()) . ' ':"";
				echo ' '.CHtml::link('[x]','#',array('onclick'=>'deleteAction('.$data->id.'); return false'));
			}
			?>
		</div>
	</div>
	<div class="description">
		<?php
		if($type=='attachment' && $data->completedBy!='Email')
			echo MediaChild::attachmentActionText(Yii::app()->controller->convertUrls($data->actionDescription),true,true);
		else if($type=='workflow') {
		
			if(!empty($data->stageNumber) && !empty($data->workflowId) && $data->stageNumber <= count($stageRecords)) {
				if($data->complete == 'Yes')
					echo ' <b>'.Yii::t('workflow','Completed').'</b> '.date('Y-m-d H:i:s',$data->completeDate);
				else
					echo ' <b>'.Yii::t('workflow','Started').'</b> '.date('Y-m-d H:i:s',$data->createDate);
			}
			if(isset($data->actionDescription))
				echo '<br>'.$data->actionDescription;
			
		} elseif($type=='webactivity') {
			if(!empty($data->actionDescription))
				echo $data->actionDescription,'<br>';
			echo date('Y-m-d H:i:s',$data->completeDate);
		}elseif($type=='email' || $type=='emailOpened'){ 
            preg_match('/^<b>(.*?)<\/b>(.*)/mis',$data->actionDescription,$matches);
            if(!empty($matches)) {
                $subject = $matches[1];
				$body = $matches[2];
			} else {
                $subject = "No subject found";
				$body = "(Error displaying email)";
			}
            if($type=='emailOpened'){
                echo "Contact has opened the following email:<br />";
            }
            echo '<strong>'.$subject.'</strong> '.$body;
			echo '<br /><br />'.CHtml::link('[View email]',array('actions/view','id'=>$data->id),array('target'=>'_blank'));
        }else
			echo Yii::app()->controller->convertUrls(($data->actionDescription));	// convert LF and CRLF to <br />
		?>
	</div>
	<div class="footer">
	<?php if(empty($data->type) || $data->type=='weblead' || $data->type=='workflow') {
		if($data->complete == 'Yes') {
			echo Yii::t('actions','Completed by {name}',array('{name}'=>User::getUserLinks($data->completedBy)));
		} else {
			$userLink = User::getUserLinks($data->assignedTo);
			$userLink = empty($userLink)? Yii::t('actions','Anyone') : $userLink;
			echo Yii::t('actions','Assigned to {name}',array('{name}'=>$userLink));
		}
	} else if($data->type == 'note' || $data->type == 'call' || $data->type == 'emailOpened') {
		echo User::getUserLinks($data->completedBy);
		// echo ' '.Actions::formatDate($data->completeDate);
	} else if($data->type == 'attachment' && $data->completedBy!='Email') {
		echo Yii::t('media','Uploaded by {name}',array('{name}'=>User::getUserLinks($data->completedBy)));
	} else if($data->type == 'email' && $data->completedBy!='Email') {
		echo Yii::t('media','Sent by {name}',array('{name}'=>User::getUserLinks($data->completedBy)));
	}
	?>
	</div>

</div>
