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
 * Behavior for handling requests to Amazon S3
 * 
 * @package application.tests.unit.components
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class S3BehaviorTest extends X2TestCase {
    public function testHandleS3Response() {
        $c = new CComponent;
        $c->attachBehavior ('S3Behavior', new S3Behavior);
        $expectedResponses = array(
            '' => false,
            '<?xml version="1.0" encoding="UTF-8"?>
            <Error>
              <Code>NoSuchKey</Code>
              <Message>The resource you requested does not exist</Message>
              <Resource>/mybucket/myfoto.jpg</Resource> 
              <RequestId>4442587FB7D0A2F9</RequestId>
            </Error>' => false,
            '<?xml version="1.0" encoding="UTF-8"?>              
            <PostResponse>
              <Location>http://s3.amazonaws.com/MyBucket/accounts.csv</Location>
              <Bucket>MyBucket</Bucket>
              <Key>accounts.csv</Key>
              <ETag>"bb39ea135fbd487c8e10a64ac9d75f89"</ETag>
            </PostResponse>' => true
        );

        foreach ($expectedResponses as $response => $result)
            $this->assertEquals ($result, $c->handleS3Response ($response));
    }

    public function testBuildPolicy() {
        $c = new CComponent;
        $c->attachBehavior ('S3Behavior', new S3Behavior);

        // Policy parameters
        $credentialString = 'AAABCDEFG/19850305/us-east-1/s3/aws4_request';
        $bucket = 'MyBucket';
        $key = 'accounts.csv';
        $date = '1985-03-05T11:30:00Z';
        $expirationDate = '1985-03-05T12:30:00Z';
        $checksum = ''; // Currently unused

        // Expected base64 encoded JSON policy
        $encodedPolicy = base64_encode (preg_replace('/\s/', '', '{
            "expiration": "'.$expirationDate.'",
            "conditions": [
                {"key": "'.$key.'"},
                {"bucket": "'.$bucket.'"},
                {"success_action_status": "201"},
                {"x-amz-date": "'.$date.'"}
            ]
        }'));

        $this->assertEquals ($encodedPolicy, $c->buildPolicy ($credentialString, $bucket, $key, $date, $expirationDate, $checksum));
    }
}
