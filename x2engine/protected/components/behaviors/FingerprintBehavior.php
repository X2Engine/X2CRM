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
 * Allows methods to be shared between classes assocatiated with fingerprint records (Contacts,
 * AnonContacts).
 *
 * @package application.components
 */
class FingerprintBehavior extends CBehavior {

	public function events() {
		return array_merge(parent::events(),array(
			'onAfterDelete'=>'afterDelete',
		));
	}


    /**
     * Update the fingerprint record associated with this record.
     */
    public function setFingerprint($fingerprint, $attributes) {
        if (ctype_digit($fingerprint)) {
            if (isset($this->owner->fingerprintId)) {
                // already associated with a fingerprint
                $fingerprintRecord = X2Model::model('Fingerprint')->findByPk(
                    $this->owner->fingerprintId);
            } else {
                // lookup fingerprint by hash
                $fingerprintRecord = X2Model::model('Fingerprint')->findByAttributes(
                    array('fingerprint' => $fingerprint));
                if (!isset($fingerprintRecord)) {
                    // create a new fingerprint
                    $fingerprintRecord = new Fingerprint();
                    $fingerprintRecord->createDate = time();
                    if (get_class ($this->owner) === 'AnonContact')
                        $fingerprintRecord->anonymous = true;
                    else
                        $fingerprintRecord->anonymous = false;
                }
            }

            // update the fingerprint hash
            $fingerprintRecord->fingerprint = $fingerprint;

            // update the fingerprint attributes
            if (is_array ($attributes)) {
                foreach ($attributes as $attr => $value) {
                    if (is_array($value))
                        $value = json_encode($value);
                    $fingerprintRecord->$attr = $value;
                }
            }

            if (!$fingerprintRecord->save()) {
                AuxLib::debugLogR ($fingerprintRecord->getErrors ());
            }

            // update the fingerprint pseudo-foreign key
            $this->owner->fingerprintId = $fingerprintRecord->id;
        }
    }

    /**
     * Record the last hostname or IP address associated
     * with a fingerprint
     */
    public function recordAddress() {
        $contact = $this->owner;
        $ip = Yii::app()->controller->getRealIp();
        $contact->reverseIp = (Yii::app()->settings->performHostnameLookups && !empty($ip))?
            gethostbyaddr($ip) : $ip;
        if (!$contact->isNewRecord)
            $contact->update(array('reverseIp'));
    }

    /**
     * Delete fingerprint if no other record links to it 
     */
    public function afterDelete () {
        $fingerprint = $this->owner->fingerprint; 
        if ($fingerprint instanceof Fingerprint) {
            $contacts = Contacts::model ()->findAllByAttributes (array (
                'fingerprintId' => $fingerprint->id
            ));
            $anonContacts = AnonContact::model ()->findAllByAttributes (array (
                'fingerprintId' => $fingerprint->id
            ));

            if (sizeof ($contacts) === 0 && sizeof ($anonContacts) === 0) {
                $fingerprint->delete ();
            }
        }
    }

}
?>
