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
 * This class is functionally identical to {@link ExportServiceReportAction} and
 * full documentation can be found there.
 */
class ExportAccountsReportAction extends CAction {

    public function run(){
        $this->attachBehavior('ImportExportBehavior', array('class'=>'application.components.behaviors.ImportExportBehavior'));
        $page = $_GET['page'];
        $gridviewSettings = json_decode(Yii::app()->params->profile->gridviewSettings, true);

        // remove x2gridview columns which don't correspond to account attributes
        $accountColumns = array_keys($gridviewSettings['accounts']);
        $accountAttrs = Services::model ()->attributeNames ();
        $accountColumns = array_filter (
            $accountColumns, function ($a) use ($accountAttrs) {
                return (in_array ($a, $accountAttrs));
            }
        );

        $file = $_SESSION['accountsReport']['accountsReportFile'];
        $filePath = $this->safePath($file);
        $fields = X2Model::model('Accounts')->getFields();
        if($page == 0){
            $fp = fopen($filePath, 'w+');
            fputcsv($fp, $accountColumns);
        }else{
            $fp = fopen($filePath, 'a+');
        }
        $fieldNames = Yii::app()->db->createCommand()
                ->select('fieldName')
                ->from('x2_fields')
                ->where('modelName="Accounts"')
                ->queryColumn();
        $_GET = json_decode($_SESSION['accountsReport']['GET'], true);
        $dateRange = X2DateUtil::getDateRange();
        if(isset($_GET['dateField'], $_GET['start'], $_GET['end'], $_GET['range'])){
            if(isset($_GET['sort'])){
                $_SESSION['accountsReportSort'] = str_replace('.', ' ', $_GET['sort']);
            }
            $dateField = $_GET['dateField'];
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $attributeConditions = "($dateField BETWEEN $startDate AND $endDate)";
            $criteria = new CDbCriteria;
            if(isset($_GET['Accounts'], $_GET['Accounts']['attribute'], $_GET['Accounts']['comparison'], $_GET['Accounts']['value'])){
                $filters = $_GET['Accounts'];
                for($i = 0; $i < count($filters['attribute']); $i++){
                    $attribute = $filters['attribute'][$i];
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
                                    break;
                                case 'link':
                                    if(!ctype_digit((string) $value))
                                        $value = Fields::getLinkId($field->linkType, $value); break;
                                case 'boolean':
                                case 'visibility':
                                    $value = in_array(strtolower($value), array('1', 'yes', 'y', 't', 'true')) ? 1 : 0;
                                    break;
                            }
                            break;
                        }
                    }
                    switch($comparison){
                        case '=':
                            $criteria->compare($attribute, $value, false);
                            break;
                        case '>':
                            $criteria->compare($attribute, '>='.$value, true);
                            break;
                        case '<':
                            $criteria->compare($attribute, '<='.$value, true);
                            break;
                        case '<>': // must test for != OR is null, because both mysql and yii are stupid
                            $criteria->addCondition('('.$attribute.' IS NULL OR '.$attribute.'!='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount.')');
                            $criteria->params[CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++] = $value;
                            break;
                        case 'notEmpty':
                            $criteria->addCondition($attribute.' IS NOT NULL AND '.$attribute.'!=""');
                            break;
                        case 'empty':
                            $criteria->addCondition('('.$attribute.'="" OR '.$attribute.' IS NULL)');
                            break;
                        case 'list':
                            $criteria->addInCondition($attribute, explode(',', $value));
                            break;
                        case 'notList':
                            $criteria->addNotInCondition($attribute, explode(',', $value));
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
            $accountColumnStr = implode(', ', $accountColumns);
            $sql = 'SELECT '.$accountColumnStr.' FROM x2_accounts WHERE '.$attributeConditions;
            $count = Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from('x2_accounts')
                    ->where($attributeConditions, $criteria->params)
                    ->queryScalar();
            $dataProvider = new CSqlDataProvider($sql, array(
                        'totalItemCount' => $count,
                        'params' => $criteria->params,
                        'sort' => array(
                            'attributes' => $fieldNames,
                            'defaultOrder' => isset($_SESSION['accountsReportSort']) ? $_SESSION['accountsReportSort'] : "$dateField ASC"
                        ),
                        'pagination' => array(
                            'pageSize' => 100,
                        ),
                    ));
            $pg = $dataProvider->getPagination();
            $pg->setCurrentPage($page);
            $dataProvider->setPagination($pg);
            $records = $dataProvider->getData();
            $pageCount = $dataProvider->getPagination()->getPageCount();
            foreach($records as $record){
                foreach($fields as $field){
                    if(in_array($field->fieldName, $accountColumns)){
                        $fieldName = $field->fieldName;
                        if($field->type == 'date' || $field->type == 'dateTime'){
                            if(is_numeric($record[$fieldName])){
                                if($field->type == 'date'){
                                    $record[$fieldName] = Formatter::formatDate($record[$fieldName]);
                                }else{
                                    $record[$fieldName] = Formatter::formatDateTime($record[$fieldName]);
                                }
                            }
                        }elseif($field->type == 'link'){
                            try{
                                $linkModel = X2Model::model($field->linkType)->findByPk($record[$fieldName]);
                                if(isset($linkModel) && $linkModel->hasAttribute('name')){
                                    $record[$fieldName] = $linkModel->name;
                                }
                            }catch(Exception $e){

                            }
                        }elseif($fieldName == 'visibility'){
                            $record[$fieldName] = $record[$fieldName] == 1 ? 'Public' : 'Private';
                        }
                    }
                }
                fputcsv($fp, $record);
            }
            unset($dataProvider);
            fclose($fp);
            if($page + 1 < $pageCount){
                echo $page + 1;
            }
        }
    }

}
