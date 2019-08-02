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






class RelationshipsGraph extends X2Widget {

    /**
     * @var null|numeric $height
     */
    public $height = null; 

    /**
     * @var X2Model $model 
     */
    public $model; 

    /**
     * @var bool $inline
     */
    public $inline = false; 

    public static function getNeighborData ($myModel) {
        $relatedModels = $myModel->getVisibleRelatedX2Models ();
        $neighborData = array ();
        foreach ($relatedModels as $model) {
            $neighborData[get_class ($model).$model->id] = array (
                'name' => $model->name,
                'link' => $model->link,
                'type' => get_class ($model),
                'id' => $model->id,
            );
        }
        $neighborData[get_class ($myModel).$myModel->id] = array (
            'name' => $myModel->name,
            'id' => $myModel->id,
            'type' => get_class ($myModel),
        );
        return $neighborData;
    }

    public function getHints () {
        $hints = array (
            Yii::t('app', 'Select multiple nodes or edges with shift + click'),
            Yii::t('app', 
                'Once selected, up to 4 nodes can be connected with the "Connect nodes" button'),
        );
        return $hints;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $packages = array_merge (parent::getPackages (), array (
                'd3' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/d3/d3.js',
                    ), 
                ),
                'relationshipsGraphsJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/relationshipsGraph/RelationshipsGraph.js',
                    ),
                    'depends' => array ('d3', 'auxlib', 'RelationshipsGraphQtipManager'),
                ),
                'RelationshipsGraphQtipManager' => array (
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/relationshipsGraph/RelationshipsGraphQtipManager.js',
                    ),
                    'depends' => array ('QtipManager'),
                ),
                'QtipManager' => array (
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/QtipManager.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
                'relationshipsGraphsCSS' => array(
                    'baseUrl' => Yii::app()->theme->baseUrl,
                    'css' => array(
                        'css/components/RelationshipsGraph.css',
                    ),
                ),
            ));
