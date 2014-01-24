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
 * A behavior to automatically parse the software for translation calls on text,
 * add that text to our translation files, consolidate duplicate entries into
 * common.php, and then translate all missing entries via Google Translate API.
 * To run the translation automation, navigate to "admin/automateTranslation" in
 * the software. End users should never need to run this code (and in fact without
 * the Google API Key file and Google Translation Billing API configured it
 * will not work). This class is primarily designed for developer use to update
 * translations for new releases.
 * @package X2CRM.components
 * @author "Jake Houser" <jake@x2engine.com>, "Demitri Morgan" <demitri@x2engine.com>
 */
class X2TranslationBehavior extends CBehavior {
    /**
     * The regular expression for matching calls to Yii::t
     *
     * See protected/tests/data/messageparser/modules1.php for examples of what
     * will be matched by this pattern.
     */

    const REGEX = '/(?:(?<installer>installer_tr?)\s*|Yii::\s*t\s*)\(\s*(?(installer)|(?:(?<openquote1>")|\')(?<module>\w+)(?(openquote1)"|\')\s*,)\s*(?:(?<openquote2>")|\')(?<message>(?:(?(openquote2)\\\\"|\\\\\')|(?(openquote2)\'|")|\w|\s|[\(\)\{\}_\.\-\,\!\?\/;:])+)(?(openquote2)"|\')/';

    /**
     * @var array An array of redundant translations that are built up during the
     * consolidation process.
     */
    public $intersect = array();

    /**
     * @var array An array of aggregate data about the translation process to be displayed
     * to the user at the end.
     */
    public $statistics = array(
        'newMessages' => 0, // New messages added to the translation files
        'addedToCommon' => 0, // Messages consolidated into common.php
        'messagesRemoved' => 0, // Mesasges removed as part of the consolidation process
        'untranslated' => 0, // How many messages needed to be translated
        'characterCount' => 0, // Number of characters to be translated via Google Translate
        'languageStats' => array(
        // Stats about translations for individual languages are stored here.
        ),
        'errors'=>array(

        ),
    );

    /**
     * @var array A list of all untranslated messages in the software.
     */
    public $untranslated = array();

    /**
     * Add missing translations to files, first step of automation.
     *
     * Function to find all untralsated text in the software, and then take that
     * array of messages and add them to translation files for all languages.
     * Called in {@link X2TranslationAction::run} function as part of the full
     * translation suite.
     */
    public function addMissingTranslations(){
        $messages = $this->getMessageList(); // Get a list of messages using regex.
        foreach($messages as $file => $messageList){
            $this->addMessages($file, $messageList); // Add each message to the end of the relevant file.
        }
    }

    /**
     * Move commonly used phrases to common.php, second step of automation.
     *
     * Function that parses translation files for all languages and consolidates
     * them. First it builds a list of redundancies between files, then loops
     * through that array, adding redundant phrases to common.php and removing
     * them from their original files. This means any given word/phrase in the
     * software only needs to be translated once. Called in {@link X2TranslationAction::run}
     * function as part of the full translation suite.
     */
    public function consolidateMessages(){
        $attempts = 5; // Don't get stuck in a loop if for some reason phrases aren't being properly handled.
        $this->buildRedundancyList(); // Get a list of all redundancies between translation files and store it in $this->intersect.
        while($attempts > 0 && !empty($this->intersect)){ // Keep going until we run out of attempts or there are no more redundant translations.
            foreach($this->intersect as $data){
                $first = $data['first']; // Get the name of the first file that has the redundancy
                $second = $data['second']; // Get the name of the second file that has the redundancy
                $messages = $data['messages']; // Get the text of the redundant message.
                foreach($messages as $message){
                    $message = str_replace("'", "\\'", $message); // Make the message safe for insertion
                    if($first != 'common.php' && $second != 'common.php'){ // If neither of the matched files are common.php
                        $this->addToCommon($message); // Add the message to common.php
                    }
                    if($first != 'common.php'){ // Only remove messages from the original files if the file isn't common.php
                        $this->statistics['messagesRemoved']++;
                        $this->removeMessage($first, $message);
                    }
                    if($second != 'common.php'){
                        $this->statistics['messagesRemoved']++;
                        $this->removeMessage($second, $message);
                    }
                }
            }
            $attempts--; // One less attempt allowed to prevent endless loops.
            $this->buildRedundancyList(); // Rebuild the redundancy list to be sure there aren't any new redundancies created by the process
        }
    }

