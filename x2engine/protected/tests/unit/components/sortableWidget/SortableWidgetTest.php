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




Yii::import ('application.components.*');
Yii::import ('application.components.X2Settings.*');
Yii::import ('application.components.sortableWidget.*');

/**
 * @package application.tests.unit.components.sortableWidget
 */
class SortableWidgetTest extends X2DbTestCase {

    public $fixtures = array (
        'profiles' => 'Profile',
    );

    /**
     * Helper method to create a new profile widget 
     */
    private function createProfileWidget ($profile, $widgetSubtype) {
        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetSubtype, 'profile');
        // ensure widget was created successfully
        $this->assertTrue ($success);
        return $uid;
    }

    /**
     * Attempt to create a new widget 
     */
    public function testCreateSortableWidget () {
        $profile = $this->profiles ('adminProfile');
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        // clone the contacts grid widget
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $uid = $this->createProfileWidget ($profile, $widgetSubtype);

        $widgetLayoutAfter = $profile->profileWidgetLayout;
        $createdWidgetAttr = array_diff_key ($widgetLayoutAfter, $widgetLayoutBefore);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($createdWidgetAttr);
        // ensure that widget settings were saved correctly
        $keys = array_keys ($createdWidgetAttr);
        $this->assertEquals ($widgetSubtype.'_'.$uid, array_pop ($keys));
        $this->assertEquals (
            $createdWidgetAttr[$widgetSubtype.'_'.$uid],
            $widgetSubtype::getJSONPropertiesStructure ());
    }

    /**
     * Attempt to create a widget of an invalid subtype 
     */
    public function testCreateSortableWidgetError () {
        $profile = $this->profiles ('adminProfile');
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $widgetSubtype = 'NotAWidgetClass';
        $this->assertFalse (SortableWidget::subtypeisValid ('profile', $widgetSubtype));
        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetSubtype, 'profile');
        // ensure widget wasn't created
        $this->assertFalse ($success);
    }

    /**
     * Clone a default widget then delete it 
     */
    public function testDeleteSortableWidget () {
        $profile = $this->profiles ('adminProfile');
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $uid = $this->createProfileWidget ($profile, $widgetSubtype);

        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $success = SortableWidget::deleteSortableWidget ($profile, $widgetSubtype, $uid, 'profile');
        $this->assertTrue ($success);
        $widgetLayoutAfter = $profile->profileWidgetLayout;
        unset ($widgetLayoutBefore[$widgetSubtype.'_'.$uid]);
        $this->assertEquals ($widgetLayoutBefore, $widgetLayoutAfter);
    }

    /**
     * Test soft deletion of default widgets
     */
    public function testSoftDeletion () {
        $profile = $this->profiles ('adminProfile');
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $widgetSettings = $widgetLayoutBefore[$widgetSubtype];
        $this->assertFalse ($widgetSettings['softDeleted']);
        $success = SortableWidget::deleteSortableWidget ($profile, $widgetSubtype, '', 'profile');
        $this->assertTrue ($success);
        $widgetLayoutAfter = $profile->profileWidgetLayout;
        // old and new layout should contain same entries (with different settings) since deletion
        // of a default widget is merely a soft deletion
        $this->assertEquals (
            array_keys ($widgetLayoutBefore), array_keys ($widgetLayoutAfter));
        $widgetSettings = $widgetLayoutAfter[$widgetSubtype];
        $this->assertTrue ($widgetSettings['softDeleted']);
    }

    /**
     * Delete a default widget and ensure that it can be recreated
     */
    public function testDefaultWidgetRecreation () {
        $profile = $this->profiles ('adminProfile');
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $success = SortableWidget::deleteSortableWidget ($profile, $widgetSubtype, '', 'profile');
        $this->assertTrue ($success);
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $widgetSettings = $widgetLayoutBefore[$widgetSubtype];
        $this->assertTrue ($widgetSettings['softDeleted']);
        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetSubtype, 'profile');
        $this->assertTrue ($success);
        $widgetLayoutAfter = $profile->profileWidgetLayout;
        $widgetSettings = $widgetLayoutAfter[$widgetSubtype];
        $this->assertFalse ($widgetSettings['softDeleted']);
        $this->assertEquals (
            array_keys ($widgetLayoutBefore), array_keys ($widgetLayoutAfter));
    }

    public function testPropertyGetters () {
        TestingAuxLib::loadControllerMock ();
        $profile = $this->profiles ('adminProfile');
        $widgetLayout = $profile->profileWidgetLayout;
        $widgetClass = 'ContactsGridViewProfileWidget';
        $widgetProps = $widgetLayout[$widgetClass];
        $this->assertEquals (
            $widgetProps, $widgetClass::getJSONProperties ($profile, 'profile', ''));
        $this->assertEquals (
            $widgetProps['label'], 
            $widgetClass::getJSONProperty ($profile, 'label', 'profile', ''));

        $this->obStart ();
        $instance = self::createWidget ($widgetClass.'_', $profile);
        $this->obEndClean ();

        $this->assertEquals ($widgetProps, $instance->getWidgetProperties ());
        $this->assertEquals ($widgetProps['label'], $instance->getWidgetProperty ('label'));
        TestingAuxLib::restoreController();
    }

    public function testPropertySetters () {
        TestingAuxLib::loadControllerMock ();
        $profile = $this->profiles ('adminProfile');
        $widgetLayout = $profile->profileWidgetLayout;
        $widgetClass = 'ContactsGridViewProfileWidget';
        $widgetProps = $widgetLayout[$widgetClass];
        $newWidgetProps = $widgetProps;
        $newWidgetProps['label'] = 'newLabel';
        $widgetClass::setJSONProperties ($profile, $newWidgetProps, 'profile', '');
        $profile->refresh ();
        $widgetLayout = $profile->profileWidgetLayout;
        $widgetProps = $widgetLayout[$widgetClass];
        $this->assertEquals ($newWidgetProps, $widgetProps);

        $widgetClass::setJSONProperty ($profile, 'label', 'newLabel2', 'profile', '');
        $newWidgetProps['label'] = 'newLabel2';
        $profile->refresh ();
        $widgetLayout = $profile->profileWidgetLayout;
        $widgetProps = $widgetLayout[$widgetClass];
        $this->assertEquals ($newWidgetProps, $widgetProps);

        $this->obStart ();
        $instance = self::createWidget ($widgetClass.'_', $profile);
        $this->obEndClean ();

        $instance->setWidgetProperty ('label', 'newLabel3');
        $newWidgetProps['label'] = 'newLabel3';
        $profile->refresh ();
        $widgetLayout = $profile->profileWidgetLayout;
        $widgetProps = $widgetLayout[$widgetClass];
        $this->assertEquals ($newWidgetProps, $widgetProps);

        $instance->setWidgetProperties (array ('label' => 'newLabel4'));
        $newWidgetProps['label'] = 'newLabel4';
        $profile->refresh ();
        $widgetLayout = $profile->profileWidgetLayout;
        $widgetProps = $widgetLayout[$widgetClass];
        $this->assertEquals ($newWidgetProps, $widgetProps);
        TestingAuxLib::restoreController();
    }

    protected static function createWidget (
        $layoutKey, $profile, $widgetType='profile', $options=array ()) {

        list($widgetClass, $widgetUID) = SortableWidget::parseWidgetLayoutKey ($layoutKey);
        return Yii::app()->controller->createWidget(
            'application.components.sortableWidget.'.$widgetClass, array_merge(
                array(
                    'widgetUID' => $widgetUID,
                    'profile' => $profile,
                    'widgetType' => $widgetType,
                )
        , $options));

    }

}

?>
