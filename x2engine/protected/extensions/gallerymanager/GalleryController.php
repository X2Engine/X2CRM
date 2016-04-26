<?php

/* x2modstart */
Yii::import('application.extensions.gallerymanager.models.*');
/* x2modend */

/**
 * Backend controller for GalleryManager widget.
 * Provides following features:
 *  - Image removal
 *  - Image upload/Multiple upload
 *  - Arrange images in gallery
 *  - Changing name/description associated with image
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class GalleryController extends CController {

    public function filters(){
        return array(
            'postOnly + delete, ajaxUpload, order, changeData',
        );
    }

    /**
     * Removes image with ids specified in post request.
     * On success returns 'OK'
     */
    public function actionDelete(){
        $id = $_POST['id'];
        /** @var $photos GalleryPhoto[] */
        $photos = GalleryPhoto::model()->findAllByPk($id);
        foreach($photos as $photo){
            if($photo !== null)
                $photo->delete();
            else
                throw new CHttpException(400, 'Photo, not found');
        }
        echo 'OK';
    }

    /**
     * Method to handle file upload thought XHR2
     * On success returns JSON object with image info.
     * @param $gallery_id string Gallery Id to upload images
     * @throws CHttpException
     */
    public function actionAjaxUpload($gallery_id = null){
        $model = new GalleryPhoto();
        $model->gallery_id = $gallery_id;
        $imageFile = CUploadedFile::getInstanceByName('image');
        if($imageFile->size > 0){
            $model->file_name = $imageFile->getName();
            $model->save();

            $model->setImage($imageFile->getTempName());
            header("Content-Type: application/json");
            echo CJSON::encode(
                    array(
                        'id' => $model->id,
                        'rank' => $model->rank,
                        'name' => (string) $model->name,
                        'description' => (string) $model->description,
                        'preview' => $model->getPreview(),
            ));
        }else{
            $response['message'] = "Invalid image file size.";
			$response['error'] = true;
			header("Content-type: application/json");
            echo CJSON::encode($response);
        }
    }

    /**
     * Saves images order according to request.
     * Variable $_POST['order'] - new arrange of image ids, to be saved
     * @throws CHttpException
     */
    public function actionOrder(){
        if(!isset($_POST['order']))
            throw new CHttpException(400, 'No data, to save');
        $gp = $_POST['order'];
        $orders = array();
        $i = 0;
        foreach($gp as $k => $v){
            if(!$v)
                $gp[$k] = $k;
            $orders[] = $gp[$k];
            $i++;
        }
        sort($orders);
        $i = 0;
        $res = array();
        foreach($gp as $k => $v){
            /** @var $p GalleryPhoto */
            $p = GalleryPhoto::model()->findByPk($k);
            $p->rank = $orders[$i];
            $res[$k] = $orders[$i];
            $p->save(false);
            $i++;
        }

        echo CJSON::encode($res);
    }

    /**
     * Method to update images name/description via AJAX.
     * On success returns JSON array od objects with new image info.
     * @throws CHttpException
     */
    public function actionChangeData(){
        if(!isset($_POST['photo']))
            throw new CHttpException(400, 'Nothing, to save');
        $data = $_POST['photo'];
        $criteria = new CDbCriteria();
        $criteria->index = 'id';
        $criteria->addInCondition('id', array_keys($data));
        /** @var $models GalleryPhoto[] */
        $models = GalleryPhoto::model()->findAll($criteria);
        foreach($data as $id => $attributes){
            if(isset($attributes['name']))
                $models[$id]->name = $attributes['name'];
            if(isset($attributes['description']))
                $models[$id]->description = $attributes['description'];
            $models[$id]->save();
        }
        $resp = array();
        foreach($models as $model){
            $resp[] = array(
                'id' => $model->id,
                'rank' => $model->rank,
                'name' => (string) $model->name,
                'description' => (string) $model->description,
                'preview' => $model->getPreview(),
            );
        }
        echo CJSON::encode($resp);
    }

}
