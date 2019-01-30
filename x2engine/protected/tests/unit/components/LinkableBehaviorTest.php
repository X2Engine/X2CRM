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




Yii::import ('application.modules.products.models.*');
Yii::import ('application.components.permissions.*');

class LinkableBehaviorTest extends X2DbTestCase {

    public function testGetItems2 () {
        // test attribute retrieval
        $prefix = 't';
        $page = 0;
        $limit = 1;
        $valueAttr = array ('id', 'price');
        $nameAttr = 'name';
        $items = Product::model ()->getItems2 ($prefix, $page, $limit, $valueAttr, $nameAttr);
        $this->assertEquals (1, count ($items));
        $expectedRecord = Yii::app()->db->createCommand ()
            ->select ('*')
            ->from ('x2_products')
            ->where ("name like '$prefix%'")
            ->queryRow ();
        $this->assertEquals ($expectedRecord['name'], $items[0][0]);
        $this->assertEquals ($expectedRecord['price'], $items[0][1]['price']);
        $this->assertEquals ($expectedRecord['id'], $items[0][1]['id']);

        // test multiple results
        $prefix = 'c';
        $page = 0;
        $limit = 3;
        $valueAttr = array ('id', 'price');
        $nameAttr = 'name';
        $items = Product::model ()->getItems2 ($prefix, $page, $limit, $valueAttr, $nameAttr);
        $this->assertEquals (2, count ($items));

        // test limit
        $prefix = 'c';
        $page = 0;
        $limit = 1;
        $valueAttr = array ('id', 'price');
        $nameAttr = 'name';
        $items = Product::model ()->getItems2 ($prefix, $page, $limit, $valueAttr, $nameAttr);
        $this->assertEquals (1, count ($items));

        // test pagination
        $prefix = 'c';
        $page = 1;
        $limit = 1;
        $valueAttr = array ('id', 'price');
        $nameAttr = 'name';
        $itemsNext = Product::model ()->getItems2 ($prefix, $page, $limit, $valueAttr, $nameAttr);
        $this->assertEquals (1, count ($items));
        $this->assertNotEquals ($itemsNext[0][1]['id'], $items[0][1]['id']);
    }

}

?>
