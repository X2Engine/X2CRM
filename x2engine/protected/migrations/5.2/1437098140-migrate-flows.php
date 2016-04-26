<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/


/* @edition:pro */

/**
 * Add unique ids to actions in old flows
 */

$migrateFlows = function () {

    function generateIds ($row) {
        if (!isset ($row['flow'])) return;
        $flowData = CJSON::decode ($row['flow']);
        if (!is_array ($flowData) || 
            !isset ($flowData['version']) || 
            $flowData['version'] !== '3.0.1' || 
            !isset ($flowData['items']) ||
            !isset ($flowData['trigger']) ||
            !is_array ($flowData['trigger'])) return $row;
        $idCount = 0;
        $flowData['trigger'] = array (
            'id' => ++$idCount
        ) + $flowData['trigger'];
        _generateIds ($flowData['items'], $idCount);
        $flowData = array (
            'version' => '5.2',
            'idCounter' => $idCount,
        ) + $flowData;
        $row['flow'] = CJSON::encode ($flowData);
        return $row;
    }

    function _generateIds (&$items, &$idCount) {
        if (!is_array ($items)) return;
        foreach ($items as &$item) {
            if (!isset ($item['type'])) break;
            $item = array (
                'id' => ++$idCount,
            ) + $item;
            if ($item['type'] === 'X2FlowSwitch') {
                if (isset ($item['trueBranch'])) {
                    _generateIds ($item['trueBranch'], $idCount);    
                }
                if (isset ($item['falseBranch'])) {
                    _generateIds ($item['falseBranch'], $idCount);
                }
            } 
        }
    }

    $flows = Yii::app()->db->createCommand ("
        select * 
        from x2_flows
    ")->queryAll ();

    foreach ($flows as $flow) {
        $flow = generateIds ($flow);
        Yii::app()->db->createCommand ("
            update x2_flows
            set flow=:flow where id=:id
        ")->execute (array (
            ':flow' => $flow['flow'],
            ':id' => $flow['id']
        ));
    }
};

$migrateFlows ();

?>
