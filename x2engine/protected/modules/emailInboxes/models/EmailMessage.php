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






Yii::import('application.models.X2Model');

/**
 * This is the model class for individual Email Messages
 *
 * @package application.modules.emailInboxes.models
 */
class EmailMessage extends CModel {

    // IMAP Unique ID of message
    private $uid;

    // the associated EmailInbox
    private $inbox;

    /**
     * @var null|Actions $_action model associated with this message
     */
    private $_action; 

    // Email Message attributes
    public $msgno;
    public $subject;
    public $from;
    public $to;
    public $cc;
    public $reply_to;
    public $body;
    public $date;
    public $size;
    public $attachments;
    // Flags
    public $seen;
    public $flagged;
    public $answered;

    public function attributeNames() {
        return array(
            'uid',
            'msgno',
            'subject',
            'from',
            'to',
            'cc',
            'reply_to',
            'body',
            'date',
            'size',
            'attachments',
            'seen',
            'flagged',
            'answered',
        );
    }

    /**
     * Constructor sets the Email Inbox then loads the message attributes
     */
    public function __construct($inbox, $attributes) {
        $this->inbox = $inbox;
        // Load private email attributes
        foreach ($attributes as $attr => $value)
            $this->$attr = $value;
        if (empty($this->subject))
            $this->subject = "(No Subject)";
    }

    private static $_purifier;
    public static function getPurifier () {
        if (!isset (self::$_purifier)) {
            self::$_purifier = new CHtmlPurifier();
            self::$_purifier->options = array ( 
                'HTML.ForbiddenElements' => array (
                    'script'
                ),
            );
        }
        return self::$_purifier;
    }

    /**
     * Retrieve all emails addresses to Reply All
     * @return array of email addresses
     */
    public function getReplyAllAddresses() {
        $replyAll = array();
        foreach (array('from', 'to', 'cc') as $field) {
            if (!empty ($this->$field)) {
                $emailArray = EmailDeliveryBehavior::addressHeaderToArray ($this->$field, true);
                $replyAll = array_merge ($replyAll, $emailArray);
            }
        }
        // filter out address of current inbox
        $inboxAddress = $this->inbox->credentials->auth->email;
        $replyAll = array_filter ($replyAll, function ($address) use ($inboxAddress) {
            return $address[1] !== $inboxAddress;
        });
        return $replyAll;
    }

    /**
     * Purifies certain attributes, removing script tags and inline JS
     */
    public function purifyAttributes () {
        $purifier = self::getPurifier ();
        $excludeList = array ('from', 'to', 'cc');
        foreach (array_diff ($this->attributeNames (), $excludeList) as $name) {
            $this->$name = $purifier->purify ($this->$name);
        }
    }

    /**
     * @return int Unique ID of the message
     */
    public function getUid() { return $this->uid; }

    /**
     * @return EmailInboxes The inbox this message belongs to
     */
    public function getInbox () { return $this->inbox; }

    public function renderSubject ($fullTextTitle = false) {
        $subject = $this->subject;
        $subject = preg_replace ('/(re: ?)+/i', 'Re: ', $subject);
        if ($fullTextTitle) {
            return CHtml::tag ('span', array ( 
                'title' => CHtml::encode ($subject),
            ), CHtml::encode ($subject));
        } else {
            return CHtml::encode ($subject);
        }
    }

    public function renderDate ($format='dynamic') {
        switch ($format) {
            case 'dynamic':
                return X2Html::dynamicDate ($this->date);
            case 'full':
                return Yii::app()->dateFormatter->formatDateTime ($this->date, 'long', null);
            case 'hours':
                return Yii::app()->dateFormatter->format ('h:mm a', $this->date);
        }
    }

