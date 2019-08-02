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







Yii::import('application.components.EmlParse');
Yii::import('application.components.util.FileUtil');
Yii::import('application.models.EmlRegex');


/**
 * Unit test for the email parser.
 * 
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmlParseTest extends X2TestCase {

	public $ignoreFilesMatching = '/(.*_.*|^\.[a-z]{3})$/';
	
	public $zapLineBreaks = true;
	
	/**
	 * Instantiates an email parser object. 
	 * 
	 * Creates an EmlParse object using content from an email stored in 
	 * protected/tests/data/email
	 * 
	 * @param type $relpath
	 * @return \EmlParse 
	 */
	public function mkParser($file) {
		$parser = new EmlParse(file_get_contents(Yii::app()->basePath . FileUtil::rpath("/tests/data/email/$file")));
		$parser->zapLineBreaks = $this->zapLineBreaks;
		return $parser; 
	}
	
	/**
	 * Test the ability of the component to parse contacts from forwarded messages
	 */
	public function testFwParse() {
		$emails = array_filter(scandir(Yii::app()->basePath . FileUtil::rpath('/tests/data/email/')), function($e) {
					return !in_array($e, array('..', '.'));
				});
		foreach ($emails as $emlFile) {
			if (preg_match($this->ignoreFilesMatching, $emlFile)) // Ignore files with underscores
				continue;
			$parse = $this->mkParser($emlFile);
			try {
				$contact = $parse->getForwardedFrom();
				if(X2_TEST_DEBUG_LEVEL > 1) echo "\nObtained contact name/address from $emlFile using pattern group \"{$parse->forwardedGroupName}\"";

				$this->assertEquals('Test Contact',$contact->name);
				$this->assertEquals('customer@prospect.com',$contact->address);
			} catch (Exception $e) {
                if($e instanceof PHPUnit_Framework_AssertionFailedError)
                    throw $e;
				$body = $parse->getBody();
				$this->assertTrue(false, strtr("Exception thrown ({msg}) on parse of {file}. The body was:\n{body}",array('{msg}'=>$e->getMessage(),'{file}'=>$emlFile,'{body}'=>$body)));
			}
		}
	}

	/**
	 * Very basic body parse (tests for a special character sequence).
	 */
	public function testBodyParse() {
		$emails = array_filter(scandir(Yii::app()->basePath . FileUtil::rpath('/tests/data/email/')), function($e) {
					return !in_array($e, array('..', '.'));
				});
		foreach ($emails as $emlFile) {
			if (preg_match($this->ignoreFilesMatching, $emlFile)) // Ignore files with underscores
				continue;
			$parse = $this->mkParser($emlFile);
			$fwdFrom = $parse->getForwardedFrom();
            $cleanBody = $parse->bodyCleanup();
            $this->assertRegexp('/%123%/m', $cleanBody,"Failed parse of body from email file $emlFile. body = $cleanBody");
		}
	}
	

	public function testGetFrom() {
		$emails = array_filter(scandir(Yii::app()->basePath .FileUtil::rpath('/tests/data/email/')), function($e) {
					return !in_array($e, array('..', '.'));
				});
		foreach ($emails as $emlFile) {
			if (preg_match($this->ignoreFilesMatching, $emlFile)) // Ignore files with underscores, which are used with tests that involve fixtures
				continue;
			$parse = $this->mkParser($emlFile);
			try {
				$from = $parse->getFrom();
			} catch (Exception $e) {
				$msg = "Exception thrown:\n";
				$msg .= $e->getMessage() . "\n on parse of $emlFile. The body was:\n";
				$msg .= $parse->getBody();
				$this->assertTrue(false,$msg);
			}
			$this->assertEquals($from->name,'Sales Rep');
			$this->assertEquals($from->address,'sales@rep.com');
		}
	}
	
	public function testEmptyName() {
		
		$parse = $this->mkParser("CC_Test_new_emptyname.eml");
		$to = $parse->getTo();
		$this->assertEquals('UnknownFirstName UnknownLastName',$to[0]->name);
	}
	
	/**
	 * Test cleaning up the body, correctly collapsing linebreaks (or not), and 
	 * removing the forwarded message header
	 */
	public function testBodyCleanup() {
		$this->zapLineBreaks = true;
		
		$parse = $this->mkParser("Applemail1.eml");
		$from = $parse->getForwardedFrom();
		$body = 'Redacted,
%123%          I know that sometimes you prop your door open. Be aware that Redacted were the victims of property getting stolen and here is a picture from our camera. Just giving you the heads up. There are thieves out there that are currently looking for open doors.%123%

Regards,

Redacted';
		$this->assertEquals($body,$parse->bodyCleanup());
		
		// Test getting main body (not forwarded)
		$parse = $this->mkParser("Unknown1.eml");
		$body = 'Hi Developer
Thanks, this sounds great! One issue I have is setting up my email and using crm. I use thunderbird for my email, wish gmail was free for businesses.

Everything is good. I have been out for almost 10 days with packing, moving and unpacking. We relocated to South Carolina, right outside of Charleston. %123%

Starting to get back in the swing.

Best Regards,

sales@rep.com
www.redacted.com';
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$parse = $this->mkParser("Unknown2.eml");
		$from = $parse->getForwardedFrom();
		$body = 'Hi Redacted,

Thank you for responding, and I will be testing the forwarded message format. I agree that your use case is very sensible. Originally, the request was to design it as a contact importer as well as an email importer, but the use case you described was not considered. I will try to fit the option to control the behavior into the upcoming release. If not, you can expect it very soon; advanced self-service (but to begin with, a settings page) for the dropbox feature has been requested and in the works for some time.%123%';
		$this->assertEquals($body,$parse->bodyCleanup());
		
		$parse = $this->mkParser("Test_raw_shortlines.eml");
		$body = 'Testing testing 123.

This is a test of the email dropbox.

These lines should be separate!';
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$parse = $this->mkParser("GMail1.eml");
		$from = $parse->getForwardedFrom();
		$body = 'Email dropbox test body paragraph 1.
%123%
Email dropbox test body paragraph 2.';
		$this->assertEquals($body,$parse->bodyCleanup());
		
		
		
		$body = 'The email capture script was not able to recognize the format of the forwarded message, and thus was not able to obtain contact data. Note also that if email is sent directly to the email capture address, it requires that the body contain a forwarded message.

Please forward this email, using the email client program that caused the error, to X2Engine Customer Support with the subject "Unrecognized forwarded email format" so that support for the format can be added. You may redact any information you wish from the original message.

The original email\'s contents were as follows:%123%';
		$parse = $this->mkParser("Applemail2.eml");
		$from = $parse->getForwardedFrom();
		$this->assertEquals($body,$parse->bodyCleanup(true,true));
		
		
		$body = 'Greetings, Test!

Thank you for forwarding me that email; it allowed me to capture the raw format and generate a new pattern so that the forwarding use case will support Zimbra\'s attached forwarded message format. This pattern will formally be included in the next release, but I have taken the liberty of loading it into the database on your VPS so that it will be available right away.

FYI, all VPS customers get the email dropbox functionality automatically set up for them during the provisioning process. As described in the installation
wiki article on its setup, doing it manually involves a few extra server-end configuration steps. Not having to deal with that is one major advantage of our VPS hosting.

To use the email dropbox:

Method 1: Send an email *to* the contact and, in the CC field, put dropbox@testdomain.com, and it will be appended to the contact record as an email-type action. It\'s similar to what I\'m doing with this email. Method 2: Forward an email sent *from* the contact directly to dropbox@testdomain.com, and it will be appended to the contact record as a note-type action ("contact sent email")

If you have any suggestions or feature requests, or run into any problems, please do not hesitate to ask. You can email me at this address, or submit a support case, or ask on our forums (at x2community.com, where we\'ll grant you access to the private priority forum).

--
Sincerely,
Sales Rep
Staff Engineer / Systems Administrator
X2Engine Inc.';
		$parse = $this->mkParser("BodyCleanup_GMail1.eml");
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$body = 'Hello Customer!

Thank you so much for purchasing a hosted account with X2Engine! My name is Haley, and I am a Sales and Marketing Representative. Please feel free to reach out to me at any time. We appreciate your business and are looking forward to working with you. You should have already received an email that will take you through the set up process for your virtual private server.

For access to your X2Engine Virtual Private Server (VPS), you will be granted a free subdomain of x2vps.com of your choosing in addition to the one it is given initially. If you own a domain name that you wish to user for your VPS, you may use that as well. To specify a subdomain name or associate a domain name with your VPS, please send an email to customersupport@x2engine.com or open a support case , beginning the subject/description with "Domain name change:"

As to the dropbox, here is a short video about how to use the functionality:

http://www.youtube.com/watch?v=7ltkwgGvXvs

Essentially, after set up, you can BCC or CC your dropbox (as I did on this email) and X2Engine will recognize the email and attach the correspondence onto the account record.

Please feel free to contact me with any questions, concerns or comments you may have. I would be very interested to hear about your company and how you decided on X2Engine. I look forward to hearing from you, and I hope you enjoy using X2Engine!

--
Sales Rep';
		$parse = $this->mkParser("BodyCleanup_GMail2.eml");
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$body = 'Sales,

Thanks for the email, we will start looking at the software this afternoon. I have worked with Sage Act and Goldmine CRM software in the past, however I never felt “comfortable” with either one.

Quickly here is what I am looking for:

·        I work as a sales rep/marketing manager in the fly fishing, which is a small, niche industry. So staying on task and maintaining a constant relationship with my customers is very important.

·        My total # of contacts is less than 5000

·        Most of my sales are done via phone and email, however I do travel.

·        We produce 1-2 email campaigns each month, to specific, regional and national customers

·        The majority of my larger customers (800) are contacted via phone 2-3 times each year, plus client specific emails.

·        All customers in data base receive email from us

·        We do have a website(s)

·        Developing a sales pipeline with a task manager is important

·        Email integration?

Access via Android?

Suggestions?

As of now I’m looking for a CRM for myself, however several of the companies I consult for are also looking for software.

I appreciate your time.. have a great day!

customer@prospect.com

XXX-XXX-XXXX Cell/Home Office

XXX-XXX-XXXX (New Phase)';
		
		////////////////////////////////////
		// TEST WITHOUT LINEBREAK REMOVAL //
		////////////////////////////////////
		$this->zapLineBreaks = false;
		
		$parse = $this->mkParser("Applemail1.eml");
		$from = $parse->getForwardedFrom();
        $this->assertEquals(array('address' =>'customer@prospect.com','name'=>'Test Contact'),(array) $from);
		$body = 'Redacted,
%123%          I know that sometimes you prop your door open. Be
aware that Redacted were the victims of property getting stolen and
here is a picture from our camera. Just giving you the heads up. There
are thieves out there that are currently looking for open doors.%123%

Regards,

Redacted';
		$this->assertEquals($body,$parse->bodyCleanup());
		
		// Test getting main body (not forwarded)
		$parse = $this->mkParser("Unknown1.eml");
		$body = 'Hi Developer
Thanks, this sounds great! One issue I have is setting up my email and using
crm. I use thunderbird for my email, wish gmail was free for businesses.

Everything is good. I have been out for almost 10 days with packing, moving
and unpacking. We relocated to South Carolina, right outside of Charleston.
%123%

Starting to get back in the swing.

Best Regards,

sales@rep.com
www.redacted.com';
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$parse = $this->mkParser("Unknown2.eml");
		$from = $parse->getForwardedFrom();
        $this->assertEquals(array('address' =>'customer@prospect.com','name'=>'Test Contact'),(array) $from);
		$body = 'Hi Redacted,

Thank you for responding, and I will be testing the forwarded message format. I agree that your use case is very sensible. Originally, the request was to design it as a contact importer as well as an email importer, but the use case you described was not considered. I will try to fit the option to control the behavior into the upcoming release. If not, you can expect it very soon; advanced self-service (but to begin with, a settings page) for the dropbox feature has been requested and in the works for some time.%123%';
		$this->assertEquals($body,$parse->bodyCleanup());
		
		$parse = $this->mkParser("Test_raw_shortlines.eml");
		$body = 'Testing testing 123.

This is a test of the email dropbox.

These lines should be separate!';
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$parse = $this->mkParser("GMail1.eml");
		$from = $parse->getForwardedFrom();
        $this->assertEquals(array('address' =>'customer@prospect.com','name'=>'Test Contact'),(array) $from);
		$body = 'Email dropbox test body paragraph 1.
%123%
Email dropbox test body paragraph 2.';
		$this->assertEquals($body,$parse->bodyCleanup());
		
		
		
		$body = 'The email capture script was not able to recognize the format of the
forwarded message, and thus was not able to obtain contact data. Note
also that if email is sent directly to the email capture address, it
requires that the body contain a forwarded message.

Please forward this email, using the email client program that caused
the error, to X2Engine Customer Support with the subject "Unrecognized
forwarded email format" so that support for the format can be added. You
may redact any information you wish from the original message.

The original email\'s contents were as follows:%123%';
		$parse = $this->mkParser("Applemail2.eml");
		$from = $parse->getForwardedFrom();
        $this->assertEquals(array('address' =>'customer@prospect.com','name'=>'Test Contact'),(array) $from);
		$this->assertEquals($body,$parse->bodyCleanup(true,true));
		
		
		$body = 'Greetings, Test!

Thank you for forwarding me that email; it allowed me to capture the raw
format and generate a new pattern so that the forwarding use case will
support Zimbra\'s attached forwarded message format. This pattern will
formally be included in the next release, but I have taken the liberty of
loading it into the database on your VPS so that it will be available right
away.

FYI, all VPS customers get the email dropbox functionality automatically
set up for them during the provisioning process. As described in the
installation
wiki article on
its setup, doing it manually involves a few extra server-end
configuration steps. Not having to deal with that is one major advantage of
our VPS hosting.

To use the email dropbox:

Method 1: Send an email *to* the contact and, in the CC field, put
dropbox@testdomain.com, and it will be appended to the contact record
as an email-type action. It\'s similar to what I\'m doing with this email.
Method 2: Forward an email sent *from* the contact directly to
dropbox@testdomain.com, and it will be appended to the contact record
as a note-type action ("contact sent email")

If you have any suggestions or feature requests, or run into any problems,
please do not hesitate to ask. You can email me at this address, or submit
a support case, or ask on our forums (at x2community.com, where we\'ll grant
you access to the private priority forum).

--
Sincerely,
Sales Rep
Staff Engineer / Systems Administrator
X2Engine Inc.';
		$parse = $this->mkParser("BodyCleanup_GMail1.eml");
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$body = 'Hello Customer!

Thank you so much for purchasing a hosted account with X2Engine! My name is
Haley, and I am a Sales and Marketing Representative. Please feel free to
reach out to me at any time. We appreciate your business and are looking
forward to working with you. You should have already received an email that
will take you through the set up process for your virtual private server.

For access to your X2Engine Virtual Private Server (VPS), you will be granted
a free subdomain of x2vps.com of your choosing in addition to the one it is
given initially. If you own a domain name that you wish to user for your
VPS, you may use that as well. To specify a subdomain name or associate a
domain name with your VPS, please send an email to
customersupport@x2engine.com or open a support
case ,
beginning the subject/description with "Domain name change:"

As to the dropbox, here is a short video about how to use the functionality:

http://www.youtube.com/watch?v=7ltkwgGvXvs

Essentially, after set up, you can BCC or CC your dropbox (as I did on
this email) and X2Engine will recognize the email and attach the
correspondence onto the account record.

Please feel free to contact me with any questions, concerns or comments you
may have. I would be very interested to hear about your company and how you
decided on X2Engine. I look forward to hearing from you, and I hope you enjoy
using X2Engine!

--
Sales Rep';
		$parse = $this->mkParser("BodyCleanup_GMail2.eml");
		$this->assertEquals($body,$parse->bodyCleanup(true,false));
		
		$body = 'Sales,

Thanks for the email, we will start looking at the software this afternoon.
I have worked with Sage Act and Goldmine CRM software in the past, however
I never felt “comfortable” with either one.

Quickly here is what I am looking for:

·        I work as a sales rep/marketing manager in the fly fishing,
which is a small, niche industry. So staying on task and maintaining a
constant relationship with my customers is very important.

·        My total # of contacts is less than 5000

·        Most of my sales are done via phone and email, however I do
travel.

·        We produce 1-2 email campaigns each month, to specific,
regional and national customers

·        The majority of my larger customers (800) are contacted via
phone 2-3 times each year, plus client specific emails.

·        All customers in data base receive email from us

·        We do have a website(s)

·        Developing a sales pipeline with a task manager is important

·        Email integration?

Access via Android?

Suggestions?

As of now I’m looking for a CRM for myself, however several of the
companies I consult for are also looking for software.

I appreciate your time.. have a great day!

customer@prospect.com

XXX-XXX-XXXX Cell/Home Office

XXX-XXX-XXXX (New Phase)';
		$parse = $this->mkParser("BodyCleanup_Unknown1.eml");
		$from = $parse->getForwardedFrom();
        $this->assertEquals(array('address' =>'customer@prospect.com','name'=>'Test Contact'),(array) $from);
		$this->assertEquals($body,$parse->bodyCleanup(true,true));


        $body = 'Hi Sales,

Test test test test test test, test test test. Test.%123%

Regards

Test';
        $parse = $this->mkParser('Zimbra2.eml');
        $from = $parse->getForwardedFrom();
        $this->assertEquals(array('address' =>'customer@prospect.com','name'=>'Test Contact'),(array) $from);
        $this->assertEquals($body,$parse->bodyCleanup(true,true));
	}

}

?>
