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




Yii::import ('application.components.behaviors.ImportExportBehavior');

class ImportExportBehaviorTest extends X2TestCase {
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
