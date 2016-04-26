<?php

/**
 * Widget to manage gallery.
 * Requires Twitter Bootstrap styles to work.
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class GalleryManager extends CWidget {

    /** @var Gallery Model of gallery to manage */
    public $gallery;

    /** @var string Route to gallery controller */
    public $controllerRoute = false;
    public $assets;

    public function init(){
        $this->assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__).'/assets');
    }

    public $htmlOptions = array();

    /** Render widget */
    public function run(){
        /** @var $cs CClientScript */
        $cs = Yii::app()->clientScript;
        $cs->registerCssFile($this->assets.'/galleryManager.css');

        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('jquery.ui');

        //if (YII_DEBUG) {
        $cs->registerScriptFile($this->assets.'/jquery.iframe-transport.js');
        $cs->registerScriptFile($this->assets.'/jquery.galleryManager.js');
        //} else {
        //$cs->registerScriptFile($this->assets . '/jquery.iframe-transport.min.js');
        //$cs->registerScriptFile($this->assets . '/jquery.galleryManager.min.js');
        //}
        $cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/gallerymanager/bootstrap/js/bootstrap.js');
        /* x2modstart */ 
        //$cs->registerCssFile(Yii::app()->request->baseUrl.'/js/gallerymanager/bootstrap/css/bootstrap.css');
        /* x2modend */ 
        if($this->controllerRoute === null)
            throw new CException('$controllerRoute must be set.', 500);

        $photos = array();
        foreach($this->gallery->galleryPhotos as $photo){
            $photos[] = array(
                'id' => $photo->id,
                'rank' => $photo->rank,
                'name' => (string) $photo->name,
                'description' => (string) $photo->description,
                'preview' => $photo->getPreview(),
            );
        }

        $opts = array(
            'hasName' => $this->gallery->name ? true : false,
            'hasDesc' => $this->gallery->description ? true : false,
            'uploadUrl' => Yii::app()->createUrl($this->controllerRoute.'/gallery/ajaxUpload', array('gallery_id' => $this->gallery->id)),
            'deleteUrl' => Yii::app()->createUrl($this->controllerRoute.'/gallery/delete'),
            'updateUrl' => Yii::app()->createUrl($this->controllerRoute.'/gallery/changeData'),
            'arrangeUrl' => Yii::app()->createUrl($this->controllerRoute.'/gallery/order'),
            'nameLabel' => Yii::t('galleryManager.main', 'Name'),
            'descriptionLabel' => Yii::t('galleryManager.main', 'Description'),
            'editDialogTitle' => Yii::t('galleryManager.main', 'Edit Information'),
            'editDialogSaveButtonLabel' => Yii::t('galleryManager.main', 'Save Changes'),
            'editDialogCloseButtonLabel' => Yii::t('galleryManager.main', 'Close'),
            'viewDialogTitle' => Yii::t('galleryManager.main', 'View Image'),
            'viewDialogCloseButtonLabel' => Yii::t('galleryManager.main', 'Close'),
            'photos' => $photos,
        );

        if(Yii::app()->request->enableCsrfValidation){
            $opts['csrfTokenName'] = Yii::app()->request->csrfTokenName;
            $opts['csrfToken'] = Yii::app()->request->csrfToken;
        }
        $opts = CJavaScript::encode($opts);
        $cs->registerScript('galleryManager#'.$this->id, "$('#{$this->id}').galleryManager({$opts});");

        $this->htmlOptions['id'] = $this->id;
        $this->htmlOptions['class'] = 'GalleryEditor';

        $this->render('galleryManager');
    }

}
