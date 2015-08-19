<?php

/**
 * This is a modified version of the default Yii class. The only change that has
 * been made is the inclusion of "common.php" as a shared source of translation
 * messages. Ctrl + F for "X2CHANGE" to find the exact location of this customization.
 */
class X2MessageSource extends CMessageSource {

    const CACHE_KEY_PREFIX = 'Yii.CPhpMessageSource.';

    /**
     * @var integer the time in seconds that the messages can remain valid in cache.
     * Defaults to 0, meaning the caching is disabled.
     */
    public $cachingDuration = 0;

    /**
     * @var string the ID of the cache application component that is used to cache the messages.
     * Defaults to 'cache' which refers to the primary cache application component.
     * Set this property to false if you want to disable caching the messages.
     */
    public $cacheID = 'cache';

    /**
     * @var string the base path for all translated messages. Defaults to null, meaning
     * the "messages" subdirectory of the application directory (e.g. "protected/messages").
     */
    public $basePath;

    /**
     * @var array the message paths for extensions that do not have a base class to use as category prefix.
     * The format of the array should be:
     * <pre>
     * array(
     *     'ExtensionName' => 'ext.ExtensionName.messages',
     * )
     * </pre>
     * Where the key is the name of the extension and the value is the alias to the path
     * of the "messages" subdirectory of the extension.
     * When using Yii::t() to translate an extension message, the category name should be
     * set as 'ExtensionName.categoryName'.
     * Defaults to an empty array, meaning no extensions registered.
     * @since 1.1.13
     */
    public $extensionPaths = array();

    /**
     * @var boolean Whether or not to log a missing translation if the index is found
     * in the messages file, but the translation message is blank. For example,
     * 'X2Engine'=>'' would trigger an onMissingTranslation event if this parameter
     * is set to true, but will not trigger if it is set to false.
     */
    public $logBlankMessages = true;
    private $_files = array();
    private $_messages = array();

    /**
     * Initializes the application component.
     * This method overrides the parent implementation by preprocessing
     * the user request data.
     */
    public function init(){
        parent::init();
        if($this->basePath === null)
            $this->basePath = Yii::getPathOfAlias('application.messages');
        }

    /**
     * Translates the specified message.
     * If the message is not found, an {@link onMissingTranslation}
     * event will be raised.
     * @param string $category the category that the message belongs to
     * @param string $message the message to be translated
     * @param string $language the target language
     * @return string the translated message
     */
    protected function translateMessage($category, $message, $language){
        $key = $language.'.'.$category;
        // X2CHANGE The customization occurs here, see comments below.
        if(!isset($this->_messages[$key]))
            $this->_messages[$key] = $this->loadMessages($category, $language); // Load the messages for the chosen language.
        if(isset($this->_messages[$key][$message]) && $this->_messages[$key][$message] !== ''){
            return $this->_messages[$key][$message];  // If we find the message in the translation files, return the translated version.
        }elseif(!isset($this->_messages[$key][$message]) || $this->_messages[$key][$message] == ''){
            if(!isset($this->_messages['common'])){ // Otherwise, lookup the common file to see if the translation is saved there.
                $this->_messages['common'] = $this->loadMessages('common', $language);
            }
            if(isset($this->_messages['common'][$message])){
                if($this->_messages['common'][$message] !== ''){
                    return $this->_messages['common'][$message]; // If we find the message in common, return the translated version.
                }elseif($this->logBlankMessages && $this->hasEventHandler('onMissingTranslation')){
                    $event = new CMissingTranslationEvent($this, $category, $message, $language);
                    $this->onMissingTranslation($event); // If we find the index but not the message
                    return $event->message; // and we're logging blank messages, log the translation issue.
                }else{
                    return $message; // Otherwise return the starting text.
                }
            }
            if(!Yii::app()->params->noSession && (!isset($this->_messages[$key][$message]) || ($this->_messages[$key][$message] == '' && $this->logBlankMessages)) && $this->hasEventHandler('onMissingTranslation')){
                $event = new CMissingTranslationEvent($this, $category, $message, $language);
                $this->onMissingTranslation($event);
                return $event->message; // Same as above logging, but if we never found anything in common.
            }else{
                return $message;
            }
        }else
            return $message;
    }

    /**
     * Determines the message file name based on the given category and language.
     * If the category name contains a dot, it will be split into the module class name and the category name.
     * In this case, the message file will be assumed to be located within the 'messages' subdirectory of
     * the directory containing the module class file.
     * Otherwise, the message file is assumed to be under the {@link basePath}.
     * @param string $category category name
     * @param string $language language ID
     * @return string the message file path
     */
    protected function getMessageFile($category, $language){
        if(!isset($this->_files[$category][$language])){
            if(($pos = strpos($category, '.')) !== false){
                $extensionClass = substr($category, 0, $pos);
                $extensionCategory = substr($category, $pos + 1);
                // First check if there's an extension registered for this class.
                if(isset($this->extensionPaths[$extensionClass]))
                    $this->_files[$category][$language] = Yii::getPathOfAlias($this->extensionPaths[$extensionClass]).DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
                else{
                    // No extension registered, need to find it.
                    $class = new ReflectionClass($extensionClass);
                    $this->_files[$category][$language] = dirname($class->getFileName()).DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
                }
            } else {
                if(file_exists('custom'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php')){
                    $this->_files[$category][$language] = 'custom'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';
                }else{
                    $this->_files[$category][$language] = $this->basePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';
                }
            }
        }
        return $this->_files[$category][$language];
    }

    /**
     * Loads the message translation for the specified language and category.
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages
     */
    protected function loadMessages($category, $language){
        $messageFile = $this->getMessageFile($category, $language);
        
        if($this->cachingDuration > 0 && $this->cacheID !== false && ($cache = Yii::app()->getComponent($this->cacheID)) !== null){
            $key = self::CACHE_KEY_PREFIX.$messageFile;
            if(($data = $cache->get($key)) !== false)
                return unserialize($data);
        }

        if(is_file($messageFile)){
            $messages = include($messageFile);
            if(!is_array($messages))
                $messages = array();
            if(isset($cache)){
                $dependency = new CFileCacheDependency($messageFile);
                $cache->set($key, serialize($messages), $this->cachingDuration, $dependency);
            }
            return $messages;
        }
        else
            return array();
    }

}

?>
