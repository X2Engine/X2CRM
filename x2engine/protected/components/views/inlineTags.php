<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

if($filter) { ?>
<b><?php echo Yii::t('app', 'Tags'); ?></b>
<div id="x2-inline-tags-filter" class="form">
	<?php if(is_array($tags) && count($tags)>0) {
		echo '<div id="x2-tag-list-filter" style="min-height:15px;">';
		foreach($tags as $tag)
			echo '<span class="tag link-disable"><span class="delete-tag filter">[x]</span> '.CHtml::link($tag,'#').'</span>';
		
		echo "</div>";
	} else { ?>
	<div id="x2-tag-list-filter" title="<?php echo Yii::t('contacts','Drop a tag here to filter map results.');?>" style="min-height:15px;"><?php echo Yii::t('contacts','Drop a tag here to filter map results.');?></div>
<?php } ?>
</div>    
<script>initTags();</script>
<?php 
} else {
?>
<div id="x2-inline-tags">
	<div id="x2-tag-list">
		<?php foreach($tags as $tag) {
			echo '<span class="tag"><span class="delete-tag">[x]</span> '.CHtml::link($tag['tag'],array('/search/search?term=%23'.substr($tag['tag'],1)), array('class'=>'')).'</span>';
		}?>
	</div>
</div>
<?php
	// give javascript URLs, model type, and model id
	// javascript is in /js/tags.js
	$appendTag = $this->controller->createUrl('/site/appendTag');
	$removeTag = $this->controller->createUrl('/site/removeTag');
	$searchUrl = $this->controller->createUrl('/search/search');
	
	Yii::app()->clientScript->registerScript('tags-list','
	$(function() {
		initTags();
		$("#x2-inline-tags").data("appendTagUrl", "'.$appendTag.'");
		$("#x2-inline-tags").data("removeTagUrl", "'.$removeTag.'");
		$("#x2-inline-tags").data("searchUrl", "'.$searchUrl.'");
		$("#x2-inline-tags").data("type", "'.get_class($model).'");
		$("#x2-inline-tags").data("id",'.$model->id.');
	});',CClientScript::POS_HEAD);
}