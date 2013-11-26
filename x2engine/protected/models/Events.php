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

/**
 * This is the model class for table "x2_events".
 * @package X2CRM.models
 */
class Events extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Imports the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_events';
    }

    public function relations(){
        $relationships = array();
        $relationships = array_merge($relationships, array(
            'children' => array(self::HAS_MANY, 'Events', 'associationId', 'condition' => 'children.associationType="Events"'),
                ));
        return $relationships;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
        );
    }

    public static function parseModelName($model){
        $model = ucfirst($model);
        switch($model){
            case 'Contacts':
                $model = 'contact';
                break;
            case 'Actions':
                $model = 'action';
                break;
            case 'Accounts':
                $model = 'account';
                break;
            case 'Opportunities':
                $model = 'opportunity';
                break;
            case 'Campaign':
                $model = 'marketing campaign';
                break;
            case 'Services':
                $model = 'service case';
                break;
            case 'Docs':
                $model = 'document';
                break;
            case 'Groups':
                $model = 'group';
                break;
            case 'BugReports':
                $model = 'bug report';
                break;
            default:
                $model = strtolower($model);
        }
        return Yii::t('app', $model);
    }

    public function getText(array $params = array()){
        $truncated = (array_key_exists('truncated', $params)) ? $params['truncated'] : false;
        $requireAbsoluteUrl = (array_key_exists('requireAbsoluteUrl', $params)) ? $params['requireAbsoluteUrl'] : false;
        $text = "";
        $authorText = "";
        if(Yii::app()->user->getName() == $this->user){
            $authorText = CHtml::link(Yii::t('app', 'You'), Yii::app()->controller->createAbsoluteUrl('/profile/view', array('id' => Yii::app()->user->getId())));
        }else{
            $authorText = User::getUserLinks($this->user);
        }
        if(!empty($authorText)){
            $authorText.=" ";
        }
        switch($this->type){
            case 'notif':
                $parent = X2Model::model('Notification')->findByPk($this->associationId);
                if(isset($parent)){
                    $text = $parent->getMessage();
                }else{
                    $text = Yii::t('app', "Notification not found");
                }
                break;
            case 'record_create':
                $actionFlag = false;
                if(class_exists($this->associationType)){
                    if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                        if($this->associationType == 'Actions'){
                            $action = X2Model::model('Actions')->findByPk($this->associationId);
                            if(isset($action) && (strcasecmp($action->associationType, 'contacts') === 0 || in_array($action->type,array('call','note','time')))){
                                // Special considerations for publisher-created actions, i.e. call, note, time, and anything associated with a contact
                                $actionFlag = true;
                            }
                        }
                        if($actionFlag){
                            $authorText = empty($authorText) ? Yii::t('app', 'Someone') : $authorText;
                            switch($action->type){
                                case 'call':
                                    $text = Yii::t('app', '{authorText} logged a call ({duration}) with {modelLink}: "{logAbbrev}"', array(
                                                '{authorText}' => $authorText,
                                                '{duration}' => empty($action->dueDate) || empty($action->completeDate) ? Yii::t('app', 'duration unknown') : Formatter::formatTimeInterval($action->dueDate, $action->completeDate, '{hoursMinutes}'),
                                                '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl),
                                                '{logAbbrev}' => Formatter::truncateText($action->actionDescription,60)
                                            ));
                                    break;
                                case 'note':
                                    $text = Yii::t('app', '{authorText} posted a comment on {modelLink}: "{noteAbbrev}"', array(
                                                '{authorText}' => $authorText,
                                                '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl),
                                                '{noteAbbrev}' => Formatter::truncateText($action->actionDescription,60)
                                            ));
                                    break;
                                case 'time':
                                    $text = Yii::t('app', '{authorText} logged {time} on {modelLink}: "{noteAbbrev}"', array(
                                                '{authorText}' => $authorText,
                                                '{time}' => Formatter::formatTimeInterval($action->dueDate, $action->completeDate, '{hoursMinutes}'),
                                                '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType)),
                                                '{noteAbbrev}' => Formatter::truncateText($action->actionDescription,60)
                                            ));
                                    break;
                                default:
                                    if(!empty($authorText)){
                                        $text = $authorText.Yii::t('app', "created a new {actionLink} associated with the contact {contactLink}", array('{actionLink}' => CHtml::link(Events::parseModelName($this->associationType), '#', array('class' => 'action-frame-link', 'data-action-id' => $this->associationId)), '{contactLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl)));
                                    }else{
                                        $text = Yii::t('app', "A new {actionLink} associated with the contact {contactLink} has been created.", array('{actionLink}' => CHtml::link(Events::parseModelName($this->associationType), '#', array('class' => 'action-frame-link', 'data-action-id' => $this->associationId)), '{contactLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl)));
                                    }
                            }
                        }else{
                            if(!empty($authorText)){
                                $text = $authorText.Yii::t('app', "created a new {modelName}, {modelLink}", array('{modelName}' => Events::parseModelName($this->associationType),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType, $requireAbsoluteUrl)));
                            }else{
                                $text = Yii::t('app', "A new {modelName}, {modelLink}, has been created.", array('{modelName}' => Events::parseModelName($this->associationType),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType, $requireAbsoluteUrl)));
                            }
                        }
                    }else{
                        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                        if(isset($deletionEvent)){
                            if(!empty($authorText)){
                                $text = $authorText.Yii::t('app', "created a new {modelName}, {deletionText}. It has been deleted.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType),
                                            '{deletionText}' => $deletionEvent->text,
                                        ));
                            }else{
                                $text = Yii::t('app', "A {modelName}, {deletionText}, was created. It has been deleted.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType),
                                            '{deletionText}' => $deletionEvent->text,
                                        ));
                            }
                        }else{
                            if(!empty($authorText)){
                                $text = $authorText.Yii::t('app', "created a new {modelName}, but it could not be found.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType)
                                        ));
                            }else{
                                $text = Yii::t('app', "A {modelName} was created, but it could not be found.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType)
                                        ));
                            }
                        }
                    }
                }
                break;
            case 'weblead_create':
                if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                    $text = Yii::t('app', "A new web lead has come in: {modelLink}", array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                            ));
                }else{
                    $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                    if(isset($deletionEvent)){
                        $text = Yii::t('app', "A new web lead has come in: {deletionText}. It has been deleted.", array(
                                    '{deletionText}' => $deletionEvent->text
                                ));
                    }else{
                        $text = Yii::t('app', "A new web lead has come in, but it could not be found.");
                    }
                }
                break;
            case 'record_deleted':
                if(class_exists($this->associationType)){
                    if(((Yii::app()->params->profile !== null && Yii::app()->params->profile->language != 'en' && !empty(Yii::app()->params->profile->language)) ||
                            (Yii::app()->params->profile === null && Yii::app()->language !== 'en')) ||
                            (strpos($this->associationType, 'A') !== 0 && strpos($this->associationType, 'E') !== 0 && strpos($this->associationType, 'I') !== 0 &&
                            strpos($this->associationType, 'O') !== 0 && strpos($this->associationType, 'U') !== 0)){
                        if(!empty($authorText)){
                            $text = $authorText.Yii::t('app', "deleted a {modelType}, {text}", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                                    ));
                        }else{
                            $text = Yii::t('app', "A {modelType}, {text}, was deleted", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                                    ));
                        }
                    }else{
                        if(!empty($authorText)){
                            $text = $authorText.Yii::t('app', "deleted an {modelType}, {text}.", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                                    ));
                        }else{
                            $text = Yii::t('app', "An {modelType}, {text}, was deleted.", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                                    ));
                        }
                    }
                }
                break;
            case 'workflow_start':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if(isset($action)){
                    $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
                    if(isset($record)){
                        $stages = Workflow::getStages($action->workflowId);
                        if(isset($stages[$action->stageNumber - 1])){
                            $text = $authorText.Yii::t('app', 'started the workflow stage "{stage}" for the {modelName} {modelLink}', array(
                                        '{stage}' => $stages[$action->stageNumber - 1],
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                                    ));
                        }else{
                            $text = $authorText.Yii::t('app', "started a workflow stage for the {modelName} {modelLink}, but the workflow stage could not be found.", array(
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                                    ));
                        }
                    }else{
                        $text = $authorText.Yii::t('app', "started a workflow stage, but the associated {modelName} was not found.", array(
                                    '{modelName}' => Events::parseModelName($action->associationType)
                                ));
                    }
                }else{
                    $text = $authorText.Yii::t('app', "started a workflow stage, but the workflow record could not be found.");
                }
                break;
            case 'workflow_complete':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if(isset($action)){
                    $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
                    if(isset($record)){
                        $stages = Workflow::getStages($action->workflowId);
                        if(isset($stages[$action->stageNumber - 1])){
                            $text = $authorText.Yii::t('app', 'completed the workflow stage "{stageName}" for the {modelName} {modelLink}', array(
                                        '{stageName}' => $stages[$action->stageNumber - 1],
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                                    ));
                        }else{
                            $text = $authorText.Yii::t('app', "completed a workflow stage for the {modelName} {modelLink}, but the workflow stage could not be found.", array(
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                                    ));
                        }
                    }else{
                        $text = $authorText.Yii::t('app', "completed a workflow stage, but the associated {modelName} was not found.", array(
                                    '{modelName}' => Events::parseModelName($action->associationType)
                                ));
                    }
                }else{
                    $text = $authorText.Yii::t('app', "completed a workflow stage, but the workflow record could not be found.");
                }
                break;
            case 'workflow_revert':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if(isset($action)){
                    $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
                    if(isset($record)){
                        $stages = Workflow::getStages($action->workflowId);
                        $text = $authorText.Yii::t('app', 'reverted the workflow stage "{stageName}" for the {modelName} {modelLink}', array(
                                    '{stageName}' => $stages[$action->stageNumber - 1],
                                    '{modelName}' => Events::parseModelName($action->associationType),
                                    '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                                ));
                    }else{
                        $text = $authorText.Yii::t('app', "reverted a workflow stage, but the associated {modelName} was not found.", array(
                                    '{modelName}' => Events::parseModelName($action->associationType)
                                ));
                    }
                }else{
                    $text = $authorText.Yii::t('app', "reverted a workflow stage, but the workflow record could not be found.");
                }
                break;
            case 'feed':
                if(Yii::app()->user->getName() == $this->user){
                    $author = CHtml::link(Yii::t('app', 'You'), Yii::app()->controller->createAbsoluteUrl('/profile/view', array('id' => Yii::app()->user->getId())))." ";
                }else{
                    $author = User::getUserLinks($this->user);
                }
                $recipUser = Yii::app()->db->createCommand()
                        ->select('username')
                        ->from('x2_users')
                        ->where('id=:id', array(':id' => $this->associationId))
                        ->queryScalar();
                $modifier = '';
                $recipient = '';
                if($this->user != $recipUser && $this->associationId != 0){
                    if(Yii::app()->user->getId() == $this->associationId){
                        $recipient = Yii::t('app', 'You');
                    }else{
                        $recipient = User::getUserLinks($recipUser);
                    }
                    if(!empty($recipient)){
                        $modifier = ' &raquo; ';
                    }
                }
                $text = $author.$modifier.$recipient.": ".($truncated ? strip_tags(Formatter::convertLineBreaks(x2base::convertUrls($this->text), true, true), '<a></a>') : $this->text);
                break;
            case 'email_sent':
                if(class_exists($this->associationType)){
                    $model = X2Model::model($this->associationType)->findByPk($this->associationId);
                    if(!empty($model)){
                        switch($this->subtype){
                            case 'quote':
                                $text = $authorText.Yii::t('app', "issued the {transModelName} \"{modelLink}\" via email", array(
                                            '{transModelName}' => Yii::t('quotes', 'quote'),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                        ));
                                break;
                            case 'invoice':
                                $text = $authorText.Yii::t('app', "issued the {transModelName} \"{modelLink}\" via email", array(
                                            '{transModelName}' => Yii::t('quotes', 'invoice'),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                        ));
                                break;
                            default:
                                $text = $authorText.Yii::t('app', "sent an email to the {transModelName} {modelLink}", array(
                                            '{transModelName}' => Events::parseModelName($this->associationType),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                        ));
                                break;
                        }
                    }else{
                        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                        switch($this->subtype){
                            case 'quote':
                                if(isset($deletionEvent)){
                                    $text = $authorText.Yii::t('app', "issued a quote by email, but that record has been deleted.");
                                }else{
                                    $text = $authorText.Yii::t('app', "issued a quote by email, but that record could not be found.");
                                }
                                break;
                            case 'invoice':
                                if(isset($deletionEvent)){
                                    $text = $authorText.Yii::t('app', "issued an invoice by email, but that record has been deleted.");
                                }else{
                                    $text = $authorText.Yii::t('app', "issued an invoice by email, but that record could not be found.");
                                }
                                break;
                            default:
                                if(isset($deletionEvent)){
                                    $text = $authorText.Yii::t('app', "sent an email to a {transModelName}, but that record has been deleted.", array(
                                                '{transModelName}' => Events::parseModelName($this->associationType)
                                            ));
                                }else{
                                    $text = $authorText.Yii::t('app', "sent an email to a {transModelName}, but that record could not be found.", array(
                                                '{transModelName}' => Events::parseModelName($this->associationType)
                                            ));
                                }
                                break;
                        }
                    }
                }
                break;
            case 'email_opened':
                switch($this->subtype){
                    case 'quote':
                        $emailType = Yii::t('app', 'a quote email');
                        break;
                    case 'invoice':
                        $emailType = Yii::t('app', 'an invoice email');
                        break;
                    default:
                        $emailType = Yii::t('app', 'an email');
                        break;
                }
                if(X2Model::getModelName($this->associationType) && count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                    $text = X2Model::getModelLink($this->associationId, $this->associationType).Yii::t('app', ' has opened {emailType}!', array(
                                '{emailType}' => $emailType,
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                            ));
                }else{
                    $text = Yii::t('app', "A contact has opened {emailType}, but that contact cannot be found.", array('{emailType}' => $emailType));
                }
                break;
            case 'email_clicked':
                if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                    $text = X2Model::getModelLink($this->associationId, $this->associationType).Yii::t('app', ' opened a link in an email campaign and is visiting your website!', array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                            ));
                }else{
                    $text = Yii::t('app', "A contact has opened a link in an email campaign, but that contact cannot be found.");
                }
                break;
            case 'web_activity':
                if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                    $text = X2Model::getModelLink($this->associationId, $this->associationType)." ".Yii::t('app', "is currently on your website!");
                }else{
                    $text = Yii::t('app', "A contact was on your website, but that contact cannot be found.");
                }
                break;
            case 'case_escalated':
                if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                    $case = X2Model::model($this->associationType)->findByPk($this->associationId);
                    $text = $authorText.Yii::t('app', "escalated service case {modelLink} to {userLink}", array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType),
                                '{userLink}' => User::getUserLinks($case->escalatedTo)
                            ));
                }else{
                    $text = $authorText.Yii::t('app', "escalated a service case but that case could not be found.");
                }
                break;
            case 'calendar_event':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if(isset($action)){
                    $text = Yii::t('app', "{calendarText} event: {actionDescription}", array(
                                '{calendarText}' => CHtml::link(Yii::t('calendar', 'Calendar'), Yii::app()->controller->createAbsoluteUrl('/calendar/calendar/index')),
                                '{actionDescription}' => $action->actionDescription
                            ));
                }else{
                    $text = Yii::t('app', "{calendarText} event: event not found.", array(
                                '{calendarText}' => CHtml::link(Yii::t('calendar', 'Calendar'), Yii::app()->controller->createAbsoluteUrl('/calendar/calendar/index')),
                            ));
                }
                break;
            case 'action_reminder':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if(isset($action)){
                    $text = Yii::t('app', "Reminder! The following action is due now: {transModelLink}", array(
                                '{transModelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                            ));
                }else{
                    $text = Yii::t('app', "An action is due now, but the record could not be found.");
                }
                break;
            case 'action_complete':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if(isset($action)){
                    $text = $authorText.Yii::t('app', "completed the following action: {actionDescription}", array(
                                '{actionDescription}' => X2Model::getModelLink($this->associationId, $this->associationType, $requireAbsoluteUrl)
                            ));
                }else{
                    $text = $authorText.Yii::t('app', "completed an action, but the record could not be found.");
                }
                break;
            case 'doc_update':
                $text = $authorText.Yii::t('app', 'updated a document, {docLink}', array(
                            '{docLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                        ));
                break;
            case 'email_from':
                if(class_exists($this->associationType)){
                    if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                        $text = $authorText.Yii::t('app', "received an email from a {transModelName}, {modelLink}", array(
                                    '{transModelName}' => Events::parseModelName($this->associationType),
                                    '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                ));
                    }else{
                        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                        if(isset($deletionEvent)){
                            $text = $authorText.Yii::t('app', "received an email from a {transModelName}, but that record has been deleted.", array(
                                        '{transModelName}' => Events::parseModelName($this->associationType)
                                    ));
                        }else{
                            $text = $authorText.Yii::t('app', "received an email from a {transModelName}, but that record could not be found.", array(
                                        '{transModelName}' => Events::parseModelName($this->associationType)
                                    ));
                        }
                    }
                }

                break;
            case 'voip_call':
                if(count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0){
                    $text = Yii::t('app', "{modelLink} called.", array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                            ));
                }else{
                    $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                    if(isset($deletionEvent)){
                        $text = $authorText.Yii::t('app', "A contact called, but the contact record has been deleted.");
                    }else{
                        $text = $authorText.Yii::t('app', "Call from a contact whose record could not be found.");
                    }
                }

                break;
            case 'media':
                $media = X2Model::model('Media')->findByPk($this->associationId);
                $text = substr($authorText, 0, -1).": ".$this->text;
                if(isset($media)){
                    if(!$truncated){
                        $text.="<br>".Media::attachmentSocialText($media->getMediaLink(), true, true);
                    }else{
                        $text.="<br>".Media::attachmentSocialText($media->getMediaLink(), true, false);
                    }
                }else{
                    $text.="<br>Media file not found.";
                }
                break;
            default:
                $text = $authorText.$this->text;
                break;
        }
        if($truncated && mb_strlen($text, 'UTF-8') > 250){
            $text = mb_substr($text, 0, 250, 'UTF-8')."...";
        }
        return $text;
    }

    public static $eventLabels = array(
        'feed' => 'Social Posts',
        'comment' => 'Comment',
        'record_create' => 'Records Created',
        'record_deleted' => 'Records Deleted',
        'action_reminder' => 'Action Reminders',
        'action_complete' => 'Actions Completed',
        'calendar_event' => 'Calendar Events',
        'case_escalated' => 'Cases Escalated',
        'email_opened' => 'Emails Opened',
        'email_sent' => 'Emails Sent',
        'notif' => 'Notifications',
        'weblead_create' => 'Webleads Created',
        'web_activity' => 'Web Activity',
        'workflow_complete' => 'Workflow Complete',
        'workflow_revert' => 'Workflow Reverted',
        'workflow_start' => 'Workflow Started',
        'doc_update' => 'Doc Updates',
        'email_from' => 'Email Received',
        'media' => 'Media',
        'voip_call' => 'VOIP Call',
    );

    public static function parseType($type){
        if(array_key_exists($type, self::$eventLabels))
            $type = self::$eventLabels[$type];

        return Yii::t('app', $type);
    }

    public static function getEvents($id, $timestamp, $user = null, $maxTimestamp = null, $limit = null){
        if(is_null($maxTimestamp)){
            $maxTimestamp = time();
        }
        if(is_null($user)){
            $user = Yii::app()->user->getName();
        }
        $criteria = new CDbCriteria();
        $parameters = array('order' => 'timestamp DESC, id DESC');
        if(!is_null($limit) && is_numeric($limit)){
            $parameters['limit'] = $limit;
        }
        if(!Yii::app()->params->isAdmin && !Yii::app()->user->isGuest){
            if(Yii::app()->params->admin->historyPrivacy == 'user'){
                $visibilityCondition = ' AND (associationId='.Yii::app()->user->getId().' OR `user`="'.Yii::app()->user->getName().'")';
            }elseif(Yii::app()->params->admin->historyPrivacy == 'group'){
                $visibilityCondition = ' AND (`user` IN (SELECT DISTINCT b.username FROM x2_group_to_user a INNER JOIN x2_group_to_user b ON a.groupId=b.groupId WHERE a.username="'.Yii::app()->user->getName().'") OR (associationId='.Yii::app()->user->getId().' OR `user`="'.Yii::app()->user->getName().'"))';
            }else{
                $visibilityCondition = " AND (associationId=".Yii::app()->user->getId()." OR `user`='".Yii::app()->user->getName()."' OR visibility=1)";
            }
        }else{
            $visibilityCondition = "";
        }
        $condition = isset($_SESSION['feed-condition']) ? $_SESSION['feed-condition']." AND timestamp < $maxTimestamp AND (`type`!='action_reminder' OR `user`='$user')  AND (`type`!='notif' OR `user`='$user') AND (id > $id OR timestamp > $timestamp)" : '(id > '.$id.' OR timestamp > '.$timestamp.') AND timestamp <= '.$maxTimestamp.'  AND `type`!="comment"'." AND (`type`!='action_reminder' OR `user`='$user') AND (`type`!='notif' OR `user`='".Yii::app()->user->getName()."')".$visibilityCondition;
        $parameters['condition'] = $condition;
        $criteria->scopes = array('findAll' => array($parameters));
        return array(
            'events' => X2Model::model('Events')->findAll($criteria),
        );
    }

    protected function beforeSave(){
        if(empty($this->timestamp))
            $this->timestamp = time();
        $this->lastUpdated = time();
        if(!empty($this->user) && $this->isNewRecord){
            $eventsData = X2Model::model('EventsData')->findByAttributes(array('type' => $this->type, 'user' => $this->user));
            if(isset($eventsData)){
                $eventsData->count++;
            }else{
                $eventsData = new EventsData;
                $eventsData->user = $this->user;
                $eventsData->type = $this->type;
                $eventsData->count = 1;
            }
            $eventsData->save();
        }
        if($this->type == 'record_deleted'){
            $this->text = preg_replace('/(<script(.*?)>|<\/script>)/', '', $this->text);
        }
        return parent::beforeSave();
    }

    protected function beforeDelete(){
        if(!empty($this->children)){
            foreach($this->children as $child){
                $child->delete();
            }
        }
        return parent::beforeDelete();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            'type' => Yii::t('admin', 'Type'),
            'text' => Yii::t('admin', 'Text'),
            'associationType' => Yii::t('admin', 'Association Type'),
            'associationId' => Yii::t('admin', 'Association ID'),
        );
    }

}