    /**
     * Retrieves/creates action corresponding to this message and returns it.
     * @return Actions|null null if action doesn't exist and could not be created
     */
    public function getAction ($inbound = false) {
        if (!isset ($this->_action)) {
            $action = Actions::model () // check for existence of action
                ->with (array (
                    'actionMetaData' => array (
                        'select' => false,
                        'joinType' => 'INNER JOIN',
                        'condition' => 
                            'actionMetaData.emailImapUid=:uid AND
                             actionMetaData.emailFolderName=:folderName AND
                             actionMetaData.emailUidValidity=:uidValidity AND
                             actionMetaData.emailInboxId=:inboxId',
                        'params' => array (
                            ':uid' => $this->uid,
                            ':inboxId' => $this->inbox->id,
                            ':folderName' => $this->inbox->currentFolder,
                            ':uidValidity' => $this->inbox->getUidValidity(),
                        )
                    )
                ))
                ->find ();
            if (!$action && !$inbound && ($this->date > 1449780000)) {
                // check for outbound message logged by InlineEmail form if this message was
                // sent after cron-based email logging was introduced.
                // Unfortunately, we cannot match on the message body, as when it is logged by
                // the InlineEmail model the email header information is prepended to the body
                $action = Actions::model ()
                    ->with (array (
                        'actionMetaData' => array (
                            'select' => false,
                            'joinType' => 'INNER JOIN',
                            'condition' => 
                                'actionMetaData.emailImapUid IS NULL AND
                                 actionMetaData.emailFolderName=:folderName AND
                                 actionMetaData.emailUidValidity=:uidValidity AND
                                 actionMetaData.emailInboxId=:inboxId',
                            'params' => array (
                                ':inboxId' => $this->inbox->id,
                                ':folderName' => $this->inbox->currentFolder,
                                ':uidValidity' => $this->inbox->getUidValidity(),
                            )
                        )
                    ))
                    ->findAll ('subject = :subject', array(':subject' => $this->subject));
                if (count($action) === 1) {
                    // Located single matching Action
                    $action = $action[0];
                } else {
                    // Located multiple matching Actions, attempt to determine which body matches
                    $possibleMatches = array();
                    $matchUid = InlineEmail::extractTrackingUid ($this->body);
                    if ($matchUid) {
                        foreach ($action as $a)
                            if (strpos($a->actionDescription, $matchUid) !== false)
                                $possibleMatches[] = $a;
                        if (count($possibleMatches) === 1)
                            $action = $possibleMatches[0];
                        else
                            $action = null;
                    } else {
                        $action = null;
                    }
                }
                // an email previously logged by InlineEmail will have a single association and
                // will be missing the UID
                if ($action) {
                    $action->emailImapUid = $this->uid;
                    $action->convertToMultiassociation();
                }
            }

            if ($action) {
                $this->_action = $action;
            } else { // no action exists, create one
                $action = new Actions;
                $now = time ();
                $user = $this->inbox->assignedTo;
                $action->setAttributes (array (
                    'subject' => $this->subject,
                    'type' => ($inbound ? 'emailFrom' : 'email'),
                    'visibility' => 1,
                    'createDate' => $now,
                    'lastUpdated' => $now,
                    'completeDate' => $this->date,
                    'assignedTo' => $user,
                    'completedBy' => $user,
                    'associationType' => Actions::ASSOCIATION_TYPE_MULTI,
                ), false);

                $action->actionDescription = $this->body;
                $action->emailImapUid = $this->uid;
                $action->emailInboxId = $this->inbox->id;
                $action->emailFolderName = $this->inbox->currentFolder;
                $action->emailUidValidity = $this->inbox->getUidValidity();
                if ($action->save ()) {
                    $this->_action = $action;
                } else {
                    $this->_action = null;
                }
            }
        }
        return $this->_action;
    }

    /**
     * @return array of Contacts models having email addresses in the from, to, or cc fields
     */
    public function getAssociatedContacts ($inbound = false) {
        $addresses = array ();
        $contacts = array ();
        $emailFields = $inbound ? array('from') : array ('from', 'to', 'cc');
        // Don't raise exceptions on invalid emails if called by command line
        $ignoreInvalid = ResponseUtil::isCli();
        foreach ($emailFields as $attr) {
            $addresses = array_merge (
                $addresses, EmailDeliveryBehavior::addressHeaderToArray ($this->$attr, $ignoreInvalid));
        }
        $encountered = array (); // used to avoid returning duplicate contacts
        foreach ($addresses as $address) {
            list ($name, $email) = $address;
            $contact = Contacts::model ()->findByEmail ($email);
            if ($contact && !isset ($encountered[$contact->id])) {
                $contacts[] = $contact;
                $encountered[$contact->id] = true;
            }
        }
        return $contacts;
    }

