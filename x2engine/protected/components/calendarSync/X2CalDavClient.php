<?php
/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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
    public function sync($url, $syncToken) {
        $body = '<?xml version="1.0"?>' . "\n";
        $body.= '<d:sync-collection xmlns:d="DAV:">' . "\n";

        $body.='  <d:sync-token>' . $syncToken . '</d:sync-token>' . "\n";
        $body.='  <d:sync-level>1</d:sync-level>' . "\n";

        $body.= '  <d:prop>' . "\n";
        $body.= '    <d:getetag/>' . "\n";
        $body.= '  </d:prop>' . "\n";

        $body.= '</d:sync-collection>';

        $headers = array(
            'Content-Type' => 'application/xml'
        );
        if ($this->oAuthToken) {
            $headers['Authorization'] = 'Bearer ' . $this->oAuthToken;
        }

        $response = $this->request('REPORT', $url, $body, $headers);
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

}
