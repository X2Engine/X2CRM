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



Yii::import('application.components.*');

/**
 * Test for translations auto-parse
 *
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class TranslationBehaviorTest extends X2TestCase {

    public function instantiate() {
        $component = new CComponent();
        $component->attachBehavior('messageParser',
                array('class' => 'TranslationBehavior'));
        return $component;
    }

    /**
     * Cursory test of regex
     */
    public function testGetRegex() {
        $cmpb = $this->instantiate();
        preg_match($cmpb->getRegex(),
                "Yii::t(  'app' ,     'Everything\\'s \"fine\".'   );", $matches);
        $this->assertEquals('app', $matches['module']);
        $this->assertEquals('Everything\\\'s "fine".',
                $cmpb->parseRegexMatch($matches['message']));
        $this->assertEquals('', $matches['openquote1']);
        //$this->assertEquals('',$matches['openquote2']);
        preg_match($cmpb->getRegex(),
                'Yii::t(  "app" ,     "Everything\'s \"fine\"."   );', $matches);
        $this->assertEquals('"', $matches['openquote1']);
        // $this->assertEquals('"',$matches['openquote2']);
        $this->assertEquals('app', $matches['module']);
        $this->assertEquals('Everything\'s \"fine\".',
                $cmpb->parseRegexMatch($matches['message']));
    }

    public function testFileList() {
        $cmpb = $this->instantiate();
        $t0 = time();
        $fl = $cmpb->fileList();
        $t1 = time();
        // Should not take super-long
        $this->assertLessThan(10, $t1 - $t0);
    }

    public function testParseFile() {
        $cmpb = $this->instantiate();
        $msgFile = Yii::app()->basePath . '/tests/data/TranslationBehaviorTest/translationCalls1.php';
        // Note that the assertions in this method must correspond to the above file
        $messages = $cmpb->parseFile($msgFile);
        $expected = require Yii::app()->basePath. '/tests/data/TranslationBehaviorTest/expectedMessages1.php';
        $this->assertEquals($expected, $messages);
    }
    
    public function testGetMessageList() {
        $cmpb = $this->instantiate();
        $msgFile = Yii::app()->basePath . '/tests/data/TranslationBehaviorTest/translationCalls1.php';
        $messages = $cmpb->getMessageList($msgFile);
        $expected = require Yii::app()->basePath . '/tests/data/TranslationBehaviorTest/expectedMessages1.php';
        foreach ($expected as $key => $values) {
            $this->assertArrayEquals(array_keys($values), $messages[$key]);
        }
    }
    
    public function testGetAttributeLabels(){
        $cmpb = $this->instantiate();
        $attributeLabels = $cmpb->getAttributeLabels();
        $this->assertNotEmpty($attributeLabels);
        $this->assertEmpty($cmpb->errors);
    }
    
    public function testAddMessages(){
        $cmpb =  $this->instantiate();
        $msgFile = Yii::app()->basePath . '/tests/data/TranslationBehaviorTest/translationCalls1.php';
        $messages = $cmpb->getMessageList($msgFile);
        $translationFile = Yii::app()->basePath . '/tests/data/TranslationBehaviorTest/messages/app.php';
        $commonFile = Yii::app()->basePath . '/tests/data/TranslationBehaviorTest/messages/common.php';
        file_put_contents($translationFile, '<?php return '.var_export( array(), true ).";\n");
        file_put_contents($commonFile, '<?php return '.var_export( array(), true ).";\n");
        
        $translationMessages = require $translationFile;
        $this->assertEmpty(array_keys($translationMessages));
        $this->assertNotEmpty($messages['app']);
        
        $cmpb->addMessages($translationFile, $messages['app']);
        $translationMessages = require $translationFile;
        $this->assertArrayEquals($messages['app'],array_keys($translationMessages));
        
        $messages['app'][] = 'Another message!';
        
        $cmpb->addMessages($translationFile, $messages['app']);
        $translationMessages = require $translationFile;
        $this->assertArrayEquals($messages['app'],array_keys($translationMessages));
        
        $messages['common'] = array('Test common.');
        $cmpb->addMessages($commonFile, $messages['common']);
        
        $oldMessages = $messages['app'];
        $messages['app'][] = 'Test common.';
        $cmpb->addMessages($translationFile, $messages['app'], $commonFile);
        
        $translationMessages = require $translationFile;
        $this->assertArrayEquals($oldMessages,array_keys($translationMessages));
        $this->assertEmpty($cmpb->errors);
        
        file_put_contents($translationFile, '<?php return '.var_export( array(), true ).";\n");
        file_put_contents($commonFile, '<?php return '.var_export( array(), true ).";\n");
    }
    
    public function testBuildRedundancyList(){
        $cmpb = $this->instantiate();
        $this->assertEmpty($cmpb->buildRedundancyList());
        
        $adminFile = Yii::app()->basePath.'/messages/template/admin.php';
        $oldAdminMessages = require $adminFile;
        $appFile = Yii::app()->basePath.'/messages/template/app.php';
        $oldAppMessages = require $appFile;
        
        $newMessages = array(
            'This\'s a this is a test of redundancies.',
        );
        
        $cmpb->addMessages($adminFile, $newMessages);
        $cmpb->addMessages($appFile, $newMessages);
        $this->assertNotEmpty($cmpb->buildRedundancyList());
        
        file_put_contents($adminFile, '<?php return '.var_export( $oldAdminMessages, true ).";\n");
        file_put_contents($appFile, '<?php return '.var_export( $oldAppMessages, true ).";\n");
    }
    
    public function testRemoveMessage() {
        $cmpb = $this->instantiate();
        $message = 'This\'s a test message for removal.';
        $appFile = Yii::app()->basePath . '/messages/template/app.php';
        $oldAppMessages = require $appFile;
        $this->assertFalse(in_array($message, array_keys($oldAppMessages)));

        $cmpb->addMessages($appFile, array($message));
        $appMessages = require $appFile;
        $this->assertTrue(in_array($message, array_keys($appMessages)));

        $cmpb->removeMessage('app.php', $message);
        $appMessages = require $appFile;
        $this->assertFalse(in_array($message, array_keys($appMessages)));
        $this->assertArrayEquals(array_keys($oldAppMessages),
                array_keys($appMessages));
    }

    public function testAddToCommon() {
        $cmpb = $this->instantiate();
        $message = 'This\'s a test message for common.';

        $commonFile = Yii::app()->basePath . '/messages/template/common.php';
        $oldCommonMessages = require $commonFile;
        $this->assertFalse(in_array($message, array_keys($oldCommonMessages)));

        $cmpb->addToCommon($message);
        $commonMessages = require $commonFile;
        $this->assertTrue(in_array($message, array_keys($commonMessages)));
        unset($commonMessages[$message]);
        $this->assertNotEmpty($commonMessages);
        $this->assertArrayEquals(array_keys($oldCommonMessages),
                array_keys($commonMessages));

        $cmpb->removeMessage('common.php', $message);
        $commonMessages = require $commonFile;
        $this->assertFalse(in_array($message, array_keys($commonMessages)));
        $this->assertArrayEquals(array_keys($oldCommonMessages),
                array_keys($commonMessages));
    }
    
    public function testConsolidateMessages(){
        $cmpb = $this->instantiate();
        $message = 'This\'s a test consolidation of messages.';
        $files = array();
        foreach(scandir(Yii::app()->basePath.'/messages/template/') as $filename){
            if(!in_array($filename, array('.','..','common.php'))){
                $path = Yii::app()->basePath.'/messages/template/'.$filename;
                $oldMessages = require $path;
                $files[$path] = $oldMessages;
                $this->assertFalse(in_array($message, array_keys($oldMessages)));
            }
        }
        $commonFile = Yii::app()->basePath.'/messages/template/common.php';
        $oldCommonMessages = require $commonFile;
        $this->assertFalse(in_array($message, array_keys($oldCommonMessages)));
        
        foreach(array_keys($files) as $filename){
            $cmpb->addMessages($filename, array($message));
            $messages = require $filename;
            $this->assertTrue(in_array($message, array_keys($messages)));
        }
        $commonMessages = require $commonFile;
        $this->assertFalse(in_array($message, array_keys($commonMessages)));
        
        $cmpb->consolidateMessages();
        
        $commonMessages = require $commonFile;
        $this->assertTrue(in_array($message, array_keys($commonMessages)));
        
        foreach ($files as $filename => $oldMessages) {
            $messages = require $filename;
            $this->assertFalse(in_array($message, array_keys($messages)));
            $this->assertArrayEquals(array_keys($oldMessages),
                    array_keys($messages));
        }
        
        $cmpb->removeMessage('common.php', $message);
    }
    
    public function testGetUntranslatedText(){
        $cmpb = $this->instantiate();
        $message = 'This\'s a test of untranslated text.';
        
        $untranslated = $cmpb->getUntranslatedText();
        $this->assertEmpty($untranslated);
        
        $appFile = Yii::app()->basePath . '/messages/ja/app.php';
        $cmpb->addMessages($appFile, array($message));
        
        $untranslated = $cmpb->getUntranslatedText();
        $this->assertNotEmpty($untranslated);
        $this->assertTrue(in_array($message,$untranslated['ja']['app.php']));
        
        $cmpb->removeMessage('app.php',$message);
    }
    
    public function testTranslateMessage(){
        if(!X2_THOROUGH_TESTING){
            $this->markTestSkipped('By default skip this test because it makes a call to a billable Google API');
        }
        if(!file_exists(Yii::app()->basePath .'/config/googleApiKey.php')){
            $this->markTestIncomplete('Google API key required to run this test');
        }
        $cmpb = $this->instantiate();
        
        $lang = 'ja';
        $message = 'This\'s a test of Google Translate\'s API.';
        
        $translated = $cmpb->translateMessage($message, $lang);
        
        //Just ensure it ran without errors. Can't guarantee result of Google API.
        $this->assertNotEmpty($translated);
    }
    
    public function testNoTranslate(){
        $cmpb = $this->instantiate();
        
        $messages = array(
            'This\'s a test of some {notranslate} text.' => 'This\'s a test of some <span class="notranslate">{notranslate}</span> text.',
            'This message has <a href="#">HTML Tags</a> in it.' => 'This message has <span class="notranslate"><a href="#"></span>HTML Tags<span class="notranslate"></a></span> in it.',
        );
        
        foreach($messages as $message => $expected){
            $added = $cmpb->addNoTranslateTags($message);
            $this->assertEquals($expected, $added);
            
            $removed = $cmpb->removeNoTranslateTags($added);
            $this->assertEquals($message, $removed);
        }
    }
    
    public function testReplaceTranslations(){
        $cmpb = $this->instantiate();
        $appFile = Yii::app()->basePath.'/messages/template/app.php';
        $oldAppMessages = require $appFile;
        
        $message = 'Test message';
        $translation = 'is translated';
        
        $lang = 'template';
        $file = 'app.php';
        $translations = array(
            $message=>$translation,
        );
        $this->assertNotEmpty(array_keys($translations));
        $this->assertNotEmpty(array_keys($oldAppMessages));
        $this->assertEmpty(array_intersect(array_keys($translations),array_keys($oldAppMessages)));
        
        $cmpb->addMessages($appFile, array($message));
        $cmpb->replaceTranslations($lang, $file, $translations);
        
        $appMessages = require $appFile;
        $this->assertFalse(in_array($message, array_keys($oldAppMessages)));
        $this->assertTrue(in_array($message, array_keys($appMessages)));
        $this->assertNotEmpty(array_diff(array_keys($appMessages), array_keys($oldAppMessages)));
        $this->assertEquals($translation, $appMessages[$message]);
        
        $cmpb->removeMessage('app.php', $message);
        
    }
    
    public function testUpdateTranslations(){
        if(!X2_THOROUGH_TESTING){
            $this->markTestSkipped('By default skip this test because it makes a call to a billable Google API');
        }
        if(!file_exists(Yii::app()->basePath .'/config/googleApiKey.php')){
            $this->markTestIncomplete('Google API key required to run this test');
        }
        $cmpb = $this->instantiate();
        $appFile = Yii::app()->basePath.'/messages/ja/app.php';
        $oldAppMessages = require $appFile;
        $lang = 'ja';
        $message = 'This\'s a test of Google Translate\'s API.';
        
        $untranslated = $cmpb->getUntranslatedText();
        $this->assertEmpty($untranslated);
        
        $cmpb->addMessages($appFile, array($message));
        $untranslated = $cmpb->getUntranslatedText();
        $this->assertNotEmpty($untranslated);
        
        $cmpb->updateTranslations();
        
        $untranslated = $cmpb->getUntranslatedText();
        $this->assertEmpty($untranslated);
        $appMessages = require $appFile;
        $this->assertTrue(in_array($message, array_keys($appMessages)));
        $this->assertNotEmpty($appMessages[$message]);
        
        $cmpb->removeMessage('app.php',$message);
        
    }
    
    public function testMergeCustomTranslationFile(){
        $cmpb = $this->instantiate();
        $customDir = str_replace('/protected','/custom/protected',Yii::app()->basePath);
        $appFile = Yii::app()->basePath.'/messages/template/app.php';
        $customAppFile = str_replace('/protected','/custom/protected',$appFile);
        
        $message = 'This\'s a test of';
        $translation = 'merging custom translations';
        $messages = array(
            $message=>$translation,
        );
        
        mkdir($customDir.'/messages');
        mkdir($customDir.'/messages/template');
        file_put_contents($customAppFile, '<?php return '.var_export( $messages, true ).";\n"); 
        
        $oldAppMessages = require $appFile;
        $this->assertFalse(in_array($message,array_keys($oldAppMessages)));
        
        $cmpb->mergeCustomTranslationFile($customAppFile);
        
        $appMessages = require $appFile;
        $this->assertTrue(in_array($message, array_keys($appMessages)));
        $this->assertEquals(1,count(array_diff(array_keys($appMessages), array_keys($oldAppMessages))));
        $this->assertEquals($translation, $appMessages[$message]);
        
        $cmpb->removeMessage('app.php', $message);
        
        unlink($customAppFile);
        rmdir($customDir.'/messages/template');
        rmdir($customDir.'/messages');
    }
    
    /**
     * This test is unused as the function is very simple right now. However,
     * planned changes to the translation tool will make this test necessary
     * once it's associated function is modified
     */
    public function testMergeCustomLanguagePack(){
        $this->markTestIncomplete();
    }
    
    public function testMergeCustomTranslations(){
        $cmpb = $this->instantiate();
        $customDir = str_replace('/protected','/custom/protected',Yii::app()->basePath);
        $appFile = Yii::app()->basePath.'/messages/template/app.php';
        $oldAppMessages = require $appFile;
        $customAppFile = str_replace('/protected','/custom/protected',$appFile);
        
        //No errors and no changes with no custom messages dir
        $cmpb->mergeCustomTranslations();
        $appMessages = require $appFile;
        $this->assertArrayEquals(array_keys($oldAppMessages), array_keys($appMessages));
        
        //No errors and no changes with empty custom messages dir
        mkdir($customDir.'/messages');
        $cmpb->mergeCustomTranslations();
        $appMessages = require $appFile;
        $this->assertArrayEquals(array_keys($oldAppMessages), array_keys($appMessages));        
        
        //No errors and no changes with empty custom language dir
        mkdir($customDir.'/messages/template');
        $cmpb->mergeCustomTranslations();
        $appMessages = require $appFile;
        $this->assertArrayEquals(array_keys($oldAppMessages), array_keys($appMessages));
        
        //No errors and no changes with empty custom translation file
        file_put_contents($customAppFile, '<?php return '.var_export( array(), true ).";\n"); 
        $cmpb->mergeCustomTranslations();
        $appMessages = require $appFile;
        $this->assertArrayEquals(array_keys($oldAppMessages), array_keys($appMessages));
        
        unlink($customAppFile);
        rmdir($customDir.'/messages/template');
        rmdir($customDir.'/messages');
        
        
    }

}

?>
