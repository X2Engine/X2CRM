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

    $showUserCalendars = isset($showCalendars['userCalendars']) ? $showCalendars['userCalendars'] : array();
    //$showUserCalendars = $showCalendars['userCalendars'];
    echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
    foreach($this->calendarUsers as $userName=>$user) {
        // check if current user has permission to edit calendar
        if(isset($editableUserCalendars[$userName])) {
            $editable = 'true';
        } else {
            $editable = 'false';
        }
        echo "<li class='user-calendar-entry'>\n<div class='calendar-checkbox'>";
        // checkbox for each user calendar the current user is alowed to view
        echo CHtml::checkBox($userName, in_array($userName, $showUserCalendars),
            array(
                // add or remove user's actions to calendar if checked/unchecked
                'onChange'=>"toggleUserCalendarSource(
                    this.name, this.checked, $editable);", 
            )
        );
        echo "</div>";
        echo "<div class='calendar-name'><label for=\"$userName\">".CHtml::encode ($user)."</label></div>\n";
        if($editable==='true'){
            echo "<div class='calendar-edit-button'>";
            echo CHtml::link(
                '', $this->createUrl('update', array('id' => $userName)),
                array(
                    'class' => 'x2-button icon edit minimal',
                )
            );
            echo "</div>";
        }
        
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


if ($this->action->id === 'index') {
    $this->beginWidget('leftWidget',array(
        'widgetLabel'=>Yii::t('calendar','Export {calendar}', array('{calendar}'=>Modules::displayName())),
        'widgetName' => 'IcalExportUrl',
        'id'=>'ical-export-url',
    ));
    echo '<input type="text" class="x2-textfield" name="ical-export-url-field" id="ical-export-url-field" style="width:50%;display:inline-block;"></input>&nbsp;';
    echo '<a id="ical-export-url-link" href="#">['.Yii::t('calendar','link').']</a>&nbsp;';
    echo X2Html::hint(Yii::t('admin',"This link is to a special URL that displays the current {calendar} in ICS format. It is useful for setting up the {calendar} in third-party programs such as Apple iCal.", array(
            '{calendar}' => lcfirst(Modules::displayName()),
    )),false,null,true); // text, superscript, id,brackets, encode
    $this->endWidget();
}

