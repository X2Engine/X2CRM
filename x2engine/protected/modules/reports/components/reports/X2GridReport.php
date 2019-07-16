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




class X2GridReport extends X2Report {

    const TOTAL_ALIAS = '__total';
    const NULL_ALIAS = '__null';
    const EMPTY_ALIAS = '__empty';

    /**
     * @var string $rowField
     */
    public $rowField;

    /**
     * @var string $columnField
     */
    public $columnField;

    /**
     * @var string $cellDataType
     */
    public $cellDataType;

    /**
     * @var string $cellDataField
     */
    public $cellDataField;

    /**
     * Optional field which should be specified if row or column field is stageNumber and 
     * primary model type is Actions.
     * @var int $workflowId
     */
    public $workflowId; 

    /**
     * @param array $columns 
     * @param array $rows 
     * @return false|array
     */
    public function getData ($columns=array (), $rows=array ()) {
        if ($columns === 'all') {
            $this->getRawData = true;
            return $this->generate (false);
        }

        $retArray = array (array (), array ());
        if (count ($columns)) {
            $queryTotalsCol = false;
            if (in_array (self::TOTAL_ALIAS, $columns, true)) {
                // since totals column is calculated in php from the values in the rest of the
                // columns, all columns must be queried
                $queryTotalsCol = true;
                $columnFilter = null;
            } else {
                // filter grid columns by the ones specified
                $deAliasedColumns = $columns;
                foreach ($deAliasedColumns as &$col) {
                    if ($col === self::NULL_ALIAS) $col = null;
                    if ($col === self::EMPTY_ALIAS) $col = '';
                }
                $columnFilter = function ($col) use ($deAliasedColumns) {
                    return in_array ($col, $deAliasedColumns, true);
                };
            }
            $this->getRawData = true;
            $retVal = $this->generate (false, $columnFilter);
            if (!$retVal) return false; // failed to generate report
            list ($columnData, $formattedColumnHeaders, $dataColumns) = $retVal;

            $columnIndices = array ();
            if ($queryTotalsCol) {
                // if totals column was queried, columnData contains all columns and indices of
                // selected columns must be determined by scanning the row data
                $columnIndices = $this->getColumnIndices ($columnData, $columns);
            } else {
                $columnIndices = array_flip ($columns);
            }

            $columnData = ArrayUtil::transpose ($columnData);

            if (!$queryTotalsCol) {
                if (count ($columns) !== count ($columnData)) { // validate column names
                    return false;
                }
            }
            foreach ($columns as $col) {
                $retArray[0][$col] = $columnData[$columnIndices[$col]];
            }
            if (count ($retArray[0]) !== count ($columns)) return false; // validate column names
        }
    
        if (count ($rows)) {
            $this->getRawData = true;

            $queryTotalsRow = in_array (self::TOTAL_ALIAS, $rows, true);
            $formattedRowData = array ();
            if (count ($rows)) {
                // add a filter group for the specified rows
                list ($rowData, $formattedColumnHeaders, $dataColumns) =
                    $this->generate (false);
                $totalsRow = array_pop ($rowData); // remove the totals row
                if ($queryTotalsRow) $formattedRowData[self::TOTAL_ALIAS] = $totalsRow;

                // remove the first column and use its values as indices
                foreach ($rowData as $row) {
                    $rowKey = $row[$this->rowField];
                    unset ($row[$this->rowField]);
                    if (in_array ($rowKey, $rows, true)) {
                        $relabelledRow = array ();
                        $i = 0;
                        foreach ($row as $key => $val) {
                            if ($i === 0) 
                                $relabelledRow[Yii::t('reports', 'Total')] = $val;
                            else
                                $relabelledRow[$dataColumns[$i - 1]] = $val;
                            $i++;
                        }
                        $formattedRowData[$rowKey] = $relabelledRow;
                    }
                }
            }

            $retArray[1] = $formattedRowData;
        }

        if (isset ($formattedColumnHeaders)) $retArray[] = $formattedColumnHeaders;
        return $retArray;
    }

