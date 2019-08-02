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




Yii::app()->clientScript->registerScript('deleteActionJs', "
function deleteAction(actionId, type) {

	if(confirm('".Yii::t('app', 'Are you sure you want to delete this item?')."')) {
		$.ajax({
			url: '".CHtml::normalizeUrl(array('/actions/actions/delete'))."/'+actionId+'?ajax=1',
			type: 'POST',
			success: function(response) {
				if(response === 'success')
					$('#history-'+actionId).fadeOut(200,function() { 
                        $('#history-'+actionId).remove(); 
                    });

					// event detected by x2chart.js
					$(document).trigger ('deletedAction');
                    x2.TransactionalViewWidget.refreshByActionType (type);
				}
		});
	}
}
", CClientScript::POS_HEAD);
$themeUrl = Yii::app()->theme->getBaseUrl();
$data = Actions::model()->findByPk($data['id']);
if(!$data){
    return;
}
if(empty($data->type)){
    if($data->complete == 'Yes')
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

<?php
    // Use standard email icon for emailFrom
    $iconType = $type;
    if ($type === 'emailFrom')
        $iconType = 'email';
?>

<div class="view" id="history-<?php echo $data->id; ?>">
    <!--<div class="deleteButton">
<?php //echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button')  ?>
    </div>-->
    <div class="icon <?php echo $iconType; ?>">
    <div class="stacked-icon"></div>
    </div>
<div class='history-content-container'>
    <div class="header">
<?php

if(empty($data->type) || $data->type == 'weblead'){
    echo "<span style='color:grey;cursor:pointer' class='action-frame-link' data-action-id='{$data->id}'>";
    if($data->complete == 'Yes'){
        echo Yii::t('actions', 'Completed:')." </span>".Formatter::formatCompleteDate($data->completeDate);
    }else{
        if(!empty($data->dueDate)){
            echo Yii::t('actions', 'Due:')." </span>".Actions::parseStatus($data->dueDate).'</b>';
        }elseif(!empty($data->createDate)){
            echo Yii::t('actions', 'Created:')." </span>".Formatter::formatLongDateTime($data->createDate).'</b>';
        }else{
            echo "&nbsp;";
        }
    }
} elseif($data->type == 'workflow'){
    // $actionData = explode(':',$data->actionDescription);
    echo Yii::t('workflow', 'Process:').'<b> '.$data->workflow->name.'/'.$data->workflowStage->name.'</b> ';
}elseif($data->type == 'event'){
    echo '<b>'.CHtml::link(Yii::t('calendar', 'Event').': ', '#', array('class' => 'action-frame-link', 'data-action-id' => $data->id));
    if($data->allDay){
        echo Formatter::formatLongDate($data->dueDate);
        if($data->completeDate)
            echo ' - '.Formatter::formatLongDate($data->completeDate);
    } else{
        echo Formatter::formatLongDateTime($data->dueDate);
        if($data->completeDate)
            echo ' - '.Formatter::formatLongDateTime($data->completeDate);
    }
    echo '</b>';
}elseif($data->type == 'call'){
    echo Yii::t('actions', 'Call:').' '.($data->completeDate == $data->dueDate
            ? Formatter::formatCompleteDate($data->completeDate)
            : Formatter::formatTimeInterval(
                $data->dueDate,$data->completeDate,'{start}; {decHours} '.Yii::t('app','hours')));
}elseif($data->type == 'webactivity'){
    echo Yii::t('actions', 'This contact visited your website');
}elseif($data->type == 'time'){
    echo Formatter::formatTimeInterval($data->dueDate,$data->dueDate+$data->timeSpent);
} else{
    $timeFormat = Formatter::formatCompleteDate($data->getRelevantTimestamp());
    if($data->type == 'attachment') {
        if ($data->completedBy === 'Email') {
            $label = 'Email Message:';
        } else {
            $label = 'Attachment:';
        }
    } elseif($data->type == 'quotes') {
        $label = 'Quote:';
    } elseif(in_array($data->type, array('email', 'emailFrom', 'email_quote', 'email_invoice'))) {
        $label = 'Email Message:';
    } elseif(
        in_array($data->type, array('emailOpened', 'emailOpened_quote', 'email_opened_invoice'))) {

        $label = 'Email Opened:';
    }

    if(isset($label)) echo Yii::t('actions', $label).' ';
    echo $timeFormat;
}
?>
        <div class="buttons">
        <?php
        if(!Yii::app()->user->isGuest){
            if(empty($data->type) || $data->type == 'weblead'){
                if($data->complete == 'Yes' && 
                   Yii::app()->user->checkAccess('ActionsUncomplete',
                    array('assignedTo'=>$data->assignedTo))) {

                    echo CHtml::link(
                        X2Html::fa('fa-undo'), 
                        '#', array(   
                            'class' => 'uncomplete-button',
                            'title' => Yii::t('app', 'uncomplete'),
                            'data-action-id' => $data->id));
                } elseif(Yii::app()->user->checkAccess(
                    'ActionsComplete',array('assignedTo'=>$data->assignedTo))){

                    echo CHtml::link(
                        X2Html::fa('fa-check-circle'), 
                        '#', array(
                            'class' => 'complete-button', 
                            'title' => Yii::t('app', 'complete'),
                            'data-action-id' => $data->id));
                }
            }
            if($data->type != 'workflow'){
                if(Yii::app()->user->checkAccess(
                    'ActionsUpdate',array('assignedTo'=>$data->assignedTo))){

                    echo ($data->type != 'attachment' && $data->type != 'email') ?
                        ' '.CHtml::link(
                            X2Html::fa('fa-edit'), 
                            '#', array(
                                'class' => 'update-button', 'title' => Yii::t('app', 'edit'),
                                'data-action-id' => $data->id)) : '';
                }
                if(Yii::app()->user->checkAccess(
                    'ActionsDelete',array('assignedTo'=>$data->assignedTo))){

                    echo ' '.CHtml::link(
                        X2Html::fa('fa-times'), 
                        '#', array(
                            'onclick' => 'deleteAction('.
                                $data->id.', "'.$data->type.'"); return false',
                            'title' => Yii::t('app', 'delete')
                        ));
                }
            }
        }
        ?>
        </div>
    </div>
    <div class="description">
<?php
if($type == 'attachment' && $data->completedBy != 'Email') {
    echo Media::attachmentActionText($data, true, true);
} else if($type == 'workflow'){
    if($data->complete == 'Yes'){
        echo ' <b>'.Yii::t('workflow', 'Completed').'</b> '.Formatter::formatLongDateTime($data->completeDate);
    } else {
        echo ' <b>'.Yii::t('workflow', 'Started').'</b> '.Formatter::formatLongDateTime($data->createDate);
    }
    if(isset($data->actionDescription))
        echo '<br>'.CHtml::encode($data->actionDescription);
} elseif($type == 'webactivity'){
    if(!empty($data->actionDescription))
        echo CHtml::encode($data->actionDescription), '<br>';
    echo date('Y-m-d H:i:s', $data->completeDate);
} elseif(in_array($data->type, 
    array(
        'email',
        'emailFrom',
        'email_quote',
        'email_invoice',
        'emailOpened',
        'emailOpened_quote', 
        'emailOpened_invoice'
    ))) {

    $legacy = false;
    if(!preg_match(
        InlineEmail::insertedPattern('ah', '(.*)', 1, 'mis'), $data->actionDescription, $matches)){
        // Legacy pattern:
        preg_match('/<b>(.*?)<\/b>(.*)/mis', $data->actionDescription, $matches);
        $legacy = true;
    }
    if(!empty($matches)){
        $header = $matches[1];
        $body = '';
    }else{
        $header = empty($data->subject) ? '' : $data->subject."<br>";
        $body = empty($data->actionDescription) ? Yii::t('actions', "(Error displaying email)") : $data->actionDescription;
    }
    if($type == 'emailOpened'){
        echo "Contact has opened the following email:<br />";
    }
    $body = '<div class="historyEmailBody email-frame" id="'.$data->id.'">'.$body.'</div>';
    if(!Yii::app()->user->isGuest){
        echo $legacy ? '<strong>'.$header.'</strong> '.$body: $header.$body;
    }else{
        echo $body;
    }
    if ($type !== 'emailFrom') {
        echo ($legacy ? '<br />' : '').
            CHtml::link(
                '[View email]', '#', 
                array('onclick' => 'return false;', 'id' => $data->id, 'class' => 'email-frame'));
    }
}elseif($data->type == 'quotesDeleted'){
    echo $data->actionDescription;
}elseif($data->type == 'quotes'){
    $data->renderInlineViewLink ();
} else {
    if (isset($data->subject) && $data->subject !== '' && !ctype_space($data->subject)) {
        echo '<b>'.Yii::t('actions', 'Subject: ').'</b>'. Yii::app()->controller->convertUrls($data->subject);
        echo '<br />';
        echo '<br />';
    }
    echo Yii::app()->controller->convertUrls($data->actionDescription); // convert LF and CRLF to <br />
}
?>
    </div>

<?php if ($type == 'emailFrom') { ?>
        <button class='x2-button message-reply-button fa fa-reply fa-lg'
          title='<?php echo CHtml::encode (Yii::t('emailInboxes', 'Reply')); ?>'></button>
<?php } ?>

    <div class="footer">
        <?php
        if(isset($relationshipFlag) && $relationshipFlag && $data->associationId !== 0 && X2Model::getModelName($data->associationType) !== false){
            $relString=" | ".X2Model::getModelLink($data->associationId, X2Model::getModelName($data->associationType));
        }else{
            $relString="";
        }
        if(empty($data->type) || $data->type == 'weblead' || $data->type == 'workflow'){
            if($data->complete == 'Yes'){
                echo Yii::t('actions', 'Completed by {name}', array('{name}' => User::getUserLinks($data->completedBy))).$relString;
            }else{
                $userLink = User::getUserLinks($data->assignedTo);
                $userLink = empty($userLink) ? Yii::t('actions', 'Anyone') : $userLink;
                echo Yii::t('actions', 'Assigned to {name}', array('{name}' => $userLink)).$relString;
            }
        }else if(in_array($data->type,array('note','call','emailOpened','time'))){
            echo $data->completedBy == 'Guest' ? "Guest" : User::getUserLinks($data->completedBy).$relString;
            // echo ' '.Formatter::formatDate($data->completeDate);
        }else if($data->type == 'attachment' && $data->completedBy != 'Email'){
            echo Yii::t('media', 'Uploaded by {name}', array('{name}' => User::getUserLinks($data->completedBy))).$relString;
        }else if(in_array($data->type, array('email', 'emailFrom')) && $data->completedBy != 'Email'){
            echo Yii::t('media', ($data->type == 'email' ? 'Sent by {name}' : 'Sent to {name}'), array('{name}' => User::getUserLinks($data->completedBy))).$relString;
        }
        if (Yii::app()->settings->googleIntegration && isset($data->location)) {
            echo '<div class="right">';
            echo X2Html::fa('crosshairs').' '.$data->location->getLocationLink();
            echo '</div>';
        }
        ?>
    </div>

</div>

</div>

<?php
Yii::app()->clientScript->registerScript('replyToEmailJS', "
/**
 * Prepare the inline email form in reply to an email on the History widget
 */
function replyToLoggedEmail(emailElem) {
    var parent = $(emailElem).parent();
    var emailBody = parent.find ('.historyEmailBody').clone ();
    emailBody.find ('script, meta, html, head, title, body').remove ();

    var emailSubject = parent.find ('strong').text ();
    var emailDate = parent.find ('.header').text ();
    emailDate = emailDate.slice (emailDate.indexOf (':') + 1);
    var emailTo = $('#email-to').val();
    emailTo = emailTo.slice (0, emailTo.length - 2);

    var quotedBody$ = $('<blockquote>').append (emailBody.html ());
    var replyBody$ = $('<div>')
        .text(emailDate + ', ' + emailTo + ':')
        .prepend ('<br><br><br>')
        .append (quotedBody$);

    x2.inlineEmailEditorManager
        .toggleEmailForm ()
        .setSubjectField ('Re: ' + emailSubject)
        .prependToBody (replyBody$);
}

$(function() {
    $('.message-reply-button').click (function() {
        replyToLoggedEmail ($(this));
    });
});
", CClientScript::POS_READY);
