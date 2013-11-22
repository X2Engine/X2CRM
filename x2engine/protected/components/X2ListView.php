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
Yii::import('zii.widgets.CListView');

/**
 * Renders a CListView
 *
 * @package X2CRM.components
 */
class X2ListView extends CListView {

    protected $ajax = false;

    public function init(){
        $this->ajax = isset($_GET['ajax']) && $_GET['ajax'] === $this->id;
        if($this->ajax)
            ob_clean();
        if($this->itemView === null)
            throw new CException(Yii::t('zii', 'The property "itemView" cannot be empty.'));
        parent::init();

        if(!isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = 'list-view';

        if($this->baseScriptUrl === null)
            $this->baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')).'/listview';

        if($this->cssFile !== false){
            if($this->cssFile === null)
                $this->cssFile = $this->baseScriptUrl.'/styles.css';
            Yii::app()->getClientScript()->registerCssFile($this->cssFile);
        }
    }

    public function run(){
        $this->registerClientScript();

        echo CHtml::openTag($this->tagName, $this->htmlOptions)."\n";

        $this->renderContent();
        $this->renderKeys();
        if($this->ajax){
            // remove any external JS and CSS files
            Yii::app()->clientScript->scriptMap['*.js'] = false;
            Yii::app()->clientScript->scriptMap['*.css'] = false;
            // remove JS for gridview checkboxes and delete buttons (these events use jQuery.on() and shouldn't be reapplied)
            Yii::app()->clientScript->registerScript('CButtonColumn#C_gvControls', null);
            Yii::app()->clientScript->registerScript('CCheckBoxColumn#C_gvCheckbox', null);

            $output = '';
            Yii::app()->getClientScript()->renderBodyEnd($output);
            echo $output;

            echo CHtml::closeTag($this->tagName);
            ob_flush();


            Yii::app()->end();
        }
        echo CHtml::closeTag($this->tagName);
    }

}

?>
