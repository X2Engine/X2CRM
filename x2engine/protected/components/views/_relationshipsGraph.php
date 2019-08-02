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






$neighborData = RelationshipsGraph::getNeighborData ($this->model);

Yii::app()->clientScript->registerScript('RelationshipsGraphJS',"

$(function () {
    x2.relationshipsGraph = new x2.RelationshipsGraph ({
        element: '#relationships-graph-container',
        nodes: ".CJSON::encode ($nodes).",
        edges: ".CJSON::encode ($edges).",
        nodeUidToIndex: ".CJSON::encode ($metaData['nodeUidToIndex']).",
        nodeUidsToEdgeIndex: ".CJSON::encode ($metaData['nodeUidsToEdgeIndex']).",
        adjacencyDictionary: ".CJSON::encode ($metaData['adjacencyArray']).",
        colors: ".CJSON::encode ($colorByType).",
        initialFocus: ['".get_class ($this->model)."', ".$this->model->id."],
        initialNeighborData: ".CJSON::encode ($neighborData).",
        inline: ".CJSON::encode ($this->inline).",
        translations: {
            duplicateRecordError: '".CHtml::encode (
                Yii::t('app', 'Record is already in the graph.'))."'
        }
    });
});
    
", CClientScript::POS_END);

if (!$this->inline) {
?>
<div id='hints-show-button' class='fa fa-question-circle'
 title='<?php echo CHtml::encode (Yii::t('app', 'Show hints')); ?>' style='display: none;'></div>
<div id='relationships-graph-toolbar'>
    <div class='record-detail-box toolbar-box'>
    <?php
        $this->render ('_relationshipsGraphRecordDetails', array (
            'model' => $this->model,
            'neighborData' => $neighborData,
        ));
    ?>
    </div>
    <div class='graph-hints-box toolbar-box'>
        <h2 class='hints-title'><?php echo CHtml::encode (Yii::t('app', 'Hints:')); ?></h2>
        <span class='fa fa-times hints-close-button' 
         title='<?php echo CHtml::encode (Yii::t('app', 'Close')); ?>'>
        </span>
        <div class='clearfix'></div>
        <ul>
        <?php
            foreach ($this->getHints () as $hint) {
                echo '<li>'.CHtml::encode ($hint).'</li>';
            }
        ?>
        </ul>
    </div>
    <div class='toolbar-spacer'></div>
    <div class='toolbar-box add-node-box'>
        <?php
        $this->widget ('MultiTypeAutocomplete', array (
            'options' => $linkableModelsOptions,
            'htmlOptions' => array (
                'class' => 'all-form-input-style',
            )
        ));
        ?>
        <button class='add-node-button x2-button' 
         title='<?php echo CHtml::encode (Yii::t('app', 'Add a new node to the graph'))  ?>'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Add node'));
        ?>
        </button>
    </div>
    <button class='stop-animation-button x2-button' style='display: none;'>
    <?php
        echo CHtml::encode (Yii::t('app', 'Stop simulation'));
    ?>
    </button>
    <button class='start-animation-button x2-button highlight'>
    <?php
        echo CHtml::encode (Yii::t('app', 'Start simulation'));
    ?>
    </button>
    <div class='button-container'>
        <button class='connect-nodes-button x2-button disabled' 
         title='<?php echo CHtml::encode (Yii::t('app', 'Connect 2-4 nodes'))  ?>'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Connect nodes'));
        ?>
        </button>
        <button class='delete-edges-button x2-button disabled'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Delete edges'));
        ?>
        </button>
        <button class='show-labels-button x2-button' style='display: none;'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Show labels'));
        ?>
        </button>
        <button class='hide-labels-button x2-button'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Hide labels'));
        ?>
        </button>
        <button class='label-all-button x2-button'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Label all'));
        ?>
        </button>
        <button class='label-active-button x2-button' style='display: none;'>
        <?php
            echo CHtml::encode (Yii::t('app', 'Label active'));
        ?>
        </button>
    </div>
    <div class='graph-legend toolbar-box'>
        <ul>
        <?php
        foreach (array_keys ($metaData['types']) as $type) {
            $color = $colorByType[$type];
            $title = X2Model::getModelTitle ($type);
            echo '<li data-class="'.$type.'">';
            echo '<span class="legend-swatch" 
                   style="background-color: '.$color.';"></span>';
            echo '<span class="legend-label">
                '.CHtml::encode ($title).'
                </span>';
            echo '</li>';
        }
        ?>
        </ul>
    </div>
    <div id='graph-nav-controls' class='no-selection'>
        <div class='panning-buttons-container'>
            <span class='pan-up-button fa fa-lg fa-chevron-up icon-button'
             title='<?php echo CHtml::encode (Yii::t('app', 'Pan up')); ?>'></span>
            <span class='pan-right-button fa fa-lg fa-chevron-right icon-button'
             title='<?php echo CHtml::encode (Yii::t('app', 'Pan right')); ?>'></span>
            <span class='pan-down-button fa fa-lg fa-chevron-down icon-button'
             title='<?php echo CHtml::encode (Yii::t('app', 'Pan down')); ?>'></span>
            <span class='pan-left-button fa fa-lg fa-chevron-left icon-button'
             title='<?php echo CHtml::encode (Yii::t('app', 'Pan left')); ?>'></span>
        </div>
        <div class='zoom-buttons-container'>
            <span class='zoom-in-button fa fa-lg fa-search-plus icon-button'
             title='<?php echo CHtml::encode (Yii::t('app', 'Zoom in')); ?>'></span>
            <span class='zoom-out-button fa fa-lg fa-search-minus icon-button'
             title='<?php echo CHtml::encode (Yii::t('app', 'Zoom out')); ?>'></span>
        </div>
    </div>
</div>
<?php
}
?>
<div id='relationships-graph-container' 
 style='<?php echo ($this->height ? 'height: '.$this->height.'px;' : ''); ?>'
 class='<?php echo ($this->inline ? 'inline-graph' : ''); ?>'>
    <svg class='relationships-graph' height='100%' width='100%'></svg>
</div>
<?php
if ($this->inline) {
?>
<div id='relationships-graph-resize-handle' title='<?php echo Yii::t('app', 'Resize'); ?>'></div>
<?php
}
?>
