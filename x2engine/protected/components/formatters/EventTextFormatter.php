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
 * Formatter for Event model text
 *
 */
class EventTextFormatter {

    /**
     * Whether associated record links were already rendered in a formatter function
     */
    public static $renderedRecordLinks = false;

    public static function getText(Events $event, array $params = array(), array $htmlOptions = array()) {
        $truncated = (array_key_exists('truncated', $params)) ? $params['truncated'] : false;
        $eventTextFn = 'format' . ucfirst(str_replace('-', '_', $event->type));
        if (method_exists('EventTextFormatter', $eventTextFn)) {
            $text = static::$eventTextFn($event, $params, $htmlOptions);
        } else {
            $text = static::formatDefault($event, $params, $htmlOptions);
        }
        if (!static::$renderedRecordLinks && !empty($event->recordLinks)) {
            $text .= '<br /><br /><div>' . Yii::t('app', 'Associated Records') . '</div>';
            $text .= $event->renderRecordLinks(array('style' => 'margin-bottom: 0px'));
            static::$renderedRecordLinks = false;
        }
        if ($truncated && mb_strlen($text, 'UTF-8') > 250) {
            $text = mb_substr($text, 0, 250, 'UTF-8') . "...";
        }
        //takeout trailing $|&|$ that's used to format activity feed posts
        $text = str_replace("$|&|$", "", $text);
        return $text;
    }

    private static function formatNotif($event, $params, $htmlOptions) {
        $parent = X2Model::model('Notification')->findByPk($event->associationId);
        if ($parent) {
            return $parent->getMessage();
        } else {
            return Yii::t('app', "Notification not found");
        }
    }

