<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
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
Yii::import('application.models.*');

/**
 * Test case for the model class {@link Events}.
 * @package X2CRM.tests.unit.models
 * @author Jake Houser <jake@x2engine.com>
 */
class EventsTest extends X2DbTestCase {
    
    public $fixtures=array(
        'event'=>'Events',
    );

    public static function referenceFixtures() {
        return array();
    }
    
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
