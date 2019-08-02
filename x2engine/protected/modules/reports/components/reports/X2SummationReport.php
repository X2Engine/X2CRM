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


class X2SummationReport extends X2Report {
    const STAR_ALIAS = '__$star$__';
    const GROUP_HEADER_TOKEN = '__$groupHeader$__';
    /**
     * If true, generate drill-down sub grid, otherwise generate group headers grid
     * @var bool $generateSubgrid
     */
    public $generateSubgrid; 
    /**
     * Used to filter results for drill down query
     * @var array $groupAttrValues
     */
    public $groupAttrValues; 
    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'RowsAndColumnsDataColumn'; 
    /**
     * @var null|array $drillDownColumns 
     */
    public $drillDownColumns;
     
    /**
     * @var array $groups 
     */
    public $groups;
    /**
     * @var array $groupsOrderBy
     */
    public $groupsOrderBy;
    /**
     * @var array $groupsAnyFilters
     */
    public $groupsAnyFilters;
    /**
     * @var array $groupsAllFilters
     */
    public $groupsAllFilters;
    /**
     * @var int $subgridIndex
     */
    public $subgridIndex; 
    /**
     * @var array $aggregates 
     */
    private $_aggregates;
    private $_formattedColumnHeaders;
    /**
     * @param array $_columns columns specified in drill down records query. Includes attributes in 
     *  $groups not in $drillDownColumns.
     */
    private $_columns = array (); 
    /**
     * @var array $_drillDownFilterAttributes 
     */
    private $_drillDownFilterAttributes; 
    /**
     * @var array $_orderByAttributes 
     */
    private $_orderByAttributes; 
    /**
     * @var array $_groupsOrderByAttributes 
     */
    private $_groupsOrderByAttributes; 
    /**
     * @var array $_aggregateAttrs
     */
    private $_aggregateAttrs; 
    /**
     * @var array $_aggregateColumnValueAliases
     */
    private $_aggregateColumnValueAliases; 
    /**
     * @var array $_groupAttrs
     */
    private $_groupAttrs; 
    private $_groupsAnyFilterAttrs;
    private $_groupsAllFilterAttrs;
    /**
     * @param array $columns 
     * @param array $rows 
     * @param bool $enableDrillDown 
     */
    public function getData (
        array $columns=array (), array $rows=array (), $enableDrillDown=false) {
        // separate aggregates from groups
        $aggregates = array ();
        $filteredColumns = array ();
        for ($i = 0; $i < count ($columns); $i++) {
            $col = $columns[$i];
            if ($this->isAggregate ($col)) {
                $aggregates[] = $col;
            } else {
                $filteredColumns[] = $col;
            }
        }
        // validate columns and aggregates
        if (array_intersect ($filteredColumns, $this->getGroupAttrs ()) !== $filteredColumns ||
            array_intersect ($aggregates, $this->aggregates) !== $aggregates) {
            return false;
        }
        $this->aggregates = $aggregates;
        $this->getRawData = true;
        $columnData = $this->generate ();
        $retArray = array ();
        if (count ($columnData)) {
            $columnIndices = $this->getColumnIndices ($columnData, $columns);
            $columnData = ArrayUtil::transpose ($columnData);
            foreach ($columns as $col) {
                $retArray[0][$col] = $columnData[$columnIndices[$col]];
            }
        } else {
            $retArray[] = array ();
        }
        $retArray[] = array ();
        $retArray[] = $this->getFormattedColumnHeaders ();
        return $retArray;
    }
    public function run () {
        if ($this->generateSubgrid) {
            $this->generateSubgrid ();
        } else {
            $this->generate ();
        }
    }
    public function generateSubgrid () {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $this->clearCache ();
        $qpg = new QueryParamGenerator (':generateSummationReportSubgrid');
        $primaryModel = $this->getPrimaryModel ();
        $primaryTableName = $primaryModel->tableName ();
        $joinClause = $this->getJoinClauses ($qpg);
        $whereClause = 'WHERE '.$this->buildSQLConditions (array (
            array (
                $this->getFilterConditions ('any', $this->anyFilters, $qpg), 'AND',
            ),
            array (
                $this->getFilterConditions ('all', $this->allFilters, $qpg), 'AND',
            ),
            array (
                $this->getDrillDownCondition ($qpg), 'AND',
            ),
            array (
                $this->getPermissionsCondition ($qpg), 'AND',
            )
        ));
        $orderByClause = $this->getOrderByClause ($this->orderBy);
        if (count ($this->drillDownColumns)) {
            $selectClause = $this->buildSelectClause ($this->getColumns (), true);
            $query = $this->buildQueryCommand (
                $selectClause, $primaryTableName, $joinClause, $whereClause, null, null,
                $orderByClause);
            //AuxLib::debugLogR ($query->getText ());
            // don't fetch associative since field names might repeat (as a result of a join)
            $reportRecords = $query->queryAll (true, $qpg->getParams ());
        } else {
            $reportRecords = array ();
        }
        $gridViewParams = $this->getSubgridGridViewParams ($reportRecords);
        Yii::app()->controller->renderPartial (
            'application.modules.reports.components.reports.views._summationReportSubgrid',
            array (
                'report' => $this,
                'gridViewParams' => $gridViewParams,
            ), false, true);
    }
    protected function getTotalsRow (array $reportRecords, array $columnAttrs) {
        $summationRow = array ();
        if ($this->drillDownColumns) {
            $summationRow[] = '';
        }
        $summationRow = array_merge (
            $summationRow, parent::getTotalsRow ($reportRecords, $columnAttrs));
        return $summationRow;
    }
    public function generate () {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $this->clearCache ();
        $qpg = new QueryParamGenerator (':generateSummationReport');
        $primaryModel = $this->getPrimaryModel ();
        $primaryTableName = $primaryModel->tableName ();
        // relevant related models must be added to JOIN clause
        $joinClause = $this->getJoinClauses ($qpg);
        $whereClause = 'WHERE '.$this->buildSQLConditions (array (
            array (
                $this->getFilterConditions ('any', $this->anyFilters, $qpg), 'AND',
            ),
            array (
                $this->getFilterConditions ('all', $this->allFilters, $qpg), 'AND',
            ),
            array (
                $this->getPermissionsCondition ($qpg), 'AND',
            )
        ));
        $havingClause = 'HAVING '.$this->buildSQLConditions (array (
            array (
                $this->getFilterConditions ('any', $this->groupsAnyFilters, $qpg), 'AND',
            ),
            array (
                $this->getFilterConditions ('all', $this->groupsAllFilters, $qpg), 'AND',
            ),
        ));
        // query with groups for section headers, ungrouped query will be used for drill down
        $groupByClause = $this->getGroupByClause ();
        $selectClause = $this->buildSelectClause (
            array_merge ($this->getGroupAttrs (), $this->aggregates), false);
        $orderByClause = $this->getOrderByClause ($this->groupsOrderBy);
        $query = $this->buildQueryCommand (
            $selectClause, $primaryTableName, $joinClause, $whereClause,
            $groupByClause, $havingClause, $orderByClause);
        //AuxLib::debugLogR ($query->getText ());
        // don't fetch associative since field names might repeat (as a result of a join)
        $groupHeaders = $query->queryAll (true, $qpg->getParams ());
        if ($this->includeTotalsRow)  {
            $totalsRow = $this->getTotalsRow ($groupHeaders,  $this->getGridColumnAttrs ());
           //AuxLib::debugLogR ('$totalsRow = ');
            //AuxLib::debugLogR ($totalsRow);
        }
        //AuxLib::debugLogR ('$groupHeaders = ');
        //AuxLib::debugLogR ($groupHeaders);
        if ($this->getRawData) {
            return $groupHeaders;
        }
        if ($this->print || $this->email || $this->export) {
            list ($drillDownRecords, $drillDownFormattedHeaders) = 
                $this->getAllDrillDownRecords ();
            $drillDownRecords = $this->formatData ($drillDownRecords);
            $this->clearCache ();
            $records = $this->insertGroupHeaders (
                $drillDownRecords, $groupHeaders, 
                $drillDownFormattedHeaders, $this->formatData ($groupHeaders), !$this->export);
            
            //Format records' dates from numbers to month names
            $records = $this->recordsDateFormatter($records);
            $records = $this->formatCurrency($records);
            
            if ($this->includeTotalsRow) {
                $totalsRow = $this->formatData (array ($totalsRow));
                $totalsRow = array_pop ($totalsRow);
            }
            if ($this->print || $this->email) {
                $this->printReport (
                    array_merge ($records, $this->includeTotalsRow ? 
                        array ($totalsRow) : array ()));
            } else {
                $this->export (array_merge (
                    array ($this->getFormattedColumnHeaders ()),
                    $records, 
                    ($this->includeTotalsRow ? array ($totalsRow) : array ())));
            }
        } else {
            $gridViewParams = $this->getSummationReportGridViewParams ($groupHeaders);
            // a separate grid view is used just for the summation row. Allows summation row to be
            // visible on every page of sibling grid view.
            if ($this->includeTotalsRow)
                $totalsRowGridViewParams = $this->getSummationReportGridViewParams (
                    array ($totalsRow), 'summation-grid');
            Yii::app()->controller->renderPartial (
                'application.modules.reports.components.reports.views._summationReport',
                array (
                    'gridViewParams' => $gridViewParams,
                    'totalsRowGridViewParams' => $this->includeTotalsRow ? 
                        $totalsRowGridViewParams : null,
                ), false, true);
        }
    }
    
