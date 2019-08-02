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






Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.docs.models.*');

/**
 * Test for the API web listener action.
 *
 * @todo Add automated tests for browser fingerprinting
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class WebListenerActionTest extends CURLDbTestCase{


	public $fixtures = array(
		'actions' => 'Actions',
		'notifications' => 'Notification',
		'events' => 'Events',
        'listItems' => 'X2ListItem',
	);

	public $urlParam = array(
		'{params}' => '',
	);

	public static function referenceFixtures(){
		return array(
			'contacts' => 'Contacts',
            'lists' => 'X2List',
            'campaigns' => 'Campaign'
		);
	}

    public function setUp(){
        Yii::app()->settings->webTrackerCooldown = 1;
        Yii::app()->settings->enableWebTracker = 1;
        Yii::app()->settings->update(array('webTrackerCooldown','enableWebTracker'));
        parent::setUp();
    }

    public function tearDown(){
        Yii::app()->settings->webTrackerCooldown = 1;
        Yii::app()->settings->update(array('webTrackerCooldown'));
        parent::tearDown();
    }

	public function urlFormat() {
		return 'api/webListener{params}';
	}

    public function getCurlHandle2 ($params, $cookie=null) {
		$ch = parent::getCurlHandle($params);
		curl_setopt($ch, CURLOPT_COOKIESESSION,true);
		if(!empty($cookie)){
			curl_setopt($ch,CURLOPT_COOKIE,"x2_key=$cookie");
		}
		curl_setopt($ch, CURLOPT_HEADER, 1);
		return $ch;
    }

	public function assertCookieSetting($value,$response){
		preg_match('/^Set\-Cookie:\s*x2_key=([^;]+);?/im', $response, $m);
		$this->assertNotEmpty($m,'Cookie did not get set. Response = '.$response);
		$this->assertEquals($value,$m[1],'Cookie did not get set to the correct value');
	}

	public function assertNoTrackCampaignClick($message='Campaign tracking happened (double-check the fixture data) although in this scenario it should not have occurred.') {
		$this->assertNull(Actions::model()->findByAttributes(array('type'=>'email_clicked')),$message);
		$this->assertNull(Events::model()->findByAttributes(array('type'=>'email_clicked')),$message);
		$this->assertNull(Notification::model()->findByAttributes(array('type'=>'email_clicked')),$message);		
	}
	public function assertNoTrackGeneric($message='Generic tracking appears to have happened (you might want to double-check the fixture data) although in this scenario it should not have.') {
		$this->assertNull(Actions::model()->findByAttributes(array('type'=>'webactivity')),$message);
		$this->assertNull(Events::model()->findByAttributes(array('type'=>'web_activity')),$message);
		$this->assertNull(Notification::model()->findByAttributes(array('type'=>'webactivity')),$message);
	}

	public function assertTrackCampaignClick($item,$param,$cookie){
		$admin = Yii::app()->settings;
		$ch = $this->getCurlHandle2($param,$cookie);
		$cr = curl_exec($ch);
		file_put_contents('api_response-'.$this->getName().'.html',$cr);
		$this->assertResponseCodeIs(200, $ch,'API request failed due to server error.');
        
		if((bool)$item->contact){
			$actionAttr = array(
				'type' => 'email_clicked',
				'complete' => 'Yes',
				'updatedBy' => 'API',
				'associationType' => 'contacts',
				'associationId' => $item->contact->id,
				'associationName' => $item->contact->name,
				'visibility' => $item->contact->visibility,
				'assignedTo' => $item->contact->assignedTo
			);
			$eventAttr = array(
				'level' => 3,
				'associationId' => $item->contact->id,
				'associationType' => 'Contacts',
				'type' => 'email_clicked',
			);
			$notifAttr = array(
				'type' => 'email_clicked',
				'user' => $item->contact->assignedTo,
				'modelType' => 'Contacts',
				'modelId' => $item->contact->id,
			);
			// Actions, events and notifications
			$this->assertNotNull(Actions::model()->findByAttributes($actionAttr),'Action not created');
			$this->assertNotNull(Events::model()->findByAttributes($eventAttr),'Event not created');
			$this->assertNotNull(Notification::model()->findByAttributes($notifAttr),'Notification not created');
		}
		// The item must be clicked!
		$item->refresh();
		$this->assertNotEquals(0,$item->opened,'Web listener triggered, but the email was not marked as opened.');
		$this->assertNotEquals(0,$item->clicked,'Web listener triggered, but the email was not marked as link-clicked.');
	}

	/**
	 * Test that generic tracking works.
	 *
	 * @param type $contact Contact record to track
	 * @param type $param URL params to set in the request
	 * @param type $cookie Cookie to set in the request
	 * @param type $runCurl True to run the API request; false to merely test for records
	 */
	public function assertTrackGeneric($contact,$param,$cookie,$runCurl = true){
		$admin = Yii::app()->settings;
		if($runCurl){
			$ch = $this->getCurlHandle2($param, $cookie);
			$cr = curl_exec($ch);
			file_put_contents('api_response-'.$this->getName().'.html',$cr);
			$this->assertResponseCodeIs(200, $ch, 'API request failed due to server error.');
		}
        
		$actionAttr = array(
			'associationType' => 'Contacts',
			'associationId' => $contact->id,
			'type' => 'webactivity',
			'assignedTo' => $contact->assignedTo,
			'visibility' => 1,
			'associationName' => $contact->name,
			'complete' => 'Yes',
			'updatedBy' => 'admin',
		);
		$eventAttr = array(
			'level' => 1,
			'user' => $contact->assignedTo,
			'type' => 'web_activity',
			'associationType' => 'Contacts',
			'associationId' => $contact->id,
		);
		$notifAttr = array(
			'modelType' => 'Contacts',
			'modelId' => $contact->id,
			'type' => 'webactivity',
			'createdBy' => 'API',
			'user' => $contact->assignedTo,
		);
		$this->assertNotNull(Actions::model()->findByAttributes($actionAttr),'Action not created');
		$this->assertNotNull(Events::model()->findByAttributes($eventAttr),'Event not created');
		$this->assertNotNull(Notification::model()->findByAttributes($notifAttr),'Notification not created');
        if(WebListenerAction::DEBUG_TRACK) {
            if(X2_TEST_DEBUG_LEVEL > 1){
                echo "\nNOTE: tracker debugging is enabled, so there is no way to test if cooldown works.\n";
            }
            return $cr;
        }
        
        // Now, set the web tracker cooldown high, try the request over again,
		// and verify that duplicate tracking records don't get made.
		$cooldown = $admin->webTrackerCooldown;
		$admin->webTrackerCooldown = 86400;
		$admin->update(array('webTrackerCooldown'));
		$ch = $this->getCurlHandle2($param,'NoTaNyKeY');
		$cr = curl_exec($ch);

		$this->assertEquals(1,Actions::model()->countByAttributes($actionAttr),"Cooldown not working");
		$this->assertEquals(1,Events::model()->countByAttributes($eventAttr),"Cooldown not working");
		$this->assertEquals(1,Notification::model()->countByAttributes($notifAttr),"Cooldown not working");
		$admin->webTrackerCooldown = $cooldown;
		$admin->save();
		return $cr;
	}


	/**
	 * TESTING SCENARIOS
	 * +------+--------+----------+-----------+-------+----------+
	 * | URL  | Cookie | Campaign | Contact | Contact | Method   |
	 * | key? | key?   | key?     | key?    | exists? |          |
	 * +------+--------+----------+---------+---------+----------+
	 * | Y    | N      | N        | Y       | Y       | testRun1 |
	 * | N    | Y      | N        | Y       | Y       | testRun2 |
	 * | Y    | N      | Y        | N       | Y       | testRun3 |
	 * | Y    | Y      | N        | Y       | Y       | testRun1 |
	 * | Y    | Y      | Y        | N       | Y       | testRun3 |
	 * | Y    | N      | Y        | N       | N       | testRun4 |
	 * +------+--------+----------+---------+---------+----------+
	 *
	 * Miscellaneous:
	 * Web tracker cooldown and disablement features tested in testRun1
	 */

	/**
	 * Contact exists, generic/URL-based tracking
	 *
	 * Performed: trackGeneric() if cooldown inactive, setKey(), exit.
	 * Expected: action, event, notification (types webactivity, web_activity and webactivity, respectively) and a cookie
	 */
	public function testRun1() {
		// Bonus test (since it's broadly applicable, doesn't disturb data and
		// isn't big enough to warrant its own method): make sure nothing
		// happens if the web tracker is disabled:
		$admin = Yii::app()->settings;
		$admin->enableWebTracker = 0;
		$admin->save();
		$param = $this->urlParam;
		$param['{params}'] = '';
		$ch = $this->getCurlHandle2($param);
		$cr = curl_exec($ch);
                file_put_contents('api_response-'.$this->getName().'.html',$cr);
		$this->assertNoTrackGeneric();
		$this->assertNoTrackCampaignClick();
		$admin->enableWebTracker = 1;
		$admin->save();

		// Now, on with the testing.
		// 
		// Forcefully set the cookie to something wrong, to verify that the link
		// key supersedes it:
		$contact = $this->contacts('testUser');
		$param['{params}'] = "?get_key={$contact->trackingKey}";
		$cr = $this->assertTrackGeneric($contact,$param,'NoTaNyKeY');
		$this->assertCookieSetting($contact->trackingKey,$cr);
	}

	/**
	 * Contact exists, generic/cookie-based tracking
	 */
	public function testRun2(){
		$param = $this->urlParam;
		$param['{params}'] = '';
		$contact = $this->contacts('testUser');
		$this->assertTrackGeneric($contact,$param,$contact->trackingKey);
	}

	/**
	 * Contact exists, campaign/URL-based tracking
	 */
	public function testRun3(){
		$param = $this->urlParam;
		$listItem = $this->listItems('testUser');
		$param['{params}'] = "?get_key={$listItem->uniqueId}";
		// Set the cookie to the contact's tracking key, to verify that the key
		// in the URL supersedes it and it exits after finishing up campaign tracking:
		$this->assertTrackCampaignClick($listItem,$param,$listItem->contact->trackingKey);
		// It should have exited without doing anything else.
		//$this->assertNoTrackGeneric();
	}

	/**
	 * Contact does not exist; newsletter
	 */
	public function testRun4(){
		$param = $this->urlParam;

		$listItem = $this->listItems('newsletterCampaignEntry');
		$this->assertNotNull($listItem->list->attributes);

		$param['{params}'] = "?get_key={$listItem->uniqueId}";
		$this->assertTrackCampaignClick($listItem,$param,$listItem->uniqueId);
		// It should have exited without doing anything else.
		$this->assertNoTrackGeneric();
	}

}

?>