    /**
     * Retrieve the service related to an email based on the tracking image UID
     * @return array of Services models having email addresses in the from, to, or cc fields
     */
    public function getAssociatedServices () {
        $trackRecord = TrackEmail::model()->findByAttributes (array(
            'uniqueId' => InlineEmail::extractTrackingUid ($this->body),
        ));
        if ($trackRecord) {
            $action = Actions::model()->findByPk ($trackRecord->actionId);
            if ($action && $action->associationType === 'services') {
                $service = Services::model()->findByPk ($action->associationId);
                return array($service);
            }
        }
        return array();
    }

    /**
     * Creates tag for non-contact entity
     * @param string $name name of entity
     * @param string $emailAddress
     * @param bool $return
     */
    public function nonContactEntityTag ($name, $emailAddress, $return=false) {
        $tag = 
            "<span class='non-contact-entity-tag' 
              data-email='".CHtml::encode ($emailAddress)."'>".CHtml::encode ($name)."</span>";
        if ($return) return $tag;
        else echo $tag;
    }

    /**
     * @param array name and email address 
     * @param bool $includeEmailAddress whether to render email address in addition to name
     * @param bool $return if true, results will be returned instead of echoed
     */
    public function renderAddress ($address, $includeEmailAddress=false, $return=false) {
        list ($name, $email) = $address;
        $name = trim ($name);
        if (empty ($name)) $name = preg_replace ('/@.*/', '', $email);
        $contact = Contacts::model ()->findByEmail ($email);

        if ($contact) {
            $formattedField = $contact->link;
        } else {
            $formattedField = $this->nonContactEntityTag ($name, $email, true);
        }
        if ($includeEmailAddress)
            $formattedField .= ' '.CHtml::encode ('<'.$email.'>');
        if (!$return)
            echo $formattedField;
        else
            return $formattedField;
    }

    /**
     * @param array of arrays of names and email addresses
     * @param bool $includeEmailAddress whether to render email address in addition to name
     * @param bool $return if true, results will be returned instead of echoed
     */
    public function renderAddresses ($addresses, $includeEmailAddress=false, $return=false) {
        $formattedAddresses = array ();
        foreach ($addresses as $address) {
            $formattedAddresses[] = $this->renderAddress ($address, $includeEmailAddress, true);
        }
        if (!$return) {
            echo implode (', ', $formattedAddresses);
        } else {
            return implode (', ', $formattedAddresses);
        }
    }

    public function renderFromField ($includeEmailAddress=false) {
        $addresses = EmailDeliveryBehavior::addressHeaderToArray ($this->from, true);
        $this->renderAddress (array_shift ($addresses));
    }

    public function renderToField ($includeEmailAddress=false) {
        $addresses = EmailDeliveryBehavior::addressHeaderToArray ($this->to, true);
        $this->renderAddresses ($addresses, false, false);
    }

    public function renderCCField ($includeEmailAddress=false) {
        $addresses = EmailDeliveryBehavior::addressHeaderToArray ($this->cc, true);
        $this->renderAddresses ($addresses, false, false);
    }

    /**
     * Generate a link to toggle this message's importance
     */
    public function renderToggleImportant() {
        echo 
            '<div class="flagged-toggle'.($this->flagged ? ' flagged' : '').'"
              data-uid="'.CHtml::encode ($this->uid).'"
              title="'.
                ($this->flagged ? 
                    CHtml::encode (
                        Yii::t('emailInboxes', 'Click to remove star')) :
                    CHtml::encode (Yii::t('emailInboxes', 'Click to add star'))
                ).
              '">
            </div>';
    }

