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




Yii::import('application.tests.WebTestCase');
Yii::import('application.controllers.AdminController');
Yii::import('application.models.Modules');
Yii::import('application.components.util.FileUtil');

/**
 * Test for the Admin controller methods
 *
 * @package application.tests.functional.controllers
 */
class AdminControllerTest extends X2WebTestCase {

    public $fixtures = array(
        'modules' => 'Modules',
        'action_text' => array('ActionText', '.ImportTest'),
        'actions' => array('Actions', '.ImportTest'),
        'contacts' => array('Contacts', '.ImportTest'),
        'accounts' => array('Accounts', '.ImportTest'),
        'opportunities' => array('Opportunity', '.ImportTest'),
        'services' => array('Services', '.ImportTest'),
        'product' => array('Product', '.ImportTest'),
        'x2Leads' => array('X2Leads', '.ImportTest'),
        'quotes' => array('Quote', '.ImportTest'),
        'docs' => array('Docs', '.ImportTest'),
        'bugReports' => array('BugReports', '.ImportTest'),
        'tags' => array('Tags', '.ImportTest'),
    );

    /**
     * Array of CSVs to test. These are loaded from protected/tests/data/csvs/
     */
    public $csvs = array(
        'actions',
        'contacts',
        'accounts',
        'opportunity',
        'services',
        'product',
        'x2Leads',
        'quote',
        'docs',
        'bugReports',
    );

    /**
     * Array of attributes that are to be ignored when verifying imports. These are
     * usually generated or updated, and will be different in most cases
     */
    public $ignoreImportFields = array(
        'createDate',
        'lastUpdated',
        'lastActivity',
    );
    
