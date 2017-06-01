<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

Yii::import ('application.components.behaviors.ImportExportBehavior');

class ImportExportBehaviorTest extends X2TestCase {
    public function testSendFile() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $this->assertFalse($component->sendFile('/etc/shadow'));
        $this->assertFalse($component->sendFile('../../../../../../etc/hosts'));
        $this->assertFalse($component->sendFile('nonexistent'));
    }

    public function testSafePath() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $path = $component->safePath();
        $expected = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath, 'data', 'data.csv'
        ));
        $this->assertEquals($expected, $path);

        $path = $component->safePath('update_backup.sql');
        $expected = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath, 'data', 'update_backup.sql'
        ));
        $this->assertEquals($expected, $path);

        $path = $component->safePath('../config/config.php');
        $expected = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath, 'data', '..', 'config', 'config.php'
        ));
        $this->assertEquals($expected, $path);
    }

    public function testGetImportDelimeter() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $_SESSION = array();
        $this->assertEquals(',', $component->getImportDelimeter());
        $_SESSION['importDelimeter'] = '|';
        $this->assertEquals('|', $component->getImportDelimeter());
    }

    public function testGetImportEnclosure() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $_SESSION = array();
        $this->assertEquals('"', $component->getImportEnclosure());
        $_SESSION['importEnclosure'] = '|';
        $this->assertEquals('|', $component->getImportEnclosure());
    }

    public function testGetNextImportId() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);
        $fn = TestingAuxLib::setPublic('ImportExportBehavior', 'getNextImportId');

        $import = new Imports;
        $import->importId = 22;
        $import->modelId = 12345;
        $import->modelType = 'Contacts';
        $import->save();
        $this->assertEquals(23, $fn());
        $import->delete();

        // TODO fails when full suite is executed
        //$this->assertEquals(1, $fn());
    }

    public function testAvailableImportMaps() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        foreach($component->availableImportMaps() as $file => $map) {
            $this->assertEquals(1, preg_match('/^[\w-]+\.json$/', $file, $matches));
            $fileName = $component->safePath('importMaps/'.$file);
            $this->assertTrue(is_file($fileName));
        }
    }

    public function testNormalizeImportMap() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $expected = array(
            'fieldA' => null,
            'fieldB' => 'B',
            'fieldC' => null,
        );
        $map = array(
            'fieldB' => 'B',
        );
        $fields = array('fieldA', 'fieldB', 'fieldC');
        $this->assertEquals($expected, $component->normalizeImportMap($map, $fields));
    }

    public function testCalculateCsvLength() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $csvFile = $component->safePath('test.csv');
        $csvData = "one,two,three\n";
        $csvData .= "1,2,3\n";
        $csvData .= "4,5,6\n";
        $csvData .= "7,8,9\n";
        file_put_contents($csvFile, $csvData);
        $this->assertEquals(3, $component->calculateCsvLength($csvFile));
    }

    public function testFixCsvLineEndings() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $csvFile = $component->safePath('test.csv');
        $csvData = "one,two,three\r";
        $csvData .= "1,2,3\r";
        $csvData .= "4,5,6\r";
        file_put_contents($csvFile, $csvData);

        $fixedCsvData = "one,two,three\r\n";
        $fixedCsvData .= "1,2,3\r\n";
        $fixedCsvData .= "4,5,6\r\n";
        $component->fixCsvLineEndings($csvFile);
        $output = file_get_contents($csvFile);
        $this->assertEquals($fixedCsvData, $output);
    }

    public function testReadExportFormatOptions() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        $defaultParams = array(
            'exportDestination' => 'download',
            'compressOutput' => false,
        );
        $this->assertEquals($defaultParams, $component->readExportFormatOptions(array()));

        $expected = array(
            'exportDestination' => 'server',
            'compressOutput' => true,
            'server-path' => '/tmp',
        );
        $this->assertEquals($expected, $component->readExportFormatOptions(array(
            'compressOutput' => 'true',
            'exportDestination' => 'server',
            'server-path' => '/tmp',
        )));
    }

    public function testFixupImportedContactName () {
        $admin = Yii::app()->settings;
        $fixupImportedContactName = TestingAuxLib::setPublic (
            'ImportExportBehavior', 'fixupImportedContactName', true);

        // Using Explicit 'First Last' format
        $admin->contactNameFormat = 'firstName lastName';
        $this->assertUpdates ($admin, array ('contactNameFormat'));

        $contact = new Contacts;
        $contact->name = 'FirstName LastName';
        $fixupImportedContactName ($contact);
        $this->assertEquals ('FirstName', $contact->firstName);
        $this->assertEquals ('LastName', $contact->lastName);

        $contact = new Contacts;
        $contact->firstName = 'FirstName';
        $contact->lastName = 'LastName';
        $fixupImportedContactName ($contact);
        $this->assertEquals ('FirstName LastName', $contact->name);

        // Using 'Last, First' format
        $admin->contactNameFormat = 'lastName, firstName';
        $this->assertUpdates ($admin, array ('contactNameFormat'));

        $contact = new Contacts;
        $contact->name = 'LastName, FirstName';
        $fixupImportedContactName ($contact);
        $this->assertEquals ('FirstName', $contact->firstName);
        $this->assertEquals ('LastName', $contact->lastName);

        $contact = new Contacts;
        $contact->firstName = 'FirstName';
        $contact->lastName = 'LastName';
        $fixupImportedContactName ($contact);
        $this->assertEquals ('LastName, FirstName', $contact->name);

        // Reset format to default empty value
        $admin->contactNameFormat = null;
        $this->assertUpdates ($admin, array ('contactNameFormat'));
    }

    public function testAdjustExportPath() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);

        // Test csv filename without compression enabled
        $expected = 'records_export.csv';
        foreach (array('records_export.csv', 'records_export') as $path) {
            $this->assertEquals (
                $expected,
                $component->adjustExportPath ($path, array())
            );
        }

        // Test csv filename with compression enabled
        $expected = 'records_export.zip';
        foreach (array('records_export.csv', 'records_export', 'records_export.zip') as $path) {
            $this->assertEquals (
                $expected,
                $component->adjustExportPath ($path, array('compressOutput' => true))
            );
        }
    }

    /**
     * Verify correct operation of {@link prepareExportDeliverable}.
     * Currently, the exportDestinations with test cases include: download, server, ftp, scp
     * exportDestinations remaining to be tested: s3, gdrive
     */
    public function testPrepareExportDeliverable() {
        $component = new CComponent;
        $component->attachBehavior ('importexport', new ImportExportBehavior);
        $testfile = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath, 'tests', 'data', 'csvs', 'contacts.csv'
        ));

        // Ensure failure when the exportDestination is not specified
        $ret = $component->prepareExportDeliverable ($testfile, array());
        $this->assertFalse ($ret);

        // Test standard browser download method
        $params = array('exportDestination' => 'download');
        $ret = $component->prepareExportDeliverable ($testfile, $params);
        $this->assertTrue ($ret);
        $params['compressOutput'] = true;

        $ret = $component->prepareExportDeliverable ($testfile, $params);
        $this->assertTrue ($ret);
        $this->assertFileExists ($component->safePath('contacts.zip'));
        unlink ($component->safePath ('contacts.zip'));
        

        // Test save to server method
        $dst = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath, 'runtime', 'contacts.csv'
        ));
        $params = array('exportDestination' => 'server', 'server-path' => $dst);
        $ret = $component->prepareExportDeliverable ($testfile, $params);
        $this->assertTrue ($ret);
        $this->assertFileExists ($dst);
        unlink ($dst);

        $params['compressOutput'] = true;
        $dst = str_replace ('csv', 'zip', $dst);
        $ret = $component->prepareExportDeliverable ($testfile, $params);
        $this->assertTrue ($ret);
        $this->assertFileExists ($dst);
        unlink ($dst);

        // Test FTP method
        if (X2_FTP_FILEOPER) {
            $dst = implode(DIRECTORY_SEPARATOR, array(
                Yii::app()->basePath, 'runtime', 'contacts.csv'
            ));
            $params = array(
                'exportDestination' => 'ftp',
                'ftp-path' => $dst,
                'ftp-server' => X2_FTP_HOST,
                'ftp-user' => X2_FTP_USER,
                'ftp-pass' => X2_FTP_PASS,
            );
            $ret = $component->prepareExportDeliverable ($testfile, $params);
            $this->assertTrue ($ret);
            $this->assertFileExists ($dst);
            unlink ($dst);

            $params['compressOutput'] = true;
            $dst = str_replace ('csv', 'zip', $dst);
            $ret = $component->prepareExportDeliverable ($testfile, $params);
            $this->assertTrue ($ret);
            $this->assertFileExists ($dst);
            unlink ($dst);
        }

        // Test SCP method
        if (X2_SCP_FILEOPER) {
            $dst = implode(DIRECTORY_SEPARATOR, array(
                Yii::app()->basePath, 'runtime', 'contacts.csv'
            ));
            $params = array(
                'exportDestination' => 'scp',
                'scp-path' => $dst,
                'scp-server' => X2_SCP_HOST,
                'scp-user' => X2_SCP_USER,
                'scp-pass' => X2_SCP_PASS,
            );
            $ret = $component->prepareExportDeliverable ($testfile, $params);
            $this->assertTrue ($ret);
            $this->assertFileExists ($dst);
            unlink ($dst);

            $params['compressOutput'] = true;
            $dst = str_replace ('csv', 'zip', $dst);
            $ret = $component->prepareExportDeliverable ($testfile, $params);
            $this->assertTrue ($ret);
            $this->assertFileExists ($dst);
            unlink ($dst);
        }
        // TODO
        // Test Amazon S3 method
        // Test Google Drive method
        
    }
}
