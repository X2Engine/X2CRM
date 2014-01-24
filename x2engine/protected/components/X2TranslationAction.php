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

/**
 * Action to be included in AdminController to allow for the automated updates
 * of X2CRM's translation files. Running this action will find all instances of
 * Yii::t calls in the app, add them to our translation files, and translate them.
 * End users should not need to run this action.
 * @package X2CRM.components
 * @author "Jake Houser" <jake@x2engine.com>
 */
class X2TranslationAction extends CAction {

    /**
     * A list of behaviors for the Action to include. Only the X2TranslationBehavior
     * is needed.
     * @return array An array of behaviors to attach
     */
    public function behaviors(){
        return array(
            'X2TranslationBehavior' => array('class' => 'X2TranslationBehavior'),
        );
    }

    /**
     * Needed to override the constructor to make this automatically attach
     * behaviors listed in the {@link X2TranslationAction::behaviors} function.
     * @param CController $controller The controller who owns this action.
     * @param string $action The ID of the action.
     */
    public function __construct($controller, $action){
        $this->attachBehaviors($this->behaviors()); // Automatically attach all behaviors defined in behaviors method.
        parent::__construct($controller, $action); // Call parent constructor, nothing else to do.
    }

    /**
     * Runs the action. Combines functionality from the various primary methods
     * in X2TranslationBehavior to run the full suite of translation automation.
     * Outputs data to the screen at various points to update the user of the status
     * of the translations, as well as statistics on what has been translated.
     */
    public function run(){
        set_time_limit(0); // This action can take a long time, don't want it to time out.
        echo "Searching for missing translation entries...<br>";
        $this->addMissingTranslations(); // Find Yii::t calls and add to language files.
        echo "<ul>";
        echo "<li>".$this->statistics['newMessages']." messages were found and added to translation files.</li>";
        echo "</ul>";
        echo "Consolidating messages into common.php...<br>";
        $this->consolidateMessages(); // Consolidate duplicate messages into common.php
        echo "<ul>";
        echo "<li>".$this->statistics['addedToCommon']." messages were added to common.php</li>";
        echo "<li>".$this->statistics['messagesRemoved']." messages were removed from other files</li>";
        echo "</ul>";
        echo "Consolidation to common.php complete.<br><br>";

        echo "Updating missing translations via Google Translate...<br>";
        $this->updateTranslations(); // Update translations via Google Translate
        setlocale(LC_MONETARY, 'en_US'); // Set currency locale to America so we can estimate cost.
        echo "<ul>";
        echo "<li>".$this->statistics['untranslated']." messages needed to be translated</li>";
        echo "<li>".$this->statistics['characterCount']." characters needed to be translated, which amounts to approximately ".money_format('%n',(($this->statistics['characterCount']) / 2000000) * 20)." in fees to Google.";
        foreach($this->statistics['languageStats'] as $language => $count){
            echo "<li>".$count." messages were translated for the language: ".$language."</li>";
        }
        echo "</ul>";
        if(isset($this->statistics['errors']['missingFiles'])){
            echo "Unable to find the following requested translation files:<br><ul>";
            foreach($this->statistics['errors']['missingFiles'] as $file){
                echo "<li>".$file."</li>";
            }
            echo "</ul>";
        }
        echo "Translation complete.";
    }

}

?>
