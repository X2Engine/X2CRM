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
 * Send an email to an individual user. This method was previously defined in x2base, and
 * was extracted into a behavior to share with controllers who do not inherit x2base
 *
 * @package application.components
 */
class UserMailerBehavior extends CBehavior {
    /**
     * Send an email from X2Engine, returns an array with status code/message
     *
     * @param array addresses
     * @param string $subject the subject for the email
     * @param string $message the body of the email
     * @param array $attachments array of attachments to send
     * @param array|integer $from from and reply to address for the email array(name, address)
     *     or, if integer, the ID of a email credentials record to use for delivery.
     * @return array
     */
    protected function sendUserEmail($addresses, $subject, $message, $attachments = null, $from = null){
        $eml = new InlineEmail();
        if(is_array($addresses) ? count($addresses)==0 : true)
            throw new Exception('Invalid argument 1 sent to x2base.sendUserEmail(); expected a non-empty array, got instead: '.var_export($addresses,1));
        // Set recipients:
        if(array_key_exists('to',$addresses) || array_key_exists('cc',$addresses) || array_key_exists('bcc',$addresses)) {
            $eml->mailingList = $addresses;
        } else
            return array('code'=>500,'message'=>'No recipients specified for email; array given for argument 1 of x2base.sendUserEmail does not have a "to", "cc" or "bcc" key.');
        // Resolve sender (use stored email credentials or system default):
        if($from === null || in_array($from,Credentials::$sysUseId)) {
            $from = (int) Credentials::model()->getDefaultUserAccount($from);
            // Set to the user's name/email if no valid defaults found:
            if($from == Credentials::LEGACY_ID)
                $from = array('name' => Yii::app()->params->profile->fullName, 'address'=> Yii::app()->params->profile->emailAddress);
        }

        if(is_numeric($from))
            $eml->credId = $from;
        else
            $eml->from = $from;
        // Set other attributes
        $eml->subject = $subject;
        $eml->message = $message;
        $eml->attachments = $attachments;
        return $eml->deliver();
    }
}
?>
