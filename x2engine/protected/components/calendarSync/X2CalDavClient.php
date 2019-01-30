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




Yii::setPathOfAlias(
        'Sabre', Yii::getPathOfAlias('application.integration.SabreDAV'));

use Sabre\DAV\Client;
use Sabre\DAV\XMLUtil;

class X2CalDavClient extends Client {

    protected $oAuthToken;

    /**
     * 
     * @param array $settings
     * @throws \InvalidArgumentException
     */
    public function __construct(array $settings) {

        if (!isset($settings['baseUri'])) {
            throw new \InvalidArgumentException('A baseUri must be provided');
        }

        $validSettings = array(
            'baseUri',
            'userName',
            'password',
            'oAuthToken',
            'proxy',
        );

        foreach ($validSettings as $validSetting) {
            if (isset($settings[$validSetting])) {
                $this->$validSetting = $settings[$validSetting];
            }
        }

        if (isset($settings['authType'])) {
            $this->authType = $settings['authType'];
        } else {
            $this->authType = self::AUTH_BASIC | self::AUTH_DIGEST;
        }

        $this->propertyMap['{DAV:}resourcetype'] = 'Sabre\\DAV\\Property\\ResourceType';
    }

    /**
     * 
     * @param type $url
     * @param array $properties
     * @param type $depth
     * @return type
     */
    public function propFind($url, array $properties, $depth = 0) {

        $body = '<?xml version="1.0"?>' . "\n";
        $body.= '<d:propfind xmlns:d="DAV:">' . "\n";
        $body.= '  <d:prop>' . "\n";

        foreach ($properties as $property) {

            list(
                    $namespace,
                    $elementName
                    ) = XMLUtil::parseClarkNotation($property);

            if ($namespace === 'DAV:') {
                $body.='    <d:' . $elementName . ' />' . "\n";
            } else {
                $body.="    <x:" . $elementName . " xmlns:x=\"" . $namespace . "\"/>\n";
            }
        }

        $body.= '  </d:prop>' . "\n";
        $body.= '</d:propfind>';

        $headers = array(
            'Depth' => $depth,
            'Content-Type' => 'application/xml'
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }
        
        $response = $this->request('PROPFIND', $url, $body, $headers);
        $result = $this->parseMultiStatus($response['body']);

        // If depth was 0, we only return the top item
        if ($depth === 0) {
            reset($result);
            $result = current($result);
            return isset($result[200]) ? $result[200] : array();
        }

        $newResult = array();
        foreach ($result as $href => $statusList) {
            $newResult[$href] = isset($statusList[200]) ? $statusList[200] : array();
        }

        return $newResult;
    }