    /**
     * @return records array with the months as names 
     */
    public function recordsDateFormatter ($records) {
        $monthNames = array (' ' => ' ', '1' => 'January', '2' => 'February', '3' => 'March', 
            '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', 
            '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November',
            '12' => 'December');
        $queryDateTypes = Yii::app()->db->createCommand(
               'SELECT fieldName FROM x2_fields WHERE type LIKE \'date%\'')->queryAll();
        $temp = $records;
        // Iterate through temporary records array looking for 
        // keyword 'month' where the month number is stored
        // and change the value to its respective month name
        for($i = 0; $i < count($records); $i++) {
            foreach($temp[$i] as $key => $value) {
                if(strpos($key, 'month') !== false) {
                    $month = $records[$i][$key]; 
                    $records[$i][$key] = $monthNames[$month];
                    continue;
                } elseif((strpos($key, 'year') !== false) || 
                        (strpos($key, 'day') !== false)){
                    continue;
                } else {
                    // If our time format is of Month Day, Year then
                    // need to format it from epoch to said format
                    foreach($queryDateTypes as $q){
                        if(strpos($key, strval($q['fieldName'])) !== false){
                            $dateTime = date('M d, Y', $value);
                            $records[$i][$key] = $dateTime;
                            continue;
                        }
                    }
                }
            }
        }
        return $records;
    }    
    
