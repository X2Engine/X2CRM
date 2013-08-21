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

// check if we need to load a model
if(!isset($model) && isset($modelType) && isset($modelId)){
    // didn't get passed a model, but we have the modelType and modelId, so load the model
    $model = X2Model::model($modelType)->findByPk($modelId);
}
$themeUrl = Yii::app()->theme->getBaseUrl();
$relationshipCount = ""; // only used in InlineRelationships title; shows the number of relationships
if($name == "InlineRelationships"){
    $modelName = ucwords($modelType);
    $relationshipsDataProvider = new CArrayDataProvider($model->relatedX2Models, array(
                'id' => 'relationships-gridview',
                'sort' => array('attributes' => array('name', 'myModelName', 'createDate', 'assignedTo')),
                'pagination' => array('pageSize' => 10)
            ));
    $relationshipCount = " (".count($relationshipsDataProvider->data).")";
}
?>

<div class="x2-widget form" id="x2widget_<?php echo $name; ?>">
    <div class="x2widget-header" onclick="$('#x2widget_<?php echo $name; ?>').minimizeWidget(); return false">
        <span class="x2widget-title">
            <b><?php echo Yii::t('app', $widget['title']).$relationshipCount; ?></b>
        </span>
        <?php if(!Yii::app()->user->isGuest){ ?>
            <div class="portlet-minimize">
                <a onclick="$('#x2widget_<?php echo $name; ?>').minimizeWidget(); return false" href="#" class="x2widget-minimize">
					<?php 
					if ($widget['minimize']) {
						echo CHtml::image($themeUrl.'/images/icons/Expand_Widget.png', Yii::t('app', 'Maximize Widget'), 
							array ('title' => Yii::t('app', 'Maximize Widget')));
					} else {
						echo CHtml::image($themeUrl.'/images/icons/Collapse_Widget.png', Yii::t('app', 'Minimize Widget'), 
							array ('title' => Yii::t('app', 'Minimize Widget'))); 
					} 
					?>
				</a>
				<?php 
					echo CHtml::image(
						$themeUrl.'/css/gridview/arrow_both.png', 
						Yii::t('app', 'Sort Widget'), 
						array (
							'title' => Yii::t('app', 'Sort Widget'),
							'class' => 'widget-sort-handle'
						)
					); 
				?>
                <a onclick="$('#x2widget_<?php echo $name; ?>').hideWidget(); return false" href="#">
					<?php echo CHtml::image($themeUrl.'/images/icons/Close_Widget.png', Yii::t('app', 'Close Widget'), 
						array ('title' => Yii::t('app', 'Close Widget'))); ?>
				</a>
            </div>
        <?php } ?>
    </div>
    <div class="x2widget-container" style="<?php echo $widget['minimize'] ? 'display: none;' : ''; ?>">
        <?php if(isset($this->controller)){ // not ajax  ?>
            <?php $this->render('x2widget', array('widget' => $widget, 'name' => $name, 'model' => $model, 'modelType' => $modelType)); ?>
        <?php }else{ // we are in an ajax call ?>
            <?php $this->renderPartial('application.components.views.x2widget', array('widget' => $widget, 'name' => $name, 'model' => $model, 'modelType' => $modelType)); ?>
        <?php } ?>
    </div>
</div>
