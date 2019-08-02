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






/**
 * This class is included as a pro action to generate a report of Accounts based
 * on user filters of Account fields. From here, this information can be exported
 * to a CSV {@link ExportAccountsReportAction} or a campaign can be generated
 * for the Contacts linked to these accounts {@link AccountCampaignAction}.
 */
class AccountsReportAction extends CAction {

    /**
     * This method is functionally identical to the {@link ServicesReportAction}
     * see there for more detailed comments on what's going on.
     */
    public function run(){
        $model = X2Model::model('Accounts');
        $_SESSION['accountsReport']=array();
        $_SESSION['accountsReport']['accountsReportFile']='accountsReport.csv';
        $dateRange = X2DateUtil::getDateRange();
        $sqlParams = array ();
        $dateFieldQuery = Yii::app()->db->createCommand()
                ->select('fieldName, attributeLabel')
                ->from('x2_fields')
                ->where('modelName="Accounts" AND (type="date" OR type="dateTime")')
                ->queryAll();
        $dateFields = array();
        foreach($dateFieldQuery as $row){
            $dateFields[$row['fieldName']] = $model->getAttributeLabel($row['fieldName']);
        }
        $dateRange = X2DateUtil::getDateRange();
        $dateFieldQuery = Yii::app()->db->createCommand()
                ->select('fieldName, attributeLabel')
                ->from('x2_fields')
                ->where('modelName="Accounts" AND (type="date" OR type="dateTime")')
                ->queryAll();
        $dateFields = array();
        foreach($dateFieldQuery as $row){
            $dateFields[$row['fieldName']] = $model->getAttributeLabel($row['fieldName']);
        }
        $fieldNames = Yii::app()->db->createCommand()
                ->select('fieldName')
                ->from('x2_fields')
                ->where('modelName="Accounts"')
                ->queryColumn();
        if(isset($_GET['dateField'], $_GET['start'], $_GET['end'], $_GET['range'])){
            if(isset($_GET['sort'])){
                $_SESSION['accountsReportSort'] = str_replace('.', ' ', $_GET['sort']);
            }
            $dateField = $_GET['dateField'];
            Accounts::checkThrowAttrError ($dateField);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $attributeConditions = "($dateField BETWEEN :startDate AND :endDate)";
            $sqlParams = array_merge ($sqlParams, 
                array (
                    ':startDate' => $startDate,
                    ':endDate' => $endDate));
            $criteria = new X2DbCriteria;
            $qpg = new QueryParamGenerator (':accountsReport');
            if(isset($_GET['Accounts'], $_GET['Accounts']['attribute'], $_GET['Accounts']['comparison'], $_GET['Accounts']['value'])){
                $filters = $_GET['Accounts'];
                for($i = 0; $i < count($filters['attribute']); $i++){
                    $attribute = $filters['attribute'][$i];
                    Accounts::checkThrowAttrError ($attribute);
                    $comparison = $filters['comparison'][$i];
                    $value = $filters['value'][$i];
                    foreach(X2Model::model('Accounts')->fields as $field){
                        if($field->fieldName == $attribute){
                            switch($field->type){
                                case 'date':
                                case 'dateTime':
                                    if(ctype_digit((string) $value) || (substr($value, 0, 1) == '-' && ctype_digit((string) substr($value, 1))))
                                        $value = (int) $value;
                                    else
                                        $value = strtotime($value);
                                    $dateType = true;
                                    break;
                                case 'link':
                                    if(!ctype_digit((string) $value))
                                        $value = Fields::getLinkId($field->linkType, $value); break;
                                case 'boolean':
                                case 'visibility':
                                    $value = in_array(strtolower($value), array('1', 'yes', 'y', 't', 'true')) ? 1 : 0;
                                    break;
                                case 'dropdown':
                                    $value = $field->parseValue (explode (',', $value));
                                    break;
                            }
                            break;
                        }
                    }
                    switch($comparison){
                        case '=':
                            $criteria->compare($attribute, $value, false, 'AND', true, false);
                            break;
                        case '>':
                            $criteria->compare($attribute, '>='.$value, true, 'AND', true, false);
                            break;
                        case '<':
                            $criteria->compare($attribute, '<='.$value, true, 'AND', true, false);
                            break;
                        case '<>': // must test for != OR is null, because both mysql and yii are stupid
                            $criteria->addCondition(
                                '('.$attribute.' IS NULL OR '.$attribute.'!='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount.')');
                            $criteria->params[CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++] = $value;
                            break;
                        case 'notEmpty':
                            $criteria->addCondition($attribute.' IS NOT NULL AND '.$attribute.'!=""');
                            break;
                        case 'empty':
                            $criteria->addCondition('('.$attribute.'="" OR '.$attribute.' IS NULL)');
                            break;
                        case 'list':
                        case 'notList':
                            if (is_string ($value)) {
                                if (StringUtil::isJson ($value)) {
                                    $value = CJSON::decode ($value);
                                    $attribute = 
                                        "(trim(leading '[\"' from (trim(trailing '\"]' from $attribute))))";
                                } else {
                                    $value = array_map (function ($elem) { 
                                        return trim ($elem); }, explode (',', $value));
                                }
                            }
                            $criteria->addCondition ("$attribute ".($comparison === 'list' ? 'IN' : 'NOT IN').' '.
                                $qpg->bindArray ($value, true));
                            break;
                        case 'noContains':
                            $criteria->compare($attribute, '<>'.$value, true);
                            break;
                        case 'contains':
                        default:
                            $criteria->compare($attribute, $value, true);
                    }
                }
                $attributeConditions.=" AND ".$criteria->condition;
            }
            $sql = 'SELECT * FROM x2_accounts WHERE '.$attributeConditions;
            $params = array_merge (
                $criteria->params, 
                $sqlParams,
                $qpg->getParams ()
            );
            $count = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_accounts')
                ->where(
                    $attributeConditions, 
                    $params 
                )
                ->queryScalar();

            $dataProvider = new CSqlDataProvider($sql, array(
                        'totalItemCount' => $count,
                        'params' => $params,
                        'sort' => array(
                            'attributes' => $fieldNames,
                            'defaultOrder' => isset($_SESSION['accountsReportSort']) ? $_SESSION['accountsReportSort'] : "$dateField ASC"
                        ),
                        'pagination' => array(
                            'pageSize' => Profile::getResultsPerPage(),
                        ),
                    ));
        }else{
            unset($_SESSION['accountsReportSort']);
        }
        $this->controller->render('accountsReport', array(
            'dateRange' => $dateRange,
            'dateFields' => $dateFields,
            'dataProvider' => isset($dataProvider) ? $dataProvider : null,
        ));
    }

}
