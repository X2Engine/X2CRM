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




Yii::import('application.modules.users.models.*');

class LocationTest extends X2DbTestCase {
    
    public $fixtures = array(
        'location' => 'Locations',
    );
    
    public static function referenceFixtures() {
        return array(
            'user' => 'User',
        );
    }

    public function testLocationRowValues() {
        Yii::app()->cache->flush();
        // Prepare expected data:
        $locationCount = array(
            /*'locationZeroCreateDate' => 1,
            'locationZeroRecordId' => 1,
            'locationNullTypeAndIpAddress' => 1,
            'locationNullType' => 1,*/
            'locationMobileIdle' => 1,
            'locationMobileActivityPost' => 2,
            'locationLogin' => 3,
        );
        
        foreach($locationCount as $alias => $count){
            $column = Locations::model()->findByPk(array('id'=>$count));
            $this->assertNotNull($column);
            
            /*
             * test for an id, there should be an id > 0 (starting at 1)
             */
            $this->assertNotNull($column->id);
            $this->assertGreaterThan(0,$column->id);
            
            /*
             * test for a recordId, there should be a recordId > 0 (starting at 1)
             */
            $this->assertNotNull($column->recordId);
            $this->assertGreaterThan(0,$column->recordId);
            
            /*
             *  test for a recordType:
             * 
             *  'address' => Yii::t('app', 'Address'),
             *  'weblead' => Yii::t('app', 'Weblead Form Submission'),
             *  'webactivity' => Yii::t('app', 'Webactivity'),
             *  'open' => Yii::t('app', 'Email Opened'),
             *  'click' => Yii::t('app', 'Email Click'),
             *  'unsub' => Yii::t('app', 'Email Unsubscribe'),
             *  'login' => Yii::t('app', 'User Login'),
             *  'activityPost' => Yii::t('app', 'Activity Post'),
             *  'mobileIdle' => Yii::t('app', 'Mobile Location'),
             *  'mobileActivityPost' => Yii::t('app', 'Mobile Activity Post'),
             * 
             */
            $typeArray = array(
                'address', 'weblead' , 'webactivity', 'open', 'click', 'unsub', 
                'login', 'activityPost', 'mobileIdle', 'mobileActivityPost'
            );
            $this->assertNotNull($column->recordType);
            $this->assertNotEquals('',$column->recordType);
            $this->assertContains($column->recordType,$typeArray);
            
            /*
             * test if lat and lon is valid
             */
            $this->assertNotNull($column->lat);
            $this->assertGreaterThan(-90,$column->lat);
            $this->assertLessThan(90,$column->lat);
            $this->assertNotNull($column->lon);
            $this->assertGreaterThan(-180,$column->lon);
            $this->assertLessThan(180,$column->lon);
            $this->assertNotNull($column->createDate);
            $this->assertGreaterThan(0,$column->createDate);
            
            /*
             *  test for a type:
             * 
             *  'address' => Yii::t('app', 'Address'),
             *  'weblead' => Yii::t('app', 'Weblead Form Submission'),
             *  'webactivity' => Yii::t('app', 'Webactivity'),
             *  'open' => Yii::t('app', 'Email Opened'),
             *  'click' => Yii::t('app', 'Email Click'),
             *  'unsub' => Yii::t('app', 'Email Unsubscribe'),
             *  'login' => Yii::t('app', 'User Login'),
             *  'activityPost' => Yii::t('app', 'Activity Post'),
             *  'mobileIdle' => Yii::t('app', 'Mobile Location'),
             *  'mobileActivityPost' => Yii::t('app', 'Mobile Activity Post'),
             * 
             */
            if($column->type != NULL) {
                $typeArray = array(
                    'address', 'weblead' , 'webactivity', 'open', 'click', 'unsub',
                     'login', 'activityPost', 'mobileIdle', 'mobileActivityPost'
                );
                $this->assertNotNull($column->type);
                $this->assertNotEquals('',$column->type);
                $this->assertContains($column->type,$typeArray);
            }
                    
            /*
             * test for valid ip address
             */
            if($column->ipAddress != NULL) {
                $this->assertNotEquals('',$column->ipAddress);
                $valid = ip2long($column->ipAddress) !== false;
                $this->assertTrue($valid);          
            }
            
            /*
             * test for create date
             * (test for date being a bigint and later than 2015)
             */
            $this->assertGreaterThan(0,$column->createDate);
        }
    }
}

?>
