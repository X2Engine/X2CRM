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




abstract class X2Report extends X2Widget {

    const HIDDEN_ID_ALIAS = '__$hiddenIdAlias$__';

    const EMPTY_ALIAS = '__$empty$__';

    const CACHE_GENERATED_REPORT = false;

    /**
     * Returns array of Action Models that need a special join condition.
     * (See function getJoinClauses() for usage)
     * JustinT Sept. 30th
     */
    const ACTION_RELATION_CHECK = array('ActionMetaData', 'ActionText');
     
    /**
     * @var string $primaryModelType 
     */
    public $primaryModelType;

    /**
     * @var bool $export
     */
    public $export; 

    /**
     * @var bool $print
     */
    public $print; 

    /**
     * @var bool $print
     */
    public $email; 

    /**
     * @var array $orderBy 
     */
    public $orderBy;

    /**
     * @var array $allFilters 
     */
    public $allFilters;

    /**
     * @var array $anyFilters 
     */
    public $anyFilters;
    
    
    /**
     * @var array $relativeFilters 
     */
    public $relativeFilters;

    /**
     * @var array $subTotals
     */
    public $subTotals;

    /**
     * @var bool $getRawData
     */
    public $getRawData = false; 

    /**
     * @var bool $includeTotalsRow
     */
    public $includeTotalsRow;

    protected $_joinAliases;

    protected $_gridColumnAttrs;

    /**
     * @var array $_relatedModelsByLinkField
     */
    protected $_relatedModelsByLinkField; 

    /**
     * @var $_gridId id of CGridView instance
     */
    public $_gridId = 'generated-report'; 

    /**
     * @var CModel $_primaryModel
     */
    private $_primaryModel; 

    private $_anyFilterAttrs;

    private $_allFilterAttrs;
    
    private $_relativeFilterAttrs;

    private $_subTotalAttrs;

    /**
     * Generate and render the report
     */
    abstract public function generate ();

    abstract protected function getRelatedModelsByLinkField ($refresh = false);

    public function behaviors () {
        return array_merge (parent::behaviors (), array (
            'ReportsAttributeParsingBehavior' => array (
                'class' => 'application.modules.reports.components.ReportsAttributeParsingBehavior'
            ),
        ));
    }

    public function run () {
        if (YII_DEBUG && self::CACHE_GENERATED_REPORT) {
            $cache = Yii::app()->cache;
            $cacheKey = serialize ($_GET);
            $report = $cache->get ('generatedReport'.$cacheKey);
            if ($report) {
                echo $report;
                return;
            } else {
                ob_start ();
                $this->generate ();
                $report = ob_get_clean ();
                $cache->set ('generatedReport'.$cacheKey, $report, 60 * 5);
                echo $report;
            }
        } else {
            $this->generate ();
        }
    }

    /**
     * Given non-empty data, returns the indices of the columns specified in $columns
     */
    protected function getColumnIndices (array $data, array $columns) {
        assert (count ($data) > 0);

        $columnIndices = array ();
        $sampleRow = $data[0];
        $rowKeys = array_keys ($sampleRow);
        foreach ($columns as $col) {
            $index = array_search ($col, $rowKeys);
            assert ($index !== null); // validate column name
            $columnIndices[$col] = $index;
        }
        return $columnIndices;
    }

    /**
     * @return array names of attributes specified in any filters
     */
    protected function getAnyFilterAttrs () {
        if (!isset ($this->_anyFilterAttrs)) {
            $this->_anyFilterAttrs = $this->extractFilterNames ($this->anyFilters);
        }
        return $this->_anyFilterAttrs;
    }

    /**
     * @return array names of attributes specified in all filters
     */
    protected function getAllFilterAttrs () {
        if (!isset ($this->_allFilterAttrs)) {
            $this->_allFilterAttrs = $this->extractFilterNames ($this->allFilters);
        }
        return $this->_allFilterAttrs;
    }
    
    
    /**
     * @return array names of attributes specified in Relative Filters
     */
    protected function getRelativeFilterAttrs () {
        if (!isset ($this->_relativeFilterAttrs)) {
            $this->_relativeFilterAttrs = $this->extractFilterNames ($this->relativeFilters);
        }
        return $this->_relativeFilterAttrs;
    }

