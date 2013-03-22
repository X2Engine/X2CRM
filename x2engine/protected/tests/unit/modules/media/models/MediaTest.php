<?php

Yii::import('application.components.FileUtil');
Yii::import('application.modules.media.models.Media');
class MediaTest extends X2DbTestCase {
	
	public $fixtures = array(
		'media' => 'Media'
	);
	
	public function getRootPath() {
		return realpath(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..');
	}
	
	public function testFilesystem() {
		$image = $this->media('bg');
		$this->assertNotEquals(null,$image->path);
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
		$this->assertEquals(280869,$image->resolveSize());
		$this->assertEquals(280869,Yii::app()->db->createCommand()->select('filesize')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar());
	}
	
	public function testResolveDimensions() {
		$this->assertEquals(1,extension_loaded('gd'));
		$image = $this->media('bg');
		$this->assertEquals(array('height'=>1200,'width'=>1500),CJSON::decode($image->resolveDimensions()));
		$this->assertEquals(array('height'=>1200,'width'=>1500),CJSON::decode(Yii::app()->db->createCommand()->select('dimensions')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar()));
	}
}

?>
