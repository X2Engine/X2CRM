<?php
/**
 * MMaske Class File
 * 
 * @author Morris Jencen O. Chavez  <macinville@gmail.com>
 * @version 1
 * @license http://www.opensource.org/licenses/mit-license.php MIT license
 * 
 * @desc MMask (Money Mask) is a wrapper for https://github.com/plentz/jquery-maskmoney
 */

class MMask extends CWidget {

    /**
     * @var string Path of the asset files after publishing.
     */
    private $assetsPath;
    
    /**
     * @var string the selected HTML elements
     */
    public $element;
    
    /**
     * @var array options for maskMoney 
     */
    public $config = array();
    
    /**
     * @var string this will be used to get the currency symbol if $config['symbol'] is not given
     */
    public $currency;
    
    
    public function init() {
        $assets = dirname(__FILE__) . '/' . 'assets';
        $this->assetsPath = Yii::app()->getAssetManager()->publish($assets);
        Yii::app()->getClientScript()->registerScriptFile($this->assetsPath . '/' . 'jquery.maskMoney.js');
        Yii::app()->clientScript->registerCoreScript('jquery');
    }

     public function run() {
         isset($this->config['symbol']) ? '': $this->config['symbol'] = Yii::app()->getLocale()->getCurrencySymbol($this->currency);
         Yii::app()->clientScript->registerScript('processPrint', '
             $("'.$this->element.'").maskMoney('.json_encode($this->config).')');
     }
}

?>
