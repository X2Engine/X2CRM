<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */



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

    // javascript function togglePortletVisible defined in js/layout.js
    $minimizeLink = CHtml::ajaxLink(
        $visible ? '[&ndash;]' : '[+]', $toggleUserCalendarsVisibleUrl, 
        array(
            'success'=>'function(response) {
                togglePortletVisible($("#user-calendars"), response); 
            }'
        )
    );
    $this->beginWidget('zii.widgets.CPortlet',
        array(
            'title'=>Yii::t('calendar', 'User Calendars') . 
                '<div class="portlet-minimize">'.$minimizeLink.'</div>',
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
        echo "<label for=\"$userName\">$user</label>\n";
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


// Calendar Filters
if(isset($this->calendarFilter) && $this->calendarFilter !== null) {
    $this->beginWidget('zii.widgets.CPortlet',
        array(
            'title'=>Yii::t('calendar', 'Filter'),
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
            'contacts'=>Yii::t('calendar', 'Show Actions associated with Contacts'),
            'accounts'=>Yii::t('calendar', 'Show Actions associated with Accounts'),
            'opportunities'=>Yii::t(
                'calendar', 'Show Actions associated with Opportunities'),
            'quotes'=>Yii::t('calendar', 'Show Actions associated with Quotes'),
            'products'=>Yii::t('calendar', 'Show Actions associated with Products'),
            'media'=>Yii::t('calendar', 'Show Actions associated with Media'),
            'completed'=>Yii::t('calendar', 'Show Completed Actions'),
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
                togglePortletVisible($("#group-calendar"), response); 
            }'
        )
    ); 
    $this->beginWidget('zii.widgets.CPortlet',
            array(
                'title'=>Yii::t('calendar', 'Group Calendars') . 
                    '<div class="portlet-minimize">'.$minimizeLink.'</div>',
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
            echo "<label for=\"$groupId\">$groupName</label>\n";
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
