<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
?>
<style>
    a.show-duplicate-link{
        text-decoration:none;
    }    
</style>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Mass Duplicate Detection and Removal'); ?></h2></div>
<div class="form">
    <div style="max-width: 630px;">
        <?php echo Yii::t('admin', 'This interface allows you to view all potential duplicates within your system and act to resolve them.'); ?>
        <?php echo Yii::t('admin', 'Clicking an individual record in one of the grids will take you to the duplicate resolution page for that record and redirect you to this page after resolution.'); ?>
    </div>
    <br>
    <div style="max-width:630px;">
        <?php
        echo Yii::t('admin', 'Currently displaying {type} duplicates.', array(
            '{type}' => $showAll ? Yii::t('admin', 'all') : Yii::t('admin', 'unresolved')
        )) . ' ' . X2Html::hint(Yii::t('admin', 'Unresolved duplicates are records which have yet to be manually dealt with via the duplicate checker. All duplicates includes records which have already been acted upon.'));
        echo "<br><br>" . X2Html::tag('a', array('class' => 'x2-button', 'href' => $this->createUrl('admin/massDedupe', array('showAll' => !$showAll))), $showAll ? Yii::t('admin', 'Show Unresolved') : Yii::t('admin', 'Show All')) . "<br><br>";
        ?>
    </div>
    <?php
    foreach ($dataProviders as $modelType => $dataProvider) {
        Yii::app()->clientScript->registerScript($modelType . '-duplicate-grid', ' 
                $("#' . $modelType . '-show-duplicates").on("click",function(){
                        if($("#' . $modelType . '-duplicates").is(":visible")){
                            $(this).removeClass("fa-minus-square");
                            $(this).addClass("fa-plus-square");
                            $("#' . $modelType . '-duplicates").hide();
                        } else {
                            $(this).removeClass("fa-plus-square");
                            $(this).addClass("fa-minus-square");
                            $("#' . $modelType . '-duplicates").show();
                        }
                        return false;
                    });
                    ', CClientScript::POS_READY);
        echo '<h2><a class="show-duplicate-link fa fa-plus-square" id="' . $modelType . '-show-duplicates" href="#"></a> ' . Modules::displayName(true, $modelType) . '</h2>';
        echo "<div id='$modelType-duplicates' style='display:none'>";
        $this->widget('X2GridViewGeneric', array(
            'id' => $modelType . '-dedupe-grid',
            'buttons' => array('autoResize'),
            'baseScriptUrl' =>
            Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/gridview',
            'template' => '<div class="page-title">'
            . '{buttons}{summary}</div>{items}{pager}',
            'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
            'dataProvider' => $dataProvider,
            'defaultGvSettings' => array(
                'id' => 150,
                'createDate' => 150,
                'lastUpdated' => 150,
            ),
            'gvSettingsName' => $modelType . '-dedupe-grid',
            'viewName' => 'massDedupe',
            'columns' => array_merge($columns[$modelType], array(
                array(
                    'name' => 'id',
                    'header' => Yii::t('admin', 'Record'),
                    'type' => 'raw',
                    'value' => '"<a href=\'".Yii::app()->controller->createUrl("/site/duplicateCheck", array("moduleName"=>"' . strtolower(X2Model::getModuleName($modelType)) . '","modelName"=>"' . $modelType . '", "id"=>$data["id"], "ref"=>"massDedupe"))."\'>".$data["name"]."</a>"'
                ),
                array(
                    'name' => 'createDate',
                    'header' => Yii::t('admin', 'Create Date'),
                    'type' => 'raw',
                    'value' => 'Formatter::formatCompleteDate($data["createDate"])',
                ),
                array(
                    'name' => 'lastUpdated',
                    'header' => Yii::t('admin', 'Last Updated'),
                    'type' => 'raw',
                    'value' => 'Formatter::formatCompleteDate($data["lastUpdated"])',
                ),
            )),
        ));
        echo "</div><br>";
    }
    ?>
    <?php  ?>
</div>