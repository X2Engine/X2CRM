<?php

/**
 * This is the model class for table "gallery_photo".
 *
 * The followings are the available columns in table 'gallery_photo':
 * @property integer $id
 * @property integer $gallery_id
 * @property integer $rank
 * @property string $name
 * @property string $description
 * @property string $file_name
 *
 * The followings are the available model relations:
 * @property Gallery $gallery
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class GalleryPhoto extends CActiveRecord
{
    /** @var string Extensions for gallery images */
    public $galleryExt = 'jpg';
    /** @var string directory in web root for galleries */
    public $galleryDir = 'uploads/gallery';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return GalleryPhoto the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
            return 'x2_gallery_photo';

    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('gallery_id', 'required'),
//            array('gallery_id, rank', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 512),
            array('file_name', 'length', 'max' => 128),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, gallery_id, rank, name, description, file_name', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'gallery' => array(self::BELONGS_TO, 'Gallery', 'gallery_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'gallery_id' => 'Gallery',
            'rank' => 'Rank',
            'name' => 'Name',
            'description' => 'Description',
            'file_name' => 'File Name',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('gallery_id', $this->gallery_id);
        $criteria->compare('rank', $this->rank);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('file_name', $this->file_name, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function save($runValidation = true, $attributes = null)
    {

        parent::save($runValidation, $attributes);
        if ($this->rank == null) {
            $this->rank = $this->id;
            $this->setIsNewRecord(false);
            $this->save(false);
        }
        return true;
    }

    public function getPreview()
    {
        return Yii::app()->request->baseUrl . '/' . $this->galleryDir . '/_' . $this->getFileName('') . '.' . $this->galleryExt;
    }

    private function getFileName($version = '')
    {
        return $this->id . $version;
    }

    public function getUrl($version = '')
    {
        return Yii::app()->request->baseUrl . '/' . $this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt;
    }

    public function setImage($path)
    {
        //save image in original size
        Yii::app()->image->load($path)->save($this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt);
        //create image preview for gallery manager
        Yii::app()->image->load($path)->resize(300, null)->save($this->galleryDir . '/_' . $this->getFileName('') . '.' . $this->galleryExt);

        foreach ($this->gallery->versions as $version => $actions) {
            $image = Yii::app()->image->load($path);
            foreach ($actions as $method => $args) {
                call_user_func_array(array($image, $method), is_array($args) ? $args : array($args));
            }
            $image->save($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
    }

    public function delete()
    {
        $this->removeFile($this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt);
        //create image preview for gallery manager
        $this->removeFile($this->galleryDir . '/_' . $this->getFileName('') . '.' . $this->galleryExt);

        foreach ($this->gallery->versions as $version => $actions) {
            $this->removeFile($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
        return parent::delete();
    }

    private function removeFile($fileName)
    {
        if (file_exists($fileName))
            @unlink($fileName);
    }

    public function removeImages()
    {
        foreach ($this->gallery->versions as $version => $actions) {
            $this->removeFile($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
    }

    /**
     * Regenerate image versions
     */
    public function updateImages()
    {
        foreach ($this->gallery->versions as $version => $actions) {
            $this->removeFile($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);

            $image = Yii::app()->image->load($this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt);
            foreach ($actions as $method => $args) {
                call_user_func_array(array($image, $method), is_array($args) ? $args : array($args));
            }
            $image->save($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
    }


}