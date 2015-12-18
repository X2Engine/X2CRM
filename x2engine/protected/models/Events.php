<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * @package application.models
 */
class Events extends X2ActiveRecord {

    public $photo;

    /**
     * Returns the static model of the specified AR class.
     * @return Imports the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_events';
    }

    public function attributeNames () {
         return array_merge (
            parent::attributeNames (), 
            array (
                'photo',
            )
        );
    }

    public function relations() {
        $relationships = array();
        $relationships = array_merge($relationships, array(
            'children' => array(
                self::HAS_MANY, 'Events', 'associationId', 
                'condition' => 'children.associationType="Events"'),
            'profile' => array(self::HAS_ONE, 'Profile', 
                array ('username' => 'user')),
            'userObj' => array(self::HAS_ONE, 'User', 
                array ('username' => 'user')),
            'media' => array (
                self::MANY_MANY, 'Media', 'x2_events_to_media(eventsId, mediaId)'),
            // only use this if $this->type === 'media'
            'legacyMedia' => array (
                self::HAS_ONE, 'Media', array ('id' => 'associationId')),
        ));
        return $relationships;
    }

    public function scopes () {
        return array (  
            'comments' => array (
                'condition' => 'associationType="Events" and associationId=:id',
                'params' => array (':id' => $this->id)
            )
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
        );
    }

    public function save ($runValidation=true, $attributes=null) {
        if ($this->photo) {

            // save related photo record
            $transaction = Yii::app()->db->beginTransaction ();
            try {
                // save the event
                $ret = parent::save ($runValidation, $attributes);
                if (!$ret) {
                    throw new CException (implode (';', $this->getAllErrorMessages ()));
                }

                // save the file
                $tempName = $this->photo->getTempName ();
                $username = Yii::app()->user->getName ();
                if (!FileUtil::ccopy(
                    $tempName, 
                    "uploads/protected/media/$username/{$this->photo->getName ()}")) {

                    throw new CException ();
                }

                // add media record for file
                $media = new Media; 
                $media->setAttributes (array (
                    'fileName' => $this->photo->getName (),
                    'mimetype' => $this->photo->type,
                ), false);
                $media->resolveNameConflicts ();
                if (!$media->save ()) {
                    throw new CException (implode (';', $media->getAllErrorMessages ()));
                }

                // relate file to event
                $join = new RelationshipsJoin ('insert', 'x2_events_to_media');
                $join->eventsId = $this->id;
                $join->mediaId = $media->id;
                if (!$join->save ()) {
                    throw new CException (implode (';', $join->getAllErrorMessages ()));
                }

                $transaction->commit ();
                return $ret;
            } catch (CException $e) {
                $transaction->rollback ();
            }
        } else {
            return parent::save ($runValidation, $attributes);
        }
    }

    public function renderFrameLink ($htmlOptions) {
        if (Yii::app()->params->isMobileApp &&
            !X2LinkableBehavior::isMobileLinkableRecordType ($this->associationType)) {

            return Events::parseModelName ($this->associationType);
        }
        return CHtml::link(
            Events::parseModelName($this->associationType),
            '#', array_merge($htmlOptions, array(
                'class' => 'action-frame-link',
                'data-action-id' => $this->associationId
            ))
        );
    }

    public function getRecipient () {
        $recipUser = Yii::app()->db->createCommand()
                ->select('username')
                ->from('x2_users')
                ->where('id=:id', array(':id' => $this->associationId))
                ->queryScalar();
        $recipient = '';
        if ($this->user != $recipUser && $this->associationId != 0) {
            if (Yii::app()->user->getId() == $this->associationId) {
                $recipient = 
                    CHtml::link(
                        Yii::t('app', 'You'), 
                        Yii::app()->params->profile->getUrl ());
            } else {
                $recipient = User::getUserLinks($recipUser);
            }
        }
        return $recipient;
    }

    /**
     * Parse an associationType field and resolve the model name
     * @param string $model Model type to resolve
     * @return string Model's name
     */
    public static function parseModelName($model) {
         


        $customModule = Modules::model()->findByAttributes(array(
            'custom' => 1,
            'name' => $model,
        ));
        if ($customModule) {
            //$model = $customModule->title;
            $model = Modules::itemDisplayName($customModule->name);
            $model = strtolower($model);
        } else {
            switch ($model) {
                case 'Product':
                    $model .= 's'; break;
                case 'Quote':
                    $model .= 's'; break;
                case 'Opportunity':
                    $model = str_replace('y', 'ies', $model); break;
            }
            $requestedModel = $model;
            $model = Modules::displayName(false, ucfirst($model));
            $model = strtolower($model);
            if (empty($model)) {
                // If the model type couldn't be resolved, check for special cases
                // of models without a dedicated module
                if ($requestedModel === 'AnonContact')
                    $model = 'anonymous contact';
                else if ($requestedModel === 'Campaign')
                    $model = 'campaign';
            }
        }
        return Yii::t('app', $model);
    }

