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




/**
 * @file protected/admin/updater.php
 *
 * The multi-role view file for the web updater.
 */

// Base64-encoded icons
//
// Pending (empty gray box)
$noneImg='data:image/png;base64,'
        .'iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAAAXNSR0IArs4c6QAAAAZ'
        .'iS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KARUxBT'
        .'yiSDMAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAANklEQVQoz'
        .'2NgoAAwMjAwMMycOfM/qRrT09MZmSixmQXdNEIakF1Jkc2jmoeMZhZcCYDmNlMEAOBm'
        .'Cugmc5JZAAAAAElFTkSuQmCC';
// Success (green check mark)
$doneImg='data:image/png;base64,'
        .'iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAAAXNSR0IArs4c6QAAAAZ'
        .'iS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KAQAoNg'
        .'Kf4aYAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAABF0lEQVQoz'
        .'7XSvS5EURTF8d+5RKEhEQ0xRKLRiYZOI1N4BbV2ChKVuTOmkygknsATaIlSQhSIiB5R'
        .'+GimEsI9mmsGGYlb2N3aO/+Vs89eQZGqYAvrhr2bDYpW1YjgBpWkEFgzjvNcPYcC4LD'
        .'MiWAID6KJv8GpEk4xgBeZUQ33iao+NfO5e6cdR3GIARGsaLi3RiLoldmX2hNN/gAHBQ'
        .'cYAcGuum3QIEgN4Q5EJI69WdAlwxnGWmZd+lU1P2WCR9FV7hxlZiRuW2AU8S6YVtW00'
        .'35Y+PIpp5jKVWzNI4INdasaWOsEb+rRdCSY+tZnX1250xHaIVn2KlEWXXyZNwVLv10w'
        .'+RGEJ93mcJl3FtVcF8tvqiS14j/rA71RR/81pxD1AAAAAElFTkSuQmCC';
// Fail (red "X")
$failImg='data:image/png;base64,'
        .'iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAAAXNSR0IArs4c6QAAAAZ'
        .'iS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KAQApEb'
        .'6OZYwAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAABAklEQVQoz'
        .'4WTvUoEQRCEPxE1URTkDAQzTwVTEURfcvEJTEwML9zUBzAWk011NVA4hGOrDOw952YH'
        .'t2EYdqqrf6p7oWDOvjtG7Bs2DLXhoIQLzgwPJWDd8CSw4V1wnOE3hrnAgjrelmU+BtH'
        .'+dWgFh4FdGBaBKe67tMeJ4SMhy/BqODd8OQlsaAxrK+IIpoY3/UUvnecOJiVR6eAoMg'
        .'6IgmYBe/5PdcOVylk3GSFeFnrsT5tPYVm74bpXNUaSljwI4CTAqWBeUHXL0Ga9t4btN'
        .'PtMyRwFL4bdmMJJMkaFX5VvWR1AI9jXKjaNkq1YEBUEu6/6BcjsE3YMt4z9SdWQWPT7'
        .'AXm84ZpJAEdGAAAAAElFTkSuQmCC';
// Warning (yellow triangle with bang)
$warnImg = 'data:image/png;base64,'
        .'iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAAAXNSR0IArs4c6QAAA'
        .'AZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KAQAo'
        .'GalO3P8AAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAABE0lEQVQ'
        .'oz53RvW4TQRTF8d9dObQQbNxShjRrKYIHoKChSMUrUOE3SBWlhiLKS6RL2ijCz7BT5R'
        .'WQJSQKkFDCXgp2LWLZy8dpZkYz/3vOncuA2uLF0H0MgJNgmelRNfNl05tqa9X0BiIc/'
        .'rNzFt/xAJ+jNv5r5yyOOpD0uC3e/RHOstq+xSJqgYU0h2wG4Khpi9fJU0y7xqYRnmVx'
        .'ELMBuG0ILrpj3+ekW68HY0d4mexI2UOZxkjsto3nbbP9wz7EryIhjbqCo34qEU6q2YZ'
        .'RZeNVhqswrGS/qt1AtYoRjn8HM32MWiSLNfr9PecspsmnNdcl5jjDk95W+IGHUfvaw+'
        .'fYw91g5HQb4Rsuo3aqLf5LbeEnQNBUlqgp34YAAAAASUVORK5CYII=';