    /**
     * Call Google Translate API for mising translations, third step of automation.
     *
     * This method will get a list of all messages which do not have translations
     * into the appropriate language from all of our language files. Then, it will
     * call Google Translate's API to get a base translation of the message and
     * insert the translated versions into our translation files. Called in
     * {@link X2TranslationAction::run} function as part of the full translation suite.
     */
    public function updateTranslations(){
        $untranslated = $this->getUntranslatedText(); // Get a list of all messages with missing translations.
        $limit = 1000; // Don't do more than 1000 messages at a time, Google charges by the character and we don't want to get caught in a loop.
        $translations = array(); // Store translated messages to only do 1 file write per file.
        foreach($untranslated as $lang => $langData){
            $this->statistics['languageStats'][$lang] = 0; // Start tracking stats for this langage.
            foreach($langData as $fileName => $file){
                foreach($file as $index){
                    if($limit > 0){ // Haven't hit 1000 messages yet.
                        $limit--;
                        $index = str_replace("'", "\\'", $index); // Make message safe for insertion
                        $message = $this->translateMessage($index, $lang); // Translate message for the specified language
                        $translations[$index] = $message; // Store the translation (and original message) to be written to the file later.
                        $this->statistics['languageStats'][$lang]++;
                    }else{ // We hit our limit of 1000 messages.
                        $this->replaceTranslations($lang, $fileName, $translations); // Replace translations for what we have now, we'll manually refresh to get more.
                        break 3; // Break out of all the loops to save time
                    }
                }
                $this->replaceTranslations($lang, $fileName, $translations); // Replace the translated messages into the right file.
            }
        }
    }

    /**
     * Gets a list of Yii::t calls.
     *
     * Helper function called by {@link addMissingTranslations}
     * to get a list of messages found in Yii::t calls found in the software in
     * an easily parsed array format. Also checks attribute labels of non-custom
     * modules in the x2_fields table.
     *
     * @return array An array of messages found in the software that need to be added to the translation files.
     */
    public function getMessageList(){
        $files = $this->fileList(); // Get a list of all relevant files that might have Yii::t calls.
        $messages = array();
        foreach($files as $file){
            $newMessages = $this->parseFile($file); // Parse the file for all messages within Yii::t calls.
            foreach($newMessages as $fileName => $messageList){ // Loop through the found messages.
                if(array_key_exists($fileName, $messages)){ // We've already got this file in our return array
                    $messages[$fileName] = array_merge($messages[$fileName], $newMessages[$fileName]); // Merge the new messages with the old messages for the given file
                }else{
                    $messages[$fileName] = $newMessages[$fileName]; // Otherwise, define the messages we found as the initial data set for this file.
                }
            }
        }
        $fields = Yii::app()->db->createCommand()
                ->select('attributeLabel, modelName')
                ->from('x2_fields')
                ->where('custom=0')
                ->queryAll(); // Grab all the attribute labels for fields for all non-custom modules that might need to be translated.
        foreach($fields as $field){
            if($translationFile = $this->getTranslationFileName($field['modelName'])){ // Get the name of the translation file each model is associated with.
                $messages[$translationFile][$field['attributeLabel']] = ''; // Add the attribute labels to our list of text to be translated.
            }
        }
        return $messages;
    }

    /**
     * Converts model name to translation file name.
     *
     * Helper method called in {@link getMessageList} to
     * find the correct translation file for a model. This is necessary because some
     * models have class names like Quote or Opportunity but their file names are
     * quotes and opportunities.
     *
     * @param string $modelName The name of the model to look up the related translation file for.
     * @return string|boolean Returns the name of the translation file to use, or false if a correct file cannot be found.
     */
    public function getTranslationFileName($modelName){
        $modelToTranslation = array(
            'Accounts' => 'accounts',
            'Actions' => 'actions',
            //'BugReports'=>'bugReports', // Don't translate bug reports... not really used as a module
            'Calendar' => 'calendar',
            'Campaign' => 'marketing',
            'Contacts' => 'contacts',
            'Docs' => 'docs',
            'Media' => 'media',
            'Opportunity' => 'opportunities',
            'Product' => 'products',
            'Quote' => 'quotes',
            'Services' => 'services',
        );
        if(isset($modelToTranslation[$modelName])){
            return $modelToTranslation[$modelName];
        }else{
            return false; // Translation file not found for the specified model.
        }
    }