    /**
     * Generates the report, performing 2 primary queries:
     * 1) column generation query
     *     -get all values of column attribute among filtered records
     * 2) grid report query
     *     -using the results of query 1, retrieve the rows of the report 
     *     -query is of the form:
     *         select <rowAttr>, sum(if(<colAttr>='<colVal1>', 1, 0)) as <alias1> ...
     *         from <primary model table> 
     *         group by <rowAttr>;
     * @param bool $render
     * @param function $columnFilter If set, will be used to filter columns in report data
     */
    public function generate ($render=true, $columnFilter=null) {
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $qpg = new QueryParamGenerator (':generateGridReport');
        $primaryModel = $this->getPrimaryModel ();
        $primaryTableName = $primaryModel->tableName ();
        $joinClause = $this->getJoinClauses ($qpg);

        // now that we have all table aliases, get aliased versions of row and column attributes
        $aliasedColumnAttr = $this->getAliasedAttr ($this->columnField);
        $aliasedRowAttr = $this->getAliasedAttr ($this->rowField);
        if ($this->cellDataType !== 'count') {
            $aliasedCellDataField = $this->getAliasedAttr ($this->cellDataField);
        }

        $conditions = array (
            array (
                $this->getFilterConditions ('any', $this->anyFilters, $qpg), 'AND',
            ),
            array (
                $this->getFilterConditions ('all', $this->allFilters, $qpg), 'AND',
            ),
            array (
                $this->getPermissionsCondition ($qpg), 'AND',
            )
        );
        if (isset ($this->workflowId)) {
            $conditions[] = array ('workflowId='.$qpg->nextParam ($this->workflowId), 'AND');
        }
        $whereClause = 'WHERE '.$this->buildSQLConditions ($conditions);

        // get columns from all distinct colAttr values 
        $command = Yii::app()->db->createCommand ("
            select distinct($aliasedColumnAttr)
            from $primaryTableName as t
            $joinClause
            $whereClause
        ");
         //AuxLib::debugLogR ($command->getText ());
         //AuxLib::debugLogR ('$qpg->getParams () = ');
         //AuxLib::debugLogR ($qpg->getParams ());
        $columns = $command->queryColumn ($qpg->getParams ());

        sort ($columns);

        if (!$columnFilter ||
            count (array_filter (array ($this->rowField), $columnFilter))) {

            $selectClause = "select $aliasedRowAttr";
        } else {
            $selectClause = "select ";
        }

        // create column aliases to prevent SQL injection
        $columnAliases = array ();
        $i = 0;
        foreach ($columns as $col) {
            $columnAliases[$i] = '__column'.$i;
            $i++;
        }

        if ($columnFilter)
            $this->filterColumnsAndAliases ($columns, $columnAliases, $columnFilter);

        // invalid column filter
        if (!count ($columns) && $selectClause === 'select ') return false;

        // generate select clause from available columns using specified cell data type
        $i = 0;
        foreach ($columns as $col) {
            if ($selectClause !== 'select ') {
                $selectClause .= ', '; 
            }
            switch ($this->cellDataType) {
                case 'count':
                    $selectClause .= 
                        "sum(if($aliasedColumnAttr={$qpg->nextParam ($col)}, 1, 0)) 
                        as `{$columnAliases[$i]}`";
                    break;
                case 'sum':
                    $selectClause .= 
                        "$this->cellDataType(".
                            "if($aliasedColumnAttr={$qpg->nextParam ($col)}, ".
                            "$aliasedCellDataField, 0)) as `{$columnAliases[$i]}`";
                    break;
                case 'avg':
                    $selectClause .= 
                        "$this->cellDataType(".
                            "if($aliasedColumnAttr={$qpg->nextParam ($col)}, ".
                            // if value doesn't match, use null to prevent average from being 
                            // skewed
                            "$aliasedCellDataField, NULL)) as `{$columnAliases[$i]}`";
                    break;
                default:
                    throw new CException ('Invalid cell data type');
            }
            $i++;
        }
        if (!$this->getRawData)
            $selectClause .= ", t.id as ".self::HIDDEN_ID_ALIAS;

        // generate the report
        $command = Yii::app()->db->createCommand ("
            $selectClause

            from $primaryTableName as t
            $joinClause
            $whereClause
            group by $aliasedRowAttr
        ");
         //AuxLib::debugLogR ('$qpg->getParams () = ');
         //AuxLib::debugLogR ($qpg->getParams ());
        //AuxLib::debugLogR ('$command->getText () = ');
        //AuxLib::debugLogR ($command->getText ());

        $reportRecords = $command->queryAll (true, $qpg->getParams ());

        //AuxLib::debugLogR ('$reportRecords = ');
        //AuxLib::debugLogR ($reportRecords[0]);
        // AuxLib::debugLogR ('$totalsRow = ');
        // AuxLib::debugLogR ($totalsRow);
        if (!$columnFilter ||
            count (array_filter (array (self::TOTAL_ALIAS), $columnFilter))) {

            $this->addGridReportSummationColumn ($reportRecords);
        }
        $totalsRow = $this->getGridReportTotalsRow ($reportRecords, $columnFilter);

        $formattedColumnHeaders = $this->getFormattedColumnHeaders ($columns, $columnFilter);
       //AuxLib::debugLogR ('$formattedColumnHeaders = ');
        //AuxLib::debugLogR ($formattedColumnHeaders);

        if ($this->getRawData) {
            return array (
                array_merge ($reportRecords, array ($totalsRow)), $formattedColumnHeaders,
                $columns);
        } elseif ($this->print || $this->email) {
            $this->printReport (
                $formattedColumnHeaders, array_merge ($reportRecords, array ($totalsRow)),
                $columnAliases);
        } elseif ($this->export) {
            $this->export ($this->formatData (
                array_merge (array ($formattedColumnHeaders), $reportRecords, array ($totalsRow))));
        } else {
            $gridViewParams = $this->getGridReportGridViewParams (
                $reportRecords, $formattedColumnHeaders, $columnAliases);

            // a separate grid view is used just for the summation row. Allows summation row to be
            // visible on every page of sibling grid view.
            $totalsRowGridViewParams = $this->getGridReportGridViewParams (
                array ($totalsRow), $formattedColumnHeaders, $columnAliases, 'summation-grid');

            // prevent formatting of "Total" label
            unset ($totalsRowGridViewParams['columns'][0]['modelType']);
            unset ($totalsRowGridViewParams['columns'][0]['attribute']);

            Yii::app()->controller->renderPartial (
                'application.modules.reports.components.reports.views._gridReport', array (
                    'gridViewParams' => $gridViewParams,
                    'totalsRowGridViewParams' => $totalsRowGridViewParams,
                ), false, true);
        }
    }

    /**
     * Determine relevant related models by searching through specified attributes
     */
    protected function getRelatedModelsByLinkField ($refresh = false) {
        if (!isset ($this->_relatedModelsByLinkField) || $refresh) {
            $this->_relatedModelsByLinkField = $this->_getRelatedModelsByLinkField (
                array_merge (
                    ($this->cellDataType !== 'count' ? array ($this->cellDataField) : array ()),
                    array ($this->rowField, $this->columnField), $this->getAnyFilterAttrs (),
                    $this->getAllFilterAttrs ()));
        }
        return $this->_relatedModelsByLinkField;
    }

    protected function printReport (
        array $formattedColumnHeaders, array $data, array $columnAliases) {

        $data = $this->formatData ($data);
        $columns = array ();
        
        list ($columnAttrModel, $columnAttr, $fns) = $this->getModelAndAttr ($this->rowField);
        $columns[] = array (
            'name' => $this->rowField,
            'header' => array_shift ($formattedColumnHeaders),
            'fns' => $fns,
            'attribute' => $columnAttr,
            'type' => 'raw',
        );
        $columns[] = array (
            'name' => self::TOTAL_ALIAS,
            'header' => array_shift ($formattedColumnHeaders),
            'type' => 'raw',
        );
        $i = 0;
        foreach ($formattedColumnHeaders as $header) {
            $columns[] = array (
                'name' => $columnAliases[$i],
                'header' => $header,
                'type' => 'raw',
            );
            $i++;
        }

        $reportDataProvider = new CArrayDataProvider ($data, array(
            'id' => $this->_gridId,
            'keyField' => false, 
            'pagination' => array ('pageSize'=>PHP_INT_MAX),
        ));

        Yii::app()->controller->renderPartial (
            'application.modules.reports.components.reports.views._printReport', array (
            'dataProvider' => $reportDataProvider,
            'columns' => $columns,
        ), false, !$this->email);
    }

    /**
     * Use related models to format column headers
     * @return array headers displayed in grid view 
     */
    protected function getFormattedColumnHeaders ($columns, $columnFilter=null) {
        $primaryModel = $this->getPrimaryModel ();
        $relatedModelsByLinkField = $this->getRelatedModelsByLinkField ();
        list ($columnAttrModel, $columnAttr, $fns) = $this->getModelAndAttr ($this->columnField);
        $dateFn = $this->getDateFn ($fns);
        $formattedColumnHeaders = array ();
        if (!$columnFilter || count (array_filter (array ($this->rowField), $columnFilter)))
            $formattedColumnHeaders[$this->rowField] = '';
        if (!$columnFilter || count (array_filter (array (self::TOTAL_ALIAS), $columnFilter)))
            $formattedColumnHeaders[self::TOTAL_ALIAS] = Yii::t('reports', 'Total');
        if ($this->primaryModelType === 'Actions' &&  $this->columnField === 'stageNumber') {
            $columnAttrModel->workflowId = $this->workflowId;
        }
        foreach ($columns as $col) {
            $columnAttrModel->$columnAttr = $col;
            if ($col === null) $col = self::NULL_ALIAS;
            if ($col === '') $col = self::EMPTY_ALIAS;
            if ($fns === array () ||  $col === self::NULL_ALIAS || $col === self::EMPTY_ALIAS) {
                $formattedColumnHeaders[$col] = $columnAttrModel->renderAttribute (
                    $columnAttr, false, true, true);
            } elseif ($dateFn) {
                $formattedColumnHeaders[$col] = ReportsFormatter::renderDate ($col, $dateFn);
            } else {
                $formattedColumnHeaders[$col] = $col;
            }
        }
        return $formattedColumnHeaders;
    }

    protected function formatData (array $data) {
        $relatedModelsByLinkField = $this->getRelatedModelsByLinkField ();
        list ($rowFieldModel, $rowAttribute, $rowFns) = $this->getModelAndAttr ($this->rowField);
        $rowDateFn = $this->getDateFn ($rowFns);
        list ($cellDataFieldModel, $cellDataAttribute, $cellDataFns) = $this->getModelAndAttr (
            $this->cellDataField);

        $rowCount = count ($data);
        $colCount = count ($data[0]);
        for ($i = 1; $i < $rowCount; $i++) {
            $row = &$data[$i];
            $j = 0;
            foreach ($row as $col => &$val) {
                if ($j === 0 && $i !== $rowCount - 1) {
                    $rowFieldModel->$rowAttribute = $val;

                    if ($rowDateFn) {
                        $val = ReportsFormatter::renderDate ($val, $rowDateFn);
                    } else {
                        $val = $rowFieldModel->renderAttribute ($rowAttribute, false, true, false);
                    }
                } else if ($j !== 0 && $this->cellDataType !== 'count') {
                    $cellDataFieldModel->$cellDataAttribute = $val;
                    $val = $cellDataFieldModel->renderAttribute (
                        $cellDataAttribute, false, true, false);
                }
                $j++;
            }
        }
        return $data;
    }

    /**
     * @param array $reportRecords
     * @param array $formattedColumnHeaders
     * @param array $relatedModelsByLinkField
     * @param array $columnAliases
     * @param string $id grid view id
     * @return array parameters which can be used to instantiate report grid view 
     */
    protected function getGridReportGridViewParams (
        array $reportRecords, array $formattedColumnHeaders, array $columnAliases, 
        $id = null) {

        if ($id === null) $id = $this->_gridId;

        $relatedModelsByLinkField = $this->getRelatedModelsByLinkField ();
       // AuxLib::debugLogR ('$columnAliases = ');
       //  AuxLib::debugLogR ($columnAliases);
       // AuxLib::debugLogR ('$formattedColumnHeaders = ');
       //  AuxLib::debugLogR ($formattedColumnHeaders);

        // get attribute and modelType which will be used to render grid cell values
        if ($this->cellDataType !== 'count') {
            list ($model, $attribute, $cellFns) = $this->getModelAndAttr ($this->cellDataField);
            $modelType = get_class ($model);
        } else {
            $attribute = null;
            $modelType = null;
            $cellFns = array ();
        }

        list ($rowFieldModel, $rowAttribute, $fns) = $this->getModelAndAttr ($this->rowField);

        $columns = array ();
        $columns[] = array (
            'name' => $this->rowField,
            'header' => array_shift ($formattedColumnHeaders),
            'type' => 'raw',
            'fns' => $fns,
            'attribute' => $rowAttribute,
            'modelType' => $rowFieldModel,
        );
        $columns[] = array (
            'name' => self::TOTAL_ALIAS,
            'header' => array_shift ($formattedColumnHeaders),
            'type' => 'raw',
            'attribute' => $attribute,
            'modelType' => $modelType,
            'fns' => $cellFns,
        );
        $i = 0;
        foreach ($formattedColumnHeaders as $header) {
            $columns[] = array (
                'name' => $columnAliases[$i],
                'header' => $header,
                'type' => 'raw',
                'attribute' => $attribute,
                'modelType' => $modelType,
                'fns' => $cellFns,
            );
            $i++;
        }
        // AuxLib::debugLogR ('$columns = ');
        // AuxLib::debugLogR ($columns);

        $reportDataProvider = new CArrayDataProvider($reportRecords,array(
            'id' => $id,
            'keyField' => false, 
            'pagination' => array('pageSize'=>Profile::getResultsPerPage()),
            'sort' => array (
                //'attributes' => array_merge (array ($this->rowField, '__total'), $columnAliases),
            ),
        ));
        $reportDataProvider->pagination->route = Yii::app()->request->pathInfo;
        $reportDataProvider->pagination->params = 
            array_merge ($_GET, array ('ajax' => $id));
        //$reportDataProvider->sort->route = Yii::app()->request->pathInfo;
        //$reportDataProvider->sort->params = $_GET;

        return array (
            'dataProvider' => $reportDataProvider,
            'columns' => $columns,
        );
    }

    /**
     * Inserts a summation column in report records after the row field column. The summation column
     * contains the sum of the values in all subsequent columns
     * @param array $reportRecords records to display in the report grid view
     */
    protected function addGridReportSummationColumn (&$reportRecords) {
        $summationCol = array_map (function ($row) {
            return array_sum (
                array_slice ($row, 1, isset ($row[X2GridReport::HIDDEN_ID_ALIAS]) ? -1 : 0));
        }, $reportRecords);
        //AuxLib::debugLogR ('$summationCol = ');
        //AuxLib::debugLogR ($summationCol);
        //AuxLib::debugLogR ($reportRecords[0]);

        foreach ($reportRecords as $i => &$row) {
            $newRow = array ($this->rowField => array_shift ($row));
            $newRow[self::TOTAL_ALIAS] = $summationCol[$i];
            $row = array_merge ($newRow, $row);
        }
    }

    /**
     * @param array $reportRecords records to display in the report grid view
     * @return array sum of values in each column 
     */
    protected function getGridReportTotalsRow (array $reportRecords, $columnFilter) {
        $summationRow = array ();
        if (!$columnFilter || count (array_filter (array ($this->rowField), $columnFilter))) {
            $summationRow[$this->rowField] = Yii::t('reports', 'Total');
        }
        if (!$columnFilter || count (array_filter (array (self::TOTAL_ALIAS), $columnFilter))) {
            $summationRow[self::TOTAL_ALIAS] = true; // placeholder
        }
        foreach ($reportRecords as $row) {
            $i = 0;
            foreach ($row as $attr => $val) {
                if ($i++ < 2) { // don't add up the totals or id columns
                    continue;
                }
                if (isset ($summationRow[$attr])) {
                    $summationRow[$attr] += $val;
                } else {
                    $summationRow[$attr] = $val;
                }
            }
        }
        if (isset ($summationRow[self::TOTAL_ALIAS])) {
            $summationRow[self::TOTAL_ALIAS] = array_sum (
                array_slice (
                    $summationRow, 2, isset ($summationRow[self::HIDDEN_ID_ALIAS]) ? -1 : 0));
        }
        return $summationRow;
    }

    private function filterColumnsAndAliases (
        array &$columns, array &$columnAliases, $columnFilter) {

        $i = 0;
        $filteredAliases = array ();
        $filteredColumns = array ();
        foreach ($columnAliases as $alias) {
            if ($columnFilter ($alias)) {
                $filteredAliases[] = $columnAliases;
                $filteredColumns[] = $columns[$i];
            }
            $i++;
        }
        $columns = $filteredColumns;
        $columnAliases = $filteredAliases;
    }
}