    public function getText(array $params = array(), array $htmlOptions = array()) {
        $truncated = (array_key_exists('truncated', $params)) ? $params['truncated'] : false;
        $requireAbsoluteUrl = (array_key_exists('requireAbsoluteUrl', $params)) ? $params['requireAbsoluteUrl'] : false;
        $text = "";
        $authorText = "";
        if (Yii::app()->user->getName() == $this->user) {
            $authorText = CHtml::link(
                Yii::t('app', 'You'), $this->profile->url, $htmlOptions);
        } else {
            $authorText = User::getUserLinks($this->user);
        }
        if (!empty($authorText)) {
            $authorText.=" ";
        }
        switch ($this->type) {
            case 'notif':
                $parent = X2Model::model('Notification')->findByPk($this->associationId);
                if (isset($parent)) {
                    $text = $parent->getMessage();
                } else {
                    $text = Yii::t('app', "Notification not found");
                }
                break;
            case 'record_create':
                $actionFlag = false;
                if (class_exists($this->associationType)) {
                    if (count(X2Model::model($this->associationType)
                        ->findAllByPk($this->associationId)) > 0) {

                        if ($this->associationType == 'Actions') {
                            $action = X2Model::model('Actions')->findByPk($this->associationId);
                            if (isset($action) && 
                                (strcasecmp($action->associationType, 'contacts') === 0 || 
                                 in_array($action->type, array('call', 'note', 'time')))) {
                                // Special considerations for publisher-created actions, i.e. call,
                                // note, time, and anything associated with a contact
                                $actionFlag = true;
                                // Retrieve the assigned user from the related action
                                $relatedAction = Actions::model()->findByPk ($this->associationId);
                                if ($authorText)
                                    $authorText = User::getUserLinks ($relatedAction->assignedTo);
                            }
                        }
                        if ($actionFlag) {
                            $authorText = empty($authorText) ? 
                                Yii::t('app', 'Someone') : $authorText;
                            switch ($action->type) {
                                case 'call':
                                    $text = Yii::t('app', '{authorText} logged a call ({duration}) with {modelLink}.', array(
                                                '{authorText}' => $authorText,
                                                '{duration}' => empty($action->dueDate) || empty($action->completeDate) ? Yii::t('app', 'duration unknown') : Formatter::formatTimeInterval($action->dueDate, $action->completeDate, '{hoursMinutes}'),
                                                '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl)
                                    ));
                                    break;
                                case 'note':
                                    $text = Yii::t('app', '{authorText} posted a comment on {modelLink}.', array(
                                                '{authorText}' => $authorText,
                                                '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType), $requireAbsoluteUrl)
                                    ));
                                    break;
                                case 'time':
                                    $text = Yii::t('app', '{authorText} logged {time} on {modelLink}.', array(
                                                '{authorText}' => $authorText,
                                                '{time}' => Formatter::formatTimeInterval($action->dueDate, $action->dueDate + $action->timeSpent, '{hoursMinutes}'),
                                                '{modelLink}' => X2Model::getModelLink($action->associationId, ucfirst($action->associationType))
                                    ));
                                    break;
                                default:
                                    if (!empty($authorText)) {
                                        $text = Yii::t(
                                            'app', 
                                            "A new {actionLink} associated with the contact ".
                                            "{contactLink} has been assigned to " . $authorText, 
                                            array(
                                                '{actionLink}' => $this->renderFrameLink (
                                                    $htmlOptions),
                                                '{contactLink}' => X2Model::getModelLink(
                                                    $action->associationId, 
                                                    ucfirst($action->associationType), 
                                                    $requireAbsoluteUrl
                                                )
                                            )
                                        );
                                    } else {
                                        $text = Yii::t(
                                            'app', 
                                            "A new {actionLink} associated with the contact ".
                                            "{contactLink} has been created.", 
                                            array(
                                                '{actionLink}' => $this->renderFrameLink (
                                                    $htmlOptions),
                                                '{contactLink}' => X2Model::getModelLink(
                                                    $action->associationId, 
                                                    ucfirst($action->associationType), 
                                                    $requireAbsoluteUrl
                                                )
                                            )
                                        );
                                    }
                            }
                        } else {
                            if (!empty($authorText)) {
                                $modelLink = X2Model::getModelLink (
                                    $this->associationId, $this->associationType, $requireAbsoluteUrl);
                                if (isset($action) && $this->user !== $action->assignedTo) {
                                    // Include the assignee if this is for an action assigned to someone other than the creator
                                    $translateText = "created a new {modelName} for {assignee}, {modelLink}";
                                } elseif (
                                     
                                    $modelLink !== '') {

                                    $translateText = "created a new {modelName}, {modelLink}";
                                } else {
                                    $translateText = "created a new {modelName}";
                                }
                                $text = $authorText . Yii::t('app', $translateText, array(
                                    '{modelName}' => Events::parseModelName($this->associationType),
                                    '{modelLink}' => $modelLink,
                                    '{assignee}' => isset($action) ? User::getUserLinks ($action->assignedTo) : null,
                                ));
                            } else {
                                $text = Yii::t('app', "A new {modelName}, {modelLink}, has been created.", array('{modelName}' => Events::parseModelName($this->associationType),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType, $requireAbsoluteUrl)));
                            }
                        }
                    } else {
                        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                        if (isset($deletionEvent)) {
                            if (!empty($authorText)) {
                                $text = $authorText . Yii::t('app', "created a new {modelName}, {deletionText}. It has been deleted.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType),
                                            '{deletionText}' => $deletionEvent->text,
                                ));
                            } else {
                                $text = Yii::t('app', "A {modelName}, {deletionText}, was created. It has been deleted.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType),
                                            '{deletionText}' => $deletionEvent->text,
                                ));
                            }
                        } else {
                            if (!empty($authorText)) {
                                $text = $authorText . Yii::t('app', "created a new {modelName}, but it could not be found.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType)
                                ));
                            } else {
                                $text = Yii::t('app', "A {modelName} was created, but it could not be found.", array(
                                            '{modelName}' => Events::parseModelName($this->associationType)
                                ));
                            }
                        }
                    }
                }
                break;
            case 'weblead_create':
                if (count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                    $text = Yii::t('app', "A new web lead has come in: {modelLink}", array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                    ));
                } else {
                    $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                    if (isset($deletionEvent)) {
                        $text = Yii::t('app', "A new web lead has come in: {deletionText}. It has been deleted.", array(
                                    '{deletionText}' => $deletionEvent->text
                        ));
                    } else {
                        $text = Yii::t('app', "A new web lead has come in, but it could not be found.");
                    }
                }
                break;
            case 'record_deleted':
                if (class_exists($this->associationType)) {
                    if (((Yii::app()->params->profile !== null && Yii::app()->params->profile->language != 'en' && !empty(Yii::app()->params->profile->language)) ||
                            (Yii::app()->params->profile === null && Yii::app()->language !== 'en')) ||
                            (strpos($this->associationType, 'A') !== 0 && strpos($this->associationType, 'E') !== 0 && strpos($this->associationType, 'I') !== 0 &&
                            strpos($this->associationType, 'O') !== 0 && strpos($this->associationType, 'U') !== 0)) {
                        if (!empty($authorText)) {
                            $text = $authorText . Yii::t('app', "deleted a {modelType}, {text}", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                            ));
                        } else {
                            $text = Yii::t('app', "A {modelType}, {text}, was deleted", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                            ));
                        }
                    } else {
                        if (!empty($authorText)) {
                            $text = $authorText . Yii::t('app', "deleted an {modelType}, {text}.", array(
                                        '{modelType}' => Events::parseModelName($this->associationType),
                                        '{text}' => $this->text
                            ));
                        } else {
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
                if (isset($action)) {
                    $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
                    if (isset($record)) {
                        if (isset($action->workflowStage)) {
                            $text = $authorText . Yii::t('app', 'started the process stage "{stage}" for the {modelName} {modelLink}', array(
                                        '{stage}' => $action->workflowStage->name,
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                            ));
                        } else {
                            $text = $authorText . Yii::t('app', "started a process stage for the {modelName} {modelLink}, but the process stage could not be found.", array(
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                            ));
                        }
                    } else {
                        $text = $authorText . Yii::t('app', "started a process stage, but the associated {modelName} was not found.", array(
                                    '{modelName}' => Events::parseModelName($action->associationType)
                        ));
                    }
                } else {
                    $text = $authorText . Yii::t('app', "started a process stage, but the process record could not be found.");
                }
                break;
            case 'workflow_complete':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if (isset($action)) {
                    $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
                    if (isset($record)) {
                        if (isset($action->workflowStage)) {
                            $text = $authorText . Yii::t('app', 'completed the process stage "{stageName}" for the {modelName} {modelLink}', array(
                                        '{stageName}' => $action->workflowStage->name,
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                            ));
                        } else {
                            $text = $authorText . Yii::t('app', "completed a process stage for the {modelName} {modelLink}, but the process stage could not be found.", array(
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                            ));
                        }
                    } else {
                        $text = $authorText . Yii::t('app', "completed a process stage, but the associated {modelName} was not found.", array(
                                    '{modelName}' => Events::parseModelName($action->associationType)
                        ));
                    }
                } else {
                    $text = $authorText . Yii::t('app', "completed a process stage, but the process record could not be found.");
                }
                break;
            case 'workflow_revert':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if (isset($action)) {
                    $record = X2Model::model(ucfirst($action->associationType))->findByPk($action->associationId);
                    if (isset($record)) {
                        if (isset($action->workflowStage)) {
                            $text = $authorText . Yii::t('app', 'reverted the process stage "{stageName}" for the {modelName} {modelLink}', array(
                                        '{stageName}' => $action->workflowStage->name,
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                            ));
                        } else {
                            $text = $authorText . Yii::t('app', "reverted a process stage for the {modelName} {modelLink}, but the process stage could not be found.", array(
                                        '{modelName}' => Events::parseModelName($action->associationType),
                                        '{modelLink}' => X2Model::getModelLink($action->associationId, $action->associationType)
                            ));
                        }
                    } else {
                        $text = $authorText . Yii::t('app', "reverted a process stage, but the associated {modelName} was not found.", array(
                                    '{modelName}' => Events::parseModelName($action->associationType)
                        ));
                    }
                } else {
                    $text = $authorText . Yii::t('app', "reverted a process stage, but the process record could not be found.");
                }
                break;
            case 'structured-feed':
            case 'feed':
                if (Yii::app()->user->getName() == $this->user) {
                    $author = CHtml::link(Yii::t('app', 'You'), Yii::app()->controller->createAbsoluteUrl('/profile/view', array('id' => Yii::app()->user->getId())), $htmlOptions) . " ";
                } else {

                    $author = User::getUserLinks($this->user);
                }
                $modifier = '';
                $recipient = $this->getRecipient ();
                if ($recipient) {
                    $modifier = ' &raquo; ';
                }
                $text = $author . $modifier . $recipient . ": " . ($truncated ? strip_tags(Formatter::convertLineBreaks(x2base::convertUrls($this->text), true, true), '<a></a>') : $this->text);
                break;
            case 'email_sent':
                if (class_exists($this->associationType)) {
                    $model = X2Model::model($this->associationType)->findByPk($this->associationId);
                    if (!empty($model)) {
                        switch ($this->subtype) {
                            case 'quote':
                                $text = $authorText . Yii::t('app', "issued the {transModelName} \"{modelLink}\" via email", array(
                                            '{transModelName}' => Yii::t('quotes', 'quote'),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                ));
                                break;
                            case 'invoice':
                                $text = $authorText . Yii::t('app', "issued the {transModelName} \"{modelLink}\" via email", array(
                                            '{transModelName}' => Yii::t('quotes', 'invoice'),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                ));
                                break;
                            default:
                                $text = $authorText . Yii::t('app', "sent an email to the {transModelName} {modelLink}", array(
                                            '{transModelName}' => Events::parseModelName($this->associationType),
                                            '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                                ));
                                break;
                        }
                    } else {
                        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                        switch ($this->subtype) {
                            case 'quote':
                                if (isset($deletionEvent)) {
                                    $text = $authorText . Yii::t('app', "issued a quote by email, but that record has been deleted.");
                                } else {
                                    $text = $authorText . Yii::t('app', "issued a quote by email, but that record could not be found.");
                                }
                                break;
                            case 'invoice':
                                if (isset($deletionEvent)) {
                                    $text = $authorText . Yii::t('app', "issued an invoice by email, but that record has been deleted.");
                                } else {
                                    $text = $authorText . Yii::t('app', "issued an invoice by email, but that record could not be found.");
                                }
                                break;
                            default:
                                if (isset($deletionEvent)) {
                                    $text = $authorText . Yii::t('app', "sent an email to a {transModelName}, but that record has been deleted.", array(
                                                '{transModelName}' => Events::parseModelName($this->associationType)
                                    ));
                                } else {
                                    $text = $authorText . Yii::t('app', "sent an email to a {transModelName}, but that record could not be found.", array(
                                                '{transModelName}' => Events::parseModelName($this->associationType)
                                    ));
                                }
                                break;
                        }
                    }
                }
                break;
            case 'email_opened':
                switch ($this->subtype) {
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
                if (X2Model::getModelName($this->associationType) && count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                    $text = X2Model::getModelLink($this->associationId, $this->associationType) . Yii::t('app', ' has opened {emailType}!', array(
                                '{emailType}' => $emailType,
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                    ));
                } else {
                    $text = Yii::t('app', "A contact has opened {emailType}, but that contact cannot be found.", array('{emailType}' => $emailType));
                }
                break;
            case 'email_clicked':
                if (count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                    $text = X2Model::getModelLink($this->associationId, $this->associationType) . Yii::t('app', ' opened a link in an email campaign and is visiting your website!', array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                    ));
                } else {
                    $text = Yii::t('app', "A contact has opened a link in an email campaign, but that contact cannot be found.");
                }
                break;
            case 'web_activity':
                if (count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                    $text = "";
                    
                    $text .= X2Model::getModelLink($this->associationId, $this->associationType) . " " . Yii::t('app', "is currently on your website!");
                }else {
                    $text = Yii::t('app', "A contact was on your website, but that contact cannot be found.");
                }
                break;
            case 'case_escalated':
                if (count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                    $case = X2Model::model($this->associationType)->findByPk($this->associationId);
                    $text = $authorText . Yii::t('app', "escalated service case {modelLink} to {userLink}", array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType),
                                '{userLink}' => User::getUserLinks($case->escalatedTo)
                    ));
                } else {
                    $text = $authorText . Yii::t('app', "escalated a service case but that case could not be found.");
                }
                break;
            case 'calendar_event':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if (!Yii::app()->params->isMobileApp ||
                    X2LinkableBehavior::isMobileLinkableRecordType ('Calendar')) {

                    $calendarText = CHtml::link(
                        Yii::t('calendar', 'Calendar'), 
                        Yii::app()->controller->createAbsoluteUrl(
                            '/calendar/calendar/index'), $htmlOptions);
                } else {
                    $calendarText = Yii::t('calendar', 'Calendar');
                }
                if (isset($action)) {
                    $text = Yii::t('app', "{calendarText} event: {actionDescription}", array(
                                '{calendarText}' => $calendarText,
                                '{actionDescription}' => CHtml::encode($action->actionDescription)
                    ));
                } else {
                    $text = Yii::t('app', "{calendarText} event: event not found.", array(
                                '{calendarText}' => $calendarText
                    ));
                }
                break;
            case 'action_reminder':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if (isset($action)) {
                    $text = Yii::t('app', "Reminder! The following {action} is due now: {transModelLink}", array(
                        '{transModelLink}' => X2Model::getModelLink($this->associationId, $this->associationType),
                        '{action}' => strtolower(Modules::displayName (false, 'Actions')),
                    ));
                } else {
                    $text = Yii::t('app', "An {action} is due now, but the record could not be found.", array(
                        '{action}' => strtolower(Modules::displayName (false, 'Actions')),
                    ));
                }
                break;
            case 'action_complete':
                $action = X2Model::model('Actions')->findByPk($this->associationId);
                if (isset($action)) {
                    $text = $authorText . Yii::t('app', "completed the following {action}: {actionDescription}", array(
                            '{actionDescription}' => X2Model::getModelLink(
                                $this->associationId, $this->associationType, $requireAbsoluteUrl),
                            '{action}' => strtolower(Modules::displayName (false, 'Actions')),
                        )
                    );
                } else {
                    $text = $authorText . Yii::t('app', "completed an {action}, but the record could not be found.", array(
                        '{action}' => strtolower(Modules::displayName (false, 'Actions')),
                    ));
                }
                break;
            case 'doc_update':
                $text = $authorText . Yii::t('app', 'updated a document, {docLink}', array(
                            '{docLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                ));
                break;
            case 'email_from':
                if (class_exists($this->associationType)) {
                    if (count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                        $email = Yii::t('app', 'email');
                        if ($this->associationType === 'Actions' && $this->subtype = 'email') {
                            $action = X2Model::model('Actions')->findByPk ($this->associationId);
                            if ($action) {
                                $multiAssociations = $action->getMultiassociations();
                                if (isset($multiAssociations['Contacts'])) {
                                    // Link to the first multiassociated Contact
                                    $contact = $multiAssociations['Contacts'][0];
                                    $modelName = Events::parseModelName('Contacts');
                                    $emailLink = X2Model::getModelLink($action->id, 'Actions');
                                    $modelLink = X2Model::getModelLink($contact->id, 'Contacts');
                                    $email = Yii::t('app', 'email') . ' '. $emailLink;
                                }
                            }
                        } else {
                            $modelName = Events::parseModelName($this->associationType);
                            $modelLink = X2Model::getModelLink($this->associationId, $this->associationType);
                        }
                        $text = $authorText . Yii::t('app', "received an {email} from a {transModelName}, {modelLink}", array(
                                '{email}' => $email,
                                '{transModelName}' => $modelName,
                                '{modelLink}' => $modelLink,
                        ));
                    } else {
                        $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                        if (isset($deletionEvent)) {
                            $text = $authorText . Yii::t('app', "received an email from a {transModelName}, but that record has been deleted.", array(
                                        '{transModelName}' => Events::parseModelName($this->associationType)
                            ));
                        } else {
                            $text = $authorText . Yii::t('app', "received an email from a {transModelName}, but that record could not be found.", array(
                                        '{transModelName}' => Events::parseModelName($this->associationType)
                            ));
                        }
                    }
                }

                break;
            case 'voip_call':
                if (count(X2Model::model($this->associationType)->findAllByPk($this->associationId)) > 0) {
                    $text = Yii::t('app', "{modelLink} called.", array(
                                '{modelLink}' => X2Model::getModelLink($this->associationId, $this->associationType)
                    ));
                } else {
                    $deletionEvent = X2Model::model('Events')->findByAttributes(array('type' => 'record_deleted', 'associationType' => $this->associationType, 'associationId' => $this->associationId));
                    if (isset($deletionEvent)) {
                        $text = $authorText . Yii::t('app', "A contact called, but the contact record has been deleted.");
                    } else {
                        $text = $authorText . Yii::t('app', "Call from a contact whose record could not be found.");
                    }
                }

                break;
            case 'media':
                $media = $this->legacyMedia;
                $text = substr($authorText, 0, -1) . ": " . $this->text;
                if (isset($media)) {
                    if (!$truncated) {
                        $text.="<br>" . Media::attachmentSocialText($media->getMediaLink(), true, true);
                    } else {
                        $text.="<br>" . Media::attachmentSocialText($media->getMediaLink(), true, false);
                    }
                } else {
                    $text.="<br>Media file not found.";
                }
                break;
            case 'topic_reply':
                $reply = TopicReplies::model()->findByPk($this->associationId);
                if (isset($reply)) {
                    $topicLink = X2Html::link(
                        $reply->topic->name, 
                        Yii::app()->controller->createUrl(
                            '/topics/topics/view', 
                            array('id' => $reply->topic->id, 'replyId' => $reply->id)));
                    $text = Yii::t('topics', '{poster} posted a new reply to {topic}.', array(
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
                    $text = Yii::t('topics', '{poster} posted a new reply to a topic, but that reply has been deleted.', array(
                                '{poster}' => $authorText,
                    ));
                }
                break;
            default:
                $text = $authorText . CHtml::encode($this->text);
                break;
        }
        if ($truncated && mb_strlen($text, 'UTF-8') > 250) {
            $text = mb_substr($text, 0, 250, 'UTF-8') . "...";
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
        'workflow_complete' => 'Process Complete',
        'workflow_revert' => 'Process Reverted',
        'workflow_start' => 'Process Started',
        'doc_update' => 'Doc Updates',
        'email_from' => 'Email Received',
        'media' => 'Media',
        'voip_call' => 'VOIP Call',
        'topic_reply' => 'Topic Replies',
    );

    public static function parseType($type) {
        if (array_key_exists($type, self::$eventLabels))
            $type = self::$eventLabels[$type];

        return Yii::t('app', $type);
    }

    /**
     * Delete expired events (expiration defined by "Event Deletion Time" admin setting
     */
    public static function deleteOldEvents() {
        $dateRange = Yii::app()->settings->eventDeletionTime;
        if (!empty($dateRange)) {
            $dateRange = $dateRange * 24 * 60 * 60;
            $deletionTypes = json_decode(Yii::app()->settings->eventDeletionTypes, true);
            if (!empty($deletionTypes)) {
                $deletionTypes = "('" . implode("','", $deletionTypes) . "')";
                $time = time() - $dateRange;
                X2Model::model('Events')->deleteAll(
                        'lastUpdated < ' . $time . ' AND type IN ' . $deletionTypes);
            }
        }
    }

    /**
     * @param User $user
     * @return bool true if event is visible to user, false otherwiser 
     */
    public function isVisibleTo ($user) {
        if (Yii::app()->params->isAdmin) return true;
        if (!$user) return false;

        $assignedUser = null;
        if ($this->associationType === 'User') {
            $assignedUser = User::model ()->findByPk ($this->associationId);
        }
        switch (Yii::app()->settings->historyPrivacy) { 
            case 'group':
                if (in_array ($this->user, Groups::getGroupmates ($user->id)) ||
                    ($this->associationType === 'User' && 
                     $assignedUser &&
                     in_array ($assignedUser->username, Groups::getGroupmates ($user->id)))) {

                    return true;
                }
                // fall through
            case 'user':
                if ($this->user === $user->username ||
                    ($this->associationType === 'User' && 
                     ($this->associationId === $user->id))) {

                    return true;
                }
                break;
            default: // default history privacy (public or assigned)
                return ($this->user === $user->username || $this->visibility ||
                    $this->associationType === 'User' && $this->associationId === $user->id);
        }
        return false;
    }

    /**
     * @param Profile $profile Profile to filter events by. Used for profile feeds other than
     *  the current user's
     * @return CDbCriteria Events access criteria based on history privacy admin setting
     */
    public function getAccessCriteria (Profile $profile=null) {
        $criteria = new CDbCriteria;

        // ensures that condition string can be appended to other conditions
        $criteria->addCondition ('TRUE');
        if (!Yii::app()->params->isAdmin) {
            $criteria->params[':getAccessCriteria_username'] = Yii::app()->user->getName ();
            $criteria->params[':getAccessCriteria_userId'] = Yii::app()->user->getId ();
            $userCondition = '
                user=:getAccessCriteria_username OR
                associationType="User" AND associationId=:getAccessCriteria_userId
            ';
            if (Yii::app()->settings->historyPrivacy == 'user') {
                $criteria->addCondition ($userCondition);
            } elseif (Yii::app()->settings->historyPrivacy == 'group') {
                $criteria->addCondition ("
                    $userCondition OR
                    user IN (
                        SELECT DISTINCT b.username 
                        FROM x2_group_to_user a JOIN x2_group_to_user b 
                        ON a.groupId=b.groupId 
                        WHERE a.username=:getAccessCriteria_username
                    ) OR (
                        associationType='User' AND associationId in (
                            SELECT DISTINCT b.id
                            FROM x2_group_to_user a JOIN x2_group_to_user b
                            ON a.groupId=b.groupId
                            WHERE a.userId=:getAccessCriteria_userId
                        )
                    )");
            } else { // default history privacy (public or assigned)
                $criteria->addCondition ("
                    $userCondition OR visibility=1
                ");
            }
        }

        if ($profile) {
            $criteria->params[':getAccessCriteria_profileUsername'] = $profile->username;
            /* only show events associated with current profile which current user has
              permission to see */
            $criteria->addCondition ("user=:getAccessCriteria_profileUsername");
            if (!Yii::app()->params->isAdmin) {
                $criteria->addCondition ("visibility=1");
            }
        }
        return $criteria;
    }

    /**
     * Checks permissions for this event
     * TODO: add unit test
     */
    private $_permissions;
    public function checkPermissions ($action=null, $refresh = false) {
        if (!isset ($this->_permissions) || $refresh) {
            if (!Yii::app()->params->isAdmin) {
                $username = Yii::app()->user->getName ();
                $userId = Yii::app()->user->getId ();
                $userCondition = '
                    user=:getAccessCriteria_username OR
                    associationType="User" AND associationId=:getAccessCriteria_userId
                ';
                $edit = false;
                $view = $this->user === $username ||
                    strtolower ($this->associationType) === 'user' && 
                    $this->associationId == $userId;
                if (Yii::app()->settings->historyPrivacy == 'user') {
                } elseif (Yii::app()->settings->historyPrivacy == 'group') {
                    $view |= in_array (
                        strtolower ($this->user), 
                        Yii::app()->db->createCommand ("
                            SELECT LOWER(DISTINCT b.username)
                            FROM x2_group_to_user a JOIN x2_group_to_user b 
                            ON a.groupId=b.groupId 
                            WHERE a.username=:username
                        ")->queryColumn (array (':username' => $username))) ||
                        $this->associationType==='User' && 
                        in_array (
                            $this->associationId, 
                            Yii::app()->db->createCommand ("
                                SELECT DISTINCT b.id
                                FROM x2_group_to_user a JOIN x2_group_to_user b
                                ON a.groupId=b.groupId
                                WHERE a.userId=:userId
                            ")->queryColumn (array (':userId' => $userId)));
                } else { // default history privacy (public or assigned)
                    $view |= $this->visibility;
                }

                $edit = $view && $this->type === 'feed' && $this->user === $username;
                $delete = $view && $this->type === 'feed' && 
                    ($this->user === $username || $this->associationId == $userId);
            } else {
                $view = $edit = $delete = true;
            }
            $this->_permissions = array (
                'view' => $view,
                'edit' => $edit,
                'delete' => $delete,
            );
        } else {
            extract ($this->_permissions);
        }

        if (!$action) 
            return array('view' => (bool)$view, 'edit' => (bool)$edit, 'delete' => (bool)$delete);
        switch ($action) {
            case 'view':
                return (bool)$view;
            case 'edit':
                return (bool)$edit;
            case 'delete':
                return (bool)$delete;
            default:
                return false;
        }
    }

    /**
     * Returns events filtered with feed filters. Saves filters in session.
     * @param Profile $profile
     * @param bool $privateProfile whether to display public or private profile
     * @param array $filters
     * @param bool $filtersOn
     * @return array
     *  'dataProvider' => <object>
     *  'lastUpdated' => <integer>
     *  'lastId' => <integer>
     */
    public static function getFilteredEventsDataProvider(
        $profile, $privateProfile, $filters, $filtersOn) {

        $params = array(':username' => Yii::app()->user->getName ());
        $accessCriteria = Events::model ()->getAccessCriteria (!$privateProfile ? $profile : null);

        $visibilityCondition = '';
        if ($filtersOn || isset($_SESSION['filters'])) {
            if ($filters) {
                unset($_SESSION['filters']);
            } else {
                $filters = $_SESSION['filters'];
                $filters = array_map(function ($n) {
                    return implode(',', $n);
                }, $filters);
                $filters['default'] = false;
            }
            $parsedFilters = Events::parseFilters($filters, $params);

            $visibilityFilter = $parsedFilters['filters']['visibility'];
            $userFilter = $parsedFilters['filters']['users'];
            $typeFilter = $parsedFilters['filters']['types'];
            $subtypeFilter = $parsedFilters['filters']['subtypes'];

            $default = $filters['default'];
            $_SESSION['filters'] = array(
                'visibility' => $visibilityFilter,
                'users' => $userFilter,
                'types' => $typeFilter,
                'subtypes' => $subtypeFilter
            );
            if ($default == 'true') {
                Yii::app()->params->profile->defaultFeedFilters = json_encode(
                    $_SESSION['filters']);
                Yii::app()->params->profile->save();
            }
            $visibilityCondition .= $parsedFilters['conditions']['visibility'];
            $userCondition = $parsedFilters['conditions']['users'];
            $typeCondition = $parsedFilters['conditions']['types'];
            $subtypeCondition = $parsedFilters['conditions']['subtypes'];

            $condition = "(associationType is null or associationType!='Events') AND 
                (type!='action_reminder' " .
                "OR user=:username) AND " .
                "(type!='notif' OR user=:username)" .
                $visibilityCondition . $userCondition . $typeCondition . $subtypeCondition;
            $_SESSION['feed-condition'] = $condition;
            $_SESSION['feed-condition-params'] = $params;
        } else {
            $condition = "(associationType is null or associationType!='Events') AND " .
                    "(type!='action_reminder' OR user=:username) " .
                    "AND (type!='notif' OR user=:username)" .
                    $visibilityCondition;
        }

        $condition.= " AND timestamp <= " . time();
        $condition .= ' AND ('.$accessCriteria->condition.')';

        if (!isset($_SESSION['lastEventId'])) {

            $lastId = Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->order('timestamp DESC, id DESC')
                ->limit(1)
                ->queryScalar();

            $_SESSION['lastEventId'] = $lastId;
        } else {
            $lastId = $_SESSION['lastEventId'];
        }
        $lastTimestamp = Yii::app()->db->createCommand()
            ->select('MAX(timestamp)')
            ->from('x2_events')
            ->where($condition, array_merge ($params, $accessCriteria->params))
            ->order('timestamp DESC, id DESC')
            ->limit(1)
            ->queryScalar();
        if (empty($lastTimestamp)) {
            $lastTimestamp = 0;
        }
        if (isset($_SESSION['lastEventId'])) {
            $condition.=" AND id <= :lastEventId AND sticky = 0";
            $params[':lastEventId'] = $_SESSION['lastEventId'];
        }


        $paginationClass = 'CPagination';
         
        $dataProvider = new CActiveDataProvider('Events', array(
            'criteria' => array(
                'condition' => $condition,
                'order' => 'timestamp DESC, id DESC',
                'params' => array_merge ($params, $accessCriteria->params),
            ),
            'pagination' => array(
                'class' => $paginationClass,
                'pageSize' => 20
            ),
        ));

        return array(
            'dataProvider' => $dataProvider,
            'lastTimestamp' => $lastTimestamp,
            'lastId' => $lastId
        );
    }

    private static function parseFilters($filters, &$params) {
        unset($filters['filters']);
        $visibility = $filters['visibility'];
        $visibility = str_replace('Public', '1', $visibility);
        $visibility = str_replace('Private', '0', $visibility);
        $visibilityFilter = explode(",", $visibility);
        if ($visibility != "") {
            $visibilityParams = AuxLib::bindArray($visibilityFilter, 'visibility');
            $params = array_merge($params, $visibilityParams);
            $visibilityCondition = " AND visibility NOT IN (" . 
                implode(',', array_keys($visibilityParams)) . ")";
        } else {
            $visibilityCondition = "";
            $visibilityFilter = array();
        }

        $users = $filters['users'];
        if ($users != "") {
            $users = explode(",", $users);
            $users[] = '';
            $users[] = 'api';
            $userFilter = $users;
            if (sizeof($users)) {
                $usersParams = AuxLib::bindArray($users, 'users');
                $params = array_merge($params, $usersParams);
                $userCondition = " AND (user NOT IN (" . 
                    implode(',', array_keys($usersParams)) . ")";
            } else {
                $userCondition = "(";
            }
            if (!in_array('Anyone', $users)) {
                $userCondition.=" OR user IS NULL)";
            } else {
                $userCondition.=")";
            }
        } else {
            $userCondition = "";
            $userFilter = array();
        }

        $types = $filters['types'];
        if ($types != "") {
            $types = explode(",", $types);
            $typeFilter = $types;
            $typesParams = AuxLib::bindArray($types, 'types');
            $params = array_merge($params, $typesParams);
            $typeCondition = " AND (type NOT IN (" . 
                implode(',', array_keys($typesParams)) . ") OR important=1)";
        } else {
            $typeCondition = "";
            $typeFilter = array();
        }
        $subtypes = $filters['subtypes'];
        if (is_array($types) && $subtypes != "") {
            $subtypes = explode(",", $subtypes);
            $subtypeFilter = $subtypes;
            if (sizeof($subtypes)) {
                $subtypeParams = AuxLib::bindArray($subtypes, 'subtypes');
                $params = array_merge($params, $subtypeParams);
                $subtypeCondition = " AND (
                    type!='feed' OR subtype NOT IN (" . 
                        implode(',', array_keys($subtypeParams)) . ") OR important=1)";
            } else {
                $subtypeCondition = "";
            }
        } else {
            $subtypeCondition = "";
            $subtypeFilter = array();
        }
        $ret = array(
            'filters' => array(
                'visibility' => $visibilityFilter,
                'users' => $userFilter,
                'types' => $typeFilter,
                'subtypes' => $subtypeFilter,
            ),
            'conditions' => array(
                'visibility' => $visibilityCondition,
                'users' => $userCondition,
                'types' => $typeCondition,
                'subtypes' => $subtypeCondition,
            ),
            'params' => $params,
        );
        return $ret;
    }

    /**
     * TODO: merge this method with getFilteredEventsDataProvider, and remove reliance on SESSION, 
     *  regenerating condition on each request instead of storing it
     * @param int $lastEventId
     * @param string $lastTimestamp
     * @param object $profile The current user's profile model
     * @param object $profileId The profile model for which events are being requested.
     */
    public static function getEvents(
        $lastEventId, $lastTimestamp, $limit = null, $profile = null, $privateProfile = true) {

        $user = Yii::app()->user->getName();
        $criteria = new CDbCriteria();
        $prefix = ':getEvents'; // to prevent name collisions with feed-condition-params
        $sqlParams = array(
            $prefix . 'username' => $user,
            $prefix . 'maxTimestamp' => time(),
        );
        $parameters = array('order' => 'timestamp DESC, id DESC');
        if (!is_null($limit) && is_numeric($limit)) {
            $parameters['limit'] = $limit;
        }

        $sqlParams[$prefix . 'id'] = $lastEventId;
        $sqlParams[$prefix . 'timestamp'] = $lastTimestamp;
        $accessCriteria = Events::model ()->getAccessCriteria (!$privateProfile ? $profile : null);
        if (isset($_SESSION['feed-condition']) && isset($_SESSION['feed-condition-params'])) {
            $sqlParams = array_merge($sqlParams, $_SESSION['feed-condition-params']);
            $condition = $_SESSION['feed-condition'] . " AND 
                (`type`!='action_reminder' OR `user`=" . $prefix . "username) AND 
                (`type`!='notif' OR `user`=" . $prefix . "username) AND 
                (id > " . $prefix . "id AND (timestamp > " . $prefix . "timestamp AND timestamp < " . $prefix . "maxTimestamp))";
        } else {
            $condition = '(id > ' . $prefix . 'id AND (timestamp > ' . $prefix . 'timestamp AND timestamp < ' . $prefix . 'maxTimestamp)) AND 
                 (`associationType` is null or `associationType`!="Events")' . " AND 
                 (`type`!='action_reminder' OR `user`=" . $prefix . "username) AND 
                 (`type`!='notif' OR `user`=" . $prefix . "username)";
        }

        $sqlParams = array_merge($sqlParams, $accessCriteria->params);
        $condition .= " AND ($accessCriteria->condition)";

        $parameters['condition'] = $condition;
        $parameters['params'] = $sqlParams;
        $criteria->scopes = array('findAll' => array($parameters));

        return array(
            'events' => X2Model::model('Events')->findAll($criteria),
        );
    }

    public static function generateFeedEmail($filters, $userId, $range, $limit, $eventId, $deleteKey) {
        $image = Yii::app()->getAbsoluteBaseUrl(true).'/images/x2engine.png';

        $msg = "<div id='wrap' style='width:6.5in;height:9in;margin-top:auto;margin-left:auto;margin-bottom:auto;margin-right:auto;'><html><body><center>";
        $msg .= '<table border="0" cellpadding="0" cellspacing="0" height="100%" id="top-activity" style="background: white; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-weight: normal; font-style: normal; font-size: 14px; line-height: 1; color: #222222; position: relative; -webkit-font-smoothing: antialiased;background-color:#FAFAFA;height:25% !important; margin:0; padding:0; width:100% !important;" width="100%">'
                . "<tbody><tr><td align=\"center\" style=\"padding-top:20px;\" valign=\"top\">"
                . '<table border="0" cellpadding="0" cellspacing="0" id="templateContainer" style="border: 1px solid #DDDDDD;background-color:#FFFFFF;" width="600"><tbody>';
        $msg .= '<tr>
                    <td align="center" valign="top"><!-- // Begin Template Header \\ -->
			<table border="0" cellpadding="0" cellspacing="0" id="templateHeader" width="600">
                            <tbody>
				<tr>
                                    <td class="headerContent" style="color:#202020;font-weight:bold;line-height:100%;padding:0;text-align:center;vertical-align:middle;font-family: inherit;font-weight: normal;font-size: 14px;margin-bottom: 17px"><img id="headerImage campaign-icon" src="' . $image .'" style="border:0; height:auto; line-height:100%; outline:none; text-decoration:none;max-width:600px;" /></td>
                                </tr>
                                <tr>
                                    <td style="color:#202020;font-weight:bold;padding:5px;text-align:center;vertical-align:middle;font-family: inherit;font-weight: normal;font-size: 14px;"><h2>' . Yii::t('profile', 'Activity Feed Report') . '</h2></td>
                                </tr>
                            </tbody>
			</table>
                    <hr width="60%"></td><!-- // End Template Header \\ -->
		</tr>';

        $msg.='<tr><td align="center" valign="top"><!-- // Begin Template Body \\ -->'
                . '<table border="0" cellpadding="0" cellspacing="0" id="templateBody" width="600"><tbody><tr>'
                . '<td valign="top"><!-- // Begin Module: Standard Content \\ -->'
                . '<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody>';

        $params = array();

        $userRecord = X2Model::model('Profile')->findByPk($userId);
        $params[':username'] = $userRecord->username;

        $parsedFilters = Events::parseFilters($filters, $params);

        $visibilityCondition = $parsedFilters['conditions']['visibility'];
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $userCondition = $parsedFilters['conditions']['users'];
        $typeCondition = $parsedFilters['conditions']['types'];
        $subtypeCondition = $parsedFilters['conditions']['subtypes'];

        $condition = "type!='comment' AND (type!='action_reminder' " .
                "OR user=:username) AND " .
                "(type!='notif' OR user=:username)" .
                $visibilityCondition . $userCondition . $typeCondition . $subtypeCondition . 
                ' AND ('.$accessCriteria->condition.')';
        switch ($range) {
            case 'daily':
                $timeRange = 24 * 60 * 60;
                break;
            case 'weekly':
                $timeRange = 7 * 24 * 60 * 60;
                break;
            case 'monthly':
                $timeRange = 30 * 24 * 60 * 60;
                break;
            default:
                $timeRange = 24 * 60 * 60;
                break;
        }
        $condition .= " AND timestamp BETWEEN " . (time() - $timeRange) . " AND " . time();

        $topTypes = Yii::app()->db->createCommand()
                ->select('type, COUNT(type)')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->group('type')
                ->order('COUNT(type) DESC')
                ->limit(5)
                ->queryAll();

        $topUsers = Yii::app()->db->createCommand()
                ->select('user, COUNT(user)')
                ->from('x2_events')
                ->where($condition, array_merge ($params, $accessCriteria->params))
                ->group('user')
                ->order('COUNT(user) DESC')
                ->limit(5)
                ->queryAll();

        $msg .= "<tr><td style='text-align:center;'>";
        $msg .= "<div>" . Yii::t('profile', "Here's your {range} update on what's been going on in X2Engine!", array(
                    '{range}' => Yii::t('profile', $range))) . "</div><br>"
                . "<div>Time Range: <em>" . Formatter::formatDateTime(time() - $timeRange) . "</em> to <em>" . Formatter::formatDateTime(time()) . "</em></div>";
        $msg .= "</tr></td>";

        $msg .= "<tr><td><table width='100%'><tbody>";
        $msg .= "<tr><th>" . Yii::t('profile', "Top Activity") . "</th><th>" . Yii::t('profile', "Top Users") . "</th></tr>";
        for ($i = 0; $i < 5; $i++) {
            $msg .= "<tr><td style='text-align:center;'>";
            if (isset($topTypes[$i])) {
                $type = Events::parseType($topTypes[$i]['type']);
                $count = $topTypes[$i]['COUNT(type)'];
                $msg .= $count . " " . $type;
            }
            $msg .= "</td><td style='text-align:center;'>";
            if (isset($topUsers[$i]) && $topUsers[$i]['COUNT(user)'] > 0) {
                $username = User::getUserLinks($topUsers[$i]['user'], false, true);
                $count = $topUsers[$i]['COUNT(user)'];
                $msg .= $count . " " . Yii::t('profile', "events from") . " " . $username . ".";
            }
            $msg .= "</td></tr>";
        }
        $msg .= "</tbody></table></td></tr>";
        $msg .= "<tr><td style='text-align:center'><hr width='60%'>";
        $msg .= "<tr><td style='text-align:center;'>" .
                Yii::t('profile', "Here's the {limit} most recent items on the Activity Feed.", array('{limit}' => $limit))
                . "</td></tr>";
        $msg .= "</td></tr>";
        $msg .= "<tr><td style='text-align:center'><hr width='60%'><table><tbody>";
        $events = new CActiveDataProvider('Events', array(
            'criteria' => array(
                'condition' => $condition,
                'order' => 'timestamp DESC',
                'params' => array_merge ($params, $accessCriteria->params),
            ),
            'pagination' => array(
                'pageSize' => $limit
            ),
        ));

        foreach ($events->getData() as $event) {
            $msg .= "<tr>";
            $avatar = Yii::app()->db->createCommand()
                ->select('avatar')
                ->from('x2_profile')
                ->where('username=:user', array(':user' => $event->user))
                ->queryScalar();
            if (!empty($avatar) && file_exists($avatar)) {
                $avatar = Profile::renderAvatarImage($id, 45, 45);
            } else {
                $avatar = X2Html::x2icon('profile-large',
                                array(
                            'class' => 'avatar-image default-avatar',
                            'style' => "font-size: ${dimensionLimit}px",
                ));
            }
            $typeFile = $event->type;
            if (in_array($event->type, array('email_sent', 'email_opened'))) {
                if (in_array($event->subtype, array('quote', 'invoice')))
                    $typeFile .= "_{$event->subtype}";
            }
            if ($event->type == 'record_create') {
                switch ($event->subtype) {
                    case 'call':
                        $typeFile = 'voip_call';
                        break;
                    case 'time':
                        $typeFile = 'log_time';
                        break;
                }
            }
            $img = $avatar;
            if (file_exists(Yii::app()->theme->getBasePath() . '/images/eventIcons/' . $typeFile . '.png')) {
                $imgFile = Yii::app()->getAbsoluteBaseUrl() . '/themes/' . Yii::app()->theme->getName() . '/images/eventIcons/' . $typeFile . '.png';
                $img = CHtml::image($imgFile, '',
                                array(
                            'style' => 'width:45px;height:45px;float:left;margin-right:5px;',
                ));
            }

            $msg .= "<td>" . $img . "</td>";
            $msg .= "<td style='text-align:left'><span class='event-text'>" . $event->getText(array('requireAbsoluteUrl' => true), array('style' => 'text-decoration:none;')) . "</span></td>";
            $msg .= "</tr>";
        }
        $msg .= "</tbody></table></td></tr>";

        $msg .= "<tr><td style='text-align:center'><hr width='60%'><table><tbody>";
        $msg .= Yii::t('profile', "To stop receiving this report, ") . CHtml::link(Yii::t('profile', 'click here'), Yii::app()->getAbsoluteBaseUrl() . '/index.php/profile/deleteActivityReport?id=' . $eventId . '&deleteKey=' . $deleteKey);
        $msg .= "</tbody></table></td></tr>";

        $msg .= '</tbody></table></td></tr></tbody></table></td></tr>';

        $msg .= "<tbody></table></td></tr></tbody></table></center></body></html></div>";

        return $msg;
    }

    public function isTypeFeed () {
        return $this->type === 'feed' || $this->type === 'structured-feed';
    }

    protected function beforeSave() {
        if (empty($this->timestamp))
            $this->timestamp = time();
        $this->lastUpdated = time();
        if (!empty($this->user) && $this->isNewRecord) {
            $eventsData = X2Model::model('EventsData')->findByAttributes(array('type' => $this->type, 'user' => $this->user));
            if (isset($eventsData)) {
                $eventsData->count++;
            } else {
                $eventsData = new EventsData;
                $eventsData->user = $this->user;
                $eventsData->type = $this->type;
                $eventsData->count = 1;
            }
            $eventsData->save();
        }
        if ($this->type == 'record_deleted') {
            $this->text = preg_replace('/(<script(.*?)>|<\/script>)/', '', $this->text);
        }
        return parent::beforeSave();
    }

    protected function beforeDelete() {
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $child->delete();
            }
        }
        return parent::beforeDelete();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('admin', 'ID'),
            'type' => Yii::t('admin', 'Type'),
            'text' => Yii::t('admin', 'Text'),
            'associationType' => Yii::t('admin', 'Association Type'),
            'associationId' => Yii::t('admin', 'Association ID'),
        );
    }

}
