<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */
Yii::import('application.models.*');

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