    /**
     * Should be overridden in child class to format data for export, print, and email.
     * @param array $data 
     * @return array formatted data
     */
    protected function formatData (array $data) {
        return $data;
    }

    /**
     * Export report to a .csv file, send the file to the client, unlink the file, and exit
     * @param array $data
     */
    protected function export (array $data) {
        $file = 'reportExport'.time ().'.csv';

        $filePath = Yii::app()->controller->safePath($file);
        $fp = fopen ($filePath, "w+");
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        if (!Yii::app()->controller->sendFile ($file, true)) {
            throw new CHttpException (500, Yii::t('reports', 'Export failed'));
        }
    }

//    protected function printReport (array $data) {
//        $data = $this->formatData ($data);
//        $headerRow = $data[0];
//        unset ($data[0]);
//        $columns = array ();
//        foreach ($headerRow as $colHeader) {
//            $columns[] = array (
//                'name' 
//            );
//        }
//        $reportDataProvider = new CArrayDataProvider ($reportRecords,array(
//            'id' => $this->_gridId,
//            'keyField' => false, 
//            'pagination' => array ('pageSize'=>PHP_INT_MAX),
//        ));
//
//        $this->renderPartial ('printReport', array (
//            'dataProvider' => $reportDataProvider,
//        ));
//    }

    /**
     * @return array unique alias to use in report query when referring to joined table, indexed
     *  by attribute name
     */
    protected function getJoinAliases ($refresh = false) {
        if (!isset ($this->_joinAliases) || $refresh) {
            $joinOnFields = array_keys (
                $this->getRelatedModelsByLinkField ()); // names of fields to join on 
            $aliases = array ();
            foreach ($joinOnFields as $field) {
                $aliases[$field] = 't'.count ($aliases); 
            }
            $this->_joinAliases = $aliases;
        }
        return $this->_joinAliases;
    }

    /**
     * @return string JOIN clauses for report query
     */
    protected function getJoinClauses (QueryParamGenerator $qpg) {
        $joinAliases = $this->getJoinAliases ();
        $relatedModelsByLinkField = $this->getRelatedModelsByLinkField ();

        assert (count ($relatedModelsByLinkField) === count ($joinAliases));
        $joinStmt = '';
        $i = 0;
        foreach ($relatedModelsByLinkField as $linkField => $relatedModel) {
            if ($this->primaryModelType === 'Actions' && 
                (in_array ($linkField, array_keys (X2Model::getModelNames ())) ||
                (in_array ($linkField, self::ACTION_RELATION_CHECK)))
	       ) {

                if (!in_array ($linkField, self::ACTION_RELATION_CHECK)) {
                    $primaryModelField = $linkField;
                    $joinStmt .= 
                        " JOIN {$relatedModel->tableName ()} as {$joinAliases[$linkField]} 
                            ON t.associationId={$joinAliases[$linkField]}.id AND
                                t.associationType={$qpg->nextParam (
                                    X2Model::getAssociationType ($linkField))}";
                } else {
                    $textAlias = $joinAliases[$linkField];
                    $joinStmt .= 
                        " LEFT JOIN {$relatedModel->tableName ()} as $textAlias
                            ON $textAlias.actionId=t.id";
                }
            } else {
            //if (in_array ($linkField, $leftJoinOn)) {
                $joinStmt .= ' LEFT ';
            //}
                $joinStmt .= 
                    "JOIN {$relatedModel->tableName ()} as {$joinAliases[$linkField]} 
                        ON t.$linkField={$joinAliases[$linkField]}.nameId ";
            }
        }
        return $joinStmt;
    }

    /**
     * Used to determine which tables belong in a reports JOIN statement. 
     * @param array $attributes The relevant attributes (these could be attributes selected 
     *  in the filters, row/column fields, columns, groups, etcetera)
     * @return array static models indexed by link field name
     */
    protected function _getRelatedModelsByLinkField (array $attributes) {
        $model = $this->getPrimaryModel ();
        $relatedModelsByLinkField = array ();

        foreach ($attributes as $attr) {
            list ($attr, $fns) = $this->parseFns ($attr);
            $pieces = explode (".", $attr);
            if (count ($pieces) > 1) {
                if ($this->primaryModelType === 'Actions') {
                    $modelName = $pieces[0];
                    $relatedModelsByLinkField[$modelName] = $this->_getRelatedModel ($modelName);
                } else {
                    $linkField = $pieces[0];
                    $relatedModelsByLinkField[$linkField] = $this->_getRelatedModel ($linkField);
                }
            }
        }
        return $relatedModelsByLinkField;
    }

