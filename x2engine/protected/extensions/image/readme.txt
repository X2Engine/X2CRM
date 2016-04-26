This is a modified version of the Image Yii extension.

There's 3 new functions :
 - grayscale
 - emboss
 - negate

The ImageMagick Driver is optimized greatly by appending arguments for convert instead
of reading / saving file for each function. The temp image copy in this driver is now useless
and was removed too.

Author : Parcouss

First release (this is the following) :

说明：
移植自Kohana的Image类库

英文文档地址：http://docs.kohanaphp.com/libraries/image
中文文档地址：http://khnfans.cn/docs/libraries/image

------------------------------------------------------------------------------

安装：
将image文件夹放入application的extensions文件中即可

------------------------------------------------------------------------------

使用方法：

第一种：
配置：
在application的main config的components中添加以下配置
'image'=>array(
            'class'=>'application.extensions.image.CImageComponent',
            // GD or ImageMagick
            'driver'=>'GD',
            // ImageMagick setup path
            'params'=>array('directory'=>'D:/Program Files/ImageMagick-6.4.8-Q16'),
        ),

调用方法()：
$image = Yii::app()->image->load('images/test.jpg');
$image->resize(400, 100)->rotate(-45)->quality(75)->sharpen(20);
$image->save(); // or $image->save('images/small.jpg');

第二种：
Yii::import('application.extensions.image.Image');
$image = new Image('images/test.jpg');
$image->resize(400, 100)->rotate(-45)->quality(75)->sharpen(20);
$image->render();