<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

class X2HtmlTest extends X2TestCase {
    public function testSanitizeAttribute() {
        $testVals = array(
            'admin\' OR 1 = 1--' => 'adminOR11--',
            '<script>alert("xss");</script>' => 'scriptalertxssscript',
        );
        foreach ($testVals as $input => $expected) {
            $this->assertEquals($expected, X2Html::sanitizeAttribute($input));
        }
    }

    public function testClearfix() {
        $expected = '<span class="clearfix"></span>';
        $this->assertEquals($expected, X2Html::clearfix());
    }

    public function testRenderPhoneLink() {
        $expected = '<a href="tel:+831-123-4567">831-123-4567</a>';
        $this->assertEquals($expected, X2Html::renderPhoneLink('831-123-4567'));

        $expected = '<a href="tel:+8311234567">831 123 4567</a>';
        $this->assertEquals($expected, X2Html::renderPhoneLink('831 123 4567'));
    }

    public function testRenderEmailLink() {
        $expected = '<a href="mailto:test@example.com">test@example.com</a>';
        $this->assertEquals($expected, X2Html::renderEmailLink('test@example.com'));
    }

    public function testRenderSkypeLink() {
        ob_start();
        X2Html::renderSkypeLink('Bill.Gates');
        $link = ob_get_clean();
        $this->assertEquals(1, preg_match('/Skype.ui/', $link, $matches));
        $this->assertEquals(1, preg_match('/participants: \["Bill.Gates"\]/', $link, $matches));
    }

    public function testLoadingIcon() {
        $expected = '<div class="x2-loading-icon load8 full-page-loader x2-loader"><div class="loader"></div></div>';
        $this->assertEquals($expected, X2Html::loadingIcon());

        $expected = '<div class="x2-loading-icon load8 full-page-loader x2-loader customClass" id="myLoader"><div class="loader"></div></div>';
        $this->assertEquals($expected, X2Html::loadingIcon(array(
            'id' => 'myLoader',
            'class' => 'customClass',
        )));
    }

    public function testHint() {
        $expected = '<span class="x2-hint x2-question-mark fa fa-question-circle" title="testing"></span>';
        $this->assertEquals($expected, X2Html::hint('testing'));

        $expected = '<span class="x2-hint x2-question-mark fa fa-question-circle" title="testing" id="myHint"></span>';
        $this->assertEquals($expected, X2Html::hint('testing', true, 'myHint'));
    }

    public function testHint2() {
        $expected = '<span class="x2-hint x2-question-mark fa fa-question-circle" title="testing"></span>';
        $this->assertEquals($expected, X2Html::hint2('testing'));

        $expected = '<span class="x2-hint x2-question-mark fa fa-question-circle myClass" title="testing"></span>';
        $this->assertEquals($expected, X2Html::hint2('testing', array('class' => 'myClass')));
    }

    public function testSettingsButton() {
        $expected = '<span class=" fa-lg fa fa-cog x2-settings-button"></span>';
        $this->assertEquals($expected, X2Html::settingsButton('', array()));

        $expected = '<span class="myClass fa-lg fa fa-cog x2-settings-button" id="mySettings"></span>';
        $this->assertEquals($expected, X2Html::settingsButton('', array('class' => 'myClass', 'id' => 'mySettings')));
    }

    public function testRenderPageTitle() {
        $expected = '<div class="page-title"><h2>Test Page</h2></div>';
        $title = 'Test Page';
        ob_start();
        X2Html::renderPageTitle($title);
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);

        $expected = '<div class="customClass page-title" id="myTitle"><h2>Test Page</h2></div>';
        $options = array(
            'class' => 'customClass',
            'id' => 'myTitle',
        );
        ob_start();
        X2Html::renderPageTitle($title, $options);
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }

    public function testFa() {
        $expected = '<i class=" fa fa-testing"> </i>';
        $this->assertEquals($expected, X2Html::fa('testing'));

        $expected = '<i class="highlight fa fa-testing"> </i>';
        $this->assertEquals($expected, X2Html::fa('fa-testing', array('class' => 'highlight')));
    }

    public function testX2Icon() {
        $expected = '<i class=" icon-testing"> </i>';
        $this->assertEquals($expected, X2Html::x2icon('testing'));

        $expected = '<i class="highlight icon-testing"> </i>';
        $this->assertEquals($expected, X2Html::x2icon('testing', array('class' => 'highlight')));
    }

    public function testIEBanner() {
        $this->assertFalse(X2Html::IEBanner(10, false));

        $expected = '<h2 class="ie-banner">This feature does not support your version of Internet Explorer</h2>';
        $_SERVER['HTTP_USER_AGENT'] = 'msie 8';
        ob_start();
        $this->assertTrue(X2Html::IEBanner(10));
        ob_get_clean();
        $this->assertEquals($expected, X2Html::IEBanner(10, false));
    }

    public function testEmailFormButton() {
        $expected = '<a class="x2-button icon right email" title="Open email form" onclick="toggleEmailForm(); return false;" href="#"></a>';
        $this->assertEquals($expected, X2Html::emailFormButton());
    }

    public function testOrderedList() {
        $expected = '<ol><li>one</li><li>two</li><li>three</li></ol>';
        $items = array('one', 'two', 'three');
        $this->assertEquals($expected, X2Html::orderedList($items));

        $expected = '<ol id="myList" class="unstyled"><li>one</li><li>two</li><li>three</li></ol>';
        $options = array('id' => 'myList', 'class' => 'unstyled');
        $this->assertEquals($expected, X2Html::orderedList($items, $options));
    }

    public function testUnorderedList() {
        $expected = '<ul><li>one</li><li>two</li><li>three</li></ul>';
        $items = array('one', 'two', 'three');
        $this->assertEquals($expected, X2Html::unorderedList($items));

        $expected = '<ul id="myList" class="unstyled"><li>one</li><li>two</li><li>three</li></ul>';
        $options = array('id' => 'myList', 'class' => 'unstyled');
        $this->assertEquals($expected, X2Html::unorderedList($items, $options));
    }

    public function testEncodeArray() {
        $testArray = array(
            '<script>',
            'docs.php?page=How-to-"Blank"',
        );
        $expected = array(
            '&lt;script&gt;',
            'docs.php?page=How-to-&quot;Blank&quot;',
        );
        $this->assertEquals($expected, X2Html::encodeArray($testArray));
    }

    public function testDefaultAvatar() {
        $expected = '<i style="font-size: px;" class="default-avatar icon-profile-large"> </i>';
        $this->assertEquals($expected, X2Html::defaultAvatar());

        $expected = '<i style="font-size: 32px;" class="default-avatar icon-profile-large"> </i>';
        $this->assertEquals($expected, X2Html::defaultAvatar(32));
    }

    public function testCsrfToken() {
        $_COOKIE['YII_CSRF_TOKEN'] = 'NOOP';
        ob_start();
        $token = Yii::app()->request->getCsrfToken();
        ob_get_clean();
        $expected = '<input type="hidden" value="'.$token.'" name="YII_CSRF_TOKEN" id="YII_CSRF_TOKEN" />';
        $this->assertEquals($expected, X2Html::csrfToken());
    }
}
?>
