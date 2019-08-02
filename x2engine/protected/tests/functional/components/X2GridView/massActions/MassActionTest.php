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






Yii::import ('application.modules.contacts.controllers.*');
Yii::import ('application.modules.contacts.*');
Yii::import ('application.modules.accounts.controllers.*');
Yii::import ('application.modules.accounts.*');
Yii::import ('application.modules.services.controllers.*');
Yii::import ('application.modules.services.*');
Yii::import ('application.modules.x2Leads.controllers.*');
Yii::import ('application.modules.x2Leads.*');
Yii::import ('application.components.X2GridView.massActions.*');

/**
 * @group chrome-only 
 */
class MassActionTest extends X2WebTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
        'tags' => array ('Tags', '.MassActionTest'),
    );

    /**
     * Filter grid by specified attribute 
     */
    public function filterBy ($modelName, $attr, $filterVal, $type='text') {
        $this->waitForCondition (
            "window.document.querySelector ('[name=\"".$modelName."[$attr]\"]')");
        switch ($type) {
            case 'select':
                $this->select("name=".$modelName."[$attr]", "value=$filterVal");
                break;
            case 'text':
            default:
                $this->type("name=".$modelName."[$attr]", $filterVal);
        }
        sleep (1);
        $this->waitForCondition ("!window.$('.x2-gridview-updating-anim').is (':visible')");
    }

    /**
     * Sort grid by specified attribute 
     */
    public function sortBy ($modelName, $attr) {
        $headerCellId = lcfirst ($modelName) . 'gridC_' . $attr;
        $this->click ("dom=document.querySelector ('#$headerCellId .sort-link')");
        sleep (1);
        $this->waitForCondition ("!window.$('.x2-gridview-updating-anim').is (':visible')");
    }

    public function checkAll ($modelName) {
        $inputName = lcfirst ($modelName) . 'gridC_gvCheckbox_all';
        $this->click ("dom=document.querySelector ('[name=\"$inputName\"]')");
    }

    public function checkAllOnAllPages () {
        $this->click ("dom=document.querySelector ('.select-all-records-on-all-pages')");
        $this->waitForGridRefresh ();
    }

    public function clickMassActionButton ($massAction) {
        $this->click ("dom=document.querySelector ('.mass-action-button-$massAction')");
    }

    /**
     * Use this for mass actions which can only be selected from the 'More' dropdown 
     */
    public function clickMassActionOption ($massAction) {
        $this->click ("dom=document.querySelector ('.mass-action-$massAction')");
    }
   
    /**
     * Use this to click the confirm button on the first mass action dialog
     */
    public function clickMassActionGoButton () {
        $this->click ("dom=document.querySelector ('.x2-dialog-go-button')");
    }

    /**
     * Enter password into super mass delete double confirm dialog 
     */
    public function enterDoubleConfirmPassword ($password='admin') {
        $this->type("name=password", $password);
    }

    public function clickDoubleConfirmDialogGoButton () {
        $this->click ("dom=document.querySelector ('.double-confirm-dialog-go-button')");
    }

    /**
     * Asserts that grid contains no records 
     */
    public function assertGridIsEmpty () {
        $this->waitForCondition (
            "window.$('.grid-view td.empty span.empty').text () === 
             'No results found.'");
    }

    /**
     * @return int grid view column number (0-indexed) of attribute
     */
    public function getColumnNumOfAttr ($modelName, $attr) {
        $this->storeEval (
            "window.$('.filters [name=\"".$modelName."[$attr]\"]').parent ().index ()"
            , 'columnNum');
        return intval ($this->getExpression ('${columnNum}'));
    }

    /**
     * Extracts values from all cells in grid column corresponding to name attribute
     * @return array  
     */
    public function getNamesOfSelectedRecords ($modelName) {
        $columnNum = $this->getColumnNumOfAttr ($modelName, 'name'); 
        $this->storeEval (
            "window.JSON.stringify (
                window.$.makeArray (window.$('.items tr td:nth-child(".($columnNum + 1).") span').
                    map (function (i, elem) {
                        return window.$(elem).text ();
                    })))", 
            'names');
        return json_decode ($this->getExpression ('${names}'));
    }

    /**
     * With the mass update dialog open, select field and input field value   
     */
    public function selectFieldToUpdate ($attr, $value) {
        $this->select("css=.update-field-field-selector", "value=$attr");
        $this->waitForCondition (
            "window.document.querySelector ('.update-fields-field-input-container input')");
        $this->type("css=.update-fields-field-input-container input", $value);
    }

    public function waitForSuperMassActionCompletion () {
        $this->waitForCondition (
            "(!window.$('.ui-dialog').length ||
             !window.$('.ui-dialog').is (':visible')) && 
             (!window.$('.grid-view-loading').length ||
             !window.$('.grid-view-loading').is (':visible'))");
    }

    public function waitForGridRefresh () {
        $this->waitForCondition ("!window.$('.x2-gridview-updating-anim').is (':visible')");
    }

    public function testMassUpdate () {
        $expectedRemovals = array_map (
            function ($a) { return strtolower ($a); }, 
            Yii::app()->db->createCommand ("
                select name
                from x2_contacts
                where lastName like '%e%' and firstName not like '%e%'
            ")->queryColumn ());

        $this->openX2 ('contacts/index');
        $this->filterBy ('Contacts', 'name', 'e');
        $this->filterBy ('Contacts', 'leadSource', 'Google');
        $this->sortBy ('Contacts', 'lastActivity');
        $this->checkAll ('Contacts');
        $this->checkAllOnAllPages ('Contacts');

        // ensure that records are correctly filtered and sorted
        $this->assertEquals (
            array_map (function ($a) { return strtolower ($a); },  
                Yii::app()->db->createCommand ("
                    select name
                    from x2_contacts
                    where name like '%e%' and leadSource='Google'
                    order by lastActivity asc, id desc
                ")->queryColumn ()), 
            array_map (function ($a) { return strtolower ($a); },  
                $this->getNamesOfSelectedRecords ('Contacts')));
        $this->clickMassActionOption ('MassUpdateFields');
        $this->selectFieldToUpdate ('firstName', 'f');
        $this->clickMassActionGoButton ();
        $this->waitForSuperMassActionCompletion ();
        $remainingRecords = array_map (
            function ($a) { return strtolower ($a); },  
            $this->getNamesOfSelectedRecords ('Contacts'));

        // ensure that records are correctly filtered and sorted after update
        $this->assertEquals (
            array_map (function ($a) { return strtolower ($a); },  
                Yii::app()->db->createCommand ("
                    select name
                    from x2_contacts
                    where name like '%e%' and leadSource='Google'
                    order by lastActivity asc, id desc
                ")->queryColumn ()), 
            $remainingRecords);

        // ensure that all records whose names no longer contain the letter 'e' have been removed
        // from the grid view
        foreach ($expectedRemovals as $record) {
            $this->assertTrue (!in_array ($record, $remainingRecords));
        }
    }

    /**
     * Filter contacts then super mass delete them and ensure that grid is empty afterwards
     */
    public function testMassDeleteWithFilter () {
        $this->openX2 ('contacts/index');
        $this->filterBy ('Contacts', 'name', 'steve');
        $this->checkAll ('Contacts');
        $this->checkAllOnAllPages ('Contacts');
        $this->clickMassActionButton ('MassDelete');
        $this->clickMassActionGoButton ();
        $this->enterDoubleConfirmPassword ();
        $this->clickDoubleConfirmDialogGoButton ();
        $this->assertGridIsEmpty ();
    }

}

?>
