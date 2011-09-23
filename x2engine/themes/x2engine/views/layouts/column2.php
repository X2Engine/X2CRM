<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$this->beginContent('/layouts/main'); ?>
<div class="container">
	<?php if(Yii::app()->controller->id!='admin' && Yii::app()->controller->id!='site' || Yii::app()->controller->action->id=='whatsNew'){ ?>
	<div class="span-4">
		<?php }else{ ?>
	<div class="span-0">
		<?php } ?>
		<div id="sidebar-left">
		<!-- sidebar -->
		<?php 
			if(Yii::app()->controller->id!='admin' && Yii::app()->controller->id!='site' || Yii::app()->controller->action->id=='whatsNew'){
				$this->beginWidget('zii.widgets.CPortlet',array(
					'title'=>Yii::t('app','Actions'),
					'id'=>'actions'
				));
				$this->widget('zii.widgets.CMenu',array(
					'items'=>$this->menu,
				));
				$this->endWidget();

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
	<?php if(Yii::app()->controller->id!='admin' && Yii::app()->controller->id!='site' || Yii::app()->controller->action->id=='whatsNew'){ ?>
	<div class="span-15">
		<?php }else{ ?>
	<div class="span-19">
		<?php } ?>
		<div id="content">
		<!-- content -->
		<?php echo $content; ?>
		</div>
	</div>
	<div class="span-5 last">
		<div id="sidebar-right">
		<?php
		
		$this->widget('SortableWidgets', array(
			//list of items
			'portlets'=>$this->portlets, 
			'jQueryOptions'=>array(
				'opacity'=>0.6,	//set the dragged object's opacity to 0.6
				'handle'=>'.portlet-decoration',	//specify tag to be used as handle
				'distance'=>10,
				'delay'=>250,
				'revert'=>50,
				'update'=>"js:function(){
					$.ajax({
							type: 'POST',
							url: '{$this->createUrl('site/widgetOrder')}',
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