//            if (AuxLib::getIEver () < 9) {
//                $packages['d3']['depends'] = array('aight');
//                $packages['aightd3'] = array(
//                        'baseUrl' => Yii::app()->request->baseUrl,
//                        'js' => array('js/lib/aight/aight.d3.js'),
//                        'depends' => array('aight', 'd3'),
//                    );
//                $packages['relationshipsGraphsJS']['depends'][] = 'aightd3';
//            }
            $this->_packages = $packages;
        }
        return $this->_packages;
    }

    public function getRelationships () {
        $criteria = $this->model->getVisibleRelationshipsCriteria ();
        $condition = $criteria->condition;
        $params = $criteria->params;
        $relationships = Yii::app()->db->createCommand ("
            SELECT *
            FROM x2_relationships
            WHERE $condition
        ")->queryAll (true, $params);
        return $relationships;
    }

    public function getRelationshipsAnnotated () {
        $criteria = $this->model->getVisibleRelationshipsCriteria ();
        return $this->getAllRelationshipsAnnotated ($criteria, false);
    }

    public function getAllRelationships () {
        return Yii::app()->db->createCommand ("
            SELECT *
            FROM x2_relationships
            WHERE 
                firstId IS NOT NULL AND firstId != '' AND 
                secondId IS NOT NULL AND secondId != ''
        ")->queryAll ();
    }

    /**
     * Retrieves all relationships and the names of the records that each node refers to.
     * Performs a left join with all tables which support relationships.
     */
    public function getAllRelationshipsAnnotated (
        CDbCriteria $criteria=null, $useAccessCondition=true) {

        $linkableModelTypes = X2Model::getModelTypesWhichSupportRelationships(false);
        $joinStmts = '';
        $nameFilterA = array ();
        $nameFilterB = array ();
        $qpg = new QueryParamGenerator;
        $conditions = array (
            "firstId IS NOT NULL AND firstId != ''",
            "secondId IS NOT NULL AND secondId != ''"
        );

        $i = 0;
        foreach ($linkableModelTypes as $type) {
            $model = $type::model ();
            $nameField = $model->hasAttribute ('name') ? 'name' : 'id';
            $table = $model->tableName ();
            $aliasA = 't'.$i++;
            $aliasB = 't'.$i++;
            // null values must be converted to the min string to prevent greatest() from returning
            // null
            $nameFilterA[] = "ifnull($aliasA.$nameField, 0x0)";
            $nameFilterB[] = "ifnull($aliasB.$nameField, 0x0)";
            $joinStmts .= "
                left join $table as $aliasA on firstType={$qpg->nextParam ($type)} and 
                    firstId=$aliasA.id
                left join $table as $aliasB on secondType={$qpg->nextParam ($type)} and 
                    secondId=$aliasB.id
            ";

            // add permissions conditions for model type which apply only if type of node matches
            // type for which permissions conditions were generated 
            if (!Yii::app()->params->isAdmin && $useAccessCondition) {
                list($condA, $paramsA) = $model->getAccessSQLCondition (
                    $aliasA, $aliasA.'getAllRelationshipsAnnotated');
                list($condB, $paramsB) = $model->getAccessSQLCondition (
                    $aliasB, $aliasB.'getAllRelationshipsAnnotated');
                $conditions[] = "
                    if(firstType={$qpg->nextParam ($type)} AND firstId=$aliasA.id, $condA, 1=1) AND
                    if(secondType={$qpg->nextParam ($type)} AND secondId=$aliasB.id, $condB, 1=1)";
                $qpg->mergeParams ($paramsA, $paramsB);
            }
        }
        // select all info from relationships table and use greatest() to select the names of the
        // matching records
        $selectClause = 'SELECT firstId, firstType, secondId, secondType, '.
            'greatest('.implode (', ', $nameFilterA).') as firstName, '.
            'greatest('.implode (',', $nameFilterB).') as secondName';
        $whereClause = "
            WHERE ".implode (' AND ', $conditions);
        if ($criteria) {
            $whereClause .= 'AND ('.$criteria->condition.')';
            $qpg->mergeParams ($criteria->params);
        }

        $command = Yii::app()->db->createCommand ("
            $selectClause
            FROM x2_relationships
            $joinStmts
            $whereClause
        ");
        //AuxLib::debugLogR ($command->getText ());
        return $command->queryAll (true, $qpg->getParams ());
    }

    public function getNodesAndEdges ($annotated=true) {
        if ($this->inline) {
            if ($annotated)
                $relationships = $this->getRelationshipsAnnotated ();
            else
                $relationships = $this->getRelationships ();
        } else {
            if ($annotated)
                $relationships = $this->getAllRelationshipsAnnotated ();
            else
                $relationships = $this->getAllRelationships ();
        }
        $nodes = array ();
        // lookup table mapping node labels to numeric indices into the nodes array. This is used
        // to determine vertex set membership in O(1) time
        $nodeUidToIndex = array (); 
        // lookup table mapping node labels to numeric indices into the edges array. This is used
        // to quickly (O(1)) access the edge object given two connected nodes.
        $nodeUidsToEdgeIndex = array (); 
        // used to keep track of node adjacency. Each node with an edge gets an associative array
        // indexed by neighbor node uid. This allows for quick determination of node degree and 
        // adjacency
        $adjacencyArray = array (); 
        $metaData = array (
            'types' => array (),
        );
        $edges = array ();
        $nodeCount = 0;
        $edgeCount = 0;
        if (!count ($relationships)) {
            // single node in graph, empty edge set
            $type = get_class ($this->model);
            $id = $this->model->id;
            $nodeUid = $type.$id;
            $nodes[] = array (
                'type' => $type,
                'id' => $id,
                'name' => $this->model->getName (),
            );
            $nodeUidToIndex[$nodeUid] = 0;
            $adjacencyArray[$nodeUid] = array ();
        } else {
            // build edge and vertex set
            foreach ($relationships as $rel) {
                $nodeUidA = $rel['firstType'] . $rel['firstId'];
                $nodeUidB = $rel['secondType'] . $rel['secondId'];
                $metaData['types'][$rel['firstType']] = true;
                $metaData['types'][$rel['secondType']] = true;

                // add node to vertex set if it's not there already
                if (!isset ($nodeUidToIndex[$nodeUidA])) {
                    $nodeA = array (
                        'type' => $rel['firstType'],
                        'id' => $rel['firstId'],
                    );
                    if ($annotated) $nodeA['name'] = $rel['firstName'];
                    $nodes[$nodeCount] = $nodeA;
                    $nodeUidToIndex[$nodeUidA] = $nodeCount;
                    $adjacencyArray[$nodeUidA] = array (); // add a slot in the adjacency array
                    $nodeCount++;
                }

                // add node to vertex set if it's not there already
                if (!isset ($nodeUidToIndex[$nodeUidB])) {
                    $nodeB = array (
                        'type' => $rel['secondType'],
                        'id' => $rel['secondId'],
                    );
                    if ($annotated) $nodeB['name'] = $rel['secondName'];
                    $nodes[$nodeCount] = $nodeB;
                    $nodeUidToIndex[$nodeUidB] = $nodeCount;
                    $adjacencyArray[$nodeUidB] = array (); // add a slot in the adjacency array
                    $nodeCount++;
                }

                // add edge to adjacency array for each node
                $adjacencyArray[$nodeUidA][$nodeUidB] = true;
                $adjacencyArray[$nodeUidB][$nodeUidA] = true;

                // add a new edge
                $edges[] = array (
                    'source' => $nodeUidToIndex[$nodeUidA],
                    'target' => $nodeUidToIndex[$nodeUidB],
                );

                // add index of the new edge to the edge lookup table
                $nodeUidsToEdgeIndex[$nodeUidA.$nodeUidB] = $edgeCount;
                $nodeUidsToEdgeIndex[$nodeUidB.$nodeUidA] = $edgeCount;
                $edgeCount++;
            }
        }

        $metaData['nodeUidToIndex'] = $nodeUidToIndex;
        $metaData['nodeUidsToEdgeIndex'] = $nodeUidsToEdgeIndex;
        $metaData['adjacencyArray'] = $adjacencyArray;
        return array ($nodes, $edges, $metaData);
    }

    public function run () {
        if (X2Html::IEBanner()) {
            return;
        }

        $this->registerPackages ();
        list ($nodes, $edges, $metaData) = $this->getNodesAndEdges ();
        $linkableModels = X2Model::getModelTypesWhichSupportRelationships(true);

        $colorPalette = X2Color::generatePalette (count ($linkableModels), 0.1);
        $colorPaletteCss = array_map (function ($a) {
            return 'rgb('.$a[0].','.$a[1].','.$a[2].')';
        }, $colorPalette);
        $colorByType = array_combine (array_keys ($linkableModels), $colorPaletteCss);

        $this->render ('_relationshipsGraph', array (
            'nodes' => $nodes,
            'edges' => $edges,
            'metaData' => $metaData,
            'colorByType' => $colorByType,
            'linkableModelsOptions' => $linkableModels,
        ));
    }

}

?>
