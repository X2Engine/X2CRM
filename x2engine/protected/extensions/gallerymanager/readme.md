Gallery Manager usage instructions
===========================

Manual
------

1. Checkout source code to your project, for example to ext.galleryManager.
2. Install and configure image component(https://bitbucket.org/z_bodya/yii-image).
3. Import gallery models to project, by adding "ext.galleryManager.models.*" to import in config/main.php
4. Add GalleryController to application or module controllerMap.
5. Configure and save gallery model

        Example:
        $gallery = new Gallery();
        $gallery->name = true;
        $gallery->description = true;
        $gallery->versions = array(
            'small' => array(
                'resize' => array(200, null),
            ),
            'medium' => array(
                'resize' => array(800, null),
            )
        );
        $gallery->save();

6. Render widget for gallery created above:

        $this->widget('GalleryManager', array(
            'gallery' => $gallery,
            'controllerRoute' => '/admin/gallery', //route to gallery controller
        ));

Using GalleryBehavior
----------------------
Using gallery behavior is possible to add gallery to any model in application.

To use GalleryBehavior:

1. Add it to your model:

        Example:
        public function behaviors()
        {
            return array(
                'galleryBehavior' => array(
                    'class' => 'GalleryBehavior',
                    'idAttribute' => 'gallery_id',
                    'versions' => array(
                        'small' => array(
                            'centeredpreview' => array(98, 98),
                        ),
                        'medium' => array(
                            'resize' => array(800, null),
                        )
                    ),
                    'name' => true,
                    'description' => true,
                )
            );
        }

2. Add gallery widget to your view:

        Example:
        <h2>Product galley</h2>
        <?php
        if ($model->galleryBehavior->getGallery() === null) {
            echo '<p>Before add photos to product gallery, you need to save product</p>';
        } else {
            $this->widget('GalleryManager', array(
                'gallery' => $model->galleryBehavior->getGallery(),
            ));
        }
        ?>