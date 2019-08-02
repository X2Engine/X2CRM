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
 * MappableBehavior class file.
 * 
 * @package application.components 
 */
class MappableBehavior extends CActiveRecordBehavior {

    public $recordType;

    public function logLocation($type, $method = 'GET', $param = 'geoCoords') {
        $ip = Yii::app()->controller->getRealIP();
        $logIp = false;
        $coords = false;
        $comment = null;
        if ($method === 'GET' && isset($_GET[$param])) {
            $geoCoords = $_GET[$param];
        } else if ($method === 'POST' && isset($_POST[$param])) {
            $geoCoords = $_POST[$param];
        }
        if ($method && isset($geoCoords)) {
            $coords = json_decode($geoCoords, true);
            $comment = isset($coords['comment']) ? $coords['comment'] : null;
        }
        if ((!$coords || !array_key_exists('lat', $coords) || !array_key_exists('lon', $coords)) &&
                !X2IPAddress::isPrivateAddress($ip)) {
            $coords = Locations::resolveIpLocation($ip);
            $logIp = $ip;
        }
        if ($coords && array_key_exists('lat', $coords) && array_key_exists('lon', $coords)) {
            $location = $this->updateLocation($coords['lat'], $coords['lon'], $type, $logIp, $comment);
            return $location;
        }
    }

    /**
     * Update contact location of specified type. Only a single address Location
     * will be stored, any other type may have multiple
     * @param float $lat latitude
     * @param float $lon longitude
     * @param string $type null for address (authoritative)
     * @param string|false $logIp IP Address to log, if requested
     * @param string $comment Comment to log if requested
     */
    public function updateLocation($lat, $lon, $type = null, $logIp = false, $comment = null) {
        if (is_null($type)) {
            // Look for existing address Location
            $location = Locations::model()->findByAttributes(array(
                'recordType' => get_class($this->owner),
                'recordId' => $this->owner->id,
                'type' => null,
            ));
        }
        if (!isset($location)) {
            $location = new Locations;
            $location->recordId = $this->owner->id;
            $location->recordType = get_class($this->owner);
            $location->lat = $lat;
            $location->lon = $lon;
            $location->type = $type;
            if ($logIp) {
                $location->ipAddress = $logIp;
            }
            if ($comment) {
                $location->comment = $comment;
            }
            $location->save();
        } else if ($location->lat != $lat || $location->lon != $lon) {
            $location->lat = $lat;
            $location->lon = $lon;
            if ($logIp) {
                $location->ipAddress = $logIp;
            }
            if ($comment) {
                $location->comment = $comment;
            }
            $location->save();
        }
        if (get_class($this->owner) == 'Contacts') {
            X2Flow::trigger('LocationTrigger', array('model' => $this->owner));
        }
        return $location;
    }

    /**
     * Retrieve list of locations with types, formatted for Google Maps
     */
    public function getMapLocations($type = null) {
        $params = array(
            'recordId' => $this->owner->id,
            'recordType' => get_class($this->owner),
        );
        if ($type) {
            $params['type'] = $type;
        }
        $locations = array();
        foreach (Locations::model()->findAllByAttributes($params) as $loc) {
            // Provide an appropriate description and link for locations
            switch ($loc->type) {
                case "weblead":
                    $infoText = Yii::t('app', 'Submitted Weblead Form');
                    break;
                case "webactivity":
                    $infoText = Yii::t('app', 'Web Activity');
                    break;
                case "open":
                    $infoText = Yii::t('app', 'Email Opened');
                    break;
                case "unsub":
                    $infoText = Yii::t('app', 'Email Unsubscribed');
                    break;
                case "click":
                    $infoText = Yii::t('app', 'Email Clicked');
                    break;
                case "login":
                    $infoText = Yii::t('app', 'User Login');
                    break;
                case 'activityPost':
                    $infoText = Yii::t('app', 'Activity Post');
                    break;
                case 'mobileIdle':
                    $infoText = Yii::t('app', 'Mobile Location');
                    break;
                case 'mobileActivityPost':
                    $infoText = Yii::t('app', 'Mobile Activity Post');
                    break;
                case 'eventRSVP':
                    $infoText = Yii::t('app', 'Calendar Event RSVP');
                    break;
                default:
                    $infoText = Yii::t('app', 'Stated Address');
            }
            $action = $loc->action;
            $event = $loc->event;
            if ($action) {
                $infoText = CHtml::link($infoText, $action->getUrl(), array(
                            'class' => 'action-frame-link',
                            'data-action-id' => $action->id,
                ));
            } else if ($event) {
                $infoText = $infoText . ': ' . $event->getText();
            }
            $locations[] = array(
                'lat' => (float) $loc['lat'],
                'lng' => (float) $loc['lon'],
                'type' => $loc['type'],
                'infoText' => $infoText,
                'time' => Formatter::formatDateTime($loc['createDate']),
            );
        }
        return $locations;
    }

}
