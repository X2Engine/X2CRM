<?php 
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * Media Selector Widget. 
 * Pops up when the Media button is clicked on the CKEditor. 
 * The button will lazily load this widget via site/mediaSelector
 * and append it to the dom with all JS. 
 */
class MediaSelector extends X2Widget {

    /**
     * @see X2Widget::$viewFile
     */
    public $viewFile = 'application.components.views.mediaSelector';

    /**
     * @see X2Widget::$JSClass
     */
    public $JSClass = 'MediaSelector';

    /**
     * Flag whether or not this widget is being created
     * just to update the Yii List View. If true, javascript will 
     * not re-register. 
     * @var boolean
     */
    public $update = false;

    /**
     * @see X2Widget::init()
     */
    public function init (){
        parent::init ();
    }

    /**
     * @see X2Widget::run()
     */
    public function run() {
        $this->registerPackages ();

        // Instantiate the javascript only if 
        // the view is NOT being updated
        if (!$this->update) 
            $this->instantiateJSClass ();

        $this->render ($this->viewFile);
    }

    /**
     * @see X2Widget::getPackages()
     */
    public function getPackages() {
        $this->_packages = array(
            'MediaSelectorJS' => array(
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array(
                    'js/MediaSelector.js'
                ),
                'depends' => array('Dropzone')
            ),
            'MediaSelectorCSS' => array(
                'baseUrl' => Yii::app()->theme->baseUrl,
                'css' => array(
                    'css/components/MediaSelector.css'
                ),
            ),
        );
        return $this->_packages;
    }

    /**
     * @see X2Widget::getTranslations()
     */
    public function getTranslations () {
        return array(
            'title' => Yii::t('app', 'Image Gallery'),
            'deleteText' => Yii::t('app', 'Are you sure you want to delete this image?'),
            'Insert Image' => Yii::t('app', 'Insert Image'),
            'Close' => Yii::t('app', 'Close')
        );
    }

    /**
     * Gets the dataProvider given to the list view to create
     * the list of images
     * @return CDataProvider Data provider for Yii List View.
     * @todo Tweak to shopw other peoples
     */
    public function getDataProvider () {
        $criteria = new CDbCriteria;

        $criteria->addInCondition ('associationType', 
            array ('none', 'docs'));

        $criteria->addSearchCondition('mimetype', 'image');
        $criteria->addCondition ('uploadedBy="'.Yii::app()->user->name.'"');
        $criteria->addCondition ('drive!=1');

        $dataProvider = new CActiveDataProvider('Media', array(
            'criteria' => $criteria,
            'sort' => array (
                'defaultOrder' => 't.createDate DESC'
            ),
            'pagination' => array (
                'pageSize' => 20,
            ),
        ));
        return $dataProvider;
    }
}
?>