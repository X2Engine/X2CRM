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
$themeUrl = Yii::app()->theme->getBaseUrl();
if(empty($data->type)) {
	if($data->complete=='Yes')
		$type = 'complete';
	else if($data->dueDate < time())
		$type = 'overdue';
	else
		$type = 'action';
} else
	$type = $data->type;


// if($type == 'call') {
	// $type = 'note';
	// $data->type = 'note';
// }

?>



<div class="view" id="history-<?php echo $data->id; ?>">
    <div class="sticky-icon x2-hint" title="<?php echo Yii::t('actions','This action has been marked as sticky and will remain at the top of the list.');?>" style="<?php echo $data->sticky?"":"display:none"; ?>"></div>
    <?php
    if($data->complete!='Yes'){
		if(empty($data->dueDate)){
			echo X2DateUtil::actionDate($data->createDate,$data->priority);
        }else{
			echo X2DateUtil::actionDate($data->dueDate,$data->priority);
        }
    }else{
		echo X2DateUtil::actionDate($data->completeDate,$data->priority,'Yes');
    }
?>
<div class="history-content-container">
	<div class="header">
		<?php
		if(empty($data->type) || $data->type=='weblead') {
			// if ($data->complete=='Yes') {
				// echo Yii::t('actions','Completed {date}',array('{date}'=>Formatter::formatCompleteDate($data->completeDate)));
			// } else {
                // if(!empty($data->dueDate) && (!isset($order) || $order=='due' || $order=='priority')){
                    // echo Yii::t('actions','Due: ').Actions::parseStatus($data->dueDate).'</b>';
                // }elseif((isset($dateType) && $order=='create') || (empty($data->dueDate) && !empty($data->createDate))){
                    // echo Yii::t('actions','Created: ').Formatter::formatLongDateTime($data->createDate).'</b>';
                // }else{
                    // echo "&nbsp;";
                // }
			// }
		} elseif ($data->type == 'attachment') {
			if($data->completedBy=='Email')
				echo Yii::t('actions','Email Message:').' '.Formatter::formatCompleteDate($data->completeDate);
			else
				echo Yii::t('actions','Attachment:').' '.Formatter::formatCompleteDate($data->completeDate);
				//User::getUserLinks($data->completedBy);

			echo ' ';

			//if ($data->complete=='Yes')
				//echo Formatter::formatDate($data->completeDate);
			//else
				//echo Actions::parseStatus($data->dueDate);
		} elseif ($data->type == 'workflow') {
			// $actionData = explode(':',$data->actionDescription);
			echo Yii::t('workflow','Process:').'<b> '.$data->workflow->name .'/'.$data->workflowStage->name.'</b> ';
		} elseif(in_array($data->type,array('email','emailFrom'))) {
			echo Yii::t('actions','Email Message:').' '.Formatter::formatCompleteDate($data->completeDate);
		} elseif($data->type == 'quotes') {
			echo Yii::t('actions','Quote:').' '.Formatter::formatCompleteDate($data->createDate);
		} elseif($data->type == 'emailOpened') {
			echo Yii::t('actions', 'Email Opened:'). ' '.Formatter::formatCompleteDate($data->completeDate);
		} elseif($data->type == 'webactivity') {
			echo Yii::t('actions','This contact visited your website');
		} elseif($data->type == 'note' && $data->complete=='Yes') {
			echo Formatter::formatCompleteDate($data->completeDate);
		} elseif($data->type == 'call' && $data->complete=='Yes') {
			echo Yii::t('actions','Call:').' '.Formatter::formatCompleteDate($data->completeDate); //Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"),$data->completeDate);
		} elseif($data->type == 'event') {
			echo '<b>'.CHtml::link(Yii::t('calendar','Event').':',array('/actions/actions/view','id'=>$data->id)).' ';
			if($data->allDay) {
				echo Formatter::formatLongDate($data->dueDate);
				if($data->completeDate)
					echo ' - '. Formatter::formatLongDate($data->completeDate);
			} else {
				echo Formatter::formatLongDateTime($data->dueDate);
				if($data->completeDate)
					echo ' - '. Formatter::formatLongDateTime($data->completeDate);
			}
			echo '</b>';
		}else{
            if ($data->complete=='Yes') {
				echo Yii::t('actions','Completed {date}',array('{date}'=>Formatter::formatCompleteDate($data->completeDate)));
			} else {
                if(!empty($data->dueDate) && (!isset($order) || $order=='due')){
                    echo Yii::t('actions','Due: ').Actions::parseStatus($data->dueDate).'</b>';
                }elseif((isset($dateType) && $order=='create') || (empty($data->dueDate) && !empty($data->createDate))){
                    echo Yii::t('actions','Created: ').Formatter::formatLongDateTime($data->createDate).'</b>';
                }else{
                    echo "&nbsp;";
                }
			}
        }
		?>
	</div>
	<div class="description" style="overflow:hidden;height:15px;text-overflow: ellipsis;white-space:nowrap;">
		<?php
		if($type=='attachment' && $data->completedBy!='Email')
			echo Media::attachmentActionText($data,true,true);
		else if($type=='workflow') {
                        if($data->complete == 'Yes'){
                            echo ' <b>'.Yii::t('workflow','Completed').'</b> '.date('Y-m-d H:i:s',$data->completeDate);
                        }else{
                            echo ' <b>'.Yii::t('workflow','Started').'</b> '.date('Y-m-d H:i:s',$data->createDate);
                        }
			if(isset($data->actionDescription))
				echo '<br>'.$data->actionDescription;

		} elseif($type=='webactivity') {
			if(!empty($data->actionDescription))
				echo $data->actionDescription,'<br>';
			echo date('Y-m-d H:i:s',$data->completeDate);
		} elseif(in_array($data->type,array('email','emailFrom')) || $type=='emailOpened') {
            preg_match('/<b>(.*?)<\/b>(.*)/mis',$data->actionDescription,$matches);
            if(!empty($matches)) {
                $subject = $matches[1];
				$body = '';
			} else {
                $subject = Yii::t('actions',"No subject found");
				$body = Yii::t('actions',"(Error displaying email)");
			}
            if($type=='emailOpened'){
                echo Yii::t('actions',"Contact has opened the following email:")."<br>";
            }
            echo '<strong>'.$subject.'</strong> '.$body;
			echo '<br /><br />'.CHtml::link(Yii::t('actions','[View Email]'),'#',array('onclick'=>'return false;','id'=>$data->id,'class'=>'email-frame'));
        } elseif($data->type == 'quotes') {
			echo CHtml::link(Yii::t('actions','[View Quote]'), '#', array('onclick' => 'return false;', 'id' => $data->id, 'class' => 'quote-frame'));
		} else
			echo Yii::app()->controller->convertUrls(CHtml::encode(empty($data->subject)?$data->actionDescription:$data->subject));	// convert LF and CRLF to <br />
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
		// echo ' '.Formatter::formatDate($data->completeDate);
	} else if($data->type == 'attachment' && $data->completedBy!='Email') {
		echo Yii::t('media','Uploaded by {name}',array('{name}'=>User::getUserLinks($data->completedBy)));
	} else if(in_array($data->type,array('email','emailFrom')) && $data->completedBy!='Email') {
		echo Yii::t('media',($data->type=='email'?'Sent by {name}':'Sent to {name}'),array('{name}'=>User::getUserLinks($data->completedBy)));
	}
	?>
	</div>
</div>

</div>

