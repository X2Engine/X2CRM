<?php

/* x2modstart */
Yii::import('application.extensions.gallerymanager.models.*');
/* x2modend */

/**
 * Behavior for adding gallery to any model.
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class GalleryBehavior extends CActiveRecordBehavior {

    /** @var string Model attribute name to store created gallery id */
    public $idAttribute;

    /**
     * @var array Settings for image auto-generation
     * @example
     *  array(
     *       'small' => array(
     *              'resize' => array(200, null),
     *       ),
     *      'medium' => array(
     *              'resize' => array(800, null),
     *      )
     *  );
     */
    public $versions;

    /** @var boolean does images in gallery need names */
    public $name;

    /** @var boolean does images in gallery need descriptions */
    public $description;
    private $_gallery;

    /** Will create new gallery after save if no associated gallery exists */
//    public function beforeSave($event){
//        parent::beforeSave($event);
//        if($event->isValid){
//            if(empty($this->getOwner()->{$this->idAttribute})){
//                $gallery = new Gallery();
//                $gallery->name = $this->name;
//                $gallery->description = $this->description;
//                $gallery->versions = $this->versions;
//                $gallery->save();
//
//                $this->getOwner()->{$this->idAttribute} = $gallery->id;
//            }
//        }
//    }

    /** Will remove associated Gallery before object removal */
    public function beforeDelete($event){
        if(!empty($this->getOwner()->gallery)){
            /** @var $gallery Gallery */
            //$galleryLink = GalleryToModel::model()->findByAttributes(array('galleryId'=>$this->getOwner()->{$this->idAttribute}));
            //$galleryLink->delete(); // Unnecessary after improving foreign key definition
            $this->getOwner()->gallery->delete();
        }
        parent::beforeDelete($event);
    }

    /** Method for changing gallery configuration and regeneration of images versions */
    public function changeConfig(){
        /** @var $gallery Gallery */
        $gallery = Gallery::model()->findByPk($this->getOwner()->{$this->idAttribute});
        if($gallery == null)
            return;
        foreach($gallery->galleryPhotos as $photo){
            $photo->removeImages();
        }

        $gallery->name = $this->name;
        $gallery->description = $this->description;
        $gallery->versions = $this->versions;
        $gallery->save();

        foreach($gallery->galleryPhotos as $photo){
            $photo->updateImages();
        }

        $this->getOwner()->{$this->idAttribute} = $gallery->id;
        $this->getOwner()->saveAttributes($this->getOwner()->getAttributes());
    }

    /** @return Gallery Returns gallery associated with model */
    public function getGallery(){
        if(empty($this->_gallery)){
            $this->_gallery = Gallery::model()->findByPk($this->getOwner()->{$this->idAttribute});
        }
        return $this->_gallery;
    }

    /** @return GalleryPhoto[] Photos from associated gallery */
    public function getGalleryPhotos(){
        $criteria = new CDbCriteria();
        $criteria->condition = 'gallery_id = :gallery_id';
        $criteria->params[':gallery_id'] = $this->getOwner()->{$this->idAttribute};
        $criteria->order = '`rank` asc';
        return GalleryPhoto::model()->findAll($criteria);
    }

    public function getGalleryId(){
        if(isset($this->owner->gallery)){
            return $this->owner->gallery->id;
        }else{
            if(!empty($this->owner->id)){
                $gallery = new Gallery();
                $gallery->name = $this->name;
                $gallery->description = $this->description;
                $gallery->versions = $this->versions;
                $gallery->save();
                $this->getOwner()->{$this->idAttribute} = $gallery->id;
                return $gallery->id;
            }
        }
    }

    public function setGalleryId($id){
        if(!empty($this->owner->id) && !isset($this->owner->gallery)){
            $galleryLink = new GalleryToModel;
            $galleryLink->modelName = get_class($this->owner);
            $galleryLink->modelId = $this->owner->id;
            $galleryLink->galleryId = $id;
            $galleryLink->save();
        }
    }

}
