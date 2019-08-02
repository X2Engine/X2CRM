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





class PrintableSummationReportGridView extends PrintableReportsGridView {

    /**
     * Renders a table body row.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     * @param integer $row the row number (zero-based).
     */
    public function renderTableRow($row) {
        $htmlOptions = array();
        if ($this->rowHtmlOptionsExpression !== null) {
            $data = $this->dataProvider->data[$row];
            $options = $this->evaluateExpression($this->rowHtmlOptionsExpression, array('row' => $row, 'data' => $data));
            if (is_array($options))
                $htmlOptions = $options;
        }

        if ($this->rowCssClassExpression !== null) {
            $data = $this->dataProvider->data[$row];
            $class = $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $data));
        } elseif (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
            $class = $this->rowCssClass[$row % $n];

        if (!empty($class)) {
            if (isset($htmlOptions['class']))
                $htmlOptions['class'].=' ' . $class;
            else
                $htmlOptions['class'] = $class;
        }

        /* x2modstart */
        $data = $this->dataProvider->data[$row];
        if (!isset($data[X2SummationReport::GROUP_HEADER_TOKEN])) {
            // add in rows of nested grids
            // if previous row is not part of this nested grid, that means this row is a nested
            // grid header row
            if ($row !== 0) {
                $prevRow = $this->dataProvider->data[$row - 1];
                if (isset($prevRow[X2SummationReport::GROUP_HEADER_TOKEN]))
                    $htmlOptions['class'] .= ' group-header-row';
            }
            echo CHtml::openTag('tr', $htmlOptions) . "\n";
            $dataCount = count($data);
            $colCount = count($this->columns);
            $this->renderDrillDownRow($data);

            // add extra empty cells to fill out the grid
            for ($i = $dataCount; $i < $colCount; $i++) {
                echo '<td></td>';
            }
        } else {
            echo CHtml::openTag('tr', $htmlOptions) . "\n";
            foreach ($this->columns as $column)
                $this->renderDataCell($column, $row);
        }
        /* x2modend */
        echo "</tr>\n";
    }

    public function renderDrillDownRow(array $data) {
        foreach ($data as $datum) {
            echo CHtml::openTag('td');
            if ($datum !== X2Report::EMPTY_ALIAS)
                echo CHtml::encode($datum);
            echo '</td>';
        }
    }

}

?>
