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
 * Tests for CronEvent
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CronEventTest extends X2TestCase {

    public function testRecur() {
        // Expected use: "time" should never be in the future when this method
        // runs.
        $e = new CronEvent;
        $e->interval = 5;
        // Scenario:
        // Plain and simple. Event was less than one interval into the past.
        // This is the expected use most of the time.
        // Expected: add one interval
        $now = time();
        $time = $now-1;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time + $e->interval, $e->time);
        $this->assertEquals($now,$e->lastExecution);
        
        // Scenario:
        // Event was more than one interval into the past.
        // Expected: add two intervals
        $now = time();
        $time = $now-($e->interval+1);
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time + 2*$e->interval,$e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was exactly one interval into the past.
        // Expected: add two intervals.
        $now = time();
        $time = $now-$e->interval;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time + 2*$e->interval,$e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was right now.
        // Expected: add one interval.
        $now = time();
        $time = $now;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time+$e->interval,$e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was multiple intervals into the past and then some.
        // Expected: add as many intervals as necessary to put the execution
        // time ahead of now.
        $now = time();
        $time = $now - (10*$e->interval) - 2;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($now + $e->interval - 2, $e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was a round multiple of intervals into the past.
        // Expected: add that number of intervals plus one.
        $now = time();
        $time = $now - 11*$e->interval;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($now + $e->interval, $e->time);
        $this->assertEquals($now,$e->lastExecution);
    }

}

?>