// Loading throbber:
$pendImg='data:image/gif;base64,'
        .'R0lGODlhDwAPAMZUAGtra29vb3x8fH19fX5+fn9/f4CAgIGBgYKCgomJiYqKiouLi4y'
        .'MjI2NjY+Pj5CQkJGRkZKSkpOTk5SUlJaWlpeXl5iYmJqampubm5ycnJ2dnZ6enp+fn6'
        .'CgoKGhoaKioqOjo6SkpKWlpaampqenp6mpqaysrK2tra6urq+vr7W1tba2tre3t7i4u'
        .'Lm5ubq6uru7u7y8vL29vb6+vr+/v8DAwMHBwcLCwsPDw8TExMXFxcbGxsfHx8jIyMnJ'
        .'ycrKysvLy8zMzM7OztLS0tPT09TU1NXV1dbW1tfX19jY2NnZ2dra2tvb29zc3N3d3d7'
        .'e3t/f3+Dg4OHh4eTk5P////////////////////////////////////////////////'
        .'///////////////////////////////////////////////////////////////////'
        .'////////////////////////////////////////////////////////////yH/C05F'
        .'VFNDQVBFMi4wAwEAAAAh/hFDcmVhdGVkIHdpdGggR0lNUAAh+QQFCgB/ACwAAAAADwA'
        .'PAAAHaoB/goN/FYSHfzAcggaCSIh/DwmCA39KSZB/Ghp/AS5KgktMghcffyoRh0xIoK'
        .'QeiEuYhyyZgzsuLi+QRkNGTbWCrYRLwoNIR4OXsoNLgkmPf0ytR0pGf0mjkErI1n9Iz'
        .'YjIf91K2ZnFg4EAIfkEBQoAfwAsAAAAAAsACAAAByqAf4KDhC+DHSCEGAeDHxSEBRiE'
        .'IimCHw+COTE6NYSEOj+eoqNKRkNHo4EAIfkEBQoAfwAsAAAAAA4ABwAABzKAf4KDhIW'
        .'CQYIVFYaDNjl/i4yCRC5Iikd/LYIlSkqCQIYeBQR/R0VMhhcCEoNPT5KCgQAh+QQFCg'
        .'B/ACwDAAAADAAIAAAHLIB/gkQygoaCUYIvL4IVh0pOf4t/KI6HR06TlpeRgiyNh6GCK'
        .'KKXpYcHqQeBACH5BAUKAH8ALAcAAAAIAAsAAAcmgD9/g4R/OIM2hYc7MYp/Pjx/IYWE'
        .'E5OUmJmagyaZEwcYmBYHL4EAIfkEBQoAfwAsBwAAAAgADgAABy6AR3+DhH9Ggz+Fgn8'
        .'8ioyJN4mFNzM5hX87l5qXExSbn5cXfySFJg6bEAqbJYSBACH5BAUKAH8ALAcAAAAIAA'
        .'8AAAc0gH+CTVOChn9Hh4JKiYqNS42GjJGDioYvLzSHmC8ylpYkH58VFSMHiBWHB6eph'
        .'quWBwCWgQAh+QQFCgB/ACwEAAcACwAIAAAHKYB/goN/R0ZGTYSKi4M1N4yCPTE2ghIh'
        .'giclhBgIhBQfgwcTkH8vpH+BACH5BAUKAH8ALAEACAAOAAcAAAcygH+Cg4R/SoQNDIW'
        .'CR0ZRGAIPizmCSkcrhCMVFX82lIuDnEQuPYUSLX+cgjxCoIIji4EAIfkEBQoAfwAsAA'
        .'AHAAwACAAABzOAEQeDf4WGhSAHAAeHjYY0jo0/kYUlLH8Vly9SR1CNFX8vL39LR4coo'
        .'KKFpoYjhTQyhoEAIfkEBQoAfwAsAAAEAAgACwAABymAIQ4ef4WGEQcZhosji46PkH8V'
        .'jhMbk4skhUMuf0aLMDV/R4uhnZBJgQAh+QQBCgB/ACwAAAEABwAOAAAHNYB/gn8Yg4M'
        .'ChoISD4l/C4I/Eo1/SYMrFZiDJJgVk4lCNjaDPC4yTZV/SzuCRkyGSkaJR4KBADs=';

$edition = isset($edition) ? $edition : $this->action->edition;
$unique_id = isset($unique_id) ? $unique_id : $this->action->uniqueId;
$ready = isset($ready) ? $ready : false;

// Delete this when appropriate:
if(!isset($message))
    $message = '';
if(!isset($files))
    $files = array();

