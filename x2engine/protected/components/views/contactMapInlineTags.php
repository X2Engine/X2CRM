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




if(empty($tags))
    $tags = array();

Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2Tags/TagContainer.js', CClientScript::POS_BEGIN);
Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2Tags/MapTagsContainer.js', CClientScript::POS_BEGIN);
?>
<b><?php echo Yii::t('app', 'Tags'); ?></b>
<div id="x2-tags-container" class="form">
    <?php
		echo '<div class="x2-tag-list" style="min-height:15px;">';
		foreach($tags as $tag) {
			echo '<span class="tag link-disable"><span class="delete-tag filter">[x]</span>'.
                    CHtml::link(CHtml::encode ($tag),'#').
                '</span>';
    } 
    ?>
    <span class='tag-container-placeholder' 
     <?php echo (sizeof ($tags) > 0 ? 'style="display: none;"' : ''); ?>>
        <?php echo Yii::t('contacts','Drop a tag here to filter map results.');?>
    </span>
    <?php
		echo "</div>";
    ?>
</div>    
<?php
	Yii::app()->clientScript->registerScript('tags-list','
	$(document).on ("ready", function () {
        new MapTagsContainer ({
            containerSelector: "#x2-tags-container .x2-tag-list"
        }); 
	});',CClientScript::POS_HEAD);
