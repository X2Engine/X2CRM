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
 * @package application.components
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class S3Behavior extends CBehavior {
    // Currently available regions
    public static $AWSRegions = array(
        'us-east-1',
        'us-west-2',
        'us-west-1',
        'eu-west-1',
        //'eu-central-1', // reenable when using v4 signature method
        'ap-southeast-1',
        'ap-southeast-2',
        'ap-northeast-1',
        'sa-east-1',
    );

    /**
     * POST the requested file to an Amazon S3 bucket using Amazon's v2 signature method
     * @param string $accessKey Amazon Access Key ID
     * @param string $secretKey Amazon Secret Key ID
     * @param string $bucket Amazon S3 bucket and filepath
     * @param string $key S3 object identifier
     * @param string $region AWS Region
     * @param string $src Source file
     * @returns bool Whether the object POST was successful
     */
    protected function postToS3($accessKey, $secretKey, $bucket, $key, $region, $src) {
        if (!in_array ($region, self::$AWSRegions))
            throw new CHttpException (400, Yii::t('app', 'Invalid S3 region specified'));
        if ($region === 'us-east-1')
            $url = "http://s3.amazonaws.com/$bucket/";
        else
            $url = "http://s3-$region.amazonaws.com/$bucket/";
        $now = time();
        $shortDate = gmdate('Ymd', $now);
        $date = gmdate('Y-m-d\TG:i:s\Z', $now);
        $expirationDate = gmdate('Y-m-d\TG:i:s\Z', ($now + 60*30));
        $credentialString = "$accessKey/$shortDate/$region/s3/aws4_request";

        // Construct a policy, generate a signing key, then sign the policy with the derived key
        $contents = file_get_contents ($src);
        $contentChecksum = md5 ($contents);
        $policy = $this->buildPolicy ($credentialString, $bucket, $key, $date, $expirationDate, $contentChecksum);
        // TODO update signature method to use v4
        $signature = base64_encode(hash_hmac('sha1', $policy, $secretKey, true));
        //$signingKey = $this->generateS3SigningKey ($secretKey, $shortDate, $region);
        //$signature = hash_hmac ('sha256', $policy, $signingKey, true);
        $params = array(
            'url' => $url,
            'method' => 'POST',
            'multipart' => true,
            'timeout' => 20,
            'header' => array(
                'Content-Type' => 'multipart/form-data',
            ),
            'content' => array(
                'AWSAccessKeyId' => $accessKey,
                'key' => $key,
                'bucket' => $bucket,
                'success_action_status' => '201',
                //'x-amz-algorithm' => 'AWS4-HMAC-SHA256',
                //'x-amz-credential' => $credentialString,
                'x-amz-date' => $date,
                'policy' => $policy,
                'signature' => $signature,
                //'Content-MD5' => $contentChecksum,
                'file' => $contents,
            ),
        );

        $response = RequestUtil::request ($params);
        return $this->handleS3Response ($response);
    }

    /**
     * Parse an HTTP response from S3 and check for the presence of errors
     * @param string $response HTTP response body
     * @returns bool Whether an error occured
     */
    protected function handleS3Response($response) {
        if (empty($response)) {
            AuxLib::debugLog ("Received empty response from S3");
            return false;
        }
        $parser = xml_parser_create();
        xml_parse_into_struct ($parser, $response, $values, $index);
        xml_parser_free ($parser);
        if (array_key_exists ('ERROR', $index)) {
            if (array_key_exists ('MESSAGE', $index)) {
                $error = $values[$index['MESSAGE'][0]]['value'];
                Yii::log ("Failed to POST object to S3. Error was: $error", 'error');
                AuxLib::debugLog ("Failed to POST object to S3. Error was: $error");
            }
            return false;
        }
        return true;
    }

    /**
     * Construct the security policy for the S3 request
     * @param string $credentialString Amazon formatted credential string
     * @param string $bucket Amazon S3 bucket and filepath
     * @param string $key S3 object identifier
     * @param string $date Formatted date string
     * @param string $expirationDate Formatted expiration date string
     * @param string $checksum Content MD5 checksum
     * @returns string Base64 encoded JSON policy
     */
    protected function buildPolicy($credentialString, $bucket, $key, $date, $expirationDate, $checksum) {
        $policy = array(
            'expiration' => $expirationDate,
            'conditions' => array(
                array('key' => $key),
                array('bucket' => $bucket),
                array('success_action_status' => '201'),
                //array('x-amz-algorithm' => 'AWS4-HMAC-SHA256'),
                //array('x-amz-credential' => $credentialString),
                array('x-amz-date' => $date),
                //array('Content-MD5' => $checksum),
            ),
        );
        return base64_encode (json_encode ($policy));
    }

    /**
     * Generate an S3 Signing key using Amazon's v4 signature method
     * https://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-authentication-HTTPPOST.html
     * @param string $secretKey Amazon Secret Key ID
     * @param string $date Date in short format. Note: this must be the same date which was
     *  provided to buildPolicy()
     * @param string $region AWS Region
     * @returns string S3 Signing Key
     */
    protected function generateS3SigningKey($secretKey, $date, $region) {
        $dateKey = hash_hmac ('sha256', $date, 'AWS4'.$secretKey);
        $dateRegionKey = hash_hmac ('sha256', $region, $dateKey);
        $dateRegionServiceKey = hash_hmac ('sha256', 's3', $dateRegionKey);
        return hash_hmac ('sha256', 'aws4_request', $dateRegionServiceKey);
    }
}