    /**
     * @param string $type {'any', 'all'}
     * @param array $filters As serialized by X2ConditionList.js
     * @return string mysql where clause condition 
     * @throws CException
     */
    protected function getFilterConditions (
        $type, array $filters, QueryParamGenerator $qpg) {

        if (!in_array ($type, array ('any', 'all'))) {
            throw new CException ('invalid filter type: '.$type);
        }
        $joinAliases = $this->getJoinAliases ();
        $filterConditions = '('; 
        $separator = $type === 'any' ? 'OR' : 'AND';

        foreach ($filters as $filter) {
            if (count ($filter) === 2) { // a kludge to allow support for nested filters
                $filterConditions .= $this->getFilterConditions ($filter[0], $filter[1], $qpg);
                continue;
            }

            if ($filterConditions !== '(') {
                $filterConditions .= " $separator ";
            }
            $attribute = $filter['name'];
            $field = $this->getAttrField ($attribute);
            $value = $filter['value'];

            $attribute = $this->getAliasedAttr ($attribute);

            $operator = $filter['operator'];

            if ($field && $field->type === 'dropdown' && $field->getDropdown () &&
                $field->getDropdown ()->multi) {
                $attribute = 
                    "(trim(leading '[\"' from (trim(trailing '\"]' from $attribute))))";
            }

            switch($operator){
                case '=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                    $filterConditions .= "$attribute$operator{$qpg->nextParam ($value)}";
                    break;
                case '<>': 
                    $filterConditions .= 
                        "($attribute IS NULL OR $attribute!={$qpg->nextParam ($value)})";
                    break;
                case 'notEmpty':
                    $filterConditions .= 
                        "($attribute IS NOT NULL AND $attribute!='')";
                    break;
                case 'empty':
                    $filterConditions .= 
                        "($attribute IS NULL OR $attribute='')";
                    break;
                case 'list':
                case 'notList':
                    if (is_string ($value)) {
                        if (StringUtil::isJson ($value)) {
                            $value = CJSON::decode ($value);
                        } else {
                            $value = array_map (function ($elem) { 
                                return trim ($elem); }, explode (',', $value));
                        }
                    }
                    $filterConditions .= "$attribute ".($operator === 'list' ? 'IN' : 'NOT IN').' '.
                        $qpg->bindArray ($value, true);
                    break;
                case 'noContains':
                    $value = self::escapeLikeExprValue ($value);
                    $filterConditions .= "$attribute NOT LIKE {$qpg->nextParam ($value)}";
                    break;
                case 'contains':
                    $value = self::escapeLikeExprValue ($value);
                    $filterConditions .= "$attribute LIKE {$qpg->nextParam ($value)}";
                    break;
                default:
                    throw new CException ('invalid operator: '.$operator);
            }
        }
        if ($filterConditions === '(') {
            $filterConditions .= 'TRUE';
        }
        $filterConditions .= ')'; 
        return $filterConditions;
    }
    
       /**
     * @param int the month 
     * @return int the quarter for that month
     * 
     */
    
    private function getCurQuarter($month){
        if($month < 4){
            return 1;
        }elseif($month < 7){
            return 2;
        }elseif($month < 10){
            return 3;
        }else{
            return 4;
        }
    }
    

    

