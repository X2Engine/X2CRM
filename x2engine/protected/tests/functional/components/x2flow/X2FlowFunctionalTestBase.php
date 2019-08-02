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
 * Contains utility methods to for X2Flow testing 
 * Disclaimer: Placing your cursor over the test window while tests are executing may prevent
 *  automated drag and drop from functioning properly.
 */

abstract class X2FlowFunctionalTestBase extends X2WebTestCase {

    public function checkTrace ($trace) {
        $flowTestBase = new X2FlowTestBase;
        $flowTestBase->checkTrace ($trace[1]);
    }

    /**
     * Navigate to the flow designer page and enter a flow name
     */
    protected function initiateFlowCreation () {
        $this->openX2 ('studio/flowDesigner');
        $this->waitForCondition ("window.document.querySelector ('[name=\"X2Flow[name]\"]')");
        $this->type("name=X2Flow[name]", 'testFlow');
    }

    /**
     * @return string the label for the given flow trigger class 
     */
    protected function getTriggerLabel ($triggerClassName) { 
        $this->storeEval (
            "window.document.querySelector (
                '#trigger-selector [value=\"".$triggerClassName."\"]').innerHTML", 
            'triggerLabel');
        return $this->getExpression ('${triggerLabel}');
    }

    /**
     * @return string the label for the given flow action class 
     */
    protected function getActionLabel ($actionClassName) {
        // get the label for the action
        $this->storeEval (
            "window.document.querySelector ('#all .$actionClassName > span').innerHTML", 
            'actionLabel');
        return $this->getExpression ('${actionLabel}');
    }

    /**
     * Selects a trigger from the trigger options menu 
     * @param string $triggerLabel the label the trigger option
     */
    protected function selectTrigger ($triggerClassName) {
        $this->select ('id=trigger-selector', 'value='.$triggerClassName);
        $triggerLabel = $this->getTriggerLabel ($triggerClassName);
        $this->waitForCondition (
            "((configTitle = window.document.querySelector ('#x2flow-main-config h2')) && 
                configTitle.innerHTML === '$triggerLabel')");
    }

    /**
     * Calculates the offset between the action menu item and the last empty node. 
     * @return string A coordinate string which can be used by Selenium mouse interaction methods 
     */
    private function getOffsetBetweenItemBoxAndEmptyNode ($actionClassName) {
        $this->storeEval (
            "window.$('.x2flow-node.x2flow-empty').last ().offset ().left - 
                window.$('#all .$actionClassName').offset ().left", 
            'offsetX');
        $offsetX = $this->getExpression ('${offsetX}');
        $this->storeEval (
            "window.$('.x2flow-node.x2flow-empty').last ().offset ().top - 
                window.$('#all .$actionClassName').offset ().top", 
            'offsetY');
        $offsetX = $this->getExpression ('${offsetX}');
        $offsetY = $this->getExpression ('${offsetY}');
        X2_TEST_DEBUG_LEVEL > 1 && println ($offsetX);
        X2_TEST_DEBUG_LEVEL > 1 && println ($offsetY);

        return ($offsetX + 50).','.($offsetY + 25);
    }

    /**
     * Drag the specified flow action to the first available empty node
     * @param string $actionClassName The flow action class name of the flow action which should
     *  be dragged into the flow
     */
    protected function appendActionToFlow ($actionClassName) {
        $this->waitForCondition ("window.document.querySelector ('#all .$actionClassName')");
        $this->assertElementPresent ('css=#all');
        $this->assertElementPresent ("css=#all .$actionClassName");
        $this->assertElementPresent ("css=.x2flow-node.x2flow-empty");

        //$this->setMouseSpeed (1);
        $offset = $this->getOffsetBetweenItemBoxAndEmptyNode ($actionClassName);

        // simulate drag from action menu to empty node
        $this->mouseDown ("dom=document.querySelector ('#all .$actionClassName')");
        //sleep (1);
        $this->mouseMoveAt ("dom=document.querySelector ('#all .$actionClassName')", $offset);
        sleep (1); 
        $this->mouseUpAt ("dom=document.querySelector ('#all .$actionClassName')", $offset);
        sleep (1);

        $actionLabel = $this->getActionLabel ($actionClassName);
        $this->assertConfigMenuOpened ($actionLabel);
    }

    /**
     * Assert that the config menu has the correct title displayed 
     */
    protected function assertConfigMenuOpened ($label) {
        $this->waitForCondition (
            // wait until the config title exists and has the correct text
            "((configTitle = window.document.querySelector ('#x2flow-main-config h2')) && 
                configTitle.innerHTML === '$label')");
        $this->assertText (
            "dom=document.querySelector ('#x2flow-main-config h2')", $label);
    }

    /**
     * Save the flow and assert that there aren't any errors
     */
    protected function saveFlow () {
        $this->clickAndWait ('css=#save-button');
        $this->storeEval (
            // retrieve the error message or null, if there isn't one
            "(elem = window.document.querySelector ('.errorSummary')) && elem.innerHTML", 
            'errorMessage');
        X2_TEST_DEBUG_LEVEL > 1 && println ($this->getExpression ('${errorMessage}'));
        $this->assertElementNotPresent ('css=.errorSummary');
    }

    /**
     * Opens the trigger config menu and asserts that the menu loaded correctly
     */
    protected function openTriggerConfigMenu ($triggerClass) {
        $this->click ("dom=document.querySelector('.x2flow-node.$triggerClass')");
        $this->assertConfigMenuOpened ($this->getTriggerLabel ($triggerClass));
    }

    /**
     * Opens the flow action config menu and asserts that the menu loaded correctly
     */
    protected function openConfigMenu ($actionClass) {
        $this->click (
            // convert the node list into an array and take the last node
            "dom=Array.prototype.slice.call (
                document.querySelectorAll ('.x2flow-node.$actionClass')).pop ()");
        $this->assertConfigMenuOpened ($this->getActionLabel ($actionClass));
    }

    /**
     * Types the given value into the config input corresponding to the field with the given name 
     * @param string $optionName
     * @param string $inputVal
     */
    protected function inputValueIntoConfigMenu ($optionName, $inputVal) {
        $this->assertElementPresent (
            "dom=document.querySelector ('[name=\"$optionName\"] input')");
        $this->type(
            "dom=document.querySelector ('[name=\"$optionName\"] input')", $inputVal);
        $this->typeKeys(
            "dom=document.querySelector ('[name=\"$optionName\"] input')", $inputVal);
        $this->assertValue (
            "dom=document.querySelector ('[name=\"$optionName\"] input')", $inputVal);
        /*$this->fireEvent (
            "dom=document.querySelector ('[name=\"$optionName\"] input')", 'blur');*/
    }

    protected function waitForLoadingComplete () {
        $this->waitForCondition ("
            !window.$('#x2flow-config-box').hasClass ('loading')");
    }
    
    public static function tearDownAfterClass() {
        X2FlowTestingAuxLib::clearLogs();
        parent::tearDownAfterClass();
    }

}
