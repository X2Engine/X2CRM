<?php

/* * *********************************************************************************
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
 * ******************************************************************************** */

/**
 * X2FlowAction that creates a notification
 *
 * @package application.components.x2flow.actions
 */
abstract class BaseX2FlowLocation extends X2FlowAction {

    protected $typeDropdown = array(
        'login' => 'User Login',
        'webactivity' => 'Website Activity',
        'open' => 'Email Opened',
        'mobileCheckIn' => 'Mobile Check In',
        'activityPost' => 'Activity Post',
    );
    protected $typeTexts = array(
        'login' => 'logged in',
        'webactivity' => 'had web activity',
        'open' => 'opened an email',
        'mobileCheckIn' => 'checked in on mobile',
        'activityPost' => 'posted on the Activity Feed',
    );

    public function paramRules() {
        return array_merge(
                parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'to',
                    'label' => 'Send To',
                    'type' => 'dropdown',
                    'options' => array(
                'admin' => 'admin',
                    ) + array_diff_key(
                            X2Model::getAssignmentOptions(false, false), array('admin' => '')
                    ),
                    'defaultVal' => 'admin',
                ),
                array(
                    'name' => 'type',
                    'label' => 'Looking for nearest',
                    'type' => 'dropdown',
                    'options' => $this->typeDropdown,
                    'defaultVal' => 'webactivity',
                ),
                array(
                    'name' => 'distance',
                    'label' => 'Distance from X2 user',
                ),
                array(
                    'name' => 'distance_units',
                    'label' => '',
                    'type' => 'dropdown',
                    'options' => array(
                        'meters' => 'meters',
                        'kilometers' => 'kilometers',
                        'feet' => 'feet',
                        'miles' => 'miles',
                    ),
                    'defaultVal' => 'kilometers',
                ),
                array(
                    'name' => 'time',
                    'label' => 'Time since X2 user action',
                ),
                array(
                    'name' => 'time_units',
                    'label' => '',
                    'type' => 'dropdown',
                    'options' => array(
                        'mins' => 'minutes',
                        'hours' => 'hours',
                        'days' => 'days',
                    ),
                    'defaultVal' => 'days',
                ),
            )
                )
        );
    }

    protected function getKey($query, $list) {
        foreach ($list as $key => $value) {
            if ($value == $query) {
                return $key;
            }
        }
        return 'login';
    }

    protected function createLongMessage($params, $nearbyUsers, $break, $isLink) {
        $message = '';
        foreach ($nearbyUsers as $nearbyUser) {
            $message .= ($message == '' ? '' : $break) .
                    $this->createMessage($params, $nearbyUser, $isLink);
        }
        return $message;
    }

    protected function createMessage(&$params, $record, $isLink) {
        $recent = $this->getRecentLoginRecord();
        $distance = $this->getRecordDistance($record, $params);
        $distanceUnits = $this->parseOption('distance_units', $params);
        $date = $this->createDate($record);
        $time = $this->createTime($record);

        $typeText = 'done something';
        foreach ($this->typeTexts as $key => $value) {
            if ($record->type == $key) {
                $typeText = $value;
                break;
            }
        }

        $message = sprintf('%s has %s %d %s away on %s at %s. ', $this->getRecordFullName($record), $typeText, $distance, $distanceUnits, $date, $time);
        if ($isLink) {
            $message .= sprintf('<a href="https://google.com/maps/dir/%f,%f/%f,%f/">Get directions</a>', $recent->lat, $recent->lon, $record->lat, $record->lon);
        }

        return $message;
    }

    protected function getNearbyUserRecords(&$params, $flag) {
        if ($this->getRecentLoginRecord() == null) {
            return array();
        }

        $distance = intval($this->parseOption('distance', $params));
        $time = intval($this->parseOption('time', $params));

        $locations = Locations::model()->findAll('type="' . $this->getKey($this->parseOption('type', $params), $this->typeDropdown) . '"');
        
        $result = array();
        foreach ($locations as $location) {
            $miles = $this->getRecordDistance($location, $params);
            $days = $this->getRecordDays($location, $params);

            $seenArray = explode(' ', $location->seen);
            $recent = $this->getRecentLoginRecord();
            $found = false;
            foreach ($seenArray as $seen) {
                if (strpos($seen, $flag) !== false) {
                    $withoutFlag = str_replace($flag, '', $seen);
                    $ids = explode(',', $withoutFlag);
                    foreach ($ids as $currId) {
                        if ($recent->recordId == $currId) {
                            $found = true;
                            break;
                        }
                    }
                }
                if ($found) {
                    break;
                }
            }
            if (!$found && $miles <= $distance && $days <= $time) {
                $result[$location->recordId] = $location;
            }
        }

        return $result;
    }

    protected function getRecentLoginRecord() {
        $userId = Yii::app()->params->profile->id;
        $loginRecord = Locations::model()->findBySql('SELECT * FROM' .
                ' x2_locations WHERE type="login" AND recordId=' . $userId .
                ' ORDER BY createDate DESC LIMIT 1');
        return $loginRecord;
    }

    protected function getRecordFullName($record) {
        $current = User::model()->findByPk($record->recordId);
        return $current->firstName . ' ' . $current->lastName;
    }

    protected function getRecordDistance($record1, &$params) {
        $distanceUnits = $this->parseOption('distance_units', $params);

        $radius = 3961.0;
        if ($distanceUnits == 'meters') {
            $radius = 6373000.0;
        } else if ($distanceUnits == 'kilometers') {
            $radius = 6373.0;
        } else if ($distanceUnits == 'feet') {
            $radius = 20914080.0;
        }

        $record2 = $this->getRecentLoginRecord();

        $latFrom = deg2rad($record2->lat);
        $lonFrom = deg2rad($record2->lon);
        $latTo = deg2rad($record1->lat);
        $lonTo = deg2rad($record1->lon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance = round($angle * $radius, 2);

        return $distance;
    }

    protected function getRecordDays($record1, &$params) {
        $timeUnits = $this->parseOption('time_units', $params);

        $time = 60.0 * 60.0 * 24.0;
        if ($timeUnits == 'minutes') {
            $time = 60.0;
        } else if ($timeUnits == 'hours') {
            $time = 60.0 * 24.0;
        }

        $record2 = $this->getRecentLoginRecord();
        return floor(abs($record1->createDate - $record2->createDate) / $time);
    }

    protected function createDate($record) {
        return date('m-d-Y', $record->createDate);
    }

    protected function createTime($record) {
        return date('g:ia', $record->createDate);
    }

    protected function updateSeen($location, $flag) {
        if ($location->seen == null) {
            $location->seen = 't a e n';
        }
        $seenArray = explode(' ', $location->seen);
        $location->seen = '';
        $recent = $this->getRecentLoginRecord();
        $updated = false;
        foreach ($seenArray as $seen) {
            $new = $seen;
            if ($updated === false && strpos($seen, $flag) !== false) {
                $withoutFlag = str_replace($flag, '', $seen);
                if ($withoutFlag == '') {
                    $new = $flag . Yii::app()->params->profile->id;
                    $updated = true;
                } else {
                    $ids = explode(',', $withoutFlag);
                    $found = false;
                    foreach ($ids as $currId) {
                        if ($recent->recordId == $currId) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $new = $flag . $withoutFlag . ',' . Yii::app()->params->profile->id;
                        $updated = true;
                    }
                }
            }
            if ($location->seen !== '') {
                $location->seen .= ' ';
            }
            $location->seen .= $new;
        }
        $location->save();
    }

    function showRecords($records, &$params) {
        printR('- start -', false);
        foreach ($records as $record) {
            printR($this->getRecordFullName($record), false);
            printR('miles: ' . $this->getRecordDistance($record, $params), false);
            printR('days: ' . $this->getRecordDays($record, $params), false);
        }
        printR('- end -', true);
    }

}
