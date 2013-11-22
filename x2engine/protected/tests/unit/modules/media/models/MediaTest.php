<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('application.components.util.FileUtil');
Yii::import('application.modules.media.models.Media');

/**
 * Test case for the {@link Media} model class.
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package X2CRM.tests.unit.modules.media.models
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
	
	public function testResolveMimetype() {
		$image = $this->media('bg');
		$mt = $image->resolveType();
		$this->assertStringStartsWith('image/', $mt);
		$this->assertStringStartsWith('image/',Yii::app()->db->createCommand()->select('mimetype')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar());
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
		$this->assertEquals(array('height'=>682,'width'=>1024),CJSON::decode(Yii::app()->db->createCommand()->select('dimensions')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar()));
	}
}

?>
