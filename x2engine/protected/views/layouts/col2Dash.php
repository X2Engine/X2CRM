<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

$this->beginContent('//layouts/main2');
$themeURL = Yii::app()->theme->getBaseUrl();

Yii::app()->clientScript->registerScript('logos',base64_decode(
	'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
	.'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
	.'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));

$showSidebars = Yii::app()->controller->id!='admin' && Yii::app()->controller->id!='site' || Yii::app()->controller->action->id=='whatsNew';

?>



<div id="sidebar-left-container">

	<div id="sidebar-left">
	<!-- sidebar -->
	<?php 
		if(isset($this->actionMenu)) {
			$this->beginWidget('zii.widgets.CPortlet',array(
				'title'=>Yii::t('app','Actions'),
				'id'=>'actions'
			));
			
			$this->widget('zii.widgets.CMenu',array('items'=>$this->actionMenu));
			$this->endWidget();
		}
		if(isset($this->modelClass) && $this->modelClass == 'Actions' && $this->showActions !== null) {
			$this->beginWidget('zii.widgets.CPortlet', array(
				'title'=>Yii::t('actions', 'Show Actions'),
				'id'=>'actions-filter',
			));
			echo '<div class="form" style="border: none;">';
			echo CHtml::dropDownList('show-actions', $this->showActions,
				array(
					'uncomplete'=>Yii::t('actions', 'Uncomplete'),
					'complete'=>Yii::t('actions', 'Complete'),
					'all'=>Yii::t('actions', 'All'),
				),
				array(
					'id'=>'dropdown-show-actions',
					'onChange'=>'toggleShowActions();',
				)
			);
			echo '</div>';
			$this->endWidget();
		}
		if(isset($this->modelClass) && $this->modelClass == 'Calendar') {
			$user = UserChild::model()->findByPk(Yii::app()->user->getId());
			$showCalendars = json_decode($user->showCalendars, true);
//			$editableCalendars = X2Calendar::getEditableCalendarNames(); // list of calendars current user can edit
			$editableUserCalendars = X2CalendarPermissions::getEditableUserCalendarNames(); // list of user calendars current user can edit
			if(isset($this->groupCalendars) && $this->groupCalendars !== null) {
			$this->beginWidget('zii.widgets.CPortlet',
					array(
						'title'=>Yii::t('calendar', 'Group Calendars'),
						'id'=>'group-calendar',
					)
				);
				$showGroupCalendars = $showCalendars['groupCalendars'];
				echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
				foreach($this->groupCalendars as $groupId=>$groupName) {
					echo "<li>\n";
					// checkbox for each user; current user and Anyone are set to checked
					echo CHtml::checkBox($groupId, in_array($groupId, $showGroupCalendars),
						array(
							'onChange'=>"toggleGroupCalendarSource(this.name, this.checked);", // add or remove group calendar actions to calendar if checked/unchecked
						)
					);
					echo "<label for=\"$groupId\">$groupName</label>\n";
					echo "</li>";
				}
				echo "</ul>\n";
				$this->endWidget();
			}
			/*
			if(isset($this->sharedCalendars) && $this->sharedCalendars !== null) {
				$this->beginWidget('zii.widgets.CPortlet',
					array(
						'title'=>Yii::t('calendar', 'Calendars'),
						'id'=>'shared-calendar',
					)
				);
				$showSharedCalendars = $showCalendars['sharedCalendars'];
				echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
				foreach($this->sharedCalendars as $calendarId=>$calendarName) {
					if(isset($editableCalendars[$calendarId])) // check if current user has permission to edit calendar
						$editable = 'true';
					else
						$editable = 'false';
					echo "<li>\n";
					// checkbox for each user; current user and Anyone are set to checked
					echo CHtml::checkBox($calendarId, in_array($calendarId, $showSharedCalendars),
						array(
							'onChange'=>"toggleCalendarSourceShared(this.name, this.checked, $editable);", // add or remove shared calendar actions to calendar if checked/unchecked
						)
					);
					echo "<label for=\"$calendarId\">$calendarName</label>\n";
					echo "</li>";
				}
				echo "</ul>\n";
				$this->endWidget();
			}
			*/
			if(isset($this->calendarUsers) && $this->calendarUsers !== null) {
				$this->beginWidget('zii.widgets.CPortlet',
					array(
						'title'=>Yii::t('calendar', 'User Calendars'),
						'id'=>'user-calendars',
					)
				);
				$showUserCalendars = $showCalendars['userCalendars'];
				echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
				foreach($this->calendarUsers as $userName=>$user) {
					if($user=='Anyone'){
						$user=Yii::t('app',$user);
					}
					if(isset($editableUserCalendars[$userName])) // check if current user has permission to edit calendar
						$editable = 'true';
					else
						$editable = 'false';
					echo "<li>\n";
					// checkbox for each user calendar the current user is alowed to view
					echo CHtml::checkBox($userName, in_array($userName, $showUserCalendars),
						array(
							'onChange'=>"toggleUserCalendarSource(this.name, this.checked, $editable);", // add or remove user's actions to calendar if checked/unchecked
						)
					);
					echo "<label for=\"$userName\">$user</label>\n";
					echo "</li>";
				}
				echo "</ul>\n";
				$this->endWidget();
			}
			/*
			if(isset($this->googleCalendars) && $this->googleCalendars !== null) {
				$this->beginWidget('zii.widgets.CPortlet',
					array(
						'title'=>Yii::t('calendar', 'Google Calendars'),
						'id'=>'google-calendars',
					)
				);
				$showGoogleCalendars = $showCalendars['googleCalendars'];
				echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
				foreach($this->googleCalendars as $calendarId=>$calendarName) {
					if(isset($editableCalendars[$calendarId])) // check if current user has permission to edit calendar
						$editable = 'true';
					else
						$editable = 'false';
					echo "<li>\n";
					$calendar = X2Calendar::model()->findByPk($calendarId);
					// checkbox for each user; current user and Anyone are set to checked
					if($calendar->googleCalendarId) // read/write google calendar
						echo CHtml::checkBox($calendarId, in_array($calendarId, $showGoogleCalendars),
							array(
								'onChange'=>"toggleCalendarSourceGoogle($calendarId, this.checked, $editable);", // add or remove user's actions to calendar if checked/unchecked
							)
						);
					else // read only google calendar feed
						echo CHtml::checkBox($calendarId, in_array($calendarId, $showGoogleCalendars),
							array(
								'onChange'=>"toggleCalendarSourceGoogleFeed($calendarId, this.checked, '{$calendar->googleFeed}');", // add or remove user's actions to calendar if checked/unchecked
							)
						);
					echo "<label for=\"$calendarId\">$calendarName</label>\n";
					echo "</li>";
				}
				echo "</ul>\n";
				$this->endWidget();
			}
			*/
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
					echo CHtml::checkBox($filterName, $filter,
						array(
							'onChange'=>"toggleCalendarFilter('$filterName', $checked);", // add/remove filter if checked/unchecked
						)
					);
					$filterDisplayName = ucwords($filterName); // capitalize filter name for label
					echo "<label for=\"$filterName\">".Yii::t('calendar',$filterDisplayName)."</label>";
					echo "</li>\n";
				} 
				echo "</ul>\n"; 
				$this->endWidget();
			}
		}
		$this->widget('TopContacts',array(
			'id'=>'top-contacts'
		));
		$this->widget('RecentItems',array(
			'currentAction'=>$this->getAction()->getId(),
			'id'=>'recent-items'
		));
		?>
	</div>
</div>

<div id="flexible-content">
	<div id="content-container">
		<div id="content">
			<!-- content -->
			<?php echo $content; ?>
		</div>
	</div>
</div>

<?php $this->endContent(); ?>
