<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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
 ********************************************************************************/

$this->beginContent('//layouts/main');
$themeURL = Yii::app()->theme->getBaseUrl();

Yii::app()->clientScript->registerScript('logos',"
$(window).load(function(){
	if((!$('#x2touch-logo').length) || (!$('#x2crm-logo').length)){
		$('a').removeAttr('href');
		alert('Please put the logo back');
		window.location='http://www.x2engine.com';
	}
	var touchlogosrc = $('#x2touch-logo').attr('src');
	var logosrc=$('#x2crm-logo').attr('src');
	if(logosrc!='$themeURL/images/x2footer.png'|| touchlogosrc!='$themeURL/images/x2touch.png'){
		$('a').removeAttr('href');
		alert('Please put the logo back');
		window.location='http://www.x2engine.com';
	}
});
");

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
			
			$this->widget('zii.widgets.CMenu',array('items'=>$this->actionMenu,'encodeLabel'=>false));
			$this->endWidget();
		}
		if(isset($this->modelClass) && $this->modelClass == 'Actions' && $this->showActions !== null) {
			$this->beginWidget('zii.widgets.CPortlet', array(
				'title'=>Yii::t('actions', 'Show Actions'),
				'id'=>'actions-filter',
			));
			echo '<div class="form no-border" style="text-align:center;">';
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
		
		// Add Show/Hide Status for Service Cases
		// This is a list of service case statuses. When the User checks one of the corresponding checkboxes
		// the status will be hidden in the gridview in services/index
		if(isset($this->modelClass) && $this->modelClass == 'Services' && isset($this->serviceCaseStatuses) && $this->serviceCaseStatuses != null) {
			$hideStatus = CJSON::decode(Yii::app()->params->profile->hideCasesWithStatus); // get a list of statuses the user wants to hide
			if(!$hideStatus) {
				$hideStatus = array();
			}

			$this->beginWidget('zii.widgets.CPortlet',
			    array(
			    	'title'=>Yii::t('services', 'Filter By Status'),
			    	'id'=>'service-case-status-filter',
			    )
			);			

			echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
			$i = 1;
			foreach($this->serviceCaseStatuses as $status) {
				echo "<li>\n";
				echo CHtml::checkBox("service-case-status-filter-$i", !in_array($status, $hideStatus),
				    array(
				    	'id'=>"service-case-status-filter-$i",
		//		    	'onChange'=>"toggleUserCalendarSource(this.name, this.checked, $editable);", // add or remove user's actions to calendar if checked/unchecked
						'ajax' => array(
							'type' => 'POST', //request type
							'url' => Yii::app()->controller->createUrl('/services/statusFilter'), //url to call
							'success' => 'js:function(response) { $.fn.yiiGridView.update("services-grid"); }', //selector to update
							'data' => 'js:{checked: $(this).attr("checked")=="checked", status:"'.$status.'"}',
							// check / uncheck the checkbox after the ajax call
							'complete'=>'function(){
								if($("#service-case-status-filter-'.$i.'").attr("checked")=="checked") {
									$("#service-case-status-filter-'.$i.'").removeAttr("checked","checked");
								} else {
									$("#service-case-status-filter-'.$i.'").attr("checked","checked");
								}
							}'
						)
				    )
				);
				echo CHtml::label(CHtml::encode($status), "service-case-status-filter-$i");
				echo "</li>";
				$i++;
			}
			echo "</ul>\n";
			$this->endWidget();
		}
		
		if(isset($this->modelClass) && $this->modelClass == 'Calendar') {
			$user = UserChild::model()->findByPk(Yii::app()->user->getId());
			$showCalendars = json_decode($user->showCalendars, true);
//			$editableCalendars = X2Calendar::getEditableCalendarNames(); // list of calendars current user can edit
			$editableUserCalendars = X2CalendarPermissions::getEditableUserCalendarNames(); // list of user calendars current user can edit
			
			// User Calendars
			if(isset($this->calendarUsers) && $this->calendarUsers !== null) {
				$toggleUserCalendarsVisibleUrl = $this->createUrl('togglePortletVisible', array('portlet'=>'userCalendars')); // actionTogglePortletVisible is defined in calendar controller
				$visible = Yii::app()->params->profile->userCalendarsVisible;
				$minimizeLink = CHtml::ajaxLink($visible? '[&ndash;]' : '[+]', $toggleUserCalendarsVisibleUrl, array('success'=>'function(response) { togglePortletVisible($("#user-calendars"), response); }')); // javascript function togglePortletVisible defined in js/layout.js
				$this->beginWidget('zii.widgets.CPortlet',
					array(
						'title'=>Yii::t('calendar', 'User Calendars') . '<div class="portlet-minimize">'.$minimizeLink.'</div>',
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
						'opportunities'=>Yii::t('calendar', 'Show Actions associated with Opportunities'),
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
							'onChange'=>"toggleCalendarFilter('$filterName', $checked);", // add/remove filter if checked/unchecked
							'title'=>$title,
							'class'=>$class,
						)
					);
					$filterDisplayName = ucwords($filterName); // capitalize filter name for label
					echo "<label for=\"$filterName\" class=\"$class\" title=\"$title\">".Yii::t('calendar',$filterDisplayName)."</label>";
					echo "</li>\n";
				} 
				echo "</ul>\n"; 
				$this->endWidget();
			}

			// Group Calendars
			if(isset($this->groupCalendars) && $this->groupCalendars !== null) {
				$toggleGroupCalendarsVisibleUrl = $this->createUrl('togglePortletVisible', array('portlet'=>'groupCalendars')); // actionTogglePortletVisible is defined in calendar controller
				$visible = Yii::app()->params->profile->groupCalendarsVisible;
				$minimizeLink = CHtml::ajaxLink($visible? '[&ndash;]' : '[+]', $toggleGroupCalendarsVisibleUrl, array('success'=>'function(response) { togglePortletVisible($("#group-calendar"), response); }')); // javascript function togglePortletVisible defined in js/layout.js
				$this->beginWidget('zii.widgets.CPortlet',
						array(
							'title'=>Yii::t('calendar', 'Group Calendars') . '<div class="portlet-minimize">'.$minimizeLink.'</div>',
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
					if(!$visible) {
							Yii::app()->clientScript->registerScript('hideGroupCalendars', "
								$(function() {
									$('#group-calendar .portlet-content').hide();
							});",CClientScript::POS_HEAD);
					}
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
	<div id="sidebar-right">
		<?php
		$this->widget('SortableWidgets', array(
			//list of items
			'portlets'=>$this->portlets, 
			'jQueryOptions'=>array(
				'opacity'=>0.6,	//set the dragged object's opacity to 0.6
				'handle'=>'.portlet-decoration',	//specify tag to be used as handle
				'distance'=>20,
				'delay'=>150,
				'revert'=>50,
				'update'=>"js:function(){
					$.ajax({
							type: 'POST',
							url: '{$this->createUrl('/site/widgetOrder')}',
							data: $(this).sortable('serialize'),
					});
				}"
			)
		));
		?>
	</div>
	<div id="content-container">
		<div id="content">
			<!-- content -->
			<?php echo $content; ?>
		</div>
	</div>
</div>

<?php $this->endContent(); ?>