    /**
     * Add missing messages to translation files.
     *
     * Helping function called by {@link addMissingTranslations}
     * to add the messages found in Yii::t calls to the appropriate translation file.
     *
     * @param string $file The translation file to add messages to
     * @param array $messageList A list of messages to be added to the translation files.
     */
    public function addMessages($file, $messageList){
        $messageList = array_keys($messageList);
        $languages = scandir('protected/messages/');
        foreach($languages as $lang){
            if($lang != '.' && $lang != '..'){ // Don't include the current or parent directory.
                if(file_exists("protected/messages/$lang/$file.php")){
                    $messages = array_merge(array_keys(require "protected/messages/$lang/$file.php"), array_keys(require "protected/messages/$lang/common.php")); // Get all of the messages already in the appropriate language as well as common.php
                    $messages = array_map(function($data){
                                return str_replace("'", "\\'", $data); // Make all strings safe for insertion--more importantly to match the provided message list to check for equivalency.
                            }, $messages);
                    $diff = array_diff($messageList, $messages); // Create a diff array of messages not already in the provided language file or common.php
                    if(!empty($diff)){
                        $contents = file_get_contents("protected/messages/$lang/$file.php"); // Grab the array of messages from the translation file.
                        foreach($diff as $message){
                            if($lang == 'template'){
                                $this->statistics['newMessages']++; // Only count statistics once, even if adding to every file.
                            }
                            $contents = preg_replace('/^\);/m', "'$message'=>'',\n);", $contents); // Replace the ending of the array in the file with one of the diff messages.
                        }
                        file_put_contents("protected/messages/$lang/$file.php", $contents); // Put the array back in the translation file.
                    }
                }else{
                    if(!isset($this->statistics['errors']['missingFiles']))
                        $this->statistics['errors']['missingFiles']=array();
                    $this->statistics['errors']['missingFiles'][$file]=$file;
                }
            }
        }
    }

    /**
     * Return Yii::t calls in a specific file
     *
     * Helper method called in {@link getMessageList}
     * Parses a file and returns an associative array of module names to messages
     * for that file.
     *
     * @param string $path Filepath to the file to be checked by the REGEX
     * @return array An array of messages in Yii::t calls in the provided file.
     */
    public function parseFile($path){
        if(!file_exists($path))
            return array();
        preg_match_all(self::REGEX, file_get_contents($path), $matches);
        // Modify the match array to incorporate the special installer_t case
        foreach($matches['installer'] as $index => $groupText)
            if($groupText != '')
                $matches['module'][$index] = 'install';

        $messages = array_fill_keys(array_unique($matches['module']), array());
        foreach($matches['message'] as $index => $message){
            $message = str_replace("\\'", "'", $message);
            $message = str_replace("'", "\\'", $message);
            $messages[$matches['module'][$index]][$message] = '';
        }
        if(isset($messages['yii'])){
            unset($messages['yii']);
        }
        return $messages;
    }

    /**
     * Returns true or false based on whether a path should be parsed for
     * messages.
     *
     * Some files in the software don't need to be translated. Yii provides all
     * of its own translations for the framework directory, and there are other
     * files which simply have no possibility of having Yii::t calls in them.
     * Ignoring these files speeds up the process, especially since framework is
     * a very large directory.
     *
     * @param string $relPath Paths to folders which should not be included in the Yii::t search
     * @return boolean True if file should be excluded from the search, false if the file is OK.
     */
    public function excludePath($relPath){
        $paths = array(
            'framework', // Yii handles its own translations
            'protected/messages', // These are the translation files...
            'protected/extensions', // Extensions are rarely translated and generally don't display text.
            'protected/tests', // Unit tests have no translation calls
            'backup', // Backup of older files that may no longer be relevant
        );
        foreach($paths as $path)
            if(strpos($relPath, $path) === 0) // We found the excluded directory in the relative path.
                return true;
        return !preg_match('/\.php$/', $relPath); // Only look in PHP files.
    }

