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
 * Component class to render calendar actions in the
 * iCal format, see RFC5545: http://tools.ietf.org/html/rfc5545
 * Usage: Load up the relevent actions into an Ical object,
 * and then call render.
 *    $ical = new Ical;
 *    $ical->setActions($actions);
 *    $ical->render();
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class Ical {
    private $_actions;

    public function setActions($actions) {
        if (is_array($actions))
            $this->_actions = $actions;
    }
    
    public function getActions() {
        return $this->_actions;
    }
    
    /**
     * Escape the following characters: ,'";\ and \n
     * according to the RFC spec
     */
    public function escapeText($str) {
        $str = preg_replace('/([,\'";\\\\])/', "\\\\$1", $str);
        return preg_replace('/\n/', '\\\\n', $str);
    }

    /**
     * Writes output for the set $_actions in the iCal format
     */
    public function render() {
        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//X2Engine//NONSGML X2 Calendar//EN\r\n";
        
        if (is_array($this->_actions)) {
            $actionsPrinted = array();
            $users = array();
            foreach($this->_actions as $action) {
                // skip events showing up more than once (i.e. combined cal)
                if(!($action instanceof Actions) || !empty($actionsPrinted[$action->id])) 
                    continue;
                // Render a VEVENT for each action
                echo "BEGIN:VEVENT\r\n";
                // Treat the first assignee as the one to use for generating the "organizer" 
                // and all of the assignees for the unique ID field
                $assignees = $action->getAssignees();
                if(!empty($assignees)) {
                    $first = reset($assignees);
                    echo "UID:".$action->assignedTo."-".$action->id."@".$_SERVER['SERVER_NAME']."\r\n";
                                   
                    if (!empty($first) && !array_key_exists($first, $users)) {
                        // cache user emails
                        $user = User::model()->findByAttributes(array('username'=>$first));
                        $users[$first] = array(
                            'name'=>$user->name, 
                            'email'=>$user->emailAddress,
                            'timeZone'=>$user->profile->timeZone
                        );
                    }
                    $tz = $users[$first]['timeZone'];
                    echo "ORGANIZER;CN=\"".$users[$first]['name']."\":mailto:".$users[$first]['email']."\r\n";
                } else {
                    // Default app timezone (which is actually stored as the column default value)
                    $tz = Profile::model()->tableSchema->columns['timeZone']->defaultValue;
                    echo "UID:Anyone-{$action->id}@{$_SERVER['SERVER_NAME']}\r\n";
                }
                $start = new DateTime();
                $end = new DateTime();
                $tzOb = new DateTimeZone($tz);
                $start->setTimestamp($action->dueDate);
                $end->setTimestamp($action->completeDate);
                $start->setTimezone($tzOb);
                $end->setTimezone($tzOb);
                echo "DTSTART;TZID=$tz:".$start->format('Ymd\THis')."\r\n";
                echo "DTEND;TZID=$tz:".$end->format('Ymd\THis')."\r\n";
                echo "SUMMARY:".self::escapeText($action->actionText->text)."\r\n";
                echo "END:VEVENT\r\n";
                $actionsPrinted[$action->id] = true;
            }
        }

        echo "END:VCALENDAR\r\n";
    }
}