    /**
     * Download a specific attachment
     * @param int $part Message part number
     */
    public function downloadAttachment($part, $inline = false, $return=false, $decode=true) {
        $stream = $this->inbox->stream;
        $partStruct = imap_bodystruct($stream, imap_msgno($stream, $this->uid), $part);
        if (!$partStruct) {
            throw new CHttpException (404, Yii::t('emailInboxes',
                'Unable to find the requested message part'));
        }

        $filename = utf8_decode($partStruct->dparameters[0]->value);
        $encoding = EmailInboxes::$encodingTypes[$partStruct->encoding];
        if ($decode) {
            $message = $this->inbox->decodeBodyPart ($this->uid, $partStruct, $part);
        } else {
            $message = $this->inbox->getBodyPart ($this->uid, $partStruct, $part);
        }
        $size = strlen($message); // $partStruct->bytes; is not accurate due to encoding
        $mimeType = $this->inbox->getStructureMimetype ($partStruct, true);

        if (!$return) {
            // Render the attachment

            header("Content-Description: File Transfer");
            header("Content-Type: ".$mimeType);
            if (!$inline)
                header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$size);
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            echo $message;
        } else {
            return array ($mimeType, $filename, $size, $message, $encoding);
        }
    }

    /**
     * Generates a link to an attachment
     * @param array $attachment
     * @param string $type ('view'|'download')
     * @return string
     */
    public function getAttachmentLink (array $attachment, $type='view') {
        if ($type === 'view') {
            $action = 'viewAttachment';
        } else if ($type === 'associate') {
            $action = 'associateAttachment';
        } else {
            $action = 'downloadAttachment';
        }
        return Yii::app()->controller->createUrl ('emailInboxes/'.$action, array (
            'id' => $this->inbox->id,
            'uid' => $this->uid,
            'part' => $attachment['part'],
            'emailFolder' => $this->inbox->getCurrentFolder (),
        ), '&amp;'); // encode ampersand to prepare for {@link purifyAttributes}
    }

    /**
     * Find and replace inline attachments in the email with links to view each
     * inline attachment.
     */
    public function parseInlineAttachments() {
        $inlineAttachments = array_filter ($this->attachments, function ($attachment) {
            return 'inline' === $attachment['type'];
        });

        // Construct an array of replacements
        $replacements = array();
        foreach ($inlineAttachments as $attachment) {
            $link = $this->getAttachmentLink ($attachment);
            $replacements['src="cid:'.$attachment['cid'].'"'] = 'src="'.$link.'"';
        }

        $this->body = strtr ($this->body, $replacements);
    }

    /**
     * Renders links to non-inline attachments
     */
    public function renderAttachmentLinks () {
        $attachments = array_filter ($this->attachments, function ($attachment) {
            return 'attachment' === $attachment['type'];
        });

        foreach ($attachments as $attachment) {
            list ($topLevelTypeName, $subtypeName, $params) = 
                $this->parseMimeType ($attachment['mimetype']);

            switch ($topLevelTypeName) {
                case 'audio':
                    $attachmentIconClass = 'fa-file-sound-o';
                    break;
                case 'image':
                    $attachmentIconClass = 'fa-file-picture-o';
                    break;
                case 'video':
                    $attachmentIconClass = 'fa-file-video-o';
                    break;
                case 'text':
                    $attachmentIconClass = 'fa-file-text-o';
                    break;
                /*case 'application':
                case 'message':
                case 'model':
                case 'multipart':*/
                default:
                    $attachmentIconClass = 'fa-file-o';
            }

            echo "
                <div class='message-attachment'>
                    <span class='fa {$attachmentIconClass} attachment-type-icon'></span>
                    <span class='attachment-filename'>".
                        CHtml::encode ($attachment['filename'])."</span>
                    <a class='attachment-download-link fa fa-download x2-button'
                     title='".CHtml::encode (Yii::t('emailInboxes', 'Download attachment'))."'
                     href='#'
                     data-href='".$this->getAttachmentLink ($attachment, 'download')."'>
                    </a>
            ";
            $contacts = $this->getAssociatedContacts(true);
            if (!empty($contacts)) {
                echo CHtml::ajaxLink('', $this->getAttachmentLink ($attachment, 'associate'), array(
                    'complete' => 'function(data) {
                        data = JSON.parse(data.responseText);
                        if (typeof throbber !== "undefined") throbber.remove();
                        x2.topFlashes.displayFlash (data.message, data.type);
                    }',
                ), array(
                    'class' => 'attachment-association-link fa fa-link x2-button',
                    'title' => CHtml::encode (Yii::t('emailInboxes', 'Associate attachment with related record')),
                    'onclick' => 'var throbber = auxlib.pageLoading();',
                ));
            }
            echo "</div>";
        }
    }

    /**
     * Parses the mime type returning the top-level type, the subtype, and the parameters
     * @param string $mimeType 
     */
    public function parseMimeType ($mimeType) {
        $parts = explode ('/', $mimeType);
        if (!count ($parts)) return null;
        $topLevelTypeName = $parts[0];
        $parts = explode (';', $parts[1]);
        $subtypeName = array_shift ($parts);
        $params = $parts;
        return array ($topLevelTypeName, $subtypeName, $params);
    }
}

