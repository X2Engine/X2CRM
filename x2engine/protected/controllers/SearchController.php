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
 * Global internal searching through records.
 *
 * @package application.controllers
 */
class SearchController extends x2base {

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules(){
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('search', 'buildIndex'),
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function filters(){
        return array(
            'setPortlets',
            'accessControl',
        );
    }

    /**
     * Rebuilds the search index.
     */
    public function actionBuildIndex(){

        $contact = new Contacts;
        $fieldData = $contact->getFields();


        // lookup searchable fields
        $fields = array();
        for($i = 0; $i < count($fieldData); $i++){
            if(in_array($fieldData[$i]->type, array('dropdown', 'text', 'varchar', 'assignment'))
                    && !in_array($fieldData[$i]->fieldName, array('firstName', 'lastName', 'updatedBy', 'priority', 'id'))
            ){ //,'phone','url','email','link',
                // || !$fields[$i]->searchable
                if($fieldData[$i]->relevance == 'High')
                    $relevance = 3;
                elseif($fieldData[$i]->relevance == 'Medium')
                    $relevance = 2;
                else
                    $relevance = 1;

                $fields[$fieldData[$i]->fieldName] = array(
                    'type' => $fieldData[$i]->type,
                    'linkType' => $fieldData[$i]->linkType,
                    'relevance' => $relevance
                );
            }
        }

        $t0 = microtime(true);


        $totalCount = Yii::app()->db->createCommand('SELECT count(*) from x2_contacts;')->queryScalar();

        $dataProvider = new CSqlDataProvider('SELECT '.implode(',', array_merge(array_keys($fields), array('id', 'visibility'))).' FROM x2_contacts', array(
                    // 'criteria'=>array(
                    // 'order'=>'id ASC',
                    // ),
                    'totalItemCount' => $totalCount,
                    'sort' => array('defaultOrder' => 'id ASC'),
                    'pagination' => array(
                        'pageSize' => 500,
                    ),
                ));
        $dataProvider->getData();


        $pages = $dataProvider->pagination->getPageCount();
        echo $pages.' pages.<br>';
        $searchTerms = array();

        // $fh = fopen('search.csv','w+');

        ob_end_flush();

        $keys = array();
        $tokenChars = " \n\r\t!$%^&*()_+-=~[]{}\\|:;'\",.<>?/`‘’•–—“”";
        $noiseWords = array(
            'a', 'about', 'after', 'all', 'also', 'an', 'and', 'another', 'any', 'are', 'arent', 'as', 'at', 'back', 'be', 'because', 'been', 'before',
            'being', 'between', 'both', 'but', 'by', 'came', 'can', 'cant', 'come', 'contact', 'contacts', 'contacted', 'could', 'data', 'did', 'didnt',
            'do', 'dont', 'does', 'doesnt', 'each', 'for', 'from', 'get', 'go', 'going', 'goes', 'got', 'has', 'hasnt', 'had', 'hadnt', 'he', 'hes', 'his',
            'hed', 'have', 'havent', 'her', 'hers', 'here', 'heres', 'him', 'himself', 'how', 'i', 'if', 'in', 'into', 'is', 'it', 'its', 'like', 'make',
            'made', 'makes', 'many', 'me', 'might', 'mightnt', 'more', 'most', 'much', 'must', 'mustnt', 'my', 'mine', 'never', 'no', 'now', 'not', 'of',
            'on', 'only', 'onto', 'or', 'other', 'our', 'out', 'over', 'said', 'same', 'see', 'she', 'shes', 'should', 'shouldnt', 'since', 'some', 'still',
            'such', 'take', 'than', 'that', 'the', 'their', 'them', 'then', 'there', 'theres', 'these', 'they', 'theyre', 'this', 'those', 'through', 'to',
            'too', 'today', 'under', 'up', 'very', 'want', 'wants', 'wanted', 'was', 'wasnt', 'way', 'ways', 'we', 'well', 'were', 'what', 'whats', 'where',
            'which', 'while', 'who', 'why', 'will', 'with', 'would', 'wont', 'you', 'your', 'youre'
        );


        for($i = 1; $i <= $pages; ++$i){
            // for($i = 1; $i<=1; ++$i) {

            $links = array();

            $dataProvider->pagination->setCurrentPage($i);

            foreach($dataProvider->getData($i > 1) as $record){
                // var_dump($record);
                foreach($fields as $fieldName => &$field){
                    // $fieldName = $field['fieldName'];

                    if(!empty($record[$fieldName])){
                        // break string into words, and eliminate any contractions so we can safely tokenize on ' characters
                        $token = strtok(preg_replace('/(?<=\w)\'(?=\w)/u', '', $record[$fieldName]), $tokenChars);
                        while($token !== false){
                            $token = strtolower($token);

                            if(strlen($token) <= 50 && !in_array($token, $noiseWords)){
                                $links[] = array(
                                    $token,
                                    'Contacts',
                                    $record['id'],
                                    $field['relevance'],
                                    $record['assignedTo'],
                                    $record['visibility']
                                );
                            }
                            $token = strtok($tokenChars);
                        }
                    }
                }
                unset($field);
            }

            $sql = 'INSERT INTO x2_search (keyword, modelType, modelId, relevance, assignedTo, visibility) VALUES ';
            for($j = 0; $j < count($links); ++$j){
                $sql .= '(?,?,?,?,?,?)';
                if($j < count($links) - 1)
                    $sql .= ',';
            }

            // echo $sql;
            // var_dump($links);
            // die();

            $query = Yii::app()->db->createCommand($sql);
            for($j = 0; $j < count($links); ++$j){
                $query = $query->bindValues(array(
                    6 * $j + 1 => $links[$j][0],
                    6 * $j + 2 => $links[$j][1],
                    6 * $j + 3 => $links[$j][2],
                    6 * $j + 4 => $links[$j][3],
                    6 * $j + 5 => $links[$j][4],
                    6 * $j + 6 => $links[$j][5]
                        ));
            }
            // die(var_dump($links));
            // echo $query->getText();
            $query->execute();

            // break;
            echo "Page $i...done<br>";
            flush();
        }

        // Yii::app()->db->createCommand();

        echo 'Time: '.(microtime(true) - $t0).'<br>';
    }

    public function actionFastSearch(){

    }

    /**
     * Search X2Engine for a record.
     *
     * This is the action called by the search bar in the main menu.
     */
    public function actionSearch(){
        ini_set('memory_limit', -1);

        $term = isset($_GET['term']) ? $_GET['term'] : "";
        if (empty($term)) {
            $dataProvider = new CArrayDataProvider(array());
            Yii::app()->user->setFlash ('error', Yii::t('app', "Search term cannot be empty."));
            $this->render('search', array(
                'dataProvider' => $dataProvider,
            ));
        } else {

            if(substr($term, 0, 1) != "#"){
    
                $modules = Modules::model()->findAllByAttributes(array('searchable' => 1));
                $comparisons = array();
                $other = array();
                foreach($modules as $module){
                    $module->name == 'products' ? $type = ucfirst('Product') : $type = ucfirst($module->name);
                    $module->name == 'quotes' ? $type = ucfirst('Quote') : $type = $type;
                    $module->name == 'opportunities' ? $type = ucfirst('Opportunity') : $type = $type;
                    $criteria = new CDbCriteria();
                    $fields = Fields::model()->findAllByAttributes(array('modelName' => $type, 'searchable' => 1));
                    $temp = array();
                    $fieldNames = array();
                    if(count($fields) < 1){
                        $criteria->compare('id', '<0', true, 'AND');
                    }
                    foreach($fields as $field){
                        $temp[] = $field->id;
                        $fieldNames[] = $field->fieldName;
                        $criteria->compare($field->fieldName, $term, true, "OR");
                        if($field->type == 'phone'){
                            $tempPhone = preg_replace('/\D/', '', $term);
                            $phoneLookup = PhoneNumber::model()->findByAttributes(array('modelType' => $field->modelName, 'number' => $tempPhone, 'fieldName' => $field->fieldName));
                            if(isset($phoneLookup)){
                                $criteria->compare('id', $phoneLookup->modelId, true, "OR");
                            }
                        }
                    }
                    if(Yii::app()->user->getName() != 'admin' && X2Model::model($type)->hasAttribute('visibility') && X2Model::model($type)->hasAttribute('assignedTo')){
                        $condition = 'visibility="1" OR (assignedTo="Anyone" AND visibility!="0")  OR assignedTo="'.Yii::app()->user->getName().'"';
                        /* x2temp */
                        $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
                        if(!empty($groupLinks))
                            $condition .= ' OR assignedTo IN ('.implode(',', $groupLinks).')';
    
                        $condition .= 'OR (visibility=2 AND assignedTo IN
                            (SELECT username FROM x2_group_to_user WHERE groupId IN
                                (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
                        $criteria->addCondition($condition);
                    }
                    if($module->name == 'actions'){
                        $criteria->with = array('actionText');
                        $criteria->compare('actionText.text', $term, true, "OR");
                    }
                    if(class_exists($type)){
                        $arr = X2Model::model($type)->findAll($criteria);
                        $comparisons[$type] = $temp;
                        $other[$type] = $arr;
                    }
                }
                $high = array();
                $medium = array();
                $low = array();
    
                $userHigh = array();
                $userMedium = array();
                $userLow = array();
    
                $records = array();
                $userRecords = array();
    
                $regEx = "/".preg_quote($term, '/')."/i";
    
                foreach($other as $key => $recordType){
                    $fieldList = $comparisons[$key];
                    foreach($recordType as $otherRecord){
                        if($key == 'Actions'){
                            if($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                $userHigh[] = $otherRecord;
                            else
                                $high[] = $otherRecord;
                        }else{
                            foreach($fieldList as $field){
                                $fieldRecord = Fields::model()->findByPk($field);
                                $fieldName = $fieldRecord->fieldName;
                                if(preg_match($regEx, $otherRecord->$fieldName) > 0){
                                    switch($fieldRecord->relevance){
                                        case "High":
                                            if(!in_array($otherRecord, $high, true) && !in_array($otherRecord, $medium, true) && !in_array($otherRecord, $low, true) &&
                                                    !in_array($otherRecord, $userHigh, true) && !in_array($otherRecord, $userMedium, true) && !in_array($otherRecord, $userLow, true)){
                                                if($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                                    $userHigh[] = $otherRecord;
                                                else
                                                    $high[] = $otherRecord;
                                            }
                                            break;
                                        case "Medium":
                                            if(!in_array($otherRecord, $high, true) && !in_array($otherRecord, $medium, true) && !in_array($otherRecord, $low, true) &&
                                                    !in_array($otherRecord, $userHigh, true) && !in_array($otherRecord, $userMedium, true) && !in_array($otherRecord, $userLow, true)){
                                                if($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                                    $userMedium[] = $otherRecord;
                                                else
                                                    $medium[] = $otherRecord;
                                            }
                                            break;
                                        case "Low":
                                            if(!in_array($otherRecord, $high, true) && !in_array($otherRecord, $medium, true) && !in_array($otherRecord, $low, true) &&
                                                    !in_array($otherRecord, $userHigh, true) && !in_array($otherRecord, $userMedium, true) && !in_array($otherRecord, $userLow, true)){
                                                if($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                                    $userLow[] = $otherRecord;
                                                else
                                                    $low[] = $otherRecord;
                                            }
                                            break;
                                        default:
                                            if($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                                $userLow[] = $otherRecord;
                                            else
                                                $low[] = $otherRecord;
                                    }
                                }elseif($fieldRecord->type == 'phone'){
                                    $tempPhone = preg_replace('/\D/', '', $term);
    
                                    if(strlen($tempPhone) == 10){
                                        $phoneLookup = PhoneNumber::model()->findByAttributes(array('modelType' => $fieldRecord->modelName, 'number' => $tempPhone, 'fieldName' => $fieldName));
                                        if(!in_array($otherRecord, $high, true) && !in_array($otherRecord, $medium, true) && !in_array($otherRecord, $low, true) &&
                                                !in_array($otherRecord, $userHigh, true) && !in_array($otherRecord, $userMedium, true) && !in_array($otherRecord, $userLow, true)){
                                            if(isset($phoneLookup) && $otherRecord->id == $phoneLookup->modelId){
                                                if($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                                    $userHigh[] = $otherRecord;
                                                else
                                                    $high[] = $otherRecord;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $records = array_merge($high, $medium);
                $records = array_merge($records, $low);
    
                $userRecords = array_merge($userHigh, $userMedium);
                $userRecords = array_merge($userRecords, $userLow);
    
                $records = array_merge($userRecords, $records);
    
                $records = Record::convert($records, false);
                if(count($records) == 1){
                    // Only one match, so go straight to it.
                    // 
                    // The record's corresponding model class must have
                    // LinkableBehavior for this to be possible.
                    if(!empty($records[0]['#recordUrl'])) {
                        $this->redirect($records[0]['#recordUrl']);
                    }
                }
                $dataProvider = new CArrayDataProvider($records, array(
                            'id' => 'id',
                            'pagination' => array(
                                'pageSize' => Profile::getResultsPerPage(),
                            ),
                        ));
    
                $this->render('search', array(
                    'records' => $records,
                    'dataProvider' => $dataProvider,
                    'term' => $term,
                ));
            }else{
                Yii::app()->user->setState('vcr-list', $term);
                $_COOKIE['vcr-list'] = $term;
                $tagQuery = "
                    SELECT * 
                    FROM x2_tags
                    WHERE tag=:tag
                    group BY tag, type, itemId";
                $params = array (':tag' => $term);

                // group by type and itemId to prevent display of duplicate tags
                $sql = Yii::app()->db->createCommand ($tagQuery);
                $totalItemCount = Yii::app()->db->createCommand ("
                    SELECT count(*)
                    FROM ($tagQuery) as t1;
                ")->queryScalar ($params);

                $results = new CSqlDataProvider ($sql, array (
                    'totalItemCount' => $totalItemCount,
                    'sort' => array(
                        'defaultOrder' => 'timestamp DESC',
                    ),
                    'pagination' => array(
                        'pageSize' => Profile::getResultsPerPage(),
                    ),
                    'params' => $params,
                ));
                $this->render('searchTags', array(
                    'tags' => $results,
                    'term' => $term,
                ));
            }
        }
    }

}

?>