        /* 
    * days_in_month($month, $year) 
    * Returns the number of days in a given month and year, taking into account leap years. 
    * 
    * $month: numeric month (integers 1-12) 
    * $year: numeric year (any integer) 
    * 
    * Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer. 
    * Post: none 
    */ 
    // corrected by ben at sparkyb dot net 
    private function days_in_month($month, $year) 
    { 
    // calculate number of days in a month 
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); 
    } 

    
         /**
     * @param array where the dates will be stored 
     * @param the amount of quarters distance from current quarter
     * 
     */
    
    
    
    
    private function getFiscalQuarter($dates, $timeScale){
        $curMonth = date("n");
        $curYear = date("Y");
        $numOfYears = $timeScale%4;
        $numberOfQart = $timeScale - (4 * $numOfYears);
        $curQuarter = $this->getCurQuarter($curMonth);
        $wantQuarter = $curQuarter + $numberOfQart;
        if($wantQuarter > 4){
            $curYear++;
            $wantQuarter = $wantQuarter - 4;
        }
        if($wantQuarter < 1){
            $curYear--;
            $wantQuarter = $wantQuarter + 4;
        }
        $wantYear = $curYear + $numberOfQart;
        $startMonth = ($wantQuarter * 3) - 2;
        $endMonth = ($wantQuarter * 3);
        $numOfDays = $this->days_in_month( $endMonth, $wantYear);
       
        $dates['start'] = strtotime($wantYear . "/" . $startMonth . "/1" );
        $dates['end'] = strtotime($wantYear . "/" . $endMonth . "/" . $numOfDays ) + 86400;
        
        return $dates;
    }
    
    private function getCurFiscalYear(){
        if ( date('m') > 6 ) {
           return (date('Y') + 1);
        }
        elseif ( date('m') < 6 ){
            return date('Y');
        }else{
            return NULL;
        }
        
        
    }
    
    private function getMonthDates($month, $year = null){
        if(!isset($year)){
            $year = date('Y');
        }
        $dates = array( 'start' => NULL , 'end' => NULL);
        $dates['start'] =  strtotime($month . "/1/" . $year);
        
        $numOfDays = ($month == 2) ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); 
        $dates['end'] =  $dates['start'] + ($numOfDays * 86400);
        return $dates;
        
    }
    
        private function getweekDates($scale){
            $dates = array( 'start' => NULL , 'end' => NULL);
            $dates['start'] = strtotime("last Sunday");
            //86400 is a day, so 7 days is 604800
            $dates['end'] = $dates['start'] + (($scale + 1) * 604800);
            $dates['start'] = $dates['start'] + ($scale * 604800);
            
            return $dates;
    }
    
    
    private function getEpocTime($timeName){
        $dates = array( 'start' => NULL , 'end' => NULL);
        $curYear = date("Y");
        $curMonth = date("n");
        $dayValue = 86400;
        $fiscalYear = $this->getCurFiscalYear();
        if($timeName == 'Current FY'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear - 1));
            $dates['end'] =  strtotime("September 30 " . $fiscalYear) + $dayValue;
        }elseif($timeName == 'Previous FY'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear - 2));
            $dates['end'] =  strtotime("September 30 " . ($fiscalYear - 1)) + $dayValue;
        }elseif($timeName == '2 FY Ago'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear - 3));
            $dates['end'] =  strtotime("September 30 " . ($fiscalYear - 2)) + $dayValue;
        }elseif($timeName == 'Next FY'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear));
            $dates['end'] =  strtotime("September 30 " . ($fiscalYear + 1)) + $dayValue;
        }elseif($timeName == 'Current FQ'){
            $dates = $this->getFiscalQuarter($dates , 0);
        }elseif($timeName == 'Next FQ'){
            $dates =  $this->getFiscalQuarter($dates , 1);
        }elseif($timeName == 'Previous FQ'){
            $dates =  $this->getFiscalQuarter($dates , -1);
        }elseif($timeName == 'Current CY'){
            $dates['end'] =  strtotime("december 31 " . ($curYear)) + $dayValue;
            $dates['start'] =  strtotime("january 1 " . ($curYear));
        }elseif($timeName == 'Previous CY'){
            $dates['end'] =  strtotime("december 31 " . ($curYear - 1)) + $dayValue;
            $dates['start'] =  strtotime("january 1 " . ($curYear - 1));
        }elseif($timeName == '2 CY Ago'){
           $dates['end'] =  strtotime("december 31 " . ($curYear - 2)) + $dayValue;
            $dates['start'] =  strtotime("january 1 " . ($curYear - 2));            
        }elseif($timeName == 'Next CY'){
           $dates['end'] =  strtotime("december 31 " . ($curYear + 1)) + $dayValue;
            $dates['start'] =  strtotime("january 1 " . ($curYear + 1));            
        }elseif($timeName == 'Current CQ'){
            $dates =  $this->getFiscalQuarter($dates , 0);
        }elseif($timeName == 'Next CQ'){
            $dates =  $this->getFiscalQuarter($dates , 1);
        }elseif($timeName == 'Previous CQ'){
            $dates =  $this->getFiscalQuarter($dates , -1);
        }elseif($timeName == 'Last Month'){
            $targetMonth =  $curMonth - 1;
            if($targetMonth == 0){
                $targetMonth = 12;
                $curYear --;
            }
           $dates = $this->getMonthDates($targetMonth , $curYear);
        }elseif($timeName == 'This Month'){
           $dates = $this->getMonthDates($curMonth , $curYear);
        }elseif($timeName == 'Next Month'){
            
            $targetMonth =  $curMonth + 1;
            if($targetMonth == 13){
                $targetMonth = 1;
                $curYear ++;
            }
           $dates = $this->getMonthDates($targetMonth , $curYear);
            
        }elseif($timeName == 'Last Week'){
            $dates = $this->getweekDates(-1);
        }elseif($timeName == 'This Week'){
            $dates = $this->getweekDates(0);
        }elseif($timeName == 'Next Week'){
            $dates = $this->getweekDates(1);
        }elseif($timeName == 'Yesterday'){
            $dates['start'] = strtotime("Yesterday");
            $dates['end'] = strtotime("Today");
        }elseif($timeName == 'Today'){
            $dates['start'] = strtotime("Today");
            $dates['end'] = $dates['start'] + 86400 ;
            
        }elseif($timeName == 'Tomorrow'){
            $dates['start'] = strtotime("Tomorrow");
            $dates['end'] = $dates['start'] + 86400 ;
            
        }elseif($timeName == 'Last 7 Days'){
            
            $dates['end'] = strtotime("today");
            $dates['start'] = strtotime("-7 days", strtotime("today")) ;             
        }elseif($timeName == 'Last 30 Days'){
            
            $dates['end'] = strtotime("today");
            $dates['start'] =  strtotime("-30 days", strtotime("today")) ;            
        }elseif($timeName == 'Last 60 Days'){
            
            $dates['end'] = strtotime("today");
            $dates['start'] = strtotime("-60 days", strtotime("today")) ;            
        }elseif($timeName == 'Last 90 Days'){
            
            $dates['end'] = strtotime("today");
            $dates['start'] =  strtotime("-90 days", strtotime("today")) ;          
        }elseif($timeName == 'Last 120 Days'){
            
            $dates['end'] = strtotime("tomorrow");
            $dates['start'] = strtotime("-120 days", strtotime("today")) ;            
        }elseif($timeName == 'Next 7 Days'){
            
            $dates['start'] = strtotime("tomorrow");
            $dates['end'] = strtotime("+8 days", strtotime("today")) ;            
        }elseif($timeName == 'Next 30 Days'){
            
            $dates['start'] = strtotime("tomorrow");
            $dates['end'] = strtotime("+31 days", strtotime("today")) ;            
        }elseif($timeName == 'Next 60 Days'){
            
            $dates['start'] = strtotime("tomorrow");
            $dates['end'] = strtotime("+61 days", strtotime("today")) ;            
        }elseif($timeName == 'Next 90 Days'){
            
            $dates['start'] = strtotime("tomorrow");
            $dates['end'] = strtotime("+91 days", strtotime("today")) ;            
        }elseif($timeName == 'Next 120 Days'){
            $dates['start'] = strtotime("tomorrow");
            $dates['end'] = strtotime("+121 days", strtotime("today"));           
        }
        //goning to subtract one sec so midnight is included in dates range
        $dates['start'] = $dates['start'] - 1;
        return $dates;
    }
    
    
        /**
     * @param string $type {'any', 'all'}
     * @param array $filters As serialized by X2ConditionList.js
     * @return string mysql where clause condition 
     * @throws CException
     */
    protected function getRelativeFilterConditions (
        $type, array $filters, QueryParamGenerator $qpg) {

        if (!in_array ($type, array ('any', 'all'))) {
            throw new CException ('invalid filter type: '.$type);
        }
        $joinAliases = $this->getJoinAliases ();
        $filterConditions = '('; 
        $separator = $type === 'any' ? 'OR' : 'AND';

        foreach ($filters as $filter) {
            if(isset($filter['value'])){
                if ($filterConditions !== '(') {
                    $filterConditions .= " $separator ";
                }
                $attribute = $filter['name'];
                $field = $this->getAttrField ($attribute);
              
                $Dates = $this->getEpocTime($filter['value']);
                $start = $Dates['start'];
                $end = $Dates['end'];

                $attribute = $this->getAliasedAttr ($attribute);


                 $filterConditions .= "$attribute > $start AND $attribute < $end";
            }
        }
        if ($filterConditions === '(') {
            $filterConditions .= 'TRUE';
        }

        $filterConditions .= ')'; 
        return $filterConditions;
    }
    

    /**
     * Taken from CDbCriteria's addSearchCondition ()
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    protected function escapeLikeExprValue ($value) {
        return '%'.strtr($value,array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\')).'%';
    }

    protected function getAttributeLabel (CModel $model, $attribute, array $fns) {
        $label = '';
        if (get_class ($model) !== $this->primaryModelType && 
            is_subclass_of (get_class ($model), 'X2Model')) {

            $label = X2Model::getModelTitle (get_class ($model)) . ': ';
        }
        $label .= $model->getAttributeLabel ($attribute);

        foreach ($fns as $fn) {
            if (in_array ($fn, array ('second', 'minute', 'hour', 'day', 'month', 'year'))) {
                $label .= ' ('.$fn.')';
            }
        }
        return $label;
    }

    /**
     * @param string $attr   
     * @return string aliased version of attribute 
     */
    protected function getAliasedAttr ($attr) {
        $joinAliases = $this->getJoinAliases ();
        list ($attr, $fns) = $this->parseFns ($attr);

        $pieces = explode ('.', $attr);
        if ($attr === '*') {
            $aliasedAttr = $attr;
        } elseif (count ($pieces) > 1) {
            $aliasedAttr = $joinAliases[$pieces[0]].'.'.$pieces[1];
        } else {
            $aliasedAttr = 't.'.$attr;
        }
        foreach ($fns as $fn) {
            if (in_array ($fn, array ('second', 'minute', 'hour', 'day', 'month', 'year'))) {
                $aliasedAttr = $fn.'(FROM_UNIXTIME('.$aliasedAttr.'))';
            } else { // in_array ($fn, array ('avg', 'sum', 'min', 'max'))
                $aliasedAttr = $fn.'('.$aliasedAttr.')';
            } 
        }
        return $aliasedAttr;
    }

    /**
     * @return array names of attributes to filter on 
     */
    protected function extractFilterNames ($filters) {
        $names = array ();
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if (count ($filter) === 2  && !isset($filter['name'])) {
                    $names = array_merge ($names, $this->extractFilterNames ($filter[1]));
                } else {
                    $names[] = $filter['name'];
                }
            }
        }
        return $names;
    }

    /**
     * @param array $anyFilterAttrs 
     * @param array $otherAttrs 
     * @return array names of fields on which the primary table should be left joined
     */
    protected function getLeftJoinFields (array $anyFilterAttrs, array $otherAttrs) {

        // link attributes which are present only in the any filters should be queried
        // with a left join. If, however, all any conditions are on attributes of the same
        // link type, the query should be an inner join 
        $linkFieldsInAnyFilters = array_unique (array_map (function ($attr) {
            return preg_replace ('/\..*$/', '', $attr); 
        }, array_filter ($anyFilterAttrs, function ($a) {
            return preg_match ('/\..*$/', $a);
        })));

        if (count ($linkFieldsInAnyFilters) > 1) {
            $leftJoinOn = $linkFieldsInAnyFilters;
        } else {
            $leftJoinOn = array (); 
        }
        return $leftJoinOn;
    }

      /**
     * @return string select clause for ungrouped report query. The records returned by this query
     *  get used for group drill down and an ungrouped grid view
     */
    protected function buildSelectClause (array $columns, $includeId=false) {
        $selectClause = 'SELECT ';
        $i = 0;
        foreach ($columns as $col) {
            if ($i++ !== 0) $selectClause .= ',';
            $matches = array ();
            $selectClause.= $this->getAliasedAttr ($col);
            $selectClause.= ' AS `'.addslashes ($col).'`';
        }
        if ($includeId) $selectClause .= ', t.id as '.self::HIDDEN_ID_ALIAS;
        return $selectClause;
    }
    
    /**
     * @return string order by clause constructed from orderBy property 
     */
    protected function getOrderByClause (array $attrs) {
        $sortOrder = $this->getSortOrderSQL ($attrs);
        if ($sortOrder !== '') {
            $orderByClause = 'ORDER BY ' . $sortOrder;
        } else {
            $orderByClause = '';
        }
        return $orderByClause;
    }

    /**
     * @return string User-specified sort orders formatted as SQL
     */
    protected function getSortOrderSQL (array $orderByAttrs) {
        $sortOrderSQL = '';
        $first = true;
        foreach ($orderByAttrs as $arr) {
            if (!$first) {
                $sortOrderSQL .= ', ';
            }
            $attr = $arr[0];
            $direction = $arr[1];
            $sortOrderSQL .= $this->getAliasedAttr ($attr);
            if ($direction === 'desc') {
                $sortOrderSQL .= ' DESC';
            }
            $first = false;
        }

        return $sortOrderSQL;
    }

    protected function getTotalsRow (array $reportRecords, array $columnAttrs) {
        $columnFields = $this->getAttrFields ($columnAttrs);
        $summationRow = array ();
        foreach ($reportRecords as $row) {
            $count = count ($row);
            foreach ($row as $attr => $val) {
                if ($attr === self::HIDDEN_ID_ALIAS) break;
                $field = $columnFields[$attr];
                if ($field !== null && !in_array ($field->type, array ('int', 'currency'))) {
                    $summationRow[$attr] = self::EMPTY_ALIAS;
                } elseif (isset ($summationRow[$attr])) {
                    $summationRow[$attr] += $val;
                } else {
                    $summationRow[$attr] = $val;
                }
            }
        }
        return $summationRow;
    }

    public function getAttrField ($attr) {
        list ($model, $modelAttr, $fns, $linkField) = $this->getModelAndAttr ($attr);
        return $model->getField ($modelAttr);
    }

    public function getAttrFields (array $attrs) {
        $fields = array ();
        foreach ($attrs as $attr) {
            list ($model, $modelAttr, $fns, $linkField) = $this->getModelAndAttr ($attr);
            if ($attr !== '*') {
                $fields[$attr] = $model->getField ($modelAttr);
            } else {
                $fields[$attr] = null;
            }
        }
        return $fields;
    }

    protected function getPermissionsCondition (QueryParamGenerator $qpg) {
        list ($permissionsCondition, $params) = $this->getPrimaryModel ()->getAccessSQLCondition ();
        $qpg->mergeParams ($params);
        return $permissionsCondition;
    }
    
    /**
     * @param array conditions and logical operators 
     */
    protected function buildSQLConditions (array $conditions ) { 
        $conditionsSQL = '';
        foreach ($conditions as $cond) {
            $this->addCondition ($conditionsSQL, $cond);
        }
        return $conditionsSQL;
    }

    /**
     * Constructs CDbCommand from constituent query clauses.  
     * @return CdbCommand
     */
    protected function buildQueryCommand (
        $selectClause, $primaryTableName, $joinClause, $whereClause, $groupByClause=null,
        $havingClause=null, $orderByClause=null) {
        $query = Yii::app()->db->createCommand ("
            $selectClause
            FROM $primaryTableName AS t 
            $joinClause
            $whereClause
            ".(isset ($groupByClause) ? $groupByClause : '')."
            ".(isset ($havingClause) ? $havingClause : '')."
            ".(isset ($orderByClause) ? $orderByClause : ''));

        return $query;
    }
   
    
    
    /**
     * @param string $conditionsSQL
     * @param array|string $condition
     */
    private function addCondition (&$conditionsSQL, $condition) {
        if (is_array ($condition)) {
            $separator = $condition[1];
            $condition = $condition[0];
        } else {
            $separator = 'AND';
        }
        if ($conditionsSQL !== '') {
            $conditionsSQL .= " $separator $condition";
        } else {
            $conditionsSQL = $condition;
        }
    }
    
    
}
