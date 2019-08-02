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
 * Model class for a fingerprint associated with either a Contact or
 * an AnonContact. This stores their fingerprint, the timestamp it
 * was created, and the collected browser attributes.
 *
 * @package application.models
 */
class Fingerprint extends X2Model {

    public $supportsWorkflow = false;
    //public $supportsFieldLevelPermissions = false;
    
    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_fingerprint';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        $rules = parent::rules();
        return $rules;
    }

    public function behaviors() {
        return array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'marketing',
                'autoCompleteSource' => null
            ),
            'ERememberFiltersBehavior' => array(
                'class'=>'application.components.behaviors.ERememberFiltersBehavior',
                'defaults'=>array(),
                'defaultStickOnClear'=>false
            ),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('createDate', $this->createDate);
        $criteria->compare('addBehavior', $this->addBehavior);
        $criteria->compare('anonymous', $this->anonymous);
        $criteria->compare('canvasFingerprint', $this->canvasFingerprint);
        $criteria->compare('cookiesEnabled', $this->cookiesEnabled);
        $criteria->compare('fingerprint', $this->fingerprint);
        $criteria->compare('fonts', $this->fonts);
        $criteria->compare('indexedDB', $this->indexedDB);
        $criteria->compare('javaEnabled', $this->javaEnabled);
        $criteria->compare('language', $this->language);
        $criteria->compare('localStorage', $this->localStorage);
        $criteria->compare('plugins', $this->plugins);
        $criteria->compare('screenRes', $this->screenRes);
        $criteria->compare('sessionStorage', $this->sessionStorage);
        $criteria->compare('timezone', $this->timezone);
        $criteria->compare('userAgent', $this->userAgent);

        if (!Yii::app()->user->isGuest) {
            $pageSize = Profile::getResultsPerPage();
        } else {
            $pageSize = 20;
        }

        return new SmartActiveDataProvider(get_class($this), array(
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            'criteria' => $criteria,
        ));
    }

    private static $_attributes; 

    public function getFingerprintAttributes() {
        return array_intersect_key (
            $this->getAttributes (), array_flip (self::getFingerprintAttributeNames ()));
    }

    /**
     * @param bool $withBits if true, this will return an associative array with number of bits
     *  of entropy that can be reduced per attribute. otherwise, an array just containing the
     *  attribute names is returned.
     * @param array
     */
    public static function getFingerprintAttributeNames($withBits=false) {
        if (!isset (self::$_attributes)) {
            self::$_attributes = array(
                'plugins' => 15.4,
                'userAgent' => 10.0,
                'language' => 0,
                'screenRes' => 4.83,
                'timezone' => 3.04,
                'cookiesEnabled' => 0,
                'indexedDB' => 0,
                'addBehavior' => 0,
                'javaEnabled' => 0,
                'canvasFingerprint' => 0,
                'localStorage' => 0,
                'sessionStorage' => 0,
                'fonts' => 0,
            );
        }
        if ($withBits) {
            return self::$_attributes;
        } else {
            return array_keys (self::$_attributes);
        }
    }

    /**
     * Helper method to render a link to a Fingerprint's associated Contact
     * or AnonContact into the GridView
     */
    public function renderContactLink($data, $row) {
        $type = $data->anonymous? "AnonContact" : "Contacts";
        $path = $data->anonymous? "/marketing/anonContactView" : "/contacts";
        $contact = X2Model::model($type)->findByAttributes(array(
            'fingerprintId' => $data->id,
        ));

        // Display the link as either the Contact's name or the
        // AnonContact's email
        if ($contact) {
            $name = $data->anonymous? $contact->email : $contact->name;
            if (empty($name))
                $name = $data->fingerprint;
            else
                $name = $data->fingerprint.": ".$name;

            return CHtml::link($name, array(
                $path,
                'id' => $contact->id,
            ));
        } else {
            return $data->fingerprint;
        }
    }

    /**
     * Searches for a record with a matching fingerprint, or if one
     * is not found, attempts to locate a partial match.
     * @param hash $fingerprint A murmurhash3 of the collected attributes
     * @param array $attributes An associative array of the collected attributes
     * to be used in partial matching.
     * @return array A contact, if a match was found, an anonymous contact otherwise. Also returns
     *  the reduction in bits of entropy for the attributes matched, or null if no match was made.
     */
    public static function track($fingerprint, $attributes = array()) {

        // search with fingerprint hash
        $fingerprintRecord = X2Model::model('Fingerprint')
            ->findByAttributes(array('fingerprint'=> $fingerprint));
        if (isset($fingerprintRecord)) {
            $type = ((bool) $fingerprintRecord->anonymous) ? 'AnonContact' : 'Contacts';
            $contact = X2Model::model($type)->findByAttributes(
                array('fingerprintId'=> $fingerprintRecord->id));
            if (isset($contact))
                return array ($contact, self::getReductionInBitsOfEntropy ($attributes));
        }

        // perform a partial match using non-hashed fingerprint attributes
        list($contact, $bits) = self::partialMatch($attributes);
        if (isset($contact))
            return array ($contact, $bits);

        // No existing Contact or AnonContact has been found, so create a new AnonContact
        $anonContact = new AnonContact();
        $anonContact->setFingerprint($fingerprint, $attributes);
        $anonContact->trackingKey = Contacts::getNewTrackingKey();
        $anonContact->createDate = time();
        $anonContact->disableBehavior ('changelog');
        $anonContact->save();
        return array ($anonContact, null);
    }

    public static function getReductionInBitsOfEntropy ($attributes) {
        return array_sum (
            array_intersect_key (self::getFingerprintAttributeNames (true), $attributes));
    }

    /**
     * Return the readable timezone string based on a Fingerprint's timezone offset
     */
    public function getTimezoneString() {
        // Use whether DST was in effect when the record was last updated, otherwise
        // use the server's current DST settings
        if (isset($this->createDate) && ctype_digit($this->createDate))
            $isDST = date('I', $this->createDate);
        else
            $isDST = date('I');
        // Set timezone offset to seconds
        $offset = -($this->timezone * 60);
        $tzString = timezone_name_from_abbr('', $offset, $isDST);
        return $tzString;
    }

    /**
     * Calculates probability of match based on number of matching fingerprint attributes
     */
    public static function calculateProbability ($bits) {
        $probability = 100;

        /*
         * Set the probability based on the estimated bits of entropy that were 
         * collected from the contact.
         * The bits of entropy collected per browser attribute are estimated in the 
         * EFF fingerprinting study, see: 
         * https://www.eff.org/deeplinks/2010/01/primer-information-theory-and-privacy
         * The necessary number of bits is determined with a base 2 logarithm of the 
         * population size. At the time of writing, this is 7,176,023,000 according to 
         * census.gov.
         * log(7176023000, 2) = 32.7405
         * 33 bits will be sufficient for a population of 8 billion.
         */
        $necessaryBits = 32.7;
        if ($bits > $necessaryBits)
            $probability = 99;
        else
            $probability = sprintf("%.1f",((1 / pow(2, $necessaryBits - $bits)) * 100));

        return $probability;
    }

    /**
     * Search for a fingerprint that matches with at least the minimum number of
     * required attributes, as set by the admin.
     *
     * @param array $attributes the attributes to attempt to match.
     * @return type $contact The matching Contact or AnonContact, or null if
     * none are found.
     */
    public static function partialMatch($attributes = array()) {
        $checkAttrs = self::getFingerprintAttributeNames ();

        // Set the minimum number of attributes to constitute a partial match.
        $threshold = Yii::app()->settings->identityThreshold;

        // don't try to perform a partial match if threshold would require a perfect match
        if ($threshold >= sizeof ($checkAttrs)) return array (null, null);

        $params = array();
        $attributeAliases = array();
        $disambiguatingPrefix = '';
        $attributeBooleans = array ();
        foreach ($checkAttrs as $attr) {
            // Build the if statements to be used in the SELECT, and create
            // an array of parameters to bind.
            if (isset($attributes[$attr])) {
                // Any attributes that were arrays are stored in JSON
                if (is_array($attributes[$attr])) {
                    $attributes[$attr] = json_encode($attributes[$attr]);
                }
                $params[':'.$attr] = $attributes[$attr];
                $attributeBooleans[] = 
                    "if(x2_fingerprint.".$attr."=:".$attr.", 1, 0) as $disambiguatingPrefix$attr";
                $attributeAliases[] = $disambiguatingPrefix.$attr;
            }
        }

        if (empty($attributeAliases))
            return array (null, null); // there aren't any fingerprint attributes to match on

        // get partial matches
        $matched = Yii::app()->db->createCommand('
            /* outer query calculates sum of number of matched attributes and orders the 
               results */
            SELECT fingerprintId, anonymous, lastUpdated, '.
                implode (', ',$attributeAliases).', ('.implode (' + ', $attributeAliases).') as sum
            FROM (
                /* inner queries group by number of matched attributes */

                /* first inner query looks for anon contact partial matches */
                (SELECT x2_fingerprint.id as fingerprintId, anonymous, 
                    x2_anon_contact.lastUpdated as lastUpdated, 
                    '.implode (', ', $attributeBooleans).'
                FROM x2_fingerprint
                JOIN x2_anon_contact ON x2_anon_contact.fingerprintId=x2_fingerprint.id
                GROUP BY x2_fingerprint.id
                HAVING ('.implode (' + ', $attributeAliases).') >= '.$threshold.')

                UNION

                /* second inner query looks for contact partial matches */
                (SELECT x2_fingerprint.id as fingerprintId, anonymous, 
                    x2_contacts.lastUpdated as lastUpdated,
                    '.implode (', ', $attributeBooleans).'
                FROM x2_fingerprint
                JOIN x2_contacts ON x2_contacts.fingerprintId=x2_fingerprint.id
                GROUP BY x2_fingerprint.id
                HAVING ('.implode (' + ', $attributeAliases).') >= '.$threshold.')
            ) as t
            /* Order by best matches first, then by contacts first, then by last updated first */
            ORDER BY sum DESC, anonymous ASC, lastUpdated DESC
        ')->bindValues ($params)
          ->queryAll ();

        $contact = null;
        if (sizeof ($matched) > 0) { 

            // we've already ordered by sum, lastUpdated. If more that one fingerprint has the same
            // sum and lastUpdated, just pick one.
            $matched = $matched[0];

            $type = ($matched['anonymous'])? 'AnonContact' : 'Contacts';
            $contact = X2Model::model($type)
                ->findByAttributes(array('fingerprintId'=> $matched['fingerprintId']));
        }

        $reductionInBitsOfEntropy = self::getReductionInBitsOfEntropy (
            // get all attributes which matched
            array_filter ($matched, function ($a) {
                return $a === '1';
            }));

        return array ($contact, $reductionInBitsOfEntropy);
    }

    public function getDisplayName ($plural=true) {
        return Yii::t('app', 'Fingerprint'.($plural ? 's' : ''));
    }
}
