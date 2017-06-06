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

Yii::import('application.components.util.FileUtil');
Yii::import('application.modules.media.models.Media');

/**
 * Test case for the {@link Media} model class.
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package application.tests.unit.modules.media.models
 */
class MediaTest extends X2DbTestCase {
    
    public static function referenceFixtures(){
        return array(
            'media' => 'Media'
        );
    }
    
    public function getRootPath() {
        return realpath(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..');
    }
    
    public function testFilesystem() {
        $image = $this->media('bg');
        $this->assertNotEquals(null,$image->path,'Failed asserting valid path for media item "bg"');
        $this->assertFileExists($image->path);
    }
        
    public function testGetFilePath() {
        $image = $this->media("bg");
        $expected = implode(DIRECTORY_SEPARATOR, array('uploads', 'protected', $image->fileName));
        $this->assertEquals($expected, Media::getFilePath(null, $image->fileName));
    }
        
    public function testFileExists() {
        $image = $this->media("bg");
        $this->assertTrue($image->fileExists());
        $image = $this->media("testfile");
        $this->assertFalse($image->fileExists());
    }
    
    public function testGetImage() {
        TestingAuxLib::loadControllerMock ();
        $image = $this->media('bg');
        $this->assertTrue($image->fileExists());
        $this->assertTrue($image->isImage());
        $expected = '<img class="attachment-img" src="'.$image->getPublicUrl ().'" alt="" />';
        $imageTag = $image->getImage();
        $this->assertEquals($expected, $imageTag);
        TestingAuxLib::restoreController();
    }
        
    public function testGetPath() {
        $source = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'tests', 'data', 'media', 'testfile.txt'));
        $dest = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..', 'uploads', 'protected', 'media', 'admin', 'testfile.txt'));
        FileUtil::ccopy($source, $dest);
        $dest = realpath($dest);
        $testfile = $this->media("testfile");

        $this->assertEquals($dest, $testfile->getPath());

        unlink($dest);
    }

    public function testDeleteUpload() {
        $source = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'tests', 'data', 'media', 'testfile.txt'));
        $dest = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..', 'uploads', 'protected', 'media', 'admin', 'testfile.txt'));
        FileUtil::ccopy($source, $dest);
        $dest = realpath($dest);
        $testfile = $this->media("testfile");

        $this->assertFileExists($dest);
        $testfile->delete();
        $this->assertFileNotExists($dest);
    }
        
    public function testResolveMimetype() {
        $image = $this->media('bg');
        $image->refresh (); 
        $mt = $image->resolveType();
        $this->assertStringStartsWith('image/', $mt);
        $image->refresh (); 
        $mimetype = $image->mimetype;
        $this->assertStringStartsWith('image/',$mimetype);
    }  
    
    public function testResolveSize() {
        $image = $this->media('bg');
        $this->assertEquals(97724,$image->resolveSize());
        $this->assertEquals(97724,Yii::app()->db->createCommand()->select('filesize')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar());
    }

    public function testResolveDimensions() {
        $this->assertEquals(1,extension_loaded('gd'));
        $image = $this->media('bg');
        $this->assertEquals(array('height'=>682,'width'=>1024),CJSON::decode($image->resolveDimensions()));
        $this->assertEquals(
            array('height'=>682,'width'=>1024),
            CJSON::decode(
                Yii::app()->db->createCommand()
                    ->select('dimensions')
                    ->from('x2_media')
                    ->where("id=:id",array(':id'=>$image->id))
                    ->queryScalar()
                ));
    }
        
    public function testGetFmtDimensions() {
        $image = $this->media('bg');
        $this->assertEquals('1024 x 682', $image->getFmtDimensions());
    }

        public function testToBytes() {
            $fn = TestingAuxLib::setPublic('Media', 'toBytes');
            $testSizes = array(
                '3PB' => 3 * pow(1024, 5),
                '1g' => 1024 * 1024 * 1024,
                '2m' => 2 * 1024 * 1024,
                '1MB' => 1024 * 1024,
                '1k' => 1024,
                666 => 666,
            );
            foreach ($testSizes as $readable => $bytes) {
                $this->assertEquals($bytes, $fn ($readable));
            }
        }

    public function testResolveNameConflicts() {
        $media = $this->media('bg');
        $dupe = new Media;
        $dupe->fileName = $media->fileName;
        $dupe->resolveNameConflicts();
        $this->assertEquals('Divers(1).jpg', $dupe->fileName);
        $this->assertTrue($dupe->save());
        $dupe2 = new Media;
        $dupe2->fileName = $dupe->fileName;
        $dupe2->resolveNameConflicts();
        $this->assertEquals('Divers(2).jpg', $dupe2->fileName);
        $this->assertTrue($dupe->delete());
    }

    public function testIsAudio() {
        $audioFiles = array('audio/wav', 'audio/mp3', 'audio/flac');
        $notAudioFiles = array('application/exe', 'application/json', 'video/mpeg');
        $media = new Media;
        foreach ($audioFiles as $type) {
            $media->mimetype = $type;
            $this->assertTrue($media->isAudio());
        }
        foreach ($notAudioFiles as $type) {
            $media->mimetype = $type;
            $this->assertFalse($media->isAudio());
        }
    }

    public function testIsVideo() {
        $videoFiles = array('video/mpeg', 'video/wmv', 'video/mp4');
        $notVideoFiles = array('application/exe', 'application/json', 'audio/mp3');
        $media = new Media;
        foreach ($videoFiles as $type) {
            $media->mimetype = $type;
            $this->assertTrue($media->isVideo());
        }
        foreach ($notVideoFiles as $type) {
            $media->mimetype = $type;
            $this->assertFalse($media->isVideo());
        }
    }

    public function testIsImageExt() {
        $imageFiles = array('test.jpg', 'avatar.png', 'background.bmp');
        $notImageFiles = array('freeDownload.exe', 'package.json', 'recording.wav');
        foreach ($imageFiles as $file) {
            $this->assertTrue(Media::isImageExt($file));
        }
        foreach ($notImageFiles as $file) {
            $this->assertFalse(Media::isImageExt($file));
        }
    }

    public function testGetServerMaxUploadSize() {
        $uploadSize = rtrim(ini_get('upload_max_filesize'), 'M');
        $postSize = rtrim(ini_get('post_max_size'), 'M');
        if ($uploadSize < $postSize)
            $expected = $uploadSize;
        else
            $expected = $postSize;
        $maxUploadSize = Media::getServerMaxUploadSize();
        $this->assertEquals($expected, $maxUploadSize);
    }

    public function testForbiddenFileTypes() {
        $types = explode(', ', Media::forbiddenFileTypes());
        foreach (array('exe', 'bat', 'js', 'php', 'rb', 'py') as $type) {
            $this->assertTrue(in_array($type, $types));
        }
    }
}

?>
