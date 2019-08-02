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




Yii::import('zii.widgets.grid.CGridView');
Yii::import('X2GridViewBase');

/**
 * @package application.components.X2GridView
 */
class X2GridViewGeneric extends X2GridViewBase {

    /**
     * @var bool $rememberColumnSort whether or not to preserve order of columns in gvSettings
     */
    public $rememberColumnSort = true;  

    /**
     * Used to populate allFieldNames property with attribute labels indexed by
     * attribute names.
     */
    protected function addFieldNames () {
        foreach ($this->columns as $column) {
            $header = (isset ($column['header'])) ? $column['header'] : '';
            $name = (isset ($column['name'])) ? $column['name'] : '';
            $this->allFieldNames[$name] = $header;
        }
    }

    protected function generateColumns () {
        $unsortedColumns = array ();

        foreach ($this->columns as &$column) {
            $name = (isset ($column['name'])) ? $column['name'] : '';
            if (!isset ($column['id'])) {
                if (isset ($column['class']) && 
                    is_subclass_of ($column['class'], 'CCheckboxColumn')) {

                    $column['id'] = $this->namespacePrefix.'C_gvCheckbox'.$name;
                } else {
                    $column['id'] = $this->namespacePrefix.'C_'.$name;
                }
            } else {
                $column['id'] = $this->namespacePrefix.$column['id'];
            }
            if (!isset ($this->gvSettings[$name])) {
                if ($name === 'gvCheckbox') {
                    $column = $this->getGvCheckboxColumn (null, $column);
                }
                $unsortedColumns[] = $column;
                continue;
            }
            $width = $this->gvSettings[$name];
            $width = $this->formatWidth ($width);
            if ($width) {
                $column['headerHtmlOptions'] = array_merge (
                    isset ($column['headerHtmlOptions']) ? $column['headerHtmlOptions'] : array (),
                    array('style'=>'width:'.$width.';')
                );
                $column['htmlOptions'] = X2Html::mergeHtmlOptions (
                    isset ($column['htmlOptions']) ? 
                        $column['htmlOptions'] : array (), array ('width' => $width));
            }
        }
        unset ($column); // unset lingering reference

        if (isset ($this->gvSettings['gvControls']) && $this->enableControls) {
            $width = $this->gvSettings['gvControls'];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $this->columns[] =  $this->getGvControlsColumn ($width);
        }
        if (isset ($this->gvSettings['gvCheckBox'])) {
            $width = $this->gvSettings['gvCheckBox'];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $this->columns[] =  $this->getGvCheckboxColumn ($width);
        }

        if ($this->rememberColumnSort) {
            $sortedColumns = array ();
            foreach ($this->gvSettings as $columnName => $width) {
                foreach ($this->columns as $column) {
                    $name = (isset ($column['name'])) ? $column['name'] : '';
                    if ($name === $columnName) {
                        $sortedColumns[] = $column;
                        break;
                    } 
                }
            }
            $this->columns = array_merge ($sortedColumns, $unsortedColumns);
        } 
    }


    public function setSummaryText () {
        if ($this->asa ('GridViewSortableWidgetsBehavior')) {
            $this->setSummaryTextForSortableWidgets ();
            return;
        }

        /* add a dropdown to the summary text that let's user set how many rows to show on each 
           page */
        $this->summaryText =  Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>')
        .'<div class="form no-border" style="display:inline;"> '
        .CHtml::dropDownList(
            'resultsPerPage', 
            $this->getResultsPerPage (), 
            $this->getPossibleResultsPerPageFormatted(),
            array(
                'ajax' => array(
                    'url' => Yii::app()->controller->createUrl('/profile/setResultsPerPage'),
                    'data' => 'js:{results:$(this).val()}',
                    'complete' => 'function(response) { 
                        $.fn.yiiGridView.update("'.$this->id.'"); 
                    }',
                ),
                'id' => 'resultsPerPage'.$this->id,
                'style' => 'margin: 0;',
                'class' => 'x2-select resultsPerPage',
            )
        ).'</div>';
    }

}
?>
