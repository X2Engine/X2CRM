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






class FingerprintTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.FingerprintTest'),
        'anonContacts' => array ('AnonContact', '.FingerprintTest'),
        'fingerprints' => array ('Fingerprint', '.FingerprintTest'),
    );

    /**
     * Returns number of matching, non-null fingerprint attributes 
     * @param Fingerprint $fingerprint1
     * @param Fingerprint $fingerprint2
     * @return int count
     */
    public function calculateMatchCount (Fingerprint $fingerprint1, Fingerprint $fingerprint2) {
        return count ($this->getMatchedAttributes ($fingerprint1, $fingerprint2));
    }

    public function testRelations() {
        $anonContact = $this->anonContacts ('anonContact2');
        $this->assertTrue ($anonContact->fingerprint instanceof Fingerprint);
    }

    /**
     * @return array Array of matched attributes with reductions in bits of entropy 
     */
    public function getMatchedAttributes (Fingerprint $fingerprint1, Fingerprint $fingerprint2) {
        return array_intersect (
            array_filter ($fingerprint1->getFingerprintAttributes (), function ($a) {
                return ($a !== null);
            }), 
            array_filter ($fingerprint2->getFingerprintAttributes (), function ($a) {
                return ($a !== null);
            })); 
    }

    public function testPartialMatch () {
        $fingerprint1 = $this->fingerprints('fingerprint1');
        $fingerprint2 = $this->fingerprints('fingerprint2');
        $fingerprint3 = $this->fingerprints('fingerprint3');
        $fingerprint4 = $this->fingerprints('fingerprint4');
        $fingerprint5 = $this->fingerprints('fingerprint5');

        $matched = $this->calculateMatchCount ($fingerprint1, $fingerprint2);
        Yii::app()->settings->identityThreshold = $matched;
        list ($contact, $bits) = Fingerprint::partialMatch ($fingerprint2->getAttributes ());
        $this->assertTrue (
            $bits === Fingerprint::getReductionInBitsOfEntropy (
                $this->getMatchedAttributes ($fingerprint1, $fingerprint2)));

        // should have found the contact by matching all but 1 attribute
        $this->assertTrue ($contact !== null);

        // the contact should be contact1 since for contact2 (which has fingerprint2), all 
        // attributes match
        $this->assertTrue ($contact->id == $this->contacts ('contact2')->id);



        $matched = $this->calculateMatchCount ($fingerprint1, $fingerprint3);
        Yii::app()->settings->identityThreshold = $matched + 1;
        list ($contact, $bits) = 
            Fingerprint::partialMatch ($fingerprint3->getAttributes ());
        $this->assertTrue (
            $bits !== Fingerprint::getReductionInBitsOfEntropy (
                $this->getMatchedAttributes ($fingerprint1, $fingerprint3)));

        // should have failed partial match by 1 attribute
        $this->assertTrue ($contact === null);



        $matched = $this->calculateMatchCount ($fingerprint4, $fingerprint5);
        Yii::app()->settings->identityThreshold = $matched;

        list ($contact, $bits) = 
            Fingerprint::partialMatch ($fingerprint4->getAttributes ());
        $this->assertTrue (
            $bits === Fingerprint::getReductionInBitsOfEntropy (
                $this->getMatchedAttributes ($fingerprint4, $fingerprint5)));

        // should return contact4 instead of contact3, even though they have fingerprints with 
        // identical attributes since contact4 was updated more recently
        $this->assertTrue ($contact->id === $this->contacts ('contact4')->id);


        Yii::app()->settings->identityThreshold = sizeof (
            Fingerprint::getFingerprintAttributeNames ());
        list ($contact, $bits) = 
            Fingerprint::partialMatch ($fingerprint4->getAttributes ());

        // contact should be null since partial match should not be performed if a perfect match
        // is required (i.e. when identity threshold is same as max number of attributes
        $this->assertEquals ($contact, null);

    }

    public function testPartialMatchTieBreaking () {
        $fingerprint7 = $this->fingerprints('fingerprint7');
        $fingerprint8 = $this->fingerprints('fingerprint8');

        $matched = $this->calculateMatchCount ($fingerprint7, $fingerprint8);

        // fingerprints should be identical, except that 7 is anonymous. Therefore, number of 
        // matched attributes should be one less than number of possible attributes since plugins 
        // attribute in either fingerprint is null.
        $this->assertEquals (
            sizeof (Fingerprint::getFingerprintAttributeNames ()) - 1, $matched);

        Yii::app()->settings->identityThreshold = $matched;

        list ($contact, $bits) = 
            Fingerprint::partialMatch ($fingerprint7->getAttributes ());
        $this->assertEquals (
            Fingerprint::getReductionInBitsOfEntropy (
                $this->getMatchedAttributes ($fingerprint7, $fingerprint8)), $bits);

        // should return contact5 instead of anonContact3, even though they have fingerprints with 
        // identical attributes since contacts are chosen before anon contacts
        $this->assertTrue ($contact->id === $this->contacts ('contact5')->id);
    }

    public function testCalculateProbability() {
        // Array of bits => probability
        $testValues = array(
            40 => 99,
            33 => 99,
            31 => 30.8,
            30 => 15.4,
            25 => 0.5,
            10 => 0,
        );
        foreach ($testValues as $bits => $probability) {
            $this->assertEquals($probability, Fingerprint::calculateProbability($bits));
        }
    }

    public function testGetReductionInBitsOfEntropy() {
        $noBits = array(
            'language' => 0,
            'cookiesEnabled' => 0,
            'indexedDB' => 0,
            'addBehavior' => 0,
            'javaEnabled' => 0,
            'canvasFingerprint' => 0,
            'localStorage' => 0,
            'sessionStorage' => 0,
            'fonts' => 0,
        );
        $pluginsAndUA = array('plugins'=>15.4, 'userAgent'=>10.0);
        $cookiesUALangAndTZ = array(
            'cookiesEnabled' => 0,
            'userAgent' => 10.0,
            'language' => 0,
            'timezone' => 3.04
        );
        $pluginsOnly = array('plugins'=>15.4);
        $this->assertEquals(0, Fingerprint::getReductionInBitsOfEntropy($noBits));
        $this->assertEquals(25.4, Fingerprint::getReductionInBitsOfEntropy($pluginsAndUA));
        $this->assertEquals(13.04, Fingerprint::getReductionInBitsOfEntropy($cookiesUALangAndTZ));
        $this->assertEquals(15.4, Fingerprint::getReductionInBitsOfEntropy($pluginsOnly));
    }

    public function testTrack() {
        $fingerprint1 = $this->fingerprints('fingerprint1');
        $fingerprint2 = $this->fingerprints('fingerprint2');
        $fingerprint3 = $this->fingerprints('fingerprint3');
        $fingerprint4 = $this->fingerprints('fingerprint4');
        $fingerprint5 = $this->fingerprints('fingerprint5');
        $fingerprint6 = $this->fingerprints('fingerprint6');

        // Ensure track() can locate a Contact by fingerprint
        list($contact, $bits) = Fingerprint::track(
            $fingerprint1->fingerprint, $fingerprint1->getAttributes());
        $this->assertNotNull($contact);
        $this->assertNotNull($bits);
        $this->assertTrue($contact instanceof Contacts);
        $this->assertEquals($this->contacts('contact1')->id, $contact->id);

        // Ensure track() can locate an AnonContact by fingerprint
        list($contact, $bits) = Fingerprint::track(
            $fingerprint6->fingerprint, $fingerprint6->getAttributes());
        $this->assertNotNull($contact);
        $this->assertNotNull($bits);
        $this->assertTrue($contact instanceof AnonContact);
        $this->assertEquals($this->anonContacts('anonContact2')->id, $contact->id);
        
        // Test whether a new AnonContact is returned when a the fingerprint is not yet in use
        list($contact, $bits) = Fingerprint::track(12345);
        $this->assertNotNull($contact);
        $this->assertNull($bits);
        $this->assertTrue($contact instanceof AnonContact);
    }

}

?>