Yii::app()->clientScript->registerCss('status-messages','
    #autorestore-disclaimer {
        display:none;
        margin-top: 10px;
    }
    #update-status {
        width:250px;
        float:right;
        border: 1px solid #666;
        padding:10px;
    }
    #update-ready {
        display: none;
    }
    #update-info {
        display: none;
        width:600px;
    }
    #update-info div.message {
        
    }
    #update-info .summary {
        font-weight:bold;
    }
    #update-info .details ul {
        margin: 0;
    }
    #update-info .details {
        display: none;
        max-height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    #update-info .details.warn {
        border-color: red;
    }
    #update-info .summary.warn {
        color: red;
    }
    #update-info .version-info-link {
        font-weight: bold;
    }
    #update-info .version-info-link a {
        text-decoration: none;
    }
    #update-info .version-info-link a:hover {
        text-decoration: underline;
    }
    #update-info .details {
        display: none;
        padding: 5px;
        border: 1px dashed #666;
        margin: 5px 0 10px 0;
    }
    #update-info ul.details {
        font-size: 11px;
        color: #333;
        list-style-type: none;
        font-family: "Lucida Console", Monaco, monospace;
    }
    #update-info .details.detail-default li {
        display:block;
    }
    #update-info .details.detail-default li:nth-child(odd) {
        background-color: #CCEEFF;
    }
    #update-info .details.detail-default li:hover {
        background-color: #FFFFCC;
    }
    #update-info a.show-hide-details {
        text-decoration: none;
    }
    #update-errors {
        border: 1px dashed #CC0000;
        display: none;
        float:left;
        margin-top: 10px;
        padding: 10px;
        width:600px;
    }
    #update-status div.update-message {
        display: block;
        min-height: 20px;
        background-position: left center;
        background-repeat: no-repeat;
        padding-left:20px;
        font-weight: bold;
    }
    #update-status div.update-message.current {
        color:black;
        background-image: url('.$noneImg.');
    }
    #update-status div.update-message.none {
        color: #999;
        background-image: url('.$noneImg.');
    }
    #update-status div.update-message.pend {
        color: #666;
        background-image: url('.$pendImg.');
    }
    #update-status div.update-message.done {
        background-image: url('.$doneImg.');
    }
    #update-status div.update-message.fail {
        background-image: url('.$failImg.');
    }
    #update-status div.update-message.warn {
        background-image: url('.$warnImg.');
    }

    #registration-form-container {
        display:none;
        width: 600px;
    }
    #registration-form-container hr {
        float:none;
        clear:none;
        width:100%;
    }
    #registration-form-container hr:first-child {
        display:none;
    }
    #registration-form-container span.registration-sub-text{
        display:none;
    }
    #registration-form {
        width: 600px;
        padding: 0;
        margin: 0;
    }
    #registration-form-container #registration-form div.row {
        clear: none;
    }

    #update-cancel {
        display: none;
    }
    ');
    ?>

<script>
var scenario="<?php echo $scenario; ?>";
var unique_id = '<?php echo $scenario == 'update' ? $unique_id : ''; ?>';
var edition = '<?php echo $edition; ?>';
var version = '<?php echo $this->action->version; ?>';
var ready = <?php echo $ready ? 'true' : 'false'; ?>;

var n_users = 0; // Used in the upgrade process

if (jQuery == undefined) {
	alert(<?php echo json_encode(Yii::t('admin','{jQuery} is required for the updater to work, and it is missing.',array('{jQuery}'=>'jQuery'))); ?>);
}

var inProgress = 0;
var msgIndex = 0;
var updateStatus;
var errorMessages;
var messages;
var queuedStages = {};
var updateLock;
var updateStageUrl = <?php echo json_encode($this->createUrl('/admin/updateStage')); ?>;
var updateHeader;

String.prototype.format = function() {
	var formatted = this;
	for (var i = 0; i < arguments.length; i++) {
		var regexp = new RegExp('\\{'+i+'\\}', 'gi');
		formatted = formatted.replace(regexp, arguments[i]);
	}
	return formatted;
};


//////////////
//FUNCTIONS //
//////////////

/**
 * Appends a message to the message box
 */
function addMessage(message,state) {
    msgIndex++;
    $('<div>',{'id':'update-message-'+msgIndex,'class':(typeof state == 'undefined' ? 'pending' : state)+' update-message'}).text(message).appendTo(updateStatus);
    return msgIndex;
}

/**
 * Clears out all messages and resets the message counter.
 */
function clearMessages() {
    updateStatus.html('').fadeOut(300);
    msgIndex = 0;
}

/**
 * Appends an error message to the status box.
 * @param string message The message HTML to put in the error box
 * @param 
 */
function errorMessage(message,header,color) {
    if (typeof color=='undefined') {
        color = 'red';
    }
    if(typeof header == 'undefined') {
        header = <?php echo json_encode(Yii::t('admin','Errors encountered.')); ?>;
    }
    updateHeader.text(header);
    $('<div>',{'html':message,'style':'color:'+color}).appendTo(errorMessages.show());
}

/**
 * Returns the current state of the progress. Used with window.onbeforeunload
 * because the re-declaration of it would use the initial state (0) instead of
 * whatever it is currently set to.
 */
function isActive() {
    return inProgress;
}

/**
 * Composes an object with query parameters for the jQuery AJAX configuration.
 */
function getStageParams(stage) {
    return {
        'stage':stage,
        'scenario':scenario,
        'version':version,
        'uniqueId':unique_id,
        'autoRestore':$('#auto-restore').is(':checked')
    };
}

function messageHasState(id,state){
    return updateStatus.find('#update-message-'+id).hasClass(state);
}

