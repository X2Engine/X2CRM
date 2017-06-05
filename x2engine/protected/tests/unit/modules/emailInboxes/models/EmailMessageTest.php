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

Yii::import ('application.modules.emailInboxes.*');
Yii::import ('application.modules.emailInboxes.controllers.*');
Yii::import ('application.modules.emailInboxes.modules.*');

class EmailMessageTest extends X2DbTestCase {

    public $fixtures = array (
        'inboxes' => 'EmailInboxes',
        'users' => 'User',
    );

    private function getTestEmail(array $attrs = array()) {
        $defaultAttrs = array(
            'uid' => 1,
            'msgno' => 1,
            'subject' => 'Test Message',
            'from' => 'sender@example.com',
            'to' => 'receiver@example.com',
            'cc' => 'carboncopied@example.com',
            'reply_to' => 'replyto@example.com',
            'body' => 'This is a test',
            /*'date' => ,
            'size' => ,
            'attachments' => ,
            'seen' => ,
            'flagged' => ,
            'answered' => ,*/
        );
        $message = new EmailMessage(null, array_merge($defaultAttrs, $attrs));
        return $message;
    }

    public function testAttributeNames() {
        $message = $this->getTestEmail();
        $attrs = $message->attributeNames();
        foreach ($attrs as $attr) {
            $this->assertTrue(property_exists($message, $attr) || $message->hasProperty($attr), $attr);
        }
    }

    public function testGetPurifier() {
        $purifier = EmailMessage::getPurifier();
        $this->assertTrue($purifier instanceof CHtmlPurifier);
        $options = $purifier->getOptions();
        $this->assertTrue(is_array($options) && !empty($options));
        $this->assertTrue(array_key_exists('HTML.ForbiddenElements', $options));
        $this->assertTrue(in_array('script', $options['HTML.ForbiddenElements']));
    }

    public function testPurifyAttributes() {
        $attrs = array(
            'from' => 'somehacker@example.com',
            'to' => 'unsuspectingUser@example.com',
            'subject' => 'LOL<script>document.write("fail");</script>',
            'body' => 'Check out this cat picture!<script>alert("xss");</script>',
        );
        $expected = array(
            'from' => 'somehacker@example.com',
            'to' => 'unsuspectingUser@example.com',
            'subject' => 'LOL',
            'body' => 'Check out this cat picture!',
        );
        $message = $this->getTestEmail($attrs);
        $message->purifyAttributes();
        $test = array(
            'from' => $message->from,
            'to' => $message->to,
            'subject' => $message->subject,
            'body' => $message->body,
        );
        $this->assertEquals($expected, $test);
    }

    public function testGetUid() {
        $message = $this->getTestEmail();
        $this->assertEquals(1, $message->uid);
        $this->assertEquals(1, $message->getUid());
    }

    public function testRenderSubject() {
        $message = $this->getTestEmail(array(
            'subject' => 'Test<script>alert("hacked");</script>'
        ));
        $subject = $message->renderSubject();
        $expected = 'Test&lt;script&gt;alert(&quot;hacked&quot;);&lt;/script&gt;';
        $this->assertEquals($expected, $subject);

        $subject = $message->renderSubject(true);
        $expected = '<span title="Test&amp;lt;script&amp;gt;alert(&amp;quot;hacked&amp;quot;);&amp;lt;/script&amp;gt;">Test&lt;script&gt;alert(&quot;hacked&quot;);&lt;/script&gt;</span>';
        $this->assertEquals($expected, $subject);

        $message->subject = 'RE: that thing';
        $this->assertEquals('Re: that thing', $message->renderSubject());

        $message->subject = 'RE: re: that thing';
        $this->assertEquals('Re: that thing', $message->renderSubject());
    }

    public function testRenderDate() {
        // Note: this test may fail when executed in isolation due to timezone
        // mismatches when all fixture data is loaded and updated. Currently
        // tests values expected when full unit test suite is executed.
        $message = $this->getTestEmail(array(
            'date' => '1496440164'
        ));
        $testValues = array(
            'dynamic' => '<span title="June 2, 2017, 2:49:24 PM">Jun 2</span>',
            'full' => 'June 2, 2017',
            'hours' => '2:49 PM',
            'missing' => null,
        );
        foreach ($testValues as $format => $expected) {
            $this->assertEquals($expected, $message->renderDate($format));
        }
    }