    /**
     * Copy a module directory structure from the tests/data directory
     */
    protected function setupModule($moduleName) {
        $baseDir = Yii::app()->basePath;
        $src = implode(DIRECTORY_SEPARATOR, array(
            $baseDir,
            'tests',
            'data',
            'testModules',
            $moduleName
        ));
        $dest = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $moduleName
        ));
        $this->assertTrue(FileUtil::ccopy ($src, $dest));
    }

    /**
     * Copy a module directory structure from the tests/data directory
     */
    protected function cleanupModule($moduleName) {
        $dest = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $moduleName
        ));
        FileUtil::rrmdir ($dest);
        $this->assertTrue(!is_dir($dest));
    }

    /**
     * Remove fields that are to be ignored when verifying imports
     * @param array $attributes
     * @return filtered attributes
     */
    protected function removeIgnoredFields(&$attributes) {
        $attributes = array_diff_key ($attributes, array_flip ($this->ignoreImportFields));
    }

    /**
     * Return the translated validation failure text
     */
    protected function getFailedValidationText() {
        return Yii::t('admin', 'have failed validation and were not imported');
    }

    /**
     * Navigate to the import page, upload a CSV, and ensure it was
     * properly uploaded
     * @param string $model Name of the model to import
     */
    protected function prepareImport($model, $csvName, $verifyUpload = true) {
        $this->openX2 ('/admin/importModels?model='.ucfirst($model));
        $csv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'csvs',
            $csvName
        ));
        $this->type ('data', $csv);
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        if ($verifyUpload)
            $this->assertCsvUploaded ($csv);
    }

    /**
     * Navigate to the export page and begin export
     * @param string $model Name of the model to export
     */
    protected function prepareExport($model) {
        $this->openX2 ('/admin/exportModels?model='.ucfirst($model));
    }

    /**
     * Click the 'process' link to begin the import, and assert ui functions
     * as expected
     */
    protected function beginImport() {
        $this->click ("css=#process-link");
        $this->waitForTextPresent ("Import Complete");
        $this->assertAlert ("Import Complete!");

        // Handle warning confirmations whenever a link match attribute selector is used
        $this->storeEval ('window.document.querySelector (".linkMatchSelector") ?
            window.document.querySelector (".linkMatchSelector").length : null',
            'retVal');
        $retVal = $this->getExpression ('${retVal}');
        if ($retVal && $retVal > 0) {
            $this->storeConfirmation();
        }
    }

    /**
     * Click the 'process' link to begin the import, and assert ui functions
     * as expected
     */
    protected function beginExport($exportOptions = array()) {
        foreach ($exportOptions as $option) {
            switch ($option) {
            case 'includeHidden':
                $this->click ("css=#includeHidden");
                break;
            case 'includeTags':
                $this->click ("css=#includeTags");
                break;
            }
        }
        $this->click ("css=#export-button");
        $this->waitForTextPresent ("data successfully exported.");
        $this->assertAlert ("Export Complete!");
    }

    /**
     * Save specified records in memory and remove so that the fields being
     * imported can be verified
     * @param string $model Name of the model
     * @return array of model attribute arrays, indexed by the original model ID
     */
    protected function stashModels ($modelName) {
        $models = X2Model::model ($modelName)->findAll();
        $attributes = array();
        foreach ($models as $model) {
            $attributes[$model->id] = $model->attributes;
            $model->delete();
        }
        return $attributes;
    }

    /********************************************************************
     * Tests
     ********************************************************************/
    public function testConvertCustomModules() {
        // Launch conversion without any new modules. Should have no errors
        $this->openX2 ('/admin/convertCustomModules');
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertElementNotPresent ("css=#x2-php-error");

        // Copy over a legacy module, convert, and verify
        $legacyModule = $this->modules('legacyModule');
        $this->setupModule ($legacyModule->name);
        $this->openX2 ('/admin/convertCustomModules');
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertElementNotPresent ("css=#x2-php-error");
        $this->assertConvertedLegacyModule ($legacyModule->name);
        $this->cleanupModule ($legacyModule->name);

        // Verify behavior when conversion fails and backups are handled properly
        $controllerFile= implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $legacyModule->name,
            'controllers',
            'DefaultController.php'
        ));
        $expected = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'testModules',
            $legacyModule->name
        ));
        $actual = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $legacyModule->name
        ));

        $this->setupModule ($legacyModule->name);
        chmod($controllerFile, 0440); // unwritable file will cause conversion to fail
        $this->openX2 ('/admin/convertCustomModules');
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertElementNotPresent ("css=#x2-php-error");
        $this->assertTextPresent ("Fatal error - Unable to change class declaration. ".
            "Aborting module conversion.");
        $this->assertTextPresent ("Module backup was successfully restored.");

        $this->assertModulesEqual ($expected, $actual);
        $this->cleanupModule ($legacyModule->name);
    }

    /**
     * Iterate over possible module imports, testing to ensure that attributes
     * are correctly imported and set according to importer conventions when
     * the CSV contains valid records
     */
    public function testValidRecordImport() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        foreach ($this->csvs as $modelName) {
            $this->prepareImport ($modelName, $modelName.'.csv');
            $expected = $this->stashModels ($modelName);
            $this->assertGreaterThan (0, count($expected),
                'Failed to load fixture! Models were expected');

            $this->beginImport();
            $this->assertNoValidationErrorsPresent();
            $this->assertModelsWereImported ($expected, $modelName);
        }
    }

    /**
     * Import an Account, Contact, and Action to ensure relationships and links are
     * correctly established
     */
    public function testImportRelations() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        // Import the account, contact, and action
        $relationCsvs = array(
            'Accounts' => 'relations-accounts.csv',
            'Contacts' => 'relations-contacts.csv',
            'Actions' => 'relations-actions.csv',
        );
        foreach ($relationCsvs as $modelType => $csvFile) {
            $this->prepareImport ($modelType, $csvFile);
            $this->beginImport();
            $this->assertNoValidationErrorsPresent();
        }

        // Assert that Relationship records were created
        $this->assertImportRelation(array(
            array('type' => 'Contacts', 'id' => 8888),
            array('type' => 'Accounts', 'id' => 9999),
        ));
        $this->assertImportRelation(array(
            array('type' => 'Contacts', 'id' => 8889),
            array('type' => 'Accounts', 'id' => 9999),
        ));
        $this->assertImportRelation(array(
            array('type' => 'Contacts', 'id' => 8889),
            array('type' => 'Actions', 'id' => 7778),
        ));

        // Ids as defined in tests/data/csvs/relations-*.csv
        $action = X2Model::model('Actions')->findByPk (7777);
        $association = X2Model::model('Accounts')->findByPk (9999);
        $this->assertActionAssociation ($action, $association);

        $action = X2Model::model('Actions')->findByPk (7778);
        $association = X2Model::model('Contacts')->findByPk (8888);
        $this->assertActionAssociation ($action, $association);
    }

    /**
     * Ensure the importer raises validation errors when data in the supplied
     * CSV is malformed
     */
    public function testImportValidationFailures() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        $failureCsvs = array(
            'Contacts' => 'failure-contacts.csv',
        );
        foreach ($failureCsvs as $modelType => $csvFile) {
            $this->prepareImport ($modelType, $csvFile);
            $this->beginImport();
            $this->assertValidationErrorsPresent();
            $this->assertCorrectFailedRecords ($csvFile);
        }
    }

        
    /**
     * Verify that preexisting records are correctly updated without clobbering
     * unmapped fields
     */
    public function testUpdateExistingImports() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        $firstUpdateCsv = 'contacts-update.csv';
        $secondUpdateCsv = 'contacts-update2.csv';

        // First ensure updates can be performed when matching on id (default)
        $this->prepareImport ('Contacts', $firstUpdateCsv);
        $this->click ('update-records-box');
        $this->beginImport();

        // Assert that each model had only the requested fields updated
        $this->assertContactUpdated ('one', array(
            'leadscore' => 0,
            'doNotCall' => 0,
            'doNotEmail' => 1,
        ));
        $this->assertContactUpdated ('two', array(
            'leadscore' => 4,
            'doNotCall' => 1,
            'doNotEmail' => 0,
        ));
        $this->assertContactUpdated ('three', array(
            'leadscore' => 2,
            'doNotCall' => 0,
            'doNotEmail' => 1,
        ));

        // Now ensure updates can be performed when matching another attribute
        $this->prepareImport ('Contacts', $secondUpdateCsv);
        $this->click ('update-records-box');
        $this->select ('update-field', 'value=email');
        $this->beginImport();

        // Assert that each model had only the requested fields updated
        $this->assertContactUpdated ('one', array(
            'leadscore' => 3,
            'doNotCall' => 1,
            'doNotEmail' => 0,
        ));
        $this->assertContactUpdated ('two', array(
            'leadscore' => 4,
            'doNotCall' => 0,
            'doNotEmail' => 1,
        ));
        $this->assertContactUpdated ('three', array(
            'leadscore' => 2,
            'doNotCall' => 1,
            'doNotEmail' => 1,
        ));
    }
      

    /**
     * Verify that the importer can handle line endings from various operating systems.
     * The specified CSV has lines ending in: \r\n, \n, \r, and \r\n, respectively.
     */
    public function testImportLineEndings() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        $csvFile = 'lineendings-contacts.csv';
        // Skip verification of uploaded CSV since it will be modified
        $this->prepareImport ('Contacts', $csvFile, false);
        $expected = $this->stashModels ('Contacts');
        $this->assertGreaterThan (0, count($expected),
            'Failed to load fixture! Models were expected');
        $this->beginImport();
        $this->assertNoValidationErrorsPresent();
        $this->assertModelsWereImported ($expected, 'Contacts');
    }

    /**
     * Ensure that visibility is handled properly on import, regardless of whether the
     * field contains digits or strings
     */
    public function testImportVisibility() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        $csvFile = 'contacts-visibility.csv';
        $this->prepareImport ('Contacts', $csvFile);
        $expected = $this->stashModels ('Contacts');
        $this->assertGreaterThan (0, count($expected),
            'Failed to load fixture! Models were expected');
        $this->beginImport();
        $this->assertNoValidationErrorsPresent();
        $this->assertModelsWereImported ($expected, 'Contacts');
    }

    /**
     * Ensure that hidden records are included/excluded when requested
     */
    public function testExportHiddenRecords() {
        $exportCsv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'records_export.csv'
        ));

        // Without hidden records, should export 3 models
        $three = $this->contacts ('three');
        $three->markAsDuplicate ();
        $this->prepareExport ('contacts');
        $this->beginExport ();
        $this->assertEquals (3, count(file($exportCsv)));

        // With hidden records, should export 4 models instead
        $this->prepareExport ('contacts');
        $this->beginExport (array('includeHidden'));
        $this->assertEquals (4, count(file($exportCsv)));
    }

    /**
     * Ensure that tags are handled accordinly on import
     */
    public function testImportTags() {
        if($this->isChrome()){
            $this->markTestSkipped('Import tests do not function in Chrome.');
        }
        $csvFile = 'tags-contacts.csv';
        $this->prepareImport ('Contacts', $csvFile);
        $expected = $this->stashModels ('Contacts');
        $this->assertGreaterThan (0, count($expected),
            'Failed to load fixture! Models were expected');
        $this->beginImport();
        $this->assertNoValidationErrorsPresent();
        $this->assertModelsWereImported ($expected, 'Contacts');
        $this->assertModelsHaveTags ('Contacts', array(
            '1' => array('#one', '#testing'),
            '2' => array('#two'),
            '3' => array('#three'),
        ));
    }

    /**
     * Verify that models can be exported properly
     */
    public function testRecordsExport() {
        // TODO get actions tested
        $csvs = array_diff($this->csvs, array('actions'));
        foreach ($csvs as $modelName) {
            $this->prepareExport ($modelName);
            $this->beginExport (array('includeHidden'));
            $this->assertCsvExported ($modelName);
        }
    }

    /**
     * Ensure that tags are handled accordinly on export
     */
    public function testExportTags() {
        $this->prepareExport ('contacts');
        $this->beginExport (array('includeTags', 'includeHidden'));
        $this->assertCsvExported ('contacts');
        $this->assertExportCsvTags (array(
            '1' => array('#one', '#testing'),
            '2' => array('#two'),
            '3' => array('#three'),
        ));
    }

    /********************************************************************
     * Assert methods
     ********************************************************************/
    /**
     * Assert that only the specified attributes of a Contact were updated
     * accordingly
     * @param string $fixtureName Identifier of individual fixture record to verify
     * @param array $updatedAttributes array of updated attribute values, indexed
     *   by attribute name.
     */
    protected function assertContactUpdated ($fixtureName, $updatedAttributes) {
        $contact = $this->contacts ($fixtureName);
        $attributes = $contact->attributes;
        $dbContact = Contacts::model()->findByPk ($contact->id);

        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $this->ignoreImportFields))
                continue;
            if (in_array($attribute, array_keys($updatedAttributes))) {
                // Attribute was updated: verify the value is as intended
                $this->assertEquals ($updatedAttributes[$attribute], $dbContact->$attribute);
            } else {
                // Attribute was not updated: verify the value hasn't been changed
                $this->assertEquals ($value, $dbContact->$attribute);
            }
        }
    }

    protected function assertConvertedLegacyModule($moduleName) {
        $path = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $moduleName
        ));
        $defaultController = implode(DIRECTORY_SEPARATOR, array(
            $path,
            'controllers',
            'DefaultController.php'
        ));
        $controllerClass = ucfirst($moduleName)."Controller";
        $controllerFile = implode(DIRECTORY_SEPARATOR, array(
            $path,
            'controllers',
            $controllerClass.".php"
        ));
        $this->assertTrue(is_dir($path));

        // Ensure the controller and views directory were converted from pre-3.5.1 format
        $newViewDir = implode(DIRECTORY_SEPARATOR, array($path, 'views', $moduleName));
        $this->assertTrue( is_dir($newViewDir) );
        $this->assertFileNotExists( $defaultController );
        $this->assertFileExists( $controllerFile );
        $file = Yii::app()->file->set($controllerFile);
        $contents = $file->getContents();
        $classnameRegex = "/class ".$controllerClass."/";
        $this->assertTrue(preg_match ($classnameRegex, $contents) === 1);

        // Assert that the views have been updated to retrieve item name from the db
        $viewPath = implode(DIRECTORY_SEPARATOR, array($path, "views", $moduleName));
        $viewFiles = scandir ($viewPath);
        foreach ($viewFiles as $viewFile) {
            // Skip partials and anything non-php
            if (!preg_match('/^[^_].*\.php$/', $viewFile))
                continue;
            $file = Yii::app()->file->set($viewPath.DIRECTORY_SEPARATOR.$viewFile);
            $contents = $file->getContents();
            if ($viewFile === 'index.php') {
                $searchPattern = "/'title'=>Modules::displayName\(true, \\\$moduleConfig\['moduleName'\]\)/m";
                $this->assertTrue(preg_match ($searchPattern, $contents) === 1);
            }
            $this->assertTrue(preg_match ("/Modules::itemDisplayName()/", $contents) === 1);
            $this->assertTrue(preg_match ("/\\\$moduleConfig\['recordName'\]/", $contents) === 0);
        }
    }

    /**
     * Compare two module structures recursively
     * @param string $expected Path to expected module layout
     * @param string $actual Path to actual module layout
     */
    protected function assertModulesEqual($expected, $actual) {
        $moduleName = explode(DIRECTORY_SEPARATOR, $expected);
        $moduleName = end($moduleName);
        $viewsDir = 'views'.DIRECTORY_SEPARATOR.$moduleName;

        foreach (array('controllers', 'models', $viewsDir) as $dir) {
            $expectedFiles = scandir ($expected.DIRECTORY_SEPARATOR.$dir);
            foreach ($expectedFiles as $file) {
                if (in_array($file, array('.', '..')))
                    continue;
                $expectedFile = implode(DIRECTORY_SEPARATOR, array($expected, $dir, $file));
                $actualFile = implode(DIRECTORY_SEPARATOR, array($actual, $dir, $file));
                $this->assertFileEquals ($expectedFile, $actualFile);
            }
        }
    }

    /**
     * Assert that the CSV exists and the file contents are equal
     * @param string $csv Path to the uploaded csv
     */
    protected function assertCsvUploaded ($csv) {
        $uploadedPath = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'data.csv'
        ));
        $this->assertFileExists ($uploadedPath);
        $this->assertFileEquals ($csv, $uploadedPath);
    }

    /**
     * Assert that the CSV exists and the file contents are equal
     * @param string $csv Path to the uploaded csv
     */
    protected function assertCsvExported ($csv) {
        $exportPath = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'records_export.csv'
        ));
        $csvFile = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'csvs',
            $csv.".csv"
        ));
        $this->assertFileExists ($exportPath);
        $expectedLength = count(file($csvFile));
        $exportedLength = count(file($exportPath));
        $this->assertEquals ($expectedLength, $exportedLength);
        // TODO achieve consistency in fields
        //$this->assertFileEquals ($csvFile, $exportPath);
    }

    /**
     * Assert that all models in the CSV were properly imported
     * @param array $expected Array of expected model attributes, indexed by model ID.
     *  This attribute expects a parameter like that returned by {@link stashModels}
     * @param string $model Name of the model that was imported
     */
    protected function assertModelsWereImported($expected, $modelName) {
        $models = X2Model::model ($modelName)->findAll();
        foreach ($models as $model) {
            $attributes = $model->attributes;
            $this->assertArrayHasKey ($model->id, $expected);
            $expectedAttributes = $expected[$model->id];
            $this->removeIgnoredFields ($expectedAttributes);
            $this->removeIgnoredFields ($attributes);
            $this->assertEquals ($expectedAttributes, $attributes);
        }
    }

    /**
     * Assert that relationships were created properly, and that nameIds used for
     * link type fields are correctly set
     * @param array $models Array of associative arrays, each with a model id and type
     */
    protected function assertImportRelation($models) {
        $this->assertTrue (is_array($models));
        $this->assertTrue (count($models) === 2);
        $whereClause = 'firstType = :firstType AND firstId = :firstId AND '.
                       'secondType = :secondType AND secondId = :secondId';
        $relationModelId = Yii::app()->db->createCommand()
            ->select ('id')
            ->from ('x2_relationships')
            ->where ($whereClause, array(
                ':firstType' => $models[0]['type'],
                ':firstId' => $models[0]['id'],
                ':secondType' => $models[1]['type'],
                ':secondId' => $models[1]['id'],
            ))->orWhere ($whereClause, array(
                ':firstType' => $models[1]['type'],
                ':firstId' => $models[1]['id'],
                ':secondType' => $models[0]['type'],
                ':secondId' => $models[0]['id'],
            ))->queryScalar();
        $this->assertTrue (!is_null($relationModelId),
            'Failed to locate a relationship between '.$models[0]['type'].' '.$models[0]['id'].
            ' and '.$models[1]['type'].' '.$models[1]['id']);
    }

    /**
     * Assert that the models in question have the proper tags
     * @param array $modelName Class name of the model
     * @param array $tagSpec Array of tag arrays, indexed by model id to verify
     */
    protected function assertModelsHaveTags($modelName, $tagSpec) {
        foreach ($tagSpec as $modelId => $tags) {
            $model = X2Model::model ($modelName)->findByPk ($modelId);
            $this->assertNotNull ($model);
            $this->assertTrue ($model->hasTags ($tags, true),
                "Failed to assert $modelName $modelId has tags ".implode(', ', $tags));
        }
    }

    /**
     * Assert that the exported CSV contains the proper tags for each specified model
     * @param array $tagSpec Array of tag arrays, indexed by model id to verify
     */
    protected function assertExportCsvTags($tagSpec) {
        $csvData = file_get_contents (implode (DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'records_export.csv'
        )));
        foreach ($tagSpec as $modelId => $tags) {
            $tagPattern = implode(',', $tags);
            if (count($tags) > 1)
                $tagPattern = '"'.$tagPattern.'"';
            $pattern = '/^'.$modelId.',.*,'. preg_quote($tagPattern) .'$/m';
            $this->assertEquals (1, preg_match ($pattern, $csvData));
        }
    }

    /**
     * Assert that an Action's association* fields are properly set
     * @param Actions $action
     * @param X2Model $association
     */
    protected function assertActionAssociation(Actions $action, X2Model $association) {
        $associationNameId = Fields::nameId ($association->name, $association->id);
        $this->assertEquals (lcfirst(get_class($association)), $action->associationType);
        $this->assertEquals ($association->id, $action->associationId);
        $this->assertEquals ($associationNameId, $action->associationName);
    }

    /**
     * Assert that the failedRecords.csv file generated on validation failures
     * is as expected
     */
    protected function assertCorrectFailedRecords($csv) {
        $generatedCsv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'failedRecords.csv'
        ));
        $expectedCsv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'csvs',
            str_replace('.csv', '-expected.csv', $csv)
        ));
        $this->assertFileExists ($generatedCsv);
        $this->assertFileEquals ($expectedCsv, $generatedCsv);
    }

    /**
     * Assert that no validation errors were present after import
     */
    protected function assertNoValidationErrorsPresent() {
        $this->assertTextNotPresent ($this->getFailedValidationText());
    }

    /**
     * Assert that validation errors were present after import
     */
    protected function assertValidationErrorsPresent() {
        $this->assertTextPresent ($this->getFailedValidationText());
    }
}