/**
 * Changes the "state" of an existing installer message, i.e. from pending to
 * complete or error/warning
 */
function messageState(id,state) {
    updateStatus.find('#update-message-'+id)
        .removeClass('none pend done fail warn')
        .addClass(state);
}

/**
 * Put an update detail message into the update info area (i.e. list of files).
 *
 * Expects:
 * @var string summary "title" for the details
 * @var mixed details markup or list of items for the details
 * @var string cssClass Optional class for details and summary messages. If
 *  specified, default styling of the detail's contents won't apply unless the
 *  class explicitly includes the "detail-default" class.
 * Structure:
 * div.message
 * |-> span.summary
 * |-> a.show-hide-details
 * |-> ul.details
 */
function newUpdateDetailMessage(summary,details,summaryCssClass) {
    summaryCssClass = typeof summaryCssClass == 'undefined' ? ' detail-default' : ' '+summaryCssClass;
    var detailContent;
    if(typeof details == 'object') {
        detailContent = $('<ul>',{'class':'details'+summaryCssClass});
        for(var i=0;i<details.length;i++) {
            $('<li>',{'text':details[i]}).appendTo(detailContent);
        }
    } else {
        detailContent = $('<div>',{'class':'details'+summaryCssClass,'html':details});
    }
    // Put it in:
    $('<div>',{'class':'message'})
        .append($('<span>',{'class':'summary'+summaryCssClass,'text':summary}))
        .append($('<a>',{'href':'javascript:void(0);','class':'show-hide-details','text':' [ '+<?php echo json_encode(Yii::t('app','Show')); ?>+' ]'}))
        .append(detailContent)
        .appendTo(updateInfo);
}

function proceedToNextStage(i,state) {
    state = typeof state == 'undefined' ? 'done' : state;
    messageState(i,state);
    runQueuedStage(i+1);
}

function runQueuedStage(i) {
    if(typeof queuedStages[i] != 'undefined'){
        messageState(i,'pend');
        queuedStages[i](i);
    }
}

/**
 * Prepares a list of installation stages to run.
 *
 * The argument "config" must be an array. Each element is an object with the
 * properties "message" (name of the operation) and "callback" (the function
 * to be called to complete that stage).
 */
function queueStages(config) {
    for(var i=0;i<config.length;i++) {
        var ind = addMessage(config[i].message,'none');
        queuedStages[ind] = config[i].callback;
    }
}

///////////////////////
// STAGING FUNCTIONS //
///////////////////////
//
// Each corresponds to a "stage" in the process of updating/upgrading. They
// each should take one argument (only): the ID (index) of the message that
// displays their status.

/**
 * Get/display update data for the user's review.
 *
 * Message:
 * In update/upgrade, if no package ready: "Request update data"
 * In update/upgrade, if package ready: "Checking update data
 */
function stageCheck(i) {
    var ind = i;
	$.ajax({
        url: updateStageUrl,
        data: getStageParams('check'),
        dataType:'json'
    }).done(function(d){
        if(typeof d.allClear == 'undefined' || d.error) {
            messageState(ind,'fail');
            errorMessage(d.message);
            return 0;
        }
        manifest = d.manifest;

        updateHeader.text(<?php echo json_encode(Yii::t('admin','Confirm Changes to be Applied:')); ?>);
        
        // Version info links:
        var releaseGitBaseUrl = 'https://github.com/X2Engine/X2Engine/tree/'+d.manifest.targetVersion+'/';
        var releaseInfoLinkContainer = $('<ul>');
        <?php foreach(array('README','CHANGELOG','RELEASE-NOTES') as $document) { ?>
        releaseInfoLinkContainer.append($('<li>',{'class':'version-info-link',html:$('<a>',{href:releaseGitBaseUrl+<?php echo json_encode($document.'.md'); ?>,target:'_blank',text:<?php echo json_encode(strtolower(str_replace('-',' ',$document))); ?>})}));
        <?php } ?>
        newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','Version Info')) ?>,releaseInfoLinkContainer.html(),'no-style');
        updateInfo.find('.no-style').siblings('a').trigger('click.toggle');

        // Files to copy
        if(d.manifest.fileList.length > 0)
            newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','Files to be changed or added: {0}')); ?>.format(d.manifest.fileList.length),d.manifest.fileList);

        // Files to delete
        if(d.manifest.deletionList.length > 0)
            newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','Files to be deleted: {0}')); ?>.format(d.manifest.deletionList.length),d.manifest.deletionList);

        // SQL commands to run:
        var sqlList = [];
        for(var i=0;i<d.manifest.data.length;i++) {
            for(var j=0;j<d.manifest.data[i].<?php echo $sqlListName = ($scenario == 'upgrade' ? 'sqlUpgrade':'sqlList'); ?>.length;j++) {
                sqlList.push(d.manifest.data[i].<?php echo $sqlListName; ?>[j]);
            }
        }
        if(sqlList.length > 0) {
            newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','SQL commands to run: {0}')); ?>.format(sqlList.length),sqlList);
        }

        // Update scripts to run:
        var scriptList = [];
        for(var i=0;i<d.manifest.data.length;i++) {
            for(var j=0;j<d.manifest.data[i].migrationScripts.length;j++) {
                scriptList.push(d.manifest.data[i].migrationScripts[j]);
            }
        }
        if(scriptList.length > 0) {
            newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','Migration scripts to run: {0}')); ?>.format(scriptList.length),scriptList);
        }

        // Add compatibility check messages:
        if(!d.allClear) {
            newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','Compatibility issues detected.')); ?>,d.message,'warn');
        }

        $('div#update-ready').show();
        updateInfo.show();

        proceedToNextStage(ind,d.allClear ? 'done' : 'warn');

    }).fail(webErrorMessage(ind));
}

