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




Yii::app()->clientScript->registerScriptFile($this->module->getAssetsUrl ().'/js/chartManager.js',
    CClientScript::POS_BEGIN);

require_once("protected/modules/charts/chartsConfig.php");

$menuOptions = array(
    'leadVolume', 'marketing', 'pipeline', 'opportunities',
);
$this->insertMenu($menuOptions);

?>


<div class="page-title icon charts"><h2>
    <?php
        echo Yii::t('app', '{marketing} Dashboard', array(
            '{marketing}' => Modules::displayName(true, "Marketing"),
        )); ?>
&nbsp;&nbsp;</h2></div>
<div class="form">
    <br>

    <?php
    $form = $this->beginWidget('CActiveForm', array('id' => 'chart'));
    $range = $model->dateRange;
    $userName = Yii::app()->user->getName();
    $filters = array(
        "leadDate > (unix_timestamp() - ($range*24*3600))",
        "((visibility = 0 AND assignedTo='$userName') OR (visibility = 1) OR (visibility = 2 and assignedTo='$userName'))"
    );
    ?>
    <div class="x2-chart-container-controls">
        <div class="x2-chart-control">
            <div class="row">
                <?php 
                echo $form->label(
                    $model, 'dateRange', 
                    array(
                        'label' => Yii::t('charts', 'Select {leads} received in the last', array(
                            '{leads}' => strtolower(Modules::displayName(true, "X2Leads")),
                        )).
                            ' &nbsp;&nbsp;&nbsp;&nbsp;'
                    )
                ); 
                echo $form->dropDownList(
                    $model, 'dateRange', array(
                    10 => Yii::t('charts', '{n} days',array('{n}'=>'10')),
                    30 => Yii::t('charts', '{n} days',array('{n}'=>'30')),
                    60 => Yii::t('charts', '{n} days',array('{n}'=>'60')),
                    90 => Yii::t('charts', '{n} days',array('{n}'=>'90')),
                    120 => Yii::t('charts', '{n} days',array('{n}'=>'120')),
                    360 => Yii::t('charts', '{n} days',array('{n}'=>'360'))
                ));
                ?>
                <a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
            </div>
        </div>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large-pie">
        <?php
        $range = $model->dateRange;
        $chart1 = $this->widget('X2PieChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 3,
                'x-axis' => array('column' => 'leadSource')),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Source', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid')
            )
                ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large-pie">
        <?php
        $this->widget('X2PieChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 1,
                'x-axis' => array('column' => 'leadType')
            ),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Type', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid')
            )
        ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large">
        <?php
        $this->widget('X2StackedBarChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 2,
                'x-axis' => array('column' => 'assignedToName'),
                'x-axis1' => array('column' => 'leadStatus')
            ),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Distribution and Status', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'axes' => array(
                    'xaxis' => array('label' => Yii::t('charts', 'Assigned To')),
                    'yaxis' => array('label' => Yii::t('charts', 'Count'))
                )
            )
        ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large">
        <?php
        $this->widget('X2StackedBarChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 0,
                'orderby' => 'leadDate asc',
                'x-axis' => array('column' => "from_unixtime(leadDate,'%b-%Y')"),
                'x-axis1' => array('column' => 'dealStatus')
            ),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Conversion', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'axes' => array(
                    'xaxis' => array('label' => Yii::t('charts', '{lead} Received', array(
                        '{lead}' => Modules::displayName(false, "X2Leads"),
                    ))),
                    'yaxis' => array('label' => Yii::t('charts', 'Count'))
                )
            )
        ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <?php
    $form = $this->endWidget();
    ?>
</div>

