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





Yii::import ('application.components.behaviors.S3Behavior');


/**
 * Behavior for dealing with data files directly on the server while avoiding
 * directory traversal and publicly visible files.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class ImportExportBehavior extends CBehavior {

    protected $recordsImported;
    private $importRelations = array();
    private $createdLinkedModels = array();
    private $modelContainer = array(
        'Tags' => array(),
        'ActionText' => array(),
    );

    /**
     * Sends the file to the web client upon request
     * @param type $file
     * @return false if send file failed (if successful, script is terminated)
     */
    public function sendFile($file, $deleteAfterSend=false){
        if(!preg_match('/(\.\.|\/)/', $file)){
            $file = Yii::app()->file->set($this->safePath($file));
            return $file->send(false, false, $deleteAfterSend);
        }
        return false;
    }

    /**
     * Returns a file path that is within the protected folder, to protect data
     * @param type $filename
     * @return type
     */
    public function safePath($filename = 'data.csv'){
        return implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            $filename
        ));
    }

    /**
     * Retrieve the current CSV delimeter for import/export
     * @return string Import CSV delimeter
     */
    public function getImportDelimeter() {
        if (array_key_exists ('importDelimeter', $_SESSION) &&
            strlen ($_SESSION['importDelimeter']) === 1)
                return $_SESSION['importDelimeter'];
        else
            return ',';
    }

    /**
     * Retrieve the current CSV enclosure for import/export
     * @return string Import CSV enclosure
     */
    public function getImportEnclosure() {
        if (array_key_exists ('importEnclosure', $_SESSION) &&
            strlen ($_SESSION['importEnclosure']) === 1)
                return $_SESSION['importEnclosure'];
        else
            return '"';
    }

    /**
     * Create tag records for each of the specified tags
     * @param string $modelName The model class being imported
     * @param string $tagsField Comma separated list of tags to generate
     * @return array of Tag attributes
     */
    protected function importTags($modelName, $tagsField) {
        $tagAttributes = array();
        if (empty($tagsField)) return array();

        // Read in comma separated list of tags and store Tag attributes
        $tags = explode(',', $tagsField);
        foreach ($tags as $tagName) {
            if (empty($tagName)) continue;
            $tag = new Tags;
            $tag->tag = $tagName;
            $tag->type = $modelName;
            $tag->timestamp = time();
            $tag->taggedBy = Yii::app()->getSuName();
            $tagAttributes[] = $tag->attributes;
        }
        $this->modelContainer['Tags'][] = $tagAttributes;
    }

    /**
     * Helper function to return the next importId
     * @return int Next import ID
     */
    private function getNextImportId() {
        $criteria = new CDbCriteria;
        $criteria->order = "importId DESC";
        $criteria->limit = 1;
        $import = Imports::model()->find($criteria);

        // Figure out which import this is so we can set up the Imports models
        // for this import.
        if (isset($import)) {
            $importId = $import->importId + 1;
        } else {
            $importId = 1;
        }
        return $importId;
    }

    /**
     * List available import maps from a directory,
     * optionally of type $model
     * @param string $model Name of the model to load import maps
     * @return array Available import maps, with the filenames as
     * keys, and import mapping names (product/version) as values
     */
    protected function availableImportMaps($model = null) {
        $maps = array();
        if ($model === "X2Leads")
            $model = "Leads";
        $modelName = (isset($model)) ? lcfirst($model) : '.*';
        $importMapDir = "importMaps";
        $files = scandir($this->safePath($importMapDir));
        foreach ($files as $file) {
            $filename = basename($file);
            // Filenames are in the form "app-model.json"
            if (!preg_match('/^.*-' . $modelName . '\.json$/', $filename))
                continue;
            $mapping = file_get_contents($this->safePath($importMapDir . DIRECTORY_SEPARATOR . $file));
            $mapping = json_decode($mapping, true);
            $maps[$file] = $mapping['name'];
        }
        return $maps;
    }

    /**
     * Load an import map from the map directory
     * @param string $filename of the import map
     * @return array Import map
     */
    protected function loadImportMap($filename) {
        if (empty($filename))
            return null;
        $importMapDir = "importMaps";
        $map = file_get_contents($this->safePath($importMapDir . DIRECTORY_SEPARATOR . $filename));
        $map = json_decode($map, true);
        return $map;
    }

    /**
     * Parse the given keys and attributes to ensure required fields are
     * mapped and new fields are to be created. The verified map will be
     * stored in the 'importMap' key for the $_SESSION super global.
     * @param string $model name of the model
     * @param array $keys
     * @param array $attributes
     * @param boolean $createFields whether or not to create new fields
     */
    protected function verifyImportMap($model, $keys, $attributes, $createFields = false) {
        if (!empty($keys) && !empty($attributes)) {
            // New import map is the provided data
            $importMap = array_combine($keys, $attributes);
            $conflictingFields = array();
            $failedFields = array();

            // To keep track of fields that were mapped multiple times
            $mappedValues = array();
            $multiMappings = array();

            foreach ($importMap as $key => &$value) {
                if (in_array($value, $mappedValues) && !empty($value) && !in_array($value, $multiMappings)) {
                    // This attribute is mapped to two different fields in X2
                    $multiMappings[] = $value;
                } else if ($value !== 'createNew') {
                    $mappedValues[] = $value;
                }
                // Loop through and figure out if we need to create new fields
                $origKey = $key;
                $key = Formatter::deCamelCase($key);
                $key = preg_replace('/\[W|_]/', ' ', $key);
                $key = mb_convert_case($key, MB_CASE_TITLE, "UTF-8");
                $key = preg_replace('/\W/', '', $key);
                if ($value == 'createNew' && !$createFields) {
                    $importMap[$origKey] = 'c_' . strtolower($key);
                    $fieldLookup = Fields::model()->findByAttributes(array(
                        'modelName' => $model,
                        'fieldName' => $key
                    ));
                    if (isset($fieldLookup)) {
                        $conflictingFields[] = $key;
                        continue;
                    } else {
                        $customFieldLookup = Fields::model()->findByAttributes(array(
                            'modelName' => $model,
                            'fieldName' => $importMap[$origKey]
                        ));
                        if (!$customFieldLookup instanceof Fields) {
                            // Create a custom field if one doesn't exist already
                            $columnName = strtolower($key);
                            $field = new Fields;
                            $field->modelName = $model;
                            $field->type = "varchar";
                            $field->fieldName = $columnName;
                            $field->required = 0;
                            $field->searchable = 1;
                            $field->relevance = "Medium";
                            $field->custom = 1;
                            $field->modified = 1;
                            $field->attributeLabel = $field->generateAttributeLabel($key);
                            if (!$field->save()) {
                                $failedFields[] = $key;
                            }
                        }
                    }
                }
            }

            // Check for required attributes that are missing
            $requiredAttrs = Yii::app()->db->createCommand()
                    ->select('fieldName, attributeLabel')
                    ->from('x2_fields')
                    ->where('modelName = :model AND required = 1', array(
                        ':model' => str_replace(' ', '', $model)))
                    ->queryAll();
            $missingAttrs = array();
            foreach ($requiredAttrs as $attr) {
                // Skip visibility, it can be set for them
                if (strtolower($attr['fieldName']) == 'visibility')
                    continue;
                // Ignore missing first/last name, this can be inferred from full name
                if ($model === 'Contacts' && ($attr['fieldName'] === 'firstName' || $attr['fieldName'] === 'lastName') && in_array('name', array_values($importMap)))
                    continue;
                // Otherwise, a required field is missing and should be reported to the user
                if (!in_array($attr['fieldName'], array_values($importMap)))
                    $missingAttrs[] = $attr['attributeLabel'];
            }
            if (!empty($conflictingFields)) {
                $result = array("2", implode(', ', $conflictingFields));
            } else if (!empty($missingAttrs)) {
                $result = array("3", implode(', ', $missingAttrs));
            } else if (!empty($failedFields)) {
                $result = array("1", implode(', ', $failedFields));
            } else if (!empty($multiMappings)) {
                $result = array("4", implode(', ', $multiMappings));
            } else {
                $result = array("0");
            }
            $_SESSION['importMap'] = $importMap;
        } else {
            $result = array("0");
            $_SESSION['importMap'] = array();
        }
        return $result;
    }

    /**
     * Insert placeholders for unmapped fields to ensure the import
     * map contains all possible fields
     * @param array $map Import map
     * @param array $fields Metadata fields from CSV
     * @returns array Normalized import map
     */
    protected function normalizeImportMap($map, $fields) {
        foreach ($fields as $field) {
            if (!array_key_exists ($field, $map)) {
                $map[$field] = null;
            }
        }
        return $map;
    }

    /**
     * Calculates the number of lines in a CSV file for import
     * Warning: This must load and traverse the length of the file
     */
    protected function calculateCsvLength($csvfile) {
        $lineCount = null;
        ini_set('auto_detect_line_endings', 1); // Account for Mac based CSVs if possible
        $fp = fopen ($csvfile, 'r');
        if ($fp) {
            $lineCount = 0;
            while (true) {
                $arr = fgetcsv ($fp);
                if ($arr !== false && !is_null ($arr)) {
                    if ($arr === array (null)) {
                        continue;
                    } else {
                        $lineCount++;
                    }
                } else {
                    break;
                }
            }
        }
        return $lineCount - 1;
    }

    /**
     * Remove any lone \r characters
     * @param string $csvfile Path to CSV file
     */
    protected function fixCsvLineEndings($csvFile) {
        $text = file_get_contents($csvFile);
        $replacement = preg_replace('/\r([^\n])/m', "\r\n\\1", $text);
        file_put_contents($csvFile, $replacement);
    }

    /**
     * Read metadata from the CSV and initialize session variables
     * @param resource $fp File pointer to CSV
     * @return array CSV Metadata and X2Attributes
     */
    protected function initializeModelImporter($fp) {
        $meta = fgetcsv($fp);
        if ($meta === false)
            throw new Exception('There was an error parsing the models of the CSV.');
        while ("" === end($meta)) {
            array_pop($meta); // Remove empty data from the end of the metadata
        }
        if (count($meta) == 1) { // This was from a global export CSV, the first row is the version
            $version = $meta[0]; // Remove it and repeat the above process
            $meta = fgetcsv($fp);
            if ($meta === false)
                throw new Exception('There was an error parsing the contents of the CSV.');
            while ("" === end($meta)) {
                array_pop($meta);
            }
        }
        if (empty($meta)) {
            $_SESSION['errors'] = Yii::t('admin', "Empty CSV or no metadata specified");
            $this->owner->redirect('importModels');
        }

        // Add the import failures column to the failed records meta
        $failedContacts = fopen($this->safePath('failedRecords.csv'), 'w+');
        $failedHeader = $meta;
        if (end($meta) != 'X2_Import_Failures')
            $failedHeader[] = 'X2_Import_Failures';
        else
            array_pop ($meta);
        fputcsv($failedContacts, $failedHeader);
        fclose($failedContacts);

        // Set our file offset for importing Contacts
        $_SESSION['offset'] = ftell($fp);
        $_SESSION['metaData'] = $meta;

        // Ensure the selected model hasn't been lost
        if (array_key_exists('model', $_SESSION))
            $modelName = str_replace(' ', '', $_SESSION['model']);
        else
            $this->errorMessage(Yii::t('admin', "Session information has been lost. Please retry your import."
            ));
        $x2attributes = array_keys(X2Model::model($modelName)->attributes);
        while ("" === end($x2attributes)) {
            array_pop($x2attributes);
        }
        if ($modelName === 'Actions') {
            // add Action.description to attributes so that it is automatically mapped
            $x2attributes[] = 'actionDescription';
        }
        // Initialize session data
        $_SESSION['importMap'] = array();
        $_SESSION['imported'] = 0;
        $_SESSION['failed'] = 0;
        $_SESSION['created'] = 0;
        $_SESSION['fields'] = X2Model::model($modelName)->getFields(true);
        $_SESSION['x2attributes'] = $x2attributes;
        $_SESSION['mapName'] = "";
        $_SESSION['importId'] = $this->getNextImportId();

        return array($meta, $x2attributes);
    }

    /**
     * The goal of this function is to attempt to map meta into a series of
     * Contact attributes, which it will do via string comparison on the Contact
     * attribute names, the Contact attribute labels and a pattern match.
     * @param array $attributes Contact model's attributes
     * @param array $meta Provided metadata in the CSV
     */
    protected function createImportMap($attributes, $meta) {
        // We need to do data processing on attributes, first copy & preserve
        $originalAttributes = $attributes;
        // Easier to just do both strtolower than worry about case insensitive comparison
        $attributes = array_map('strtolower', $attributes);
        $processedMeta = array_map('strtolower', $meta);
        // Remove any non word characters or underscores
        $processedMeta = preg_replace('/[\W|_]/', '', $processedMeta);
        // Now do the same with Contact attribute labels
        $labels = X2Model::model(str_replace(' ', '', $_SESSION['model']))->attributeLabels();
        $labels = array_map('strtolower', $labels);
        $labels = preg_replace('/[\W|_]/', '', $labels);
        /*
         * At the end of this loop, any fields we are able to suggest a mapping
         * for are automatically populated into an array in $_SESSION with
         * the format:
         *
         * $_SESSION['importMap'][<x2_attribute>] = <metadata_attribute>
         */
        foreach ($meta as $metaVal) {
            // Ignore the import failures column
            if ($metaVal == 'X2_Import_Failures')
                continue;
            if ($metaVal === 'tags') {
                $_SESSION['importMap']['applyTags'] = $metaVal;
                continue;
            }
            // Same reason as $originalAttributes
            $originalMetaVal = $metaVal;
            $metaVal = strtolower(preg_replace('/[\W|_]/', '', $metaVal));
            /*
             * First check if we're lucky and maybe the processed metadata value
             * matches a contact attribute directly. Things like first_name
             * would be converted to firstname and so match perfectly. If we
             * find a match here, assume it is the most correct possibility
             * and add it to our session import map
             */
            if (in_array($metaVal, $attributes)) {
                $attrKey = array_search($metaVal, $attributes);
                $_SESSION['importMap'][$originalAttributes[$attrKey]] = $originalMetaVal;
                /*
                 * The next possibility is that the metadata value matches an attribute
                 * label perfectly. This is more common for a field like company
                 * where the label is "Account" but it's our second best bet for
                 * figuring out the metadata.
                 */
            } elseif (in_array($metaVal, $labels)) {
                $attrKey = array_search($metaVal, $labels);
                $_SESSION['importMap'][$attrKey] = $originalMetaVal;
                /*
                 * The third best option is that there is a partial word match
                 * on the metadata value. However, we don't want to do a simple
                 * preg search as that may give weird results, we want to limit
                 * with a word boundary to see if the first part matches. This isn't
                 * ideal but it fixes some edge cases.
                 */
            } elseif (count(preg_grep("/\b$metaVal/i", $attributes)) > 0) {
                $keys = array_keys(preg_grep("/\b$metaVal/i", $attributes));
                $attrKey = $keys[0];
                if (!isset($_SESSION['importMap'][$originalMetaVal]))
                    $_SESSION['importMap'][$originalAttributes[$attrKey]] = $originalMetaVal;
                /*
                 * Finally, check if there is a partial word match on the attribute
                 * label as opposed to the field name
                 */
            }elseif (count(preg_grep("/\b$metaVal/i", $labels)) > 0) {
                $keys = array_keys(preg_grep("/\b$metaVal/i", $labels));
                $attrKey = $keys[0];
                if (!isset($_SESSION['importMap'][$originalMetaVal]))
                    $_SESSION['importMap'][$attrKey] = $originalMetaVal;
            }
        }
        /*
         * Finally, we want to do a quick reverse operation in case there
         * were any fields that weren't mapped correctly based on the directionality
         * of the word boundary. For example, if we were checking "zipcode"
         * against "zip" this would not be a match because the pattern "zipcode"
         * is longer and will fail. However, "zip" will match into "zipcode"
         * and should be accounted for. This loop goes through the x2 attributes
         * instead of the metadata to ensure bidirectionality.
         */
        foreach ($originalAttributes as $attribute) {
            if (in_array($attribute, $processedMeta)) {
                $metaKey = array_search($attribute, $processedMeta);
                $_SESSION['importMap'][$attribute] = $meta[$metaKey];
            } elseif (count(preg_grep("/\b$attribute/i", $processedMeta)) > 0) {
                $matches = preg_grep("/\b$attribute/i", $processedMeta);
                $metaKeys = array_keys($matches);
                $metaValue = $meta[$metaKeys[0]];
                if (!isset($_SESSION['importMap'][$attribute]))
                    $_SESSION['importMap'][$attribute] = $metaValue;
            }
        }
    }

    /**
     * Append an empty placeholder for action texts, or set the attribute of the last action
     * text in the container if attributes are specified
     */
    protected function setCurrentActionText($attributes = null) {
        if (is_null($attributes))
            $this->modelContainer['ActionText'][] = array();
        else {
            $containerId = count($this->modelContainer['ActionText']) - 1;
            $this->modelContainer['ActionText'][$containerId] = $attributes;
        }
    }

    /**
     * The import assumes we have human readable data in the CSV and will thus need to convert. This
     * method converts link, date, and dateTime fields to the appropriate machine friendly data.
     * @param string $modelName The model class being imported
     * @param X2Model $model The currently importing model record
     * @param string $fieldName Field to set
     * @param string $importAttribute Value to set field
     * @returns X2Model $model
     */
    protected function importRecordAttribute($modelName, X2Model $model, $fieldName, $importAttribute) {

        $fieldRecord = Fields::model()->findByAttributes(array(
            'modelName' => $modelName,
            'fieldName' => $fieldName,
        ));

        // Skip setting the attribute if it has already been set or if the entry from
        // the CSV is empty.
        if (empty($importAttribute) && ($importAttribute !== 0 && $importAttribute !== '0')) {
            return $model;
        }
        if ($fieldName === 'actionDescription' && $modelName === 'Actions') {
            $text = new ActionText;
            $text->text = $importAttribute;
            if (isset($model->id))
                $text->actionId = $model->id;
            $this->setCurrentActionText ($text->attributes);
            return $model;
        }

        // ensure the provided id is valid
        if ((strtolower($fieldName) === 'id') && (!preg_match('/^\d+$/', $importAttribute) || $importAttribute >= 4294967295)) {
            $model->id = $importAttribute;
            $model->addError ('id', Yii::t('importexport', "ID '$importAttribute' is not valid."));
            return $model;
        }

        switch ($fieldRecord->type) {
            case "link":
                $model = $this->importRecordLinkAttribute($modelName, $model, $fieldRecord, $importAttribute);
                break;
            case "dateTime":
            case "date":
                if (Formatter::parseDateTime ($importAttribute) !== false)
                    $model->$fieldName = Formatter::parseDateTime ($importAttribute);
                break;
            case "visibility":
                switch ($importAttribute) {
                    case 'Private':
                        $model->$fieldName = 0;
                        break;
                    case 'Public':
                        $model->$fieldName = 1;
                        break;
                    case 'User\'s Groups':
                        $model->$fieldName = 2;
                        break;
                    default:
                        $model->$fieldName = $importAttribute;
                }
                break;
            default:
                $model->$fieldName = $importAttribute;
        }
        return $model;
    }

    /**
     * Handle setting link type fields and create linked records if specified
     * @param string $modelName The model class being imported
     * @param X2Model $model The currently importing model record
     * @param Fields $fieldRecord Field to set
     * @param string $importAttribute Value to set field
     * @returns X2Model $model
     */
    protected function importRecordLinkAttribute($modelName, X2Model $model, Fields $fieldRecord, $importAttribute) {
        $fieldName = $fieldRecord->fieldName;
        $className = ucfirst($fieldRecord->linkType);
        if (isset($_SESSION['linkMatchMap']) && !empty($_SESSION['linkMatchMap'][$fieldName])) {
            $linkMatchAttribute = $_SESSION['linkMatchMap'][$fieldName];
        }

        if (ctype_digit($importAttribute) && !isset($linkMatchAttribute)) {
            $lookup = X2Model::model($className)->findByPk($importAttribute);
            $model->$fieldName = $importAttribute;
            if (!empty($lookup) && isset($lookup)) {
                // Create a link to the existing record
                $model->$fieldName = $lookup->nameId;
                $relationship = new Relationships;
                $relationship->firstType = $modelName;
                $relationship->secondType = $className;
                $relationship->secondId = $importAttribute;
                $this->importRelations[$this->recordsImported][] = $relationship->attributes;
            }
        } else {
            $lookupAttr = isset($linkMatchAttribute) ? $linkMatchAttribute : 'name';
            $lookup = X2Model::model($className)->findByAttributes(array($lookupAttr => $importAttribute));
            if (!empty($lookup) && isset($lookup)) {
                $model->$fieldName = $lookup->nameId;
                $relationship = new Relationships;
                $relationship->firstType = $modelName;
                $relationship->secondType = $className;
                $relationship->secondId = $lookup->id;
                $this->importRelations[$this->recordsImported][] = $relationship->attributes;
            } elseif (!empty($lookup) && isset($lookup) && isset($_SESSION['createRecords']) && $_SESSION['createRecords'] == 1 &&
                    !($modelName === 'BugReports' && $fieldRecord->linkType === 'BugReports')) {
                // Skip creating related bug reports; the created report wouldn't hold any useful info.
                $className = ucfirst($fieldRecord->linkType);
                if (class_exists($className)) {
                    $lookup = new $className;
                    if ($_SESSION['skipActivityFeed'] === 1)
                        $lookup->createEvent = false;
                    $lookup->name = $importAttribute;
                    if ($className === 'Contacts' || $className === 'X2Leads') {
                        self::fixupImportedContactName($lookup);
                    }
                    if ($lookup->hasAttribute('visibility'))
                        $lookup->visibility = 1;
                    if ($lookup->hasAttribute('description'))
                        $lookup->description = "Generated by " . $modelName . " import.";
                    if ($lookup->hasAttribute('createDate'))
                        $lookup->createDate = time();

                    if (!array_key_exists($className, $this->modelContainer))
                        $this->modelContainer[$className] = array();

                    // Ensure this linked record has not already been accounted for
                    $createNewLinkedRecord = true;
                    if ($model->hasAttribute('name')) {
                        $model->$fieldName = $lookup->name;
                    } else {
                        $model->$fieldName = $importAttribute;
                    }

                    foreach ($this->modelContainer[$className] as $record) {
                        if ($record['name'] === $lookup->name) {
                            $createNewLinkedRecord = false;
                            break;
                        }
                    }

                    if ($createNewLinkedRecord) {
                        $this->modelContainer[$className][] = $lookup->attributes;
                        if (isset($_SESSION['created'][$className])) {
                            $_SESSION['created'][$className] ++;
                        } else {
                            $_SESSION['created'][$className] = 1;
                        }
                    }
                    $relationship = new Relationships;
                    $relationship->firstType = $modelName;
                    $relationship->secondType = $className;
                    $this->importRelations[$this->recordsImported][] = $relationship->attributes;
                    $this->createdLinkedModels[] = $model->$fieldName;
                }
            } else {
                $model->$fieldName = $importAttribute;
            }
        }
        return $model;
    }

    /**
     * Helper method to help out the user in the special case where a Contact's full name
     * is set, but first and last name aren't, or vice versa.
     * @param $model
     * @returns X2Model $model
     */
    protected static function fixupImportedContactName($model) {
        if (!empty($model->name) || !empty($model->firstName) || !empty($model->lastName)) {
            $nameFormat = Yii::app()->settings->contactNameFormat;
            switch ($nameFormat) {
                case 'lastName, firstName':
                    if (empty ($model->name))
                        $model->name = $model->lastName . ", " . $model->firstName;
                    $decomposePattern = '/^(?P<last>\w+), ?(?P<first>\w+)$/';
                    break;
                case 'firstName lastName':
                default:
                    if (empty ($model->name))
                        $model->name = $model->firstName . " " . $model->lastName;
                    $decomposePattern = '/^(?P<first>\w+) (?P<last>\w+)$/';
                    break;
            }
            preg_match ($decomposePattern, $model->name, $matches);
            if (array_key_exists ('first', $matches) && array_key_exists ('last', $matches)) {
                $model->firstName = $matches['first'];
                $model->lastName = $matches['last'];
            }
        }
        return $model;
    }

    /**
     * This method is used after importing a records attributes to perform extra tasks, such as
     * assigning lead routing, setting visibility, and reconstructing Action associations.
     * @param string $modelName Name of the model being imported
     * @param X2Model $model Current model to import
     * @returns X2Model $model
     */
    protected function fixupImportedAttributes($modelName, X2Model $model) {
        if ($modelName === 'Contacts' || $modelName === 'X2Leads')
            $model = self::fixupImportedContactName($model);

        if ($modelName === 'Actions' && isset($model->associationType))
            $model = $this->reconstructImportedActionAssoc($model);

        // Set visibility to Public by default if unset by import
        if ($model->hasAttribute('visibility') && is_null($model->visibility))
            $model->visibility = 1;
        // If date fields were provided, do not create new values for them
        if (!empty($model->createDate) || !empty($model->lastUpdated) ||
                !empty($model->lastActivity)) {
            $now = time();
            if (empty($model->createDate))
                $model->createDate = $now;
            if (empty($model->lastUpdated))
                $model->lastUpdated = $now;
            if ($model->hasAttribute('lastActivity') && empty($model->lastActivity))
                $model->lastActivity = $now;
        }
        if ($_SESSION['leadRouting'] == 1) {
            $assignee = $this->getNextAssignee();
            if ($assignee == "Anyone")
                $assignee = "";
            $model->assignedTo = $assignee;
        }
        // Loop through our override and set the manual data
        foreach ($_SESSION['override'] as $attr => $val) {
            $model->$attr = $val;
        }

        return $model;
    }

    /**
     * Remove a record with the same ID, save the model attributes in the container, and increment
     * the count of imported records
     * @param X2Model $model
     * @param array $importedIds Array of ids from imported models
     */
    protected function saveImportedModel(X2Model $model, $modelName, $importedIds) {
        if (!empty($model->id) && $_SESSION['updateRecords']) {
            $tableName = X2Model::model($modelName)->tableName();
            $criteria = new CDbCriteria;
            $criteria->compare('id', $model->id);
            Yii::app()->db->schema->commandBuilder
                    ->createDeleteCommand($tableName, $criteria)
                    ->execute();
        }
        // Save our model & create the import records and 
        // relationships. Passing $validate=false to CActiveRecord.save
        // because validation has already happened at this point
        $this->modelContainer[$modelName][] = $model->attributes;
        $_SESSION['imported'] ++;
        $importedIds[] = $model->id;
        return $importedIds;
    }

    /**
     * Execute a multiple insert command
     * @param string $modelType Child of X2Model
     * @param array $models Array of model attributes to create
     * @return int Last inserted id
     */
    protected function insertMultipleRecords($modelType, $models) {
        if (empty($models))
            return null;
        $tableName = X2Model::model($modelType)->tableName();
        Yii::app()->db->schema->commandBuilder
                ->createMultipleInsertCommand($tableName, $models)
                ->execute();
        $lastInsertId = Yii::app()->db->schema->commandBuilder
                ->getLastInsertId($tableName);
        return $lastInsertId;
    }

    /**
     * This grabs 5 sample records from the CSV to get an example of what
     * the data looks like.
     * @return array Sample records
     */
    protected function prepareImportSampleRecords($meta, $fp) {
        $sampleRecords = array();
        for ($i = 0; $i < 5; $i++) {
            if ($sampleRecord = fgetcsv($fp, 0, $_SESSION['delimeter'], $_SESSION['enclosure'])) {
                if(count($sampleRecord) > count($meta)){
                    $sampleRecord = array_slice($sampleRecord, 0, count($meta));
                }
                if (count($sampleRecord) < count($meta)) {
                    $sampleRecord = array_pad($sampleRecord, count($meta), null);
                }
                if (!empty($meta)) {
                    $sampleRecord = array_combine($meta, $sampleRecord);
                    $sampleRecords[] = $sampleRecord;
                }
            }
        }
        return $sampleRecords;
    }

    /**
     * Handle reconstructing and validating Action associations
     * @param Actions $model Action to reconstruct association
     * @returns Actions $model
     */
    protected function reconstructImportedActionAssoc(Actions $model) {
        $exportableModules = array_merge(
                array_keys(Modules::getExportableModules()), array('None')
        );
        $exportableModules = array_map('lcfirst', $exportableModules);
        $model->associationType = lcfirst($model->associationType);
        if (!in_array($model->associationType, $exportableModules)) {
            // Invalid association type
            $model->addError('associationType', Yii::t('admin', 'Unknown associationType.'));
        } else if (isset($model->associationId) && $model->associationId !== '0') {
            $associatedModel = X2Model::model($model->associationType)
                    ->findByPk($model->associationId);
            if ($associatedModel)
                $model->associationName = $associatedModel->nameId;
        } else if (!isset($model->associationId) && isset($model->associationName)) {
            // Retrieve associationId
            $staticAssociationModel = X2Model::model($model->associationType);
            if ($staticAssociationModel->hasAttribute('name') &&
                    !ctype_digit($model->associationName)) {
                $associationModelParams = array('name' => $model->associationName);
            } else {
                $associationModelParams = array('id' => $model->associationName);
            }
            $lookup = $staticAssociationModel->findByAttributes($associationModelParams);
            if (isset($lookup)) {
                $model->associationId = $lookup->id;
                $model->associationName = $lookup->hasAttribute('nameId') ?
                        $lookup->nameId : $lookup->name;
            }
        }
        return $model;
    }

    /**
     * Finalize this batch of records by performing a mass insert, handling accounting,
     * updating nameIds, and rendering the JSON response
     * @param string $modelName Name of the model class being imported
     * @param boolean $mappedId Whether the primary model's ID has been mapped: this alters
     *    the result of lastInsertId
     * @param boolean $finished Whether this batch has reached the end of the CSV
     */
    protected function finishImportBatch($modelName, $mappedId, $finished = false) {
        if (!array_key_exists ($modelName, $this->modelContainer) || empty($this->modelContainer[$modelName])) {
            $this->importerResponse ($finished);
            return;
        }

        // Keep track of the lastInsertId for each type
        $lastInsertedIds = array();

        // First insert the records being imported
        $lastInsertedIds[$modelName] = $this->insertMultipleRecords(
                $modelName, $this->modelContainer[$modelName]
        );
        $primaryModelCount = count($this->modelContainer[$modelName]);
        // If id was mapped, then lastInsertId would actually be the last in the sequence.
        // Otherwise, lastInsertId would be the first
        if ($mappedId) {
            $primaryIdRange = range(
                    $lastInsertedIds[$modelName] - $primaryModelCount + 1, $lastInsertedIds[$modelName]
            );
        } else {
            $primaryIdRange = range(
                    $lastInsertedIds[$modelName], $lastInsertedIds[$modelName] + $primaryModelCount - 1
            );
        }
        $this->handleImportAccounting($this->modelContainer[$modelName], $modelName, $lastInsertedIds, $mappedId);
        $this->massUpdateImportedNameIds($primaryIdRange, $modelName);

        // Now create remaining auxiliary records
        foreach ($this->modelContainer as $type => $models) {
            if ($type === $modelName) // these were already processed
                continue;

            if ($modelName === 'Actions' && $type === 'ActionText') {
                // set the actionIds and insert ActionText records
                $firstInsertedId = $primaryIdRange[0];
                $actionTexts = array();
                foreach ($models as $i => $model) {
                    if (empty($model))
                        continue;
                    if (!isset($model['actionId']))
                        $model['actionId'] = $firstInsertedId + $i;
                    $actionTexts[] = $model;
                }
                $this->insertMultipleRecords ('ActionText', $actionTexts);
            } else if ($type === 'Tags') {
                // Associate each of the tags with the respective imported model
                $firstInsertedId = $primaryIdRange[0];
                $tags = array();
                foreach ($models as $i => $tagModels) {
                    if (empty($tagModels))
                        continue;
                    foreach ($tagModels as $tag) {
                        $tag['itemId'] = $firstInsertedId + $i;
                        $tags[] = $tag;
                    }
                }
                $this->insertMultipleRecords ('Tags', $tags);
            } else {
                // otherwise handle the records normally
                $lastInsertedIds[$type] = $this->insertMultipleRecords($type, $models);
                $this->handleImportAccounting($models, $type, $lastInsertedIds);
                $this->fixupLinkFields($modelName, $type, $primaryIdRange);
                // related records won't have ID set; therefore, lastInsertId would have
                // returned the first record in a sequence
                $idRange = range(
                        $lastInsertedIds[$type], $lastInsertedIds[$type] + count($models) - 1
                );
                $this->massUpdateImportedNameIds ($idRange, $type);
                $this->triggerImportedRecords ($idRange, $type);
            }
        }

        $this->establishImportRelationships ($primaryIdRange[0], $mappedId);
        $this->triggerImportedRecords ($primaryIdRange, $modelName);
        $this->importerResponse ($finished);
    }

    /**
     * Populate the nameId field since auto-populating fields is
     * disabled and it is far more efficient to do it in a single query
     * @param array $importedIds List of record ids
     * @param string $type Model name
     */
    protected function massUpdateImportedNameIds($importedIds, $type) {
        $hasNameId = Fields::model()->findByAttributes(array(
            'fieldName' => 'nameId',
            'modelName' => $type,
        ));
        if ($hasNameId)
            X2Model::massUpdateNameId($type, $importedIds);
    }

    /**
     * Trigger the X2Workflow RecordCreateTrigger on the imported models
     * @param array $importedIds List of record ids
     * @param string $type Model name
     */
    protected function triggerImportedRecords($importedIds, $type) {
        foreach ($importedIds as $id) {
            $model = X2Model::model ($type)->findByPk ($id);
            X2Flow::trigger('RecordCreateTrigger', array('model'=>$model));
        }
    }

    /**
     * Render a JSON encoded response for the importer JS to handle
     */
    private function importerResponse ($finished) {
        $finished = !isset ($finished) ? false : $finished;
        echo json_encode(array(
            ($finished ? '1' : '0'),
            $_SESSION['imported'],
            $_SESSION['failed'],
            json_encode($_SESSION['created']),
        ));
    }

    /**
     * Create additional records related to the import, including the requested Tags, comment
     * Actions, Import records, Events, and Relationships
     * @param array $models Array of arrays of model attributes
     * @param string $modelName Name of the model being imported
     * @param array $lastInsertedIds The last MySQL IDs that were created, indexed by model type
     * @param boolean $mappedId Whether ID was mapped: this affects lastInsertId's behavior
     */
    protected function handleImportAccounting($models, $modelName, $lastInsertedIds, $mappedId = false) {
        if (count($models) === 0)
            return;
        $now = time();
        $editingUsername = Yii::app()->user->name;
        $auxModelContainer = array(
            'Imports' => array(),
            'Actions' => array(),
            'Events' => array(),
            'Notification' => array(),
            'Tags' => array(),
        );
        if ($mappedId)
            $firstNewId = $lastInsertedIds[$modelName] - count($models) + 1;
        else
            $firstNewId = $lastInsertedIds[$modelName];

        for ($i = 0; $i < count($models); $i++) {
            $record = $models[$i];
            if ($mappedId || ($_SESSION['updateRecords'] && !empty($record['id']))) {
                $modelId = $models[$i]['id'];
            } else {
                $modelId = $i + $firstNewId;
            }
            // Create a event for the imported record, and create a notification for the assigned
            // user if one exists, since ChangelogBehavior will not be triggered with
            // createMultipleInsertCommand()
            if ($_SESSION['skipActivityFeed'] !== 1) {
                $event = new Events;
                $event->visibility = array_key_exists('visibility', $record) ?
                        $record['visibility'] : 1;
                $event->associationType = $modelName;
                $event->associationId = $modelId;
                $event->timestamp = $now;
                $event->user = $editingUsername;
                $event->type = 'record_create';
                $auxModelContainer['Events'][] = $event->attributes;
            }
            if (array_key_exists('assignedTo', $record) && !empty($record['assignedTo']) &&
                    $record['assignedTo'] != $editingUsername && $record['assignedTo'] != 'Anyone') {
                $notif = new Notification;
                $notif->user = $record['assignedTo'];
                $notif->createdBy = $editingUsername;
                $notif->createDate = $now;
                $notif->type = 'create';
                $notif->modelType = $modelName;
                $notif->modelId = $modelId;
                $auxModelContainer['Notification'][] = $notif->attributes;
            }

            // Add all listed tags
            foreach ($_SESSION['tags'] as $tag) {
                // Retrieve existing records to avoid duplicate tags, as we don't have the
                // convenience of ActiveRecord while adding tags
                if (!empty($record['id']) && $_SESSION['updateRecords'])
                    $model = X2Model::model($modelName)->findByPk($record['id']);
                else
                    unset($model);
                if (!isset($model) || $model->isNewRecord || !$model->hasTag($tag)) {
                    $tagModel = new Tags;
                    $tagModel->taggedBy = 'Import';
                    $tagModel->timestamp = $now;
                    $tagModel->type = $modelName;
                    $tagModel->itemId = $modelId;
                    $tagModel->tag = $tag;
                    $tagModel->itemName = $modelName;
                    $auxModelContainer['Tags'][] = $tagModel->attributes;
                }
            }
            // Log a comment if one was requested
            if (!empty($_SESSION['comment'])) {
                $action = new Actions;
                $action->associationType = lcfirst(str_replace(' ', '', $modelName));
                $action->associationId = $modelId;
                $action->createDate = $now;
                $action->updatedBy = Yii::app()->user->getName();
                $action->lastUpdated = $now;
                $action->complete = "Yes";
                $action->completeDate = $now;
                $action->completedBy = Yii::app()->user->getName();
                $action->type = "note";
                $action->visibility = 1;
                $action->reminder = "No";
                $action->priority = 1; // Set priority to Low
                $auxModelContainer['Actions'][] = $action->attributes;
            }

            $importLink = new Imports;
            $importLink->modelType = $modelName;
            $importLink->modelId = $modelId;
            $importLink->importId = $_SESSION['importId'];
            $importLink->timestamp = $now;
            $auxModelContainer['Imports'][] = $importLink->attributes;
        }

        foreach ($auxModelContainer as $type => $records) {
            if (empty($records))
                continue;
            $lastInsertId = $this->insertMultipleRecords($type, $records);
            if ($type === 'Actions') {
                // Create ActionText and Import records for the comment Actions that were created
                if (empty($records))
                    continue;
                $actionImportRecords = array();
                $actionTextRecords = array();
                $actionIdRange = range($lastInsertId, $lastInsertId + count($records) - 1);
                foreach ($actionIdRange as $i) {
                    $importLink = new Imports;
                    $importLink->modelType = "Actions";
                    $importLink->modelId = $i;
                    $importLink->importId = $_SESSION['importId'];
                    $importLink->timestamp = $now;
                    $actionImportRecords[] = $importLink->attributes;

                    $actionText = new ActionText;
                    $actionText->actionId = $i;
                    $actionText->text = $_SESSION['comment'];
                    $actionTextRecords[] = $actionText->attributes;
                }
                $this->insertMultipleRecords('Imports', $actionImportRecords);
                $this->insertMultipleRecords('ActionText', $actionTextRecords);
            }
        }
    }

    /**
     * Process the link-type fields to set nameId
     * @param int $count The number of primary models being imported
     * @param string $modelName The primary model being imported
     * @param string $type The model of the linked record
     * @param array $lastInsertedIds Array of last inserted IDs, indexed by model name
     */
    protected function fixupLinkFields($modelName, $type, $primaryIdRange) {
        $linkTypeFields = Yii::app()->db->createCommand()
                        ->select('fieldName')
                        ->from('x2_fields')
                        ->where('type = "link" AND modelName = :modelName AND linkType = :linkType', array(
                            ':modelName' => $modelName,
                            ':linkType' => $type,
                        ))->queryColumn();
        $primaryTable = X2Model::model($modelName)->tableName();
        foreach ($linkTypeFields as $field) {
            // update each link type field
            $staticModel = X2Model::model($type);
            if (!$staticModel->hasAttribute('name'))
                continue;
            $secondaryTable = $staticModel->tableName();

            $sql = 'UPDATE `' . $primaryTable . '` a JOIN `' . $secondaryTable . '` b ' .
                    'ON a.' . $field . ' = b.name ' .
                    'SET a.`' . $field . '` = CONCAT(b.name, \'' . Fields::NAMEID_DELIM . '\', b.id) ' .
                    'WHERE a.id in (' . implode(',', $primaryIdRange) . ')';
            Yii::app()->db->createCommand($sql)->execute();
        }
    }

    /**
     * Create relationships records for the linked models
     * @param int $firstNewId The first inserted id
     * @param boolean $mappedId Whether or not ID was a mapped field
     */
    protected function establishImportRelationships($firstNewId, $mappedId = false) {
        $validRelationships = array();

        foreach ($this->importRelations as $i => $modelsRelationships) {
            $modelId = $i + $firstNewId;
            if (empty($modelsRelationships)) // skip placeholders
                continue;
            foreach ($modelsRelationships as $relationship) {
                $relationship['firstId'] = $modelId;
                if (empty($relationship['secondId'])) {
                    $model = X2Model::model($relationship['firstType'])
                            ->findByPk($modelId);
                    $linkedStaticModel = X2Model::model($relationship['secondType']);
                    if (!$model)
                        continue;
                    $fields = Yii::app()->db->createCommand()
                                    ->select('fieldName')
                                    ->from('x2_fields')
                                    ->where('type = \'link\' AND modelName = :firstType AND ' .
                                            'linkType = :secondType', array(
                                        ':firstType' => $relationship['firstType'],
                                        ':secondType' => $relationship['secondType'],
                                    ))->queryColumn();
                    foreach ($fields as $field) {
                        // Check for relationships to new linked models for each link type field
                        if (empty($model->$field)) // skip fields that weren't set
                            continue;
                        $attr = $linkedStaticModel->hasAttribute('nameId') ? 'nameId' : 'name';
                        $linkedId = Yii::app()->db->createCommand()
                                        ->select('id')
                                        ->from($linkedStaticModel->tableName())
                                        ->where($attr . ' = :reference', array(
                                            ':reference' => $model->$field,
                                        ))->queryScalar();
                        if (!$linkedId)
                            continue;
                        $relationship['secondId'] = $linkedId;
                    }
                }
                if (!empty($relationship['secondId']))
                    $validRelationships[] = $relationship;
            }
        }
        $this->insertMultipleRecords('Relationships', $validRelationships);
    }

    /**
     * Save the failed record into a CSV with validation errors
     * @param string $modelName
     * @param X2Model $model
     * @param array $csvLine
     * @param array $metadata
     */
    protected function markFailedRecord($modelName, X2Model $model, $csvLine, $metaData) {
        // If the import failed, then put the data into the failedRecords CSV for easy recovery.
        $failedRecords = fopen($this->safePath('failedRecords.csv'), 'a+');
        $errorMsg = array();
        foreach ($model->errors as $error)
            $errorMsg[] = strtr(implode(' ', array_values($error)), '"', "'");
        $errorMsg = implode(' ', $errorMsg);

        // Add the error to the last column of the csv record
        if (end($metaData) === 'X2_Import_Failures')
            $csvLine[count($csvLine) - 1] = $errorMsg;
        else
            $csvLine[] = $errorMsg;
        fputcsv($failedRecords, $csvLine);
        fclose($failedRecords);
        $_SESSION['failed']++;

        // Remove ActionText placeholder from model container
        if ($modelName === 'Actions')
            array_pop ($this->modelContainer['ActionText']);
    }

    /**
     * Save and attempt to load the uploaded import mapping
     */
    protected function loadUploadedImportMap() {
        $temp = CUploadedFile::getInstanceByName('mapping');
        $temp->saveAs($mapPath = $this->safePath('mapping.json'));
        $mappingFile = fopen($mapPath, 'r');
        $importMap = fread($mappingFile, filesize($mapPath));
        $importMap = json_decode($importMap, true);
        if ($importMap === null) {
            $_SESSION['errors'] = Yii::t('admin', 'Invalid JSON string specified');
            $this->owner->redirect('importModels');
        }
        $_SESSION['importMap'] = $importMap;

        if (array_key_exists('mapping', $importMap)) {
            $_SESSION['importMap'] = $importMap['mapping'];
            if (isset($importMap['name']))
                $_SESSION['mapName'] = $importMap['name'];
            else
                $_SESSION['mapName'] = Yii::t('admin', "Untitled Mapping");
            // Make sure $importMap is consistent with legacy import map format
            $importMap = $importMap['mapping'];
        } else {
            $_SESSION['importMap'] = $importMap;
            $_SESSION['mapName'] = Yii::t('admin', "Untitled Mapping");
        }

        fclose($mappingFile);
        if (file_exists($mapPath))
            unlink($mapPath);
    }

    /**
     * Retrieve all associated export format options from the request parameters
     * @param array $params Request parameters, e.g., $_GET
     * @return array of format options, indexed by form element ID
     */
    protected function readExportFormatOptions($params) {
        $paramNames = array(
            'compressOutput',
            'exportDestination',
            
            'server-path',
            'ftp-path', 'ftp-server', 'ftp-user', 'ftp-pass',
            'scp-path', 'scp-server', 'scp-user', 'scp-pass',
            's3-accessKey', 's3-secretKey', 's3-bucket', 's3-key', 's3-region',
            'gdrive-path', 'gdrive-description',
            
        );
        // Defaults
        $formatParams = array(
            'exportDestination' => 'download',
            'compressOutput' => 'false',
        );
        foreach ($paramNames as $param) {
            if (array_key_exists ($param, $params) && !empty($params[$param]))
                $formatParams[$param] = $params[$param];
        }
        $formatParams['compressOutput'] = $formatParams['compressOutput'] === 'true' ? true : false;
        return $formatParams;
    }

    /**
     * Modifies the export path to ensure a consistent file extensions
     * @param string $path Path to export file
     * @param array $params Export format parameters
     * @param string $filetype Expected file extension
     * @return string $path Modified export path
     */
    protected function adjustExportPath($path, $params, $filetype = 'csv') {
        if (isset($params['compressOutput']) && $params['compressOutput']) {
            $path = str_replace('.'.$filetype, '.zip', $path);
            if (!preg_match ('/\.zip$/', $path))
                $path = $path.'.zip';
        } else {
            if (!preg_match ('/\.'.$filetype.'$/', $path))
                $path = "{$path}.{$filetype}";
        }
        return $path;
    }

    /*
     * Handle pushing exported data to various targets, including download in browser, save
     * locally to server, copy to remote server by FTP or SCP, or push to a cloud provider
     * like Amazon S3 or Google Drive.
     * @param string $src Source file to copy
     * @param array $params Export deliverable parameters, as retrieved by {@link readExportFormatOptions}
     */
    public function prepareExportDeliverable($src, $params) {
        $success = true;
        if (!array_key_exists ('mimeType', $params))
            $params['mimeType'] = 'text/csv';
        if (!array_key_exists ('exportDestination', $params))
            return false;

        if (array_key_exists ('compressOutput', $params) && $params['compressOutput']) {
            // Package the CSV, media files, and modules
            $zip = Yii::app()->zip;
            $dirname = str_replace('.csv', '', $src);
            $dst = $dirname .'/'. basename($src);
            AppFileUtil::ccopy ($src, $dst);
            $zipPath = $this->safePath(basename ($dirname) . '.zip');

            if ($zip->makeZip($dirname, $zipPath)) {
                $src = $zipPath;
                $params['mimeType'] = 'application/zip';
            } else {
                $success = false;
            }
        }
        

        if ($success) {
            switch ($params['exportDestination']) {
                case 'ftp':
                    $success = $this->exportByFtp ($src, $params);
                    break;
                case 'scp':
                    $success = $this->exportByScp ($src, $params);
                    break;
                case 's3':
                    $success = $this->exportToS3 ($src, $params);
                    break;
                case 'gdrive':
                    $success = $this->exportToDrive ($src, $params);
                    break;
                case 'server':
                    $success = $this->saveExportToServer ($src, $params);
                    break;
                case 'download':
                default:
            }
        }
        
        return $success;
    }

    
    /**
     * Copy the exported data to a remote server via FTP
     * @param string $src Source file
     * @param array $params
     * @returns bool $success
     */
    protected function saveExportToServer($src, $params) {
        $dst = $params['server-path'];
        if (is_dir ($dst))
            $dst = $dst .DIRECTORY_SEPARATOR. basename ($src);
        $dst = $this->adjustExportPath ($dst, $params);
        return AppFileUtil::ccopy ($src, $dst);
    }

    /**
     * Copy the exported data to a remote server via FTP
     * @param string $src Source file
     * @param array $params
     * @returns bool $success
     */
    protected function exportByFtp($src, $params) {
        $host = $params['ftp-server'];
        $user = $params['ftp-user'];
        $pass = $params['ftp-pass'];
        $dst = $params['ftp-path'];

        AppFileUtil::ftpInit ($host, $user, $pass);
        $dst = $this->adjustExportPath ($dst, $params);
        $success = AppFileUtil::ccopy ($src, $dst);
        AppFileUtil::ftpClose ();
        return $success;
    }

    /**
     * Copy the exported data to a remote server via SCP
     * @param string $src Source file
     * @param array $params
     * @returns bool $success
     */
    protected function exportByScp($src, $params) {
        $host = $params['scp-server'];
        $user = $params['scp-user'];
        $pass = $params['scp-pass'];
        $dst = $params['scp-path'];

        AppFileUtil::sshInit ($host, $user, $pass);
        $dst = $this->adjustExportPath ($dst, $params);
        $success = AppFileUtil::ccopy ($src, $dst);
        AppFileUtil::sshClose();
        return $success;
    }

    /**
     * Push the exported data to an S3 bucket
     * @param string $src Source file
     * @param array $params
     * @returns bool $success
     */
    protected function exportToS3($src, $params) {
        $accessKey = $params['s3-accessKey'];
        $secretKey = $params['s3-secretKey'];
        $bucket = $params['s3-bucket'];
        $s3Key = $params['s3-key'];
        $region = $params['s3-region'];

        $s3Key = $this->adjustExportPath ($s3Key, $params);
        if ($owner = $this->owner->asa ('S3Behavior'))
            return $this->owner->postToS3 ($accessKey, $secretKey, $bucket, $s3Key, $region, $src);
        else
            return false;
    }

    /**
     * Post the exported data to Google Drive
     * @param string $src Source file
     * @param array $params
     * @returns bool $success
     */
    protected function exportToDrive($src, $params) {
        $path = $params['gdrive-path'];
        $description = $params['gdrive-description'];
        $mimeType = $params['mimeType'];

        $path = $this->adjustExportPath ($path, $params);
        if (Yii::app()->settings->googleIntegration) {
            $auth = new GoogleAuthenticator();
            if ($auth->getAccessToken()){
                $service = $auth->getDriveService();
            }
            $createdFile = null;
            if (isset($service, $_SESSION['access_token'])){
                $file = new Google_Service_Drive_DriveFile();
                $file->setTitle ($path);
                $file->setDescription ($description);
                $file->setMimeType ($mimeType);

                try {
                    $createdFile = $service->files->insert($file, array(
                        'data' => file_get_contents($src),
                        'mimeType' => $mimeType,
                        'uploadType' => 'multipart',
                    ));
                    return !is_null($createdFile);
                } catch(Google_AuthException $e) {
                    unset($_SESSION['access_token']);
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Rollback a particular import stage
     * @param string $model Imported model name
     * @param string $stage Import stage to rollback
     * @param int $importId Import ID
     * @return int Number of rows affected
     */
    protected function rollbackStage($model, $stage, $importId) {
        $stages = array(
            // Delete all tag data
            "tags" => "DELETE a FROM x2_tags a
                INNER JOIN
                x2_imports b ON b.modelId=a.itemId AND b.modelType=a.type
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete all relationship data
            "relationships" => "DELETE a FROM x2_relationships a
                INNER JOIN
                x2_imports b ON b.modelId=a.firstId AND b.modelType=a.firstType
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete any associated actions
            "actions" => "DELETE a FROM x2_actions a
                INNER JOIN
                x2_imports b ON b.modelId=a.associationId AND b.modelType=a.associationType
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete the records themselves
            "records" => "DELETE a FROM " . X2Model::model($model)->tableName() . " a
                INNER JOIN
                x2_imports b ON b.modelId=a.id
                WHERE b.modelType='$model' AND b.importId='$importId'",
            // Delete the log of the records being imported
            "import" => "DELETE FROM x2_imports WHERE modelType='$model' AND importId='$importId'",
        );
        $sqlQuery = $stages[$stage];
        $command = Yii::app()->db->createCommand($sqlQuery);
        return $command->execute();
    }

}

?>