/**
 * Redirects to a location post-completion, with a countdown.
 *
 * This should always be the last stage, so there's no call to "proceedToNextStage"
 * in this function.
 */
function stageCompletion(i) {
    var ind = i;
    messageState(ind,'pend');
    var secondsBefore = 3;
    var countdown = function() {
        if(secondsBefore==0) {
            messageState(ind,'done');
            window.location.href = '<?php echo CHtml::normalizeUrl(array('/site/page','view'=>'about')); ?>';
        } else {
            updateHeader.text(<?php echo json_encode(Yii::t('admin','All operations complete. Redirecting in {0}')); ?>.format(secondsBefore.toString()));
            secondsBefore--;
            setTimeout(countdown,1000);
        }
    };
    countdown();
}

/**
 * Download an update package.
 */
function stageDownload(i) {
    var ind = i;
    $.ajax({
        url: updateStageUrl,
        data: getStageParams('download'),
        dataType:'json'
    }).done(function(d){
        if(d.error) {
            messageState(ind,'warn');
            errorMessage(d.message);
        } else {
            proceedToNextStage(ind);
        }
    }).fail(webErrorMessage(ind));
}

/**
 * Enact changes to X2Engine.
 */
function stageEnact(i) {
    if(inProgress) { // Duplicate request to server 
        return 0;
    }
    // Warning message will be displayed to the user if they try to leave the page
    inProgress = 1;
    var ind = i;
    $.ajax({
        url: updateStageUrl,
        data: getStageParams('enact'),
        dataType:'json'
    }).done(function(d){
        inProgress = 0;
        if(d.error) {
            messageState(ind,'fail');
            errorMessage(d.message);
        } else {
            proceedToNextStage(ind);
        }
    }).fail(webErrorMessage(ind));;
}

/**
 * In the case of upgrading, this presents the registration form, attaches
 * event handlers for input, etc.
 */
function stageRegister(i) {
    var ind = i;
    updateHeader.text(<?php echo json_encode(Yii::t('admin','Registration')); ?>);
    messageState(i,'current');
    $('#registration-form-container').show().bind('submit.nextStage',function(){
        var that = $(this);
        unique_id = that.find('input[name="unique_id"]').val();
        edition = that.find('input[name="edition"]').val();
        proceedToNextStage(ind);
    });
}

/**
 * Pause and wait for the user's approval. This should typically be the last
 * stage with user interaction before attempting to finish the update/upgrade.
 */
function stageReview(i) {
    messageState(i,'current');
    var ind = i;
    // Attach event handler to the "Apply" button that hides itself and runs the "enact" stage.
    $('#update-button').bind('click.nextStage',function(e){
        e.preventDefault();
        $('#database-backup').hide();
        $('#update-cancel').hide();
        $(this).fadeOut(300,function(){
            $('html, body').animate({scrollTop:0});
        });
        updateHeader.text(<?php echo json_encode($scenario == 'update' ? Yii::t('admin','Updating X2Engine...'):Yii::t('admin','Upgrading X2Engine...')); ?>);
        proceedToNextStage(ind);
    });
}

/**
 * Unpack the downloaded update package
 */
function stageUnpack(i) {
    var ind = i;
    $.ajax({
        url: updateStageUrl,
        data: getStageParams('unpack'),
        dataType:'json'
    }).done(function(d){
        if(d.error) {
            messageState(ind,'fail');
            errorMessage(d.message);
        } else {
            proceedToNextStage(ind);
        }
    }).fail(webErrorMessage(ind));
}

/**
 * Verify package contents
 */
