<?php

/**
 * JustGage for YII.
 *
 * @author Christian Oviedo <christian.oviedo@gmail.com>
 * @licence MIT
 * @version 1.0
 * Based on: HighchartsWidget class file by Milo Schuman.
 */

/**
 * JustGage for YII encapsulates {@link http://www.justgage.com}
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 *    $this->Widget('ext.justgage.JustGage', array(
 *       'options'=>array(
 *           'value' => 67, 
 *           'min' => 0,
 *           'max' => 100,
 *           'title' => "Visitors",
 *       ),
 *       'htmlOptions'=> array(
 *           'style'=>'width:200px; height:160px; margin: 0 auto;',
 *       ),
 *   ));
 *
 * </pre>
 *
 * Alternatively, you can use a valid JSON string in place of an associative
 * array to specify options:
 *
 * <pre>
 * $this->Widget('ext.justgage.JustGage', array(
 *       'options'=>'{
 *           "value": 67, 
 *           "min": 0,
 *           "max": 100,
 *           "title": "Visitors"
 *           "title": { "text": "Fruit Consumption" },
 *       }',
 *       'htmlOptions'=> array(
 *           'style'=>'width:200px; height:160px; margin: 0 auto;',
 *       ),
 * ));
 * </pre>
 *
 */

class JustGage extends CWidget
{
    public $options = array();
    public $htmlOptions = array();

    /**
     * Renders the widget.
     */
    public function run()
    {
        if (isset($this->htmlOptions['id'])) {
            $id = $this->htmlOptions['id'];
        } else {
            $id = $this->htmlOptions['id'] = $this->getId();
        }

        echo CHtml::openTag('div', $this->htmlOptions);
        echo CHtml::closeTag('div');

        if (is_string($this->options)) {
            if (!$this->options = CJSON::decode($this->options)) {
                throw new CException('The options parameter is not valid JSON.');
            }
        }

        $defaultOptions = array('id' => $id);
        $this->options = CMap::mergeArray($defaultOptions, $this->options);

        $jsOptions = CJavaScript::encode($this->options);
        $this->registerScripts(__CLASS__ . '#' . $id, "x2." . $id . " = new JustGage($jsOptions);");
    }

    /**
     * Publishes and registers the necessary script files.
     *
     * @param string the id of the script to be inserted into the page
     * @param string the embedded script to be inserted into the page
     */
    protected function registerScripts($id, $embeddedScript)
    {
        $basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
        $baseUrl = Yii::app()->getAssetManager()->publish($basePath, false, 1, YII_DEBUG);

        $extension = (YII_DEBUG)?'.js':'.min.js';

        $cs = Yii::app()->clientScript;
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile("{$baseUrl}/raphael.2.1.0{$extension}");
        $cs->registerScriptFile("{$baseUrl}/justgage.1.0.1{$extension}");

        // register embedded script
        $cs->registerScript($id, $embeddedScript, CClientScript::POS_LOAD);
    }
}
