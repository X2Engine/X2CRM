<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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



$user = X2Model::model('User')->findByPk(Yii::app()->user->getId());
$showCalendars = json_decode($user->showCalendars, true);

// list of user calendars current user can edit
$editableUserCalendars = X2CalendarPermissions::getEditableUserCalendarNames(); 

// User Calendars
if(isset($this->calendarUsers) && $this->calendarUsers !== null) {

    // actionTogglePortletVisible is defined in calendar controller
    $toggleUserCalendarsVisibleUrl = 
        $this->createUrl('togglePortletVisible', array('portlet'=>'userCalendars')); 
    $visible = Yii::app()->params->profile->userCalendarsVisible;

    $this->beginWidget('LeftWidget',
        array(
            'widgetLabel'=>Yii::t('calendar', 'User {calendars}', array(
                '{calendars}' => Modules::displayName()."s",
            )),
            'widgetName' => 'UserCalendars',
            'id'=>'user-calendars',
        )
    );

    $showUserCalendars = $showCalendars['userCalendars'];
    echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
    foreach($this->calendarUsers as $userName=>$user) {
        if($user=='Anyone'){
            $user=Yii::t('app',$user);
        }
        // check if current user has permission to edit calendar
        if(isset($editableUserCalendars[$userName])) {
            $editable = 'true';
        } else {
            $editable = 'false';
        }
        echo "<li>\n";
        // checkbox for each user calendar the current user is alowed to view
        echo CHtml::checkBox($userName, in_array($userName, $showUserCalendars),
            array(
                // add or remove user's actions to calendar if checked/unchecked
                'onChange'=>"toggleUserCalendarSource(
                    this.name, this.checked, $editable);", 
            )
        );
        echo "<label for=\"$userName\">".CHtml::encode ($user)."</label>\n";
        echo "</li>";
    }
    echo "</ul>\n";
    $this->endWidget();
    if(!$visible) {
            Yii::app()->clientScript->registerScript('hideUserCalendars', "
                $(function() {
                    $('#user-calendars .portlet-content').hide();
            });",CClientScript::POS_HEAD);
    }
}

$modTitles = array();
foreach (array("Actions", "Contacts", "Accounts", "Products", "Quotes",
               "Media", "Opportunities") as $mod) {
    $modTitles[strtolower($mod)] = Modules::displayName(true, $mod);
}

// Calendar Filters
if(isset($this->calendarFilter) && $this->calendarFilter !== null) {
    $modTitles = array(
        'Accounts' => Modules::displayName (true, 'Accounts'),
        'Actions' => Modules::displayName (true, 'Actions'),
        'Contacts' => Modules::displayName (true, 'Contacts'),
        'Media' => Modules::displayName (true, 'Media'),
        'Opportunities' => Modules::displayName (true, 'Opportunities'),
        'Products' => Modules::displayName (true, 'Products'),
        'Quotes' => Modules::displayName (true, 'Quotes'),
    );
    $this->beginWidget('LeftWidget',
        array(
            'widgetLabel'=>Yii::t('calendar', 'Filter'),
            'widgetName' => 'CalendarFilter',
            'id'=>'calendar-filter',
        )
    );
    echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
    foreach($this->calendarFilter as $filterName=>$filter) {
        echo "<li>\n";
        if($filter)
            $checked = 'true';
        else
            $checked = 'false';
        $title = '';
        $class = '';
        $titles = array(
            'contacts'=>Yii::t('calendar', 'Show {actions} associated with {contacts}', array(
                '{actions}' => $modTitles["Actions"],
                '{contacts}' => $modTitles["Contacts"],
            )),
            'accounts'=>Yii::t('calendar', 'Show {actions} associated with {accounts}', array(
                '{actions}' => $modTitles["Actions"],
                '{accounts}' => $modTitles["Accounts"],
            )),
            'opportunities'=>Yii::t('calendar', 'Show {actions} associated with {opportunities}', array(
                '{actions}' => $modTitles["Actions"],
                '{opportunities}' => $modTitles["Opportunities"],
            )),
            'products'=>Yii::t('calendar', 'Show {actions} associated with {products}', array(
                '{actions}' => $modTitles["Actions"],
                '{products}' => $modTitles["Products"],
            )),
            'media'=>Yii::t('calendar', 'Show {actions} associated with {media}', array(
                '{actions}' => $modTitles["Actions"],
                '{media}' => $modTitles["Media"],
            )),
            'completed'=>Yii::t('calendar', 'Show Completed {actions}', array(
                '{actions}' => $modTitles["Actions"],
            )),
            'email'=>Yii::t('calendar', 'Show Emails'),
            'attachment'=>Yii::t('calendar', 'Show Attachments'),
        );
        if(isset($titles[$filterName])) {
            $title = $titles[$filterName];
            $class = 'x2-info';
        }
        echo CHtml::checkBox($filterName, $filter,
            array(
                // add/remove filter if checked/unchecked
                'onChange'=>"toggleCalendarFilter('$filterName', $checked);", 
                'title'=>$title,
                'class'=>$class,
            )
        );
        $filterDisplayName = ucwords($filterName); // capitalize filter name for label
        echo "<label for=\"$filterName\" class=\"$class\" title=\"$title\">".
            Yii::t('calendar',$filterDisplayName)."</label>";
        echo "</li>\n";
    }
    echo "</ul>\n";
    $this->endWidget();
}

// Group Calendars
if(isset($this->groupCalendars) && $this->groupCalendars !== null) {
   
    // actionTogglePortletVisible is defined in calendar controller
    $toggleGroupCalendarsVisibleUrl = 
        $this->createUrl(
            'togglePortletVisible', array('portlet'=>'groupCalendars')); 
    $visible = Yii::app()->params->profile->groupCalendarsVisible;
    $minimizeLink = CHtml::ajaxLink(
        $visible? '[&ndash;]' : '[+]', 
        $toggleGroupCalendarsVisibleUrl, 
        // javascript function togglePortletVisible defined in js/layout.js
        array(
            'success'=>'function(response) { 
                x2.LayoutManager.togglePortletVisible($("#group-calendar"), response); 
            }'
        )
    ); 
    $this->beginWidget('LeftWidget',
            array(
                'widgetLabel'=>Yii::t('calendar', 'Group {calendars}', array(
                    '{calendars}' => Modules::displayName()."s",
                )),
                'widgetName' => 'GroupCalendars',
                'id'=>'group-calendar',
            )
        );
        $showGroupCalendars = $showCalendars['groupCalendars'];
        echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
        foreach($this->groupCalendars as $groupId=>$groupName) {
            echo "<li>\n";
            // checkbox for each user; current user and Anyone are set to checked
            echo CHtml::checkBox($groupId, in_array($groupId, $showGroupCalendars),
                // add or remove group calendar actions to calendar if checked/unchecked
                array(
                    'onChange'=>"toggleGroupCalendarSource(this.name, this.checked);", 
                )
            );
            echo "<label for=\"$groupId\">".CHtml::encode($groupName)."</label>\n";
            echo "</li>";
        }
        echo "</ul>\n";
        $this->endWidget();
        if(!$visible) {
                Yii::app()->clientScript->registerScript('hideGroupCalendars', "
                    $(function() {
                        $('#group-calendar .portlet-content').hide();
                });",CClientScript::POS_HEAD);
        }
}