function stageVerify(i) {
    $.ajax({
        url: updateStageUrl,
        data: getStageParams('verify'),
        dataType:'json'
    }).done(function(d){
        if(d.error && typeof d.filesByStatus != 'undefined') {
            messageState(i,'warn');
            var header = <?php echo json_encode(Yii::t('admin','Update package cannot be used.')); ?>;
            if(d.files == false) {
                errorMessage(d.message,header);
            } else {
                updateHeader.text(header);
                var i_corrupt = d.statusCodes.corrupt;
                var i_missing = d.statusCodes.missing;
                if(d.filesStatus[i_corrupt] > 0) {
                    newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','{0} files were found to be corrupt')); ?>.format(d.filesStatus[i_corrupt]),d.filesByStatus[i_corrupt],'detail-default warn');
                }
                if(d.filesStatus[i_missing] > 0) {
                    newUpdateDetailMessage(<?php echo json_encode(Yii::t('admin','{0} files are missing')); ?>.format(d.filesStatus[i_missing]),d.filesByStatus[i_missing],'detail-default warn');
                }
            }
            updateInfo.show();
        } else if(d.error) {
            messageState(i,'fail');
            errorMessage(d.message);
        } else {
            proceedToNextStage(i);
        }
    }).fail(webErrorMessage(i));
}

/**
 * This function used by the upgrade/pro registration form
 */
function submitExternalForm() {
    $('#registration-form-container').slideUp().trigger('submit.nextStage');

}

/**
 * Returns an appropriate function for use as a "fail" callback in jQuery.ajax objects
 */
function webErrorMessage(i) {
    return (function(j) {
        return function(jqXHR,textStatus,message) {
            messageState(j,'fail');
            inProgress = 0;
            errorMessage(<?php echo json_encode(Yii::t('admin','Could not complete operation because the request to the server failed or timed out.')); ?>+' ('+textStatus+' '+jqXHR.errorCode+' '+message+')');
        }
    })(i);
}

///////////////////
// END FUNCTIONS //
///////////////////

// Actions to take on page load:
$(function() {
    updateStatus = $('#update-status');
    errorMessages = $('#update-errors');
    updateHeader = $('#update-header');
    updateInfo = $('div#update-info');

    $('#auto-restore').change(function() {
        if($(this).is(':checked'))
            $('#autorestore-disclaimer').fadeIn(300);
        else
            $('#autorestore-disclaimer').fadeOut(300);
    });


    updateInfo.on('click.toggle','a.show-hide-details',function() {
        var that = $(this);
        var detailsContainer = that.siblings('.details');
        var showText = <?php echo json_encode(Yii::t('app','Show')); ?>;
        var hideText = <?php echo json_encode(Yii::t('app','Hide')); ?>;

        if(detailsContainer.is(':hidden')) {
            var buttonText = that.text().replace('[ '+showText,'[ '+hideText);
            that.text(buttonText);
            detailsContainer.show();
        } else {
            var buttonText = that.text().replace('[ '+hideText,'[ '+showText);
            that.text(buttonText);
            detailsContainer.hide();
        }
    });

    if(typeof x2 == 'undefined') {
        x2 = {Notifs:{}};
    }
    if(typeof x2.Notifs == 'undefined'){
        x2.Notifs = {};
    }
    x2.fetchNotificationUpdates = false;
    x2.Notifs.fetchNotificationUpdates = false;

    // Ready-state-specific JavaScript:
    if(ready) {
        // Only show the "cancel" button if there's a package in the filesystem
        $('#update-cancel').css({display:'inline-block'});
        // Queue the appropriate stages
        queueStages([
            {callback:stageVerify,message:<?php echo json_encode(Yii::t('admin','Verify package contents')); ?>},
            {callback:stageCheck,message:<?php echo json_encode(Yii::t('admin','Check update data')); ?>},
            {callback:stageReview,message:<?php echo json_encode(Yii::t('admin','Review and confirm changes')); ?>},
            {callback:stageEnact,message:<?php echo json_encode(Yii::t('admin','Apply changes'));?>},
            {callback:stageCompletion,message:<?php echo json_encode(Yii::t('admin','Reload')); ?>}
        ]);
    } else {
        if(scenario == 'upgrade') {
            queueStages([
                {callback:stageRegister,message:<?php echo json_encode(Yii::t('admin','Product registration')); ?>}
            ]);
        }
        queueStages([
            {callback:stageCheck,message:<?php echo json_encode(Yii::t('admin','Obtain and check data')); ?>},
            {callback:stageReview,message:<?php echo json_encode(Yii::t('admin','Review and confirm changes')); ?>},
            {callback:stageDownload,message:<?php echo json_encode(Yii::t('admin','Download package'));?>},
            {callback:stageUnpack,message:<?php echo json_encode(Yii::t('admin','Extract package'));?>},
            {callback:stageVerify,message:<?php echo json_encode(Yii::t('admin','Verify package contents')); ?>},
            {callback:stageEnact,message:<?php echo json_encode(Yii::t('admin','Apply changes'));?>},
            {callback:stageCompletion,message:<?php echo json_encode(Yii::t('admin','Reload')); ?>}
        ]);
    }
    runQueuedStage(1);


});

