<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

Yii::import('application.components.FileUtil');
Yii::import('application.modules.media.models.Media');

/**
 * Test case for the {@link Media} model class.
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package X2CRM.tests.unit.modules.media.models
 */
class MediaTest extends X2DbTestCase {
	
	public $fixtures = array(
		'media' => 'Media'
	);
	
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
