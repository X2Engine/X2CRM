<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/

Yii::import('application.components.x2flow.*');
Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

/**
 * Utility methods for flow related unit tests.
 */
class X2FlowTestingAuxLib {

    /**
     * Decodes flow from flow fixture record. 
     * @param X2DbTestCase $context
     * @param string $rowAlias The row within the fixture to get
     * @param string $fixtureName Name of the fixture from which to get data
     * @return array decoded flow JSON string
     */
    public function getFlow ($context,$rowAlias,$fixtureName = 'x2flow') {
        return CJSON::decode ($context->{$fixtureName}[$rowAlias]['flow']);
    }

    /**
     * Returns array of decoded flows from fixture records
     * @param X2DbTestCase $context A test case for which to obtain data
     * @param string $fixtureName The name of the fixture to pull from
     * @return <array of arrays> decoded flow JSON strings
     */
    public function getFlows ($context,$fixtureName = 'x2flow') {
         return array_map (function ($a) { return CJSON::decode ($a['flow']); }, $context->{$fixtureName});
    }

    public function assertGetInstances ($context, $subClass,$ignoreClassFiles) {
        $items = call_user_func("X2Flow{$subClass}::get{$subClass}Instances");
        $allFiles = scandir(
            $actionsPath = 
                Yii::getPathOfAlias('application.components.x2flow.'.strtolower($subClass).'s'));

        $classFiles = array();
        foreach($allFiles as $file) {
            $classPath = $actionsPath.DIRECTORY_SEPARATOR.$file;
            if(is_file($classPath) && !is_dir($classPath)) {
                $classFiles[] = substr($file,0,-4);
            }
        }
        $classesLoaded = array();
        foreach($items as $itemObject) {
            $classesLoaded[] = get_class($itemObject);
        }
        $classesNotLoaded = array_diff($classFiles,$classesLoaded);
        $classesShouldBeLoaded = array_diff($classesNotLoaded,$ignoreClassFiles);
        $context->assertEquals(
            array(),$classesShouldBeLoaded,'Some classes were not instantiated.');
    }

}

?>