    /**
     * @param type $records
     * @return formatted currencies
     */
    public function formatCurrency($records){
        $temp = $records;
        for($i = 0; $i < count($records); $i++) {
            foreach($temp[$i] as $key => $value) {
                $pattern = '/\d+\.\d{2}/';
                if(preg_match($pattern, $value)) {
                    setlocale(LC_MONETARY, 'en_US.UTF-8');
                    $records[$i][$key] = money_format('%.2n', $records[$i][$key]);
                }
            }
        }
        return $records;
    }
    
    /**
     * @return array names of attributes specified in any filters
     */
    protected function getGroupsAnyFilterAttrs () {
        if (!isset ($this->_groupsAnyFilterAttrs)) {
            $this->_groupsAnyFilterAttrs = $this->extractFilterNames ($this->groupsAnyFilters);
        }
        return $this->_groupsAnyFilterAttrs;
    }
    /**
     * @return array names of attributes specified in all filters
     */
    protected function getGroupsAllFilterAttrs () {
        if (!isset ($this->_groupsAllFilterAttrs)) {
            $this->_groupsAllFilterAttrs = $this->extractFilterNames ($this->groupsAllFilters);
        }
        return $this->_groupsAllFilterAttrs;
    }
    protected function getColumns ($refresh = false) {
        if (!isset ($this->_columns) || $refresh) {
            if ($this->generateSubgrid) {
                $this->_columns = $this->drillDownColumns;
            } else {
                $this->_columns = array_merge (
                    $this->getGroupAttrs (), $this->aggregates);
            }
        }
        return $this->_columns;
    }
    protected function getAggregates () {
        return $this->_aggregates;
    }
    protected function setAggregates ($value) {
        $this->_aggregates = array_merge (array ('count(*)'), $value);
    }
    protected function getGridId ($refresh = false) {
        if (!isset ($this->_gridId) || $refresh) {
            if ($this->generateSubgrid) {
                $this->_gridId = 'generated-report-subgrid-'.$this->subgridIndex;
            } else {
                $this->_gridId = 'generated-report';
            }
        }
        return $this->_gridId;
    }
    /**
     * Get group attribute names from groups property 
     * @return array
     */
    protected function getGroupAttrs () {
        if (!isset ($this->_groupAttrs)) {
            $this->_groupAttrs = array_map (function ($a) {
                    return $a[0];
                }, $this->groups ? $this->groups : array());
        }
        return $this->_groupAttrs;
    }
    /**
     * @return array 
     */
    protected function getAggregateAttrs () {
        if (!isset ($this->_aggregateAttrs)) {
            $this->_aggregateAttrs = array_map (function ($a) {
                    return $a[0];
                }, $this->aggregates);
        }
        return $this->_aggregateAttrs;
    }
    /**
     * @return array 
     */
    protected function getDrillDownFilterAttributes () {
        if (!isset ($this->_drillDownFilterAttributes)) {
            $this->_drillDownFilterAttributes = array_keys ($this->groupAttrValues);
        }
        return $this->_drillDownFilterAttributes;
    }
    /**
     * @return array 
     */
    protected function getOrderByAttributes () {
        if (!isset ($this->_orderByAttributes)) {
            $this->_orderByAttributes = array_map (function ($a) { 
                return $a[0]; 
            }, $this->orderBy);
        }
        return $this->_orderByAttributes;
    }
    /**
     * @return array 
     */
    protected function getGroupsOrderByAttributes () {
        if (!isset ($this->_groupsOrderByAttributes)) {
            $this->_groupsOrderByAttributes = array_map (function ($a) { 
                return $a[0]; 
            }, $this->groupsOrderBy);
        }
        return $this->_groupsOrderByAttributes;
    }
    /**
     * @return sql condition used to retrieve drill down records
     */
    protected function getDrillDownCondition (QueryParamGenerator $qpg) {
        $condition = '(';
        foreach ($this->groupAttrValues as $attr => $value) {
            $attr = $this->getAliasedAttr ($attr);
            if ($condition !== '(') {
                $condition .= ' AND ';
            }
            $condition .= "$attr={$qpg->nextParam ($value)}";
        }
        if ($condition === '(') {
            $condition .= '1=1';
        }
        $condition .= ')';
        return $condition;
    }
    protected function formatData (array $data) {
        $rowCount = count ($data);
        if ($rowCount) {
            $colModelsAndAttrs = array ();
            foreach ($this->getColumns () as $col) {
                list ($attr, $fns) = $this->parseFns ($col);
                if ($attr !== '*') {
                    list ($colModel, $colAttr) = $this->getModelAndAttr ($col);
                    $colModelsAndAttrs[] = array ($colModel, $colAttr);
                } else {
                    $colModelsAndAttrs[] = array ();
                }
            }
            $colCount = count ($data[0]);
            for ($i = 0; $i < $rowCount; $i++) {
                $row = &$data[$i];
                $j = 0;
                foreach ($row as $colAttr => &$val) {
                    if ($val === self::EMPTY_ALIAS || $colAttr === self::HIDDEN_ID_ALIAS) { 
                        $val = '';
                        $j++;
                        continue;
                    }
                    if (isset ($colModelsAndAttrs[$j]) && count ($colModelsAndAttrs[$j])) {
                        $colModelAndAttr = $colModelsAndAttrs[$j];
                        $colModel = $colModelAndAttr[0];
                        $colAttr = $colModelAndAttr[1];
                        $colModel->$colAttr = $val;
                        $val = $colModel->renderAttribute ($colAttr, false, true, false);
                    }
                    $j++;
                }
            }
        }
        return $data;
    }
    /**
     * Determine relevant related models by searching through specified attributes
     */
    protected function getRelatedModelsByLinkField ($refresh = false) {
        if (!isset ($this->_relatedModelsByLinkField) || $refresh) {
            if ($this->generateSubgrid) {
                $this->_relatedModelsByLinkField = $this->_getRelatedModelsByLinkField (
                    array_merge (
                        $this->getColumns (), $this->getAnyFilterAttrs (),
                        $this->getAllFilterAttrs (), $this->getOrderByAttributes (),
                        $this->getGroupAttrs (), $this->getGroupsAnyFilterAttrs (),
                        $this->getGroupsAllFilterAttrs ()
                    ));
            } else {
                $this->_relatedModelsByLinkField = $this->_getRelatedModelsByLinkField (
                    array_merge (
                        $this->getColumns (), $this->getAnyFilterAttrs (),
                        $this->getAllFilterAttrs (), $this->getGroupsOrderByAttributes (),
                        $this->getGroupsAnyFilterAttrs ()
                    ));
            }
        }
        return $this->_relatedModelsByLinkField;
    }
    private function clearCache (array $exclude=array ()) {
        if (!in_array ('columns', $exclude)) $this->getColumns (true);
        if (!in_array ('gridId', $exclude)) $this->getGridId (true);
        if (!in_array ('relatedModelsByLinkField', $exclude))
            $this->getRelatedModelsByLinkField (true);
        if (!in_array ('joinAliases', $exclude))
            $this->getJoinAliases (true);
        if (!in_array ('gridColumnAttrs', $exclude)) $this->getGridColumnAttrs (true);
        if (!in_array ('formattedColumnHeaders', $exclude)) 
            $this->getFormattedColumnHeaders (true);
    }
    protected function printReport (array $data) {
        $formattedColumnHeaders = $this->getFormattedColumnHeaders ();
        $columns = array ();
        $gridColumnAttrs = array_reverse ($this->getGridColumnAttrs ());
        foreach ($formattedColumnHeaders as $header) {
            $columns[] = array (
                'name' => array_pop ($gridColumnAttrs),
                'header' => $header,
                'type' => 'raw',
            );
        }
        $reportDataProvider = new CArrayDataProvider ($data, array(
            'id' => $this->_gridId,
            'keyField' => false, 
            'pagination' => array ('pageSize'=>PHP_INT_MAX),
        ));
        Yii::app()->controller->renderPartial (
            'application.modules.reports.components.reports.views._printSummationReport', array (
            'dataProvider' => $reportDataProvider,
            'columns' => $columns,
        ), false, !$this->email);
    }
    /**
     * Inserts group headers faster than insertGroupHeaders (). This algorithm can only be used 
     * if user hasn't specified a group ordering since it assumes that groups are in the same
     * order as the group headers.
     */
    private function fastGroupHeaderInsertion (
        array $drillDownRecords, array $groupHeaders, array $drillDownFormattedHeaders, 
        array $groupFormattedHeaders, $insertHeaderTokens=true) {
        $groupCount = count ($groupHeaders);
        if ($groupCount) {
            if (count ($this->drillDownColumns)) {
                $groupAttrs = $this->getGroupAttrs ();
                // insert group headers into report records.
                // O(r * g + h) where r = |records|, g = |group attributes|, h = |headers|
                $newReportRecords = array ();
                $recordCount = count ($drillDownRecords);
                $j = 0;
                for ($i = 0; $i < $groupCount; $i++) {
                    $header = $groupHeaders[$i];
                    $formattedHeader = $groupFormattedHeaders[$i];
                    // scan through report records until first record matching header is found, 
                    // then insert the header before that record
                    for (; $j < $recordCount; $j++) {
                        $record = $drillDownRecords[$j];
                        $matchFound = true;
                        foreach ($groupAttrs as $group) {
                            if ($record[$group] !== $header[$group]) {
                                $matchFound = false;
                            }
                            unset ($record[$group]);
                        }
                        if ($matchFound) {
                            if ($insertHeaderTokens)
                                $header[self::GROUP_HEADER_TOKEN] = true;
                            $newReportRecords[] = $formattedHeader;
                            $newReportRecords[] = $drillDownFormattedHeaders;
                            $j++;
                        }
                        $newReportRecords[] = $record;
                        if ($matchFound) break;
                    }
                }
                // add the rest of the report records
                for (; $j < $recordCount; $j++) {
                    $record = $drillDownRecords[$j];
                    foreach ($groupAttrs as $group) {
                        unset ($record[$group]);
                    }
                    $newReportRecords[] = $record;
                }
                assert (count ($newReportRecords) === 
                    (count ($drillDownRecords) + 2 * count ($groupHeaders)));
                $drillDownRecords = $newReportRecords;
            } else { // no user-selected columns, only display headers
                if ($insertHeaderTokens) {
                    foreach ($groupHeaders as &$header) {
                        $header[self::GROUP_HEADER_TOKEN] = true;
                    }
                }
                $drillDownRecords = $groupHeaders;
            }
        }
        return $drillDownRecords;
    }
    /**
     * Returns an array with group headers interpersed at appropriate locations within
     * drill down records
     */
    private function insertGroupHeaders (
        array $drillDownRecords, array $groupHeaders, array $drillDownFormattedHeaders,
        array $groupFormattedHeaders, $insertHeaderTokens=true) {
        if ($this->groupsOrderBy === array()) {
            // user hasn't specified group order attributes, so we can use faster group insertion
            // algorithm
            return $this->fastGroupHeaderInsertion (
                $drillDownRecords, $groupHeaders, $drillDownFormattedHeaders, 
                $groupFormattedHeaders, $insertHeaderTokens);
        }
        $groupCount = count ($groupHeaders);
        if ($groupCount) {
            if (count ($this->drillDownColumns)) {
                $groupAttrs = $this->getGroupAttrs ();
                // insert group headers into report records.
                // O(r * g + h * g + r) where r = |records|, g = |group attributes|, h = |headers|
                // O(r * g) is for creating the lookup table
                // O(h * g) is for scanning the headers and performing table lookups
                // O(r) is for inserting each group into the output array
                // build lookup table of start indices of groups in drilldown records and
                // sizes of groups. This prevents us from having to scan through the records
                // each time a certain group is needed (which would result in an unacceptable 
                // r * h factor in the runtime)
                $drillDownGroupLookupTable = array ();
                $prevGroup = null;
                $i = 0;
                $currGroupSize = 0;
                foreach ($drillDownRecords as &$record) {
                    $currGroup = '';
                    foreach ($groupAttrs as $group) {
                        $currGroup .= $record[$group];
                        unset ($record[$group]);
                    }
                    if ($currGroup !== $prevGroup) {
                        if ($prevGroup !== null) {
                            $drillDownGroupLookupTable[$prevGroup][] = $currGroupSize;
                        }
                        $prevGroup = $currGroup;
                        $drillDownGroupLookupTable[$currGroup] = array ($i);
                        $currGroupSize = 0;
                    }
                    $currGroupSize++;
                    $i++;
                }
                if ($prevGroup !== null) {
                    $drillDownGroupLookupTable[$prevGroup][] = $currGroupSize;
                }
                $newReportRecords = array ();
                $recordCount = count ($drillDownRecords);
                // for each group header, retrieve drill down records and insert them into 
                // output array 
                for ($i = 0; $i < $groupCount; $i++) {
                    $header = $groupHeaders[$i];
                    $formattedHeader = $groupFormattedHeaders[$i];
                    $groupIndex = '';
                    foreach ($groupAttrs as $group) {
                        $groupIndex .= $header[$group];
                    }

                    $startIndex = null;
                    $groupSize = null;
                    try {
                        list ($startIndex, $groupSize) = array_pop(array_reverse($drillDownGroupLookupTable));
                    } catch (Exception $e) {
                        list ($startIndex, $groupSize) = $drillDownGroupLookupTable[$groupIndex];
                    }

                    if ($insertHeaderTokens)
                        $header[self::GROUP_HEADER_TOKEN] = true;
                    $newReportRecords[] = $formattedHeader;
                    $newReportRecords[] = $drillDownFormattedHeaders;
                    // jump to the relevant group and add it to the output array
                    for ($j = $startIndex; $j < $startIndex + $groupSize; $j++) {
                        $record = $drillDownRecords[$j];
                        $newReportRecords[] = $record;
                    }
                }
                // assert (count ($newReportRecords) === 
                    // (count ($drillDownRecords) + 2 * count ($groupHeaders)));
                $drillDownRecords = $newReportRecords;
            } else { // no user-selected columns, only display headers
                if ($insertHeaderTokens) {
                    foreach ($groupHeaders as &$header) {
                        $header[self::GROUP_HEADER_TOKEN] = true;
                    }
                }
                $drillDownRecords = $groupHeaders;
            }
        }
        return $drillDownRecords;
    }
    /**
     * @return array 
     */
    protected function getGridColumnAttrs ($refresh = false) {
        if (!isset ($this->_gridColumnAttrs) || $refresh) {
            if ($this->generateSubgrid) {
                $columns = $this->getColumns ();
            } else {
                $columns = array_merge ($this->getGroupAttrs (), $this->aggregates);
            }
            $this->_gridColumnAttrs = $columns;
        }
        return $this->_gridColumnAttrs;
    }
    private function getAllDrillDownRecords () {
        $this->generateSubgrid = true;
        // add columns in groups array not in columns array. Report query needs to include these
        // columns so that group headers can be properly inserted, even though these columns won't
        // be displayed to the user.
        $this->_columns = array_merge ($this->drillDownColumns, $this->getGroupAttrs ());
        $this->clearCache (array ('columns'));
        $qpg = new QueryParamGenerator (':generateSummationReportSubgrid');
        $primaryModel = $this->getPrimaryModel ();
        $primaryTableName = $primaryModel->tableName ();
        $joinClause = $this->getJoinClauses ($qpg);
        $whereClause = 'WHERE '.$this->buildSQLConditions (array (
            array (
                $this->getFilterConditions ('any', $this->anyFilters, $qpg), 'AND',
            ),
            array (
                $this->getFilterConditions ('all', $this->allFilters, $qpg), 'AND',
            ),
            array (
                $this->getPermissionsCondition ($qpg), 'AND',
            )
        ));
        // drill down query is ordered by specified groups, allowing group headers to be
        // inserted directly above the group that they correspond to
        $groupByClause = $this->getGroupByClause ();
        $orderByClause = preg_replace ('/GROUP/', 'ORDER', $groupByClause);
        $drillDownOrder = $this->getSortOrderSQL ($this->orderBy);
        if ($drillDownOrder) {
            $orderByClause .= 
                ($orderByClause !== '' ? ', ' : '').$drillDownOrder;
        }
        if (count ($this->drillDownColumns)) {
            $selectClause = $this->buildSelectClause ($this->getColumns ());
            // indent results by adding empty column
            $selectClause = preg_replace (
                '/SELECT/', 'SELECT "" as '.self::EMPTY_ALIAS.', ', $selectClause);
            $query = $this->buildQueryCommand (
                $selectClause, $primaryTableName, $joinClause, $whereClause, null, null,
                $orderByClause);
            //AuxLib::debugLogR ($query->getText ());
            // don't fetch associative since field names might repeat (as a result of a join)
            $reportRecords = $query->queryAll (true, $qpg->getParams ());
        } else {
            $reportRecords = array ();
        }
        $formattedHeaders = array_merge (array (''), array_slice (
            $this->getFormattedColumnHeaders (), 0, count ($this->drillDownColumns)));
        $this->generateSubgrid = false;
        return array ($reportRecords, $formattedHeaders);
    }
    /**
     * Format aggregate column headers for grid view 
     * @param string $fn
     * @param string $label
     */
    private function formatAggregateColumnHeader ($fn, $label) {
        $header = '';
        switch ($fn) {
            case 'count':
                $header = CHtml::encode (Yii::t('reports', 'Count'));
                break;
            case 'avg':
                $header = CHtml::encode (Yii::t('reports', 'Average'))." $label";
                break;
            case 'sum':
                $header = "$label ".CHtml::encode (Yii::t('reports', 'Sum'));
                break;
            case 'max':
                $header = CHtml::encode (Yii::t('reports', 'Max'))." $label";
                break;
            case 'min':
                $header = CHtml::encode (Yii::t('reports', 'Min'))." $label";
                break;
            default:
                throw new CException ('Invalid aggregate function name '.$fn);
                break;
        }
        return $header;
    }
    protected function getAttributeLabel (CModel $model, $attribute, array $fns) {
        if ($attribute === '*') $label = '*';
        else $label = parent::getAttributeLabel ($model, $attribute, $fns);
        $aggregateFn = null;
        foreach ($fns as $fn) {
            if (in_array ($fn, array ('avg', 'sum', 'min', 'max', 'count'))) {
                $aggregateFn = $fn;
                break;
            }
        }
        if ($aggregateFn) {
            $label = $this->formatAggregateColumnHeader ($aggregateFn, $label);
        }
        return $label;
    }
    /**
     * @return array CGridView columns corresponding to $_gridColumnAttrs
     */
    private function getGridViewColumns () {
        $columns = $this->getGridColumnAttrs ();
        $columns = array_combine ($columns, $this->getFormattedColumnHeaders ());
        $gridColumns = array ();
        foreach ($columns as $col => $header) {
            list ($columnAttrModel, $columnAttr, $fns) = $this->getModelAndAttr ($col);
            $column = array (
                'name' => $col,
                'header' => $header,
                'type' => 'raw',
                'fns' => $fns,
                'attribute' => $columnAttr !== '*' ? $columnAttr : null,
                'modelType' => get_class ($columnAttrModel),
            );
        
            $gridColumns[] = $column;
        }
        return $gridColumns;
    }
    /**
     * @return array parameters which can be used to instantiate report grid view 
     */
    private function getSubgridGridViewParams ($reportRecords) {
        $primaryModel = $this->getPrimaryModel ();
        // build columns array for CGridView
        $gridColumns = $this->getGridViewColumns ();
        $reportDataProvider = new CArrayDataProvider($reportRecords, array(
            'id' => $this->getGridId (),
            'keyField' => false, 
            'pagination' => array('pageSize'=>Profile::getResultsPerPage()),
        ));
        // hack to get pagination links to generate properly
        $pathInfo = Yii::app()->request->pathInfo;
        $matches = array ();
        $params = $_GET;
        if (preg_match ('/reports\/(?:id\/)?(\d+)$/', $pathInfo, $matches)) {
            $id = $matches[1];
            $params['id'] = $id;
            $pathInfo = 'reports/view';
        }
        $reportDataProvider->pagination->route = $pathInfo;
        $reportDataProvider->pagination->params = $params;
        return array (
            'dataProvider' => $reportDataProvider,
            'id' => $this->getGridId (),
            'columns' => $gridColumns,
        );
    }
    /**
     * Use related models to format column headers
     * @return array headers
     */
    protected function getFormattedColumnHeaders ($refresh = false) {
        if (!isset ($this->_formattedColumnHeaders) || $refresh) {
            $formattedHeaders = array ();
            $columns = $this->getGridColumnAttrs ();
            foreach ($columns as $col) {
                list ($columnAttrModel, $columnAttr, $fns) = $this->getModelAndAttr ($col);
                $header = $this->getAttributeLabel ($columnAttrModel, $columnAttr, $fns);
                $formattedHeaders[$col] = $header;
            }
            $this->_formattedColumnHeaders = $formattedHeaders;
        }
        return $this->_formattedColumnHeaders;
    }
    /**
     * @param array $groupHeaders
     * @return array parameters which can be used to instantiate report grid view 
     */
    private function getSummationReportGridViewParams (array $groupHeaders, $gridId=null) {
        $primaryModel = $this->getPrimaryModel ();
        // build columns array for CGridView
        $gridColumns = array ();
        if (count ($this->drillDownColumns)) {
            $gridColumns[] = array ( // add column which will contain subgrid expansion buttons
                'name' => 'subgrid-expand-button-column',
                'header' => '',
                'htmlOptions' => array (
                    'class' => 'subgrid-expand-button-container',
                ),
            );
        }
        $gridColumns = array_merge (
            $gridColumns, $this->getGridViewColumns ()); 
        $reportDataProvider = new CArrayDataProvider($groupHeaders,array(
            'id' => $gridId ? $gridId : $this->getGridId (),
            'keyField' => false, 
            'pagination' => array('pageSize'=>Profile::getResultsPerPage()),
        ));
        $reportDataProvider->pagination->route = Yii::app()->request->pathInfo;
        $reportDataProvider->pagination->params = $_GET;
        return array (
            'dataProvider' => $reportDataProvider,
            'id' => $gridId ? $gridId : $this->getGridId (),
            'groupAttrs' => $this->getGroupAttrs (),
            'columns' => $gridColumns,
            'reportConfig' => $_GET,
        );
    }
    /**
     * @return string group by clause for report records query
     */
    private function getGroupByClause () {
        $groupByClause = '';
        // Additionally group by any attributes which were used in group conditions
        // but not specified explicitly as a group
        $havingGroups = array_map(
            function($a) { return array($a['name'], 'asc'); },
            array_merge ($this->groupsAnyFilters, $this->groupsAllFilters)
        );
        $groups = array_merge ($this->groups, array_filter($havingGroups,function($a){
            return strpos($a[0], '(') === false;
        }));
        if (count ($groups)) $groupByClause .= 'GROUP BY ';
        for ($i = 0; $i < count ($groups); $i++) {
            $groupAttr = $groups[$i][0];
            $groupDirection = $groups[$i][1];
            $alias = $this->getAliasedAttr ($groupAttr);
            $groupByClause .= $alias;
            if ($groupDirection === 'desc') {
                $groupByClause .= ' DESC';
            }
            if ($i !== count ($groups) - 1) {
                $groupByClause .= ', ';
            }
        }
        return $groupByClause;
    }
    private function isAggregate ($col) {
        $regex = '/^('.implode ('|', SummationReportFormModel::$validAggregateFns).'|count)'.
            '\(.*\)$/';
        return preg_match ($regex, $col);
    }
}
