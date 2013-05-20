<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/
Yii::import('application.models.*');

/**
 * Test case for the model class {@link Events}.
 * @package X2CRM.tests.unit.models
 * @author Jake Houser <jake@x2engine.com>
 */
class EventsTest extends CDbTestCase {
    
    public $fixtures=array(
        'event'=>'Events',
    );
    
    public function testGetEvents(){
        $lastEventId=0;
        $lastTimestamp=0;
        $events=Events::getEvents($lastEventId,$lastTimestamp,'admin',1359483530);
        
        $this->assertArrayHasKey('events',$events);
        $this->assertNotEmpty($events['events']);
        $this->assertCount(1,$events['events']);
        
        $firstEvent=array_pop($events['events']); 
        $this->assertEquals('Test social post.',$firstEvent->text);
        if(empty($events['events'])){
            $lastEvent=$firstEvent;
        }else{
            $lastEvent=array_pop($events['events']);
        }
        if($lastEvent->id > $lastEventId){
            $lastEventId=$lastEvent->id;
        }
        if($lastEvent->timestamp > $lastTimestamp){
            $lastTimestamp=$lastEvent->timestamp;
        }
        
        $events2=Events::getEvents($lastEventId,$lastTimestamp,'admin',1359484627);
        $this->assertArrayHasKey('events',$events2);
        $this->assertNotEmpty($events2['events']);
        $this->assertCount(1,$events2['events']);
        
        $firstEvent2=array_pop($events2['events']);
        $this->assertEquals('New social post.',$firstEvent2->text);
        if(empty($events2['events'])){
            $lastEvent=$firstEvent2;
        }else{
            $lastEvent=array_pop($events2['events']);
        }
        if($lastEvent->id > $lastEventId){
            $lastEventId=$lastEvent->id;
        }
        if($lastEvent->timestamp > $lastTimestamp){
            $lastTimestamp=$lastEvent->timestamp;
        }
        
        $events3=Events::getEvents($lastEventId,$lastTimestamp,'admin',1359485241);
        $this->assertArrayHasKey('events',$events3);
        $this->assertNotEmpty($events3['events']);
        $this->assertCount(2,$events3['events']);
        
        $firstEvent3=array_pop($events3['events']);
        $this->assertEquals('record_create',$firstEvent3->type);
        if(empty($events3['events'])){
            $lastEvent=$firstEvent3;
        }else{
            $lastEvent=array_pop($events3['events']);
        }
        if($lastEvent->id > $lastEventId){
            $lastEventId=$lastEvent->id;
        }
        if($lastEvent->timestamp > $lastTimestamp){
            $lastTimestamp=$lastEvent->timestamp;
        }
        
        $events4=Events::getEvents($lastEventId,$lastTimestamp,'admin',1359485280);
        $this->assertArrayHasKey('events',$events4);
        $this->assertNotEmpty($events4['events']);
        $this->assertCount(1,$events4['events']);
        
        $firstEvent4=array_pop($events4['events']);
        $this->assertEquals('action_reminder',$firstEvent4->type);
        if(empty($events4['events'])){
            $lastEvent=$firstEvent4;
        }else{
            $lastEvent=array_pop($events4['events']);
        }
        if($lastEvent->id > $lastEventId){
            $lastEventId=$lastEvent->id;
        }
        if($lastEvent->timestamp > $lastTimestamp){
            $lastTimestamp=$lastEvent->timestamp;
        }
        
        $events5=Events::getEvents($lastEventId,$lastTimestamp,'admin',null);
        $this->assertArrayHasKey('events',$events5);
        $this->assertEmpty($events5['events']);
        
    }
}

?>
