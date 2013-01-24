<?php

class IasPager extends CLinkPager {

    public $listViewId;
    public $rowSelector = '.row';
    public $itemsSelector = '.items';
    public $nextSelector = '.next:not(.hidden) a';
    public $pagerSelector = '.pager';
    private $baseUrl;
    public $options = array();

    public function init() {

        parent::init();

        $assets = dirname(__FILE__) . '/assets';
        $this->baseUrl = Yii::app()->assetManager->publish($assets);

        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerCSSFile($this->baseUrl . '/css/jquery.ias.css');
        $cs->registerScriptFile($this->baseUrl . '/js/jquery.ias.js', CClientScript::POS_END);

        return;
    }

    public function run() {

        $js = "jQuery.ias(" .
                CJavaScript::encode(
                        CMap::mergeArray($this->options, array(
                            'container' => '#' . $this->listViewId . ' ' . $this->itemsSelector,
                            'item' => $this->rowSelector,
                            'pagination' => '#' . $this->listViewId . ' ' . $this->pagerSelector,
                            'next' => '#' . $this->listViewId . ' ' . $this->nextSelector,
                            'loader' => " Loading...",
                        ))) . ");";


        $cs = Yii::app()->clientScript;
        $cs->registerScript('infscrl', $js, CClientScript::POS_READY);


        $buttons = $this->createPageButtons();

        echo $this->header; // if any


        echo CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons));

        echo $this->footer;  // if any
    }

}