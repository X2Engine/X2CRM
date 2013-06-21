<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

// Import the main models to be used:
Yii::import('application.models.Profile');
Yii::import('application.models.*');


/**
 * Test for JSONFieldsBehavior.
 *
 * It is not a database test because only the transformation of data (as
 * performed by packAttribute and unpackAttribute) need be tested. Testing that
 * involves database interaction is already covered by {@link TransformedFieldStorageBehaviorTest}.
 *
 * @package X2CRM.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>, Derek Mueller <derek@x2engine.com>
 */
class JSONFieldsBehaviorTest extends X2DbTestCase {

	public $fixtures = array(
		'profile' => 'Profile'
	);

    /*static private function instantiateJSONFieldsBehavior ($transformAttributes) {
        $behavior = new JSONFieldsBehavior ();
        $behavior->transformAttributes = $transformAttributes;
        return $behavior;
    }

    
    static private function instantiateProfileModel () {
        $profile = new Profile ();
        return $profile;
    }*/

	public function testPackUnpackAttribute() {
        /*$transformAttributes = array('theme' => array (
            'backgroundColor', 'menuBgColor', 'menuTextColor', 'pageHeaderBgColor', 
            'pageHeaderTextColor', 'activityFeedWidgetBgColor', 
            'activityFeedWidgetTextColor', 'backgroundImg', 'backgroundTiling', 
            'pageOpacity', 'themeName', 'private', 'owner'));*/
        //$model = self::instantiateProfileModel ();


        $model = $this->profile ('testProfile');
        $model->theme = array (
            'backgroundColor'=>0, 'menuBgColor'=>0, 'menuTextColor'=>0,
            'pageHeaderBgColor'=>0, 'pageHeaderTextColor'=>0, 
            'activityFeedWidgetBgColor'=>0, 'activityFeedWidgetTextColor'=>0, 
            'backgroundImg'=>0, 'backgroundTiling'=>0, 'pageOpacity'=>0, 
            'themeName'=>0, 'private'=>0);
        $model->save ();
		
		// Verify that "owner" field got added:
		$this->assertArrayHasKey('owner', $model->theme);
	}
}

?>
