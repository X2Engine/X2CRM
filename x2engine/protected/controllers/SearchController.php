<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

/**
 * Global internal searching through records.
 *  
 * @package X2CRM.controllers
 */
class SearchController extends x2base {

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
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

    /**
     * Rebuilds the search index. 
     */
    public function actionBuildIndex() {

        $contact = new Contacts;
        $fieldData = $contact->getFields();


        // lookup searchable fields
        $fields = array();
        for ($i = 0; $i < count($fieldData); $i++) {
            if (in_array($fieldData[$i]->type, array('dropdown', 'text', 'varchar', 'assignment'))
                    && !in_array($fieldData[$i]->fieldName, array('firstName', 'lastName', 'updatedBy', 'priority', 'id'))
            ) { //,'phone','url','email','link',
                // || !$fields[$i]->searchable 
                if ($fieldData[$i]->relevance == 'High')
                    $relevance = 3;
                elseif ($fieldData[$i]->relevance == 'Medium')
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

        $dataProvider = new CSqlDataProvider('SELECT ' . implode(',', array_merge(array_keys($fields), array('id', 'visibility'))) . ' FROM x2_contacts', array(
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
        echo $pages . ' pages.<br>';
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


        for ($i = 1; $i <= $pages; ++$i) {
            // for($i = 1; $i<=1; ++$i) {

            $links = array();

            $dataProvider->pagination->setCurrentPage($i);

            foreach ($dataProvider->getData($i > 1) as $record) {
                // var_dump($record);
                foreach ($fields as $fieldName => &$field) {
                    // $fieldName = $field['fieldName'];

                    if (!empty($record[$fieldName])) {
                        // break string into words, and eliminate any contractions so we can safely tokenize on ' characters
                        $token = strtok(preg_replace('/(?<=\w)\'(?=\w)/u', '', $record[$fieldName]), $tokenChars);
                        while ($token !== false) {
                            $token = strtolower($token);

                            if (strlen($token) <= 50 && !in_array($token, $noiseWords)) {
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
            for ($j = 0; $j < count($links); ++$j) {
                $sql .= '(?,?,?,?,?,?)';
                if ($j < count($links) - 1)
                    $sql .= ',';
            }

            // echo $sql;
            // var_dump($links);
            // die();

            $query = Yii::app()->db->createCommand($sql);
            for ($j = 0; $j < count($links); ++$j) {
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

        echo 'Time: ' . (microtime(true) - $t0) . '<br>';
    }

    public function actionFastSearch() {
        
    }

    /**
     * Search X2EngineCRM for a record.
     * 
     * This is the action called by the search bar in the main menu. 
     */
    public function actionSearch() {
        ini_set('memory_limit', -1);
        $term = $_GET['term'];

        if (substr($term, 0, 1) != "#") {

            $modules = Modules::model()->findAllByAttributes(array('searchable' => 1));
            $comparisons = array();
            $other = array();
            foreach ($modules as $module) {
                $module->name == 'products' ? $type = ucfirst('Product') : $type = ucfirst($module->name);
                $module->name == 'quotes' ? $type = ucfirst('Quote') : $type = $type;
                $module->name == 'opportunities' ? $type = ucfirst('Opportunity') : $type = $type;
                $criteria = new CDbCriteria();
                $fields = Fields::model()->findAllByAttributes(array('modelName' => $type, 'searchable' => 1));
                $temp = array();
                if (count($fields) < 1) {
                    $criteria->compare('id', '<0', true, 'AND');
                }
                foreach ($fields as $field) {
                    $temp[] = $field->id;
                    $criteria->compare($field->fieldName, $term, true, "OR");
                    if ($field->type == 'phone') {
                        $tempPhone = preg_replace('/\D/', '', $term);
                        $phoneLookup = PhoneNumber::model()->findByAttributes(array('modelType' => $field->modelName, 'number' => $tempPhone, 'fieldName' => $field->fieldName));
                        if (isset($phoneLookup)) {
                            $criteria->compare('id', $phoneLookup->modelId, true, "OR");
                        }
                    }
                }

                $arr = CActiveRecord::model($type)->findAll($criteria);
                $comparisons[$type] = $temp;
                $other[$type] = $arr;
            }
            $high = array();
            $medium = array();
            $low = array();

            $userHigh = array();
            $userMedium = array();
            $userLow = array();

            $records = array();
            $userRecords = array();

            $regEx = "/$term/i";

            foreach ($other as $key => $recordType) {
                $fieldList = $comparisons[$key];
                foreach ($recordType as $otherRecord) {
                    foreach ($fieldList as $field) {
                        $fieldRecord = Fields::model()->findByPk($field);
                        $fieldName = $fieldRecord->fieldName;
                        if (preg_match($regEx, $otherRecord->$fieldName) > 0) {
                            switch ($fieldRecord->relevance) {
                                case "High":
                                    if (!in_array($otherRecord, $high) && !in_array($otherRecord, $medium) && !in_array($otherRecord, $low) &&
                                            !in_array($otherRecord, $userHigh) && !in_array($otherRecord, $userMedium) && !in_array($otherRecord, $userLow)) {
                                        if ($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                            $userHigh[] = $otherRecord;
                                        else
                                            $high[] = $otherRecord;
                                    }
                                    break;
                                case "Medium":
                                    if (!in_array($otherRecord, $high) && !in_array($otherRecord, $medium) && !in_array($otherRecord, $low) &&
                                            !in_array($otherRecord, $userHigh) && !in_array($otherRecord, $userMedium) && !in_array($otherRecord, $userLow)) {
                                        if ($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                            $userMedium[] = $otherRecord;
                                        else
                                            $medium[] = $otherRecord;
                                    }
                                    break;
                                case "Low":
                                    if (!in_array($otherRecord, $high) && !in_array($otherRecord, $medium) && !in_array($otherRecord, $low) &&
                                            !in_array($otherRecord, $userHigh) && !in_array($otherRecord, $userMedium) && !in_array($otherRecord, $userLow)) {
                                        if ($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                            $userLow[] = $otherRecord;
                                        else
                                            $low[] = $otherRecord;
                                    }
                                    break;
                                default:
                                    if ($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
                                        $userLow[] = $otherRecord;
                                    else
                                        $low[] = $otherRecord;
                            }
                        }elseif ($fieldRecord->type == 'phone') {
                            $tempPhone = preg_replace('/\D/', '', $term);

                            if (strlen($tempPhone) == 10) {
                                $phoneLookup = PhoneNumber::model()->findByAttributes(array('modelType' => $fieldRecord->modelName, 'number' => $tempPhone, 'fieldName' => $fieldName));
                                if (!in_array($otherRecord, $high) && !in_array($otherRecord, $medium) && !in_array($otherRecord, $low) &&
                                        !in_array($otherRecord, $userHigh) && !in_array($otherRecord, $userMedium) && !in_array($otherRecord, $userLow)) {
                                    if (isset($phoneLookup) && $otherRecord->id == $phoneLookup->modelId) {
                                        if ($otherRecord->hasAttribute('assignedTo') && $otherRecord->assignedTo == Yii::app()->user->getName())
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
            $records = array_merge($high, $medium);
            $records = array_merge($records, $low);

            $userRecords = array_merge($userHigh, $userMedium);
            $userRecords = array_merge($userRecords, $userLow);

            $records = array_merge($userRecords, $records);

            $records = Record::convert($records, false);
            if (count($records) == 1) {
                $this->redirect($this->createUrl($records[0]['link']));
            }
            $dataProvider = new CArrayDataProvider($records, array(
                        'id' => 'id',
                        'pagination' => array(
                            'pageSize' => ProfileChild::getResultsPerPage(),
                        ),
                    ));

            $this->render('search', array(
                'records' => $records,
                'dataProvider' => $dataProvider,
                'term' => $term,
            ));
        } else {
            $results = new CActiveDataProvider('Tags', array(
                        'criteria' => array('condition' => 'tag="' . $term . '"')
                    ));
            $this->render('searchTags', array(
                'tags' => $results,
                'term' => $term,
            ));
        }
    }

}
?>
