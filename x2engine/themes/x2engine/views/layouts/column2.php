<?php
/*********************************************************************************
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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
Yii::app()->clientScript->registerScript('menuScroling',"
if ($.browser != 'msie' || $.browser.version > 6) {
	var sidebarMenu = $('#sidebar-left');
	var sidebarTop = sidebarMenu.parent().offset().top - 5;
	var pageContainer = $('#page').find('.container:first');
	var hasScrolled = false;
	
	sidebarMenu.parent().height(sidebarMenu.height());
	
	$(window).scroll(function(event) {
		if ($(this).scrollTop() >= sidebarTop) {

			if($(this).scrollTop() + 5 + sidebarMenu.height() > pageContainer.offset().top + pageContainer.height()) {
				if(!hasScrolled)
					sidebarMenu.addClass('fixed').css('top','');
					
				if(sidebarMenu.hasClass('fixed'))
					sidebarMenu.css('top',(pageContainer.height()-sidebarMenu.height())+'px').removeClass('fixed');
					
			} else {
				sidebarMenu.addClass('fixed').css('top','');
			}
		} else {
			sidebarMenu.removeClass('fixed');
		}
		hasScrolled = true;
	});
	// $(window).scroll();
}
",CClientScript::POS_READY);
Yii::app()->clientScript->registerScript('logos',"
$(window).load(function(){
	if((!$('#main-menu-icon').length) || (!$('#x2touch-logo').length) || (!$('#x2crm-logo').length)){
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
<div class="container">
	<div class="<?php echo $showSidebars? 'span-4' : 'span-0'; ?>" id="sidebar-left-box">
		<div id="sidebar-left">
		<!-- sidebar -->
		<?php 
			if($showSidebars) {
				if(isset($this->actionMenu)) {
					$this->beginWidget('zii.widgets.CPortlet',array(
						'title'=>Yii::t('app','Actions'),
						'id'=>'actions'
					));
					
					$this->widget('zii.widgets.CMenu',array('items'=>$this->actionMenu));
					$this->endWidget();
				}
				$user = UserChild::model()->findByPk(Yii::app()->user->getId());
				$showCalendars = json_decode($user->showCalendars, true);
				$editableCalendars = Calendar::getEditableCalendarNames(); // list of calendars current user can edit
				$editableUserCalendars = Calendar::getEditableUserCalendarNames(); // list of user calendars current user can edit
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
						if(isset($editableUserCalendars[$userName])) // check if current user has permission to edit calendar
							$editable = 'true';
						else
							$editable = 'false';
						echo "<li>\n";
						// checkbox for each user calendar the current user is alowed to view
						echo CHtml::checkBox($userName, in_array($userName, $showUserCalendars),
							array(
								'onChange'=>"toggleCalendarSource(this.name, this.checked, $editable);", // add or remove user's actions to calendar if checked/unchecked
							)
						);
						echo "<label for=\"$userName\">$user</label>\n";
						echo "</li>";
					}
					echo "</ul>\n";
					$this->endWidget();
				}
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
						echo "<li>\n";
						$calendar = Calendar::model()->findByPk($calendarId);
						// checkbox for each user; current user and Anyone are set to checked
						echo CHtml::checkBox($calendarId, in_array($calendarId, $showGoogleCalendars),
							array(
								'onChange'=>"toggleCalendarSourceGoogle($calendarId, this.checked, '{$calendar->googleFeed}');", // add or remove user's actions to calendar if checked/unchecked
							)
						);
						echo "<label for=\"$calendarId\">$calendarName</label>\n";
						echo "</li>";
					}
					echo "</ul>\n";
					$this->endWidget();
				}
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
						echo "<label for=\"$filterName\">$filterDisplayName</label>";
						echo "</li>\n";
					} 
					echo "</ul>\n"; 
					$this->endWidget();
				}
				$this->widget('TopContacts',array(
					'id'=>'top-contacts'
				));
				$this->widget('RecentItems',array(
					'currentAction'=>$this->getAction()->getId(),
					'id'=>'recent-items'
				));
			}
		?>
		</div>
	</div>
	<div class="<?php echo $showSidebars? 'span-15' : 'span-19'; ?>" id="content-box">
		<div id="content">
			<!-- content -->
			<?php echo $content; ?>
		</div>
	</div>
	<div class="<?php echo $showSidebars? 'span-5' : 'span-0'; ?> last right" id="sidebar-right-box">
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
			),
			/*
			'items'=>array(
				'id1'=>'Actions',
				'id2'=>'Skype',
				'id3'=>'Location',
				'id4'=>'Quick Charts',
				), * /
				// additional javascript options for the accordion plugin
				'jQueryOptions'=>array(
					'opacity'=>0.6,	//set the dragged object's opacity to 0.6
					'handle'=>'h3',	//specify tag to be used as handle
					'distance'=>20,
					'revert'=>50,
				)*/
			)
		);		

		
		//foreach($this->portlets as $class=>$properties)
			//$this->widget($class,$properties);
		?>
		</div>
		
	</div>
</div>
<?php $this->endContent(); ?>