/**
 * Discourage the user from navigating away if there's something in progress.
 */
window.onbeforeunload = function(e) {
    if(isActive())
        return <?php echo json_encode(Yii::t('admin','The updater is currently applying changes to X2Engine. If you interrupt it, you could cause SEVERE damage that may include (but would not be limited to) loss of data.')); ?>;
}

</script>

<div class="page-title"><h2><?php
if(in_array($scenario,array('message','error'))) {
    echo $message;
} else {
    if($scenario == 'update'){
        echo Yii::t('admin', 'Update X2Engine to {latestVer} from {currentVer}', array('{latestVer}' => $latestVersion, '{currentVer}' => $version));
    }elseif($scenario == 'upgrade'){
        echo Yii::t('admin', 'Upgrade X2Engine');
    } else {
        echo Yii::t('admin','X2Engine Updater');
    }
}
?></h2></div>
<div class="span-24 updater-page">

<div class="form">



<?php if (in_array($scenario, array('update', 'upgrade'))):
////////////////////////////////////////////
// Render Updates/Upgrades Form Info View //
////////////////////////////////////////////
?>

<!-- Progress box -->
<div id="update-status">
</div>

<h3 id="update-header"><?php echo Yii::t('admin','Initializing...'); ?></h3>
<!-- This div to be populated with update info -->
<div id="update-info">
</div><!-- #update-info -->

<!-- Parts of the updater to be displayed when ready to proceed with the update/upgrade, including compatibility messages. -->
<div id="update-ready">

<!-- Section w/disclaimers and "before proceeding" messages -->
<div id="update-disclaimers">
<h3><?php echo Yii::t('admin', 'Before Proceeding'); ?></h3>

<?php
$timeout = (int) ini_get('max_execution_time');
if($timeout < 300 && $timeout != 0 && $timeout != -1) { ?>
<strong><?php echo Yii::t('admin','Disclaimer'); ?></strong><br />
<?php echo Yii::t('admin',"Your web server's maximum request time, {n} seconds, may not be long enough for safely using the web-based updater, especially if updating from a very old version.",array('{n}'=>$timeout)).' ';
$wikiPage = CHtml::link('"Using the Web Updater"','http://wiki.x2engine.com/wiki/Software_Updates_and_Upgrades#Using_the_Web_Updater',array('target'=>'_blank'));
echo Yii::t('admin','For more information, see {article} in the official X2Engine updating guide.',array('{article}'=>$wikiPage));
?><br /><br />
<?php } ?>

<strong><?php echo Yii::t('admin', 'The following precautions are highly recommended:') ?></strong><br />
<ul style="margin-top:10px;">
    <li><?php echo Yii::t('admin', "Make a backup copy of X2Engine's database:") ?>
        <ul>
            <li><?php echo Yii::t('admin', 'using third-party web hosting tools, or:'); ?></li>
            <li><?php echo Yii::t('admin', 'by clicking the button below.'); ?></li>
        </ul>
    </li>
    <li><?php echo Yii::t('admin', "Make a backup copy of all X2Engine's files in addition to its database, in case you want to revert to the current version."); ?></li>
    <?php if(file_exists(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'components','LockAppAction.php')))):
    if(!Yii::app()->locked): ?>
    <li><?php echo CHtml::link(Yii::t('admin','Lock X2Engine'),array('/admin/lockApp')); ?></li>
    <?php
    endif;
    endif; ?>
    <li><?php echo Yii::t('admin', "Disable pop-up blocking on this page."); ?></li>
    <?php if($scenario == 'update') echo '<li>'.Yii::t('admin', 'Notify all users that an update will be occurring; everyone (including you) will be logged out when the update has completed.').'</li>'; ?>
    <li><?php echo Yii::t('admin','During the final stages of the process:') ?><ul>
            <li><?php echo Yii::t('admin','do not close your web browser'); ?></li>
            <li><?php echo Yii::t('admin','do not leave this page'); ?></li>
            <li><?php echo Yii::t('admin','do not refresh this page'); ?></li>
    </ul></li>
</ul>
</div><!-- #update-disclaimers -->

<hr />
<!-- Make-a-database-backup feature -->
<div id="database-backup">
    <script type="text/javascript">
    function makeBackup() {
    	var proceed = true;
        var inProgress = $('#something-inprogress').show().css({'display':'inline-block'});
        $.ajax({
            url:'backup',
    		type:'GET',
        	dataType:'json'
    	}).done(function(data){
        	alert(data.message);
    		$('#backup-state-error').hide();
        	$('#backup-download-link').show();
    	}).fail(function(jqXHR,textStatus,errorMessage) {
        	if(jqXHR.status != 0)
    			alert(<?php echo json_encode(Yii::t('admin','Backup failed.'));?>+' '+textStatus+' '+jqXHR.status+' '+errorMessage);
        }).always(function() {
    		inProgress.hide();
        });
    }
    </script>
