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




class CalendarInviteBehavior extends ActiveRecordBehavior {

    public $emailAddresses = array();

    public function afterSave($event) {
        if ($this->owner->isNewRecord) {
            $this->createCalendarInvites();
        } else {
            if($this->owner->oldAttributes['dueDate'] != $this->owner->dueDate){
                $this->updateCalendarInvites();
            }
        }
    }

    private function createCalendarInvites() {
        $subject = "Event Invite";
        $message = "You have been invited to an event by ".Yii::app()->params->profile->fullName.".<br><br>";
        $message .= "<b>What: </b>".$this->owner->actionDescription."<br>";
        $message .= "<b>When: </b>{date}<br>";
        $message .= "<b>RSVP: </b>Please RSVP by clicking {rsvpLink}.";
        foreach ($this->emailAddresses as $email) {
            $invite = new CalendarInvites();
            $invite->actionId = $this->owner->id;
            $invite->email = $email;
            $invite->inviteKey = bin2hex(openssl_random_pseudo_bytes(32));
            $invite->save();
            $this->sendCalendarInvite($invite, $subject, $message);
        }
    }

    private function updateCalendarInvites() {
        $subject = "Event Time Change";
        $message = "An event you were previously invited to has changed.<br><br>";
        $message .= "<b>What: </b>".$this->owner->actionDescription."<br>";
        $message .= "<b>When: </b>{date}<br>";
        $message .= "<b>RSVP: </b>Please RSVP again by clicking {rsvpLink}.";
        $invites = X2Model::model('CalendarInvites')->findAllByAttributes(array('actionId' => $this->owner->id));
        foreach ($invites as $invite) {
            $invite->status = null;
            $invite->save();
            $this->sendCalendarInvite($invite, $subject, $message);
        }
    }

    private function sendCalendarInvite($invite, $subject, $message) {
        
        if ($this->owner->allDay) {
            $formattedDate = Formatter::formatDueDate($this->owner->dueDate, 'long', null) . Yii::t('calendar', 'All Day');
        } else {
            $formattedDate = Formatter::formatDueDate($this->owner->dueDate, 'long', 'long');
        }
        $rsvpLink = $invite->getRsvpLink();

        $eml = new InlineEmail();
        $from = Credentials::model()->getDefaultUserAccount(Yii::app()->user->id);
        if ($from == Credentials::LEGACY_ID) {
            $from = array('name' => Yii::app()->params->profile->fullName, 'address' => Yii::app()->params->profile->emailAddress);
        }

        if (is_numeric($from)) {
            $eml->credId = $from;
        } else {
            $eml->from = $from;
        }
        $eml->to = $invite->email;
        $eml->subject = str_replace('{date}', $formattedDate, $subject);
        $eml->message = str_replace(array('{rsvpLink}', '{date}'), array($rsvpLink, $formattedDate), $message);
        if ($eml->prepareBody()) {
            $eml->send();
        }
    }

}