    /**
     * Parse file structure for valid files.
     *
     * Returns a list of all files in the codebase that are eligible for searching
     * for Yii::t calls within.
     *
     * @param string $revision Unused, may implement comparison between Git revisions rather than searching all files.
     * @return array List of files to be parsed for Yii::t calls
     */
    public function fileList($revision = null){
        $cwd = Yii::app()->basePath;
        $fileList = array();
        $basePath = realpath($cwd.'/../');
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath), RecursiveIteratorIterator::SELF_FIRST); // Build PHP File Iterator to loop through valid directories
        foreach($objects as $name => $object){
            if(!$object->isDir()){ // Make sure it's actually a file if we're going to try to parse it.
                $relPath = str_replace("$basePath/", '', $name); // Get the relative path to it.
                if(!$this->excludePath($relPath)){ // Make sure the file is not in one of the excluded diectories.
                    $fileList[] = $relPath;
                }
            }
        }
        return $fileList;
    }

    /**
     * Get redundant translations to be merged into common.php
     *
     * Helper function called by {@link consolidateMessages)
     * to build a list of files that have redundant messages in them, as well as a
     * list of what those messages are. Loads this data into the property
     * $this->intersect;
     */
    public function buildRedundancyList(){
        $this->intersect = array();
        $files = scandir('protected/messages/template'); // Only need to check template, not all languages. All languages should mirror template.
        $languageList = array();
        foreach($files as $file){
            if($file != '.' && $file != '..'){
                $languageList[$file] = array_keys(include("protected/messages/template/$file")); // Get the messages from each file in the template folder.
            }
        }
        $keys = array_keys($languageList);
        for($i = 0; $i < count($languageList); $i++){ // Outer loop to check all files in the language list.
            for($j = $i + 1; $j < count($languageList); $j++){ // Inner loop to compare each file against each other file.
                $messages = array_intersect($languageList[$keys[$i]], $languageList[$keys[$j]]); // Calculate the intersection of the messages between each pair of files.
                if(!empty($messages)){ // If we found messages that exist in both, add them to the intersect array to be consolidated.
                    $this->intersect[] = array('first' => $keys[$i], 'second' => $keys[$j], 'messages' => $messages);
                }
            }
        }
    }

    /**
     * Add a message to common.php for all languages
     *
     * Helper function called by {@link consolidateMessages}
     * to add a redundant message into common.php. The message will nto be added
     * if it already exists in common.
     *
     * @param string $message The message to be added to common.php
     */
    public function addToCommon($message){
        $languages = scandir('protected/messages/');
        foreach($languages as $lang){
            if($lang != '.' && $lang != '..'){
                if(!file_exists('protected/messages/'.$lang.'/'.'common.php')){ // For some reason common.php doesn't exist for this language.
                    $fp = fopen('protected/messages/'.$lang.'/'.'common.php', 'w+'); // Create the file and set up an empty array to build into.
                    fwrite($fp, "<?php
return array(
);");
                    fclose($fp);
                }
                $contents = file_get_contents('protected/messages/'.$lang.'/'.'common.php'); // Get the contents of the common file.
                $pattern = preg_quote("'".$message."'=>'',", '/'); // Prepare a regex to search for the provided message.
                if(!preg_match('/'.$pattern.'/', $contents)){ // We don't find the message in common.php
                    if($lang == 'template'){
                        $this->statistics['addedToCommon']++; // Only add to our statistics once.
                    }
                    $contents = preg_replace('/^\);/m', "'$message'=>'',\n);", $contents); // Replace the end of the array declaration with an extra index for the provided message.
                    file_put_contents('protected/messages/'.$lang.'/'.'common.php', $contents);
                }
            }
        }
    }

    /**
     * Deletes a message from a language file in all languages.
     *
     * Called as a part of the consolidation process to remove redundant messages
     * from the files they were found in. This keeps the amount of messages lower
     * and reduced the burden on anyone who is translating the software.
     *
     * @param string $file The name of the file to look for the message in
     * @param string $message The message to be removed
     */
    public function removeMessage($file, $message){
        $languages = scandir('protected/messages/'); // Load all languages.
        foreach($languages as $lang){
            if($lang != '.' && $lang != '..'){
                $pattern = preg_quote("'".$message."'=>'',\n", '/'); // Prepare regex for search.
                $contents = file_get_contents('protected/messages/'.$lang.'/'.$file);
                $contents = preg_replace('/'.$pattern.'/', '', $contents); // Replace any instances of the message with an empty string.
                file_put_contents('protected/messages/'.$lang.'/'.$file, $contents); // Write to the file.
            }
        }
    }

    /**
     * Get all untranslated messages
     *
     * Helper function called by {@link updateTranslations}
     * to get an array of all messages which have indeces in the translation files
     * but no translated version.
     *
     * @return array A list of all messages which have missing translations.
     */
    public function getUntranslatedText(){
        if(empty($this->untranslated)){
            $languages = scandir('protected/messages');
            foreach($languages as $lang){
                if(!in_array($lang, array('template', '.', '..'))){ // Ignore current, parent, and template (all template translations are blank) directories.
                    $this->untranslated[$lang] = array();
                    $files = scandir('protected/messages/'.$lang); // Get all the files for the current language.
                    foreach($files as $file){
                        if($file != '.' && $file != '..'){
                            $this->untranslated[$lang][$file] = array();
                            $translations = (include('protected/messages/'.$lang.'/'.$file)); // Include the translations.
                            foreach($translations as $index => $message){
                                if(empty($message)){
                                    $this->untranslated[$lang][$file][] = $index; // If the translated version is empty, add the message index to our unranslated array.
                                    $this->statistics['untranslated']++;
                                }
                            }
                            if(empty($this->untranslated[$lang][$file])){
                                unset($this->untranslated[$lang][$file]); // If we don't find any untranslated messages, don't both returning that file.
                            }
                        }
                    }
                    if(empty($this->untranslated[$lang])){
                        unset($this->untranslated[$lang]); // The whole language is translated, no need to return it either.
                    }
                }
            }
        }
        return $this->untranslated;
    }

    /**
     * Translate a message via Google Translate API.
     *
     * Helper function called by {@link updateTranslations}
     * to translate individual messages via the Google Translate API. Any text
     * between braces {} is preserved as is for variable replacement.
     *
     * @param string $message The untranslated message
     * @param string $lang The language to translate to
     * @return string The translated message
     */
    public function translateMessage($message, $lang){

        $key = require 'protected/config/googleApiKey.php'; // Git Ignored file containing the Google API key to store. Ours is not included with public release for security reasons...
        $message = preg_replace_callback('/\{(.*?)\}/', function($matches){
                    return '<span class="notranslate">'.$matches[0].'</span>'; // Replace every instance of text between braces like {text} with <span class="notranslate">{text}</span>. This will make Google Translate ignore that text.
                }, $message);
        $this->statistics['characterCount']+=mb_strlen($message, 'UTF-8');
        if(strpos($message, ' ') !== false){
            $message = urlencode($message); // URL encode the message so we can make a GET request.
        }
        try{ // Occasionally we get a timeout that causes an exception
            $data = file_get_contents("https://www.googleapis.com/language/translate/v2?key=$key&source=en&target=$lang&q=$message"); // Grab the response from Google Translate API.
            $data = json_decode($data, true); // Response is JSON, need to decode it to an array.
            if(isset($data['data'], $data['data']['translations'], $data['data']['translations'][0], $data['data']['translations'][0]['translatedText'])){
                $message = $data['data']['translations'][0]['translatedText']; // Make sure the data structure returned is correct, then store the message as the translated version.
            }else{
                $message = ''; // Otherwise, leave the message blank.
            }
            $message = preg_replace_callback('/'.preg_quote('<span class="notranslate">', '/').'(.*?)'.preg_quote('</span>', '/').'/', function($matches){
                        return $matches[1]; // Strip out the <span></span> tags
                    }, $message);
            $message = trim($message, '\\/'); // Trim any harmful characters Google Translate may have moved around, like leaving a "\" at the end of the string...
        }catch(CException $e){
            $message = '';
        }
        return $message;
    }

    /**
     * Add translated messages to translation files.
     *
     * Helper function called by {@link updateTranslations}
     * to replace the untranslated messages in a translation file with the response
     * we got from Google.
     *
     * @param string $lang The language we translated our messages to
     * @param string $file The file we need to put the translations in
     * @param array $translations An array of translations with the English message as the index and the translated version as the value.
     */
    public function replaceTranslations($lang, $file, $translations){
        $contents = file_get_contents('protected/messages/'.$lang.'/'.$file); // Grab the translation array.
        foreach($translations as $index => $message){
            $pattern = preg_quote("'".$index."'=>''", '/'); // Prepare the index for regex search.
            if(preg_match('/'.$pattern.'/', $contents)){ // We found the index in the translation file!
                $contents = preg_replace('/'.$pattern.'/', '\''.$index.'\'=>\''.$message.'\'', $contents); // Replace the index with index=>translated in the file.
            }
        }
        file_put_contents('protected/messages/'.$lang.'/'.$file, $contents); // Write the changes back into the translation file and save.
    }

}
