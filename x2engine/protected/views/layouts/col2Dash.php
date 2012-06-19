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
	<div class='span-4' id="dashSIDE">
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
				if(isset($this->calendarUsers)) {
					$this->beginWidget('zii.widgets.CPortlet',
						array(
							'title'=>Yii::t('calendar', 'Calendars'),
							'id'=>'calendar-users',
						)
					);
					foreach($this->calendarUsers as $userName=>$user) {
						// checkbox for each user; current user and Anyone are set to checked
						echo CHtml::checkBox($userName, (($userName == Yii::app()->user->name || $userName == '')? true: false),
							array(
								'onChange'=>"toggleCalendarSource(this.name, this.checked);", // add or remove user's actions to calendar if checked/unchecked
							)
						);
						echo $user . "<br />\n";
					}
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
	<div class='span-20' id="dashboard-box">
		<div id="contentDASH">
			<!-- content -->
			<?php echo $content; ?>
		</div>
	</div>
</div>
<?php $this->endContent(); ?>