    private static function formatRecord_create($event, $params, $htmlOptions) {
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            if ($event->associationType === 'Actions') {
                return static::formatRecordCreateActions($event, $params, $htmlOptions);
            } else {
                return static::formatRecordCreateGeneric($event, $params, $htmlOptions);
            }
        } else {
            return static::formatRecordCreateDeleted($event, $params, $htmlOptions);
        }
    }

    private static function formatRecordCreateActions($event, $params, $htmlOptions) {
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if ((strcasecmp($action->associationType, 'contacts') === 0 ||
                in_array($action->type, array('call', 'note', 'time')))) {
            // Special considerations for publisher-created actions, i.e. call,
            // note, time, and anything associated with a contact
            return static::formatContactAction($event, $action, $params, $htmlOptions);
        } else {
            return static::formatGenericAction($event, $action, $params, $htmlOptions);
        }
    }

    private static function formatContactAction($event, $action, $params, $htmlOptions) {
        $requireAbsoluteUrl = (array_key_exists('requireAbsoluteUrl', $params)) ? $params['requireAbsoluteUrl'] : false;
        $actionFmtFn = 'format' . ucfirst($action->type) . 'Action';
        if (method_exists('EventTextFormatter', $actionFmtFn)) {
            return static::$actionFmtFn($action, $requireAbsoluteUrl);
        } else {
            return static::formatContactActionGeneric($event, $action, $requireAbsoluteUrl, $htmlOptions);
        }
    }

    private static function formatCallAction($action, $requireAbsoluteUrl = false) {
        $authorText = User::getUserLinks($action->assignedTo);
        if ($authorText === Yii::t('app', 'Anyone')) {
            $authorText = Yii::t('app', 'Someone');
        }
        return Yii::t('app', '{authorText} logged a call ({duration}) with {modelLink}: {text}', array(
                    '{authorText}' => $authorText,
                    '{duration}' => empty($action->dueDate) || empty($action->completeDate) ? Yii::t('app', 'duration unknown') : Formatter::formatTimeInterval($action->dueDate, $action->completeDate, '{hoursMinutes}'),
                    '{text}' => $action->actionDescription,
                    '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl)
        ));
    }

    private static function formatNoteAction($action, $requireAbsoluteUrl = false) {
        $authorText = User::getUserLinks($action->assignedTo);
        if ($authorText === Yii::t('app', 'Anyone')) {
            $authorText = Yii::t('app', 'Someone');
        }
        return Yii::t('app', '{authorText} posted a comment on {modelLink}: {text}', array(
                    '{authorText}' => $authorText,
                    '{text}' => $action->actionDescription,
                    '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl)
        ));
    }

    private static function formatTimeAction($action, $requireAbsoluteUrl = false) {
        $authorText = User::getUserLinks($action->assignedTo);
        if ($authorText === Yii::t('app', 'Anyone')) {
            $authorText = Yii::t('app', 'Someone');
        }
        return Yii::t('app', '{authorText} logged {time} on {modelLink}: {text}', array(
                    '{authorText}' => $authorText,
                    '{time}' => Formatter::formatTimeInterval($action->dueDate, $action->dueDate + $action->timeSpent, '{hoursMinutes}'),
                    '{text}' => $action->actionDescription,
                    '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType))
        ));
    }

    private static function formatContactActionGeneric($event, $action, $requireAbsoluteUrl = false, $htmlOptions = array()) {
        $authorText = User::getUserLinks($action->assignedTo);
        if ($authorText === Yii::t('app', 'Anyone')) {
            $authorText = Yii::t('app', 'Someone');
        }
        if ((!empty($authorText) && $authorText !== (Yii::t('app', 'Anyone'))) && $authorText != Yii::t('app', 'Someone')) {
            return Yii::t('app', "A new {actionLink} associated with the contact " .
                            "{contactLink} has been assigned to {authorText}", array(
                        '{authorText}' => $authorText,
                        '{actionLink}' => static::renderFrameLink(
                                $event, $htmlOptions),
                        '{contactLink}' => X2Model::getModelLink(
                                $action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl
                        )
                            )
            );
        } else {
            return Yii::t('app', "A new {actionLink} associated with the contact " .
                            "{contactLink} has been created.", array('{actionLink}' =>
                        static::renderFrameLink($event, $htmlOptions),
                        '{contactLink}' => X2Model::getModelLink(
                                $action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl
                        )
                            )
            );
        }
    }

    private static function formatGenericAction($event, $action, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $requireAbsoluteUrl = (array_key_exists('requireAbsoluteUrl', $params)) ? $params['requireAbsoluteUrl'] : false;
        if (!empty($authorText) && $authorText !== (Yii::t('app', 'Anyone') . " ")) {
            $modelLink = X2Model::getModelLink(
                            $event->associationId, $event->associationType, $requireAbsoluteUrl);
            if ($event->user !== $action->assignedTo) {
                $translateText = "created a new {modelName} for {assignee}, {modelLink}";
            } else {
                $translateText = "created a new {modelName}, {modelLink}";
            }
            return $authorText . Yii::t('app', $translateText, array(
                        '{modelName}' => Events::parseModelName($event->associationType),
                        '{modelLink}' => $modelLink,
                        '{assignee}' => isset($action) ? User::getUserLinks($action->assignedTo) : null,
            ));
        } else {
            return Yii::t('app', "A new {modelName}, {modelLink}, has been created.", array('{modelName}' => Events::parseModelName($event->associationType),
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType, $requireAbsoluteUrl)));
        }
    }

    private static function formatRecordCreateGeneric($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $requireAbsoluteUrl = (array_key_exists('requireAbsoluteUrl', $params)) ? $params['requireAbsoluteUrl'] : false;
        if ((!empty($authorText) && $authorText !== (Yii::t('app', 'Anyone') . " "))) {
            $modelLink = X2Model::getModelLink(
                            $event->associationId, $event->associationType, $requireAbsoluteUrl);
            if ($event->associationType !== 'EmailInboxes' &&
                    $modelLink !== '') {
                $translateText = "created a new {modelName}, {modelLink}";
            } else {
                $translateText = "created a new {modelName}";
            }
            return $authorText . Yii::t('app', $translateText, array(
                        '{modelName}' => Events::parseModelName($event->associationType),
                        '{modelLink}' => $modelLink,
                        '{assignee}' => isset($action) ? User::getUserLinks($action->assignedTo) : null,
            ));
        } else {
            return Yii::t('app', "A new {modelName}, {modelLink}, has been created.", array('{modelName}' => Events::parseModelName($event->associationType),
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType, $requireAbsoluteUrl)));
        }
    }

    private static function formatRecordCreateDeleted($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
            'associationType' => $event->associationType, 'associationId' => $event->associationId));
        if (isset($deletionEvent)) {
            if ((!empty($authorText) && $authorText !== (Yii::t('app', 'Anyone') . " "))) {
                return $authorText . Yii::t('app', "created a new {modelName}, {deletionText}. It has been deleted.", array(
                            '{modelName}' => Events::parseModelName($event->associationType),
                            '{deletionText}' => $deletionEvent->text,
                ));
            } else {
                return Yii::t('app', "A {modelName}, {deletionText}, was created. It has been deleted.", array(
                            '{modelName}' => Events::parseModelName($event->associationType),
                            '{deletionText}' => $deletionEvent->text,
                ));
            }
        } else {
            if ((!empty($authorText) && $authorText !== (Yii::t('app', 'Anyone') . " "))) {
                return $authorText . Yii::t('app', "created a new {modelName}, but it could not be found.", array(
                            '{modelName}' => Events::parseModelName($event->associationType)
                ));
            } else {
                return Yii::t('app', "A {modelName} was created, but it could not be found.", array(
                            '{modelName}' => Events::parseModelName($event->associationType)
                ));
            }
        }
    }

    private static function formatWeblead_create($event, $params, $htmlOptions) {
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            return Yii::t('app', "A new web lead has come in: {modelLink}", array(
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)
            ));
        } else {
            $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
                'associationType' => $event->associationType, 'associationId' => $event->associationId));
            if (isset($deletionEvent)) {
                return Yii::t('app', "A new web lead has come in: {deletionText}. It has been deleted.", array(
                            '{deletionText}' => $deletionEvent->text
                ));
            } else {
                return Yii::t('app', "A new web lead has come in, but it could not be found.");
            }
        }
    }

    private static function formatRecord_deleted($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        if ((!empty($authorText) && $authorText !== (Yii::t('app', 'Anyone') . " "))) {
            return $authorText . Yii::t('app', "deleted the {modelType}, {text}.", array(
                        '{modelType}' => Events::parseModelName($event->associationType),
                        '{text}' => $event->text
            ));
        } else {
            return Yii::t('app', "The {modelType}, {text}, was deleted.", array(
                        '{modelType}' => Events::parseModelName($event->associationType),
                        '{text}' => $event->text
            ));
        }
    }

    private static function formatWorkflow_start($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if (isset($action) && isset($action->workflowStage)) {
            $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
            if (isset($record)) {
                return $authorText . Yii::t('app', 'started the process stage "{stage}" for the {modelName} {modelLink}', array(
                            '{stage}' => $action->workflowStage->name,
                            '{modelName}' => Events::parseModelName($action->associationType),
                            '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                ));
            } else {
                return $authorText . Yii::t('app', "started a process stage, but the associated {modelName} was not found.", array(
                            '{modelName}' => Events::parseModelName($action->associationType)
                ));
            }
        } else {
            return $authorText . Yii::t('app', "started a process stage, but the process record could not be found.");
        }
    }

    private static function formatWorkflow_complete($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if (isset($action)) {
            $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
            if (isset($record)) {
                return $authorText . Yii::t('app', 'completed the process stage "{stageName}" for the {modelName} {modelLink}', array(
                            '{stageName}' => $action->workflowStage->name,
                            '{modelName}' => Events::parseModelName($action->associationType),
                            '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                ));
            } else {
                return $authorText . Yii::t('app', "completed a process stage, but the associated {modelName} was not found.", array(
                            '{modelName}' => Events::parseModelName($action->associationType)
                ));
            }
        } else {
            return $authorText . Yii::t('app', "completed a process stage, but the process record could not be found.");
        }
    }

    private static function formatWorkflow_revert($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if (isset($action) && isset($action->workflowStage)) {
            $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
            if (isset($record)) {
                return $authorText . Yii::t('app', 'reverted the process stage "{stageName}" for the {modelName} {modelLink}', array(
                            '{stageName}' => $action->workflowStage->name,
                            '{modelName}' => Events::parseModelName($action->associationType),
                            '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                ));
            } else {
                return $authorText . Yii::t('app', "reverted a process stage, but the associated {modelName} was not found.", array(
                            '{modelName}' => Events::parseModelName($action->associationType)
                ));
            }
        } else {
            return $authorText . Yii::t('app', "reverted a process stage, but the process record could not be found.");
        }
    }

    private static function formatStructured_feed($event, $params, $htmlOptions) {
        return static::formatFeed($event, $params, $htmlOptions);
    }

    private static function formatFeed($event, $params, $htmlOptions) {
        $truncated = (array_key_exists('truncated', $params)) ? $params['truncated'] : false;
        $authorText = static::getAuthorText($event);
        $modifier = '';
        $recipient = $event->getRecipient();
        if ($recipient) {
            $modifier = ' &raquo; ';
        }
        return $authorText . $modifier . $recipient . ": " . ($truncated ? strip_tags(Formatter::convertLineBreaks(x2base::convertUrls($event->text), true, true), '<a></a>') : $event->text);
    }

    private static function formatEmail_sent($event, $params, $htmlOptions) {
        $sentEmlFmtFn = 'formatEmailSent' . ucfirst($event->subtype);
        if (method_exists('EventTextFormatter', $sentEmlFmtFn)) {
            return static::$sentEmlFmtFn($event, $params, $htmlOptions);
        } else {
            return static::formatEmailSentGeneric($event, $params, $htmlOptions);
        }
    }

    private static function formatEmailSentQuote($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $model = X2Model::getModelOfTypeWithId($event->associationType, $event->associationId);
        if ($model) {
            return $authorText . Yii::t('app', "issued the quote \"{modelLink}\" via email", array(
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)));
        } else {
            $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
                'associationType' => $event->associationType, 'associationId' => $event->associationId));
            if (isset($deletionEvent)) {
                return $authorText . Yii::t('app', "issued a quote by email, but that record has been deleted.");
            } else {
                return $authorText . Yii::t('app', "issued a quote by email, but that record could not be found.");
            }
        }
    }

    private static function formatEmailSentInvoice($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $model = X2Model::getModelOfTypeWithId($event->associationType, $event->associationId);
        if ($model) {
            return $authorText . Yii::t('app', "issued the invoice \"{modelLink}\" via email", array(
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)));
        } else {
            $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
                'associationType' => $event->associationType, 'associationId' => $event->associationId));
            if (isset($deletionEvent)) {
                return $authorText . Yii::t('app', "issued an invoice by email, but that record has been deleted.");
            } else {
                return $authorText . Yii::t('app', "issued an invoice by email, but that record could not be found.");
            }
        }
    }

    private static function formatEmailSentGeneric($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $model = X2Model::getModelOfTypeWithId($event->associationType, $event->associationId);
        if ($model) {
            return $authorText . Yii::t('app', "sent an email to the {transModelName} {modelLink}", array(
                        '{transModelName}' => Events::parseModelName($event->associationType),
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)));
        } else {
            $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
                'associationType' => $event->associationType, 'associationId' => $event->associationId));
            if (isset($deletionEvent)) {
                return $authorText . Yii::t('app', "sent an email to a {transModelName}, but that record has been deleted.", array(
                            '{transModelName}' => Events::parseModelName($event->associationType)
                ));
            } else {
                return $authorText . Yii::t('app', "sent an email to a {transModelName}, but that record could not be found.", array(
                            '{transModelName}' => Events::parseModelName($event->associationType)
                ));
            }
        }
    }

    private static function formatEmail_opened($event, $params, $htmlOptions) {
        switch ($event->subtype) {
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
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            return X2Model::getModelLink($event->associationId, $event->associationType) . Yii::t('app', ' has opened {emailType}!', array(
                        '{emailType}' => $emailType,
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)
            ));
        } else {
            return Yii::t('app', "A contact has opened {emailType}, but that contact cannot be found.", array('{emailType}' => $emailType));
        }
    }

    private static function formatEmail_clicked($event, $params, $htmlOptions) {
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            return X2Model::getModelLink($event->associationId, $event->associationType) . Yii::t('app', ' opened a link in an email campaign and is visiting your website!', array(
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)
            ));
        } else {
            return Yii::t('app', "A contact has opened a link in an email campaign, but that contact cannot be found.");
        }
    }

    private static function formatWeb_activity($event, $params, $htmlOptions) {
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            $text = "";

            if ($event->associationType === 'AnonContact') {
                $text = Yii::t('app', "Anonymous contact ");
            }

            $text .= X2Model::getModelLink($event->associationId, $event->associationType) . " " . Yii::t('app', "is currently on your website!") . (!empty($event->text) ? " " . $event->text : "");
        } else {
            $text = Yii::t('app', "A contact was on your website, but that contact cannot be found.");
        }
        return $text;
    }

    private static function formatCase_escalated($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            $case = X2Model::model($event->associationType)->findByPk($event->associationId);
            return $authorText . Yii::t('app', "escalated service case {modelLink} to {userLink}", array(
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType),
                        '{userLink}' => User::getUserLinks($case->escalatedTo)
            ));
        } else {
            return $authorText . Yii::t('app', "escalated a service case but that case could not be found.");
        }
    }

    private static function formatCalendar_event($event, $params, $htmlOptions) {
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if (!Yii::app()->params->isMobileApp ||
                LinkableBehavior::isMobileLinkableRecordType('Calendar')) {

            $calendarText = CHtml::link(
                            Yii::t('calendar', 'Calendar'), Yii::app()->absoluteBaseUrl . '/index.php' . '/calendar/calendar/index', $htmlOptions);
        } else {
            $calendarText = Yii::t('calendar', 'Calendar');
        }
        if (isset($action)) {
            return Yii::t('app', "{calendarText} event: {actionDescription}", array(
                        '{calendarText}' => $calendarText,
                        '{actionDescription}' => CHtml::encode($action->actionDescription)
            ));
        } else {
            return Yii::t('app', "{calendarText} event: event not found.", array(
                        '{calendarText}' => $calendarText
            ));
        }
    }

    private static function formatAction_reminder($event, $params, $htmlOptions) {
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if (isset($action)) {
            return Yii::t('app', "Reminder! The following {action} is due now: {transModelLink}", array(
                        '{transModelLink}' => X2Model::getModelLink($event->associationId, $event->associationType),
                        '{action}' => strtolower(Modules::displayName(false, 'Actions')),
            ));
        } else {
            return Yii::t('app', "An {action} is due now, but the record could not be found.", array(
                        '{action}' => strtolower(Modules::displayName(false, 'Actions')),
            ));
        }
    }

    private static function formatAction_complete($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $requireAbsoluteUrl = (array_key_exists('requireAbsoluteUrl', $params)) ? $params['requireAbsoluteUrl'] : false;
        $action = X2Model::model('Actions')->findByPk($event->associationId);
        if (isset($action)) {
            return $authorText . Yii::t('app', "completed the following {action}: {actionDescription}", array(
                        '{actionDescription}' => X2Model::getModelLink(
                                $event->associationId, $event->associationType, $requireAbsoluteUrl),
                        '{action}' => strtolower(Modules::displayName(false, 'Actions')),
                            )
            );
        } else {
            return $authorText . Yii::t('app', "completed an {action}, but the record could not be found.", array(
                        '{action}' => strtolower(Modules::displayName(false, 'Actions')),
            ));
        }
    }

    private static function formatDoc_update($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $doc = X2Model::model('Docs')->findByPk($event->associationId);
        if ($doc) {
            return $authorText . Yii::t('app', 'updated a document, {docLink}', array(
                        '{docLink}' => X2Model::getModelLink($event->associationId, $event->associationType)
            ));
        } else {
            return $authorText . Yii::t('app', 'updated a document, but the record could not be found.');
        }
    }

    private static function formatEmail_from($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            if ($event->associationType === 'Actions' && $event->subtype = 'email') {
                $action = X2Model::model('Actions')->findByPk($event->associationId);
                return static::formatContactEmail($action, $authorText);
            } else {
                $modelName = Events::parseModelName($event->associationType);
                $modelLink = X2Model::getModelLink($event->associationId, $event->associationType);
                return $authorText . Yii::t('app', "received an email from a {transModelName}, {modelLink}", array(
                            '{transModelName}' => $modelName,
                            '{modelLink}' => $modelLink,
                ));
            }
        } else {
            $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
                'associationType' => $event->associationType, 'associationId' => $event->associationId));
            if (isset($deletionEvent)) {
                return $authorText . Yii::t('app', "received an email from a {transModelName}, but that record has been deleted.", array(
                            '{transModelName}' => Events::parseModelName($event->associationType)
                ));
            } else {
                return $authorText . Yii::t('app', "received an email from a {transModelName}, but that record could not be found.", array(
                            '{transModelName}' => Events::parseModelName($event->associationType)
                ));
            }
        }
    }

    private static function formatContactEmail($action, $authorText) {
        $emailLink = X2Model::getModelLink($action->id, 'Actions');
        $email = Yii::t('app', 'email') . ' ' . $emailLink;
        $modelLinks = $action->getMultiassociationLinks();
        $text = $authorText . Yii::t('app', "received an {email} from ", array(
                    '{email}' => $email,
        ));
        // construct event text for multiassociated actions
        foreach (array('Contacts', 'Services') as $modelType) {
            if (array_key_exists($modelType, $modelLinks) && !empty($modelLinks[$modelType])) {
                $multiple = count($modelLinks[$modelType]) > 1;
                $modelDescription = lcfirst(Modules::displayName($multiple, $modelType));
                if ($multiple) {
                    $modelDescription .= ',';
                }
                $text .= Yii::t('app', 'the {transModelName} {modelLinks}', array(
                            '{transModelName}' => $modelDescription,
                            '{modelLinks}' => implode(', ', $modelLinks[$modelType]),
                ));

                if ($modelType === 'Contacts' && array_key_exists('Services', $modelLinks) &&
                        !empty($modelLinks['Services'])) {

                    $text .= Yii::t('app', ', regarding ');
                }
            }
        }
        return $text;
    }

    private static function formatVoip_call($event, $params, $htmlOptions) {
        if (X2Model::getModelOfTypeWithId($event->associationType, $event->associationId)) {
            return Yii::t('app', "{modelLink} called.", array(
                        '{modelLink}' => X2Model::getModelLink($event->associationId, $event->associationType)
            ));
        } else {
            $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted',
                'associationType' => $event->associationType, 'associationId' => $event->associationId));
            if (isset($deletionEvent)) {
                return Yii::t('app', "A contact called, but the contact record has been deleted.");
            } else {
                return Yii::t('app', "Call from a contact whose record could not be found.");
            }
        }
    }

    private static function formatMedia($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $authorText = rtrim($authorText, " ");
        $truncated = (array_key_exists('truncated', $params)) ? $params['truncated'] : false;
        /*
         *  get table x2_events_to_media and by using $event->id get mediaId
         *  but for legacy media that weren't loaded in x2_events_to_media get them via legacyMedia
         * 
         */

        // var array $eventTexts to parse $event->text for further formatting
        $eventTexts = explode('$|&|$', $event->text);
        if ($params['media'] == null) {
            $media = $event->legacyMedia;
            //$text = substr($authorText, 0, -1) . ": " . $event->text;
            if (count($eventTexts) == 3) {
                $text = $authorText . ": " . $eventTexts[2] . "<br><br>" . $eventTexts[0];
            } else {
                $text = $authorText . ": " . $event->text;
            }
        } else {
            $media = $params['media'];
            $recipient = $params['recipient'];
            $profileRecipient = $params['profileRecipient'];
            if ($recipient) {
                $recipientLink = CHtml::link(
                                Yii::t('app', $recipient->firstName . ' ' . $recipient->lastName), $profileRecipient->getUrl());
                $modifier = ' &raquo; ';
                if (Yii::app()->user->getName() == $recipient->username) {
                    $recipientLink = CHtml::link(
                                    Yii::t('app', 'You'), $profileRecipient->getUrl());
                }
                if (!($event->user == Yii::app()->user->getName() && $recipient->username == Yii::app()->user->getName())) {
                    $authorText .= $modifier . $recipientLink;
                }
            }
            if (count($eventTexts) == 3) {
                $text = $authorText . ": " . $eventTexts[2] . "<br><br><br>" . $eventTexts[0];
            } else {
                $text = $authorText . ": " . $event->text;
            }
        }
        if (!empty($event->recordLinks)) {
            $text .= '<br /><br /><div>' . Yii::t('app', 'Associated Records') . '</div>';
            $text .= $event->renderRecordLinks(array('style' => 'margin-bottom: 0px'));
            static::$renderedRecordLinks = true;
        }
        if (!empty($event->media)) {
            $index = 0;
            foreach ($event->media as $key => $media) {
                if ($index == (count($event->media) - 1) && count($eventTexts) == 3) {
                    $text .= "<br>";
                    $text .= $eventTexts[1];
                }
                $text .= "<br>" . Media::attachmentSocialText($media->getMediaLink(), false, !$truncated) . "<br>";
                $index++;
            }
        } else {
            $text .= "<br>Media file not found.";
        }

        return $text;
    }

    private static function formatTopic_reply($event, $params, $htmlOptions) {
        $authorText = static::getAuthorText($event, $htmlOptions);
        $reply = TopicReplies::model()->findByPk($event->associationId);
        if (isset($reply)) {
            if (!Yii::app()->params->isMobileApp) {
                if (!ResponseUtil::isCli()) {
                    $url = Yii::app()->controller->createUrl(
                            '/topics/topics/view', array('id' => $reply->topic->id, 'replyId' => $reply->id));
                } else {
                    $url = Yii::app()->absoluteBaseUrl . (YII_UNIT_TESTING ? '/index-test.php' : '/index.php') . '/topics/topics/view/' . $reply->topic->id . '?replyId=' . $reply->id;
                }
                $topicLink = X2Html::link(
                                $reply->topic->name, $url);
            } else {
                $topicLink = $reply->topic->name;
            }
            return Yii::t('topics', '{poster}posted a new reply to {topic}.', array(
                        '{poster}' => $authorText,
                        '{topic}' => $topicLink,
            ));
            // TODO: add topic preview
            // reliable rich text truncation tool needed
//                    $text .= '<br/>';
//                    $text .= '<blockquote class="topic-preview">';
//                    $text .= $reply->text;
//                    $text .= '</blockquote>';
        } else {
            return Yii::t('topics', '{poster}posted a new reply to a topic, but that reply has been deleted.', array(
                        '{poster}' => $authorText,
            ));
        }
    }

    private static function formatDefault($event, $params, $htmlOptions) {
        return static::getAuthorText($event, $htmlOptions) . CHtml::encode($event->text);
    }

    public static function getAuthorText($event, $htmlOptions = array()) {
        $authorText = "";
        if (Yii::app()->user->getName() == $event->user) {
            $authorText = CHtml::link(
                            Yii::t('app', 'You'), $event->profile->url, $htmlOptions);
        } else {
            $authorText = User::getUserLinks($event->user);
        }
        if (!empty($authorText)) {
            $authorText .= " ";
        }
        return $authorText;
    }

    public static function renderFrameLink($event, $htmlOptions, $text = null) {
        if (Yii::app()->params->isMobileApp &&
                !LinkableBehavior::isMobileLinkableRecordType($event->associationType)) {

            return Events::parseModelName($event->associationType);
        }
        $association = $event->getAssociation();
        if (!$association) {
            return Events::parseModelName($event->associationType);
        }
        $htmlOptions = array_merge($htmlOptions, array(
            'class' => 'action-frame-link',
            'data-action-id' => $event->associationId
        ));
        if ($association instanceof Actions &&
                in_array($association->type, array('note', 'time', 'call'))) {

            $name = Yii::t('app', 'comment');
            $htmlOptions['data-action-type'] = $association->type;
            $htmlOptions['data-text-only'] = 1;
        } else {
            $name = Events::parseModelName($event->associationType);
        }
        return CHtml::link(
                        $text ? $text : $name, '#', $htmlOptions
        );
    }

}
