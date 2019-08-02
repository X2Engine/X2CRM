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




class MigrateMediaFiles extends X2TestCase {

    protected static $skipAllTests = true;
    
    public $mediaSrc;
    public $mediaDest;
    public $migrationDest;
    public $testMediaCount;
    public $mediaCount;
    public $migrationCount;
    public $exclude = array(
        '.',
        '..',
    );

    public function setUp() {
        $baseDir = Yii::app()->basePath;
        $src = implode(DIRECTORY_SEPARATOR,
                array(
            $baseDir,
            'tests',
            'data',
            'mediaMigration',
        ));
        $this->mediaSrc = $src;
        $dest = implode(DIRECTORY_SEPARATOR,
                array(
            $baseDir,
            '..',
            'uploads',
        ));
        $this->mediaDest = $dest;
        $this->migrationDest = $dest . DIRECTORY_SEPARATOR . 'protected';

        $this->testMediaCount = count(array_diff(scandir($this->mediaSrc),
                        $this->exclude));
        $this->mediaCount = count(array_diff(scandir($this->mediaDest),
                        $this->exclude));
        $this->migrationCount = count(array_diff(scandir($this->migrationDest),
                        $this->exclude));

        $this->assertTrue(FileUtil::ccopy($src, $dest));
    }

    public function testMigration() {
        //Assert that all files have been copied over
        $this->assertEquals($this->mediaCount + $this->testMediaCount,
                count(array_diff(scandir($this->mediaDest), $this->exclude)));
        $this->assertFileExists($this->mediaDest . DIRECTORY_SEPARATOR . 'testing');

        $this->runMigrationScript();

        //Assert that the only files remaining are protected and .htaccess
        $this->assertEquals(2,
                count(array_diff(scandir($this->mediaDest), $this->exclude)));
        //Assert new files have been copied (-1 for testing)
        $this->assertEquals($this->testMediaCount + $this->migrationCount - 1,
                count(array_diff(scandir($this->migrationDest), $this->exclude)));
        //Assert testing dir has been deleted
        $this->assertFileNotExists($this->mediaDest . DIRECTORY_SEPARATOR . 'testing');
        $this->assertFileNotExists($this->migrationDest . DIRECTORY_SEPARATOR . 'testing');
    }

    public function runMigrationScript() {
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
                'migrations/5.4/1447369865-migrate-old-media.php';
        $return_var;
        $output = array();
        if (X2_TEST_DEBUG_LEVEL > 1) {
            print_r(exec($command, $return_var, $output));
        } else {
            exec($command, $return_var, $output);
        }
        X2_TEST_DEBUG_LEVEL > 1 && print_r($return_var);
        X2_TEST_DEBUG_LEVEL > 1 && print_r($output);
    }

    public function tearDown() {
        $files = array_diff(scandir($this->mediaSrc), $this->exclude);
        foreach ($files as $file) {
            FileUtil::rrmdir($this->migrationDest . DIRECTORY_SEPARATOR . $file);
        }
        $this->assertEquals($this->migrationCount,
                count(array_diff(scandir($this->migrationDest), $this->exclude)));
    }

}
