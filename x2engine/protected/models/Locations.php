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

/**
 * This is the model class for table "x2_locations".
 * @package application.models
 */
class Locations extends CActiveRecord
{
    public static $geoIpProviders = array(
        'https://freegeoip.net/json',
        'https://geoip.nekudo.com/api',
    );

    public static function getLocationTypes() {
        return array(
            'address' => Yii::t('app', 'Address'),
            'weblead' => Yii::t('app', 'Weblead Form Submission'),
            'webactivity' => Yii::t('app', 'Webactivity'),
            'open' => Yii::t('app', 'Email Opened'),
            'click' => Yii::t('app', 'Email Click'),
            'unsub' => Yii::t('app', 'Email Unsubscribe'),
            'login' => Yii::t('app', 'User Login'),
            'activityPost' => Yii::t('app', 'Activity Post'),
            'mobileIdle' => Yii::t('app', 'Mobile Location'),
            'mobileActivityPost' => Yii::t('app', 'Mobile Activity Post'),
            'eventRSVP' => Yii::t('app','Calendar Event RSVP'),
        );
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @return Imports the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_locations';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('contactId, lat, lon', 'required'),
        );
	}

	/**
	 * @return array behaviors
	 */
    public function behaviors(){
        return array(
            'TimestampBehavior' => array('class' => 'TimestampBehavior'),
        );
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'contactId' => Yii::t('contacts','Contact ID'),
			'recordId' => Yii::t('contacts','Record ID'),
			'recordType' => Yii::t('contacts','Record Type'),
			'lat' => Yii::t('contacts','Latitutde'),
			'lon' => Yii::t('contacts','Longitude'),
			'type' => Yii::t('contacts','Type'),
			'ipAddress' => Yii::t('contacts','IP Address'),
			'comment' => Yii::t('contacts','Check-in comment'),
		);
	}

    public static function getDefaultContactTypes() {
        return array(
            'address',
            'weblead',
            'webactivity',
            'open',
            'click',
            'unsub',
            'eventRSVP',
        );
    }

    public static function getDefaultUserTypes() {
        return array(
            'address',
            'login',
            'activityPost',
            'mobileIdle',
            'mobileActivityPost',
            'eventRSVP',
        );
    }

    public function getLocationLink($text = null) {
        if (is_null($text)) {
            if (!empty($this->comment))
                $text = $this->comment;
            else
                $text = '('.$this->lat.', '.$this->lon.')';
        }
        $modelParam = ($this->recordType === 'Contacts') ? 'contactId' : 'userId';
        return CHtml::link($text, array(
            '/contacts/contacts/googleMaps',
            $modelParam => $this->recordId,
            'noHeatMap' => 1,
            'locationType' => array($this->type),
        ));
    }

    /**
     * Retrieve the associated record, optionally as a link
     * @param bool $link Whether to return a link to the record
     * @return X2Model | string | null
     */
    public function getAssociation($link = false){
        $model = X2Model::getAssociationModel($this->recordType, $this->recordId);
        if($model && $link)
            return $model->getLink();
        return $model;
    }

    public function relations() {
        return array(
            'action' => array(self::HAS_ONE, 'Actions', 'locationId'),
            'event' => array(self::HAS_ONE, 'Events', 'locationId'),
        );
    }

    /**
     * Perform a GeoIP lookup of the specified IP. Dual layered caching is used here, first
     * checking the cache, before consulting the db, resorting to a GeoIP lookup if missing
     * @param string $ip IP Address
     * @return array | null Array with 'lat' and 'lon', or null if unable to resolve
     */
    public static function resolveIpLocation($ip) {
        $cacheKey = 'X2-Locations-geoip-'.$ip;
        $cachedLoc = Yii::app()->cache->get($cacheKey);
        if ($cachedLoc) { // Check the cache first
                $location = array(
                    'lat' => $cachedLoc['lat'],
                    'lon' => $cachedLoc['lon'],
                    'provider' => 'cache',
                );
        } else { // Otherwise, see if the IP was cached in db within a week ago
            $cachedLoc = Yii::app()->db->createCommand()
                ->select('lat, lon')
                ->from(self::model()->tableName())
                ->where('ipAddress = :ip AND createDate > :week', array(
                    ':ip' => $ip,
                    ':week' => time() - (3600 * 24 * 7),
                ))->queryRow();
            if ($cachedLoc) {
                $location = array(
                    'lat' => $cachedLoc['lat'],
                    'lon' => $cachedLoc['lon'],
                    'provider' => 'database',
                );
            } else { // Finally, perform the GeoIP lookup
                $location = self::geoIPLookup($ip);
            }
            if ($location && array_key_exists('lat', $location) && array_key_exists('lat', $location)) {
                Yii::app()->cache->set($cacheKey, array(
                    'lat' => $location['lat'],
                    'lon' => $location['lon'],
                ), 3600);
            }
        }
        return $location;
    }

    private static function geoIPLookup($ip) {
        $location = null;
        if (isset($_SERVER['GEOIP_LATITUDE']) && isset($_SERVER['GEOIP_LONGITUDE']) &&
                $_SERVER['GEOIP_ADDR'] === $ip) {
            // Retrieve coords from mod_geoip, verifying that $ip is the request address, which
            // may not be the case when executed independantly, or when the client is behind a
            // proxy but mod_geoip is not configured to scan proxy headers
            $location = array(
                'lat' => $_SERVER['GEOIP_LATITUDE'],
                'lon' => $_SERVER['GEOIP_LONGITUDE'],
                'provider' => 'mod-geoip',
            );
        } else {
            foreach (self::$geoIpProviders as $provider) {
                if(extension_loaded('curl')) // note: freegeoip.net does not resolve without curl
                    AppFileUtil::$alwaysCurl = true;
                $resp = RequestUtil::request(array(
                    'url' => $provider.'/'.$ip,
                    'header' => array(
                        'Content-Type' => 'application/json',
                    ),
                ));
                $resp = CJSON::decode($resp);
                if ($resp) {
                    if (array_key_exists('location', $resp) &&
                        array_key_exists('latitude', $resp['location']) &&
                        array_key_exists('longitude', $resp['location'])) {
                            $location = array(
                                'lat' => $resp['location']['latitude'],
                                'lon' => $resp['location']['longitude'],
                                'provider' => $provider,
                            );
                            break;
                    } else if (array_key_exists('latitude', $resp) &&
                        array_key_exists('longitude', $resp)) {
                            $location = array(
                                'lat' => $resp['latitude'],
                                'lon' => $resp['longitude'],
                                'provider' => $provider,
                            );
                            break;
                    }
                }
            }
        }
        return $location;
    }
}
