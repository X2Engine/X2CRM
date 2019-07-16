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
 * Tests behavior of SmartDataProviderBehavior and ERememberFiltersBehavior 
 */

class PersistentGridSettingsTest extends X2DbTestCase {

    /**
     * Ensure that DB persistent sort settings get set properly for all children of X2Model
     */
    public function testX2ModelSort () {
        Yii::app()->params->profile->generalGridViewSettings = '';
        Yii::app()->params->profile->save ();
        $models = X2Model::getModelNames (); 
        unset ($models['EmailInboxes']);
        foreach ($models as $class => $title) {
            X2_TEST_DEBUG_LEVEL > 1 && println ('testing sort for '.$class);
            $uid = rand ();
            $_GET["{$uid}_sort"] = 'test';
            $_GET[$class] = array (
                'id' => 0,
            );
            $searchModel = new $class ('search', $uid, true);
            $dataProvider = $searchModel->search ();
            $this->assertNotNull (
                $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort'));
            $this->assertNotNull (
                $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters'));
        }
    }

    /**
     * Ensure that DB persistent sort settings get set properly
     */
    public function testContactSort () {
        Yii::app()->params->profile->generalGridViewSettings = '';
        Yii::app()->params->profile->save ();
        $_GET['Contacts'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
        );
        $uid = 'testUID';
        $_GET["{$uid}_sort"] = 'firstName';
        $contact = new Contacts ('search', $uid, true);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($contact->getAttributes ());
        $dataProvider = $contact->search ();
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort'));
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters'));
    }

    /**
     * Ensure that DB persistent sort settings get set properly
     */
    public function testProfileSort () {
        Yii::app()->params->profile->generalGridViewSettings = '';
        Yii::app()->params->profile->save ();
        $_GET['Profile'] = array (
            'username' => 'test',
        );
        $uid = 'testUID';
        $_GET["{$uid}_sort"] = 'username';
        $profile = new Profile ('search', $uid, true);
        $dataProvider = $profile->search ();
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort'));
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters'));
    }

    /**
     * @group failing
     * Ensure that sort order and filters in GET params get saved to session correctly 
     */
    public function testSessionSettings () {
        $_SESSION = array ();

        $_GET['Contacts'] = array (
            'tags' => array(),
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'test@test.com',
        );
        $_GET["Contacts_sort"] = 'firstName';
        $contact = new Contacts ('search');
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($contact->getAttributes ());
        $dataProvider = $contact->search ();
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($_SESSION);

        $sort = $contact->asa ('ERememberFiltersBehavior')->getSetting ('sort');
        $filters = $contact->asa ('ERememberFiltersBehavior')->getSetting ('filters');
        $this->assertEquals ($filters, $_GET['Contacts']);
        $this->assertEquals ($sort, $_GET['Contacts_sort']);

    }


    /**
     * For each child of X2Model, ensure that filters and sort order get saved in session correctly
     * and that analogous methods in ERememberFiltersBehavior and SmartDataProviderBehavior behave
     * the same
     */
    public function testX2ModelSessionSettings () {
        $models = X2Model::getModelNames (); 
        unset ($models['EmailInboxes']);
        foreach ($models as $class => $title) {
            $_SESSION = array ();
            X2_TEST_DEBUG_LEVEL > 1 && println ('testing sort for '.$class);
            $uid = rand ();
            $_GET["{$class}_sort"] = 'test';
            $_GET[$class] = array (
                'tags' => array(),
                'id' => 0,
            );
            $searchModel = new $class ('search');
            $dataProvider = $searchModel->search ();
            $sort = $searchModel->asa ('ERememberFiltersBehavior')->getSetting ('sort');
            $filters = $searchModel->asa ('ERememberFiltersBehavior')->getSetting ('filters');
            $sort2 = $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort');
            $filters2 = $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters');
            $this->assertEquals ($filters, $_GET[$class]);
            $this->assertEquals ($filters, $filters2);
            $this->assertEquals ($sort, $_GET[$class.'_sort']);
            $this->assertEquals ($sort, $sort2);
        }
    }

// tests functions that don't get called in the app
    /**
     * Set filters, then try unsetting filters not in a specified list of attributes
     */
//    public function testUnsetFiltersNotIn () {
//        $_SESSION = array ();
//
//        $_GET['Contacts'] = array (
//            'firstName' => 'test',
//            'lastName' => 'test',
//            'email' => 'test@test.com',
//        );
//        $_GET["Contacts_sort"] = 'firstName';
//        $contact = new Contacts ('search');
//        X2_TEST_DEBUG_LEVEL > 1 && print_r ($contact->getAttributes ());
//        $dataProvider = $contact->search ();
//
//        $contact->asa ('ERememberFiltersBehavior')
//            ->unsetFiltersNotIn (array ('firstName', 'lastName'));
//        $filters = $contact->asa ('ERememberFiltersBehavior')->getSetting ('filters');
//        unset ($_GET['Contacts']['email']);
//        X2_TEST_DEBUG_LEVEL > 1 && print_r ($filters);
//        $this->assertEquals ($filters, $_GET['Contacts']);
//    }
//
//    /**
//     * Set sort order, then try unsetting it
//     */
//    public function testUnsetSortOrderIfNotIn () {
//        $_SESSION = array ();
//
//        $_GET['Contacts'] = array (
//            'firstName' => 'test',
//            'lastName' => 'test',
//            'email' => 'test@test.com',
//        );
//        $_GET["Contacts_sort"] = 'firstName.desc';
//        $contact = new Contacts ('search');
//        $dataProvider = $contact->search ();
//        $dataProvider->asa ('SmartDataProviderBehavior')
//            ->unsetSortOrderIfNotIn (array ('lastName', 'email', 'firstName'));
//        $sort = $contact->asa ('ERememberFiltersBehavior')->getSetting ('sort');
//        $this->assertEquals ($sort, $_GET['Contacts_sort']);
//        $dataProvider->asa ('SmartDataProviderBehavior')
//            ->unsetSortOrderIfNotIn (array ('lastName', 'email'));
//        $sort = $contact->asa ('ERememberFiltersBehavior')->getSetting ('sort');
//        $this->assertEquals ('', $sort);
//    }
}
?>