    public function testNonContactEntityTag() {
        $message = $this->getTestEmail();
        $expected = "<span class='non-contact-entity-tag' 
              data-email='someguy@example.com'>Some Guy</span>";
        $this->assertEquals($expected, $message->nonContactEntityTag('Some Guy', 'someguy@example.com', true));
    }

    public function testRenderAddress() {
        $message = $this->getTestEmail();
        $expected = "<span class='non-contact-entity-tag' 
              data-email='someguy@example.com'>Some Guy</span> &lt;someguy@example.com&gt;";
        $this->assertEquals($expected, $message->renderAddress(array('Some Guy', 'someguy@example.com'), true, true));

        $expected = "<span class='non-contact-entity-tag' 
              data-email='someguy@example.com'>Some Guy</span>";
        $this->assertEquals($expected, $message->renderAddress(array('Some Guy', 'someguy@example.com'), false, true));

        $contact = new Contacts;
        $contact->firstName = 'Some';
        $contact->lastName = 'Guy';
        $contact->email = 'someguy@example.com';
        $contact->visibility = 1;
        $this->assertTrue($contact->save());

        $rendered = $message->renderAddress(array('Some Guy', 'someguy@example.com'), false, true);
        $this->assertEquals(0, strpos($rendered, '<a class="contact-name" href="'));
        $this->assertGreaterThan(0, strpos($rendered, '<span>Some Guy</span></a>'));
        $this->assertFalse(strpos($rendered, 'someguy@example.com'));

        $rendered = $message->renderAddress(array('Some Guy', 'someguy@example.com'), true, true);
        $this->assertEquals(0, strpos($rendered, '<a class="contact-name" href="'));
        $this->assertGreaterThan(0, strpos($rendered, '<span>Some Guy</span></a>'));
        $this->assertGreaterThan(0, strpos($rendered, 'someguy@example.com'));

        $contact->delete();
    }

    public function testRenderAddresses() {
        $message = $this->getTestEmail();
        $expected = "<span class='non-contact-entity-tag' 
              data-email='someguy@example.com'>Some Guy</span> &lt;someguy@example.com&gt;, ".
              "<span class='non-contact-entity-tag' 
              data-email='someotherguy@example.com'>SomeOther Guy</span> &lt;someotherguy@example.com&gt;";
        // Just test the most basic case, since testRenderAddress handles the case where
        // a contact with the specified email exists
        $this->assertEquals($expected, $message->renderAddresses(array(array('Some Guy', 'someguy@example.com'), array('SomeOther Guy', 'someotherguy@example.com')), true, true));
    }

    public function testRenderFromToAndCCFields() {
        $message = $this->getTestEmail();
        foreach (array('from', 'to', 'cc') as $header) {
            if ($header !== 'from') {
                // From will only use the first address
                $expected = "<span class='non-contact-entity-tag' 
              data-email='someguy@example.com'>Some Guy</span>, ".
              "<span class='non-contact-entity-tag' 
              data-email='someotherguy@example.com'>SomeOther Guy</span>";
            } else {
                $expected = "<span class='non-contact-entity-tag' 
              data-email='someguy@example.com'>Some Guy</span>";
            }
            $message->$header = 'Some Guy <someguy@example.com>, SomeOther Guy <someotherguy@example.com>';
            $fn = 'render'.ucfirst($header).'Field';
            ob_start();
            $message->$fn();
            $output = ob_get_clean();
            $this->assertEquals($expected, $output);
        }
    }

    public function testParseMimeType() {
        $message = $this->getTestEmail();
        $test = 'application/javascript';
        $expected = array('application', 'javascript', array());
        $this->assertEquals($expected, $message->parseMimeType($test));

        $test = 'video/mp4';
        $expected = array('video', 'mp4', array());
        $this->assertEquals($expected, $message->parseMimeType($test));

        $test = 'text/html; charset=UTF-8';
        $expected = array('text', 'html', array(' charset=UTF-8'));
        $this->assertEquals($expected, $message->parseMimeType($test));
    }
}

