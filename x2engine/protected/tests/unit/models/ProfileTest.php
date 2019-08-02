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
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ProfileTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array('profile'=>'Profile');
    }

    public function testGetLayout() {
        $profile = $this->profile('testProfile');
        $profile->setAttribute('layout',null);
        $initialLayout = $profile->getLayout();
        // Try (just running) with empty layouts
        $profile->setAttribute('layout',json_encode(array('left'=>array(),'center'=>array(),'right'=>array(),'hidden'=>array(),'hiddenRight'=>array())));
        $emptyLayout = $profile->getLayout();
        // Test that non-existing layout widgets are removed:
        $profile->setAttribute('layout',json_encode(array('left'=>array('FooWidget'=>array('nothing to see here')),'center'=>array(),'right'=>array(),'hidden'=>array(),'hiddenRight'=>array())));
        $this->assertEquals($emptyLayout,$profile->getLayout());
    }

    public function testAddRemoveLayoutElements () {
        $profile = $this->profile('testProfile');
        $fn = TestingAuxLib::setPublic (
            $profile, 'addRemoveLayoutElements', false, function ($method, $class) {

                return function () use ($method, $class) {
                    $args = func_get_args ();
                    $args = array (
                        $args[0],
                        &$args[1],
                        $args[2],
                    );
                    return $method->invokeArgs ($class, $args);
                };
            });

        $defaultLayout = $profile->initLayout ();

        // attempt to construct default layout from empty layout
        $profile->layout = json_encode (array ());
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $this->assertEquals ($defaultLayout, json_decode ($profile->layout, true));

        // ensure that invalid hidden right widgets get removed
        $profile->layout = json_encode (array (
            'hiddenRight' => array (
                'InvalidRightWidget' => array(
                    'title' => 'Invalid Right Widget',
                    'minimize' => false,
                ),
            )
        ));
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $this->assertEquals ($defaultLayout, json_decode ($profile->layout, true));

        // ensure that invalid left widgets get removed
        $profile->layout = json_encode (array (
            'left' => array (
                'Invalid' => array(
                    'title' => 'Invalid',
                    'minimize' => false,
                ),
            )
        ));
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $this->assertEquals ($defaultLayout, json_decode ($profile->layout, true));

        // ensure that invalid right widgets get removed
        $profile->layout = json_encode (array (
            'right' => array (
                'Invalid' => array(
                    'title' => 'Invalid',
                    'minimize' => false,
                ),
            )
        ));
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $this->assertEquals ($defaultLayout, json_decode ($profile->layout, true));

        // ensure that right widgets get retitled while preserving other settings
        $helpfulTipsConfig = $defaultLayout['right']['TimeZone'];
        $this->assertFalse ($helpfulTipsConfig['minimize']); // make sure we're changing state
        $newHelpfulTipsConfig = array(
            'title' => 'Not Clock',
            'minimize' => true,
        );
        $profile->layout = json_encode (array (
            'right' => array (
                'TimeZone' => $newHelpfulTipsConfig,
            )
        ));
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $expected = $defaultLayout;
        $newHelpfulTipsConfig['title'] = $helpfulTipsConfig['title'];
        $expected['right']['TimeZone'] = $newHelpfulTipsConfig;
        $this->assertEquals ($expected, json_decode ($profile->layout, true));

        // ensure that hidden right widgets remain hidden
        $helpfulTipsConfig = $defaultLayout['right']['TimeZone'];
        $profile->layout = json_encode (array (
            'hiddenRight' => array (
                'TimeZone' => $helpfulTipsConfig,
            )
        ));
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $expected = $defaultLayout;
        unset ($expected['right']['TimeZone']);
        $expected['hiddenRight']['TimeZone'] = $helpfulTipsConfig;
        $this->assertEquals ($expected, json_decode ($profile->layout, true));

        // ensure that hidden right widgets get retitled while preserving other settings
        $helpfulTipsConfig = $defaultLayout['right']['TimeZone'];
        $this->assertFalse ($helpfulTipsConfig['minimize']); // make sure we're changing state
        $newHelpfulTipsConfig = array(
            'title' => 'Not Clock',
            'minimize' => true,
        );
        $profile->layout = json_encode (array (
            'hiddenRight' => array (
                'TimeZone' => $newHelpfulTipsConfig,
            )
        ));
        $profile->update ('layout');
        $layout = json_decode ($profile->layout, true);
        $this->assertNotEquals (json_decode ($profile->layout), $defaultLayout);
        $fn ('left', $layout, $defaultLayout);
        $profile->refresh ();
        $layout = json_decode ($profile->layout, true);
        $fn ('right', $layout, $defaultLayout);
        $profile->refresh ();
        $expected = $defaultLayout;
        $newHelpfulTipsConfig['title'] = $helpfulTipsConfig['title'];
        unset ($expected['right']['TimeZone']);
        $expected['hiddenRight']['TimeZone'] = $newHelpfulTipsConfig;
        $this->assertEquals ($expected, json_decode ($profile->layout, true));
    }
}

?>