    /**
     * 
     * @param type $url
     * @param array $properties
     */
    public function propPatch($url, array $properties) {

        $body = '<?xml version="1.0"?>' . "\n";
        $body.= '<d:propertyupdate xmlns:d="DAV:">' . "\n";

        foreach ($properties as $propName => $propValue) {

            list(
                    $namespace,
                    $elementName
                    ) = XMLUtil::parseClarkNotation($propName);

            if ($propValue === null) {

                $body.="<d:remove><d:prop>\n";

                if ($namespace === 'DAV:') {
                    $body.='    <d:' . $elementName . ' />' . "\n";
                } else {
                    $body.="    <x:" . $elementName . " xmlns:x=\"" . $namespace . "\"/>\n";
                }

                $body.="</d:prop></d:remove>\n";
            } else {

                $body.="<d:set><d:prop>\n";
                if ($namespace === 'DAV:') {
                    $body.='    <d:' . $elementName . '>';
                } else {
                    $body.="    <x:" . $elementName . " xmlns:x=\"" . $namespace . "\">";
                }
                // Shitty.. i know
                // Above comment copied as-is from SabreDAV
                $body.=htmlspecialchars($propValue, ENT_NOQUOTES, 'UTF-8');
                if ($namespace === 'DAV:') {
                    $body.='</d:' . $elementName . '>' . "\n";
                } else {
                    $body.="</x:" . $elementName . ">\n";
                }
                $body.="</d:prop></d:set>\n";
            }
        }

        $body.= '</d:propertyupdate>';

        $headers = array(
            'Content-Type' => 'application/xml'
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $this->request('PROPPATCH', $url, $body, $headers);
    }

    /**
     * 
     * @param type $url
     * @param array $properties
     * @param array $filters
     * @param type $depth
     * @return type
     */
    public function report($url, array $properties, array $filters = array('VCALENDAR'), $depth = 1) {
        $body = '<?xml version="1.0"?>' . "\n";
        $body.= '<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">' . "\n";

        $body.= '  <d:prop>' . "\n";
        foreach ($properties as $property) {

            list(
                    $namespace,
                    $elementName
                    ) = XMLUtil::parseClarkNotation($property);

            if ($namespace === 'DAV:') {
                $body.='    <d:' . $elementName . ' />' . "\n";
            } elseif ($namespace === 'urn:ietf:params:xml:ns:caldav') {
                $body.='    <c:' . $elementName . ' />' . "\n";
            } else {
                $body.="    <x:" . $elementName . " xmlns:x=\"" . $namespace . "\"/>\n";
            }
        }
        $body.= '  </d:prop>' . "\n";

        $body.= '  <c:filter>' . "\n";
        foreach ($filters as $filter) {
            $body.='    <c:comp-filter name="' . $filter . '" />' . "\n";
        }
        $body.= '  </c:filter>' . "\n";

        $body.= '</c:calendar-query>';

        $headers = array(
            'Depth' => $depth,
            'Content-Type' => 'application/xml'
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('REPORT', $url, $body, $headers);
        $result = $this->parseMultiStatus($response['body']);

        // If depth was 0, we only return the top item
        if ($depth === 0) {
            reset($result);
            $result = current($result);
            return isset($result[200]) ? $result[200] : array();
        }

        $newResult = array();
        foreach ($result as $href => $statusList) {
            $newResult[$href] = isset($statusList[200]) ? $statusList[200] : array();
        }

        return $newResult;
    }

    /**
     * 
     * @param type $url
     * @param type $syncToken
     * @return type
     */
    public function sync($url, $syncToken, $time = NULL) {
        $body = '<?xml version="1.0"?>' . "\n";
        $body.= '<d:sync-collection xmlns:d="DAV:">' . "\n";

        $body.='  <d:sync-token>' . $syncToken . '</d:sync-token>' . "\n";
        $body.='  <d:sync-level>1</d:sync-level>' . "\n";

        $body.= '  <d:prop>' . "\n";
        $body.= '    <d:getetag/>' . "\n";
        $body.= '  </d:prop>' . "\n";

        
        if($time != NULL){
            $body.= '<C:filter><C:time-range start="' . date("Y") . (date('M') - 6 + $time) . '00T000000Z"' . 
                    ' end="' . date("Y") . (date('M') - 5 + $time) . '00T000000Z"/> </C:filter>' . "\n";
        }
        $body.= '</d:sync-collection>';
        $headers = array(
            'Content-Type' => 'application/xml'
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('REPORT', $url, $body, $headers, $time);
        return $this->parseMultiStatus($response['body']);
    }

    public function get($url, $eventPath) {
        $body = '';
        $headers = array();
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('GET', $url . $eventPath, $body, $headers);
        if ($response['statusCode'] === 200) {
            return array(
                'etag' => $response['headers']['etag'],
                'body' => $response['body'],
            );
        }
        return array('etag' => '', 'body' => '');
    }
    
    /*
     * Custom get function for outlook
     */
    public function getOutlook($url, $eventPath) {
        $body = '';
        $headers = array();
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('GET', $url, $body, $headers);
        if ($response['statusCode'] === 200) {
            return array(
                'body' => $response['body'],
            );
        }
        
        return array('body' => '');
    }
    
    /*
     * Custom put function for outlook
     */
    public function patchOutlook($url, $data, $timezone) {
        
        $subject = $data->actionDescription;
        
        //send as UTC
        $dueDate = gmdate('Y-m-d\TH:i:s', $data->dueDate);
        $completeDate = gmdate('Y-m-d\TH:i:s', $data->completeDate);
        
        $start = "'start': {'dateTime': " . CJSON::encode($dueDate) .",'timeZone': " . CJSON::encode($timezone) . "},";
        $end = "'end': {'dateTime': " . CJSON::encode($completeDate) .",'timeZone': " . CJSON::encode($timezone) . "}";
        $body = "{'subject': '" . $subject . "'," . $start . $end.  "}";

        $headers = array(
            'Content-Type' => 'application/json',
        );

        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }
        
        $response = $this->request('PATCH', $url, $body, $headers);
        
        return ($response['statusCode'] === 204 || $response['statusCode'] === 201 || $response['statusCode'] === 200);
    }
    
    /*
     * CUSTOM post function for outlook
     */
    public function postOutlook($url, $data, $timezone){
                
        $subject = $data->actionDescription;
       
        //send as UTC
        $dueDate = gmdate('Y-m-d\TH:i:s', $data->dueDate);
        $completeDate = gmdate('Y-m-d\TH:i:s', $data->completeDate);
        
        $start = "'start': {'dateTime': " . CJSON::encode($dueDate) .",'timeZone': " . CJSON::encode($timezone) . "},";
        $end = "'end': {'dateTime': " . CJSON::encode($completeDate) .",'timeZone': " . CJSON::encode($timezone) . "}";
        $body = "{'subject': '" . $subject . "'," . $start . $end.  "}";
        
        $headers = array(
            'Content-Type' => 'application/json',
        );

        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }
        
        $response = $this->request('POST', $url, $body, $headers);
        
        if ($response['statusCode'] === 204 || $response['statusCode'] === 201 || $response['statusCode'] === 200){
            return $response;
        }else{
            return false;
        }
    }
    
    public function multiget($url, $eventPaths, array $properties) {
        $body = '<?xml version="1.0"?>' . "\n";
        $body.= '<c:calendar-multiget xmlns:d="DAV:"  xmlns:c="urn:ietf:params:xml:ns:caldav">' . "\n";

        $body.= '  <d:prop>' . "\n";
        foreach ($properties as $property) {
            list(
                    $namespace,
                    $elementName
                    ) = XMLUtil::parseClarkNotation($property);

            if ($namespace === 'DAV:') {
                $body.='    <d:' . $elementName . ' />' . "\n";
            } else {
                $body.="    <x:" . $elementName . " xmlns:x=\"" . $namespace . "\"/>\n";
            }
        }
        $body.= '  </d:prop>' . "\n";

        foreach ($eventPaths as $path) {
            $body.= '  <d:href>' . $path . '</d:href>' . "\n";
        }

        $body.= '</c:calendar-multiget>';

        $headers = array(
            'Content-Type' => 'application/xml'
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('REPORT', $url, $body, $headers);
        $result = $this->parseMultiStatus($response['body']);

        $newResult = array();
        foreach ($result as $href => $statusList) {
            $newResult[$href] = isset($statusList[200]) ? $statusList[200] : array();
        }

        return $newResult;
    }

    public function put($url, $eventPath, $data, $etag = null) {
        $body = $data;
        $headers = array(
            'Content-Type' => 'text/calendar',
        );
        if (!empty($etag)) {
            $headers['If-Match'] = $etag;
        }
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('PUT', $url . $eventPath, $body, $headers);
        
        return ($response['statusCode'] === 204 || $response['statusCode'] === 201);
    }

    public function delete($url, $eventPath, $etag) {
        $body = '';
        $headers = array(
            'Content-Type' => 'text/calendar',
            'If-Match' => $etag,
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('DELETE', $url . $eventPath, $body, $headers);

        return $response['statusCode'] === 204;
    }
    
    public function deleteOutlook($url) {
        $body = '';
        $headers = array(
            'Content-Type' => 'application/json',
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('DELETE', $url, $body, $headers);

        return $response['statusCode'] === 204;
    }

}