<a href="#" onclick="makeBackup()" class="x2-button" id="backup-button"><?php echo Yii::t('admin', 'Backup Database'); ?></a>&nbsp;<img id="something-inprogress" style="height:25px;width:25px;vertical-align:middle;display:none" src="<?php echo Yii::app()->theme->BaseUrl.'/images/loading.gif'; ?>" /><br /><br />
<input type="checkbox" name="auto-restore" id="auto-restore" style="display:inline-block;padding:0;margin:0;vertical-align: middle" />
<label for="auto-restore" style="display:inline-block;margin-right:10px"><?php echo Yii::t('admin', 'Automatically restore from backup if update fails'); ?></label>
    <?php
$msg = '';
try{
    $this->checkIfDatabaseBackupExists();
}catch(Exception $e){
    if($e->getCode() == UpdaterBehavior::ERR_DBNOBACK){
        $msg = Yii::t('admin', 'Note: ').$e->getMessage();
    }else if($e->getCode() == UpdaterBehavior::ERR_DBOLDBAK){
        $msg = Yii::t('admin', 'Note: ').$e->getMessage();
    }else{
        throw $e;
    }
}
?>
<span id="backup-state">
    <span id="backup-state-error" style="color:red;"><?php echo $msg; ?></span>
    <span id="backup-download-link" style="<?php echo empty($msg)?'':'display:none;'; ?>"><?php echo CHtml::link('[ '.Yii::t('admin', 'Download database backup').' ]', array('/admin/backup', 'download' => 1)); ?></span>
</span>
<br />

<div class="form" id="autorestore-disclaimer">
    <h4 style="margin:0;"><?php echo Yii::t('admin', 'Disclaimer'); ?></h4>
    <?php echo Yii::t('admin','Restoring a database may take longer than the maximum PHP execution time permitted in some server environments, or even longer than the request timeout value in the configuration of your web browser. This is especially likely to occur if you have a large X2Engine installation with hundreds of thousands of records. If a database restore operation is cut short, the consequences could be severe. Please check your web server configuration and test making a backup of the database first. If database backups do not succeed, consider disabling this option.'); ?>
</div>
</div><!-- #database-backup -->
<br />
<hr />
<a href="javascript:void(0);" class="x2-button" id="update-button"><?php echo Yii::t('app','Apply Changes'); ?></a>
<?php echo CHtml::link(Yii::t('admin','Cancel'),array('/admin/updater','scenario'=>'delete','redirect'=>1),array('class'=>'x2-button','id'=>'update-cancel')); ?>

</div><!-- #update-ready -->

<!-- The (mostly) self-contained registration form -->
<div id="registration-form-container">
        <?php
            echo '<p id="upgrade-step">'.Yii::t('admin','To upgrade, begin by filling out the following form with your registration details. To obtain a license key: see {pp}.',array('{pp}'=>'<a href="http://www.x2engine.com/x2engine-x2crm-products/" target="_blank">'.Yii::t('admin','pricing plans').'</a>')).'</p>';
// Upgrade registration form
            Yii::app()->clientScript->registerScriptfile(Yii::app()->baseUrl.'/js/webtoolkit.sha256.js');
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'registration-form',
                'enableAjaxValidation' => false,
                    ));
            $updatesForm = new UpdatesForm(
                            array(
                                'x2_version' => Yii::app()->params['version'],
                                'unique_id' => '',
                                'formId' => 'registration-form',
                                'submitButtonId' => 'submit-button',
                                'statusId' => 'error-box',
                                'themeUrl' => Yii::app()->theme->baseUrl,
                                'serverInfo' => True,
                                'edition' => $edition,
                                'titleWrap' => array('<span style="display:none">', '</span>'),
                                'receiveUpdates' => 1,
                                'isUpgrade' => True
                            ),
                            'Yii::t',
                            array('install')
            );
            $this->renderPartial('stayUpdated', array('form' => $updatesForm));
            echo CHtml::submitButton(Yii::t('app', 'Register'), array('class' => 'x2-button', 'id' => 'submit-button'))."\n";
            echo '<div id="error-box" class="form" style="display:none"></div>';
            $this->endWidget();
            echo '<div id="upgrade-data" style="display:none;"></div>';
    ?>
</div><!-- #registration-form -->


<!-- To be populated with error messages, if any. -->
<div id="update-errors">
</div>


<?php
else: // "message" or "error"

if (isset($longMessage)) echo "<p>$longMessage</p>";
echo CHtml::link(Yii::t('admin', 'Go back'), array('/admin/index'),array('class'=>'x2-button'));

endif; /* in_array($scenario, array('update', 'upgrade')) */
?>


</div><!-- .form -->
</div><!-- .span-24 -->
